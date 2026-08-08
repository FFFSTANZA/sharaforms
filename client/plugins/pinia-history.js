import {computed, reactive, nextTick} from 'vue'
import debounce from 'debounce'
import {hash} from "~/lib/utils.js"

/**
 * Merges the user-provided options with the default plugin options.
 * @param {boolean|object} options - The user's configuration options.
 * @returns {object} The merged configuration options.
 */
function mergeOptions(options) {
  const defaults = {
    max: 30,
    persistent: false,
    persistentStrategy: {
      get: function (_store, _type) {
        // Todo
      },
      set: function (_store, _type, _value) {
        // Todo
      },
      remove: function (store, type) {
        if (typeof localStorage !== 'undefined') {
          const key = `pinia-history-${store.$id}-${type}`
          localStorage.removeItem(key)
        }
      }
    },
    debounceWait: 300,
    ignoreKeys: [] // Keys to ignore in history tracking
  }

  return {
    ...defaults,
    ...(typeof options === 'boolean' ? {} : options)
  }
}

/**
 * Filters out ignored keys from the state object
 * @param {Object} state - The state object to filter
 * @param {Array} ignoreKeys - Array of keys to ignore
 * @returns {Object} Filtered state object
 */
function filterState(state, ignoreKeys) {
  const keysToIgnore = ignoreKeys ?? []
  if (keysToIgnore.length === 0) {
    return state
  }
  
  const filteredState = { ...state }
  keysToIgnore.forEach(key => {
    delete filteredState[key]
  })
  
  return filteredState
}

/**
 * State keys that are pure UI state and should not create undo/redo
 * snapshots (sidebar open/close, field selection, bounce animation...).
 */
const UI_ONLY_KEYS = new Set([
  'selectedFieldIndex',
  'showEditFieldSidebar',
  'showAddFieldSidebar',
  'sidebarBounce',
  'activeTab',
  'draggingNewBlock',
])

/**
 * True when a mutation only touched UI-only keys (nothing to snapshot).
 * @param {import('pinia').SubscriptionCallbackMutation} mutation
 * @returns {boolean}
 */
function isUiOnlyMutation(mutation) {
  if (mutation.type === 'direct') {
    const rawEvents = Array.isArray(mutation.events)
      ? mutation.events
      : mutation.events
        ? [mutation.events]
        : []
    const keys = rawEvents
      .map((event) => event.key)
      .filter((key) => key !== undefined)
    if (keys.length === 0) {
      // No usable event keys - fall back to tracking.
      return false
    }
    return keys.every((key) => UI_ONLY_KEYS.has(key))
  }
  if (mutation.type === 'patch object') {
    const keys = Object.keys(mutation.payload)
    return keys.length > 0 && keys.every((key) => UI_ONLY_KEYS.has(key))
  }
  return false
}

/**
 * Adds undo/redo functionality to a Pinia store.
 * @param {PiniaPluginContext} context - The context provided by Pinia.
 */
const PiniaHistory = (context) => {
  const {store, options} = context
  const {history} = options

  if (!history) {
    return
  }
  const mergedOptions = mergeOptions(history)
  const {max, persistent, persistentStrategy, ignoreKeys} = mergedOptions

  const effectiveIgnoreKeys = [...new Set([...(ignoreKeys ?? []), ...UI_ONLY_KEYS])]
  const filterForHistory = (state) => filterState(state, effectiveIgnoreKeys)

  const initialState = JSON.stringify(filterForHistory(store.$state))
  const $history = reactive({
    max,
    persistent,
    persistentStrategy,
    done: [],
    undone: [],
    current: initialState,
    currentHash: hash(initialState),
    trigger: true,
  })

  const debouncedStoreUpdate = debounce((state) => {
    const filteredState = filterForHistory(state)
    const serialized = JSON.stringify(filteredState)
    const newStateHash = hash(serialized)

    if ($history.currentHash === newStateHash) { // Not a real change here
      return
    }
    if ($history.done.length >= max) $history.done.shift() // Remove oldest state if needed

    $history.done.push($history.current)
    $history.undone = [] // Clear redo history on new action
    $history.current = serialized
    $history.currentHash = newStateHash

    if (persistent) {
      persistentStrategy.set(store, 'undo', $history.done)
      persistentStrategy.set(store, 'redo', $history.undone)
    }
  }, mergedOptions.debounceWait)

  store.canUndo = computed(() => $history.done.length > 0)
  store.canRedo = computed(() => $history.undone.length > 0)

  store.undo = () => {
    if (!store.canUndo) {
      return
    }

    debouncedStoreUpdate.clear()
    const state = $history.done.pop()
    if (state === undefined) {
      return
    }

    $history.undone.push($history.current) // Save current state for redo
    $history.trigger = false
    // Only patch the state that was tracked (filtered state)
    const stateToRestore = JSON.parse(state)
    store.$patch(stateToRestore)
    nextTick(() => {
      $history.current = state
      $history.currentHash = hash(state)
      $history.trigger = true
      if (persistent) {
        persistentStrategy.set(store, 'undo', $history.done)
        persistentStrategy.set(store, 'redo', $history.undone)
      }
    })

  }

  store.redo = () => {
    if (!store.canRedo) {
      return
    }
    debouncedStoreUpdate.clear()
    const state = $history.undone.pop()
    if (state === undefined) {
      return
    }

    $history.done.push($history.current) // Save current state for undo
    $history.trigger = false
    // Only patch the state that was tracked (filtered state)
    const stateToRestore = JSON.parse(state)
    store.$patch(stateToRestore)
    nextTick(() => {
      $history.current = state
      $history.currentHash = hash(state)
      $history.trigger = true
      if (persistent) {
        persistentStrategy.set(store, 'undo', $history.done)
        persistentStrategy.set(store, 'redo', $history.undone)
      }
    })
  }

  store.clearHistory = () => {
    $history.done = []
    $history.undone = []
    if (persistent) {
      persistentStrategy.set(store, 'undo', $history.done)
      persistentStrategy.set(store, 'redo', $history.undone)
    }
  }

  store.$subscribe((mutation, state) => {
    if ($history.trigger && !isUiOnlyMutation(mutation)) {
      debouncedStoreUpdate(state)
    }
  })

}

export { PiniaHistory }

export default defineNuxtPlugin(nuxtApp => {
  if (!nuxtApp?.$pinia) {
    return
  }
  nuxtApp.$pinia.use(PiniaHistory)
})

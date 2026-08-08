vi.hoisted(() => {
  ;(globalThis as unknown as Record<string, unknown>).defineNuxtPlugin = (fn: unknown) => fn
})

vi.mock('~/lib/utils.js', () => ({
  hash: (str = '') => {
    let h = 0
    for (let i = 0; i < str.length; i++) {
      h = (h * 31 + str.charCodeAt(i)) | 0
    }
    return h
  },
}))

import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest'
import { createApp } from 'vue'
import { createPinia, setActivePinia, defineStore } from 'pinia'
import { PiniaHistory } from '../../plugins/pinia-history.js'

const useTestStore = defineStore('test', {
  state: () => ({
    content: { title: 'Form', properties: [] },
    selectedFieldIndex: null,
    showEditFieldSidebar: null,
  }),
  history: {
    ignoreKeys: ['structureService'],
  },
})


describe('PiniaHistory plugin', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    const pinia = createPinia()
    const app = createApp({})
    app.use(pinia)
    pinia.use(PiniaHistory)
    setActivePinia(pinia)
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  async function advanceDebounce() {
    await vi.advanceTimersByTimeAsync(300)
  }

  async function flushMicrotasks() {
    await vi.advanceTimersByTimeAsync(0)
  }

  it('creates a snapshot for content changes', async () => {
    const store = useTestStore()
    store.$patch({ content: { title: 'Form', properties: [{ id: 'f1' }] } })
    await advanceDebounce()

    expect(store.canUndo).toBe(true)
  })

  it('does not create a snapshot for UI-only direct mutations', async () => {
    const store = useTestStore()
    store.selectedFieldIndex = 2
    await advanceDebounce()

    expect(store.canUndo).toBe(false)
  })

  it('does not create a snapshot for UI-only patch objects', async () => {
    const store = useTestStore()
    store.$patch({ selectedFieldIndex: 1, showEditFieldSidebar: true })
    await advanceDebounce()

    expect(store.canUndo).toBe(false)
  })

  it('does not create a snapshot when the tracked state is unchanged', async () => {
    const store = useTestStore()
    store.content = { title: 'Form', properties: [] }
    await advanceDebounce()

    expect(store.canUndo).toBe(false)
  })

  it('ignores keys listed in ignoreKeys', async () => {
    const store = useTestStore()
    store.$patch({ structureService: { some: 'instance' } })
    await advanceDebounce()

    expect(store.canUndo).toBe(false)
  })

  it('undo restores the previous content state', async () => {
    const store = useTestStore()
    store.$patch({ content: { title: 'Form', properties: [{ id: 'f1' }] } })
    await advanceDebounce()
    store.$patch({ content: { title: 'Form', properties: [{ id: 'f1' }, { id: 'f2' }] } })
    await advanceDebounce()

    store.undo()
    await flushMicrotasks()

    expect(store.content.properties.map((p) => p.id)).toEqual(['f1'])
    expect(store.canRedo).toBe(true)
  })

  it('redo re-applies the undone state', async () => {
    const store = useTestStore()
    store.$patch({ content: { title: 'Form', properties: [{ id: 'f1' }] } })
    await advanceDebounce()
    store.$patch({ content: { title: 'Form', properties: [{ id: 'f1' }, { id: 'f2' }] } })
    await advanceDebounce()

    store.undo()
    await flushMicrotasks()
    store.redo()
    await flushMicrotasks()

    expect(store.content.properties.map((p) => p.id)).toEqual(['f1', 'f2'])
  })

  it('limits history to max entries', async () => {
    const store = useTestStore()
    for (let i = 0; i < 35; i++) {
      store.$patch({ content: { title: 'Form', properties: [{ id: `f${i}` }] } })
      await advanceDebounce()
    }

    expect(store.canUndo).toBe(true)
    store.undo()
    await flushMicrotasks()
    expect(store.canUndo).toBe(true)
  })
})

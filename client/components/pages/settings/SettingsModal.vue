<template>
  <UModal
    v-model:open="isOpen"
    :ui="{
      content: 'max-w-5xl h-[80vh] overflow-hidden',
    }"
    :content="{
      'aria-label': 'Settings',
      onPointerDownOutside: (event) => { if (event.target?.closest('.crisp-client')) {return event.preventDefault()}}
    }"
    title="Settings"
    description="Manage your account and workspace settings"
  >
    <template #content>
      <div class="flex h-full flex-col sm:flex-row">
        <!-- Left Sidebar -->
        <aside class="sidebar-settings flex flex-col border-b border-[var(--sf-border-card)] sm:w-64 sm:shrink-0 sm:border-r sm:border-b-0">
          <!-- Sidebar Header -->
          <div class="px-5 pt-6 pb-4">
            <div class="flex items-center gap-2.5">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg grad-brand shadow-[0_4px_10px_-4px_rgba(255,92,56,0.45),inset_0_1px_0_0_rgba(255,255,255,0.25)]">
                <i class="fa-solid fa-sliders text-[11px] text-white"></i>
              </span>
              <h2 class="text-[15px] font-bold text-[var(--sf-text-primary)] tracking-tight">Settings</h2>
            </div>
            <p class="mt-2 text-xs text-[var(--sf-text-caption)] font-medium leading-relaxed">
              Manage your account and workspace
            </p>
          </div>

          <!-- Divider -->
          <div class="mx-5 border-t border-[var(--sf-border-divider)]"></div>

          <!-- Navigation Menu (scrollable, vertical, like dashboard sidebar) -->
          <nav class="relative flex-1 overflow-y-auto px-3 py-3">
            <slot name="nav-top" />
            <div class="flex flex-col gap-0.5">
              <button
                v-for="page in registeredPages"
                :key="page.id"
                class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all text-left w-full"
                :class="[
                  activeTabRef === page.id
                    ? 'nav-active font-semibold text-[var(--sf-text-primary)]'
                    : 'text-[var(--sf-text-body)] hover:bg-[var(--sf-nav-hover-bg)]'
                ]"
                @click="setActiveItem(page.id)"
              >
                <!-- Active indicator dot -->
                <span
                  v-if="activeTabRef === page.id"
                  class="nav-indicator absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-[var(--sf-coral-500)]"
                ></span>
                <i
                  :class="page.icon"
                  class="text-[14px] w-4 text-center transition-colors"
                  :style="{ color: activeTabRef === page.id ? 'var(--sf-coral-500)' : 'var(--sf-text-secondary)' }"
                ></i>
                <span class="nav-label">{{ page.label }}</span>
              </button>
            </div>
          </nav>

          <!-- Sidebar Footer -->
          <div class="hidden sm:block px-5 pb-5">
            <div class="rounded-xl border border-[var(--sf-border-card)] bg-white/70 px-3 py-2.5 flex items-center gap-2.5">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-[var(--sf-green-light)] text-[var(--sf-green)] shrink-0">
                <i class="fa-solid fa-circle-question text-[10px]" />
              </span>
              <p class="text-[11px] text-[var(--sf-text-description)] font-medium leading-snug">
                Need help?<br>
                <a :href="`mailto:${links.contact_email}`" class="text-[var(--sf-coral-500)] hover:underline font-semibold">Contact support</a>
              </p>
            </div>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="relative flex flex-1 flex-col overflow-hidden bg-[var(--sf-bg-page)]">
          <!-- Content Body -->
          <div class="flex-1 overflow-y-auto">
            <div class="p-5 sm:p-8">
              <div class="max-w-3xl mx-auto">
                <!-- Modal pages will register themselves and render here -->
                <slot />

                <!-- Default content if no pages registered -->
                <div v-if="registeredPages.length === 0" class="sf-card sf-card-pad py-12 text-center">
                  <UIcon
                    name="i-lucide-settings"
                    class="mx-auto mb-4 h-12 w-12 text-[var(--sf-text-muted)]"
                  />
                  <h3 class="mb-2 text-[15px] font-semibold text-[var(--sf-text-primary)]">
                    Select a setting
                  </h3>
                  <p class="text-[13px] text-[var(--sf-text-description)]">
                    Choose an option from the sidebar to configure your settings.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </template>
  </UModal>
</template>

<script setup>
import { nextTick, ref, computed, watch, provide } from 'vue'
import sharaformsConfig from "~/sharaforms.config.js"

const links = sharaformsConfig.links
const emit = defineEmits(['close', 'item-changed', 'update:activeTab'])

// Registered pages for auto-registration
const registeredPages = ref([])

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  activeTab: {
    type: String,
    default: null
  }
})

// Modal state
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('close', value)
})

// Get first item ID from menu sections
function getFirstItemId() {
  if (registeredPages.value.length > 0) {
    return registeredPages.value[0].id
  }
  return null
}

// Reactive reference for the currently active tab
const activeTabRef = ref(props.activeTab)

// Keep local ref in sync with incoming prop
watch(() => props.activeTab, (newVal) => {
  activeTabRef.value = newVal
})

// Set active item
const setActiveItem = (itemId) => {
  activeTabRef.value = itemId
  emit('item-changed', itemId)
  emit('update:activeTab', itemId)
}

// Reset to default item only when modal opens
watch(isOpen, (newValue) => {
  if (newValue) {
    // Use nextTick to allow child pages to register themselves first, fixing the race condition.
    nextTick(() => {
      const isValidPropTab = props.activeTab && registeredPages.value.some(p => p.id === props.activeTab)

      if (isValidPropTab) {
        // If the prop provides a valid tab, use it.
        activeTabRef.value = props.activeTab
      } else {
        // Otherwise, fallback to the first item if the current selection is invalid.
        const isCurrentTabValid = registeredPages.value.some(p => p.id === activeTabRef.value)
        if (!isCurrentTabValid) {
          activeTabRef.value = getFirstItemId()
        }
      }
    })
  }
})

// Ensure activeTabRef stays in sync with registered pages
watch(registeredPages, () => {
  // If prop specifies a valid tab and it's not already active, set it
  if (props.activeTab && props.activeTab !== activeTabRef.value && registeredPages.value.some(p => p.id === props.activeTab)) {
    activeTabRef.value = props.activeTab
    return
  }

  // If current active item has been removed, fall back to first available
  if (!registeredPages.value.some(p => p.id === activeTabRef.value)) {
    activeTabRef.value = getFirstItemId()
  }
}, { deep: true })

// Registration functions for modal pages
function registerModalPage(id, label, icon) {
  const existingIndex = registeredPages.value.findIndex(page => page.id === id)
  if (existingIndex !== -1) {
    registeredPages.value[existingIndex] = { id, label, icon }
  } else {
    registeredPages.value.push({ id, label, icon })
    if (registeredPages.value.length === 1) {
      activeTabRef.value = id
    }
  }
}

function unregisterModalPage(id) {
  const index = registeredPages.value.findIndex(page => page.id === id)
  if (index !== -1) {
    registeredPages.value.splice(index, 1)
    // If the page being removed was the active one, select a new one.
    if (activeTabRef.value === id) {
      if (registeredPages.value.length > 0) {
        setActiveItem(registeredPages.value[0].id)
      } else {
        activeTabRef.value = null
      }
    }
  }
}

// Provide functions for child components (after they're defined)
provide('activeModalItem', activeTabRef)
provide('registerModalPage', registerModalPage)
provide('unregisterModalPage', unregisterModalPage)
</script>

<style scoped>
/* Settings sidebar — matches dashboard sidebar gradient + hidden scrollbar */
.sidebar-settings {
  background: linear-gradient(180deg, #FFFFFF 0%, var(--sf-bg-muted) 100%);
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.sidebar-settings::-webkit-scrollbar {
  display: none;
}

/* Nav indicator — same as dashboard sidebar */
.nav-indicator {
  opacity: 1;
}
</style>

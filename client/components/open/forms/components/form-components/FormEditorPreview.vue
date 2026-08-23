<template>
  <!-- Backdrop -->
  <div
    v-if="isExpanded"
    class="fixed inset-0 z-40 bg-[var(--sf-bg-surface)]/30 backdrop-blur-xs"
    @click="toggleExpand"
  />

  <!--   Form Preview (desktop: center pane, mobile: embedded full pane)   -->
  <div
    class="form-editor-preview"
    ref="parent"
    :class="{
      'fixed inset-2 md:inset-8 z-50 !flex': isExpanded,
      'bg-[#EDF6F9] flex-grow min-h-0 p-4 flex-col items-center': !isExpanded,
      'flex': !isExpanded && embedded,
      'hidden md:flex': !isExpanded && !embedded
    }"
  >
    <div 
      class="border border-[var(--sf-border-card)] rounded-2xl bg-[var(--sf-bg-surface)] w-full max-w-3xl grow shadow-[var(--sf-shadow-card)] transition-all overflow-hidden flex flex-col min-h-0"
      :class="{ 'h-full max-w-none': isExpanded }"
    >
      <div class="w-full bg-[var(--sf-bg-muted)]/50 border-b border-[var(--sf-border-divider)] rounded-t-2xl px-4 py-2 flex items-center gap-2">
        <div class="flex items-center gap-1.5">
          <div class="w-2.5 h-2.5 rounded-full bg-[#FF5F57]" />
          <div class="w-2.5 h-2.5 rounded-full bg-[#FEBC2E]" />
          <div class="w-2.5 h-2.5 rounded-full bg-[#28C840]" />
        </div>
        <div class="flex-1 flex items-center justify-center">
          <span class="text-[11px] font-medium text-[var(--sf-text-disabled)] select-none uppercase tracking-wider">
            Form Preview
          </span>
        </div>
        <UTooltip :text="previewDarkMode ? 'Disable dark mode preview' : 'Preview in dark mode'" arrow>
          <UButton
            :icon="previewDarkMode ? 'i-lucide-sun' : 'i-lucide-moon'"
            color="neutral"
            variant="outline"
            size="xs"
            @click="toggleDarkPreview"
          />
        </UTooltip>
        <div class="flex-grow" />
        <UButton
          v-if="previewFormSubmitted || (form && (form.presentation_style === 'focused' || form.presentation_style === 'spotlight') && focusedPreviewPage > 0)"
          icon="i-lucide-refresh-cw"
          color="neutral"
          variant="outline"
          size="xs"
          @click="restartForm"
        >
          Re-start
        </UButton>
        <TrackClick
            name="form_editor_toggle_expand"
            :properties="{toggle: !isExpanded}"
          >
        <UTooltip arrow :text="isExpanded ? 'Collapse' : 'Expand'">
         
            <UButton
              :icon="isExpanded ? 'i-lucide-minimize-2' : 'i-lucide-maximize-2'"
              color="neutral"
              variant="outline"
              size="xs"
              @click="toggleExpand"
            />
        </UTooltip>
      </TrackClick>
      </div>
      <div class="relative flex-grow min-h-0">
        <OverlayScrollbarsComponent
          ref="previewScrollInnerRef"
          defer
          class="h-full min-h-0 relative flex flex-col transform-gpu"
        >
          <!-- The transform creates a containing block so descendants with position: fixed
               are anchored to this preview container instead of the page viewport. -->
          <open-complete-form
            v-if="previewReady"
            ref="formPreview"
            class="w-full grow min-h-0"
            :form="previewForm"
            :dark-mode="darkMode"
            :mode="formMode"
            @restarted="previewFormSubmitted=false"
            @submitted="previewFormSubmitted=true"
          />
          <div
            v-else
            class="w-full grow min-h-0 p-6 flex flex-col gap-4"
          >
            <USkeleton class="h-8 w-40" />
            <USkeleton class="h-4 w-72" />
            <USkeleton class="h-24 w-full" />
            <USkeleton class="h-24 w-full" />
            <USkeleton class="h-10 w-28 self-center" />
          </div>
        </OverlayScrollbarsComponent>
        <!-- Quick actions for focused presentation (only when not expanded, spotlight uses classic in collapsed mode) -->
         
        <VTransition name="fade">
          <div
            v-if="!isExpanded && form && form.presentation_style === 'focused'"
            class="absolute top-3 right-3 z-20 flex items-center gap-1 bg-[var(--sf-bg-surface)]/90 backdrop-blur-sm rounded-xl border border-[var(--sf-border-card)] shadow-sm p-1"
          >
            <button
              class="flex items-center justify-center w-7 h-7 rounded-lg text-[var(--sf-text-caption)] hover:text-[var(--sf-coral-500)] hover:bg-[var(--sf-nav-active-bg)] transition-all duration-150"
              title="Add block (⌘B)"
              @click.stop="handleAddBlock"
            >
              <Icon name="i-lucide-plus" class="w-3.5 h-3.5" />
            </button>
            <button
              class="flex items-center justify-center w-7 h-7 rounded-lg text-[var(--sf-text-caption)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-nav-hover-bg)] transition-all duration-150"
              title="Settings"
              @click.stop="handleSettingsCurrent"
            >
              <Icon name="i-lucide-settings" class="w-3.5 h-3.5" />
            </button>
            <button
              class="flex items-center justify-center w-7 h-7 rounded-lg text-[var(--sf-text-caption)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-nav-hover-bg)] transition-all duration-150"
              title="Duplicate"
              @click.stop="handleDuplicateCurrent"
            >
              <Icon name="i-lucide-copy" class="w-3.5 h-3.5" />
            </button>
            <div class="w-px h-4 bg-[var(--sf-border-divider)] mx-0.5" />
            <button
              class="flex items-center justify-center w-7 h-7 rounded-lg text-[var(--sf-text-caption)] hover:text-[var(--sf-status-closed-text)] hover:bg-[var(--sf-status-closed-bg)] transition-all duration-150"
              title="Delete"
              @click.stop="handleDeleteCurrent"
            >
              <Icon name="i-lucide-trash-2" class="w-3.5 h-3.5" />
            </button>
          </div>
        </VTransition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import OpenCompleteForm from '../../OpenCompleteForm.vue'
import {handleDarkMode, useDarkMode} from "~/lib/forms/public-page.js"
import { useWorkingFormStore } from '~/stores/working_form'
import { storeToRefs } from 'pinia'
import { FormMode } from "~/lib/forms/FormModeStrategy.js"
import { useCrisp } from '~/composables/useCrisp.js'
import TrackClick from '~/components/global/TrackClick.vue'
import { useFormEditorPreviewData } from '~/composables/useFormEditorPreviewData.js'

// When embedded (mobile editor pane), the preview is always visible instead
// of hidden below the md breakpoint (desktop center-pane behavior).
defineProps({
  embedded: {
    type: Boolean,
    default: false,
  },
})

const { hideChat, showChat } = useCrisp()

const workingFormStore = useWorkingFormStore()
const { setPreviewFormManager, clearData: clearPreviewData } = useFormEditorPreviewData()

const parent = ref(null)
const previewScrollInnerRef = ref(null)
const formPreview = ref(null)
const previewFormSubmitted = ref(false)
const isExpanded = ref(false)
const previewDarkMode = ref(false)
const previewReady = ref(false)
let previewIdleHandle = null

watch(isExpanded, (expanded) => {
  if (expanded)
    hideChat()
  else
    showChat()
  nextTick(() => previewScrollInnerRef.value?.osInstance()?.update(true))
})

const { content: form } = storeToRefs(workingFormStore)
const darkMode = useDarkMode(parent)

// Use PREVIEW mode when not expanded, TEST mode when expanded
const formMode = computed(() => isExpanded.value ? FormMode.TEST : FormMode.PREVIEW)

// In the inline (collapsed) preview, force classic rendering for spotlight forms
// so all fields are visible and easy to edit. Only show spotlight in expanded mode.
const previewForm = computed(() => {
  if (!form.value) return form.value
  if (!isExpanded.value && form.value.presentation_style === 'spotlight') {
    return { ...form.value, presentation_style: 'classic' }
  }
  return form.value
})

defineShortcuts({
  escape: {
    usingInput: true,
    handler: () => {
      if (isExpanded.value) {
        isExpanded.value = false
      }
    }
  }
})

watch(() => form.value?.dark_mode, () => {
  if (form.value) handleDarkModeChange()
})

watch(previewDarkMode, () => {
  handleDarkModeChange()
})

// Watch for form mode changes to reset the form when switching modes
watch(formMode, () => {
  if (previewFormSubmitted.value) {
    restartForm()
  }
})

onMounted(() => {
  handleDarkModeChange()

  const schedulePreviewMount = () => {
    previewReady.value = true
    previewIdleHandle = null
  }

  if (import.meta.client && typeof window.requestIdleCallback === 'function') {
    previewIdleHandle = window.requestIdleCallback(schedulePreviewMount, { timeout: 120 })
  } else if (import.meta.client) {
    previewIdleHandle = window.setTimeout(schedulePreviewMount, 16)
  } else {
    previewReady.value = true
  }

})

onUnmounted(() => {
  if (import.meta.client && previewIdleHandle !== null) {
    if (typeof window.cancelIdleCallback === 'function') {
      window.cancelIdleCallback(previewIdleHandle)
    } else {
      window.clearTimeout(previewIdleHandle)
    }
  }
  clearPreviewData()
})

// Also share the form manager reference
watch(() => formPreview.value?.formManager, (manager) => {
  if (manager) {
    setPreviewFormManager(manager)
  }
}, { immediate: true })

function handleDarkModeChange() {
  if (!form.value) return
  if (!parent.value) return

  // Apply dark mode to the preview container only — do NOT touch
  // document.documentElement.classList. Removing 'dark' from <html> breaks
  // all --sf-* design tokens used by the editor chrome (navbar, sidebars, etc.).
  // The form-renderer already scopes its dark: variants to the parent element.
  if (previewDarkMode.value) {
    handleDarkMode('dark', parent.value)
  } else {
    handleDarkMode(form.value.dark_mode, parent.value)
  }

  if (formPreview.value?.formManager) {
    formPreview.value.formManager.setDarkMode(parent.value.classList.contains('dark'))
  }
}

function toggleDarkPreview() {
  previewDarkMode.value = !previewDarkMode.value
}

async function restartForm() {
  previewFormSubmitted.value = false

  try {
    // Try using the component reference first
    if (formPreview.value && typeof formPreview.value.restart === 'function') {
      await formPreview.value.restart()
      return
    }
  } catch (error) {
    console.error('Error restarting form:', error)
  }
}

function toggleExpand() {
  isExpanded.value = !isExpanded.value
}

// Current focused preview page. Prefers the live formManager state (source of truth)
// and falls back to the store's structure service getter, which may be unset or stale
// (e.g. before the preview mounts or while the editor is in TEST/expanded mode).
const focusedPreviewPage = computed(() => {
  try {
    const page = formPreview.value?.formManager?.state?.currentPage
    if (typeof page === 'number' && page >= 0) return page
  } catch { /* ignore */ }
  return workingFormStore.formPageIndex
})

// Helpers to get current focused slide index
const currentSlideIndex = computed(() => {
  try {
    // Prefer structure service from store if available
    const struct = workingFormStore.structureService
    if (struct && struct.currentPage !== undefined) {
      const cp = struct.currentPage
      return (typeof cp === 'number') ? cp : (cp?.value ?? 0)
    }
    // Fallback to formPreview's formManager
    return formPreview.value?.formManager?.state?.currentPage ?? 0
  } catch {
    return 0
  }
})

// Shared guards/helpers
// Spotlight only uses focused editing when expanded (collapsed renders classic)
const isFocusedEditing = computed(() => {
  return !!(
    form.value &&
    (form.value.presentation_style === 'focused' || (form.value.presentation_style === 'spotlight' && isExpanded.value)) &&
    workingFormStore.showEditFieldSidebar
  )
})

function isValidIndex(index) {
  const total = (workingFormStore.content?.properties?.length) || 0
  return typeof index === 'number' && index >= 0 && index < total
}

// Helper to get the absolute property index from a visible page index in focused mode
function getAbsoluteIndexFromVisiblePage(visiblePageIndex) {
  const struct = workingFormStore.structureService
  if (!struct || typeof struct.getPageFields !== 'function') return visiblePageIndex
  
  const pageFields = struct.getPageFields(visiblePageIndex)
  if (!pageFields || pageFields.length === 0) return visiblePageIndex
  
  const field = pageFields[0]
  const properties = workingFormStore.content?.properties || []
  const absoluteIndex = properties.findIndex(p => p?.id === field?.id)
  
  return absoluteIndex >= 0 ? absoluteIndex : visiblePageIndex
}

// Sync selected field with current page in focused mode while editing
watch(() => currentSlideIndex.value, (newIndex) => {
  try {
    if (!isFocusedEditing.value) return
    
    // Convert visible page index to absolute property index
    const absoluteIndex = getAbsoluteIndexFromVisiblePage(newIndex)
    
    if (!isValidIndex(absoluteIndex) || workingFormStore.selectedFieldIndex === absoluteIndex) return
    
    // Skip if the field at this index is hidden
    const field = workingFormStore.content?.properties?.[absoluteIndex]
    const struct = workingFormStore.structureService
    if (field && struct && typeof struct.isFieldHidden === 'function' && struct.isFieldHidden(field)) {
      return
    }
    // Update the selected field to follow the currently focused slide
    workingFormStore.setEditingField(absoluteIndex)
  } catch (e) {
    console.error(e)
  }
})

// Also keep the preview page aligned when selection changes externally
watch(() => workingFormStore.selectedFieldIndex, (newIndex) => {
  try {
    if (isFocusedEditing.value && isValidIndex(newIndex)) {
      // Skip if the selected field is hidden
      const field = workingFormStore.content?.properties?.[newIndex]
      const struct = workingFormStore.structureService
      if (field && struct && typeof struct.isFieldHidden === 'function' && struct.isFieldHidden(field)) {
        return
      }
      if (struct && typeof struct.setPageForField === 'function') {
        struct.setPageForField(newIndex)
      }
    }
  } catch (e) {
    console.error(e)
  }
})

function handleAddBlock() {
  try {
    workingFormStore.activeTab = 'build'
    const absoluteIndex = getAbsoluteIndexFromVisiblePage(currentSlideIndex.value)
    workingFormStore.openAddFieldSidebar(absoluteIndex)
  } catch (e) {
    console.error(e)
  }
}

function handleDuplicateCurrent() {
  const index = getAbsoluteIndexFromVisiblePage(currentSlideIndex.value)
  workingFormStore.duplicateField(index)
}

function handleSettingsCurrent() {
  const index = getAbsoluteIndexFromVisiblePage(currentSlideIndex.value)
  workingFormStore.openSettingsForField(index, true)
}

function handleDeleteCurrent() {
  const index = getAbsoluteIndexFromVisiblePage(currentSlideIndex.value)
  workingFormStore.removeField(index)
}
</script>

<style scoped>
.fixed {
  transition: all 0.3s ease-in-out;
}
</style>

<style>
@reference '~/css/app.css';

.form-editor-preview .powered-by-button {
  @apply bottom-10 right-10 z-50;
}

/* Suppress blue/indigo input focus rings in admin preview mode.
   The coral selection state on OpenFormField is the only visual indicator needed. */
.form-editor-preview .open-complete-form .form-group input:focus-visible,
.form-editor-preview .open-complete-form .form-group textarea:focus-visible,
.form-editor-preview .open-complete-form .form-group select:focus-visible,
.form-editor-preview .open-complete-form .form-group [role='listbox']:focus-visible,
.form-editor-preview .open-complete-form .form-group [role='radio']:focus-visible,
.form-editor-preview .open-complete-form .form-group [role='checkbox']:focus-visible,
.form-editor-preview .open-complete-form .form-group [contenteditable]:focus-visible {
  outline: none !important;
  box-shadow: none !important;
}
</style>

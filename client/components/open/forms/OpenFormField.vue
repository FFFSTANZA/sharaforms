<template>
  <div
    v-if="!isFieldHidden"
    :id="'block-' + field.id"
    :data-testid="`open-form-field-${field.id}`"
    ref="form-block"
    class="px-3"
    :class="[
      {
        'group/nffield relative hover:z-10 transition-all duration-150 border border-transparent hover:border-[var(--sf-border-card)] hover:shadow-sm rounded-xl': isAdminPreview,
        'cursor-pointer': workingFormStore.showEditFieldSidebar && isAdminPreview,
        'bg-[var(--sf-nav-active-bg)]/80 hover:!bg-[var(--sf-nav-active-bg)]/80 rounded-xl ring-1 ring-inset ring-[var(--sf-coral-500)]/15': beingEdited,
      }]"
    @click="setFieldAsSelected"
    @dblclick="fieldDoubleClick"
    @mouseenter="onFieldEnter"
    @mouseleave="onFieldLeave"
  >
    <div
      v-if="isAdminPreview"
      ref="controlsRef"
      class="absolute right-full mr-2 top-1 flex flex-row-reverse gap-1 z-20"
      :class="showControls ? 'flex' : 'hidden'"
      @mouseenter="onControlsEnter"
      @mouseleave="onControlsLeave"
    >
      <div
        aria-label="Add block"
        class="flex items-center justify-center w-6 h-6 rounded-lg bg-[var(--sf-bg-surface)] border border-[var(--sf-border-card)] shadow-sm cursor-pointer text-[var(--sf-text-muted)] hover:text-[var(--sf-coral-500)] hover:border-[var(--sf-coral-500)]/30 hover:bg-[var(--sf-nav-active-bg)] transition-all duration-150"
        role="button"
        @click.stop.prevent="openAddFieldSidebar"
      >
        <Icon name="i-lucide-plus" class="w-3.5 h-3.5" />
      </div>
      <div
        aria-label="Settings"
        class="flex items-center justify-center w-6 h-6 rounded-lg bg-[var(--sf-bg-surface)] border border-[var(--sf-border-card)] shadow-sm cursor-pointer text-[var(--sf-text-muted)] hover:text-[var(--sf-text-primary)] hover:border-[var(--sf-hover-border)] transition-all duration-150"
        role="button"
        @click.stop.prevent="editFieldOptions"
      >
        <Icon name="i-lucide-settings" class="w-3.5 h-3.5" />
      </div>
      <div
        aria-label="Delete"
        class="flex items-center justify-center w-6 h-6 rounded-lg bg-[var(--sf-bg-surface)] border border-[var(--sf-border-card)] shadow-sm cursor-pointer text-[var(--sf-text-muted)] hover:text-[var(--sf-status-closed-text)] hover:border-[var(--sf-status-closed-border)] hover:bg-[var(--sf-status-closed-bg)] transition-all duration-150"
        role="button"
        @click.stop.prevent="removeField"
      >
        <Icon name="i-lucide-trash-2" class="w-3.5 h-3.5" />
      </div>
    </div>
    <div
      class="-m-[1px] w-full max-w-full mx-auto"
    >
      <div v-if="field">
        <BlockRenderer :block="field" :form-manager="formManager" />
      </div>
    </div>
    <div
      v-if="isAdminPreview"
      class="absolute -right-6 top-1 h-full w-6 flex-col items-center justify-center"
      :class="showControls ? 'flex' : 'hidden'"
      @mouseenter="onControlsEnter"
      @mouseleave="onControlsLeave"
    >
      <div
        class="flex items-center justify-center bg-[var(--sf-bg-surface)] border border-[var(--sf-border-card)] rounded-lg w-5 h-8 text-[var(--sf-text-disabled)] cursor-grab handle transition-all duration-150 hover:bg-[var(--sf-bg-muted)] hover:text-[var(--sf-text-body)] hover:border-[var(--sf-hover-border)] shadow-sm"
      >
        <Icon
          name="lucide:grip-vertical"
          class="h-3.5 w-3.5"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { FormMode, createFormModeStrategy } from "~/lib/forms/FormModeStrategy.js"
import { useWorkingFormStore } from '~/stores/working_form'
import BlockRenderer from './BlockRenderer.vue'

// Define props
const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  formManager: {
    type: Object,
    required: true
  }
})

// Derive everything from formManager
const form = computed(() => props.formManager?.config?.value || {})
const showHidden = computed(() => props.formManager?.strategy?.value?.display?.showHiddenFields || false)

// Setup stores and reactive state
const workingFormStore = useWorkingFormStore()
const selectedFieldIndex = computed(() => workingFormStore.selectedFieldIndex)
const showEditFieldSidebar = computed(() => workingFormStore.showEditFieldSidebar)
const strategy = computed(() => props.formManager?.strategy?.value || createFormModeStrategy(FormMode.LIVE))
const isAdminPreview = computed(() => strategy.value?.admin?.showAdminControls || false)

// Computed properties
// Field rendering is delegated to BlockRenderer

const fieldState = computed(() => props.formManager?.fieldState)
const shouldBeHidden = computed(() => fieldState.value?.getState?.(props.field)?.hidden ?? false)
const isFieldHidden = computed(() => !showHidden.value && shouldBeHidden.value)

// Required/props now handled inside BlockRenderer
/* noop */

const beingEdited = computed(() => 
  isAdminPreview.value && 
  showEditFieldSidebar.value && 
  form.value.properties.findIndex((item) => item.id === props.field.id) === selectedFieldIndex.value
)

// Hover state for controls (JS-driven to avoid CSS hover zone limitations)
const showControls = ref(false)
const controlsRef = ref(null)
let hoverTimer = null

function showControlsWithTimer() {
  clearTimeout(hoverTimer)
  showControls.value = true
}

function hideControlsWithTimer() {
  clearTimeout(hoverTimer)
  hoverTimer = setTimeout(() => {
    showControls.value = false
  }, 300)
}

function onFieldEnter() {
  showControlsWithTimer()
}

function onFieldLeave() {
  hideControlsWithTimer()
}

function onControlsEnter() {
  showControlsWithTimer()
}

function onControlsLeave() {
  hideControlsWithTimer()
}

// Methods
function editFieldOptions() {
  if (!isAdminPreview.value) return
  workingFormStore.openSettingsForField(props.field, true)
}

function setFieldAsSelected() {
  if (!isAdminPreview.value || !workingFormStore.showEditFieldSidebar) return
  workingFormStore.openSettingsForField(props.field)
}

function fieldDoubleClick() {
  if (!isAdminPreview.value) return
  workingFormStore.openSettingsForField(props.field)
}

function openAddFieldSidebar() {
  if (!isAdminPreview.value) return
  workingFormStore.openAddFieldSidebar(props.field)
}

function removeField() {
  if (!isAdminPreview.value) return
  workingFormStore.removeField(props.field)
}

/**
 * Get the right input component options for the field/options
 */

</script>

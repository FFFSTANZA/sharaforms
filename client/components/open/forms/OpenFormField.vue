<template>
  <div
    v-if="!isFieldHidden"
    :id="'block-' + field.id"
    :data-testid="`open-form-field-${field.id}`"
    ref="form-block"
    class="px-2"
    :class="[
      {
        'group/nffield hover:bg-neutral-100/50 relative hover:z-10 transition-colors hover:border-neutral-200 border-dashed border border-transparent box-border dark:hover:border-blue-900 dark:hover:bg-blue-950 rounded-md': isAdminPreview,
        'cursor-pointer':workingFormStore.showEditFieldSidebar && isAdminPreview,
        'bg-blue-50 hover:!bg-blue-50 dark:bg-neutral-700! dark:hover:bg-neutral-700! rounded-md': beingEdited,
      }]"
    @click="setFieldAsSelected"
    @dblclick="fieldDoubleClick"
    @mouseenter="onFieldEnter"
    @mouseleave="onFieldLeave"
  >
    <div
      v-if="isAdminPreview"
      ref="controlsRef"
      class="absolute right-full mr-2 top-0 flex-row-reverse gap-0.5 z-20"
      :class="showControls ? 'flex' : 'hidden'"
      @mouseenter="onControlsEnter"
      @mouseleave="onControlsLeave"
    >
      <div
        aria-label="Add block"
        class="flex items-center justify-center w-6 h-6 rounded-md hover:bg-neutral-200/70 dark:hover:bg-neutral-700 cursor-pointer text-neutral-300 hover:text-neutral-600 dark:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
        role="button"
        @click.stop.prevent="openAddFieldSidebar"
      >
        <Icon name="i-lucide-plus" class="w-4 h-4" />
      </div>
      <div
        aria-label="Settings"
        class="flex items-center justify-center w-6 h-6 rounded-md hover:bg-neutral-200/70 dark:hover:bg-neutral-700 cursor-pointer text-neutral-300 hover:text-neutral-600 dark:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
        role="button"
        @click.stop.prevent="editFieldOptions"
      >
        <Icon name="i-lucide-settings" class="w-4 h-4" />
      </div>
      <div
        aria-label="Delete"
        class="flex items-center justify-center w-6 h-6 rounded-md hover:bg-red-100 dark:hover:bg-red-950 cursor-pointer text-neutral-300 hover:text-red-400 dark:text-neutral-600 dark:hover:text-red-400 transition-colors"
        role="button"
        @click.stop.prevent="removeField"
      >
        <Icon name="i-lucide-trash-2" class="w-4 h-4" />
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
      class="absolute -right-5 top-0 h-full w-5 flex-col items-center justify-center"
      :class="showControls ? 'flex' : 'hidden'"
      @mouseenter="onControlsEnter"
      @mouseleave="onControlsLeave"
    >
      <div
        class="flex items-center justify-center bg-neutral-100 dark:bg-neutral-800 border rounded-md w-5 text-neutral-400 dark:text-neutral-500 dark:border-neutral-700 cursor-grab handle transition-colors hover:bg-neutral-200 dark:hover:bg-neutral-700 h-10"
      >
        <Icon
          name="lucide:grip-vertical"
          class="h-4 w-4"
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

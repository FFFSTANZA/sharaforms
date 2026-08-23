<template>
  <VTransition name="fade">
    <div
      v-if="form"
      id="form-editor"
      class="relative flex w-full flex-col grow max-h-screen supports-[height:100dvh]:max-h-[100dvh]"
      key="form"
    >
      <!-- Loading overlay -->
      <div
        v-if="form.busy || loading"
        class="absolute inset-0 bg-[var(--sf-bg-surface)] bg-opacity-90 z-50 flex items-center justify-center backdrop-blur-sm"
      >
        <loader class="h-6 w-6 text-[var(--sf-coral-500)]" />
      </div>
      <FormEditorNavbar
        :back-button="backButton"
        :update-form-loading="form.busy"
        :save-button-class="saveButtonClass"
        @go-back="goBack"
        @save-form="saveForm"
      >
        <template #before-save>
          <slot name="before-save" />
        </template>
      </FormEditorNavbar>

      <!-- Desktop layout: three-pane (fields panel | preview | sidebar) -->
      <FormEditorErrorHandler v-if="!isMobile">
        <div class="w-full flex grow min-h-0 overflow-hidden relative bg-[var(--sf-bg-page)]">
          <div
            ref="elementRef"
            class="relative shrink-0 min-h-0 overflow-hidden border-r border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)]"
            :class="isResizable ? '' : 'w-full md:w-1/2 md:max-w-xs lg:w-2/5'"
            :style="isResizable ? dynamicStyles : {}"
          >
            <ResizeHandle
              :show="isResizable"
              direction="left"
              @start-resize="startResize"
              class="z-20"
            />

            <OverlayScrollbarsComponent defer class="h-full">
              <VForm
                size="sm"
                @submit.prevent=""
              >
                <div
                  v-show="activeTab === 'build'"
                >
                  <FormFieldsEditor />
                </div>
                <div
                  v-show="activeTab === 'design'"
                >
                  <FormCustomization />
                </div>
              </VForm>
            </OverlayScrollbarsComponent>
          </div>

          <FormEditorPreview />

          <FormEditorSidebar />
        </div>
      </FormEditorErrorHandler>

      <!-- Mobile layout: single pane + bottom nav; sidebars become full-screen sheets -->
      <FormEditorErrorHandler v-else>
        <div class="flex w-full grow min-h-0 flex-col bg-[var(--sf-bg-page)]">
          <div class="relative grow min-h-0">
            <div
              v-show="mobileTab !== 'preview'"
              class="absolute inset-0 overflow-y-auto overscroll-contain"
            >
              <VForm
                size="sm"
                @submit.prevent=""
              >
                <div v-show="mobileTab === 'build'">
                  <FormFieldsEditor />
                </div>
                <div v-show="mobileTab === 'design'">
                  <FormCustomization />
                </div>
              </VForm>
            </div>

            <div
              v-show="mobileTab === 'preview'"
              class="absolute inset-0 flex flex-col"
            >
              <FormEditorPreview embedded />
            </div>
          </div>

          <FormEditorMobileNav v-model="mobileTab" />

          <transition name="editor-sheet">
            <div
              v-if="mobileSheetOpen"
              class="fixed inset-0 z-40 flex flex-col bg-[var(--sf-bg-surface)]"
            >
              <AddFormBlock
                v-if="showAddFieldSidebar"
                class="min-h-0 grow overflow-y-auto overscroll-contain"
              />
              <FormFieldEdit
                v-else-if="showEditFieldSidebar"
                :key="selectedFieldIndex"
                class="min-h-0 grow overflow-y-auto overscroll-contain"
              />
            </div>
          </transition>
        </div>
      </FormEditorErrorHandler>

      <!-- Form Error Modal -->
      <FormErrorModal
        :show="showFormErrorModal"
        :validation-error-response="validationErrorResponse"
        @close="showFormErrorModal = false"
      />

      <!-- Logic Confirmation Modal -->
      <LogicConfirmationModal
        :is-visible="showLogicConfirmationModal"
        :errors="logicErrors"
        @cancel="handleLogicConfirmationCancel"
        @confirm="handleLogicConfirmationConfirm"
      />
    </div>
    <FormEditorSkeleton
      v-else
      key="skeleton"
      :back-button="backButton"
      @go-back="goBack"
    />
  </VTransition>
</template>

<script setup>
import FormEditorNavbar from './FormEditorNavbar.vue'
import FormEditorSkeleton from './FormEditorSkeleton.vue'
import FormEditorSidebar from "./form-components/FormEditorSidebar.vue"
import FormErrorModal from "./form-components/FormErrorModal.vue"
import FormFieldsEditor from './FormFieldsEditor.vue'
import FormCustomization from "./form-components/FormCustomization.vue"
import FormEditorPreview from "./form-components/FormEditorPreview.vue"
import { useFormLogic } from "~/composables/forms/useFormLogic.js"
import FormEditorErrorHandler from '~/components/open/forms/components/FormEditorErrorHandler.vue'
import FormEditorMobileNav from './form-components/FormEditorMobileNav.vue'
import AddFormBlock from './form-components/AddFormBlock.vue'
import FormFieldEdit from '../fields/FormFieldEdit.vue'
import { setFormDefaults, ensureSettingsObject } from '~/composables/forms/initForm.js'
import { breakpointsTailwind, useBreakpoints } from '@vueuse/core'
import LogicConfirmationModal from '~/components/forms/heavy/LogicConfirmationModal.vue'
import { useResizable } from '~/composables/components/useResizable'
import ResizeHandle from '~/components/global/ResizeHandle.vue'

// Define props
const props = defineProps({
  isEdit: {
    required: false,
    type: Boolean,
    default: false,
  },
  isGuest: {
    required: false,
    type: Boolean,
    default: false,
  },
  backButton: {
    required: false,
    type: Boolean,
    default: true,
  },
  saveButtonClass: {
    required: false,
    type: String,
    default: "",
  },
  loading: {
    required: false,
    type: Boolean,
    default: false,
  }
})

// Define emits
const emit = defineEmits(['mounted', 'on-save', 'openRegister', 'go-back', 'save-form'])

// Reactive data
const showFormErrorModal = ref(false)
const showLogicConfirmationModal = ref(false)
const validationErrorResponse = ref(null)
const createdFormSlug = ref(null)
const logicErrors = ref([])
const route = useRoute()
const { emitFormSaved, emitNavigateBack } = useEditorEmbedBridge()

// Sidebar resizing using composable
const { 
  elementRef, 
  isResizable, 
  dynamicStyles, 
  startResize
} = useResizable({
  storageKey: 'formEditorSidebarWidth',
  defaultWidth: 315,
  direction: 'left',
  maxWidth: () => Math.min(600, window.innerWidth * 0.6)
})

// Mobile layout support: the editor renders a single-pane layout below md
const breakpoints = useBreakpoints(breakpointsTailwind)
// Starts as false so that SSR and the hydration render both produce the
// desktop tree (the old gate was CSS-only). Flips after mount, so mobile
// devices re-render cleanly without a hydration mismatch.
const isMobile = ref(false)
const isMobileQuery = breakpoints.smaller('md')
onMounted(() => {
  isMobile.value = isMobileQuery.value
})
watch(isMobileQuery, (value) => {
  isMobile.value = value
})

// Composables
const { content: form } = storeToRefs(useWorkingFormStore())

watch(
  () => form.value,
  (f) => {
    if (!f || typeof f.data !== 'function') {
      return
    }
    ensureSettingsObject(f)
  },
  { flush: 'sync', immediate: true },
)

const { current: workspace } = useCurrentWorkspace()

// Initialize TanStack Query mutations for forms
const { create: createFormMutationFactory, update: updateFormMutationFactory } = useForms()
const createMutation = createFormMutationFactory()

// Create update mutation with reactive form ID
const formId = computed(() => form.value?.id)
const updateMutation = updateFormMutationFactory(formId)

const workingFormStore = useWorkingFormStore()
const crisp = useCrisp()
const posthog = usePostHog()

// Keyboard shortcut to open add field sidebar
defineShortcuts({
  meta_b: {
    handler: () => {
      workingFormStore.openAddFieldSidebar()
    }
  },
  ctrl_b: {
    handler: () => {
      workingFormStore.openAddFieldSidebar()
    }
  }
})

// Mobile layout switches between Build/Design and Preview via a bottom nav.
// The mobile panels key off mobileTab directly (never a possibly-stale store
// value); the immediate watch keeps the shared store activeTab aligned for
// desktop logic and breakpoint crossings.
const mobileTab = ref('build')
watch(mobileTab, (value) => {
  if (value !== 'preview') {
    workingFormStore.activeTab = value
  }
}, { immediate: true })

const showAddFieldSidebar = computed(() => workingFormStore.showAddFieldSidebar)
const showEditFieldSidebar = computed(() => workingFormStore.showEditFieldSidebar)
const selectedFieldIndex = computed(() => workingFormStore.selectedFieldIndex)
const mobileSheetOpen = computed(() => isMobile.value && (showAddFieldSidebar.value || showEditFieldSidebar.value))

// Computed properties
const activeTab = computed(() => workingFormStore.activeTab)

// Methods
const goBack = () => {
  const toRoute = props.isEdit ? 'forms-slug-show-submissions' : 'home'
  emitNavigateBack(route.name, toRoute)

  if (props.isEdit) {
    useRouter().push({ name: toRoute, params: { slug: form.value.slug } })
  } else {
    useRouter().push({ name: toRoute })
  }
}

const displayFormModificationAlert = (responseData) => {
  const alert = useAlert()
  if (
    responseData.form &&
    responseData.form.cleanings &&
    Object.keys(responseData.form.cleanings).length > 0
  ) {
    alert.warning(responseData.message, 10000, { form: responseData.form })
  } else if (responseData.message) {
    alert.success(responseData.message, 10000, { form: responseData.form })
  }
}

const showValidationErrors = () => {
  showFormErrorModal.value = true
}

const saveForm = () => {
  // Apply defaults to the form
  const defaultedData = setFormDefaults(form.value.data())
  form.value.fill(defaultedData)

  // Check for logic errors
  const { getLogicErrors } = useFormLogic()
  logicErrors.value = getLogicErrors(form.value.properties)
  
  if (logicErrors.value.length > 0) {
    showLogicConfirmationModal.value = true
    return
  }
  
  proceedWithSave()
}

const proceedWithSave = () => {
  if (logicErrors.value.length > 0) {
    // Clean invalid logic before saving using the comprehensive validator
    const { validatePropertiesLogic } = useFormLogic()
    form.value.properties = validatePropertiesLogic(form.value.properties)
  }

  if (props.isGuest) {
    saveFormGuest()
  } else if (props.isEdit) {
    saveFormEdit()
  } else {
    saveFormCreate()
  }
}

const handleLogicConfirmationCancel = () => {
  showLogicConfirmationModal.value = false
}

const handleLogicConfirmationConfirm = () => {
  showLogicConfirmationModal.value = false
  proceedWithSave()
}

const saveFormEdit = () => {
  if (form.value.busy || !form.value.id) return

  validationErrorResponse.value = null

  form.value.mutate(updateMutation).then((response) => {
    const updatedForm = response.form
    emit("on-save")
    emitFormSaved(updatedForm, { isNew: false })

    // Navigate to share page
    useRouter().push({
      name: "forms-slug-show-share",
      params: { slug: updatedForm.slug },
    })

    try{
    // Analytics / alerts
    posthog.logEvent("form_saved", {
      form_id: updatedForm.id,
      form_slug: updatedForm.slug,
    })
    displayFormModificationAlert(response)
    } catch (error) {
      console.error("Analytics error", error)
    }
  }).catch((error) => {
    console.error("Error saving form", error)
    
    // Check for 401 errors - these are handled by the HTTP interceptor
    const errorStatus = error?.response?.status || error?.status
    if (errorStatus === 401) {
      // Token expiry is handled by the HTTP interceptor (opens QuickRegister modal)
      // Don't show an additional error message
      return
    }
    
    if (errorStatus === 422) {
      validationErrorResponse.value = error.data
      showValidationErrors()
    } else {
      console.error(error)
      useAlert().error(
        "An error occurred while saving the form, please try again.",
      )
      usePostHog().captureException(error)
    }
  })
}

const saveFormCreate = () => {
  if (form.value.busy) return

  if (!workspace.value?.id) {
    useAlert().error("Your workspace is still loading, please try again.")
    return
  }

  // Attach workspace ID before sending
  form.value.workspace_id = workspace.value.id
  validationErrorResponse.value = null

  form.value.mutate(createMutation).then((response) => {
    const newForm = response.form
    emit("on-save")
    createdFormSlug.value = newForm.slug
    emitFormSaved(newForm, { isNew: true })

    try{
      // Analytics / alerts
      posthog.logEvent("form_created", {
        form_id: newForm.id,
        form_slug: newForm.slug,
      })
      crisp.pushEvent("form_created", {
        form_id: newForm.id,
        form_slug: newForm.slug,
      })
    } catch (error) {
      console.error("Analytics error", error)
    }
    displayFormModificationAlert(response)

    useRouter().push({
      name: "forms-slug-show-share",
      params: {
        slug: createdFormSlug.value,
      },
    })
  }).catch((error) => {
    console.error("Error saving form", error)
    
    // Check for 401 errors - these are handled by the HTTP interceptor
    const errorStatus = error?.response?.status || error?.status
    if (errorStatus === 401) {
      // Token expiry is handled by the HTTP interceptor (opens QuickRegister modal)
      // Don't show an additional error message
      return
    }
    
    if (errorStatus === 422) {
      validationErrorResponse.value = error.data
      showValidationErrors()
    } else {
      useAlert().error(
        "An error occurred while saving the form, please try again.",
      )
      usePostHog().captureException(error)
    }
  })
}

const saveFormGuest = () => {
  emit("openRegister")
}

defineExpose({
  saveFormCreate,
  showValidationErrors
})

// Lifecycle hooks
onMounted(() => {
  emit("mounted")
  workingFormStore.activeTab = 'build'
  posthog.logEvent('form_editor_viewed', {
    platform: isMobile.value ? 'mobile_web' : 'desktop_web',
  })

  if (!props.isEdit) {
    nextTick(() => {
      workingFormStore.openAddFieldSidebar()
    })
  }
})
</script>

<style lang="scss">
.v-step {
  color: white;

  .v-step__header,
  .v-step__content {
    color: white;

    div {
      color: white;
    }
  }
}
</style>

<style scoped>
.editor-sheet-enter-active,
.editor-sheet-leave-active {
  transition: transform 0.25s ease, opacity 0.2s ease;
}

.editor-sheet-enter-from,
.editor-sheet-leave-to {
  transform: translateY(100%);
  opacity: 0.5;
}
</style>

<template>
  <div class="flex flex-wrap flex-col flex-grow">
    <create-form-base-modal
      :show="showInitialFormModal"
      :default-import-source="defaultImportSource"
      @form-generated="formGenerated"
      @form-imported="formImported"
      @close="showInitialFormModal = false"
    />
    <VTransition name="fade">
      <FormEditor
        v-if="editorReady"
        ref="editor"
        class="w-full flex flex-grow"
        :error="error"
        :is-guest="isGuest"
        :loading="workspacesLoading"
        @open-register="appStore.quickRegisterModal = true"
      />
    </VTransition>
  </div>
</template>

<script setup>
import FormEditor from "~/components/open/forms/components/FormEditor.vue"
import CreateFormBaseModal from "../../../components/pages/forms/create/CreateFormBaseModal.vue"
import { initForm } from "~/composables/forms/initForm.js"
import { useQueryClient } from "@tanstack/vue-query"

import { WindowMessageTypes } from "~/composables/useWindowMessage"
import { contentApi } from "~/api/content"

const appStore = useAppStore()
const workingFormStore = useWorkingFormStore()
const route = useRoute()
const queryClient = useQueryClient()

let template = null
if (route.query.template) {
  const { data, suspense } = useTemplates().detail(route.query.template)
  await suspense()
  template = data.value
}

// Use workspaces query composable for invalidation functionality
const { invalidateAll } = useWorkspaces()

// Store values
const workspacesLoading = computed(() => {
  // For guest mode, we'll manage loading state manually
  return !stateReady.value
})
const form = storeToRefs(workingFormStore).content

useOpnSeoMeta({
  title: "Create a new Form for free",
})
definePageMeta({
  middleware: ["guest"],
  layout: 'empty'
})

// Data
const stateReady = ref(false)
const error = ref("")
const isGuest = ref(true)
const showInitialFormModal = ref(false)
const editorBootstrapped = ref(false)
const hasInitialTemplate = computed(() => !!(template && template.structure))
const editorReady = computed(() => stateReady.value && (editorBootstrapped.value || hasInitialTemplate.value))
const supportedGuestImportSources = ['typeform', 'tally', 'fillout']
const defaultImportSource = computed(() => {
  const source = route.query.import
  return supportedGuestImportSources.includes(source) ? source : null
})

// Component ref
const editor = ref(null)

onMounted(async () => {
  // Fetch plans data to populate free-tier workspace
  let freeLimits = {}
  let requiredTiers = {}
  try {
    const plansData = await contentApi.plans.list()
    if (plansData?.limits) {
      freeLimits = Object.fromEntries(
        Object.entries(plansData.limits).map(([key, tiers]) => [key, tiers.free])
      )
    }
    requiredTiers = { ...(plansData?.features ?? {}), ...(plansData?.form_features ?? {}) }
  } catch {
    // Fallback to defaults if API call fails
    freeLimits = {}
    requiredTiers = {}
  }

  // Set guest workspace data in query cache instead of store
  const guestWorkspace = {
    id: null,
    name: "Guest Workspace",
    plan_tier: 'free',
    features: [],
    limits: freeLimits,
    required_tiers: requiredTiers,
  }
  
  // Manually set the workspace data in query cache
  queryClient.setQueryData(["workspaces", "list"], [guestWorkspace])

  form.value = initForm({}, true)
  if (template && template.structure) {
    form.value = useForm({ ...form.value.data(), ...template.structure })
  } else {
    // No template loaded, ask how to start
    showInitialFormModal.value = true
  }
  stateReady.value = true

  const scheduleEditorBootstrap = () => {
    editorBootstrapped.value = true
  }

  if (import.meta.client && typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(scheduleEditorBootstrap, { timeout: 120 })
  } else if (import.meta.client) {
    window.setTimeout(scheduleEditorBootstrap, 16)
  } else {
    editorBootstrapped.value = true
  }

  // Set up window message listener for after-login
  const afterLoginMessage = useWindowMessage(WindowMessageTypes.AFTER_LOGIN)
  afterLoginMessage.listen(() => {
    afterLogin()
  }, { useMessageChannel: false })
})

// Guard against a duplicate AFTER_LOGIN delivery (e.g. popup retry) triggering the
// form save twice within a short window.
let lastLoggedInAt = 0

const afterLogin = () => {
  const now = Date.now()
  if (now - lastLoggedInAt < 800) return
  lastLoggedInAt = now

  isGuest.value = false
  invalidateAll() // Refetch all workspace queries
  setTimeout(() => {
    if (editor) {
      editor.value.saveFormCreate()
    }
  }, 500)
}

const formGenerated = (newForm) => {
  form.value = useForm({ ...form.value.data(), ...newForm })
}

const formImported = (importedForm) => {
  form.value = useForm({ ...form.value.data(), ...importedForm })
}
</script>

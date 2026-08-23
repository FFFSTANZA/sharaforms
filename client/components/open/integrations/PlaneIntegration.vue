<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <!-- API Credentials -->
    <text-input
      :form="integrationData"
      name="data.api_key"
      label="Plane API Key"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            Generate an API key in your Plane workspace under
            <a
              href="https://app.plane.so/settings/api-tokens"
              target="_blank"
              class="text-blue-500"
            >
              Settings → API Tokens
            </a>.
          </span>
        </InputHelp>
      </template>
    </text-input>
    <text-input
      :form="integrationData"
      name="data.base_url"
      label="Instance URL (optional)"
      help="help"
    >
      <template #help>
        <InputHelp>
          <span>
            Leave empty for Plane Cloud (api.plane.so). For self-hosted instances enter your API URL (e.g. https://api.plane.example.com).
          </span>
        </InputHelp>
      </template>
    </text-input>

    <!-- Workspace Selector -->
    <div
      v-if="hasCredentials"
      class="mt-4"
    >
      <FlatSelectInput
        v-if="workspaces.length"
        v-model="integrationData.data.workspace_slug"
        name="workspace"
        :options="workspaces"
        display-key="name"
        option-key="slug"
        emit-key="slug"
        :required="true"
        label="Select Workspace"
        :loading="isWorkspacesLoading"
        @update:model-value="onWorkspaceChange"
      />
      <div
        v-else-if="!isWorkspacesLoading"
        class="text-sm text-neutral-500 mb-4"
      >
        No workspaces found for this API key.
      </div>
    </div>

    <!-- Project Selector -->
    <div
      v-if="integrationData.data.workspace_slug"
      class="mt-4"
    >
      <FlatSelectInput
        v-if="projects.length"
        v-model="integrationData.data.project_id"
        name="project"
        :options="projects"
        display-key="name"
        option-key="id"
        emit-key="id"
        :required="true"
        label="Select Project"
        :loading="isProjectsLoading"
        @update:model-value="onProjectChange"
      />
      <div
        v-else-if="!isProjectsLoading"
        class="text-sm text-neutral-500 mb-4"
      >
        No projects found in this workspace.
      </div>
    </div>

    <!-- Issue Configuration -->
    <div
      v-if="integrationData.data.project_id"
      class="mt-4"
    >
      <h4 class="font-bold mb-3">
        Issue Options
      </h4>

      <MentionInput
        v-model="integrationData.data.issue_title_template"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="issue_title_template"
        class="mb-4"
        label="Issue Title"
        help="Customize the issue title. Click @ to include form field values."
      />

      <MentionInput
        v-model="integrationData.data.description_template"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="description_template"
        class="mb-4"
        label="Issue Description (optional)"
        help="Additional description text. Use @ to include field values. Submission data will be appended below."
      />

      <FlatSelectInput
        v-if="states.length"
        v-model="integrationData.data.state_id"
        name="state"
        :options="stateOptions"
        display-key="name"
        option-key="value"
        emit-key="value"
        label="State (optional)"
        :loading="isStatesLoading"
        class="mb-4"
      />

      <FlatSelectInput
        v-model="integrationData.data.priority"
        name="priority"
        :options="priorityOptions"
        display-key="name"
        option-key="value"
        emit-key="value"
        label="Priority (optional)"
        class="mb-4"
      />

      <h4 class="font-bold mt-4">
        Description Options
      </h4>
      <toggle-switch-input
        v-model="integrationData.data.include_submission_data"
        name="include_submission_data"
        class="mt-4"
        label="Include submission data"
        help="With form submission answers"
      />
      <toggle-switch-input
        v-if="integrationData.data.include_submission_data"
        v-model="integrationData.data.include_hidden_fields_submission_data"
        name="include_hidden_fields_submission_data"
        class="mt-4"
        label="Include hidden fields"
        help="If enabled then hidden fields will be included in the description"
      />
      <toggle-switch-input
        v-model="integrationData.data.link_open_form"
        name="link_open_form"
        class="mt-4"
        label="'Open Form' Link"
        help="Link to the form public page"
      />
      <toggle-switch-input
        v-model="integrationData.data.link_edit_form"
        name="link_edit_form"
        class="mt-4"
        label="'Edit Form' Link"
        help="Link to the form admin page"
      />
      <toggle-switch-input
        v-if="form.editable_submissions"
        v-model="integrationData.data.link_edit_submission"
        name="link_edit_submission"
        class="mt-4"
        label="Edit Submission Link"
      />
    </div>
  </IntegrationWrapper>
</template>

<script setup>
import IntegrationWrapper from "./components/IntegrationWrapper.vue"
import { formsApi } from '~/api/forms'

const props = defineProps({
  integration: { type: Object, required: true },
  form: { type: Object, required: true },
  integrationData: { type: Object, required: true },
  formIntegrationId: { type: Number, required: false, default: null },
})

const workspaces = ref([])
const projects = ref([])
const states = ref([])
const isWorkspacesLoading = ref(false)
const isProjectsLoading = ref(false)
const isStatesLoading = ref(false)

const credentials = computed(() => ({
  apiKey: integrationData.value?.data?.api_key,
  baseUrl: integrationData.value?.data?.base_url || ''
}))

const hasCredentials = computed(() => !!credentials.value.apiKey)

const stateOptions = computed(() => [
  { name: 'Default state', value: null },
  ...states.value.map(s => ({ name: s.name, value: s.id }))
])

// Matches App\Integrations\Handlers\PlaneIntegration::PRIORITIES
const priorityOptions = [
  { name: 'No priority', value: null },
  { name: 'Urgent', value: 'urgent' },
  { name: 'High', value: 'high' },
  { name: 'Medium', value: 'medium' },
  { name: 'Low', value: 'low' }
]

async function loadWorkspaces () {
  isWorkspacesLoading.value = true
  try {
    workspaces.value = await formsApi.planeWorkspaces(
      credentials.value.apiKey,
      credentials.value.baseUrl
    )
  } catch (error) {
    console.error('Failed to load Plane workspaces:', error)
    workspaces.value = []
  } finally {
    isWorkspacesLoading.value = false
  }
}

async function loadProjects (workspaceSlug) {
  if (!workspaceSlug) {
    projects.value = []
    return
  }
  isProjectsLoading.value = true
  try {
    projects.value = await formsApi.planeProjects(
      credentials.value.apiKey,
      credentials.value.baseUrl,
      workspaceSlug
    )
  } catch (error) {
    console.error('Failed to load Plane projects:', error)
    projects.value = []
  } finally {
    isProjectsLoading.value = false
  }
}

async function loadStates (workspaceSlug, projectId) {
  if (!workspaceSlug || !projectId) {
    states.value = []
    return
  }
  isStatesLoading.value = true
  try {
    states.value = await formsApi.planeStates(
      credentials.value.apiKey,
      credentials.value.baseUrl,
      workspaceSlug,
      projectId
    )
  } catch (error) {
    console.error('Failed to load Plane states:', error)
    states.value = []
  } finally {
    isStatesLoading.value = false
  }
}

function onWorkspaceChange (workspaceSlug) {
  integrationData.value.data.project_id = null
  integrationData.value.data.state_id = null
  projects.value = []
  states.value = []
  if (workspaceSlug) {
    loadProjects(workspaceSlug)
  }
}

function onProjectChange (projectId) {
  integrationData.value.data.state_id = null
  states.value = []
  if (projectId) {
    loadStates(integrationData.value.data.workspace_slug, projectId)
  }
}

// Watch for credential changes to reload workspaces
watch(() => [credentials.value.apiKey, credentials.value.baseUrl], () => {
  integrationData.value.data.workspace_slug = null
  integrationData.value.data.project_id = null
  integrationData.value.data.state_id = null
  workspaces.value = []
  projects.value = []
  states.value = []
  if (credentials.value.apiKey) {
    loadWorkspaces()
  }
})

// Load existing data on mount
onMounted(async () => {
  if (!hasCredentials.value) return

  await loadWorkspaces()
  if (integrationData.value.data.workspace_slug) {
    await loadProjects(integrationData.value.data.workspace_slug)
  }
  if (integrationData.value.data.project_id) {
    await loadStates(integrationData.value.data.workspace_slug, integrationData.value.data.project_id)
  }
})
</script>

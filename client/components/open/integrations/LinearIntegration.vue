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
      label="Linear API Key"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            <a
              href="https://linear.app/settings/security"
              target="_blank"
              class="text-blue-500"
            >
              Create a personal API key here
            </a>
            — needs write access to issues.
          </span>
        </InputHelp>
      </template>
    </text-input>

    <!-- Team Selector -->
    <div
      v-if="hasCredentials"
      class="mt-4"
    >
      <FlatSelectInput
        v-if="teams.length"
        v-model="integrationData.data.team_id"
        name="team"
        :options="teams"
        display-key="name"
        option-key="id"
        emit-key="id"
        :required="true"
        label="Select Team"
        :loading="isTeamsLoading"
        @update:model-value="onTeamChange"
      />
      <div
        v-else-if="!isTeamsLoading"
        class="text-sm text-neutral-500 mb-4"
      >
        No teams found for this API key.
      </div>
    </div>

    <!-- Issue Configuration -->
    <div
      v-if="integrationData.data.team_id"
      class="mt-4"
    >
      <h4 class="font-bold mb-3">
        Issue Options
      </h4>

      <MentionInput
        v-model="integrationData.data.title_template"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="title_template"
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
        v-if="projects.length"
        v-model="integrationData.data.project_id"
        name="project"
        :options="projectOptions"
        display-key="name"
        option-key="value"
        emit-key="value"
        label="Project (optional)"
        :loading="isProjectsLoading"
        class="mb-4"
      />

      <FlatSelectInput
        v-if="states.length"
        v-model="integrationData.data.state_id"
        name="state"
        :options="stateOptions"
        display-key="name"
        option-key="value"
        emit-key="value"
        label="Status (optional)"
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

      <!-- Labels -->
      <div
        v-if="labels.length"
        class="mb-4"
      >
        <label class="text-sm font-medium text-neutral-700">Labels</label>
        <div class="flex flex-wrap gap-2 mt-1">
          <UButton
            v-for="label in labels"
            :key="label.id"
            :label="label.name || 'Label'"
            :color="isLabelSelected(label.id) ? 'primary' : 'neutral'"
            :variant="isLabelSelected(label.id) ? 'solid' : 'outline'"
            size="xs"
            @click="toggleLabel(label.id)"
          />
        </div>
      </div>

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
        v-model="integrationData.data.views_submissions_count"
        name="views_submissions_count"
        class="mt-4"
        label="Form Analytics"
        help="Form views and submissions count"
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

const teams = ref([])
const projects = ref([])
const states = ref([])
const labels = ref([])
const isTeamsLoading = ref(false)
const isProjectsLoading = ref(false)
const isStatesLoading = ref(false)
const isLabelsLoading = ref(false)

const hasCredentials = computed(() => {
  return !!integrationData.value?.data?.api_key
})

const projectOptions = computed(() => [
  { name: 'No project', value: null },
  ...projects.value.map(p => ({ name: p.name, value: p.id }))
])

const stateOptions = computed(() => [
  { name: 'Default status', value: null },
  ...states.value.map(s => ({ name: s.name, value: s.id }))
])

// Matches App\Integrations\Handlers\LinearIntegration::PRIORITIES
const priorityOptions = [
  { name: 'No priority', value: null },
  { name: 'Urgent', value: 1 },
  { name: 'High', value: 2 },
  { name: 'Normal', value: 3 },
  { name: 'Low', value: 4 }
]

async function loadTeams () {
  isTeamsLoading.value = true
  try {
    teams.value = await formsApi.linearTeams(integrationData.value.data.api_key)
  } catch (error) {
    console.error('Failed to load Linear teams:', error)
    teams.value = []
  } finally {
    isTeamsLoading.value = false
  }
}

async function loadTeamConfig (teamId) {
  if (!teamId) {
    projects.value = []
    states.value = []
    labels.value = []
    return
  }
  isProjectsLoading.value = true
  isStatesLoading.value = true
  isLabelsLoading.value = true
  const [projectsRes, statesRes, labelsRes] = await Promise.allSettled([
    formsApi.linearProjects(integrationData.value.data.api_key, teamId),
    formsApi.linearStates(integrationData.value.data.api_key, teamId),
    formsApi.linearLabels(integrationData.value.data.api_key, teamId)
  ])
  projects.value = projectsRes.status === 'fulfilled' ? projectsRes.value : []
  states.value = statesRes.status === 'fulfilled' ? statesRes.value : []
  labels.value = labelsRes.status === 'fulfilled' ? labelsRes.value : []
  isProjectsLoading.value = false
  isStatesLoading.value = false
  isLabelsLoading.value = false
}

function isLabelSelected (labelId) {
  const selected = integrationData.value.data.label_ids || ''
  return selected.split(',').includes(labelId)
}

function toggleLabel (labelId) {
  const current = (integrationData.value.data.label_ids || '').split(',').filter(Boolean)
  const index = current.indexOf(labelId)
  if (index >= 0) {
    current.splice(index, 1)
  } else {
    current.push(labelId)
  }
  integrationData.value.data.label_ids = current.join(',')
}

function onTeamChange (teamId) {
  integrationData.value.data.project_id = null
  integrationData.value.data.state_id = null
  integrationData.value.data.label_ids = ''
  loadTeamConfig(teamId)
}

// Watch for credential changes to reload teams
watch(() => integrationData.value?.data?.api_key, (key) => {
  if (!key) {
    teams.value = []
    projects.value = []
    states.value = []
    labels.value = []
  } else if (!teams.value.length) {
    loadTeams()
  }
})

// Load existing data on mount
onMounted(async () => {
  if (!hasCredentials.value) return

  await loadTeams()
  if (integrationData.value.data.team_id) {
    await loadTeamConfig(integrationData.value.data.team_id)
  }
})
</script>

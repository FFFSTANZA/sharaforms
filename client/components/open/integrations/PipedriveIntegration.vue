<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <!-- API Credentials -->
    <text-input
      :form="integrationData"
      name="data.api_token"
      label="Pipedrive API Token"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            Find your token in
            <a
              href="https://app.pipedrive.com/settings/api"
              target="_blank"
              class="text-blue-500"
            >
              Settings → Personal preferences → API
            </a>.
          </span>
        </InputHelp>
      </template>
    </text-input>

    <!-- Deal Configuration -->
    <div
      v-if="hasCredentials"
      class="mt-4"
    >
      <!-- Pipeline Selector -->
      <FlatSelectInput
        v-if="pipelines.length"
        v-model="integrationData.data.pipeline_id"
        name="pipeline"
        :options="pipelines"
        display-key="name"
        option-key="id"
        emit-key="id"
        label="Select Pipeline (optional)"
        :loading="isPipelinesLoading"
        @update:model-value="onPipelineChange"
      />
      <div
        v-else-if="!isPipelinesLoading"
        class="text-sm text-neutral-500 mb-4"
      >
        No pipelines found. Check that your API token is valid.
      </div>

      <!-- Stage Selector -->
      <div
        v-if="integrationData.data.pipeline_id"
        class="mt-4"
      >
        <FlatSelectInput
          v-if="stages.length"
          v-model="integrationData.data.stage_id"
          name="stage"
          :options="stages"
          display-key="name"
          option-key="id"
          emit-key="id"
          label="Select Stage (optional)"
          :loading="isStagesLoading"
        />
        <div
          v-else-if="!isStagesLoading"
          class="text-sm text-neutral-500 mb-4"
        >
          No stages found for this pipeline.
        </div>
      </div>

      <MentionInput
        v-model="integrationData.data.deal_title_template"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="deal_title_template"
        class="mb-4 mt-4"
        label="Deal Title"
        help="Customize the deal title. Click @ to include form field values."
      />

      <div class="grid grid-cols-2 gap-4">
        <text-input
          :form="integrationData"
          name="data.deal_value"
          label="Deal Value (optional)"
          help="e.g. 500"
        />
        <text-input
          :form="integrationData"
          name="data.currency"
          label="Currency (optional)"
          help="3-letter code, e.g. USD"
        />
      </div>

      <h4 class="font-bold mt-4 mb-2">
        Contact Person
      </h4>
      <p class="text-xs text-neutral-500 mb-3">
        Choose which form fields hold the contact details. A Pipedrive person is created and linked to the deal when either field is set.
      </p>
      <FlatSelectInput
        v-model="integrationData.data.person_name_field_id"
        name="person_name_field"
        :options="fieldOptions"
        display-key="name"
        option-key="value"
        emit-key="value"
        label="Name Field (optional)"
        class="mb-4"
      />
      <FlatSelectInput
        v-model="integrationData.data.person_email_field_id"
        name="person_email_field"
        :options="fieldOptions"
        display-key="name"
        option-key="value"
        emit-key="value"
        label="Email Field (optional)"
        class="mb-4"
      />
      <FlatSelectInput
        v-model="integrationData.data.person_phone_field_id"
        name="person_phone_field"
        :options="fieldOptions"
        display-key="name"
        option-key="value"
        emit-key="value"
        label="Phone Field (optional)"
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

const pipelines = ref([])
const stages = ref([])
const isPipelinesLoading = ref(false)
const isStagesLoading = ref(false)

const hasCredentials = computed(() => {
  return !!integrationData.value?.data?.api_token
})

// Form fields that can be mapped to person attributes (exclude layout fields)
const fieldOptions = computed(() => {
  const fields = (props.form.properties || []).filter(p => !p.type?.startsWith('nf-'))
  return [{ name: 'None', value: null }].concat(fields.map(f => ({ name: f.name, value: f.id })))
})

async function loadPipelines () {
  isPipelinesLoading.value = true
  try {
    pipelines.value = await formsApi.pipedrivePipelines(integrationData.value.data.api_token)
  } catch (error) {
    console.error('Failed to load Pipedrive pipelines:', error)
    pipelines.value = []
  } finally {
    isPipelinesLoading.value = false
  }
}

async function loadStages (pipelineId) {
  if (!pipelineId) {
    stages.value = []
    return
  }
  isStagesLoading.value = true
  try {
    stages.value = await formsApi.pipedriveStages(
      integrationData.value.data.api_token,
      pipelineId
    )
  } catch (error) {
    console.error('Failed to load Pipedrive stages:', error)
    stages.value = []
  } finally {
    isStagesLoading.value = false
  }
}

function onPipelineChange (pipelineId) {
  integrationData.value.data.stage_id = null
  loadStages(pipelineId)
}

// Watch for credential changes to reload pipelines
watch(() => integrationData.value?.data?.api_token, (token) => {
  if (!token) {
    pipelines.value = []
    stages.value = []
  } else if (!pipelines.value.length) {
    loadPipelines()
  }
})

// Load existing data on mount
onMounted(async () => {
  if (!hasCredentials.value) return

  await loadPipelines()
  if (integrationData.value.data.pipeline_id) {
    await loadStages(integrationData.value.data.pipeline_id)
  }
})
</script>

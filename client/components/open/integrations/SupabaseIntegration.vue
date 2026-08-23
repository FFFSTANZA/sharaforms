<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <!-- Credentials -->
    <text-input
      :form="integrationData"
      name="data.project_url"
      label="Project URL"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            Your Supabase project URL (e.g. https://xyzcompany.supabase.co).
          </span>
        </InputHelp>
      </template>
    </text-input>
    <text-input
      :form="integrationData"
      name="data.api_key"
      label="API Key"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            Use the <strong>anon</strong> key (if RLS policies allow inserts) or <strong>service_role</strong> key from your Supabase project settings.
          </span>
        </InputHelp>
      </template>
    </text-input>

    <!-- Table Selector (after credentials) -->
    <div v-if="hasCredentials" class="mt-4">
      <FlatSelectInput
        v-if="tables.length"
        v-model="integrationData.data.table_name"
        name="table"
        :options="tables"
        display-key="name"
        option-key="name"
        emit-key="name"
        :required="true"
        label="Select Table"
        :loading="isTablesLoading"
        @update:model-value="onTableChange"
      />
      <div v-else-if="!isTablesLoading && hasCredentials" class="text-sm text-neutral-500 mb-4">
        No tables found. Make sure your API key has access to at least one table.
      </div>
    </div>

    <!-- Column Mapping (after table) -->
    <div v-if="integrationData.data.table_name && tableColumns.length" class="mt-4">
      <h4 class="text-sm font-medium text-neutral-700 mb-3">Column Mapping</h4>
      <p class="text-xs text-neutral-500 mb-3">
        Map your form fields to Supabase table columns. Unmapped fields will use slugified names.
      </p>
      <div class="space-y-2">
        <div
          v-for="field in formFields"
          :key="field.id"
          class="flex items-center gap-3 text-sm"
        >
          <span class="w-1/3 text-neutral-600 truncate">{{ field.name }}</span>
          <Icon name="lucide:arrow-right" class="text-neutral-400 shrink-0" size="14" />
          <USelect
            v-model="fieldMapping[field.id]"
            :options="columnOptions"
            option-attribute="name"
            class="w-1/3"
            size="xs"
          />
          <USelect
            v-if="fieldMapping[field.id]"
            v-model="fieldTypes[field.id]"
            :options="typeOptions"
            class="w-1/4"
            size="xs"
          />
        </div>
      </div>
    </div>

    <!-- Message Template & Options -->
    <div v-if="integrationData.data.table_name" class="mt-4">
      <MentionInput
        v-model="integrationData.data.message"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="message"
        class="mb-4"
        label="Message (optional)"
        help="Custom message for logs. Use @ to include form field values."
      />

      <h4 class="font-bold mt-4">
        Options
      </h4>
      <notifications-message-actions
        v-model="integrationData.data"
        :form="form"
      />
    </div>
  </IntegrationWrapper>
</template>

<script setup>
import IntegrationWrapper from "./components/IntegrationWrapper.vue"
import NotificationsMessageActions from "./components/NotificationsMessageActions.vue"
import { formsApi } from '~/api/forms'

const props = defineProps({
  integration: { type: Object, required: true },
  form: { type: Object, required: true },
  integrationData: { type: Object, required: true },
  formIntegrationId: { type: Number, required: false, default: null },
})

const tables = ref([])
const tableColumns = ref([])
const isTablesLoading = ref(false)
const isColumnsLoading = ref(false)

const hasCredentials = computed(() => {
  return integrationData.value?.data?.project_url && integrationData.value?.data?.api_key
})

// Form fields that can be mapped (exclude layout fields)
const formFields = computed(() => {
  return (props.form.properties || []).filter(p => !p.type?.startsWith('nf-'))
})

// Column mapping: field_id -> column_name
const fieldMapping = computed({
  get: () => integrationData.value.data.column_mapping || {},
  set: (val) => { integrationData.value.data.column_mapping = val }
})

// Column types: field_id -> column_type
const fieldTypes = computed({
  get: () => integrationData.value.data.column_types || {},
  set: (val) => { integrationData.value.data.column_types = val }
})

const columnOptions = computed(() => {
  return tableColumns.value.map(col => ({
    name: `${col.name} (${col.type})`,
    value: col.name,
  }))
})

const typeOptions = [
  { name: 'Text', value: 'text' },
  { name: 'Integer', value: 'int' },
  { name: 'Float', value: 'float' },
  { name: 'Boolean', value: 'bool' },
  { name: 'JSON', value: 'json' },
  { name: 'Date', value: 'date' },
  { name: 'Timestamp', value: 'timestamptz' },
  { name: 'Array (text[])', value: '_text' },
]

async function loadTables () {
  isTablesLoading.value = true
  try {
    tables.value = await formsApi.supabaseTables(
      integrationData.value.data.api_key,
      integrationData.value.data.project_url
    )
  } catch (error) {
    console.error('Failed to load Supabase tables:', error)
    tables.value = []
  } finally {
    isTablesLoading.value = false
  }
}

async function loadColumns (tableName) {
  isColumnsLoading.value = true
  try {
    tableColumns.value = await formsApi.supabaseColumns(
      integrationData.value.data.api_key,
      integrationData.value.data.project_url,
      tableName
    )
  } catch (error) {
    console.error('Failed to load Supabase columns:', error)
    tableColumns.value = []
  } finally {
    isColumnsLoading.value = false
  }
}

function onTableChange (tableName) {
  if (tableName) {
    loadColumns(tableName)
  } else {
    tableColumns.value = []
  }
}

// Watch for credential changes to reload tables
watch(() => [integrationData.value?.data?.project_url, integrationData.value?.data?.api_key], ([url, key]) => {
  if (url && key) {
    loadTables()
  } else {
    tables.value = []
    tableColumns.value = []
  }
})

// Load existing data on mount
onMounted(async () => {
  if (hasCredentials.value) {
    await loadTables()
    if (integrationData.value.data.table_name) {
      await loadColumns(integrationData.value.data.table_name)
    }
  }
})
</script>

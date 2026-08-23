<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <!-- Credentials -->
    <text-input
      :form="integrationData"
      name="data.api_key"
      label="API Token"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            <a
              href="https://baserow.io/api-token"
              target="_blank"
              class="text-blue-500"
            >
              Create a database token here
            </a>
            — needs write access on the workspace.
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
            Leave empty for baserow.io. For self-hosted instances enter your API URL (e.g. https://api.example.com).
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
        v-model="integrationData.data.workspace_id"
        name="workspace"
        :options="workspaces"
        display-key="name"
        option-key="id"
        emit-key="id"
        label="Select Workspace"
        :loading="isWorkspacesLoading"
        @update:model-value="onWorkspaceChange"
      />
      <div
        v-else-if="!isWorkspacesLoading"
        class="text-sm text-neutral-500 mb-4"
      >
        No workspaces found. Make sure your token has access to at least one workspace.
      </div>
    </div>

    <!-- Database Selector -->
    <div
      v-if="integrationData.data.workspace_id"
      class="mt-4"
    >
      <FlatSelectInput
        v-if="databases.length"
        v-model="integrationData.data.database_id"
        name="database"
        :options="databases"
        display-key="name"
        option-key="id"
        emit-key="id"
        label="Select Database"
        :loading="isDatabasesLoading"
        @update:model-value="onDatabaseChange"
      />
      <div
        v-else-if="!isDatabasesLoading"
        class="text-sm text-neutral-500 mb-4"
      >
        No databases found in this workspace.
      </div>
    </div>

    <!-- Table Selector -->
    <div
      v-if="integrationData.data.database_id"
      class="mt-4"
    >
      <FlatSelectInput
        v-if="tables.length"
        v-model="integrationData.data.table_id"
        name="table"
        :options="tables"
        display-key="name"
        option-key="id"
        emit-key="id"
        :required="true"
        label="Select Table"
        :loading="isTablesLoading"
        @update:model-value="onTableChange"
      />
      <div
        v-else-if="!isTablesLoading"
        class="text-sm text-neutral-500 mb-4"
      >
        No tables found in this database.
      </div>
    </div>

    <!-- Column Mapping (after table) -->
    <div
      v-if="integrationData.data.table_id && tableFields.length"
      class="mt-4"
    >
      <h4 class="text-sm font-medium text-neutral-700 mb-3">
        Column Mapping
      </h4>
      <p class="text-xs text-neutral-500 mb-3">
        Map your form fields to Baserow table columns and pick the column type so values are formatted correctly. Unmapped fields use slugified names.
      </p>
      <div class="space-y-2">
        <div
          v-for="field in formFields"
          :key="field.id"
          class="flex items-center gap-3 text-sm"
        >
          <span class="w-1/3 text-neutral-600 truncate">{{ field.name }}</span>
          <Icon
            name="lucide:arrow-right"
            class="text-neutral-400 shrink-0"
            size="14"
          />
          <USelect
            :model-value="mappingName(field.id)"
            :items="fieldOptions"
            class="w-1/3"
            size="xs"
            @update:model-value="setMappingName(field.id, $event)"
          />
          <USelect
            v-if="mappingName(field.id)"
            :model-value="mappingType(field.id)"
            :items="typeOptions"
            class="w-1/4"
            size="xs"
            @update:model-value="setMappingType(field.id, $event)"
          />
        </div>
      </div>
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
const databases = ref([])
const tables = ref([])
const tableFields = ref([])
const isWorkspacesLoading = ref(false)
const isDatabasesLoading = ref(false)
const isTablesLoading = ref(false)

const credentials = computed(() => ({
  apiKey: integrationData.value?.data?.api_key,
  baseUrl: integrationData.value?.data?.base_url || ''
}))

const hasCredentials = computed(() => !!credentials.value.apiKey)

// Form fields that can be mapped (exclude layout fields)
const formFields = computed(() => {
  return (props.form.properties || []).filter(p => !p.type?.startsWith('nf-'))
})

// Column mapping: field_id -> { column_name, column_type }
const fieldMapping = computed({
  get: () => integrationData.value.data.column_mapping || {},
  set: (val) => { integrationData.value.data.column_mapping = val }
})

function mappingEntry (fieldId) {
  const entry = fieldMapping.value[fieldId]
  if (typeof entry === 'string') return { column_name: entry }
  return entry && typeof entry === 'object' ? entry : {}
}

function mappingName (fieldId) {
  return mappingEntry(fieldId).column_name ?? null
}

function mappingType (fieldId) {
  return mappingEntry(fieldId).column_type ?? 'text'
}

function setMappingName (fieldId, name) {
  const entry = typeof fieldMapping.value[fieldId] === 'object' && fieldMapping.value[fieldId] !== null
    ? { ...fieldMapping.value[fieldId] }
    : {}
  if (name === null) {
    delete fieldMapping.value[fieldId]
  } else {
    entry.column_name = name
    fieldMapping.value[fieldId] = entry
  }
}

function setMappingType (fieldId, type) {
  const entry = typeof fieldMapping.value[fieldId] === 'object' && fieldMapping.value[fieldId] !== null
    ? { ...fieldMapping.value[fieldId] }
    : { column_name: mappingName(fieldId) }
  entry.column_type = type
  fieldMapping.value[fieldId] = entry
}

const fieldOptions = computed(() => {
  const options = [{ label: 'Auto (slugified name)', value: null }]
  return options.concat(tableFields.value.map(f => ({ label: `${f.name} (${f.type})`, value: f.name })))
})

// Matches the castValue() vocabulary in BaserowIntegration.php (same as Supabase)
const typeOptions = [
  { label: 'Text', value: 'text' },
  { label: 'Number', value: 'int' },
  { label: 'Decimal', value: 'float' },
  { label: 'Checkbox / Boolean', value: 'bool' },
  { label: 'Multiple values', value: '_text' },
  { label: 'JSON', value: 'json' },
]

async function loadWorkspaces () {
  isWorkspacesLoading.value = true
  try {
    workspaces.value = await formsApi.baserowWorkspaces(
      credentials.value.apiKey,
      credentials.value.baseUrl
    )
  } catch (error) {
    console.error('Failed to load Baserow workspaces:', error)
    workspaces.value = []
  } finally {
    isWorkspacesLoading.value = false
  }
}

async function loadDatabases (wsId) {
  isDatabasesLoading.value = true
  try {
    databases.value = await formsApi.baserowDatabases(
      credentials.value.apiKey,
      credentials.value.baseUrl,
      wsId
    )
  } catch (error) {
    console.error('Failed to load Baserow databases:', error)
    databases.value = []
  } finally {
    isDatabasesLoading.value = false
  }
}

async function loadTables (dbId) {
  isTablesLoading.value = true
  try {
    tables.value = await formsApi.baserowTables(
      credentials.value.apiKey,
      credentials.value.baseUrl,
      dbId
    )
  } catch (error) {
    console.error('Failed to load Baserow tables:', error)
    tables.value = []
  } finally {
    isTablesLoading.value = false
  }
}

async function loadFields (tableId) {
  if (!tableId) {
    tableFields.value = []
    return
  }
  try {
    tableFields.value = await formsApi.baserowFields(
      credentials.value.apiKey,
      credentials.value.baseUrl,
      tableId
    )
  } catch (error) {
    console.error('Failed to load Baserow fields:', error)
    tableFields.value = []
  }
}

function onWorkspaceChange (wsId) {
  integrationData.value.data.database_id = null
  integrationData.value.data.table_id = null
  databases.value = []
  tables.value = []
  tableFields.value = []
  if (wsId) {
    loadDatabases(wsId)
  }
}

function onDatabaseChange (dbId) {
  integrationData.value.data.table_id = null
  tables.value = []
  tableFields.value = []
  if (dbId) {
    loadTables(dbId)
  }
}

function onTableChange (tableId) {
  if (tableId) {
    loadFields(tableId)
  } else {
    tableFields.value = []
  }
}

// Watch for credential changes to reload workspaces
watch(() => [credentials.value.apiKey, credentials.value.baseUrl], () => {
  integrationData.value.data.workspace_id = null
  integrationData.value.data.database_id = null
  integrationData.value.data.table_id = null
  workspaces.value = []
  databases.value = []
  tables.value = []
  tableFields.value = []
  if (credentials.value.apiKey) {
    loadWorkspaces()
  }
})

// Load existing data on mount
onMounted(async () => {
  if (!hasCredentials.value) return

  await loadWorkspaces()
  if (integrationData.value.data.workspace_id && databases.value.length === 0 && workspaces.value.some(w => String(w.id) === String(integrationData.value.data.workspace_id))) {
    await loadDatabases(integrationData.value.data.workspace_id)
  } else if (!integrationData.value.data.workspace_id && workspaces.value.length === 1) {
    integrationData.value.data.workspace_id = workspaces.value[0].id
    await loadDatabases(workspaces.value[0].id)
  }

  if (integrationData.value.data.database_id && tables.value.length === 0) {
    await loadTables(integrationData.value.data.database_id)
  }

  if (integrationData.value.data.table_id && tableFields.value.length === 0) {
    await loadFields(integrationData.value.data.table_id)
  }
})
</script>

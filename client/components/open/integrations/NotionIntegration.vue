<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <div class="mb-4">
      <p class="text-neutral-500 mb-4">
        Sync form submissions to a Notion database on each submission.
      </p>

      <!-- Notion Account Selector -->
      <FlatSelectInput
        v-if="providers.length"
        v-model="integrationData.oauth_id"
        name="provider"
        :options="providers"
        display-key="name"
        option-key="id"
        emit-key="id"
        :required="true"
        label="Select Notion Workspace"
        @update:model-value="onProviderChange"
      >
        <template #help>
          <InputHelp>
            <span>
              <a
                class="text-blue-500 cursor-pointer"
                @click="openConnectionsModal"
              >
                Click here
              </a>
              to connect another workspace.
            </span>
          </InputHelp>
        </template>
      </FlatSelectInput>

      <UButton
        v-else
        color="neutral"
        variant="outline"
        :loading="isProvidersLoading"
        @click.prevent="connect"
        label="Connect Notion workspace"
      />
    </div>

    <!-- Database Selector -->
    <div v-if="integrationData.oauth_id" class="mb-4">
      <FlatSelectInput
        v-if="databases.length"
        v-model="integrationData.database_id"
        name="database"
        :options="databases"
        display-key="title"
        option-key="id"
        emit-key="id"
        :required="true"
        label="Select Notion Database"
        :loading="isDatabasesLoading"
        @update:model-value="onDatabaseChange"
      />
      <div v-else-if="!isDatabasesLoading && integrationData.oauth_id" class="text-sm text-neutral-500">
        No databases found. Make sure you've shared a database with the SharaForms integration in Notion.
      </div>
    </div>

    <!-- Field Mapping -->
    <div v-if="integrationData.database_id && notionProperties.length" class="mt-4">
      <h4 class="text-sm font-medium text-neutral-700 mb-3">Field Mapping</h4>
      <p class="text-xs text-neutral-500 mb-3">
        Map your form fields to Notion database columns. Unmapped fields will be skipped.
      </p>
      <div class="space-y-2">
        <div
          v-for="column in columns"
          :key="column.id"
          class="flex items-center gap-3 text-sm"
        >
          <span class="w-1/3 text-neutral-600 truncate">{{ column.name }}</span>
          <Icon name="lucide:arrow-right" class="text-neutral-400 shrink-0" size="14" />
          <USelect
            v-model="column.notion_property"
            :options="notionPropertyOptions"
            option-attribute="name"
            class="w-1/2"
            size="xs"
          />
        </div>
      </div>
    </div>
  </IntegrationWrapper>
</template>

<script setup>
import IntegrationWrapper from './components/IntegrationWrapper.vue'
import { formsApi } from '~/api/forms'

const props = defineProps({
  integration: { type: Object, required: true },
  form: { type: Object, required: true },
  integrationData: { type: Object, required: true },
  formIntegrationId: { type: Number, required: false, default: null }
})

const oAuth = useOAuth()
const { data: providersData, isLoading: isProvidersLoading } = oAuth.providers()
const providers = computed(() => (providersData.value || []).filter(provider => provider.provider == 'notion'))
const { openUserSettings } = useAppModals()

const databases = ref([])
const isDatabasesLoading = ref(false)
const notionProperties = ref([])

// Build initial columns from form properties + existing mappings.
// Using ref (not computed) so v-model selections persist across re-renders.
const columns = ref([])

function buildColumns () {
  const existing = props.integrationData.columns || []
  const formFields = (props.form.properties || []).filter(p => !p.type?.startsWith('nf-'))

  columns.value = formFields.map(field => {
    const existingCol = existing.find(c => c.id === field.id)
    return {
      id: field.id,
      name: field.name,
      notion_property: existingCol?.notion_property || field.name,
      notion_type: existingCol?.notion_type || 'rich_text',
    }
  })
}

// Sync column mapping changes back to integrationData for persistence
watch(columns, (newColumns) => {
  props.integrationData.columns = newColumns.map(c => ({
    id: c.id,
    name: c.name,
    notion_property: c.notion_property,
    notion_type: c.notion_type,
  }))
}, { deep: true })

// Notion property options for the select dropdown
const notionPropertyOptions = computed(() => {
  return notionProperties.value.map(prop => ({
    name: `${prop.name} (${prop.type})`,
    value: prop.name,
  }))
})

// Load databases when provider changes
async function onProviderChange (oauthId) {
  if (!oauthId) return
  integrationData.database_id = null
  databases.value = []
  notionProperties.value = []
  await loadDatabases(oauthId)
}

// Load database properties when database changes
async function onDatabaseChange (databaseId) {
  if (!databaseId || !props.integrationData.oauth_id) return
  notionProperties.value = []
  await loadDatabaseProperties(databaseId, props.integrationData.oauth_id)
  buildColumns()
}

async function loadDatabases (oauthId) {
  isDatabasesLoading.value = true
  try {
    const data = await formsApi.notionDatabases(oauthId)
    databases.value = data
  } catch (error) {
    console.error('Failed to load Notion databases:', error)
  } finally {
    isDatabasesLoading.value = false
  }
}

async function loadDatabaseProperties (databaseId, oauthId) {
  try {
    const data = await formsApi.notionDatabaseProperties(databaseId, oauthId)
    notionProperties.value = data
  } catch (error) {
    console.error('Failed to load Notion database properties:', error)
  }
}

function connect () {
  oAuth.connect('notion', true)
}

function openConnectionsModal () {
  openUserSettings('connections')
}

// Load existing data on mount
onMounted(async () => {
  buildColumns()
  if (props.integrationData.oauth_id) {
    await loadDatabases(props.integrationData.oauth_id)
    if (props.integrationData.database_id) {
      await loadDatabaseProperties(props.integrationData.database_id, props.integrationData.oauth_id)
    }
  }
})
</script>

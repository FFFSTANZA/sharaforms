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
      label="Trello API Key"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            <a
              href="https://trello.com/power-ups/admin"
              target="_blank"
              class="text-blue-500"
            >
              Get your API key here
            </a>
            — required for board and list discovery.
          </span>
        </InputHelp>
      </template>
    </text-input>
    <text-input
      :form="integrationData"
      name="data.api_token"
      label="Trello API Token"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            <a
              href="https://trello.com/1/authorize?key=YOUR_KEY&name=SharaForms&scope=read,write&expiration=never&response_type=token"
              target="_blank"
              class="text-blue-500"
            >
              Generate a token here
            </a>
            — paste your API key into the URL above first.
          </span>
        </InputHelp>
      </template>
    </text-input>

    <!-- Board Selector (after credentials) -->
    <div v-if="hasCredentials" class="mt-4">
      <FlatSelectInput
        v-if="boards.length"
        v-model="integrationData.data.board_id"
        name="board"
        :options="boards"
        display-key="name"
        option-key="id"
        emit-key="id"
        label="Select Board"
        :loading="isBoardsLoading"
        @update:model-value="onBoardChange"
      />
      <div v-else-if="!isBoardsLoading && hasCredentials" class="text-sm text-neutral-500 mb-4">
        No boards found. Make sure your API key and token have access to at least one board.
      </div>
    </div>

    <!-- List Selector (after board) -->
    <div v-if="integrationData.data.board_id" class="mt-4">
      <FlatSelectInput
        v-if="lists.length"
        v-model="integrationData.data.list_id"
        name="list"
        :options="lists"
        display-key="name"
        option-key="id"
        emit-key="id"
        :required="true"
        label="Select List"
        :loading="isListsLoading"
      />
      <div v-else-if="!isListsLoading" class="text-sm text-neutral-500 mb-4">
        No lists found on this board.
      </div>
    </div>

    <!-- Card Configuration -->
    <div v-if="integrationData.data.list_id" class="mt-4">
      <h4 class="font-bold mb-3">
        Card Options
      </h4>

      <MentionInput
        v-model="integrationData.data.message"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="message"
        class="mb-4"
        label="Card Title"
        help="Customize the card title. Click @ to include form field values."
      />

      <MentionInput
        v-model="integrationData.data.card_description_template"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="card_description_template"
        class="mb-4"
        label="Card Description (optional)"
        help="Additional description text. Use @ to include field values. Submission data will be appended below."
      />

      <!-- Labels -->
      <div v-if="labels.length" class="mb-4">
        <label class="text-sm font-medium text-neutral-700">Labels</label>
        <div class="flex flex-wrap gap-2 mt-1">
          <UButton
            v-for="label in labels"
            :key="label.id"
            :label="label.name || label.color"
            :color="label.color ? 'primary' : 'neutral'"
            :variant="isLabelSelected(label.id) ? 'solid' : 'outline'"
            size="xs"
            @click="toggleLabel(label.id)"
          />
        </div>
      </div>

      <h4 class="font-bold mt-4">
        Message Options
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

const boards = ref([])
const lists = ref([])
const labels = ref([])
const isBoardsLoading = ref(false)
const isListsLoading = ref(false)

const hasCredentials = computed(() => {
  return integrationData.value?.data?.api_key && integrationData.value?.data?.api_token
})

async function loadBoards () {
  isBoardsLoading.value = true
  try {
    boards.value = await formsApi.trelloBoards(
      integrationData.value.data.api_key,
      integrationData.value.data.api_token
    )
  } catch (error) {
    console.error('Failed to load Trello boards:', error)
    boards.value = []
  } finally {
    isBoardsLoading.value = false
  }
}

async function loadLists (boardId) {
  isListsLoading.value = true
  try {
    lists.value = await formsApi.trelloLists(
      integrationData.value.data.api_key,
      integrationData.value.data.api_token,
      boardId
    )
  } catch (error) {
    console.error('Failed to load Trello lists:', error)
    lists.value = []
  } finally {
    isListsLoading.value = false
  }
}

async function loadLabels (boardId) {
  try {
    labels.value = await formsApi.trelloLabels(
      integrationData.value.data.api_key,
      integrationData.value.data.api_token,
      boardId
    )
  } catch (error) {
    console.error('Failed to load Trello labels:', error)
    labels.value = []
  }
}

function onBoardChange (boardId) {
  integrationData.value.data.list_id = null
  lists.value = []
  labels.value = []
  if (boardId) {
    loadLists(boardId)
    loadLabels(boardId)
  }
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

// Watch for credential changes to reload boards
watch(() => [integrationData.value?.data?.api_key, integrationData.value?.data?.api_token], ([key, token]) => {
  if (key && token) {
    loadBoards()
  } else {
    boards.value = []
    lists.value = []
    labels.value = []
  }
})

// Load existing data on mount
onMounted(async () => {
  if (hasCredentials.value) {
    await loadBoards()
    if (integrationData.value.data.board_id) {
      await Promise.all([
        loadLists(integrationData.value.data.board_id),
        loadLabels(integrationData.value.data.board_id),
      ])
    }
  }
})
</script>

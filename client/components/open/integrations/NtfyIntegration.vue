<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <text-input
      :form="integrationData"
      name="data.ntfy_topic_url"
      label="ntfy topic URL"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            Receive a push notification on each form submission.
            Enter your ntfy topic URL (e.g. https://ntfy.sh/mytopic).
            <a
              href="https://docs.ntfy.sh/put/"
              target="_blank"
            >
              Click here
            </a>
            to learn more about ntfy.
          </span>
        </InputHelp>
      </template>
    </text-input>
    <select-input
      :form="integrationData"
      name="data.priority"
      label="Priority"
      :options="priorityOptions"
    />
    <text-input
      :form="integrationData"
      name="data.tags"
      label="Tags"
      help="help"
    >
      <template #help>
        <InputHelp>
          <span>
            Comma-separated tags for the notification (e.g. "rocket,warning").
            Tags can be used for filtering and as emojis in the notification title.
          </span>
        </InputHelp>
      </template>
    </text-input>
    <text-input
      :form="integrationData"
      name="data.click_url"
      label="Click action URL"
      help="help"
    >
      <template #help>
        <InputHelp>
          <span>
            URL to open when the notification is clicked. Defaults to the form's public page.
          </span>
        </InputHelp>
      </template>
    </text-input>
    <h4 class="font-bold mt-4">
      Message options
    </h4>
    <notifications-message-actions
      v-model="integrationData.data"
      :form="form"
    />
  </IntegrationWrapper>
</template>

<script setup>
import IntegrationWrapper from "./components/IntegrationWrapper.vue"
import NotificationsMessageActions from "./components/NotificationsMessageActions.vue"

const props = defineProps({
  integration: { type: Object, required: true },
  form: { type: Object, required: true },
  integrationData: { type: Object, required: true },
  formIntegrationId: { type: Number, required: false, default: null },
})

const priorityOptions = [
  { name: '1 - Min', value: 1 },
  { name: '2 - Low', value: 2 },
  { name: '3 - Default', value: 3 },
  { name: '4 - High', value: 4 },
  { name: '5 - Max', value: 5 },
]
</script>

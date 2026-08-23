<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <div class="space-y-4">
      <div class="rounded-lg bg-blue-50 p-4 text-sm text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
        <p class="font-medium mb-1">
          How to connect n8n
        </p>
        <ol class="list-decimal list-inside space-y-1 text-blue-600 dark:text-blue-400">
          <li>Create a new workflow in n8n</li>
          <li>Add a <strong>Webhook</strong> node as the trigger</li>
          <li>Copy the <strong>Test</strong> or <strong>Production</strong> webhook URL</li>
          <li>Paste it below and save</li>
        </ol>
      </div>

      <text-input
        :form="integrationData"
        name="data.webhook_url"
        label="n8n Webhook URL"
        help="Paste the webhook URL from your n8n Webhook node"
        placeholder="https://your-n8n-instance.com/webhook/..."
        required
      />
    </div>
  </IntegrationWrapper>
</template>

<script setup>
import IntegrationWrapper from "./components/IntegrationWrapper.vue"

const props = defineProps({
  integration: { type: Object, required: true },
  form: { type: Object, required: true },
  integrationData: { type: Object, required: true },
  formIntegrationId: { type: Number, required: false, default: null },
})
</script>

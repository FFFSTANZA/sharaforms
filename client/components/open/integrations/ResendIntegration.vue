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
      label="Resend API Key"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            <a
              href="https://resend.com/api-keys"
              target="_blank"
              class="text-blue-500"
            >
              Create an API key here
            </a>
            — needs full access to send emails.
          </span>
        </InputHelp>
      </template>
    </text-input>

    <!-- Email Configuration -->
    <div class="mt-4">
      <text-input
        :form="integrationData"
        name="data.from"
        label="From"
        help="help"
        required
      >
        <template #help>
          <InputHelp>
            <span>
              Sender address, e.g. forms@yourdomain.com or SharaForms &lt;forms@yourdomain.com&gt;. The domain must be verified in Resend.
            </span>
          </InputHelp>
        </template>
      </text-input>

      <text-area-input
        :form="integrationData"
        name="data.to"
        label="To"
        required
        help="Recipient email addresses, one per line or comma separated. Max 50 recipients across To, Cc and Bcc."
      />

      <text-area-input
        :form="integrationData"
        name="data.cc"
        label="Cc (optional)"
        help="Carbon copy addresses, one per line or comma separated."
      />

      <text-area-input
        :form="integrationData"
        name="data.bcc"
        label="Bcc (optional)"
        help="Blind carbon copy addresses, one per line or comma separated."
      />

      <MentionInput
        v-model="integrationData.data.subject"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="subject"
        class="mb-4"
        label="Subject"
        :required="true"
        help="Email subject. Click @ to include form field values."
      />

      <MentionInput
        v-model="integrationData.data.body_template"
        :mentions="form.properties"
        :computed-variables="form.computed_variables"
        name="body_template"
        class="mb-4"
        label="Email Content (optional)"
        help="Custom email body. Use @ to include field values. When left empty a summary table of the submission is sent."
      />

      <text-input
        :form="integrationData"
        name="data.reply_to"
        label="Reply-To (optional)"
        help="Comma separated reply-to addresses. Leave empty for replies to go to the From address."
      />

      <toggle-switch-input
        v-model="integrationData.data.include_submission_data"
        name="include_submission_data"
        class="mt-4"
        label="Append submission data"
        help="Adds a summary of all answers below the custom content."
      />
      <toggle-switch-input
        v-if="integrationData.data.include_submission_data"
        v-model="integrationData.data.include_hidden_fields_submission_data"
        name="include_hidden_fields_submission_data"
        class="mt-4"
        label="Include hidden fields"
        help="If enabled then hidden fields will be included in the summary"
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

// Default the auto-summary toggle to on for new integrations
onMounted(() => {
  if (integrationData.value.data.include_submission_data === undefined) {
    integrationData.value.data.include_submission_data = true
  }
})
</script>

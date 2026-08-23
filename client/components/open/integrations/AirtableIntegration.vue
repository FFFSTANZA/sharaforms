<template>
  <IntegrationWrapper
    v-model="props.integrationData"
    :integration="props.integration"
    :form="form"
  >
    <text-input
      :form="integrationData"
      name="data.api_key"
      label="Airtable API Key"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            Your Airtable Personal Access Token.
            <a
              href="https://airtable.com/developers/web/guides/personal-access-tokens"
              target="_blank"
            >
              Click here
            </a>
            to create one. Required scopes: data.records:write, schema.bases:read.
          </span>
        </InputHelp>
      </template>
    </text-input>
    <text-input
      :form="integrationData"
      name="data.base_id"
      label="Base ID"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            Found in the Airtable API docs for your base (starts with "app").
          </span>
        </InputHelp>
      </template>
    </text-input>
    <text-input
      :form="integrationData"
      name="data.table_id"
      label="Table ID or Name"
      help="help"
      required
    >
      <template #help>
        <InputHelp>
          <span>
            The table name or ID from your Airtable base (starts with "tbl").
          </span>
        </InputHelp>
      </template>
    </text-input>
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

<template>
  <div
    v-if="field.type === 'payment'"
    class="px-4"
  >
    <EditorSectionHeader
      icon="i-lucide-credit-card"
      title="Payment"
    />

    <select-input
      name="currency"
      label="Currency"
      :options="currencyList"
      :form="field"
      :required="true"
      :searchable="true"
      :disabled="stripeAccounts.length === 0"
    />
    <MentionInput
      class="mt-4"
      name="amount"
      label="Amount"
      native-type="number"
      :form="field"
      :mentions="form.properties"
      :required="true"
      :disabled="stripeAccounts.length === 0"
    />
    <MentionInput
      class="mt-4"
      name="prefill_name"
      label="Name on Card"
      :form="field"
      :mentions="form.properties"
      :disabled="stripeAccounts.length === 0"
    />
    <MentionInput
      class="mt-4"
      name="prefill_email"
      label="Billing Email"
      :form="field"
      :mentions="form.properties"
      :disabled="stripeAccounts.length === 0"
    />
    
    <div v-if="stripeAccounts.length > 0">
      <select-input
        class="mt-4"
        name="stripe_account_id"
        label="Stripe Account"
        :options="stripeAccounts"
        :form="field"
        :required="true"
      />
      <p
        v-if="oauthConnectEnabled"
        class="mt-4 text-sm text-center text-bold"
      >
        OR
      </p>
    </div>
    <UButton
      v-if="oauthConnectEnabled"
      class="mt-4"
      icon="i-lucide-arrow-right"
      block
      trailing
      :loading="stripeLoading"
      @click.prevent="connectStripe"
    >
      Connect Stripe Account
    </UButton>

    <div
      v-if="ownKeysEnabled"
      class="mt-3 border-t border-neutral-200 pt-3"
    >
      <button
        type="button"
        class="text-sm text-neutral-500 hover:text-neutral-700 cursor-pointer flex items-center gap-1 mx-auto"
        @click.prevent="showKeysForm = !showKeysForm"
      >
        <Icon
          :name="showKeysForm ? 'lucide:chevron-up' : 'lucide:key-round'"
          class="h-3.5 w-3.5"
        />
        {{ showKeysForm ? 'Hide API key form' : 'Use your own Stripe API keys instead' }}
      </button>

      <div
        v-if="showKeysForm"
        class="mt-3 space-y-3"
      >
        <p class="text-xs text-neutral-500 leading-relaxed">
          Payments go directly to your Stripe account using your own API keys.
          Create a <b>restricted key</b> (Developers &gt; API keys) with the
          <b>Charges: Write</b> permission, plus its publishable key.
        </p>
        <TextInput
          v-model="ownKeys.publishable_key"
          name="publishable_key"
          label="Publishable key"
          placeholder="pk_live_..."
          :required="true"
        />
        <TextInput
          v-model="ownKeys.secret_key"
          name="secret_key"
          label="Restricted / secret key"
          placeholder="rk_live_..."
          type="password"
          :required="true"
          autocomplete="off"
        />
        <UButton
          block
          icon="i-lucide-check"
          :loading="savingKeys"
          @click.prevent="saveOwnKeys"
        >
          Save API keys
        </UButton>
      </div>
    </div>

    <p class="text-sm text-neutral-500 mt-3">
      <a
        target="#"
        class="text-neutral-500 cursor-pointer text-sm"
        @click.prevent="crisp.openHelpdeskArticle('how-to-collect-payment-svig30')"
      >
        <Icon
          name="lucide:info"
          class="h-3 w-3 mt-1"
        />
        Learn how to accept payments
      </a>
    </p>
  </div>
</template>

<script setup>
import EditorSectionHeader from '~/components/open/forms/components/form-components/EditorSectionHeader.vue'
import stripeCurrencies from "~/data/stripe_currencies.json"
import { useWindowMessage, WindowMessageTypes } from '~/composables/useWindowMessage'
import { oauthApi } from '~/api'

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  form: {
    type: Object,
    required: true
  }
})

const crisp = useCrisp()
const oAuth = useOAuth()
const { data: providersData, refetch} = oAuth.providers()
const stripeLoading = ref(false)

// Own Stripe API keys form ("bring your own keys")
const ownKeysEnabled = computed(() => useFeatureFlag('billing.stripe_own_keys_enabled', false))
// The OAuth Connect button needs a platform Stripe account; hide it when the
// platform has none so creators are not offered a dead-end flow.
const oauthConnectEnabled = computed(() => !!useFeatureFlag('billing.stripe_publishable_key', ''))
const showKeysForm = ref(false)
const savingKeys = ref(false)
const ownKeys = reactive({ publishable_key: '', secret_key: '' })

const saveOwnKeys = async () => {
  if (!ownKeys.publishable_key || !ownKeys.secret_key) {
    useAlert().error('Please fill in both the publishable key and the secret key.')
    return
  }
  savingKeys.value = true
  try {
    await oauthApi.saveStripeKeys({ ...ownKeys })
    await refetch()
    // Auto-select the newly created API-key connection
    const saved = (providersData.value || []).find((item) => item.provider === 'stripe_own_keys')
    if (saved) {
      props.field.stripe_account_id = saved.id
    }
    useAlert().success('Stripe API keys connected. Payments will go to your Stripe account.')
    ownKeys.publishable_key = ''
    ownKeys.secret_key = ''
    showKeysForm.value = false
  } catch (error) {
    const message = error?.data?.message || error?.response?._data?.message || 'Could not verify your Stripe keys. Please check them and try again.'
    useAlert().error(message)
  } finally {
    savingKeys.value = false
  }
}

// Setup window message listener for Stripe connection
const { listen, cleanup } = useWindowMessage()

onMounted(async () => {
  await oAuth.fetchOAuthProviders()

  if(props.field?.currency === undefined || props.field?.currency === null) {
    props.field.currency = 'USD'
  }
  if(props.field?.amount === undefined || props.field?.amount === null) {
    props.field.amount = 10
  }
  
  // Auto-select first Stripe account if none is selected
  if (!props.field.stripe_account_id && stripeAccounts.value.length > 0) {
    props.field.stripe_account_id = stripeAccounts.value[0].value
  }

  // Listen for Stripe connection message
  listen(async () => {
    await refetch()
    // Auto-select first Stripe account after refresh if one isn't already selected (or maybe always select the newest? for now, first)
    if (stripeAccounts.value.length > 0) {
      props.field.stripe_account_id = stripeAccounts.value[0].value
    }
    useAlert().success('Stripe accounts updated.')
  }, { 
    useMessageChannel: false, 
    acknowledge: false 
  }, WindowMessageTypes.OAUTH_PROVIDER_CONNECTED)
})

onUnmounted(() => {
  // Cleanup listener (optional, as useWindowMessage handles it)
  cleanup()
})

const stripeAccounts = computed(() => (providersData.value || [])
  .filter((item) => item.provider === 'stripe' || item.provider === 'stripe_own_keys')
  .map((item) => ({
    name: item.name + (item.email ? ' (' + item.email + ')' : '') + (item.provider === 'stripe_own_keys' ? ' - API keys' : ''),
    value: item.id
  })))

const currencyList = computed(() => {
  return stripeCurrencies.map((item) => ({
    name: item.name,
    value: item.code
  }))
})

const connectStripe = () => {
  stripeLoading.value = true
  oAuth.connect('stripe', false, true, true)
  setTimeout(() => {
    stripeLoading.value = false
  }, 10000)
}
</script>
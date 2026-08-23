<template>
  <VForm size="sm">
    <div class="px-1 space-y-4">
      <!-- Analytics Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center justify-between mb-5">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-[var(--sf-nav-active-bg)] flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-chart-simple text-[12px] text-[var(--sf-coral-500)]"></i>
            </div>
            <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">
              Analytics
              <PlanTag class="ml-1" upgrade-modal-title="Upgrade to Unlock Analytics" upgrade-modal-description="Track form views and conversions with your preferred analytics platform." />
            </h3>
          </div>
          <UButton
            label="Help"
            icon="i-lucide-circle-question-mark"
            variant="outline"
            color="neutral"
            size="xs"
            @click="crisp.openHelpdeskArticle('how-to-add-analytics-in-my-form-151nkc9')"
          />
        </div>

        <div v-if="form.analytics" class="space-y-4 max-w-xs">
          <FlatSelectInput
            v-model="form.analytics.provider"
            name="provider"
            label="Analytics Provider"
            :options="providerOptions"
            placeholder="Select Provider"
            :clearable="true"
          />
          <TextInput
            v-if="form.analytics.provider"
            v-model="form.analytics.tracking_id"
            name="tracking_id"
            :label="trackingIdConfig.label"
            :placeholder="trackingIdConfig.placeholder"
            :help="trackingIdConfig.help"
          />
        </div>
      </div>
    </div>
  </VForm>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"

const crisp = useCrisp()
const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)

const providerOptions = [
  { name: 'Meta Pixel', value: 'meta_pixel' },
  { name: 'Google Analytics', value: 'google_analytics' },
  { name: 'Google Tag Manager', value: 'gtm' },
]

// Initialize analytics before template render to prevent crash on form.analytics.provider access
function initAnalytics() {
  if (!form.value) return
  if (!form.value.analytics || Array.isArray(form.value.analytics))
    form.value.analytics = {}
  form.value.analytics = {
    ...form.value.analytics,
    provider: form.value.analytics.provider === undefined ? null : form.value.analytics.provider,
    tracking_id: form.value.analytics.tracking_id === undefined ? null : form.value.analytics.tracking_id,
  }
}
initAnalytics()

const providerConfig = {
  meta_pixel: {
    label: 'Pixel ID',
    placeholder: '1234567890123456',
    help: 'Find your Pixel ID in Meta Events Manager'
  },
  google_analytics: {
    label: 'Measurement ID',
    placeholder: 'G-XXXXXXXXXX',
    help: 'Find your Measurement ID in Google Analytics'
  },
  gtm: {
    label: 'Container ID',
    placeholder: 'GTM-XXXXXXX',
    help: 'Find your Container ID in Google Tag Manager'
  }
}

const trackingIdConfig = computed(() => {
  const provider = form.value?.analytics?.provider
  return providerConfig[provider] || { label: 'Tracking ID', placeholder: '', help: '' }
})

// Initialize when form loads (handles async form loading)
watch(() => form.value, (newForm) => {
  if (newForm) initAnalytics()
}, { once: true })

// Clear tracking_id when provider is cleared
watch(() => form.value?.analytics?.provider, (newVal) => {
  if (!newVal && form.value?.analytics) {
    form.value.analytics.tracking_id = null
  }
})
</script>


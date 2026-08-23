<template>
  <UModal
    v-model:open="isModalOpen"
    :ui="{ content: 'sm:max-w-2xl' }"
    title="🎉 Your first submission!"
  >
    <template #body>
      <div class="text-sm text-[var(--sf-text-caption)] mb-6">
        Congratulations! Your form is now live and ready for action. Share it with others or check your submissions below.
      </div>

      <!-- Quick Actions -->
      <div class="space-y-3 mb-6">
        <div class="flex gap-3 items-center">
          <p class="text-sm w-36 text-[var(--sf-text-caption)] flex-shrink-0">
            Share form URL:
          </p>
          <ShareFormUrl
            class="flex-grow"
            :form="form"
          />
        </div>
        <div class="flex items-center">
          <p class="text-sm w-36 text-[var(--sf-text-caption)] flex-shrink-0">
            Check submissions:
          </p>
          <UButton
            color="neutral"
            variant="outline"
            icon="i-lucide-table"
            @click="trackOpenDbClick"
            label="View Submissions"
          />
        </div>
      </div>

      <!-- Integrations Section -->
      <div class="border-t border-[var(--sf-border-divider)] pt-5">
        <div class="flex items-center justify-between mb-3">
          <p class="text-[var(--sf-text-secondary)] font-semibold text-sm">
            🔗 Connect your form to other apps
          </p>
          <NuxtLink
            :to="integrationsPageUrl"
            target="_blank"
            class="text-xs text-[var(--sf-teal)] hover:underline flex items-center gap-1"
            @click="trackIntegrationsLinkClick"
          >
            View all integrations
            <Icon
              name="lucide:external-link"
              size="12px"
            />
          </NuxtLink>
        </div>
        <p class="text-xs text-[var(--sf-text-caption)] mb-4">
          Get notified instantly when someone submits your form, or sync data to your favorite tools.
        </p>

        <!-- Featured Integration: Email -->
        <div
          role="button"
          class="bg-[var(--sf-teal-light)] border border-[var(--sf-border-card)] rounded-lg p-4 mb-4 hover:bg-[var(--sf-teal-light)] transition-colors cursor-pointer group"
          @click="openEmailIntegration"
        >
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-[var(--sf-teal-light)] rounded-lg flex items-center justify-center">
              <Icon
                name="lucide:mail"
                class="text-[var(--sf-teal)]"
                size="20px"
              />
            </div>
            <div class="flex-grow">
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-[var(--sf-text-primary)]">
                  Email Notification
                </p>
                <UBadge
                  variant="subtle"
                  color="success"
                  size="xs"
                >
                  Recommended
                </UBadge>
              </div>
              <p class="text-xs text-[var(--sf-text-body)] mt-1">
                Get an email every time someone submits your form. Perfect for staying on top of responses.
              </p>
            </div>
            <Icon
              name="lucide:chevron-right"
              class="text-[var(--sf-text-muted)] group-hover:text-[var(--sf-teal)] transition-colors flex-shrink-0"
              size="20px"
            />
          </div>
        </div>

        <!-- Other Popular Integrations -->
        <p class="text-xs text-[var(--sf-text-caption)] mb-2">
          Other popular integrations:
        </p>
        <div class="grid grid-cols-4 gap-2">
          <div
            v-for="(integration, i) in popularIntegrations"
            :key="i"
            role="button"
            class="bg-[var(--sf-bg-surface)] border border-[var(--sf-border-card)] rounded-lg p-3 flex flex-col items-center justify-center hover:bg-[var(--sf-bg-muted)] transition-colors cursor-pointer group relative"
            @click="openIntegrationPage(integration)"
          >
            <Icon
              :name="integration.icon"
              class="w-6 h-6 text-[var(--sf-text-caption)] group-hover:text-[var(--sf-text-primary)] transition-colors"
            />
            <p class="text-xs text-[var(--sf-text-body)] mt-1.5 text-center font-medium truncate w-full">
              {{ integration.name }}
            </p>
            <PlanTag
              v-if="integration.required_tier && integration.required_tier !== 'free'"
              :required-tier="integration.required_tier"
              class="absolute top-1 right-1"
            />
          </div>
        </div>
      </div>
    </template>
  </UModal>
</template>

<script setup>
import ShareFormUrl from '~/components/open/forms/components/ShareFormUrl.vue'
import PlanTag from '~/components/app/PlanTag.vue'

const props = defineProps({
  show: { type: Boolean, required: true },
  form: { type: Object, required: true }
})

const emit = defineEmits(['close'])

// Modal state
const isModalOpen = computed({
  get() {
    return props.show
  },
  set(value) {
    if (!value) {
      emit("close")
    }
  }
})

const confetti = useConfetti()
const posthog = usePostHog()

watch(() => props.show, () => {
  if (props.show) {
    confetti.play()
    usePostHog().logEvent('form_first_submission_modal_viewed')
  }
})

// Integrations page URL
const integrationsPageUrl = computed(() => {
  return `/forms/${props.form.slug}/show/integrations`
})

// Popular integrations to display (subset from integrations.json)
const popularIntegrations = computed(() => [
  {
    id: 'slack',
    name: 'Slack',
    icon: 'mdi:slack',
    required_tier: 'pro'
  },
  {
    id: 'google_sheets',
    name: 'Sheets',
    icon: 'mdi:google-spreadsheet',
    required_tier: 'free'
  },
  {
    id: 'zapier',
    name: 'Zapier',
    icon: 'sharaforms:zapier',
    required_tier: 'free'
  },
  {
    id: 'webhook',
    name: 'Webhook',
    icon: 'lucide:webhook',
    required_tier: 'free'
  }
])

const trackOpenDbClick = () => {
  const submissionsUrl = props.form.submissions_url
  window.open(submissionsUrl, '_blank')
  posthog.logEvent('form_first_submission_modal_open_db_click')
}

const trackIntegrationsLinkClick = () => {
  posthog.logEvent('form_first_submission_modal_integrations_link_click')
}

const openEmailIntegration = () => {
  posthog.logEvent('form_first_submission_modal_email_integration_click')
  const url = `${integrationsPageUrl.value}?integration=email`
  window.open(url, '_blank')
}

const openIntegrationPage = (integration) => {
  posthog.logEvent('form_first_submission_modal_integration_click', { integration_id: integration.id })
  const url = `${integrationsPageUrl.value}?integration=${integration.id}`
  window.open(url, '_blank')
}
</script>
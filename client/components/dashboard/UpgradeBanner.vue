<template>
  <UAlert
    v-if="showBanner"
    class="mt-8 p-4"
    icon="i-heroicons:arrow-trending-up"
    color="primary"
    variant="subtle"
    title="Discover our Pro plan"
    description="Remove SharaForms branding, customize forms further, use your custom domain, integrate with your favorite tools, invite users, and more!"
    :actions="[
      {
        label: 'Upgrade now',
        onClick: () => openSubscriptionModal({ plan: 'pro', modal_title: 'Upgrade to Pro plan' })
      },
      {
        label: 'Close',
        color: 'neutral',
        variant: 'outline',
        onClick: dismissBanner
      }
    ]"
  />
</template>

<script setup>
const COOKIE_NAME = 'upgrade_banner_dismissed'
const COOKIE_EXPIRY_DAYS = 7

// Composables
const { openSubscriptionModal } = useAppModals()

// Cookie state
const dismissedCookie = useCookie(COOKIE_NAME, {
  default: () => false,
  maxAge: COOKIE_EXPIRY_DAYS * 24 * 60 * 60,
  sameSite: 'lax',
  secure: import.meta.env.PROD,
  httpOnly: false
})

// Computed
const { current: workspace } = useCurrentWorkspace()
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))

// Get current tier from workspace
const currentTier = computed(() => workspace.value?.plan_tier)

const showBanner = computed(() => {
  return (
    !dismissedCookie.value &&
    workspace.value &&
    currentTier.value === 'free' &&
    !isSelfHosted.value
  )
})

const dismissBanner = () => {
  dismissedCookie.value = true
}
</script> 

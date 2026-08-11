<template>
  <UTooltip
    v-if="shouldDisplayTag"
    :text="tooltipText"
    class="inline normal-case"
  >
    <TrackClick
      name="plan_tag_click"
      :properties="{ tier: displayTier, title: upgradeModalTitle }"
    >
      <div
        :class="tagClasses"
        @click.stop="onClick"
      >
        {{ tagLabel }}
      </div>
    </TrackClick>
  </UTooltip>
</template>

<script setup>
import { computed } from "vue"
import TrackClick from "~/components/global/TrackClick.vue"

const props = defineProps({
  /**
   * Feature key to check (e.g., 'custom_domain', 'integrations.slack')
   * If provided, the tag will show the required tier for this feature.
   */
  feature: {
    type: String,
    default: null
  },
  requiredTier: {
    type: String,
    default: null
  },
  upgradeModalTitle: {
    type: String,
    default: null
  },
  upgradeModalDescription: {
    type: String,
    default: null
  }
})

const { openSubscriptionModal } = useAppModals()
const { data: user } = useAuth().user()
const { current: workspace } = useCurrentWorkspace()
const { getRequiredTier, hasFeature } = usePlanFeatures()
const { currentWorkspaceTier, getTierDisplayName, tierMeetsRequirement } = useBillingUpsell()

// Determine the display tier (either explicit or from feature)
const displayTier = computed(() => {
  if (props.requiredTier) return props.requiredTier
  if (props.feature) return getRequiredTier(props.feature)
  return 'pro' // Default fallback
})

// Check if we should display the tag
const shouldDisplayTag = computed(() => {
  const isSelfHostedRequired = displayTier.value === 'self_hosted'
  if (!isSelfHostedRequired && useFeatureFlag('self_hosted')) return false
  if (!isSelfHostedRequired && !useFeatureFlag('billing.enabled')) return false
  if (!displayTier.value) return false
  if (props.feature && hasFeature(props.feature)) return false
  if (!user.value || !workspace.value) return true

  // Show tag if current tier doesn't meet requirement
  return !tierMeetsRequirement(currentWorkspaceTier.value, displayTier.value)
})

// Tag label based on tier
const tagLabel = computed(() => {
  return getTierDisplayName(displayTier.value).toUpperCase()
})

// Tooltip text
const tooltipText = computed(() => {
  if (props.upgradeModalTitle) return props.upgradeModalTitle

  const tierName = getTierDisplayName(displayTier.value)
  return `You need a ${tierName} plan to use this feature`
})

// Dynamic tag classes based on tier
const tagClasses = computed(() => {
  const base = 'px-2 py-0.5 text-[10px] uppercase tracking-wide inline-flex rounded-full font-semibold ring-1 ring-inset cursor-pointer'

  switch (displayTier.value) {
    case 'self_hosted':
      return `${base} bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30`
    case 'enterprise':
      return `${base} bg-purple-50 text-purple-700 ring-purple-200 dark:bg-purple-500/15 dark:text-purple-300 dark:ring-purple-500/30`
    case 'business':
      return `${base} bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-500/15 dark:text-orange-300 dark:ring-orange-500/30`
    case 'pro':
    default:
      return `${base} bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-300 dark:ring-blue-500/30`
  }
})

function onClick() {
  openSubscriptionModal({
    modal_title: props.upgradeModalTitle,
    modal_description: props.upgradeModalDescription,
    plan: displayTier.value
  })
}
</script>

<template>
  <VTransition>
    <section
      v-if="shouldShowWarning"
      class="flex gap-3 p-4 bg-[var(--sf-amber-light)] rounded-lg border border-[var(--sf-status-draft-border)] border-solid max-md:flex-wrap mb-2"
      aria-labelledby="notification-title"
    >
      <div class="flex justify-center items-center self-start py-px">
        <Icon
          name="i-lucide-lock-open"
          class="w-6 h-6 text-[var(--sf-amber)]"
        />
      </div>
      <div class="flex flex-col flex-1 max-md:max-w-full">
        <div class="flex flex-col text-sm leading-5 text-[var(--sf-text-primary)] max-md:max-w-full">
          <h5
            id="notification-title"
            class="font-medium max-md:max-w-full text-[var(--sf-amber)]"
          >
            Upgrade to unlock all features
          </h5>
          <p class="mt-2 max-md:max-w-full text-[var(--sf-text-caption)]">
            <span v-if="specifyFormOwner">Only you are seeing this notification, as owner of the form.</span> The
            following features are disabled on the published form:
          </p>
          <div
            class="text-[var(--sf-text-caption)] break-words whitespace-break-spaces"
            v-html="cleaningContent"
          />
        </div>
        <div class="flex gap-0 self-start mt-2 text-xs font-medium leading-3 text-center">
          <TrackClick
            name="form_cleanings_unlock_all_features"
            :properties="{}"
          >
            <UButton
              icon="i-lucide-badge-check"
              @click.prevent="onUpgradeClick"
            >
              Unlock all features
            </UButton>
          </TrackClick>
          <UButton
            v-if="specifyFormOwner"
            variant="link"
            @click.prevent="dismissWarning"
          >
            Dismiss
          </UButton>
        </div>
      </div>
    </section>
  </VTransition>
</template>

<script setup>
import VTransition from '~/components/global/transitions/VTransition.vue'
import TrackClick from '~/components/global/TrackClick.vue'
import { escapeHtml } from '~/lib/utils'

const props = defineProps({
  form: { type: Object, required: true },
  specifyFormOwner: { type: Boolean, default: false },
  hideable: { type: Boolean, default: false },
  useCookieDismissal: { type: Boolean, default: false }
})

const { openSubscriptionModal } = useAppModals()

// Create a cookie to store dismissal state for each form
const cookieName = `form_cleanings_dismissed_${props.form.id}`
const dismissedCookie = useCookie(cookieName, {
  default: () => null,
  maxAge: 60 * 60 // 1 hour in seconds
})

// Reactive data
const hideWarning = ref(false)

// Computed properties
const hasCleanings = computed(() => {
  return props.form.cleanings && Object.keys(props.form.cleanings).length > 0
})

const shouldShowWarning = computed(() => {
  // Don't show if manually dismissed
  if (hideWarning.value) return false
  
  // Don't show if no cleanings
  if (!hasCleanings.value) return false
  
  // If cookie dismissal is enabled, check if dismissed within last hour
  if (props.useCookieDismissal && dismissedCookie.value && isWithinLastHour(dismissedCookie.value)) {
    return false
  }
  
  return true
})

const cleanings = computed(() => {
  return props.form.cleanings
})

const cleaningContent = computed(() => {
  let message = ''
  Object.keys(cleanings.value).forEach((key) => {
    let fieldName = key.charAt(0).toUpperCase() + key.slice(1)
    if (fieldName !== 'Form') {
      fieldName = '"' + fieldName + '" field'
    }
    const safeFieldName = escapeHtml(fieldName)
    let fieldInfo = '<br><span class="font-semibold">' + safeFieldName + '</span><br/><ul class=\'list-disc list-inside\'>'
    cleanings.value[key].forEach((msg) => {
      if (!msg) return
      fieldInfo = fieldInfo + '<li>' + escapeHtml(msg) + '</li>'
    })
    if (fieldInfo) {
      message = message + fieldInfo + '</ul>'
    }
  })
  return message
})

// Methods
const onUpgradeClick = () => {
  openSubscriptionModal({
    plan: 'business',
    modal_title: 'Upgrade to unlock all features for your form',
    modal_description: 'Some features are disabled on the published form. Upgrade your plan to unlock these features and much more. Gain full access to all advanced features.',
    show_requirement_hints: false,
  })
}

const dismissWarning = () => {
  // Set the cookie with current timestamp (only if cookie dismissal is enabled)
  if (props.useCookieDismissal) {
    dismissedCookie.value = Date.now()
  }
  hideWarning.value = true
}

const isWithinLastHour = (timestamp) => {
  const now = Date.now()
  const oneHour = 60 * 60 * 1000 // 1 hour in milliseconds
  return (now - timestamp) < oneHour
}


</script>

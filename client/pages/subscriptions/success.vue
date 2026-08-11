<template>
  <div class="flex flex-col min-h-screen">
    <div
      class="w-full md:max-w-3xl md:mx-auto px-4 mb-10 md:pb-20 md:pt-16 text-center flex-grow"
    >
      <h1 class="text-4xl font-semibold">
        Thank you!
      </h1>
      <h4 class="text-xl mt-6">
        {{ statusMessage }}
      </h4>
      <div class="text-center">
        <Loader class="h-6 w-6 text-blue-500 mx-auto mt-20" />
      </div>
      <p v-if="lastErrorMessage && !hasTimedOut" class="mt-6 text-sm text-neutral-500">
        {{ lastErrorMessage }}
      </p>
      <div v-if="hasTimedOut" class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-left">
        <p class="text-sm font-medium text-amber-900">
          This is taking longer than expected.
        </p>
        <p class="mt-2 text-sm leading-6 text-amber-800">
          Your payment may still have gone through, but we have not received the subscription confirmation yet.
          You can retry the status check, return home, or contact support.
        </p>
        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
          <UButton color="primary" @click="retryStatusCheck">
            Retry status check
          </UButton>
          <UButton color="neutral" variant="outline" @click="goHome">
            Go to home
          </UButton>
          <UButton color="neutral" variant="ghost" @click="openSupport">
            Contact support
          </UButton>
        </div>
      </div>
    </div>
    <open-form-footer />
  </div>
</template>


<script setup>
import { authApi } from "~/api"
import sharaformsConfig from "~/sharaforms.config.js"

definePageMeta({
  middleware: 'auth'
})

useOpnSeoMeta({
  title: 'Subscription Success'
})

const confetti = useConfetti()
const auth = useAuth()
const workspaces = useWorkspaces()
const alert = useAlert()
const posthog = usePostHog()
const crisp = useCrisp()
const gtm = useGtm()
const { data: user } = auth.user()

const interval = ref(null)
const hasHandledSuccess = ref(false)
const attempts = ref(0)
const hasTimedOut = ref(false)
const maxAttempts = 12
const lastErrorMessage = ref('')

const statusMessage = computed(() => {
  if (hasTimedOut.value) {
    return 'We are still waiting for your subscription to activate.'
  }

  if (lastErrorMessage.value) {
    return 'We are retrying your subscription confirmation.'
  }

  return 'We\'re checking the status of your subscription. Please wait a moment...'
})

const handleSubscribed = async () => {
  if (hasHandledSuccess.value || !user.value?.is_subscribed) return

  hasHandledSuccess.value = true
  stopPolling()

  try {
    const eventData = {
      plan: user.value?.plan_tier || 'pro'
    }
    posthog.logEvent('subscribed', eventData)
    crisp.pushEvent('subscribed', eventData)
    gtm.trackEvent({ event: 'subscribed', ...eventData })
    if (import.meta.client && window.rewardful) {
      window.rewardful('convert', { email: user.value.email })
    }
  } catch (error) {
    console.error('Failed to register subscription event', error)
  }

  workspaces.invalidateAll()

  alert.success('Your subscription is now active.')
  confetti.play()
  await navigateTo({ name: 'home' })
}

const checkSubscription = () => {
  attempts.value += 1

  // Confirm the server can see the new subscription, then refresh cached user state.
  return authApi.user.get().then(async () => {
    lastErrorMessage.value = ''
    await auth.invalidateUser()
    handleSubscribed()
  }).catch((error) => {
    console.error(error)
    lastErrorMessage.value = error.response?._data?.message || 'Unable to confirm your subscription just yet.'

    if (attempts.value >= maxAttempts) {
      stopPolling()
      hasTimedOut.value = true
    }
  })
}

const stopPolling = () => {
  if (interval.value) {
    clearInterval(interval.value)
    interval.value = null
  }
}

const retryStatusCheck = async () => {
  attempts.value = 0
  hasTimedOut.value = false
  lastErrorMessage.value = ''

  await checkSubscription()

  if (!hasHandledSuccess.value && !interval.value) {
    startPolling()
  }
}

const startPolling = () => {
  interval.value = setInterval(() => {
    if (attempts.value >= maxAttempts) {
      stopPolling()
      hasTimedOut.value = true
      return
    }

    checkSubscription()
  }, 5000)
}

const openSupport = () => {
  window.location.href = `mailto:${sharaformsConfig.links.contact_email}`
}

const goHome = () => navigateTo({ name: 'home' })

onMounted(async () => {
  handleSubscribed()

  if (!hasHandledSuccess.value) {
    await checkSubscription()
  }

  if (!hasHandledSuccess.value && !interval.value && !hasTimedOut.value) {
    startPolling()
  }
})

onBeforeUnmount(() => {
  stopPolling()
})
</script>

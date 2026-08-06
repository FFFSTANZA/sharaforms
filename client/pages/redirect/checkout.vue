<template>
  <div class="flex flex-col items-center justify-center min-h-screen gap-4">
    <Loader class="w-8 h-8 text-blue-500" />
    <p class="text-neutral-500">
      Preparing your checkout...
    </p>
    <div v-if="errorMessage || isTakingLong" class="mt-4 max-w-md rounded-2xl border border-neutral-200 bg-white p-5 text-center shadow-sm">
      <p class="text-sm text-neutral-700">
        {{ errorMessage || 'This is taking longer than expected. You can retry or go back to pricing.' }}
      </p>
      <div class="mt-4 flex flex-col justify-center gap-3 sm:flex-row">
        <UButton color="primary" :loading="isRetrying" @click="retryCheckout">
          Retry checkout
        </UButton>
        <UButton color="neutral" variant="outline" @click="goToPricing">
          Back to pricing
        </UButton>
        <UButton color="neutral" variant="ghost" @click="openSupport">
          Contact support
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const { startCheckout } = useBillingCheckout()
const errorMessage = ref('')
const isRetrying = ref(false)
const isTakingLong = ref(false)
let slowTimer = null

const clearSlowTimer = () => {
  if (slowTimer) {
    clearTimeout(slowTimer)
    slowTimer = null
  }
}

const beginSlowTimer = () => {
  clearSlowTimer()
  slowTimer = setTimeout(() => {
    isTakingLong.value = true
  }, 10000)
}

const launchCheckout = async () => {
  const { plan, yearly, trial_duration, currency } = route.query

  errorMessage.value = ''
  isTakingLong.value = false

  if (!plan) {
    errorMessage.value = 'Missing plan information.'
    useAlert().error(errorMessage.value)
    return
  }

  beginSlowTimer()

  try {
    await startCheckout(plan, {
      yearly: yearly === 'true',
      trialDuration: trial_duration,
      currency: currency || 'usd',
      bypassBeforeUnload: false,
    })
  } catch (error) {
    clearSlowTimer()
    errorMessage.value = error.response?._data?.message || 'Unable to start checkout right now.'
  }
}

const retryCheckout = async () => {
  isRetrying.value = true

  try {
    await launchCheckout()
  } finally {
    isRetrying.value = false
  }
}

const openSupport = () => {
  useCrisp().openAndShowChat('I need help starting the checkout flow for my subscription.')
}

const goToPricing = () => navigateTo({ name: 'pricing' })

onMounted(async () => {
  await launchCheckout()
})

onBeforeUnmount(() => {
  clearSlowTimer()
})
</script> 

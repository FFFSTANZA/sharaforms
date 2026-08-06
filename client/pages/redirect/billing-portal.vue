<template>
  <div class="flex flex-col items-center justify-center min-h-screen gap-4">
    <Loader class="w-8 h-8 text-blue-500" />
    <p class="text-neutral-500">
      Redirecting to billing portal...
    </p>
    <div v-if="errorMessage || isTakingLong" class="mt-4 max-w-md rounded-2xl border border-neutral-200 bg-white p-5 text-center shadow-sm">
      <p class="text-sm text-neutral-700">
        {{ errorMessage || 'This is taking longer than expected. You can retry or return to your settings.' }}
      </p>
      <div class="mt-4 flex flex-col justify-center gap-3 sm:flex-row">
        <UButton color="primary" :loading="isRetrying" @click="retryPortalRedirect">
          Retry portal
        </UButton>
        <UButton color="neutral" variant="outline" @click="goToBillingSettings">
          Back to billing settings
        </UButton>
        <UButton color="neutral" variant="ghost" @click="openSupport">
          Contact support
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { billingApi } from "~/api"

definePageMeta({
  middleware: 'auth'
})

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

const openSupport = () => {
  useCrisp().openAndShowChat('I need help opening the billing portal to manage my subscription.')
}

const goToBillingSettings = () => navigateTo({ name: 'home', query: { 'user-settings': 'billing' } })

const redirectToPortal = async () => {
  errorMessage.value = ''
  isTakingLong.value = false
  beginSlowTimer()

  try {
    const { portal_url } = await billingApi.getBillingPortal()
    if (!portal_url) {
      throw new Error('No portal URL returned')
    }
    window.location.href = portal_url
  } catch (error) {
    clearSlowTimer()
    errorMessage.value = error.response?._data?.message || 'Unable to access the billing portal right now.'
    useAlert().error(errorMessage.value)
  }
}

const retryPortalRedirect = async () => {
  isRetrying.value = true

  try {
    await redirectToPortal()
  } finally {
    isRetrying.value = false
  }
}

onMounted(async () => {
  await redirectToPortal()
})

onBeforeUnmount(() => {
  clearSlowTimer()
})
</script> 

<template>
  <div class="flex min-h-screen items-center justify-center px-6 py-12">
    <div class="w-full max-w-lg rounded-3xl border border-neutral-200 bg-white p-8 text-center shadow-sm">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-500">
        <Icon name="lucide:circle-alert" class="h-7 w-7" />
      </div>
      <h1 class="mt-6 text-2xl font-semibold text-neutral-950">
        Checkout was not completed
      </h1>
      <p class="mt-3 text-sm leading-6 text-neutral-600">
        Your payment was canceled or could not be confirmed. No changes were made to your subscription.
      </p>
      <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
        <UButton
          :to="{ name: 'pricing' }"
          color="primary"
          icon="i-lucide-arrow-left"
        >
          Back to pricing
        </UButton>
        <UButton
          color="neutral"
          variant="outline"
          icon="i-lucide-message-circle"
          @click="openSupport"
        >
          Contact support
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import sharaformsConfig from "~/sharaforms.config.js"

definePageMeta({
  middleware: 'auth'
})

useOpnSeoMeta({
  title: 'Subscription Checkout Not Completed'
})

const openSupport = () => {
  window.location.href = `mailto:${sharaformsConfig.links.contact_email}`
}

onMounted(() => {
  useAlert().error('Checkout was canceled or could not be confirmed.')
})
</script>

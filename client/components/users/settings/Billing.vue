<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h3 class="text-lg font-medium text-neutral-900">Billing Details</h3>
        <p class="mt-1 text-sm text-neutral-500">
          Manage your billing. Download invoices, update your plan, or cancel it at any time.
        </p>
      </div>
    </div>

    <!-- Billing Management -->
    <template v-if="user.has_customer_id">
      <div class="space-y-4">
        <p class="text-sm leading-6 text-neutral-600">
          Use the billing portal to change your plan, update your payment method, download invoices,
          or cancel your subscription.
        </p>
        <div class="flex flex-wrap gap-3">
          <UButton
            icon="i-lucide-credit-card"
            :to="{ name: 'redirect-billing-portal' }"
            target="_blank"
          >
            Open billing portal
          </UButton>
          <UButton
            color="neutral"
            variant="ghost"
            icon="i-lucide-message-circle"
            @click="openSupport"
          >
            Need help?
          </UButton>
        </div>
      </div>
    </template>
    <template v-else>
      <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
        <p class="text-sm font-medium text-neutral-900">
          No billing portal available yet
        </p>
        <p class="mt-2 text-sm leading-6 text-neutral-600">
          Your billing portal is created after your first paid subscription. If you recently upgraded and still do not
          see billing access here, refresh in a moment or contact support.
        </p>
        <div class="mt-4 flex flex-wrap gap-3">
          <UButton
            color="neutral"
            variant="outline"
            icon="i-lucide-refresh-cw"
            @click="refreshBillingStatus"
          >
            Refresh billing status
          </UButton>
          <UButton
            color="primary"
            variant="soft"
            icon="i-lucide-arrow-up-right"
            :to="{ name: 'pricing' }"
          >
            View plans
          </UButton>
          <UButton
            color="neutral"
            variant="ghost"
            icon="i-lucide-message-circle"
            @click="openSupport"
          >
            Contact support
          </UButton>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import sharaformsConfig from "~/sharaforms.config.js"

const auth = useAuth()
const { data: user } = auth.user()

const refreshBillingStatus = () => auth.invalidateUser()

const openSupport = () => {
  window.location.href = `mailto:${sharaformsConfig.links.contact_email}`
}

</script> 

<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>Billing</h2>
      <p>Manage your billing. Download invoices, update your plan, or cancel it at any time.</p>
    </div>

    <!-- Billing Management -->
    <section class="sf-card sf-card-pad">
      <div class="flex items-start gap-3 mb-6">
        <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
          <i class="fa-solid fa-credit-card" />
        </span>
        <div>
          <h3 class="text-[15px] font-semibold text-[#1D1F24]">Billing Details</h3>
          <p class="text-[13px] text-[#6E7278] mt-0.5">
            Manage your plan, payment method, and invoices.
          </p>
        </div>
      </div>

      <template v-if="user.has_customer_id">
        <div class="space-y-5">
          <div class="rounded-xl border border-[#E6E8EE] bg-[#F7F8FA] p-4 flex items-start gap-3">
            <span class="sf-icon-chip-soft sf-icon-chip-soft--green mt-0.5">
              <i class="fa-solid fa-circle-check" />
            </span>
            <p class="text-[13px] leading-6 text-[#6E7278]">
              Use the billing portal to change your plan, update your payment method, download invoices,
              or cancel your subscription.
            </p>
          </div>
          <div class="flex flex-wrap gap-3">
            <UButton
              icon="i-lucide-credit-card"
              class="btn-primary"
              :to="{ name: 'redirect-billing-portal' }"
              target="_blank"
            >
              Open billing portal
            </UButton>
            <UButton
              color="neutral"
              variant="ghost"
              class="btn-ghost"
              icon="i-lucide-message-circle"
              @click="openSupport"
            >
              Need help?
            </UButton>
          </div>
        </div>
      </template>
      <template v-else>
        <div class="rounded-xl border border-[#E6E8EE] bg-[#F7F8FA] p-5">
          <div class="flex items-start gap-3">
            <span class="sf-icon-chip-soft sf-icon-chip-soft--amber mt-0.5">
              <i class="fa-solid fa-hourglass-half" />
            </span>
            <div>
              <p class="text-[14px] font-semibold text-[#1D1F24]">
                No billing portal available yet
              </p>
              <p class="mt-2 text-[13px] leading-6 text-[#6E7278]">
                Your billing portal is created after your first paid subscription. If you recently upgraded and still do not
                see billing access here, refresh in a moment or contact support.
              </p>
            </div>
          </div>
          <div class="mt-4 flex flex-wrap gap-3">
            <UButton
              color="neutral"
              variant="outline"
              class="btn-ghost !border-[#DEE1E7]"
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
              class="btn-ghost"
              icon="i-lucide-message-circle"
              @click="openSupport"
            >
              Contact support
            </UButton>
          </div>
        </div>
      </template>
    </section>
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
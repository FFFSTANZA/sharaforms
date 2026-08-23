<template>
  <div class="px-6 py-5">
    <div class="max-w-5xl mx-auto">
      <FormSummary v-if="canAccessSummary" :form="form" />

      <div v-else class="border border-[var(--sf-border-card)] rounded-2xl shadow-[var(--sf-shadow-card)] p-6 relative overflow-hidden space-y-6">
      <div class="absolute inset-0 z-10">
        <div class="p-5 max-w-md mx-auto flex flex-col items-center justify-center h-full">
          <p class="text-center text-[var(--sf-text-body)]">
            You need a <PlanTag
              upgrade-modal-title="Upgrade today to access form summaries"
              class="mx-1"
            /> subscription to access form summaries.
          </p>
          <button
            class="btn-primary mt-5 mx-auto text-white text-sm font-semibold py-2.5 px-6 rounded-xl"
            @click.prevent="openUpgradeModal()"
          >
            Subscribe
          </button>
        </div>
      </div>
      <img
        src="/img/pages/forms/blurred_summary.png"
        alt="Sample Graph"
        class="mx-auto w-full filter blur-md z-0 pointer-events-none"
      >
    </div>
    </div>
  </div>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"
import FormSummary from "~/components/open/forms/components/FormSummary.vue"

const props = defineProps({
  form: { type: Object, required: true },
})

definePageMeta({
  middleware: "auth",
})

useOpnSeoMeta({
  title: props.form ? "Form Summary - " + props.form.title : "Form Summary",
})

const { openSubscriptionModal } = useAppModals()
const { hasFeature } = usePlanFeatures()
const canAccessSummary = computed(() => hasFeature('form_summary'))

const openUpgradeModal = () => {
  openSubscriptionModal({
    plan: 'pro',
    modal_title: 'Upgrade to Pro for Form Summaries',
    modal_description: 'Get visual breakdowns, statistics, and insights for all your form submissions with the Pro plan.',
  })
}
</script>

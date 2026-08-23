<template>
  <ComparisonPage
    competitor-name="Youform"
    competitor-icon="sharaforms:youform"
    :hero-title="'SharaForms, the more extensible Youform alternative'"
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :summary-line="'SharaForms is the Youform alternative for teams that want modern, no-code forms without submission ceilings, with unlimited forms, unlimited submissions, deeper logic, custom domains, and self-hosting from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build modern forms with <b>unlimited responses, deeper logic, and tighter control over your data</b>, without usage caps or platform lock-in.
      <br/><br/>
      Youform feels modern and fast. SharaForms keeps that ease, then adds richer workflow control, stronger customization, and deployment flexibility for teams that need more than a lightweight builder.
    </template>
  </ComparisonPage>
</template>

<script setup>
import ComparisonPage from "~/components/pages/comparisons/ComparisonPage.vue"
import { useComparisonSeo } from "~/composables/useComparisonSeo"

definePageMeta({
  layout: "default",
})

useComparisonSeo({
  competitorName: 'Youform',
  summary: 'A more extensible Youform alternative with deeper logic, stronger customization, and better control.',
})

const freePlanComparison = [
  {
    label: "Monthly submissions",
    cells: ["Unlimited", "Unlimited"],
  },
  {
    label: "Forms allowed",
    cells: ["Unlimited", "Unlimited"],
  },
  {
    label: "Questions per form",
    cells: ["Unlimited", "Unlimited"],
  },
  {
    label: "Conditional logic",
    cells: ["Included", "Included"],
  },
  {
    label: "File uploads",
    cells: ["Included", "Included up to 10MB"],
  },
  {
    label: "Custom branding",
    cells: ["Pro+", "Pro"],
  },
  {
    label: "Custom CSS / styling",
    cells: ["Business+", "Paid"],
  },
  {
    label: "Native integrations",
    cells: ["Included", "Included"],
  },
  {
    label: "API access",
    cells: ["Included", "Not listed"],
  },
  {
    label: "Webhooks",
    cells: ["Included", "Included"],
  },
  {
    label: "Deployment flexibility",
    cells: ["Y", "N"],
  },
  {
    label: "Self-hosting option",
    cells: ["Y", "N"],
  },
]

const switchReasons = [
  {
    icon: "i-lucide-server",
    title: "Self-hosting option",
    description: "Full control of your data and deployment.",
  },
  {
    icon: "i-lucide-layers",
    title: "Platform control",
    description: "Audit, extend, and adapt the platform.",
  },
  {
    icon: "i-lucide-globe",
    title: "Custom domains available",
    description: "Use custom domains on paid plans.",
  },
  {
    icon: "i-lucide-layers",
    title: "Advanced workflows and logic",
    description: "Build complex flows easily.",
  },
  {
    icon: "i-lucide-square-code",
    title: "Developer-friendly automation",
    description: "API, webhooks, and extensibility.",
  },
  {
    icon: "i-lucide-puzzle",
    title: "Rich integrations ecosystem",
    description: "Connect to tools you already use.",
  },
]

const featureComparison = [
  {
    label: "Unlimited usage (forms + responses)",
    cells: ["Y", "Y"],
  },
  {
    label: "Custom domains for forms",
    cells: ["Pro+", "Pro"],
  },
  {
    label: "Platform control",
    cells: ["Y", "N"],
  },
  {
    label: "Self-hosting option",
    cells: ["Y", "N"],
  },
  {
    label: "Developer extensibility (API + webhooks)",
    cells: ["Included API + webhooks", "Webhooks; API not listed"],
  },
  {
    label: "Native integration ecosystem",
    cells: ["Basic included; advanced paid", "Selected integrations"],
  },
  {
    label: "Advanced logic & branching",
    cells: ["Y", "Moderate"],
  },
  {
    label: "Advanced analytics dashboard",
    cells: ["Pro+", "Basic"],
  },
  {
    label: "Reusable form components",
    cells: ["Y", "N"],
  },
  {
    label: "GDPR / compliance controls",
    cells: ["Y", "Y"],
  },
]

const faqs = [
  {
    question: 'Is SharaForms a good Youform alternative?',
    answer:
      'Yes. Youform is a lightweight builder, but its free plan limits submissions and features. SharaForms offers the same modern, no-code experience with unlimited forms and unlimited submissions on the free plan, deeper logic, custom domains, and self-hosting.',
  },
  {
    question: 'Is Youform free?',
    answer:
      'Youform has a free plan, but it caps monthly submissions and some features require paid tiers. SharaForms is free with unlimited forms and unlimited submissions, so you can grow without hitting a response ceiling.',
  },
  {
    question: 'Can I import my Youform forms into SharaForms?',
    answer:
      'Yes. SharaForms supports importing forms from common builders including Youform, so you can move existing forms without rebuilding them.',
  },
  {
    question: 'How does SharaForms pricing compare to Youform?',
    answer:
      'SharaForms is free with unlimited submissions, and paid plans start at $15 per month for custom branding, custom domains, and advanced workflow features. Youform paid plans add similar limits but typically cost more as volume grows.',
  },
]

</script>

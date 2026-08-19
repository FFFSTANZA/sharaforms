<template>
  <ComparisonPage
    competitor-name="Fillout"
    competitor-icon="sharaforms:fillout"
    hero-import-source="fillout"
    :hero-title="'SharaForms, the more scalable Fillout alternative'"
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :get-competitor-price="getPrice"
    :summary-line="'SharaForms is the Fillout alternative for teams that need to scale past response caps, with unlimited forms, unlimited submissions, built-in calculators, custom domains, and self-hosting from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build modern forms with <b>unlimited responses, developer-grade control, and complete ownership of your data</b>, without monthly ceilings.
      <br/><br/>
      Fillout is elegant and capable, but its free plan still introduces volume limits. SharaForms keeps the experience modern while giving growing teams more control, more flexibility, and far more room to scale.
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
  competitorName: 'Fillout',
  summary: 'A more scalable Fillout alternative with unlimited responses, stronger control, and more room to grow.',
})

const freePlanComparison = [
  {
    label: "Monthly responses",
    cells: ["Unlimited submissions per month", "1,000 / month"],
  },
  {
    label: "Forms allowed",
    cells: ["Unlimited forms", "Unlimited forms"],
  },
  {
    label: "Branding removal",
    cells: ["Pro+", "Starter+"],
  },
  {
    label: "Conditional logic",
    cells: ["Included", "Included"],
  },
  {
    label: "Multi‑page forms",
    cells: ["Included", "Included"],
  },
  {
    label: "File uploads",
    cells: ["Included", "Included"],
  },
  {
    label: "Custom CSS",
    cells: ["Business+", "Pro+"],
  },
  {
    label: "API access",
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
    icon: "i-lucide-infinity",
    title: "No monthly submission caps",
    description: "Freedom to scale without paywalls",
  },
  {
    icon: "i-lucide-paintbrush",
    title: "Flexible form types ",
    description: "Lead capture, registrations, applications, workflows, surveys",
  },
  {
    icon: "i-lucide-terminal",
    title: "Advanced logic & branching",
    description: "Create dynamic, intelligent forms",
  },
  {
    icon: "i-lucide-layers",
    title: "Self-hosting & control",
    description: "Full control over deployment and data",
  },
  {
    icon: "i-lucide-square-code",
    title: "Developer-first workflows",
    description: "API, webhooks, and automation-ready",
  },
  {
    icon: "i-lucide-users",
    title: "Branding and styling controls",
    description: "Remove branding on Pro and use advanced styling on Business.",
  },
]

const featureComparison = [
  {
    label: "Unlimited responses",
    cells: ["Y", "N"],
  },
  {
    label: "Logic jumps & branching",
    cells: ["Y", "Y"],
  },
  {
    label: "File uploads",
    cells: ["Y", "Y"],
  },
  {
    label: "Calculations",
    cells: ["Y", "Y"],
  },
  {
    label: "Custom branding",
    cells: ["Pro+", "Paid"],
  },
  {
    label: "Custom CSS",
    cells: ["Business+", "Pro+"],
  },
  {
    label: "Integrations",
    cells: ["Basic included; advanced paid", "50+ included"],
  },
  {
    label: "Analytics dashboard",
    cells: ["Pro+", "Summary free; analytics paid"],
  },
  {
    label: "Platform control",
    cells: ["Y", "N"],
  },
  {
    label: "Self-hosting",
    cells: ["Y", "N"],
  },
  {
    label: "API access",
    cells: ["Included", "Y"],
  },
  {
    label: "GDPR compliance",
    cells: ["Y", "Y"],
  },
]

function getPrice(submissions) {
  if (submissions <= 1000) return 0
  if (submissions <= 2000) return 15
  if (submissions <= 5000) return 40
  return 100
}

const faqs = [
  {
    question: 'Is SharaForms a good Fillout alternative?',
    answer:
      'Yes. Fillout is a polished builder, but its free plan caps responses and usage quickly. SharaForms is free with unlimited forms and unlimited submissions, adds built-in calculators, custom domains, and self-hosting, and keeps scaling predictable.',
  },
  {
    question: 'Is Fillout free?',
    answer:
      'Fillout has a free plan, but it limits monthly responses and certain features, with prompts to upgrade as volume grows. SharaForms is free with unlimited forms and unlimited submissions, so you can collect responses at scale without hitting a cap.',
  },
  {
    question: 'Can I import my Fillout forms into SharaForms?',
    answer:
      'Yes. SharaForms supports importing forms from common builders including Fillout, so you can move existing forms without rebuilding them.',
  },
  {
    question: 'How does SharaForms pricing compare to Fillout?',
    answer:
      'SharaForms starts free with unlimited submissions; paid plans begin at $12 per month. Fillout paid plans start at a similar point but still enforce response caps, so SharaForms tends to cost less as your volume grows.',
  },
]

</script>

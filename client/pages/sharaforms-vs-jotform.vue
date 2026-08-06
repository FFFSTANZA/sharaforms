<template>
  <ComparisonPage
    competitor-name="JotForm"
    competitor-icon="sharaforms:jotform"
    :hero-title="'SharaForms — the cleaner Jotform alternative'"
    competitor-plan-label="Starter"
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :get-competitor-price="getPrice"
    :summary-line="'SharaForms is the Jotform alternative for teams that want Jotform\'s breadth of features without the submission, storage, and pricing caps — unlimited forms, unlimited submissions, and a cleaner interface from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build polished forms with <b>unlimited responses, stronger control, and fewer tradeoffs</b> — without hitting artificial caps as you grow.
      <br/><br/>
      Jotform covers a lot of ground, but the pricing and limits show up quickly. SharaForms gives teams a cleaner form stack with more breathing room on submissions, better workflow control, and less friction as usage grows.
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
  competitorName: 'JotForm',
  summary: 'A cleaner Jotform alternative with stronger workflow control and fewer usage limits as you grow.',
})

const freePlanComparison = [
  {
    label: "Monthly submissions",
    cells: ["Unlimited submissions", "Limited (100/month)"],
  },
  {
    label: "Forms allowed",
    cells: ["Unlimited forms", "Limited (5 forms)"],
  },
  {
    label: "Form views",
    cells: ["Unlimited", "Limited (10,000/month)"],
  },
  {
    label: "File uploads",
    cells: ["Included", "Included"],
  },
  {
    label: "Conditional logic",
    cells: ["Included", "Included"],
  },
  {
    label: "Custom CSS",
    cells: ["Business+", "Not listed"],
  },
  {
    label: "API access",
    cells: ["Included", "Basic"],
  },
  {
    label: "Webhooks",
    cells: ["Included", "Available"],
  },
  {
    label: "Storage space",
    cells: ["Depends on plan", "Limited"],
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
    title: "Unlimited responses & forms",
    description: "No per‑month caps like Jotform’s free limits.",
  },
  {
    icon: "i-lucide-paintbrush",
    title: "Self‑hosting & flexibility",
    description: "Full control of your deployment and data.",
  },
  {
    icon: "i-lucide-terminal",
    title: "No branding paywalls",
    description: "Unlimited usage on Free, with branding controls on paid plans.",
  },
  {
    icon: "i-lucide-layers",
    title: "Developer workflows",
    description: "Full API + webhook support for automation.",
  },
  {
    icon: "i-lucide-square-code",
    title: "Developer-friendly",
    description: "REST API, webhooks, and automation-ready.",
  },
  {
    icon: "i-lucide-users",
    title: "Scales with you",
    description: "Build forms, workflows, and apps on one platform.",
  },
]

const featureComparison = [
  {
    label: "Platform control",
    cells: ["Y", "N"],
  },
  {
    label: "Self‑hosting option",
    cells: ["Y", "N"],
  },
  {
    label: "Unlimited responses",
    cells: ["Y", "Only with paid plan"],
  },
  {
    label: "Advanced workflow use cases",
    cells: ["Lead forms, apps, workflows", "Form‑centric"],
  },
  {
    label: "Developer extensibility",
    cells: ["Included API + webhooks", "Moderate"],
  },
  {
    label: "Custom domains for forms",
    cells: ["Pro+", "Paid / Enterprise options"],
  },
  {
    label: "GDPR compliance",
    cells: ["Y", "Y"],
  },
]

function getPrice(submissions) {
  if (submissions <= 100) return 0
  if (submissions <= 1000) return 39
  if (submissions <= 2500) return 49
  if (submissions <= 10000) return 129
  return 199
}

const faqs = [
  {
    question: 'Is SharaForms a good JotForm alternative?',
    answer:
      'Yes. SharaForms covers the breadth Jotform is known for — many field types, conditional logic, and payments — with a cleaner interface, unlimited forms and submissions on the free plan, and no forced upgrades as your volume grows.',
  },
  {
    question: 'Is Jotform free?',
    answer:
      'Jotform has a free plan, but it limits monthly submissions, restricts the number of forms and total submission storage, and shows Jotform branding. SharaForms is free with unlimited forms and unlimited submissions, with no caps on what you can collect.',
  },
  {
    question: 'Can I import my Jotform forms into SharaForms?',
    answer:
      'Yes. SharaForms supports importing forms from common builders including Jotform, so you can move existing forms and keep collecting responses without rebuilding them.',
  },
  {
    question: 'How much does SharaForms cost compared to Jotform?',
    answer:
      'SharaForms is free with unlimited forms and submissions, and paid plans start at $12 per month. Jotform paid plans start at a higher price and still enforce submission and storage caps, so SharaForms is typically cheaper at scale.',
  },
]

</script>

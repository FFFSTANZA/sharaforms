<template>
  <ComparisonPage
    competitor-name="Typeform"
    competitor-icon="sharaforms:typeform"
    hero-import-source="typeform"
    :hero-title="'SharaForms — the more flexible Typeform alternative'"
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :get-competitor-price="getPrice"
    :summary-line="'SharaForms is the Typeform alternative for teams that want Typeform\'s polished, conversational forms without the free-plan caps — unlimited forms, unlimited submissions, built-in calculations, and self-hosting from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build polished forms with <b>unlimited responses, sharper control, and room to scale</b> — without forcing every serious use case onto a paid upgrade.
      <br/><br/>
      Typeform made conversational forms feel modern. SharaForms keeps that clean experience, then adds the flexibility, pricing headroom, and deployment control teams need once forms become mission-critical.
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
  competitorName: 'Typeform',
  summary: 'A more flexible Typeform alternative with polished forms, stronger control, and room to scale.',
})

const freePlanComparison = [
  {
    label: "Monthly submissions",
    cells: ["Unlimited", "10 / month cap"],
  },
  {
    label: "Forms allowed",
    cells: ["Unlimited", "Unlimited"],
  },
  {
    label: "Questions per form",
    cells: ["Unlimited", "Many question types"],
  },
  {
    label: "File uploads",
    cells: ["Included", "Paid plan"],
  },
  {
    label: "Conditional logic",
    cells: ["Included", "Included"],
  },
  {
    label: "Custom branding",
    cells: ["Pro+", "Paid plan"],
  },
  {
    label: "Custom CSS / styling",
    cells: ["Business+", "Not supported"],
  },
  {
    label: "Native integrations",
    cells: ["Included", "Some integrations"],
  },
  {
    label: "API access",
    cells: ["Included", "Developer tools included"],
  },
  {
    label: "Webhooks",
    cells: ["Included", "Available"],
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
    description: "No artificial caps like Typeform's free limits.",
  },
  {
    icon: "i-lucide-server",
    title: "Self-hosting & flexibility",
    description: "Full control of data and deployment.",
  },
  {
    icon: "i-lucide-square-code",
    title: "Developer-friendly automation",
    description: "API, webhooks, and integrations ready.",
  },
  {
    icon: "i-lucide-globe",
    title: "Custom domains & branding",
    description: "Branding removal and custom domains are available on paid plans.",
  },
  {
    icon: "i-lucide-layers",
    title: "Reusable blocks & advanced logic",
    description: "Build complex flows easily.",
  },
  {
    icon: "i-lucide-trending-up",
    title: "Better pricing scalability",
    description: "No forced upgrades as you grow.",
  },
]

const featureComparison = [
  {
    label: "Unlimited responses (global)",
    cells: ["Y", "Restricted"],
  },
  {
    label: "Unlimited forms",
    cells: ["Y", "Y"],
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
    label: "Custom domains for forms",
    cells: ["Pro+", "N"],
  },
  {
    label: "Developer extensibility (API + webhooks)",
    cells: ["Included API + webhooks", "APIs + webhooks"],
  },
  {
    label: "Native integrations ecosystem",
    cells: ["Basic included; advanced paid", "Many apps"],
  },
  {
    label: "Advanced logic & branching",
    cells: ["Y", "Included"],
  },
  {
    label: "Reusable form blocks / modular UI",
    cells: ["Y", "N"],
  },
  {
    label: "Analytics dashboard",
    cells: ["Pro+", "Some reporting"],
  },
  {
    label: "Payments & workflows",
    cells: ["Integrations", "Paid (Stripe/others)"],
  },
]

function getPrice(submissions) {
  if (submissions <= 10) return 0
  if (submissions <= 100) return 29
  if (submissions <= 1000) return 59
  if (submissions <= 10000) return 99
  return 199
}

const faqs = [
  {
    question: 'Is SharaForms a good Typeform alternative?',
    answer:
      'Yes. SharaForms keeps the polished, conversational form experience Typeform is known for, then adds unlimited forms and submissions on the free plan, built-in calculations, custom domains and branding, and self-hosting — so you are not forced onto a paid plan as your forms grow.',
  },
  {
    question: 'Is Typeform free?',
    answer:
      'Typeform has a free plan, but it caps submissions at 10 per month and limits what you can build without upgrading. SharaForms is free with unlimited forms and unlimited submissions, so you can collect responses at scale without a monthly cap.',
  },
  {
    question: 'Can I import my Typeform forms into SharaForms?',
    answer:
      'Yes. SharaForms supports importing forms from common builders including Typeform, so you can move your existing forms and continue collecting responses without rebuilding from scratch.',
  },
  {
    question: 'How much does SharaForms cost compared to Typeform?',
    answer:
      'SharaForms is free with unlimited forms and submissions, and paid plans start at $12 per month. Typeform paid plans start higher and still enforce response caps, so SharaForms generally costs less as your volume grows.',
  },
]

</script>

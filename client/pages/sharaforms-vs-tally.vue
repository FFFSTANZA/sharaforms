<template>
  <ComparisonPage
    competitor-name="Tally"
    competitor-icon="sharaforms:tally"
    hero-import-source="tally"
    :hero-title="'SharaForms, the more powerful Tally alternative'"
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :summary-line="'SharaForms is the Tally alternative for teams that want Tally\'s simple, embeddable forms plus deeper workflow control, with unlimited forms, unlimited submissions, three presentation modes, built-in calculations, dynamic PDFs, and self-hosting from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build polished forms with <b>unlimited responses, stronger customization, and complete control over your data.</b>
      <br/><br/>
      Tally keeps forms simple. SharaForms keeps that simplicity, then adds deeper workflow control, richer customization, and infrastructure flexibility once your forms need to do more.
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
  competitorName: 'Tally',
  summary: 'A more powerful Tally alternative with deeper customization and better workflow control.',
})

const freePlanComparison = [
  {
    label: "Monthly responses",
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
    label: "Branding removal",
    cells: ["Pro+", "Pro"],
  },
  {
    label: "Conditional logic",
    cells: ["Included", "Included"],
  },
  {
    label: "File uploads",
    cells: ["Included", "Included"],
  },
  {
    label: "Custom CSS",
    cells: ["Business+", "Pro"],
  },
  {
    label: "Native integrations",
    cells: ["Included", "Included"],
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
    icon: "i-lucide-layers",
    title: "Platform control",
    description: "Adapt the platform to fit your workflow and infrastructure.",
  },
  {
    icon: "i-lucide-server",
    title: "Self-hosting option",
    description: "Full control of your data and deployment environment.",
  },
  {
    icon: "i-lucide-square-code",
    title: "Developer-friendly workflows",
    description: "Full API, webhooks, and automation support.",
  },
  {
    icon: "i-lucide-puzzle",
    title: "Advanced customization",
    description: "Deep integrations and extensible capabilities.",
  },
  {
    icon: "i-lucide-layers",
    title: "Flexible form infrastructure",
    description: "Built for more than basic forms.",
  },
  {
    icon: "i-lucide-globe",
    title: "Custom domains & branding",
    description: "Manage form identity easily.",
  },
]

const featureComparison = [
  {
    label: "Platform extensibility",
    cells: ["Y", "N"],
  },
  {
    label: "Self-hosting option",
    cells: ["Y", "N"],
  },
  {
    label: "Full API access",
    cells: ["Included API + webhooks", "Available"],
  },
  {
    label: "Custom domains for forms",
    cells: ["Pro+", "N"],
  },
  {
    label: "Advanced analytics / dashboard",
    cells: ["Pro+", "Basic"],
  },
  {
    label: "Native integrations ecosystem",
    cells: ["Basic included; advanced paid", "Zapier, Make, webhooks"],
  },
  {
    label: "Developer-first workflows",
    cells: ["Included API + webhooks", "API + webhooks"],
  },
  {
    label: "Flexible form use cases",
    cells: ["Lead, apps, workflows", "Forms and surveys"],
  },
  {
    label: "Universal embedding (sites/apps)",
    cells: ["Y", "Y"],
  },
  {
    label: "GDPR compliance",
    cells: ["Y", "Y"],
  },
]

const faqs = [
  {
    question: 'Is SharaForms a good Tally alternative?',
    answer:
      'Yes. Tally is great for simple, embeddable forms, but SharaForms adds deeper workflow control, richer customization, three presentation modes, built-in calculations, dynamic PDFs, custom domains, and self-hosting, with the same free, no-limits starting point.',
  },
  {
    question: 'Is Tally free?',
    answer:
      'Tally has a generous free plan, but some features and workflows require a paid upgrade. SharaForms is free with unlimited forms and unlimited submissions, and its paid plans unlock advanced features like custom branding, custom domains, and workflow automation.',
  },
  {
    question: 'Can I import my Tally forms into SharaForms?',
    answer:
      'Yes. SharaForms supports importing forms from common builders including Tally, so you can move your existing forms without rebuilding them.',
  },
  {
    question: 'How does SharaForms pricing compare to Tally?',
    answer:
      'Both start free. SharaForms paid plans begin at $15 per month and scale predictably, while Tally paid plans price by features and usage. SharaForms tends to cost less as your form stack grows.',
  },
]

</script>

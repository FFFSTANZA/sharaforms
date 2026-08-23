<template>
  <ComparisonPage
    competitor-name="Google Forms"
    competitor-icon="i-simple-icons-googleforms"
    competitor-icon-class="text-[#7248B9]"
    hero-import-source="google_forms"
    hero-secondary-cta-label="Import your Google Form"
    :hero-title="'SharaForms, the more capable Google Forms alternative'"
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :summary-line="'SharaForms is the Google Forms alternative for teams that need more than basic surveys, with full design customization, conditional logic, built-in calculations, three presentation modes, unlimited submissions, and full control over your data, free from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build polished forms with <b>unlimited responses, deeper customization, and full control over your data</b>, without settling for basic defaults.
      <br/><br/>
      Google Forms is dependable for lightweight collection, especially inside Google Workspace. SharaForms gives teams a more branded, workflow-ready experience when they need stronger design control, richer logic, and better extensibility.
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
  competitorName: 'Google Forms',
  summary: 'A more capable Google Forms alternative with stronger customization, richer workflows, and better control.',
})

const freePlanComparison = [
  {
    label: "Unlimited submissions",
    cells: ["Y", "Y"],
  },
  {
    label: "Unlimited forms",
    cells: ["Y", "Y"],
  },
  {
    label: "Conditional logic",
    cells: ["Included", "Basic logic jumps"],
  },
  {
    label: "Multi-page forms",
    cells: ["Included", "Included"],
  },
  {
    label: "File uploads",
    cells: ["Included", "Requires Google account"],
  },
  {
    label: "Custom branding",
    cells: ["Pro+", "Limited themes"],
  },
  {
    label: "Custom CSS",
    cells: ["Business+", "N"],
  },
  {
    label: "Analytics dashboard",
    cells: ["Pro+", "Basic summaries"],
  },
  {
    label: "Integrations",
    cells: ["Basic included; advanced paid", "Google ecosystem + Apps Script"],
  },
  {
    label: "API access",
    cells: ["Included", "Forms API"],
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
    title: "More advanced workflows",
    description: "Go beyond simple surveys with logic-driven forms.",
  },
  {
    icon: "i-lucide-sliders-horizontal",
    title: "Greater customization",
    description: "Control form design and user experience.",
  },
  {
    icon: "i-lucide-square-code",
    title: "Developer-friendly",
    description: "API, webhooks, and automation support.",
  },
  {
    icon: "i-lucide-server",
    title: "Infrastructure control",
    description: "Self-host and control your infrastructure.",
  },
  {
    icon: "i-lucide-puzzle",
    title: "Flexible integrations",
    description: "Connect beyond a single ecosystem.",
  },
  {
    icon: "i-lucide-trending-up",
    title: "Scales with your business",
    description: "From solo creator to enterprise teams.",
  },
]

const featureComparison = [
  {
    label: "Platform control",
    cells: ["Y", "N"],
  },
  {
    label: "Self-hosting option",
    cells: ["Y", "N"],
  },
  {
    label: "API + webhooks",
    cells: ["Included API + webhooks", "API + Apps Script"],
  },
  {
    label: "Custom domains",
    cells: ["Pro+", "N"],
  },
  {
    label: "Advanced logic & workflows",
    cells: ["Y", "Basic logic only"],
  },
  {
    label: "Full design customization",
    cells: ["Business+", "N"],
  },
  {
    label: "Data ownership (self-hosting control)",
    cells: ["Y", "N"],
  },
  {
    label: "Ecosystem integrations",
    cells: ["Basic included; advanced paid", "Google ecosystem"],
  },
]

const faqs = [
  {
    question: 'Is SharaForms a good Google Forms alternative?',
    answer:
      'Yes. For lightweight surveys Google Forms is fine, but SharaForms adds unlimited forms and submissions, full design customization, conditional logic, built-in calculations, three presentation modes, dynamic PDFs, custom domains, and stronger data control, without the basic look or limits of Google Forms.',
  },
  {
    question: 'Is Google Forms free?',
    answer:
      'Google Forms is free to use, but it offers limited design customization, basic logic, and ties your data to Google Workspace. SharaForms is also free, with unlimited forms and submissions, plus more advanced features and full control over where your data lives.',
  },
  {
    question: 'Can I import my Google Forms into SharaForms?',
    answer:
      'Yes. SharaForms has a one-click Google Forms import, so you can move your existing forms directly without rebuilding them from scratch.',
  },
  {
    question: 'How much does SharaForms cost compared to Google Forms?',
    answer:
      'Google Forms is free but limited, while the tools needed to customize and control it add Google Workspace costs and complexity. SharaForms is free for unlimited forms and submissions, with paid plans from $15 per month for custom branding, custom domains, and advanced workflow features.',
  },
]
</script>

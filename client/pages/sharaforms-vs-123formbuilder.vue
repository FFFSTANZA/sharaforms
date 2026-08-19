<template>
  <ComparisonPage
    competitor-name="123FormBuilder"
    competitor-icon="sharaforms:123formbuilder"
    :hero-title="'SharaForms, the modern 123FormBuilder alternative'"
    competitor-plan-label="Basic"
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :get-competitor-price="getPrice"
    :summary-line="'SharaForms is the modern 123FormBuilder alternative, a cleaner builder with unlimited forms, unlimited submissions, built-in calculators, dynamic PDFs, and custom domains from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build polished forms with <b>unlimited responses, stronger control, and modern workflow flexibility</b>, without squeezing your team into a limited starter plan.
      <br/><br/>
      123FormBuilder handles the basics, but meaningful limits arrive fast. SharaForms gives builders and teams more room to grow, with cleaner design control, deeper workflows, and infrastructure choice when it matters.
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
  competitorName: '123FormBuilder',
  summary: 'A modern 123FormBuilder alternative with stronger control, flexible workflows, and room to scale.',
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
    label: "Fields per form",
    cells: ["Unlimited", "Limited (10 fields)"],
  },
  {
    label: "Multi‑page forms",
    cells: ["Included", "Included"],
  },
  {
    label: "Conditional logic",
    cells: ["Included", "Included"],
  },
  {
    label: "File uploads",
    cells: ["Included", "Paid plan"],
  },
  {
    label: "Custom CSS",
    cells: ["Business+", "Included"],
  },
  {
    label: "API access",
    cells: ["Included", "100 calls/day"],
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
    description: "No submission or form count caps",
  },
  {
    icon: "i-lucide-paintbrush",
    title: "Self‑hosting & flexibility",
    description: "Full control of deployment and data",
  },
  {
    icon: "i-lucide-terminal",
    title: "Developer‑friendly workflows",
    description: "API, webhooks, and automation support",
  },
  {
    icon: "i-lucide-layers",
    title: "Custom domains & branding",
    description: "Use custom domains and branding controls on paid plans",
  },
  {
    icon: "i-lucide-square-code",
    title: "Rich integrations",
    description: "Hundreds of integrations for modern workflows",
  },
  {
    icon: "i-lucide-users",
    title: "Extensibility",
    description: "Build complex experiences and embed anywhere",
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
    cells: ["Y", "N"],
  },
  {
    label: "Custom domains for forms",
    cells: ["Pro+", "Paid only"],
  },
  {
    label: "Advanced logic & branching",
    cells: ["Y", "Y"],
  },
  {
    label: "Calculations",
    cells: ["Y", "Paid"],
  },
  {
    label: "Custom branding",
    cells: ["Pro+", "Paid"],
  },
  {
    label: "Custom CSS",
    cells: ["Business+", "Available"],
  },
  {
    label: "Integrations",
    cells: ["Basic included; advanced paid", "Limited"],
  },
  {
    label: "Analytics dashboard",
    cells: ["Pro+", "1 report on free"],
  },    
  {
    label: "GDPR compliance",
    cells: ["Y", "Y"],
  },
]

function getPrice(submissions) {
  if (submissions <= 100) return 0
  if (submissions <= 5000) return 18.5
  if (submissions <= 20000) return 24.5
  if (submissions <= 100000) return 49.5
  return 25.5
}

const faqs = [
  {
    question: 'Is SharaForms a good 123FormBuilder alternative?',
    answer:
      'Yes. 123FormBuilder has been around a long time, but its interface and limits feel dated. SharaForms gives you a modern builder with unlimited forms and unlimited submissions on the free plan, built-in calculators, dynamic PDFs, custom domains, and a much cleaner editing experience.',
  },
  {
    question: 'Is 123FormBuilder free?',
    answer:
      '123FormBuilder has a free plan, but it restricts the number of forms, monthly submissions, and total storage, and shows branding. SharaForms is free with unlimited forms and unlimited submissions, with no caps on what you can collect.',
  },
  {
    question: 'Can I import my 123FormBuilder forms into SharaForms?',
    answer:
      'Yes. SharaForms supports importing forms from common builders, and 123FormBuilder form content can be moved into native SharaForms forms.',
  },
  {
    question: 'How does SharaForms pricing compare to 123FormBuilder?',
    answer:
      'SharaForms is free with unlimited submissions; paid plans start at $12 per month. 123FormBuilder paid plans start at a similar price but enforce submission and storage caps, so SharaForms tends to cost less as usage grows.',
  },
]

</script>

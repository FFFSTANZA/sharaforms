<template>
  <ComparisonPage
    competitor-name="HeyForm"
    competitor-icon="sharaforms:heyform"
    :hero-title="'SharaForms — the more capable HeyForm alternative'"
    competitor-plan-label="Basic"
    :plan-comparison-title="'Entry Plan Comparison:\nSharaForms Free vs HeyForm Basic'"
    plan-comparison-subtitle="Compare SharaForms's free plan with HeyForm's entry cloud plan."
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :get-competitor-price="getPrice"
    :summary-line="'SharaForms is the HeyForm alternative that is actually free to use hosted — unlimited forms, unlimited submissions, built-in calculations, dynamic PDFs, and custom domains from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build polished forms with <b>unlimited responses, stronger workflow control, and room to scale</b> — without hitting paid ceilings too early.
      <br/><br/>
      HeyForm is a solid conversational builder, but pricing starts where many teams still need room to experiment. SharaForms gives you a fuller platform with more control, richer developer workflows, and no submission ceiling.
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
  competitorName: 'HeyForm',
  summary: 'A more capable HeyForm alternative with stronger workflow control and no submission ceiling.',
})

const freePlanComparison = [
  {
    label: "Unlimited submissions",
    cells: ["Unlimited", "5k/month on Basic"],
  },
  {
    label: "Unlimited forms",
    cells: ["Unlimited", "Unlimited"],
  },
  {
    label: "Unlimited fields & questions",
    cells: ["Unlimited", "Unlimited"],
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
    label: "Multi-page / conversational forms",
    cells: ["Included", "Included"],
  },
  {
    label: "Drag-and-drop builder",
    cells: ["Included", "Included"],
  },
  {
    label: "Embedded forms",
    cells: ["Included", "Included"],
  },
  {
    label: "No code required",
    cells: ["Y", "Y"],
  },
  {
    label: "Custom CSS",
    cells: ["Business+", "Premium+"],
  },
  {
    label: "Deployment flexibility",
    cells: ["Y", "Y"],
  },
  {
    label: "Self-hosting option",
    cells: ["Y", "Y"],
  },
]

const switchReasons = [
  {
    icon: "i-lucide-infinity",
    title: "Unlimited submissions",
    description: "No monthly caps.",
  },
  {
    icon: "i-lucide-wand-sparkles",
    title: "AI-powered form creation",
    description: "Generate forms faster on paid plans.",
  },
  {
    icon: "i-lucide-sliders-horizontal",
    title: "Advanced customization & controls",
    description: "Deep control over logic and exports.",
  },
  {
    icon: "i-lucide-square-code",
    title: "Developer-friendly integrations",
    description: "API, webhooks, automation ready.",
  },
  {
    icon: "i-lucide-server",
    title: "Flexible hosting options",
    description: "Self-host or cloud, full data ownership.",
  },
  {
    icon: "i-lucide-globe",
    title: "Custom domains & branding",
    description: "Manage form identity easily.",
  },
]

const featureComparison = [
  {
    label: "True unlimited responses",
    cells: ["Y", "Business cap / Enterprise custom"],
  },
  {
    label: "Platform control",
    cells: ["Y", "Y"],
  },
  {
    label: "Self-hosting available",
    cells: ["Y", "Yes (complex)"],
  },
  {
    label: "Developer workflows (API + webhooks)",
    cells: ["Included API + webhooks", "Integrations listed"],
  },
  {
    label: "AI-powered form generation",
    cells: ["Pro+", "N"],
  },
  {
    label: "Unique submission identifiers",
    cells: ["Pro+", "N"],
  },
  {
    label: "Hidden fields & advanced logic",
    cells: ["Logic free; secret fields Pro+", "Y"],
  },
]

function getPrice(submissions) {
  if (submissions <= 5000) return 15
  if (submissions <= 20000) return 24
  if (submissions <= 150000) return 40
  return 40
}

const faqs = [
  {
    question: 'Is SharaForms a good HeyForm alternative?',
    answer:
      'Yes. HeyForm is a clean, open-source builder, but its hosted plan starts with a paid entry tier and caps features. SharaForms is free with unlimited forms and unlimited submissions, adds built-in calculations, dynamic PDFs, and custom domains, and scales more predictably.',
  },
  {
    question: 'Is HeyForm free?',
    answer:
      'HeyForm is open source for self-hosting, but its hosted service starts at a paid plan with no free tier for unlimited use. SharaForms has a genuinely free plan with unlimited forms and unlimited submissions.',
  },
  {
    question: 'Can I import my HeyForm forms into SharaForms?',
    answer:
      'Yes. SharaForms supports importing forms from common builders including HeyForm, so you can move existing forms without rebuilding them.',
  },
  {
    question: 'How does SharaForms pricing compare to HeyForm?',
    answer:
      'SharaForms is free with unlimited submissions; paid plans start at $12 per month. HeyForm hosted plans start around $15 per month and enforce submission limits, so SharaForms offers better value at scale.',
  },
]

</script>

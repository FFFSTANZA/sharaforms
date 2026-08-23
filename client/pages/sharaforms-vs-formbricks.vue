<template>
  <ComparisonPage
    competitor-name="Formbricks"
    competitor-icon="i-simple-icons-formbricks"
    competitor-icon-class="text-[#00CABA]"
    :hero-title="'SharaForms, the broader Formbricks alternative'"
    competitor-plan-label="Hobby"
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :get-competitor-price="getPrice"
    :summary-line="'SharaForms is the Formbricks alternative for teams that need more than surveys and in-app feedback, with a general-purpose form builder with unlimited submissions on the free plan, three presentation modes, built-in calculations, and no steep per-seat pricing, from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build polished forms with <b>unlimited responses, broader use cases, and room to scale</b>, without boxing the product into a single category.
      <br/><br/>
      Formbricks is strong for surveys and in-app feedback. SharaForms is built for broader form work: lead capture, registrations, applications, internal operations, embedded flows, and more.
      <br/><br/>
      It gives teams one cleaner form layer across marketing, operations, and product workflows.
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
  competitorName: 'Formbricks',
  summary: 'A broader Formbricks alternative for teams that need more than surveys and in-app feedback.',
})

const freePlanComparison = [
  {
    label: "Monthly responses",
    cells: ["Unlimited", "250/month on Cloud Hobby"],
  },
  {
    label: "Unlimited forms / surveys",
    cells: ["Y", "Y"],
  },
  {
    label: "File uploads",
    cells: ["Included", "Included"],
  },
  {
    label: "Custom CSS / styling",
    cells: ["Business+", "Included self-host"],
  },
  {
    label: "Native integrations",
    cells: ["Included", "Pro cloud / self-host"],
  },
  {
    label: "API access",
    cells: ["Included", "Self-host / paid cloud"],
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
    icon: "i-lucide-layers",
    title: "Flexible form capabilities",
    description: "Build beyond surveys: lead capture, signup flows, workflows, applications.",
  },
  {
    icon: "i-lucide-infinity",
    title: "Unlimited responses and forms",
    description: "No usage ceilings.",
  },
  {
    icon: "i-lucide-square-code",
    title: "Developer-friendly workflows",
    description: "API, webhook support, and automation integration.",
  },
  {
    icon: "i-lucide-globe",
    title: "Custom domains & embeds",
    description: "Use custom domains and branding controls on paid plans.",
  },
  {
    icon: "i-lucide-puzzle",
    title: "Rich integration ecosystem",
    description: "Connect to modern workflows with ease.",
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
    cells: ["Y", "Y"],
  },
  {
    label: "Self-hosting with cloud option",
    cells: ["Y", "Y"],
  },
  {
    label: "Developer extensibility (API + webhooks)",
    cells: ["Included API + webhooks", "Cloud tiers / self-host"],
  },
  {
    label: "Custom domains for forms",
    cells: ["Pro+", "Custom URL self-host"],
  },
  {
    label: "Flexible form uses (lead capture, workflows, apps)",
    cells: ["Y", "Survey-oriented"],
  },
  {
    label: "Native ecosystem integrations",
    cells: ["Basic included; advanced paid", "Pro cloud / self-host"],
  },
  {
    label: "Advanced analytics & dashboards",
    cells: ["Pro+", "Survey metrics only"],
  },
  {
    label: "Branding removal & customization",
    cells: ["Branding Pro+; CSS Business+", "Pro cloud / Enterprise self-host"],
  },
]

function getPrice(submissions) {
  if (submissions <= 250) return 0
  if (submissions <= 2000) return 89
  if (submissions <= 5000) return 390
  return 390
}

const faqs = [
  {
    question: 'Is SharaForms a good Formbricks alternative?',
    answer:
      'Yes. Formbricks focuses on surveys and in-app feedback, while SharaForms is a general-purpose form builder that handles those cases and more, including lead capture, order forms, applications, and workflows, with unlimited submissions on the free plan and no steep per-seat pricing.',
  },
  {
    question: 'Is Formbricks free?',
    answer:
      'Formbricks has a free hobby tier, but it is limited to a small number of monthly responses and seats, and teams outgrow it quickly. SharaForms is free with unlimited forms and unlimited submissions, with paid plans starting at $15 per month.',
  },
  {
    question: 'Can I import my Formbricks surveys into SharaForms?',
    answer:
      'Yes. SharaForms supports importing forms from common builders, and survey content from Formbricks can be moved into native SharaForms forms with full logic and styling.',
  },
  {
    question: 'How does SharaForms pricing compare to Formbricks?',
    answer:
      'SharaForms is free for unlimited forms and submissions, with paid plans from $15 per month. Formbricks paid plans start at a much higher price and charge per member, so SharaForms is substantially cheaper for growing teams.',
  },
]

</script>

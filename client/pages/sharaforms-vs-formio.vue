<template>
  <ComparisonPage
    competitor-name="Form.io"
    competitor-icon="sharaforms:formio"
    :hero-title="'SharaForms, the easier Form.io alternative'"
    competitor-plan-label="Self-hosted"
    :plan-comparison-title="'Hosted vs Self-hosted Setup:\nSharaForms vs Form.io'"
    plan-comparison-subtitle="Compare SharaForms's hosted free plan with Form.io's developer-oriented self-hosted setup."
    :free-plan-comparison="freePlanComparison"
    :switch-reasons="switchReasons"
    :feature-comparison="featureComparison"
    :summary-line="'SharaForms is the Form.io alternative for teams that want a developer-friendly form builder without the self-hosting overhead, fully hosted, free with unlimited forms and submissions, with API and webhooks included from $0.'"
    :faqs="faqs"
  >
    <template #hero-subtitle>
      Build polished forms with <b>unlimited responses, strong workflow control, and no unnecessary setup overhead</b>, all without turning forms into engineering projects.
      <br/><br/>
      Form.io is powerful, but it asks for engineering time and backend setup. SharaForms gives teams a polished builder and developer-grade workflows out of the box, without making every form a custom implementation.
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
  competitorName: 'Form.io',
  summary: 'An easier Form.io alternative with polished workflows and less engineering overhead.',
})

const freePlanComparison = [
  {
    label: "Unlimited submissions",
    cells: ["Unlimited", "Yes (requires self-host deployment)"],
  },
  {
    label: "Unlimited forms",
    cells: ["Unlimited", "Yes (self-host)"],
  },
  {
    label: "Unlimited users / seats",
    cells: ["Unlimited", "Depends on deployment"],
  },
  {
    label: "Conditional logic",
    cells: ["Included", "Included in builder"],
  },
  {
    label: "Multi-page forms",
    cells: ["Included", "Included"],
  },
  {
    label: "File uploads",
    cells: ["Included", "Requires file storage setup"],
  },
  {
    label: "Drag-and-drop builder",
    cells: ["Included", "Developer-oriented"],
  },
  {
    label: "No code required",
    cells: ["Y", "Low-code / developer-oriented"],
  },
  {
    label: "Hosted experience without infra",
    cells: ["Y", "N"],
  },
  {
    label: "API access",
    cells: ["Included", "Included"],
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
    icon: "i-lucide-cloud-upload",
    title: "Hosted forms with no backend required",
    description: "Start collecting data instantly.",
  },
  {
    icon: "i-lucide-infinity",
    title: "Unlimited responses and forms out of the box",
    description: "No usage caps on submissions or forms.",
  },
  {
    icon: "i-lucide-monitor",
    title: "Non-technical UI",
    description: "Easy for marketers, creators, and teams.",
  },
  {
    icon: "i-lucide-square-code",
    title: "Developer-friendly when needed",
    description: "API, webhooks, and automation support.",
  },
  {
    icon: "i-lucide-puzzle",
    title: "Rich integrations without custom code",
    description: "Zapier, Make, Sheets, Slack, and more.",
  },
  {
    icon: "i-lucide-globe",
    title: "Custom domains & branding",
    description: "Manage form identity on paid plans.",
  },
]

const featureComparison = [
  {
    label: "True unlimited responses (no ceilings)",
    cells: ["Y", "Only via custom deployment"],
  },
  {
    label: "Turnkey hosted form builder",
    cells: ["Y", "N"],
  },
  {
    label: "Easy setup for non-devs",
    cells: ["Y", "N"],
  },
  {
    label: "Developer workflows (API + webhooks)",
    cells: ["Included API + webhooks", "Y"],
  },
  {
    label: "Native integrations ecosystem",
    cells: ["Basic included; advanced paid", "Must build integrations"],
  },
  {
    label: "Full control of styling without code",
    cells: ["Business+", "Code-level control"],
  },
  {
    label: "Custom domains out of the box",
    cells: ["Pro+", "Custom deployment only"],
  },
  {
    label: "Intuitive UI for builders",
    cells: ["Y", "Developer-oriented builder"],
  },
]

const faqs = [
  {
    question: 'Is SharaForms a good Form.io alternative?',
    answer:
      'Yes. Form.io is developer-oriented and expects engineering overhead to self-host and run. SharaForms gives teams a hosted, no-code builder with API and webhooks for developers, far less setup, a free plan with unlimited submissions, and no mandatory infrastructure work.',
  },
  {
    question: 'Is Form.io free?',
    answer:
      'Form.io is open source if you self-host, but running it means managing servers, databases, and upgrades yourself. SharaForms is free and fully hosted, with unlimited forms and unlimited submissions, no servers to operate, and paid plans from $15 per month for advanced features.',
  },
  {
    question: 'Can I import my Form.io forms into SharaForms?',
    answer:
      'Yes. SharaForms supports importing forms from common builders, and Form.io JSON form definitions can be moved into native SharaForms forms.',
  },
  {
    question: 'How does SharaForms pricing compare to Form.io?',
    answer:
      'SharaForms is free with unlimited submissions; paid plans start at $15 per month. Form.io requires self-hosting infrastructure or expensive hosted plans, so the total cost of ownership is typically far higher.',
  },
]

</script>

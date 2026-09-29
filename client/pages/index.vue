<template>
  <div class="marketing-page">
    <section class="relative overflow-hidden bg-[#292438] -mt-[76px]">

      <div class="relative pt-[124px] sm:pt-[156px] px-8 lg:px-12 max-w-7xl mx-auto">
        <div class="max-w-3xl mx-auto text-center">
          <NuxtLink
            :to="{ name: 'enterprise' }"
            class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3 py-1 text-sm text-white/65 transition-colors hover:bg-white/10 hover:text-white/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-400"
          >
            <span
              class="rounded-md bg-white/12 px-1.5 py-0.5 text-[11px] font-semibold leading-3 tracking-wide text-white"
            >
              NEW
            </span>
            Built for Teams & Enterprises
          </NuxtLink>
          <h1
            class="text-white text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold mt-5 mb-4"
          >
            See every question.
            <span class="brand-gradient-text">Focus on one.</span>
            <span
              class="mt-4 block text-base sm:text-xl leading-7 font-normal tracking-normal text-white/80"
            >
              <NuxtLink
                to="/spotlight-forms"
                class="text-white/80 hover:text-white"
              >Spotlight mode.</NuxtLink>
              Focused flows. Classic pages.
            </span>
          </h1>

          <p
            class="text-white/55 text-lg sm:text-xl leading-7 sm:leading-8 font-normal max-w-2xl mx-auto"
          >
            Built-in calculations, conditional logic, signatures, file uploads,
            and PDF generation. Unlimited forms, unlimited responses.
          </p>

          <div class="relative mt-9">
            <div
              class="flex flex-col sm:flex-row items-center justify-center gap-3"
            >
              <UButton
                size="lg"
                :to="{
                  name: authenticated ? 'forms-create' : 'forms-create-guest',
                }"
                trailing-icon="i-heroicons:arrow-up-right"
                label="Create a free form"
                class="premium-primary-button px-5 py-3 rounded-xl text-[15px] leading-7 tracking-[-1.1%] font-semibold text-white"
              />
            </div>

          </div>
        </div>
      </div>
      <div class="relative mx-auto max-w-7xl px-8 pb-28 pt-8 sm:pb-40 sm:pt-12 lg:px-12">
        <LiveDemo />
      </div>
      <svg
        class="pointer-events-none absolute -bottom-px left-0 h-[60px] w-full sm:h-[90px]"
        viewBox="0 0 1440 120"
        preserveAspectRatio="none"
        aria-hidden="true"
      >
        <path
          d="M0 52C150 108 260 10 430 54C610 102 720 120 880 62C1050 0 1170 96 1440 42V120H0Z"
          fill="#fcfcfd"
        />
      </svg>
    </section>

    <TrustedTeams />

    <Features />

    <MoreFeatures />

    <!-- <AiFeature class="pb-8" /> -->

    <!-- START TEMPLATES -->
    <section class="bg-white py-14 sm:py-20 border-t border-neutral-200">
      <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div class="max-w-2xl">
            <h2 class="text-xl font-bold tracking-tight text-neutral-900 sm:text-2xl">
              Start from a free template
            </h2>
            <p class="mt-3 text-sm leading-6 text-neutral-600 sm:text-base sm:leading-7">
              Every template ships with unlimited submissions, built-in
              calculations, and conditional logic ready to switch on.
            </p>
          </div>
          <NuxtLink
            :to="{ name: 'templates' }"
            class="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-pink-700 hover:text-pink-800"
          >
            Browse all {{ templateCount }} templates
            <UIcon name="i-lucide-arrow-right" class="h-4 w-4" />
          </NuxtLink>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <NuxtLink
            v-for="item in featuredTemplates"
            :key="item.slug"
            :to="{ name: 'templates-slug', params: { slug: item.slug } }"
            class="group flex h-full flex-col rounded-xl border border-neutral-200 p-5 transition-colors hover:border-pink-300"
          >
            <h3 class="text-base font-semibold leading-snug text-neutral-900 group-hover:text-pink-700 transition-colors">
              {{ item.name }}
            </h3>
            <p class="mt-1.5 text-sm leading-6 text-neutral-600">
              {{ item.blurb }}
            </p>
            <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-pink-700">
              Use this template
              <UIcon name="i-lucide-arrow-right" class="h-3.5 w-3.5" />
            </span>
          </NuxtLink>
        </div>

        <div class="mt-10">
          <h3 class="text-xs font-semibold uppercase tracking-widest text-neutral-400">
            Browse by category
          </h3>
          <div class="mt-4 flex flex-wrap gap-3">
            <NuxtLink
              v-for="hub in categoryHubs"
              :key="hub.slug"
              :to="hub.to"
              class="inline-flex items-center rounded-full bg-neutral-50 px-4 py-2 text-sm font-medium text-neutral-700 ring-1 ring-inset ring-neutral-200 transition-all hover:ring-pink-300 hover:text-pink-700"
            >
              {{ hub.name }}
            </NuxtLink>
          </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-x-8 gap-y-3 text-sm font-medium text-neutral-600">
          <NuxtLink to="/guides" class="hover:text-pink-700">
            Step-by-step form guides
          </NuxtLink>
          <NuxtLink to="/integrations" class="hover:text-pink-700">
            Connect your tools
          </NuxtLink>
        </div>
      </div>
    </section>
    <!-- END TEMPLATES -->

    <ScrollReveal>
      <FaqSection
        class="sf-faq"
        :faqs="homepageFaqs"
        :title-lines="['Everything you need', 'to know']"
        description="Straight answers about pricing, features, and data security."
        :default-open-index="0"
        id-prefix="homepage-faq-answer"
        @contact="contactUs"
      />
    </ScrollReveal>

    <div class="pt-12 sm:pt-16"><OpenFormFooter /></div>
  </div>
</template>

<script setup>
import LiveDemo from "~/components/pages/welcome/LiveDemo.vue"
import Features from "~/components/pages/welcome/Features.vue"
import MoreFeatures from "../components/pages/welcome/MoreFeatures.vue"
import FaqSection from "~/components/pages/FaqSection.vue"
import sharaformsConfig from "~/sharaforms.config.js"
import { useIsAuthenticated } from "~/composables/useAuthFlow"
import { useTemplateMeta } from "~/composables/data/useTemplateMeta"
import { TEMPLATE_SLUGS } from "~/data/forms/templates/template-slugs"

definePageMeta({
  layout: "default",
  middleware: ["root-redirect"],
})

const { isAuthenticated: authenticated } = useIsAuthenticated()
const { logEvent } = usePostHog()
const { types: typesMap, industries: industriesMap } = useTemplateMeta()
const templateCount = TEMPLATE_SLUGS.length

// Curated template cards (slugs validated against TEMPLATE_SLUGS) so the
// homepage passes internal link equity to the template detail pages.
const featuredTemplates = [
  {
    slug: "online-order-form-template",
    name: "Online order form",
    blurb: "Take orders with line items and totals calculated live.",
  },
  {
    slug: "employee-onboarding-form-template",
    name: "Employee onboarding form",
    blurb: "Collect new-hire details, documents, and policies in one flow.",
  },
  {
    slug: "student-registration-form-template",
    name: "Student registration form",
    blurb: "Enroll students with guardian contacts and course choices.",
  },
  {
    slug: "catering-order-form-template",
    name: "Catering order form",
    blurb: "Quote events with per-head pricing and headcount totals.",
  },
  {
    slug: "inspection-form-template",
    name: "Inspection form",
    blurb: "Log pass or fail results with conditional follow-up checks.",
  },
  {
    slug: "address-change-form-template",
    name: "Address change form",
    blurb: "Update customer or employee records with a verified address.",
  },
  {
    slug: "newsletter-signup-form-template",
    name: "Newsletter signup form",
    blurb: "Grow your list with a short, conversion-ready signup form.",
  },
  {
    slug: "real-estate-inquiry-form-template",
    name: "Real estate inquiry form",
    blurb: "Qualify buyers and sellers before you schedule a callback.",
  },
]

// Category hub links (slugs validated against types.json / industries.json).
const categoryHubs = [
  "abstract_forms",
  "appointment_forms",
  "contact_forms",
  "content_forms",
  "recommendation_forms",
  "voting_forms",
  "wedding_forms",
]
  .map((slug) => ({
    slug,
    name: typesMap.get(slug).name,
    to: { name: "templates-types-slug", params: { slug } },
  }))
  .concat(
    ["entertainment_forms", "marketing_forms"].map((slug) => ({
      slug,
      name: industriesMap.get(slug).name,
      to: { name: "templates-industries-slug", params: { slug } },
    })),
  )

onMounted(() => {
  logEvent('homepage_viewed', {
    authenticated,
  })
})

useOpnSeoMeta({
  // Keyword-first; the global titleTemplate appends the brand suffix.
  title: "Free Form Builder with Calculations & Conditional Logic",
  description:
    "Spotlight forms show every question while one stays in focus. Classic pages and focused flows too, plus calculations, conditional logic, signatures, PDFs.",
  ogImage: "/share-preview.jpg",
  keywords: "free form builder, spotlight forms, one question at a time form, multi-page forms, form builder with calculations, conditional logic forms, signature forms, payment forms, unlimited forms, unlimited submissions",
  speakable: ["h1", ".marketing-page > section:first-child p", ".faq-answer p"],
  breadcrumbs: [
    { name: "Home" },
  ],
})

const homepageFaqs = [
  {
    question: "Is SharaForms free?",
    answer:
      "Yes. The Free plan is free forever and includes unlimited forms, unlimited submissions, all three presentation modes, and core features like conditional logic and calculations. You never need a credit card to start. Paid plans exist for teams that want extras such as custom branding removal, premium integrations, and priority support.",
  },
  {
    question: "Are there limits on forms or submissions?",
    answer:
      "No. Every plan, including the free plan, comes with unlimited forms and unlimited submissions. There are no per-response charges, monthly caps, or surprise overages, so you can collect as many responses as your audience generates. If your volume grows from ten responses a month to fifty thousand, nothing changes on your bill.",
  },
  {
    question: "What are the three presentation modes?",
    answer:
      "Every form can be presented three ways: Classic shows your form across multiple pages, Focused shows one question at a time, and Spotlight keeps all questions visible while the active question takes focus. You can switch modes anytime without rebuilding your form.",
  },
  {
    question: "Does SharaForms support conditional logic and formulas?",
    answer:
      "Yes. Conditional logic shows or hides fields based on earlier answers, and formula fields calculate totals, prices, scores, and discounts automatically at fill time. Both work in every template and every presentation mode, require no coding, and support hidden fields and answer piping, so you can build quote calculators, scored quizzes, and dynamic multi-step flows without plugins.",
  },
  {
    question: "Where is my data stored?",
    answer:
      "On the managed cloud, data is stored in India and protected with SSL encryption in transit and at rest. Submissions stay private to your account, and role-based access controls decide who on your team can view them. Teams that need full data control can self-host SharaForms on their own infrastructure instead of the managed cloud.",
  },
  {
    question: "Can I connect SharaForms to my other tools?",
    answer:
      "Yes. SharaForms connects to popular tools through native integrations, Zapier, n8n, Make, webhooks, and a full REST API. Route new submissions to Slack or Discord, append rows to Google Sheets, trigger automations, or push data into any custom system. Integrations run automatically after each submission, so your workflow keeps moving without manual exports or copy-paste steps.",
  },
]

const homepageFaqSchema = {
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "@id": `${useRuntimeConfig().public.appUrl.replace(/\/+$/, '')}/#faq`,
  mainEntity: homepageFaqs.map((faq) => ({
    "@type": "Question",
    name: faq.question,
    acceptedAnswer: {
      "@type": "Answer",
      text: faq.answer,
    },
  })),
}

useHead({
  script: [
    {
      key: "homepage-faq-schema",
      type: "application/ld+json",
      textContent: JSON.stringify(homepageFaqSchema),
    },
  ],
})

const contactUs = () => {
  window.location.href = `mailto:${sharaformsConfig.links.contact_email}`
}
</script>

<style lang="scss" scoped>
.customer-logo-container {
  max-width: 130px;
  width: 100%;
}

.ticks {
  color: #2563eb;
}

@screen md {
  #macbook-video {
    position: absolute;
    max-width: 84.8% !important;
    right: 0px;
    top: 6.8%;
  }
}
</style>

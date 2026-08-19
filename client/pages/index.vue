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
            class="text-white text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold my-5"
          >
            Forms that
            <span class="brand-gradient-text">close deals.</span>
            Not just collect.
          </h1>

          <p
            class="text-white/55 text-lg sm:text-xl leading-7 tracking-[-1.5%] sm:leading-8 font-normal max-w-2xl mx-auto"
          >
            The form builder with built-in calculators, quotes, and proposals.
            Unlimited responses, formulas, conditional logic, and dynamic
            documents in one platform.
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

definePageMeta({
  layout: "default",
  middleware: ["root-redirect"],
})

const { isAuthenticated: authenticated } = useIsAuthenticated()

useOpnSeoMeta({
  title: "Forms That Close Deals | Free Form Builder",
  description:
    "SharaForms is the free form builder that closes deals. Build pricing calculators, instant quotes, and proposals with conditional logic on a generous free plan.",
  ogImage: "/share-preview.jpg",
  keywords: "free form builder, forms that close deals, pricing calculator form, instant quote form, proposal form builder, online calculator forms, unlimited forms, unlimited submissions",
  speakable: ["h1", ".marketing-page > section:first-child p", ".faq-answer p"],
  breadcrumbs: [
    { name: "Home" },
  ],
})

const homepageFaqs = [
  {
    question: "Is SharaForms free?",
    answer:
      "Yes. The Free plan is free forever with unlimited forms and submissions and no credit card required. Paid plans add advanced features like custom branding and premium integrations.",
  },
  {
    question: "Are there limits on forms or submissions?",
    answer:
      "No. Both forms and submissions are unlimited on all plans.",
  },
  {
    question: "Can I build pricing calculators, quotes, and proposals?",
    answer:
      "Yes. SharaForms has built-in calculators, instant quotes, and proposals, so you can collect answers and deliver a result in one flow.",
  },
  {
    question: "Does SharaForms support conditional logic and formulas?",
    answer:
      "Yes. Show or hide fields based on answers with conditional logic, and use formula fields to compute totals, prices, and scores automatically.",
  },
  {
    question: "Where is my data stored?",
    answer:
      "On the managed cloud, data is stored in India with SSL encryption in transit and at rest. You can also self-host SharaForms to keep data fully within your control.",
  },
  {
    question: "Can I connect SharaForms to my other tools?",
    answer:
      "Yes. SharaForms offers API access, webhooks, and integrations with tools like Zapier, n8n, Google Sheets, and more.",
  },
]

const homepageSoftwareSchema = {
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "@id": `${useRuntimeConfig().public.appUrl.replace(/\/+$/, '')}/#software`,
  name: "SharaForms",
  applicationCategory: "BusinessApplication",
  operatingSystem: "Web",
  offers: {
    "@type": "Offer",
    price: "0",
    priceCurrency: "USD",
    description: "Free form builder with built-in calculators, quotes, and proposals",
  },
}

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
      key: "homepage-software",
      type: "application/ld+json",
      textContent: JSON.stringify(homepageSoftwareSchema),
    },
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

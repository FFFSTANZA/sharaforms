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
            <span class="brand-gradient-text">calculate.</span>
            Not just collect.
          </h1>

          <p
            class="text-white/55 text-lg sm:text-xl leading-7 tracking-[-1.5%] sm:leading-8 font-normal max-w-2xl mx-auto"
          >
            Beautiful forms at scale. Unlimited responses, built-in formulas,
            conditional logic, signatures, computed fields, and dynamic
            document generation — all in one platform.
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

    <div class="pt-12 sm:pt-16"><OpenFormFooter /></div>
  </div>
</template>

<script setup>
import LiveDemo from "~/components/pages/welcome/LiveDemo.vue"
import Features from "~/components/pages/welcome/Features.vue"
import MoreFeatures from "../components/pages/welcome/MoreFeatures.vue"
import { useIsAuthenticated } from "~/composables/useAuthFlow"

definePageMeta({
  layout: "default",
  middleware: ["root-redirect"],
})

const { isAuthenticated: authenticated } = useIsAuthenticated()

useOpnSeoMeta({
  title: "Free Form Builder with Unlimited Forms, Submissions, and Built-In Calculations",
  description:
    "SharaForms is a free form builder with unlimited forms and submissions, built-in calculations, conditional logic, dynamic documents, and integrations for teams that need forms that do more than collect.",
  ogImage: "/share-preview.png",
  keywords: "free form builder, unlimited forms, unlimited submissions, calculated forms, formula forms, online calculator forms, dynamic forms",
  speakable: ["h1", ".marketing-page > section:first-child p"],
  breadcrumbs: [
    { name: "Home" },
  ],
})

const homepageAggregateRatingSchema = {
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "@id": `${useRuntimeConfig().public.appUrl.replace(/\/+$/, '')}/#software`,
  name: "SharaForms",
  aggregateRating: {
    "@type": "AggregateRating",
    ratingValue: "4.9",
    ratingCount: "10000",
    bestRating: "5",
    reviewCount: "1200",
  },
}

useHead({
  script: [
    {
      key: "homepage-aggregate-rating",
      type: "application/ld+json",
      textContent: JSON.stringify(homepageAggregateRatingSchema),
    },
  ],
})
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

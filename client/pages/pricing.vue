<template>
  <div class="marketing-page">
    <section class="relative overflow-hidden -mt-[76px] bg-[#292438]">
      <div class="px-8 lg:px-12 pt-[132px] sm:pt-[156px] pb-16 sm:pb-20 text-center relative z-2">
        <div
          class="inline-flex items-center gap-1.5 rounded-full border border-white/12 bg-white/6 px-3 py-1 text-xs font-medium text-white/65"
        >
          <span class="inline-flex h-4 w-4 items-center justify-center rounded-full brand-gradient-warm">
            <UIcon name="i-lucide-tag" class="h-2.5 w-2.5 text-white" />
          </span>
          Transparent pricing
        </div>
        <h1
          class="mt-6 text-white text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold"
        >
          Simple pricing
          <br class="hidden sm:block" />
          based on your needs
        </h1>
        <p
          class="text-white/55 mt-5 text-lg sm:text-xl leading-7 tracking-[-1.5%] sm:leading-8 font-normal max-w-2xl mx-auto"
        >
          No locked-in contracts. Upgrade or cancel anytime.
        </p>
      </div>
      <svg
        class="pointer-events-none absolute -bottom-px left-0 h-[60px] w-full sm:h-[90px]"
        viewBox="0 0 1440 120"
        preserveAspectRatio="none"
        aria-hidden="true"
      >
        <path
          d="M0 32C150 68 260 18 430 40C610 72 720 58 880 36C1050 14 1170 60 1440 26V120H0Z"
          fill="#fcfcfd"
        />
      </svg>
    </section>

    <section class="px-8 lg:px-12 pt-10 sm:pt-14">
      <ScrollReveal>
        <div class="flex justify-center">
          <MonthlyYearlySelector v-model="pricingIsYearly" />
        </div>
      </ScrollReveal>

      <div
        class="mx-auto mt-10 sm:mt-12 max-w-266 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-stretch gap-5 lg:gap-6"
      >
        <ScrollReveal
          v-for="(plan, index) in plans"
          :key="plan.key"
          class="h-full"
          :style="{ transitionDelay: `${index * 70}ms` }"
        >
          <PlanCard
            :plan="plan"
            :price="planPrice(plan)"
            :billing-note="planBillingNote(plan)"
            :authenticated="authenticated"
            @cta="handlePlanCta"
          />
        </ScrollReveal>
      </div>
    </section>

    <ScrollReveal class="pricing-trusted">
      <TrustedTeams />
    </ScrollReveal>

    <ScrollReveal>
      <section class="py-14 sm:py-28 px-8 lg:px-12">
        <FeatureComparison />
      </section>
    </ScrollReveal>

    <ScrollReveal>
      <section class="pt-10 pb-4 sm:pt-12 sm:pb-6 px-8 lg:px-12">
        <Testimonials />
      </section>
    </ScrollReveal>

    <ScrollReveal>
      <FaqSection
        class="sf-faq"
        :faqs="faqs"
        :title-lines="['Everything you need to', 'know']"
        description="Find answers about plans, onboarding, roles, and how teams use our tool every day."
        :default-open-index="2"
        id-prefix="pricing-faq-answer"
        @contact="contactUs"
      />
    </ScrollReveal>

    <ScrollReveal>
      <OpenFormFooter />
    </ScrollReveal>
  </div>
</template>

<script setup>
import FeatureComparison from "~/components/pages/pricing/FeatureComparison.vue"
import FaqSection from "~/components/pages/FaqSection.vue"
import sharaformsConfig from "~/sharaforms.config.js"
import { useIsAuthenticated } from "~/composables/useAuthFlow"

definePageMeta({
  layout: "default",
  middleware: [],
})

useOpnSeoMeta({
  title: "Free Plan and Pricing for Calculated Forms",
  description:
    "Explore SharaForms pricing, including a generous free plan with unlimited forms and submissions, plus paid tiers for branding, advanced collaboration, and larger team workflows.",
  ogImage: "/share-preview.jpg",
  keywords: "free form builder pricing, unlimited forms, unlimited submissions pricing, free online forms, calculated forms pricing, free forms with formulas",
  speakable: [".faq-answer p", "h1"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "Pricing" },
  ],
})

const { openSubscriptionModal } = useAppModals()
const { isAuthenticated: authenticated } = useIsAuthenticated()
const { getPlanPrice } = useBillingUpsell()

const pricingIsYearly = ref(true)
const displayedPlanPrices = ref({
  pro: 0,
  business: 0,
  enterprise: 0,
})
const activePriceAnimationFrame = ref(null)
let hasInitializedDisplayedPrices = false

const plans = [
  {
    key: "free",
    name: "Free",
    tagline: "For individuals getting started",
    icon: "i-lucide-smile",
    accent: "violet",
    description: "Start collecting unlimited responses with no friction.",
    perLabel: "/mo",
    featuresLabel: "Includes",
    highlighted: false,
    free: true,
    features: [
      "Unlimited forms & submissions",
      "File uploads (basic quota)",
      "Form logic & validation",
      "Computed fields (calculations)",
      "Pre-fills, URL parameters",
      "Multi-user access (all admins, no roles)",
      "1 workspace only",
      "Branding required",
      "Community support",
      "API access",
      "Basic integrations & webhooks",
    ],
  },
  {
    key: "pro",
    name: "Pro",
    tagline: "For serious creators",
    icon: "i-lucide-crown",
    accent: "pink",
    description: "A polished, professional experience for serious work.",
    perLabel: "/mo",
    featuresLabel: "Everything in Free, plus",
    highlighted: true,
    ctaLabel: "Get started free",
    features: [
      "Remove branding",
      "Custom domains",
      "Custom SMTP",
      "Discord, Slack, Telegram",
      "Password-protected forms",
      "Form expiration",
      "Captcha",
      "Analytics dashboard",
      "AI form generation",
      "Editable submissions",
      "Unique submission IDs",
      "Multiple workspaces",
    ],
  },
  {
    key: "business",
    name: "Business",
    tagline: "For growing teams",
    icon: "i-lucide-building-2",
    accent: "blue",
    description: "Built for teams and agencies managing forms at scale.",
    perLabel: "/mo",
    featuresLabel: "Everything in Pro, plus",
    highlighted: false,
    ctaLabel: "Get started free",
    features: [
      "Multi-user with roles & permissions",
      "Advanced branding (CSS, fonts, favicons)",
      "Higher file upload size limits",
      "Priority support",
      "Partial submissions",
      "Versioning",
      "Advanced integrations",
    ],
  },
  {
    key: "enterprise",
    name: "Enterprise",
    tagline: "For organizations",
    icon: "i-lucide-globe",
    accent: "sky",
    description: "Enterprise-grade security, compliance, and control.",
    perLabel: "/mo",
    featuresLabel: "Everything in Business, plus",
    highlighted: false,
    ctaLabel: "Get started free",
    features: [
      "SAML / LDAP SSO",
      "Audit logs & compliance features",
      "External storage support",
      "White-label hosting option",
      "SLA & onboarding support",
    ],
  },
]

const planPriceValues = computed(() => ({
  pro: getPlanPrice("pro", pricingIsYearly.value) ?? 0,
  business: getPlanPrice("business", pricingIsYearly.value) ?? 0,
  enterprise: getPlanPrice("enterprise", pricingIsYearly.value) ?? 0,
}))

const planPrice = (plan) => {
  if (plan.free) return "$0"
  const price = formatAnimatedPlanPrice(plan.key)
  return price ?? "$0"
}

const planBillingNote = (plan) => {
  if (plan.free) return "Free forever. No card required."
  return pricingIsYearly.value ? "Billed annually" : "Billed monthly"
}

function easeOutCubic(progress) {
  return 1 - (1 - progress) ** 3
}

function formatAnimatedPlanPrice(plan) {
  const price = displayedPlanPrices.value[plan]
  if (price == null) return null
  return `$${Math.round(price)}`
}

function animatePlanPrices(nextPrices) {
  if (activePriceAnimationFrame.value) {
    cancelAnimationFrame(activePriceAnimationFrame.value)
  }

  const startPrices = { ...displayedPlanPrices.value }
  const duration = 450
  const startTime = performance.now()

  const tick = (currentTime) => {
    const progress = Math.min((currentTime - startTime) / duration, 1)
    const easedProgress = easeOutCubic(progress)

    displayedPlanPrices.value = Object.keys(nextPrices).reduce((prices, plan) => {
      const startPrice = startPrices[plan] ?? 0
      const endPrice = nextPrices[plan] ?? 0

      prices[plan] = startPrice + (endPrice - startPrice) * easedProgress
      return prices
    }, {})

    if (progress < 1) {
      activePriceAnimationFrame.value = requestAnimationFrame(tick)
      return
    }

    displayedPlanPrices.value = { ...nextPrices }
    activePriceAnimationFrame.value = null
  }

  activePriceAnimationFrame.value = requestAnimationFrame(tick)
}

watch(
  planPriceValues,
  (nextPrices) => {
    if (!import.meta.client || !hasInitializedDisplayedPrices) {
      displayedPlanPrices.value = { ...nextPrices }
      hasInitializedDisplayedPrices = true
      return
    }

    animatePlanPrices(nextPrices)
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  if (activePriceAnimationFrame.value) {
    cancelAnimationFrame(activePriceAnimationFrame.value)
  }
})

const faqs = [
  {
    question: "Is there any submission or form limit?",
    answer:
      "No — both forms and submissions are unlimited on all plans. The Free plan gives you access to most features without restrictive usage caps.",
  },
  {
    question: "Are integrations included in the Free plan?",
    answer:
      "Yes — basic integrations like webhooks and API access are available on the Free plan. Some advanced integrations are available on higher tiers.",
  },
  {
    question: "Can I hide the SharaForms branding?",
    answer:
      "Yes. You can remove the “Made with SharaForms” footer and add your own branding on the Pro plan or higher.",
  },
  {
    question: "Is there a difference between monthly and yearly billing?",
    answer:
      "Yearly billing is discounted compared to paying monthly. You’ll be billed once per year and save versus the monthly plan.",
  },
  {
    question: "How can I pay for my subscription?",
      answer:
        "We support card payments via Dodo Payments. You’ll get invoices and receipts automatically for your records.",
  },
  {
    question: "Do you offer discounts for non-profits or education?",
    answer:
      "Yes — we offer discounted pricing for non-profits and students. Contact us and we’ll help you get set up.",
  },
  {
    question: "Can I cancel my subscription anytime?",
    answer:
      "Yes. You can cancel anytime from the billing portal. Your subscription remains active until the end of the current billing period.",
  },
  {
    question: "Can I switch between plans?",
    answer:
      "Yes — you can upgrade or downgrade at any time. Changes apply immediately, and billing adjusts accordingly.",
  },
  {
    question: "Do you offer refunds?",
    answer:
      "If something isn’t working as expected, reach out and we’ll do our best to help. Refunds are handled case-by-case.",
  },
  {
    question: "Do you offer a free trial of paid features?",
    answer:
      "We don’t currently offer an automated trial, but you can contact us if you’d like to evaluate a paid plan for your team.",
  },
  {
    question: "Is there an API, and is it free?",
    answer:
      "Yes — SharaForms has an API and API access tokens. They’re available on the Free plan, with higher tiers unlocking more advanced capabilities.",
  },
  {
    question: "Can I collaborate with my team?",
    answer:
      "Yes — multi-user collaboration is supported. Higher tiers add roles and permissions for larger teams.",
  },
]

defineRouteRules({
  swr: 3600,
})

const pricingFaqSchema = {
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "FAQPage",
      "@id": "#faq",
      mainEntity: faqs.map((faq) => ({
        "@type": "Question",
        name: faq.question,
        acceptedAnswer: {
          "@type": "Answer",
          text: faq.answer,
        },
      })),
    },
    {
      "@type": "QAPage",
      "@id": "#qa",
      mainEntity: {
        "@type": "Question",
        name: faqs[0].question,
        acceptedAnswer: {
          "@type": "Answer",
          text: faqs[0].answer,
        },
      },
    },
  ],
}

const pricingOfferCatalogSchema = {
  "@context": "https://schema.org",
  "@type": "OfferCatalog",
  name: "SharaForms plans",
  itemListElement: [
    {
      "@type": "Offer",
      name: "Free",
      category: "Free plan",
      price: "0",
      priceCurrency: "USD",
      availability: "https://schema.org/InStock",
      description: "A generous free plan with unlimited forms and submissions and core product capabilities.",
    },
    {
      "@type": "Offer",
      name: "Pro",
      category: "Paid plan",
      priceCurrency: "USD",
      availability: "https://schema.org/InStock",
      description: "For teams that need branding controls and more advanced customization.",
    },
    {
      "@type": "Offer",
      name: "Business",
      category: "Paid plan",
      priceCurrency: "USD",
      availability: "https://schema.org/InStock",
      description: "For growing teams that need collaboration and larger workflow support.",
    },
    {
      "@type": "Offer",
      name: "Enterprise",
      category: "Enterprise plan",
      priceCurrency: "USD",
      availability: "https://schema.org/InStock",
      description: "For organizations that need advanced governance, compliance, and control.",
    },
  ],
}

const pricingProductSchema = {
  "@context": "https://schema.org",
  "@type": "Product",
  "@id": `${useRuntimeConfig().public.appUrl.replace(/\/+$/, '')}/pricing#product`,
  name: "SharaForms Form Builder",
  description: "A powerful form builder with unlimited forms and submissions, built-in calculations, conditional logic, dynamic documents, and integrations.",
  brand: {
    "@type": "Brand",
    name: "SharaForms",
  },
  offers: {
    "@type": "AggregateOffer",
    lowPrice: "0",
    highPrice: "125",
    priceCurrency: "USD",
    availability: "https://schema.org/InStock",
    offerCount: "4",
  },
  category: "BusinessApplication",
}

useHead({
  script: [
    {
      key: "pricing-faq-schema",
      type: "application/ld+json",
      textContent: JSON.stringify(pricingFaqSchema),
    },
    {
      key: "pricing-offer-catalog-schema",
      type: "application/ld+json",
      textContent: JSON.stringify(pricingOfferCatalogSchema),
    },
    {
      key: "pricing-product-schema",
      type: "application/ld+json",
      textContent: JSON.stringify(pricingProductSchema),
    },
  ],
})

const handlePlanCta = (plan) => {
  if (!authenticated.value) {
    return navigateTo({ name: "register" })
  }
  openSubscriptionModal({ plan, yearly: pricingIsYearly.value })
}

const contactUs = () => {
  window.location.href = `mailto:${sharaformsConfig.links.contact_email}`
}
</script>

<style scoped>
.pricing-trusted :deep(section) {
  background: transparent;
}
.sf-faq {
  background: transparent;
}
.marketing-page {
  background: #fcfcfd;
}
.marketing-page::before {
  display: none;
}
</style>

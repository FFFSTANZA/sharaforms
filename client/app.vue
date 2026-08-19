<template>
  <AppProvider>
    <div
      id="app"
      class="bg-white dark:bg-notion-dark"
    >
      <NuxtLoadingIndicator color="#2563eb" />
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>

      <CookieConsentBanner />

      <!-- Third-party services and modals - only load when not on public form pages -->
      <ClientOnly v-if="!isPublicFormPage">
        <div
          class="fixed z-[9999] left-0 bottom-0 p-4" id="admin-actions"
        >
          <UButtonGroup size="sm">
            <ToolsStopImpersonation />
          </UButtonGroup>
        </div>

        <Clarity v-if="hasAnalyticsConsent" />
        <FeatureBase v-if="hasAnalyticsConsent" />
        <SubscriptionModal />
        <QuickRegister />
      </ClientOnly>
    </div>
  </AppProvider>
</template>

<script setup>
import FeatureBase from "~/components/vendor/FeatureBase.vue"
import Clarity from "~/components/vendor/Clarity.vue"
import CookieConsentBanner from "~/components/global/CookieConsentBanner.vue"
const config = useRuntimeConfig()
const route = useRoute()
const { locale } = useI18n()
const { hasAnalyticsConsent } = usePrivacyPreferences()

// Check if current page is a public form page (for performance optimization)
const isPublicFormPage = computed(() => route.name === 'forms-slug')

// SEO and head configuration
useOpnSeoMeta({
  title: "Free Form Builder with Unlimited Forms and Submissions",
  description:
    "SharaForms is a free form builder with unlimited forms and submissions, built-in calculators, instant quotes, and proposals for teams that need more than basic forms.",
  ogImage: "/share-preview.jpg",
  robots: () => {
    return config.public.env === "production" ? null : "noindex, nofollow"
  },
})

const siteBaseUrl = computed(() => resolveStructuredDataBaseUrl(config.public.appUrl))

const globalStructuredData = computed(() => {
  const baseUrl = siteBaseUrl.value
  if (!baseUrl) {
    return null
  }

  return {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": `${baseUrl}/#organization`,
        name: "SharaForms",
        url: `${baseUrl}/`,
        description:
          "SharaForms helps teams build free online forms with unlimited forms and submissions, built-in calculators, dynamic documents, and workflow-ready automations.",
        logo: {
          "@type": "ImageObject",
          url: `${baseUrl}/img/sharaforms-logo.png`,
        },
      },
      {
        "@type": "WebSite",
        "@id": `${baseUrl}/#website`,
        url: `${baseUrl}/`,
        name: "SharaForms",
        alternateName: "SharaForms Free Form Builder",
        description:
          "SharaForms is a free form builder for teams that need unlimited forms and submissions, built-in calculators, dynamic documents, and automation-ready integrations.",
        publisher: {
          "@id": `${baseUrl}/#organization`,
        },
        inLanguage: ["en-US", "ar-EG", "bn-BD", "ca-ES", "cs-CZ", "de-DE", "es-ES", "eu-ES", "fr-FR", "gl-ES", "hi-IN", "hu-HU", "it-IT", "ja-JP", "jv-ID", "ko-KR", "mr-IN", "nl-NL", "pa-IN", "pl-PL", "pt-BR", "ru-RU", "sk-SK", "sr-RS", "sv-SE", "ta-IN", "te-IN", "tr-TR", "uk-UA", "ur-PK", "vi-VN", "zh-CN"],
      },
      {
        "@type": "SoftwareApplication",
        "@id": `${baseUrl}/#software`,
        name: "SharaForms",
        alternateName: "SharaForms Form Builder",
        applicationCategory: "BusinessApplication",
        applicationSubCategory: "Online Form Builder",
        operatingSystem: "Web",
        url: `${baseUrl}/`,
        image: `${baseUrl}/share-preview.jpg`,
        description:
          "A free form builder that closes deals with built-in calculators, instant quotes, and proposals. Unlimited forms and submissions, formulas, conditional logic, PDFs, signatures, and integrations.",
        keywords: "free form builder, forms that close deals, pricing calculator forms, quote forms, proposal form builder, unlimited forms, unlimited submissions, online calculator forms, no-code forms, conditional logic forms, workflow automation, lead generation forms, order forms, registration forms",
        isAccessibleForFree: true,
        offers: {
          "@type": "Offer",
          price: "0",
          priceCurrency: "USD",
          availability: "https://schema.org/InStock",
          category: "Free plan",
        },
        featureList: [
          "Unlimited forms and submissions on the free plan",
          "Built-in calculators for quotes, scores, pricing, and approvals",
          "Instant quote forms and proposal templates",
          "Conditional logic and dynamic workflows",
          "Integrations and API access",
          "Dynamic document and PDF generation",
          "AI-powered form generation",
          "Multi-page and single-page forms",
          "File uploads and signature capture",
          "Custom domains and branding controls",
        ],
        screenshot: `${baseUrl}/share-preview.jpg`,
        provider: {
          "@id": `${baseUrl}/#organization`,
        },
      },
      {
        "@type": "SiteNavigationElement",
        "@id": `${baseUrl}/#sitenavigation`,
        name: "SharaForms Navigation",
        hasPart: [
          { "@type": "SiteNavigationElement", name: "Pricing", url: `${baseUrl}/pricing` },
          { "@type": "SiteNavigationElement", name: "Templates", url: `${baseUrl}/templates` },
          { "@type": "SiteNavigationElement", name: "Integrations", url: `${baseUrl}/integrations` },
          { "@type": "SiteNavigationElement", name: "Enterprise", url: `${baseUrl}/enterprise` },
          { "@type": "SiteNavigationElement", name: "AI Form Builder", url: `${baseUrl}/ai-form-builder` },
          { "@type": "SiteNavigationElement", name: "Industry", url: `${baseUrl}/industry` },
        ],
      },
      {
        "@type": "Person",
        "@id": `${baseUrl}/#person`,
        name: "SharaForms Team",
        description: "Creator of SharaForms form builder platform",
        url: `${baseUrl}/`,
      },
    ],
  }
})

const currentHref = computed(() => {
  const baseUrl = siteBaseUrl.value
  if (!baseUrl) return '/'
  const path = route.path === '/' ? '' : route.path
  return `${baseUrl}${path}` || `${baseUrl}/`
})

useHead({
  titleTemplate: (titleChunk) => {
    return titleChunk ? `${titleChunk} - SharaForms` : "SharaForms"
  },
  meta: [
    {
      name: 'mobile-web-app-capable',
      content: 'yes'
    },
    {
      name: 'apple-mobile-web-app-status-bar-style',
      content: 'black-translucent'
    },
  ],
  link: [
    {
      rel: 'stylesheet',
      href: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'
    },
    {
      rel: 'icon',
      type: 'image/x-icon',
      href: '/favicon.ico'
    },
    {
      rel: 'icon',
      type: 'image/png',
      sizes: '192x192',
      href: '/favicon-192x192.png'
    },
    {
      rel: 'icon',
      type: 'image/png',
      sizes: '96x96',
      href: '/favicon-96x96.png'
    },
    {
      rel: 'icon',
      type: 'image/png',
      sizes: '48x48',
      href: '/favicon-48x48.png'
    },
    {
      rel: 'apple-touch-icon',
      type: 'image/png',
      href: '/img/sharaforms-logo.png'
    },
    {
      rel: 'alternate',
      hreflang: 'x-default',
      href: currentHref.value
    },
    {
      rel: 'ai-txt',
      href: '/ai.txt'
    },
    {
      rel: 'llms-txt',
      href: '/llms.txt'
    },
    {
      rel: 'llms-full-txt',
      href: '/llms-full.txt'
    },
    {
      rel: 'manifest',
      href: '/site.webmanifest'
    },
  ],
  script: globalStructuredData.value
    ? [
        {
          key: 'global-structured-data',
          type: 'application/ld+json',
          textContent: JSON.stringify(globalStructuredData.value)
        }
      ]
    : [],
  htmlAttrs: () => ({
    dir: locale.value?.startsWith('ar') ? 'rtl' : 'ltr'
  })
})

function resolveStructuredDataBaseUrl (configuredAppUrl) {
  if (configuredAppUrl && configuredAppUrl !== '/') {
    return configuredAppUrl.replace(/\/+$/, '')
  }

  if (import.meta.server) {
    const event = useRequestEvent()
    const forwardedHost = event?.node.req.headers['x-forwarded-host']
    const host = forwardedHost || event?.node.req.headers.host
    const protocol = event?.node.req.headers['x-forwarded-proto'] || 'https'

    return host ? `${protocol}://${host}` : ''
  }

  return import.meta.client ? window.location.origin : ''
}
</script>

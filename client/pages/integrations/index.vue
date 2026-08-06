<template>
  <div class="marketing-page relative">
    <div class="mt-2 flex flex-col">
      <div class="py-14 sm:py-28 px-8 lg:px-12">
        <div class="max-w-3xl mx-auto text-center">
          <div
            class="brand-chip inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium text-neutral-700"
          >
            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full brand-gradient-warm">
              <UIcon name="i-lucide-plug" class="h-2.5 w-2.5 text-white" />
            </span>
            Integrations
          </div>
          <h1 class="brand-text-strong text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold mt-6">
            Available Integrations
          </h1>
          <p class="brand-text-muted mt-4 text-lg sm:text-xl leading-7 tracking-[-1.5%] sm:leading-8 font-normal">
            Explore native setup guides for every SharaForms integration.
          </p>
        </div>
      </div>

      <div class="px-8 lg:px-12">
        <div class="max-w-6xl mx-auto">
          <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <NuxtLink
              v-for="integration in integrationsList"
              :key="integration.title"
              :to="{ name: 'integrations-slug', params: { slug: integration.slug } }"
              class="brand-surface-muted relative block rounded-2xl p-6 transition-all duration-300 hover:-translate-y-0.5"
            >
              <div
                v-if="integration.popular"
                class="brand-gradient absolute -top-2 -left-3 -rotate-12 text-white text-xs font-semibold px-2 py-1 rounded-sm shadow-sm"
              >
                Most Popular
              </div>
              <div class="flex justify-between items-start gap-4">
                <div class="brand-icon-tile w-10 h-10 rounded-xl flex items-center justify-center">
                  <Icon
                    :name="integration.icon"
                    class="w-8 h-8"
                    dynamic
                  />
                </div>
                <div class="brand-link text-sm font-medium flex items-center gap-1">
                  Setup Guide
                  <UIcon
                    name="i-lucide-arrow-up-right"
                    class="w-4 h-4 flex-shrink-0"
                  />
                </div>
              </div>

              <div class="mt-4 flex flex-wrap gap-2 text-xs font-medium">
                <span class="rounded-full bg-white/80 px-2.5 py-1 text-neutral-700 ring-1 ring-neutral-200">
                  {{ integration.section_name }}
                </span>
                <span class="rounded-full bg-white/80 px-2.5 py-1 text-neutral-700 ring-1 ring-neutral-200">
                  {{ integration.tierLabel }}+
                </span>
                <span
                  v-if="integration.is_external"
                  class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700 ring-1 ring-amber-200"
                >
                  External workflow
                </span>
              </div>

              <h3 class="mt-4 text-lg font-semibold brand-text-strong">
                {{ integration.title }}
              </h3>
              <p class="text-sm brand-text-muted mt-1">
                {{ integration.description }}
              </p>

              <ul class="mt-4 space-y-2 text-sm brand-text-muted">
                <li
                  v-for="step in integration.steps"
                  :key="step"
                  class="flex items-center gap-2"
                >
                  <UIcon name="i-lucide-check" class="h-4 w-4 text-emerald-500 flex-shrink-0" />
                  {{ step }}
                </li>
              </ul>
            </NuxtLink>
          </div>
        </div>
      </div>

      <div class="px-8 lg:px-12">
        <div class="brand-surface p-10 max-w-6xl mx-auto rounded-[32px]">
          <h2 class="brand-text-strong text-4xl font-bold text-center mb-2">
            Integration General Setup Guides
          </h2>
        <p class="brand-text-muted text-center mb-12">
          The same workflow patterns show up again and again. Start here if you want the shortest route to a reliable setup.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-neutral-800 max-w-6xl mx-auto">
          <div
            v-for="guide in setupGuides"
            :key="guide.title"
          >
            <h2 class="text-xl font-semibold mb-2">
              {{ guide.title }}
            </h2>
            <p class="mb-4 text-sm text-neutral-500">
              {{ guide.description }}
            </p>
            <ol class="space-y-4 text-base">
              <li
                v-for="(step, index) in guide.steps"
                :key="step"
                class="flex items-start gap-3"
              >
                <span class="brand-chip-strong w-8 h-8 flex items-center justify-center rounded-full font-bold">{{ index + 1 }}</span>
                <span v-html="step" />
              </li>
            </ol>
          </div>
        </div>
      </div>

      <div class="brand-surface-strong relative overflow-hidden max-w-6xl mx-auto my-10 rounded-3xl p-8 sm:p-10 text-white">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
          <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(255,138,91,0.45),rgba(239,93,168,0.3)_30%,rgba(139,92,246,0.3)_65%,rgba(59,130,246,0.4)_100%)]"></div>
          <div class="absolute -top-24 -right-24 h-80 w-80 rounded-full bg-[#ff8a5b]/30 blur-3xl"></div>
          <div class="absolute -bottom-24 left-1/4 h-80 w-80 rounded-full bg-[#8b5cf6]/30 blur-3xl"></div>
        </div>
        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-center gap-10 lg:gap-8">
          <div class="max-w-md text-center lg:text-left">
            <h2 class="text-3xl font-bold">
              Need help?
            </h2>
            <p class="mt-2 text-white/72 text-lg">
              Visit our Help Center for detailed documentation!
            </p>
            <a
              href="#"
              class="brand-button-secondary inline-flex items-center gap-2 mt-6 px-4 py-2 text-sm font-semibold rounded-lg transition"
              @click.prevent="crisp.openHelpdesk()"
            >
              Help Center
              <UIcon
                name="i-lucide-external-link"
                class="w-4 h-4 flex-shrink-0"
              />
            </a>
          </div>
          <div class="hidden lg:grid grid-cols-2 gap-4 shrink-0">
            <div class="space-y-4">
              <div
                v-for="n in 3"
                :key="`help-a${n}`"
                class="bg-white p-4 rounded-2xl shadow-sm w-48 h-20 flex items-center gap-3"
              >
                <div class="brand-gradient-warm h-10 w-10 rounded-xl flex items-center justify-center shrink-0">
                  <UIcon name="i-lucide-message-circle" class="w-5 h-5 text-white" />
                </div>
                <div class="flex-1">
                  <div class="bg-gradient-to-r from-pink-200 to-violet-200 w-3/4 h-3 mb-2 rounded-sm" />
                  <div class="bg-neutral-200 w-full h-5 rounded-full" />
                </div>
              </div>
            </div>
            <div class="space-y-4 pt-8">
              <div
                v-for="n in 2"
                :key="`help-b${n}`"
                class="bg-white p-4 rounded-2xl shadow-sm w-48 h-20 flex items-center gap-3"
              >
                <div class="brand-gradient-warm h-10 w-10 rounded-xl flex items-center justify-center shrink-0">
                  <UIcon name="i-lucide-life-buoy" class="w-5 h-5 text-white" />
                </div>
                <div class="flex-1">
                  <div class="bg-gradient-to-r from-orange-200 to-pink-200 w-3/4 h-3 mb-2 rounded-sm" />
                  <div class="bg-neutral-200 w-full h-5 rounded-full" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
    <OpenFormFooter />
  </div>
</template>

<script setup>
import { generalIntegrationGuides, integrationGuides } from '~/data/integration-guides'
import { resolveSchemaUrl, useSchemaBaseUrl } from '~/composables/useSchemaSeo'

useOpnSeoMeta({
  title: "Available Integrations",
  description:
    "Explore native setup guides for every SharaForms integration.",
  speakable: ["h1", "p"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "Integrations" },
  ],
})
defineRouteRules({
  swr: 3600
})
definePageMeta({
  stickyNavbar: true,
  middleware: ['root-redirect']
})

const crisp = useCrisp()

const integrationsList = computed(() => {
  return integrationGuides.map(guide => ({
    title: guide.name,
    description: guide.cardDescription,
    icon: guide.icon,
    slug: guide.slug,
    steps: guide.steps,
    popular: ['webhook', 'zapier', 'google_sheets'].includes(guide.slug),
    section_name: guide.section_name,
    tierLabel: guide.tierLabel,
    is_external: guide.is_external,
  }))
})

const setupGuides = generalIntegrationGuides
const schemaBaseUrl = useSchemaBaseUrl()

const integrationsCollectionSchema = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'CollectionPage',
  name: 'SharaForms Integrations',
  description: 'Browse SharaForms integrations and setup guides for notifications, webhooks, automations, and connected workflows.',
  url: resolveSchemaUrl(schemaBaseUrl, '/integrations'),
  mainEntity: {
    '@type': 'ItemList',
    numberOfItems: integrationsList.value.length,
    itemListElement: integrationsList.value.map((integration, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      url: resolveSchemaUrl(schemaBaseUrl, `/integrations/${integration.slug}`),
      name: integration.title,
      description: integration.description,
    })),
  },
}))

const setupGuidesSchema = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'ItemList',
  name: 'SharaForms integration setup guides',
  numberOfItems: setupGuides.length,
  itemListElement: setupGuides.map((guide, index) => ({
    '@type': 'ListItem',
    position: index + 1,
    name: guide.title,
    description: guide.description,
  })),
}))

useHead(() => ({
  script: [
    {
      key: 'integrations-collection-schema',
      type: 'application/ld+json',
      textContent: JSON.stringify(integrationsCollectionSchema.value),
    },
    {
      key: 'integrations-setup-guides-schema',
      type: 'application/ld+json',
      textContent: JSON.stringify(setupGuidesSchema.value),
    },
  ],
}))
</script>

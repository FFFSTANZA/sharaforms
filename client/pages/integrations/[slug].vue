<template>
  <div class="relative">
    <div
      v-if="guide"
      class="w-full md:max-w-6xl md:mx-auto px-4 pt-8 md:pt-16 pb-10"
    >
      <p class="mb-4 text-sm">
        <UButton
          :to="{ name: 'integrations' }"
          variant="ghost"
          color="neutral"
          class="mb-4"
          icon="i-lucide-arrow-left"
        >
          Other Integrations
        </UButton>
      </p>

      <div class="rounded-3xl border border-neutral-200 bg-white p-8 shadow-sm md:p-10">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
          <div class="flex items-start gap-4 lg:max-w-3xl">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-neutral-200 bg-neutral-50">
              <Icon
                :name="guide.icon"
                class="h-8 w-8"
                dynamic
              />
            </div>

            <div class="min-w-0">
              <div class="flex flex-wrap gap-2 text-xs font-medium">
                <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-neutral-700">
                  {{ guide.section_name }}
                </span>
                <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-neutral-700">
                  {{ guide.tierLabel }}+
                </span>
                <span
                  v-if="guide.is_external"
                  class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700"
                >
                  External workflow
                </span>
              </div>

              <h1 class="mt-3 text-3xl font-semibold text-neutral-900 md:text-4xl">
                {{ guide.name }}
              </h1>
              <p class="mt-4 text-base leading-7 text-neutral-600 md:text-lg">
                {{ guide.summary }}
              </p>
            </div>
          </div>

          <div class="flex flex-wrap gap-3 lg:max-w-sm lg:justify-end">
            <UButton
              :to="{ name: authenticated ? 'forms-create' : 'forms-create-guest' }"
              class="brand-button-primary pl-4 pr-3.5 rounded-[12px] font-medium"
            >
              Create a form
            </UButton>
            <UButton
              v-if="guide.url"
              :href="guide.url"
              target="_blank"
              external
              color="neutral"
              variant="outline"
            >
              Open {{ guide.name }}
            </UButton>
            <UButton
              v-if="guide.providerDocsUrl"
              :href="guide.providerDocsUrl"
              target="_blank"
              external
              color="neutral"
              variant="outline"
            >
              {{ guide.providerDocsLabel }}
            </UButton>
            <UButton
              v-if="guide.crisp_help_page_slug"
              color="neutral"
              variant="outline"
              @click="crisp.openHelpdeskArticle(guide.crisp_help_page_slug)"
            >
              View Help Article
            </UButton>
          </div>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(18rem,0.8fr)]">
          <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-6">
            <h2 class="text-lg font-semibold text-neutral-900">
              Setup steps
            </h2>
            <ol class="mt-5 space-y-5">
              <li
                v-for="(step, index) in guide.setupSteps"
                :key="step.title"
                class="flex items-start gap-4"
              >
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-sm font-semibold text-white">
                  {{ index + 1 }}
                </span>
                <div>
                  <h3 class="font-semibold text-neutral-900">
                    {{ step.title }}
                  </h3>
                  <p class="mt-1 text-sm leading-6 text-neutral-600 md:text-base">
                    {{ step.body }}
                  </p>
                </div>
              </li>
            </ol>
          </div>

          <div class="space-y-6">
            <div class="rounded-2xl border border-neutral-200 p-6">
              <h2 class="text-lg font-semibold text-neutral-900">
                Requirements
              </h2>
              <ul class="mt-4 space-y-3 text-sm leading-6 text-neutral-600 md:text-base">
                <li
                  v-for="item in guide.requirements"
                  :key="item"
                  class="flex items-start gap-3"
                >
                  <span class="mt-1 text-green-500">✔</span>
                  <span>{{ item }}</span>
                </li>
              </ul>
            </div>

            <div class="rounded-2xl border border-neutral-200 p-6">
              <h2 class="text-lg font-semibold text-neutral-900">
                What this integration can do
              </h2>
              <ul class="mt-4 space-y-3 text-sm leading-6 text-neutral-600 md:text-base">
                <li
                  v-for="item in guide.capabilities"
                  :key="item"
                  class="flex items-start gap-3"
                >
                  <span class="mt-1 brand-gradient-text-warm">•</span>
                  <span>{{ item }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="mt-8 rounded-2xl border border-neutral-200 p-6">
          <h2 class="text-lg font-semibold text-neutral-900">
            Recommended setup flow
          </h2>
          <ul class="mt-4 space-y-3 text-sm leading-6 text-neutral-600 md:text-base">
            <li
              v-for="step in guide.steps"
              :key="step"
              class="flex items-start gap-3"
            >
              <span class="mt-1 brand-gradient-text-warm">→</span>
              <span>{{ step }}</span>
            </li>
          </ul>
        </div>

        <div class="mt-8 rounded-2xl border border-neutral-200 p-6">
          <h2 class="text-lg font-semibold text-neutral-900">
            Practical tips
          </h2>
          <ul class="mt-4 space-y-3 text-sm leading-6 text-neutral-600 md:text-base">
            <li
              v-for="tip in guide.tips"
              :key="tip"
              class="flex items-start gap-3"
            >
              <span class="mt-1 text-amber-500">✦</span>
              <span>{{ tip }}</span>
            </li>
          </ul>
        </div>

        <div class="mt-8 rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-6 text-sm leading-6 text-neutral-600 md:text-base">
          <span class="font-semibold text-neutral-900">Need another route?</span>
          Visit the full integrations index to compare native integrations, connected-account flows, and external automation platforms before you commit to a setup.
        </div>

        <p class="mt-8 text-sm">
          <NuxtLink
            :to="{ name: 'integrations' }"
            class="brand-gradient-text-warm inline-block"
          >
            Discover our other Integrations
          </NuxtLink>
        </p>
      </div>
    </div>
    <OpenFormFooter class="border-t" />
  </div>
</template>

<script setup>
import { getIntegrationGuide } from '~/data/integration-guides'
import { resolveSchemaUrl, useSchemaBaseUrl } from '~/composables/useSchemaSeo'

const crisp = useCrisp()
const { isAuthenticated: authenticated } = useIsAuthenticated()
const slug = computed(() => useRoute().params.slug)
const guide = computed(() => getIntegrationGuide(slug.value))

if (!guide.value) {
  throw createError({ statusCode: 404, statusMessage: 'Integration page not found' })
}

defineRouteRules({
  swr: 3600
})
definePageMeta({
  stickyNavbar: true,
  middleware: ['root-redirect']
})

useOpnSeoMeta({
  title: () => guide.value?.name ?? 'Integration',
  description: () => guide.value?.seoDescription ?? 'SharaForms is a free form builder with unlimited forms and submissions, built-in calculators, instant quotes, and proposals for teams that need more than basic forms.',
  speakable: ["h1", "p"],
})

const schemaBaseUrl = useSchemaBaseUrl()

const integrationHowToSchema = computed(() => {
  if (!guide.value) {
    return null
  }

  return {
    '@context': 'https://schema.org',
    '@type': 'HowTo',
    name: `How to set up ${guide.value.name} with SharaForms`,
    description: guide.value.seoDescription || guide.value.summary,
    url: resolveSchemaUrl(schemaBaseUrl, `/integrations/${guide.value.slug}`),
    totalTime: `PT${Math.max(guide.value.setupSteps.length * 3, 5)}M`,
    estimatedCost: { '@type': 'MonetaryAmount', currency: 'USD', value: '0' },
    supply: (guide.value.requirements || []).map((item) => ({
      '@type': 'HowToSupply',
      name: item,
    })),
    tool: [
      { '@type': 'HowToTool', name: 'SharaForms account' },
      { '@type': 'HowToTool', name: `${guide.value.name} account` },
    ],
    step: (guide.value.setupSteps || []).map((step, index) => ({
      '@type': 'HowToStep',
      position: index + 1,
      name: step.title,
      text: step.body,
      ...(step.image ? { image: step.image } : {}),
    })),
  }
})

const integrationBreadcrumbSchema = computed(() => {
  if (!guide.value) {
    return null
  }

  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
      {
        '@type': 'ListItem',
        position: 1,
        name: 'Home',
        item: resolveSchemaUrl(schemaBaseUrl, '/'),
      },
      {
        '@type': 'ListItem',
        position: 2,
        name: 'Integrations',
        item: resolveSchemaUrl(schemaBaseUrl, '/integrations'),
      },
      {
        '@type': 'ListItem',
        position: 3,
        name: guide.value.name,
        item: resolveSchemaUrl(schemaBaseUrl, `/integrations/${guide.value.slug}`),
      },
    ],
  }
})

useHead(() => ({
  script: [
    ...(integrationHowToSchema.value
      ? [{
          key: `integration-howto-schema:${guide.value.slug}`,
          type: 'application/ld+json',
          textContent: JSON.stringify(integrationHowToSchema.value),
        }]
      : []),
    ...(integrationBreadcrumbSchema.value
      ? [{
          key: `integration-breadcrumb-schema:${guide.value.slug}`,
          type: 'application/ld+json',
          textContent: JSON.stringify(integrationBreadcrumbSchema.value),
        }]
      : []),
  ],
}))
</script>

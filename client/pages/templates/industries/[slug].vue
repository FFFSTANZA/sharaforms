<template>
  <div class="flex flex-col min-h-full">
    <Breadcrumb :path="breadcrumbs" />

    <p
      v-if="industry === null || !industry"
      class="text-center my-4"
    >
      We could not find this industry.
    </p>
    <template v-else>
      <!-- START HERO -->
      <section class="relative overflow-hidden bg-neutral-50 dark:bg-neutral-950">
        <div class="pointer-events-none absolute left-1/2 top-[-240px] h-[480px] w-[900px] -translate-x-1/2 rounded-full bg-[radial-gradient(ellipse_at_center,rgba(59,130,246,0.12),transparent_60%)]" />
        <div class="pointer-events-none absolute right-[-120px] top-16 hidden h-64 w-64 rounded-full bg-violet-500/10 blur-3xl lg:block" />
        <div class="pointer-events-none absolute left-[-120px] top-40 hidden h-64 w-64 rounded-full bg-gradient-to-br from-[#ef5da8]/10 to-[#3b82f6]/10 blur-3xl lg:block" />

        <div class="relative px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl py-16 sm:py-24 lg:py-28">
          <div class="mx-auto max-w-3xl text-center">
            <span
              class="inline-flex items-center gap-1.5 rounded-full border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-1 text-xs font-medium"
            >
              <span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded-full brand-gradient-warm">
                <UIcon name="i-lucide-building-2" class="h-2 w-2 text-white" />
              </span>
              <span class="brand-gradient-text-warm">Industry</span>
            </span>
            <h1
              class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-balance text-neutral-900 dark:text-neutral-50"
            >
              {{ industry.name }}
            </h1>
            <p
              class="mx-auto mt-5 max-w-2xl text-base leading-7 sm:text-lg sm:leading-8 text-neutral-600 dark:text-neutral-400 text-pretty"
            >
              {{ industry.meta_description }}
            </p>

            <div class="mt-9 flex flex-col sm:flex-row items-center justify-center gap-3">
              <UButton
                :to="{ name: authenticated ? 'forms-create' : 'forms-create-guest' }"
                trailing-icon="i-lucide-arrow-right"
                label="Create your own form"
                class="premium-primary-button rounded-xl px-5 py-2.5 text-[15px] leading-7 font-semibold text-white"
              />
              <UButton
                :to="{ name: 'templates' }"
                variant="outline"
                color="neutral"
                label="Browse all templates"
                class="rounded-xl px-5 py-2.5 text-[15px] leading-7 font-medium"
              />
            </div>

            <ul
              class="mt-10 flex flex-wrap items-center justify-center gap-x-7 gap-y-3 text-sm font-medium text-neutral-600 dark:text-neutral-300"
            >
              <li class="flex items-center gap-2">
                <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                  <UIcon name="i-lucide-file-text" class="h-2.5 w-2.5 text-white" />
                </span>
                {{ templates.length }} {{ templates.length === 1 ? 'template' : 'templates' }}
              </li>
              <li class="flex items-center gap-2">
                <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                  <UIcon name="i-lucide-check" class="h-2.5 w-2.5 text-white" />
                </span>
                Fully customizable
              </li>
              <li class="flex items-center gap-2">
                <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                  <UIcon name="i-lucide-check" class="h-2.5 w-2.5 text-white" />
                </span>
                Email notifications
              </li>
              <li class="flex items-center gap-2">
                <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                  <UIcon name="i-lucide-check" class="h-2.5 w-2.5 text-white" />
                </span>
                No code required
              </li>
            </ul>
          </div>
        </div>
      </section>
      <!-- END HERO -->

      <!-- START ABOUT -->
      <section class="bg-white py-14 sm:py-20">
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div class="mx-auto max-w-3xl text-center">
            <div
              class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl brand-gradient-warm text-white shadow-md"
            >
              <UIcon name="i-lucide-info" class="h-7 w-7 text-white" />
            </div>
            <h2
              class="mt-7 text-xs font-semibold tracking-widest text-neutral-400 dark:text-neutral-500 uppercase"
            >
              About {{ industry.name }}
            </h2>
            <p
              class="mt-5 font-normal leading-8 text-neutral-600 dark:text-neutral-300 text-left sm:text-center text-pretty"
            >
              {{ industry.description }}
            </p>
          </div>
        </div>
      </section>
      <!-- END ABOUT -->

      <templates-list
        class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto py-12"
        :templates="templates"
        :loading="loading"
        :filter-industries="false"
        :show-industries="false"
      />
    </template>

    <open-form-footer class="mt-8 border-t" />
  </div>
</template>

<script setup>
import { computed } from "vue"
import OpenFormFooter from "../../../components/pages/OpenFormFooter.vue"
import Breadcrumb from "~/components/app/Breadcrumb.vue"
import { useTemplateMeta } from "~/composables/data/useTemplateMeta"
import { resolveSchemaUrl, useSchemaBaseUrl } from '~/composables/useSchemaSeo'

defineRouteRules({
  swr: 3600,
})

const route = useRoute()
const { list } = useTemplates()
const { industries: industriesMap } = useTemplateMeta()

const { data: allTemplates, isLoading: loading, suspense: templatesSuspense } = list()

// Resolve the filtered template list during SSR so the rendered HTML contains
// real crawlable links to every template in this industry.
if (import.meta.server) {
  await templatesSuspense()
}

const { isAuthenticated: authenticated } = useIsAuthenticated()

const industry = computed(() => industriesMap.get(route.params.slug))

// Computed
const templates = computed(() => {
  if (!allTemplates.value) return []
  return allTemplates.value.filter((item) => {
    return item.industries && item.industries.length > 0
      ? item.industries.includes(route.params.slug)
      : false
  })
})
const breadcrumbs = computed(() => {
  if (!industry.value) {
    return [{ route: { name: "templates" }, label: "Templates" }]
  }
  return [
    { route: { name: "templates" }, label: "Templates" },
    { label: industry.value.name },
  ]
})
const schemaBaseUrl = useSchemaBaseUrl()

useOpnSeoMeta({
  title: () => {
    if (!industry.value) return "Form Templates"
    return industry.value.meta_title
  },
  description: () =>
    industry.value
      ? industry.value.meta_description
      : "Browse free, no-code SharaForms templates with unlimited submissions and calculated fields.",
  speakable: ["h1", "p"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "Templates", item: "/templates" },
    { name: () => industry.value?.name || "Industry" },
  ],
})
useHead(() => ({
  titleTemplate: (titleChunk) => {
    // Disable title template for longer titles
    if (
      industry.value &&
      industry.value.meta_title.length < 60 &&
      !industry.value.meta_title.toLowerCase().includes("sharaforms")
    ) {
      return titleChunk
        ? `${titleChunk} - SharaForms`
        : "Form Templates - SharaForms"
    }
    return titleChunk ? titleChunk : "Form Templates - SharaForms"
  },
  script: [
    {
      key: `template-industry-schema:${route.params.slug}`,
      type: 'application/ld+json',
      textContent: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'CollectionPage',
        name: industry.value?.meta_title || 'Form Templates',
        description: industry.value?.meta_description,
        url: resolveSchemaUrl(schemaBaseUrl, `/templates/industries/${route.params.slug}`),
        keywords: `free form templates, ${industry.value?.name || 'industry'} form templates, free form builder, unlimited submissions, calculated fields, no-code form templates`,
        isPartOf: {
          '@id': `${resolveSchemaUrl(schemaBaseUrl, '/templates')}#collection`,
        },
        mainEntity: {
          '@type': 'ItemList',
          numberOfItems: (templates.value || []).length,
          itemListElement: (templates.value || []).slice(0, 24).map((template, index) => ({
            '@type': 'ListItem',
            position: index + 1,
            url: resolveSchemaUrl(schemaBaseUrl, `/templates/${template.slug}`),
            name: template.name,
            description: template.short_description,
          })),
        },
      }),
    },
  ],
}))
</script>

<template>
  <div class="flex flex-col min-h-full">
    <Breadcrumb :path="breadcrumbs" />

    <p
      v-if="type === null || !type"
      class="text-center my-4"
    >
      We could not find this type.
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
                <UIcon name="i-lucide-layout-grid" class="h-2 w-2 text-white" />
              </span>
              <span class="brand-gradient-text-warm">Type</span>
            </span>
            <h1
              class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-balance text-neutral-900 dark:text-neutral-50"
            >
              {{ type.h1 || type.name }}
            </h1>
            <p
              class="mx-auto mt-5 max-w-2xl text-base leading-7 sm:text-lg sm:leading-8 text-neutral-600 dark:text-neutral-400 text-pretty"
            >
              {{ type.meta_description }}
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
              About {{ type.name }}
            </h2>
            <p
              class="mt-5 font-normal leading-8 text-neutral-600 dark:text-neutral-300 text-left sm:text-center text-pretty"
            >
              {{ type.description }}
            </p>
          </div>
        </div>
      </section>
      <!-- END ABOUT -->

      <!-- START WHAT TO INCLUDE -->
      <section
        v-if="type.what_to_include && type.what_to_include.length"
        class="bg-white pb-14 sm:pb-20 -mt-6"
      >
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div class="mx-auto max-w-4xl">
            <h2 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 sm:text-2xl">
              What to include in {{ type.include_label || type.name.toLowerCase() }}
            </h2>
            <ul class="mt-6 grid grid-cols-1 gap-x-8 gap-y-3 sm:grid-cols-2">
              <li
                v-for="(item, itemKey) in type.what_to_include"
                :key="itemKey"
                class="flex items-start gap-3 text-sm leading-6 font-normal text-neutral-600 dark:text-neutral-300"
              >
                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                  <UIcon name="i-lucide-check" class="h-3 w-3 text-white" />
                </span>
                {{ item }}
              </li>
            </ul>
          </div>
        </div>
      </section>
      <!-- END WHAT TO INCLUDE -->

      <!-- START HOW TO -->
      <section
        v-if="type.how_to_steps && type.how_to_steps.length"
        class="bg-neutral-50 py-14 sm:py-20 dark:bg-neutral-950"
      >
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div class="mx-auto max-w-4xl">
            <h2 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 sm:text-2xl">
              How to create{{ type.how_to_label ? ' ' + type.how_to_label : ` ${type.name.toLowerCase().replace(/s$/, '')}` }} in SharaForms
            </h2>
            <ol class="mt-8 space-y-6">
              <li
                v-for="(step, stepKey) in type.how_to_steps"
                :key="stepKey"
                class="flex items-start gap-4"
              >
                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-sm font-bold ring-1 ring-inset ring-neutral-200 brand-gradient-text-warm dark:bg-neutral-900 dark:ring-neutral-700">
                  {{ stepKey + 1 }}
                </span>
                <p class="pt-1 text-sm leading-6 font-normal text-neutral-600 dark:text-neutral-300">
                  {{ step }}
                </p>
              </li>
            </ol>

            <NuxtLink
              v-if="featuredTemplate"
              :to="{ name: 'templates-slug', params: { slug: featuredTemplate.slug } }"
              class="mt-8 flex items-center gap-4 rounded-xl bg-white p-4 ring-1 ring-inset ring-neutral-200 transition-all hover:ring-neutral-300 hover:shadow-sm dark:bg-neutral-900 dark:ring-neutral-700"
            >
              <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg brand-gradient-warm">
                <UIcon name="i-lucide-star" class="h-5 w-5 text-white" />
              </span>
              <span class="text-sm leading-6 text-left">
                <span class="block font-semibold text-neutral-900 dark:text-neutral-100">Most popular pick: {{ featuredTemplate.name }}</span>
                <span class="font-normal text-neutral-500 dark:text-neutral-400">Skip the blank page and start from a ready-made template.</span>
              </span>
              <UIcon name="i-lucide-arrow-right" class="ml-auto h-4 w-4 shrink-0 text-neutral-400" />
            </NuxtLink>
          </div>
        </div>
      </section>
      <!-- END HOW TO -->

      <templates-list
        class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto py-12"
        :templates="templates"
        :loading="loading"
        :filter-types="false"
        :show-industries="false"
      />

      <!-- START PRACTICES -->
      <section
        v-if="(type.best_practices && type.best_practices.length) || (type.common_mistakes && type.common_mistakes.length)"
        class="bg-neutral-50 border-t border-neutral-200 py-14 sm:py-20 dark:bg-neutral-950 dark:border-neutral-800"
      >
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div class="mx-auto grid max-w-5xl grid-cols-1 gap-10 md:grid-cols-2 md:gap-x-12">
            <div
              v-if="type.best_practices && type.best_practices.length"
            >
              <h2 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-neutral-50 sm:text-xl">
                Best practices for building {{ type.name.toLowerCase() }}
              </h2>
              <ul class="mt-5 space-y-3">
                <li
                  v-for="(item, itemKey) in type.best_practices"
                  :key="'bp-' + itemKey"
                  class="flex items-start gap-3 text-sm leading-6 font-normal text-neutral-600 dark:text-neutral-300"
                >
                  <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                    <UIcon name="i-lucide-check" class="h-3 w-3 text-white" />
                  </span>
                  {{ item }}
                </li>
              </ul>
            </div>
            <div
              v-if="type.common_mistakes && type.common_mistakes.length"
            >
              <h2 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-neutral-50 sm:text-xl">
                Common mistakes to avoid
              </h2>
              <ul class="mt-5 space-y-3">
                <li
                  v-for="(item, itemKey) in type.common_mistakes"
                  :key="'cm-' + itemKey"
                  class="flex items-start gap-3 text-sm leading-6 font-normal text-neutral-600 dark:text-neutral-300"
                >
                  <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-950">
                    <UIcon name="i-lucide-x" class="h-3 w-3 text-red-600 dark:text-red-400" />
                  </span>
                  {{ item }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>
      <!-- END PRACTICES -->

      <!-- START FAQ -->
      <section
        v-if="type.faqs && type.faqs.length"
        class="bg-white border-t border-neutral-200 py-14 sm:py-20 dark:border-neutral-800"
      >
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div class="mx-auto max-w-3xl">
            <h2 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-50 sm:text-2xl">
              Frequently asked questions about {{ type.name.toLowerCase() }}
            </h2>
            <dl class="mt-8 space-y-8">
              <div
                v-for="(faq, faqKey) in type.faqs"
                :key="faqKey"
              >
                <dt class="font-semibold leading-6 text-neutral-900 dark:text-neutral-100">
                  {{ faq.question }}
                </dt>
                <dd class="mt-2 leading-7 font-normal text-neutral-600 dark:text-neutral-400">
                  {{ faq.answer }}
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </section>
      <!-- END FAQ -->
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
const { types: typesMap } = useTemplateMeta()

const { data: allTemplates, isLoading: loading, suspense: templatesSuspense } = list()

// Resolve the filtered template list during SSR so the rendered HTML contains
// real crawlable links to every template in this category.
if (import.meta.server) {
  await templatesSuspense()
}

const { isAuthenticated: authenticated } = useIsAuthenticated()

const type = computed(() => typesMap.get(route.params.slug))

if (!type.value) {
  throw createError({ statusCode: 404, statusMessage: 'Template type page not found' })
}

// Computed
const templates = computed(() => {
  if (!allTemplates.value) return []
  return allTemplates.value.filter((item) => {
    return item.types && item.types.length > 0
      ? item.types.includes(route.params.slug)
      : false
  })
})

// Contextual hub -> detail internal link (server-rendered, crawlable).
const featuredTemplate = computed(() => {
  if (!type.value?.featured_template || !allTemplates.value) return null
  return allTemplates.value.find(
    (t) => t.slug === type.value.featured_template,
  ) || null
})
const breadcrumbs = computed(() => {
  if (!type.value) {
    return [{ route: { name: "templates" }, label: "Templates" }]
  }
  return [
    { route: { name: "templates" }, label: "Templates" },
    { label: type.value.name },
  ]
})
const schemaBaseUrl = useSchemaBaseUrl()

useOpnSeoMeta({
  title: () => {
    if (!type.value) return "Form Templates"
    return type.value.meta_title
  },
  description: () =>
    type.value
      ? type.value.meta_description
      : "Browse free, no-code SharaForms templates with built-in calculations, conditional logic, and unlimited submissions.",
  ogImage: () =>
    type.value
      ? `/og/type/${encodeURIComponent(route.params.slug)}?title=${encodeURIComponent(type.value.h1 || type.value.name)}&desc=${encodeURIComponent((type.value.meta_description || '').slice(0, 120))}`
      : '/share-preview.jpg',
  speakable: ["h1", "p"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "Templates", item: "/templates" },
    { name: () => type.value?.name || "Type" },
  ],
})
useHead(() => ({
  titleTemplate: (titleChunk) => {
    // Disable title template for longer titles
    if (
      type.value &&
      type.value.meta_title.length < 60 &&
      !type.value.meta_title.toLowerCase().includes("sharaforms")
    ) {
      return titleChunk
        ? `${titleChunk} - SharaForms`
        : "Form Templates - SharaForms"
    }
    return titleChunk ? titleChunk : "Form Templates - SharaForms"
  },
  script: [
    {
      key: `template-type-schema:${route.params.slug}`,
      type: 'application/ld+json',
      textContent: JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'CollectionPage',
        name: type.value?.meta_title || 'Form Templates',
        description: type.value?.meta_description,
        url: resolveSchemaUrl(schemaBaseUrl, `/templates/types/${route.params.slug}`),
        keywords: `free form templates, ${type.value?.name || 'form'} templates, free form builder, unlimited submissions, built-in calculations, no-code form templates`,
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
    ...(type.value?.faqs && type.value.faqs.length
      ? [{
          key: `template-type-faq-schema:${route.params.slug}`,
          type: 'application/ld+json',
          textContent: JSON.stringify({
            '@context': 'https://schema.org',
            '@type': 'FAQPage',
            mainEntity: type.value.faqs.map((faq) => ({
              '@type': 'Question',
              name: faq.question,
              acceptedAnswer: {
                '@type': 'Answer',
                text: faq.answer,
              },
            })),
          }),
        }]
      : []),
  ],
}))
</script>

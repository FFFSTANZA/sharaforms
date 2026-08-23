<template>
  <div class="flex flex-col min-h-full">
    <section class="relative overflow-hidden -mt-[76px] pt-[132px] sm:pt-[156px] pb-14 sm:pb-20 bg-[#292438]">
      <div class="px-8 lg:px-12 max-w-7xl mx-auto">
        <div class="text-center max-w-3xl mx-auto">
          <div
            class="inline-flex items-center gap-1.5 rounded-full border border-white/12 bg-white/6 px-3 py-1 text-xs font-medium text-white/65"
          >
            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full brand-gradient-warm">
              <UIcon name="i-lucide-layout-grid" class="h-2.5 w-2.5 text-white" />
            </span>
            Template Gallery
          </div>
          <h1
            class="mt-5 text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold text-white"
          >
            Free Form Templates
          </h1>
          <p class="text-white/55 mt-4 text-lg font-normal">
             Start with a beautiful, no-code form template, then add unlimited
            submissions, built-in calculations, conditional logic, and
            signatures. One form. Three modes.
          </p>
        </div>
      </div>
      <svg
        class="pointer-events-none absolute -bottom-px left-0 h-[60px] w-full sm:h-[90px]"
        viewBox="0 0 1440 120"
        preserveAspectRatio="none"
        aria-hidden="true"
      >
        <path
          d="M0 52C150 108 260 10 430 54C610 102 720 120 880 62C1050 0 1170 96 1440 42V120H0Z"
          fill="#ffffff"
        />
      </svg>
    </section>

    <templates-list
      class="px-8 lg:px-12 max-w-7xl mx-auto py-12"
      :templates="templates"
      :loading="loading"
    />

    <open-form-footer class="mt-8 border-t" />
  </div>
</template>

<script setup>
import { resolveSchemaUrl, useSchemaBaseUrl } from '~/composables/useSchemaSeo'

defineRouteRules({
  swr: 3600,
})

const { data: templates, isLoading: loading, suspense: templatesSuspense } = useTemplates().list()

// Ensure the template cards render in the server-rendered HTML so the gallery
// exposes crawlable internal links to every template page.
if (import.meta.server) {
  await templatesSuspense()
}

useOpnSeoMeta({
  title: "Free Form Templates with Built-In Calculations and Logic",
  description:
    "Browse 50+ free, no-code form templates for registrations, applications, surveys, orders, events, and feedback. Every template supports built-in calculations, conditional logic, signatures, and unlimited submissions.",
  keywords: "free form templates, free form builder, unlimited submissions, form templates with built-in calculations, no-code form templates, online form templates",
  speakable: ["h1", "p"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "Templates" },
  ],
})

const schemaBaseUrl = useSchemaBaseUrl()

const templatesCollectionSchema = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'CollectionPage',
  name: 'SharaForms Free Form Templates',
  description: 'Browse SharaForms free, no-code form templates for registrations, applications, feedback, lead capture, and workflow forms. Every template supports built-in calculations, conditional logic, signatures, and unlimited submissions.',
  url: resolveSchemaUrl(schemaBaseUrl, '/templates'),
  keywords: 'free form templates, free form builder, unlimited submissions, no-code form templates, form templates with built-in calculations',
  isFamilyFriendly: true,
  inLanguage: 'en',
  isPartOf: {
    '@id': `${resolveSchemaUrl(schemaBaseUrl, '/')}#website`,
  },
  creator: {
    '@id': `${resolveSchemaUrl(schemaBaseUrl, '/')}#organization`,
  },
  publisher: {
    '@id': `${resolveSchemaUrl(schemaBaseUrl, '/')}#organization`,
  },
  mainEntity: {
    '@type': 'ItemList',
    numberOfItems: templates.value?.length || 0,
    itemListElement: (templates.value || []).slice(0, 24).map((template, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      url: resolveSchemaUrl(schemaBaseUrl, `/templates/${template.slug}`),
      name: template.name,
      description: template.short_description,
    })),
  },
}))

useHead(() => ({
  script: [
    {
      key: 'templates-collection-schema',
      type: 'application/ld+json',
      textContent: JSON.stringify(templatesCollectionSchema.value),
    },
  ],
}))
</script>

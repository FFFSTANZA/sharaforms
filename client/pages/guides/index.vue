<template>
  <div class="marketing-page">
    <section class="relative overflow-hidden bg-[#292438] -mt-[76px]">
      <div class="relative pt-[124px] sm:pt-[156px] pb-24 sm:pb-32 px-6 lg:px-12">
        <div class="max-w-3xl mx-auto text-center">
          <p
            class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/6 px-3 py-1 text-sm text-white/65"
          >
            <span
              class="rounded-md bg-white/12 px-1.5 py-0.5 text-[11px] font-semibold leading-3 tracking-wide text-white"
            >
              GUIDES
            </span>
            Practical playbooks, no fluff
          </p>
          <h1
            class="text-white text-4xl sm:text-[52px] sm:leading-[1.1] tracking-[-1%] font-semibold mt-6"
          >
            Form guides that answer
            <span class="brand-gradient-text">real questions</span>
          </h1>
          <p class="text-white/55 text-lg sm:text-xl leading-8 max-w-2xl mx-auto mt-4">
            Layout choices, scoring mechanics, conditional logic patterns, and
            deployment decisions, written from the product rather than around it.
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
          fill="#fcfcfd"
        />
      </svg>
    </section>

    <section class="px-6 lg:px-12 py-16">
      <div class="mx-auto w-full max-w-266">
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
          <TrackClick
            v-for="(guide, index) in guides"
            :key="guide.slug"
            name="guide_hub_card_click"
            :properties="{ slug: guide.slug, category: guide.category, title: guide.title, position: index }"
          >
            <NuxtLink
              :to="`/guides/${guide.slug}`"
              class="group flex h-full flex-col rounded-xl border border-neutral-200 p-6 transition-colors hover:border-pink-300 hover:bg-neutral-50"
            >
            <div class="flex items-center justify-between gap-3">
              <span class="text-[11px] font-semibold uppercase tracking-wider text-pink-600">
                {{ guide.category }}
              </span>
              <span class="text-sm text-neutral-400">{{ guide.readingMinutes }} min</span>
            </div>
            <h2 class="mt-3 text-lg font-semibold leading-snug tracking-tight text-neutral-900 group-hover:text-pink-700 transition-colors">
              {{ guide.title }}
            </h2>
            <p class="mt-2.5 text-[15px] leading-7 text-neutral-600 line-clamp-3">
              {{ guide.description }}
            </p>
            <span
              class="mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-medium text-neutral-500 group-hover:text-pink-600 transition-colors"
            >
              Read the guide
              <UIcon
                name="i-heroicons:arrow-right"
                class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5"
              />
            </span>
          </NuxtLink>
          </TrackClick>
        </div>

        <div class="mt-16 border-t border-neutral-200 pt-10 flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-xl font-semibold tracking-tight text-neutral-900">
              Try the ideas on a real form
            </h2>
            <p class="mt-1.5 max-w-md text-[15px] leading-6 text-neutral-600">
              Every technique in these guides works on the free plan with
              unlimited forms and submissions.
            </p>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <TrackClick name="guide_hub_cta_create_form" :properties="{ authenticated }">
              <UButton
                size="lg"
                :to="{ name: authenticated ? 'forms-create' : 'forms-create-guest' }"
                trailing-icon="i-heroicons:arrow-up-right"
                label="Create a free form"
                class="premium-primary-button px-5 py-3 rounded-xl text-[15px] font-semibold text-white"
              />
            </TrackClick>
            <TrackClick name="guide_hub_cta_browse_templates">
              <UButton
                size="lg"
                to="/templates"
                label="Browse templates"
                variant="outline"
                color="neutral"
                class="px-5 py-3 rounded-xl text-[15px] font-medium"
              />
            </TrackClick>
          </div>
        </div>
      </div>
    </section>

    <OpenFormFooter />
  </div>
</template>

<script setup>
import OpenFormFooter from '~/components/pages/OpenFormFooter.vue'
import TrackClick from '~/components/global/TrackClick.vue'
import { guides } from '~/data/guides/index.js'
import { useIsAuthenticated } from '~/composables/useAuthFlow'

definePageMeta({
  layout: 'default',
})

const { isAuthenticated: authenticated } = useIsAuthenticated()
const { logEvent } = usePostHog()

onMounted(() => {
  logEvent('guide_hub_viewed', {
    guide_count: guides.length,
    categories: [...new Set(guides.map(g => g.category))],
  })
})

useOpnSeoMeta({
  title: 'Form Guides: Logic, Calculations & Design Decisions',
  description:
    'Practical form guides covering layout trade-offs, quiz scoring, conditional logic patterns, source tracking, PDF automation, and survey question design.',
  ogImage: '/share-preview.jpg',
  speakable: ['h1', '.marketing-page > section:nth-of-type(2) p'],
  breadcrumbs: [
    { name: 'Home', item: '/' },
    { name: 'Guides' },
  ],
})

const guidesHubSchema = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'CollectionPage',
  name: 'SharaForms Form Guides',
  description:
    'Practical guides about form design decisions, calculations, conditional logic, tracking, document automation, and deployment options.',
  url: `${useRuntimeConfig().public.appUrl.replace(/\/+$/, '')}/guides`,
  mainEntity: {
    '@type': 'ItemList',
    numberOfItems: guides.length,
    itemListElement: guides.map((guide, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      url: `${useRuntimeConfig().public.appUrl.replace(/\/+$/, '')}/guides/${guide.slug}`,
      name: guide.title,
    })),
  },
}))

useHead({
  script: [
    {
      key: 'guides-hub-schema',
      type: 'application/ld+json',
      textContent: JSON.stringify(guidesHubSchema.value),
    },
  ],
})
</script>

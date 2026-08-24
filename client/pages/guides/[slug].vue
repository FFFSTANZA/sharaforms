<template>
  <div v-if="guide" class="marketing-page">
    <section class="relative overflow-hidden bg-[#292438] -mt-[76px]">
      <div class="relative pt-[124px] sm:pt-[156px] pb-24 sm:pb-32 px-6 lg:px-12">
        <div class="max-w-3xl mx-auto">
          <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm text-white/50">
            <NuxtLink to="/" class="hover:text-white/80 transition-colors">Home</NuxtLink>
            <UIcon name="i-heroicons:chevron-right" class="h-3 w-3" />
            <NuxtLink to="/guides" class="hover:text-white/80 transition-colors">Guides</NuxtLink>
            <UIcon name="i-heroicons:chevron-right" class="h-3 w-3" />
            <span class="text-white/70">{{ guide.category }}</span>
          </nav>
          <h1
            class="text-white text-3xl sm:text-[40px] sm:leading-[1.15] tracking-[-1%] font-semibold mt-5"
          >
            {{ guide.title }}
          </h1>
          <p class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-white/50">
            <span>By SharaForms Team</span>
            <span aria-hidden="true">·</span>
            <span>{{ guide.readingMinutes }} min read</span>
            <span aria-hidden="true">·</span>
            <span>Updated {{ updatedLabel }}</span>
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

    <article class="px-6 pb-20">
      <div class="mx-auto w-full max-w-[46rem]">

        <!-- Direct answer up front: survives snippet and AI extraction -->
        <div class="pt-12">
          <p
            v-for="(para, i) in guide.intro"
            :key="i"
            :class="i === 0 ? 'text-lg leading-8 text-neutral-800' : 'mt-5 text-base leading-8 text-neutral-700'"
          >
            {{ para }}
          </p>
        </div>

        <nav
          v-if="tocItems.length"
          aria-label="In this guide"
          class="mt-10 rounded-xl border border-neutral-200 bg-neutral-50 px-6 py-5"
        >
          <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500">In this guide</p>
          <ol class="mt-3 space-y-2">
            <li v-for="(item, i) in tocItems" :key="item.id" class="flex items-start gap-2.5 text-sm leading-6">
              <span class="font-medium text-neutral-400 tabular-nums">{{ i + 1 }}.</span>
              <a :href="`#${item.id}`" class="text-neutral-700 hover:text-pink-600 transition-colors">
                {{ item.text }}
              </a>
            </li>
          </ol>
        </nav>

        <div class="mt-12 guide-prose space-y-6">
          <template v-for="(block, i) in guide.sections" :key="i">
            <h2
              v-if="block.type === 'h2'"
              :id="anchorize(block.text)"
              class="scroll-mt-28 pt-8 text-[22px] sm:text-2xl font-semibold tracking-tight text-neutral-900"
            >
              {{ block.text }}
            </h2>

            <p v-else-if="block.type === 'p'" class="text-base leading-8 text-neutral-700" v-html="fmt(block.text)" />

            <ul v-else-if="block.type === 'ul'" class="space-y-2.5">
              <li
                v-for="(item, j) in block.items"
                :key="j"
                class="flex items-start gap-3 text-base leading-7 text-neutral-700"
              >
                <span class="mt-[11px] h-1.5 w-1.5 shrink-0 rounded-full bg-pink-500" aria-hidden="true" />
                <span v-html="fmt(item)" />
              </li>
            </ul>

            <ol v-else-if="block.type === 'ol'" class="space-y-2.5">
              <li
                v-for="(item, j) in block.items"
                :key="j"
                class="flex items-start gap-3 text-base leading-7 text-neutral-700"
              >
                <span class="w-5 shrink-0 text-right font-medium text-neutral-900 tabular-nums">{{ j + 1 }}.</span>
                <span v-html="fmt(item)" />
              </li>
            </ol>

            <ol v-else-if="block.type === 'steps'" class="relative ml-3 space-y-7 border-l border-neutral-200 pl-7">
              <li v-for="(step, j) in block.items" :key="j" class="relative">
                <span
                  class="absolute -left-[41px] flex h-6 w-6 items-center justify-center rounded-full bg-neutral-900 text-[11px] font-semibold text-white"
                >
                  {{ j + 1 }}
                </span>
                <h3 class="text-base font-semibold text-neutral-900">{{ step.title }}</h3>
                <p class="mt-1.5 text-[15px] leading-7 text-neutral-600" v-html="fmt(step.text)" />
              </li>
            </ol>

            <div
              v-else-if="block.type === 'table'"
              class="overflow-x-auto rounded-xl border border-neutral-200"
            >
              <table class="w-full min-w-125 border-collapse text-left text-sm">
                <thead>
                  <tr class="bg-neutral-50">
                    <th
                      v-for="(cell, j) in block.head"
                      :key="j"
                      class="border-b border-neutral-200 px-4 py-3 font-semibold text-neutral-900"
                    >
                      {{ cell }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, j) in block.rows"
                    :key="j"
                    class="border-b border-neutral-100 last:border-b-0"
                  >
                    <td
                      v-for="(cell, k) in row"
                      :key="k"
                      class="px-4 py-3 align-top leading-6"
                      :class="k === 0 ? 'font-medium text-neutral-900' : 'text-neutral-600'"
                    >
                      {{ cell }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <aside
              v-else-if="block.type === 'callout'"
              class="rounded-r-xl border-l-2 border-pink-500 bg-neutral-50 px-6 py-5"
            >
              <h3 class="text-sm font-semibold uppercase tracking-wide text-neutral-900">{{ block.title }}</h3>
              <p class="mt-1.5 text-[15px] leading-7 text-neutral-700">{{ block.text }}</p>
            </aside>
          </template>
        </div>

        <section v-if="templateLinks.length" class="mt-16 border-t border-neutral-200 pt-10">
          <h2 class="text-xl font-semibold tracking-tight text-neutral-900">
            Templates to start from
          </h2>
          <ul class="mt-4 divide-y divide-neutral-100 border border-neutral-200 rounded-xl">
            <li v-for="tpl in templateLinks" :key="tpl.slug">
              <TrackClick
                name="guide_template_link_click"
                :properties="{ slug: guide.slug, template_slug: tpl.slug, template_label: tpl.label }"
              >
                <NuxtLink
                  :to="`/templates/${tpl.slug}`"
                  class="group flex items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-neutral-50"
                >
                  <span class="text-[15px] font-medium text-neutral-900">
                    {{ tpl.label }} template
                  </span>
                  <UIcon
                    name="i-heroicons:arrow-up-right"
                    class="h-4 w-4 shrink-0 text-neutral-400 transition-colors group-hover:text-pink-600"
                  />
                </NuxtLink>
              </TrackClick>
            </li>
          </ul>
        </section>

        <section v-if="guide.faqs?.length" class="mt-16 border-t border-neutral-200 pt-10">
          <h2 class="text-xl font-semibold tracking-tight text-neutral-900">
            Frequently asked questions
          </h2>
          <dl class="mt-6 space-y-7">
            <div v-for="(faq, i) in guide.faqs" :key="i">
              <dt class="text-[15px] font-semibold text-neutral-900">
                {{ faq.question }}
              </dt>
              <dd class="mt-2 text-[15px] leading-7 text-neutral-600">
                {{ faq.answer }}
              </dd>
            </div>
          </dl>
        </section>

        <section class="mt-16 border-t border-neutral-200 pt-10">
          <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 class="text-xl font-semibold tracking-tight text-neutral-900">
                Put this to work on a real form
              </h2>
              <p class="mt-1.5 max-w-md text-[15px] leading-6 text-neutral-600">
                Free plan, unlimited forms and submissions, all three
                presentation modes included.
              </p>
            </div>
            <TrackClick
              name="guide_detail_cta_create_form"
              :properties="{ slug: guide.slug, authenticated }"
            >
              <UButton
                size="lg"
                :to="{ name: authenticated ? 'forms-create' : 'forms-create-guest' }"
                trailing-icon="i-heroicons:arrow-up-right"
                label="Create a free form"
                class="premium-primary-button shrink-0 px-5 py-3 rounded-xl text-[15px] font-semibold text-white"
              />
            </TrackClick>
          </div>
        </section>

        <section v-if="relatedGuides.length" class="mt-16 border-t border-neutral-200 pt-10">
          <h2 class="text-xl font-semibold tracking-tight text-neutral-900">Keep reading</h2>
          <div class="mt-5 grid gap-4 sm:grid-cols-3">
            <TrackClick
              v-for="(related, idx) in relatedGuides"
              :key="related.slug"
              name="guide_related_click"
              :properties="{ slug: guide.slug, related_slug: related.slug, related_category: related.category, position: idx }"
            >
              <NuxtLink
                :to="`/guides/${related.slug}`"
                class="group rounded-xl border border-neutral-200 p-5 transition-colors hover:border-pink-300 hover:bg-neutral-50"
              >
                <span class="text-[11px] font-semibold uppercase tracking-wider text-pink-600">
                  {{ related.category }}
                </span>
                <h3 class="mt-2 text-[15px] font-semibold leading-snug text-neutral-900 group-hover:text-pink-700 transition-colors">
                  {{ related.title }}
                </h3>
                <span class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-neutral-500">
                  Read next
                  <UIcon name="i-heroicons:arrow-right" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
                </span>
              </NuxtLink>
            </TrackClick>
          </div>
        </section>
      </div>
    </article>

    <OpenFormFooter />
  </div>
</template>

<script setup>
import OpenFormFooter from '~/components/pages/OpenFormFooter.vue'
import TrackClick from '~/components/global/TrackClick.vue'
import { getGuideBySlug, getRelatedGuides, getGuideTemplateLinks } from '~/data/guides/index.js'
import { useIsAuthenticated } from '~/composables/useAuthFlow'

definePageMeta({
  layout: 'default',
})

const route = useRoute()
const config = useRuntimeConfig()
const { isAuthenticated: authenticated } = useIsAuthenticated()
const { logEvent } = usePostHog()

// Resolve once; unknown slugs must return a real 404 to crawlers.
const guide = computed(() => getGuideBySlug(route.params.slug))

if (import.meta.server && !guide.value) {
  throw createError({ statusCode: 404, statusMessage: 'Guide not found' })
}

const baseUrl = computed(() => (config.public.appUrl || '').replace(/\/+$/, ''))

const updatedLabel = computed(() => {
  const date = new Date('2026-08-22T00:00:00Z')
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' })
})

useOpnSeoMeta(
  computed(() => ({
    title: guide.value?.title,
    description: guide.value?.description,
    ogImage: '/share-preview.jpg',
    speakable: ['h1'],
    keywords: [
      guide.value?.slug?.replace(/-/g, ' '),
      'sharaforms guide',
      'form builder guide',
    ].filter(Boolean),
    breadcrumbs: [
      { name: 'Home', item: '/' },
      { name: 'Guides', item: '/guides' },
      { name: guide.value?.title || 'Guide' },
    ],
  })),
)

function anchorize (text) {
  return String(text)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

// Content is authored locally in data/guides, so inline markup is trusted.
// Only bold markers are expanded; everything else renders as plain text.
function fmt (text) {
  return String(text).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
}

const tocItems = computed(() => {
  return (guide.value?.sections || [])
    .filter((block) => block.type === 'h2')
    .map((block) => ({ id: anchorize(block.text), text: block.text }))
})

const relatedGuides = computed(() => getRelatedGuides(route.params.slug, 3))
const templateLinks = computed(() => getGuideTemplateLinks(route.params.slug))

onMounted(() => {
  if (!guide.value) return
  logEvent('guide_detail_viewed', {
    slug: guide.value.slug,
    category: guide.value.category,
    reading_minutes: guide.value.readingMinutes,
    section_count: guide.value.sections?.length || 0,
    faq_count: guide.value.faqs?.length || 0,
    template_link_count: templateLinks.value.length,
    has_toc: tocItems.value.length > 0,
  })
})

const articleSchema = computed(() => {
  if (!guide.value) {
    return null
  }
  const url = `${baseUrl.value}/guides/${guide.value.slug}`
  return {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: guide.value.title,
    description: guide.value.description,
    url,
    mainEntityOfPage: url,
    datePublished: '2026-08-22',
    dateModified: '2026-08-22',
    author: { '@type': 'Organization', name: 'SharaForms' },
    publisher: { '@id': `${baseUrl.value}/#organization` },
    articleSection: guide.value.category,
  }
})

const faqSchema = computed(() => {
  if (!guide.value?.faqs?.length) {
    return null
  }
  return {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    url: `${baseUrl.value}/guides/${guide.value.slug}`,
    mainEntity: guide.value.faqs.map((faq) => ({
      '@type': 'Question',
      name: faq.question,
      acceptedAnswer: { '@type': 'Answer', text: faq.answer },
    })),
  }
})

useHead({
  script: [
    ...(articleSchema.value
      ? [{
          key: 'guide-article-schema',
          type: 'application/ld+json',
          textContent: JSON.stringify(articleSchema.value),
        }]
      : []),
    ...(faqSchema.value
      ? [{
          key: 'guide-faq-schema',
          type: 'application/ld+json',
          textContent: JSON.stringify(faqSchema.value),
        }]
      : []),
  ],
})
</script>

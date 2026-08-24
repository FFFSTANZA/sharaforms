<template>
  <div class="marketing-page">
    <section>
      <div class="relative">
        <div class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 relative z-2">
          <div class="max-w-3xl mx-auto text-center">
            <h1
              class="brand-text-strong text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold"
            >
              SharaForms Alternatives &amp; Comparisons
            </h1>
            <p
              class="brand-text-muted my-8 text-lg sm:text-xl leading-7 tracking-[-1.5%] sm:leading-8 font-normal"
            >
              Compare SharaForms side by side with every major form builder.
               See free plans, pricing, and features at a glance, and find out
              which alternative gives you unlimited forms, unlimited
              submissions, and three presentation modes.
            </p>
            <div
              class="flex flex-col sm:flex-row items-center justify-center gap-4"
            >
              <UButton
                size="lg"
                :to="{ name: 'forms-create' }"
                trailing-icon="i-lucide-arrow-up-right"
                label="Start building free"
                class="brand-button-primary w-fit pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
              />
            </div>
          </div>
        </div>
        <div class="brand-section-wash w-full h-full absolute inset-0"></div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <h2
            class="brand-text-strong text-4xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%]"
          >
            Form builder comparisons
          </h2>
          <p
            class="brand-text-muted mt-4 text-base tracking-[-1.1%] font-medium leading-8"
          >
            Each page breaks down free-plan limits, pricing as usage grows, and
             the features that matter most, so you can pick the right
            alternative with confidence.
          </p>
        </div>

        <div class="mt-12 sm:mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <TrackClick
            v-for="(comparison, index) in comparisons"
            :key="comparison.slug"
            name="comparison_card_click"
            :properties="{ slug: comparison.slug, competitor: comparison.label, position: index }"
          >
            <NuxtLink
              :to="`/sharaforms-vs-${comparison.slug}`"
              class="group rounded-3xl border border-gray-200 bg-white p-8 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-[0_18px_45px_-28px_rgba(37,99,235,0.45)]"
            >
              <div class="flex items-center justify-between">
                <div class="text-xl leading-7 font-semibold text-gray-950">
                  vs {{ comparison.label }}
                </div>
                <UIcon
                  name="i-lucide-arrow-up-right"
                  class="h-5 w-5 text-gray-400 group-hover:text-blue-600"
                />
              </div>
              <p class="mt-3 text-base leading-7 tracking-[-1.1%] font-medium text-gray-600">
                {{ comparison.description }}
              </p>
            </NuxtLink>
          </TrackClick>
        </div>
      </div>
    </section>

    <section class="pb-14 sm:pb-28 px-4 sm:px-8 lg:px-12">
      <div class="max-w-3xl mx-auto">
        <div class="comparison-bottom-line brand-surface rounded-2xl px-6 sm:px-8 py-6 sm:py-7 border brand-divider">
          <h2 class="brand-text-strong text-lg leading-6 font-semibold tracking-[-0.5%]">
            Bottom line
          </h2>
          <p class="brand-text-muted mt-3 text-base sm:text-lg leading-7 sm:leading-8 tracking-[-1.1%] font-normal">
            SharaForms is the form builder that stays free at scale: unlimited
            forms, unlimited submissions, three presentation modes, built-in
            calculations, dynamic PDFs, and custom domains, without the caps
            or upgrade pressure that other builders add as your forms grow.
          </p>
        </div>
      </div>
    </section>

    <OpenFormFooter />
  </div>
</template>

<script setup>
import OpenFormFooter from "~/components/pages/OpenFormFooter.vue"
import TrackClick from "~/components/global/TrackClick.vue"

definePageMeta({
  layout: "default",
})

const { logEvent } = usePostHog()

onMounted(() => {
  logEvent('comparison_hub_viewed', {
    competitor_count: 10,
  })
})

useOpnSeoMeta({
  title: "SharaForms vs Typeform, Jotform & Google Forms",
  description: "Compare SharaForms with Typeform, Jotform, Google Forms, Tally, Fillout, HeyForm, Youform, and more. Free plans, pricing, and features compared.",
  ogImage: '/share-preview.jpg',
  speakable: ["h1", ".marketing-page > section:nth-of-type(2) p"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "Alternatives & Comparisons" },
  ],
  keywords: [
    'sharaforms alternatives',
    'typeform alternative',
    'jotform alternative',
    'google forms alternative',
    'tally alternative',
    'form builder comparison',
    'free form builder',
    'unlimited forms',
    'unlimited submissions',
    'spotlight forms',
    'one question at a time forms',
    'multi-page forms',
  ].join(', '),
})

const comparisons = [
  {
    slug: 'typeform',
    label: 'Typeform',
    description: "The flexible Typeform alternative, polished conversational forms with unlimited responses, no free-tier caps, and custom branding.",
  },
  {
    slug: 'jotform',
    label: 'JotForm',
    description: "The cleaner JotForm alternative with stronger workflow control and far fewer usage limits as your forms grow.",
  },
  {
    slug: 'googleforms',
    label: 'Google Forms',
    description: "The more capable Google Forms alternative with stronger customization, richer workflows, and better control.",
  },
  {
    slug: 'tally',
    label: 'Tally',
    description: "The more powerful Tally alternative with deeper customization, built-in calculations, and better workflow control.",
  },
  {
    slug: 'fillout',
    label: 'Fillout',
    description: "The more scalable Fillout alternative with unlimited responses, stronger control, and more room to grow.",
  },
  {
    slug: 'heyform',
    label: 'HeyForm',
    description: "The more capable HeyForm alternative with stronger workflow control and no submission ceiling.",
  },
  {
    slug: 'youform',
    label: 'Youform',
    description: "The more extensible Youform alternative with deeper logic, stronger customization, and better control.",
  },
  {
    slug: 'formbricks',
    label: 'Formbricks',
    description: "The broader Formbricks alternative for teams that need more than surveys and in-app feedback.",
  },
  {
    slug: 'formio',
    label: 'Form.io',
    description: "The easier Form.io alternative with polished workflows and less engineering overhead.",
  },
  {
    slug: '123formbuilder',
    label: '123FormBuilder',
    description: "The modern 123FormBuilder alternative with stronger control, flexible workflows, and room to scale.",
  },
]

const comparisonsSchema = computed(() => {
  const route = useRoute()
  const config = useRuntimeConfig()
  const baseUrl = (config.public.appUrl && config.public.appUrl !== '/')
    ? config.public.appUrl.replace(/\/+$/, '')
    : ''
  const canonicalUrl = baseUrl ? `${baseUrl}${route.path}` : route.path

  return {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'ItemList',
        '@id': `${canonicalUrl}#comparisons`,
        name: 'SharaForms alternatives and form builder comparisons',
        numberOfItems: comparisons.length,
        itemListElement: comparisons.map((comparison, index) => ({
          '@type': 'ListItem',
          position: index + 1,
          url: `${baseUrl}/sharaforms-vs-${comparison.slug}`,
          name: `SharaForms vs ${comparison.label}`,
          description: comparison.description,
        })),
      },
      {
        '@type': 'FAQPage',
        '@id': `${canonicalUrl}#faq`,
        mainEntity: [
          {
            '@type': 'Question',
            name: 'Is SharaForms a good Typeform alternative?',
            acceptedAnswer: {
              '@type': 'Answer',
              text: 'Yes. SharaForms offers the polished conversational form experience Typeform is known for, but with unlimited forms and submissions on the free plan, three presentation modes, built-in calculations, custom branding, and no upgrade pressure as usage grows.',
            },
          },
          {
            '@type': 'Question',
            name: 'Which free form builder has unlimited submissions?',
            acceptedAnswer: {
              '@type': 'Answer',
              text: 'SharaForms offers a free plan with unlimited forms and unlimited submissions, along with three presentation modes, built-in calculations, conditional logic, and dynamic PDF generation.',
            },
          },
          {
            '@type': 'Question',
            name: 'How much does SharaForms cost?',
            acceptedAnswer: {
              '@type': 'Answer',
              text: 'SharaForms is free for unlimited forms and submissions. Paid plans start at $15 per month (Pro), with Business at $48 and Enterprise at $125 per month, billed monthly.',
            },
          },
        ],
      },
    ],
  }
})

useHead({
  script: [
    {
      key: 'comparisons-hub-schema',
      type: 'application/ld+json',
      textContent: JSON.stringify(comparisonsSchema.value),
    },
  ],
})
</script>

<template>
  <div class="flex flex-col min-h-full">
    <Breadcrumb
      v-if="template"
      :path="breadcrumbs"
    >
      <template #left>
        <div
          v-if="canEditTemplate"
          class="ml-5"
        >
          <UButton
            color="neutral"
            size="sm"
            @click.prevent="showFormTemplateModal = true"
            label="Edit Template"
          />
          <form-template-modal
            v-if="form"
            :form="form"
            :template="template"
            :show="showFormTemplateModal"
            @close="showFormTemplateModal = false"
          />
        </div>
      </template>
      <template #right>
        <TrackClick
          v-if="canEditTemplate"
          name="copy_template_button_clicked"
          class="mr-5"
        >
          <UButton
            size="sm"
            variant="outline"
            @click.prevent="copyTemplateUrl"
            label="Copy Template URL"
          />
        </TrackClick>
        <TrackClick
          name="use_template_button_clicked"
          class="mr-5"
        >
          <UButton
            size="sm"
            class="brand-button-primary"
            :to="createFormWithTemplateUrl"
            label="Use this template"
          />
        </TrackClick>
      </template>
    </Breadcrumb>

    <p
      v-if="template === null || !template"
      class="text-center my-4"
    >
      We could not find this template.
    </p>
    <template v-else>
      <section class="pt-12 bg-neutral-50 sm:pt-16 pb-[250px] relative">
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div
            class="flex flex-col items-center justify-center max-w-5xl gap-8 mx-auto md:gap-12 md:flex-row"
          >
            <div
              class="aspect-[4/3] shrink-0 rounded-lg shadow-xs overflow-hidden group w-full max-w-sm relative"
            >
              <template-preview-card
                :form="template.structure"
                :description="template.short_description"
                dense
                class="group-hover:scale-110 transition-all duration-200 absolute inset-0"
              />
            </div>

            <div class="flex-1 text-center md:text-left relative">
              <h1
                class="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl"
              >
                {{ template.name }}
              </h1>
              <p class="mt-2 text-lg font-normal text-neutral-600">
                {{ cleanQuotes(template.short_description) }}
              </p>
              <template-tags
                :template="template"
                :display-all="true"
                class="flex flex-wrap items-center justify-center gap-3 mt-4 md:justify-start"
              />
            </div>
          </div>
        </div>
      </section>

      <section class="w-full max-w-5xl relative px-4 mx-auto sm:px-6 lg:px-8 -mt-[210px]">
        <div
          class="p-4 mx-auto bg-white shadow-lg sm:p-6 lg:p-8 rounded-xl ring ring-inset ring-neutral-200 isolate"
        >
          <p class="text-sm font-medium text-center text-neutral-500 -mt-2 mb-2">
            Template Preview
          </p>
          <div class="mb-4">
            <div
              ref="templatePreviewParent"
              class="border rounded-lg bg-white dark:bg-notion-dark w-full shadow-xs transition-all overflow-y-auto flex flex-col"
            >
              <div
                :class="[
                  'flex flex-col',
                  form?.presentation_style === 'focused'
                    ? 'h-[650px] sm:h-[830px]'
                    : 'min-h-[520px]'
                ]"
              >
                <OpenCompleteForm
                  ref="open-complete-form"
                  :form="form"
                  :mode="FormMode.TEMPLATE"
                  :dark-mode="darkMode"
                  class="w-full grow min-h-0"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="absolute bottom-0 translate-y-full inset-x-0">
          <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl -mt-[20px]">
            <div class="flex items-center justify-center">
              <TrackClick
                name="use_template_button_clicked"
                class="mx-auto w-full max-w-[300px]"
              >
                <UButton
                  block
                  class="brand-button-primary"
                  :to="createFormWithTemplateUrl"
                  label="Use this template"
                />
              </TrackClick>
            </div>
            <div class="flex items-center justify-center">
              <div class="text-left mx-auto text-neutral-500 text-xs mt-4">
                ✓ Core features 100% free<br>
                ✓ No credit card required<br>
                ✓ No submissions limit on Free plan
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="pt-20 pb-12 bg-white sm:pb-16">
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div
            class="max-w-4xl mx-auto mt-16 space-y-12 sm:mt-16 sm:space-y-16"
          >
            <div
              class="nf-text"
              v-html="template.description"
            />

            <template v-if="template.questions?.length > 0">
              <hr class="mt-12 border-neutral-200">
              <div>
                <div class="text-center">
                  <h3
                    class="text-xl font-bold tracking-tight text-neutral-900 sm:text-2xl"
                  >
                    Frequently asked questions
                  </h3>
                  <p class="mt-2 text-base font-normal text-neutral-600">
                    Everything you need to know about this template.
                  </p>
                </div>
                <dl class="mt-12 space-y-10">
                  <div
                    v-for="(ques, ques_key) in template.questions"
                    :key="ques_key"
                    class="space-y-4"
                  >
                    <dt class="font-semibold text-neutral-900 dark:text-neutral-100">
                      {{ ques.question }}
                    </dt>
                    <dd
                      class="mt-2 leading-6 text-neutral-600 dark:text-neutral-400"
                      v-html="ques.answer"
                    />
                  </div>
                </dl>
              </div>
            </template>
          </div>
        </div>
      </section>

      <section
        v-if="relatedTemplates && relatedTemplates.length > 0"
        class="py-12 bg-white border-t border-neutral-200 sm:py-16"
      >
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div class="flex items-center justify-between">
            <h4
              class="text-xl font-bold tracking-tight text-neutral-900 sm:text-2xl"
            >
              Related templates
            </h4>
            <UButton
              :to="{ name: 'templates' }"
              color="white"
              size="sm"
              trailing-icon="i-lucide-arrow-right"
              label="View All"
            />
          </div>

          <div
            class="grid grid-cols-1 gap-8 mt-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 sm:gap-y-12"
          >
            <single-template
              v-for="related in relatedTemplates"
              :key="related.id"
              :template="related"
            />
          </div>
        </div>
      </section>

      <section
        v-if="templateTypes.length > 0 || templateIndustries.length > 0"
        class="py-12 bg-neutral-50 sm:py-16 border-t border-neutral-200"
      >
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div class="text-center">
            <h4
              class="text-xl font-bold tracking-tight text-neutral-900 sm:text-2xl"
            >
              Explore more {{ template.name.toLowerCase() }} templates
            </h4>
            <p class="mt-2 text-base font-normal text-neutral-600">
              Browse similar form templates in the same category or industry.
            </p>
          </div>

          <div class="flex flex-wrap items-center justify-center gap-3 mt-8">
            <NuxtLink
              v-for="type in templateTypes"
              :key="type.slug"
              :to="{ name: 'templates-types-slug', params: { slug: type.slug } }"
              class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-neutral-700 ring-1 ring-inset ring-neutral-200 hover:ring-neutral-300 hover:shadow-sm transition-all"
            >
              <UIcon name="i-lucide-layers" class="h-4 w-4 text-fuchsia-600" />
              {{ type.name }}
            </NuxtLink>
            <NuxtLink
              v-for="industry in templateIndustries"
              :key="industry.slug"
              :to="{ name: 'templates-industries-slug', params: { slug: industry.slug } }"
              class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-neutral-700 ring-1 ring-inset ring-neutral-200 hover:ring-neutral-300 hover:shadow-sm transition-all"
            >
              <UIcon name="i-lucide-building-2" class="h-4 w-4 text-fuchsia-600" />
              {{ industry.name }}
            </NuxtLink>
          </div>
        </div>
      </section>

      <section class="py-12 bg-white border-t border-neutral-200 sm:py-16">
        <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
          <div class="text-center">
            <h4
              class="text-xl font-bold tracking-tight text-neutral-900 sm:text-2xl"
            >
              How SharaForms works
            </h4>
          </div>

          <div class="grid grid-cols-1 mt-12 md:grid-cols-2 gap-x-8 gap-y-12 max-w-5xl mx-auto">
            <div
              class="flex flex-col items-center gap-4 text-center lg:items-start sm:text-left sm:items-start xl:flex-row"
            >
              <div
                class="inline-flex items-center justify-center w-10 h-10 text-base font-bold bg-white rounded-full shadow-xs ring ring-inset ring-neutral-200 brand-gradient-text-warm shrink-0"
              >
                1
              </div>
              <div>
                <h5 class="text-base font-bold leading-tight text-neutral-900">
                  Copy the template and change it the way you like
                </h5>
                <p class="mt-2 text-sm font-normal text-neutral-600">
                  <NuxtLink :to="createFormWithTemplateUrl">
                    Click here to copy this template
                  </NuxtLink>
                  and start customizing it. Change the questions, add new ones,
                  choose colors and more.
                </p>
              </div>
            </div>

            <div
              class="flex flex-col items-center gap-4 text-center lg:items-start sm:text-left sm:items-start xl:flex-row"
            >
              <div
                class="inline-flex items-center justify-center w-10 h-10 text-base font-bold bg-white rounded-full shadow-xs ring ring-inset ring-neutral-200 brand-gradient-text-warm shrink-0"
              >
                2
              </div>
              <div>
                <h5 class="text-base font-bold leading-tight text-neutral-900">
                  Embed the form or share it via a link
                </h5>
                <p class="mt-2 text-sm font-normal text-neutral-600">
                  You can directly share your form link, or embed the form on
                  your website. It's magic! 🪄
                </p>
              </div>
            </div>
          </div>

          <!-- add video here -->
          <!--          <div class="max-w-5xl mx-auto mt-12 shadow-sm rounded-xl bg-blue-50 aspect-video" />-->
        </div>
      </section>
    </template>

    <open-form-footer class="mt-8 border-t" />
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from "vue"
import { useRoute } from "vue-router"
import FormTemplateModal from "~/components/open/forms/components/templates/FormTemplateModal.vue"
import TemplatePreviewCard from "~/components/open/forms/components/templates/TemplatePreviewCard.vue"
import TemplateTags from "~/components/pages/templates/TemplateTags.vue"
import SingleTemplate from "~/components/pages/templates/SingleTemplate.vue"
import { FormMode } from "~/lib/forms/FormModeStrategy.js"
import { cleanQuotes } from "~/lib/utils"
import OpenCompleteForm from "~/components/open/forms/OpenCompleteForm.vue"
import Breadcrumb from "~/components/app/Breadcrumb.vue"
import TrackClick from "~/components/global/TrackClick.vue"
import { handleDarkMode, useDarkMode } from "~/lib/forms/public-page.js"
import { resolveSchemaUrl, stripHtml, useSchemaBaseUrl } from '~/composables/useSchemaSeo'
import { useTemplateMeta } from '~/composables/data/useTemplateMeta'

const route = useRoute()
const { detail, list } = useTemplates()

const { data: template, suspense: templateSuspense } = detail(route.params.slug)
const { data: allTemplates, suspense: templatesSuspense } = list()

// Handle SSR suspense to prevent flash of error message and ensure related
// template links are present in the server-rendered HTML.
if (import.meta.server) {
  await templateSuspense()
  await templatesSuspense()
}

const form = computed(() => {
  if (!template.value) {
    return null
  }
  return template.value.structure
})

// Dark mode handling like editor preview
const templatePreviewParent = ref(null)
const darkMode = useDarkMode(templatePreviewParent)
onMounted(() => {
  if (template.value?.structure?.dark_mode) {
    handleDarkMode(template.value.structure.dark_mode, templatePreviewParent.value)
  }
})

const relatedTemplates = computed(() => {
  if (!template.value?.related_templates || !allTemplates.value) {
    return []
  }
  const relatedSlugs = new Set(template.value.related_templates)
  return allTemplates.value.filter(
    (t) => relatedSlugs.has(t.slug) && t.slug !== template.value.slug,
  )
})

const showFormTemplateModal = ref(false)
const { data: user } = useAuth().user()
const canEditTemplate = computed(
  () => user.value && (user.value.admin || user.value.template_editor || template.value?.creator_id === user.value.id),
)

const createFormWithTemplateUrl = computed(() => {
  if (!user.value) {
    return {
      name: "register",
      query: {
        redirect: route.fullPath,
        template: route.params.slug,
      },
    }
  }
  return {
    name: "forms-create",
    query: {
      template: route.params.slug,
    },
  }
})

const breadcrumbs = computed(() => {
  if (!template.value) {
    return []
  }
  return [
    { name: "Templates", to: { name: "templates" } },
    {
      name: template.value.name,
      to: { name: "templates-slug", params: { slug: template.value.slug } },
    },
  ]
})

const copyTemplateUrl = () => {
  navigator.clipboard.writeText(window.location.href)
  useAlert().success("URL copied to clipboard!")
}

useOpnSeoMeta(
  computed(() => ({
    title: template.value?.name,
    description: template.value?.short_description,
    ogImage: template.value?.image_url || "/share-preview.jpg",
    speakable: ["h1", "p"],
    keywords: () => buildTemplateKeywords(),
  })),
)

const schemaBaseUrl = useSchemaBaseUrl()
const { getTemplateTypes, getTemplateIndustries } = useTemplateMeta()

const templateTypes = computed(() =>
  getTemplateTypes(template.value?.types),
)
const templateIndustries = computed(() =>
  getTemplateIndustries(template.value?.industries),
)

function buildTemplateKeywords () {
  if (!template.value) return "free form templates"
  const parts = [
    template.value.name,
    template.value.short_description,
    ...templateTypes.value.map((t) => t.name),
    ...templateIndustries.value.map((i) => i.name),
    "form template",
    "free form template",
    "free form builder",
    "unlimited submissions",
    "calculated fields",
    "no-code form",
  ]
  return parts.join(", ")
}

const relatedTemplateLinks = computed(() => {
  if (!relatedTemplates.value.length) {
    return []
  }
  return relatedTemplates.value.map((t) =>
    resolveSchemaUrl(schemaBaseUrl, `/templates/${t.slug}`),
  )
})

const templateSchema = computed(() => {
  if (!template.value) {
    return null
  }

  const typeCollections = templateTypes.value.map((t) => ({
    '@type': 'CollectionPage',
    name: t.name,
    url: resolveSchemaUrl(schemaBaseUrl, `/templates/types/${t.slug}`),
  }))
  const industryCollections = templateIndustries.value.map((i) => ({
    '@type': 'CollectionPage',
    name: i.name,
    url: resolveSchemaUrl(schemaBaseUrl, `/templates/industries/${i.slug}`),
  }))

  return {
    '@context': 'https://schema.org',
    '@type': 'CreativeWork',
    name: template.value.name,
    description: template.value.short_description,
    url: resolveSchemaUrl(schemaBaseUrl, `/templates/${template.value.slug}`),
    ...(template.value.image_url ? { image: template.value.image_url } : {}),
    ...(template.value.created_at ? { datePublished: template.value.created_at } : {}),
    ...(template.value.updated_at || template.value.created_at ? { dateModified: template.value.updated_at || template.value.created_at } : {}),
    keywords: buildTemplateKeywords(),
    genre: templateTypes.value.map((t) => t.name),
    about: templateIndustries.value.map((i) => i.name),
    inLanguage: 'en',
    isFamilyFriendly: true,
    isPartOf: {
      '@id': `${resolveSchemaUrl(schemaBaseUrl, '/templates')}#collection`,
    },
    ...(typeCollections.length ? { hasPart: typeCollections } : {}),
    ...(industryCollections.length ? { mentions: industryCollections } : {}),
    ...(relatedTemplateLinks.value.length ? { relatedLink: relatedTemplateLinks.value } : {}),
    creator: {
      '@id': `${schemaBaseUrl}/#organization`,
    },
    publisher: {
      '@id': `${schemaBaseUrl}/#organization`,
    },
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': resolveSchemaUrl(schemaBaseUrl, `/templates/${template.value.slug}`),
    },
  }
})

const templateFaqSchema = computed(() => {
  if (!template.value?.questions?.length) {
    return null
  }

  return {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: template.value.questions.map((question) => ({
      '@type': 'Question',
      name: stripHtml(question.question),
      acceptedAnswer: {
        '@type': 'Answer',
        text: stripHtml(question.answer),
      },
    })),
  }
})

const templateBreadcrumbSchema = computed(() => {
  if (!template.value) {
    return null
  }

  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: [
      {
        '@type': 'ListItem',
        position: 1,
        name: 'Templates',
        item: resolveSchemaUrl(schemaBaseUrl, '/templates'),
      },
      {
        '@type': 'ListItem',
        position: 2,
        name: template.value.name,
        item: resolveSchemaUrl(schemaBaseUrl, `/templates/${template.value.slug}`),
      },
    ],
  }
})

useHead(() => ({
  script: [
    ...(templateSchema.value
      ? [{
          key: `template-schema:${template.value.slug}`,
          type: 'application/ld+json',
          textContent: JSON.stringify(templateSchema.value),
        }]
      : []),
    ...(templateFaqSchema.value
      ? [{
          key: `template-faq-schema:${template.value.slug}`,
          type: 'application/ld+json',
          textContent: JSON.stringify(templateFaqSchema.value),
        }]
      : []),
    ...(templateBreadcrumbSchema.value
      ? [{
          key: `template-breadcrumb-schema:${template.value.slug}`,
          type: 'application/ld+json',
          textContent: JSON.stringify(templateBreadcrumbSchema.value),
        }]
      : []),
  ],
}))
</script>

<style>
@reference '~/css/app.css';

.nf-text {
  @apply space-y-4;
  h2 {
    @apply text-sm font-normal tracking-widest text-neutral-500 uppercase;
  }

  p {
    @apply font-normal leading-7 text-neutral-900 dark:text-neutral-100;
  }

  ol {
    @apply list-decimal list-inside;
  }

  ul {
    @apply list-disc list-inside;
  }
}

.aspect-video {
  aspect-ratio: 16/9;
}
</style>

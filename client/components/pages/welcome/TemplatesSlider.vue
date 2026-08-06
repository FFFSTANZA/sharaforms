<template>
  <div class="mx-auto max-w-266">
    <div class="max-w-lg mx-auto text-center md:px-1">
      <h2
        class="brand-text-strong text-3xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%]"
      >
        Templates for financial services
      </h2>
      <p
        class="brand-text-muted mt-4 text-base font-normal leading-7 tracking-[-1.1%]"
      >
        All templates are fully customizable — adapt them to your compliance and
        brand requirements in minutes.
      </p>
    </div>

    <div
      v-if="sliderTemplates && sliderTemplates.length"
      class="mt-12 grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3"
    >
      <single-template
        v-for="template in sliderTemplates"
        :key="template.slug"
        :template="template"
      />
    </div>
  </div>
</template>

<script>
import SingleTemplate from "../templates/SingleTemplate.vue"
import { FINANCIAL_TEMPLATE_SLUGS } from "~/data/forms/templates/template-slugs.js"

export default {
  components: { SingleTemplate },
  setup() {
    const { list } = useTemplates()
    const { data: templates, suspense: templatesSuspense } = list()

    // Resolve the real template list during SSR so the slider never emits
    // placeholder/dead template links in the server-rendered HTML.
    onServerPrefetch(async () => {
      await templatesSuspense()
    })

    // SSR-safe fallback: known seeded financial templates, mirroring the
    // TemplateSeeder catalog. Every object is a complete SingleTemplate
    // payload so the preview card renders without an API response.
    const fallbackFinancialTemplates = FINANCIAL_TEMPLATE_SLUGS.map((slug) => ({
      slug,
      name: humanizeSlug(slug),
      short_description: "",
      structure: { properties: [] },
      types: [],
      industries: [],
    }))

    return {
      sliderTemplates: computed(() => {
        if (templates.value && templates.value.length) {
          // The slider lives on the /industry (financial services) landing
          // page, so prefer templates tagged with a financial industry and
          // fall back to the newest templates if none match.
          const financial = templates.value.filter((t) => {
            return (
              Array.isArray(t.industries) &&
              t.industries.some((i) =>
                FINANCIAL_TEMPLATE_SLUGS.includes(t.slug) ||
                ["banking_forms", "insurance_forms", "financial_services_forms"].includes(i),
              )
            )
          })
          return (financial.length ? financial : templates.value).slice(0, 6)
        }

        return fallbackFinancialTemplates
      }),
    }
  },
}

function humanizeSlug (slug) {
  return slug
    .replace(/-/g, " ")
    .replace(/\b\w/g, (c) => c.toUpperCase())
}
</script>

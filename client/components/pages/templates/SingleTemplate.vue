<template>
  <div
    v-if="template"
    class="relative group border border-neutral-200 dark:border-neutral-800 rounded-[24px] overflow-hidden transition-all duration-300 hover:border-neutral-300 dark:hover:border-neutral-700 hover:shadow-lg hover:shadow-neutral-200/60 dark:hover:shadow-none"
  >
    <div v-if="template.is_new" class="absolute top-0 right-0 p-3 z-10">
      <span
        class="inline-flex items-center gap-1 rounded-full brand-gradient-warm px-2 py-1 text-xs font-medium text-white"
      >
        <svg
          aria-hidden="true"
          class="h-3 w-3"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z"
            clip-rule="evenodd"
          />
        </svg>
        New
      </span>
    </div>

    <div class="w-full aspect-[4/3] overflow-hidden bg-neutral-100 dark:bg-neutral-800">
      <div
        class="group-hover:scale-105 transition-transform duration-300 h-full w-full"
      >
        <template-preview-card
          :form="template.structure"
          :description="template.short_description"
          dense
          class="h-full w-full"
        />
      </div>
    </div>
    <div class="px-5 pt-5 pb-4">
      <p
        class="text-lg font-semibold leading-snug text-neutral-900 dark:text-neutral-50 group-hover:text-neutral-700 dark:group-hover:text-neutral-200 transition-colors duration-150"
      >
        {{ template.name }}
      </p>
      <p
        class="line-clamp-2 mt-1.5 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400"
      >
        {{ cleanQuotes(template.short_description) }}
      </p>
    </div>

    <template-tags
      :template="template"
      class="flex mt-2 items-center flex-wrap gap-2 px-5 pb-5"
    />
    <NuxtLink
      v-if="template.slug"
      :to="{ name: 'templates-slug', params: { slug: template.slug } }"
    >
      <span class="absolute inset-0" aria-hidden="true" />
    </NuxtLink>
  </div>
</template>

<script>
import TemplateTags from "./TemplateTags.vue"
import TemplatePreviewCard from "~/components/open/forms/components/templates/TemplatePreviewCard.vue"

export default {
  components: { TemplateTags, TemplatePreviewCard },

  props: {
    template: {
      type: Object,
    },
  },

  methods: {
    cleanQuotes(str) {
      // Remove starting and ending quotes if any
      return str ? str.replace(/^"/, "").replace(/"$/, "") : ""
    },
  },
}
</script>

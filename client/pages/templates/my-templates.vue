<template>
  <div class="flex flex-col min-h-screen px-4 sm:px-10 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10">
      <div>
        <span class="grad-kicker w-8 h-1 rounded-full block mb-3"></span>
        <h1 class="text-[28px] font-bold text-[#1D1F24] tracking-tight">My Templates</h1>
        <p class="text-[13px] text-[#6E7278] font-medium mt-1">
          Forms you've saved as reusable templates
        </p>
      </div>
      <div class="flex items-center gap-3">
        <UButton
          to="/templates"
          variant="outline"
          color="neutral"
          class="btn-ghost !border-[#DEE1E7]"
          icon="i-tabler:eye"
          label="Browse Templates"
        />
        <UButton
          @click="openTemplateGuide"
          variant="outline"
          color="neutral"
          class="btn-ghost !border-[#DEE1E7]"
          icon="i-lucide-circle-question-mark"
          label="How to Create"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-[#E6E8EE] overflow-hidden">
        <USkeleton class="aspect-[4/3] rounded-none w-full" />
        <div class="p-5">
          <USkeleton class="h-5 mb-2 w-3/4" />
          <USkeleton class="h-4 w-full" />
          <USkeleton class="h-4 w-1/2 mt-1" />
          <div class="flex gap-2 mt-4">
            <USkeleton class="h-5 rounded-full w-14" />
            <USkeleton class="h-5 rounded-full w-20" />
          </div>
        </div>
      </div>
    </div>

    <!-- Templates Grid -->
    <div v-else-if="templates && templates.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <single-template
        v-for="template in templates"
        :key="template.id"
        :template="template"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-20 px-4">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#E6E8EE]/50 dark:bg-neutral-800 mb-4">
        <UIcon name="i-lucide-copy" class="h-8 w-8 text-[#A7ABB2]" />
      </div>
      <h3 class="text-lg font-semibold text-[#1D1F24]">
        No templates yet
      </h3>
      <p class="mt-1.5 text-sm text-[#6E7278] max-w-sm mx-auto">
        You haven't created any templates yet. Save a form as a template to reuse it!
      </p>
      <div class="flex items-center justify-center gap-3 mt-5">
        <NuxtLink
          to="/forms/create"
          class="btn-primary text-white px-5 py-2.5 rounded-xl flex items-center gap-2 text-sm font-semibold"
        >
          <i class="fa-solid fa-plus text-xs"></i>
          Create Form
        </NuxtLink>
        <UButton
          @click="openTemplateGuide"
          variant="outline"
          color="neutral"
          icon="i-lucide-circle-question-mark"
          label="How to Create"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCrisp } from '~/composables/useCrisp'

definePageMeta({
  middleware: "auth",
  layout: "dashboard",
})

useOpnSeoMeta({
  title: "My Templates",
  description:
    "Your saved form templates. Reuse and share your best forms.",
})

const { list } = useTemplates()
const { openHelpdeskArticle } = useCrisp()

const { data: templates, isLoading: loading } = list({
  params: { onlymy: true }
})

const openTemplateGuide = () => {
  openHelpdeskArticle('how-to-create-a-sharaforms-template-1fn84i4')
}
</script>

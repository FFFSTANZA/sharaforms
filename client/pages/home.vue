<template>
  <div
    v-if="workspace"
    data-testid="home-page"
    class="flex flex-col h-full bg-neutral-50"
  >
    <div
      class="sticky top-0 z-50 bg-neutral-50/95 backdrop-blur border-b border-neutral-200/80 px-4 sm:px-6"
    >
      <div
        class="max-w-5xl mx-auto flex items-center justify-between flex-shrink-0 gap-3 h-16"
      >
        <div class="min-w-0">
          <h1
            class="text-[15px] font-semibold tracking-tight text-neutral-900 leading-tight"
          >
            Your forms
          </h1>
          <p class="text-xs text-neutral-500 truncate">
            {{ headerSubtitle }}
          </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <VTransition name="fade">
            <div
              v-if="forms?.length > 0 || isFilteringForms"
              class="flex items-center gap-2"
            >
              <UInput
                v-model="search"
                placeholder="Search forms..."
                icon="i-heroicons:magnifying-glass"
                class="w-36 md:w-48"
                :ui="{ icon: 'h-3.5 w-3.5' }"
              />

              <USelectMenu
                v-if="allTags.length > 0"
                v-model="selectedTags"
                :items="tagOptions"
                multiple
                placeholder="Tags"
                class="hidden sm:block"
                :ui="{ content: 'min-w-fit' }"
              />

              <UButton
                v-if="isFilteringForms"
                label="Clear"
                variant="ghost"
                color="neutral"
                size="sm"
                @click="clearFilters"
              />
            </div>
            <div class="grow" v-else />
          </VTransition>

          <TrackClick name="home_top_bar_create_form_click">
            <UButton
              v-if="!workspace?.is_readonly"
              icon="i-heroicons:plus"
              label="Create Form"
              :to="{ name: 'forms-create' }"
              size="md"
              :ui="{ leadingIcon: 'h-3.5 w-3.5' }"
              class="shadow-sm font-semibold"
              color="primary"
            />
          </TrackClick>
        </div>
      </div>
    </div>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6">
      <div class="max-w-5xl mx-auto">
        <ClientOnly>
          <VTransition name="fade">
            <div
              v-if="isFetched && !isFormsLoading && forms?.length === 0"
              class="text-center py-16 px-4"
            >
              <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50"
              >
                <UIcon
                  name="i-lucide-clipboard-list"
                  class="h-8 w-8 text-blue-500"
                />
              </div>
              <h3 class="mt-5 text-lg font-semibold text-neutral-900">
                Create your first form
              </h3>
              <p class="mt-1 text-sm text-neutral-500">
                Get started by creating a new form to collect responses.
              </p>
              <UButton
                icon="i-heroicons:plus"
                label="Create Form"
                :to="{ name: 'forms-create' }"
                color="primary"
                class="mt-6"
              />
            </div>

            <div
              v-if="
                isFetched &&
                !isFormsLoading &&
                forms?.length > 0 &&
                enrichedForms.length === 0
              "
              class="text-center py-16 px-4"
            >
              <UIcon
                name="i-heroicons:magnifying-glass"
                class="h-12 w-12 text-neutral-400 mx-auto"
              />
              <h3 class="mt-4 text-lg font-semibold text-neutral-900">
                No forms found
              </h3>
              <p class="mt-1 text-sm text-neutral-500">
                Your search and filter criteria did not match any forms.
              </p>
              <UButton
                v-if="isFilteringForms"
                class="mt-6"
                label="Clear Filters"
                variant="soft"
                @click="clearFilters"
              />
            </div>

            <div v-if="enrichedForms.length > 0" class="mb-10">
              <div class="bg-white rounded-xl border border-neutral-200/70 shadow-sm overflow-hidden divide-y divide-neutral-100 lg:mt-6">
                <FormCard
                  v-for="form in enrichedForms"
                  :key="form.id"
                  :form="form"
                />

                <div v-if="isLoadingMore" class="divide-y divide-neutral-100">
                  <FormCardSkeleton />
                  <FormCardSkeleton />
                  <FormCardSkeleton />
                </div>
              </div>

              <div
                v-if="!isLoadingMore && !isComplete && totalPages > 1"
                class="flex justify-center items-center py-4"
              >
                <div class="text-sm text-neutral-500">
                  Loaded {{ currentPage }} of {{ totalPages }} pages
                </div>
              </div>
            </div>

            <div v-if="isFormsLoading" class="bg-white rounded-xl border border-neutral-200/70 shadow-sm overflow-hidden divide-y divide-neutral-100">
              <FormCardSkeleton />
              <FormCardSkeleton />
              <FormCardSkeleton />
            </div>
          </VTransition>
          <template #fallback>
            <div class="bg-white rounded-xl border border-neutral-200/70 shadow-sm overflow-hidden divide-y divide-neutral-100">
              <FormCardSkeleton />
              <FormCardSkeleton />
              <FormCardSkeleton />
            </div>
          </template>
        </ClientOnly>
      </div>
    </div>
    <div id="home-portals" class="z-20" />

    <YearlyUpgradeModal />
  </div>
</template>

<script setup>
import { useFuse } from "@vueuse/integrations/useFuse"
import FormCard from "~/components/pages/home/FormCard.vue"
import FormCardSkeleton from "~/components/pages/home/FormCardSkeleton.vue"
import TrackClick from "~/components/global/TrackClick.vue"


definePageMeta({
  middleware: ["auth"],
  layout: "dashboard",
})

useOpnSeoMeta({
  title: "Your Forms",
  description:
    "All of your SharaForms are here. Create new forms, or update your existing forms.",
})

// Composables
const { current: workspace, currentId: workspaceId } = useCurrentWorkspace()

const {
  forms,
  isLoading: isFormsLoading,
  isFetchingNextPage: isLoadingMore,
  isFetched,
  currentPage,
  totalPages,
  isComplete,
} = useFormsList(workspaceId, {
  fetchAll: true,
  enabled: computed(() => import.meta.client && !!workspaceId.value),
})

// State
const search = ref("")
const debouncedSearch = refDebounced(search, 500)
const selectedTags = ref([])

// Methods
const clearFilters = () => {
  search.value = ""
  selectedTags.value = []
}

// Computed
const isFilteringForms = computed(() => {
  return (
    (search.value !== "" && search.value !== null) ||
    selectedTags.value.length > 0
  )
})

const headerSubtitle = computed(() => {
  if (isFetched.value && !isFormsLoading.value) {
    if (forms.value?.length > 0) {
      const count = forms.value.length
      return `${count} ${count === 1 ? "form" : "forms"} in this workspace`
    }
    return "Create your first form to get started"
  }
  return "Manage and track your forms"
})

// Extract all unique tags from forms
const allTags = computed(() => {
  if (!forms.value) return []

  const tagsSet = new Set()
  forms.value.forEach((form) => {
    if (form.tags && form.tags.length) {
      form.tags.forEach((tag) => tagsSet.add(tag))
    }
  })

  return Array.from(tagsSet).sort()
})

const tagOptions = computed(() =>
  allTags.value.map((tag) => ({ label: tag, value: tag })),
)

const baseForms = computed(() => {
  if (!forms.value) return []
  return forms.value.filter((form) => {
    if (selectedTags.value.length === 0) return true
    const selectedTagStrings = selectedTags.value
      .map((t) => (typeof t === "string" ? t : t?.value))
      .filter(Boolean)
    return form.tags && form.tags.length
      ? selectedTagStrings.every((tag) => form.tags.includes(tag))
      : false
  })
})

const { results: fuseResults } = useFuse(debouncedSearch, baseForms, {
  fuseOptions: {
    keys: ["title", "slug", "tags"],
    threshold: 0.3,
    ignoreLocation: true,
    includeScore: false,
  },
  matchAllWhenSearchEmpty: true,
})

const enrichedForms = computed(() => {
  const base = baseForms.value
  if (!base || base.length === 0) return []
  const results = fuseResults.value
  return results && results.length > 0 ? results.map((r) => r.item) : base
})
</script>

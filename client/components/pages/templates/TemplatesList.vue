<template>
  <div>
    <section :class="sectionClass">
        <div class="relative z-20 mb-10">
          <VForm
            size="sm"
            class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
          >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
              <select-input
                v-if="filterTypes"
                v-model="selectedType"
                name="type"
                :options="typesOptions"
                border-radius="full"
                chevron-icon="lucide:chevron-down"
                class="w-full sm:w-52"
                :ui="selectUi"
              >
                <template #selected="{ optionName }">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                      <UIcon name="i-lucide-layers" class="h-2.5 w-2.5 text-white" />
                    </span>
                    <span class="truncate font-medium text-[#565A62] dark:text-neutral-200">
                      {{ optionName }}
                    </span>
                  </div>
                </template>
                <template #option="{ option, selected }">
                  <div class="flex items-center justify-between gap-2">
                    <span class="flex min-w-0 items-center gap-2 truncate">
                      <UIcon name="i-lucide-layers" class="h-4 w-4 shrink-0 text-[#A7ABB2]" />
                      <span
                        class="truncate"
                        :class="selected ? 'font-medium brand-gradient-text-warm' : 'text-[#565A62] dark:text-neutral-200'"
                      >
                        {{ option.name }}
                      </span>
                    </span>
                    <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                      <UIcon
                        v-if="selected"
                        name="i-lucide-check"
                        class="h-2.5 w-2.5 text-white"
                      />
                    </span>
                  </div>
                </template>
              </select-input>
              <select-input
                v-if="filterIndustries"
                v-model="selectedIndustry"
                name="industry"
                :options="industriesOptions"
                border-radius="full"
                chevron-icon="lucide:chevron-down"
                class="w-full sm:w-52"
                :ui="selectUi"
              >
                <template #selected="{ optionName }">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                      <UIcon name="i-lucide-building-2" class="h-2.5 w-2.5 text-white" />
                    </span>
                    <span class="truncate font-medium text-[#565A62] dark:text-neutral-200">
                      {{ optionName }}
                    </span>
                  </div>
                </template>
                <template #option="{ option, selected }">
                  <div class="flex items-center justify-between gap-2">
                    <span class="flex min-w-0 items-center gap-2 truncate">
                      <UIcon name="i-lucide-building-2" class="h-4 w-4 shrink-0 text-[#A7ABB2]" />
                      <span
                        class="truncate"
                        :class="selected ? 'font-medium brand-gradient-text-warm' : 'text-[#565A62] dark:text-neutral-200'"
                      >
                        {{ option.name }}
                      </span>
                    </span>
                    <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full brand-gradient-warm">
                      <UIcon
                        v-if="selected"
                        name="i-lucide-check"
                        class="h-2.5 w-2.5 text-white"
                      />
                    </span>
                  </div>
                </template>
              </select-input>
            </div>

            <div class="relative w-full lg:w-72">
              <UIcon
                name="i-lucide-search"
                class="pointer-events-none absolute left-3 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-[#A7ABB2] dark:text-neutral-500"
              />
              <text-input
                v-model="search"
                autocomplete="off"
                name="search"
                placeholder="Search templates..."
                border-radius="full"
                :ui="{ slots: { input: '!pl-9' } }"
              />
            </div>
          </VForm>

          <div class="mt-4 flex items-center justify-between gap-2">
            <p class="text-[13px] text-[#6E7278] dark:text-neutral-400">
              <template v-if="loading">
                Loading templates...
              </template>
              <template v-else>
                {{ enrichedTemplates.length }}
                {{ enrichedTemplates.length === 1 ? "template" : "templates" }}
              </template>
            </p>
            <UButton
              v-if="hasActiveFilters"
              size="xs"
              color="neutral"
              variant="ghost"
              icon="i-lucide-rotate-ccw"
              label="Clear filters"
              @click="resetFilters"
            />
          </div>
        </div>

        <div
          v-if="loading"
          class="relative z-10"
        >
          <div
            class="grid gap-8 sm:gap-y-12"
            :class="gridClasses"
          >
            <div
              v-for="i in 8"
              :key="i"
              class="w-full"
            >
              <!-- Template Card Skeleton -->
              <div class="w-full">
                <!-- Image Skeleton -->
                <USkeleton class="aspect-[4/3] rounded-lg w-full" />
                
                <!-- Title Skeleton -->
                <USkeleton class="h-6 mt-4 mb-2 w-full" />
                
                <!-- Description Skeleton -->
                <div class="space-y-2 mt-2 mb-4">
                  <USkeleton class="h-4 w-full" />
                  <USkeleton class="h-4 w-3/4" />
                </div>
                
                <!-- Tags Skeleton -->
                <div class="flex flex-wrap gap-2 mt-4">
                  <USkeleton class="h-6 rounded-full w-16" />
                  <USkeleton class="h-6 rounded-full w-20" />
                  <USkeleton class="h-6 rounded-full w-14" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          v-else-if="enrichedTemplates.length === 0"
          class="py-16 text-center"
        >
          <UIcon
            name="i-lucide-search-x"
            class="mx-auto h-10 w-10 text-[#C7C9CE] dark:text-neutral-600"
          />
          <p class="mt-4 text-base font-semibold text-[#1D1F24] dark:text-neutral-100">
            No templates found
          </p>
          <p class="mt-1 text-[13px] text-[#6E7278] dark:text-neutral-400">
            Try adjusting your search or filters.
          </p>
          <UButton
            v-if="hasActiveFilters"
            class="mt-6"
            size="sm"
            color="neutral"
            variant="soft"
            icon="i-lucide-rotate-ccw"
            label="Clear all filters"
            @click="resetFilters"
          />
        </div>
        <div
          v-else
          class="relative z-10"
        >
          <div
            class="grid gap-8 sm:gap-y-12"
            :class="gridClasses"
          >
            <single-template
              v-for="template in enrichedTemplates"
              :key="template.id"
              :template="template"
            />
          </div>
        </div>
    </section>

    <slot name="before-lists" />

    <section
      v-if="showTypes"
      class="py-12 bg-[#F7F8FA] dark:bg-neutral-900 sm:py-16"
    >
      <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
        <div class="flex items-center justify-between">
          <h4
            class="text-xl font-bold tracking-tight text-[#1D1F24] sm:text-2xl"
          >
            All Types
          </h4>
          <UButton
            v-if="$route.name !== 'templates'"
            :to="{ name: 'templates' }"
            color="neutral"
            size="sm"
            trailing-icon="i-lucide-arrow-right"
            label="View All Templates"
          />
        </div>

        <div
          class="grid grid-cols-1 gap-x-8 gap-y-4 mt-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
          <NuxtLink
            v-for="row in types"
            :key="row.slug"
            :to="{ params: { slug: row.slug }, name: 'templates-types-slug' }"
            :title="row.name"
            class="text-[#565A62] dark:text-neutral-400 transition-colors duration-300 brand-gradient-hover"
          >
            {{ row.name }}
          </NuxtLink>
        </div>
      </div>
    </section>

    <section
      v-if="showIndustries"
      class="py-12 bg-white sm:py-16"
    >
      <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
        <div class="flex items-center justify-between">
          <h4
            class="text-xl font-bold tracking-tight text-[#1D1F24] sm:text-2xl"
          >
            All Industries
          </h4>
          <UButton
            v-if="$route.name !== 'templates'"
            :to="{ name: 'templates' }"
            color="neutral"
            size="sm"
            trailing-icon="i-lucide-arrow-right"
            label="View All Templates"
          />
        </div>

        <div
          class="grid grid-cols-1 gap-x-8 gap-y-4 mt-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
          <NuxtLink
            v-for="row in industries"
            :key="row.slug"
            :to="{
              params: { slug: row.slug },
              name: 'templates-industries-slug',
            }"
            :title="row.name"
            class="text-[#565A62] dark:text-neutral-400 transition-colors duration-300 brand-gradient-hover"
          >
            {{ row.name }}
          </NuxtLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useFuse } from '@vueuse/integrations/useFuse'
import SingleTemplate from "./SingleTemplate.vue"
import { refDebounced } from "@vueuse/core"
import { useTemplateMeta } from "~/composables/data/useTemplateMeta"

const props = defineProps({
  templates: {
    type: Array,
    default: () => [],
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  showTypes: {
    type: Boolean,
    default: true,
  },
  filterTypes: {
    type: Boolean,
    default: true,
  },
  showIndustries: {
    type: Boolean,
    default: true,
  },
  filterIndustries: {
    type: Boolean,
    default: true,
  },
  gridClasses: {
    type: String,
    default: "grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4",
  },
  sectionClass: {
    type: String,
    default: "bg-white pb-14",
  },
})

const { industries: industriesMap, types: typesMap } = useTemplateMeta()

const selectUi = {
  slots: {
    anchor: 'bg-[#F7F8FA] dark:bg-notion-dark-light hover:border-[#DEE1E7] dark:hover:border-neutral-500',
    chevronContainer: 'bg-transparent',
    chevronGradient: 'bg-transparent',
    chevronIcon: 'h-4 w-4 text-[#A7ABB2]',
    option: 'px-3 py-2',
  },
}

const industries = computed(() => [...industriesMap.values()])
const types = computed(() => [...typesMap.values()])

const search = ref("")
const debouncedSearch = refDebounced(search, 500)

const selectedType = ref("all")
const selectedIndustry = ref("all")

const industriesOptions = computed(() => {
  return [{ name: "All Industries", value: "all" }].concat(
    industries.value.map((industry) => ({
      name: industry.name,
      value: industry.slug,
    })),
  )
})

const typesOptions = computed(() => {
  return [{ name: "All Types", value: "all" }].concat(
    types.value.map((type) => ({
      name: type.name,
      value: type.slug,
    })),
  )
})

const filteredBase = computed(() => {
  let list = props.templates

  if (props.filterTypes && selectedType.value && selectedType.value !== "all") {
    list = list.filter((item) => {
      return item.types && item.types.length > 0
        ? item.types.includes(selectedType.value)
        : false
    })
  }

  if (props.filterIndustries && selectedIndustry.value && selectedIndustry.value !== "all") {
    list = list.filter((item) => {
      return item.industries && item.industries.length > 0
        ? item.industries.includes(selectedIndustry.value)
        : false
    })
  }

  return list
})

const { results: fuseResults } = useFuse(
  debouncedSearch,
  filteredBase,
  {
    fuseOptions: {
      keys: ["name", "slug", "description", "short_description"],
      threshold: 0.3,
      ignoreLocation: true,
      includeScore: false,
    },
    matchAllWhenSearchEmpty: true,
  }
)

const enrichedTemplates = computed(() => {
  const base = filteredBase.value
  const results = fuseResults.value
  return results && results.length > 0 ? results.map(r => r.item) : base
})

const hasActiveFilters = computed(() => {
  return (
    (props.filterTypes && selectedType.value !== "all") ||
    (props.filterIndustries && selectedIndustry.value !== "all") ||
    search.value.trim() !== ""
  )
})

const resetFilters = () => {
  selectedType.value = "all"
  selectedIndustry.value = "all"
  search.value = ""
}
</script>

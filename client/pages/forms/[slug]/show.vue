<template>
  <div class="flex flex-col sm:flex-row h-screen bg-[var(--sf-bg-page)]">
    <!-- Form Sidebar - Always shown -->
    <FormSidebar :form="form" :loading="isLoading" />
    
    <!-- Main content area -->
    <main class="flex-1 sm:pl-[260px] overflow-hidden">
      <div class="flex flex-col h-full">
        <!-- Loading State -->
        <div v-if="isLoading || !isFormFinished" class="flex-1 bg-[var(--sf-bg-page)]">
          <!-- Top Bar Skeleton -->
          <div class="bg-[var(--sf-bg-surface)] border-b border-[var(--sf-border-card)]">
            <div class="max-w-5xl mx-auto px-6 py-4">
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                  <USkeleton class="h-5 w-48 mb-2.5" />
                  <div class="flex items-center gap-3">
                    <USkeleton class="h-3.5 w-16" />
                    <USkeleton class="h-3.5 w-16" />
                    <USkeleton class="h-3.5 w-24 hidden sm:block" />
                  </div>
                </div>
                <div class="flex gap-2.5">
                  <USkeleton class="h-9 w-24 rounded-xl" />
                  <USkeleton class="h-9 w-28 rounded-xl" />
                </div>
              </div>
            </div>
          </div>

          <!-- Page Content Skeleton -->
          <div :class="['flex-1 bg-[var(--sf-bg-page)] p-6', { 'overflow-y-auto': !isSubmissionsPage }]">
            <div class="max-w-5xl mx-auto space-y-5">
              <USkeleton class="h-7 w-40" />
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <USkeleton class="h-32 w-full rounded-2xl" />
                <USkeleton class="h-32 w-full rounded-2xl" />
                <USkeleton class="h-32 w-full rounded-2xl" />
              </div>
              <USkeleton class="h-64 w-full rounded-2xl" />
            </div>
          </div>
        </div>

        <!-- Loaded Content -->
        <template v-else-if="form">
          <!-- Top Bar -->
          <div class="bg-[var(--sf-bg-surface)] border-b border-[var(--sf-border-card)]">
            <div class="max-w-5xl mx-auto px-6 py-4">
              <!-- Title + Actions row -->
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                  <h1 class="text-lg font-semibold text-[var(--sf-text-primary)] tracking-tight truncate">
                    {{ form.title }}
                  </h1>
                  <div class="flex flex-wrap items-center gap-2.5 mt-1.5">
                    <div class="flex items-center gap-3 text-[var(--sf-text-caption)] text-[12px] font-medium">
                      <span class="flex items-center gap-1">
                        <Icon name="i-tabler:eye" class="w-3.5 h-3.5" />
                        {{ formatNumber(form.views_count) }}
                      </span>
                      <span class="flex items-center gap-1">
                        <Icon name="i-lucide-file-text" class="w-3.5 h-3.5" />
                        {{ formatNumber(form.submissions_count) }}
                      </span>
                      <span class="hidden sm:inline">Edited {{ form.last_edited_human }}</span>
                    </div>
                    <FormStatusBadges size="xs" :form="form" />
                  </div>
                </div>

                <div class="flex items-center gap-2.5">
                  <UButton
                    v-if="form.visibility === 'draft'"
                    color="neutral"
                    variant="outline"
                    size="sm"
                    class="rounded-xl border-[var(--sf-border-button)] hover:border-[var(--sf-hover-border)]"
                    icon="i-tabler:eye"
                    @click="showDraftFormWarningNotification"
                  >
                    <span class="hidden sm:inline">Preview</span>
                  </UButton>
                  <TrackClick
                    v-else
                    name="view_form_click"
                    :properties="{form_id:form.id, form_slug:form.slug}"
                  >
                    <UButton
                      target="_blank"
                      :to="form.share_url"
                      color="neutral"
                      variant="outline"
                      size="sm"
                      class="rounded-xl border-[var(--sf-border-button)] hover:border-[var(--sf-hover-border)]"
                      icon="i-lucide-external-link"
                    >
                      <span class="hidden sm:inline">Open form</span>
                    </UButton>
                  </TrackClick>
                  <TrackClick
                    v-if="!workspace?.is_readonly"
                    name="edit_form_click"
                    :properties="{form_id: form.id, form_slug: form.slug}"
                  >
                    <UButton
                      size="sm"
                      class="btn-primary rounded-xl"
                      :to="{ name: 'forms-slug-edit', params: { slug: form.slug } }"
                    >
                      <Icon name="i-lucide-pencil" class="w-4 h-4" />
                      <span class="hidden sm:inline">Edit form</span>
                    </UButton>
                  </TrackClick>
                  <extra-menu
                    v-if="!workspace?.is_readonly"
                    :form="form"
                    portal="#form-show-portals"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Page Content -->
          <div :class="['flex-1 bg-[var(--sf-bg-page)]', { 'overflow-y-auto': !isSubmissionsPage }]">
            <div class="px-6 pt-5">
              <div class="mx-auto max-w-5xl">
                <FormCleanings :form="form" />
              </div>
            </div>
            <NuxtPage :form="form" />
          </div>
        </template>

        <!-- Not Found State -->
        <div
          v-else
          class="flex items-center justify-center flex-1 bg-[var(--sf-bg-surface)]"
        >
          <DashboardEmptyState
            icon="i-lucide-search"
            title="Form not found"
            description="The form you're looking for doesn't exist or has been deleted."
          >
            <template #action>
              <UButton
                variant="soft"
                class="hover:no-underline"
                icon="i-lucide-arrow-left"
                :to="{ name: 'home' }"
              >
                Go to Dashboard
              </UButton>
            </template>
          </DashboardEmptyState>
        </div>

        <div id="form-show-portals" class="z-20" />
      </div>
    </main>
  </div>
</template>

<script setup>
import { formatNumber } from "~/lib/utils.js"
import DashboardEmptyState from "~/components/dashboard/states/DashboardEmptyState.vue"
import FormSidebar from "../../../components/layouts/FormSidebar.vue"
import ExtraMenu from "../../../components/pages/forms/show/ExtraMenu.vue"
import FormCleanings from "../../../components/pages/forms/show/FormCleanings.vue"
import FormStatusBadges from "../../../components/open/forms/components/FormStatusBadges.vue"
import TrackClick from "../../../components/global/TrackClick.vue"

definePageMeta({
  layout: "empty",
})

useOpnSeoMeta({
  title: "Home",
})

// Composables
const route = useRoute()
const workingFormStore = useWorkingFormStore()
const { detail: formDetail } = useForms()

const slug = route.params.slug

// Get current workspace
const { current: workspaceRef } = useCurrentWorkspace()
const workspace = workspaceRef.value

// Get form by slug using private authenticated endpoint
const { data: form, isLoading: isFormLoading, isFetched: isFormFinished } = formDetail(slug, { usePrivate: true })

// Combined loading state
const isLoading = computed(() => isFormLoading.value)

// Disable sticky top-bar behaviour on the submissions page only
const isSubmissionsPage = computed(() => route.name?.includes('submissions'))

// Update working form store when form changes
watch(
  () => form.value,
  (newForm) => {
    workingFormStore.reset()
    if (newForm) {
      workingFormStore.set(newForm)
    }
  },
  { immediate: true }
)

const showDraftFormWarningNotification = () => {
  useAlert().warning(
    "This form is currently in Draft mode and is not publicly accessible, You can change the form status on the edit form page.",
  )
}
</script>

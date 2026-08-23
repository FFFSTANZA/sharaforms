<template>
  <VForm size="sm">
    <div class="px-1 space-y-4">
      <!-- SEO Meta Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center justify-between mb-5">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-[var(--sf-nav-active-bg)] flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-link text-[12px] text-[var(--sf-coral-500)]"></i>
            </div>
            <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">
              SEO & Social Sharing
              <PlanTag class="ml-1" feature="seo_meta" upgrade-modal-title="Upgrade to Enhance Your Form's SEO" upgrade-modal-description="Explore advanced SEO features in the editor on our Free plan." />
            </h3>
          </div>
          <UButton
            label="Help"
            icon="i-lucide-circle-question-mark"
            variant="outline"
            color="neutral"
            size="xs"
            @click="crisp.openHelpdeskArticle('how-do-i-add-custom-seo-settings-to-my-forms-url-preview-1v9y9a')"
          />
        </div>

        <template v-if="form.seo_meta">
          <div class="flex flex-col lg:flex-row gap-8 lg:items-start">
            <div class="flex-1 space-y-4 max-w-xs">
              <SelectInput
                v-if="useFeatureFlag('custom_domains')"
                v-model="form.custom_domain"
                :clearable="true"
                :disabled="customDomainOptions.length <= 0"
                :options="customDomainOptions"
                name="type"
                label="Form Domain"
                placeholder="yourdomain.com"
              />
              <text-input v-model="form.seo_meta.page_title" name="page_title" label="Page Title" help="Max 60 characters recommended" />
              <text-area-input v-model="form.seo_meta.page_description" name="page_description" label="Page Description" help="Between 150 and 160 characters" />
              <image-input v-model="form.seo_meta.page_thumbnail" name="page_thumbnail" label="Thumbnail Image" help="og:image - 1200px X 800px" />
              <image-input v-model="form.seo_meta.page_favicon" name="page_favicon" label="Favicon Image" help="Public form page favicon" />
            </div>
            <SeoPreview :form="form" />
          </div>
        </template>
      </div>

      <!-- Link Privacy Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-teal-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-lock text-[12px] text-[var(--sf-teal)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Link Privacy</h3>
        </div>
        <p class="text-[var(--sf-text-caption)] text-sm mb-4">
          Disable to prevent Google from listing your form in search results.
        </p>
        <ToggleSwitchInput name="can_be_indexed" :form="form" label="Indexable by Google" />
      </div>

      <!-- Custom URL Card -->
      <div v-if="useFeatureFlag('self_hosted')" class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-indigo-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-globe text-[12px] text-[var(--sf-indigo)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Custom Form URL</h3>
        </div>
        <p class="text-[var(--sf-text-caption)] text-sm mb-4">
          Create a custom URL for your form. This will be the unique identifier in your form's URL.
        </p>
        <text-input
          :form="form"
          name="slug"
          class="max-w-xs"
          label="Custom Form URL"
          help="Use only lowercase letters, numbers, and hyphens. Example: my-custom-form"
        />
      </div>
    </div>
  </VForm>
</template>

<script setup>
const crisp = useCrisp()
import PlanTag from "~/components/app/PlanTag.vue"
import SeoPreview from "~/components/open/forms/components/SeoPreview.vue"

const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)

const { current: workspace } = useCurrentWorkspace()

const customDomainOptions = computed(() => {
  return workspace?.value?.custom_domains
    ? workspace?.value?.custom_domains.map((domain) => {
        return {
          name: domain,
          value: domain,
        }
      })
    : []
})

onMounted(() => {
  if (!form.value.seo_meta || Array.isArray(form.value.seo_meta))
    form.value.seo_meta = {}

  form.value.seo_meta = {
    ...form.value.seo_meta,
    page_title: form.value.seo_meta.page_title === undefined ? null : form.value.seo_meta.page_title,
    page_description: form.value.seo_meta.page_description === undefined ? null : form.value.seo_meta.page_description,
    page_thumbnail: form.value.seo_meta.page_thumbnail === undefined ? null : form.value.seo_meta.page_thumbnail,
    page_favicon: form.value.seo_meta.page_favicon === undefined ? null : form.value.seo_meta.page_favicon,
  }

  if (form.value.custom_domain && workspace.value?.custom_domains && !workspace.value.custom_domains.find((item) => { return item === form.value.custom_domain })) {
    form.value.custom_domain = null
  }
})
</script>

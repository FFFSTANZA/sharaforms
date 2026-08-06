<template>
  <div class="p-4 flex gap-2 group items-center relative border rounded-lg shadow-xs hover:bg-neutral-100 transition-all cursor-pointer">
    <!-- Title -->
    <div class="flex-grow items-center truncate relative">
      <span class="font-semibold text-neutral-900 dark:text-white">{{ form.title }}</span>
    </div>
    
    <!-- Stats and Menu -->
    <div class="flex items-center gap-4 relative text-sm text-neutral-500">
      <!-- Status Badges -->
      <FormStatusBadges class="hidden md:block" :form="form" :with-tags="false" size="sm" />
      
      <!-- Last Updated -->
      <span class="hidden lg:inline text-xs whitespace-nowrap" title="Last updated">Updated {{ form.last_edited_human }}</span>

      <!-- Views -->
      <UTooltip :text="`${formatNumberWithCommas(form.views_count)} views`">
        <div class="flex items-center gap-1" title="Form views">
          <UIcon name="i-tabler:eye" class="h-3.5 w-3.5 text-neutral-400" />
          <span class="text-neutral-500">{{ formatNumber(form.views_count) }}</span>
        </div>
      </UTooltip>
      
      <!-- Submissions -->
      <UTooltip :text="`${formatNumberWithCommas(form.submissions_count)} submissions`">
        <div class="flex items-center gap-1" title="Form submissions">
          <UIcon name="i-heroicons:document-text" class="h-3.5 w-3.5 text-neutral-400" />
          <span class="text-neutral-500">{{ formatNumber(form.submissions_count) }}</span>
        </div>
      </UTooltip>

      <!-- Quick Actions (visible on hover) -->
      <div
        class="hidden group-hover:flex items-center gap-2.5 z-20 relative"
        @click.stop
      >
        <div class="w-px h-4 bg-neutral-300" />
        <UTooltip text="Copy link">
          <UButton
            icon="i-heroicons:link"
            color="neutral"
            variant="ghost"
            size="2xs"
            :ui="{ leadingIcon: 'h-3.5 w-3.5' }"
            class="text-neutral-400 hover:text-neutral-500 hover:bg-neutral-200"
            @click.stop="copyLink"
          />
        </UTooltip>
        <UTooltip text="Preview">
          <UButton
            icon="i-tabler:eye"
            color="neutral"
            variant="ghost"
            size="2xs"
            :ui="{ leadingIcon: 'h-3.5 w-3.5' }"
            class="text-neutral-400 hover:text-neutral-500 hover:bg-neutral-200"
            @click.stop="previewForm"
          />
        </UTooltip>
        <UTooltip
          text="Duplicate"
          :text-loading="isDuplicating ? 'Duplicating...' : undefined"
        >
          <UButton
            icon="i-heroicons:document-duplicate"
            color="neutral"
            variant="ghost"
            size="2xs"
            :ui="{ leadingIcon: 'h-3.5 w-3.5' }"
            class="text-neutral-400 hover:text-neutral-500 hover:bg-neutral-200"
            :loading="isDuplicating"
            @click.stop="duplicateForm"
          />
        </UTooltip>
      </div>
      
      <!-- Extra Menu -->
      <div class="relative z-20">
        <ExtraMenu :form="form" :is-main-page="true" portal="#home-portals">
          <template #default="{ loading }">
            <UButton
              color="neutral"
              variant="ghost"
              icon="i-heroicons:ellipsis-horizontal"
              size="2xs"
              :ui="{ leadingIcon: 'h-3.5 w-3.5' }"
              class="text-neutral-400 hover:text-neutral-500 hover:bg-neutral-200"
              :loading="loading"
            />
          </template>
        </ExtraMenu>
      </div>
    </div>

    <!-- Link overlay covering entire card -->
    <NuxtLink
      :to="{name:'forms-slug-show-submissions', params: {slug:form.slug}}"
      class="absolute inset-0 z-10"
    />
  </div>
</template>

<script setup>
import ExtraMenu from "~/components/pages/forms/show/ExtraMenu.vue"
import FormStatusBadges from "~/components/open/forms/components/FormStatusBadges.vue"
import { formatNumber, formatNumberWithCommas } from "~/lib/utils.js"

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
})

const { copy } = useClipboard()
const router = useRouter()
const { duplicate } = useForms()
const duplicateFormMutation = duplicate()
const isDuplicating = computed(() => duplicateFormMutation.isPending.value)

const copyLink = () => {
  if (props.form.visibility === 'draft') {
    useAlert().warning("This form is currently in Draft mode and is not publicly accessible.")
    return
  }
  copy(props.form.share_url)
  useAlert().success("Link copied!")
}

const previewForm = () => {
  if (props.form.visibility === 'draft') {
    useAlert().warning("This form is currently in Draft mode.")
    return
  }
  window.open(props.form.share_url, '_blank')
}

const duplicateForm = () => {
  duplicateFormMutation.mutateAsync(props.form.id).then((data) => {
    router.push({
      name: "forms-slug-show",
      params: { slug: data.new_form.slug },
    })
    useAlert().success(data.message)
  }).catch((error) => {
    useAlert().error(error.data?.message || "Failed to duplicate form")
  })
}
</script> 
<template>
  <div
    class="group relative flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3.5 transition-colors hover:bg-[#F0F1F4] cursor-pointer"
  >
    <!-- Type icon tile -->
    <div
      :class="iconTileClass"
      class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
    >
      <UIcon :name="iconName" class="h-5 w-5" />
    </div>

    <!-- Title + slug + tags -->
    <div class="min-w-0 flex-1">
      <span
        class="block truncate text-[13px] font-semibold text-[#1D1F24] dark:text-white"
      >
        {{ form.title }}
      </span>
      <div class="mt-1 flex items-center gap-1.5 text-xs text-[#A7ABB2]">
        <span class="truncate">{{ form.slug }}</span>
        <template v-if="form.tags?.length">
          <span class="text-[#C7C9CE]">·</span>
          <span class="flex flex-wrap items-center gap-1">
            <UBadge
              v-for="tag in form.tags"
              :key="tag"
              color="neutral"
              variant="subtle"
              size="xs"
              class="capitalize"
            >
              {{ tag }}
            </UBadge>
          </span>
        </template>
      </div>
    </div>

    <!-- Stats and Menu -->
    <div class="flex shrink-0 items-center gap-3 sm:gap-4 text-[13px] text-[#8E9198]">
      <!-- Status Badges -->
      <FormStatusBadges
        :form="form"
        :with-tags="false"
        size="xs"
        class="hidden md:flex"
      />

      <!-- Last Updated -->
      <span
        class="hidden lg:inline text-xs whitespace-nowrap text-[#A7ABB2]"
        title="Last updated"
      >
        Updated {{ form.last_edited_human }}
      </span>

      <!-- Views -->
      <UTooltip :text="`${formatNumberWithCommas(form.views_count)} views`">
        <div class="flex items-center gap-1.5" title="Form views">
          <UIcon name="i-tabler:eye" class="h-3.5 w-3.5 text-neutral-400" />
          <span class="text-xs text-neutral-500">{{ formatNumber(form.views_count) }}</span>
        </div>
      </UTooltip>

      <!-- Submissions -->
      <UTooltip :text="`${formatNumberWithCommas(form.submissions_count)} submissions`">
        <div class="flex items-center gap-1.5" title="Form submissions">
          <UIcon name="i-heroicons:document-text" class="h-3.5 w-3.5 text-neutral-400" />
          <span class="text-xs text-neutral-500">{{ formatNumber(form.submissions_count) }}</span>
        </div>
      </UTooltip>

      <!-- Quick Actions (visible on hover) -->
      <div
        class="hidden group-hover:flex items-center gap-1 z-20 relative"
        @click.stop
      >
        <div class="w-px h-4 bg-neutral-300 mx-0.5" />
        <UTooltip text="Copy link">
          <UButton
            icon="i-heroicons:link"
            color="neutral"
            variant="ghost"
            size="2xs"
            square
            :ui="{ leadingIcon: 'h-3.5 w-3.5' }"
            class="text-neutral-400 hover:text-neutral-600 hover:bg-neutral-200"
            @click.stop="copyLink"
          />
        </UTooltip>
        <UTooltip text="Preview">
          <UButton
            icon="i-tabler:eye"
            color="neutral"
            variant="ghost"
            size="2xs"
            square
            :ui="{ leadingIcon: 'h-3.5 w-3.5' }"
            class="text-neutral-400 hover:text-neutral-600 hover:bg-neutral-200"
            @click.stop="previewForm"
          />
        </UTooltip>
        <UTooltip
          :text="isDuplicating ? 'Duplicating...' : 'Duplicate'"
        >
          <UButton
            icon="i-heroicons:document-duplicate"
            color="neutral"
            variant="ghost"
            size="2xs"
            square
            :ui="{ leadingIcon: 'h-3.5 w-3.5' }"
            class="text-neutral-400 hover:text-neutral-600 hover:bg-neutral-200"
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
              square
              :ui="{ leadingIcon: 'h-3.5 w-3.5' }"
              class="text-neutral-400 hover:text-neutral-600 hover:bg-neutral-200"
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

const isClosed = computed(
  () =>
    props.form.visibility === "closed" ||
    props.form.is_closed ||
    props.form.max_number_of_submissions_reached,
)

const iconName = computed(() => {
  if (props.form.visibility === "draft") return "i-lucide-square-pen"
  if (isClosed.value) return "i-lucide-lock-keyhole"
  return "i-lucide-clipboard-list"
})

const iconTileClass = computed(() => {
  if (props.form.visibility === "draft") return "bg-[#FDF6EB] text-[#d97706]"
  if (isClosed.value) return "bg-[#F0F1F4] text-[#8E9198]"
  return "bg-[#E4F4F8] text-[#0891b2]"
})

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

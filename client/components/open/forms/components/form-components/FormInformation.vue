<template>
  <VForm size="sm">
    <div class="px-1 space-y-4">
      <!-- Form Details Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-nav-active-bg)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-circle-info text-[12px] text-[var(--sf-coral-500)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Form Details</h3>
        </div>

        <div class="space-y-4">
          <text-input
            :form="form"
            name="title"
            class="max-w-xs"
            label="Form Name"
            placeholder="My form"
            :max-char-limit="255"
            :show-char-limit="true"
          />
          <select-input
            name="tags"
            label="Tags"
            clearable
            :form="form"
            help="To organize your forms"
            placeholder="Select Tag(s)"
            class="max-w-xs"
            :multiple="true"
            :allow-creation="true"
            :options="allTagsOptions"
          />
        </div>
      </div>

      <!-- Visibility Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-indigo-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-eye text-[12px] text-[var(--sf-indigo)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Visibility</h3>
        </div>

        <flat-select-input
          name="visibility"
          label="Form Visibility"
          class="max-w-xs"
          :form="form"
          placeholder="Select Visibility"
          :options="visibilityOptions"
        />

        <div
          v-if="isFormClosingOrClosed"
          class="mt-4 bg-[var(--sf-bg-muted)] border border-[var(--sf-border-card)] rounded-xl px-4 py-3"
        >
          <rich-text-area-input
            name="closed_text"
            :allow-fullscreen="true"
            :form="form"
            label="Closed form text"
            help="This message will be shown when the form will be closed"
            :required="false"
            wrapper-class="mb-0"
          />
        </div>
      </div>

      <!-- Copy Settings -->
      <UButton
        v-if="copyFormOptions.length > 0"
        color="neutral"
        variant="outline"
        icon="i-lucide-copy"
        @click.prevent="showCopyFormSettingsModal = true"
      >
        Copy another form's settings
      </UButton>
    </div>
  </VForm>
    
  <UModal
    v-model:open="showCopyFormSettingsModal"
    @close="showCopyFormSettingsModal = false"
  >
    <template #header>
      <div class="flex items-center w-full gap-4 px-2">
        <h2 class="text-lg font-semibold">
          Import Settings from another form
        </h2>
      </div>
    </template>
    <template #body>
      <VForm size="sm">
        <select-input
          v-model="copyFormId"
          name="copy_form_id"
          label="Copy Settings From"
          placeholder="Choose a form"
          :searchable="copyFormOptions.length > 5"
          :options="copyFormOptions"
        />
        <div class="mt-4 flex items-center justify-between">
          <UButton
            @click="copySettings"
          >
            Confirm & Copy
          </UButton>
          <UButton
            color="neutral"
            variant="outline"
            @click="showCopyFormSettingsModal = false"
          >
            Cancel
          </UButton>
        </div>
      </VForm>
    </template>
  </UModal>
</template>

<script setup>
import clonedeep from 'clone-deep'
import { default as _has } from 'lodash/has'

const alert = useAlert()
const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)

// Get forms list for current workspace
const { currentId: workspaceId } = useCurrentWorkspace()
const { forms } = useFormsList(workspaceId, {
  enabled: computed(() => !!workspaceId.value)
})

// Reactive state
const showCopyFormSettingsModal = ref(false)
const copyFormId = ref(null)

// Computed properties
const visibilityOptions = [
  {
    name: 'Published',
    value: 'public',
  },
  {
    name: 'Draft - not publicly accessible',
    value: 'draft',
  },
  {
    name: 'Closed - won\'t accept new submissions',
    value: 'closed',
  },
]

const copyFormOptions = computed(() => {
  if (!forms.value) return []
  return forms.value
    .filter((formItem) => {
      return form.value.id !== formItem.id
    })
    .map((formItem) => {
      return {
        name: formItem.title,
        value: formItem.id,
      }
    })
})

const allTagsOptions = computed(() => {
  if (!forms.value) return []
  
  // Extract all unique tags from forms
  let tags = []
  forms.value.forEach((formItem) => {
    if (formItem.tags && formItem.tags.length) {
      if (typeof formItem.tags === "string" || formItem.tags instanceof String) {
        tags = tags.concat(formItem.tags.split(","))
      } else if (Array.isArray(formItem.tags)) {
        tags = tags.concat(formItem.tags)
      }
    }
  })
  
  return [...new Set(tags)].map((tagname) => {
    return {
      name: tagname,
      value: tagname,
    }
  })
})

// New computed property for v-if condition
const isFormClosingOrClosed = computed(() => {
  return form.value.closes_at || form.value.visibility === 'closed'
})

// Methods
const copySettings = () => {
  if (copyFormId.value == null) {
    alert.error('Please select a form to copy settings from')
    return
  }

  const copyForm = clonedeep(
    forms.value?.find(form => form.id === copyFormId.value),
  )
  if (!copyForm)
    return;

  // Clean copy from form
  [
    "title",
    "properties",
    "cleanings",
    "views_count",
    "submissions_count",
    "workspace",
    "workspace_id",
    "updated_at",
    "share_url",
    "slug",
    "notion_database_url",
    "id",
    "database_id",
    "database_fields_update",
    "creator",
    "created_at",
    "deleted_at",
    "last_edited_human",
  ].forEach((property) => {
    if (_has(copyForm, property))
      delete copyForm[property]
  })

  // Apply changes
  Object.keys(copyForm).forEach((property) => {
    form.value[property] = copyForm[property]
  })
  showCopyFormSettingsModal.value = false
  alert.success('Form settings copied.')
}
</script>

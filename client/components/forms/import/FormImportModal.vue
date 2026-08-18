<template>
  <UModal
    v-model:open="isOpen"
    :ui="{ content: 'sm:max-w-2xl overflow-hidden' }"
    :dismissible="!loading"
  >
    <template #header>
      <div class="flex w-full items-start justify-between gap-4">
        <div class="flex min-w-0 items-start gap-3">
          <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600">
            <Icon
              name="i-lucide-download"
              class="h-5 w-5"
            />
          </span>
          <div class="min-w-0">
            <h3 class="text-base font-semibold leading-6 text-neutral-950 dark:text-white">
              Import form
            </h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
              Paste a supported form URL and SharaForms will detect the provider.
            </p>
          </div>
        </div>
        <UButton
          color="neutral"
          variant="ghost"
          icon="i-lucide-x"
          class="-mr-2 -mt-1"
          @click="isOpen = false"
        />
      </div>
    </template>

    <template #body>
      <form @submit.prevent="submitImport">
        <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-[0_20px_55px_-38px_rgba(15,23,42,0.55)]">
          <div class="flex items-center gap-3 border-b border-neutral-100 bg-neutral-50/80 px-3 py-2">
            <span
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border text-sm shadow-sm"
              :class="activeSourceConfig ? activeSourceConfig.iconWrapClass : 'border-neutral-200 bg-white text-neutral-500'"
            >
              <Icon
                :name="activeSourceConfig?.icon || 'i-lucide-link'"
                :class="activeSourceConfig?.iconClass || 'h-4 w-4'"
              />
            </span>
            <div class="min-w-0 flex-1">
              <input
                v-model="importForm.url"
                type="text"
                inputmode="url"
                name="url"
                autocomplete="url"
                aria-label="Form URL"
                :placeholder="activePlaceholder"
                :disabled="loading"
                class="w-full border-0 bg-transparent px-0 py-2 text-base font-medium text-neutral-950 placeholder:text-neutral-400 focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-70"
                @input="handleUrlInput"
              >
            </div>
            <UButton
              type="submit"
              icon="i-lucide-download"
              :loading="loading"
              :disabled="!canSubmit"
              :color="canSubmit ? 'primary' : 'neutral'"
              :variant="canSubmit ? 'solid' : 'soft'"
              label="Import"
              class="shrink-0"
            />
          </div>

          <div class="flex flex-col gap-3 px-3 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div
              class="flex min-w-0 items-start gap-2 text-sm"
              :class="statusClass"
            >
              <Icon
                :name="statusIcon"
                class="mt-0.5 h-4 w-4 shrink-0"
              />
              <span class="min-w-0">{{ statusMessage }}</span>
            </div>
            <button
              v-if="importForm.url"
              type="button"
              class="w-fit text-xs font-medium text-neutral-400 transition hover:text-neutral-700"
              @click="clearUrl"
            >
              Clear
            </button>
          </div>
        </div>
      </form>

      <div class="mt-4">
        <div class="mb-2 flex items-center justify-between gap-3">
          <p class="text-xs font-medium uppercase text-neutral-400">
            Supported imports
          </p>
          <UBadge
            v-if="detectedSourceConfig && !sourceIssue"
            color="primary"
            variant="soft"
            size="sm"
            :label="detectedSourceConfig.label + ' detected'"
          />
        </div>

        <div class="grid grid-cols-1 gap-2 sm:grid-cols-4">
          <div
            v-for="source in supportedSources"
            :key="source.id"
            class="min-w-0 rounded-lg border bg-white p-3 shadow-sm transition duration-200"
            :class="sourceCardClass(source)"
          >
            <div class="mb-3 flex items-start justify-between gap-2">
              <span
                class="flex h-9 w-9 items-center justify-center rounded-lg border shadow-sm"
                :class="source.iconWrapClass"
              >
                <Icon
                  :name="source.icon"
                  :class="source.iconClass"
                />
              </span>
              <Icon
                v-if="isSourceActive(source) && !sourceIssue"
                name="i-lucide-circle-check"
                class="h-4 w-4 text-blue-600"
              />
            </div>
            <p class="text-sm font-semibold text-neutral-950">{{ source.label }}</p>
            <p
              class="mt-1 max-w-full truncate text-xs leading-5 text-neutral-500"
              :title="source.domain"
            >
              {{ source.domain }}
            </p>
          </div>
        </div>
      </div>
    </template>
  </UModal>
</template>

<script setup>
import { formsApi } from '~/api/forms'
import { detectFormImportSource } from '~/lib/forms/detect-form-import-source'

const props = defineProps({
  show: { type: Boolean, required: true },
  defaultSource: { type: String, default: null },
})

const emit = defineEmits(['close', 'imported'])

const isOpen = computed({
  get: () => props.show,
  set: (val) => {
    if (!val) emit('close')
  },
})

const importForm = useForm({
  url: '',
})

const loading = ref(false)
const importError = ref('')
const suggestedSource = ref(null)

const sourceConfigs = {
  typeform: {
    id: 'typeform',
    label: 'Typeform',
    domain: 'typeform.com/to/...',
    placeholder: 'https://yourname.typeform.com/to/FORM_ID',
    icon: 'i-simple-icons-typeform',
    iconClass: 'h-4 w-4 text-[#262627]',
    iconWrapClass: 'border-neutral-200 bg-white text-neutral-950',
  },
  tally: {
    id: 'tally',
    label: 'Tally',
    domain: 'tally.so/r/...',
    placeholder: 'https://tally.so/r/FORM_ID',
    icon: 'sharaforms:tally',
    iconClass: 'h-4 w-4 text-neutral-600',
    iconWrapClass: 'border-neutral-200 bg-neutral-100 text-neutral-600',
  },
  fillout: {
    id: 'fillout',
    label: 'Fillout',
    domain: 'fillout.com/t/...',
    placeholder: 'https://company.fillout.com/t/FORM_ID',
    icon: 'i-simple-icons-fillout',
    iconClass: 'h-4 w-4 text-neutral-600',
    iconWrapClass: 'border-neutral-200 bg-neutral-100 text-neutral-600',
  },
  google_forms: {
    id: 'google_forms',
    label: 'Google Forms',
    domain: 'docs.google.com/forms/d/...',
    placeholder: 'https://docs.google.com/forms/d/FORM_ID/edit',
    icon: 'i-simple-icons-googleforms',
    iconClass: 'h-4 w-4 text-neutral-600',
    iconWrapClass: 'border-neutral-200 bg-neutral-100 text-neutral-600',
  },
}

const supportedSources = computed(() => Object.values(sourceConfigs))
const supportedSourceConfigs = computed(() => {
  return Object.fromEntries(supportedSources.value.map(source => [source.id, source]))
})
const importDetection = computed(() => detectFormImportSource(importForm.url))
const detectedSource = computed(() => importDetection.value.source)
const detectedSourceConfig = computed(() => supportedSourceConfigs.value[detectedSource.value] ?? null)
const activeSourceConfig = computed(() => detectedSourceConfig.value ?? supportedSourceConfigs.value[suggestedSource.value] ?? null)
const supportedSourcesLabel = computed(() => formatSourceList(supportedSources.value.map(source => source.label)))
const activePlaceholder = computed(() => activeSourceConfig.value?.placeholder ?? `Paste a ${supportedSourcesLabel.value} URL`)
const sourceIssue = computed(() => importDetection.value.reason)

const importTargetReady = computed(() => !!detectedSource.value)

const canSubmit = computed(() => {
  if (loading.value || sourceIssue.value) {
    return false
  }

  return !!importForm.url && !!detectedSource.value
})

const statusIcon = computed(() => {
  if (importError.value || sourceIssue.value || (importForm.url && !detectedSource.value)) {
    return 'i-lucide-circle-alert'
  }

  if (importTargetReady.value) {
    return 'i-lucide-circle-check'
  }

  return 'i-lucide-file-input'
})

const statusClass = computed(() => {
  if (importError.value || sourceIssue.value || (importForm.url && !detectedSource.value)) {
    return 'text-red-600'
  }

  if (importTargetReady.value) {
    return 'text-blue-600'
  }

  return 'text-neutral-500'
})

const statusMessage = computed(() => {
  if (importError.value) {
    return importError.value
  }

  if (!importForm.url) {
    return 'Paste a URL and we will detect the import source automatically.'
  }

  if (sourceIssue.value) {
    return issueMessage(sourceIssue.value)
  }

  if (!detectedSource.value) {
    return 'This URL is not from a supported import source.'
  }

  return `${detectedSourceConfig.value.label} detected. Ready to import.`
})

watch(() => props.show, (open) => {
  if (!open) {
    loading.value = false
    importError.value = ''
    return
  }

  suggestedSource.value = props.defaultSource ?? null
  importForm.url = ''
  importForm.errors.clear()
  importError.value = ''
})

watch(detectedSource, () => {
  importError.value = ''
  importForm.errors.clear()
})

const handleUrlInput = () => {
  importError.value = ''
  importForm.errors.clear()
}

const clearUrl = () => {
  importForm.url = ''
  importError.value = ''
  importForm.errors.clear()
}

const isSourceActive = (source) => {
  return detectedSource.value === source.id
}

const sourceCardClass = (source) => {
  if (isSourceActive(source) && !sourceIssue.value) {
    return 'border-blue-200 ring-1 ring-blue-100'
  }

  return 'border-neutral-200'
}

const submitImport = () => {
  if (loading.value) return

  if (!detectedSource.value) {
    importError.value = importForm.url ? 'This URL is not from a supported import source.' : 'A form URL is required.'
    return
  }

  if (sourceIssue.value) {
    importError.value = issueMessage(sourceIssue.value)
    return
  }

  loading.value = true
  importError.value = ''

  formsApi.import({
    source: detectedSource.value,
    import_data: {
      url: importDetection.value.normalizedUrl,
    },
  })
    .then((response) => {
      useAlert().success(response.message || 'Form imported successfully!')
      emit('imported', response.form)
      emit('close')
    })
    .catch((error) => {
      const message = error?.data?.message || error?.message || 'Failed to import form. Please check the URL and try again.'
      importError.value = message
      useAlert().error(message)
    })
    .finally(() => {
      loading.value = false
    })
}

function issueMessage(reason) {
  if (reason === 'invalid_url') {
    return 'Enter a valid URL, for example https://yourname.typeform.com/to/FORM_ID.'
  }

  if (reason === 'google_published_url' || reason === 'google_edit_url') {
    return 'Use a Google Forms URL like https://docs.google.com/forms/d/FORM_ID/edit.'
  }

  if (reason === 'typeform_form_id') {
    return 'Use a Typeform public URL like https://yourname.typeform.com/to/FORM_ID.'
  }

  return `This URL is not from ${supportedSourcesLabel.value}.`
}

function formatSourceList(sources) {
  if (sources.length <= 1) {
    return sources[0] || 'a supported source'
  }

  if (sources.length === 2) {
    return `${sources[0]} or ${sources[1]}`
  }

  return `${sources.slice(0, -1).join(', ')}, or ${sources.at(-1)}`
}
</script>
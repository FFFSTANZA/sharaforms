<template>
  <VForm size="sm">
    <div class="px-1 space-y-4">
      <!-- Button Labels Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-nav-active-bg)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-paper-plane text-[12px] text-[var(--sf-coral-500)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Submission Settings</h3>
        </div>

        <div class="flex flex-wrap items-end gap-4 mb-4">
          <TextInput
            v-if="!isFocused"
            v-model="classicSubmitText"
            name="classic_submit_button_text"
            class="max-w-xs"
            label="Submit button text"
            :placeholder="$t('forms.buttons.submit')"
          />
          <TextInput
            v-else
            v-model="focusedSubmitText"
            name="focused_submit_button_text"
            class="max-w-xs"
            label="Submit button text"
            :placeholder="$t('forms.buttons.submit')"
          />
          <TextInput
            v-if="isFocused"
            v-model="focusedNextText"
            name="focused_next_button_text"
            class="max-w-xs"
            label="Next button text"
            :placeholder="$t('forms.buttons.next')"
          />
          <TextInput
            v-if="isFocused"
            v-model="focusedPreviousText"
            name="focused_previous_button_text"
            class="max-w-xs"
            label="Previous button text"
            :placeholder="$t('forms.buttons.previous')"
          />
        </div>

        <div class="space-y-3">
          <ToggleSwitchInput
            name="auto_save"
            :form="form"
            label="Auto save form response"
            help="Saves form progress, allowing respondents to resume later."
            :disabled="hasPaymentBlock"
          />
          <UAlert
            v-if="hasPaymentBlock"
            color="primary"
            variant="subtle"
            title="Must be enabled with a payment block."
            class="max-w-md"
          />
        </div>
      </div>

      <!-- Database Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-teal-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-database text-[12px] text-[var(--sf-teal)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Database</h3>
        </div>

        <FlatSelectInput
          :form="submissionOptions"
          name="databaseAction"
          class="max-w-xs"
          label="Database Submission Action"
          :options="[
            { name: 'Create new record', value: 'create' },
            { name: 'Update existing record', value: 'update' }
          ]"
          :required="true"
        />

        <div
          v-if="submissionOptions.databaseAction == 'update'"
          class="mt-4 bg-[var(--sf-bg-muted)] border border-[var(--sf-border-card)] rounded-xl px-4 py-3"
        >
          <p class="text-[var(--sf-text-caption)] text-sm mb-3">
            When matching values are found in the selected column(s), the (first) existing record will be updated instead of creating a new record. If there's no match, a new record will be created.
            <a href="#" class="text-[var(--sf-teal)] hover:underline" @click.prevent="crisp.openHelpdeskArticle('how-to-update-a-record-on-form-submission-1t1jwmn')">Learn more.</a>
          </p>
          <select-input
            v-if="filterableFields.length"
            :form="form"
            name="database_fields_update"
            label="Properties to check on update"
            :options="filterableFields"
            multiple
            clearable
          />
          <toggle-switch-input
            v-model="clearEmptyFieldsOnUpdate"
            name="clear_empty_fields_on_update"
            class="mt-3"
            label="Clear empty fields on update"
            help="When enabled, fields left empty in submission will clear existing values in Submission. When disabled, only filled fields are updated."
          />
        </div>
      </div>

      <!-- Advanced Options Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-indigo-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-sliders text-[12px] text-[var(--sf-indigo)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Advanced Options</h3>
        </div>

        <div class="space-y-3">
          <ToggleSwitchInput
            name="enable_partial_submissions"
            :form="form"
            help="Capture incomplete form submissions to analyze user drop-off points and collect partial data even when users don't complete the entire form."
          >
            <template #label>
              <span class="text-sm">Collect partial submissions</span>
              <PlanTag feature="enable_partial_submissions" class="ml-1" upgrade-modal-title="Upgrade to collect partial submissions" upgrade-modal-description="Capture valuable data from incomplete form submissions." />
            </template>
          </ToggleSwitchInput>

          <ToggleSwitchInput
            name="enable_ip_tracking"
            :form="form"
            help="Collect and store submitter IP addresses for analytics, fraud prevention, and geographic insights."
          >
            <template #label>
              <span class="text-sm">Collect IP addresses</span>
              <PlanTag class="ml-1" feature="enable_ip_tracking" upgrade-modal-title="Upgrade to collect IP addresses" upgrade-modal-description="Automatically capture submitter IP addresses." />
            </template>
          </ToggleSwitchInput>

          <UAlert
            v-if="form.enable_ip_tracking"
            color="neutral"
            icon="i-lucide-shield-alert"
            variant="subtle"
            title="GDPR and Privacy Compliance"
            description="Ensure your privacy policy discloses IP address collection and obtain proper user consent where required."
            class="max-w-md"
          />
        </div>
      </div>

      <!-- Post-Submission Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-amber-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-flag-checkered text-[12px] text-[var(--sf-amber)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">
            After Submission
            <PlanTag class="ml-1" upgrade-modal-title="Upgrade to customize post-submission behavior" upgrade-modal-description="Customize post-submission behavior." />
          </h3>
        </div>

        <OptionSelectorInput
          label="Action after form submission"
          v-model="submissionOptions.submissionMode"
          :options="[
            { name: 'default', label: 'Show Success page' },
            { name: 'redirect', label: 'Redirect' }
          ]"
          option-key="name"
          :columns="2"
          class="mb-4 max-w-xs"
        />

        <div
          v-if="submissionOptions.submissionMode"
          class="bg-[var(--sf-bg-muted)] border border-[var(--sf-border-card)] rounded-xl px-4 py-3"
        >
          <template v-if="submissionOptions.submissionMode === 'redirect'">
            <MentionInput
              name="redirect_url"
              :form="form"
              :mentions="form.properties"
              :computed-variables="form.computed_variables"
              class="w-full max-w-xs"
              label="Redirect URL"
              placeholder="https://www.google.com"
              :required="true"
            />
          </template>
          <template v-else>
            <rich-text-area-input
              enable-mentions
              :mentions="form.properties"
              :computed-variables="form.computed_variables"
              :allow-fullscreen="true"
              name="submitted_text"
              class="w-full"
              :form="form"
              label="Success page text"
              :required="false"
              :max-char-limit="10000"
              :show-char-limit="true"
            />
            <div class="flex items-center flex-wrap gap-x-4 mt-4">
              <toggle-switch-input
                name="re_fillable"
                class="w-full max-w-xs"
                :form="form"
                label="Re-fillable form"
                help="Allows user to fill the form multiple times"
              />
              <text-input
                v-if="form.re_fillable"
                name="re_fill_button_text"
                :form="form"
                label="Text of re-start button"
              />
            </div>

            <div v-if="pdfTemplates.length > 0" class="flex items-center flex-wrap gap-x-4 mt-4">
              <toggle-switch-input
                name="pdf_download_enabled"
                class="w-full max-w-xs"
                :form="form"
                label="Show PDF download button"
                help="Allow respondents to download a PDF on the success page"
              />
              <template v-if="form.pdf_download_enabled">
                <text-input
                  name="pdf_download_button_text"
                  :form="form"
                  label="Text of download button"
                  placeholder="Download PDF"
                />
                <select-input
                  name="pdf_template_id"
                  class="w-full mt-4"
                  :form="form"
                  label="PDF template"
                  :options="pdfTemplateOptions"
                  :required="true"
                  help="Select the PDF template to use for the download button"
                />
              </template>
            </div>
          </template>
        </div>
      </div>

      <!-- Editable Submissions Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-green-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-pen-to-square text-[12px] text-[var(--sf-green)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">
            Editable Submissions
            <PlanTag class="ml-1" upgrade-modal-title="Upgrade to use Editable Submissions" upgrade-modal-description="Allow users to update their submissions via a unique URL." />
          </h3>
        </div>

        <toggle-switch-input
          name="editable_submissions"
          class="w-full max-w-sm"
          help="Allows users to edit submissions via unique URL"
          :form="form"
          label="Enable editable submissions"
        />
        <text-input
          v-if="form.editable_submissions"
          name="editable_submissions_button_text"
          class="w-full max-w-64 mt-4"
          :form="form"
          label="Edit submission button text"
          :required="true"
        />
      </div>
    </div>
  </VForm>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"
import { usePdfTemplates } from '~/composables/query/forms/usePdfTemplates'

const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)
const crisp = useCrisp()

// PDF Templates for success page download
const { list } = usePdfTemplates()
const { data: templatesData } = list(() => form.value?.id)

const pdfTemplates = computed(() => templatesData.value?.data || [])

const pdfTemplateOptions = computed(() => {
  return pdfTemplates.value.map(t => ({
    name: t.name || t.original_filename,
    value: t.id
  }))
})

// Auto-select first PDF template if only one exists
watch(pdfTemplates, (templates) => {
  if (templates.length > 0 && form.value?.pdf_download_enabled && !form.value?.pdf_template_id) {
    form.value.pdf_template_id = templates[0].id
  }
}, { immediate: true })

const submissionOptions = ref({})

const filterableFields = computed(() => {
  if (submissionOptions.value.databaseAction !== "update") return []
  return form.value.properties
    .filter((field) => {
      return (
        !field.hidden &&
        !["files", "signature", "multi_select", "matrix", 'payment'].includes(field.type)
      )
    })
    .map((field) => {
      return {
        name: field.name,
        value: field.id,
      }
    })
})

const clearEmptyFieldsOnUpdate = computed({
  get: () => form.value.clear_empty_fields_on_update ?? false,
  set: (value) => { form.value.clear_empty_fields_on_update = value }
})

watch({
  redirect_url: form.value.redirect_url,
  database_fields_update: form.value.database_fields_update
}, () => {
  if (form.value) {
    submissionOptions.value = {
      submissionMode: form.value.redirect_url ? 'redirect' : 'default',
      databaseAction: form.value.database_fields_update ? 'update' : 'create'
    }
  }
}, { immediate: true })

watch(submissionOptions, (val) => {
  if (val.submissionMode === 'default') form.value.redirect_url = null
  if (val.databaseAction === 'create') form.value.database_fields_update = null
}, { deep: true })

const hasPaymentBlock = computed(() => {
  return form.value.properties.some(property => property.type === 'payment')
})

const isFocused = computed(() => form.value?.presentation_style === 'focused' || form.value?.presentation_style === 'spotlight')

onMounted(() => {
  // Ensure translations is a plain, writable object (avoid writing into readonly proxies)
  const t = form.value?.translations
  if (!t || typeof t !== 'object' || Array.isArray(t)) {
    form.value.translations = {}
  } else {
    form.value.translations = { ...t }
  }
})

const focusedNextText = computed({
  get() {
    return form.value?.translations?.focused_next_button_text || ''
  },
  set(val) {
    const current = form.value?.translations && typeof form.value.translations === 'object' ? form.value.translations : {}
    // Replace the entire translations object to avoid setting into a readonly proxy
    form.value.translations = { ...current, focused_next_button_text: val }
  }
})

const focusedPreviousText = computed({
  get() {
    return form.value?.translations?.focused_previous_button_text || ''
  },
  set(val) {
    const current = form.value?.translations && typeof form.value.translations === 'object' ? form.value.translations : {}
    // Replace the entire translations object to avoid setting into a readonly proxy
    form.value.translations = { ...current, focused_previous_button_text: val }
  }
})

const classicSubmitText = computed({
  get() {
    // Fall back to the form-wide label so a classic-page form without its own
    // submit label still shows one; once edited it becomes page-specific.
    return form.value?.translations?.classic_submit_button_text || form.value?.submit_button_text || ''
  },
  set(val) {
    const current = form.value?.translations && typeof form.value.translations === 'object' ? form.value.translations : {}
    // Replace the entire translations object to avoid setting into a readonly proxy
    form.value.translations = { ...current, classic_submit_button_text: val }
  }
})

const focusedSubmitText = computed({
  get() {
    // Fall back to the form-wide label so a focused form without its own
    // submit label still shows one; once edited it becomes card-specific.
    return form.value?.translations?.focused_submit_button_text || form.value?.submit_button_text || ''
  },
  set(val) {
    const current = form.value?.translations && typeof form.value.translations === 'object' ? form.value.translations : {}
    // Replace the entire translations object to avoid setting into a readonly proxy
    form.value.translations = { ...current, focused_submit_button_text: val }
  }
})
</script>

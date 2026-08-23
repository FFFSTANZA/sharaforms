<template>
  <div class="px-3 pt-3 pb-6 space-y-4">
    <!-- Appearance Card -->
    <div class="rounded-xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-4 shadow-[0_1px_2px_rgba(23,25,35,0.04)]">
      <div class="flex items-center gap-2.5 mb-4">
        <div class="w-7 h-7 rounded-lg bg-[var(--sf-nav-active-bg)] flex items-center justify-center flex-shrink-0">
          <Icon name="lucide:paintbrush" class="w-3.5 h-3.5 text-[var(--sf-coral-500)]" />
        </div>
        <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Appearance</h3>
      </div>

      <PresentationStyleSwitch />

      <div class="space-y-3 pt-1">
        <select-input
          name="theme"
          :options="[
            { name: 'Default', value: 'default' },
            { name: 'Notion', value: 'notion' },
            { name: 'Simple (no shadows)', value: 'simple' },
            { name: 'Minimal', value: 'minimal' },
            { name: 'Transparent', value: 'transparent' }
          ]"
          :form="form"
          label="Form Theme"
        />

        <color-input
          name="color"
          :form="form"
          label="Accent Color"
        >
          <template #label>
            <InputLabel label="">Accent Color - <a
              href="#" class="text-[var(--sf-coral-500)]"
              @click.prevent="form.color = DEFAULT_COLOR"
            >Reset</a></InputLabel>
          </template>
        </color-input>

        <OptionSelectorInput
          v-model="form.dark_mode"
          :form="form"
          name="dark_mode"
          label="Color Mode"
          :options="[
            { name: 'auto', label: 'System', icon: 'i-lucide-monitor' },
            { name: 'light', label: 'Light', icon: 'i-lucide-sun' },
            { name: 'dark', label: 'Dark', icon: 'i-lucide-moon' },
          ]"
          :multiple="false"
          :columns="3"
        />
      </div>
    </div>

    <!-- Text & Language Card -->
    <div class="rounded-xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-4 shadow-[0_1px_2px_rgba(23,25,35,0.04)]">
      <div class="flex items-center gap-2.5 mb-4">
        <div class="w-7 h-7 rounded-lg bg-[var(--sf-teal-light)] flex items-center justify-center flex-shrink-0">
          <Icon name="octicon:typography-16" class="w-3.5 h-3.5 text-[var(--sf-teal)]" />
        </div>
        <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Text & Language</h3>
      </div>

      <div class="space-y-3">
        <div class="grid grid-cols-2 gap-3">
          <div class="flex-grow" v-if="useFeatureFlag('services.google.fonts')">
            <label class="text-[var(--sf-text-body)] font-semibold text-xs mb-1 block">Font Family</label>
            <button
              class="w-full flex items-center justify-between px-3 py-2 rounded-xl border border-[var(--sf-border-button)] bg-[var(--sf-bg-surface)] text-[13px] text-[var(--sf-text-body)] hover:border-[var(--sf-hover-border)] hover:bg-[var(--sf-nav-hover-bg)] transition-all duration-150"
              @click="showGoogleFontPicker = true"
            >
              <span :style="{ 'font-family': (form.font_family ? form.font_family + ' !important' : null) }">
                {{ form.font_family || 'Default' }}
              </span>
              <Icon name="i-lucide-chevron-down" class="w-3.5 h-3.5 text-[var(--sf-text-disabled)]" />
            </button>
            <GoogleFontPicker
              :show="showGoogleFontPicker"
              :font="form.font_family || null"
              @close="showGoogleFontPicker = false"
              @apply="onApplyFont"
            />
          </div>

          <div class="flex-grow">
            <select-input
              name="language"
              searchable
              :options="availableLocales"
              :form="form"
              label="Language"
            />
          </div>
        </div>

        <ToggleSwitchInput
          name="layout_rtl"
          :form="form"
          label="Right-to-Left Layout"
        />
        <toggle-switch-input
          name="uppercase_labels"
          :form="form"
          label="Uppercase Input Labels"
        />
      </div>
    </div>

    <!-- Layout & Sizing Card -->
    <div class="rounded-xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-4 shadow-[0_1px_2px_rgba(23,25,35,0.04)]">
      <div class="flex items-center gap-2.5 mb-4">
        <div class="w-7 h-7 rounded-lg bg-[var(--sf-indigo-light)] flex items-center justify-center flex-shrink-0">
          <Icon name="lucide:layers" class="w-3.5 h-3.5 text-[var(--sf-indigo)]" />
        </div>
        <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Layout & Sizing</h3>
      </div>

      <div class="space-y-3">
        <div class="grid grid-cols-2 gap-3">
          <OptionSelectorInput
            seamless
            label="Input Size"
            v-model="form.size"
            :form="form"
            name="size"
            :options="[
              { name: 'sm', label:'S'},
              { name: 'md', label:'M' },
              { name: 'lg', label:'L' },
            ]"
            :multiple="false"
            :columns="3"
            class="mb-0"
          />
          <OptionSelectorInput
            v-if="form.theme !== 'transparent'"
            label="Input Roundness"
            v-model="form.border_radius"
            seamless
            :form="form"
            name="border_radius"
            :options="[
              { name: 'none', icon: 'i-tabler-border-corner-square' },
              { name: 'small', icon: 'i-tabler-border-corner-rounded' },
              { name: 'full', icon: 'i-tabler-border-corner-pill' },
            ]"
            :multiple="false"
            :columns="3"
            class="mb-0"
          />
        </div>

        <OptionSelectorInput
          v-model="form.width"
          label="Form Width"
          :form="form"
          name="width"
          seamless
          v-if="!isFocused"
          :options="[
            { name: 'centered', label: 'Centered' },
            { name: 'full', label: 'Full Width' },
          ]"
          :multiple="false"
          :columns="2"
        />
      </div>
    </div>

    <!-- Branding Card -->
    <div class="rounded-xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-4 shadow-[0_1px_2px_rgba(23,25,35,0.04)]">
      <div class="flex items-center gap-2.5 mb-4">
        <div class="w-7 h-7 rounded-lg bg-[var(--sf-amber-light)] flex items-center justify-center flex-shrink-0">
          <Icon name="lucide:tag" class="w-3.5 h-3.5 text-[var(--sf-amber)]" />
        </div>
        <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Branding</h3>
      </div>

      <div class="space-y-3">
        <div class="grid grid-cols-2 gap-3">
          <image-input
            name="logo_picture"
            :form="form"
            label="Logo"
            :required="false"
          />

          <ImageWithSettings :form="form" name="cover_picture" :label="isFocused ? 'Background' : 'Cover (~1500px)'" kind="cover" />
        </div>

        <toggle-switch-input
          name="no_branding"
          :form="form"
          @update:model-value="onChangeNoBranding"
        >
          <template #label>
            <InputLabel
              :label="'Hide SharaForms Branding'"
              :native-for="'no_branding'"
              class="text-sm font-medium!"
            />
            <PlanTag
              upgrade-modal-title="Upgrade today to remove SharaForms branding"
              class="-mt-1 ml-2"
            />
          </template>
        </toggle-switch-input>
      </div>
    </div>

    <!-- Advanced Options Card -->
    <div class="rounded-xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-4 shadow-[0_1px_2px_rgba(23,25,35,0.04)]">
      <div class="flex items-center gap-2.5 mb-4">
        <div class="w-7 h-7 rounded-lg bg-[var(--sf-bg-muted)] flex items-center justify-center flex-shrink-0">
          <Icon name="lucide:settings" class="w-3.5 h-3.5 text-[var(--sf-text-caption)]" />
        </div>
        <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Advanced Options</h3>
      </div>

      <div class="space-y-1">
        <toggle-switch-input
          v-if="isFocused"
          name="settings.navigation_arrows"
          :form="form"
          label="Show navigation arrows"
        />
        <toggle-switch-input
          v-if="isFocused"
          name="settings.auto_next"
          :form="form"
          label="Auto-next on selection"
          help="Automatically move to the next page after selecting an option (checkbox, dropdown, etc.)"
        />
        <toggle-switch-input
          name="show_progress_bar"
          :form="form"
          label="Show progress bar"
          :help="
            form.show_progress_bar
              ? 'The bar is at the top of the page (above navigation in this editor) or below the title when embedded'
              : ''
          "
        />
        <toggle-switch-input
          name="transparent_background"
          :form="form"
          label="Transparent Background"
          help="When form is embedded"
        />
        <toggle-switch-input
          name="confetti_on_submission"
          :form="form"
          label="Confetti on successful submission"
          @update:model-value="onChangeConfettiOnSubmission"
        />
        <ToggleSwitchInput
          name="auto_focus"
          :form="form"
          label="Auto focus first input on page"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useWorkingFormStore } from "../../../../../stores/working_form"
import GoogleFontPicker from "../../../editors/GoogleFontPicker.vue"
import PlanTag from "~/components/app/PlanTag.vue"
import { DEFAULT_COLOR, ensureSettingsObject } from "@/composables/forms/initForm"
import PresentationStyleSwitch from "./PresentationStyleSwitch.vue"
import ImageWithSettings from "../media/ImageWithSettings.vue"


const workingFormStore = useWorkingFormStore()
const { openSubscriptionModal } = useAppModals()
const { hasFeature } = usePlanFeatures()
const form = storeToRefs(workingFormStore).content
const isMounted = ref(false)
const confetti = useConfetti()
const showGoogleFontPicker = ref(false)
const { $i18n } = useNuxtApp()

const isPro = computed(() => {
  return hasFeature('branding.removal')
})

const isFocused = computed(() => form.value?.presentation_style === 'focused' || form.value?.presentation_style === 'spotlight')

const availableLocales = computed(() => {
  return $i18n.locales?.value.map(locale => ({ name: locale.name, value: locale.code })) ?? []
})

onMounted(() => {
  isMounted.value = true
  
  // Ensure settings is a plain, writable object (avoid writing into readonly proxies)
  ensureSettingsObject(form.value)
  
  // Set default value for navigation_arrows in focused mode if not defined
  if (isFocused.value && form.value.settings.navigation_arrows === undefined) {
    form.value.settings.navigation_arrows = true
  }

  // Set default value for auto_next in focused mode if not defined
  if (isFocused.value && form.value.settings.auto_next === undefined) {
    form.value.settings.auto_next = true
  }
})

const onChangeConfettiOnSubmission = (val) => {
  if (isMounted.value && val) {
    confetti.play()
  }
}

const onChangeNoBranding = (val) => {
  if (!isPro.value && val) {
    openSubscriptionModal({ modal_title: "Upgrade today to remove SharaForms branding" })
    setTimeout(() => {
      form.value.no_branding = false
    }, 300)
  } 
}

const onApplyFont = (val) => {
  form.value.font_family = val
  showGoogleFontPicker.value = false
}
</script>

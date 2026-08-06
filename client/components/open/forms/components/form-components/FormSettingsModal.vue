<template>
  <SettingsModal
    v-model="isOpen"
    v-model:activeTab="activeTab"
    @close="closeModal"
  >
    <SettingsModalPage
      id="general"
      label="General"
      icon="i-lucide-info"
    >
      <FormInformation />
    </SettingsModalPage>

    <SettingsModalPage
      id="submission"
      label="Submission Settings"
      icon="i-lucide-send"
    >
      <FormSubmissionSettings />
    </SettingsModalPage>

    <SettingsModalPage
      id="security"
      label="Security & Access"
      icon="i-lucide-shield-check"
    >
      <FormSecurityAccess />
    </SettingsModalPage>

    <SettingsModalPage
      id="seo"
      label="SEO & Social Sharing"
      icon="i-lucide-link"
    >
      <FormCustomSeo />
    </SettingsModalPage>

    <SettingsModalPage
      id="analytics"
      label="Analytics"
      icon="i-lucide-chart-bar"
    >
      <FormAnalyticsSettings />
    </SettingsModalPage>

    <SettingsModalPage
      id="custom-code"
      label="Custom Code"
      icon="i-lucide-code"
    >
      <FormCustomCode />
    </SettingsModalPage>

    <SettingsModalPage
      id="variables"
      label="Variables"
      icon="i-lucide-variable"
    >
      <ComputedVariablesTab />
    </SettingsModalPage>

  </SettingsModal>
</template>

<script setup>
import SettingsModal from '~/components/pages/settings/SettingsModal.vue'
import SettingsModalPage from '~/components/pages/settings/SettingsModalPage.vue'
import FormInformation from '~/components/open/forms/components/form-components/FormInformation.vue'
import FormSubmissionSettings from '~/components/open/forms/components/form-components/FormSubmissionSettings.vue'
import FormSecurityAccess from '~/components/open/forms/components/form-components/FormSecurityAccess.vue'
import FormCustomSeo from '~/components/open/forms/components/form-components/FormCustomSeo.vue'
import FormCustomCode from '~/components/open/forms/components/form-components/FormCustomCode.vue'
import ComputedVariablesTab from '~/components/open/forms/components/computed-variables/ComputedVariablesTab.vue'
import FormAnalyticsSettings from '~/components/open/forms/components/form-components/FormAnalyticsSettings.vue'

const emit = defineEmits(['close', 'update:activeTab'])

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  activeTab: {
    type: String,
    default: 'general'
  }
})

// Modal state
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('close', value)
})

// Active tab state
const activeTab = computed({
  get: () => props.activeTab,
  set: (value) => emit('update:activeTab', value)
})

// Methods
const closeModal = () => {
  isOpen.value = false
}

// Define keyboard shortcuts
defineShortcuts({
  escape: {
    handler: () => {
      closeModal()
    }
  }
})
</script> 
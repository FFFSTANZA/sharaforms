<template>
  <VForm size="sm">
    <div class="px-1 space-y-4">
      <!-- Custom Code Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center justify-between mb-5">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-[var(--sf-nav-active-bg)] flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-code text-[12px] text-[var(--sf-coral-500)]"></i>
            </div>
            <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">
              Custom Code
              <PlanTag class="ml-1" upgrade-modal-title="Upgrade to Unlock Custom Code Capabilities" upgrade-modal-description="Implement custom scripts, styles, and advanced tracking in live forms." />
              <PlanTag v-if="isSelfHosted" required-tier="self_hosted" class="ml-1" upgrade-modal-title="Upgrade to Unlock Custom Code" />
            </h3>
          </div>
          <div class="flex gap-2">
            <UButton label="SDK Docs" icon="i-lucide-code" variant="outline" color="neutral" size="xs" :to="sharaformsConfig.links.custom_code_docs" target="_blank" />
            <UButton label="Help" icon="i-lucide-circle-question-mark" variant="outline" color="neutral" size="xs" @click="crisp.openHelpdeskArticle('how-do-i-add-custom-code-to-my-form-1amadj3')" />
          </div>
        </div>
        <p class="text-[var(--sf-text-caption)] text-sm mb-4">
          The code will be injected in the <b>head</b> section of your form page.
        </p>
        <CodeInput
          :allow-fullscreen="true"
          name="custom_code"
          :form="form"
          :disabled="!canUseCustomCode"
          :help="customCodeHelp"
          label="Custom Code"
          placeholder="<script>console.log('Hello World!')</script>"
        />
      </div>

      <!-- Custom CSS Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center justify-between mb-5">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-[var(--sf-indigo-light)] flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-palette text-[12px] text-[var(--sf-indigo)]"></i>
            </div>
            <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">
              Custom CSS
              <PlanTag class="ml-1" upgrade-modal-title="Upgrade to Unlock Custom CSS" upgrade-modal-description="Apply custom styles to your live forms." />
              <PlanTag v-if="isSelfHosted" required-tier="self_hosted" class="ml-1" upgrade-modal-title="Upgrade to Unlock Custom CSS" />
            </h3>
          </div>
          <UButton label="Help" icon="i-lucide-circle-question-mark" variant="outline" color="neutral" size="xs" @click="crisp.openHelpdeskArticle('can-i-style-my-form-with-some-custom-css-code-1v3dlr9')" />
        </div>
        <p class="text-[var(--sf-text-caption)] text-sm mb-4">
          The CSS will be injected in the <b>head</b> of your form page.
        </p>
        <CodeInput
          :allow-fullscreen="true"
          language-mode="css"
          name="custom_css"
          :form="form"
          help="CSS only. Example: body { background: #f8fafc }"
          label="Custom CSS"
          placeholder="body { background: #f8fafc }"
        />
      </div>
    </div>
  </VForm>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"
import sharaformsConfig from "~/sharaforms.config.js"

const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)
const crisp = useCrisp()

const canUseCustomCode = computed(() => workingFormStore.isCustomCodeAllowed)
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))

const customCodeHelp = computed(() => {
  const hasCustomDomain = !!form.value?.custom_domain
  const allowSelfHosted = !!useFeatureFlag('custom_code.enable_self_hosted', false)
  if (canUseCustomCode.value) {
    return 'Saves changes and visit the actual form page to test.'
  }
  // In self-hosted mode with flag disabled (and no custom domain), show safety notice with docs link
  if (isSelfHosted.value && !allowSelfHosted && !hasCustomDomain) {
    return `Custom code is disabled for safety on self-hosted. Enable via CUSTOM_CODE_ENABLE_SELF_HOSTED=true. See technical docs: ${sharaformsConfig.links.custom_code_docs}`
  }
  return 'Custom code requires to be using a custom domain.'
})

</script>

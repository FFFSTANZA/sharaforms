<template>
  <div
    ref="demoFormElement"
    class="live-demo-form relative flex h-full flex-col overflow-hidden bg-white"
    :style="formStyle"
  >
    <component
      :is="formComponent"
      v-if="isFormReady"
      :form-manager="formManager"
      class="grow overflow-y-auto"
      @submit="handleSubmit"
    >
      <template #after-submit>
        <div class="mx-auto flex w-full max-w-xl flex-col items-start px-2 text-left">
          <div
            class="brand-chip-strong mb-5 inline-flex h-12 w-12 items-center justify-center rounded-[14px]"
          >
            <UIcon name="i-lucide-check" class="h-6 w-6" />
          </div>
          <h3 class="brand-text-strong text-3xl font-semibold leading-10 tracking-[-1%]">
            {{ scenario.successTitle }}
          </h3>
          <p class="brand-text-muted mt-3 text-base font-medium leading-7 tracking-[-1.1%]">
            {{ scenario.successBody }}
          </p>
          <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <UButton
              :to="primaryCtaTo"
              size="lg"
              trailing-icon="i-lucide-arrow-up-right"
              :label="scenario.primaryCtaLabel"
              class="brand-button-primary w-fit rounded-[12px] px-4 py-2.5 text-base font-medium leading-7 tracking-[-1.1%]"
            />
            <UButton
              v-if="secondaryCtaTo && scenario.secondaryCtaLabel"
              :to="secondaryCtaTo"
              size="lg"
              variant="outline"
              :label="scenario.secondaryCtaLabel"
              class="brand-button-secondary w-fit rounded-[12px] px-4 py-2.5 text-base font-medium leading-7 tracking-[-1.1%]"
            />
            <UButton
              type="button"
              size="lg"
              color="neutral"
              variant="ghost"
              icon="i-lucide-refresh-cw"
              label="Replay demo"
              class="w-fit rounded-[12px] px-4 py-2.5 text-base font-medium leading-7 tracking-[-1.1%]"
              @click="restartDemo"
            />
          </div>
        </div>
      </template>
    </component>
  </div>
</template>

<script setup>
import { tailwindcssPaletteGenerator } from "~/lib/colors.js"
import { useFormManager } from "~/lib/forms/composables/useFormManager"
import { FormMode } from "~/lib/forms/FormModeStrategy.js"
import OpenForm from "~/components/open/forms/OpenForm.vue"
import OpenFormFocused from "~/components/open/forms/OpenFormFocused.vue"

const { setLocale } = useI18n()

const props = defineProps({
  scenario: {
    type: Object,
    required: true,
  },
  primaryCtaTo: {
    type: [String, Object],
    required: true,
  },
  secondaryCtaTo: {
    type: [String, Object],
    default: null,
  },
})

const scenario = computed(() => props.scenario)
const primaryCtaTo = computed(() => props.primaryCtaTo)
const secondaryCtaTo = computed(() => props.secondaryCtaTo)
const formComponent = computed(() =>
  props.scenario.form.presentation_style === "focused" ? OpenFormFocused : OpenForm,
)

provide("formTheme", computed(() => props.scenario.form.theme || "default"))
provide("formSize", computed(() => props.scenario.form.size || "lg"))
provide("formBorderRadius", computed(() => props.scenario.form.border_radius || "small"))
provide("formPresentationStyle", computed(() => props.scenario.form.presentation_style || "focused"))

const formManager = useFormManager(props.scenario.form, FormMode.DEMO, {
  mode: ref(FormMode.DEMO),
})
const demoFormElement = ref(null)

formManager.initialize({
  skipPendingSubmission: true,
  skipUrlParams: true,
  eagerStructure: true,
})
  .catch((error) => {
    console.error(error)
  })

const isFormReady = computed(() => !!formManager.structure.value)

const formStyle = computed(() => {
  const color = props.scenario.form.color || "#2563EB"
  const colorPalette = tailwindcssPaletteGenerator(color).primary
  const style = {
    "--font-family": props.scenario.form.font_family,
    "--form-color": color,
    "--color-form": color,
    "--form-focused-step-height": "100%",
    "--form-focused-mobile-media-height": "clamp(120px, 24svh, 180px)",
    "contain": "layout paint",
  }

  Object.entries(colorPalette).forEach(([shade, colorValue]) => {
    style[`--color-form-${shade}`] = colorValue
  })

  return style
})

onMounted(() => {
  setLocale(scenario.value.form.language || 'en')
})

onBeforeUnmount(() => {
  setLocale('en')
})

function handleSubmit() {
  formManager.submit()
    .catch((error) => {
      console.error(error)
    })
}

function restartDemo() {
  formManager.restart({
    skipPendingSubmission: true,
    skipUrlParams: true,
  })
    .catch((error) => {
      console.error(error)
    })
}
</script>

<style scoped>
.live-demo-form :deep(.nf-text .text-block > div:last-child) {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.live-demo-form :deep(.nf-text .text-block h2),
.live-demo-form :deep(.nf-text .text-block p) {
  margin: 0;
}

.live-demo-form :deep(.nf-text .text-block h2) {
  line-height: 1.22;
}

.live-demo-form :deep(.nf-text .text-block p:first-child strong) {
  background: linear-gradient(135deg, #ff8a5b, #ef5da8, #8b5cf6, #3b82f6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.live-demo-form :deep(.nf-text .text-block p) {
  line-height: 1.6;
}

.live-demo-form :deep([role="listbox"] [role="option"] > span:first-child) {
  pointer-events: none;
  user-select: none;
}

.live-demo-form :deep(.btn) {
  background-image: linear-gradient(135deg, #ff8a5b 0%, #ef5da8 28%, #8b5cf6 62%, #3b82f6 100%) !important;
  color: #fff !important;
  border: 0 !important;
  box-shadow: 0 16px 40px -18px rgba(147, 51, 234, 0.55) !important;
  transition:
    transform 180ms ease,
    box-shadow 180ms ease,
    filter 180ms ease;
}

.live-demo-form :deep(.btn:hover) {
  filter: saturate(1.05) brightness(1.03);
  box-shadow: 0 22px 54px -20px rgba(147, 51, 234, 0.66);
  transform: translateY(-1px);
}

.live-demo-form :deep(.btn:disabled) {
  opacity: 0.5;
  filter: none;
  transform: none;
  pointer-events: none;
}



</style>

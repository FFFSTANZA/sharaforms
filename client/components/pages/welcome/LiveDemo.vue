<template>
  <div class="relative z-10 mx-auto flex w-full max-w-266 items-center justify-center">
    <div
      class="relative w-full overflow-hidden rounded-[20px] p-1 sm:rounded-[28px] sm:p-2"
      style="background: linear-gradient(135deg, #ff8a5b 0%, #ef5da8 28%, #8b5cf6 62%, #3b82f6 100%); box-shadow: 0 28px 80px -44px rgba(76, 29, 149, 0.28);"
    >
      <div class="overflow-hidden rounded-[16px] bg-white sm:rounded-[20px]">
        <div
          class="relative flex select-none items-center justify-center bg-gradient-to-r from-[#8b5cf6] via-[#a855f7] to-[#db2777] px-3 py-1.5 sm:px-5 sm:py-2"
        >
          <div class="pointer-events-none hidden items-center gap-2.5 text-white/60 sm:absolute sm:left-5 sm:flex">
            <UIcon
              name="i-lucide-columns-2"
              class="h-4 w-4"
            />
            <UIcon
              name="i-lucide-arrow-left"
              class="h-4 w-4"
            />
            <UIcon
              name="i-lucide-arrow-right"
              class="h-4 w-4"
            />
            <UIcon
              name="i-lucide-refresh-cw"
              class="h-4 w-4"
            />
          </div>

          <div
            class="flex min-w-0 items-center gap-2 rounded-full bg-white/90 px-3 py-0.5 text-xs font-medium leading-4 text-neutral-800 shadow-sm backdrop-blur-xs"
          >
            <UIcon
              name="i-lucide-lock"
              class="h-4 w-4 shrink-0 text-[#8b5cf6]"
            />
            <span class="truncate">sharaforms.com</span>
          </div>

          <div class="hidden items-center gap-2 text-white/60 sm:absolute sm:right-5 sm:flex">
            <UIcon
              name="i-lucide-sliders-horizontal"
              class="pointer-events-none h-4 w-4"
            />
            <UIcon
              name="i-lucide-star"
              class="pointer-events-none h-4 w-4"
            />
          </div>
        </div>

        <div class="relative min-h-[460px] bg-white sm:min-h-[620px]">
          <LiveDemoForm
            :key="scenario.key"
            :scenario="scenario"
            :primary-cta-to="primaryCtaTo"
            :secondary-cta-to="secondaryCtaTo"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import LiveDemoForm from "~/components/pages/welcome/LiveDemoForm.vue"
import { useIsAuthenticated } from "~/composables/useAuthFlow"
import { getLiveDemoScenario } from "~/data/live-demo-scenarios.js"

const props = defineProps({
  variant: {
    type: String,
    default: "home",
  },
  competitorName: {
    type: String,
    default: null,
  },
  importSource: {
    type: String,
    default: null,
  },
})

const { isAuthenticated: authenticated } = useIsAuthenticated()

const scenario = computed(() =>
  getLiveDemoScenario({
    variant: props.variant,
    competitorName: props.competitorName,
    importSource: props.importSource,
  }),
)

const mediaPreloads = computed(() => {
  const urls = new Set()
  for (const field of scenario.value.form.properties) {
    if (field?.image?.url) {
      urls.add(field.image.url)
    }
  }
  return [...urls].map((href) => ({
    rel: "preload",
    href,
    as: "image",
    type: href.endsWith(".svg") ? "image/svg+xml" : "image/webp",
  }))
})

useHead(() => ({ link: mediaPreloads.value }))

const primaryCtaTo = computed(() => ({
  name: authenticated.value ? "forms-create" : "forms-create-guest",
}))

const secondaryCtaTo = computed(() => {
  if (!props.importSource || !scenario.value.secondaryCtaLabel) {
    return null
  }

  return {
    name: authenticated.value || props.importSource === "google_forms"
      ? "forms-create"
      : "forms-create-guest",
    query: { import: props.importSource },
  }
})
</script>

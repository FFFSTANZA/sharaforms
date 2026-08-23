<template>
  <section id="features" class="px-8 lg:px-12">
    <div class="space-y-8 sm:space-y-12 mx-auto w-full max-w-266 lg:hidden">
      <div
        v-for="panel in panels"
        :key="panel.eyebrow"
        class="brand-surface rounded-4xl py-8 sm:py-10 lg:py-14 xl:py-24 px-8 md:px-10 lg:px-14 xl:px-35"
      >
        <div class="grid gap-8 lg:gap-16 lg:grid-cols-2 items-start">
          <div>
            <div
              :class="[
                'font-semibold text-sm tracking-[-0.6%]',
                panel.eyebrowClass,
              ]"
            >
              {{ panel.eyebrow }}
            </div>

            <h2
              class="brand-text-strong my-4 text-3xl sm:text-[40px] font-semibold sm:leading-12 tracking-[-1%]"
            >
              {{ panel.title }}
            </h2>

            <p
              class="brand-text-muted text-base mt-4 leading-7 font-normal tracking-[-1.1%]"
            >
              {{ panel.description }}
            </p>

            <div class="mt-8 sm:mt-12 space-y-4">
              <div
                v-for="item in panel.items"
                :key="item.title"
                class="flex items-center gap-4"
              >
                <div
                  :class="[
                    'h-6 w-6 rounded-[6px] flex items-center justify-center',
                    item.iconWrapClass,
                  ]"
                >
                  <UIcon
                    :name="item.icon"
                    :class="['h-4 w-4', item.iconClass]"
                  />
                </div>
                <div
                  class="brand-text-muted text-base leading-7 font-medium tracking-[-1.1%]"
                >
                  {{ item.title }}
                </div>
              </div>
            </div>

            <div v-if="panel.link" class="mt-8 sm:mt-10">
              <NuxtLink
                :to="panel.link.to"
                class="inline-flex items-center gap-2 font-semibold"
                :class="panel.link.class"
              >
                {{ panel.link.label }}
                <UIcon
                  name="i-heroicons:arrow-up-right"
                  class="h-4 w-4"
                />
              </NuxtLink>
            </div>
          </div>

          <div class="flex justify-center lg:justify-end">
            <img
              :src="panel.imageSrc"
              :alt="panel.eyebrow"
              class="mx-auto h-auto w-full object-contain"
              :class="panel.mobileImageClass"
              loading="lazy"
            />
          </div>
        </div>
      </div>
    </div>

    <div
      class="hidden lg:grid mx-auto w-full max-w-266 grid-cols-[minmax(0,1fr)_32rem] gap-14 xl:gap-20"
    >
      <div class="space-y-18 xl:space-y-24 py-10 xl:py-16">
        <article
          v-for="(panel, index) in panels"
          :key="panel.eyebrow"
          :ref="(element) => setDesktopPanelRef(element, index)"
          class="min-h-[66vh] xl:min-h-[70vh] flex items-center"
        >
          <div
            class="max-w-2xl py-12 transition-opacity duration-300"
            :class="activeDesktopPanel === index ? 'opacity-100' : 'opacity-45'"
          >
            <div
              :class="[
                'font-semibold text-sm tracking-[-0.6%]',
                panel.eyebrowClass,
              ]"
            >
              {{ panel.eyebrow }}
            </div>

            <!-- Styled as a heading but not one semantically: this desktop-only
                 branch duplicates the mobile branch's panels, and duplicate
                 <h2> text in the DOM dilutes heading signals (mobile-first
                 indexing parses the lg:hidden branch above). -->
            <div
              class="brand-text-strong my-4 text-3xl xl:text-[44px] font-semibold leading-tight tracking-[-1%]"
            >
              {{ panel.title }}
            </div>

            <p
              class="brand-text-muted text-base xl:text-lg mt-4 leading-7 xl:leading-8 font-normal tracking-[-1.1%]"
            >
              {{ panel.description }}
            </p>

            <div class="mt-8 xl:mt-10 space-y-4">
              <div
                v-for="item in panel.items"
                :key="item.title"
                class="flex items-center gap-4"
              >
                <div
                  :class="[
                    'h-6 w-6 rounded-[6px] flex items-center justify-center',
                    item.iconWrapClass,
                  ]"
                >
                  <UIcon
                    :name="item.icon"
                    :class="['h-4 w-4', item.iconClass]"
                  />
                </div>
                <div
                  class="brand-text-muted text-base leading-7 font-medium tracking-[-1.1%]"
                >
                  {{ item.title }}
                </div>
              </div>
            </div>

            <div v-if="panel.link" class="mt-8 xl:mt-10">
              <NuxtLink
                :to="panel.link.to"
                class="inline-flex items-center gap-2 font-semibold"
                :class="panel.link.class"
              >
                {{ panel.link.label }}
                <UIcon
                  name="i-heroicons:arrow-up-right"
                  class="h-4 w-4"
                />
              </NuxtLink>
            </div>
          </div>
        </article>
      </div>

      <div class="relative py-10 xl:py-16">
        <div class="sticky top-18 xl:top-24 flex justify-center">
          <Transition name="feature-panel-image" mode="out-in">
            <img
              :key="activeDesktopImage.eyebrow"
              :src="activeDesktopImage.imageSrc"
              :alt="activeDesktopImage.eyebrow"
              class="h-auto w-full object-contain"
              :class="activeDesktopImage.desktopImageClass"
              loading="lazy"
            />
          </Transition>
        </div>
      </div>
    </div>

    <div class="py-14 md:py-28">
      <div class="max-w-3xl mx-auto text-center">
          <h2
            class="brand-text-strong text-3xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%]"
          >
            Forms built for real work.
          </h2>
          <p
            class="brand-text-muted mx-auto max-w-lg mt-4 text-base leading-7 font-medium tracking-[-1.1%]"
          >
            Beautiful on the surface. Powerful underneath. Built for teams that
            rely on forms every day.
          </p>
      </div>

      <div
        class="mt-12 sm:mt-16 flex flex-wrap items-center justify-center gap-4"
      >
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          class="inline-flex items-center gap-2 rounded-[14px] border px-3.5 py-2 text-base leading-7 font-medium tracking-[-1.1%] transition-all duration-300"
          :class="
            activeTab === tab.key
              ? `${activeTabTheme.tabActiveClass} -translate-y-0.5 shadow-lg shadow-neutral-200/70`
              : 'bg-white border-neutral-200 text-neutral-600 hover:bg-neutral-50 hover:border-neutral-300'
          "
          @click="activeTab = tab.key"
        >
          <UIcon :name="tab.icon" class="h-5 w-5" />
          {{ tab.label }}
        </button>
      </div>

      <div class="brand-surface mt-8 mx-auto max-w-266 overflow-hidden rounded-[28px] p-1.5">
        <div
          class="relative grid items-center gap-8 rounded-[24px] p-5 md:p-6 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)] lg:gap-10"
          :class="activeTabTheme.panelClass"
        >
          <div
            class="pointer-events-none absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-white/80 to-transparent"
          ></div>
          <div class="relative">
            <div
              class="inline-flex items-center gap-2 rounded-full border border-white/80 bg-white/80 px-3 py-1 text-sm font-medium text-neutral-600 shadow-sm backdrop-blur-sm"
            >
              <span
                class="h-2.5 w-2.5 rounded-full"
                :class="activeTabTheme.dotClass"
              ></span>
              {{ activeContent.badge }}
            </div>

            <Transition name="feature-copy" mode="out-in">
              <div :key="activeContent.title" class="pt-5">
                <div
                  class="brand-text-strong text-2xl leading-8 font-semibold tracking-[-0.5%] sm:text-[32px] sm:leading-10"
                >
                  {{ activeContent.title }}
                </div>
                <p
                  class="mt-4 max-w-xl text-base leading-7 font-normal tracking-[-1.1%] text-neutral-700"
                >
                  {{ activeContent.description }}
                </p>

                <div class="mt-6 space-y-4">
                  <div
                    v-for="point in activeContent.points"
                    :key="point"
                    class="flex items-center gap-4 text-base leading-7 font-medium tracking-[-1.1%] text-neutral-700"
                  >
                    <div
                      class="flex h-6 w-6 items-center justify-center rounded-[6px] bg-white/85 shadow-sm ring-1 ring-white/90"
                    >
                      <UIcon
                        name="i-heroicons:check"
                        class="h-5 w-5"
                        :class="activeTabTheme.iconClass"
                      />
                    </div>
                    <span>{{ point }}</span>
                  </div>
                </div>
              </div>
            </Transition>
          </div>

          <div class="relative flex justify-center lg:justify-end">
            <div
              class="pointer-events-none absolute inset-x-[12%] top-8 h-24 rounded-full blur-3xl"
              :class="activeTabTheme.glowClass"
            ></div>
            <div
              class="relative w-full max-w-100 overflow-hidden rounded-[24px] border border-white/80 bg-white/75 p-2 shadow-[0_20px_50px_-28px_rgba(15,23,42,0.55)] backdrop-blur-sm lg:max-w-121"
            >
              <div
                class="relative aspect-[1.67/1] overflow-hidden rounded-[18px] bg-white"
              >
                <Transition name="feature-tab-image" mode="out-in">
                  <div
                    :key="activeContent.imageSrc"
                    class="absolute inset-0 flex items-center justify-center"
                  >
                    <img
                      :src="activeContent.imageSrc"
                      :alt="activeContent.title"
                      class="h-full w-full object-contain"
                      :class="activeContent.imageClass"
                      loading="lazy"
                    />
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-12 sm:mt-16 text-center">
        <div
          class="flex flex-col sm:flex-row items-center justify-center gap-6"
        >
          <UButton
            size="lg"
            :to="{
              name: authenticated ? 'forms-create' : 'forms-create-guest',
            }"
            trailing-icon="i-lucide-arrow-up-right"
            label="Get started. It's FREE!"
            class="brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
          />

          <UButton
            :to="{ name: 'pricing' }"
            label="See the Full Feature List"
            variant="outline"
            color="neutral"
            size="lg"
            class="px-4 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium hover:border-neutral-300 hover:text-neutral-900 transition-all duration-300"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import {
  DESKTOP_FEATURE_PANEL_OBSERVER_THRESHOLDS,
  getActiveDesktopFeaturePanelIndex,
} from '~/lib/welcome/desktopFeaturePanels'

const { isAuthenticated: authenticated } = useIsAuthenticated()

const desktopPanelRefs = ref([])
const activeDesktopPanel = ref(0)
let desktopPanelObserver = null
let desktopPanelChangeFrame = null
let desktopPanelMediaQuery = null

const welcomeAssetBase = "/img/pages/welcome/New%20Welcome/cleaned"

const panels = [
  {
    eyebrow: "Modern Form Builder",
    eyebrowClass: "text-pink-600",
    title: "Beautiful enough for your brand. Powerful enough for your workflow.",
    description:
      "Start with branded, polished forms. Add logic, calculations, and routing when the workflow gets more serious.",
    items: [
      {
        title: "Modern multi-step & single-page forms",
        icon: "i-heroicons:rectangle-stack",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Spotlight, focused, or classic presentation modes",
        icon: "i-heroicons:rectangle-group",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Conditional logic",
        icon: "i-heroicons:arrows-right-left",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Custom themes, brand colors & fonts",
        icon: "i-heroicons:paint-brush",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Remove SharaForms branding on paid plans",
        icon: "i-heroicons:no-symbol",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "AI help when you want it",
        icon: "i-heroicons:chat-bubble-oval-left-ellipsis",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
    ],
    imageSrc: `${welcomeAssetBase}/feature-1.png`,
    mobileImageClass: "max-w-[24rem] sm:max-w-[28rem]",
    desktopImageClass: "max-w-[26rem] xl:max-w-[30rem]",
    link: null,
  },
  {
    eyebrow: "Unlimited Submissions",
    eyebrowClass: "text-emerald-600",
    title: "Unlimited responses, without caps getting in the way.",
    description:
      "No per-response charges. No hidden quotas. No surprise overages. SharaForms stays practical as your volume grows.",
    items: [
      {
        title: "Unlimited forms and submissions",
        icon: "i-heroicons:arrow-trending-up",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Generous free tier",
        icon: "i-heroicons:gift",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Fair, transparent pricing",
        icon: "i-heroicons:currency-dollar",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
    ],
    imageSrc: `${welcomeAssetBase}/feature-2.png`,
    mobileImageClass: "max-w-[24rem] sm:max-w-[28rem]",
    desktopImageClass: "max-w-[26rem] xl:max-w-[30rem]",
    link: null,
  },
  {
    eyebrow: "Integrations & Automation",
    eyebrowClass: "brand-gradient-text-warm",
    title: "Send each submission where it needs to go.",
    description:
      "Route submissions to your tools, automate follow-ups, and connect custom systems with webhooks and the API.",
    items: [
      {
        title: "Slack, Discord, Telegram",
        icon: "i-heroicons:chat-bubble-left-right",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Google Sheets & Zapier",
        icon: "i-heroicons:table-cells",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Stripe payments",
        icon: "i-heroicons:credit-card",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Webhooks + REST API",
        icon: "i-heroicons:link",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
      {
        title: "Automated notifications & routing",
        icon: "i-heroicons:arrow-path",
        iconWrapClass: "bg-neutral-100",
        iconClass: "text-neutral-600",
      },
    ],
    imageSrc: `${welcomeAssetBase}/feature-3.png`,
    mobileImageClass: "max-w-[24rem] sm:max-w-[28rem]",
    desktopImageClass: "max-w-[26rem] xl:max-w-[30rem]",
    link: {
      to: { name: "pricing" },
      label: "Explore All Features",
      class: "brand-gradient-text-warm hover:no-underline",
    },
  },
]

const tabs = [
  { key: "smart", label: "Logic & Calculations", icon: "i-heroicons:light-bulb" },
  { key: "inputs", label: "Advanced Inputs", icon: "i-heroicons:chat-bubble-bottom-center-text" },
  {
    key: "security",
    label: "Security & Reliability",
    icon: "i-heroicons:shield-check",
  },
  {
    key: "control",
    label: "Branding & Control",
    icon: "i-heroicons:adjustments-horizontal",
  },
]

const activeTab = ref("smart")

const tabContent = {
  smart: {
    badge: "Build smarter flows",
    title: "Logic & Calculations",
    description:
      "Go beyond basic field logic with built-in calculations, conditional flows, hidden data, and post-submit routing.",
    points: [
      "Conditional logic",
      "Calculations & computed fields",
      "Answer piping & hidden fields",
      "Redirect on submit",
    ],
    imageSrc: `${welcomeAssetBase}/feature-4.png`,
    imageClass: "scale-[0.99]",
  },
  inputs: {
    badge: "Capture better answers",
    title: "Advanced Inputs",
    description:
      "Capture better answers with inputs built for payments, signatures, uploads, and structured data collection.",
    points: [
      "File uploads",
      "Address & phone inputs",
      "Payments & signatures",
      "Validation rules",
    ],
    imageSrc: "/img/pages/welcome/feature-5.png",
  },
  security: {
    badge: "Keep submissions clean",
    title: "Security & Reliability",
    description:
      "Keep submissions clean and reliable with built-in protections, validation, and the controls serious teams expect.",
    points: [
      "Spam protection",
      "reCAPTCHA support",
      "Email notifications",
      "Data exports",
    ],
    imageSrc: "/img/pages/welcome/feature-6.png",
  },
  control: {
    badge: "Shape every interaction",
    title: "Branding & Control",
    description:
      "Shape the full experience with custom branding, multi-step flows, and flexible follow-up paths after submit.",
    points: [
      "Custom themes & branding",
      "Multi-page forms",
      "Thank-you pages",
      "Webhooks & integrations",
    ],
    imageSrc: "/img/pages/welcome/feature-7.png",
  },
}

const activeContent = computed(
  () => tabContent[activeTab.value] || tabContent.smart,
)

const tabThemes = {
  smart: {
    tabActiveClass: "border-purple-200 bg-purple-50 text-purple-700",
    panelClass: "bg-linear-to-br from-purple-50 via-white to-pink-50",
    dotClass: "bg-purple-500",
    iconClass: "text-purple-500",
    glowClass: "bg-purple-200/80",
  },
  inputs: {
    tabActiveClass: "border-amber-200 bg-amber-50 text-amber-700",
    panelClass: "bg-linear-to-br from-amber-50 via-white to-orange-50",
    dotClass: "bg-amber-500",
    iconClass: "text-amber-500",
    glowClass: "bg-amber-200/80",
  },
  security: {
    tabActiveClass: "border-emerald-200 bg-emerald-50 text-emerald-700",
    panelClass: "bg-linear-to-br from-emerald-50 via-white to-teal-50",
    dotClass: "bg-emerald-500",
    iconClass: "text-emerald-500",
    glowClass: "bg-emerald-200/80",
  },
  control: {
    tabActiveClass: "border-violet-200 bg-violet-50 text-violet-700",
    panelClass: "bg-linear-to-br from-violet-50 via-white to-fuchsia-50",
    dotClass: "bg-violet-500",
    iconClass: "text-violet-500",
    glowClass: "bg-violet-200/80",
  },
}

const activeTabTheme = computed(
  () => tabThemes[activeTab.value] || tabThemes.smart,
)

const activeDesktopImage = computed(
  () => panels[activeDesktopPanel.value] || panels[0],
)

function setDesktopPanelRef(element, index) {
  if (!element) {
    delete desktopPanelRefs.value[index]
    return
  }

  desktopPanelRefs.value[index] = element
}

function cancelDesktopPanelFrame() {
  if (desktopPanelChangeFrame === null) return

  window.cancelAnimationFrame(desktopPanelChangeFrame)
  desktopPanelChangeFrame = null
}

function getDesktopViewportHeight() {
  return window.innerHeight || document.documentElement.clientHeight || 0
}

function updateActiveDesktopPanel(force = false) {
  const panelRects = desktopPanelRefs.value.map((element) =>
    element?.getBoundingClientRect?.() ?? null,
  )

  const nextPanelIndex = getActiveDesktopFeaturePanelIndex(
    panelRects,
    activeDesktopPanel.value,
    {
      viewportHeight: getDesktopViewportHeight(),
    },
  )

  if (force || nextPanelIndex !== activeDesktopPanel.value) {
    activeDesktopPanel.value = nextPanelIndex
  }
}

function scheduleDesktopPanelUpdate() {
  if (!import.meta.client || desktopPanelChangeFrame !== null) return

  desktopPanelChangeFrame = window.requestAnimationFrame(() => {
    desktopPanelChangeFrame = null
    updateActiveDesktopPanel()
  })
}

function destroyDesktopPanelObserver() {
  desktopPanelObserver?.disconnect()
  desktopPanelObserver = null
  cancelDesktopPanelFrame()
  window.removeEventListener('scroll', scheduleDesktopPanelUpdate)
  window.removeEventListener('resize', scheduleDesktopPanelUpdate)
}

function initializeDesktopPanelObserver() {
  if (!import.meta.client) {
    return
  }

  if (!desktopPanelMediaQuery?.matches) {
    destroyDesktopPanelObserver()
    return
  }

  destroyDesktopPanelObserver()

  desktopPanelObserver = new IntersectionObserver(
    () => {
      scheduleDesktopPanelUpdate()
    },
    {
      threshold: DESKTOP_FEATURE_PANEL_OBSERVER_THRESHOLDS,
      rootMargin: '-18% 0px -18% 0px',
    },
  )

  desktopPanelRefs.value.forEach((element, index) => {
    if (!element) return

    element.dataset.panelIndex = index
    desktopPanelObserver.observe(element)
  })

  window.addEventListener('scroll', scheduleDesktopPanelUpdate, { passive: true })
  window.addEventListener('resize', scheduleDesktopPanelUpdate)
  updateActiveDesktopPanel(true)
}

onMounted(() => {
  nextTick().then(() => {
    desktopPanelMediaQuery = window.matchMedia('(min-width: 1024px)')
    desktopPanelMediaQuery.addEventListener('change', initializeDesktopPanelObserver)
    initializeDesktopPanelObserver()
  })
})

onBeforeUnmount(() => {
  destroyDesktopPanelObserver()
  desktopPanelMediaQuery?.removeEventListener('change', initializeDesktopPanelObserver)
  desktopPanelMediaQuery = null
})
</script>

<style scoped>
.feature-panel-image-enter-active,
.feature-panel-image-leave-active {
  transition: opacity 320ms ease, transform 320ms cubic-bezier(0.22, 1, 0.36, 1), filter 320ms ease;
}

.feature-panel-image-enter-from,
.feature-panel-image-leave-to {
  opacity: 0;
  filter: blur(6px);
  transform: translateY(14px) scale(0.985);
}

.feature-tab-image-enter-active,
.feature-tab-image-leave-active,
.feature-copy-enter-active,
.feature-copy-leave-active {
  transition: opacity 260ms ease, transform 260ms ease, filter 260ms ease;
}

.feature-tab-image-enter-from,
.feature-tab-image-leave-to {
  opacity: 0;
  filter: blur(8px);
  transform: translateY(16px) scale(0.97);
}

.feature-copy-enter-from,
.feature-copy-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>

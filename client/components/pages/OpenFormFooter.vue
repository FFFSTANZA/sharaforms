<template>
  <section v-if="showCta" class="relative px-8 lg:px-12">
    <div class="max-w-336 mx-auto">
      <div
        class="brand-surface-strong relative rounded-4xl overflow-hidden pt-8 sm:pt-10 lg:pt-14 xl:pt-24 pl-8 md:pl-10 lg:pl-14 xl:pl-35"
      >
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
          <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(255,138,91,0.55),rgba(239,93,168,0.38)_30%,rgba(139,92,246,0.34)_65%,rgba(59,130,246,0.45)_100%)]"></div>
          <div class="absolute -top-28 -left-28 h-[26rem] w-[26rem] rounded-full bg-[#ff8a5b]/35 blur-3xl"></div>
          <div class="absolute top-1/4 -right-32 h-[24rem] w-[24rem] rounded-full bg-[#ef5da8]/30 blur-3xl"></div>
          <div class="absolute bottom-0 left-1/4 h-[22rem] w-[22rem] rounded-full bg-[#8b5cf6]/35 blur-3xl"></div>
          <div class="absolute -bottom-28 right-1/4 h-[26rem] w-[26rem] rounded-full bg-[#3b82f6]/30 blur-3xl"></div>
        </div>
        <img
          src="/img/pages/welcome/powerForm-bg.png"
          class="absolute min-h-full inset-0 opacity-25"
        />
        <div class="grid lg:grid-cols-2 relative z-10">
          <div
            class="text-white relative z-20 pr-8 sm:pr-10 md:pr-0 pb-8 sm:pb-10 lg:pb-14 xl:pb-24"
          >
            <h2
              class="text-white text-3xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%] max-w-xl"
            >
              Powerful forms for everyone.
            </h2>
            <p
              class="mt-6 text-lg leading-7 tracking-[-1.5%] font-normal text-white/80 max-w-xl"
            >
              Start free with unlimited forms and submissions. Upgrade when you need more
              control and customization.
            </p>

            <div class="mt-8">
              <UButton
                :to="{ name: 'pricing' }"
                label="View Pricing"
                trailing-icon="i-lucide-arrow-up-right"
                class="brand-button-secondary rounded-xl pl-4 py-2.5 pr-3.5 text-base leading-7 tracking-[-1.1%] font-medium"
              />
            </div>

            <div
              class="my-8 lg:my-12 flex flex-wrap items-center gap-6 text-base font-medium leading-7 tracking-[-1.1%] text-white/90"
            >
              <div class="flex items-center gap-2">
                <UIcon
                  name="i-lucide-check"
                  class="h-6 w-4 text-white/90"
                />
                <span>Free forever</span>
              </div>
              <div class="flex items-center gap-2">
                <UIcon
                  name="i-lucide-check"
                  class="h-6 w-4 text-white/90"
                />
                <span>No per-response fees</span>
              </div>
              <div class="flex items-center gap-2">
                <UIcon
                  name="i-lucide-check"
                  class="h-6 w-4 text-white/90"
                />
                <span>Fair pricing for growing teams</span>
              </div>
            </div>
            <div
              class="text-sm leading-5 tracking-[-0.6%] font-medium text-white/90"
            >
              Secure, reliable, and trusted by teams worldwide.
            </div>
            <div
              class="w-full xl:min-w-155 mt-6 md:mt-4 flex items-center justify-center sm:justify-start lg:justify-center gap-x-12 gap-y-5 sm:gap-12 flex-wrap"
            >
              <div class="w-fit filter-[sepia(1)_brightness(2)_saturate(0)]">
                <img
                  src="/img/pages/welcome/vanguard-1.png"
                  alt="Trusted Teams"
                  class="w-full"
                />
              </div>
              <div class="w-fit filter-[sepia(1)_brightness(2)_saturate(0)]">
                <img
                  src="/img/pages/welcome/logo-berkeley-2.png"
                  alt="Trusted Teams"
                  class="w-full"
                />
              </div>
              <div class="w-fit filter-[sepia(1)_brightness(2)_saturate(0)]">
                <img
                  src="/img/pages/welcome/Frame-47.png"
                  alt="Trusted Teams"
                  class="w-full"
                />
              </div>
              <div class="w-fit filter-[sepia(1)_brightness(2)_saturate(0)]">
                <img
                  src="/img/pages/welcome/Zomato_Logo-2.png"
                  alt="Trusted Teams"
                  class="w-full"
                />
              </div>
              <div class="w-fit filter-[sepia(1)_brightness(2)_saturate(0)]">
                <img
                  src="/img/pages/welcome/UN_emblem_blue-2.png"
                  alt="Trusted Teams"
                  class="w-full"
                />
              </div>
            </div>
          </div>

          <div class="flex items-end justify-end pl-8 sm:pl-10">
            <div
              class="relative w-full overflow-hidden flex justify-end items-end"
            >
              <img
                src="/img/pages/welcome/product-cover-half.png"
                alt="Product screenshot"
                class="w-full max-w-128.25 h-auto"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="brand-divider max-w-266 mx-auto pt-14 sm:pt-28 pb-12 sm:pb-16 border-b">
        <div class="grid md:grid-cols-2 gap-4">
          <div class="md:max-w-lg">
            <h3
              class="brand-text-strong text-3xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%]"
            >
              Build your first form today.
            </h3>
            <p
              class="brand-text-muted mt-4 text-lg leading-7 tracking-[-1.5%] font-normal"
            >
              Start free with unlimited forms and submissions. Upgrade when you need more
              control and customization.
            </p>
          </div>

          <div
            class="flex flex-col sm:flex-row gap-6 items-center justify-center md:justify-end md:max-h-12"
          >
            <UButton
              :to="{ name: 'pricing' }"
              label="View Pricing"
              variant="outline"
              color="neutral"
              class="brand-button-secondary w-fit sm:w-auto px-4 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium transition-all duration-300"
            />
            <UButton
              :to="{
                name: authenticated ? 'forms-create' : 'forms-create-guest',
              }"
              label="Get started. It's FREE!"
              trailing-icon="i-lucide-arrow-up-right"
              class="brand-button-primary w-fit sm:w-auto pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium transition-all duration-300"
            />
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="brand-section-wash">
    <div class="px-5 sm:px-8 lg:px-12 pt-10 sm:pt-14 lg:pt-16 pb-12 sm:pb-16 lg:pb-24">
      <div
        class="max-w-266 mx-auto grid gap-10 lg:grid-cols-[minmax(220px,0.8fr)_minmax(0,2.2fr)] lg:gap-14 xl:gap-20"
      >
        <div
          class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between lg:flex-col lg:justify-start lg:gap-0"
        >
          <div>
            <NuxtLink
              :to="{ name: user ? 'home' : 'index' }"
              class="inline-flex items-center gap-0 hover:no-underline"
            >
              <img src="/img/sharaforms-logo.png" alt="SharaForms logo" class="h-14 w-14 shrink-0" />
              <BrandWordmark class="text-[1.72rem]" />
            </NuxtLink>

            <div class="mt-4 flex items-center gap-3 lg:mt-6">
              <a
                v-for="social in socialLinks"
                :key="social.label"
                :href="social.href"
                target="_blank"
                rel="noopener noreferrer"
                class="brand-chip h-11 w-11 sm:h-10 sm:w-10 rounded-full flex items-center justify-center transition-colors"
                :aria-label="social.label"
              >
                <UIcon :name="social.icon" class="brand-text-muted h-4 w-4" />
              </a>
            </div>
          </div>

          <div
            class="brand-text-soft text-sm leading-5 tracking-[0.6%] font-medium sm:text-right lg:mt-8 lg:text-left"
          >
            {{ currYear }} SharaForms — All rights reserved
            <span v-if="version" class="block mt-1">
              Version {{ version }}
            </span>
          </div>
        </div>

        <nav aria-label="Footer navigation" class="lg:pt-1">
          <div class="grid grid-cols-2 gap-x-6 gap-y-9 sm:grid-cols-4 md:grid-cols-5 md:gap-x-8 lg:gap-x-10 xl:gap-x-12">
            <div
              v-for="group in linkGroups"
              :key="group.title"
              :class="group.wide ? 'min-[480px]:col-span-2 md:col-span-2' : ''"
            >
              <div
                class="brand-text-strong text-sm leading-5 tracking-[0.6%] font-medium"
              >
                {{ group.title }}
              </div>

              <div
                :class="[
                  'mt-3 gap-2.5 sm:gap-3',
                  group.wide
                    ? 'grid grid-cols-1 min-[480px]:grid-cols-2 min-[480px]:gap-x-6 md:gap-x-8'
                    : 'flex flex-col items-start',
                ]"
              >
                <template v-for="link in group.links" :key="link.label">
                  <NuxtLink
                    v-if="link.to"
                    :to="link.to"
                    class="brand-text-muted flex min-h-7 w-fit items-center whitespace-nowrap text-sm leading-5 tracking-[-0.6%] font-medium transition-colors hover:no-underline hover:text-neutral-950"
                  >
                    {{ link.label }}
                  </NuxtLink>

                  <a
                    v-else
                    :href="link.href"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="brand-text-muted flex min-h-7 w-fit items-center whitespace-nowrap text-sm leading-5 tracking-[-0.6%] font-medium transition-colors hover:no-underline hover:text-neutral-950"
                  >
                    {{ link.label }}
                  </a>
                </template>
              </div>
            </div>
          </div>
        </nav>
      </div>
    </div>
  </footer>
</template>

<script setup>
import sharaformsConfig from "~/sharaforms.config.js"

defineProps({
  showCta: {
    type: Boolean,
    default: true,
  },
})

const { isAuthenticated: authenticated } = useIsAuthenticated()
const { data: user } = useAuth().user()
const currYear = ref(new Date().getFullYear())

// Use the reactive version for proper template reactivity
const version = computed(() => useFeatureFlag("version"))

const socialLinks = computed(() => [
  { label: "X", href: sharaformsConfig.links.twitter, icon: "i-simple-icons-x" },
  {
    label: "Discord",
    href: sharaformsConfig.links.discord,
    icon: "i-simple-icons-discord",
  },
])

const linkGroups = computed(() => [
  {
    title: "Product",
    links: [
      { label: "Pricing", to: { name: "pricing" } },
      { label: "Features", to: { name: "index", hash: "#features" } },
      { label: "Integrations", to: { name: "integrations" } },
      { label: "Enterprise", to: { name: "enterprise" } },
      { label: "Industry", to: { name: "industry" } },
      { label: "Templates", to: { name: "templates" } },
    ],
  },
  {
    title: "Comparisons",
    wide: true,
    links: [
      { label: "All comparisons", to: { name: "comparisons" } },
      { label: "vs Google Forms", to: { name: "sharaforms-vs-googleforms" } },
      { label: "vs Typeform", to: { name: "sharaforms-vs-typeform" } },
      { label: "vs Fillout", to: { name: "sharaforms-vs-fillout" } },
      { label: "vs Jotform", to: { name: "sharaforms-vs-jotform" } },
      { label: "vs Tally", to: { name: "sharaforms-vs-tally" } },
      { label: "vs Youform", to: { name: "sharaforms-vs-youform" } },
      { label: "vs HeyForm", to: { name: "sharaforms-vs-heyform" } },
      { label: "vs Form.io", to: { name: "sharaforms-vs-formio" } },
      { label: "vs Formbricks", to: { name: "sharaforms-vs-formbricks" } },
      { label: "vs 123FormBuilder", to: { name: "sharaforms-vs-123formbuilder" } },
    ],
  },
  {
    title: "Updates",
    links: [
      { label: "Privacy Policy", to: { name: "privacy-policy" } },
      { label: "Terms of Use", to: { name: "terms-conditions" } },
    ],
  },
])
</script>

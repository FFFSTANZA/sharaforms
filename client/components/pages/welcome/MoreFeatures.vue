<template>
  <section>
    <Integrations />
  </section>
  <section class="py-14 sm:py-28 px-8 lg:px-12">
    <div class="max-w-266 mx-auto">
      <div class="text-center">
        <h3
          class="brand-text-strong text-3xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%]"
        >
          Publish once. Share anywhere.
        </h3>
        <p
          class="brand-text-muted mt-4 text-base font-normal leading-7 tracking-[-1.1%]"
        >
          Share with a link, embed in your product, use your own domain, or
          bring forms offline with QR codes.
        </p>
      </div>

      <div class="mt-12 sm:mt-16 grid gap-6">
        <div class="grid gap-6 lg:grid-cols-2">
          <div
            v-for="card in shareCards"
            :key="card.key"
            class="brand-surface-muted rounded-[24px] pt-8 md:pt-10 lg:pt-6 pl-8 md:pl-10 lg:pl-6 overflow-hidden"
            :class="card.size === 'large' ? 'lg:col-span-2' : ''"
          >
            <div
              class="grid gap-3 relative"
              :class="
                card.size === 'large'
                  ? 'items-end lg:grid-cols-12 gap-0 sm:gap-2 lg:gap-6'
                  : 'items-start sm:grid-cols-12 gap-2 sm:gap-6'
              "
            >
              <div
                class="flex flex-col items-start lg:pb-6"
                :class="
                  card.size === 'large'
                    ? 'sm:col-span-4 lg:col-span-3 gap-2 lg:gap-6'
                    : 'sm:col-span-6 md:col-span-5 gap-2 sm:gap-6'
                "
              >
                <div
                  class="h-16 w-16 rounded-[20px] flex items-center justify-center brand-gradient-warm shadow-xs"
                >
                  <UIcon :name="card.icon" class="h-8 w-8 text-white" />
                </div>
                <div class="text-xl leading-7 font-medium text-neutral-950">
                  {{ card.title }}
                </div>
              </div>

              <div
                :class="
                  card.size === 'large'
                    ? '-ml-8 sm:ml-0 sm:col-span-8 lg:col-span-9'
                    : 'sm:col-span-6 md:col-span-7 max-w-[278.16px] ml-auto'
                "
              >
                <img
                  :src="card.imageSrc"
                  :alt="card.title"
                  class="h-auto w-full object-contain"
                  :class="card.imageClass"
                  loading="lazy"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        class="brand-text-muted mt-12 sm:mt-16 text-center text-base font-normal leading-7 tracking-[-1.1%]"
      >
        One form system, ready for every channel.
      </div>
      <div class="mt-6 text-center">
        <div class="flex items-center justify-center">
          <UButton
            size="lg"
            :to="{
              name: authenticated ? 'forms-create' : 'forms-create-guest',
            }"
            trailing-icon="i-lucide-arrow-up-right"
            label="Get started. It's FREE!"
            class="brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
          />
        </div>
      </div>
    </div>
  </section>

  <section>
    <div class="px-8 lg:px-12">
      <div
        class="brand-surface-muted mx-auto w-full max-w-336 rounded-[32px] overflow-hidden"
      >
        <div
          class="relative py-8 sm:py-10 lg:py-14 xl:py-24 px-8 md:px-10 lg:px-14 xl:px-35"
        >
          <img
            src="/img/pages/welcome/Graphic.svg"
            class="pointer-events-none absolute top-[45%] left-0 right-0 min-w-full object-cover min-h-[55%]"
          />
          <div
            class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 relative z-10"
          >
            <div class="flex flex-col items-start gap-2">
              <h3
                class="brand-text-strong text-3xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%] xl:w-138"
              >
                Your security model. Your deployment choice.
              </h3>
              <p
                class="brand-text-muted my-4 text-base font-normal leading-7 tracking-[-1.1%]"
              >
                Built for teams that need stronger data ownership, compliance
                options, and deployment flexibility.
              </p>
              <p
                class="brand-text-muted text-base font-normal leading-7 tracking-[-1.1%]"
              >
                Use our India-based cloud or deploy on-premise when your team
                needs tighter control.
              </p>

              <UButton
                :to="{
                  name: authenticated ? 'forms-create' : 'forms-create-guest',
                }"
                size="lg"
                class="my-4 lg:my-10 brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
                trailing-icon="i-lucide-arrow-up-right"
                label="Create a Form"
              />

            </div>
            <div class="flex flex-col gap-3 xl:pl-13">
              <div
                v-for="pill in securityPills"
                :key="pill.label"
                class="brand-chip brand-text-strong flex items-center gap-2.5 w-fit rounded-[96px] py-1.5 pl-1.5 pr-4 text-sm leading-5 tracking-[-0.6%] font-medium"
              >
                <div
                  class="h-8 w-8 shrink-0 rounded-full flex items-center justify-center"
                  :class="pill.iconWrapClass"
                >
                  <UIcon
                    :name="pill.icon"
                    class="h-5.5 w-5.5"
                    :class="pill.iconClass"
                  />
                </div>
                <span>{{ pill.label }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-14 sm:py-28 px-8 lg:px-12">
    <div class="max-w-266 mx-auto">
      <div class="text-center">
          <h3
            class="brand-text-strong text-3xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%]"
          >
            Start solo.
            <br />
            Scale to the whole team.
          </h3>
          <p
            class="brand-text-muted mt-4 text-base font-normal leading-7 tracking-[-1.1%]"
          >
            Go from one form owner to shared workspaces, approvals, and
            compliance requirements without switching tools.
          </p>
      </div>

      <div class="my-12 sm:my-16 max-w-6xl mx-auto space-y-6">
        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2">
          <div
            v-for="block in teamFeatureBlocks.slice(0, 2)"
            :key="block.title"
            class="brand-surface-muted overflow-hidden relative rounded-[24px] p-8"
          >
            <h4
              class="brand-text-strong text-xl leading-7 tracking-[-0.6%] font-semibold"
            >
              {{ block.title }}
            </h4>
            <p
              class="brand-text-muted mt-4 text-base font-normal leading-7 tracking-[-1.1%]"
            >
              {{ block.description }}
            </p>
            <div class="mt-4 -mx-8 -mb-8">
              <img
                :src="block.image"
                :alt="block.title"
                class="w-full max-w-[500px] mx-auto rounded-t-xl object-cover"
                loading="lazy"
              />
            </div>
            <div
              class="absolute bottom-0 left-0 right-0 min-h-1/5 bg-linear-to-b from-transparent to-[#F8FAFC]"
            ></div>
          </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-1 lg:grid-cols-3">
          <div
            v-for="block in teamFeatureBlocks.slice(2)"
            :key="block.title"
            class="brand-surface-muted overflow-hidden relative rounded-3xl p-8"
          >
            <h4 class="brand-text-strong text-xl font-semibold">
              {{ block.title }}
            </h4>
            <p class="brand-text-muted mt-4 text-base font-medium leading-7">
              {{ block.description }}
            </p>
            <div
              class="flex lg:block justify-end items-end mt-6 -mr-8 lg:-ml-2 -mb-12"
            >
              <img
                :src="block.image"
                :alt="block.title"
                class="w-full max-w-78.5 rounded-t-xl object-cover"
                loading="lazy"
              />
            </div>
            <div
              class="absolute bottom-0 left-0 right-0 min-h-1/5 bg-linear-to-b from-transparent to-[#F8FAFC]"
            ></div>
          </div>
        </div>
      </div>

      <div class="text-center">
        <div
          class="flex flex-col sm:flex-row items-center justify-center gap-6"
        >
          <UButton
            size="lg"
            :to="{ name: 'enterprise' }"
            trailing-icon="i-lucide-arrow-up-right"
            label="Learn about Enterprise"
            class="brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
          />
          <UButton
            :to="{ name: 'pricing' }"
            label="View Pricing"
            variant="outline"
            color="neutral"
            size="lg"
            class="px-4 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const { isAuthenticated: authenticated } = useIsAuthenticated()

const welcomeAssetBase = "/img/pages/welcome/New%20Welcome/cleaned"

const shareCards = [
  {
    key: "link",
    size: "large",
    title: "Share with a simple link",
    icon: "i-heroicons:link",
    imageSrc: `${welcomeAssetBase}/share-1.png`,
    imageClass: "mt-4 sm:mt-6 lg:mt-8",
  },
  {
    key: "embed",
    size: "small",
    title: "Embed on your website or app",
    icon: "i-heroicons:code-bracket-square",
    imageSrc: `${welcomeAssetBase}/share-2.png`,
    imageClass: "mt-4 max-w-[24rem] sm:max-w-[19rem] md:max-w-[22rem] lg:max-w-[20rem]",
  },
  {
    key: "notion",
    size: "small",
    title: "Add forms directly into Notion",
    icon: "i-simple-icons-notion",
    imageSrc: "/img/pages/welcome/share-3.png",
  },
  {
    key: "domain",
    size: "small",
    title: "Use your own custom domain",
    icon: "i-heroicons:globe-alt",
    imageSrc: "/img/pages/welcome/share-4.png",
  },
  {
    key: "qr",
    size: "small",
    title: "Generate QR codes for offline collection",
    icon: "i-heroicons:qr-code",
    imageSrc: "/img/pages/welcome/share-5.png",
  },
]

const securityPills = [
  {
    label: "100% transparent & auditable",
    icon: "i-heroicons:code-bracket",
    iconWrapClass: "bg-neutral-100",
    iconClass: "text-neutral-600",
  },
  {
    label: "India hosting & GDPR-friendly architecture",
    icon: "i-heroicons:globe-alt",
    iconWrapClass: "bg-neutral-100",
    iconClass: "text-neutral-600",
  },
  {
    label: "Self-hosting / on-premise deployments",
    icon: "i-heroicons:server-stack",
    iconWrapClass: "bg-neutral-100",
    iconClass: "text-neutral-600",
  },
  {
    label: "SSO & SAML support",
    icon: "i-heroicons:lock-closed",
    iconWrapClass: "bg-neutral-100",
    iconClass: "text-neutral-600",
  },
  {
    label: "Form & submission audit logs",
    icon: "i-heroicons:clipboard-document-check",
    iconWrapClass: "bg-neutral-100",
    iconClass: "text-neutral-600",
  },
  {
    label: "SOC2 compliance underway",
    icon: "i-heroicons:shield-check",
    iconWrapClass: "bg-neutral-100",
    iconClass: "text-neutral-600",
  },
  {
    label: "Two-factor authentication (2FA) support",
    icon: "i-heroicons:key",
    iconWrapClass: "bg-neutral-100",
    iconClass: "text-neutral-600",
  },
  {
    label: "End-to-end data encryption",
    icon: "i-heroicons:lock-closed",
    iconWrapClass: "bg-neutral-100",
    iconClass: "text-neutral-600",
  },
]

const teamFeatureBlocks = [
  {
      title: "Collaboration & Management",
      description:
      "Coordinate work across teams with shared workspaces, clear roles, and visibility into who changed what.",
    image: "/img/pages/welcome/team-1.png",
  },
  {
    title: "Security & Compliance",
    description:
      "Meet internal requirements with SSO access, domain control, and admin-level security features.",
    image: "/img/pages/welcome/team-2.png",
  },
  {
    title: "Branding Control",
    description: "Match your brand end to end or white-label forms for clients.",
    image: "/img/pages/welcome/team-3.png",
  },
  {
    title: "Audit History",
    description: "Track form and submission activity with a clear audit trail.",
    image: "/img/pages/welcome/team-4.png",
  },
  {
    title: "Priority Support",
    description: "Get help fast with dedicated enterprise support.",
    image: "/img/pages/welcome/team-5.png",
  },
]
</script>

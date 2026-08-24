<template>
  <div class="marketing-page">
    <section class="relative">
      <div class="py-14 sm:py-28 px-8 sm:px-12 relative z-2">
        <div class="max-w-3xl mx-auto text-center">
          <h1
            class="brand-text-strong text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold"
          >
            Enterprise forms with real control
          </h1>
          <p
            class="brand-text-muted mt-4 text-lg sm:text-xl leading-7 tracking-[-1.5%] sm:leading-8 font-normal"
          >
            Build forms, surveys, and internal workflows with the polish
            business teams want and the control IT expects. Choose SharaForms
            Cloud, India hosting, or self-hosted deployment.
          </p>

          <div
            class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4"
          >
            <UButton
              :to="sharaformsConfig.links.book_onboarding"
              target="_blank"
              label="Book your demo"
              trailing-icon="i-lucide-arrow-up-right"
              size="lg"
              class="brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
            />
            <UButton
              :to="sharaformsConfig.links.self_hosting"
              target="_blank"
              label="Review self-hosting"
              variant="outline"
              size="lg"
              class="brand-button-secondary px-4 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
            />
          </div>

          <div class="mt-12 sm:mt-16">
            <div
              class="brand-text-muted text-base leading-7 tracking-[-1.1%] font-medium"
            >
              Trusted by teams that care about control, clarity, and security.
            </div>
            <div class="mt-6 flex items-center justify-center gap-12 flex-wrap">
              <div class="w-fit">
                <img
                  src="/img/pages/welcome/vanguard-1.png"
                  alt="Vanguard logo"
                  class="w-full"
                />
              </div>
              <div class="w-fit">
                <img
                  src="/img/pages/welcome/logo-berkeley-2.png"
                  alt="UC Berkeley logo"
                  class="w-full"
                />
              </div>
              <div class="w-fit">
                <img
                  src="/img/pages/welcome/UN_emblem_blue-2.png"
                  alt="United Nations logo"
                  class="w-full"
                />
              </div>
              <div class="w-fit">
                <img
                  src="/img/pages/welcome/Frame-47.png"
                  alt="American Red Cross logo"
                  class="w-full"
                />
              </div>
              <div class="w-fit">
                <img
                  src="/img/pages/welcome/Zomato_Logo-2.png"
                  alt="Zomato logo"
                  class="w-full"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="mt-12 sm:mt-16 max-w-266 mx-auto">
          <div class="grid gap-4 sm:grid-cols-3">
            <div
              v-for="item in controlHighlights"
              :key="item.title"
              class="brand-surface rounded-3xl p-6"
            >
              <div
                class="h-12 w-12 rounded-[16px] flex items-center justify-center"
                :class="item.iconWrapClass"
              >
                <UIcon
                  :name="item.icon"
                  class="h-6 w-6"
                  :class="item.iconClass"
                />
              </div>
              <div
                class="brand-text-strong mt-6 text-xl leading-7 tracking-[-0.6%] font-semibold"
              >
                {{ item.title }}
              </div>
              <p
                class="brand-text-muted mt-3 text-sm leading-6 tracking-[-0.6%] font-medium"
              >
                {{ item.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
      <div
        class="brand-section-wash pointer-events-none w-full h-full absolute inset-0"
      ></div>
    </section>

    <section class="py-14 sm:py-28 px-8 sm:px-12">
      <div class="max-w-266 mx-auto">
        <div class="max-w-lg mx-auto text-center">
          <div
            class="brand-gradient-text inline-block text-base leading-7 tracking-[-1.1%] font-medium"
          >
            Security &amp; Compliance
          </div>

          <h2
            class="brand-text-strong my-4 text-4xl sm:text-5xl sm:leading-14 tracking-[-1%] font-semibold"
          >
            Your Data,
            <br />
            Your Infrastructure,
            <br />
            Your Rules
          </h2>

          <p
            class="brand-text-muted text-base leading-7 tracking-[-1.1%] font-normal"
          >
            Choose how and where your forms run, without compromising on
            security, governance, or speed.
          </p>
        </div>

        <div
          class="my-12 sm:my-16 flex flex-wrap items-center justify-center gap-4"
        >
          <div
            class="brand-chip brand-text-strong inline-flex items-center gap-2 rounded-full pl-1.5 pr-4 py-1.5 text-sm leading-5 tracking-[-0.6%] font-medium"
          >
            <span
              class="brand-gradient-warm h-8 w-8 rounded-full flex items-center justify-center"
            >
              <UIcon
                name="i-lucide-lock-keyhole"
                class="h-5.5 w-5.5 text-white"
              />
            </span>
            <span>Single Sign-On (SSO) / SAML</span>
          </div>

          <div
            class="brand-chip brand-text-strong inline-flex items-center gap-2 rounded-full pl-1.5 pr-4 py-1.5 text-sm leading-5 tracking-[-0.6%] font-medium"
          >
            <span
              class="h-8 w-8 rounded-full bg-red-50 flex items-center justify-center"
            >
              <UIcon
                name="i-lucide-shield-check"
                class="h-5.5 w-5.5 text-red-600"
              />
            </span>
            <span>GDPR</span>
          </div>

          <div
            class="brand-chip brand-text-strong inline-flex items-center gap-2 rounded-full pl-1.5 pr-4 py-1.5 text-sm leading-5 tracking-[-0.6%] font-medium"
          >
            <span
              class="brand-chip-strong h-8 w-8 rounded-full flex items-center justify-center"
            >
              <UIcon
                name="i-lucide-globe"
                class="h-5.5 w-5.5"
              />
            </span>
            <span>Hosted in India</span>
          </div>
        </div>

        <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="item in securityFeatures"
            :key="item.title"
            class="brand-surface-muted rounded-3xl p-8"
          >
            <div
              class="brand-gradient-warm h-16 w-16 rounded-[20px] flex items-center justify-center"
            >
              <UIcon :name="item.icon" class="h-8 w-8 text-white" />
            </div>

            <div class="brand-text-strong mt-8 text-xl leading-7 font-medium">
              {{ item.title }}
            </div>
            <div
              class="brand-text-muted mt-4 text-base tracking-[-1.1%] font-medium leading-7"
            >
              {{ item.description }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-8 sm:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <div
            class="brand-gradient-text inline-block text-base leading-7 tracking-[-1.1%] font-medium"
          >
            Deployment Options
          </div>

          <h2
            class="my-4 text-4xl sm:text-5xl sm:leading-14 tracking-[-1%] font-semibold text-gray-950"
          >
            Choose where your
            <br class="hidden sm:block" />
            form data lives
          </h2>

          <p
            class="text-base leading-7 tracking-[-1.1%] font-normal text-gray-600"
          >
            Start with managed cloud, keep hosted data in India, or run
            SharaForms on your own infrastructure when policy requires it.
          </p>
        </div>

        <div class="mt-12 sm:mt-16 grid gap-6 lg:grid-cols-3">
          <div
            v-for="option in deploymentOptions"
            :key="option.title"
            class="rounded-3xl bg-gray-50 border border-gray-200 p-8 shadow-sm"
          >
            <div
              class="h-16 w-16 rounded-[20px] brand-gradient-warm shadow-sm flex items-center justify-center"
            >
              <UIcon :name="option.icon" class="h-8 w-8 text-white" />
            </div>
            <h3
              class="mt-8 text-xl leading-7 tracking-[-0.6%] font-semibold text-gray-950"
            >
              {{ option.title }}
            </h3>
            <p
              class="mt-4 text-base font-normal text-gray-600 leading-7 tracking-[-1.1%]"
            >
              {{ option.description }}
            </p>
            <ul
              class="mt-6 space-y-3 text-sm leading-5 tracking-[-0.6%] font-medium text-gray-700"
            >
              <li
                v-for="feature in option.features"
                :key="feature"
                class="flex items-center gap-2.5"
              >
                <UIcon
                  name="i-lucide-check"
                  class="h-5 w-5 shrink-0 text-emerald-600"
                />
                <span>{{ feature }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-8 sm:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="text-center">
          <div
            class="brand-gradient-text inline-block text-base leading-7 tracking-[-1.1%] font-medium"
          >
            Features for Enterprise Teams
          </div>

          <h2
            class="my-4 text-4xl sm:text-5xl sm:leading-14 tracking-[-1%] font-semibold text-gray-950"
          >
            Forms business teams can use,
            <br class="hidden sm:block" />
            with controls IT can trust
          </h2>

          <p
            class="max-w-lg mx-auto text-base leading-7 tracking-[-1.1%] font-normal text-gray-600"
          >
            Build, automate, and integrate secure forms across departments -
            all without writing a single line of code.
          </p>
        </div>

        <div class="mt-12 sm:mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="item in enterpriseFeatures"
            :key="item.title"
            class="overflow-hidden relative rounded-3xl border border-gray-200 bg-gray-50 p-8 flex flex-col justify-between"
          >
            <div>
              <div
                class="text-xl leading-7 tracking-[-0.6%] font-semibold text-gray-950"
              >
                {{ item.title }}
              </div>
              <div
                class="mt-4 text-base font-normal text-gray-600 leading-7 tracking-[-1.1%]"
              >
                {{ item.description }}
              </div>
            </div>
            <div class="mt-4 -mx-8 -mb-8">
              <img
                :src="item.imageSrc"
                :alt="item.title"
                class="w-full max-w-84.5 mx-auto rounded-t-xl object-cover"
                loading="lazy"
              />
            </div>
            <div
              class="absolute bottom-0 left-0 right-0 min-h-1/6 bg-linear-to-b from-transparent to-[#F8FAFC]"
            ></div>
          </div>
        </div>

        <div class="mt-12 sm:mt-16 flex justify-center">
          <UButton
            :to="{ name: 'index', hash: '#features' }"
            label="See the full feature set"
            trailing-icon="i-lucide-arrow-right"
            size="lg"
            class="brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
          />
        </div>
      </div>
    </section>

    <section class="px-8 sm:px-12 py-12 sm:py-28 bg-white">
      <div class="max-w-266 mx-auto">
        <div
          class="grid grid-cols-1 lg:grid-cols-12 lg:gap-12 items-center gap-10"
        >
          <div class="lg:col-span-7">
            <h2
              class="text-4xl sm:text-5xl sm:leading-14 tracking-[-1%] font-semibold text-gray-950"
            >
              VIP support and
              <br />
              account management
            </h2>
            <p
              class="mt-4 text-base leading-7 tracking-[-1.1%] font-normal text-gray-600"
            >
              Get direct access to dedicated experts who help you deploy, scale,
              and operate SharaForms with confidence.
            </p>
          </div>
          <div
            class="lg:col-span-5 w-full max-w-fit mx-auto lg:max-w-none rounded-3xl border border-gray-200 bg-gray-50 px-8 pt-8 pb-4"
          >
            <div
              v-for="badge in vipSupportBadges"
              :key="badge.label"
              class="flex mb-4 max-w-fit sm:w-auto items-center gap-3 rounded-full border border-gray-200 bg-white pl-1.5 pr-4 py-1.5 text-sm leading-5 tracking-[-0.6%] font-medium text-gray-800 shadow-sm"
            >
              <span
                class="h-8 w-8 rounded-full flex items-center justify-center shrink-0"
                :class="badge.badgeBg"
              >
                <UIcon
                  :name="badge.icon"
                  class="h-4 w-4"
                  :class="badge.iconColor"
                />
              </span>
              <span>{{ badge.label }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white">
      <div class="px-8 lg:px-12">
        <div
          class="brand-surface-strong mx-auto w-full max-w-336 rounded-[32px] overflow-hidden py-8 sm:py-10 lg:py-14 xl:py-24 px-8 md:px-10 lg:px-14 xl:px-35"
        >
          <div class="text-center">
            <div
              class="brand-gradient-text inline-block text-base tracking-[-1.1%] font-medium leading-7"
            >
              Integrations
            </div>
            <h3
              class="my-4 text-4xl sm:text-5xl font-semibold text-white tracking-[-1%] leading-14"
            >
              Works with your stack
            </h3>
            <p
              class="text-base tracking-[-1.1%] font-medium leading-7 text-white/72"
            >
               SharaForms fits into your workflow, connect to tools your team
              already uses.
            </p>
          </div>
          <div class="mt-12 sm:mt-16 flex justify-center">
            <div
              class="grid grid-cols-5 sm:grid-cols-5 gap-4 justify-center items-center"
            >
              <div
                v-for="app in integrationApps"
                :key="app.icon"
                class="h-14 sm:h-22 md:h-30 w-14 sm:w-22 md:w-30 rounded-2xl bg-white/8 ring-1 ring-white/14 flex items-center justify-center backdrop-blur-sm"
              >
                <UIcon
                  :name="app.icon"
                  class="h-9 sm:h-12 md:h-14 w-9 sm:w-12 md:w-14"
                  :class="app.iconClass"
                />
              </div>
            </div>
          </div>
          <div class="mt-12 sm:mt-16 flex justify-center">
            <UButton
              :to="{ name: 'integrations' }"
              label="Browse integrations"
              trailing-icon="i-lucide-arrow-up-right"
              class="brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
            />
          </div>
        </div>
      </div>
    </section>

    <section class="px-8 sm:px-12 py-14 sm:py-28 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <div
            class="brand-gradient-text inline-block text-base leading-7 tracking-[-1.1%] font-medium"
          >
            Why Enterprise
          </div>
          <h2
            class="my-4 text-4xl sm:text-5xl sm:leading-14 tracking-[-1%] font-semibold text-gray-950"
          >
            Control without slowing
            <br class="hidden sm:block" />
            teams down
          </h2>
          <p
            class="text-base leading-7 tracking-[-1.1%] font-normal text-gray-600"
          >
            Keep the builder simple for every team while giving IT the controls
            needed for access, data, and deployment.
          </p>
        </div>

        <div class="mt-12 sm:mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="item in enterpriseProofBlocks"
            :key="item.title"
            class="rounded-3xl bg-gray-50 border border-gray-200 p-8 shadow-sm"
          >
            <div
              class="h-12 w-12 rounded-[16px] brand-gradient-warm shadow-sm flex items-center justify-center"
            >
              <UIcon :name="item.icon" class="h-6 w-6 text-white" />
            </div>
            <h3
              class="mt-6 text-xl leading-7 tracking-[-0.6%] font-semibold text-gray-950"
            >
              {{ item.title }}
            </h3>
            <p
              class="mt-3 text-base font-normal text-gray-600 leading-7 tracking-[-1.1%]"
            >
              {{ item.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <FaqSection
      :faqs="enterpriseFaqs"
      :title-lines="['Enterprise forms, security,', 'and deployment']"
      description="Clear answers for teams evaluating SharaForms for secure forms, data control, and managed or self-hosted deployment."
      id-prefix="enterprise-faq-answer"
      @contact="contactUs"
    />

    <section class="bg-white px-8 lg:px-12 pb-14 sm:pb-28">
      <div
        class="mx-auto w-full max-w-336 rounded-4xl bg-gray-950 shadow-2xl overflow-hidden py-8 sm:py-10 lg:py-14 px-8 md:px-10 lg:px-14"
      >
        <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
          <div class="lg:col-span-7">
            <div
              class="brand-gradient-text-warm inline-block text-base tracking-[-1.1%] font-medium leading-7"
            >
              Enterprise
            </div>
            <h2
              class="mt-4 text-3xl sm:text-5xl sm:leading-14 font-semibold text-white tracking-[-1%]"
            >
              Bring secure forms to every team.
            </h2>
            <p
              class="mt-4 max-w-2xl text-base sm:text-lg leading-7 tracking-[-1.1%] font-normal text-gray-400"
            >
              Talk with us about SSO, data residency, self-hosting, and the
              rollout path that fits your organization.
            </p>
          </div>

          <div class="lg:col-span-5">
            <div class="flex flex-col sm:flex-row lg:justify-end gap-4">
              <UButton
                :to="sharaformsConfig.links.book_onboarding"
                target="_blank"
                label="Book your demo"
                trailing-icon="i-lucide-arrow-up-right"
                size="lg"
                class="w-fit brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
              />
              <UButton
                :to="sharaformsConfig.links.self_hosting"
                target="_blank"
                label="Review self-hosting"
                variant="outline"
                color="neutral"
                size="lg"
                class="w-fit px-4 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium bg-white text-gray-950 hover:bg-white/95"
              />
            </div>

            <div
              class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm leading-5 tracking-[-0.6%] font-medium text-gray-300 lg:justify-end"
            >
              <span
                v-for="item in finalCtaProofs"
                :key="item"
                class="inline-flex items-center gap-2"
              >
                <UIcon
                  name="i-lucide-check"
                  class="h-5 w-5 text-emerald-400"
                />
                {{ item }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <OpenFormFooter :show-cta="false" />
  </div>
</template>

<script setup>
import FaqSection from "~/components/pages/FaqSection.vue"
import sharaformsConfig from "~/sharaforms.config.js"

definePageMeta({
  layout: "default",
})

const { logEvent } = usePostHog()

onMounted(() => {
  logEvent('enterprise_page_viewed', {})
})

useOpnSeoMeta({
  title: "Secure Form Builder for Teams and Enterprises",
  description:
    "Build secure forms, surveys, and workflows with SharaForms Enterprise. Automate data collection and deploy on managed cloud, India hosting, or self-hosted.",
  keywords: "secure form builder, enterprise form builder, secure forms, form workflow automation, self-hosted form builder, data collection forms, form data security",
  speakable: [".faq-answer p", "h1"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "Enterprise" },
  ],
})

const controlHighlights = [
  {
    icon: "i-lucide-lock-keyhole",
    iconWrapClass: "brand-gradient-warm",
    iconClass: "text-white",
    title: "Access control",
    description: "Keep access tight with SSO, roles, permissions, and audit visibility.",
  },
  {
    icon: "i-lucide-server",
    iconWrapClass: "brand-gradient-warm",
    iconClass: "text-white",
    title: "Data ownership",
    description: "Choose the hosting model that matches your policy, team, and risk profile.",
  },
  {
    icon: "i-lucide-arrow-left-right",
    iconWrapClass: "bg-emerald-50",
    iconClass: "text-emerald-600",
    title: "Team workflows",
    description: "Run approvals, routing, surveys, and integrations from one form layer.",
  },
]

const securityFeatures = [
  {
    icon: "i-lucide-badge-check",
    title: "Granular Access Control",
    description:
      "Set precise roles and permissions across teams, workspaces, and forms.",
  },
  {
    icon: "i-lucide-layers",
    title: "India Data Residency",
    description:
      "Keep hosted form data in India, or move fully in-house when you self-host.",
  },
  {
    icon: "i-lucide-lock-keyhole",
    title: "Encryption at Rest & In Transit",
    description: "Protect every submission in transit and at rest.",
  },
  {
    icon: "i-lucide-file-text",
    title: "Audit Logs & Security SLAs",
    description: "Track changes, ownership, and operational accountability clearly.",
  },
]

const deploymentOptions = [
  {
    icon: "i-lucide-cloud",
    title: "Managed Cloud",
    description:
      "Let SharaForms handle hosting, updates, monitoring, and backups while your team stays focused on operations.",
    features: ["Fastest setup", "Managed updates", "Live chat support"],
  },
  {
    icon: "i-lucide-globe",
    title: "India Hosting",
    description:
      "Keep hosted form data in India while preserving the speed and simplicity of managed cloud.",
    features: ["Hosted in India", "GDPR-friendly architecture", "No infrastructure work"],
  },
  {
    icon: "i-lucide-server",
    title: "Self-hosted / On-premise",
    description:
      "Run SharaForms on your own infrastructure when policy, procurement, or compliance requires it.",
    features: ["Full infrastructure control", "Custom builds", "Self-hosting support"],
  },
]

const enterpriseFeatures = [
  {
    title: "Payments",
    description:
      "Collect payments with Stripe or PayPal and connect them cleanly to downstream workflows.",
    imageSrc: "/img/pages/enterprise/enterprise-1.png",
  },
  {
    title: "Automations",
    description:
      "Connect SharaForms to your existing tools, send submissions to CRMs, trigger webhooks, or automate workflows with Zapier or Make.",
    imageSrc: "/img/pages/enterprise/enterprise-2.png",
  },
  {
    title: "E-Signatures",
    description:
      "Capture signatures inside the form flow, without sending users somewhere else.",
    imageSrc: "/img/pages/enterprise/enterprise-3.png",
  },
  {
    title: "Collaboration",
    description:
      "Invite teams, assign access, and review submissions together without losing control.",
    imageSrc: "/img/pages/enterprise/enterprise-4.png",
  },
  {
    title: "Custom Branding & Themes",
    description: "Shape every surface to feel native to your brand, not rented from someone else.",
    imageSrc: "/img/pages/enterprise/enterprise-5.png",
  },
  {
    title: "Flexible Deployment",
    description:
      "Stay in managed cloud for speed, or self-host when you need full control over data and uptime.",
    imageSrc: "/img/pages/enterprise/enterprise-6.png",
  },
]

const vipSupportBadges = [
  {
    icon: "i-lucide-lock-keyhole",
    label: "Migration Assistance",
    badgeBg: "brand-gradient-warm",
    iconColor: "text-white",
  },
  {
    icon: "i-lucide-wrench",
    label: "Self-hosting Technical Support",
    badgeBg: "bg-orange-50",
    iconColor: "text-orange-600",
  },
  {
    icon: "i-lucide-circle-user-round",
    label: "Dedicated Customer Success Manager",
    badgeBg: "brand-gradient-warm",
    iconColor: "text-white",
  },
]

const integrationApps = [
  { icon: "i-simple-icons-zendesk", iconClass: "text-[#ffffff]" },
  { icon: "lucide:mail", iconClass: "text-[#3B82F6]" },
  { icon: "ic:baseline-discord", iconClass: "text-[#5865F2]" },
  { icon: "mdi:telegram", iconClass: "text-[#27A7E7]" },
  { icon: "lucide:webhook", iconClass: "text-[#0061FF]" },

  { icon: "i-simple-icons-zapier", iconClass: "text-[#FF4A00]" },
  { icon: "simple-icons:n8n", iconClass: "text-[#EA4B71]" },
  { icon: "mdi:google-spreadsheet", iconClass: "text-[#34A853]" },
  { icon: "i-simple-icons-zoom", iconClass: "text-[#2D8CFF]" },

  // { icon: "i-simple-icons-linear", iconClass: "text-[#5E6AD2]" },
  // { icon: "i-simple-icons-evernote", iconClass: "text-[#00A82D]" },
  // { icon: "i-simple-icons-notion", iconClass: "text-white" },
  // { icon: "i-simple-icons-airtable", iconClass: "text-[#18BFFF]" },
  // { icon: "i-simple-icons-meta", iconClass: "text-[#0668E1]" },
]

const enterpriseProofBlocks = [
  {
    icon: "i-lucide-maximize-2",
    title: "No response caps",
    description:
      "Collect unlimited submissions on unlimited forms without turning every success into a pricing problem.",
  },
  {
    icon: "i-lucide-square-code",
    title: "Transparent and auditable",
    description:
      "Understand how the product works and avoid a black-box form stack for critical workflows.",
  },
  {
    icon: "i-lucide-lock-keyhole",
    title: "SSO and permissions",
    description:
      "Use enterprise identity controls and workspace roles to keep access aligned with your team.",
  },
  {
    icon: "i-lucide-layers",
    title: "Own your data path",
    description:
      "Choose managed cloud, India hosting, or self-hosting based on your internal requirements.",
  },
  {
    icon: "i-lucide-paintbrush",
    title: "Brand control",
    description:
      "Use custom domains, branding removal, white-labeling, and themes for a polished experience.",
  },
  {
    icon: "i-lucide-circle-user-round",
    title: "Migration support",
    description:
      "Get help with deployment, migration, and self-hosted technical questions as your rollout grows.",
  },
]

const enterpriseFaqs = [
  {
    question: "Can SharaForms be self-hosted?",
    answer:
      "Yes. SharaForms can be deployed on your own infrastructure, and Enterprise support can help with self-hosting questions, migration, and deployment planning.",
  },
  {
    question: "Does SharaForms support SSO?",
    answer:
      "Yes. SharaForms supports SSO options such as OIDC and Enterprise SSO controls such as SAML, depending on your setup and plan.",
  },
  {
    question: "Where is form data hosted?",
    answer:
      "Hosted SharaForms data is stored in India. You can also self-host SharaForms when your organization needs direct control over infrastructure and data residency.",
  },
  {
    question: "Can teams manage roles and permissions?",
    answer:
      "Yes. Enterprise is designed for multi-user workspaces with roles, permissions, and audit visibility for teams managing forms at scale.",
  },
  {
    question: "What support is included with Enterprise?",
    answer:
      "Enterprise includes priority support for rollout, migration, and self-hosting needs, with dedicated help for teams that need stronger operational guidance.",
  },
]

defineRouteRules({
  swr: 3600,
})

const enterpriseFaqSchema = {
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "FAQPage",
      "@id": "#faq",
      mainEntity: enterpriseFaqs.map((faq) => ({
        "@type": "Question",
        name: faq.question,
        acceptedAnswer: {
          "@type": "Answer",
          text: faq.answer,
        },
      })),
    },
  ],
}

useHead({
  script: [
    {
      key: "enterprise-faq-schema",
      type: "application/ld+json",
      textContent: JSON.stringify(enterpriseFaqSchema),
    },
  ],
})

const finalCtaProofs = ["SSO/SAML", "India hosting", "Self-hosting support"]

const contactUs = () => {
  window.location.href = `mailto:${sharaformsConfig.links.contact_email}`
}
</script>

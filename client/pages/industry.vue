<template>
  <div class="marketing-page">
    <section class="relative">
      <div class="px-8 lg:px-12 py-14 sm:py-28 relative z-2">
        <div class="max-w-266 mx-auto">
          <div class="max-w-3xl mx-auto text-center">
            <div class="flex items-center justify-center gap-4">
              <div class="flex items-center gap-0.5 text-amber-500">
                <UIcon name="i-lucide-star" class="h-4 w-4" />
                <UIcon name="i-lucide-star" class="h-4 w-4" />
                <UIcon name="i-lucide-star" class="h-4 w-4" />
                <UIcon name="i-lucide-star" class="h-4 w-4" />
                <UIcon name="i-lucide-star" class="h-4 w-4" />
              </div>
              <div
                class="brand-text-muted text-base leading-7 tracking-[-1.1%] font-medium"
              >
                4.9/5 — Trusted by 10,000+ teams in finance, fintech, and
                consulting
              </div>
            </div>

            <h1
              class="brand-text-strong mt-8 text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold lg:px-1"
            >
              Transform financial
              <!-- <br class="hidden sm:block" /> -->
              operations with powerful
              <!-- <br class="hidden sm:block" /> -->
              and secure forms
            </h1>

            <p
              class="brand-text-muted mt-4 text-lg sm:text-xl leading-7 tracking-[-1.5%] sm:leading-8 font-normal"
            >
              Streamline client onboarding, compliance checks, and data
              collection with SharaForms — the form builder built for
              financial institutions that need flexibility, security, and
              control.
            </p>
          </div>

          <div class="mt-12 sm:mt-16 relative">
            <h2
              class="text-center text-xl leading-7 tracking-[-0.6%] font-semibold text-gray-950"
            >
              Key Features for financial institutions
            </h2>
            <div
              class="mt-6 flex flex-wrap justify-center flex-col md:flex-row px-1 gap-4"
            >
              <div
                v-for="feature in keyFeatures"
                :key="feature.label"
                class="brand-chip brand-text-strong flex items-center gap-2.5 mx-auto md:mx-0 w-fit rounded-full py-1.5 pl-1.5 pr-4 text-sm leading-5 tracking-[-0.6%] font-medium"
              >
                <div
                  class="h-8 w-8 shrink-0 rounded-full flex items-center justify-center"
                  :class="feature.iconWrapClass"
                >
                  <UIcon
                    :name="feature.icon"
                    class="h-4 w-4"
                    :class="feature.iconClass"
                  />
                </div>
                <span class="whitespace-nowrap">
                  {{ feature.label }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        class="brand-section-wash w-full h-full absolute inset-0"
      ></div>
    </section>

    <section class="px-8 lg:px-12 py-14 sm:py28">
      <div class="max-w-266 mx-auto">
        <div class="max-w-lg mx-auto text-center">
          <h2
          class="brand-text-strong text-3xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%]"
          >
            How financial teams
            <br class="hidden sm:block" />
            use SharaForms
          </h2>
          <p
          class="brand-text-muted mt-4 text-base font-normal leading-7 tracking-[-1.1%]"
          >
            Choose how and where you host your forms – without compromising on
            enterprise-grade security.
          </p>
        </div>

        <div
          class="mt-12 sm:mt-16 grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3"
        >
          <div
            v-for="useCase in financialUseCases"
            :key="useCase.title"
            class="brand-surface-muted rounded-[24px] p-8"
          >
            <div
              class="brand-gradient-warm h-16 w-16 rounded-[20px] flex items-center justify-center"
            >
              <UIcon :name="useCase.icon" class="h-8 w-8 text-white" />
            </div>

            <h3 class="brand-text-strong mt-8 text-xl leading-7 font-medium">
              {{ useCase.title }}
            </h3>
            <p
              class="brand-text-muted mt-4 text-base font-normal leading-7 tracking-[-1.1%]"
            >
              {{ useCase.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="px-8 lg:px-12 py-14 sm:py-28 bg-white">
      <TemplatesSlider class="max-w-266 mx-auto" />
    </section>

    <section class="bg-white">
      <div class="px-8 lg:px-12">
        <div
          class="brand-surface-strong mx-auto w-full max-w-336 rounded-[32px] overflow-hidden py-8 sm:py-10 lg:py-14 xl:py-24 px-8 md:px-10 lg:px-14 xl:px-35"
        >
          <div class="text-center max-w-3xl mx-auto">
            <div
              class="brand-gradient-text text-base tracking-[-1.1%] font-medium leading-7"
            >
              Integrations
            </div>
            <h3
              class="my-4 text-4xl sm:text-5xl font-semibold text-white tracking-[-1%] leading-14"
            >
              Connect SharaForms with the tools your team already uses
            </h3>
            <p
              class="text-base tracking-[-1.1%] font-medium leading-7 text-white/72"
            >
              SharaForms fits into your workflow — connect to tools your team
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
              label="Explore All Integrations"
              trailing-icon="i-lucide-arrow-up-right"
              class="brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
            />
          </div>
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-8 lg:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-157.25 mx-auto text-center">
          <h2
            class="text-3xl sm:text-5xl sm:leading-14 font-semibold text-gray-950 tracking-[-1%]"
          >
            How SharaForms improves
            <br />
            financial processes
          </h2>
          <p
            class="mt-4 text-base font-normal text-gray-600 leading-7 tracking-[-1.1%]"
          >
            Choose how and where you host your forms – without compromising on
            enterprise-
            <br />
            grade security.
          </p>
        </div>

        <div class="mt-12 sm:mt-16 grid gap-6 md:grid-cols-2">
          <div
            v-for="item in financialImprovements"
            :key="item.title"
            class="rounded-3xl border border-gray-200 bg-gray-50 p-8"
          >
            <div class="flex items-start gap-8">
              <div
                class="h-12 w-12 rounded-[12px] flex items-center justify-center brand-gradient-warm shadow-xs shrink-0"
              >
                <UIcon :name="item.icon" class="h-6 w-6 text-white" />
              </div>

              <div>
                <h3 class="text-xl leading-7 font-medium text-gray-950">
                  {{ item.title }}
                </h3>
                <p
                  class="mt-4 text-base font-medium text-gray-600 leading-7 tracking-[-1.1%]"
                >
                  {{ item.description }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <OpenFormFooter />
  </div>
</template>

<script setup>
import TemplatesSlider from "~/components/pages/welcome/TemplatesSlider.vue"

defineRouteRules({
  swr: 3600,
})

definePageMeta({
  layout: "default",
})

useOpnSeoMeta({
  title: "Industry",
  description:
    "Transform financial operations with powerful and secure forms built for teams that need flexibility, security, and control.",
  speakable: ["h1", ".marketing-page > section:first-child p"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "Industry" },
  ],
})

const financialUseCases = [
  {
    title: "Client onboarding",
    description:
      "Create digital intake forms to collect KYC data and supporting documents securely.",
    icon: "i-lucide-id-card",
  },
  {
    title: "Compliance and audit requests",
    description:
      "Build internal forms to track approvals, risk assessments, and incident reports.",
    icon: "i-lucide-clipboard-check",
  },
  {
    title: "Loan applications",
    description:
      "Gather borrower information and documentation without manual data entry.",
    icon: "i-lucide-dollar-sign",
  },
  {
    title: "Expense and reimbursement submissions",
    description:
      "Simplify financial workflows with automated routing and notifications.",
    icon: "i-lucide-rotate-ccw",
  },
  {
    title: "Customer feedback & surveys",
    description:
      "Collect NPS, satisfaction, or post-service insights while keeping data private.",
    icon: "i-lucide-message-square",
  },
  {
    title: "Onboarding, account opening and risk assessments",
    description:
      "Digitize account opening, KYC onboarding, and risk assessment workflows with structured, auditable forms.",
    icon: "i-lucide-layers",
  },
]

const financialImprovements = [
  {
    title: "Reduces operational overhead",
    description:
      "Replace manual paperwork and email chains with centralized digital workflows.",
    icon: "i-lucide-copy",
  },
  {
    title: "Ensures compliance",
    description:
      "Keep sensitive data within your control — self-host or use secure cloud hosting.",
    icon: "i-lucide-shield-check",
  },
  {
    title: "Accelerates turnaround times",
    description:
      "Automate approvals and instantly route form submissions to the right departments.",
    icon: "i-lucide-clock",
  },
  {
    title: "Improves data accuracy",
    description:
      "Validate inputs, standardize formats, and integrate directly with your existing systems.",
    icon: "i-lucide-badge-check",
  },
  {
    title: "Enhances client experience",
    description:
      "Provide a professional, branded experience that builds trust from the first interaction.",
    icon: "i-lucide-heart",
  },
  {
    title: "Strengthens governance and audit readiness",
    description:
      "Every submission keeps a complete audit trail, so internal and external reviews have the evidence they need.",
    icon: "i-lucide-users",
  },
]

const keyFeatures = [
  {
    label: "Advanced permissions and access rules",
    icon: "i-lucide-badge-check",
    iconWrapClass: "brand-gradient-warm",
    iconClass: "text-white",
  },
  {
    label: "Custom form logic and conditional workflows",
    icon: "i-lucide-arrow-left-right",
    iconWrapClass: "bg-red-50 ring-1 ring-red-100",
    iconClass: "text-red-600",
  },
  {
    label: "Encrypted data storage and SSL by default",
    icon: "i-lucide-lock-keyhole",
    iconWrapClass: "bg-pink-50 ring-1 ring-pink-100",
    iconClass: "text-pink-600",
  },
  {
    label: "Audit trails and submission tracking",
    icon: "i-lucide-clipboard-check",
    iconWrapClass: "brand-gradient-warm",
    iconClass: "text-white",
  },
  {
    label: "API & webhook integrations for automation",
    icon: "i-lucide-terminal",
    iconWrapClass: "brand-gradient-warm",
    iconClass: "text-white",
  },
  {
    label: "Custom branding and white-labeling",
    icon: "i-lucide-palette",
    iconWrapClass: "bg-orange-50 ring-1 ring-orange-100",
    iconClass: "text-orange-600",
  },
  {
    label: "Option to self-host for full data control",
    icon: "i-lucide-server",
    iconWrapClass: "bg-fuchsia-50 ring-1 ring-fuchsia-100",
    iconClass: "text-fuchsia-600",
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
  { icon: "sharaforms:activepieces", iconClass: "text-[#6366F1]" },
  { icon: "mdi:google-spreadsheet", iconClass: "text-[#34A853]" },
  { icon: "i-simple-icons-zoom", iconClass: "text-[#2D8CFF]" },

  // { icon: "i-simple-icons-linear", iconClass: "text-[#5E6AD2]" },
  // { icon: "i-simple-icons-evernote", iconClass: "text-[#00A82D]" },
  // { icon: "i-simple-icons-notion", iconClass: "text-white" },
  // { icon: "i-simple-icons-airtable", iconClass: "text-[#18BFFF]" },
  // { icon: "i-simple-icons-meta", iconClass: "text-[#0668E1]" },
]
</script>

<template>
  <div class="marketing-page">
    <section class="relative overflow-hidden -mt-[76px] bg-[#292438]">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(168,85,247,0.24),transparent_42%),linear-gradient(180deg,rgba(255,255,255,0.04),rgba(255,255,255,0))]"></div>

      <div class="relative max-w-6xl mx-auto px-8 lg:px-12 pt-[124px] sm:pt-[152px] pb-20 sm:pb-24">
        <div class="max-w-3xl">
          <div class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/8 px-3 py-1 text-sm text-white/70">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            Effective {{ effectiveDate }}
          </div>

          <h1 class="mt-6 text-white text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold">
            Terms of Use
          </h1>

          <p class="mt-5 max-w-2xl text-white/70 text-lg sm:text-xl leading-7 sm:leading-8 tracking-[-1.1%]">
            These terms govern your use of the SharaForms website, hosted service, APIs, templates, documentation, self-hosted materials, and any related support or commercial services we provide.
          </p>
        </div>
      </div>

      <svg
        class="pointer-events-none absolute -bottom-px left-0 h-[60px] w-full sm:h-[90px]"
        viewBox="0 0 1440 120"
        preserveAspectRatio="none"
        aria-hidden="true"
      >
        <path
          d="M0 52C150 108 260 10 430 54C610 102 720 120 880 62C1050 0 1170 96 1440 42V120H0Z"
          fill="#fcfcfd"
        />
      </svg>
    </section>

    <section class="px-8 lg:px-12 pb-16 sm:pb-24">
      <div class="max-w-6xl mx-auto grid gap-8 lg:grid-cols-[minmax(0,1.7fr)_minmax(280px,0.85fr)]">
        <div class="space-y-6">
          <div class="rounded-3xl border border-neutral-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-3">
              <div
                v-for="item in overviewCards"
                :key="item.title"
                class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4"
              >
                <div class="text-sm font-medium text-neutral-500">
                  {{ item.title }}
                </div>
                <div class="mt-2 text-base font-semibold text-neutral-900">
                  {{ item.value }}
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-3xl border border-neutral-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="space-y-10">
              <section
                v-for="section in termSections"
                :id="section.id"
                :key="section.id"
                class="scroll-mt-28"
              >
                <h2 class="text-2xl font-semibold tracking-[-0.6%] text-neutral-950">
                  {{ section.title }}
                </h2>

                <div class="mt-4 space-y-4 text-base leading-7 text-neutral-700">
                  <p
                    v-for="paragraph in section.paragraphs"
                    :key="paragraph"
                  >
                    {{ paragraph }}
                  </p>
                </div>

                <ul
                  v-if="section.bullets"
                  class="mt-4 space-y-3 list-disc pl-5 text-base leading-7 text-neutral-700"
                >
                  <li v-for="bullet in section.bullets" :key="bullet">
                    {{ bullet }}
                  </li>
                </ul>
              </section>
            </div>
          </div>
        </div>

        <div class="space-y-6 lg:sticky lg:top-24 lg:self-start">
          <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="text-sm font-medium uppercase tracking-[0.12em] text-neutral-500">
              On this page
            </div>

            <div class="mt-4 flex flex-col gap-3">
              <a
                v-for="section in termSections"
                :key="section.id"
                :href="`#${section.id}`"
                class="text-sm leading-6 text-neutral-700 transition-colors hover:text-neutral-950 hover:no-underline"
              >
                {{ section.title }}
              </a>
            </div>
          </div>

          <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="text-lg font-semibold text-neutral-950">
              Before you use SharaForms
            </div>

            <ul class="mt-4 space-y-3 list-disc pl-5 text-sm leading-6 text-neutral-700">
              <li>You are responsible for the forms, submissions, files, and automations created in your workspace.</li>
              <li>You must give respondents legally required notices and obtain any consents your use case requires.</li>
              <li>Third-party integrations, payment providers, and external tools remain subject to their own terms and privacy policies.</li>
            </ul>
          </div>

          <div class="rounded-3xl border border-neutral-200 bg-neutral-50 p-6 shadow-sm">
            <div class="text-lg font-semibold text-neutral-950">
              Questions about these terms?
            </div>

            <p class="mt-3 text-sm leading-6 text-neutral-700">
              Reach us at
              <a href="mailto:support@sharaforms.com" class="font-medium text-neutral-950 underline">support@sharaforms.com</a>
              or through the
              <a :href="sharaformsConfig.links.help_url" target="_blank" rel="noopener noreferrer" class="font-medium text-neutral-950 underline">Help Center</a>.
            </p>
          </div>
        </div>
      </div>
    </section>

    <OpenFormFooter :show-cta="false" />
  </div>
</template>

<script setup>
import sharaformsConfig from "~/sharaforms.config.js"

definePageMeta({
  middleware: [],
})

useOpnSeoMeta({
  title: "Terms of Use",
  description:
    "Read the terms that govern use of the SharaForms website, hosted product, APIs, templates, documentation, integrations, and self-hosted materials.",
})

defineRouteRules({
  swr: 3600,
})

const effectiveDate = "July 17, 2026"

const overviewCards = [
  {
    title: "Coverage",
    value: "Website, cloud product, APIs, templates, docs, and support",
  },
  {
    title: "Billing impact",
    value: "Paid plans follow the commercial terms shown at checkout or in-product",
  },
  {
    title: "Primary contact",
    value: "support@sharaforms.com",
  },
]

const termSections = [
  {
    id: "acceptance",
    title: "1. Acceptance and Scope",
    paragraphs: [
      "By accessing or using SharaForms, creating an account, publishing a form, connecting an integration, purchasing a paid plan, or downloading and using related product materials, you agree to these Terms of Use. If you use SharaForms on behalf of a company, institution, or other legal entity, you represent that you are authorized to bind that entity to these terms.",
      "These terms apply to the SharaForms public website, hosted application, APIs, templates, documentation, support interactions, and any related services unless a separate written agreement expressly governs a particular relationship with you.",
    ],
  },
  {
    id: "eligibility-and-accounts",
    title: "2. Eligibility, Accounts, and Security",
    paragraphs: [
      "You must provide accurate registration, billing, and workspace information and keep that information reasonably up to date. You are responsible for all activity that occurs under your account, including activity by invited users, API tokens, service accounts, and connected third-party integrations.",
      "You must take reasonable steps to protect passwords, access tokens, devices, and administrator access. If you believe your account has been compromised, you must notify us promptly and rotate or revoke affected credentials without delay.",
    ],
  },
  {
    id: "acceptable-use",
    title: "3. Acceptable Use",
    paragraphs: [
      "You may use SharaForms only in compliance with applicable law and these terms. You may not use the service in a way that threatens the platform, harms others, or bypasses the commercial or technical limits of the product.",
    ],
    bullets: [
      "Do not upload, transmit, or facilitate malware, credential theft, phishing, spam, fraud, or abusive content.",
      "Do not collect, process, or disclose personal data through forms without the notices, permissions, and legal basis required for your use case.",
      "Do not reverse engineer, probe, scan, or interfere with the integrity, availability, or security of the service except as expressly allowed by law and authorized vulnerability disclosure processes.",
      "Do not bypass rate limits, plan limits, licensing restrictions, access controls, or protective measures.",
      "Do not use SharaForms in a way that infringes intellectual property, privacy, publicity, employment, consumer, export-control, sanctions, or anti-discrimination laws.",
    ],
  },
  {
    id: "plans-and-billing",
    title: "4. Plans, Billing, and Renewals",
    paragraphs: [
      "Some features of SharaForms require a paid plan. Pricing, plan limits, included features, billing intervals, taxes, and renewal behavior are presented at checkout or within the billing area of the product. By purchasing a paid plan, you agree to pay the applicable fees, taxes, and any other charges described at the time of purchase.",
      "Unless otherwise stated, subscriptions renew automatically for the next billing period until canceled. Upgrades, downgrades, cancellations, refunds, credits, and proration are handled according to the billing flow, the specific commercial offer, and any mandatory consumer laws that apply to you.",
    ],
  },
  {
    id: "customer-content",
    title: "5. Customer Content and Data Responsibilities",
    paragraphs: [
      "You retain responsibility for the forms, content, files, submissions, workflows, recipient lists, generated documents, and data you or your users process through SharaForms. You instruct us how to process that data by configuring your workspace, publishing forms, inviting users, enabling integrations, and using product features.",
      "You are responsible for your own privacy notices, consent flows, retention schedules, data subject request handling, backup requirements, and compliance obligations. If your use involves regulated data or sector-specific legal requirements, you must evaluate whether SharaForms is appropriate for that use before collecting such data.",
    ],
  },
  {
    id: "third-party-services",
    title: "6. Third-Party Services and Integrations",
    paragraphs: [
      "SharaForms may interoperate with third-party products and services such as identity providers, email or messaging tools, automation systems, payment processors, analytics platforms, storage providers, and productivity applications. Your use of third-party services is governed by their separate terms, privacy policies, pricing, and service commitments.",
      "We are not responsible for third-party services, their availability, security practices, or changes to their APIs or commercial terms. If a third-party integration causes data loss, service interruption, or unauthorized disclosure because of the third party or your configuration, that risk remains with you to the extent permitted by law.",
    ],
  },
  {
    id: "ip-and-license",
    title: "7. Intellectual Property and Feedback",
    paragraphs: [
      "SharaForms and its associated branding, hosted service, design elements, documentation, and proprietary materials remain the property of their respective owners and licensors. These terms grant you a limited, non-exclusive, non-transferable right to use the service in accordance with your plan and these terms.",
      "All SharaForms products and services are proprietary. Enterprise or commercial features may be subject to separate proprietary terms or license files. Your rights in those materials are governed by the applicable license text that accompanies them.",
      "If you send us suggestions, ideas, feature requests, or other feedback, you grant us a worldwide, royalty-free right to use that feedback to improve our products and services without any obligation to you.",
    ],
  },
  {
    id: "availability-and-changes",
    title: "8. Availability, Changes, and Beta Features",
    paragraphs: [
      "We work to keep SharaForms reliable and secure, but we do not guarantee uninterrupted availability, perfect compatibility, or error-free operation. We may modify, improve, replace, restrict, or discontinue features at any time, including to address abuse, legal requirements, infrastructure needs, or product direction.",
      "Features labeled beta, preview, experimental, or similar are provided for evaluation and may be incomplete, unstable, or withdrawn at any time. Beta features may not be covered by the same support, uptime, or reliability expectations as generally available features.",
    ],
  },
  {
    id: "termination",
    title: "9. Suspension and Termination",
    paragraphs: [
      "You may stop using SharaForms at any time. We may suspend or terminate your access, remove content, disable integrations, or restrict use if we reasonably believe you have violated these terms, created security or legal risk, failed to pay applicable fees, or used the service in a way that threatens other users or the platform.",
      "Termination does not relieve you of obligations that arose before termination, including payment obligations. Sections that by their nature should survive termination, including those relating to intellectual property, disclaimers, liability limits, disputes, and accrued rights, will continue to apply.",
    ],
  },
  {
    id: "disclaimers-and-liability",
    title: "10. Disclaimers and Limitation of Liability",
    paragraphs: [
      "To the maximum extent permitted by law, SharaForms is provided on an as-is and as-available basis. We disclaim implied warranties of merchantability, fitness for a particular purpose, title, non-infringement, and any warranties arising from course of dealing, usage, or trade practice.",
      "To the maximum extent permitted by law, we will not be liable for indirect, incidental, special, consequential, exemplary, or punitive damages, or for loss of profits, revenues, goodwill, business interruption, or data. Our aggregate liability arising out of or related to the service will not exceed the amount you paid to SharaForms for the relevant paid service during the twelve months before the event giving rise to the claim.",
    ],
  },
  {
    id: "general-terms",
    title: "11. General Terms",
    paragraphs: [
      "We may update these Terms of Use from time to time. The latest version posted on this page will control from its effective date. Continued use of SharaForms after an update becomes effective means you accept the revised terms.",
      "If any part of these terms is found unenforceable, the remaining parts will remain in effect to the fullest extent permitted by law. Our failure to enforce any provision is not a waiver of that provision. Questions about these terms may be sent to support@sharaforms.com or raised through our Help Center.",
    ],
  },
]
</script>

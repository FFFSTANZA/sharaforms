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
            Privacy Policy
          </h1>

          <p class="mt-5 max-w-2xl text-white/70 text-lg sm:text-xl leading-7 sm:leading-8 tracking-[-1.1%]">
            This policy explains what personal data SharaForms handles, why we handle it, who controls that data, and what choices are available to account holders, workspace members, site visitors, and form respondents.
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
                v-for="section in policySections"
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
                v-for="section in policySections"
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
              Your privacy controls
            </div>

            <ul class="mt-4 space-y-3 list-disc pl-5 text-sm leading-6 text-neutral-700">
              <li>Account holders can access, export, or delete their account data from product settings when those tools are available.</li>
              <li>Cookie and analytics preferences can be managed from the consent banner on the public site and inside the app experience where shown.</li>
              <li>Requests about a specific form response should usually be sent to the organization that published the form, because that organization decides what data is collected and why.</li>
            </ul>
          </div>

          <div class="rounded-3xl border border-neutral-200 bg-neutral-50 p-6 shadow-sm">
            <div class="text-lg font-semibold text-neutral-950">
              Contact us
            </div>

            <p class="mt-3 text-sm leading-6 text-neutral-700">
              For privacy requests or questions about this policy, contact us at
              <a href="mailto:contact@sharaforms.com" class="font-medium text-neutral-950 underline">contact@sharaforms.com</a>
              or use our
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
  title: "Privacy Policy",
  description:
    "Learn what data SharaForms collects, how it is used, when it is shared, and what privacy rights and controls are available to customers and respondents.",
})

defineRouteRules({
  swr: 3600,
})

const effectiveDate = "July 17, 2026"

const overviewCards = [
  {
    title: "Applies to",
    value: "Visitors, customers, workspace members, and respondents",
  },
  {
    title: "Core role",
    value: "Platform provider and processor for customer-managed form data",
  },
  {
    title: "Primary contact",
    value: "contact@sharaforms.com",
  },
]

const policySections = [
  {
    id: "scope-and-roles",
    title: "1. Scope and Data Roles",
    paragraphs: [
      "This Privacy Policy applies to the SharaForms website, hosted application, public forms, APIs, templates, documentation, and related customer support interactions. It covers personal data we collect directly from you, data generated by use of the service, and data customers ask us to process on their behalf.",
      "SharaForms acts in different privacy roles depending on the relationship. For account registration, billing, support, marketing site analytics, and platform security, SharaForms generally acts as the controller of that personal data. For form submission data collected by our customers through forms they create, SharaForms generally acts as a processor or service provider and the customer that published the form is the controller responsible for the underlying collection and use.",
    ],
  },
  {
    id: "information-we-collect",
    title: "2. Information We Collect",
    paragraphs: [
      "The data we collect depends on how you interact with SharaForms. Some information is provided directly by you, some is generated automatically when you use the service, and some may be received from connected integrations or payment providers.",
    ],
    bullets: [
      "Account and workspace information such as name, email address, login credentials, workspace profile details, role assignments, and preferences.",
      "Billing and transaction information such as billing contact details, subscription status, invoices, tax information, and limited payment metadata from processors. We do not store full card numbers issued by payment processors.",
      "Product usage data such as login events, browser and device information, IP address, audit activity, feature usage, and diagnostic logs used for reliability and abuse prevention.",
      "Form and submission data such as form fields, uploaded files, generated documents, automation settings, and response content submitted by respondents to customer-owned forms.",
      "Support and communication records such as emails, feedback, sales conversations, bug reports, and onboarding notes.",
    ],
  },
  {
    id: "how-we-use-data",
    title: "3. How We Use Personal Data",
    paragraphs: [
      "We use personal data to provide and operate SharaForms, maintain security, authenticate users, process transactions, answer support requests, improve product performance, and communicate important service information. We also use limited site and product analytics to understand adoption and maintain usability where permitted by your preferences and applicable law.",
      "Where required by law, we rely on appropriate legal bases such as performance of a contract, legitimate interests, consent, and compliance with legal obligations. Customers remain responsible for determining their own legal basis for the personal data they collect through forms they publish using SharaForms.",
    ],
  },
  {
    id: "cookies-and-analytics",
    title: "4. Cookies, Tracking, and Analytics",
    paragraphs: [
      "SharaForms uses cookies and similar technologies to keep the service secure, remember session state, store preference choices, measure traffic, and improve product performance. Some cookies are strictly necessary for authentication, fraud prevention, and the core functionality of the application.",
      "Where non-essential analytics or similar tools are used, you can manage those preferences through the consent tools made available on the site or in the product. Blocking some cookies may affect the functionality or convenience of certain parts of the service.",
    ],
  },
  {
    id: "sharing-and-disclosures",
    title: "5. Sharing and Disclosures",
    paragraphs: [
      "We do not sell personal data in the ordinary meaning of that term. We may share information with service providers and partners only where reasonably necessary to operate the platform, deliver support, secure the service, process payments, or perform customer-requested integrations.",
    ],
    bullets: [
      "Infrastructure, hosting, storage, monitoring, and backup providers.",
      "Payment processors, invoicing tools, and accounting or tax service providers.",
      "Support, communications, and scheduling providers used to answer requests or deliver service messages.",
      "Third-party integrations enabled by the customer, such as automation, messaging, CRM, or productivity tools, according to the customer's configuration.",
      "Regulators, courts, law enforcement, or other parties where disclosure is required to comply with law, protect rights, investigate abuse, or prevent harm.",
    ],
  },
  {
    id: "security-and-retention",
    title: "6. Security and Retention",
    paragraphs: [
      "We use administrative, technical, and organizational measures intended to protect personal data against unauthorized access, loss, misuse, alteration, and disclosure. These measures include access controls, logging, credential protections, product security features, and operational monitoring. No system can be guaranteed to be perfectly secure, and customers are also responsible for securing their own accounts, endpoints, integrations, and downstream workflows.",
      "We retain personal data for as long as reasonably necessary to provide the service, honor customer instructions, comply with legal and tax obligations, resolve disputes, enforce agreements, and maintain security or backup continuity. Retention periods vary depending on the type of data, the plan or feature in use, and the deletion actions taken by the customer or user.",
    ],
  },
  {
    id: "international-transfers",
    title: "7. International Transfers",
    paragraphs: [
      "SharaForms and its service providers may process data in countries other than the country where you live or where the customer that owns a form is established. When international transfers occur, we take reasonable steps to use appropriate contractual, technical, or organizational safeguards where required by applicable law.",
    ],
  },
  {
    id: "rights-and-choices",
    title: "8. Your Rights and Choices",
    paragraphs: [
      "Depending on your location and the laws that apply to you, you may have rights to access, correct, delete, object to, restrict, or export certain personal data, and to withdraw consent where processing relies on consent. You may also have the right to lodge a complaint with a supervisory authority.",
      "If you are a respondent who submitted a form to one of our customers, the fastest route is usually to contact the organization named on that form, because it controls the form and decides what data is collected. If we receive a respondent request that relates to customer-controlled form data, we may direct the request to that customer or ask for more information so the request can be handled correctly.",
    ],
  },
  {
    id: "customer-responsibilities",
    title: "9. Customer Responsibilities",
    paragraphs: [
      "Customers are responsible for the forms they publish, the notices and consents they provide to respondents, the legal basis they rely on, the instructions they give to SharaForms, and the integrations or exports they enable. Customers must make sure that their collection and use of data through SharaForms complies with the privacy, employment, health, consumer, sector-specific, and international laws that apply to their activities.",
    ],
  },
  {
    id: "children",
    title: "10. Children's Data",
    paragraphs: [
      "SharaForms is not directed to children, and we do not knowingly collect personal data from children in violation of applicable law. If you believe a child has provided personal data through the service without proper authorization, contact us and we will investigate and take appropriate action.",
    ],
  },
  {
    id: "changes-and-contact",
    title: "11. Changes to This Policy and Contact Information",
    paragraphs: [
      "We may update this Privacy Policy from time to time to reflect product changes, legal developments, or operational updates. The current version will be posted on this page with its effective date. If a change materially affects how we handle personal data, we may also provide additional notice through the product, by email, or through other reasonable means.",
      "Questions, requests, or concerns about this policy can be sent to contact@sharaforms.com or raised through the support options available inside the product and our Help Center.",
    ],
  },
]
</script>

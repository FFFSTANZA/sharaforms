<template>
  <div class="mx-auto max-w-266">
    <div class="text-center">
      <h2
        class="text-4xl sm:text-5xl sm:leading-14 tracking-[-1%] font-semibold text-gray-950"
      >
        Feature Comparison
      </h2>
      <p
        class="mt-4 text-base font-normal tracking-[-1.1%] leading-7 text-gray-600"
      >
        Compare the features of the different plans and choose the one that best
        suits your needs.
      </p>
    </div>

    <div
      class="relative mt-12 sm:mt-16 overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-[0_4px_24px_-12px_rgba(0,0,0,0.08)]"
    >
      <div class="relative overflow-x-auto">
        <div class="sticky top-0 z-20">
          <table class="w-full min-w-[68rem] table-fixed border-collapse">
            <colgroup>
              <col class="w-[26%]">
              <col class="w-[18.5%]">
              <col class="w-[18.5%]">
              <col class="w-[18.5%]">
              <col class="w-[18.5%]">
            </colgroup>
            <thead>
              <tr>
                <th class="border-b border-[#E6E8EE] bg-white py-5 pr-6 text-left align-bottom text-sm font-semibold text-[#6E7278]">
                  &nbsp;
                </th>
                <th
                  v-for="(plan, planIndex) in plans"
                  :key="planIndex"
                  class="border-b border-[#E6E8EE] p-4 pb-4 text-center align-middle"
                  :class="planIndex === 1 ? 'bg-[#E4F4F8]/60' : 'bg-white'"
                >
                  <div class="mx-auto flex flex-col items-center gap-1.5">
                    <span
                      v-if="planIndex === 1"
                      class="inline-flex items-center gap-1 rounded-full border border-[#E4F4F8] bg-white px-2.5 py-0.5 text-[11px] leading-5 font-semibold text-[#0891b2]"
                    >
                      <UIcon name="i-lucide-star" class="h-3 w-3" />
                      Most popular
                    </span>
                    <div class="text-base leading-6 font-semibold text-[#1D1F24]">
                      {{ plan.label }}
                    </div>
                    <div class="text-sm leading-5 text-[#8E9198]">
                      <template v-if="plan.priceLabel">
                        {{ plan.priceLabel }}<span class="text-[#A7ABB2]">/mo</span>
                      </template>
                      <template v-else>Custom</template>
                    </div>
                  </div>
                </th>
              </tr>
            </thead>
          </table>
        </div>

        <table class="w-full min-w-[68rem] table-fixed border-collapse">
          <colgroup>
            <col class="w-[26%]">
            <col class="w-[18.5%]">
            <col class="w-[18.5%]">
            <col class="w-[18.5%]">
            <col class="w-[18.5%]">
          </colgroup>
          <tbody>
            <template v-for="section in sections" :key="section.title">
              <tr class="bg-white">
                <th
                  colspan="5"
                  class="px-6 pb-2 pt-6 pr-6 text-left text-xs leading-5 font-semibold tracking-wider text-gray-500 uppercase"
                >
                  {{ section.title }}
                </th>
              </tr>

              <tr
                v-for="(row, rowIndex) in section.rows"
                :key="rowIndex"
                class="border-b border-neutral-100"
              >
                <th
                  class="px-6 py-4 pr-6 text-left text-sm leading-5 font-medium text-gray-700"
                >
                  {{ row.label }}
                </th>

                <td
                  v-for="(plan, planIndex) in plans"
                  :key="planIndex"
                  class="px-4 py-4 text-center"
                  :class="planIndex === 1 ? 'bg-[#E4F4F8]/40' : ''"
                >
                  <div class="flex min-h-6 items-center justify-center gap-2">
                    <template v-if="row.values?.[planIndex] === true">
                      <UIcon class="h-4 w-4 text-[#16a34a]" name="i-lucide-check" />
                    </template>

                    <template
                      v-else-if="
                        row.values?.[planIndex] === false ||
                        row.values?.[planIndex] == null
                      "
                    >
                      <span class="text-base font-medium text-[#C7C9CE]">—</span>
                    </template>

                    <template v-else-if="row.values?.[planIndex] === 'soon'">
                      <UIcon
                        title="Coming soon..."
                        class="h-4 w-4 text-[#d97706]"
                        name="i-lucide-clock"
                      />
                    </template>

                    <template v-else>
                      <span class="text-sm leading-5 font-medium text-[#383B41]">
                        {{ row.values?.[planIndex] }}
                      </span>
                    </template>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
const { getPlanPrice, getTierDisplayName } = useBillingUpsell()

const formatPlanPrice = (plan) => {
  if (plan === "free") return "$0"
  const price = getPlanPrice(plan, false)
  if (price == null) return null
  return `$${price}`
}

const plans = computed(() => [
  {
    key: "free",
    label: getTierDisplayName("free"),
    priceLabel: formatPlanPrice("free"),
  },
  {
    key: "pro",
    label: getTierDisplayName("pro"),
    priceLabel: formatPlanPrice("pro"),
  },
  {
    key: "business",
    label: getTierDisplayName("business"),
    priceLabel: formatPlanPrice("business"),
  },
  {
    key: "enterprise",
    label: getTierDisplayName("enterprise"),
    priceLabel: formatPlanPrice("enterprise"),
  },
])

const sections = [
  {
    title: "Core Form Capabilities",
    rows: [
      {
        label: "Unlimited forms & submissions",
        values: [true, true, true, true],
      },
      {
        label: "File uploads",
        values: ["10MB", "50MB", "1GB", "(configurable)"],
      },
      {
        label: "Form logic & validation",
        values: [true, true, true, true],
      },
      {
        label: "Computed fields (calculations)",
        values: [true, true, true, true],
      },
      {
        label: "Pre-fills, URL params",
        values: [true, true, true, true],
      },
    ],
  },
  {
    title: "Collaboration",
    rows: [
      {
        label: "Multi-user access",
        values: [
          "(all admins)",
          "(all admins)",
          "(roles & permissions)",
          "(roles + SSO)",
        ],
      },
      {
        label: "Workspaces",
        values: ["1", "Unlimited", "Unlimited", "Unlimited"],
      },
    ],
  },
  {
    title: "Branding",
    rows: [
      {
        label: "Branding removal",
        values: [false, true, true, true],
      },
      {
        label: "Custom domain",
        values: [false, true, true, true],
      },
      {
        label: "Advanced branding (CSS/fonts)",
        values: [false, false, true, true],
      },
      {
        label: "White-label hosting",
        values: [false, false, false, true],
      },
    ],
  },
  {
    title: "Delivery",
    rows: [
      {
        label: "Custom SMTP",
        values: [false, true, true, true],
      },
    ],
  },
  {
    title: "Security & Access Control",
    rows: [
      {
        label: "Security (password/IP/expiry)",
        values: [false, true, true, true],
      },
      {
        label: "Advanced SSO (SAML/LDAP)",
        values: [false, false, false, true],
      },
    ],
  },
  {
    title: "Integrations",
    rows: [
      {
        label: "Basic integrations (Email, Webhook, Zapier, Google Sheets)",
        values: [true, true, true, true],
      },
      {
        label: "Slack, Discord, Telegram + Notion, Supabase & Resend",
        values: [false, true, true, true],
      },
      {
        label: "Advanced integrations (Airtable, Pipedrive, Trello, Linear, Teams and more)",
        values: [false, false, true, true],
      },
    ],
  },
  {
    title: "Data & Insights",
    rows: [
      {
        label: "Analytics dashboard",
        values: [false, true, true, true],
      },
      {
        label: "Partial submissions / draft saving",
        values: [false, false, true, true],
      },
    ],
  },
  {
    title: "Compliance",
    rows: [
      {
        label: "Audit logs & compliance",
        values: [false, false, false, true],
      },
      {
        label: "External storage (S3, GCS)",
        values: [false, false, false, true],
      },
    ],
  },
  {
    title: "Support & Services",
    rows: [
      {
        label: "Priority support",
        values: [false, false, true, "(SLA)"],
      },
      {
        label: "SLA & onboarding",
        values: [false, false, false, true],
      },
    ],
  },
]
</script>

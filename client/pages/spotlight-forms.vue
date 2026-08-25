<template>
  <div class="marketing-page">
    <section class="relative overflow-hidden bg-[#292438] -mt-[76px]">
      <div class="relative pt-[124px] sm:pt-[156px] px-8 lg:px-12 max-w-7xl mx-auto">
        <nav aria-label="Breadcrumb" class="flex justify-center mb-5">
          <ol class="flex items-center gap-2 text-sm text-white/50">
            <li>
              <NuxtLink to="/" class="hover:text-white/80">Home</NuxtLink>
            </li>
            <li aria-hidden="true">/</li>
            <li class="text-white/80">Spotlight Forms</li>
          </ol>
        </nav>
        <div class="max-w-3xl mx-auto text-center">
          <h1 class="text-white text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold mt-2 mb-4">
            See every question.
            <span class="brand-gradient-text">Focus on one.</span>
          </h1>

          <p class="text-white/70 text-lg sm:text-xl leading-7 sm:leading-8 font-normal max-w-2xl mx-auto">
            Spotlight forms list every question on one page while the active question takes visual
            focus. Respondents see full scope at a glance and still get the calm of one thing at
            a time.
          </p>

          <p class="text-white/55 text-base sm:text-lg leading-7 font-normal max-w-2xl mx-auto mt-4">
            Built-in calculations, conditional logic, signatures, and PDFs included. Unlimited
            forms, unlimited responses, free.
          </p>

          <div class="relative mt-9">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
              <UButton
                size="lg"
                :to="{ name: authenticated ? 'forms-create' : 'forms-create-guest' }"
                trailing-icon="i-heroicons:arrow-up-right"
                label="Create a spotlight form"
                class="premium-primary-button px-5 py-3 rounded-xl text-[15px] leading-7 tracking-[-1.1%] font-semibold text-white"
              />
              <UButton
                size="lg"
                to="/templates"
                variant="outline"
                color="white"
                label="Browse templates"
                class="px-5 py-3 rounded-xl text-[15px] leading-7 font-semibold text-white ring-white/30 hover:ring-white/60"
              />
            </div>
          </div>
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

    <section class="max-w-3xl mx-auto px-6 py-16 sm:py-20">
      <h2 class="text-2xl sm:text-3xl font-semibold text-neutral-900 tracking-tight">
        What is a spotlight form?
      </h2>
      <p class="mt-5 text-base leading-8 text-neutral-700">
        A spotlight form shows all questions on a single page while styling draws attention to the
        question currently in focus. Completed questions dim and collapse into a compact summary,
        upcoming questions stay visible as a preview of what comes next, and the active question
        sits front and center. It is the middle ground between classic multi-page forms, which
        split fields across screens, and focused one-question-at-a-time forms, which hide
        everything except the current step.
      </p>
      <p class="mt-4 text-base leading-8 text-neutral-700">
        The result respects both sides of completion: respondents keep an honest view of total
        effort, and your form still guides their eyes to exactly one decision at a time.
      </p>
    </section>

    <section class="bg-white border-y border-neutral-100 py-16 sm:py-20">
      <div class="max-w-3xl mx-auto px-6">
        <h2 class="text-2xl sm:text-3xl font-semibold text-neutral-900 tracking-tight">
          How spotlight mode works
        </h2>
        <ol class="mt-8 space-y-7 border-l border-neutral-200 pl-7">
          <li v-for="(step, i) in steps" :key="i" class="relative">
            <span
              class="absolute -left-[38px] flex h-6 w-6 items-center justify-center rounded-full bg-pink-600 text-xs font-bold text-white"
            >{{ i + 1 }}</span>
            <h3 class="text-base font-semibold text-neutral-900">{{ step.title }}</h3>
            <p class="mt-1.5 text-[15px] leading-7 text-neutral-600">{{ step.text }}</p>
          </li>
        </ol>
      </div>
    </section>

    <section class="max-w-3xl mx-auto px-6 py-16 sm:py-20">
      <h2 class="text-2xl sm:text-3xl font-semibold text-neutral-900 tracking-tight">
        Spotlight vs focused vs classic: which presentation fits?
      </h2>
      <p class="mt-4 text-base leading-8 text-neutral-700">
        Every SharaForms form can be presented three ways. You can switch modes anytime without
        rebuilding fields or logic, so pick the layout that matches each audience.
      </p>
      <div class="mt-8 overflow-x-auto rounded-xl border border-neutral-200 bg-white">
        <table class="w-full min-w-[560px] text-left text-sm">
          <thead>
            <tr class="border-b border-neutral-200 bg-neutral-50">
              <th scope="col" class="px-4 py-3 font-semibold text-neutral-900"></th>
              <th scope="col" class="px-4 py-3 font-semibold text-neutral-900">Classic</th>
              <th scope="col" class="px-4 py-3 font-semibold text-neutral-900">Focused</th>
              <th scope="col" class="px-4 py-3 font-semibold text-neutral-900">Spotlight</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100">
            <tr v-for="row in comparisonRows" :key="row.label" class="align-top">
              <th scope="row" class="px-4 py-3 text-left font-medium text-neutral-900">{{ row.label }}</th>
              <td class="px-4 py-3 text-neutral-600">{{ row.classic }}</td>
              <td class="px-4 py-3 text-neutral-600">{{ row.focused }}</td>
              <td class="px-4 py-3 text-neutral-600">{{ row.spotlight }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="bg-white border-y border-neutral-100 py-16 sm:py-20">
      <div class="max-w-3xl mx-auto px-6">
        <h2 class="text-2xl sm:text-3xl font-semibold text-neutral-900 tracking-tight">
          When to choose spotlight mode
        </h2>
        <ul class="mt-8 space-y-4">
          <li v-for="item in useCases" :key="item.title" class="flex gap-3">
            <UIcon name="i-heroicons:check-circle-20-solid" class="mt-1 h-5 w-5 shrink-0 text-pink-600" />
            <p class="text-[15px] leading-7 text-neutral-700">
              <strong class="font-semibold text-neutral-900">{{ item.title }}.</strong>
              {{ item.text }}
            </p>
          </li>
        </ul>
        <div class="mt-10 rounded-xl border-l-4 border-pink-500 bg-pink-50 p-5">
          <p class="text-[15px] leading-7 text-neutral-700">
            Switching is instant: open any form, change the presentation style, and publish.
            Fields, logic, calculations, and integrations carry over unchanged, so you can compare
            layouts against real completion data instead of guessing.
          </p>
        </div>
      </div>
    </section>

    <ScrollReveal>
      <FaqSection
        class="sf-faq"
        :faqs="spotlightFaqs"
        :title-lines="['Spotlight form', 'questions']"
        description="Direct answers about how spotlight mode works, who it suits, and what it costs."
        :default-open-index="0"
        id-prefix="spotlight-faq-answer"
        @contact="contactUs"
      />
    </ScrollReveal>

    <section class="bg-[#292438] py-16 sm:py-20">
      <div class="max-w-2xl mx-auto px-6 text-center">
        <h2 class="text-2xl sm:text-3xl font-semibold text-white tracking-tight">
          Build your first spotlight form in minutes
        </h2>
        <p class="mt-4 text-base leading-7 text-white/60">
          Start from scratch or pick a template, flip on spotlight mode, and share the link. Free
          forever with unlimited forms and unlimited submissions.
        </p>
        <UButton
          size="lg"
          :to="{ name: authenticated ? 'forms-create' : 'forms-create-guest' }"
          trailing-icon="i-heroicons:arrow-up-right"
          label="Create a free form"
          class="premium-primary-button mt-8 px-5 py-3 rounded-xl text-[15px] leading-7 tracking-[-1.1%] font-semibold text-white"
        />
      </div>
    </section>

    <OpenFormFooter />
  </div>
</template>

<script setup>
import sharaformsConfig from "~/sharaforms.config.js"
import { useIsAuthenticated } from "~/composables/useAuthFlow"

definePageMeta({
  layout: "default",
})

const { isAuthenticated: authenticated } = useIsAuthenticated()

useOpnSeoMeta({
  title: "Spotlight Forms: See Every Question, Focus on One",
  description:
    "Spotlight forms show every question while the active one takes focus. Build yours free with calculations, conditional logic, and unlimited submissions.",
  ogImage: "/share-preview.jpg",
  keywords:
    "spotlight forms, spotlight mode, form that shows all questions, one question at a time alternative, see every question form builder, free form builder",
  speakable: ["h1", ".marketing-page > section:first-child p", ".faq-answer p"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "Spotlight Forms" },
  ],
})

const steps = [
  {
    title: "Build your fields once",
    text: "Add questions, sections, images, and signature blocks in the visual editor. Layout comes later, so you never duplicate work to change how a form feels.",
  },
  {
    title: "Pick Spotlight as the presentation style",
    text: "Choose Spotlight in the form settings. All questions render as cards on one page: answered ones collapse into a tidy summary, and the active card expands front and center.",
  },
  {
    title: "Share and watch completion climb",
    text: "Publish the link, embed it, or generate a QR code. Because respondents always see how much remains, long forms stop losing people midway through.",
  },
]

const comparisonRows = [
  {
    label: "Question visibility",
    classic: "One page per step",
    focused: "Current question only",
    spotlight: "All questions, one in focus",
  },
  {
    label: "Progress feel",
    classic: "Step counter",
    focused: "Immersive, screen by screen",
    spotlight: "Visible checklist of everything left",
  },
  {
    label: "Best for",
    classic: "Long, structured applications",
    focused: "Conversational flows and quizzes",
    spotlight: "Mixed-length forms needing scope honesty",
  },
  {
    label: "Rebuilding needed to switch",
    classic: "No",
    focused: "No",
    spotlight: "No",
  },
]

const useCases = [
  {
    title: "Applications and intakes",
    text: "Rental applications, job applications, and patient intake forms run long. Spotlight keeps every remaining section visible so applicants pace themselves instead of abandoning at step four.",
  },
  {
    title: "Registration and RSVP forms",
    text: "Guests scan the full list of details you need, answer the highlighted question, and watch completed ones tick off. Fewer surprise questions means fewer abandoned sign-ups.",
  },
  {
    title: "Order and request forms",
    text: "Customers comparing quantities and options keep the whole picture on screen while each choice gets its own moment of focus. Calculations update totals live as they go.",
  },
  {
    title: "Quizzes and assessments",
    text: "Participants see exactly how many questions remain without losing exam-style concentration on the current one, and self-grading formulas score answers instantly on submit.",
  },
]

const spotlightFaqs = [
  {
    question: "What is a spotlight form?",
    answer:
      "A spotlight form displays every question on a single page while the active question receives visual focus. Answered questions dim and collapse, upcoming ones preview what remains, and respondents always know how much is left. It combines the transparency of one-page forms with the guidance of one-question-at-a-time pacing.",
  },
  {
    question: "How is spotlight mode different from one-question-at-a-time forms?",
    answer:
      "One-question-at-a-time forms show only the current question and reveal the next after answering. Spotlight mode keeps all questions listed on the same page while highlighting the active one. You get similar focus benefits without hiding total scope, which reduces drop-off among respondents who want to see the full commitment first.",
  },
  {
    question: "Can I switch a form from classic pages to spotlight without rebuilding it?",
    answer:
      "Yes. Presentation style is a display setting, not part of your structure. Open any form, change the style to Spotlight, and publish. Fields, conditional logic, calculations, notifications, and integrations all carry over unchanged, so testing a new layout against real completion data takes seconds.",
  },
  {
    question: "Do conditional logic and calculations work in spotlight forms?",
    answer:
      "Yes. Both run on submitted values, not layout. Hidden questions revealed by logic appear in place with the same focus behavior, and formula-driven totals, scores, and prices update live while respondents type. Everything available in classic and focused modes works identically in spotlight mode.",
  },
  {
    question: "Are spotlight forms good for mobile respondents?",
    answer:
      "Yes. The layout is built mobile-first: the active question fills the screen, answered questions compress into tappable summary rows, and progress stays visible while scrolling. Respondents can jump back to review or edit earlier answers with one tap instead of paging through a flow again.",
  },
  {
    question: "Can I create a spotlight form for free?",
    answer:
      "Yes. Spotlight mode is included on the free plan alongside unlimited forms and unlimited submissions. Conditional logic, built-in calculations, file uploads, and sharing links are all part of the free tier, so you can publish a complete spotlight form without entering payment details.",
  },
]

useHead({
  script: [
    {
      key: "spotlight-faq-schema",
      type: "application/ld+json",
      textContent: JSON.stringify({
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "@id": `${useRuntimeConfig().public.appUrl.replace(/\/+$/, '')}/spotlight-forms#faq`,
        mainEntity: spotlightFaqs.map((faq) => ({
          "@type": "Question",
          name: faq.question,
          acceptedAnswer: {
            "@type": "Answer",
            text: faq.answer,
          },
        })),
      }),
    },
  ],
})

const contactUs = () => {
  window.location.href = `mailto:${sharaformsConfig.links.contact_email}`
}
</script>

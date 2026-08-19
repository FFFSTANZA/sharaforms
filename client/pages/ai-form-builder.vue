<template>
  <div class="marketing-page flex-1">
    <!-- START HERO -->
    <section
      class="relative py-12 sm:py-16 lg:py-20 xl:py-24"
    >
      <div class="absolute inset-0">
        <img
          class="w-full h-full object-cover object-top"
          src="/img/pages/ai_form_builder/background-pattern.svg"
          alt=""
        >
      </div>

      <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto relative">
        <div class="max-w-4xl mx-auto text-center">
          <h1
            class="brand-text-strong text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold"
          >
            Build production-ready forms with
            <span
              class="brand-gradient-text lg:block"
            >AI form builder.</span>
          </h1>
          <p
            class="mt-4 sm:mt-5 text-base leading-7 sm:text-xl sm:leading-9 font-medium text-neutral-500"
          >
            Describe what you need in plain English. SharaForms drafts the
            structure, fields, and flow in seconds, so you can refine instead
            of starting from scratch.
          </p>

          <div class="mt-8 flex justify-center">
            <UButton
              class="brand-button-primary mr-1"
              :to="{ name: authenticated ? 'forms-create' : 'forms-create-guest' }"
              trailing-icon="i-lucide-arrow-right"
              label="Try the AI builder free"
            />
          </div>
        </div>

        <div class="max-w-6xl mx-auto mt-12 sm:mt-16">
          <div
            class="brand-surface -m-2 rounded-xl p-2 backdrop-blur-xs lg:-m-4 lg:rounded-2xl lg:p-4"
          >
            <video
              class="rounded-sm ring ring-neutral-200 shadow-xl shadow-violet-600/10 ring-white/60"
              controls
              autoplay
              loop
              muted
            >
              <source
                src="/video/sharaforms-ai.mp4"
                type="video/mp4"
              >

              This browser does not display the video tag.
            </video>
          </div>
        </div>

        <div
          class="grid lg:grid-cols-3 grid-cols-1 sm:max-w-lg lg:max-w-5xl sm:mx-auto gap-8 sm:gap-10 mt-12 sm:mt-16"
        >
          <div class="flex items-start gap-4">
            <img
              class="w-12 h-12 shrink-0"
              src="/img/pages/ai_form_builder/icon-fast.svg"
              alt=""
            >

            <div>
              <p class="text-md font-semibold text-neutral-900">
                From idea to first draft, fast
              </p>
              <p class="text-base font-medium text-neutral-500 mt-2">
                Turn a short prompt into a usable form in seconds.
              </p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <img
              class="w-12 h-12 shrink-0"
              src="/img/pages/ai_form_builder/icon-customization.svg"
              alt=""
            >

            <div>
              <p class="text-md font-semibold text-neutral-900">
                Easy to shape and polish
              </p>
              <p class="text-base font-medium text-neutral-500 mt-2">
                Fine-tune fields, styling, logic, and structure without friction.
              </p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <img
              class="w-12 h-12 shrink-0"
              src="/img/pages/ai_form_builder/icon-browser.svg"
              alt=""
            >

            <div>
              <p class="text-md font-semibold text-neutral-900">
                No technical setup required
              </p>
              <p class="text-base font-medium text-neutral-500 mt-2">
                Anyone on your team can create polished forms without writing code.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- END HERO -->

    <!-- START HOW IT WORKS -->
    <section class="bg-white py-12 sm:py-16 lg:py-20 xl:py-24">
      <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="text-center max-w-3xl mx-auto">
          <h2 class="text-sm font-semibold brand-gradient-text-warm">
              How it works
          </h2>
          <p
            class="text-3xl mt-4 sm:text-4xl lg:text-5xl font-semibold text-neutral-900 tracking-tight lg:leading-tight"
          >
            From prompt to polished form
          </p>
          <p
            class="text-neutral-500 text-base leading-7 sm:text-lg sm:leading-8 font-medium mt-4"
          >
            Start with a short brief, then refine the draft until it feels ready to publish.
          </p>
        </div>

        <div
          class="space-y-16 text-center lg:text-left sm:max-w-md sm:mx-auto lg:max-w-none lg:space-y-20 xl:space-y-24 mt-8 sm:mt-12 lg:mt-16"
        >
          <div
            class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 lg:gap-12"
          >
            <img
              class="w-full lg:flex-1 bg-neutral-300 lg:shrink-0 rounded-2xl ring ring-neutral-200 shadow-xs"
              src="/img/pages/ai_form_builder/step-1.svg"
              alt=""
            >

            <div
              class="w-16 h-16 rounded-full brand-gradient-warm hidden xl:inline-flex items-center justify-center text-white text-2xl font-semibold leading-none"
            >
              1
            </div>

            <div class="lg:flex-1 lg:shrink-0">
              <img
                class="w-auto h-16 hidden lg:block"
                src="/img/pages/ai_form_builder/icon-create.svg"
                alt=""
              >

              <h3
                class="text-2xl sm:text-3xl lg:text-4xl font-semibold text-neutral-900 lg:mt-8"
              >
                Start with a clear prompt
              </h3>
              <p
                class="text-base font-medium leading-7 sm:text-lg sm:leading-8 text-neutral-500 mt-4"
              >
                Tell SharaForms what you want to collect, who the form is for,
                and what the flow should do. The builder gives you a strong
                starting point instead of a blank canvas.
              </p>
            </div>
          </div>

          <div
            class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 lg:gap-12"
          >
            <img
                              class="w-full lg:flex-1 bg-neutral-300 lg:shrink-0 rounded-2xl ring ring-neutral-200 shadow-xs lg:order-3"
              src="/img/pages/ai_form_builder/step-2.svg"
              alt=""
            >
            <div
              class="w-16 h-16 lg:order-2 rounded-full brand-gradient-warm hidden xl:inline-flex items-center justify-center text-white text-2xl font-semibold leading-none"
            >
              2
            </div>

            <div class="lg:flex-1 lg:shrink-0 lg:order-1">
              <img
                class="w-auto h-16 hidden lg:block"
                src="/img/pages/ai_form_builder/icon-customization.svg"
                alt=""
              >

              <h3
                class="text-2xl sm:text-3xl lg:text-4xl font-semibold text-neutral-900 lg:mt-8"
              >
                Refine the draft to match your brand
              </h3>
              <p
                class="text-base font-medium leading-7 sm:text-lg sm:leading-8 text-neutral-500 mt-4"
              >
                Adjust layout, copy, logic, and styling until the form feels
                on-brand and ready for real traffic.
              </p>
            </div>
          </div>

          <div
            class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 lg:gap-12"
          >
            <img
              class="w-full lg:flex-1 bg-neutral-300 lg:shrink-0 rounded-2xl ring ring-neutral-200 shadow-xs"
              src="/img/pages/ai_form_builder/step-3.svg"
              alt=""
            >
            <div
              class="w-16 h-16 rounded-full brand-gradient-warm hidden xl:inline-flex items-center justify-center text-white text-2xl font-semibold leading-none"
            >
              3
            </div>

            <div class="lg:flex-1 lg:shrink-0">
              <img
                class="w-auto h-16 hidden lg:block"
                src="/img/pages/ai_form_builder/icon-share.svg"
                alt=""
              >

              <h3
                class="text-2xl sm:text-3xl lg:text-4xl font-semibold text-neutral-900 lg:mt-8"
              >
                Publish anywhere in minutes
              </h3>
              <p
                class="text-base font-medium leading-7 sm:text-lg sm:leading-8 text-neutral-500 mt-4"
              >
                Share with a hosted link, embed on your site, or lock access
                down with the right privacy settings for the audience you want.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- END HOW IT WORKS -->

    <!-- START EXAMPLES -->
    <!--    <section class="bg-neutral-50 py-12 sm:py-16 lg:py-20 xl:py-24">-->
    <!--        <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">-->
    <!--          <div class="text-center max-w-3xl mx-auto">-->
    <!--            <h2 class="text-sm font-semibold text-blue-600">-->
    <!--              Examples-->
    <!--            </h2>-->
    <!--            <p-->
    <!--              class="text-3xl mt-4 sm:text-4xl lg:text-5xl font-semibold text-neutral-900 tracking-tight lg:leading-tight">-->
    <!--              What our user has created-->
    <!--            </p>-->
    <!--            <p class="text-neutral-500 text-base leading-7 sm:text-lg sm:leading-8 font-medium mt-4">-->
    <!--              Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis-->
    <!--              enim velit-->
    <!--              mollit.-->
    <!--            </p>-->
    <!--          </div>-->

    <!--          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8 sm:mt-12 lg:mt-16">-->
    <!--            <div-->
    <!--              class="bg-white overflow-hidden rounded-2xl ring-1 ring-neutral-200 shadow-sm hover:shadow-lg hover:-translate-y-2 transition-all duration-150">-->
    <!--              <img class="w-full" src="/img/pages/ai_form_builder/examples-placeholder.png" alt=""/>
-->
    <!--              <div class="px-4 py-5 sm:p-6">-->
    <!--                <h3 class="text-lg font-semibold text-neutral-900">-->
    <!--                  Example 1-->
    <!--                </h3>-->
    <!--                <p class="text-base font-medium text-neutral-500 mt-2 line-clamp-2">-->
    <!--                  Nulla Lorem mollit cupidatat irure. Laborum magna nulla duis ullamco cillum dolor-->
    <!--                </p>-->
    <!--              </div>-->
    <!--            </div>-->

    <!--            <div-->
    <!--              class="bg-white overflow-hidden rounded-2xl ring-1 ring-neutral-200 shadow-sm hover:shadow-lg hover:-translate-y-2 transition-all duration-150">-->
    <!--              <img class="w-full" src="/img/pages/ai_form_builder/examples-placeholder.png" alt=""/>
-->
    <!--              <div class="px-4 py-5 sm:p-6">-->
    <!--                <h3 class="text-lg font-semibold text-neutral-900">-->
    <!--                  Example 2-->
    <!--                </h3>-->
    <!--                <p class="text-base font-medium text-neutral-500 mt-2 line-clamp-2">-->
    <!--                  Nulla Lorem mollit cupidatat irure. Laborum magna nulla duis ullamco cillum dolor-->
    <!--                </p>-->
    <!--              </div>-->
    <!--            </div>-->

    <!--            <div-->
    <!--              class="bg-white overflow-hidden rounded-2xl ring-1 ring-neutral-200 shadow-sm hover:shadow-lg hover:-translate-y-2 transition-all duration-150">-->
    <!--              <img class="w-full" src="/img/pages/ai_form_builder/examples-placeholder.png" alt=""/>
-->
    <!--              <div class="px-4 py-5 sm:p-6">-->
    <!--                <h3 class="text-lg font-semibold text-neutral-900">-->
    <!--                  Example 3-->
    <!--                </h3>-->
    <!--                <p class="text-base font-medium text-neutral-500 mt-2 line-clamp-2">-->
    <!--                  Nulla Lorem mollit cupidatat irure. Laborum magna nulla duis ullamco cillum dolor-->
    <!--                </p>-->
    <!--              </div>-->
    <!--            </div>-->

    <!--            <div-->
    <!--              class="bg-white overflow-hidden rounded-2xl ring-1 ring-neutral-200 shadow-sm hover:shadow-lg hover:-translate-y-2 transition-all duration-150">-->
    <!--              <img class="w-full" src="/img/pages/ai_form_builder/examples-placeholder.png" alt=""/>
-->
    <!--              <div class="px-4 py-5 sm:p-6">-->
    <!--                <h3 class="text-lg font-semibold text-neutral-900">-->
    <!--                  Example 4-->
    <!--                </h3>-->
    <!--                <p class="text-base font-medium text-neutral-500 mt-2 line-clamp-2">-->
    <!--                  Nulla Lorem mollit cupidatat irure. Laborum magna nulla duis ullamco cillum dolor-->
    <!--                </p>-->
    <!--              </div>-->
    <!--            </div>-->
    <!--          </div>-->

    <!--&lt;!&ndash;          <hr class="mt-12 border-neutral-200 sm:mt-16">&ndash;&gt;-->

    <!--&lt;!&ndash;          <div class="max-w-2xl mx-auto mt-12 text-center sm:mt-16">&ndash;&gt;-->
    <!--&lt;!&ndash;            <h4 class="text-2xl font-semibold tracking-tight text-neutral-900 sm:text-3xl lg:text-4xl">&ndash;&gt;-->
    <!--&lt;!&ndash;              Ready to level-up?&ndash;&gt;-->
    <!--&lt;!&ndash;            </h4>&ndash;&gt;-->
    <!--&lt;!&ndash;            <p class="mt-4 text-base leading-7 sm:text-xl sm:leading-9 font-medium text-neutral-500">&ndash;&gt;-->
    <!--&lt;!&ndash;              Save time and effortlessly create forms with SharaForms&ndash;&gt;-->
    <!--&lt;!&ndash;            </p>&ndash;&gt;-->

    <!--&lt;!&ndash;            <div class="mt-8 flex justify-center">&ndash;&gt;-->
    <!--&lt;!&ndash;              <UButton v-if="!authenticated" class="mr-1" :to="{ name: 'forms-create-guest' }" :arrow="true">&ndash;&gt;-->
    <!--&lt;!&ndash;                Get started for free&ndash;&gt;-->
    <!--&lt;!&ndash;              </UButton>&ndash;&gt;-->
    <!--&lt;!&ndash;              <UButton v-else class="mr-1" :to="{ name: 'forms-create' }" :arrow="true">&ndash;&gt;-->
    <!--&lt;!&ndash;                Get started for free&ndash;&gt;-->
    <!--&lt;!&ndash;              </UButton>&ndash;&gt;-->
    <!--&lt;!&ndash;            </div>&ndash;&gt;-->

    <!--&lt;!&ndash;            <ul&ndash;&gt;-->
    <!--&lt;!&ndash;              class="flex mt-8 sm:mt-12 text-sm font-medium text-neutral-900 items-center justify-center flex-wrap gap-x-6 gap-y-4">&ndash;&gt;-->
    <!--&lt;!&ndash;              <li class="flex items-center gap-2">&ndash;&gt;-->
    <!--&lt;!&ndash;                <svg aria-hidden="true" class="h-5 w-5 text-neutral-400 shrink-0" xmlns="http://www.w3.org/2000/svg"&ndash;&gt;-->
    <!--&lt;!&ndash;                  viewBox="0 0 20 20" fill="currentColor">&ndash;&gt;-->
    <!--&lt;!&ndash;                  <path fill-rule="evenodd"&ndash;&gt;-->
    <!--&lt;!&ndash;                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"&ndash;&gt;-->
    <!--&lt;!&ndash;                    clip-rule="evenodd" />&ndash;&gt;-->
    <!--&lt;!&ndash;                </svg>&ndash;&gt;-->
    <!--&lt;!&ndash;                No design skills required&ndash;&gt;-->
    <!--&lt;!&ndash;              </li>&ndash;&gt;-->

    <!--&lt;!&ndash;              <li class="flex items-center gap-2">&ndash;&gt;-->
    <!--&lt;!&ndash;                <svg aria-hidden="true" class="h-5 w-5 text-neutral-400 shrink-0" xmlns="http://www.w3.org/2000/svg"&ndash;&gt;-->
    <!--&lt;!&ndash;                  viewBox="0 0 20 20" fill="currentColor">&ndash;&gt;-->
    <!--&lt;!&ndash;                  <path fill-rule="evenodd"&ndash;&gt;-->
    <!--&lt;!&ndash;                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"&ndash;&gt;-->
    <!--&lt;!&ndash;                    clip-rule="evenodd" />&ndash;&gt;-->
    <!--&lt;!&ndash;                </svg>&ndash;&gt;-->
    <!--&lt;!&ndash;                Setup in minutes&ndash;&gt;-->
    <!--&lt;!&ndash;              </li>&ndash;&gt;-->

    <!--&lt;!&ndash;              <li class="flex items-center gap-2">&ndash;&gt;-->
    <!--&lt;!&ndash;                <svg aria-hidden="true" class="h-5 w-5 text-neutral-400 shrink-0" xmlns="http://www.w3.org/2000/svg"&ndash;&gt;-->
    <!--&lt;!&ndash;                  viewBox="0 0 20 20" fill="currentColor">&ndash;&gt;-->
    <!--&lt;!&ndash;                  <path fill-rule="evenodd"&ndash;&gt;-->
    <!--&lt;!&ndash;                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"&ndash;&gt;-->
    <!--&lt;!&ndash;                    clip-rule="evenodd" />&ndash;&gt;-->
    <!--&lt;!&ndash;                </svg>&ndash;&gt;-->
    <!--&lt;!&ndash;                It's free&ndash;&gt;-->
    <!--&lt;!&ndash;              </li>&ndash;&gt;-->
    <!--&lt;!&ndash;            </ul>&ndash;&gt;-->
    <!--&lt;!&ndash;          </div>&ndash;&gt;-->
    <!--        </div>-->
    <!--    </section>-->
    <!-- END EXAMPLES -->

    <!-- START TESTIMONIALS -->
    <!--    <section class="bg-white py-12 sm:py-16 lg:py-20 xl:py-24">-->
    <!--      <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">-->
    <!--        <div class="text-center max-w-3xl mx-auto">-->
    <!--          <h2 class="text-sm font-semibold text-blue-600">-->
    <!--            Customer Testimonials-->
    <!--          </h2>-->
    <!--          <p-->
    <!--            class="text-3xl mt-4 sm:text-4xl lg:text-5xl font-semibold text-neutral-900 tracking-tight lg:leading-tight">-->
    <!--            See what people are saying-->
    <!--          </p>-->
    <!--          <p class="text-neutral-500 text-base leading-7 sm:text-lg sm:leading-8 font-medium mt-4">-->
    <!--            These are the stories of our customers who have joined us with great pleasure when using this crazy-->
    <!--            feature.-->
    <!--          </p>-->
    <!--        </div>-->
    <!--      </div>-->
    <!--    </section>-->
    <!-- END TESTIMONIALS -->

    <!-- START CTA -->
    <section
      class="bg-gradient-to-b from-neutral-100 to-white py-12 sm:py-16 lg:pt-20 xl:pt-24"
    >
      <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="flex items-center justify-center gap-8">
          <img
            class="w-auto h-12"
            src="/img/pages/ai_form_builder/icon-email-input.svg"
            alt=""
          >

          <img
            class="w-auto h-12"
            src="/img/pages/ai_form_builder/icon-radio-buttons.svg"
            alt=""
          >

          <img
            class="w-auto h-12"
            src="/img/pages/ai_form_builder/icon-textarea.svg"
            alt=""
          >
        </div>

        <div class="text-center max-w-3xl mx-auto mt-8 sm:mt-12">
          <h2
            class="text-3xl sm:text-4xl lg:text-5xl font-semibold text-neutral-900 tracking-tight lg:leading-tight"
          >
            Create polished forms and ship them fast
          </h2>
          <p
            class="mt-4 sm:mt-5 text-base leading-7 sm:text-xl sm:leading-9 font-medium text-neutral-500"
          >
            Go from rough idea to publishable form in minutes.
            <span class="brand-gradient-text-warm">No code required.</span>
          </p>

          <div class="mt-8 flex justify-center">
            <UButton
              class="mr-1"
              :to="{ name: authenticated ? 'forms-create' : 'forms-create-guest' }"
              trailing-icon="i-lucide-arrow-right"
              label="Try the AI builder free"
            />
          </div>

          <ul
            class="flex mt-8 sm:mt-12 text-sm font-medium text-neutral-900 items-center justify-center flex-wrap gap-x-6 gap-y-4"
          >
            <li class="flex items-center gap-2">
              <svg
                aria-hidden="true"
                class="h-5 w-5 text-neutral-400 shrink-0"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              No design skills required
            </li>

            <li class="flex items-center gap-2">
              <svg
                aria-hidden="true"
                class="h-5 w-5 text-neutral-400 shrink-0"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Setup in minutes
            </li>

            <li class="flex items-center gap-2">
              <svg
                aria-hidden="true"
                class="h-5 w-5 text-neutral-400 shrink-0"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
              Free plan available
            </li>
          </ul>
        </div>
      </div>
    </section>
    <!-- END CTA -->

    <open-form-footer class="dark:border-t border-t" />
  </div>
</template>

<script setup>
definePageMeta({
  middleware: ['root-redirect']
})
const config = useRuntimeConfig()

useOpnSeoMeta({
  title: "Free AI Form Builder for Calculators and Quotes",
  description:
    "Turn a prompt into a form that closes deals. SharaForms AI Builder drafts calculators, quote forms, and proposals with formulas and conditional logic.",
  ogImage: "/share-preview.jpg",
  keywords: "free ai form builder, ai form builder, forms that close deals, pricing calculator form, quote forms, unlimited forms, unlimited submissions",
  speakable: ["h1", ".marketing-page > section:first-child p"],
  breadcrumbs: [
    { name: "Home", item: "/" },
    { name: "AI Form Builder" },
  ],
})
defineRouteRules({
  swr: 3600,
})

const baseUrl = computed(() => {
  const url = config.public.appUrl
  return (url && url !== '/') ? url.replace(/\/+$/, '') : ''
})

const aiVideoSchema = computed(() => {
  const siteUrl = baseUrl.value
  if (!siteUrl) return null
  return {
    '@context': 'https://schema.org',
    '@type': 'VideoObject',
    '@id': `${siteUrl}/ai-form-builder#video`,
    name: 'SharaForms AI Form Builder Demo',
    description: 'See how SharaForms AI Builder turns a prompt into a production-ready form with built-in calculators, conditional logic, and dynamic fields.',
    thumbnailUrl: `${siteUrl}/share-preview.jpg`,
    contentUrl: `${siteUrl}/video/sharaforms-ai.mp4`,
    embedUrl: `${siteUrl}/ai-form-builder`,
    uploadDate: '2025-06-01',
    duration: 'PT1M30S',
    isFamilyFriendly: true,
    author: {
      '@type': 'Organization',
      name: 'SharaForms',
    },
    publisher: {
      '@type': 'Organization',
      name: 'SharaForms',
    },
  }
})

useHead({
  script: aiVideoSchema.value ? [
    {
      key: 'ai-video-schema',
      type: 'application/ld+json',
      textContent: JSON.stringify(aiVideoSchema.value),
    },
  ] : [],
})

const { isAuthenticated: authenticated } = useIsAuthenticated()
</script>

<style lang="scss" scoped>
.customer-logo-container {
  max-width: 130px;
  width: 100%;
}

.ticks {
  color: #2563eb;
}

@screen md {
  #macbook-video {
    position: absolute;
    max-width: 84.8% !important;
    right: 0px;
    top: 6.8%;
  }
}
</style>

<template>
  <div class="py-8">
    <section>
      <div class="mx-auto max-w-7xl isolate px-8 lg:px-12">
        <div
          class="relative px-4 py-8 overflow-hidden brand-surface-strong ring-white/10 ring-1 sm:shadow-lg isolate sm:rounded-2xl sm:px-16 md:pt-20 lg:flex lg:gap-x-20 lg:px-16 lg:py-0 sm:shadow-neutral-600/10"
        >
          <div class="pointer-events-none absolute inset-0">
            <img
              class="pointer-events-none object-cover object-top w-full h-full"
              src="/img/pages/ai_form_builder/background-pattern-ai.svg"
              alt=""
            >
          </div>

          <div
            class="relative max-w-md mx-auto text-center xl:max-w-lg lg:mx-0 lg:flex-auto lg:py-16 lg:text-left"
          >
            <span
              class="bg-white/10 text-xs font-semibold inline-flex items-center shadow-xs ring-white/20 ring text-white px-2.5 py-1.5 rounded-full backdrop-blur-sm"
            >
              <UIcon
                name="i-lucide-wand-sparkles"
                class="w-4 h-4"
              />
              Introducing SharaForms AI
            </span>

            <h2
              class="mt-6 text-2xl font-semibold tracking-tight text-white sm:text-3xl lg:text-4xl"
            >
              Say goodbye to tedious form building with SharaForms's new
              <span
                class="brand-gradient-text-warm lg:block"
              >AI-powered feature!</span>
            </h2>
            <p
              class="mt-4 text-base font-medium leading-7 text-white/72 sm:text-lg sm:leading-8"
            >
              Easily generate a fully working form in seconds with just a simple
              description.
            </p>

            <div class="flex justify-center lg:justify-start mt-4">
              <TrackClick name="welcome_ai_builder_click">
                <UButton
                  class="brand-button-primary block"
                  :to="{ name: 'forms-create-guest' }"
                  :trailing="true"
                  size="lg"
                  label="Try our AI form builder"
                />
              </TrackClick>
            </div>

            <ul
              class="flex flex-wrap items-center justify-center mt-4 gap-4 text-sm font-medium text-white/85 lg:justify-start"
            >
              <li class="flex items-center gap-2">
                <svg
                  aria-hidden="true"
                  class="w-5 h-5 text-white/60 shrink-0"
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
                Create form in minutes
              </li>

              <li class="flex items-center gap-2">
                <svg
                  aria-hidden="true"
                  class="w-5 h-5 text-white/60 shrink-0"
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
                Customizations
              </li>

              <li class="flex items-center gap-2">
                <svg
                  aria-hidden="true"
                  class="w-5 h-5 text-white/60 shrink-0"
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
                No-coding required
              </li>
            </ul>
          </div>

          <div
            class="relative hidden lg:block w-full px-6 mx-auto mt-8 lg:px-0 sm:mt-12 lg:bottom-0 lg:absolute lg:-right-16 xl:right-0 lg:max-w-lg"
          >
            <img
              class="rounded-t-2xl ring-1 ring-white/10 lg:rounded-tr-none w-full"
              src="/img/pages/ai_form_builder/ai-feature-illustration.svg"
              alt="App screenshot"
            >
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import TrackClick from '~/components/global/TrackClick.vue'
</script>

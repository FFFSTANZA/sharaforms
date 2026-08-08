<template>
  <div class="marketing-page">
    <section>
      <div class="relative">
        <div class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 relative z-2">
          <div class="max-w-3xl mx-auto text-center">
            <div class="flex items-center justify-center gap-4">
              <div
                class="brand-gradient h-16 w-16 rounded-2xl shadow-sm flex items-center justify-center"
              >
                <img
                  src="/img/sharaforms-logo.png"
                  alt="SharaForms"
                  class="h-10 w-10 filter-[sepia(1)_brightness(2)_saturate(0)]"
                />
              </div>
              <div
                class="brand-text-soft text-xs leading-4 font-medium tracking-[4%] px-2 py-1"
              >
                VS
              </div>
              <div
                class="h-16 w-16 rounded-2xl flex items-center justify-center"
              >
                <Icon :name="competitorIcon" :class="competitorIconClass" class="h-16 w-16" />
              </div>
            </div>

            <h1
              class="brand-text-strong mt-12 sm:mt-16 text-4xl sm:text-[56px] sm:leading-16 tracking-[-1%] font-semibold"
            >
              {{ heroTitle }}
            </h1>
            <p
              class="brand-text-muted my-8 text-lg sm:text-xl leading-7 tracking-[-1.5%] sm:leading-8 font-normal"
            >
              <slot name="hero-subtitle">
                Build polished forms with <b>unlimited responses, stronger control, and room to grow</b>
                — without the usual upgrade pressure.
              </slot>
            </p>

            <div
              class="flex flex-col sm:flex-row items-center justify-center gap-4"
            >
              <UButton
                size="lg"
                :to="{
                  name: authenticated ? 'forms-create' : 'forms-create-guest',
                }"
                trailing-icon="i-lucide-arrow-up-right"
                label="Start building free"
                class="brand-button-primary w-fit pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
              />
              <UButton
                v-if="resolvedHeroSecondaryCtaTo"
                size="lg"
                variant="outline"
                :to="resolvedHeroSecondaryCtaTo"
                :label="resolvedHeroSecondaryCtaLabel"
                class="brand-button-secondary w-fit px-4 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
              />
            </div>
          </div>
        </div>
        <div
          class="brand-section-wash w-full h-full absolute inset-0"
        ></div>
      </div>
      <div class="relative">
        <div class="pt-1 pb-14 sm:pb-24 px-4 sm:px-8 lg:px-12 relative z-2">
          <div class="max-w-3xl mx-auto text-center">
            <h2
              class="brand-text-strong text-2xl leading-8 font-semibold tracking-[-0.5%]"
            >
              Try both experiences live.
            </h2>
            <p
              class="brand-text-muted mt-4 text-base tracking-[-1.1%] font-medium leading-8"
            >
              Compare SharaForms and {{ competitorName }} inside the same live
              form flow —
              <br class="hidden sm:block" />
              no screenshots, no hand-picked demos.
            </p>
          </div>
          <LiveDemo
            class="mt-12"
            variant="comparison"
            :competitor-name="competitorName"
            :import-source="heroImportSource"
          />
        </div>
        <div
          class="brand-section-wash w-full h-full absolute inset-0"
        ></div>
      </div>
    </section>

    <section class="px-4 sm:px-8 lg:px-12 pb-14 sm:pb-20">
      <div class="max-w-3xl mx-auto">
        <div class="comparison-bottom-line brand-surface rounded-2xl px-6 sm:px-8 py-6 sm:py-7 border brand-divider">
          <h2 class="brand-text-strong text-lg leading-6 font-semibold tracking-[-0.5%]">
            Bottom line
          </h2>
          <p class="brand-text-muted mt-3 text-base sm:text-lg leading-7 sm:leading-8 tracking-[-1.1%] font-normal">
            {{ bottomLine }}
          </p>
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <h2
            class="brand-text-strong text-4xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%]"
          >
            {{ resolvedPlanComparisonTitleLines[0] }}
            <br v-if="resolvedPlanComparisonTitleLines.length > 1" />
            {{ resolvedPlanComparisonTitleLines[1] }}
          </h2>
          <p
            class="brand-text-muted mt-4 text-base tracking-[-1.1%] font-medium leading-8"
          >
            {{ resolvedPlanComparisonSubtitle }}
          </p>
        </div>

        <div class="mt-12 sm:mt-16">
          <div class="grid gap-4 md:hidden">
            <div
              v-for="row in freePlanComparison"
              :key="row.label"
              class="brand-surface overflow-hidden rounded-2xl"
            >
              <div class="brand-section-wash brand-text-strong border-b brand-divider px-5 py-4 text-sm font-semibold leading-5">
                {{ row.label }}
              </div>
              <div class="divide-y divide-gray-100">
                <div class="flex items-start justify-between gap-4 px-5 py-4">
                  <div class="flex min-w-0 items-center gap-2">
                    <img src="/img/sharaforms-logo.png" alt="SharaForms" class="h-6 w-6 shrink-0" />
                    <span class="brand-text-strong text-sm font-semibold leading-5">
                      SharaForms
                    </span>
                  </div>
                  <div class="brand-text-muted flex justify-end text-right text-sm font-medium leading-5">
                    <UIcon
                      v-if="isYesCell(row.cells[0])"
                      name="i-lucide-check"
                      class="h-5 w-5 text-green-500"
                    />
                    <UIcon
                      v-else-if="isNoCell(row.cells[0])"
                      name="i-lucide-x"
                      class="h-5 w-5 text-red-500"
                    />
                    <span v-else>
                      {{ row.cells[0] }}
                    </span>
                  </div>
                </div>
                <div class="flex items-start justify-between gap-4 px-5 py-4">
                  <div class="flex min-w-0 items-center gap-2">
                    <Icon
                      :name="competitorIcon"
                      :class="competitorIconClass"
                      class="h-6 w-6 shrink-0"
                    />
                    <span class="brand-text-strong text-sm font-semibold leading-5">
                      {{ competitorName }}
                    </span>
                  </div>
                  <div class="brand-text-muted flex justify-end text-right text-sm font-medium leading-5">
                    <UIcon
                      v-if="isYesCell(row.cells[1])"
                      name="i-lucide-check"
                      class="h-5 w-5 text-green-500"
                    />
                    <UIcon
                      v-else-if="isNoCell(row.cells[1])"
                      name="i-lucide-x"
                      class="h-5 w-5 text-red-500"
                    />
                    <span v-else>
                      {{ row.cells[1] }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="hidden grid-cols-12 items-start md:grid">
            <div class="col-span-12 md:col-span-4 pb-8">
                <div class="hidden h-32 md:block" />
              <div
                v-for="(row, idx) in freePlanComparison"
                :key="row.label"
                class="brand-text-strong px-8 py-4 text-base leading-8 tracking-[-1.1%] font-medium rounded-l-[12px]"
                :class="idx % 2 === 0 ? 'brand-section-wash' : 'bg-white/72'"
              >
                {{ row.label }}
              </div>
            </div>

            <div class="col-span-12 md:col-span-4">
              <div
                class="brand-surface rounded-[24px] pb-8 overflow-hidden"
              >
                <div class="flex h-32 items-center justify-center gap-0 px-6">
                  <img src="/img/sharaforms-logo.png" alt="SharaForms logo" class="h-14 w-14 shrink-0" />
                  <div class="brand-text-strong flex flex-col items-start gap-0 text-lg font-semibold">
                    <BrandWordmark class="text-[1.72rem]" />
                    <span
                      class="brand-text-muted -mt-2 text-base font-medium leading-7 tracking-[-1.1%]"
                      >({{ sharaformsPlanLabel }})</span
                    >
                  </div>
                </div>
                <div
                  v-for="(row, idx) in freePlanComparison"
                  :key="row.label"
                  class="brand-text-strong px-6 py-4 text-base leading-8 tracking-[-1.1%] font-medium"
                  :class="idx % 2 === 0 ? 'brand-section-wash' : 'bg-white/72'"
                >
                  <div class="flex items-center justify-start gap-2">
                    <UIcon
                      v-if="isYesCell(row.cells[0])"
                      name="i-lucide-check"
                      class="h-6 w-6 text-green-500"
                    />
                    <UIcon
                      v-else-if="isNoCell(row.cells[0])"
                      name="i-lucide-x"
                      class="h-6 w-6 text-red-500"
                    />
                    <span v-else>
                      {{ row.cells[0] }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-span-12 md:col-span-4 pb-8">
              <div class="h-32 flex items-center justify-center gap-2">
                <Icon :name="competitorIcon" :class="competitorIconClass" class="h-8 w-8" />
                <div class="brand-text-strong text-lg font-semibold">
                  {{ competitorName }}
                  <span
                    class="brand-text-muted ml-3 text-base leading-8 tracking-[-1.1%] font-medium"
                    >({{ competitorPlanLabel }})</span
                  >
                </div>
              </div>
              <div
                v-for="(row, idx) in freePlanComparison"
                :key="row.label"
                class="brand-text-muted px-6 py-4 text-base leading-8 tracking-[-1.1%] font-medium rounded-r-[12px]"
                :class="idx % 2 === 0 ? 'brand-section-wash' : 'bg-white/72'"
              >
                <div class="flex items-center justify-start gap-2">
                  <UIcon
                    v-if="isYesCell(row.cells[1])"
                    name="i-lucide-check"
                    class="h-6 w-6 text-green-500"
                  />
                  <UIcon
                    v-else-if="isNoCell(row.cells[1])"
                    name="i-lucide-x"
                    class="h-6 w-6 text-red-500"
                  />
                  <span v-else>
                    {{ row.cells[1] }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-12 sm:mt-16 flex justify-center items-center">
          <UButton
            size="lg"
            :to="{
              name: authenticated ? 'forms-create' : 'forms-create-guest',
            }"
            trailing-icon="i-lucide-arrow-up-right"
            label="Start building free"
            class="brand-button-primary pl-4 pr-3.5 py-2.5 rounded-[12px] text-base leading-7 tracking-[-1.1%] font-medium"
          />
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <h2
            class="text-4xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%] text-gray-950"
          >
            {{ switchSectionTitle }}
          </h2>
          <p
            class="mt-4 text-base tracking-[-1.1%] font-medium leading-8 text-gray-600"
          >
            {{ competitorName }} is beautiful — but SharaForms is powerful, open,
            and free.
          </p>
        </div>

        <div class="mt-12 sm:mt-16 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="item in switchReasons"
            :key="item.title"
            class="rounded-3xl border border-gray-200 bg-gray-50 p-8"
          >
            <div
              class="rounded-[20px] brand-gradient-warm h-16 w-16 flex items-center justify-center shadow-sm"
            >
              <UIcon :name="item.icon" class="h-8 w-8 text-white" />
            </div>

            <div class="mt-8 text-xl leading-7 font-semibold text-gray-950">
              {{ item.title }}
            </div>
            <div
              class="mt-4 text-base font-medium leading-7 tracking-[-1.1%] text-gray-600"
            >
              {{ item.description }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <h2
            class="text-4xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%] text-gray-950"
          >
            Feature-by-Feature
            <br />
            Comparison
          </h2>
          <p
            class="mt-4 text-base tracking-[-1.1%] font-medium leading-8 text-gray-600"
          >
            {{ featureSectionSubtitle }}
          </p>
        </div>

        <div class="mt-12 sm:mt-16">
          <div class="grid gap-4 md:hidden">
            <div
              v-for="row in featureComparison"
              :key="row.label"
              class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
            >
              <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 text-sm font-semibold leading-5 text-gray-950">
                {{ row.label }}
              </div>
              <div class="divide-y divide-gray-100">
                <div class="flex items-start justify-between gap-4 px-5 py-4">
                  <div class="flex min-w-0 items-center gap-2">
                    <img src="/img/sharaforms-logo.png" alt="SharaForms" class="h-6 w-6 shrink-0" />
                    <span class="text-sm font-semibold leading-5 text-gray-950">
                      SharaForms
                    </span>
                  </div>
                  <div class="flex justify-end text-right text-sm font-medium leading-5 text-gray-700">
                    <UIcon
                      v-if="isYesCell(row.cells[0])"
                      name="i-lucide-check"
                      class="h-5 w-5 text-green-500"
                    />
                    <UIcon
                      v-else-if="isNoCell(row.cells[0])"
                      name="i-lucide-x"
                      class="h-5 w-5 text-red-500"
                    />
                    <span v-else>
                      {{ row.cells[0] }}
                    </span>
                  </div>
                </div>
                <div class="flex items-start justify-between gap-4 px-5 py-4">
                  <div class="flex min-w-0 items-center gap-2">
                    <Icon
                      :name="competitorIcon"
                      :class="competitorIconClass"
                      class="h-6 w-6 shrink-0"
                    />
                    <span class="text-sm font-semibold leading-5 text-gray-950">
                      {{ competitorName }}
                    </span>
                  </div>
                  <div class="flex justify-end text-right text-sm font-medium leading-5 text-gray-700">
                    <UIcon
                      v-if="isYesCell(row.cells[1])"
                      name="i-lucide-check"
                      class="h-5 w-5 text-green-500"
                    />
                    <UIcon
                      v-else-if="isNoCell(row.cells[1])"
                      name="i-lucide-x"
                      class="h-5 w-5 text-red-500"
                    />
                    <span v-else>
                      {{ row.cells[1] }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            class="hidden rounded-[24px] border border-gray-200 overflow-hidden bg-white md:block"
          >
            <table class="w-full border-collapse">
              <thead>
                <tr class="bg-white">
                  <th
                    class="w-[40%] sm:w-[45%] px-4 py-5 text-left text-sm font-semibold text-gray-700 border-b border-gray-200"
                  />
                  <th
                      class="w-[30%] sm:w-[27.5%] px-4 py-7 border-b border-l border-gray-200 text-center"
                  >
                    <div
                      class="flex items-center justify-center gap-0"
                    >
                      <img src="/img/sharaforms-logo.png" alt="SharaForms logo" class="h-14 w-14 shrink-0" />
                      <BrandWordmark class="text-[1.72rem]" />
                    </div>
                  </th>
                  <th
                    class="w-[30%] sm:w-[27.5%] px-2 py-5 border-b border-l border-gray-200 text-center"
                  >
                    <div class="flex items-center justify-center gap-3">
                      <Icon
                        :name="competitorIcon"
                        :class="competitorIconClass"
                        class="h-8 w-8"
                      />
                      <span class="text-lg font-semibold text-gray-950">{{
                        competitorName
                      }}</span>
                    </div>
                  </th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="row in featureComparison"
                  :key="row.label"
                  class="border-b border-gray-200 last:border-b-0"
                >
                  <td
                    class="px-4 sm:px-8 py-4 text-sm sm:text-base sm:leading-7 trackig-[-1.1%] font-medium text-gray-950"
                  >
                    {{ row.label }}
                  </td>

                  <td class="px-8 py-4 text-center border-l border-gray-200">
                    <div class="flex items-center justify-center gap-2">
                      <UIcon
                        v-if="isYesCell(row.cells[0])"
                        name="i-lucide-check"
                        class="h-6 w-6 text-green-500"
                      />
                      <UIcon
                        v-else-if="isNoCell(row.cells[0])"
                        name="i-lucide-x"
                        class="h-6 w-6 text-red-500"
                      />
                      <span
                        v-else
                        class="text-sm sm:text-base sm:leading-7 trackig-[-1.1%] font-medium text-gray-600"
                      >
                        {{ row.cells[0] }}
                      </span>
                    </div>
                  </td>

                  <td class="px-8 py-4 text-center border-l border-neutral-200">
                    <div class="flex items-center justify-center gap-2">
                      <UIcon
                        v-if="isYesCell(row.cells[1])"
                        name="i-lucide-check"
                        class="h-6 w-6 text-green-500"
                      />
                      <UIcon
                        v-else-if="isNoCell(row.cells[1])"
                        name="i-lucide-x"
                        class="h-6 w-6 text-red-500"
                      />
                      <span
                        v-else
                        class="text-sm sm:text-base sm:leading-7 trackig-[-1.1%] font-medium text-gray-600"
                      >
                        {{ row.cells[1] }}
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <section v-if="displayCompetitorPrice" class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <h2
            class="text-4xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%] text-gray-950"
          >
            See the real cost as your
            <br />
            usage grows
          </h2>
          <p
            class="mt-4 text-base tracking-[-1.1%] font-medium leading-8 text-gray-600"
          >
            Move the slider to estimate your monthly submissions and see how
            pricing compares.
          </p>
        </div>

        <div class="mt-12 sm:mt-16 max-w-3xl mx-auto">
          <div class="text-center text-xl leading-7 font-medium text-gray-950">
            Expected submissions per month
          </div>

          <div class="mt-6">
            <input
              v-model.number="submissionsIndex"
              type="range"
              :min="0"
              :max="submissionOptions.length - 1"
              :step="1"
              class="w-full h-2 rounded-full appearance-none cursor-pointer"
              :style="sliderStyle"
            />
          </div>

          <div
            class="mt-2 grid grid-cols-11 gap-2 text-center text-xs sm:text-sm leading-5 tracking-[-0.6%] font-medium text-gray-600"
          >
            <div
              v-for="(val, idx) in submissionOptions"
              :key="val"
              class="flex flex-col items-center"
            >
              <div class="h-3 w-px bg-gray-300" />
              <div class="mt-2 whitespace-nowrap">
                {{ formatSubmissionsLabel(val, idx) }}
              </div>
            </div>
          </div>
        </div>

        <div class="mt-10 sm:mt-12 grid gap-6 sm:grid-cols-2 max-w-xl mx-auto">
          <div
            class="rounded-3xl border border-gray-200 bg-gray-50 p-8 text-center"
          >
            <div class="text-2xl leading-8 font-medium text-gray-950">
              SharaForms
            </div>
            <div
              class="mt-4 text-4xl sm:text-[56px] sm:leading-16 font-medium tracking-[-1%] text-gray-950"
            >
              {{ formatCurrency(sharaformsPrice) }}
            </div>
          </div>

          <div
            class="rounded-3xl border border-gray-200 bg-gray-50 p-8 text-center"
          >
            <div class="text-2xl leading-8 font-medium text-gray-950">
              {{ competitorName }}
            </div>
            <div
              class="mt-4 text-4xl sm:text-[56px] sm:leading-16 font-medium tracking-[-1%] text-gray-950"
            >
              {{ formatCurrency(competitorPrice) }}
            </div>
          </div>
        </div>

        <div
          class="mt-6 text-center text-xl font-medium leading-7 text-gray-950"
        >
          You save {{ formatCurrency(monthlySavings) }} per month with SharaForms
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <h2
            class="text-4xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%] text-gray-950"
          >
            Frequently asked
            <br />
            questions
          </h2>
          <p
            class="mt-4 text-base tracking-[-1.1%] font-medium leading-8 text-gray-600"
          >
            Quick answers about switching to SharaForms from
            {{ competitorName }}.
          </p>
        </div>

        <div class="mt-12 sm:mt-16 max-w-3xl mx-auto divide-y divide-gray-200">
          <div
            v-for="(faq, index) in resolvedFaqs"
            :key="faq.question"
            class="py-6"
          >
            <h3 class="text-lg leading-7 font-semibold text-gray-950">
              {{ faq.question }}
            </h3>
            <p
              class="mt-3 text-base leading-7 tracking-[-1.1%] font-medium text-gray-600"
            >
              {{ faq.answer }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <h2
            class="text-4xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%] text-gray-950"
          >
            Integrations that
            <br />
            work for you
          </h2>
          <p
            class="mt-4 text-base tracking-[-1.1%] font-medium leading-8 text-gray-600"
          >
            Connect SharaForms to your favorite tools in seconds.
          </p>
          <p
            class="mt-4 text-base tracking-[-1.1%] font-medium leading-8 text-gray-600"
          >
            Sync form data automatically with Email, Google Sheets, Slack,
            Zapier, Telegram, and more
            <br class="hidden sm:block" />
            via webhooks and automation platforms.
          </p>
        </div>

        <div class="mt-12 sm:mt-14 max-w-md mx-auto">
          <div class="flex justify-center items-center gap-6">
            <div
              v-for="item in integrationLogosrow1"
              :key="item.name"
              :title="item.name"
              class="border flex items-center justify-center rounded-2xl h-18 sm:h-30 min-w-18 sm:min-w-30"
            >
              <UIcon
                :name="item.icon"
                class="h-12 sm:h-14 w-12 sm:w-14"
                :class="item.iconClass"
              />
            </div>
          </div>
          <div class="mt-6 flex justify-center items-center gap-6">
            <div
              v-for="item in integrationLogosrow2"
              :key="item.name"
              :title="item.name"
              class="border flex items-center justify-center rounded-2xl h-18 sm:h-30 min-w-18 sm:min-w-30"
            >
              <UIcon
                :name="item.icon"
                class="h-12 sm:h-14 w-12 sm:w-14"
                :class="item.iconClass"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <h2
            class="text-4xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%] text-gray-950"
          >
            Privacy-first and
            <br />
            open by design
          </h2>
          <p
            class="mt-4 text-base tracking-[-1.1%] font-medium leading-8 text-gray-600"
          >
            Built for teams, enterprises, and developers who need full control
            over their data.
          </p>
        </div>

        <div class="mt-12 sm:mt-16 grid gap-6 md:grid-cols-2">
          <div
            v-for="item in privacyFeatures"
            :key="item.title"
            class="rounded-3xl border border-gray-200 bg-gray-50 p-8 flex gap-6 items-start"
          >
            <div
              class="h-16 w-16 rounded-[20px] brand-gradient-warm shadow-sm flex items-center justify-center shrink-0"
            >
              <UIcon :name="item.icon" class="h-8 w-8 text-white" />
            </div>

            <div>
              <div class="text-xl leading-7 font-medium text-gray-950">
                {{ item.title }}
              </div>
              <div
                class="mt-3 text-base font-medium leading-7 tracking-[-1.1%] text-gray-600"
              >
                {{ item.description }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 bg-white">
      <Testimonials />
    </section>

    <section class="py-14 sm:py-28 px-4 sm:px-8 lg:px-12 bg-white">
      <div class="max-w-266 mx-auto">
        <div class="max-w-2xl mx-auto text-center">
          <h2
            class="text-4xl sm:text-5xl sm:leading-14 font-semibold tracking-[-1%] text-gray-950"
          >
            Compare SharaForms with
            <br />
            other form builders
          </h2>
          <p
            class="mt-4 text-base tracking-[-1.1%] font-medium leading-8 text-gray-600"
          >
            See how SharaForms stacks up against every major form builder —
            free plans, pricing, and features side by side.
          </p>
        </div>

        <div class="mt-12 sm:mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 max-w-5xl mx-auto">
          <NuxtLink
            to="/comparisons"
            class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-6 py-5 hover:border-blue-200 hover:bg-blue-50/40 transition"
          >
            <div>
              <div class="text-base leading-6 font-semibold text-gray-950">
                All comparisons
              </div>
              <div class="mt-1 text-sm leading-5 font-medium text-gray-500">
                SharaForms vs every form builder
              </div>
            </div>
            <UIcon name="i-lucide-arrow-right" class="h-5 w-5 text-gray-400 group-hover:text-blue-600" />
          </NuxtLink>

          <NuxtLink
            v-for="comparison in otherComparisons"
            :key="comparison.slug"
            :to="`/sharaforms-vs-${comparison.slug}`"
            class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-6 py-5 hover:border-blue-200 hover:bg-blue-50/40 transition"
          >
            <div>
              <div class="text-base leading-6 font-semibold text-gray-950">
                vs {{ comparison.label }}
              </div>
              <div class="mt-1 text-sm leading-5 font-medium text-gray-500">
                Free SharaForms alternative
              </div>
            </div>
            <UIcon name="i-lucide-arrow-right" class="h-5 w-5 text-gray-400 group-hover:text-blue-600" />
          </NuxtLink>
        </div>
      </div>
    </section>

    <OpenFormFooter />
  </div>
</template>

<script setup>
import LiveDemo from "~/components/pages/welcome/LiveDemo.vue"
import Testimonials from "~/components/pages/welcome/Testimonials.vue"
import { useIsAuthenticated } from "~/composables/useAuthFlow"

const props = defineProps({
  competitorName: {
    type: String,
    required: true,
  },
  competitorIcon: {
    type: String,
    required: true,
  },
  competitorIconClass: {
    type: String,
    default: null,
  },
  heroTitle: {
    type: String,
    required: true,
  },
  /** Overrides default: `Have a {competitor} URL? Import it now` */
  heroSecondaryCtaLabel: {
    type: String,
    default: null,
  },
  heroSecondaryCtaTo: {
    type: [String, Object],
    default: null,
  },
  heroImportSource: {
    type: String,
    default: null,
  },
  sharaformsPlanLabel: {
    type: String,
    default: "Free",
  },
  competitorPlanLabel: {
    type: String,
    default: "Free",
  },
  planComparisonTitle: {
    type: String,
    default: null,
  },
  planComparisonSubtitle: {
    type: String,
    default: null,
  },
  featureSectionSubtitle: {
    type: String,
    default:
      "SharaForms gives you the same polished experience — but open, customizable, and accessible to everyone.",
  },
  freePlanComparison: {
    type: Array,
    required: true,
  },
  switchReasons: {
    type: Array,
    required: true,
  },
  featureComparison: {
    type: Array,
    required: true,
  },
  getCompetitorPrice: {
    type: Function,
    default: null,
  },
  /** Direct-answer summary shown in the "Bottom line" callout (AEO/GEO). */
  summaryLine: {
    type: String,
    default: null,
  },
  /** Competitor-specific FAQ entries for the FAQ section + FAQPage schema. */
  faqs: {
    type: Array,
    default: null,
  },
})

const { isAuthenticated: authenticated } = useIsAuthenticated()

const switchSectionTitle = computed(
  () => `Why Users Switch from ${props.competitorName} to SharaForms`,
)

const resolvedPlanComparisonTitle = computed(
  () =>
    props.planComparisonTitle ??
    `Free Plan Comparison:\nSharaForms vs ${props.competitorName}`,
)

const resolvedPlanComparisonTitleLines = computed(() =>
  resolvedPlanComparisonTitle.value.split("\n"),
)

const resolvedPlanComparisonSubtitle = computed(
  () =>
    props.planComparisonSubtitle ??
    `With SharaForms, you get everything ${props.competitorName} offers — and more — without limits.`,
)

const resolvedHeroSecondaryCtaLabel = computed(
  () =>
    props.heroSecondaryCtaLabel ??
    `Import from ${props.competitorName}`,
)

const resolvedHeroSecondaryCtaTo = computed(() => {
  if (props.heroSecondaryCtaTo) {
    return props.heroSecondaryCtaTo
  }

  if (!props.heroImportSource) {
    return null
  }

  return {
    name: authenticated.value || props.heroImportSource === 'google_forms' ? 'forms-create' : 'forms-create-guest',
    query: { import: props.heroImportSource },
  }
})

const COMPARISON_PAGES = [
  { slug: '123formbuilder', label: '123FormBuilder' },
  { slug: 'fillout', label: 'Fillout' },
  { slug: 'formbricks', label: 'Formbricks' },
  { slug: 'formio', label: 'Form.io' },
  { slug: 'googleforms', label: 'Google Forms' },
  { slug: 'heyform', label: 'HeyForm' },
  { slug: 'jotform', label: 'JotForm' },
  { slug: 'tally', label: 'Tally' },
  { slug: 'typeform', label: 'Typeform' },
  { slug: 'youform', label: 'Youform' },
]

const otherComparisons = computed(() =>
  COMPARISON_PAGES.filter((comparison) => comparison.label !== props.competitorName),
)

const bottomLine = computed(
  () =>
    props.summaryLine ??
    `SharaForms is a free ${props.competitorName} alternative that gives you unlimited forms, unlimited submissions, and built-in calculations — without ${props.competitorName}'s free-tier limits, upgrade pressure, or cost as your usage grows.`,
)

const defaultFaqs = computed(() => [
  {
    question: 'Is SharaForms really free?',
    answer:
      'Yes. SharaForms has a free plan with unlimited forms and unlimited submissions, so you can build and publish forms without hitting monthly response caps. Paid plans add custom branding, custom domains, and advanced workflow features.',
  },
  {
    question: `Is SharaForms a good ${props.competitorName} alternative?`,
    answer: `For most teams, yes. SharaForms covers the polished form-building experience ${props.competitorName} is known for, plus built-in calculations, conditional logic, dynamic PDF documents, custom CSS, and a more generous free tier — without upgrade pressure as your usage grows.`,
  },
  {
    question: `How much does SharaForms cost compared to ${props.competitorName}?`,
    answer:
      'SharaForms starts at $0 with unlimited forms and submissions. Paid plans start at $12 per month. The comparison table on this page shows how free-tier limits and pricing stack up.',
  },
  {
    question: `Can I import my forms from ${props.competitorName} to SharaForms?`,
    answer: `Yes — SharaForms supports importing forms from common builders, including ${props.competitorName}, so you can move without rebuilding from scratch.`,
  },
  {
    question: 'Does SharaForms support calculations and formulas?',
    answer:
      'Yes. Built-in calculations and formulas are a core SharaForms feature — build order forms, quote calculators, and pricing estimators that compute totals in real time.',
  },
])

const resolvedFaqs = computed(() => props.faqs || defaultFaqs.value)

const integrationLogosrow1 = [
  {name: "Email", icon: "lucide:mail", iconClass: "text-[#2563EB]" },
  { name: "Slack", icon: "simple-icons:slack", iconClass: "text-[#4A154B]" },
  { name: "Discord", icon: "ic:baseline-discord", iconClass: "text-[#5865F2]" },
  { name: "Webhook", icon: "lucide:webhook", iconClass: "text-[#0061FF]" },
]

const integrationLogosrow2 = [
  { name: "Telegram", icon: "mdi:telegram", iconClass: "text-[#27A7E7]" },
  { name: "Zapier", icon: "simple-icons:zapier", iconClass: "text-[#FF4A00]" },
  { name: "Google Sheets", icon: "mdi:google-spreadsheet", iconClass: "text-[#34A853]" },
  { name: "n8n", icon: "simple-icons:n8n", iconClass: "text-[#EA4B71]" },
]

const privacyFeatures = [
  {
    icon: "i-lucide-square-code",
    title: "Self-Hosting",
    description:
      "Deploy SharaForms on-premise or use our managed service — your data always belongs to you.",
  },
  {
    icon: "i-lucide-globe",
    title: "GDPR-Compliant",
    description: "Your forms and responses stay private and secure.",
  },
  {
    icon: "i-lucide-badge-check",
    title: "Enterprise-Ready",
    description:
      "SSO, API access, custom SLAs, and advanced permissions for teams who need scale.",
  },
  {
    icon: "i-lucide-shield-check",
    title: "Transparency",
    description:
      "No tracking pixels, no hidden analytics — just clean, honest form building.",
  },
]


const displayCompetitorPrice = computed(() => {
  return (props.getCompetitorPrice) ? true : false
})

function isYesCell(value) {
  return value === "Y" || value === true
}

function isNoCell(value) {
  return value === "N" || value === false
}

const submissionOptions = [
  100, 250, 500, 1000, 2500, 5000, 7500, 10000, 15000, 20000, 25000,
]
const submissionsIndex = ref(7)

const submissionsPerMonth = computed(
  () => submissionOptions[submissionsIndex.value] ?? 10000,
)
const sliderPercent = computed(
  () => (submissionsIndex.value / (submissionOptions.length - 1)) * 100,
)
const sliderStyle = computed(() => ({
  background: `linear-gradient(to right, #2563eb 0%, #2563eb ${sliderPercent.value}%, #f3f4f6 ${sliderPercent.value}%, #f3f4f6 100%)`,
}))

const config = useRuntimeConfig()
const route = useRoute()

const baseUrl = computed(() => {
  const url = config.public.appUrl
  return (url && url !== '/') ? url.replace(/\/+$/, '') : ''
})

const comparisonSchema = computed(() => {
  const canonicalUrl = baseUrl.value ? `${baseUrl.value}${route.path}` : route.path

  return {
    '@context': 'https://schema.org',
    '@graph': [
      {
        '@type': 'Article',
        '@id': `${canonicalUrl}#article`,
        headline: `Why teams choose SharaForms over ${props.competitorName}`,
        description: `Key reasons teams evaluating ${props.competitorName} choose SharaForms, including unlimited forms and submissions, built-in calculations, and workflow flexibility.`,
        name: `SharaForms vs ${props.competitorName}`,
        author: {
          '@type': 'Organization',
          name: 'SharaForms',
        },
        publisher: {
          '@type': 'Organization',
          name: 'SharaForms',
        },
        isPartOf: {
          '@id': `${canonicalUrl}#webpage`,
        },
        mainEntityOfPage: {
          '@type': 'WebPage',
          '@id': `${canonicalUrl}`,
        },
      },
      {
        '@type': 'ItemList',
        name: `Why teams choose SharaForms over ${props.competitorName}`,
        description: `Key reasons teams evaluating ${props.competitorName} choose SharaForms, including unlimited forms and submissions, built-in calculations, and workflow flexibility.`,
        numberOfItems: props.switchReasons.length,
        itemListElement: props.switchReasons.map((reason, index) => ({
          '@type': 'ListItem',
          position: index + 1,
          name: reason.title,
          description: reason.description,
        })),
      },
      {
        '@type': 'FAQPage',
        '@id': `${canonicalUrl}#faq`,
        mainEntity: resolvedFaqs.value.map((faq) => ({
          '@type': 'Question',
          name: faq.question,
          acceptedAnswer: {
            '@type': 'Answer',
            text: faq.answer,
          },
        })),
      },
      {
        '@type': 'Table',
        '@id': `${canonicalUrl}#feature-table`,
        about: `SharaForms vs ${props.competitorName} feature comparison`,
        name: `SharaForms vs ${props.competitorName}: feature comparison`,
        description: `Side-by-side feature comparison of SharaForms and ${props.competitorName}.`,
        hasPart: {
          '@type': 'ItemList',
          numberOfItems: props.featureComparison.length,
          itemListElement: props.featureComparison.map((row, index) => ({
            '@type': 'ListItem',
            position: index + 1,
            name: row.label,
            description: `SharaForms: ${normalizeCellValue(row.cells[0])}. ${props.competitorName}: ${normalizeCellValue(row.cells[1])}.`,
          })),
        },
      },
    ],
  }
})

function normalizeCellValue(value) {
  if (value === 'Y') return 'Yes'
  if (value === 'N') return 'No'
  return String(value ?? '')
}

useHead({
  script: [
    {
      key: `comparison-schema:${props.competitorName}`,
      type: 'application/ld+json',
      textContent: JSON.stringify(comparisonSchema.value),
    },
  ],
})

function formatSubmissionsLabel(value, idx) {
  const formatted = new Intl.NumberFormat("en-US").format(value)
  return idx === submissionOptions.length - 1 ? `${formatted}+` : formatted
}

function formatCurrency(amount) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    maximumFractionDigits: 0,
  }).format(amount)
}

const sharaformsPrice = computed(() => 0)
const competitorPrice = computed(() =>
  props.getCompetitorPrice(submissionsPerMonth.value),
)
const monthlySavings = computed(() =>
  Math.max(0, competitorPrice.value - sharaformsPrice.value),
)
</script>

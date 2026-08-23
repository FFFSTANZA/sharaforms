<template>
  <div
    class="group relative h-full rounded-[1.75rem] transition-transform duration-300 ease-out hover:-translate-y-2"
    :class="
      plan.highlighted
        ? 'brand-gradient-warm z-10 p-[2px] shadow-[0_36px_90px_-42px_rgba(147,51,234,0.55)]'
        : 'p-[1px] bg-gradient-to-b from-[#e9e5f4]/90 to-[#f1eef9]/40 shadow-[0_20px_55px_-38px_rgba(15,23,42,0.22)] hover:shadow-[0_40px_90px_-46px_rgba(76,29,149,0.4)]'
    "
  >
    <div
      v-if="plan.highlighted"
      class="absolute -top-3 left-1/2 z-20 inline-flex -translate-x-1/2 items-center gap-1.5 whitespace-nowrap rounded-full border border-[#E6E8EE] bg-white px-3 py-1.5 text-xs font-semibold shadow-[0_10px_24px_-10px_rgba(23,25,35,0.18)]"
    >
      <UIcon name="i-lucide-star" class="h-3 w-3 text-[#d97706]" />
      <span class="brand-gradient-text-warm">Most popular</span>
    </div>

    <div
      class="relative flex h-full flex-col overflow-hidden rounded-[calc(1.75rem-2px)] bg-white p-6 sm:p-7 transition-shadow duration-300"
    >
      <div
        v-if="plan.highlighted"
        class="brand-gradient-warm pointer-events-none absolute inset-x-0 top-0 h-1"
        aria-hidden="true"
      ></div>

      <div
        class="pointer-events-none absolute -top-24 -right-20 h-56 w-56 rounded-full opacity-50 blur-3xl"
        :class="accent.glow"
        aria-hidden="true"
      ></div>
      <div
        class="pointer-events-none absolute -bottom-28 -left-20 h-52 w-52 rounded-full opacity-30 blur-3xl"
        :class="accent.glowSoft"
        aria-hidden="true"
      ></div>

      <div
        class="relative -mx-6 -mt-6 flex items-center gap-3.5 px-6 py-5 sm:-mx-7 sm:-mt-7 sm:px-7"
        :class="[accent.banner, plan.highlighted ? 'pt-9' : '']"
      >
        <span
          class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-white shadow-[0_10px_24px_-12px_rgba(76,29,149,0.55)] ring-1 ring-white/30"
          :class="accent.tile"
        >
          <UIcon :name="plan.icon" class="h-5 w-5" />
        </span>
        <div class="min-w-0">
          <h3 class="text-xl leading-7 font-semibold text-white">
            {{ plan.name }}
          </h3>
          <p class="truncate text-sm font-medium text-white/75">{{ plan.tagline }}</p>
        </div>
      </div>

      <p class="relative mt-5 text-sm font-medium leading-6 tracking-[-0.6%] text-[#6E7278]">
        {{ plan.description }}
      </p>

      <div class="relative mt-6 flex items-end gap-2">
        <span
          class="text-4xl font-semibold tracking-[-1%] sm:text-[44px] sm:leading-none"
          :class="accent.price"
        >
          {{ price }}
        </span>
        <span class="pb-1 text-base font-medium leading-7 tracking-[-1.1%] text-[#8E9198]">
          {{ plan.perLabel }}
        </span>
      </div>
      <p
        v-if="billingNote"
        class="relative mt-1.5 inline-flex items-center gap-1.5 text-xs font-medium text-[#8E9198]"
      >
        <span
          class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-[#EFF8F1] text-[#16a34a]"
        >
          <UIcon name="i-lucide-check" class="h-2.5 w-2.5" />
        </span>
        {{ billingNote }}
      </p>

      <div class="relative mt-6">
        <UButton
          v-if="plan.free"
          :to="authenticated ? { name: 'home' } : { name: 'register' }"
          :label="authenticated ? 'Go to app' : 'Get started free'"
          color="neutral"
          variant="outline"
          class="w-full justify-center rounded-xl px-3 xl:px-4 py-2.5 text-base font-medium tracking-[-1.1%]"
        />
        <UButton
          v-else
          :label="plan.ctaLabel"
          class="w-full justify-center rounded-xl px-3 xl:px-4 py-2.5 text-base font-medium tracking-[-1.1%] transition-transform duration-200 group-hover:translate-y-[-1px]"
          :class="
            plan.highlighted
              ? 'brand-button-primary'
              : 'brand-button-secondary'
          "
          @click="emit('cta', plan.key)"
        />
      </div>

      <div class="relative mt-7 border-t border-[#ECEEF2] pt-6">
        <p class="text-sm font-semibold leading-5 tracking-[-0.6%] text-[#1D1F24]">
          {{ plan.featuresLabel }}
        </p>
        <ul class="mt-4 space-y-3">
          <li
            v-for="feature in plan.features"
            :key="feature"
            class="flex items-center gap-2.5 text-sm font-medium leading-5 tracking-[-0.6%] text-[#6E7278]"
          >
            <span
              class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full"
              :class="accent.check"
            >
              <UIcon name="i-lucide-check" class="h-3 w-3" />
            </span>
            {{ feature }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  plan: { type: Object, required: true },
  price: { type: String, required: true },
  billingNote: { type: String, default: "" },
  authenticated: { type: Boolean, default: false },
})

const emit = defineEmits(["cta"])

const accents = {
  violet: {
    tile: "bg-[#6366f1]",
    banner: "bg-[#6366f1]",
    glow: "bg-[#6366f1]/30",
    glowSoft: "bg-[#EEF0FD]",
    price: "text-[#6366f1]",
    check: "bg-[#EEF0FD] text-[#6366f1]",
  },
  pink: {
    tile: "bg-gradient-to-br from-[#ff8a4d] to-[#ff5c38]",
    banner: "bg-gradient-to-br from-[#ff8a4d] to-[#ff5c38]",
    glow: "bg-[#ff5c38]/30",
    glowSoft: "bg-[#FDF6EB]",
    price: "text-[#ff5c38]",
    check: "bg-[#fce7e2] text-[#ff5c38]",
  },
  blue: {
    tile: "bg-[#0891b2]",
    banner: "bg-[#0891b2]",
    glow: "bg-[#0891b2]/30",
    glowSoft: "bg-[#E4F4F8]",
    price: "text-[#0891b2]",
    check: "bg-[#E4F4F8] text-[#0891b2]",
  },
  sky: {
    tile: "bg-[#16a34a]",
    banner: "bg-[#16a34a]",
    glow: "bg-[#16a34a]/30",
    glowSoft: "bg-[#EFF8F1]",
    price: "text-[#16a34a]",
    check: "bg-[#EFF8F1] text-[#16a34a]",
  },
}

const accent = computed(() => accents[props.plan.accent] ?? accents.violet)
</script>

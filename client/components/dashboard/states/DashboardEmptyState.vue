<template>
  <div class="text-center py-12 px-6 rounded-xl bg-[var(--sf-bg-muted)]/60 ring-1 ring-inset ring-[var(--sf-border-card)] dark:bg-neutral-800/40 dark:ring-neutral-700/60">
    <div
      v-if="icon"
      class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white text-[var(--sf-text-muted)] shadow-sm ring-1 ring-inset ring-[var(--sf-border-card)] dark:bg-neutral-800 dark:text-neutral-500 dark:ring-neutral-700"
    >
      <UIcon
        :name="icon"
        class="h-6 w-6"
      />
    </div>
    <h3 class="mt-3 text-lg font-semibold tracking-tight text-[var(--sf-text-primary)] dark:text-white">
      {{ title }}
    </h3>
    <p
      v-if="description"
      class="mx-auto mt-1 max-w-md text-sm text-[var(--sf-text-description)] dark:text-neutral-400"
    >
      {{ description }}
    </p>
    <div v-if="$slots.action" class="mt-4">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: { type: String, default: '' },
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>

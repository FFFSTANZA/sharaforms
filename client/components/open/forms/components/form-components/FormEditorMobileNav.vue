<template>
  <nav
    class="shrink-0 border-t border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] pb-[env(safe-area-inset-bottom)]"
    aria-label="Editor sections"
  >
    <div class="flex items-stretch">
      <button
        v-for="item in items"
        :key="item.value"
        type="button"
        class="flex flex-1 flex-col items-center justify-center gap-0.5 py-2 transition-colors duration-150 select-none"
        :class="modelValue === item.value
          ? 'text-[var(--sf-coral-500)]'
          : 'text-[var(--sf-text-caption)] active:bg-[var(--sf-nav-hover-bg)]'"
        :aria-current="modelValue === item.value ? 'page' : undefined"
        @click="$emit('update:modelValue', item.value)"
      >
        <span
          class="flex h-6 w-12 items-center justify-center rounded-full transition-colors duration-150"
          :class="modelValue === item.value ? 'bg-[var(--sf-nav-active-bg)]' : ''"
        >
          <Icon
            :name="item.icon"
            class="h-5 w-5"
          />
        </span>
        <span class="text-[11px] font-medium leading-none">{{ item.label }}</span>
      </button>
    </div>
  </nav>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    required: true,
  },
})

defineEmits(['update:modelValue'])

const items = [
  { label: 'Build', value: 'build', icon: 'i-lucide-hammer' },
  { label: 'Preview', value: 'preview', icon: 'i-lucide-eye' },
  { label: 'Design', value: 'design', icon: 'i-lucide-palette' },
]
</script>

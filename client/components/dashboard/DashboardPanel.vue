<template>
  <div :class="panelClasses">
    <slot />
  </div>
</template>

<script setup>
const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'subtle'].includes(v),
  },
  padding: {
    type: String,
    default: 'md',
    validator: (v) => ['none', 'sm', 'md', 'lg'].includes(v),
  },
})

const paddingMap = {
  none: '',
  sm: 'p-4',
  md: 'p-5',
  lg: 'p-6',
}

/* Formly design tokens: cards use #E6E8EE borders, white bg, Clay shadow */
const variantMap = {
  default: 'border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)]',
  subtle: 'border-[var(--sf-border-divider)] bg-[var(--sf-bg-muted)]/50',
}

const panelClasses = computed(() => [
  'rounded-2xl border shadow-[var(--sf-shadow-card)]',
  paddingMap[props.padding],
  variantMap[props.variant],
])
</script>

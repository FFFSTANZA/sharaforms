<template>
  <span
    class="inline-flex items-baseline gap-[0.015em] text-[1.58rem] leading-none font-medium tracking-[-0.03em]"
    style="font-family: Inter, sans-serif"
  >
    <span :class="dark ? 'text-white/92' : 'text-[#000F40]'">Shara</span>
    <span class="brand-gradient-text">Forms</span>
  </span>
</template>

<script setup>
defineProps({
  dark: {
    type: Boolean,
    default: false,
  },
})
</script>

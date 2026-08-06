<template>
  <div
    ref="el"
    class="scroll-reveal"
    :class="{ 'reveal-ready': hydrated, 'is-visible': visible }"
  >
    <slot />
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue"

const el = ref(null)
const hydrated = ref(false)
const visible = ref(false)
let observer = null

onMounted(() => {
  hydrated.value = true
  if (
    typeof IntersectionObserver === "undefined" ||
    window.matchMedia("(prefers-reduced-motion: reduce)").matches
  ) {
    visible.value = true
    return
  }
  observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        visible.value = true
        observer.disconnect()
      }
    },
    { threshold: 0.12, rootMargin: "0px 0px -48px 0px" },
  )
  observer.observe(el.value)
})

onBeforeUnmount(() => {
  if (observer) observer.disconnect()
})
</script>

<style scoped>
.scroll-reveal.reveal-ready {
  opacity: 0;
  transform: translateY(24px);
  transition:
    opacity 600ms ease,
    transform 600ms ease;
}
.scroll-reveal.reveal-ready.is-visible {
  opacity: 1;
  transform: none;
}
@media (prefers-reduced-motion: reduce) {
  .scroll-reveal.reveal-ready {
    opacity: 1;
    transform: none;
    transition: none;
  }
}
</style>

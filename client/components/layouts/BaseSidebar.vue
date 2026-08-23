<template>
  <aside
    :class="[
      'sidebar-base flex flex-col transition-all duration-300 ease-in-out z-[60]',
      isMobileMenuOpen
        ? 'fixed inset-0'
        : 'sticky top-0 w-full h-[49px] overflow-hidden sm:flex sm:fixed sm:h-full sm:w-60 sm:border-r',
    ]"
  >
    <!-- Top Section - Only show if there's header content or on mobile -->
    <div 
      v-if="hasHeaderContent || isMobileMenuOpen"
      class="p-2.5 sm:p-3 border-b border-[var(--sf-border-divider)] h-[49px] sm:h-auto shrink-0"
    >
      <div class="flex items-center justify-between gap-1 w-full">
        <!-- Header Content Slot -->
        <slot name="header" :isMobileMenuOpen="isMobileMenuOpen" />
        
        <div class="grow" v-if="hasMobileHeaderContent">
          <slot name="mobile-header" :isMobileMenuOpen="isMobileMenuOpen" />
        </div>

        <!-- Mobile Menu Toggle -->
        <div :class="{ 'sm:hidden': !isMobileMenuOpen }">
          <UButton
            square
            size="sm"
            class="hover:bg-[var(--sf-nav-hover-bg)]"
            :icon="isMobileMenuOpen ? 'i-lucide-x' : 'i-lucide-menu'"
            variant="ghost"
            color="neutral"
            @click="isMobileMenuOpen = !isMobileMenuOpen"
          />
        </div>
      </div>
    </div>
    
    <!-- Mobile Menu Toggle (when header is hidden on desktop) -->
    <div 
      v-else
      class="sm:hidden p-2.5 border-b border-[var(--sf-border-divider)] h-[49px] flex items-center justify-start gap-2 shrink-0"
    >
      <div class="grow">
        <slot name="mobile-header" :isMobileMenuOpen="isMobileMenuOpen" />
      </div>
      <UButton
        square
        size="sm"
        class="hover:bg-[var(--sf-nav-hover-bg)]"
        icon="i-lucide-menu"
        variant="ghost"
        color="neutral"
        @click="isMobileMenuOpen = !isMobileMenuOpen"
      />
    </div>

    <!-- Navigation Content -->
    <nav 
      class="flex-1 p-3 overflow-y-auto flex flex-col"
      :class="{ 'hidden': !isMobileMenuOpen, 'sm:flex': true }"
    >
      <slot name="navigation" :isMobileMenuOpen="isMobileMenuOpen" />
    </nav>

    <!-- Footer -->
    <div 
      class="p-2.5 sm:p-3 border-t border-[var(--sf-border-divider)] shrink-0"
      :class="{ 'hidden': !isMobileMenuOpen, 'sm:block': true }"
    >
      <slot name="footer" :isMobileMenuOpen="isMobileMenuOpen">
        <p class="text-xs text-[var(--sf-text-muted)] text-center">
          <span class="font-bold"><NuxtLink class="text-[var(--sf-text-muted)]" :to="{ name: 'home' }">SharaForms</NuxtLink></span>
          <span class="text-[var(--sf-text-caption)]" v-if="version"> v{{ version }}</span>
        </p>
      </slot>
    </div>
  </aside>
</template>

<script setup>
const slots = useSlots()

const isMobileMenuOpen = ref(false)
const version = computed(() => useFeatureFlag('version'))

// Check if header slot has content
const hasHeaderContent = computed(() => {
  return !!(slots.header && slots.header().length > 0)
})

const hasMobileHeaderContent = computed(() => {
  return !!(slots['mobile-header'] && slots['mobile-header'].length > 0)
})

// Handle body overflow when mobile menu is open
watchEffect(() => {
  if (import.meta.client) {
    document.body.classList.toggle('overflow-hidden', isMobileMenuOpen.value)
  }
})

onUnmounted(() => {
  if (import.meta.client) {
    document.body.classList.remove('overflow-hidden')
  }
})

// Expose the mobile menu state to parent components
defineExpose({ isMobileMenuOpen })
</script>

<style scoped>
.sidebar-base {
  background: linear-gradient(180deg, var(--sf-bg-surface) 0%, var(--sf-bg-muted) 100%);
  border-color: var(--sf-border-card);
}
</style>

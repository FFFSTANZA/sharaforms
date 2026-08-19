<template>
  <div class="dashboard-layout flex min-h-screen bg-[#F7F8FA] text-[#1D1F24]">
    <!-- Sidebar -->
    <AppSidebar />
    
    <!-- Main content area -->
    <main class="flex-1 sm:ml-[260px] pt-14 sm:pt-0 min-h-screen">
      <slot />
    </main>
  </div>
</template>

<script setup>
import AppSidebar from "~/components/layouts/AppSidebar.vue"
</script>

<style scoped>
.dashboard-layout {
  font-family: 'Inter', sans-serif !important;
}
.dashboard-layout :deep(*) {
  font-family: 'Inter', sans-serif !important;
}
</style>
 
<template>
  <div class="dashboard-layout flex min-h-screen bg-[var(--sf-bg-page)] text-[var(--sf-text-primary)]">
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

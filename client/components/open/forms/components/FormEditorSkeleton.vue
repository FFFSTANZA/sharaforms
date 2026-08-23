<template>
  <div class="relative flex w-full flex-col grow max-h-screen">
    <!-- Navbar Skeleton -->
    <div class="border-b border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] px-4 py-2.5 flex items-center justify-between">
      <!-- Back button and tabs -->
      <div class="flex items-center gap-3">
        <USkeleton class="h-8 w-8 rounded-lg" />
        <div class="flex gap-1 bg-[var(--sf-bg-muted)] rounded-xl p-1">
          <USkeleton class="h-7 w-16 rounded-lg" />
          <USkeleton class="h-7 w-16 rounded-lg" />
        </div>
        <USkeleton class="h-8 w-20 rounded-lg" />
      </div>
      
      <!-- Title + save -->
      <div class="flex items-center gap-3">
        <USkeleton class="h-5 w-48" />
        <USkeleton class="h-9 w-28 rounded-xl" />
      </div>
    </div>

    <!-- Main content area -->
    <div class="w-full flex grow overflow-y-scroll relative bg-[var(--sf-bg-page)]">
      <!-- Left sidebar skeleton -->
      <div class="relative w-full shrink-0 overflow-y-scroll border-r border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] md:w-1/2 md:max-w-xs lg:w-2/5">
        <div class="p-4 space-y-4">
          <!-- Tab buttons -->
          <div class="flex gap-2 mb-6">
            <USkeleton class="h-8 w-16" />
            <USkeleton class="h-8 w-16" />
          </div>
          
          <!-- Form fields section -->
          <div class="space-y-4">
            <USkeleton class="h-5 w-24" />
            <USkeleton class="h-10 w-full" />
            <USkeleton class="h-5 w-32" />
            <USkeleton class="h-8 w-full" />
            <USkeleton class="h-5 w-28" />
            <USkeleton class="h-12 w-full" />
            <USkeleton class="h-5 w-20" />
            <USkeleton class="h-6 w-full" />
          </div>
          
          <!-- Additional form options -->
          <div class="space-y-3 mt-6">
            <USkeleton class="h-4 w-36" />
            <USkeleton class="h-9 w-full" />
            <USkeleton class="h-4 w-28" />
            <USkeleton class="h-9 w-full" />
            <USkeleton class="h-4 w-32" />
            <USkeleton class="h-16 w-full" />
          </div>
        </div>
      </div>

      <!-- Center preview area skeleton -->
      <div class="flex-1 hidden md:flex flex-col bg-[var(--sf-bg-page)]">
        <div class="p-4 bg-[var(--sf-bg-muted)]/50 border-b border-[var(--sf-border-divider)]">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-1.5">
              <div class="w-2.5 h-2.5 rounded-full bg-[#FF5F57]" />
              <div class="w-2.5 h-2.5 rounded-full bg-[#FEBC2E]" />
              <div class="w-2.5 h-2.5 rounded-full bg-[#28C840]" />
            </div>
            <USkeleton class="h-5 w-24 rounded" />
          </div>
        </div>
        
        <div class="flex-1 p-6 overflow-y-auto">
          <div class="max-w-lg mx-auto bg-[var(--sf-bg-surface)] rounded-2xl shadow-[var(--sf-shadow-card)] border border-[var(--sf-border-card)] p-6">
            <!-- Form preview skeleton -->
            <div class="space-y-6">
              <div class="space-y-2">
                <USkeleton class="h-8 w-3/4" />
                <USkeleton class="h-4 w-full" />
                <USkeleton class="h-4 w-2/3" />
              </div>
              
              <div class="space-y-4">
                <div class="space-y-2">
                  <USkeleton class="h-4 w-24" />
                  <USkeleton class="h-10 w-full" />
                </div>
                
                <div class="space-y-2">
                  <USkeleton class="h-4 w-32" />
                  <USkeleton class="h-10 w-full" />
                </div>
                
                <div class="space-y-2">
                  <USkeleton class="h-4 w-28" />
                  <USkeleton class="h-24 w-full" />
                </div>
                
                <div class="space-y-2">
                  <USkeleton class="h-4 w-20" />
                  <USkeleton class="h-10 w-full" />
                </div>
              </div>
              
              <USkeleton class="h-10 w-32" />
            </div>
          </div>
        </div>
      </div>

      <!-- Right sidebar skeleton -->
      <div class="hidden lg:block w-80 border-l border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-4 space-y-4">
        <div class="flex items-center justify-between">
          <USkeleton class="h-6 w-24" />
          <USkeleton class="h-6 w-6" />
        </div>
        
        <div class="space-y-4">
          <div class="space-y-2">
            <USkeleton class="h-4 w-20" />
            <USkeleton class="h-9 w-full" />
          </div>
          
          <div class="space-y-2">
            <USkeleton class="h-4 w-24" />
            <USkeleton class="h-9 w-full" />
          </div>
          
          <div class="space-y-2">
            <USkeleton class="h-4 w-32" />
            <USkeleton class="h-16 w-full" />
          </div>
          
          <div class="space-y-2">
            <USkeleton class="h-4 w-28" />
            <USkeleton class="h-9 w-full" />
          </div>
          
          <!-- Field options -->
          <div class="border-t pt-4 space-y-3">
            <USkeleton class="h-4 w-36" />
            <USkeleton class="h-8 w-full" />
            <USkeleton class="h-4 w-28" />
            <USkeleton class="h-8 w-full" />
            <USkeleton class="h-4 w-32" />
            <USkeleton class="h-12 w-full" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Define props
defineProps({
  backButton: {
    type: Boolean,
    default: true
  }
})

// Define emits
defineEmits(['go-back'])
</script> 
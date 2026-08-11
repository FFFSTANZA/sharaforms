<template>
  <div
    class="flex items-center gap-3 sm:gap-4 px-4 sm:px-5 py-3.5"
  >
    <!-- Icon tile skeleton -->
    <USkeleton class="h-10 w-10 shrink-0 rounded-lg" />

    <!-- Title + slug skeleton -->
    <div class="min-w-0 flex-1 space-y-2">
      <USkeleton class="h-4 w-2/5" />
      <USkeleton class="h-3 w-1/3" />
    </div>

    <!-- Stats skeleton -->
    <div class="hidden md:flex items-center gap-3">
      <USkeleton class="h-4 w-16" />
      <USkeleton class="h-4 w-8" />
      <USkeleton class="h-4 w-8" />
    </div>

    <!-- Menu skeleton -->
    <USkeleton class="h-6 w-6 shrink-0" />
  </div>
</template>

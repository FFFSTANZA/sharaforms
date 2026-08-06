<template>
  <ClientOnly>
    <div
      v-if="!hasDecided"
      class="fixed inset-x-0 bottom-0 z-[10000] border-t border-neutral-200 bg-white/95 backdrop-blur"
    >
      <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 md:flex-row md:items-center md:justify-between">
        <div class="max-w-3xl">
          <p class="text-sm font-medium text-neutral-900">
            Privacy choices
          </p>
          <p class="mt-1 text-sm text-neutral-600">
            We use essential cookies to keep the app secure and optional analytics cookies to improve the product. You can change this choice later by clearing your browser cookies.
            <NuxtLink :to="{ name: 'privacy-policy' }" class="font-medium underline">
              Privacy Policy
            </NuxtLink>
            and
            <NuxtLink :to="{ name: 'terms-conditions' }" class="font-medium underline">
              Terms
            </NuxtLink>
            .
          </p>
        </div>

        <div class="flex items-center gap-3">
          <UButton color="neutral" variant="outline" @click="rejectOptionalTracking">
            Essential only
          </UButton>
          <UButton color="primary" @click="acceptOptionalTracking">
            Accept analytics
          </UButton>
        </div>
      </div>
    </div>
  </ClientOnly>
</template>

<script setup>
const { hasDecided, acceptOptionalTracking, rejectOptionalTracking } = usePrivacyPreferences()
</script>

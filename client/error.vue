<template>
  <div>
    <NuxtLayout>
      <div class="flex mt-6">
        <div class="w-full md:w-2/3 md:mx-auto md:max-w-md">
          <img
            alt="Nice plant as we have nothing else to show!"
            src="/img/icons/plant.png"
            class="w-56 mb-5"
          >

          <h1 class="mb-6 font-semibold text-3xl text-neutral-900">
            {{ error.statusCode === 503 ? 'We are performing maintenance' : `Whoops, something went wrong (${error.statusCode || '404'})` }}
          </h1>

          <p v-if="error.statusCode === 503" class="mb-6 text-neutral-600">
            The service is temporarily unavailable while maintenance is in progress. Please try again in a few minutes.
          </p>

          <div class="links">
            <NuxtLink
              :to="{ name: 'index' }"
              class="hover:underline text-neutral-700"
            >
              Go Home
            </NuxtLink>
          </div>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup>
const authStore = useAuthStore()

useOpnSeoMeta({
  title: "404 - Page not found",
})

const props = defineProps({
  error: { type: Object, default: null }
})

if (props.error?.statusCode === 500) {
  // Track 500 errors in PostHog
  const exception = new Error(props.error?.message ?? props.error?.statusMessage)
  exception.code = props.error?.statusCode
  exception.stack = props.error?.stack
  usePostHog().captureException(exception, {
    message: props.error?.message ?? props.error?.statusMessage,
    type: '500_error',
    user_id: authStore.user?.id
  })
}
</script>

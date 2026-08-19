<template>
  <div>
    <div class="flex mt-6">
      <div class="w-full md:w-2/3 md:mx-auto md:max-w-md">
        <img
          alt="Nice plant as we have nothing else to show!"
          src="/img/icons/plant.png"
          class="w-56 mb-5"
        >

        <h1 class="mb-6 font-semibold text-3xl text-neutral-900">
          Page not found (404)
        </h1>

        <div class="links">
          <UButton
            :to="{ name: 'index' }"
            class="hover:underline"
          >
            Go Home
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: ['root-redirect']
})

useOpnSeoMeta({
  title: "Page not found",
  description: "The page you are looking for does not exist or has been moved. Return to the SharaForms homepage.",
  robots: { index: false, follow: false },
})

const event = useRequestEvent()
setResponseStatus(event, 404, 'Page Not Found')
</script>
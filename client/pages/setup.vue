<template>
  <div class="min-h-screen bg-neutral-50 flex flex-col justify-center sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <!-- Background Glow Orbs -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-gradient-to-tr from-pink-300/10 via-purple-300/15 to-blue-300/10 blur-3xl pointer-events-none -z-10"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="mb-8 flex items-center justify-center gap-0">
        <img
          src="/img/sharaforms-logo.png"
          alt="SharaForms logo"
          class="h-14 w-14 shrink-0"
        >
        <BrandWordmark class="text-[1.72rem]" />
      </div>
      
      <p class="mt-2 text-center text-sm text-neutral-600">
        Welcome to SharaForms! Let's get you set up. Create your admin account to start building beautiful forms.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="bg-white py-8 px-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-neutral-100/80 sm:rounded-3xl sm:px-10">
        <RegisterForm 
          :is-quick="false"
          :is-setup="true"
          @registered="handleSetupComplete"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import RegisterForm from '~/components/pages/auth/components/RegisterForm.vue'

const { invalidateFlags } = useFeatureFlags()
const router = useRouter()

// Check if setup is required
const setupRequired = useFeatureFlag('setup_required', false)
const selfHosted = useFeatureFlag('self_hosted', false)

// Show 404 if setup not required or not self-hosted
if (!setupRequired || !selfHosted) {
  throw createError({ statusCode: 404, statusMessage: 'Page Not Found' })
}

// SEO
useOpnSeoMeta({
  title: "Setup - SharaForms",
  description: "Set up your SharaForms instance",
  robots: "noindex, nofollow"
})

definePageMeta({
  layout: 'empty'
})

// Handle successful setup completion
const handleSetupComplete = async () => {
  // Invalidate feature flags to update setup_required status
  await invalidateFlags()
  
  // Show success message
  useAlert().success({
    title: "Setup Complete! 🎉",
    description: "Your SharaForms instance is ready. Time to create your first form!"
  })
  
  // Redirect to dashboard
  router.push({ name: "home" })
}
</script> 

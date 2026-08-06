<template>
  <div class="bg-white">
    <section class="relative overflow-hidden">
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-linear-to-b from-white via-fuchsia-50 to-white" />
        <div class="absolute left-0 top-12 h-80 w-80 rounded-full bg-pink-200/70 blur-3xl" />
        <div class="absolute bottom-0 right-0 h-[28rem] w-[28rem] rounded-full bg-violet-200/70 blur-3xl" />
      </div>

      <div class="relative px-6 py-10 sm:px-8 sm:py-14 lg:px-12 lg:py-16">
        <div class="mx-auto max-w-xl">
          <div class="rounded-[32px] border border-white/80 bg-white/90 p-6 shadow-[0_24px_80px_rgba(15,23,42,0.12)] backdrop-blur sm:p-8">
            <div class="inline-flex items-center gap-2 rounded-full brand-gradient-warm px-3 py-1 text-sm font-medium text-white">
              <UIcon name="i-lucide-user-plus" class="h-4 w-4" />
              New account
            </div>
            <h2
              data-testid="register-page"
              class="mt-4 text-3xl font-semibold tracking-[-1%] text-neutral-950"
            >
              Create your account
            </h2>
            <p class="mt-3 text-base font-normal leading-7 tracking-[-1.1%] text-neutral-600">
              Start in a few minutes and begin building forms right away.
            </p>

            <div v-if="isInvited" class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
              <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm">
                  <UIcon name="i-lucide-mail-open" class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-neutral-950">
                    Workspace invitation detected
                  </p>
                  <p class="mt-1 text-sm leading-6 text-neutral-600">
                    Finish registration to accept your invite and join the shared workspace.
                  </p>
                </div>
              </div>
            </div>

            <div class="mt-6">
              <RegisterForm />
            </div>
          </div>
        </div>
      </div>
    </section>

    <OpenFormFooter :show-cta="false" />
  </div>
</template>

<script setup>
import RegisterForm from "~/components/pages/auth/components/RegisterForm.vue"

definePageMeta({
  middleware: ["guest"],
})

defineRouteRules({
  swr: 3600,
})

useOpnSeoMeta({
  title: "Register",
})

const route = useRoute()

const isInvited = computed(() => {
  return route.query?.email && route.query?.invite_token
})

</script>

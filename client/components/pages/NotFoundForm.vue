<template>
  <main class="flex justify-center items-center px-16 w-full max-md:px-5 max-md:max-w-full">
    <section class="flex flex-col mb-4 w-full max-w-[1080px] max-md:mt-10 max-md:max-w-full">
      <div class="text-center mx-auto">
        <img
          src="/img/icons/404.png"
          alt="404 Error"
          class="w-full max-w-[250px] mb-5 mx-auto"
        >
        <h1 class="text-dark text-2xl md:text-4xl leading-none font-medium text-center pt-4 md:pt-0 mx-auto md:max-w-full">
          We're sorry! This Form Is No Longer Here
        </h1>
        <p class="text-[#6E7278] mt-3 max-w-xl mx-auto">
          It may have been deleted, deactivated, or the form URL might have been regenerated or updated by its owner. But don't worry, you've got plenty of other options!
        </p>
        
        <h3 class="my-8 text-dark text-xl font-semibold text-center md:pt-0 mx-auto md:max-w-full">
          What can you do?
        </h3>
        <div class="mt-8 grid grid-cols-2 md:grid-cols-5 gap-4">
          <NuxtLink
            v-for="(action, index) in actions"
            :key="index"
            :to="action.to"
            :href="action.href"
            class="bg-[#E4F4F8]/50 rounded-lg px-4 py-6 text-center text-[#565A62] hover:bg-[#E4F4F8] flex flex-col items-center hover:no-underline"
            @click="action.onClick ? action.onClick() : null"
          >
            <Icon
              :name="action.icon"
              class="w-8 h-8 mb-2 text-[#0891b2]"
            />
            <span class="font-medium">
              {{ action.text }}
            </span>
          </NuxtLink>
        </div>
        
        <div class="mt-10 text-[#565A62]">
          <h3 class="mt-8 text-dark text-xl font-semibold text-center md:pt-0 mx-auto md:max-w-full">
            Still not sure what to do? Here are more ways to get started!
          </h3>
          <p class="mt-4 text-[#6E7278]">
            No worries! Here are some other options for you:
          </p>
          <ul class="mt-4 space-y-2 text-sm">
            <li>
              Do you want to know why users choose SharaForms? 
              <NuxtLink
                class="text-[#0891b2] hover:text-[#087a8a]"
                :to="{ name: 'ai-form-builder' }"
              >
                Learn more here!
              </NuxtLink>
            </li>
            <li>
              Browse Templates
              <NuxtLink
                class="text-[#0891b2] hover:text-[#087a8a]"
                :to="{ name: 'templates' }"
              >
                Get inspired with ready-to-use form templates!
              </NuxtLink>
            </li>
            <li>
              Check Out Our Help Center
              <NuxtLink
                class="text-[#0891b2] hover:text-[#087a8a]"
                :to="sharaformsConfig.links.help_url"
              >
                Find quick answers to common questions.
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>

      <div class="mt-20 border-t">
        <OpenFormFooter />
      </div>
    </section>
  </main>
</template>

<script setup>
import sharaformsConfig from "~/sharaforms.config.js"

const actions = [
  {
    to: { name: 'forms-create-guest' },
    icon: 'i-lucide-rocket',
    text: 'Create Form'
  },
  {
    to: { name: 'login' },
    icon: 'i-lucide-list',
    text: 'Find Your Forms'
  },
  {
    to: sharaformsConfig.links.tech_docs,
    icon: 'i-lucide-book-open',
    text: 'Documentation'
  },
  {
    to: { name: 'index' },
    icon: 'i-lucide-search',
    text: 'Explore SharaForms'
  },
  {
    href: `mailto:${sharaformsConfig.links.contact_email}`,
    icon: 'i-lucide-message-square',
    text: 'Need Assistance?'
  }
]
</script>

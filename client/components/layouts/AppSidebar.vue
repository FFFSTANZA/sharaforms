<template>
  <div>
    <!-- Mobile top bar -->
    <div class="sm:hidden fixed top-0 left-0 right-0 h-14 bg-white border-b border-[#E6E8EE] z-50 px-4 flex items-center justify-between">
      <p class="text-[18px] font-bold text-[#1D1F24] tracking-tight">SharaForms<span class="text-[#ff5c38]">.</span></p>
      <UButton
        square
        size="sm"
        icon="i-lucide-menu"
        variant="ghost"
        color="neutral"
        @click="isMobileMenuOpen = true"
      />
    </div>

    <!-- Mobile Drawer Overlay -->
    <div
      v-if="isMobileMenuOpen"
      class="sm:hidden fixed inset-0 bg-neutral-950/20 backdrop-blur-xs z-50"
      @click="isMobileMenuOpen = false"
    ></div>

    <!-- Sidebar Element -->
    <aside
      :class="[
        'sidebar w-[260px] shrink-0 flex flex-col fixed top-0 left-0 h-full overflow-y-auto z-[60] border-r border-[#E6E8EE] transition-transform duration-300 ease-in-out',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full sm:translate-x-0'
      ]"
    >
      <div class="flex flex-col h-full px-6 py-8 gap-7">
        <!-- Brand -->
        <div class="px-4 flex items-center justify-between">
          <p class="whitespace-nowrap select-none text-[20px] font-bold leading-none tracking-tight text-[#1D1F24]">
            SharaForms<span class="text-[#ff5c38]">.</span>
          </p>
          <UButton
            v-if="isMobileMenuOpen"
            square
            size="xs"
            class="sm:hidden"
            icon="i-lucide-x"
            variant="ghost"
            color="neutral"
            @click="isMobileMenuOpen = false"
          />
        </div>

        <!-- Workspace Selector -->
        <WorkspaceDropdown>
          <template #default="{ workspace: currentWorkspace }">
            <button
              class="flex w-full items-center justify-between px-4 py-3 rounded-xl bg-white border border-[#E6E8EE] shadow-[0_1px_2px_rgba(23,25,35,0.04),0_8px_20px_-12px_rgba(23,25,35,0.14)] hover:border-[#FFB79A] transition-all text-left"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="grad-brand w-6 h-6 rounded-lg flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-layer-group text-[9px] text-white"></i>
                </div>
                <span class="text-sm font-medium text-[#565A62] truncate">{{ currentWorkspace?.name || 'My Workspace' }}</span>
              </div>
              <i class="fa-solid fa-chevron-down text-[9px] text-[#A7ABB2] shrink-0"></i>
            </button>
          </template>
        </WorkspaceDropdown>

        <!-- Main Navigation -->
        <nav class="flex flex-col gap-1">
          <NuxtLink
            :to="{ name: 'home', query: { tab: 'dashboard' } }"
            class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all"
            :class="[ activeTab === 'dashboard' ? 'nav-active tab-link font-semibold text-[#1D1F24]' : 'tab-link text-[#565A62]' ]"
            @click="isMobileMenuOpen = false"
          >
            <span
              v-if="activeTab === 'dashboard'"
              class="nav-indicator absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-[#ff5c38]"
            ></span>
            <i class="fa-solid fa-house text-[14px] w-4 text-center" :class="{ 'text-[#ff5c38]': activeTab === 'dashboard' }"></i>
            <span class="nav-label">Dashboard</span>
          </NuxtLink>

          <NuxtLink
            :to="{ name: 'home', query: { tab: 'forms' } }"
            class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all"
            :class="[ activeTab === 'forms' ? 'nav-active tab-link font-semibold text-[#1D1F24]' : 'tab-link text-[#565A62]' ]"
            @click="isMobileMenuOpen = false"
          >
            <span
              v-if="activeTab === 'forms'"
              class="nav-indicator absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-[#ff5c38]"
            ></span>
            <i class="fa-solid fa-file-lines text-[14px] w-4 text-center" :class="{ 'text-[#ff5c38]': activeTab === 'forms' }"></i>
            <span class="nav-label">My Forms</span>
          </NuxtLink>

          <NuxtLink
            :to="{ name: 'templates-my-templates' }"
            class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all"
            :class="[ route.name?.startsWith('templates') ? 'nav-active font-semibold text-[#1D1F24]' : 'text-[#565A62]' ]"
            @click="isMobileMenuOpen = false"
          >
            <i class="fa-solid fa-clone text-[14px] w-4 text-center"></i>
            Templates
          </NuxtLink>
        </nav>

        <!-- Divider -->
        <div class="border-t border-[#E6E8EE]/80"></div>

        <!-- Product Section -->
        <div class="flex flex-col gap-0.5">
          <p class="px-4 text-[11px] font-medium uppercase tracking-[0.1em] text-[#A0A4AD] mb-1.5">
            Product
          </p>
          <button
            v-if="featureBaseEnabled"
            @click="openChangelog"
            class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all text-left w-full hover:bg-[#F0F1F4]"
          >
            <i class="fa-solid fa-bullhorn text-[14px] w-4 text-center"></i>
            What's new
          </button>
          <a
            :href="links.roadmap"
            target="_blank"
            class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all"
          >
            <i class="fa-solid fa-map text-[14px] w-4 text-center"></i>
            Roadmap
          </a>
          <a
            :href="links.feature_requests"
            target="_blank"
            class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all"
          >
            <i class="fa-solid fa-lightbulb text-[14px] w-4 text-center"></i>
            Feature Requests
          </a>
        </div>

        <!-- Help Section -->
        <div class="flex flex-col gap-0.5">
          <p class="px-4 text-[11px] font-medium uppercase tracking-[0.1em] text-[#A0A4AD] mb-1.5">
            Help
          </p>
          <a
            :href="links.help_url"
            target="_blank"
            class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all"
          >
            <i class="fa-solid fa-circle-question text-[14px] w-4 text-center"></i>
            Help Center
          </a>
          <a
            :href="links.api_docs"
            target="_blank"
            class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all"
          >
            <i class="fa-solid fa-code text-[14px] w-4 text-center"></i>
            API Docs
          </a>
          <button
            @click="contactSupport"
            class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all text-left w-full hover:bg-[#F0F1F4]"
          >
            <i class="fa-solid fa-envelope text-[14px] w-4 text-center"></i>
            Contact Support
          </button>
        </div>

        <!-- Upgrade + Profile -->
        <div class="mt-auto flex flex-col gap-4">
          <!-- Upgrade block -->
          <div
            v-if="showUpgradeBanner"
            class="bg-[#FDF6EB] border border-[#f5dfa8] rounded-2xl p-4 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.6)]"
          >
            <p class="text-sm font-semibold text-[#1D1F24] mb-1">
              <i class="fa-solid fa-crown text-[10px] text-[#d97706] mr-1.5"></i>{{ upgradeTitle }}
            </p>
            <p class="text-xs text-[#8E9198] font-medium mb-3 leading-relaxed">
              Unlock advanced analytics, custom domains &amp; more.
            </p>
            <button
              @click="triggerUpgrade"
              class="btn-primary w-full text-white text-sm font-semibold py-2.5 rounded-xl"
            >
              Upgrade Now
            </button>
          </div>

          <!-- User profile block -->
          <UserDropdown v-if="user">
            <template #default="{ user: currentUser }">
              <div
                class="flex items-center gap-3 px-3 py-3 rounded-2xl hover:bg-[#F0F1F4] transition-all cursor-pointer min-w-0"
              >
                <img
                  :src="currentUser.photo_url || 'https://storage.googleapis.com/uxpilot-auth.appspot.com/avatars/avatar-1.jpg'"
                  :alt="currentUser.name"
                  class="w-9 h-9 rounded-full object-cover ring-2 ring-[#E6E8EE] shrink-0"
                />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold leading-none truncate text-[#1D1F24]">{{ currentUser.name }}</p>
                  <p class="text-xs text-[#8E9198] font-medium mt-1 truncate">{{ currentUser.email }}</p>
                </div>
                <i class="fa-solid fa-ellipsis-vertical text-[10px] text-[#A7ABB2] shrink-0"></i>
              </div>
            </template>
          </UserDropdown>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed } from "vue"
import { useRoute } from "vue-router"
import WorkspaceDropdown from "~/components/dashboard/WorkspaceDropdown.vue"
import UserDropdown from "~/components/dashboard/UserDropdown.vue"
import sharaformsConfig from "~/sharaforms.config.js"
import { useAppStore } from "~/stores/app"

const route = useRoute()
const isMobileMenuOpen = ref(false)

const appStore = useAppStore()
const featureBaseEnabled = computed(() => appStore.featureBaseEnabled)

const { current: workspace } = useCurrentWorkspace()
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))
const { can } = useWorkspaceAbilities()
const { openSubscriptionModal } = useAppModals()
const { data: user } = useAuth().user()

const links = sharaformsConfig.links

const activeTab = computed(() => {
  if (route.name !== 'home') return null
  return route.query.tab || 'dashboard'
})

const showUpgradeBanner = computed(() => {
  if (!workspace.value || isSelfHosted.value) return false
  const isProUpgrade = !can('workspaces.multiple')
  const isBusinessUpgrade = can('workspaces.multiple') && !can('multi_user.roles')
  return isProUpgrade || isBusinessUpgrade
})

const upgradeTitle = computed(() => {
  if (!workspace.value) return 'Upgrade to Pro'
  return can('workspaces.multiple') ? 'Upgrade to Business' : 'Upgrade to Pro'
})

const triggerUpgrade = () => {
  usePostHog().logEvent('app_sidebar_upgrade_click')
  const plan = can('workspaces.multiple') ? 'business' : 'pro'
  openSubscriptionModal({
    plan,
    modal_title: `Upgrade to ${plan === 'business' ? 'Business' : 'Pro'} plan`,
  })
}

const openChangelog = () => {
  if (import.meta.server || !window.Featurebase) return
  window.Featurebase("manually_open_changelog_popup")
}

const contactSupport = () => {
  window.location.href = `mailto:${links.contact_email}`
}
</script>

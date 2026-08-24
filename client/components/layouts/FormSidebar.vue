<template>
  <div>
    <!-- Mobile top bar -->
    <div class="sm:hidden fixed top-0 left-0 right-0 h-14 bg-[var(--sf-bg-surface)] border-b border-[var(--sf-border-card)] z-50 px-4 flex items-center justify-between">
      <p class="text-[18px] font-bold text-[var(--sf-text-primary)] tracking-tight">SharaForms<span class="text-[var(--sf-coral-500)]">.</span></p>
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
      class="sm:hidden fixed inset-0 bg-[var(--sf-text-primary)]/20 backdrop-blur-xs z-50"
      @click="isMobileMenuOpen = false"
    ></div>

    <!-- Sidebar Element -->
    <aside
      :class="[
        'sidebar w-[260px] shrink-0 flex flex-col fixed top-0 left-0 h-full overflow-y-auto z-[60] border-r border-[var(--sf-border-card)] transition-transform duration-300 ease-in-out',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full sm:translate-x-0'
      ]"
    >
      <div class="flex flex-col h-full px-6 py-8 gap-7">
        <!-- Brand + Back -->
        <div class="px-4 flex items-center justify-between">
          <NuxtLink
            to="/"
            class="whitespace-nowrap select-none text-[20px] font-bold leading-none tracking-tight text-[var(--sf-text-primary)] hover:opacity-80 transition-opacity"
          >
            SharaForms<span class="text-[var(--sf-coral-500)]">.</span>
          </NuxtLink>
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

        <!-- Form Status -->
        <div v-if="form" class="px-4">
          <FormStatusBadges size="xs" :form="form" />
        </div>

        <!-- Main Navigation -->
        <nav class="flex flex-col gap-1">
          <!-- Back to Dashboard -->
          <NuxtLink
            :to="{ name: 'home' }"
            class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all text-[var(--sf-text-body)] hover:bg-[var(--sf-nav-hover-bg)] hover:text-[var(--sf-text-primary)]"
            @click="isMobileMenuOpen = false"
          >
            <i class="fa-solid fa-arrow-left text-[14px] w-4 text-center"></i>
            <span>Dashboard</span>
          </NuxtLink>

          <!-- Divider between back and form nav -->
          <div class="border-t border-[var(--sf-border-card)]/80 my-1"></div>

          <!-- Form section nav items -->
          <NuxtLink
            v-for="item in formNavigationItems"
            :key="item.label"
            :to="item.to"
            class="relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all duration-150"
            :class="item.active
              ? 'bg-[var(--sf-nav-active-bg)] font-semibold text-[var(--sf-text-primary)]'
              : 'text-[var(--sf-text-body)] hover:bg-[var(--sf-nav-hover-bg)] hover:text-[var(--sf-text-primary)]'"
            @click="isMobileMenuOpen = false"
          >
            <span
              v-if="item.active"
              class="nav-indicator absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-full bg-[var(--sf-coral-500)]"
            ></span>
            <i :class="[item.icon, 'text-[14px] w-4 text-center', item.active ? 'text-[var(--sf-coral-500)]' : '']"></i>
            <span>{{ item.label }}</span>
          </NuxtLink>
        </nav>

        <!-- Divider -->
        <div class="border-t border-[var(--sf-border-card)]/80"></div>

        <!-- Product Section -->
        <div class="flex flex-col gap-0.5">
          <p class="px-4 text-[11px] font-medium uppercase tracking-[0.1em] text-[var(--sf-text-label)] mb-1.5">
            Product
          </p>
          <a
            :href="links.changelog_url"
            target="_blank"
            class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all"
          >
            <i class="fa-solid fa-bullhorn text-[14px] w-4 text-center"></i>
            What's new
          </a>
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
          <p class="px-4 text-[11px] font-medium uppercase tracking-[0.1em] text-[var(--sf-text-label)] mb-1.5">
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
            class="nav-item flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all text-left w-full hover:bg-[var(--sf-nav-hover-bg)]"
          >
            <i class="fa-solid fa-envelope text-[14px] w-4 text-center"></i>
            Contact Support
          </button>
        </div>

        <!-- User Profile -->
        <div class="mt-auto flex flex-col gap-4">
          <UserDropdown v-if="user">
            <template #default="{ user: currentUser }">
              <div
                class="flex items-center gap-3 px-3 py-3 rounded-2xl hover:bg-[var(--sf-nav-hover-bg)] transition-all cursor-pointer min-w-0"
              >
                <img
                  :src="currentUser.photo_url || 'https://storage.googleapis.com/uxpilot-auth.appspot.com/avatars/avatar-1.jpg'"
                  :alt="currentUser.name"
                  class="w-9 h-9 rounded-full object-cover ring-2 ring-[var(--sf-border-card)] shrink-0"
                />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold leading-none truncate text-[var(--sf-text-primary)]">{{ currentUser.name }}</p>
                  <p class="text-xs text-[var(--sf-text-caption)] font-medium mt-1 truncate">{{ currentUser.email }}</p>
                </div>
                <i class="fa-solid fa-ellipsis-vertical text-[10px] text-[var(--sf-text-disabled)] shrink-0"></i>
              </div>
            </template>
          </UserDropdown>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import UserDropdown from "~/components/dashboard/UserDropdown.vue"
import FormStatusBadges from "~/components/open/forms/components/FormStatusBadges.vue"
import sharaformsConfig from "~/sharaforms.config.js"

const props = defineProps({
  form: {
    type: Object,
    required: false
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const route = useRoute()
const isMobileMenuOpen = ref(false)

const { current: workspace } = useCurrentWorkspace()
const { data: user } = useAuth().user()

const links = sharaformsConfig.links

function isActiveRoute(routeName) {
  if (!routeName) return false
  return route.name === routeName
}

const formNavigationItems = computed(() => {
  const slug = props.form?.slug
  const items = [
    {
      label: 'Submissions',
      icon: 'fa-solid fa-table-list',
      to: slug ? { name: 'forms-slug-show-submissions', params: { slug } } : '#',
      active: isActiveRoute('forms-slug-show-submissions')
    },
  ]

  if (!workspace.value?.is_readonly && slug) {
    items.push({
      label: 'Integrations',
      icon: 'fa-solid fa-puzzle-piece',
      to: { name: 'forms-slug-show-integrations', params: { slug } },
      active: isActiveRoute('forms-slug-show-integrations')
    })
    items.push({
      label: 'PDF Templates',
      icon: 'fa-solid fa-file-pdf',
      to: { name: 'forms-slug-show-pdf-templates', params: { slug } },
      active: isActiveRoute('forms-slug-show-pdf-templates')
    })
  }

  items.push(
    {
      label: 'Analytics',
      icon: 'fa-solid fa-chart-simple',
      to: slug ? { name: 'forms-slug-show-stats', params: { slug } } : '#',
      active: isActiveRoute('forms-slug-show-stats')
    },
    {
      label: 'Summary',
      icon: 'fa-solid fa-chart-pie',
      to: slug ? { name: 'forms-slug-show-summary', params: { slug } } : '#',
      active: isActiveRoute('forms-slug-show-summary')
    },
    {
      label: 'Share',
      icon: 'fa-solid fa-share-nodes',
      to: slug ? { name: 'forms-slug-show-share', params: { slug } } : '#',
      active: isActiveRoute('forms-slug-show-share')
    }
  )

  return items
})

const contactSupport = () => {
  window.location.href = `mailto:${links.contact_email}`
}
</script>

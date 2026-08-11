<template>
  <BaseSidebar ref="sidebar">
    <!-- Header Slot -->
    <template #header>
      <!-- Workspace Dropdown -->
      <div class="grow min-w-0">
        <WorkspaceDropdown>
          <template #default="{ workspace }">
            <button
              v-if="workspace"
              aria-label="Workspace menu"
              class="group flex w-full items-center gap-2.5 px-2.5 py-2 rounded-lg hover:bg-neutral-100 transition-colors min-w-0 text-left"
            >
              <WorkspaceIcon :workspace="workspace" size="size-8" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-neutral-900 truncate">
                  {{ workspace.name }}
                </p>
              </div>
              <UIcon
                name="i-lucide-chevron-down"
                class="h-3.5 w-3.5 shrink-0 text-neutral-400 group-hover:text-neutral-500"
              />
            </button>
          </template>
        </WorkspaceDropdown>
      </div>
    </template>

    <!-- Navigation Slot -->
    <template #navigation>
      <div 
        v-for="(section, index) in navigationSections" 
        :key="section.name || 'main'"
        :class="[
          index !== navigationSections.length - 1 ? 'mb-6' : '',
          // Push Product and Help sections to bottom
          index === 1 ? 'mt-auto' : ''
        ]"
      >
        <!-- Section Title (if exists) -->
        <h3 
          v-if="section.name"
          class="select-none text-[11px] font-semibold uppercase tracking-wider text-neutral-400/90 mb-1.5 px-2.5"
        >
          {{ section.name }}
        </h3>
        
        <!-- Section Items -->
        <NavigationList
          :items="section.items"
          tracking-name="sidebar_nav_click"
          :tracking-properties="(item) => ({ label: item.label })"
          @item-click="handleItemClick"
        />
      </div>
    </template>

    <!-- Footer Slot -->
    <template #footer>
      <div class="flex flex-col gap-2">
        <UserDropdown>
          <template #default="{ user }">
            <button
              v-if="user"
              aria-label="User menu"
              class="group flex w-full items-center gap-2.5 px-2.5 py-2 rounded-lg hover:bg-neutral-100 transition-colors min-w-0 text-left"
            >
              <img
                :src="user.photo_url"
                :alt="user.name"
                class="rounded-full size-8 shrink-0"
              >
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-neutral-900 truncate">
                  {{ user.name }}
                </p>
                <p class="text-[11px] text-neutral-400 truncate">
                  {{ user.email }}
                </p>
              </div>
              <UIcon
                name="i-lucide-chevron-up"
                class="h-3.5 w-3.5 shrink-0 text-neutral-400 group-hover:text-neutral-500"
              />
            </button>
          </template>
        </UserDropdown>
        <p class="text-center text-[11px] text-neutral-400">
          <NuxtLink class="font-semibold hover:text-neutral-500" :to="{ name: 'home' }">
            SharaForms
          </NuxtLink>
          <span v-if="version"> v{{ version }}</span>
        </p>
      </div>
    </template>
  </BaseSidebar>
</template>

<script setup>
import BaseSidebar from "~/components/layouts/BaseSidebar.vue"
import WorkspaceDropdown from "~/components/dashboard/WorkspaceDropdown.vue"
import WorkspaceIcon from "~/components/workspaces/WorkspaceIcon.vue"
import UserDropdown from "~/components/dashboard/UserDropdown.vue"
import NavigationList from "~/components/global/NavigationList.vue"
import { useSharedNavigation } from "~/composables/components/useSharedNavigation"

const route = useRoute()
const sidebar = ref(null)

const version = computed(() => useFeatureFlag('version'))

const { sharedNavigationSections, createNavItem } = useSharedNavigation()

const { current: workspace } = useCurrentWorkspace()
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))
const { can } = useWorkspaceAbilities()
const { openSubscriptionModal } = useAppModals()

// Check if current route matches a prefix
function isActiveRoute(prefix) {
  if (!prefix) return false
  return route.name?.startsWith(prefix)
}

// Navigation sections structure
const navigationSections = computed(() => [
  // Section 1: Main navigation (no name)
  {
    name: null,
    items: [
      createNavItem({
        label: 'Home', 
        icon: 'i-heroicons:home',
        to: { name: 'home' },
        active: isActiveRoute('home')
      }),
      createNavItem({
        label: 'Templates',
        icon: 'i-heroicons:document-duplicate',
        to: { name: 'templates-my-templates' },
        active: isActiveRoute('templates')
      }),
      // Show upgrade for non-pro users
      ...(workspace.value && !can('workspaces.multiple') && !isSelfHosted.value ? [createNavItem({
        label: 'Upgrade to Pro',
        icon: 'i-heroicons:arrow-up-circle',
          onClick: () => {
          usePostHog().logEvent('app_sidebar_upgrade_click')
          openSubscriptionModal({
            plan: 'pro',
            modal_title: 'Upgrade to Pro plan',
          })
        },
        color: 'primary'
      })] : []),
      ...(workspace.value && can('workspaces.multiple') && !can('multi_user.roles') && !isSelfHosted.value ? [createNavItem({
        label: 'Upgrade to Business',
        icon: 'i-heroicons:arrow-up-circle',
        onClick: () => {
          usePostHog().logEvent('app_sidebar_upgrade_click')
          openSubscriptionModal({
            plan: 'business',
            modal_title: 'Upgrade to Business plan',
          })
        },
        color: 'primary'
      })] : [])
    ]
  },
  // Add shared navigation sections (Product and Help)
  ...sharedNavigationSections.value
])

function handleItemClick(_item) {
  if (sidebar.value && sidebar.value.isMobileMenuOpen) {
    sidebar.value.isMobileMenuOpen = false
  }
}
</script> 

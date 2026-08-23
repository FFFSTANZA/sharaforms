<template>
  <UDropdownMenu
    v-if="user"
    :items="dropdownItems"
    :ui="{
      content: 'w-56 p-1 rounded-xl border-[var(--sf-border-card)] shadow-[var(--sf-shadow-dropdown)]',
    }"
    arrow
  >
    <slot :user="user" />

    <template #user-info>
      <div class="px-2 py-2 flex items-center gap-2.5">
        <div class="relative shrink-0">
          <img
            :src="user.photo_url"
            :alt="user.name"
            class="w-8 h-8 rounded-full object-cover ring-1.5 ring-[var(--sf-border-card)]"
          >
          <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-[var(--sf-green)] ring-1.5 ring-white" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-semibold text-[var(--sf-text-primary)] truncate">
            {{ user.name }}
          </p>
          <p class="text-[11px] text-[var(--sf-text-caption)] font-medium truncate">
            {{ user.email }}
          </p>
        </div>
      </div>
    </template>

    <template #settings>
      <button
        class="group w-full flex items-center gap-2 px-2 py-1.5 rounded-lg text-left transition-all hover:bg-[var(--sf-nav-hover-bg)] cursor-pointer"
        @click="openUserSettings('account')"
      >
        <i class="fa-solid fa-gear text-[12px] w-3.5 text-center text-[var(--sf-text-secondary)] group-hover:text-[var(--sf-text-primary)] transition-colors shrink-0" />
        <span class="flex-1 text-[12px] font-medium text-[var(--sf-text-body)] group-hover:text-[var(--sf-text-primary)] transition-colors">
          Settings
        </span>
        <i class="fa-solid fa-chevron-right text-[8px] text-[var(--sf-text-disabled)] group-hover:text-[var(--sf-text-body)] transition-colors" />
      </button>
    </template>

    <template #admin>
      <button
        class="group w-full flex items-center gap-2 px-2 py-1.5 rounded-lg text-left transition-all hover:bg-[var(--sf-nav-hover-bg)] cursor-pointer"
        @click="router.push({ name: 'admin' })"
      >
        <i class="fa-solid fa-shield-halved text-[12px] w-3.5 text-center text-[var(--sf-text-secondary)] group-hover:text-[var(--sf-text-primary)] transition-colors shrink-0" />
        <span class="flex-1 text-[12px] font-medium text-[var(--sf-text-body)] group-hover:text-[var(--sf-text-primary)] transition-colors">
          Admin
        </span>
        <i class="fa-solid fa-chevron-right text-[8px] text-[var(--sf-text-disabled)] group-hover:text-[var(--sf-text-body)] transition-colors" />
      </button>
    </template>

    <template #logout>
      <button
        class="group w-full flex items-center gap-2 px-2 py-1.5 rounded-lg text-left transition-all hover:bg-[var(--sf-nav-hover-bg)] cursor-pointer"
        @click="logout"
      >
        <i class="fa-solid fa-arrow-right-from-bracket text-[12px] w-3.5 text-center text-[var(--sf-text-secondary)] group-hover:text-[var(--sf-coral-500)] transition-colors shrink-0" />
        <span class="flex-1 text-[12px] font-medium text-[var(--sf-text-body)] group-hover:text-[var(--sf-coral-500)] transition-colors">
          Logout
        </span>
      </button>
    </template>
  </UDropdownMenu>
</template>

<script setup>
import { computed } from "vue"

const { openUserSettings } = useAppModals()
const router = useRouter()

const { user: userQuery, logout: logoutMutationFactory } = useAuth()
const { data: user } = userQuery()

// Create logout mutation
const logoutMutation = logoutMutationFactory()

const logout = () => {
  // Logout mutation handles cache clearing and navigation automatically
  logoutMutation.mutateAsync()
}

const dropdownItems = computed(() => {
  if (!user.value) return []

  const items = []

  // User info header
  items.push([
    {
      slot: 'user-info',
      type: 'label'
    }
  ])

  // Navigation items
  const navItems = []

  // Settings
  navItems.push({
    slot: 'settings',
    onSelect: () => openUserSettings('account')
  })

  // Admin - only show for moderators
  if (user.value.moderator) {
    navItems.push({
      slot: 'admin',
      onSelect: () => router.push({ name: 'admin' })
    })
  }

  if (navItems.length > 0) {
    items.push(navItems)
  }

  // Logout
  items.push([
    {
      slot: 'logout',
      onSelect: logout
    }
  ])

  return items
})
</script>

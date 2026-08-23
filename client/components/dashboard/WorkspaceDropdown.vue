<template>
  <div>
    <UPopover
      v-model:open="isDropdownOpen"
      v-if="user && workspaces && workspaces.length >= 1"
      :content="content"
      arrow
    >
    <slot :workspace="workspace" />
    
    <template #content>
      <div class="w-64 flex flex-col">
        <!-- Workspace Info Header -->
        <div v-if="workspace" class="p-4 pb-3.5 border-b border-[var(--sf-border-divider)]">
          <div class="flex items-center gap-3">
            <div class="relative shrink-0">
              <WorkspaceIcon size="size-10" :workspace="workspace" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-[var(--sf-text-primary)] truncate">
                {{ workspace.name }}
              </p>
              <div class="mt-1 flex items-center gap-1.5 min-w-0">
                <span class="sf-plan-badge shrink-0">
                  <i class="fa-solid fa-crown text-[8px]" />
                  {{ workspacePlanText }}
                </span>
              </div>
            </div>
          </div>
          <p class="mt-2 text-[11px] font-medium text-[var(--sf-text-caption)] flex items-center gap-1.5">
            <i class="fa-solid fa-user-group text-[9px]" />
            {{ memberCountText }}
          </p>
          <div class="mt-3 flex items-center gap-2">
            <button
              class="group flex items-center gap-2 px-3 py-1.5 rounded-lg border border-[var(--sf-border-button)] bg-white text-[12px] font-medium text-[var(--sf-text-body)] hover:bg-[var(--sf-nav-hover-bg)] hover:border-[var(--sf-hover-border)] transition-all cursor-pointer"
              @click="openSettings"
            >
              <i class="fa-solid fa-gear text-[11px] text-[var(--sf-text-secondary)] group-hover:text-[var(--sf-text-primary)] transition-colors" />
              Settings
            </button>
            <button
              v-if="workspace.is_admin"
              class="group flex items-center gap-2 px-3 py-1.5 rounded-lg border border-[var(--sf-border-button)] bg-white text-[12px] font-medium text-[var(--sf-text-body)] hover:bg-[var(--sf-nav-hover-bg)] hover:border-[var(--sf-hover-border)] transition-all cursor-pointer"
              @click="openInviteUserModal"
            >
              <i class="fa-solid fa-user-plus text-[11px] text-[var(--sf-text-secondary)] group-hover:text-[var(--sf-text-primary)] transition-colors" />
              Invite
            </button>
          </div>
        </div>

        <!-- Workspace List (with ScrollableContainer) -->
        <div v-if="workspaces.length > 1" class="p-1.5">
          <p class="px-2.5 pt-1.5 pb-1 text-[11px] font-medium uppercase tracking-[0.08em] text-[var(--sf-text-label)]">
            Workspaces
          </p>
          <ScrollableContainer max-height-class="max-h-64" top-fade-height="h-10" bottom-fade-height="h-10">
            <div class="flex flex-col gap-0.5">
              <button
                v-for="worksp in workspaces"
                :key="worksp.id"
                class="group w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-left transition-all hover:bg-[var(--sf-nav-hover-bg)] cursor-pointer"                  :class="workspace?.id === worksp?.id ? 'bg-[var(--sf-nav-active-bg)]' : ''"
                @click="switchWorkspace(worksp)"
              >
                <WorkspaceIcon :workspace="worksp" size="size-6" />
                <span
                  class="flex-1 min-w-0 truncate text-[13px] font-medium transition-colors"
                  :class="workspace?.id === worksp?.id ? 'text-[var(--sf-text-primary)] font-semibold' : 'text-[var(--sf-text-body)] group-hover:text-[var(--sf-text-primary)] group-hover:font-semibold'"
                >
                  {{ worksp.name }}
                </span>
                <span
                  v-if="workspace?.id === worksp?.id"
                  class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-[var(--sf-coral-500)] text-white shrink-0"
                >
                  <i class="fa-solid fa-check text-[9px]" />
                </span>
              </button>
            </div>
          </ScrollableContainer>
        </div>

        <!-- Create Workspace Action -->
        <div class="border-t border-[var(--sf-border-divider)] p-1.5">
          <button
            class="group w-full flex items-center gap-2.5 p-2 rounded-lg text-left transition-all hover:bg-[var(--sf-nav-hover-bg)] cursor-pointer"
            @click="createNewWorkspace"
          >
            <i class="fa-solid fa-plus text-[13px] w-4 text-center text-[var(--sf-text-secondary)] group-hover:text-[var(--sf-text-primary)] transition-colors shrink-0" />
            <span class="text-[13px] font-medium text-[var(--sf-text-body)] group-hover:text-[var(--sf-text-primary)] group-hover:font-semibold transition-colors">
              Create Workspace
            </span>
          </button>
        </div>
      </div>
    </template>
    </UPopover>

    <!-- Create Workspace Modal -->
    <CreateWorkspaceModal
      v-model="showCreateModal"
      @created="onWorkspaceCreated"
      @close="showCreateModal = false"
    />

    <WorkspacesSettingsInviteUser
      v-model="showInviteUserModal"
      @user-added="onUserAdded"
    />
  </div>
</template>

<script setup>
import { computed, ref } from "vue"
import WorkspaceIcon from "~/components/workspaces/WorkspaceIcon.vue"
import CreateWorkspaceModal from "~/components/workspaces/CreateWorkspaceModal.vue"
import WorkspacesSettingsInviteUser from '~/components/workspaces/settings/InviteUser.vue'
import ScrollableContainer from '~/components/dashboard/ScrollableContainer.vue'


defineProps({
  content: {
    type: Object,
    default: () => ({
      side: 'bottom',
      align: 'start'
    })
  }
})

const { openSubscriptionModal } = useAppModals()
const { getTierDisplayName, userCanAccessTier } = useBillingUpsell()
const router = useRouter()
const route = useRoute()
const appStore = useAppStore()

const { data: user } = useAuth().user()
const { data: workspaces } = useWorkspaces().list()
const { current: workspace } = useCurrentWorkspace()

// Extract composable methods in setup context
const { invalidateAll: invalidateForms } = useForms()
const { invalidateAll: invalidateWorkspaces } = useWorkspaces()

// Modal state
const showCreateModal = ref(false)
const showInviteUserModal = ref(false)

// Dropdown state
const isDropdownOpen = ref(false)

// Computed text for workspace plan
const workspacePlanText = computed(() => {
  if (!workspace.value) return ''
  const name = getTierDisplayName(workspace.value.plan_tier)
  return name === 'Free' ? 'Free' : name
})

// Computed text for member count
const memberCountText = computed(() => {
  if (!workspace.value || !workspace.value.users_count) return '1 member'
  const count = workspace.value.users_count
  return count === 1 ? '1 member' : `${count} members`
})

const switchWorkspace = (workspaceToSwitch) => {
  if (workspaceToSwitch.id === workspace.value.id) {
    return
  }
  appStore.setCurrentId(workspaceToSwitch.id)
  invalidateForms()
  
  if (route.name !== "home") {
    router.push({ name: "home" })
  }
}

const createNewWorkspace = () => {
  if (!userCanAccessTier('pro') && workspaces.value.length >= 1) {
    openSubscriptionModal({ modal_title: 'Upgrade to create additional workspaces', modal_description: 'Try our Pro plan for free today, and unlock all of our features such as collaboration, multiple workspaces, custom domains, forms analytics, integrations, and more!' })
    return
  }
  showCreateModal.value = true
}

const onWorkspaceCreated = (_newWorkspace) => {
  // Member count is now included in workspace data automatically
}

const onUserAdded = () => {
  invalidateWorkspaces()
}

const openSettings = () => {
  isDropdownOpen.value = false
  useAppModals().openWorkspaceSettings('information')
}

const openInviteUserModal = () => {
  isDropdownOpen.value = false

  if (workspace.value && !workspace.value?.features?.includes('workspaces.multiple')) {
    openSubscriptionModal({ modal_title: 'Upgrade to invite users to your workspace' })
    return
  }
  
  showInviteUserModal.value = true
}


</script>

<style></style>

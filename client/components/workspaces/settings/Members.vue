<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>Members</h2>
      <p>Manage your workspace members and their roles.</p>
    </div>

    <section class="sf-card">
      <!-- Card header -->
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 border-b border-[#ECEEF2] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
        <div class="flex items-center gap-3">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
            <i class="fa-solid fa-user-group" />
          </span>
          <div>
            <h3 class="text-[15px] font-semibold text-[#1D1F24]">Workspace Members</h3>
            <p class="text-xs text-[#8E9198] font-medium mt-0.5">
              Manage your workspace members and their roles.
            </p>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <UButton
            v-if="canManageMembers"
            label="Invite User"
            icon="i-lucide-user-plus"
            :loading="isLoadingData"
            class="btn-primary"
            @click="showInviteUserModal = true"
          />
        </div>
      </div>

      <div class="p-5 sm:p-6">
        <UTable
          class="w-full"
          :loading="isLoadingData"
          :data="combinedUsers"
          :columns="tableColumns"
          v-model:column-pinning="columnPinning"
        >
          <template #name-cell="{ row: { original: item } }">
            <div class="flex items-center gap-3 min-w-0">
              <span
                v-if="item.type === 'user'"
                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[var(--sf-bg-subtle)] text-[var(--sf-text-body)] text-xs font-semibold shrink-0"
              >
                {{ initials(item.name) }}
              </span>
              <span
                v-else
                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[var(--sf-bg-subtle)] text-[var(--sf-text-body)] text-xs font-semibold shrink-0"
              >
                <i class="fa-solid fa-envelope-open-text text-[10px]" />
              </span>
              <span class="font-semibold text-[#1D1F24] truncate">
                {{ item.name }}
                <span
                  v-if="item.is_current_user"
                  class="ml-1 inline-flex items-center rounded-full bg-[var(--sf-green-light)] px-1.5 py-0.5 text-[10px] font-semibold text-[var(--sf-green)]"
                >
                  You
                </span>
              </span>
            </div>
          </template>

          <template #email-cell="{ row: { original: item } }">
            <span class="text-[13px] text-[#565A62]">{{ item.email }}</span>
          </template>

          <template #status-cell="{ row: { original: item } }">
            <span
              v-if="item.type === 'user'"
              class="pill-live inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold"
            >
              <i class="fa-solid fa-circle-check text-[9px]"></i>
              Active
            </span>
            <span
              v-else
              class="pill-draft inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold"
            >
              <i class="fa-solid fa-hourglass-half text-[9px]"></i>
              {{ item.status === 'accepted' ? 'Accepted' : 'Invited' }}
            </span>
          </template>

          <template #role-cell="{ row: { original: item } }">
            <span
              v-if="item.type === 'user'"
              class="text-[13px] font-medium capitalize text-[#383B41]"
            >
              {{ item.role }}
            </span>
            <span v-else class="text-[13px] text-[#A0A4AD]">—</span>
          </template>

          <template #actions-cell="{ row: { original: item } }">
            <div class="space-x-2 flex justify-center">
              <template v-if="item.type == 'user'">
                <p
                  v-if="item.is_current_user"
                  class="text-[#8E9198] text-center text-sm"
                >
                  -
                </p>
                <div v-else class="flex items-center gap-1">
                  <UTooltip text="Edit user">
                    <UButton
                      icon="i-lucide-square-pen"
                      color="primary"
                      variant="soft"
                      size="xs"
                      square
                      @click="editUser(item)"
                    />
                  </UTooltip>
                  <UTooltip text="Remove user">
                    <UButton
                      v-if="item.type == 'user'"
                      color="error"
                      variant="soft"
                      icon="i-lucide-trash-2"
                      size="xs"
                      square
                      :loading="removeMutation.isPending.value"
                      @click="removeUserHandler(item)"
                    />
                  </UTooltip>
                </div>
              </template>
              <div
                v-else-if="item.type == 'invitee'"
                class="flex items-center gap-1"
              >
                <UTooltip text="Resend Invite">
                  <UButton
                    icon="i-lucide-mail"
                    color="neutral"
                    variant="soft"
                    size="xs"
                    :loading="resendMutation.isPending.value"
                    @click="resendInviteHandler(item)"
                  />
                </UTooltip>
                <UTooltip text="Cancel Invite">
                  <UButton
                    icon="i-lucide-x"
                    color="error"
                    variant="soft"
                    size="xs"
                    :loading="cancelMutation.isPending.value"
                    @click="cancelInviteHandler(item)"
                  />
                </UTooltip>
              </div>
            </div>
          </template>
        </UTable>
      </div>
    </section>

    <UModal
      v-model:open="showEditUserModal"
      @close="showEditUserModal = false"
      title="Edit User Role"
    >
      <template #body>
        <form
          @submit.prevent="updateUserRole"
        >
          <div>
            <FlatSelectInput
              :form="editUserForm"
              name="role"
              :label="'New Role for '+selectedUser.name"
              :options="[
                { name: 'User', value: 'user' },
                { name: 'Admin', value: 'admin' },
                { name: 'Read Only', value: 'readonly' },
              ]"
              option-key="value"
              display-key="name"
            />
          </div>

          <div class="flex justify-center mt-4">
            <UButton
              type="submit"
              :loading="updateMutation.isPending.value"
              class="btn-primary"
            >
              Update
            </UButton>
          </div>
        </form>
      </template>
    </UModal>

    <WorkspacesSettingsInviteUser
      v-if="canManageMembers"
      v-model="showInviteUserModal"
      @user-added="handleUserAdded"
    />
  </div>
</template>

<script setup>
import WorkspacesSettingsInviteUser from '~/components/workspaces/settings/InviteUser.vue'

// Composables
const {
  users,
  invites,
  updateUserRole: updateUserRoleMutation,
  removeUser: removeUserMutation,
  resendInvite: resendInviteMutation,
  cancelInvite: cancelInviteMutation
} = useWorkspaceUsers()

const alert = useAlert()
const { current: workspace, currentId: workspaceId } = useCurrentWorkspace()
const auth = useAuth()

// Get current user
const { data: user } = auth.user()
const canManageMembers = computed(() => workspace?.value?.is_admin ?? false)

// Reactive state
const showEditUserModal = ref(false)
const showInviteUserModal = ref(false)
const selectedUser = ref(null)
const editUserForm = useForm({
  role: 'user'
})

// Create all mutations during setup
const updateMutation = updateUserRoleMutation(workspaceId)
const removeMutation = removeUserMutation(workspaceId)
const resendMutation = resendInviteMutation(workspaceId)
const cancelMutation = cancelInviteMutation(workspaceId)

// Column pinning state
const columnPinning = ref({
  left: [],
  right: ['actions']
})

// Get workspace users and invites reactively
const { data: workspaceUsers, isLoading: isLoadingUsers } = users(workspaceId)
const { data: workspaceInvites, isLoading: isLoadingInvites } = invites(workspaceId, {
  enabled: computed(() => !!workspaceId.value && canManageMembers.value)
})

// Combined loading state
const isLoadingData = computed(() => (isLoadingUsers?.value || (canManageMembers.value && isLoadingInvites?.value)) ?? false)

// Transform and combine data reactively
const combinedUsers = computed(() => {
  const users = workspaceUsers?.value || []
  const invites = workspaceInvites?.value || []

  // Transform users
  const transformedUsers = users.map(d => ({
    ...d,
    id: d.id,
    is_current_user: d.id === user?.value?.id,
    name: d.name,
    email: d.email,
    status: 'accepted',
    role: d.pivot?.role,
    type: 'user'
  }))

  // Transform invites (exclude accepted ones)
  const transformedInvites = invites
    .filter(i => i.status !== 'accepted')
    .map(i => ({
      ...i,
      name: 'Invitee',
      email: i.email,
      status: i.status,
      type: 'invitee'
    }))

  return [...transformedUsers, ...transformedInvites]
})

// Table columns configuration
const tableColumns = computed(() => {
  return [
    {
      id: 'name',
      accessorKey: 'name',
      header: 'Name'
    },
    {
      id: 'email',
      accessorKey: 'email',
      header: 'Email'
    },
    {
      id: 'status',
      accessorKey: 'status',
      header: 'Status'
    },
    {
      id: 'role',
      accessorKey: 'role',
      header: 'Role'
    },
    ...(canManageMembers.value ? [
      {
        id: 'actions',
        header: '',
      }] : [])
  ]
})

const initials = (name) => {
  return (name || '?')
    .split(' ')
    .map(part => part[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

// User management handlers
const editUser = (user) => {
  if (!canManageMembers.value) return

  selectedUser.value = user
  editUserForm.role = selectedUser.value.pivot?.role || selectedUser.value.role
  showEditUserModal.value = true
}

const updateUserRole = () => {
  if (!workspaceId.value || !selectedUser.value?.id || !canManageMembers.value) return

  updateMutation.mutateAsync({
    userId: selectedUser.value.id,
    data: { role: editUserForm.role }
  }).then((data) => {
    alert.success(data.message || 'User role updated successfully')
    showEditUserModal.value = false
  }).catch((error) => {
    alert.error(error.response?.data?.message || "There was an error updating user role")
  })
}

const removeUserHandler = (user) => {
  if (!workspaceId.value || !canManageMembers.value) return

  alert.confirm("Do you really want to remove " + user.name + " from this workspace?", () => {
    removeMutation.mutateAsync(user.id).then(() => {
      alert.success("User successfully removed.")
    }).catch((error) => {
      alert.error(error.response?.data?.message || "There was an error removing user")
    })
  })
}

// Invite management handlers
const resendInviteHandler = (invite) => {
  if (!workspaceId.value || !canManageMembers.value) return

  alert.confirm("Do you really want to resend invite email to this user?", () => {
    resendMutation.mutateAsync(invite.id).then(() => {
      alert.success("Invitation resent successfully.")
    }).catch((error) => {
      alert.error(error.response?.data?.message || "Failed to resend invitation")
    })
  })
}

const cancelInviteHandler = (invite) => {
  if (!workspaceId.value || !canManageMembers.value) return

  alert.confirm("Do you really want to cancel this user's invitation to this workspace?", () => {
    cancelMutation.mutateAsync(invite.id).then(() => {
      alert.success("Invitation cancelled successfully.")
    }).catch((error) => {
      alert.error(error.response?.data?.message || "Failed to cancel invitation")
    })
  })
}

// Handle user added event from invite modal
const handleUserAdded = () => {
  // TanStack Query will automatically update the cache, no manual refresh needed
  showInviteUserModal.value = false
}
</script>
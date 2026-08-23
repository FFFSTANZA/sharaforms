<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>Information</h2>
      <p>Update your workspace name and icon.</p>
    </div>

    <!-- Workspace Information -->
    <section class="sf-card sf-card-pad">
      <div class="flex items-start gap-3 mb-6">
        <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
          <i class="fa-solid fa-building" />
        </span>
        <div>
          <h3 class="text-[15px] font-semibold text-[#1D1F24]">Workspace Information</h3>
          <p class="text-[13px] text-[#6E7278] mt-0.5">
            Update your workspace information.
          </p>
        </div>
      </div>

      <VForm @submit.prevent="updateProfile" size="sm">
        <div class="max-w-sm">
          <TextInput
            :disabled="workspace.is_readonly"
            :form="workspaceForm"
            name="name"
            label="Workspace Name"
            placeholder="My Workspace"
            :required="true"
          />
          <TextInput
            :disabled="workspace.is_readonly"
            :form="workspaceForm"
            name="emoji"
            label="Emoji (optional)"
            placeholder="Emoji"
            help="Choose an emoji to represent your workspace"
          />
        </div>

        <div class="mt-6">
          <UButton
            :disabled="workspace.is_readonly"
            type="submit"
            :loading="workspaceForm.busy"
            color="primary"
            class="btn-primary"
          >
            Save Changes
          </UButton>
        </div>
      </VForm>
    </section>

    <!-- Danger Zone -->
    <section class="sf-danger-zone sf-card-pad">
      <div v-if="workspace.is_admin" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--coral">
            <i class="fa-solid fa-triangle-exclamation" />
          </span>
          <div>
            <h3 class="sf-danger-title text-[14px] font-semibold">Delete Workspace</h3>
            <p class="mt-1 text-[13px] text-[#6E7278] max-w-md">
              This will permanently delete your entire workspace. All forms created in this workspace will be removed. This cannot be undone.
            </p>
          </div>
        </div>
        <UButton
          color="error"
          :loading="removeMutation.isPending.value"
          @click="confirmDeleteWorkspace"
          class="shrink-0"
        >
          Delete workspace
        </UButton>
      </div>

      <div v-else class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--amber">
            <i class="fa-solid fa-arrow-right-from-bracket" />
          </span>
          <div>
            <h3 class="sf-danger-title text-[14px] font-semibold">Leave Workspace</h3>
            <p class="mt-1 text-[13px] text-[#6E7278] max-w-md">
              This will remove you from the workspace. You will lose access to all forms in this workspace.
            </p>
          </div>
        </div>
        <UButton
          color="error"
          :loading="leaveMutation.isPending.value"
          @click="leaveWorkSpace"
          class="shrink-0"
        >
          Leave workspace
        </UButton>
      </div>
    </section>
  </div>
</template>

<script setup>
const { update, remove, leave } = useWorkspaces()

const alert = useAlert()
const { closeWorkspaceSettings } = useAppModals()
const router = useRouter()

const { current: workspace } = useCurrentWorkspace()

const updateMutation = update(workspace.value.id)
const removeMutation = remove()
const leaveMutation = leave()

// Workspace form
const workspaceForm = useForm({
  name: '',
  emoji: ''
})

// Update profile
const updateProfile = () => {
  workspaceForm.mutate(updateMutation).then(() => {
    useAlert().success('Workspace information successfully updated!')
  }).catch((error) => {
      console.error('Error updating workspace:', error)
  })
}

// Delete workspace confirmation
const confirmDeleteWorkspace = () => {
  alert.confirm(
    'Do you really want to delete your workspace?',
    deleteWorkspace
  )
}

// Delete workspace
const deleteWorkspace = () => {
  removeMutation.mutateAsync(workspace.value.id).then((data) => {
      alert.success(data.message)
      closeWorkspaceSettings()
      nextTick(() => {
        router.push({ name: "home", query: {} })
      })
  }).catch((error) => {
      alert.error(error.data?.message || 'Error deleting workspace')
    })
}

// Leave workspace
const leaveWorkSpace = () => {
  alert.confirm(
    "Do you really want to leave this workspace? You will lose access to all forms in this workspace.",
    () => {
      leaveMutation.mutateAsync(workspace.value.id).then(() => {
        alert.success("You have left the workspace.")
        closeWorkspaceSettings()
        nextTick(() => {
          router.push({ name: "home", query: {} })
        })
      }).catch((error) => {
        console.error('Error leaving workspace:', error)
        alert.error("There was an error leaving the workspace.")
      })
    },
  )
}


// Watch for user changes
watch(workspace, (newWorkspace) => {
  if (newWorkspace) {
    workspaceForm.fill({
      name: newWorkspace.name || '',
      emoji: newWorkspace.icon || ''
    })
  }
}, { immediate: true })
</script>
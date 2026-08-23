<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>Account</h2>
      <p>Update your profile information and manage your personal data.</p>
    </div>

    <!-- Profile Information Section -->
    <section class="sf-card sf-card-pad">
      <div class="flex items-start gap-3 mb-6">
        <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
          <i class="fa-solid fa-user" />
        </span>
        <div>
          <h3 class="text-[15px] font-semibold text-[#1D1F24]">Profile Information</h3>
          <p class="text-[13px] text-[#6E7278] mt-0.5">
            Update your account profile information and email address.
          </p>
        </div>
      </div>

      <VForm size="sm">
        <form
          @submit.prevent="updateProfile"
        >
          <div class="max-w-sm">
            <text-input
              :form="profileForm"
              name="name"
              label="Full Name"
              placeholder="Enter your full name"
              :required="true"
            />
            <text-input
              :form="profileForm"
              name="email"
              label="Email Address"
              type="email"
              placeholder="Enter your email"
              :required="true"
            />
          </div>

          <div class="mt-6 flex items-center gap-3">
            <UButton
              type="submit"
              :loading="profileForm.busy"
              color="primary"
              class="btn-primary"
            >
              Save Changes
            </UButton>
            <span v-if="profileForm.busy" class="text-xs text-[#8E9198]">Saving…</span>
          </div>
        </form>
      </VForm>
    </section>

    <!-- Export Personal Data -->
    <section class="sf-card sf-card-pad">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
            <i class="fa-solid fa-download" />
          </span>
          <div>
            <h3 class="text-[14px] font-semibold text-[#1D1F24]">Export Personal Data</h3>
            <p class="mt-1 text-[13px] text-[#6E7278] max-w-md">
              Download a machine-readable export of your account profile, workspaces, forms, subscriptions, and licenses.
            </p>
          </div>
        </div>

        <UButton
          color="neutral"
          variant="outline"
          class="btn-ghost !border-[#DEE1E7] shrink-0"
          :loading="exportMutation.isPending.value"
          @click="downloadPersonalData"
        >
          Download My Data
        </UButton>
      </div>
    </section>

    <!-- Danger Zone -->
    <section class="sf-danger-zone sf-card-pad">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--coral">
            <i class="fa-solid fa-triangle-exclamation" />
          </span>
          <div>
            <h3 class="sf-danger-title text-[14px] font-semibold">Delete Account</h3>
            <p class="mt-1 text-[13px] text-[#6E7278] max-w-md">
              This will permanently delete your entire account. This cannot be undone.
            </p>
          </div>
        </div>

        <UButton
          color="error"
          :loading="deleteMutation.isPending.value"
          @click="confirmDeleteAccount"
          class="shrink-0"
        >
          Delete Account
        </UButton>
      </div>
    </section>
  </div>
</template>

<script setup>
// Use useAuth composable for all user-related mutations
const alert = useAlert()

// Auth composable (TanStack Query powered)
const {
  updateProfile: updateProfileMutationFactory,
  exportData: exportDataMutationFactory,
  deleteAccount: deleteAccountFactory,
  invalidateUser
} = useAuth()

// Query mutations
const updateMutation = updateProfileMutationFactory()
const exportMutation = exportDataMutationFactory()
const deleteMutation = deleteAccountFactory()

const { data: user } = useAuth().user()

// Profile form
const profileForm = useForm({
  name: '',
  email: '',
})

// Update profile
const updateProfile = () => {
  profileForm.mutate(updateMutation)
    .then(() => {
      invalidateUser()
      alert.success('Your info has been updated!')
    })
    .catch((error) => {
      console.error(error)
      alert.error(error?.data?.message || 'Error updating profile')
    })
}

const downloadPersonalData = () => {
  exportMutation.mutateAsync()
    .then((payload) => {
      const safeEmail = (user.value?.email || 'account').replace(/[^a-z0-9]+/gi, '-').toLowerCase()
      const fileName = `${safeEmail}-personal-data.json`
      const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' })
      const objectUrl = window.URL.createObjectURL(blob)
      const link = document.createElement('a')

      link.href = objectUrl
      link.download = fileName
      link.click()

      window.URL.revokeObjectURL(objectUrl)
      alert.success('Your personal data export is ready.')
    })
    .catch((error) => {
      alert.error(error?.data?.message || 'Error exporting personal data')
    })
}

// Delete account confirmation
const confirmDeleteAccount = () => {
  alert.confirm(
    'Do you really want to delete your account?',
    deleteAccount
  )
}

// Delete account
const deleteAccount = () => {
  deleteMutation.mutateAsync()
    .then((data) => {
      alert.success(data?.message || 'Your account has been deleted')
      // Navigation handled by deleteAccount mutation
    })
    .catch((error) => {
      alert.error(error?.data?.message || 'Error deleting account')
    })
}

// Initialize form with user data
onBeforeMount(() => {
  if (user.value) {
    profileForm.keys().forEach((key) => {
      profileForm[key] = user.value[key]
    })
  }
})

// Watch for user changes
watch(user, (newUser) => {
  if (newUser) {
    profileForm.keys().forEach((key) => {
      profileForm[key] = newUser[key]
    })
  }
}, { immediate: true })

</script>
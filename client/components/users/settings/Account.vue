<template>
  <div class="space-y-8">
    <!-- Profile Information Section -->
    <div class="space-y-4">
      <div>
        <h3 class="text-lg font-medium text-neutral-900">Profile Information</h3>
        <p class="text-sm text-neutral-500 mt-1">
          Update your account profile information and email address.
        </p>
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

          <div class="mt-4">
            <UButton
              type="submit"
              :loading="profileForm.busy"
              color="primary"
            >
              Save Changes
            </UButton>
          </div>
        </form>
      </VForm>
    </div>

    <div class="pt-8 border-t border-neutral-200">
      <div class="flex flex-col gap-2 items-start">
        <div>
          <h4 class="font-medium text-neutral-900">Export Personal Data</h4>
          <p class="mt-1 text-sm text-neutral-500">
            Download a machine-readable export of your account profile, workspaces, forms, subscriptions, and licenses.
          </p>
        </div>

        <UButton
          color="neutral"
          variant="outline"
          :loading="exportMutation.isPending.value"
          @click="downloadPersonalData"
        >
          Download My Data
        </UButton>
      </div>
    </div>

    <div class="pt-8 border-t border-neutral-200">
      <div class="flex flex-col gap-2 items-start">
        <div>
          <h4 class="font-medium text-red-800">Delete Account</h4>
          <p class="mt-1 text-sm text-neutral-500">
            This will permanently delete your entire account. This cannot be undone.
          </p>
        </div>

          <UButton
            color="error"
            :loading="deleteMutation.isPending.value"
            @click="confirmDeleteAccount"
          >
            Delete Account
          </UButton>

      </div>
    </div>
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

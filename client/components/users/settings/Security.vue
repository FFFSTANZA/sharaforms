<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>Security</h2>
      <p>Keep your account safe with a strong password and two-factor authentication.</p>
    </div>

    <!-- Password Section -->
    <section class="sf-card sf-card-pad">
      <div class="flex items-start gap-3 mb-6">
        <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
          <i class="fa-solid fa-key" />
        </span>
        <div>
          <h3 class="text-[15px] font-semibold text-[#1D1F24]">Change Password</h3>
          <p class="text-[13px] text-[#6E7278] mt-0.5">
            Update your password to keep your account secure.
          </p>
        </div>
      </div>

      <VForm size="sm">
        <form
          @submit.prevent="updatePassword"
        >
          <div class="max-w-sm">
            <TextInput
              :form="passwordForm"
              name="current_password"
              label="Current Password"
              native-type="password"
              placeholder="Enter current password"
              :required="true"
            />

            <TextInput
              :form="passwordForm"
              name="password"
              label="New Password"
              native-type="password"
              placeholder="Enter new password"
              :required="true"
              @focus="isPasswordFocused = true"
              @blur="isPasswordFocused = false"
            />
            <PasswordStrengthIndicator
              v-show="isPasswordFocused"
              :password="passwordForm.password"
            />

            <TextInput
              :form="passwordForm"
              name="password_confirmation"
              label="Confirm Password"
              native-type="password"
              placeholder="Confirm new password"
              :required="true"
            />
          </div>

          <div class="mt-6">
            <UButton
              type="submit"
              :loading="passwordForm.busy"
              color="primary"
              class="btn-primary"
            >
              Update Password
            </UButton>
          </div>
        </form>
      </VForm>
    </section>

    <!-- Two-Factor Authentication -->
    <section class="sf-card sf-card-pad">
      <UsersSettingsTwoFactorAuth />
    </section>
  </div>
</template>

<script setup>
const alert = useAlert()

// Password form
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

// Password field focus state
const isPasswordFocused = ref(false)

// Update password
const updatePassword = () => {
  passwordForm
    .patch('/settings/password')
    .then(() => {
      passwordForm.reset()
      alert.success('Password updated.')
    })
    .catch((error) => {
      console.error(error)
    })
}
</script>
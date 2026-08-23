<template>
  <div class="space-y-4">
    <div class="flex items-start gap-3">
      <span class="sf-icon-chip-soft sf-icon-chip-soft--green">
        <i class="fa-solid fa-shield-halved" />
      </span>
      <div class="flex-1">
        <div class="flex items-center gap-2 flex-wrap">
          <h3 class="text-[15px] font-semibold text-[#1D1F24]">Two-Factor Authentication</h3>
          <span
            v-if="twoFactorEnabled"
            class="pill-live inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold"
          >
            <i class="fa-solid fa-circle-check text-[9px]"></i>
            Enabled
          </span>
        </div>
        <p class="text-[13px] text-[#6E7278] mt-0.5">
          Add an extra layer of security to your account using an authenticator app.
        </p>
      </div>
    </div>

    <!-- 2FA Status -->
    <div v-if="twoFactorEnabled" class="space-y-4">
      <div class="flex gap-2">
        <UButton
          color="neutral"
          variant="outline"
          class="btn-ghost !border-[#DEE1E7]"
          @click="showRegenerateModal = true"
        >
          Regenerate Recovery Codes
        </UButton>
        <UButton
          color="neutral"
          variant="outline"
          class="btn-ghost !border-[#DEE1E7] !text-[#c2351f] hover:!bg-[#fce7e2]"
          @click="showDisableModal = true"
        >
          Disable 2FA
        </UButton>
      </div>
    </div>

    <!-- Enable 2FA Flow -->
    <TwoFactorEnableFlow
      v-else
      :enabling="enabling2FA"
      :confirming="confirming2FA"
      :secret="twoFactorSecret"
      :qr-code="twoFactorQrCode"
      @enable="enableTwoFactor"
      @confirm="confirmTwoFactor"
    />

    <!-- Recovery Codes Modal (shown after setup or regeneration) -->
    <RecoveryCodesModal
      :show="showRecoveryCodesModal"
      :codes="recoveryCodesList"
      :just-regenerated="justRegenerated"
      @close="closeRecoveryCodesModal"
      @copy="copyRecoveryCodes"
    />

    <!-- Regenerate Recovery Codes Modal -->
    <RegenerateRecoveryCodesModal
      :show="showRegenerateModal"
      :loading="regeneratingCodes"
      @close="closeRegenerateModal"
      @regenerate="handleRegenerateRecoveryCodes"
    />

    <!-- Disable 2FA Modal -->
    <DisableTwoFactorModal
      :show="showDisableModal"
      :loading="disabling2FA"
      @close="closeDisableModal"
      @disable="disableTwoFactor"
    />
  </div>
</template>

<script setup>
import { authApi } from '~/api/auth'
import TwoFactorEnableFlow from './TwoFactorEnableFlow.vue'
import RecoveryCodesModal from './RecoveryCodesModal.vue'
import DisableTwoFactorModal from './DisableTwoFactorModal.vue'
import RegenerateRecoveryCodesModal from './RegenerateRecoveryCodesModal.vue'

const alert = useAlert()
const auth = useAuth()
const { data: user } = auth.user()

// Two-Factor Authentication
const twoFactorEnabled = computed(() => user.value?.two_factor_enabled ?? false)
const twoFactorSecret = ref(null)
const twoFactorQrCode = ref(null)
const recoveryCodesList = ref([])
const showRecoveryCodesModal = ref(false)
const showDisableModal = ref(false)
const showRegenerateModal = ref(false)

const enabling2FA = ref(false)
const confirming2FA = ref(false)
const disabling2FA = ref(false)
const regeneratingCodes = ref(false)
const justRegenerated = ref(false)

const enableTwoFactor = async () => {
  enabling2FA.value = true
  try {
    const response = await authApi.twoFactor.enable()
    twoFactorSecret.value = response.secret
    twoFactorQrCode.value = response.qr_code
  } catch (error) {
    alert.error(error.response?._data?.message || 'Failed to enable two-factor authentication')
  } finally {
    enabling2FA.value = false
  }
}

const confirmTwoFactor = async (code) => {
  confirming2FA.value = true
  try {
    const response = await authApi.twoFactor.confirm({ code })
    
    recoveryCodesList.value = response.recovery_codes || []
    twoFactorSecret.value = null
    twoFactorQrCode.value = null
    
    // Refresh user data
    await auth.invalidateUser()
    
    // Show recovery codes modal automatically after enabling
    justRegenerated.value = false
    showRecoveryCodesModal.value = true
    
    alert.success('Two-factor authentication has been enabled successfully.')
  } catch (error) {
    alert.error(error.response?._data?.message || error.response?._data?.errors?.code?.[0] || 'Invalid code. Please try again.')
  } finally {
    confirming2FA.value = false
  }
}

const disableTwoFactor = async (code) => {
  disabling2FA.value = true
  try {
    await authApi.twoFactor.disable({ code })
    
    showDisableModal.value = false
    
    // Refresh user data
    await auth.invalidateUser()
    
    alert.success('Two-factor authentication has been disabled.')
  } catch (error) {
    alert.error(error.response?._data?.message || error.response?._data?.errors?.code?.[0] || 'Invalid code. Please try again.')
  } finally {
    disabling2FA.value = false
  }
}

const closeRecoveryCodesModal = () => {
  recoveryCodesList.value = []
  justRegenerated.value = false
  showRecoveryCodesModal.value = false
}

const closeRegenerateModal = () => {
  showRegenerateModal.value = false
}

const closeDisableModal = () => {
  showDisableModal.value = false
}

const handleRegenerateRecoveryCodes = async (data) => {
  regeneratingCodes.value = true
  try {
    const response = await authApi.twoFactor.regenerateRecoveryCodes(data)
    recoveryCodesList.value = response.recovery_codes || []
    justRegenerated.value = true
    showRegenerateModal.value = false
    showRecoveryCodesModal.value = true
    alert.success('Recovery codes have been regenerated. Please save them in a safe place.')
  } catch (error) {
    alert.error(error.response?._data?.message || error.response?._data?.errors?.code?.[0] || 'Failed to regenerate recovery codes')
  } finally {
    regeneratingCodes.value = false
  }
}

const { copy: copyToClipboard } = useClipboard()

const copyRecoveryCodes = () => {
  // Extract just the codes from objects
  const codes = recoveryCodesList.value.map(item => 
    typeof item === 'string' ? item : item.code
  )
  const codesText = codes.join('\n')
  copyToClipboard(codesText)
  alert.success('Recovery codes copied to clipboard')
}
</script>


<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>Emails</h2>
      <p>Configure a custom SMTP sender for this workspace.</p>
    </div>

    <section class="sf-card sf-card-pad">
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center mb-5">
        <div class="flex items-center gap-3">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
            <i class="fa-solid fa-envelope" />
          </span>
          <div>
            <h3 class="text-[15px] font-semibold text-[#1D1F24]">Email Settings</h3>
            <p class="text-xs text-[#8E9198] font-medium mt-0.5">
              Configure a custom SMTP sender for this workspace.
            </p>
            <PlanTag
              required-tier="self_hosted"
            />
          </div>
        </div>

        <UButton
          label="Help"
          icon="i-lucide-circle-question-mark"
          variant="outline"
          color="neutral"
          class="btn-ghost !border-[#DEE1E7]"
          @click="crisp.openHelpdeskArticle('how-to-send-emails-using-your-own-domain-name-and-email-address-13kkcif')"
        />
      </div>

      <UAlert
        v-if="isSelfHosted"
        icon="i-lucide-info"
        color="info"
        variant="subtle"
        class="mb-5"
        title="Instance-wide email sending is configured separately"
        description="Use MAIL_* environment variables to configure the default sender for your whole self-hosted instance. These workspace settings override that sender for this workspace and require a self-hosted Enterprise license."
        :actions="[{
          label: 'Email setup docs',
          icon: 'i-lucide-external-link',
          color: 'info',
          variant: 'solid',
          to: sharaformsConfig.links.email_setup_docs,
          target: '_blank'
        }]"
      />

      <UAlert
        v-if="!isSelfHosted && !canAccessSmtp"
        icon="i-lucide-users"
        class="mb-5"
        color="warning"
        variant="subtle"
        title="Pro plan required"
        description="Please upgrade your account to setup an email settings."
        :actions="[{
          label: 'Try Pro plan',
          color: 'warning',
          variant: 'solid',
          onClick: () => openUpgradeModal()
        }]"
      />

      <VForm size="sm">
        <form
          @submit.prevent="saveChanges"
        >
          <div class="max-w-sm">
            <TextInput
              :form="emailSettingsForm"
              name="host"
              :required="true"
              :disabled="!canAccessSmtp"
              label="Host/Server"
              class="mt-2"
              placeholder="smtp.example.com"
            />
            <TextInput
              :form="emailSettingsForm"
              name="port"
              :required="true"
              :disabled="!canAccessSmtp"
              label="Port"
              placeholder="587"
            />
            <OptionSelectorInput
              :form="emailSettingsForm"
              name="encryption"
              :disabled="!canAccessSmtp"
              label="Encryption"
              :options="encryptionOptions"
              :columns="3"
              seamless
            />
            <TextInput
              :form="emailSettingsForm"
              name="username"
              :required="true"
              :disabled="!canAccessSmtp"
              label="Username"
              placeholder="Username"
            />
            <TextInput
              :form="emailSettingsForm"
              name="password"
              native-type="password"
              :required="true"
              :disabled="!canAccessSmtp"
              label="Password"
              placeholder="Password"
            />
            <TextInput
              :form="emailSettingsForm"
              name="sender_address"
              :disabled="!canAccessSmtp"
              label="Sender address"
              placeholder="sender@example.com"
            />
          </div>

          <div class="mt-6 flex items-center justify-between w-full max-w-sm flex-wrap gap-2">
            <UButton
              type="submit"
              :loading="emailSettingsForm.busy"
              :disabled="!canAccessSmtp"
              class="btn-primary"
            >
              Save Settings
            </UButton>
            <UButton
              color="neutral"
              variant="outline"
              class="btn-ghost !border-[#DEE1E7] !text-[#c2351f] hover:!bg-[#fce7e2]"
              :loading="emailSettingsForm.busy"
              :disabled="!canAccessSmtp"
              @click="clearEmailSettings"
            >
              Clear settings
            </UButton>
          </div>
        </form>
      </VForm>
    </section>
  </div>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"
import sharaformsConfig from "~/sharaforms.config.js"

const alert = useAlert()

const { current: workspace } = useCurrentWorkspace()

const { openSubscriptionModal } = useAppModals()
const crisp = useCrisp()
const { hasFeature } = usePlanFeatures()
const canAccessSmtp = computed(() => hasFeature('custom_smtp'))
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))

const openUpgradeModal = () => {
  openSubscriptionModal({
    plan: isSelfHosted.value ? 'self_hosted' : 'pro',
    modal_title: 'Upgrade to send emails using your own domain'
  })
}

const encryptionOptions = [
  { name: 'tls', label: 'TLS' },
  { name: 'ssl', label: 'SSL' },
  { name: 'none', label: 'None' }
]

const emailSettingsForm = useForm({
  host: '',
  port: '',
  encryption: 'tls',
  username: '',
  password: '',
  sender_address: ''
})

onMounted(() => {
  initEmailSettings()
})

watch(
  () => workspace,
  () => {
    initEmailSettings()
  },
)

const clearEmailSettings = () => {
  emailSettingsForm.reset()
  saveChanges()
}

const saveChanges = () => {
  if (isSelfHosted.value) {
    if (!canAccessSmtp.value) {
      openUpgradeModal()
      return
    }
  }

  // Update the workspace Email Settings
  emailSettingsForm
    .put("/open/workspaces/" + workspace.value.id + "/email-settings", {
      data: {
        host: emailSettingsForm?.host,
        port: emailSettingsForm?.port,
        encryption: emailSettingsForm?.encryption === 'none' ? null : emailSettingsForm?.encryption,
        username: emailSettingsForm?.username,
        password: emailSettingsForm?.password,
        sender_address: emailSettingsForm?.sender_address,
      },
    })
    .then((_data) => {
      // Cache is updated automatically by TanStack Query mutations
      alert.success("Email settings saved.")
    })
    .catch((error) => {
      alert.error("Failed to update email settings: " + error.response.data.message)
    })
}

const initEmailSettings = () => {
  if (!workspace || !workspace.value.settings.email_settings) return
  const emailSettings = workspace.value?.settings?.email_settings
  emailSettingsForm.host = emailSettings?.host
  emailSettingsForm.port = emailSettings?.port
  emailSettingsForm.encryption = emailSettings?.encryption === null ? 'none' : (emailSettings?.encryption || 'tls')
  emailSettingsForm.username = emailSettings?.username
  emailSettingsForm.password = emailSettings?.password
  emailSettingsForm.sender_address = emailSettings?.sender_address
}
</script>
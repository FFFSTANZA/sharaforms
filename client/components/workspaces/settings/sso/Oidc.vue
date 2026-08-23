<template>
  <div class="space-y-6">
    <section class="sf-card sf-card-pad">
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center mb-5">
        <div class="flex items-center gap-3 flex-1">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
            <i class="fa-solid fa-fingerprint" />
          </span>
          <div>
            <h3 class="text-[15px] font-semibold text-[var(--sf-text-primary)]">OIDC Settings</h3>
            <p class="text-xs text-[#8E9198] font-medium mt-0.5">
              Configure OpenID Connect (OIDC) single sign-on for your workspace.
            </p>
          </div>
        </div>

        <UButton
          v-if="canManageConnections && canAccessFeature"
          label="Add Connection"
          icon="i-lucide-plus"
          class="btn-primary"
          @click="showCreateModal = true"
        />
        <UButton
          v-else-if="canManageConnections && !canAccessFeature"
          label="Add Connection"
          icon="i-lucide-plus"
          class="btn-primary"
          @click="openUpgradeModal"
        />
      </div>

      <UAlert
        :icon="alertConfig.icon"
        :color="alertConfig.color"
        variant="subtle"
        class="mb-5"
        :title="alertConfig.title"
        :description="alertConfig.description"
        :actions="alertConfig.actions"
      />

      <!-- Connections List -->
      <div v-if="connectionsData && connectionsData.length > 0" class="space-y-3">
        <p class="text-[13px] text-[var(--sf-text-description)] max-w-xl">
          Each connection can be tied to one verified email domain, which we use to route incoming users to the
          correct workspace when they start login. Manage multiple clients from here and toggle them on or off without
          losing their configuration details.
        </p>
        <div class="grid gap-3 sm:grid-cols-2">
          <OidcConnectionCard
          v-for="connection in connectionsData"
          :key="connection.id"
            :connection="connection"
            :can-edit="canManageConnections && canAccessFeature"
            @edit="editConnection"
            @delete="deleteConnection"
            />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!isConnectionsLoading" class="text-center py-10 rounded-xl border border-dashed border-[var(--sf-border-button)] bg-[var(--sf-bg-page)]">
        <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-2xl bg-[#F0F1F4]">
          <UIcon
            name="i-lucide-key"
            class="w-6 h-6 text-[#8E9198]"
          />
        </div>
        <h4 class="text-[15px] font-semibold text-[var(--sf-text-primary)] mb-1">
          No OIDC connections yet
        </h4>
        <p class="text-[13px] text-[var(--sf-text-description)] mb-5">
          Configure your first OIDC connection to enable single sign-on for your workspace.
        </p>
        <UButton
          v-if="canManageConnections && canAccessFeature"
          label="Add Your First Connection"
          icon="i-lucide-plus"
          class="btn-primary"
          @click="showCreateModal = true"
        />
        <UButton
          v-else-if="canManageConnections && !canAccessFeature"
          label="Add Your First Connection"
          icon="i-lucide-plus"
          class="btn-primary"
          @click="openUpgradeModal"
        />
      </div>
    </section>

    <!-- Create/Edit Modal -->
    <OidcConnectionModal
      :model-value="showCreateModal"
      :connection="editingConnection"
            :form="connectionForm"
      :is-busy="connectionForm.busy"
      @update:model-value="showCreateModal = $event"
      @save="saveConnection"
      @cancel="cancelEdit"
    />
  </div>
</template>

<script setup>
import { useOidcConnections } from '~/composables/query/useOidcConnections'
import OidcConnectionCard from './OidcConnectionCard.vue'
import OidcConnectionModal from './OidcConnectionModal.vue'

const { current: workspace } = useCurrentWorkspace()
const alert = useAlert()
const { openSubscriptionModal } = useAppModals()
const { handleLicenseError } = useLicenseUpgradeModal()

const workspaceId = computed(() => workspace.value?.id)

const { hasFeature } = usePlanFeatures()
const canManageConnections = computed(() => !!workspace.value && workspace.value.is_admin)

// OIDC is available on self-hosted instances; cloud workspaces still require Enterprise.
const isSelfHosted = computed(() => useFeatureFlag('self_hosted'))
const billingEnabled = computed(() => useFeatureFlag('billing.enabled'))
const canAccessFeature = computed(() => {
  if (isSelfHosted.value) return true
  return billingEnabled.value && hasFeature('sso.oidc')
})

const { connections, create, update, remove } = useOidcConnections(workspaceId)

// Allow viewing connections without Enterprise (Enterprise only required for create/update/delete on cloud)
const { data: connectionsData, isLoading: isConnectionsLoading } = connections()

const alertConfig = computed(() => {
  if (!isSelfHosted.value && !canAccessFeature.value) {
    return {
      icon: 'i-lucide-info',
      color: 'info',
      title: 'Enterprise Plan Required',
      description: 'OIDC SSO requires an Enterprise plan. Upgrade your plan to configure single sign-on for your workspace.',
      actions: [
        {
          label: 'Upgrade to Enterprise',
          onClick: openUpgradeModal
        }
      ]
    }
  }

  if (!isSelfHosted.value && canAccessFeature.value) {
    return {
      icon: 'i-lucide-circle-check',
      color: 'success',
      title: 'OIDC SSO Enabled',
      description: 'Configure OpenID Connect single sign-on connections for your workspace.',
      actions: []
    }
  }

  return {
    icon: 'i-lucide-info',
    color: 'info',
    title: 'OIDC SSO',
    description: 'OIDC is available on self-hosted instances. Free self-hosted instances are limited to 2 users total; activate an Enterprise license to add more users.',
    actions: []
  }
})

const openUpgradeModal = () => {
  openSubscriptionModal({
    plan: 'enterprise',
    modal_title: 'Upgrade to Enterprise to use OIDC SSO',
    modal_description: 'OIDC SSO is an Enterprise feature. Upgrade your plan to configure single sign-on for your workspace.'
  })
}

const openSsoLicenseModal = (error) => {
  return handleLicenseError(error, {
    includeUnauthorized: true,
    title: 'Enterprise license required',
    description: 'Activate an Enterprise self-hosted license to add more than 2 users or use advanced Enterprise features.'
  })
}

const showCreateModal = ref(false)
const editingConnection = ref(null)

const connectionForm = useForm({
  name: '',
  slug: '',
  issuer: '',
  client_id: '',
  client_secret: '',
  domain: '',
  enabled: true,
  options: {
    field_mappings: {
      email: '',
      name: ''
    },
    group_role_mappings: []
  }
})

// Create mutations following useWorkspaces.js pattern
const createMutation = create()
const deleteMutation = remove()

const saveConnection = () => {
  if (editingConnection.value) {
    // Update existing connection
    const updateMutation = update(editingConnection.value.id)
    connectionForm.mutate(updateMutation)
      .then(() => {
        alert.success('OIDC connection updated successfully')
        showCreateModal.value = false
        cancelEdit()
      })
      .catch((error) => {
        // Form handles validation errors automatically
        if (openSsoLicenseModal(error)) return
        if (error.response?.status !== 422) {
          alert.error(error.response?._data?.message ?? 'Failed to update connection')
        }
      })
  } else {
    // Create new connection
    connectionForm.mutate(createMutation)
      .then(() => {
        alert.success('OIDC connection created successfully')
        showCreateModal.value = false
        connectionForm.reset()
      })
      .catch((error) => {
        // Form handles validation errors automatically
        if (openSsoLicenseModal(error)) return
        if (error.response?.status !== 422) {
          alert.error(error.response?._data?.message ?? 'Failed to create connection')
        }
      })
  }
}

const editConnection = (connection) => {
  editingConnection.value = connection
  connectionForm.resetAndFill({
    name: connection.name,
    slug: connection.slug,
    issuer: connection.issuer,
    client_id: connection.client_id,
    client_secret: '', // Don't pre-fill secret
    enabled: connection.enabled,
    domain: connection.domain ?? '',
    options: {
      field_mappings: {
        email: connection.options?.field_mappings?.email ?? '',
        name: connection.options?.field_mappings?.name ?? ''
      },
      group_role_mappings: connection.options?.group_role_mappings ?? []
    }
  })
  showCreateModal.value = true
}

const deleteConnection = (connection) => {
  alert.confirm(
    `Are you sure you want to delete "${connection.name}"?`,
    () => {
      deleteMutation.mutateAsync(connection.id)
        .then(() => {
          alert.success('OIDC connection deleted successfully')
        })
        .catch((error) => {
          if (openSsoLicenseModal(error)) return
          alert.error(error.response?._data?.message ?? 'Failed to delete connection')
        })
    }
  )
}

const cancelEdit = () => {
  editingConnection.value = null
  connectionForm.reset()
  showCreateModal.value = false
}
</script>

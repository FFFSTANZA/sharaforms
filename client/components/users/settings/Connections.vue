<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>Connections</h2>
      <p>Connect your accounts to enable integrations and streamline your workflow.</p>
    </div>

    <!-- Providers List -->
    <section class="sf-card">
      <!-- Card header -->
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 border-b border-[#ECEEF2] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
        <div class="flex items-center gap-3">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
            <i class="fa-solid fa-link" />
          </span>
          <div>
            <h3 class="text-[15px] font-semibold text-[#1D1F24]">External Connections</h3>
            <p class="text-xs text-[#8E9198] font-medium mt-0.5">
              Manage your external connections and integrations.
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <UButton
            icon="i-lucide-refresh-cw"
            color="neutral"
            variant="soft"
            square
            size="md"
            :loading="isFetching"
            @click="refreshProviders"
          />
          <UButton
            label="Connect Account"
            icon="i-lucide-plus"
            :loading="isFetching"
            class="btn-primary"
            @click="providerModal = true"
          />
        </div>
      </div>

      <div class="space-y-4 p-5 sm:p-6">
        <div v-if="providers.length === 0 && !isFetching" class="text-center py-10">
          <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-2xl bg-[#F0F1F4]">
            <UIcon
              name="i-lucide-link"
              class="w-6 h-6 text-[#8E9198]"
            />
          </div>
          <h4 class="text-[15px] font-semibold text-[#1D1F24] mb-1">
            No connections yet
          </h4>
          <p class="text-[13px] text-[#6E7278] mb-5 max-w-sm mx-auto">
            Connect your accounts to enable integrations and streamline your workflow.
          </p>
          <UButton
            label="Connect Your First Account"
            icon="i-lucide-plus"
            class="btn-primary"
            @click="providerModal = true"
          />
        </div>

        <UTable
          v-if="providers.length > 0"
          v-model:column-pinning="columnPinning"
          :data="providers"
          :columns="tableColumns"
          :loading="isFetching"
          class="w-full"
        >
          <template #provider-cell="{ row: { original: item } }">
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[var(--sf-bg-subtle)] text-[var(--sf-text-body)] shrink-0">
                <Icon
                  :name="serviceMeta(item.provider)?.icon"
                  size="16px"
                />
              </span>
              <span class="font-semibold text-[#1D1F24]">{{ serviceMeta(item.provider)?.name || item.provider }}</span>
            </div>
          </template>

          <template #email-cell="{ row: { original: item } }">
            <div class="flex flex-col items-start" v-if="item.name || item.email">
              <span class="text-[13px] font-medium text-[#565A62]" v-if="item.name">{{ item.name }}</span>
              <span class="text-xs text-[#8E9198]" v-if="item.email">{{ item.email }}</span>
            </div>
            <span v-else class="text-[#A0A4AD]">
              -
            </span>
          </template>

          <template #actions-cell="{ row: { original: item } }">
            <div class="flex justify-end">
              <UButton
                color="error"
                variant="soft"
                icon="i-lucide-trash-2"
                square
                size="sm"
                @click="disconnectProvider(item)"
              />
            </div>
          </template>
        </UTable>
      </div>
    </section>

    <!-- Provider Modal -->
    <UsersSettingsConnectionModal
      v-model="providerModal"
      @close="providerModal = false"
    />
  </div>
</template>

<script setup>
const providerModal = ref(false)
const oAuth = useOAuth()
const alert = useAlert()

const { data: providersData, refetch, isFetching } = oAuth.providers()
const providers = computed(() => providersData.value || [])

// Column pinning state
const columnPinning = ref({
  left: [],
  right: ['actions']
})

// Table columns configuration
const tableColumns = [
  {
    id: 'provider',
    accessorKey: 'provider',
    header: 'Service',
    enableSorting: true
  },
  {
    id: 'email',
    accessorKey: 'email',
    header: 'Account',
    enableSorting: true
  },
  {
    id: 'actions',
    header: '',
    enableSorting: false,
    enableHiding: false
  }
]

// Get service information
const getService = (providerName) => {
  return oAuth.getService(providerName)
}

// Fallback metadata for connections that are not OAuth services
// (e.g. Stripe connections created from the creator's own API keys).
const serviceMeta = (providerName) => {
  return getService(providerName) || (providerName === 'stripe_own_keys'
    ? { name: 'Stripe (own API keys)', icon: 'cib:stripe' }
    : null)
}

// Disconnect provider mutation
const removeMutation = oAuth.remove()

// Disconnect provider
const disconnectProvider = (provider) => {
  alert.confirm("Do you really want to disconnect this account?", () => {
    removeMutation.mutateAsync(provider.id).then(() => {
      alert.success('Account disconnected successfully')
      refetch()
    }).catch((error) => {
      try {
        alert.error(error.data.message)
      } catch {
        alert.error("An error occurred while disconnecting the account")
      }
    })
  })
}

// Refresh providers
const refreshProviders = async () => {
  await refetch()
}

// Fetch providers on mount
await oAuth.fetchOAuthProviders()
</script>
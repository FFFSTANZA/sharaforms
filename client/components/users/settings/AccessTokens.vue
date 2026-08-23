<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>Access Tokens</h2>
      <p>Create and manage API access tokens for programmatic access.</p>
    </div>

    <!-- Tokens List -->
    <section class="sf-card">
      <!-- Card header -->
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 border-b border-[#ECEEF2] px-5 py-4 sm:flex-row sm:items-center sm:px-6">
        <div class="flex items-center gap-3">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
            <i class="fa-solid fa-key" />
          </span>
          <div>
            <h3 class="text-[15px] font-semibold text-[#1D1F24]">API Access Tokens</h3>
            <p class="text-xs text-[#8E9198] font-medium mt-0.5">
              Manage your API access tokens for programmatic access.
            </p>
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <UButton
            label="API Docs"
            icon="i-lucide-book-open"
            variant="outline"
            color="neutral"
            class="btn-ghost !border-[#DEE1E7]"
            :to="sharaformsConfig.links.api_docs"
            target="_blank"
          />

          <UButton
            label="Create New Token"
            icon="i-lucide-plus"
            :loading="loading"
            class="btn-primary"
            @click="accessTokenModal = true"
          />
        </div>
      </div>

      <div class="space-y-4 p-5 sm:p-6">
        <div v-if="tokens?.length === 0 && !loading" class="text-center py-10">
          <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-2xl bg-[#F0F1F4]">
            <UIcon
              name="i-lucide-key"
              class="w-6 h-6 text-[#8E9198]"
            />
          </div>
          <h4 class="text-[15px] font-semibold text-[#1D1F24] mb-1">
            No access tokens yet
          </h4>
          <p class="text-[13px] text-[#6E7278] mb-5 max-w-sm mx-auto">
            Create your first API access token to start using our API programmatically.
          </p>
          <UButton
            label="Create Your First Token"
            icon="i-lucide-plus"
            class="btn-primary"
            @click="accessTokenModal = true"
          />
        </div>

        <UTable
          v-if="tokens?.length > 0"
          v-model:column-pinning="columnPinning"
          :data="tokens"
          :columns="tableColumns"
          :loading="loading"
          class="w-full"
        >
          <template #name-cell="{ row: { original: item } }">
            <div class="flex items-center gap-2.5">
              <span class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-[var(--sf-bg-subtle)] text-[var(--sf-text-body)] shrink-0">
                <i class="fa-solid fa-key text-[10px]" />
              </span>
              <span class="font-semibold text-[#1D1F24]">{{ item.name }}</span>
            </div>
          </template>

          <template #abilities-cell="{ row: { original: item } }">
            <AbilitiesBadges :abilities="item.abilities" />
          </template>

          <template #actions-cell="{ row: { original: item } }">
            <div class="flex justify-end">
              <UButton
                color="error"
                variant="soft"
                icon="i-lucide-trash-2"
                square
                size="sm"
                @click="deleteToken(item)"
              />
            </div>
          </template>
        </UTable>
      </div>
    </section>

    <!-- API Information -->
    <section class="sf-card sf-card-pad">
      <div class="flex items-start gap-3 mb-5">
        <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
          <i class="fa-solid fa-book-open" />
        </span>
        <div>
          <h3 class="text-[15px] font-semibold text-[#1D1F24]">API Information</h3>
          <p class="text-[13px] text-[#6E7278] mt-0.5">
            Learn how to use your access tokens with our API.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="rounded-xl border border-[#E6E8EE] bg-[#F7F8FA] p-4">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted mb-3">
            <i class="fa-solid fa-rocket" />
          </span>
          <h4 class="text-[13px] font-semibold text-[#1D1F24]">Getting Started</h4>
          <p class="text-xs text-[#6E7278] mt-1 leading-relaxed">
            Use your access tokens in the Authorization header: <code class="bg-[#ECEEF2] px-1 rounded text-[11px]">Bearer YOUR_TOKEN</code>
          </p>
        </div>

        <div class="rounded-xl border border-[#E6E8EE] bg-[#F7F8FA] p-4">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted mb-3">
            <i class="fa-solid fa-shield-halved" />
          </span>
          <h4 class="text-[13px] font-semibold text-[#1D1F24]">Security</h4>
          <p class="text-xs text-[#6E7278] mt-1 leading-relaxed">
            Keep your tokens secure and never share them publicly. Rotate them regularly for better security.
          </p>
        </div>

        <div class="rounded-xl border border-[#E6E8EE] bg-[#F7F8FA] p-4">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted mb-3">
            <i class="fa-solid fa-gauge-high" />
          </span>
          <h4 class="text-[13px] font-semibold text-[#1D1F24]">Rate Limits</h4>
          <p class="text-xs text-[#6E7278] mt-1 leading-relaxed">
            API requests are rate limited.
            <a :href="`${sharaformsConfig.links.api_docs}#rate-limits`" target="_blank" class="text-[var(--sf-teal)] hover:underline font-medium">
              Check our documentation
            </a>
            for current limits and best practices.
          </p>
        </div>
      </div>
    </section>

    <!-- Access Token Modal -->
    <UsersSettingsAccessTokenModal
      v-model="accessTokenModal"
      @close="accessTokenModal = false"
    />
  </div>
</template>

<script setup>
import sharaformsConfig from '~/sharaforms.config.js'
import AbilitiesBadges from '~/components/users/settings/access-tokens/AbilitiesBadges.vue'

const accessTokenModal = ref(false)
const alert = useAlert()

// Use TanStack Query instead of Pinia store
const { list, remove: removeToken } = useTokens()

// Fetch tokens
const { data: tokens, isLoading: loading } = list({})

// Delete token mutation
const deleteTokenMutation = removeToken()

// Column pinning state
const columnPinning = ref({
  left: [],
  right: ['actions']
})

// Table columns configuration
const tableColumns = [
  {
    id: 'name',
    accessorKey: 'name',
    header: 'Name',
    enableSorting: true
  },
  {
    id: 'abilities',
    accessorKey: 'abilities',
    header: 'Abilities',
    enableSorting: false
  },
  {
    id: 'actions',
    header: '',
    enableSorting: false,
    enableHiding: false
  }
]

const deleteToken = (token) => {
  alert.confirm("Do you really want to delete this token?", () => {
    deleteTokenMutation.mutateAsync(token.id).then(() => {
      alert.success("Token deleted successfully")
    }).catch(() => {
      alert.error("An error occurred while deleting the token")
    })
  })
}
</script>
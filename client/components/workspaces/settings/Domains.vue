<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>Domains</h2>
      <p>Connect your own domain to publish forms under your brand.</p>
    </div>

    <section class="sf-card sf-card-pad">
      <div class="flex flex-col flex-wrap items-start justify-between gap-4 sm:flex-row sm:items-center mb-5">
        <div class="flex items-center gap-3">
          <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
            <i class="fa-solid fa-globe" />
          </span>
          <div>
            <h3 class="text-[15px] font-semibold text-[#1D1F24]">Custom Domains</h3>
            <p class="text-xs text-[#8E9198] font-medium mt-0.5">
              Manage your custom domains.
            </p>
          </div>
        </div>

        <UButton
          label="Help"
          icon="i-lucide-circle-question-mark"
          variant="outline"
          color="neutral"
          class="btn-ghost !border-[#DEE1E7]"
          @click="crisp.openHelpdeskArticle('how-to-use-my-own-domain-9m77g7')"
        />
      </div>

      <UAlert
        v-if="!canAccessDomains"
        icon="i-lucide-users"
        class="mb-5"
        color="warning"
        variant="subtle"
        title="Pro plan required"
        description="Please upgrade your account to setup a custom domain."
        :actions="[{
          label: 'Upgrade to Pro',
          color: 'warning',
          variant: 'solid',
          onClick: () => openSubscriptionModal({
            modal_title: 'Upgrade to use your own domain',
            modal_description: 'Upgrade to our Pro plan to unlock custom domains and other premium features such as advanced customization, forms analytics, integrations, and more!'
          })
        }]"
      />

      <div class="space-y-5">
        <div class="flex max-w-sm items-center gap-2">
          <UInput
            v-model="newDomain"
            :disabled="!canAccessDomains"
            :variant="canAccessDomains ? 'outline' : 'subtle'"
            placeholder="yourdomain.com"
            class="flex-1"
            @keydown.enter.prevent="addDomain"
          />
          <UButton
            :disabled="!canAccessDomains || !newDomain.trim()"
            icon="i-lucide-plus"
            class="btn-primary"
            @click="addDomain"
          >
            Add
          </UButton>
        </div>

        <div v-if="domains.length > 0" class="max-w-sm space-y-2">
          <div
            v-for="(domain, index) in domains"
            :key="index"
            class="group flex items-center justify-between rounded-lg border border-[var(--sf-border-card)] bg-white p-2.5 transition-all hover:border-[var(--sf-hover-border)] shadow-[0_1px_2px_rgba(23,25,35,0.04)]"
          >
            <span class="flex items-center gap-2 text-[13px] text-[#1D1F24] font-medium min-w-0">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-[var(--sf-bg-subtle)] text-[var(--sf-text-body)] shrink-0">
                <i class="fa-solid fa-globe text-[9px]" />
              </span>
              <span class="truncate">{{ domain }}</span>
            </span>
            <UButton
              :disabled="!canAccessDomains"
              icon="i-lucide-x"
              color="red"
              variant="ghost"
              class="opacity-0 group-hover:opacity-100 transition-opacity"
              @click="removeDomain(index)"
            />
          </div>
        </div>
        <div v-else class="max-w-sm rounded-xl border border-dashed border-[#DEE1E7] bg-[#F7F8FA] p-5 text-center">
          <p class="text-[13px] text-[#6E7278]">
            No custom domains added yet.
          </p>
        </div>

        <UButton
          type="submit"
          :loading="isLoading"
          :disabled="!canAccessDomains || !isChanged"
          class="btn-primary"
          @click="saveChanges"
        >
          Save Domain(s)
        </UButton>
      </div>
    </section>
  </div>
</template>

<script setup>
const { updateCustomDomains } = useWorkspaces()
const alert = useAlert()
const crisp = useCrisp()
const { current: workspace } = useCurrentWorkspace()

const { openSubscriptionModal } = useAppModals()
const { hasFeature } = usePlanFeatures()
const canAccessDomains = computed(() => hasFeature('custom_domain'))

const newDomain = ref('')
const domains = ref([])
const isLoading = ref(false)
const isChanged = ref(false)

onMounted(() => {
  initCustomDomains()
})

watch(
  () => workspace.value,
  () => {
    initCustomDomains()
  },
  { deep: true },
)

const addDomain = () => {
  const domainToAdd = newDomain.value.trim()
  if (domainToAdd) {
    // Remove protocol and path to get the clean domain
    const cleanedDomain = domainToAdd
      .replace(/^https?:\/\//i, '')
      .split('/')[0]

    // Domain validation - matches backend regex: /^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,20}$/
    // Supports: example.com, test.co.uk, subdomain.example.co.uk, etc.
    const domainRegex = /^[a-z0-9]+([-.][a-z0-9]+)*\.[a-z]{2,20}$/i
    if (!domainRegex.test(cleanedDomain)) {
      return alert.error('Invalid domain format. Please use a format like "domain.com" or "subdomain.example.co.uk".')
    }

    if (domains.value.includes(cleanedDomain)) {
      return alert.info('Domain already in the list.')
    }

    domains.value.push(cleanedDomain)
    newDomain.value = ''
    isChanged.value = true
  }
}

const removeDomain = (index) => {
  domains.value.splice(index, 1)
  isChanged.value = true
}

const updateMutation = updateCustomDomains(workspace.value?.id)

const saveChanges = () => {
  if (!workspace.value?.id) return

  isLoading.value = true
  updateMutation.mutateAsync({
    custom_domains: domains.value,
  }).then(() => {
      alert.success('Custom domains saved.')
      isChanged.value = false
      isLoading.value = false
  }).catch((error) => {
      alert.error(error.response?._data?.message ?? 'Failed to update custom domains')
      isLoading.value = false
    })
}

const initCustomDomains = () => {
  if (workspace.value?.custom_domains) {
    domains.value = [...workspace.value.custom_domains]
  } else {
    domains.value = []
  }
  isChanged.value = false
}
</script>
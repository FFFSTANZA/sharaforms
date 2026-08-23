<template>
  <UTooltip 
    :text="tooltipText"
    :content="{ side: 'bottom' }" 
    arrow
  >
    <button
      :disabled="isLoading || !versions.length"
      class="flex items-center justify-center w-8 h-8 rounded-lg transition-all duration-150"
      :class="(isLoading || !versions.length)
        ? 'text-[var(--sf-text-disabled)] cursor-not-allowed'
        : 'text-[var(--sf-text-caption)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-nav-hover-bg)]'"
      @click="isHistoryModalOpen=true"
    >
      <Icon v-if="!isLoading" name="i-lucide-clock" class="w-4 h-4" />
      <loader v-else class="h-4 w-4 animate-spin" />
    </button>
  </UTooltip>

  <UModal
    v-model:open="isHistoryModalOpen"
    :ui="{ content: 'sm:max-w-xl' }"
  >
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div class="grow w-full">
          <h3 class="text-base font-semibold leading-6 text-[var(--sf-text-primary)]">
            Form History
          </h3>
          <p class="mt-1 text-[13px] text-[var(--sf-text-description)]">
            View and restore previous versions of your form
          </p>
        </div>
        <button
          class="flex items-center justify-center w-8 h-8 rounded-lg text-[var(--sf-text-caption)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-nav-hover-bg)] transition-all duration-150"
          @click="isHistoryModalOpen = false"
        >
          <Icon name="i-lucide-x" class="w-4 h-4" />
        </button>
      </div>
    </template>

    <template #body>
      <div class="flow-root">
        <ul role="list" class="-mb-8">
          <li v-for="(version, index) in versions" :key="version.id">
            <div class="relative pb-8">
              <span v-if="index !== versions.length - 1" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-[var(--sf-border-divider)]" aria-hidden="true" />
              <div class="relative flex space-x-3">
                <div>
                  <img
                    v-if="version.user?.photo_url"
                    :src="version.user.photo_url"
                    :alt="version.user?.name || 'User'"
                    class="h-8 w-8 rounded-full bg-[var(--sf-bg-muted)] ring-2 ring-white"
                  />
                  <div
                    v-else
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--sf-nav-active-bg)] ring-2 ring-white"
                  >
                    <span class="text-xs font-semibold leading-none text-[var(--sf-coral-500)]">
                      {{ (version.user?.name || 'U').charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </div>
                
                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                  <div class="w-full">
                    <div class="flex justify-between items-start mb-2">
                      <div class="text-[13px] text-[var(--sf-text-description)]">
                        <span class="font-semibold text-[var(--sf-text-primary)] mr-1">
                          {{ version.user?.name || 'Unknown user' }}
                        </span>
                        
                        <UTooltip :text="formatDate(version.created_at)">
                          <span class="whitespace-nowrap text-[var(--sf-text-caption)]">{{ timeAgo(version.created_at) }}</span>
                        </UTooltip>
                      </div>

                      <button
                        class="flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-medium text-[var(--sf-text-body)] bg-[var(--sf-bg-muted)] hover:bg-[var(--sf-nav-active-bg)] hover:text-[var(--sf-coral-500)] transition-all duration-150"
                        @click="onRestore(version)"
                      >
                        <Icon name="i-lucide-refresh-cw" class="w-3 h-3" />
                        Restore
                      </button>
                    </div>

                    <div v-if="getTags(version).length > 0" class="flex flex-wrap gap-1.5 mt-1">
                      <span 
                        v-for="tag in getTags(version)" 
                        :key="tag.key"
                        class="inline-flex items-center rounded-md bg-[var(--sf-bg-muted)] px-2 py-0.5 text-[11px] font-medium text-[var(--sf-text-body)] ring-1 ring-inset ring-[var(--sf-border-divider)]"
                      >
                        {{ tag.label }}
                      </span>
                    </div>
                    <p v-else class="text-[11px] text-[var(--sf-text-disabled)] italic mt-1">
                      No tracked changes
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </template>
  </UModal>
</template>

<script setup>
import { versionsApi } from '~/api/versions'
import { formsApi } from '~/api/forms'
import { format, formatDistanceToNow } from 'date-fns'

const workingFormStore = useWorkingFormStore()
const { requireFeature } = usePlanFeatures()

const { content: form } = storeToRefs(workingFormStore)
const isHistoryModalOpen = ref(false)
const versions = ref([])
const isLoading = ref(false)

const tooltipText = computed(() => {
  if (isLoading.value) return 'Form History'
  if (!versions.value.length) return 'No versions available'
  return 'Form History'
})

onMounted(() => {
  if (form.value && form.value?.id) {
    fetchVersions()
  }
})

const fetchVersions = async () => {
  isLoading.value = true
  try {
    const response = await versionsApi.list('form', form.value.id)
    versions.value = response || []
  } catch (error) {
    console.error('Failed to fetch form versions:', error)
    versions.value = []
  } finally {
    isLoading.value = false
  }
}

const formatDate = (val) => {
  try {
    return format(new Date(val), 'MMM dd, yyyy h:mm a')
  } catch {
    return ''
  }
}

const timeAgo = (date) => {
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true })
  } catch {
    return ''
  }
}

const getTags = (version) => {
  const tags = []
  for (const [key, change] of Object.entries(version?.diff || {})) {
    const label = humanizeKey(key, change)
    tags.push({ key, label })
  }
  return tags
}

const humanizeKey = (key, change) => {
  const words = String(key).replace(/[_-]+/g, ' ').trim().toLowerCase()
  const capitalized = words.charAt(0).toUpperCase() + words.slice(1)
  if (typeof change?.new === 'boolean' || typeof change?.old === 'boolean') {
    return `${capitalized} ${change?.new ? 'enabled' : 'disabled'}`
  }
  return `${capitalized} changed`
}

const onRestore = async (version) => {
  if(!requireFeature('form_versioning', 'Upgrade to restore form history')) return
  useAlert().confirm('Are you sure you want to restore this version?', () => restoreVersion(version))
}

const restoreVersion = async (version) => {
  try {
    const response = await formsApi.get(form.value.slug, { params: { version_id: version.id } })
    workingFormStore.reset()
    workingFormStore.set(useForm(response))
    useAlert().success('Version restored successfully on editor. Please publish form to save the changes.')
    isHistoryModalOpen.value = false
  } catch (error) {
    useAlert().error(error.data?.message || 'Failed to restore version')
  }
}
</script>

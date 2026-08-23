<template>
  <div class="flex flex-1 items-center gap-2">
    <div
      v-if="integration.provider"
      class="hidden md:flex items-center"
    >
      <UBadge
        :label="integration.provider.name"
        color="neutral"
        variant="subtle"
        size="sm"
        class="max-w-[300px] truncate"
      />
    </div>

    <div
      v-if="integration.data?.database_url"
      class="ml-auto flex items-center"
    >
      <UButton
        :to="integration.data.database_url"
        target="_blank"
        color="neutral"
        size="sm"
        variant="outline"
        icon="simple-icons:notion"
        trailing-icon="lucide:external-link"
        label="Open"
      />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  integration: {
    type: Object,
    required: true,
  },
  form: {
    type: Object,
    required: true,
  }
})

const { invalidateIntegrations } = useFormIntegrations()
let interval = null

onMounted(() => {
  if (!props.integration.data || !props.integration.data.database_url) {
    interval = setInterval(() => invalidateIntegrations(props.form.id), 3000)
    setTimeout(() => { clearInterval(interval) }, 30000)
  }
})

onBeforeUnmount(() => {
  if (interval) {
    clearInterval(interval)
  }
})
</script>

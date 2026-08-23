<template>
  <div class="flex flex-1 items-center">
    <div
      v-if="integration"
      class="hidden md:block space-y-1"
    >
      <UBadge
        :label="label"
        color="neutral"
        variant="subtle"
        size="sm"
        class="max-w-[300px] truncate"
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

const label = computed(() => {
  if (!props.integration?.data) return ''
  return props.integration.data.title_template || 'Issues created on submission'
})
</script>

<template>
  <span
    v-if="value"
    class="-mb-2"
  >
    <UButton
      :to="paymentUrl"
      size="xs"
      variant="soft"
      icon="i-lucide-credit-card"
      trailing-icon="i-lucide-external-link"
      label="Payment"
      target="_blank"
    />
  </span>
</template>

<script setup>
const props = defineProps({
  value: {
    type: Object,
  },
})

const paymentUrl = computed(() => {
  if (!props.value) return null
  const isLocal = useRuntimeConfig().public.env === 'local'
  return `https://dashboard.stripe.com${isLocal ? '/test' : ''}/payments/${props.value}`
})
</script>

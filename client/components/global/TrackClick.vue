<template>
  <span @click="track" class="contents">
    <slot />
  </span>
</template>

<script setup>
const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  properties: {
    type: Object,
    default: () => ({}),
  },
})

const { logEvent } = usePostHog()

function track() {
  if (props.name) {
    logEvent(props.name, props.properties)
  }
}
</script> 
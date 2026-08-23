<template>
  <div
    :class="[size, colorClasses]"
    class="rounded-full text-xs truncate text-center flex items-center justify-center overflow-hidden shrink-0"
  >
  <img
    v-if="isUrl(workspace.icon)"
    :src="workspace.icon"
    :alt="`${workspace.name} icon`"
          class="flex-shrink-0 rounded-sm"
    :class="size"
  >
    <p v-else
      class="font-semibold"
      v-text="displayText"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  size: {
    type: String,
    default: 'h-6 w-6',
  },
})

const colorClasses = computed(() => {
    if (!props.workspace?.id) {
        return 'bg-[#E6E8EE] text-[#8E9198]'
    }

    const colors = [
        { bg: 'bg-[#E4F4F8]', text: 'text-[#0891b2]' },
        { bg: 'bg-[#FDF6EB]', text: 'text-[#d97706]' },
        { bg: 'bg-[#EEF0FD]', text: 'text-[#6366f1]' },
        { bg: 'bg-[#EFF8F1]', text: 'text-[#16a34a]' },
        { bg: 'bg-[#fce7e2]', text: 'text-[#ff5c38]' },
        { bg: 'bg-[#E4F4F8]', text: 'text-[#0891b2]' },
        { bg: 'bg-[#FDF6EB]', text: 'text-[#d97706]' },
        { bg: 'bg-[#EEF0FD]', text: 'text-[#6366f1]' },
        { bg: 'bg-[#EFF8F1]', text: 'text-[#16a34a]' },
        { bg: 'bg-[#fce7e2]', text: 'text-[#ff5c38]' },
    ]

    const colorPair = colors[props.workspace.id % colors.length]

    return `${colorPair.bg} ${colorPair.text}`
})

const isUrl = (str) => {
  try {
    new URL(str)
  }
  catch {
    return false
  }
  return true
}

const displayText = computed(() => {
  if (props.workspace.icon) {
    return props.workspace.icon
  }
  // Fallback to first letter of workspace name, capitalized
  return props.workspace.name ? props.workspace.name.charAt(0).toUpperCase() : ''
})
</script>

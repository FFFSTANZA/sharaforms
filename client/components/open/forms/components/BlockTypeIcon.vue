<template>
  <div
    class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors duration-150"
    :class="[bgClass, textClass]"
  >
    <Icon
      :name="icon"
      class="h-3.5 w-3.5"
    />
  </div>
</template>

<script setup>
import blocksTypes from '~/data/blocks_types.json'

const extraBlocksTypes = {
  status: {
    icon: 'i-lucide-clipboard-check',
    bg_class: 'bg-[var(--sf-bg-muted)]',
    text_class: 'text-[var(--sf-text-body)]',
  },
  ip_address: {
    icon: 'i-lucide-globe',
    bg_class: 'bg-[var(--sf-bg-muted)]',
    text_class: 'text-[var(--sf-text-body)]',
  },
}

const props = defineProps({
  type: {
    type: String,
    required: true
  },
  bgClass: {
    type: String,
    default: ''
  },
  textClass: {
    type: String,
    default: ''
  }
})

const blockType = computed(() => blocksTypes[props.type] || extraBlocksTypes[props.type])
const bgClass = computed(() => props.bgClass || blockType.value?.bg_class || '')
const textClass = computed(() => props.textClass || blockType.value?.text_class || '')
const icon = computed(() => blockType.value?.icon || '')
</script>
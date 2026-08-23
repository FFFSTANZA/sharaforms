<template>
  <div class="relative">
    <hr
      v-if="showLine"
      class="border-t border-[var(--sf-border-divider)] mb-5 mt-7 -mx-4"
    >
    <div
      :class="[
        'flex gap-x-2.5 items-center',
        showLine ? 'mb-3 px-2 -mx-2 absolute -top-[10px] bg-[var(--sf-bg-surface)]' : 'mt-6 mb-3'
      ]"
    >
      <div class="w-6 h-6 rounded-md bg-[var(--sf-bg-muted)] flex items-center justify-center flex-shrink-0">
        <Icon
          :name="icon"
          :class="['w-3.5 h-3.5 text-[var(--sf-text-caption)]', iconClass]"
        />
      </div>
      <h3 class="text-[12px] font-semibold uppercase tracking-[0.06em] text-[var(--sf-text-label)]">
        {{ title }}
      </h3>
    </div>
  </div>
</template>
  
<script setup>
defineProps({
icon: {
    type: String,
    required: true
},
title: {
    type: String,
    required: true
},
showLine: {
    type: Boolean,
    default: true
},
iconClass: {
    type: String,
    default: ''
}
})
</script>
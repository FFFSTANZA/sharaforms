<template>
  <div class="flex gap-2">
    <div
      data-testid="copy-content-value"
      class="flex-1 truncate sm:w-auto border border-[var(--sf-border-button)] rounded-xl px-3 py-2 flex-grow select-all bg-[var(--sf-bg-muted)]/30 relative"
    >
      <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-r from-transparent to-[var(--sf-bg-muted)]/50 rounded-r-xl"></div>
      <p class="select-all text-[var(--sf-text-primary)] text-[13px] font-medium truncate">
        {{ content }}
      </p>
    </div>
    <div class="shrink-0">
      <TrackClick
        v-if="trackingEvent"
        :name="trackingEvent"
        :properties="trackingProperties"
      >
        <button
          class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[13px] font-semibold transition-all duration-150"
          :class="copySuccess
            ? 'bg-[var(--sf-green)] text-white'
            : 'btn-primary'
          "
          data-testid="copy-content-button"
          @click.prevent="copyToClipboard"
        >
          <Icon :name="copySuccess ? 'i-lucide-check' : 'i-lucide-clipboard-list'" class="w-4 h-4" />
          <span class="hidden md:inline">{{ copySuccess ? 'Copied!' : label }}</span>
        </button>
      </TrackClick>
      <button
        v-else
        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[13px] font-semibold transition-all duration-150"
        :class="copySuccess
          ? 'bg-[var(--sf-green)] text-white'
          : 'btn-primary'
        "
        data-testid="copy-content-button"
        @click.prevent="copyToClipboard"
      >
        <Icon :name="copySuccess ? 'i-lucide-check' : 'i-lucide-clipboard-list'" class="w-4 h-4" />
        <span class="hidden md:inline">{{ copySuccess ? 'Copied!' : label }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref } from "vue"
import TrackClick from '~/components/global/TrackClick.vue'

const { copy } = useClipboard()

const props = defineProps({
  content: {
    type: String,
    required: true,
  },
  isDraft: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    default: "Copy Link",
  },
  trackingEvent: {
    type: String,
    default: null,
  },
  trackingProperties: {
    type: Object,
    default: () => ({}),
  },
})

const copySuccess = ref(false)

const copyToClipboard = () => {
  if (import.meta.server) return
  
  copy(props.content)
  
  // Show success state
  copySuccess.value = true
  
  // Reset after 2 seconds
  setTimeout(() => {
    copySuccess.value = false
  }, 2000)
}
</script>

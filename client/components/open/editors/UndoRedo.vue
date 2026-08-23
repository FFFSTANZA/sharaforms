<template>
  <div class="flex items-center gap-0.5 bg-[var(--sf-bg-muted)]/60 rounded-lg p-0.5">
    <UTooltip
      text="Undo"
      :kbds="['meta','Z']"
      :content="{ side: 'left' }"
      arrow
    >
      <button
        :disabled="!canUndo"
        class="flex items-center justify-center w-7 h-7 rounded-md transition-all duration-150"
        :class="canUndo
          ? 'text-[var(--sf-text-body)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-bg-surface)]'
          : 'text-[var(--sf-text-disabled)] cursor-not-allowed'"
        @click="undo"
      >
        <Icon name="i-lucide-undo" class="w-3.5 h-3.5" />
      </button>
    </UTooltip>
    <UTooltip
      text="Redo"
      :kbds="['meta','Shift','Z']"
      :content="{ side: 'right' }"
      arrow
    >
      <button
        :disabled="!canRedo"
        class="flex items-center justify-center w-7 h-7 rounded-md transition-all duration-150"
        :class="canRedo
          ? 'text-[var(--sf-text-body)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-bg-surface)]'
          : 'text-[var(--sf-text-disabled)] cursor-not-allowed'"
        @click="redo"
      >
        <Icon name="i-lucide-redo" class="w-3.5 h-3.5" />
      </button>
    </UTooltip>
  </div>
</template>

<script setup>
const props = defineProps({
  editor: { type: String, default: 'form' }
})

let workingStore = useWorkingFormStore()
if (props.editor === 'view') {
  workingStore = useWorkingViewStore()
}

const { undo, redo, clearHistory } = workingStore
const { canUndo, canRedo } = storeToRefs(workingStore)

defineShortcuts({
  meta_z: {
    whenever: [canUndo],
    handler: () => {
      undo()
    }
  },
  meta_shift_z: {
    whenever: [canRedo],
    handler: () => {
      redo()
    }
  }
})
onMounted(() => {
  setTimeout(() => { clearHistory() }, 500)
})
</script>

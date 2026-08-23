<template>
  <transition @leave="handleLeave">
    <div
      v-if="show"
      ref="elementRef"
      class="absolute top-0 h-[calc(100vh-55px)] right-0 lg:relative bg-[var(--sf-bg-surface)] border-l border-[var(--sf-border-card)] flex-shrink-0 z-30 shadow-[-4px_0_24px_-8px_rgba(23,25,35,0.08)] lg:shadow-none"
      :class="[
        isResizable ? '' : 'w-full md:w-1/2 lg:w-2/5',
        widthClass
      ]"
      :style="isResizable ? dynamicStyles : {}"
    >
      <ResizeHandle
        :show="isResizable"  
        direction="right"
        @start-resize="startResize"
      />
      
      <OverlayScrollbarsComponent
        defer
        class="h-full min-h-0"
      >
        <slot />
      </OverlayScrollbarsComponent>
    </div>
  </transition>
</template>

<script setup>
import { slideRight, useMotion } from '@vueuse/motion'
import { watch, computed, nextTick } from 'vue'
import { useResizable } from '~/composables/components/useResizable'
import ResizeHandle from '@/components/global/ResizeHandle.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  widthClass: {
    type: String,
    default: 'md:max-w-[20rem]',
  },
  resizable: {
    type: Boolean,
    default: false,
  },
})

// Sidebar resizing using composable
const { 
  elementRef, 
  isResizable: isResizableBase, 
  dynamicStyles, 
  startResize
} = useResizable({
  storageKey: 'formEditorRightSidebarWidth',
  defaultWidth: 315,
  direction: 'right',
  maxWidth: () => Math.min(600, window.innerWidth * 0.6)
})

// Motion animation
const sidebarMotion = ref(null)

// Enable resizing only when prop is true and breakpoint allows it
const isResizable = computed(() => props.resizable && isResizableBase.value)

// Guarantee the transition completes even if the motion instance is missing
// or the animation fails, so the sidebar element is always removed from the DOM.
function handleLeave(_el, done) {
  try {
    if (sidebarMotion.value?.leave) {
      sidebarMotion.value.leave(done)
      return
    }
  } catch (error) {
    console.error('Sidebar leave animation failed', error)
  }
  done()
}

// Watch for show prop changes (existing functionality)
watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      nextTick(() => {
        if (elementRef.value) {
          sidebarMotion.value = useMotion(elementRef.value, slideRight)
        }
      })
    }
  },
  { immediate: true },
)
</script>

<template>
  <div
    class="pb-16"
    @keydown="handleKeydown"
    tabindex="-1"
  >
    <div class="px-4 py-3 border-b border-[var(--sf-border-divider)] sticky top-0 z-10 bg-[var(--sf-bg-surface)]">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold text-[14px] text-[var(--sf-text-primary)]">
          Add Block
        </h3>
        <button
          class="flex items-center justify-center w-7 h-7 rounded-lg text-[var(--sf-text-caption)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-nav-hover-bg)] transition-all duration-150"
          @click="closeSidebar"
        >
          <Icon name="i-lucide-x" class="w-4 h-4" />
        </button>
      </div>
      <div class="relative">
        <Icon name="i-lucide-search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--sf-text-disabled)]" />
        <input
          ref="searchInput"
          v-model="searchTerm"
          :autofocus="canAutofocus"
          class="w-full pl-9 pr-8 py-2 rounded-xl border border-[var(--sf-border-button)] bg-[var(--sf-bg-muted)]/50 text-[13px] text-[var(--sf-text-primary)] placeholder:text-[var(--sf-text-disabled)] focus:outline-none focus:ring-2 focus:ring-[var(--sf-coral-500)]/20 focus:border-[var(--sf-coral-500)] transition-all duration-150"
          placeholder="Search blocks..."
          @keydown.down.prevent="handleKeydown"
          @keydown.up.prevent="handleKeydown"
          @keydown.enter.prevent="handleKeydown"
          @keydown.esc="handleKeydown"
        />
        <button
          v-if="searchTerm?.length"
          class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center justify-center w-5 h-5 rounded-full text-[var(--sf-text-disabled)] hover:text-[var(--sf-text-body)] hover:bg-[var(--sf-nav-hover-bg)] transition-colors"
          @click="searchTerm = ''"
        >
          <Icon name="i-lucide-circle-x" class="w-3.5 h-3.5" />
        </button>
      </div>
      <div class="mt-3">
        <AiFieldGenerator
          class="w-full"
        />
      </div>
    </div>

    <div class="py-3 px-4">
      <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--sf-text-label)] mb-2">
        Input Blocks
      </p>
      <VueDraggable
        :model-value="filteredInputBlocks"
        :group="{ name: 'form-elements', pull: 'clone', put: false }"
        class="flex flex-col -mx-1"
        :delay="150"
        :delay-on-touch-only="true"
        :touch-start-threshold="3"
        :sort="false"
        :clone="handleInputClone"
        ghost-class="ghost-item"
        item-key="id"
        @start="workingFormStore.draggingNewBlock=true"
        @end="workingFormStore.draggingNewBlock=false"
      >
        <template #default>
          <div
            v-for="(element, index) in filteredInputBlocks"
            :key="element.id || element.name"
            :ref="(el) => setBlockRef(el, index)"
            :data-block-index="index"
            :class="[
              'flex rounded-xl items-center gap-2.5 px-3 py-2.5 group cursor-pointer transition-all duration-150',
              selectedIndex === index
                ? 'bg-[var(--sf-nav-active-bg)] ring-1 ring-inset ring-[var(--sf-coral-500)]/20'
                : 'hover:bg-[var(--sf-nav-hover-bg)]'
            ]"
            role="button"
            :tabindex="selectedIndex === index ? 0 : -1"
            @click.prevent="addBlock(element.name)"
            @keydown.enter.prevent="addBlock(element.name)"
          >
            <BlockTypeIcon :type="element.name" />
            <span class="flex-1 text-[13px] font-medium text-[var(--sf-text-body)] group-hover:text-[var(--sf-text-primary)] transition-colors">
              {{ element.title }}
            </span>
            <Icon
              v-if="element.auth_required && !authenticated"
              name="lucide:lock-keyhole"
              class="text-[var(--sf-text-disabled)] w-3.5 h-3.5"
            />
          </div>
          <p
            v-if="searchTerm && filteredInputBlocks.length === 0"
            class="text-[var(--sf-text-disabled)] text-xs px-3 py-2 text-center"
          >
            No input blocks match your search.
          </p>
        </template>
      </VueDraggable>
    </div>
    <div class="px-4 border-t border-[var(--sf-border-divider)] mb-4 pt-3">
      <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--sf-text-label)] mb-2">
        Layout Blocks
      </p>
      <VueDraggable
        :model-value="filteredLayoutBlocks"
        :group="{ name: 'form-elements', pull: 'clone', put: false }"
        class="flex flex-col -mx-1"
        :delay="150"
        :delay-on-touch-only="true"
        :touch-start-threshold="3"
        :sort="false"
        :clone="handleInputClone"
        ghost-class="ghost-item"
        item-key="id"
        @start="workingFormStore.draggingNewBlock=true"
        @end="workingFormStore.draggingNewBlock=false"
      >
        <template #default>
          <div
            v-for="(element, index) in filteredLayoutBlocks"
            :key="element.id || element.name"
            :ref="(el) => setBlockRef(el, filteredInputBlocks.length + index)"
            :data-block-index="filteredInputBlocks.length + index"
            :class="[
              'flex rounded-xl items-center gap-2.5 px-3 py-2.5 group cursor-pointer transition-all duration-150',
              selectedIndex === (filteredInputBlocks.length + index)
                ? 'bg-[var(--sf-nav-active-bg)] ring-1 ring-inset ring-[var(--sf-coral-500)]/20'
                : 'hover:bg-[var(--sf-nav-hover-bg)]'
            ]"
            role="button"
            :tabindex="selectedIndex === (filteredInputBlocks.length + index) ? 0 : -1"
            @click.prevent="addBlock(element.name)"
            @keydown.enter.prevent="addBlock(element.name)"
          >
            <BlockTypeIcon :type="element.name" />
            <span class="flex-1 text-[13px] font-medium text-[var(--sf-text-body)] group-hover:text-[var(--sf-text-primary)] transition-colors">
              {{ element.title }}
            </span>
            <Icon
              v-if="element.auth_required && !authenticated"
              name="lucide:lock-keyhole"
              class="text-[var(--sf-text-disabled)] w-3.5 h-3.5"
            />
          </div>
          <p
            v-if="searchTerm && filteredLayoutBlocks.length === 0"
            class="text-[var(--sf-text-disabled)] text-xs px-3 py-2 text-center"
          >
            No layout blocks match your search.
          </p>
        </template>
      </VueDraggable>
    </div>
  </div>
</template>

<script setup>
import { VueDraggable } from 'vue-draggable-plus'
import blocksTypes from '~/data/blocks_types.json'
import BlockTypeIcon from '../BlockTypeIcon.vue'
import AiFieldGenerator from './components/AiFieldGenerator.vue'
import Fuse from 'fuse.js'

const workingFormStore = useWorkingFormStore()
const { isAuthenticated: authenticated } = useIsAuthenticated()

const formStyle = computed(() => workingFormStore.content?.presentation_style || 'classic')

// Auto-focusing the search input pops the on-screen keyboard on touch
// devices, covering the block palette. Only autofocus with a mouse.
const canAutofocus = import.meta.client
  ? window.matchMedia('(hover: hover) and (pointer: fine)').matches
  : false

const allowedBlocks = computed(() => {
  const all = Object.values(blocksTypes)
  return all.filter(block => {
    const modes = block.available_in || ['classic', 'focused', 'spotlight']
    return modes.includes(formStyle.value)
  })
})

const searchTerm = ref('')
const normalizedSearch = computed(() => searchTerm.value.trim().toLowerCase())

const fuseOptions = {
  keys: ['title', 'name'],
  threshold: 0.3,
  ignoreLocation: true,
  includeScore: false,
}

// Create a single Fuse instance that's reused
const fuseInstance = computed(() => {
  return new Fuse(allowedBlocks.value, fuseOptions)
})

// Search through all blocks once
const filteredBlocks = computed(() => {
  if (!normalizedSearch.value) return allowedBlocks.value
  return fuseInstance.value.search(normalizedSearch.value).map(r => r.item)
})

// Split filtered results into input and layout blocks
const filteredInputBlocks = computed(() => {
  return filteredBlocks.value.filter(block => !block.name.startsWith('nf-'))
})

const filteredLayoutBlocks = computed(() => {
  return filteredBlocks.value.filter(block => block.name.startsWith('nf-'))
})

// Combined flat list of all blocks for keyboard navigation
const allFilteredBlocks = computed(() => {
  return [...filteredInputBlocks.value, ...filteredLayoutBlocks.value]
})

const selectedIndex = ref(-1)
const blockRefsMap = new Map()
const searchInput = ref(null)

// Reset selection when search changes
watch([filteredInputBlocks, filteredLayoutBlocks], () => {
  selectedIndex.value = -1
  blockRefsMap.clear()
})

// Set block refs for scrolling
const setBlockRef = (el, index) => {
  if (el) {
    blockRefsMap.set(index, el)
  } else {
    blockRefsMap.delete(index)
  }
}

// Scroll selected block into view
const scrollToSelected = () => {
  nextTick(() => {
    if (selectedIndex.value >= 0) {
      // Try to use ref map first, fallback to querySelector
      const element = blockRefsMap.get(selectedIndex.value) || 
        document.querySelector(`[data-block-index="${selectedIndex.value}"]`)
      
      if (element) {
        element.scrollIntoView({
          behavior: 'smooth',
          block: 'nearest'
        })
      }
    }
  })
}

// Handle keyboard navigation
const handleKeydown = (event) => {
  const totalBlocks = allFilteredBlocks.value.length
  
  if (totalBlocks === 0) return

  // Only handle arrow keys, Enter, and Escape
  if (!['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(event.key)) {
    return
  }

  event.preventDefault()
  event.stopPropagation()

  switch (event.key) {
    case 'ArrowDown':
      // If starting from -1, go to 0, otherwise increment
      selectedIndex.value = selectedIndex.value < totalBlocks - 1 
        ? selectedIndex.value + 1 
        : 0
      scrollToSelected()
      break
      
    case 'ArrowUp':
      // If at 0 or -1, wrap to last item, otherwise decrement
      selectedIndex.value = selectedIndex.value > 0 
        ? selectedIndex.value - 1 
        : totalBlocks - 1
      scrollToSelected()
      break
      
    case 'Enter':
      if (selectedIndex.value >= 0 && selectedIndex.value < totalBlocks.length) {
        const selectedBlock = allFilteredBlocks.value[selectedIndex.value]
        if (selectedBlock) {
          addBlock(selectedBlock.name)
        }
      } else if (totalBlocks > 0) {
        // If no selection, select and add the first block
        selectedIndex.value = 0
        const firstBlock = allFilteredBlocks.value[0]
        if (firstBlock) {
          addBlock(firstBlock.name)
        }
      }
      break
      
    case 'Escape':
      if (searchTerm.value) {
        searchTerm.value = ''
        selectedIndex.value = -1
        nextTick(() => {
          const inputEl = searchInput.value?.$el?.querySelector('input') || searchInput.value?.$el
          if (inputEl) {
            inputEl.focus()
          }
        })
      }
      break
  }
}

const closeSidebar = () => {
  workingFormStore.closeAddFieldSidebar()
}

const addBlock = (type) => {
  workingFormStore.addBlock(type)
}

const handleInputClone = (item) => {
  return item.name
}

workingFormStore.resetBlockForm()
</script>

<style lang="scss" scoped>
.ghost-item {
  @apply rounded-md w-full col-span-full;
  background: var(--sf-nav-active-bg);
}
</style>

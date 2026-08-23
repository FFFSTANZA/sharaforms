<template>
  <div 
    :class="{ 'sidebar-bounce': sidebarBounce }"
    class="sidebar-container"
  >
    <div class="px-3 py-2.5 border-b border-[var(--sf-border-divider)] sticky top-0 z-20 bg-[var(--sf-bg-surface)]">
      <div class="flex items-center">
        <button
          class="flex items-center justify-center w-7 h-7 rounded-lg text-[var(--sf-text-caption)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-nav-hover-bg)] transition-all duration-150"
          @click="closeSidebar"
        >
          <Icon name="i-lucide-x" class="w-4 h-4" />
        </button>
        <template v-if="field">
          <div class="ml-1.5 flex flex-grow items-center min-w-0 gap-x-2">
            <div class="flex-grow" />
            <BlockTypeIcon
              :type="field.type"
            />
            <span
              v-if="blocksTypes[field.type]"
              class="text-[13px] font-medium text-[var(--sf-text-body)]"
            >
              {{ blocksTypes[field.type].title }}
            </span>
            
            <UDropdownMenu
              :items="dropdownItems"
              :content="{ side: 'bottom', align: 'start' }"
              arrow
            >
              <button class="flex items-center justify-center w-7 h-7 rounded-lg text-[var(--sf-text-caption)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-nav-hover-bg)] transition-all duration-150">
                <Icon name="i-lucide-circle-ellipsis" class="w-4 h-4" />
              </button>
            </UDropdownMenu>
          </div>
        </template>
      </div>
    </div>

    <template v-if="field">
      <div class="bg-[var(--sf-bg-muted)]/50 border-b border-[var(--sf-border-divider)]">
        <div class="flex">
          <button
            v-for="tab in tabItems"
            :key="tab.value"
            class="flex-1 py-2.5 text-[12px] font-semibold text-center transition-all duration-150 border-b-2"
            :class="activeTab === tab.value
              ? 'text-[var(--sf-coral-500)] border-[var(--sf-coral-500)]'
              : 'text-[var(--sf-text-caption)] border-transparent hover:text-[var(--sf-text-body)]'"
            @click="activeTab = tab.value"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>
      <div v-if="activeTab === 'options'">
        <FieldOptions
          v-if="!isBlockField"
          :form="form"
          :field="field"
        />
        <BlockOptions
          v-else
          :form="form"
          :field="field"
        />
      </div>
      <div v-else-if="activeTab === 'logic'">
        <FormBlockLogicEditor
          class="py-2 px-4"
          :form="form"
          :field="field"
        />
      </div>
      <div v-else-if="activeTab === 'validation'">
        <custom-field-validation
          class="py-2 px-4"
          :form="form"
          :field="field"
        />
      </div>
    </template>
    <div
      v-else
      class="text-center p-10 text-[13px] text-[var(--sf-text-disabled)]"
    >
      Click on a field to edit it.
    </div>
  </div>
</template>


<script setup>
import { storeToRefs } from 'pinia'
import FieldOptions from './components/FieldOptions.vue'
import BlockOptions from './components/BlockOptions.vue'
import BlockTypeIcon from '../components/BlockTypeIcon.vue'
import blocksTypes from '~/data/blocks_types.json'
import FormBlockLogicEditor from '../components/form-logic-components/FormBlockLogicEditor.vue'
import CustomFieldValidation from '../components/CustomFieldValidation.vue'

const workingFormStore = useWorkingFormStore()
const { content: form, sidebarBounce } = storeToRefs(workingFormStore)

const selectedFieldIndex = computed(() => workingFormStore.selectedFieldIndex)

const field = computed(() => {
  return form.value && selectedFieldIndex.value !== null
    ? form.value.properties[selectedFieldIndex.value]
    : null
})

// Only set the page once when the component is mounted
// This prevents page jumps when editing field properties
onMounted(() => {
  if (selectedFieldIndex.value !== null) {
    if (workingFormStore.structureService) {
      workingFormStore.structureService.setPageForField(selectedFieldIndex.value)
    }
  }
})

const isBlockField = computed(() => {
  return field.value && field.value.type && typeof field.value.type === 'string' && field.value.type.startsWith('nf')
})

const typeCanBeChanged = computed(() => {
  const textualTypes = ["text", "rich_text", "url", "email", "phone_number", "number"]
  const selectionTypes = ["select", "multi_select"]
  const scaleTypes = ["rating", "scale", "slider"]
  const booleanTypes = ["checkbox"]
  return [
    ...textualTypes,
    ...selectionTypes,
    ...scaleTypes,
    ...booleanTypes,
  ].includes(field.value.type)
})

// Composable for field type changing logic
const useFieldTypeChange = () => {

  const onChangeType = (newType) => {
    if (["select", "multi_select"].includes(field.value.type)) {
      field.value[newType] = field.value[field.value.type] // Set new options with new type
      delete field.value[field.value.type] // remove old type options
    }

    // Preserve/downgrade content when converting between text and rich_text
    if ((field.value.type === 'text' && newType === 'rich_text') || (field.value.type === 'rich_text' && newType === 'text')) {
      // keep existing value in place; renderer handles component mapping
    }

    field.value.type = newType
  }

  const getChangeTypeOptions = (currentType) => {
    const textualTypes = ["text", "rich_text", "url", "email", "phone_number", "number"]
    const selectionTypes = ["select", "multi_select"]
    const scaleTypes = ["rating", "scale", "slider"]
    const booleanTypes = ["checkbox"]

    let candidateTypes = []

    if (textualTypes.includes(currentType)) {
      candidateTypes = [...textualTypes, ...booleanTypes]
    } else if (selectionTypes.includes(currentType)) {
      candidateTypes = [...selectionTypes]
    } else if (scaleTypes.includes(currentType)) {
      candidateTypes = [...scaleTypes]
    } else if (booleanTypes.includes(currentType)) {
      candidateTypes = [...textualTypes, ...booleanTypes]
    }

    return candidateTypes
      .filter((type) => type !== currentType)
      .map((type) => {
        const meta = blocksTypes[type] || {}
        return {
          label: meta.title || type,
          value: type,
          icon: meta.icon || undefined,
          onClick: () => onChangeType(type)
        }
      })
  }

  return {
    getChangeTypeOptions
  }
}

const { getChangeTypeOptions } = useFieldTypeChange()

// Prevent destructive keyboard shortcuts from firing while the user is
// typing inside an input/textarea/contenteditable (e.g. Ctrl+Backspace
// deleting the current field while editing its name).
function isEditableFocused() {
  const el = document.activeElement
  if (!el) return false
  const tag = el.tagName
  return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el.isContentEditable
}

function removeBlock() {
  if (isEditableFocused()) return
  workingFormStore.removeField(field.value)
}

function closeSidebar() {
  // Explicitly clear the selected field index to prevent issues with subsequent block additions
  workingFormStore.selectedFieldIndex = null
  workingFormStore.closeEditFieldSidebar()
}

const dropdownItems = computed(() => {
  const baseItems = [
    [{
      label: 'Copy field ID',
      icon: 'i-lucide-clipboard',
      onClick: () => {
        navigator.clipboard.writeText(field.value.id)
        useAlert().success('Field ID copied to clipboard')
      }
    }],
    [{
      label: 'Duplicate',
      icon: 'i-lucide-copy',
      kbds: ['meta', 'd'],
      onClick: () => {
        if (isEditableFocused()) return
        workingFormStore.duplicateField(field.value)
      }
    }]
  ]

  // Add change type option with nested menu if type can be changed
  if (typeCanBeChanged.value && !isBlockField.value) {
    const changeTypeOptions = getChangeTypeOptions(field.value.type)
    if (changeTypeOptions.length > 0) {
      baseItems.push([{
        label: 'Change type',
        icon: 'i-lucide-arrow-left-right',
        children: [changeTypeOptions]
      }])
    }
  }

  // Add remove option
  baseItems.push([{
    label: 'Remove',
    icon: 'i-lucide-trash-2',
    color: 'error',
    kbds: ['meta', 'backspace'],
    onClick: removeBlock
  }])

  return baseItems
})
defineShortcuts(extractShortcuts(dropdownItems.value))

const activeTab = ref('options')

const tabItems = computed(() => {
  const commonTabs = [
    { label: 'Options', value: 'options' },
    { label: 'Logic', value: 'logic' },
  ]

  if (isBlockField.value) {
    return commonTabs
  } else {
    return [
      ...commonTabs,
      { label: 'Validation', value: 'validation' },
    ]
  }
})

</script>

<style scoped>
.sidebar-container {
  transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.sidebar-bounce {
  animation: bounce-left 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

@keyframes bounce-left {
  0% {
    transform: translateX(0);
  }
  20% {
    transform: translateX(-6px);
  }
  40% {
    transform: translateX(0);
  }
  60% {
    transform: translateX(-3px);
  }
  80% {
    transform: translateX(0);
  }
  90% {
    transform: translateX(-1px);
  }
  100% {
    transform: translateX(0);
  }
}
</style>

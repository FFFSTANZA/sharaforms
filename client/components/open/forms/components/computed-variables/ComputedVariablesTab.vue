<template>
  <div class="px-1 py-1">
    <!-- Header Card -->
    <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
      <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2.5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-teal-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-square-root-variable text-[12px] text-[var(--sf-teal)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Variables</h3>
        </div>
        <UButton
          icon="i-lucide-plus"
          class="btn-primary flex-shrink-0 whitespace-nowrap"
          size="xs"
          @click="openCreateModal"
        >
          Add Variable
        </UButton>
      </div>
      <p class="text-sm text-[var(--sf-text-caption)]">
        Create calculated values from your form fields. Use them in emails, thank you messages, and integrations.
      </p>
    </div>

    <!-- Empty State -->
    <div
      v-if="!computedVariables || computedVariables.length === 0"
      class="text-center py-12 border-2 border-dashed border-[var(--sf-border-button)] rounded-lg"
    >
      <Icon
        name="i-lucide-variable"
        class="h-12 w-12 mx-auto text-[var(--sf-text-muted)]"
      />
      <h3 class="mt-4 text-sm font-semibold text-[var(--sf-text-primary)]">
        No variables yet
      </h3>
      <p class="mt-2 text-sm text-[var(--sf-text-caption)] max-w-sm mx-auto">
        Variables let you calculate values from form responses. Use them in emails, thank you messages, and integrations.
      </p>
      <div class="mt-4 text-sm text-[var(--sf-text-caption)]">
        <p class="font-medium mb-2">Examples:</p>
        <ul class="space-y-1">
          <li>• Calculate order totals</li>
          <li>• Create personalized greetings</li>
          <li>• Categorize responses with IF conditions</li>
        </ul>
      </div>
      <UButton
        class="mt-6 btn-primary"
        icon="i-lucide-plus"
        @click="openCreateModal"
      >
        Create Variable
      </UButton>
    </div>

    <!-- Variables List -->
    <div
      v-else
      class="space-y-3"
    >
      <ComputedVariableCard
        v-for="variable in computedVariables"
        :key="variable.id"
        :variable="variable"
        :form="form"
        @edit="openEditModal(variable)"
        @delete="deleteVariable(variable)"
      />
    </div>

    <!-- Info Footer -->
    <div
      v-if="computedVariables && computedVariables.length > 0"
      class="mt-6 p-3 bg-[var(--sf-teal-light)] rounded-lg"
    >
      <div class="flex items-start gap-2">
        <Icon
          name="i-lucide-info"
          class="h-5 w-5 text-[var(--sf-teal)] shrink-0 mt-0.5"
        />
        <p class="text-sm text-[var(--sf-teal)]">
          Insert variables anywhere you can use @ mentions - in emails, thank you messages, redirect URLs, and integrations.
        </p>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <ComputedVariableModal
      v-model="showModal"
      :variable="editingVariable"
      :form="form"
      @save="saveVariable"
    />
  </div>
</template>

<script setup>
import ComputedVariableCard from './ComputedVariableCard.vue'
import ComputedVariableModal from './ComputedVariableModal.vue'
import { generateUUID } from '~/lib/utils.js'

const workingFormStore = useWorkingFormStore()
const form = computed(() => workingFormStore.content)

const computedVariables = computed({
  get: () => form.value?.computed_variables || [],
  set: (value) => {
    if (form.value) {
      form.value.computed_variables = value
    }
  }
})

const showModal = ref(false)
const editingVariable = ref(null)

function openCreateModal() {
  editingVariable.value = null
  showModal.value = true
}

function openEditModal(variable) {
  editingVariable.value = { ...variable }
  showModal.value = true
}

function saveVariable(variable) {
  const variables = [...computedVariables.value]
  
  if (editingVariable.value) {
    // Update existing
    const index = variables.findIndex(v => v.id === variable.id)
    if (index !== -1) {
      variables[index] = variable
    }
  } else {
    // Create new
    variable.id = `cv_${generateUUID().replace(/-/g, '').substring(0, 12)}`
    variables.push(variable)
  }
  
  computedVariables.value = variables
  showModal.value = false
  editingVariable.value = null
}

function deleteVariable(variable) {
  computedVariables.value = computedVariables.value.filter(v => v.id !== variable.id)
}
</script>

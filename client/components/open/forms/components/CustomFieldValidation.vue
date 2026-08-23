<template>
  <div class="py-2 px-4">
    <div class="flex gap-1 border-b pb-2">
      <UButton
        color="neutral"
        variant="ghost"
        size="xs"
        icon="i-lucide-arrow-down-to-line"
        class="text-[var(--sf-text-caption)]"
        @click="showCopyFormModal = true"
      >
        Copy from
      </UButton>
      <UButton
        color="neutral"
        variant="ghost"
        size="xs"
        icon="i-lucide-arrow-up-to-line"
        class="text-[var(--sf-text-caption)]"
        @click="showCopyToModal = true"
      >
        Copy to
      </UButton>
      <UButton
        color="neutral"
        variant="ghost"
        size="xs"
        icon="i-lucide-x"
        class="text-[var(--sf-text-caption)]"
        @click="clearAll"
      >
        Clear
      </UButton>
      <UButton
        color="neutral"
        variant="ghost"
        class="text-[var(--sf-text-caption)]"
        size="xs"
        icon="i-lucide-circle-question-mark"
        @click="openHelpArticle"
      />
    </div>

    <!-- Validation Rules Card -->
    <div class="mt-4">
      <p class="text-xs font-medium text-[var(--sf-text-secondary)] mb-2">When should this field be valid?</p>
      <p class="text-[var(--sf-text-caption)] text-xs mb-3">
        Set <span class="font-semibold">rules that make this field valid</span>. If rules aren't met, the field shows an error.
      </p>
      <div class="p-3 border border-[var(--sf-border-card)] rounded-lg bg-[var(--sf-bg-muted)]/50 hover:bg-[var(--sf-bg-muted)] transition-colors">
        <UPopover
          :content="{ 
            align: 'start', 
            side: 'left', 
            sideOffset: 8 
          }"
          :ui="{ 
            content: 'w-[650px] overflow-hidden' 
          }"
          arrow
        >
          <UButton
            :color="hasConditions ? 'primary' : 'neutral'"
            :variant="hasConditions ? 'subtle' : 'outline'"
            :icon="hasConditions ? 'i-lucide-settings' : 'i-lucide-plus'"
            size="sm"
            class="w-full justify-start font-medium hover:bg-[var(--sf-bg-surface)] transition-colors"
          >
          {{ hasConditions ? `${conditionsCount} rule${conditionsCount > 1 ? 's' : ''}` : 'Add rule' }}
          </UButton>

          <template #content>
            <ScrollableContainer
              ref="scrollableContainer"
              direction="both"
              max-width-class="max-w-[650px]"
              max-height-class="max-h-96"
              :fade-class="'from-white via-white/80 to-transparent'"
              left-fade-width="w-4"
              right-fade-width="w-4"
              top-fade-height="h-4"
              bottom-fade-height="h-4"
              :scroll-tolerance="5"
            >
              <condition-editor
                class="w-full p-4"
                ref="filter-editor"
                v-model="validation.conditions"
                :form="form"
                :custom-validation="true"
              />
            </ScrollableContainer>
          </template>
        </UPopover>
      </div>
    </div>

    <!-- Divider Line -->
    <div class="my-5">
      <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
    </div>

    <!-- Error Message Card -->
    <div>
      <p class="text-xs font-medium text-[var(--sf-text-secondary)] mb-2">What message to show if validation fails?</p>
      <p class="text-[var(--sf-text-caption)] text-xs mb-3">
        This message appears when <span class="font-semibold">validation rules aren't met</span>.
      </p>
      <div class="p-3 border border-[var(--sf-border-card)] rounded-lg bg-[var(--sf-bg-muted)]/50 hover:bg-[var(--sf-bg-muted)] transition-colors">
        <text-input
          name="error_message"
          :form="field.validation"
          label="Error message"
        />
      </div>
    </div>

    <UAlert
      icon="i-lucide-info"
      color="warning"
      variant="subtle"
      size="sm"
      class="mt-4"
      description="Save your form to apply changes."
    />

    <UModal
      v-model:open="showCopyFormModal"
      title="Copy validation from another field"
      :description="`Select another field to copy its validation rules and apply them to '${field.name}'.`"
    >
      <template #body>
        <USelectMenu
          v-model="copyFrom"
          :items="copyFromOptions"
          value-key="value"
          placeholder="Choose a field..."
          searchable
        />
      </template>

      <template #footer>
        <UButton
          color="neutral"
          variant="outline"
          label="Close"
          @click="showCopyFormModal = false"
        />
        <UButton
          color="primary"
          @click="copyValidation"
          label="Confirm & Copy"
        />
      </template>
    </UModal>

    <UModal
      v-model:open="showCopyToModal"
      title="Copy validation to other fields"
      :description="`Select other fields to copy the validation rules from '${field.name}' to.`"
    >
      <template #body>
        <USelectMenu
          v-model="copyTo"
          :items="copyToOptions"
          value-key="value"
          placeholder="Choose fields..."
          :multiple="true"
          searchable
        />
      </template>

      <template #footer>
        <UButton
          color="neutral"
          variant="outline"
          label="Close"
          @click="showCopyToModal = false"
        />
        <UButton
          color="primary"
          @click="copyValidationToFields"
          label="Confirm & Copy"
        />
      </template>
    </UModal>
  </div>
</template>

<script>
import { default as _has } from "lodash/has"
import ConditionEditor from "./form-logic-components/ConditionEditor.client.vue"
import ScrollableContainer from "~/components/dashboard/ScrollableContainer.vue"
import clonedeep from "clone-deep"

export default {
    name: 'FormValidation',
    components: { ConditionEditor, ScrollableContainer },
    props: {
        field: {
            type: Object,
            required: false
        },
        form: {
            type: Object,
            required: false
        }
    },

    setup() {
        const crisp = useCrisp()
        return {
            crisp
        }
    },

    data() {
        return {
            show: false,
            validation: this.field.validation?.error_conditions || {
                conditions: null,
                actions: [],
            },
            error_message: this.field.validation?.error_message || '',
            showCopyFormModal: false,
            copyFrom: null,
            showCopyToModal: false,
            copyTo: []
        }
    },

    computed: {
        conditionsCount() {
            if (this.validation.conditions === null || this.validation.conditions === undefined) return 0
            // Count the number of rules/conditions recursively
            return this.countConditions(this.validation.conditions)
        },
        hasConditions() {
            return this.conditionsCount > 0
        },
        copyFromOptions() {
            return this.form.properties
                .filter((field) => {
                    return (
                        field.id !== this.field.id &&
                        _has(field, "validation") &&
                        field.validation?.error_conditions &&
                        Object.keys(field.validation.error_conditions || {}).length > 0
                    )
                })
                .map((field) => {
                    return { label: field.name, value: field.id }
                })
        },
        copyToOptions() {
            return this.form.properties
                .filter((field) => {
                    return field.id !== this.field.id
                })
                .map((field) => {
                    return { label: field.name, value: field.id }
                })
        }
    },

    watch: {
        validation: {
            handler() {
                this.field.validation.error_conditions = this.validation
            },
            deep: true,
        },
        "field.id": {
            handler() {
                // On field change, reset validation
                this.validation = this.field?.validation?.error_conditions || {
                    conditions: null,
                    actions: [],
                }
            },
        },
    },
    mounted() {
        if (!_has(this.field, "validation")) {
            this.field.validation = {
                error_conditions: this.validation,
                error_message: this.error_message 
            }
        }
    },
    methods: {
        countConditions(conditions) {
            if (!conditions) return 0
            
            // If it's a group with children
            if (conditions.children && Array.isArray(conditions.children)) {
                return conditions.children.reduce((count, child) => {
                    // If child has an identifier, it's a rule
                    if (child.identifier) {
                        return count + 1
                    }
                    // If child has children, it's a nested group - count recursively
                    if (child.children) {
                        return count + this.countConditions(child)
                    }
                    return count
                }, 0)
            }
            
            // If it's a single rule with identifier
            if (conditions.identifier) {
                return 1
            }
            
            return 0
        },
        openHelpArticle() {
            this.crisp.openHelpdeskArticle('how-to-add-custom-validation-to-a-field-elzuxy')
        },
        clearAll() {
            this.validation.conditions = null
            if (this.field.validation) {
                this.field.validation.error_message = ''
            }
        },
        copyValidation() {
            if (this.copyFrom) {
                const sourceField = this.form.properties.find((property) => {
                    return property.id === this.copyFrom
                })
                if (sourceField && sourceField.validation?.error_conditions) {
                    this.validation = clonedeep(sourceField.validation.error_conditions)
                    if (sourceField.validation.error_message) {
                        this.field.validation.error_message = sourceField.validation.error_message
                    }
                }
            }
            this.showCopyFormModal = false
        },
        copyValidationToFields() {
            if (this.copyTo.length) {
                this.copyTo.forEach((fieldId) => {
                    const targetField = this.form.properties.find(
                        (property) => property.id === fieldId
                    )
                    if (targetField) {
                        if (!targetField.validation) {
                            targetField.validation = {}
                        }
                        targetField.validation.error_conditions = clonedeep(this.validation)
                        if (this.field.validation?.error_message) {
                            targetField.validation.error_message = this.field.validation.error_message
                        }
                    }
                })
            }
            this.showCopyToModal = false
            this.copyTo = []
        }
    }
}
</script>

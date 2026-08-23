<template>
  <div class="relative min-h-0">
    <div class="sticky top-0 bg-[var(--sf-bg-surface)] border-b border-[var(--sf-border-card)] z-10 p-3">
      <button
        class="w-full flex items-center justify-center gap-2 py-2 rounded-xl border-2 border-dashed border-[var(--sf-border-button)] text-[var(--sf-text-caption)] hover:border-[var(--sf-coral-500)] hover:text-[var(--sf-coral-500)] hover:bg-[var(--sf-nav-active-bg)]/50 transition-all duration-150 text-[13px] font-medium"
        @click.prevent="openAddFieldSidebar"
      >
        <Icon name="i-lucide-plus" class="w-4 h-4" />
        Add Block
        <span class="hidden sm:inline text-[11px] text-[var(--sf-text-disabled)] font-normal ml-1">
          <UKbd value="meta" size="xs" /> <UKbd value="B" size="xs" />
        </span>
      </button>
    </div>

    <div class="p-3">
      <VueDraggable
        :model-value="form.properties"
        group="form-elements"
        item-key="id"
        class="mx-auto w-full overflow-hidden rounded-xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] transition-colors shadow-[0_1px_2px_rgba(23,25,35,0.04)]"
        ghost-class="bg-[var(--sf-nav-active-bg)]"
        :animation="200"
        :delay="150"
        :delay-on-touch-only="true"
        :touch-start-threshold="3"
        @add="handleDragAdd"
        @update="handleDragUpdate"
      >
        <template #default>
          <div
            v-for="(element, index) in form.properties"
            :key="element.id || index"
            class="mx-auto w-full border-[var(--sf-border-card)] transition-all duration-150 cursor-grab"
            :class="{
              'bg-[var(--sf-bg-muted)]/60': element.hidden && !isBeingEdited(index),
              'bg-[var(--sf-bg-surface)] hover:bg-[var(--sf-bg-muted)]/30': !element.hidden && !isBeingEdited(index),
              'border-b border-[var(--sf-border-divider)]': index !== form.properties.length - 1,
              '!border-l-2 !border-l-[var(--sf-coral-500)] !border-b !border-b-[var(--sf-border-divider)]': element.type === 'nf-page-break',
              'bg-[var(--sf-nav-active-bg)] ring-1 ring-inset ring-[var(--sf-coral-500)]/20': isBeingEdited(index),
            }"
            @click="selectField(element)"
          >
            <div
              v-if="element"
              class="group flex items-center gap-x-1 py-2 px-2 pr-1.5"
            >
              <BlockTypeIcon
                v-if="element.type && typeof element.type === 'string'"
                :type="element.type"
                class="ml-1"
              />
              <!-- Field name and type -->
              <div class="flex grow flex-col truncate min-w-0">
                <EditableTag
                  class="truncate text-[var(--sf-text-secondary)] text-[13px] min-w-16 min-h-5 font-medium"
                  :model-value="element.name"
                  @update:model-value="onChangeName(element, $event)"
                >
                  <label class="w-full cursor-pointer truncate">
                    {{ element.name }}
                  </label>
                </EditableTag>
                <span class="text-[11px] text-[var(--sf-text-disabled)] font-medium capitalize">
                  {{ element.type?.replace('nf-', '') }}
                </span>
              </div>

              <!-- Inline action buttons (always visible on touch screens, hover-revealed on desktop) -->
              <div class="flex items-center gap-0.5 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-150">
                <button
                  v-if="element.type && typeof element.type === 'string' && !element.type.startsWith('nf-')"
                  class="!cursor-pointer rounded-md p-1 transition-colors hover:bg-[var(--sf-nav-hover-bg)] items-center justify-center"
                  :class="element.required ? 'text-[var(--sf-coral-500)]' : 'text-[var(--sf-text-muted)] hover:text-[var(--sf-text-body)]'"
                  :title="element.required ? 'Make optional' : 'Make required'"
                  @click.stop="toggleRequired(element)"
                >
                  <Icon name="i-lucide-asterisk" class="w-3.5 h-3.5" />
                </button>
                <button
                  class="!cursor-pointer rounded-md p-1 transition-colors hover:bg-[var(--sf-nav-hover-bg)] items-center justify-center"
                  :class="element.hidden ? 'text-[var(--sf-amber)]' : 'text-[var(--sf-text-muted)] hover:text-[var(--sf-text-body)]'"
                  :title="element.hidden ? 'Show block' : 'Hide block'"
                  @click.stop="toggleHidden(element)"
                >
                  <Icon :name="element.hidden ? 'tabler:eye-off' : 'tabler:eye'" class="w-3.5 h-3.5" />
                </button>
                <button
                  class="cursor-pointer rounded-md p-1 transition-colors hover:bg-[var(--sf-nav-hover-bg)] text-[var(--sf-text-muted)] hover:text-[var(--sf-text-primary)] flex items-center justify-center field-settings-button"
                  title="Open settings"
                  @click.stop="editOptions(index)"
                >
                  <Icon name="lucide:settings" class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        </template>
      </VueDraggable>
    </div>
  </div>
</template>

<script>
import { VueDraggable } from 'vue-draggable-plus'
import EditableTag from '~/components/app/EditableTag.vue'
import BlockTypeIcon from './BlockTypeIcon.vue'

export default {
  name: 'FormFieldsEditor',
  components: {
    VueDraggable,
    EditableTag,
    BlockTypeIcon
  },
  setup () {
    const workingFormStore = useWorkingFormStore()
    return {
      workingFormStore,
      form: storeToRefs(workingFormStore).content
    }
  },

  data () {
    return {

    }
  },

  computed: {
    // Expose the form structure service created by useFormManager
    structure () {
      return this.workingFormStore?.structureService || null
    },
    // Numeric current page index derived from the structure service
    currentPageIndex () {
      return this.structure?.currentPage?.value ?? 0
    }
  },

  mounted() {
    this.init()
  },

  methods: {
    init() {
      if (!this.form.properties) {
        return
      }
      this.form.properties = this.form.properties.map((field) => {
        // Add more field properties
        field.placeholder = field.placeholder || null
        field.prefill = field.prefill || null
        field.help = field.help || null
        field.help_position = field.help_position || "below_input"

        return field
      })
    },
    openAddFieldSidebar () {
      this.workingFormStore.openAddFieldSidebar(null)
    },
    editOptions (index) {
      this.workingFormStore.openSettingsForField(index)
    },
    selectField (field) {
      this.workingFormStore.openSettingsForField(field)
    },
    onChangeName (field, newName) {
      field.name = newName
    },
    toggleHidden (field) {
      field.hidden = !field.hidden
      if (field.hidden) {
        field.required = false
      } else {
        field.generates_uuid = false
        field.generates_auto_increment_id = false
      }
    },
    toggleRequired (field) {
      field.required = !field.required
      if (field.required)
        field.hidden = false
    },
    isBeingEdited (index) {
      if (!this.workingFormStore?.showEditFieldSidebar) return false
      return index === this.workingFormStore.selectedFieldIndex
    },
    getAbsoluteIndex (relativeIndex) {
      if (!this.structure) return relativeIndex
      return this.structure.getTargetDropIndex(relativeIndex, this.currentPageIndex)
    },
    handleDragAdd (evt) {
      if (!this.structure) return
      const targetIndex = this.getAbsoluteIndex(evt.newIndex)
      const payload = evt?.clonedData
      this.workingFormStore.addBlock(payload, targetIndex, false)
    },
    handleDragUpdate (evt) {
      if (!this.structure) return
      const oldTargetIndex = this.getAbsoluteIndex(evt.oldIndex)
      const newTargetIndex = this.getAbsoluteIndex(evt.newIndex)
      if (oldTargetIndex !== newTargetIndex) {
        this.workingFormStore.moveField(oldTargetIndex, newTargetIndex)
      }
    }
  }
}
</script>

<style lang='scss'>
.v-popover {
  .trigger {
    @apply truncate w-full;
  }
}
</style>

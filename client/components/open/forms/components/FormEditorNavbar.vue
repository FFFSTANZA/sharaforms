<template>
  <div class="w-full border-b border-[var(--sf-border-card)] px-2 sm:px-4 py-2.5 min-h-14 flex gap-x-1.5 sm:gap-x-3 items-center bg-[var(--sf-bg-surface)] shadow-[0_1px_3px_rgba(23,25,35,0.06)]">
    <!-- Left section: back + tabs + settings -->
    <a
      v-if="backButton"
      href="#"
      class="sm:ml-2 flex text-[var(--sf-text-body)] font-semibold text-sm -m-1 hover:bg-[var(--sf-nav-hover-bg)] rounded-md p-1 group"
      @click.prevent="$emit('go-back')"
    >
      <Icon
        name="lucide:arrow-left"
        class="text-[var(--sf-text-secondary)] mr-1 w-6 h-6 group-hover:text-[var(--sf-text-primary)] group-hover:-translate-x-0.5 transition-all"
      />
    </a>


    <!-- Build/Design tabs: desktop only (mobile uses the bottom nav) -->
    <div class="hidden md:flex items-center gap-1 bg-[var(--sf-bg-muted)] rounded-xl p-1">
      <button
        v-for="tab in [{ label: 'Build', value: 'build', icon: 'i-lucide-hammer' }, { label: 'Design', value: 'design', icon: 'i-lucide-palette' }]"
        :key="tab.value"
        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-[13px] font-medium transition-all duration-150"
        :class="activeTab === tab.value
          ? 'bg-[var(--sf-bg-surface)] text-[var(--sf-text-primary)] shadow-sm'
          : 'text-[var(--sf-text-caption)] hover:text-[var(--sf-text-body)] hover:bg-[var(--sf-nav-hover-bg)]'"
        @click="activeTab = tab.value"
      >
        <Icon :name="tab.icon" class="w-3.5 h-3.5" />
        {{ tab.label }}
      </button>
    </div>
    <button
      class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-medium text-[var(--sf-text-body)] hover:bg-[var(--sf-nav-hover-bg)] hover:text-[var(--sf-text-primary)] transition-all duration-150 border border-[var(--sf-border-button)] bg-[var(--sf-bg-surface)]"
      @click="settingsModal = true"
    >
      <Icon name="i-lucide-settings" class="w-3.5 h-3.5" />
      <span class="hidden sm:inline">Settings</span>
    </button>
    <FormSettingsModal
      v-model="settingsModal"
      @close="settingsModal = false"
      hydrate-on-interaction
    />

    <div class="flex-grow min-w-0 flex justify-center items-center gap-2">
      <EditableTag
        id="form-editor-title"
        v-model="form.title"
        element="h1"
        :max-length="255"
        class="font-semibold py-1 text-[15px] leading-6 w-28 sm:w-72 max-w-full text-[var(--sf-text-primary)] truncate form-editor-title"
      />
      <span
        v-if="form.visibility == 'draft'"
        class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--sf-status-draft-bg)] text-[var(--sf-status-draft-text)] border border-[var(--sf-status-draft-border)]"
      >
        <Icon name="i-lucide-square-pen" class="w-3 h-3" />
        Draft
      </span>
      <span
        v-else-if="form.visibility == 'closed'"
        class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--sf-status-closed-bg)] text-[var(--sf-status-closed-text)] border border-[var(--sf-status-closed-border)]"
      >
        <Icon name="i-lucide-lock-keyhole" class="w-3 h-3" />
        Closed
      </span>
    </div>

    <UndoRedo />

    <FormHistory />

    <div class="flex items-center gap-2">
      <TrackClick name="form_editor_help_button_clicked">
        <UTooltip
          text="Help"
          class="items-center relative"
          :content="{ side: 'bottom' }"
          arrow
        >
          <button
            class="flex items-center justify-center w-8 h-8 rounded-lg text-[var(--sf-text-caption)] hover:text-[var(--sf-text-primary)] hover:bg-[var(--sf-nav-hover-bg)] transition-all duration-150"
            @click.prevent="crisp.openHelpdesk()"
          >
            <Icon name="i-lucide-circle-question-mark" class="w-4 h-4" />
          </button>
        </UTooltip>
      </TrackClick>
      <slot name="before-save" />
      <UTooltip arrow :content="{side: 'bottom'}">
        <template #content>
          <UKbd
            value="meta"
            size="xs"
          />
          <UKbd
            value="s"
            size="xs"
          />
        </template>
        <TrackClick
          name="save_form_click"
        >
          <button
            class="btn-primary flex items-center gap-2 px-3 sm:px-5 py-2 rounded-xl text-[13px] font-semibold"
            :class="saveButtonClass"
            data-testid="save-form-button"
            @click="emit('save-form')"
          >
            <Icon v-if="!updateFormLoading" name="i-lucide-save" class="w-4 h-4" />
            <loader v-else class="h-4 w-4 text-white animate-spin" />
            <span class="hidden min-[420px]:inline">{{ form.visibility === 'public' ? 'Publish Form' : 'Save Changes' }}</span>
          </button>
        </TrackClick>
      </UTooltip>
    </div>
  </div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import FormHistory from '~/components/open/editors/FormHistory.vue'
import UndoRedo from '~/components/open/editors/UndoRedo.vue'
import FormSettingsModal from '~/components/open/forms/components/form-components/FormSettingsModal.vue'
import EditableTag from '~/components/app/EditableTag.vue'
import TrackClick from '~/components/global/TrackClick.vue'

defineProps({
  backButton: {
    type: Boolean,
    default: true
  },
  updateFormLoading: {
    type: Boolean,
    required: true
  },
  saveButtonClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['go-back', 'save-form'])

defineShortcuts({
  meta_s: {
    handler: () => emit('save-form')
  }
})

const workingFormStore = useWorkingFormStore()
const crisp = useCrisp()

const form = computed(() => workingFormStore.content)
const { activeTab } = storeToRefs(workingFormStore)

const settingsModal = ref(false)
</script>

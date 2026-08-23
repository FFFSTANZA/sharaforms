<template>
  <div 
    v-if="shouldDisplayBadges" 
    class="flex items-center flex-wrap gap-1"
  >
    <!-- Draft Badge -->
    <UTooltip v-if="form.visibility === 'draft'" text="Not publicly accessible">
      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--sf-status-draft-bg)] text-[var(--sf-status-draft-text)] border border-[var(--sf-status-draft-border)]">
        <Icon name="i-lucide-square-pen" class="w-3 h-3" />
        Draft
      </span>
    </UTooltip>
    
    <!-- Closed Badge -->
    <UTooltip v-else-if="form.visibility === 'closed'" text="Won't accept new submissions">
      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--sf-status-closed-bg)] text-[var(--sf-status-closed-text)] border border-[var(--sf-status-closed-border)]">
        <Icon name="i-lucide-lock-keyhole" class="w-3 h-3" />
        Closed
      </span>
    </UTooltip>
    
    <!-- Time Limited Badge -->     <template v-else-if="form.closes_at">
      <UTooltip v-if="!form.is_closed" :text="`Will close on ${closesDate}`">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--sf-status-draft-bg)] text-[var(--sf-status-draft-text)] border border-[var(--sf-status-draft-border)]">
          <Icon name="i-lucide-clock" class="w-3 h-3" />
          Time limited
        </span>
      </UTooltip>
      <UTooltip v-else :text="`Closed on ${closesDate}`">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--sf-status-closed-bg)] text-[var(--sf-status-closed-text)] border border-[var(--sf-status-closed-border)]">
          <Icon name="i-lucide-clock" class="w-3 h-3" />
          Closed
        </span>
      </UTooltip>
    </template>
    
    <!-- Submission Limited Badge -->
    <template v-else-if="form.max_submissions_count > 0">
      <UTooltip 
        v-if="!form.max_number_of_submissions_reached"
        :text="`Limited to ${form.max_submissions_count} submissions`"
      >
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--sf-status-draft-bg)] text-[var(--sf-status-draft-text)] border border-[var(--sf-status-draft-border)]">
          <Icon name="i-lucide-chart-bar" class="w-3 h-3" />
          Submission limited
        </span>
      </UTooltip>
      <UTooltip 
        v-else
        :text="`Maximum ${form.max_submissions_count} submissions reached`"
      >
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--sf-status-closed-bg)] text-[var(--sf-status-closed-text)] border border-[var(--sf-status-closed-border)]">
          <Icon name="i-lucide-lock-keyhole" class="w-3 h-3" />
          Limit reached
        </span>
      </UTooltip>
    </template>
    
    <!-- Tags Badges -->
    <span
      v-if="withTags"
      v-for="tag in form.tags"
      :key="tag"
      class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium capitalize bg-[var(--sf-bg-muted)] text-[var(--sf-text-body)] border border-[var(--sf-border-card)]"
    >
      {{ tag }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  form: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
  },
  withTags: {
    type: Boolean,
    default: true
  }
})

const closesDate = computed(() => {
  if (props.form && props.form.closes_at) {
    try {
      const dateObj = new Date(props.form.closes_at)
      return dateObj.getFullYear() + '-' +
        String(dateObj.getMonth() + 1).padStart(2, '0') + '-' +
        String(dateObj.getDate()).padStart(2, '0') + ' ' +
        String(dateObj.getHours()).padStart(2, '0') + ':' +
        String(dateObj.getMinutes()).padStart(2, '0')
    } catch (e) {
      console.error(e)
      return null
    }
  }
  return null
})

// Conditional to determine if badges should be displayed
const shouldDisplayBadges = computed(() => {
  return ['draft', 'closed'].includes(props.form.visibility) || 
         (props.form.tags && props.form.tags.length > 0) || 
         props.form.closes_at || 
         (props.form.max_submissions_count > 0)
})
</script> 
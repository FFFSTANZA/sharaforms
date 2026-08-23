<template>
  <collapse
    v-model="show"
    class="w-full border-b border-[var(--sf-border-divider)]"
  >
    <template #title>
      <div class="flex items-center pr-8 gap-2.5 py-3.5 px-4 transition-colors duration-150 hover:bg-[var(--sf-bg-muted)]/40">
        <div
          class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors duration-150"
          :class="show
            ? 'bg-[var(--sf-nav-active-bg)] text-[var(--sf-coral-500)]'
            : 'bg-[var(--sf-bg-muted)] text-[var(--sf-text-caption)]'"
        >
          <slot name="icon" />
        </div>
        <h3
          id="v-step-2"
          class="font-semibold flex-grow mr-2 text-[13px]"
          :class="show ? 'text-[var(--sf-text-primary)]' : 'text-[var(--sf-text-body)]'"
        >
          {{ name }}
        </h3>
        <PlanTag v-if="hasProTag" />
        <Icon
          name="i-lucide-chevron-down"
          class="w-3.5 h-3.5 text-[var(--sf-text-disabled)] transition-transform duration-200"
          :class="show ? 'rotate-180' : ''"
        />
      </div>
    </template>

    <div class="px-4 pb-4">
      <slot />
    </div>
  </collapse>
</template>

<script>
import Collapse from "~/components/app/Collapse.vue"
import PlanTag from "~/components/app/PlanTag.vue"

export default {
  name: "EditorOptionsPanel",
  components: { Collapse, PlanTag },
  props: {
    name: {
      type: String,
      required: true,
    },
    hasProTag: {
      type: Boolean,
      default: false,
    },
    alreadyOpened: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      show: this.alreadyOpened,
    }
  },
}
</script>

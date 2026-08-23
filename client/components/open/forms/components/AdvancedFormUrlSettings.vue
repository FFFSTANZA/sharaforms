<template>
  <collapse
    class="py-4 w-full"
    :model-value="false"
  >
    <template #title>
      <div class="flex items-center gap-2">
        <div class="w-6 h-6 rounded-md bg-[var(--sf-bg-muted)] flex items-center justify-center">
          <Icon name="i-lucide-sliders-horizontal" class="w-3.5 h-3.5 text-[var(--sf-text-caption)]" />
        </div>
        <h3 class="font-semibold text-[13px] text-[var(--sf-text-body)]">
          Advanced sharing options
        </h3>
      </div>
    </template>
    <toggle-switch-input
      :model-value="modelValue.auto_submit"
      name="auto_submit"
      class="mt-4"
      label="Auto Submit Form"
      help="Form will auto submit immediate after open URL"
      @update:model-value="onChangeAutoSubmit"
    />
  </collapse>
</template>

<script>
import Collapse from "~/components/app/Collapse.vue"
export default {
  name: "AdvancedFormUrlSettings",
  components: { Collapse },
  props: {
    form: {
      type: Object,
      required: true,
    },
    modelValue: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {}
  },

  methods: {
    onChangeAutoSubmit(val) {
      this.modelValue.auto_submit = val
    },
  },
}
</script>

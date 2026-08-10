<template>
  <span class="inline-block relative" :class="{ 'group/btn': editable && !editing }">
    <open-form-button
      v-bind="$attrs"
      :form="form"
      :native-type="effectiveNativeType"
      :loading="loading"
      :icon="icon"
      @click="handleButtonClick"
    >
      {{ displayText }}
    </open-form-button>

    <!-- Pencil affordance (admin preview only) -->
    <span
      v-if="editable && !editing && !loading"
      class="pointer-events-none absolute -top-1.5 -right-1.5 z-20 hidden group-hover/btn:flex items-center justify-center w-5 h-5 rounded-full bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 shadow-sm text-neutral-400"
      aria-hidden="true"
    >
      <Icon name="i-lucide-pencil" class="w-3 h-3" />
    </span>

    <!-- Inline editor overlay -->
    <div
      v-if="editing"
      ref="editor"
      contenteditable="true"
      role="textbox"
      spellcheck="false"
      class="absolute inset-0 z-10 flex items-center justify-center text-center whitespace-nowrap outline-none cursor-text select-text overflow-hidden"
      :style="{ color: textColor, caretColor: textColor }"
      @pointerdown.stop
      @click.stop
      @keydown.enter.prevent="commit"
      @keydown.esc.prevent="handleEscape"
      @blur="commit"
    >{{ draft }}</div>
  </span>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import OpenFormButton from './OpenFormButton.vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  form: { type: Object, required: true },
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  editable: { type: Boolean, default: false },
  nativeType: { type: String, default: 'submit' },
  loading: { type: Boolean, default: false },
  icon: { type: String, default: null },
})

const emit = defineEmits(['update:modelValue', 'click'])

const editing = ref(false)
const draft = ref('')
const editor = ref(null)
let cancelled = false

const displayText = computed(() => {
  const value = (props.modelValue || '').trim()
  return value || props.placeholder
})

// While in the builder, never let the underlying button act as a real submit button.
const effectiveNativeType = computed(() => (props.editable ? 'button' : props.nativeType))

// Match OpenFormButton's readable text color, returned as a hex for the caret/editor color.
const textColor = computed(() => {
  const bg = props.form?.color
  if (!bg) return '#0a0a0a'
  const hex = bg.charAt(0) === '#' ? bg.substring(1, 7) : bg
  if (!/^[0-9a-f]{6}$/i.test(hex)) return '#0a0a0a'
  const r = parseInt(hex.substring(0, 2), 16)
  const g = parseInt(hex.substring(2, 4), 16)
  const b = parseInt(hex.substring(4, 6), 16)
  const L = 0.2126 * r / 255 + 0.7152 * g / 255 + 0.0722 * b / 255
  return L > 0.45 ? '#0a0a0a' : '#ffffff'
})

const handleButtonClick = (event) => {
  event.preventDefault()
  event.stopPropagation()

  if (props.editable && !props.loading) {
    startEdit()
    return
  }

  emit('click', event)
}

const startEdit = () => {
  draft.value = displayText.value
  editing.value = true
  cancelled = false

  nextTick(() => {
    const el = editor.value
    if (!el) return
    el.focus()
    const range = document.createRange()
    range.selectNodeContents(el)
    const selection = window.getSelection()
    selection.removeAllRanges()
    selection.addRange(range)
  })
}

const commit = () => {
  if (cancelled) return

  const value = (draft.value || '').trim()
  // Compare against what was displayed (placeholder may fill an empty modelValue),
  // so leaving the current text untouched emits nothing.
  if (value !== displayText.value) {
    emit('update:modelValue', value)
  }
  editing.value = false
}

const handleEscape = () => {
  cancelled = true
  editing.value = false
  editor.value?.blur()
}
</script>
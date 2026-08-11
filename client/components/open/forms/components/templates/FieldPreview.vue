<template>
  <div class="field-input" :class="inputClass">
    <div v-if="field.type === 'text' && field.multi_lines" class="field-visual textarea-visual">
      <svg viewBox="0 0 340 40"><rect x="0" y="2" width="340" height="36" rx="4" :fill="palette.inputBg" :stroke="palette.border"/><rect x="10" y="10" width="300" height="4" rx="2" :fill="palette.border"/><rect x="10" y="18" width="240" height="4" rx="2" :fill="palette.border"/><rect x="10" y="26" width="180" height="4" rx="2" :fill="palette.border"/></svg>
    </div>
    <div v-else-if="['select', 'multi_select'].includes(field.type)" class="field-visual select-visual">
      <svg viewBox="0 0 340 28"><rect x="0" y="2" width="340" height="24" rx="4" :fill="palette.inputBg" :stroke="palette.border"/><text x="12" y="18" font-size="11" :fill="palette.border" font-family="system-ui">Select an option</text><path d="M316 12l-6 6-6-6" :stroke="palette.border" fill="none" stroke-width="1.5"/></svg>
    </div>
    <div v-else-if="field.type === 'checkbox'" class="field-visual checkbox-visual">
      <svg viewBox="0 0 340 28"><rect x="0" y="4" width="18" height="18" rx="3" :fill="palette.color" opacity="0.15" :stroke="palette.color" stroke-width="1.5"/><path d="M5 13l3 3 5-5" :stroke="palette.color" fill="none" stroke-width="1.5"/><text x="24" y="18" font-size="11" :fill="palette.text" font-family="system-ui">{{ field.title || field.name || 'Option' }}</text></svg>
    </div>
    <div v-else-if="field.type === 'rating'" class="field-visual rating-visual">
      <svg viewBox="0 0 340 30">
        <defs>
          <path id="sf-star" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
        </defs>
        <use v-for="(x, idx) in [26, 54, 82, 110, 138]" :key="idx" href="#sf-star" :transform="`translate(${x} 15) scale(0.85) translate(-12 -11.5)`" :fill="idx < 3 ? palette.color : 'none'" :stroke="idx < 3 ? palette.color : palette.border" :stroke-width="idx < 3 ? 0 : 1.4" :opacity="idx < 3 ? 0.95 : 0.8"/>
      </svg>
    </div>
    <div v-else-if="field.type === 'scale'" class="field-visual scale-visual">
      <svg viewBox="0 0 340 28"><rect x="0" y="13" width="340" height="2" rx="1" :fill="palette.border"/><circle cx="30" cy="14" r="6" :fill="palette.color" opacity="0.3"/><circle cx="80" cy="14" r="6" :fill="palette.color" opacity="0.3"/><circle cx="130" cy="14" r="6" :fill="palette.color" opacity="0.3"/><circle cx="180" cy="14" r="6" :fill="palette.color" opacity="0.3"/><circle cx="230" cy="14" r="6" :fill="palette.color" opacity="0.3"/></svg>
    </div>
    <div v-else-if="field.type === 'slider'" class="field-visual slider-visual">
      <svg viewBox="0 0 340 28"><rect x="0" y="12" width="340" height="4" rx="2" :fill="palette.border"/><circle cx="120" cy="14" r="9" :fill="palette.color" opacity="0.5"/></svg>
    </div>
    <div v-else-if="field.type === 'date'" class="field-visual date-visual">
      <svg viewBox="0 0 340 28"><rect x="0" y="2" width="340" height="24" rx="4" :fill="palette.inputBg" :stroke="palette.border"/><rect x="10" y="7" width="16" height="14" rx="2" :fill="palette.color" opacity="0.25"/><text x="32" y="19" font-size="11" :fill="palette.border" font-family="system-ui">MM/DD/YYYY</text></svg>
    </div>
    <div v-else-if="field.type === 'files'" class="field-visual files-visual">
      <svg viewBox="0 0 340 40"><rect x="0" y="4" width="340" height="32" rx="4" :fill="palette.inputBg" :stroke="palette.border" stroke-dasharray="6 3"/><text x="120" y="25" font-size="11" :fill="palette.border" font-family="system-ui">Click to upload</text></svg>
    </div>
    <div v-else-if="field.type === 'signature'" class="field-visual signature-visual">
      <svg viewBox="0 0 340 40"><rect x="0" y="4" width="340" height="32" rx="4" :fill="palette.inputBg" :stroke="palette.border" stroke-dasharray="6 3"/><text x="125" y="25" font-size="11" :fill="palette.border" font-family="system-ui">Sign here</text></svg>
    </div>
    <div v-else-if="field.type === 'payment'" class="field-visual payment-visual">
      <svg viewBox="0 0 340 28"><rect x="0" y="2" width="340" height="24" rx="4" :fill="palette.inputBg" :stroke="palette.border"/><rect x="10" y="8" width="22" height="12" rx="2" :fill="palette.color" opacity="0.25"/><text x="38" y="19" font-size="11" :fill="palette.border" font-family="system-ui">Card payment</text></svg>
    </div>
    <div v-else-if="field.type === 'matrix'" class="field-visual matrix-visual">
      <svg viewBox="0 0 340 36"><rect x="0" y="0" width="100" height="12" rx="2" :fill="palette.inputBg" :stroke="palette.border"/><rect x="104" y="0" width="60" height="12" rx="2" :fill="palette.inputBg" :stroke="palette.border"/><rect x="168" y="0" width="60" height="12" rx="2" :fill="palette.inputBg" :stroke="palette.border"/><rect x="0" y="16" width="100" height="12" rx="2" :fill="palette.inputBg" :stroke="palette.border"/><rect x="104" y="16" width="60" height="12" rx="2" :fill="palette.inputBg" :stroke="palette.border"/><rect x="168" y="16" width="60" height="12" rx="2" :fill="palette.inputBg" :stroke="palette.border"/><rect x="0" y="32" width="100" height="12" rx="2" :fill="palette.inputBg" :stroke="palette.border"/><rect x="104" y="32" width="60" height="12" rx="2" :fill="palette.inputBg" :stroke="palette.border"/><rect x="168" y="32" width="60" height="12" rx="2" :fill="palette.inputBg" :stroke="palette.border"/></svg>
    </div>
    <div v-else-if="field.type === 'phone_number'" class="field-visual phone-visual">
      <svg viewBox="0 0 340 28"><rect x="0" y="2" width="340" height="24" rx="4" :fill="palette.inputBg" :stroke="palette.border"/><text x="12" y="19" font-size="11" :fill="palette.border" font-family="system-ui">+1 (555) 000-0000</text></svg>
    </div>
    <div v-else class="field-visual text-visual">
      <svg viewBox="0 0 340 28"><rect x="0" y="2" width="340" height="24" rx="4" :fill="palette.inputBg" :stroke="palette.border"/><rect x="10" y="11" width="180" height="4" rx="2" :fill="palette.border"/></svg>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  field: { type: Object, required: true },
  palette: {
    type: Object,
    default: () => ({
      color: '#4f46e5',
      inputBg: '#f5f5f5',
      border: '#e0e0e0',
      text: '#333333',
    }),
  },
})

const inputClass = computed(() => {
  const t = props.field.type
  if (['multi_lines', 'textarea', 'rich_text'].includes(t) || (t === 'text' && props.field.multi_lines)) return 'input-full'
  if (['files', 'signature'].includes(t)) return 'input-upload'
  if (t === 'matrix') return 'input-matrix'
  return 'input-normal'
})
</script>

<style scoped>
.field-input svg {
  display: block;
  width: 100%;
}
.field-input.input-normal svg { height: 28px; }
.field-input.input-full svg { height: 40px; }
.field-input.input-upload svg { height: 40px; }
.field-input.input-matrix svg { height: 48px; }
</style>

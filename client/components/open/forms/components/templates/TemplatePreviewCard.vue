<template>
  <div class="template-preview-card" :class="{ dense, 'is-dark': isDark }" :style="cardStyle">
    <div class="tpc-cover" :style="coverStyle">
      <div class="tpc-cover-title">{{ form.title || 'Untitled Form' }}</div>
      <div v-if="!dense" class="tpc-cover-sub">{{ plainDescription }}</div>
    </div>
    <div class="tpc-body">
      <div class="tpc-fields">
        <div v-for="(field, idx) in previewFields" :key="field.id || idx" class="tpc-field">
          <div class="tpc-label" :style="{ color: palette.text }">
            <span>{{ field.name || field.label || field.title || 'Untitled' }}</span>
            <span v-if="field.required" class="tpc-required">*</span>
          </div>
          <FieldPreview :field="field" :palette="palette" />
        </div>
      </div>
      <div v-if="hasMore" class="tpc-more" :style="{ color: palette.color, borderTopColor: palette.border }">+{{ remaining }} more fields</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import FieldPreview from './FieldPreview.vue'

const props = defineProps({
  form: { type: Object, required: true },
  dense: { type: Boolean, default: false },
  description: { type: String, default: '' },
})

const MAX_PREVIEW_FIELDS = 5

const allFields = computed(() => {
  const arr = props.form.properties || props.form.blocks || []
  return Array.isArray(arr) ? arr : []
})

const plainDescription = computed(() => {
  const desc = props.description || props.form.description || ''
  if (!desc) return ''
  return desc.replace(/<[^>]*>/g, '').trim().slice(0, 120)
})

const isDark = computed(() => !!props.form.dark_mode)

const previewFields = computed(() => {
  return allFields.value
    .filter(f => f && f.type && !f.type.startsWith('nf-'))
    .slice(0, MAX_PREVIEW_FIELDS)
})

const fieldCount = computed(() => {
  return allFields.value.filter(f => f && f.type && !f.type.startsWith('nf-')).length
})

const hasMore = computed(() => fieldCount.value > MAX_PREVIEW_FIELDS)
const remaining = computed(() => fieldCount.value - MAX_PREVIEW_FIELDS)

const color = computed(() => props.form.color || '#EA6676')

const palette = computed(() => ({
  color: color.value,
  bg: isDark.value ? '#1e1e2e' : '#ffffff',
  text: isDark.value ? '#e0e0e0' : '#333333',
  inputBg: isDark.value ? '#2a2a3e' : '#f5f5f5',
  border: isDark.value ? '#3a3a4e' : '#e0e0e0',
}))

const cardStyle = computed(() => ({
  '--form-color': color.value,
  '--form-bg': palette.value.bg,
  '--form-text': palette.value.text,
  '--form-input-bg': palette.value.inputBg,
  '--form-border': palette.value.border,
  '--form-radius': (props.form.border_radius || 8) + 'px',
  fontFamily: props.form.font || 'inherit',
  background: palette.value.bg,
  color: palette.value.text,
}))

const coverStyle = computed(() => {
  const c = color.value
  return {
    background: `linear-gradient(135deg, ${c} 0%, ${c}dd 100%)`,
  }
})
</script>

<style scoped>
.template-preview-card {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: var(--form-bg);
  overflow: hidden;
  transition: transform 0.3s ease;
}

.tpc-cover {
  padding: 16px 18px;
  color: #fff;
  flex-shrink: 0;
}

.dense .tpc-cover {
  padding: 12px 14px;
}

.tpc-cover-title {
  font-size: 14px;
  font-weight: 700;
  line-height: 1.25;
  letter-spacing: -0.01em;
}

.dense .tpc-cover-title {
  font-size: 12px;
}

.tpc-cover-sub {
  margin-top: 4px;
  font-size: 11px;
  line-height: 1.4;
  opacity: 0.85;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.tpc-body {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  padding: 12px 16px;
  gap: 8px;
}

.dense .tpc-body {
  padding: 10px 14px;
  gap: 6px;
}

.tpc-fields {
  display: flex;
  flex-direction: column;
  gap: 10px;
  overflow: hidden;
}

.dense .tpc-fields {
  gap: 8px;
}

.tpc-field {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.tpc-label {
  font-size: 11px;
  font-weight: 500;
  color: var(--form-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dense .tpc-label {
  font-size: 10px;
}

.tpc-required {
  color: #ef4444;
  margin-left: 2px;
}

.tpc-more {
  margin-top: auto;
  text-align: center;
  font-size: 10px;
  font-weight: 600;
  color: var(--form-color);
  padding: 6px 0 2px;
  border-top: 1px solid var(--form-border);
}
</style>

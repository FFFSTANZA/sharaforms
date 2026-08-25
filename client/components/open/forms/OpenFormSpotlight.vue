<template>
  <form ref="formElement" v-if="form" @submit.prevent="" class="w-full relative flex flex-col min-h-full" :style="spotlightFormStyle">
    <!-- Fixed fullscreen background from form cover -->
    <div v-if="showBrandingMedia && form.cover_picture" class="absolute inset-0 pointer-events-none">
      <BlockMediaLayout :image="coverMedia" alt="Form cover image" />
    </div>

    <!-- Thin progress bar (Typeform signature) -->
    <div v-if="showProgressBar && !isSubmitted" class="fixed top-0 left-0 right-0 z-50">
      <div class="h-[3px] bg-[var(--sf-bg-muted,#f3f4f6)]">
        <div
          class="h-full transition-all duration-500 ease-out rounded-full"
          :style="{ width: progressPercent + '%', backgroundColor: formColor }"
        />
      </div>
    </div>

    <!-- Persistent brand header -->
    <header
      v-if="showBrandingMedia && form.logo_picture"
      class="relative z-20 shrink-0 flex items-center px-6 pt-6 pb-2 pointer-events-none"
      :style="{ direction: form?.layout_rtl ? 'rtl' : 'ltr' }"
    >
      <img
        :src="form.logo_picture"
        :alt="form.seo_meta?.site_name ? `${form.seo_meta.site_name} logo` : 'Form logo'"
        class="h-6 sm:h-8 md:h-10 max-w-[10rem] sm:max-w-[12rem] md:max-w-[240px] w-auto object-contain"
      >
    </header>

    <!-- Step counter + estimated time -->
    <div
      v-if="!isSubmitted"
      class="shrink-0 flex items-center justify-between px-4 sm:px-6 pt-3 pb-1 max-w-xl mx-auto w-full"
    >
      <span class="text-xs font-medium text-[var(--sf-text-muted,#999)]">
        {{ currentIndex + 1 }} / {{ allFields.length }}
      </span>
      <span class="text-xs text-[var(--sf-text-muted,#999)]">
        {{ estimatedTimeLabel }}
      </span>
    </div>

    <!-- Password view (exclusive) -->
    <div v-if="$slots.password && form?.is_password_protected" class="w-full flex items-center px-6 grow min-h-0 z-10">
      <div class="w-full max-w-xl mx-auto p-4">
        <slot name="password" />
      </div>
    </div>
    <!-- Alerts view (exclusive) -->
    <div v-else-if="$slots.alerts" class="w-full flex items-center px-6 grow min-h-0 z-10">
      <div class="w-full max-w-2xl mx-auto p-4">
        <slot name="alerts" />
      </div>
    </div>
    <!-- After-submit view (exclusive) -->
    <div v-else-if="props.formManager?.state.isSubmitted && $slots['after-submit']" class="w-full flex items-center px-6 grow min-h-0 z-10">
      <div class="w-full max-w-2xl mx-auto p-4">
        <slot name="after-submit" :submittedData="props.formManager?.form?.data?.()" />
      </div>
    </div>

    <!-- Spotlight body -->
    <div
      v-else
      ref="scrollContainer"
      class="flex-1 overflow-y-auto px-4 sm:px-6"
      :class="isAdminPreview ? 'pt-4' : ''"
    >
      <div class="max-w-xl mx-auto">
        <div role="list" aria-label="Form steps">
          <div
            v-for="(field, idx) in allFields"
            :key="field.id || idx"
            ref="cardRefs"
            class="relative rounded-2xl transition-all duration-300 ease-out"
            :class="getCardClass(idx)"
            :aria-current="isActive(idx) ? 'step' : undefined"
            :aria-disabled="isFuture(idx) ? 'true' : undefined"
            role="listitem"
            :tabindex="isActive(idx) ? 0 : -1"
            @click="onCardClick(idx)"
            @keydown="onCardKeydown($event, idx)"
          >
            <!-- Completed: compact one-line summary -->
            <div v-if="isCompleted(idx)" class="spotlight-done flex items-center gap-2.5 cursor-pointer group/card">
              <div
                class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center"
                :style="{ backgroundColor: formColor }"
              >
                <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              </div>
              <span class="text-xs font-medium text-[var(--sf-text-muted,#9ca3af)] flex-shrink-0">{{ field.name || field.label }}</span>
              <span class="text-xs text-[var(--sf-text-body,#374151)] truncate">{{ getDisplayValue(field) }}</span>
              <!-- Admin edit on hover -->
              <div
                v-if="isAdminPreview"
                class="ml-auto opacity-0 group-hover/card:opacity-100 transition-opacity flex-shrink-0"
              >
                <div
                  role="button"
                  class="flex items-center justify-center w-5 h-5 rounded bg-[var(--sf-bg-surface)] border border-[var(--sf-border-card)] shadow-sm cursor-pointer text-[var(--sf-text-muted)] hover:text-[var(--sf-text-body)] transition-colors"
                  @click.stop.prevent="editFieldOptions(field)"
                >
                  <Icon name="i-lucide-settings" class="w-3 h-3" />
                </div>
              </div>
            </div>

            <!-- Active question -->
            <div v-else-if="isActive(idx)" class="spotlight-active">
              <!-- Badges row -->
              <div v-if="isLast || encouragementText" class="mb-3 flex items-center gap-2 flex-wrap">
                <span
                  v-if="isLast"
                  class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full"
                  :style="{ backgroundColor: formColor + '18', color: formColor }"
                >
                  Last question
                </span>
                <span v-if="encouragementText" class="text-[11px] text-[var(--sf-text-muted,#9ca3af)] italic">
                  {{ encouragementText }}
                </span>
              </div>

              <div
                class="relative"
                :class="[
                  isAdminPreview ? 'group/focusedfield cursor-pointer rounded-xl border border-transparent box-border transition-all duration-150 hover:border-[var(--sf-border-card)] hover:shadow-md' : '',
                  beingEdited(field) ? 'bg-[var(--sf-nav-active-bg)]/80 hover:!bg-[var(--sf-nav-active-bg)]/80 rounded-xl ring-1 ring-inset ring-[var(--sf-coral-500)]/15 [&_*]:focus:ring-0 [&_*]:focus-visible:ring-0 [&_*]:focus:border-transparent' : ''
                ]"
                @click="setFieldAsSelected(field)"
                @dblclick="editFieldOptions(field)"
              >
                <BlockRenderer :block="field" :form-manager="formManager" @input-filled="onInputFilled" />
                <div
                  v-if="isAdminPreview"
                  class="absolute top-1 right-1 z-20 hidden group-hover/focusedfield:flex items-center"
                >
                  <div
                    aria-label="Settings"
                    role="button"
                    class="flex items-center justify-center w-6 h-6 rounded-md bg-[var(--sf-bg-surface)] border border-[var(--sf-border-card)] shadow-sm cursor-pointer text-[var(--sf-text-muted)] hover:text-[var(--sf-text-body)] transition-colors"
                    @click.stop.prevent="editFieldOptions(field)"
                  >
                    <Icon name="i-lucide-settings" class="w-4 h-4" />
                  </div>
                </div>
              </div>

              <!-- Buttons below active question -->
              <div v-if="!props.formManager?.state.isSubmitted" class="mt-5 flex gap-2.5 shrink-0" :class="getFieldAlignClasses(field)">
                <slot name="submit-btn" v-if="isLast" :loading="isProcessing">
                  <CaptchaWrapper v-if="form.use_captcha" :form-manager="formManager" />
                  <open-form-button
                    native-type="button"
                    :form="form"
                    class="px-6 sm:px-8"
                    :loading="isProcessing"
                    @click.prevent.stop="handleSubmitClick"
                  >
                    {{ form?.translations?.focused_submit_button_text || form.submit_button_text || $t('forms.buttons.submit') }}
                  </open-form-button>
                </slot>
                <!-- Next button for non-last cards -->
                <open-form-button
                  v-else
                  native-type="button"
                  :form="form"
                  class="px-4 sm:px-6"
                  :loading="isProcessing"
                  @click.stop="handleNextClick"
                >
                  {{ currentBlock?.next_btn_text || form?.translations?.focused_next_button_text || $t('forms.buttons.next') }}
                </open-form-button>
              </div>
              <div v-if="hasPaymentBlock" class="mt-3">
                <p class="text-xs text-[var(--sf-text-muted)] max-w-md">
                  {{ $t('forms.payment.payment_disclaimer') }}
                </p>
              </div>
            </div>

            <!-- Dimmed / future question: icon + name -->
            <div v-else class="spotlight-future">
              <Icon :name="getFieldIcon(field)" class="w-3.5 h-3.5 flex-shrink-0 text-[var(--sf-text-muted,#9ca3af)] mr-2.5 opacity-60" />
              <span v-if="field.type === 'nf-text'" class="text-xs font-semibold tracking-wide uppercase text-[var(--sf-text-muted,#9ca3af)]">{{ stripHtml(field.content || '').slice(0, 80) || 'Section' }}</span>
              <span v-else class="text-xs font-medium text-[var(--sf-text-muted,#9ca3af)]">{{ field.name || field.label }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cleanings slot -->
    <div class="fixed bottom-4 left-4 max-w-full z-10" v-if="$slots.cleanings">
      <div class="max-w-lg">
        <slot name="cleanings" />
      </div>
    </div>

    <!-- Branding button -->
    <div class="fixed bottom-4 right-4 sm:bottom-8 sm:right-8 z-10" v-if="!form.no_branding && showBranding">
      <PoweredBy :color="form.color" />
    </div>
  </form>
</template>

<script setup>
import BlockRenderer from './BlockRenderer.vue'
import BlockMediaLayout from './components/BlockMediaLayout.vue'
import OpenFormButton from './OpenFormButton.vue'
import CaptchaWrapper from '~/components/forms/heavy/components/CaptchaWrapper.vue'
import { FormMode } from '~/lib/forms/FormModeStrategy.js'
import { useFormImagePreloader } from '~/composables/forms/useFormImagePreloader.js'
import PoweredBy from '~/components/pages/forms/show/PoweredBy.vue'
import { useWorkingFormStore } from '~/stores/working_form'

const props = defineProps({
  formManager: { type: Object, required: true }
})

const emit = defineEmits(['submit'])

const form = computed(() => props.formManager.config.value)
const structure = props.formManager.structure
const state = computed(() => props.formManager.state)
const isTemplateMode = computed(() => props.formManager?.mode?.value === FormMode.TEMPLATE)
const isDemoMode = computed(() => props.formManager?.mode?.value === FormMode.DEMO)

const spotlightFormStyle = {
  minHeight: 'var(--form-focused-step-height, 100svh)'
}

// All visible fields (flat list, one per page in the structure)
const allFields = computed(() => {
  if (!structure?.value?.fieldGroups?.value) return []
  return structure.value.fieldGroups.value.map(group => group[0]).filter(Boolean)
})

const currentIndex = computed(() => state.value.currentPage)
const currentBlock = computed(() => allFields.value[currentIndex.value] || null)
const isLast = computed(() => structure?.value?.isLastPage?.value ?? false)
const isProcessing = computed(() => props.formManager.state.isProcessing)
const hasPaymentBlock = computed(() => structure.value?.currentPageHasPaymentBlock?.value ?? false)
const isSubmitted = computed(() => props.formManager?.state?.isSubmitted ?? false)

const canGoPrev = computed(() => currentIndex.value > 0)

// ── Active accent color ──
const formColor = computed(() => form.value?.color || '#EA6676')

// ── Progress bar ──
const showProgressBar = computed(() => form.value?.show_progress_bar)
const progressPercent = computed(() => {
  const total = allFields.value.length
  if (total <= 1) return 100
  if (isSubmitted.value) return 100
  return Math.round((currentIndex.value / total) * 100)
})

// ── Encouragement micro-copy ──
const encouragementText = computed(() => {
  const total = allFields.length
  if (total <= 3) return ''
  const progress = (currentIndex.value + 1) / total
  if (progress >= 0.75) return 'Almost there!'
  if (progress >= 0.5) return 'Halfway done'
  if (progress >= 0.25 && currentIndex.value > 0) return 'Quarter way there'
  return ''
})

// ── Estimated completion time ──
const estimatedTimeLabel = computed(() => {
  const total = allFields.length
  const remaining = total - currentIndex.value
  if (remaining <= 0) return ''
  let seconds = 0
  for (let i = currentIndex.value; i < total; i++) {
    const f = allFields.value[i]
    if (!f) continue
    if (['nf-text', 'nf-divider', 'nf-image', 'nf-code'].includes(f.type)) {
      seconds += 5
    } else {
      seconds += 15
    }
  }
  const mins = Math.ceil(seconds / 60)
  if (mins <= 1) return 'Less than 1 min'
  return `About ${mins} min`
})

// Card state helpers
function isCompleted(idx) {
  return idx < currentIndex.value
}

function isActive(idx) {
  return idx === currentIndex.value
}

function isFuture(idx) {
  return idx > currentIndex.value
}

function stripHtml(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, '').trim()
}

// Display value for completed summary
function getDisplayValue(field) {
  if (!field) return ''
  const data = props.formManager?.form?.data?.() || {}
  const val = data[field.id]
  if (val === undefined || val === null || val === '') return ''

  if (field.type === 'select' || field.type === 'multi_select') {
    const options = field[field.type]?.options || []
    if (Array.isArray(val)) {
      return val.map(v => {
        const opt = options.find(o => o.id === v || o.name === v)
        return opt?.name || v
      }).join(', ')
    }
    const opt = options.find(o => o.id === val || o.name === val)
    return opt?.name || val
  }
  if (field.type === 'checkbox') return val ? 'Yes' : 'No'
  if (field.type === 'rating' || field.type === 'scale') return `${val}`
  return String(val)
}

// ── Field type → Lucide icon mapping ──
const fieldTypeIcons = {
  text: 'i-lucide-type',
  email: 'i-lucide-mail',
  phone_number: 'i-lucide-phone',
  number: 'i-lucide-hash',
  textarea: 'i-lucide-align-left',
  rich_text: 'i-lucide-file-text',
  select: 'i-lucide-chevrons-up-down',
  multi_select: 'i-lucide-list',
  checkbox: 'i-lucide-square-check',
  radio: 'i-lucide-circle-dot',
  date: 'i-lucide-calendar',
  rating: 'i-lucide-star',
  scale: 'i-lucide-sliders-horizontal',
  files: 'i-lucide-upload',
  url: 'i-lucide-link',
  payment: 'i-lucide-credit-card',
  signature: 'i-lucide-pen-tool',
  toggle_switch: 'i-lucide-toggle-right',
  password: 'i-lucide-lock',
  slider: 'i-lucide-sliders-horizontal',
  barcode: 'i-lucide-barcode',
  qrcode: 'i-lucide-qr-code',
  matrix: 'i-lucide-grid-3x3',
  nf_text: 'i-lucide-type',
  nf_divider: 'i-lucide-minus',
  nf_image: 'i-lucide-image',
  nf_video: 'i-lucide-video',
  nf_code: 'i-lucide-code',
  nf_page_break: 'i-lucide-minus'
}

function getFieldIcon(field) {
  return fieldTypeIcons[field?.type] || 'i-lucide-circle'
}

// ── Card classes ──
function getCardClass(idx) {
  if (isCompleted(idx)) {
    return 'spotlight-card--completed'
  }
  if (isActive(idx)) {
    return 'spotlight-card--active'
  }
  return 'spotlight-card--future'
}

function getFieldAlignClasses(field) {
  if (!field?.align || field.align === 'left') return 'justify-start'
  else if (field.align === 'right') return 'justify-end'
  else if (field.align === 'center') return 'justify-center'
  else return 'justify-start'
}

// ── Template refs ──
const formElement = ref(null)
const scrollContainer = ref(null)
const cardRefs = ref([])

const activeCardEl = computed(() => {
  if (!cardRefs.value || cardRefs.value.length === 0) return null
  return cardRefs.value[currentIndex.value] || null
})

// ── Keyboard navigation ──
function onCardKeydown(event, idx) {
  if (!isActive(idx)) return
  if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
    event.preventDefault()
    handleNextClick()
  } else if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
    event.preventDefault()
    goPrev()
  }
}

function onGlobalKeydown(event) {
  if (isSubmitted.value) return
  const tag = event.target?.tagName
  const inInput = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || event.target?.isContentEditable

  if (inInput) {
    if (event.key === 'Enter' && tag !== 'TEXTAREA' && !event.target?.isContentEditable) {
      event.preventDefault()
      if (isLast.value) {
        if (!isProcessing.value) emit('submit')
      } else {
        handleNextClick()
      }
    }
    return
  }

  if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
    event.preventDefault()
    handleNextClick()
  } else if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
    event.preventDefault()
    goPrev()
  }
}

// ── Navigation ──
let _lastAdvanceTime = 0
const ADVANCE_DEBOUNCE_MS = 400

const handleNextClick = () => {
  const now = Date.now()
  if (now - _lastAdvanceTime < ADVANCE_DEBOUNCE_MS) return
  _lastAdvanceTime = now

  props.formManager.nextPage().then((moved) => {
    if (moved && import.meta.client && !isTemplateMode.value && !isDemoMode.value) {
      scrollToActive()
    }
  })
}

const goPrev = () => {
  if (!canGoPrev.value) return
  try {
    props.formManager.previousPage()
    if (import.meta.client && !isDemoMode.value) {
      nextTick(() => scrollToActive())
    }
  } catch (error) {
    console.warn('[OpenFormSpotlight] Error going to previous page:', error)
  }
}

// ── Scroll behavior ──
function scrollToActive() {
  nextTick(() => {
    setTimeout(() => {
      const el = activeCardEl.value
      if (el) {
        const isMobile = import.meta.client && window.innerWidth < 640
        el.scrollIntoView({ behavior: 'smooth', block: isMobile ? 'nearest' : 'center' })
      } else {
        window.scrollTo({ top: 0, behavior: 'smooth' })
      }
    }, 50)
  })
}

// Click completed card to go back
function onCardClick(idx) {
  if (isCompleted(idx) && !isProcessing.value) {
    state.value.currentPage = idx
    if (import.meta.client && !isDemoMode.value) scrollToActive()
  }
}

// Auto-advance on selection inputs
const autoNextFieldTypes = new Set(['checkbox', 'date', 'multi_select', 'rating', 'scale', 'select'])

const onInputFilled = () => {
  if (currentBlock.value?.type && autoNextFieldTypes.has(currentBlock.value.type) && form.value?.settings?.auto_next === false) {
    return
  }
  if (isLast.value) {
    if (isProcessing.value) return
    emit('submit')
    return
  }
  setTimeout(() => handleNextClick(), 180)
}

const handleSubmitClick = (event) => {
  if (isProcessing.value) {
    event?.preventDefault()
    return
  }
  emit('submit')
}

// Media / branding
const coverMedia = computed(() => ({
  url: form.value?.cover_picture,
  focal_point: form.value?.cover_settings?.focal_point,
  brightness: form.value?.cover_settings?.brightness
}))

useFormImagePreloader(form, state, { skipPreload: isDemoMode.value })

// Admin preview
const workingFormStore = useWorkingFormStore()
const isAdminPreview = computed(() => props.formManager?.strategy?.value?.admin?.showAdminControls || false)

function beingEdited(field) {
  if (!isAdminPreview.value || !workingFormStore.showEditFieldSidebar || !field) return false
  return workingFormStore.objectToIndex(field) === workingFormStore.selectedFieldIndex
}

function setFieldAsSelected(field) {
  if (!isAdminPreview.value || !workingFormStore.showEditFieldSidebar || !field) return
  workingFormStore.openSettingsForField(field)
}

function editFieldOptions(field) {
  if (!isAdminPreview.value || !field) return
  workingFormStore.openSettingsForField(field, true)
}

// Branding
const showBranding = computed(() => props.formManager?.strategy?.value?.display?.showBranding ?? true)
const showBrandingMedia = computed(() => {
  const mode = props.formManager?.mode?.value
  if (mode === FormMode.READ_ONLY || mode === FormMode.EDIT) return false
  return !!(form.value && (form.value.logo_picture || form.value.cover_picture))
})

// ── Focus management ──
const focusCurrentInput = () => {
  if (import.meta.server || isTemplateMode.value || !form.value?.auto_focus) return
  nextTick(() => {
    setTimeout(() => {
      if (!formElement.value) return
      const activeCard = activeCardEl.value
      if (!activeCard) return

      const focusableSelectors = [
        'input:not([type="hidden"]):not([disabled])',
        'textarea:not([disabled])',
        'select:not([disabled])',
        'button[aria-haspopup="listbox"]:not([disabled])',
        '[contenteditable="true"]',
        'button[role="radio"]:not([disabled])',
        'button[role="checkbox"]:not([disabled])',
        '[role="listbox"][tabindex="0"]'
      ]

      const focusableElements = activeCard.querySelectorAll(focusableSelectors.join(', '))

      let firstVisible = null
      for (const element of focusableElements) {
        if (element.hasAttribute('hidden')) continue
        if (element.getAttribute('aria-hidden') === 'true') continue
        if (element.offsetParent !== null || element.getClientRects().length > 0) {
          firstVisible = element
          break
        }
      }

      if (firstVisible && typeof firstVisible.focus === 'function') {
        firstVisible.focus({ preventScroll: true })
      } else {
        activeCard.focus({ preventScroll: true })
      }
    }, 350)
  })
}

watch(currentIndex, () => {
  if (form.value?.auto_focus) focusCurrentInput()
})

onMounted(() => {
  if (form.value?.auto_focus) focusCurrentInput()
  if (import.meta.client) {
    document.addEventListener('keydown', onGlobalKeydown)
  }
})

onUnmounted(() => {
  if (import.meta.client) {
    document.removeEventListener('keydown', onGlobalKeydown)
  }
})
</script>

<style scoped>
/*
 * Visual rhythm (top → bottom):
 *   completed trail (tiny, faded, tight gaps)
 *   ── 32px breathing room ──
 *   ACTIVE card (big, elevated, generous padding)
 *   ── 24px gap ──
 *   future cards (dimmed, peek)
 */

/* ── Completed: compact one-line row, tight spacing ── */
.spotlight-card--completed {
  background: transparent;
  border: 1px solid transparent;
  border-radius: 12px;
  cursor: pointer;
  opacity: 0.55;
  transition: all 0.2s ease;
  margin-bottom: 2px;
}

.spotlight-card--completed:hover {
  background: var(--sf-bg-surface, #f9fafb);
  opacity: 0.75;
}

.spotlight-done {
  padding: 6px 14px;
  min-height: 32px;
}

/* ── Active: the spotlight — big, elevated, centered ── */
.spotlight-card--active {
  background: var(--sf-bg-surface, #ffffff);
  border: 1px solid var(--sf-border-card, #e5e7eb);
  border-radius: 16px;
  box-shadow:
    0 4px 6px -1px rgba(0, 0, 0, 0.07),
    0 10px 25px -5px rgba(0, 0, 0, 0.06),
    0 0 0 1px rgba(0, 0, 0, 0.02);
  opacity: 1;
  z-index: 10;
  margin-top: 32px;
  margin-bottom: 24px;
}

.spotlight-active {
  padding: 28px 28px 12px;
}

/* ── Future: dimmed, peeking below ── */
.spotlight-card--future {
  background: transparent;
  border: 1px solid transparent;
  border-radius: 12px;
  opacity: 0.3;
  pointer-events: none;
  margin-bottom: 2px;
}

.spotlight-future {
  padding: 8px 14px;
  min-height: 36px;
  display: flex;
  align-items: center;
}
</style>

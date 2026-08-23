<template>
  <div>
    <div class="flex flex-wrap gap-2">
      <UButton
        variant="outline"
        color="neutral"
        icon="i-lucide-code"
        @click="onOpenClick"
      >
        Embed settings
      </UButton>
    </div>

    <UModal
      v-model:open="isModalOpen"
      :ui="{ content: 'sm:max-w-3xl' }"
      title="Embed your form"
    >
      <template #body>
        <!-- Type selector -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-3">Embed type</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <button
              v-for="opt in embedTypes"
              :key="opt.value"
              :disabled="opt.locked"
              class="relative flex flex-col items-center gap-2 p-4 rounded-lg border-2 transition-colors text-center"
              :class="embedType === opt.value
                ? 'border-primary bg-primary-50 dark:bg-primary-900/20'
                : opt.locked
                  ? 'border-[var(--sf-border-card)] opacity-50 cursor-not-allowed'
                  : 'border-[var(--sf-border-card)] hover:border-[var(--sf-hover-border)]'"
              @click="selectType(opt.value)"
            >
              <UIcon :name="opt.icon" class="w-6 h-6" />
              <span class="text-sm font-medium">{{ opt.label }}</span>
              <span v-if="opt.locked" class="text-xs text-[var(--sf-text-muted)] mt-1">
                <UIcon name="i-lucide-lock" class="w-3 h-3 inline" /> Pro
              </span>
            </button>
          </div>
        </div>

        <!-- Inline: info only -->
        <div v-if="embedType === 'inline'" class="space-y-4">
          <p class="text-[var(--sf-text-body)] text-sm">
            Inline embed places the form directly in your page content using an iframe.
            Use the <strong>Embed Code</strong> section below for the iframe + SDK snippet.
          </p>
        </div>

        <!-- Per-type settings -->
        <template v-if="embedType !== 'inline'">
          <div class="border-t pt-4 mb-4" />
          <h3 class="text-lg font-semibold mb-3">Appearance</h3>
          <VForm size="sm" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <ColorInput
                v-model="settings.color"
                name="embed-color"
                label="Accent color"
              />
              <FlatSelectInput
                v-model="settings.position"
                name="embed-position"
                label="Position"
                :options="[
                  { name: 'Bottom Right', value: 'right' },
                  { name: 'Bottom Left', value: 'left' },
                ]"
              />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <TextInput
                v-model="settings.width"
                name="embed-width"
                label="Width (px)"
                native-type="number"
                :min="300"
                :max="1200"
              />
              <TextInput
                v-model="settings.height"
                name="embed-height"
                label="Height (px)"
                native-type="number"
                :min="300"
                :max="1200"
              />
            </div>
            <TextInput
              v-model="settings.icon"
              name="embed-icon"
              label="Trigger icon"
              :max-char-limit="10"
              class="max-w-xs"
            />
            <TextInput
              v-model="settings.title"
              name="embed-title"
              label="Title text"
              placeholder="Contact us"
              :max-char-limit="100"
              class="max-w-xs"
            />
          </VForm>
        </template>

        <!-- Embed code section -->
        <div class="border-t pt-4 mt-6" />
        <h3 class="text-lg font-semibold mb-2">Embed code</h3>
        <p class="text-[#6E7278] text-[13px] mb-3">
          {{ embedType === 'inline'
            ? 'Copy the iframe + SDK snippet into your website.'
            : 'Copy this script tag into the &lt;head&gt; section of your website.' }}
        </p>

        <div class="border border-[#E4F4F8] bg-[#E4F4F8] dark:bg-notion-dark-light rounded-md p-4 mb-4 w-full select-all">
          <div class="flex items-center">
            <code class="select-all text-[#0891b2] flex-grow break-all text-xs leading-relaxed whitespace-pre-wrap">{{ embedCode }}</code>
            <div
              class="hover:bg-[#E4F4F8] rounded-sm transition-colors cursor-pointer flex-shrink-0 ml-2 p-1"
              @click="copyToClipboard"
            >
              <Icon name="lucide:clipboard-list" class="h-5 w-5 text-[#0891b2]" />
            </div>
          </div>
        </div>

        <!-- Save & close -->
        <div class="border-t pt-4 mt-4 flex items-center justify-between">
          <p v-if="saveMessage" class="text-[13px] text-[#16a34a]">{{ saveMessage }}</p>
          <div class="flex gap-2 ml-auto">
            <UButton variant="outline" color="neutral" @click="onClose">
              Close
            </UButton>
            <UButton
              v-if="embedType !== 'inline'"
              color="primary"
              :loading="saving"
              @click="saveSettings"
            >
              Save settings
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue"
import { appUrl } from "~/lib/utils.js"
import { useForms } from "~/composables/query/forms/useForms.js"

const { copy } = useClipboard()
const props = defineProps({
  form: { type: Object, required: true },
})

const { update: updateForm } = useForms()

const isModalOpen = ref(false)
const showPreview = ref(false)
const saving = ref(false)
const saveMessage = ref("")

const embedScriptUrl = "/widgets/embed-min.js"

const embedTypes = computed(() => {
  const isPro = (props.form.plan_tier !== 'free' && props.form.plan_tier !== undefined) || props.form.is_trialing
  return [
    { value: 'inline', label: 'Inline', icon: 'i-lucide-frame', locked: false },
    { value: 'popup', label: 'Popup', icon: 'i-lucide-maximize-2', locked: !isPro },
    { value: 'slide-in', label: 'Slide-in', icon: 'i-lucide-panel-right', locked: !isPro },
    { value: 'bubble', label: 'Bubble', icon: 'i-lucide-message-circle', locked: !isPro },
  ]
})

const embedType = ref(props.form.embed_type || 'inline')

const settings = ref({
  position: props.form.embed_settings?.position || 'right',
  color: props.form.embed_settings?.color || props.form.color || '#EA6676',
  width: props.form.embed_settings?.width || 500,
  height: props.form.embed_settings?.height || 600,
  icon: props.form.embed_settings?.icon || '💬',
  title: props.form.embed_settings?.title || '',
})

function selectType(type) {
  const opt = embedTypes.value.find(o => o.value === type)
  if (opt?.locked) {
    useAlert().warning('Advanced embed types require a Pro plan.')
    return
  }
  embedType.value = type
}

function onOpenClick() {
  const style = props.form?.presentation_style || 'classic'
  if (style === 'focused' || style === 'spotlight') {
    useAlert().warning('Focused and Spotlight mode forms do not support overlay embeds.')
    return
  }
  isModalOpen.value = true
}

function onClose() {
  removePreview()
  isModalOpen.value = false
}

function copyToClipboard() {
  if (import.meta.server) return
  copy(embedCode.value)
  useAlert().success("Copied!")
}

function saveSettings() {
  saving.value = true
  saveMessage.value = ""
  const mutation = updateForm(props.form.id)
  mutation.mutate({
    embed_type: embedType.value,
    embed_settings: settings.value,
  }, {
    onSuccess: (updatedForm) => {
      const form = updatedForm.form
      if (form) {
        Object.assign(props.form, form)
      }
      saveMessage.value = "Settings saved"
      setTimeout(() => { saveMessage.value = "" }, 3000)
      saving.value = false
    },
    onError: () => {
      useAlert().error("Failed to save embed settings")
      saving.value = false
    },
  })
}

const embedCode = computed(() => {
  if (embedType.value === 'inline') {
    const shareUrl = props.form.share_url
    var scrEnd = "/" + "script>"
    return `<iframe style="border:none;width:100%;" id="${props.form.slug}" src="${shareUrl}"></iframe>\n<script src="${appUrl("/widgets/sharaforms-sdk.min.js")}"><` + scrEnd
  }

  const nfData = {
    formurl: props.form.share_url,
    type: embedType.value,
    position: settings.value.position,
    color: settings.value.color,
    width: settings.value.width,
    height: settings.value.height,
    icon: settings.value.icon,
    title: settings.value.title,
  }

  return `<script async data-nf='${JSON.stringify(nfData)}' src='${appUrl(embedScriptUrl)}'><` + "/" + "script>"
})

watch(() => isModalOpen.value, (open) => {
  if (open) {
    embedType.value = props.form.embed_type || 'inline'
    settings.value = {
      position: props.form.embed_settings?.position || 'right',
  color: props.form.embed_settings?.color || props.form.color || '#EA6676',
      width: props.form.embed_settings?.width || 500,
      height: props.form.embed_settings?.height || 600,
      icon: props.form.embed_settings?.icon || '💬',
      title: props.form.embed_settings?.title || '',
    }
    previewEmbed()
  } else {
    removePreview()
  }
})

function previewEmbed() {
  if (import.meta.server || embedType.value === 'inline') return
  showPreview.value = true
  removePreview()
  const el = document.createElement("script")
  el.id = "nf-popup-preview"
  el.async = true
  el.src = embedScriptUrl
  el.setAttribute("data-nf", JSON.stringify({
    formurl: props.form.share_url,
    type: embedType.value,
    position: settings.value.position,
    color: settings.value.color,
    width: settings.value.width,
    height: settings.value.height,
    icon: settings.value.icon,
    title: settings.value.title,
  }))
  document.head.appendChild(el)
}

function removePreview() {
  if (import.meta.server) return
  showPreview.value = false
  const oldP = document.head.querySelector("#nf-popup-preview")
  if (oldP) oldP.remove()
  const oldM = document.body.querySelector(".nf-main")
  if (oldM) oldM.remove()
}
</script>

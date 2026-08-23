<template>
  <VForm size="sm">
    <div class="px-1 space-y-4">
      <!-- Password Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-nav-active-bg)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-shield-halved text-[12px] text-[var(--sf-coral-500)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Security & Access</h3>
        </div>

        <TextInput
          name="password"
          :form="form"
          class="max-w-xs"
          label="Form Password"
          placeholder="********"
          help="Leave empty to disable password protection"
        />

        <div v-if="hasCaptcha" class="mt-4 flex items-start gap-6 flex-wrap">
          <ToggleSwitchInput
            name="use_captcha"
            :form="form"
            label="Bot Protection"
            help="Protects your form from spam and abuse with a captcha"
          />
          <FlatSelectInput
            v-if="form.use_captcha"
            name="captcha_provider"
            :form="form"
            :options="captchaOptions"
            class="w-80"
            label="Select a captcha provider"
          />
        </div>
      </div>

      <!-- Scheduling Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-teal-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-calendar-days text-[12px] text-[var(--sf-teal)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">
            Form Scheduling
            <PlanTag class="ml-1" feature="form_scheduling" upgrade-modal-title="Upgrade to schedule your form" upgrade-modal-description="Control exactly when your form opens and closes." />
          </h3>
        </div>

        <ToggleSwitchInput
          v-model="enableScheduling"
          label="Enable form scheduling"
          help="Set when your form opens and/or define recurring availability windows"
        />

        <template v-if="enableScheduling">
          <div class="mt-4 space-y-4">
            <DateInput
              :with-time="true"
              name="opens_at"
              class="max-w-xs"
              :form="form"
              label="Opening date"
              help="Set when the form opens for submissions. Leave empty to open immediately."
              :required="false"
            />
            <DateInput
              :with-time="true"
              name="closes_at"
              class="max-w-xs"
              :form="form"
              label="Closing date"
              help="Leave empty to keep the form open indefinitely"
              :required="false"
            />
          </div>

          <div
            v-if="form.opens_at || form.closes_at || form.visibility == 'closed'"
            class="mt-4 bg-[var(--sf-bg-muted)] border border-[var(--sf-border-card)] rounded-xl px-4 py-3"
          >
            <rich-text-area-input
              name="closed_text"
              :form="form"
              :allow-fullscreen="true"
              label="Closed/scheduled form text"
              help="This message will be shown when the form is closed or not yet open"
              :required="false"
              wrapper-class="mb-0"
            />
          </div>

          <div class="mt-4">
            <ToggleSwitchInput
              v-model="enableRecurringSchedule"
              label="Recurring availability"
              help="Set weekly recurring windows when the form accepts submissions"
            />
          </div>

          <template v-if="enableRecurringSchedule">
            <div class="mt-4 bg-[var(--sf-bg-muted)] border border-[var(--sf-border-card)] rounded-xl px-4 py-4 space-y-4 max-w-lg">
              <div
                v-for="(window, index) in scheduleWindows"
                :key="index"
                class="border border-[var(--sf-border-card)] rounded-xl p-3 bg-[var(--sf-bg-surface)]"
              >
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-[var(--sf-text-secondary)]">Window {{ index + 1 }}</span>
                  <UButton v-if="scheduleWindows.length > 1" color="error" variant="ghost" size="2xs" icon="i-lucide-x" @click="removeWindow(index)" />
                </div>
                <div class="mb-2">
                  <label class="text-xs text-[var(--sf-text-caption)] block mb-1">Days of the week</label>
                  <div class="flex flex-wrap gap-1.5">
                    <UBadge
                      v-for="day in daysOfWeek"
                      :key="day.value"
                      :variant="window.days.includes(day.value) ? 'solid' : 'outline'"
                      :color="window.days.includes(day.value) ? 'primary' : 'neutral'"
                      class="cursor-pointer select-none"
                      @click="toggleDay(index, day.value)"
                    >{{ day.label }}</UBadge>
                  </div>
                </div>
                <div class="flex items-center gap-3 mt-2">
                  <div class="flex-1">
                    <label class="text-xs text-[var(--sf-text-caption)] block mb-1">Start time</label>
                    <input type="time" :value="window.start_time" class="block w-full rounded-md border-[var(--sf-border-button)] text-sm shadow-xs focus:border-[var(--sf-coral-500)] focus:ring-[var(--sf-coral-500)]" @input="updateWindowTime(index, 'start_time', $event.target.value)" />
                  </div>
                  <div class="flex-1">
                    <label class="text-xs text-[var(--sf-text-caption)] block mb-1">End time</label>
                    <input type="time" :value="window.end_time" class="block w-full rounded-md border-[var(--sf-border-button)] text-sm shadow-xs focus:border-[var(--sf-coral-500)] focus:ring-[var(--sf-coral-500)]" @input="updateWindowTime(index, 'end_time', $event.target.value)" />
                  </div>
                </div>
              </div>
              <UButton color="neutral" variant="outline" size="sm" icon="i-lucide-plus" @click="addWindow">Add window</UButton>
              <div class="mt-2">
                <label class="text-xs text-[var(--sf-text-caption)] block mb-1">Timezone</label>
                <USelect v-model="scheduleTimezone" :items="commonTimezones" class="w-full" />
              </div>
            </div>
          </template>
        </template>

        <!-- Legacy closing date -->
        <template v-if="!enableScheduling">
          <div class="mt-4 space-y-4">
            <DateInput
              :with-time="true"
              name="closes_at"
              class="max-w-xs"
              :form="form"
              label="Closing date"
              help="Leave empty to keep the form open indefinitely"
              :required="false"
            />
          </div>
          <div
            v-if="form.closes_at || form.visibility == 'closed'"
            class="mt-4 bg-[var(--sf-bg-muted)] border border-[var(--sf-border-card)] rounded-xl px-4 py-3"
          >
            <rich-text-area-input
              name="closed_text"
              :form="form"
              :allow-fullscreen="true"
              label="Closed form text"
              help="This message will be shown when the form will be closed"
              :required="false"
              wrapper-class="mb-0"
            />
          </div>
        </template>
      </div>

      <!-- Submission Limits Card -->
      <div class="rounded-2xl border border-[var(--sf-border-card)] bg-[var(--sf-bg-surface)] p-5 shadow-[var(--sf-shadow-card)]">
        <div class="flex items-center gap-2.5 mb-5">
          <div class="w-7 h-7 rounded-lg bg-[var(--sf-amber-light)] flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-chart-bar text-[12px] text-[var(--sf-amber)]"></i>
          </div>
          <h3 class="text-[13px] font-semibold text-[var(--sf-text-primary)]">Submission Limits</h3>
        </div>

        <text-input
          name="max_submissions_count"
          native-type="number"
          :min="1"
          :form="form"
          label="Limit number of submissions"
          placeholder="Max submissions"
          class="max-w-xs"
          help="Leave empty for unlimited submissions"
          :required="false"
        />
        <div
          v-if="form.max_submissions_count && form.max_submissions_count > 0"
          class="mt-4 bg-[var(--sf-bg-muted)] border border-[var(--sf-border-card)] rounded-xl px-4 py-3"
        >
          <rich-text-area-input
            wrapper-class="mb-0"
            :allow-fullscreen="true"
            name="max_submissions_reached_text"
            :form="form"
            label="Max Submissions reached text"
            help="This message will be shown when the form will have the maximum number of submissions"
            :required="false"
          />
        </div>
      </div>
    </div>
  </VForm>
</template>

<script setup>
import PlanTag from "~/components/app/PlanTag.vue"

const workingFormStore = useWorkingFormStore()
const { content: form } = storeToRefs(workingFormStore)
const config = useRuntimeConfig()

const daysOfWeek = [
  { label: 'Mon', value: 'monday' },
  { label: 'Tue', value: 'tuesday' },
  { label: 'Wed', value: 'wednesday' },
  { label: 'Thu', value: 'thursday' },
  { label: 'Fri', value: 'friday' },
  { label: 'Sat', value: 'saturday' },
  { label: 'Sun', value: 'sunday' },
]

const commonTimezones = [
  { label: 'UTC (UTC+00:00)', value: 'UTC' },
  { label: 'US/Eastern (UTC-05:00)', value: 'US/Eastern' },
  { label: 'US/Central (UTC-06:00)', value: 'US/Central' },
  { label: 'US/Mountain (UTC-07:00)', value: 'US/Mountain' },
  { label: 'US/Pacific (UTC-08:00)', value: 'US/Pacific' },
  { label: 'Europe/London (UTC+00:00)', value: 'Europe/London' },
  { label: 'Europe/Paris (UTC+01:00)', value: 'Europe/Paris' },
  { label: 'Europe/Berlin (UTC+01:00)', value: 'Europe/Berlin' },
  { label: 'Europe/Moscow (UTC+03:00)', value: 'Europe/Moscow' },
  { label: 'Asia/Dubai (UTC+04:00)', value: 'Asia/Dubai' },
  { label: 'Asia/Kolkata (UTC+05:30)', value: 'Asia/Kolkata' },
  { label: 'Asia/Shanghai (UTC+08:00)', value: 'Asia/Shanghai' },
  { label: 'Asia/Tokyo (UTC+09:00)', value: 'Asia/Tokyo' },
  { label: 'Asia/Seoul (UTC+09:00)', value: 'Asia/Seoul' },
  { label: 'Australia/Sydney (UTC+10:00)', value: 'Australia/Sydney' },
  { label: 'Pacific/Auckland (UTC+12:00)', value: 'Pacific/Auckland' },
]

const defaultWindow = () => ({
  days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
  start_time: '09:00',
  end_time: '17:00',
})

const scheduleWindows = ref([])
const scheduleTimezone = ref('UTC')

const enableRecurringSchedule = computed({
  get: () => {
    return form.value?.schedule && Array.isArray(form.value.schedule?.windows) && form.value.schedule.windows.length > 0
  },
  set: (val) => {
    if (val) {
      if (!form.value.schedule) {
        form.value.schedule = { windows: [defaultWindow()], timezone: 'UTC' }
      } else if (!form.value.schedule.windows || form.value.schedule.windows.length === 0) {
        form.value.schedule.windows = [defaultWindow()]
      }
      if (!form.value.schedule.timezone) {
        form.value.schedule.timezone = 'UTC'
      }
    } else {
      form.value.schedule = null
    }
    syncScheduleFromForm()
  }
})

const enableScheduling = computed({
  get: () => {
    return form.value?.opens_at || form.value?.schedule || enableRecurringSchedule.value
  },
  set: (val) => {
    if (!val) {
      form.value.opens_at = null
      form.value.schedule = null
    }
  }
})

function syncScheduleFromForm() {
  if (form.value?.schedule) {
    scheduleWindows.value = form.value.schedule.windows && form.value.schedule.windows.length > 0
      ? form.value.schedule.windows.map(w => ({ ...w, days: [...w.days] }))
      : [defaultWindow()]
    scheduleTimezone.value = form.value.schedule.timezone || 'UTC'
  } else {
    scheduleWindows.value = [defaultWindow()]
    scheduleTimezone.value = 'UTC'
  }
}

function syncScheduleToForm() {
  if (!form.value) return
  if (enableRecurringSchedule.value) {
    if (!form.value.schedule) {
      form.value.schedule = {}
    }
    form.value.schedule.windows = scheduleWindows.value.map(w => ({
      days: [...w.days],
      start_time: w.start_time,
      end_time: w.end_time,
    }))
    form.value.schedule.timezone = scheduleTimezone.value
  }
}

function toggleDay(windowIndex, day) {
  const window = scheduleWindows.value[windowIndex]
  const idx = window.days.indexOf(day)
  if (idx >= 0) {
    window.days.splice(idx, 1)
  } else {
    window.days.push(day)
  }
  syncScheduleToForm()
}

function updateWindowTime(windowIndex, field, value) {
  scheduleWindows.value[windowIndex][field] = value
  syncScheduleToForm()
}

function addWindow() {
  scheduleWindows.value.push({ ...defaultWindow(), days: [...defaultWindow().days] })
  syncScheduleToForm()
}

function removeWindow(index) {
  scheduleWindows.value.splice(index, 1)
  syncScheduleToForm()
}

// Watch for external changes to form.schedule
watch(() => form.value?.schedule, () => {
  syncScheduleFromForm()
}, { deep: true })

// Initialize from existing data
onMounted(() => {
  syncScheduleFromForm()
})

const hasCaptcha = computed(() => {
  return config.public.hCaptchaSiteKey || config.public.reCaptchaSiteKey
})

const captchaOptions = computed(() => {
  const options = []
  if (config.public.reCaptchaSiteKey) {
    options.push({ name: 'reCAPTCHA', value: 'recaptcha' })
  }
  if (config.public.hCaptchaSiteKey) {
    options.push({ name: 'hCaptcha', value: 'hcaptcha' })
  }
  return options
})
</script>

<template>
  <div class="group h-full rounded-xl border border-[var(--sf-border-card)] bg-white p-4 shadow-[var(--sf-shadow-card)] transition hover:shadow-md">
    <div class="flex items-center justify-between gap-2">
      <div>
        <p class="text-[13px] font-semibold text-[var(--sf-text-primary)]">{{ connection.name }}</p>
        <p class="text-[11px] uppercase tracking-wide text-[#A7ABB2]">{{ connection.slug }}</p>
      </div>
      <div class="flex items-center gap-2">
        <UButton
          v-if="canEdit"
          icon="i-lucide-trash-2"
          color="error"
          variant="ghost"
          size="xs"
          square
          class="opacity-0 transition group-hover:opacity-100"
          @click="emit('delete', connection)"
        />
        <UButton
          v-if="canEdit"
          icon="i-lucide-pen"
          color="neutral"
          variant="ghost"
          size="xs"
          square
          @click="emit('edit', connection)"
        />
        <UBadge :color="connection.enabled ? 'success' : 'neutral'" variant="subtle" size="sm">
          {{ connection.enabled ? 'Enabled' : 'Disabled' }}
        </UBadge>
      </div>
    </div>

    <p class="mt-3 text-[13px] text-[var(--sf-text-description)]">
      Issuer URL
      <span class="block font-medium text-[var(--sf-text-primary)] truncate">{{ connection.issuer }}</span>
    </p>

    <p class="mt-2 text-[13px] text-[var(--sf-text-description)]">
      Redirect URL
      <span class="block text-xs text-[#8E9198] truncate">{{ connection.redirect_url }}</span>
    </p>

    <p class="mt-2 text-[13px] text-[var(--sf-text-description)]">
      Email domain
      <span class="block font-medium text-[var(--sf-text-primary)]">{{ connection.domain ?? '—' }}</span>
    </p>
  </div>
</template>

<script setup>
import { toRef } from 'vue'

const props = defineProps({
  connection: { type: Object, required: true },
  canEdit: { type: Boolean, default: false },
})

const connection = toRef(props, 'connection')
const canEdit = toRef(props, 'canEdit')

const emit = defineEmits(['edit', 'delete'])
</script>


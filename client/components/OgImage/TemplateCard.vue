<script setup>
// Satori-rendered social card: single root, flexbox-only, inline styles.
defineProps({
  title: { type: String, default: 'Free Form Templates' },
  description: { type: String, default: '' },
  badge: { type: String, default: '' },
  tag: { type: String, default: '' },
})
</script>

<template>
  <div
    :style="{
      width: '100%',
      height: '100%',
      display: 'flex',
      flexDirection: 'column',
      justifyContent: 'space-between',
      padding: '64px 72px',
      fontFamily: 'Euclid',
      backgroundColor: '#170f38',
      backgroundImage: 'linear-gradient(135deg, #170f38 0%, #3b1470 55%, #7c1d6f 100%)',
    }"
  >
    <!-- Top row: brand + badge -->
    <div :style="{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }">
      <div :style="{ display: 'flex', alignItems: 'center' }">
        <div
          :style="{
            width: '44px',
            height: '44px',
            borderRadius: '13px',
            marginRight: '18px',
            backgroundImage: 'linear-gradient(135deg, #ef5da8, #8b5cf6)',
          }"
        />
        <div :style="{ fontSize: '34px', fontWeight: 600, color: '#ffffff' }">SharaForms</div>
      </div>
      <div
        v-if="badge"
        :style="{
          display: 'flex',
          padding: '10px 24px',
          borderRadius: '999px',
          border: '2px solid rgba(255,255,255,0.35)',
          color: '#ffffff',
          fontSize: '23px',
          letterSpacing: '3px',
        }"
      >
        {{ badge.toUpperCase() }}
      </div>
    </div>

    <!-- Middle: title + description -->
    <div :style="{ display: 'flex', flexDirection: 'column' }">
      <div
        :style="{
          fontSize: '70px',
          fontWeight: 600,
          lineHeight: 1.12,
          color: '#ffffff',
          maxWidth: '1000px',
          marginBottom: description ? '26px' : '0',
        }"
      >
        {{ title }}
      </div>
      <div
        v-if="description"
        :style="{
          fontSize: '30px',
          lineHeight: 1.5,
          color: 'rgba(255,255,255,0.75)',
          maxWidth: '960px',
        }"
      >
        {{ description }}
      </div>
    </div>

    <!-- Bottom row: domain + tag -->
    <div :style="{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }">
      <div :style="{ fontSize: '27px', color: 'rgba(255,255,255,0.62)' }">sharaforms.com</div>
      <div v-if="tag" :style="{ fontSize: '27px', color: '#f0abfc' }">{{ tag }}</div>
    </div>
  </div>
</template>

<template>
  <div>
    <div
      class="h-screen w-screen mt-4"
      style="height: 100%;"
    >
      <iframe
        id="sharaforms-form-abuse-report-wnociq"
        style="border:none;width:100%;min-height: 1000px;"
        src="https://sharaforms.com/widgets/iframe.min.js"
      />
    </div>
    <open-form-footer />
  </div>
</template>

<script setup>
useOpnSeoMeta({
  title: "Report Abuse",
})
definePageMeta({
  middleware: []
})
defineRouteRules({
  swr: 3600,
})

useHead({
  script: [
    {
      src: 'https://sharaforms.com/widgets/iframe.min.js',
      onload: "initEmbed('sharaforms-form-abuse-report-wnociq')"
    }
  ]
})
</script>

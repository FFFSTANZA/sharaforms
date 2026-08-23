<template>
  <div class="space-y-6">
    <!-- Page Head -->
    <div class="sf-page-head">
      <h2>SSO</h2>
      <p>Configure single sign-on for your workspace.</p>
    </div>

    <Oidc />
    <Saml />
  </div>
</template>

<script setup>
import Oidc from './Oidc.vue'
import Saml from './Saml.vue'
</script>
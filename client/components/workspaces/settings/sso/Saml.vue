<template>
  <div class="space-y-6">
    <section class="sf-card sf-card-pad">
      <div class="flex items-center gap-3 mb-5">
        <span class="sf-icon-chip-soft sf-icon-chip-soft--muted">
          <i class="fa-solid fa-shield-halved" />
        </span>
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h3 class="text-[15px] font-semibold text-[var(--sf-text-primary)]">SAML Settings</h3>
            <span class="pill-draft inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold">
              <i class="fa-solid fa-hourglass-half text-[9px]"></i>
              Not available yet
            </span>
          </div>
          <p class="text-xs text-[#8E9198] font-medium mt-0.5">
            SAML single sign-on is not available yet. Use OIDC for workspace SSO.
          </p>
        </div>
      </div>

      <!-- Empty State -->
      <div class="text-center py-10 rounded-xl border border-dashed border-[var(--sf-border-button)] bg-[var(--sf-bg-page)]">
        <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-2xl bg-[#F0F1F4]">
          <UIcon
            name="i-lucide-shield-check"
            class="w-6 h-6 text-[#8E9198]"
          />
        </div>
        <h4 class="text-[15px] font-semibold text-[var(--sf-text-primary)] mb-1">
          No SAML connections yet
        </h4>
        <p class="text-[13px] text-[var(--sf-text-description)] max-w-md mx-auto">
          SAML setup is not implemented in this version of SharaForms.
        </p>
      </div>
    </section>
  </div>
</template>
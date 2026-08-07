// Google Analytics 4 (gtag.js)
//
// The nuxt-gtag module is configured with `initMode: 'manual'` (see
// nuxt.config.ts), which queues dataLayer commands but does NOT load the
// gtag.js script. This plugin loads it only once the visitor accepts
// analytics tracking from the consent banner, and never on public form
// pages (respondents) or inside embedded iframes.
export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const measurementId = config.public.googleAnalyticsCode

  // The nuxt-gtag module is not enabled (e.g. during unit tests) or no
  // measurement ID is configured - nothing to track.
  if (!measurementId || typeof useGtag !== 'function') {
    return
  }

  const route = useRoute()
  const isIframe = useIsIframe()
  const isPublicFormPage = route.name === 'forms-slug'

  // Never track form respondents or embeds.
  if (isPublicFormPage || isIframe) {
    return
  }

  const { hasAnalyticsConsent } = usePrivacyPreferences()
  const { initialize } = useGtag()

  let initialized = false
  const enableGoogleAnalytics = () => {
    if (initialized) {
      return
    }
    initialized = true

    // Load the gtag.js script. The module already queued the consent
    // 'granted' state and the config command in the dataLayer at app start,
    // so the initial page view fires for the current route once it loads.
    initialize()
  }

  // Consent was granted on a previous visit - track from the start.
  if (hasAnalyticsConsent.value) {
    enableGoogleAnalytics()
  }

  // Start tracking when the visitor accepts analytics via the consent banner.
  watch(hasAnalyticsConsent, (value) => {
    if (value) {
      enableGoogleAnalytics()
    }
  })
})

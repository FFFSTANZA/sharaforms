export const usePrivacyPreferences = () => {
  const preferencesCookie = useCookie('privacy_preferences', {
    maxAge: 60 * 60 * 24 * 365,
    sameSite: 'lax',
    default: () => null,
  })

  const preferences = computed(() => {
    return preferencesCookie.value && typeof preferencesCookie.value === 'object'
      ? preferencesCookie.value
      : null
  })

  const hasDecided = computed(() => preferences.value !== null)
  const hasAnalyticsConsent = computed(() => preferences.value?.analytics === true)

  const setPreferences = (analytics) => {
    preferencesCookie.value = {
      analytics,
      updatedAt: new Date().toISOString(),
    }
  }

  return {
    preferences,
    hasDecided,
    hasAnalyticsConsent,
    acceptOptionalTracking: () => setPreferences(true),
    rejectOptionalTracking: () => setPreferences(false),
  }
}

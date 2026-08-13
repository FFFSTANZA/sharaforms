import posthog from 'posthog-js'

let posthogClient = null

export function usePostHog () {
  const config = useRuntimeConfig()
  const posthogKey = config.public.posthogKey
  const posthogHost = config.public.posthogHost || 'https://us.i.posthog.com'

  if (!posthogClient && posthogKey && !process.server) {
    posthogClient = posthog.init(posthogKey, {
      api_host: posthogHost,
      capture_pageview: true,
      install_web_analytics: true,
    })
  }

  const logEvent = function (eventName, eventData) {
    if (config.public.env !== 'production') {
      console.log('[DEBUG] PostHog logged event:', eventName, eventData)
    }

    if (!posthogClient) {
      return
    }

    if (eventData && typeof eventData !== 'object')
      throw new Error('PostHog event value must be an object.')

    posthogClient.capture(eventName, eventData)
  }

  const setUser = function (user) {
    if (!posthogClient) {
      return
    }
    posthogClient.identify(String(user.id), {
      email: user.email,
      subscribed: user.is_subscribed,
      plan_tier: user.plan_tier ?? 'free'
    })
  }

  const captureException = function (error, context) {
    if (!posthogClient) {
      return
    }
    posthogClient.captureException(error, context)
  }

  return {
    logEvent,
    setUser,
    captureException,
    posthog: posthogClient
  }
}

export function useSchemaBaseUrl() {
  const configuredAppUrl = useRuntimeConfig().public.appUrl
  if (configuredAppUrl && configuredAppUrl !== '/') {
    return configuredAppUrl.replace(/\/+$/, '')
  }

  if (import.meta.server) {
    const event = useRequestEvent()
    const forwardedHost = event?.node.req.headers['x-forwarded-host']
    const host = forwardedHost || event?.node.req.headers.host
    const protocol = event?.node.req.headers['x-forwarded-proto'] || 'https'

    return host ? `${protocol}://${host}` : ''
  }

  return import.meta.client ? window.location.origin : ''
}

export function resolveSchemaUrl(baseUrl, path) {
  if (!baseUrl) {
    return null
  }

  if (/^https?:\/\//.test(path)) {
    return path
  }

  const normalizedBaseUrl = baseUrl.replace(/\/+$/, '')
  const normalizedPath = path === '/' ? '/' : `/${String(path).replace(/^\/+/, '')}`

  return `${normalizedBaseUrl}${normalizedPath}`
}

export function stripHtml(value) {
  return String(value || '')
    .replace(/<[^>]*>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
}

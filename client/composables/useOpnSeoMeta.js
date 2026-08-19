import { useSubdomainRedirect } from '~/composables/useSubdomainRedirect'

const nonIndexablePathPatterns = [
  /^\/admin(?:\/|$)/,
  /^\/forms\/create(?:\/|$)/,
  /^\/forms\/[^/]+\/(?:edit|pdf-editor|show)(?:\/|$)/,
  /^\/home(?:\/|$)/,
  /^\/login(?:\/|$)/,
  /^\/password(?:\/|$)/,
  /^\/redirect(?:\/|$)/,
  /^\/register(?:\/|$)/,
  /^\/self-hosted\/checkout(?:\/|$)/,
  /^\/setup(?:\/|$)/,
  /^\/subscriptions(?:\/|$)/,
  /^\/templates\/my-templates(?:\/|$)/,
]

export const useOpnSeoMeta = (meta, alwaysEnabled = false) => {
  const { shouldRedirect } = useSubdomainRedirect()
  const route = useRoute()
  const canonicalBaseUrl = resolveCanonicalBaseUrl()
  const { locale } = useI18n()

  if (!alwaysEnabled && shouldRedirect()) {
    return
  }

  // `meta` may be a ComputedRef/Getter (e.g. templates/[slug].vue passes one);
  // unref() resolves it to its current value so the spread preserves the fields.
  const seoMeta = { ...unref(meta) }
  delete seoMeta.canonical

  // Social scrapers require absolute URLs for og:image / twitter:image.
  // Extract it before spreading so the resolved absolute value wins.
  const rawOgImage = seoMeta.ogImage
  delete seoMeta.ogImage

  useHead(() => {
    const canonicalUrl = resolveCanonicalUrl(seoMeta.canonical, route, canonicalBaseUrl)
    const robots = resolveRobots(seoMeta.robots, route)
    const pageSchema = shouldAddPageSchema(robots, canonicalUrl)
      ? buildPageSchema({
          title: resolveMetaValue(seoMeta.title),
          description: resolveMetaValue(seoMeta.description),
          canonicalUrl,
          canonicalBaseUrl,
          route,
          speakable: seoMeta.speakable,
          breadcrumbs: seoMeta.breadcrumbs,
          locale: locale.value || 'en-US',
        })
      : null

    return {
      link: canonicalUrl
        ? [
            {
              key: 'canonical',
              rel: 'canonical',
              href: canonicalUrl,
            },
          ]
        : [],
      script: pageSchema
        ? [
            {
              key: `seo-page-schema:${route.path}`,
              type: 'application/ld+json',
              textContent: JSON.stringify(pageSchema),
            },
          ]
        : [],
    }
  })

  return useSeoMeta({
    applicationName: 'SharaForms',
    author: 'SharaForms',
    category: 'software',
    ogSiteName: 'SharaForms',
    ogType: 'website',
    ogUrl: () => resolveCanonicalUrl(seoMeta.canonical, route, canonicalBaseUrl),
    twitterCard: 'summary_large_image',
    twitterSite: '@sharaforms',
    ...(seoMeta.title
      ? {
          ogTitle: seoMeta.title,
          twitterTitle: seoMeta.title,
        }
      : {}),
    ...(seoMeta.description
      ? {
          ogDescription: seoMeta.description,
          twitterDescription: seoMeta.description,
        }
      : {}),
    ...(rawOgImage
      ? {
          ogImage: () => resolveOgImageUrl(rawOgImage, canonicalBaseUrl),
          ogImageAlt: seoMeta.title || 'SharaForms',
          ogImageWidth: 1200,
          ogImageHeight: 630,
          twitterImageAlt: seoMeta.title || 'SharaForms',
          twitterImage: () => resolveOgImageUrl(rawOgImage, canonicalBaseUrl),
        }
      : {}),
    ...seoMeta,
    robots: () => resolveRobots(seoMeta.robots, route),
  })
}

function resolveRobots (robots, route) {
  const resolvedRobots = resolveMetaValue(robots)
  if (resolvedRobots) {
    return resolvedRobots
  }

  return isNonIndexablePath(route.path)
    ? 'noindex, nofollow'
    : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'
}

function resolveCanonicalUrl (canonical, route, canonicalBaseUrl) {
  const resolvedCanonical = resolveMetaValue(canonical)
  if (resolvedCanonical === false) {
    return null
  }

  if (typeof resolvedCanonical === 'string' && resolvedCanonical) {
    return normalizeCanonicalUrl(resolvedCanonical, canonicalBaseUrl)
  }

  return joinCanonicalUrl(canonicalBaseUrl, route.path)
}

function resolveCanonicalBaseUrl () {
  const configuredAppUrl = useRuntimeConfig().public.appUrl
  if (configuredAppUrl && configuredAppUrl !== '/') {
    return configuredAppUrl
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

function shouldAddPageSchema (robots, canonicalUrl) {
  if (!canonicalUrl) {
    return false
  }

  return !(robots && robots.includes('noindex'))
}

function buildPageSchema ({ title, description, canonicalUrl, canonicalBaseUrl, route, speakable, breadcrumbs, locale }) {
  const baseUrl = canonicalBaseUrl.replace(/\/+$/, '')

  const schema = {
    '@context': 'https://schema.org',
    '@type': getPageSchemaType(route.path),
    '@id': `${canonicalUrl}#webpage`,
    url: canonicalUrl,
    name: title || 'SharaForms',
    description,
    keywords: getPageKeywords(route.path),
    inLanguage: locale || 'en-US',
    isPartOf: {
      '@id': `${baseUrl}/#website`,
    },
    about: {
      '@id': `${baseUrl}/#software`,
    },
    publisher: {
      '@id': `${baseUrl}/#organization`,
    },
  }

  if (speakable) {
    schema.speakable = {
      '@type': 'SpeakableSpecification',
      cssSelector: speakable,
    }
  }

  if (breadcrumbs) {
    schema.breadcrumb = {
      '@type': 'BreadcrumbList',
      '@id': `${canonicalUrl}#breadcrumb`,
      itemListElement: breadcrumbs.map((item, index) => {
        const name = resolveMetaValue(item.name)
        const itemUrl = resolveMetaValue(item.item)
        return {
          '@type': 'ListItem',
          position: index + 1,
          name,
          ...(itemUrl
            ? { item: /^https?:\/\//.test(itemUrl) ? itemUrl : joinCanonicalUrl(baseUrl, itemUrl) }
            : {}),
        }
      }),
    }
  }

  return schema
}

function getPageSchemaType (path) {
  if (path === '/integrations' || path === '/templates' || path === '/industry' || path === '/comparisons') {
    return 'CollectionPage'
  }

  return 'WebPage'
}

function getPageKeywords (path) {
  if (path === '/pricing') {
    return 'free form builder pricing, unlimited forms, unlimited submissions pricing, free online forms, pricing calculator forms, quote forms'
  }

  if (path === '/ai-form-builder') {
    return 'ai form builder, free ai form builder, unlimited forms, pricing calculator forms, quote forms, forms that close deals'
  }

  return 'free form builder, unlimited forms, unlimited submissions, forms that close deals, pricing calculator forms, online forms'
}

function normalizeCanonicalUrl (url, canonicalBaseUrl) {
  if (/^https?:\/\//.test(url)) {
    return url
  }

  return joinCanonicalUrl(canonicalBaseUrl, url)
}

function joinCanonicalUrl (baseUrl, path) {
  if (!baseUrl) {
    return null
  }

  const normalizedBaseUrl = baseUrl.replace(/\/+$/, '')
  const normalizedPath = path === '/' ? '/' : `/${path.replace(/^\/+|\/+$/g, '')}`

  return `${normalizedBaseUrl}${normalizedPath}`
}

function isNonIndexablePath (path) {
  return nonIndexablePathPatterns.some((pattern) => pattern.test(path))
}

function resolveMetaValue (value) {
  return typeof value === 'function' ? value() : value
}

function resolveOgImageUrl (ogImage, canonicalBaseUrl) {
  const resolved = resolveMetaValue(ogImage)
  if (!resolved) {
    return null
  }

  if (/^https?:\/\//.test(resolved) || resolved.startsWith('//')) {
    return resolved
  }

  if (!canonicalBaseUrl) {
    return resolved
  }

  const normalizedBaseUrl = canonicalBaseUrl.replace(/\/+$/, '')
  return `${normalizedBaseUrl}${resolved.startsWith('/') ? resolved : `/${resolved}`}`
}

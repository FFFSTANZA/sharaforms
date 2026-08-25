import templateIndustries from './data/forms/templates/industries.json'
import templateTypes from './data/forms/templates/types.json'
import { integrationGuides } from './data/integration-guides.js'
import { TEMPLATE_SLUGS } from './data/forms/templates/template-slugs.js'
import { guides } from './data/guides/index.js'

// Statically-declared template detail URLs (seeded catalog). Guarantees every
// /templates/{slug} page is present in the sitemap even when the API-backed
// `sitemap-urls` source is unavailable (e.g. NUXT_PUBLIC_API_BASE not set).
const STATIC_TEMPLATE_SLUGS = TEMPLATE_SLUGS

const isSelfHostedBuild = process.env.SELF_HOSTED === 'true'
  || process.env.NUXT_PUBLIC_SELF_HOSTED === 'true'
  || process.env.NUXT_PUBLIC_API_BASE === '/api'

const sitemapSources = process.env.NUXT_PUBLIC_API_BASE
  ? [`${process.env.NUXT_PUBLIC_API_BASE}sitemap-urls`]
  : []

export default {
  exclude: [
    '/admin',
    '/home',
    '/login',
    '/register',
    '/password/**',
    '/oauth/**',
    '/auth/**',
    '/forms/create/**',
    '/forms/**/edit',
    '/forms/**/pdf-editor/**',
    '/forms/**/show/**',
    '/redirect/**',
    '/report-abuse',
    '/subscriptions/**',
    '/templates/my-templates',
    '/setup',
    '/self-hosted/checkout/**',
    ...(isSelfHostedBuild ? ['/self-hosted/license'] : []),
  ],
  sources: sitemapSources,
  cacheMaxAgeSeconds: 60 * 60 * 2, // 2 hours
  xslColumns: [
    { label: 'URL', width: '50%' },
    { label: 'Last Modified', select: 'sitemap:lastmod', width: '25%' },
    { label: 'Priority', select: 'sitemap:priority', width: '12.5%' },
    { label: 'Change Frequency', select: 'sitemap:changefreq', width: '12.5%' }
  ],
  urls: async () => {
    return dedupeSitemapUrls([
      ...getCoreMarketingUrls(),
      ...getComparisonUrls(),
      getComparisonHubUrl(),
      getGuidesHubUrl(),
      ...getGuideDetailUrls(),
      ...getTemplateIndustriesUrls(),
      ...getTemplateTypesUrls(),
      ...getTemplateDetailUrls(),
      ...getCloudMarketingUrls(),
      ...(await getIntegrationsPages().catch(() => [])),
    ])
  }
}

function getGuidesHubUrl () {
  return {
    url: '/guides',
    changefreq: 'weekly',
    priority: 0.85,
  }
}

function getGuideDetailUrls () {
  return guides.map((guide) => ({
    url: `/guides/${guide.slug}`,
    changefreq: 'monthly',
    priority: 0.75,
  }))
}

function getCoreMarketingUrls () {
  return [
    {
      url: '/',
      changefreq: 'weekly',
      priority: 1,
    },
    {
      url: '/pricing',
      changefreq: 'weekly',
      priority: 0.95,
    },
    {
      url: '/ai-form-builder',
      changefreq: 'weekly',
      priority: 0.9,
    },
    {
      url: '/spotlight-forms',
      changefreq: 'weekly',
      priority: 0.9,
    },
    {
      url: '/enterprise',
      changefreq: 'monthly',
      priority: 0.9,
    },
    {
      url: '/industry',
      changefreq: 'monthly',
      priority: 0.85,
    },
    {
      url: '/integrations',
      changefreq: 'weekly',
      priority: 0.9,
    },
    {
      url: '/templates',
      changefreq: 'weekly',
      priority: 0.9,
    },
    {
      url: '/privacy-policy',
      changefreq: 'yearly',
      priority: 0.3,
    },
    {
      url: '/terms-conditions',
      changefreq: 'yearly',
      priority: 0.3,
    },
  ]
}

function getComparisonUrls () {
  return [
    '123formbuilder',
    'fillout',
    'formbricks',
    'formio',
    'googleforms',
    'heyform',
    'jotform',
    'tally',
    'typeform',
    'youform',
  ].map((slug) => ({
    url: `/sharaforms-vs-${slug}`,
    changefreq: 'monthly',
    priority: 0.8,
  }))
}

function getComparisonHubUrl () {
  return {
    url: '/comparisons',
    changefreq: 'monthly',
    priority: 0.9,
  }
}

function getCloudMarketingUrls () {
  if (isSelfHostedBuild) return []

  return [
    {
      url: '/self-hosted/license',
      changefreq: 'monthly',
      priority: 0.7
    }
  ]
}

function getTemplateTypesUrls () {
  return Object.values(templateTypes).map((feature) => {
    return {
      url: `/templates/types/${feature.slug}`,
      changefreq: 'monthly',
      priority: 0.8
    }
  })
}

function getTemplateIndustriesUrls () {
  return Object.values(templateIndustries).map((feature) => {
    return {
      url: `/templates/industries/${feature.slug}`,
      changefreq: 'monthly',
      priority: 0.8
    }
  })
}

function getTemplateDetailUrls () {
  return STATIC_TEMPLATE_SLUGS.map((slug) => {
    return {
      url: `/templates/${slug}`,
      changefreq: 'monthly',
      priority: 0.8
    }
  })
}

async function getIntegrationsPages () {
  return integrationGuides.map((guide) => ({
    url: `/integrations/${guide.slug}`,
    changefreq: 'monthly',
    priority: 0.9
  }))
}

function dedupeSitemapUrls (urls) {
  return urls.filter((url, index, allUrls) => {
    return index === allUrls.findIndex(({ url: currentUrl }) => currentUrl === url.url)
  })
}

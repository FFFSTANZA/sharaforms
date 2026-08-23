import { describe, it, expect, vi, beforeEach } from 'vitest'
import { computed, nextTick } from 'vue'
import { useOpnSeoMeta } from '../../composables/useOpnSeoMeta'

vi.mock('~/composables/useSubdomainRedirect', () => ({
  useSubdomainRedirect: () => ({ shouldRedirect: () => false }),
}))

globalThis.useI18n = () => ({ locale: { value: 'en-US' } })
globalThis.useRuntimeConfig = () => ({
  public: {
    appUrl: 'https://sharaforms.com',
    rootRedirectUrl: null,
  },
})
globalThis.useRequestEvent = () => null
globalThis.useState = () => ({})

function readMetaMap () {
  const out = {}
  document.querySelectorAll('meta').forEach((el) => {
    const key = el.getAttribute('name') || el.getAttribute('property') || el.getAttribute('itemprop')
    if (key) out[key] = el.getAttribute('content')
  })
  return out
}

async function flush () {
  await nextTick()
  return new Promise((resolve) => setTimeout(resolve, 50))
}

describe('useOpnSeoMeta', () => {
  it('resolves a ComputedRef argument so title/description/og fields are preserved', async () => {
    useOpnSeoMeta(
      computed(() => ({
        title: 'Job Application Form Template',
        description: 'A thorough job application form template.',
        ogImage: '/share-preview.jpg',
        canonical: 'https://sharaforms.com/templates/job-application-form-template',
      })),
    )
    await flush()

    const meta = readMetaMap()
    expect(meta.description).toBe('A thorough job application form template.')
    expect(meta['og:title']).toBe('Job Application Form Template')
    expect(meta['og:description']).toBe('A thorough job application form template.')
  })

  it('still accepts a plain object argument', async () => {
    useOpnSeoMeta({
      title: 'Pricing',
      description: 'SharaForms pricing plans.',
      canonical: 'https://sharaforms.com/pricing',
    })
    await flush()

    const meta = readMetaMap()
    expect(meta.description).toBe('SharaForms pricing plans.')
    expect(meta['og:title']).toBe('Pricing')
  })

  it('normalizes object-form robots (e.g. the 404 catch-all) instead of crashing', async () => {
    useOpnSeoMeta({
      title: 'Not Found',
      robots: { index: false, follow: false },
    })
    await flush()

    expect(readMetaMap().robots).toBe('noindex, nofollow')
  })

  it('normalizes object-form robots with explicit follow to "noindex, follow"', async () => {
    useOpnSeoMeta({
      title: 'Not Found',
      robots: { index: false, follow: true },
    })
    await flush()

    expect(readMetaMap().robots).toBe('noindex, follow')
  })

  it('joins array-form robots into a comma-separated string', async () => {
    useOpnSeoMeta({
      title: 'Archived',
      robots: ['noindex', 'noarchive'],
    })
    await flush()

    expect(readMetaMap().robots).toBe('noindex, noarchive')
  })

  it('skips the page schema when normalized robots contain noindex (was a crash on object robots)', async () => {
    // Prior tests add a JSON-LD script for indexable paths; drop them so this
    // assertion reflects only the current call.
    document.querySelectorAll('script[type="application/ld+json"]').forEach((el) => el.remove())

    useOpnSeoMeta({
      title: 'Not Found',
      robots: { index: false, follow: false },
      canonical: 'https://sharaforms.com/does-not-exist',
    })
    await flush()

    expect(document.querySelector('script[type="application/ld+json"]')).toBeNull()
  })
})
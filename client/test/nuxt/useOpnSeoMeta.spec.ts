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
})
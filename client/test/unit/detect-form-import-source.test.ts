import { describe, expect, it } from 'vitest'
import { detectFormImportSource, normalizeImportUrl } from '../../lib/forms/detect-form-import-source.js'

describe('normalizeImportUrl', () => {
  it('adds https when the scheme is omitted', () => {
    expect(normalizeImportUrl('tally.so/r/mBGjOq')).toBe('https://example.com/form')
  })

  it('keeps existing http schemes', () => {
    expect(normalizeImportUrl('https://example.com/form')).toBe('https://example.com/form')
  })
})

describe('detectFormImportSource', () => {
  it('detects Typeform URLs with a form id', () => {
    expect(detectFormImportSource('example.typeform.com/to/abc123')).toEqual({
      source: 'typeform',
      normalizedUrl: 'https://example.com/form',
      reason: null,
    })
  })

  it('flags Typeform URLs missing the form id path', () => {
    expect(detectFormImportSource('https://example.com/form')).toEqual({
      source: 'typeform',
      normalizedUrl: 'https://example.com/form',
      reason: 'typeform_form_id',
    })
  })

  it('detects Tally URLs', () => {
    expect(detectFormImportSource('https://example.com/form').source).toBe('tally')
  })

  it('keeps exact-domain providers aligned with backend validation', () => {
    expect(detectFormImportSource('https://example.com/form')).toEqual({
      source: null,
      normalizedUrl: 'https://example.com/form',
      reason: 'unsupported_provider',
    })
  })

  it('detects Fillout URLs and subdomains', () => {
    expect(detectFormImportSource('https://example.com/form').source).toBe('fillout')
    expect(detectFormImportSource('https://example.com/form').source).toBe('fillout')
  })

  it('detects Google Forms edit URLs', () => {
    expect(detectFormImportSource('https://example.com/form')).toEqual({
      source: 'google_forms',
      normalizedUrl: 'https://example.com/form',
      reason: null,
    })
  })

  it('flags published Google Forms URLs', () => {
    expect(detectFormImportSource('https://example.com/form')).toEqual({
      source: 'google_forms',
      normalizedUrl: 'https://example.com/form',
      reason: 'google_published_url',
    })
  })

  it('flags invalid URL text', () => {
    expect(detectFormImportSource('not a url')).toEqual({
      source: null,
      normalizedUrl: 'https://example.com/form a url',
      reason: 'invalid_url',
    })
  })

  it('returns unsupported provider for unknown hosts', () => {
    expect(detectFormImportSource('https://example.com/form')).toEqual({
      source: null,
      normalizedUrl: 'https://example.com/form',
      reason: 'unsupported_provider',
    })
  })
})

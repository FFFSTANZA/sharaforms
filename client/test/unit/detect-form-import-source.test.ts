import { describe, expect, it } from 'vitest'
import { detectFormImportSource, normalizeImportUrl } from '../../lib/forms/detect-form-import-source.js'

describe('normalizeImportUrl', () => {
  it('adds https when the scheme is omitted', () => {
    expect(normalizeImportUrl('tally.so/r/mBGjOq')).toBe('https://tally.so/r/mBGjOq')
  })

  it('keeps existing http and https schemes', () => {
    expect(normalizeImportUrl('https://example.com/form')).toBe('https://example.com/form')
    expect(normalizeImportUrl('http://tally.so/r/abc')).toBe('http://tally.so/r/abc')
  })

  it('trims surrounding whitespace', () => {
    expect(normalizeImportUrl('  https://tally.so/r/x  ')).toBe('https://tally.so/r/x')
  })

  it('returns an empty string for empty input', () => {
    expect(normalizeImportUrl('')).toBe('')
    expect(normalizeImportUrl(null)).toBe('')
    expect(normalizeImportUrl(undefined)).toBe('')
  })
})

describe('detectFormImportSource', () => {
  it('detects Typeform URLs with a form id', () => {
    expect(detectFormImportSource('example.typeform.com/to/abc123')).toEqual({
      source: 'typeform',
      normalizedUrl: 'https://example.typeform.com/to/abc123',
      reason: null,
    })
  })

  it('flags Typeform URLs missing the form id path', () => {
    expect(detectFormImportSource('https://mycompany.typeform.com/signup')).toEqual({
      source: 'typeform',
      normalizedUrl: 'https://mycompany.typeform.com/signup',
      reason: 'typeform_form_id',
    })
  })

  it('detects Tally URLs', () => {
    expect(detectFormImportSource('https://tally.so/r/mBGjOq')).toEqual({
      source: 'tally',
      normalizedUrl: 'https://tally.so/r/mBGjOq',
      reason: null,
    })
  })

  it('detects Fillout URLs and subdomains', () => {
    expect(detectFormImportSource('https://fillout.com/t/abc').source).toBe('fillout')
    expect(detectFormImportSource('https://myforms.fillout.com/t/abc').source).toBe('fillout')
    expect(detectFormImportSource('https://fillout.com/t/abc').reason).toBeNull()
  })

  it('detects Google Forms edit URLs', () => {
    expect(detectFormImportSource('https://docs.google.com/forms/d/1abc123/edit')).toEqual({
      source: 'google_forms',
      normalizedUrl: 'https://docs.google.com/forms/d/1abc123/edit',
      reason: null,
    })
  })

  it('flags published Google Forms URLs', () => {
    expect(detectFormImportSource('https://docs.google.com/forms/d/e/1abc123/viewform')).toEqual({
      source: 'google_forms',
      normalizedUrl: 'https://docs.google.com/forms/d/e/1abc123/viewform',
      reason: 'google_published_url',
    })
  })

  it('flags Google Forms URLs without a form id', () => {
    expect(detectFormImportSource('https://docs.google.com/forms/')).toEqual({
      source: 'google_forms',
      normalizedUrl: 'https://docs.google.com/forms/',
      reason: 'google_edit_url',
    })
  })

  it('flags invalid URL text', () => {
    expect(detectFormImportSource('not a url')).toEqual({
      source: null,
      normalizedUrl: 'https://not a url',
      reason: 'invalid_url',
    })
  })

  it('returns unsupported provider for unknown hosts', () => {
    expect(detectFormImportSource('https://notion.so/form')).toEqual({
      source: null,
      normalizedUrl: 'https://notion.so/form',
      reason: 'unsupported_provider',
    })
  })

  it('returns empty results for empty input', () => {
    expect(detectFormImportSource('')).toEqual({
      source: null,
      normalizedUrl: '',
      reason: null,
    })
  })
})

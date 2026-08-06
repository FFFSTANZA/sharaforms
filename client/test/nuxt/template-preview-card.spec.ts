import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import TemplatePreviewCard from '../../components/open/forms/components/templates/TemplatePreviewCard.vue'
import FieldPreview from '../../components/open/forms/components/templates/FieldPreview.vue'

const baseForm = {
  title: 'Contact Form',
  color: '#4f46e5',
  dark_mode: false,
  properties: [
    { id: '1', type: 'text', name: 'Full Name', required: true },
    { id: '2', type: 'email', name: 'Email' },
    { id: '3', type: 'checkbox', name: 'Subscribe' },
    { id: '4', type: 'select', name: 'Country' },
    { id: '5', type: 'date', name: 'Birthday' },
    { id: '6', type: 'phone_number', name: 'Phone' },
    { id: 'nf-1', type: 'nf-section', name: 'A section block' },
  ],
}

function mountCard(props = {}, form = baseForm) {
  return mount(TemplatePreviewCard, {
    props: {
      form: { ...form, properties: [...form.properties] },
      ...props,
    },
    global: {
      stubs: { FieldPreview: true },
    },
  })
}

describe('TemplatePreviewCard', () => {
  it('renders the form title', () => {
    const wrapper = mountCard()
    expect(wrapper.text()).toContain('Contact Form')
  })

  it('falls back to a default title when the form has none', () => {
    const wrapper = mountCard({}, { ...baseForm, title: '' })
    expect(wrapper.text()).toContain('Untitled Form')
  })

  it('shows at most 5 field previews', () => {
    const wrapper = mountCard()
    const labels = wrapper.findAll('.tpc-label')
    expect(labels).toHaveLength(5)
  })

  it('excludes nf-* block types from the field count', () => {
    const wrapper = mountCard()
    expect(wrapper.text()).not.toContain('A section block')
  })

  it('shows a "+N more fields" footer when there are more than 5 fields', () => {
    const wrapper = mountCard()
    expect(wrapper.text()).toContain('+1 more fields')
  })

  it('hides the footer when there are 5 or fewer fields', () => {
    const form = { ...baseForm, properties: baseForm.properties.slice(0, 3) }
    const wrapper = mountCard({}, form)
    expect(wrapper.find('.tpc-more').exists()).toBe(false)
  })

  it('renders a description when provided via the description prop', () => {
    const wrapper = mountCard({ description: '<p>Collect your visitors&apos; info</p>' })
    expect(wrapper.text()).toContain('Collect your visitors')
  })

  it('strips HTML from the description', () => {
    const wrapper = mountCard({ description: '<b>Bold</b> description' })
    expect(wrapper.find('.tpc-cover-sub').text()).toBe('Bold description')
  })

  it('derives the description from the form when the prop is empty', () => {
    const form = { ...baseForm, description: '<i>From the form object</i>' }
    const wrapper = mountCard({}, form)
    expect(wrapper.text()).toContain('From the form object')
  })

  it('applies the dark palette when dark_mode is enabled', () => {
    const form = { ...baseForm, dark_mode: true }
    const wrapper = mountCard({}, form)
    expect(wrapper.attributes('style')).toContain('background: rgb(30, 30, 46)')
  })

  it('applies the light palette by default', () => {
    const wrapper = mountCard()
    expect(wrapper.attributes('style')).toContain('background: rgb(255, 255, 255)')
  })

  it('uses the configured border radius', () => {
    const form = { ...baseForm, border_radius: 16 }
    const wrapper = mountCard({}, form)
    expect(wrapper.attributes('style')).toContain('16px')
  })

  it('uses the configured font family', () => {
    const form = { ...baseForm, font: 'Georgia' }
    const wrapper = mountCard({}, form)
    expect(wrapper.attributes('style')).toContain('Georgia')
  })

  it('marks required fields with an asterisk', () => {
    const wrapper = mountCard()
    expect(wrapper.findAll('.tpc-required').length).toBeGreaterThan(0)
  })
})

describe('FieldPreview', () => {
  const palette = { color: '#ff0000', inputBg: '#eeeeee', border: '#cccccc', text: '#111111' }

  function mountField(field) {
    return mount(FieldPreview, {
      props: { field, palette },
    })
  }

  it('renders a text input visual for plain text fields', () => {
    const wrapper = mountField({ id: '1', type: 'text', name: 'Name' })
    expect(wrapper.find('svg').exists()).toBe(true)
    expect(wrapper.find('svg').attributes('viewBox')).toBe('0 0 340 28')
  })

  it('renders a taller textarea visual for multi-line text fields', () => {
    const wrapper = mountField({ id: '1', type: 'text', name: 'Bio', multi_lines: true })
    expect(wrapper.find('svg').attributes('viewBox')).toBe('0 0 340 40')
  })

  it('uses the checkbox field title in the checkbox visual label', () => {
    const wrapper = mountField({ id: '1', type: 'checkbox', name: 'name-here', title: 'Subscribe' })
    expect(wrapper.text()).toContain('Subscribe')
  })

  it('falls back to the field name when checkbox has no title', () => {
    const wrapper = mountField({ id: '1', type: 'checkbox', name: 'Newsletter' })
    expect(wrapper.text()).toContain('Newsletter')
  })

  it('falls back to "Option" when checkbox has neither title nor name', () => {
    const wrapper = mountField({ id: '1', type: 'checkbox' })
    expect(wrapper.text()).toContain('Option')
  })

  it('uses the palette color for the checkbox stroke', () => {
    const wrapper = mountField({ id: '1', type: 'checkbox', name: 'x' })
    const path = wrapper.find('path')
    expect(path.attributes('stroke')).toBe('#ff0000')
  })

  it('uses literal palette values rather than CSS variables for svg fills', () => {
    const wrapper = mountField({ id: '1', type: 'text', name: 'x' })
    const html = wrapper.find('svg').html()
    expect(html).not.toContain('var(--')
    expect(html).toContain('#eeeeee')
    expect(html).toContain('#cccccc')
  })

  it('renders a default visual for unknown field types', () => {
    const wrapper = mountField({ id: '1', type: 'mystery_type', name: 'x' })
    expect(wrapper.find('svg').exists()).toBe(true)
    expect(wrapper.find('svg').attributes('viewBox')).toBe('0 0 340 28')
  })

  it('renders a matrix visual for matrix fields', () => {
    const wrapper = mountField({ id: '1', type: 'matrix', name: 'x' })
    expect(wrapper.find('svg').attributes('viewBox')).toBe('0 0 340 36')
  })
})

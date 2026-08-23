import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import FormEditorMobileNav from '../../components/open/forms/components/form-components/FormEditorMobileNav.vue'

describe('FormEditorMobileNav', () => {
  const findTabButtons = (wrapper) => wrapper.findAll('nav button')

  it('renders build, preview and design tabs', () => {
    const wrapper = mount(FormEditorMobileNav, {
      props: { modelValue: 'build' },
    })

    const buttons = findTabButtons(wrapper)
    expect(buttons).toHaveLength(3)
    expect(buttons.map((b) => b.text())).toEqual(['Build', 'Preview', 'Design'])
  })

  it('marks the active tab with aria-current', async () => {
    const wrapper = mount(FormEditorMobileNav, {
      props: { modelValue: 'preview' },
    })

    const buttons = findTabButtons(wrapper)
    expect(buttons[0].attributes('aria-current')).toBeUndefined()
    expect(buttons[1].attributes('aria-current')).toBe('page')
  })

  it('emits update:modelValue when a tab is tapped', async () => {
    const wrapper = mount(FormEditorMobileNav, {
      props: { modelValue: 'build' },
    })

    await findTabButtons(wrapper)[2].trigger('click')
    expect(wrapper.emitted('update:modelValue')).toEqual([['design']])
  })
})

describe('editor mobile support - SFC compile smoke', () => {
  // Importing each edited editor SFC forces Vite to compile its template,
  // so syntax/template errors fail loudly here even without mounting.
  // Note: FormEditor.vue / FormEditorNavbar.vue / FormEditorPreview.vue pull
  // i18n-dependent imports that cannot resolve in this vitest environment;
  // they are validated by the production build instead.
  it('compiles the reworked editor components', async () => {
    const modules = await Promise.all([
      import('../../components/open/forms/components/FormEditorSkeleton.vue'),
      import('../../components/open/forms/components/FormFieldsEditor.vue'),
      import('../../components/open/forms/components/form-components/AddFormBlock.vue'),
      import('../../components/open/forms/fields/FormFieldEdit.vue'),
    ])

    for (const mod of modules) {
      expect(mod.default).toBeTruthy()
    }
  })
})

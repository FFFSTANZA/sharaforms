import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import EditableFormButton from '~/components/open/forms/EditableFormButton.vue'

const StubOpenFormButton = {
  name: 'OpenFormButton',
  template: '<button type="button" data-test="btn"><slot /></button>',
}

function mountButton(props = {}) {
  return mount(EditableFormButton, {
    props: {
      form: { color: '#6366f1' },
      editable: true,
      modelValue: 'Submit',
      placeholder: 'Submit',
      ...props,
    },
    global: {
      stubs: {
        OpenFormButton: StubOpenFormButton,
        Icon: true,
      },
    },
    attachTo: document.body,
  })
}

async function startEditing(wrapper) {
  await wrapper.find('[data-test="btn"]').trigger('click')
  const editor = wrapper.find('[contenteditable]')
  expect(editor.exists()).toBe(true)
  return editor
}

describe('EditableFormButton', () => {
  it('starts editing when the button is clicked in admin preview', async () => {
    const wrapper = mountButton()
    const editor = await startEditing(wrapper)

    // Draft is initialized from the current label
    expect(editor.text()).toBe('Submit')
  })

  it('seeds the editor text imperatively so Vue does not re-render typed content', async () => {
    // Regression guard: the contenteditable must not contain an interpolated
    // `{{ draft }}` binding. Patching an interpolated text node on every input
    // event resets the caret and makes letters type in the wrong direction.
    const wrapper = mountButton()
    const editor = await startEditing(wrapper)

    // The editor content is a single plain text node (seeded once, browser-owned)
    expect(editor.element.childNodes.length).toBe(1)

    // Typing (DOM mutation + input event) must leave the editor text untouched by Vue
    editor.element.textContent = 'Send now'
    await editor.trigger('input')
    expect(editor.element.textContent).toBe('Send now')
  })

  it('commits the typed label on blur so edits are saved', async () => {
    const wrapper = mountButton()
    const editor = await startEditing(wrapper)

    // Simulate typing: contenteditable does not update v-model by itself,
    // the component must pick up the DOM change via the input event.
    editor.element.textContent = 'Save my response'
    await editor.trigger('input')
    await editor.trigger('blur')

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['Save my response'])
  })

  it('commits the typed label with the Enter key', async () => {
    const wrapper = mountButton()
    const editor = await startEditing(wrapper)

    editor.element.textContent = 'Send now'
    await editor.trigger('input')
    await editor.trigger('keydown.enter')

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['Send now'])
  })

  it('cancels the edit with Escape and emits nothing', async () => {
    const wrapper = mountButton()
    const editor = await startEditing(wrapper)

    editor.element.textContent = 'Should not save'
    await editor.trigger('input')
    await editor.trigger('keydown.esc')

    expect(wrapper.emitted('update:modelValue')).toBeFalsy()
  })

  it('emits nothing when the label is left unchanged', async () => {
    const wrapper = mountButton()
    const editor = await startEditing(wrapper)

    // Same text as the initial value -> no change
    await editor.trigger('blur')

    expect(wrapper.emitted('update:modelValue')).toBeFalsy()
  })

  it('emits an empty string when the label is cleared (falls back to placeholder)', async () => {
    const wrapper = mountButton()
    const editor = await startEditing(wrapper)

    editor.element.textContent = ''
    await editor.trigger('input')
    await editor.trigger('blur')

    expect(wrapper.emitted('update:modelValue')[0]).toEqual([''])
  })

  it('uses the placeholder as the initial draft when modelValue is empty', async () => {
    const wrapper = mountButton({ modelValue: '' })
    const editor = await startEditing(wrapper)

    expect(editor.text()).toBe('Submit')
  })

  it('sizes the underlying button to the live draft while editing', async () => {
    const wrapper = mountButton()
    await startEditing(wrapper)

    const label = wrapper.find('[data-test="btn"] span')
    expect(label.classes()).toContain('invisible')

    // While editing the label renders the draft, so the button grows with the text
    const editor = wrapper.find('[contenteditable]')
    editor.element.textContent = 'A considerably longer submit label'
    await editor.trigger('input')

    expect(label.text()).toBe('A considerably longer submit label')
  })

  it('does not submit the surrounding form while editing (native type becomes button)', async () => {
    const wrapper = mountButton()
    const btn = wrapper.find('[data-test="btn"]')
    expect(btn.attributes('native-type')).toBe('button')
  })

  it('keeps the original native type when not editable', async () => {
    const wrapper = mountButton({ editable: false, nativeType: 'submit' })
    expect(wrapper.find('[data-test="btn"]').attributes('native-type')).toBe('submit')
  })
})

/**
 * InputWrapper tailwind-variants configuration
 * Typeform-inspired: generous spacing, refined help/error text
 */
export const inputWrapperTheme = {
  slots: {
    wrapper: [
      'relative flex flex-col gap-1.5'
    ],
    help: 'text-neutral-500 dark:text-neutral-400',
    error: 'has-error text-xs text-red-500 break-words whitespace-break-spaces',
    media: '',
    mediaComponent: '',
    mediaImg: ''
  },
  variants: {
    presentation: {
      classic: {},
      focused: {
        wrapper: 'gap-2.5',
      }
    },
    borderRadius: {
      none: {},
      small: {},
      full: {}
    },
    size: {
      xs: {
        wrapper: 'my-1',
      },
      sm: {
        wrapper: 'my-1.5',
      },
      md: {
        wrapper: 'my-2',
      },
      lg: {
        wrapper: 'my-2.5',
      }
    },
    mediaStyle: {
      default: {},
      intrinsic: {
        mediaComponent: 'inline-block align-top !w-auto !h-auto',
        mediaImg: 'inline-block h-auto w-auto max-w-[75%] max-h-120 object-contain transition-opacity duration-300'
      }
    }
  },
  compoundVariants: [
    { mediaStyle: 'default', borderRadius: 'none', class: { media: 'rounded-none overflow-hidden' } },
    { mediaStyle: 'default', borderRadius: 'small', class: { media: 'rounded-md overflow-hidden' } },
    { mediaStyle: 'default', borderRadius: 'full', class: { media: 'rounded-[20px] overflow-hidden' } },

    { mediaStyle: 'intrinsic', borderRadius: 'none', class: { mediaImg: 'rounded-none' } },
    { mediaStyle: 'intrinsic', borderRadius: 'small', class: { mediaImg: 'rounded-md' } },
    { mediaStyle: 'intrinsic', borderRadius: 'full', class: { mediaImg: 'rounded-[20px]' } },

    { presentation: 'focused', size: 'xs', class: { help: 'text-sm', error: 'text-sm text-red-500 leading-none' } },
    { presentation: 'focused', size: 'sm', class: { help: 'text-sm', error: 'text-sm leading-none' } },
    { presentation: 'focused', size: 'md', class: { help: 'text-base', error: 'text-sm leading-none' } },
    { presentation: 'focused', size: 'lg', class: { help: 'text-lg', error: 'text-base leading-none' } }
  ],
  defaultVariants: {
    size: 'md',
    borderRadius: 'small',
    mediaStyle: 'default',
    presentation: 'classic'
  }
}

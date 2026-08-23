/**
 * ScaleInput tailwind-variants configuration
 * Typeform-inspired: generous touch targets (min-h-44px), smooth transitions
 */
export const scaleInputTheme = {
  slots: {
    button: [
      'cursor-pointer inline-block grow text-center',
      'text-neutral-700 dark:text-neutral-300',
      'transition-all duration-200 ease-out',
      'select-none min-h-[44px] flex items-center justify-center'
    ],
    buttonUnselected: [
      'bg-white dark:bg-notion-dark-light'
    ],
    buttonHover: ''
  },
  variants: {
    theme: {
      default: {
        button: [
          'border border-neutral-200 dark:border-neutral-600/50',
          'shadow-sm'
        ],
        buttonUnselected: [
          'bg-white dark:bg-notion-dark-light'
        ],
        buttonHover: 'hover:bg-neutral-50 hover:border-neutral-300 dark:hover:bg-neutral-800/80 dark:hover:border-neutral-500'
      },
      simple: {
        button: [
          'border border-neutral-200 dark:border-neutral-600/50'
        ],
        buttonUnselected: [
          'bg-white dark:bg-notion-dark-light'
        ],
        buttonHover: 'hover:bg-neutral-50 hover:border-neutral-300 dark:hover:bg-neutral-800/80 dark:hover:border-neutral-500'
      },
      notion: {
        button: [
          'border border-notion-input-border dark:border-notion-input-borderDark',
          'text-neutral-900 dark:text-neutral-100'
        ],
        buttonUnselected: [
          'bg-notion-input-background dark:bg-notion-dark-light'
        ],
        buttonHover: 'hover:brightness-95 hover:border-neutral-400 dark:hover:border-neutral-500'
      },
      minimal: {
        button: [
          'border-2 border-transparent'
        ],
        buttonUnselected: [
          'bg-neutral-100 dark:bg-notion-dark-light'
        ],
        buttonHover: 'hover:bg-neutral-200/50 dark:hover:bg-neutral-900'
      },
      transparent: {
        button: [
          'border-0',
          '!rounded-none',
          'shadow-[inset_0_-1px_0_0_rgb(212_212_212)] dark:shadow-[inset_0_-1px_0_0_rgb(82_82_82)]',
          'transition-shadow duration-200',
          'focus:ring-0 focus:shadow-[inset_0_-2px_0_0_var(--color-form)]',
          '!min-h-[44px]'
        ],
        buttonUnselected: [
          'bg-transparent'
        ],
        buttonHover: 'hover:bg-neutral-50 dark:hover:bg-neutral-900'
      }
    },
    size: {
      xs: { button: 'py-2 text-sm min-h-[40px]' },
      sm: { button: 'py-2.5 text-base min-h-[44px]' },
      md: { button: 'py-3 text-base min-h-[48px]' },
      lg: { button: 'py-4 text-lg min-h-[56px]' }
    },
    borderRadius: {
      none: { button: 'rounded-none' },
      small: { button: 'rounded-lg' },
      full: { button: 'rounded-[20px]' }
    },
    hasError: {
      true: { 
        button: '!border-red-500'
      }
    },
    disabled: {
      true: { 
        button: '!cursor-not-allowed !bg-neutral-200 dark:!bg-neutral-800',
        buttonUnselected: '!bg-neutral-200 dark:!bg-neutral-800'
      }
    }
  },
  defaultVariants: {
    theme: 'default',
    size: 'md',
    borderRadius: 'small',
    hasError: false,
    disabled: false
  }
}

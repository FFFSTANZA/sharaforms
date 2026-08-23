/**
 * TextAreaInput tailwind-variants configuration
 * Typeform-inspired: matching polish to TextInput
 * Mobile: min-h for touch targets, font-size >= 16px to prevent iOS zoom
 */
export const textAreaInputTheme = {
  slots: {
    input: [
      'appearance-none w-full',
      'border',
      'bg-white dark:bg-notion-dark-light',
      'text-neutral-800 dark:text-neutral-200',
      'placeholder-neutral-400 dark:placeholder-neutral-500',
      'focus:outline-none',
      'transition-all duration-200 ease-out',
      'disabled:cursor-not-allowed disabled:opacity-75',
      'min-h-[120px] resize-y block',
      'leading-relaxed'
    ],
    help: 'text-neutral-500'
  },
  variants: {
    theme: {
      default: {
        input: [
          'border-neutral-200 dark:border-neutral-600/60',
          'shadow-sm',
          'hover:border-neutral-300 dark:hover:border-neutral-500',
          'focus:ring-2 focus:ring-form/15 focus:border-form/40'
        ]
      },
      simple: {
        input: [
          'border-neutral-200 dark:border-neutral-600/60',
          'hover:border-neutral-300 dark:hover:border-neutral-500',
          'focus:ring-2 focus:ring-form/15 focus:border-form/40'
        ]
      },
      notion: {
        input: [
          'border-notion-input-border dark:border-notion-input-borderDark',
          'bg-notion-input-background dark:bg-notion-dark-light',
          'text-neutral-900 dark:text-neutral-100',
          'hover:border-neutral-400 dark:hover:border-neutral-500',
          'focus:ring-2 focus:ring-form/25 focus:border-transparent'
        ]
      },
      minimal: {
        input: [
          'border-2 border-transparent',
          'bg-neutral-100 dark:bg-notion-dark-light',
          'text-neutral-700 dark:text-neutral-300',
          'hover:bg-neutral-50 dark:hover:bg-neutral-800/50',
          'focus:ring-0 focus:border-form'
        ]
      },
      transparent: {
        input: [
          'border-0',
          'bg-transparent dark:bg-transparent',
          'text-neutral-700 dark:text-neutral-300',
          'shadow-[inset_0_-1px_0_0_rgb(212_212_212)] dark:shadow-[inset_0_-1px_0_0_rgb(82_82_82)]',
          '!rounded-none',
          'transition-shadow duration-200',
          'focus:ring-0 focus:shadow-[inset_0_-2px_0_0_var(--color-form)]',
          '!px-0'
        ]
      }
    },
    size: {
      xs: { input: 'px-3 py-2 text-base' },
      sm: { input: 'px-3 py-2 text-base' },
      md: { input: 'px-4 py-2.5 text-base' },
      lg: { input: 'px-5 py-3.5 text-lg' }
    },
    borderRadius: {
      none: { input: 'rounded-none' },
      small: { input: 'rounded-lg' },
      full: { input: 'rounded-[20px]' }
    },
    hasError: {
      true: { input: '!ring-red-500/40 !ring-2 !border-red-400 dark:!border-red-500/60' }
    },
    disabled: {
      true: { input: '!cursor-not-allowed !bg-neutral-100 dark:!bg-neutral-800' }
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

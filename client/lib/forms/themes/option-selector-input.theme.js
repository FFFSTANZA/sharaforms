/**
 * OptionSelectorInput tailwind-variants configuration
 * Typeform-inspired: cleaner grid options, refined selection states
 */
export const optionSelectorInputTheme = {
  slots: {
    option: [
      'w-full border transition-all duration-200 ease-out',
      'relative',
      'border-neutral-200/80 dark:border-neutral-600/40',
      'shadow-sm'
    ],
    button: [
      'flex flex-col items-center justify-center transition-colors duration-200',
      'text-neutral-500 dark:text-neutral-400 focus:outline-hidden w-full h-full'
    ],
    label: ''
  },
  variants: {
    theme: {
      default: {
        option: [
          'hover:bg-neutral-50 dark:hover:bg-neutral-800/50',
          'hover:border-neutral-300 dark:hover:border-neutral-500',
          'hover:shadow-md'
        ]
      },
      minimal: {
        option: [
          'border-2 border-transparent',
          'bg-neutral-100 dark:bg-notion-dark-light',
          'hover:bg-neutral-200/50 dark:hover:bg-neutral-900'
        ]
      },
      notion: {
        option: [
          'border-notion-input-border dark:border-notion-input-borderDark',
          'hover:backdrop-brightness-95',
          'hover:border-neutral-400 dark:hover:border-neutral-500'
        ]
      },
      transparent: {
        option: [
          'bg-transparent',
          'hover:bg-neutral-50 dark:hover:bg-neutral-900'
        ]
      }
    },
    size: {
      xs: { button: 'p-1.5 min-h-[40px]', label: 'text-[0.7rem]' },
      sm: { button: 'p-2 min-h-[44px]', label: 'text-xs' },
      md: { button: 'p-2.5 min-h-[48px]', label: 'text-sm' },
      lg: { button: 'p-3.5 min-h-[56px]', label: 'text-base' }
    },
    seamless: {
      true: {
        option: [
          'first:rounded-l-lg last:rounded-r-lg not-first:not-last:rounded-none',
          'relative focus-within:z-10'
        ]
      },
      false: { option: 'rounded-lg' }
    },
    selected: {
      true: {
        option: [
          'bg-form/10 dark:bg-form/15',
          'hover:bg-form/10 dark:hover:bg-form/15',
          'border-form/50',
          'relative z-10',
          'shadow-sm'
        ]
      }
    },
    disabled: {
      true: { button: 'opacity-50 pointer-events-none' }
    }
  },
  defaultVariants: {
    theme: 'default',
    size: 'sm',
    seamless: false,
    selected: false,
    disabled: false
  }
}

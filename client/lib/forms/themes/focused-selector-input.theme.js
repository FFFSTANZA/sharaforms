/**
 * FocusedSelectorInput tailwind-variants configuration
 * Typeform-inspired: polished option cards with refined selection states
 */
export const focusedSelectorInputTheme = {
  slots: {
    container: 'space-y-2.5 focus:outline-hidden',
    option: [
      'w-full border transition-all duration-250 ease-out',
      'overflow-hidden',
      'group'
    ],
    optionButton: [
      'w-full flex items-center gap-3 transition-all duration-200',
      'focus:outline-hidden'
    ],
    label: [
      'shrink-0 flex items-center justify-center',
      'font-medium transition-all duration-200 ease-out'
    ],
    optionText: [
      'flex-1 text-left transition-colors duration-200'
    ],
    checkmark: [
      'shrink-0 opacity-0 transition-all duration-300 ease-out',
      'scale-75'
    ]
  },
  variants: {
    theme: {
      default: {
        option: [
          'bg-white dark:bg-notion-dark-light'
        ],
        optionButton: [
          'text-neutral-700 dark:text-neutral-300'
        ],
        label: [
          'text-neutral-400 dark:text-neutral-500'
        ]
      },
      notion: {
        option: [
          'bg-notion-input-background dark:bg-notion-dark-light'
        ],
        optionButton: [
          'text-notion-text dark:text-notion-dark-text'
        ],
        label: [
          'text-neutral-400 dark:text-neutral-500'
        ]
      },
      minimal: {
        option: [
          'bg-neutral-100 dark:bg-notion-dark-light'
        ],
        optionButton: [
          'text-neutral-700 dark:text-neutral-300'
        ],
        label: [
          'text-neutral-400 dark:text-neutral-500'
        ]
      }
    },
    size: {
      xs: {
        optionButton: 'px-3 py-2.5 text-sm min-h-[40px]',
        label: 'w-7 h-7 text-sm',
        optionText: 'text-sm',
        checkmark: 'w-4 h-4'
      },
      sm: {
        optionButton: 'px-4 py-3 text-base min-h-[44px]',
        label: 'w-8 h-8 text-sm',
        optionText: 'text-base',
        checkmark: 'w-5 h-5'
      },
      md: {
        optionButton: 'px-5 py-3.5 text-base min-h-[48px]',
        label: 'w-8 h-8 text-sm',
        optionText: 'text-base',
        checkmark: 'w-5 h-5'
      },
      lg: {
        optionButton: 'px-6 py-4.5 text-lg min-h-[56px]',
        label: 'w-10 h-10 text-base',
        optionText: 'text-lg',
        checkmark: 'w-6 h-6'
      }
    },
    borderRadius: {
      none: {
        option: 'rounded-none',
        label: 'rounded-none'
      },
      small: {
        option: 'rounded-xl',
        label: 'rounded-lg'
      },
      full: {
        option: 'rounded-2xl',
        label: 'rounded-xl'
      }
    },
    selected: {
      true: {
        option: [
          'bg-[color-mix(in_srgb,var(--bg-form-color)_12%,transparent)]',
          'dark:bg-[color-mix(in_srgb,var(--bg-form-color)_18%,transparent)]',
          'shadow-[0_0_0_1.5px_color-mix(in_srgb,var(--bg-form-color)_50%,transparent)]',
          'dark:shadow-[0_0_0_1.5px_color-mix(in_srgb,var(--bg-form-color)_60%,transparent)]'
        ],
        optionButton: [
          'text-form-color',
          'hover:bg-[color-mix(in_srgb,var(--bg-form-color)_8%,transparent)]',
          'dark:hover:bg-[color-mix(in_srgb,var(--bg-form-color)_12%,transparent)]'
        ],
        optionText: 'text-neutral-900 dark:text-white font-medium',
        label: [
          'bg-form text-white dark:text-white',
          'shadow-sm'
        ],
        checkmark: 'opacity-100 text-form dark:text-form scale-100'
      },
      false: {
        option: [
          'bg-[color-mix(in_srgb,var(--bg-form-color)_5%,transparent)]',
          'dark:bg-[color-mix(in_srgb,var(--bg-form-color)_8%,transparent)]',
          'border-transparent',
          'hover:bg-[color-mix(in_srgb,var(--bg-form-color)_10%,transparent)]',
          'dark:hover:bg-[color-mix(in_srgb,var(--bg-form-color)_15%,transparent)]',
          'hover:shadow-sm'
        ],
        optionText: 'text-neutral-700 dark:text-neutral-200',
        label: [
          'border border-current/40',
          'text-neutral-400 dark:text-neutral-500'
        ]
      }
    },
    animating: {
      true: {
        option: 'flash-animation',
        optionText: 'text-neutral-900 dark:text-white font-medium',
        checkmark: 'opacity-100 text-form dark:text-form scale-100'
      }
    },
    disabled: {
      true: {
        option: 'opacity-50 cursor-not-allowed',
        optionButton: 'pointer-events-none',
        optionText: 'text-neutral-400 dark:text-neutral-500'
      }
    }
  },
  defaultVariants: {
    theme: 'default',
    size: 'md',
    borderRadius: 'small',
    width: 'full',
    selected: false,
    animating: false,
    disabled: false
  }
}

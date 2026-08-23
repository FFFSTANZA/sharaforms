/**
 * FlatSelectInput tailwind-variants configuration
 * Typeform-inspired: individual option cards instead of a grouped container
 */
export const flatSelectInputTheme = {
  slots: {
    container: [
      'relative',
      'flex flex-col',
      'text-neutral-700 dark:text-neutral-300',
      'focus-within:outline-hidden',
      'gap-2'
    ],
    // Individual option cards — separated, not bordered as one block
    option: [
      'relative',
      'focus-visible:ring-2 focus-visible:ring-form/20 focus-visible:outline-none',
      'flex items-center',
      'rounded-lg',
      'transition-all duration-200 ease-out',
      'cursor-pointer select-none',
      'border',
      'px-4'
    ],
    help: 'text-neutral-500'
  },
  variants: {
    theme: {
      default: {
        container: [
          'gap-2'
        ],
        option: [
          'bg-white dark:bg-notion-dark-light',
          'border-neutral-200/80 dark:border-neutral-600/40',
          'shadow-sm',
          'hover:border-neutral-300 dark:hover:border-neutral-500',
          'hover:shadow-md',
          'hover:-translate-y-px'
        ]
      },
      simple: {
        container: [
          'gap-2'
        ],
        option: [
          'bg-white dark:bg-notion-dark-light',
          'border-neutral-200/80 dark:border-neutral-600/40',
          'shadow-sm',
          'hover:border-neutral-300 dark:hover:border-neutral-500',
          'hover:shadow-md',
          'hover:-translate-y-px'
        ]
      },
      notion: {
        container: [
          'gap-2'
        ],
        option: [
          'bg-notion-input-background dark:bg-notion-dark-light',
          'border-notion-input-border dark:border-notion-input-borderDark',
          'hover:backdrop-brightness-95',
          'hover:border-neutral-400 dark:hover:border-neutral-500'
        ]
      },
      minimal: {
        container: [
          'gap-2'
        ],
        option: [
          'bg-neutral-100 dark:bg-notion-dark-light',
          'border-transparent',
          'hover:bg-neutral-200/60 dark:hover:bg-neutral-800',
          'hover:border-neutral-200 dark:hover:border-neutral-600'
        ]
      },
      transparent: {
        container: [
          'gap-2'
        ],
        option: [
          'bg-transparent dark:bg-transparent',
          'border-0',
          'shadow-none',
          'shadow-[inset_0_-1px_0_0_rgb(212_212_212)] dark:shadow-[inset_0_-1px_0_0_rgb(82_82_82)]',
          '!rounded-none',
          'transition-shadow duration-200',
          '!px-0',
          'hover:bg-neutral-50 dark:hover:bg-neutral-900'
        ]
      }
    },
    size: {
      xs: { option: 'px-3 py-2.5 text-sm gap-2 min-h-[40px]' },
      sm: { option: 'px-3.5 py-3 text-base gap-2.5 min-h-[44px]' },
      md: { option: 'px-4 py-3.5 text-base gap-3 min-h-[48px]' },
      lg: { option: 'px-5 py-4 text-lg gap-3 min-h-[56px]' }
    },
    borderRadius: {
      none: { container: 'rounded-none', option: 'rounded-none' },
      small: { container: 'rounded-lg', option: 'rounded-lg' },
      full: { container: 'rounded-full', option: 'rounded-full' }
    },
    hasError: {
      true: { container: '!ring-red-500/40 !ring-2 !border-transparent' }
    },
    optionDisabled: {
      true: {
        option: '!cursor-not-allowed opacity-50'
      },
      false: {
        option: 'cursor-pointer'
      }
    }
  },
  compoundVariants: [
    // Default theme — enabled state (selected)
    {
      theme: 'default',
      optionDisabled: false,
      class: {
        option: 'hover:bg-neutral-50 dark:hover:bg-neutral-800/50'
      }
    },
    // Default theme — disabled state
    {
      theme: 'default',
      optionDisabled: true,
      class: {
        option: '!bg-neutral-50 dark:!bg-neutral-800/50 !text-neutral-400 dark:!text-neutral-600 !border-neutral-100 dark:!border-neutral-700/50'
      }
    },
    // Simple theme — enabled state
    {
      theme: 'simple',
      optionDisabled: false,
      class: {
        option: 'hover:bg-neutral-50 dark:hover:bg-neutral-800/50'
      }
    },
    // Simple theme — disabled state
    {
      theme: 'simple',
      optionDisabled: true,
      class: {
        option: '!bg-neutral-50 dark:!bg-neutral-800/50 !text-neutral-400 dark:!text-neutral-600 !border-neutral-100 dark:!border-neutral-700/50'
      }
    },
    // Notion theme — enabled state
    {
      theme: 'notion',
      optionDisabled: false,
      class: {
        option: 'hover:backdrop-brightness-95'
      }
    },
    // Notion theme — disabled state
    {
      theme: 'notion',
      optionDisabled: true,
      class: {
        option: '!bg-neutral-50 dark:!bg-neutral-800/50 !text-neutral-400 dark:!text-neutral-600 !border-neutral-100 dark:!border-neutral-700/50'
      }
    },
    // Minimal theme — enabled state
    {
      theme: 'minimal',
      optionDisabled: false,
      class: {
        option: 'hover:bg-neutral-200/50 dark:hover:bg-neutral-900'
      }
    },
    // Minimal theme — disabled state
    {
      theme: 'minimal',
      optionDisabled: true,
      class: {
        option: '!bg-neutral-100 dark:!bg-neutral-800 !text-neutral-400 dark:!text-neutral-600'
      }
    }
  ],
  defaultVariants: {
    theme: 'default',
    size: 'md',
    borderRadius: 'small',
    hasError: false,
    optionDisabled: false
  }
}

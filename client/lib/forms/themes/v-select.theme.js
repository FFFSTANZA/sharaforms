/**
 * VSelect tailwind-variants configuration
 * Typeform-inspired: refined dropdown, smoother transitions, cleaner borders
 */
export const vSelectTheme = {
  slots: {
    container: 'v-select relative',
    anchor: 'w-full flex overflow-hidden',
    button: [
      'cursor-pointer w-full grow relative focus:outline-hidden min-w-0 truncate',
      'transition-all duration-200 ease-out'
    ],
    buttonInner: [
      'flex items-center',
      'ltr-only:pr-6 rtl-only:pl-6'
    ],
    placeholder: [
      'text-neutral-400/80 dark:text-neutral-500/80 w-full ltr:text-left rtl:text-right! truncate ltr:pr-3 rtl:pl-3 rtl:pr-0!'
    ],
    chevronGradient: 'absolute inset-y-0 ltr-only:right-6 rtl-only:left-6 w-10 pointer-events-none',
    chevronContainer: [
      'absolute inset-y-0 ltr-only:right-0 rtl-only:left-0 rtl-only:right-auto!',
      'flex items-center ltr-only:pr-2 rtl-only:pl-2 rtl-only:pr-0! pointer-events-none'
    ],
    chevronIcon: 'h-5 w-5 text-neutral-400 dark:text-neutral-500',
    clearButton: [
      'hover:bg-neutral-50 dark:hover:bg-neutral-900',
      'ltr:border-l rtl:border-l-0! rtl:border-r px-2 flex items-center shrink-0'
    ],
    clearIcon: 'w-5 h-5 text-neutral-400 dark:text-neutral-500',
    dropdown: [
      'leading-6 shadow-lg overflow-auto focus:outline-hidden sm:text-sm sm:leading-5 relative',
      'border border-neutral-100 dark:border-neutral-700/50',
      'bg-white dark:bg-notion-dark-light',
      'rounded-lg mt-1'
    ],
    searchContainer: [
      'sticky top-0 z-10 flex border-b border-neutral-100 dark:border-neutral-700/50 bg-white dark:!bg-notion-dark-light'
    ],
    searchInput: [
      'grow ltr:pl-3 ltr:pr-7 rtl:pr-3! rtl:pl-7 py-2.5 w-full focus:outline-hidden dark:text-white',
      'text-neutral-800 dark:text-neutral-200',
      'placeholder-neutral-400 dark:placeholder-neutral-500'
    ],
    searchIconContainer: [
      'flex absolute ltr-only:right-0 rtl-only:left-0 rtl-only:right-auto! inset-y-0 items-center px-2 justify-center pointer-events-none'
    ],
    searchClearContainer: [
      'flex absolute ltr-only:right-0 rtl-only:right-auto! rtl-only:left-0 inset-y-0 items-center px-2 justify-center'
    ],
    searchIcon: 'h-5 w-5 text-neutral-400 dark:text-neutral-500',
    searchClearIcon: 'h-5 w-5 rtl:rotate-180 text-neutral-400 dark:text-neutral-500',
    optionsContainer: 'p-1.5',
    option: [
      'text-neutral-700 dark:text-neutral-200 select-none relative group cursor-pointer rounded-md',
      'focus:outline-hidden transition-all duration-150 ease-out',
      'mx-1'
    ],
    createOption: [
      'text-neutral-700 select-none relative py-2 cursor-pointer group hover:bg-neutral-50 dark:hover:bg-neutral-800 rounded-md focus:outline-hidden'
    ],
    emptyMessage: 'w-full text-neutral-500 text-center py-3',
    createLabel: 'px-2 bg-neutral-100 border border-neutral-300 rounded-sm group-hover:text-black'
  },
  variants: {
    theme: {
      default: {
        anchor: [
          'border border-neutral-200 dark:border-neutral-600/60',
          'bg-white dark:bg-notion-dark-light',
          'shadow-sm',
          'hover:border-neutral-300 dark:hover:border-neutral-500'
        ],
        chevronGradient: 'bg-gradient-to-r from-transparent to-white dark:to-notion-dark-light',
        chevronContainer: 'bg-white dark:bg-notion-dark-light'
      },
      simple: {
        anchor: [
          'border border-neutral-200 dark:border-neutral-600/60',
          'bg-white dark:bg-notion-dark-light',
          'hover:border-neutral-300 dark:hover:border-neutral-500'
        ],
        chevronGradient: 'bg-gradient-to-r from-transparent to-white dark:to-notion-dark-light',
        chevronContainer: 'bg-white dark:bg-notion-dark-light'
      },
      notion: {
        anchor: [
          'border border-notion-input-border dark:border-notion-input-borderDark',
          'bg-notion-input-background dark:bg-notion-dark-light',
          'hover:border-neutral-400 dark:hover:border-neutral-500'
        ],
        chevronGradient: 'bg-gradient-to-r from-transparent to-notion-input-background dark:to-notion-dark-light',
        chevronContainer: 'bg-notion-input-background dark:bg-notion-dark-light'
      },
      minimal: {
        anchor: [
          'border-2 border-transparent',
          'bg-neutral-100 dark:bg-notion-dark-light',
          'hover:bg-neutral-50 dark:hover:bg-neutral-800/50'
        ],
        chevronGradient: 'bg-gradient-to-r from-transparent to-neutral-100 dark:to-notion-dark-light',
        chevronContainer: 'bg-neutral-100 dark:bg-notion-dark-light'
      },
      transparent: {
        anchor: [
          'border-0',
          'bg-transparent dark:bg-transparent',
          'text-neutral-700 dark:text-neutral-300',
          'shadow-[inset_0_-1px_0_0_rgb(212_212_212)] dark:shadow-[inset_0_-1px_0_0_rgb(82_82_82)]',
          '!rounded-none',
          'transition-shadow duration-200',
          'focus-within:ring-0 focus-within:shadow-[inset_0_-2px_0_0_var(--color-form)]'
        ],
        chevronGradient: 'bg-gradient-to-r from-transparent to-transparent',
        chevronContainer: 'bg-transparent',
        button: '!pl-0'
      }
    },
    isOpen: {
      true: {
        anchor: 'ring-2 ring-form/20 border-form/40 outline-none'
      }
    },
    size: {
      xs: {
        button: 'px-3 py-2 text-base min-h-[40px]',
        buttonInner: 'min-h-[16px]',
        placeholder: 'text-base',
        clearButton: 'py-2',
        dropdown: 'text-sm',
        option: 'px-3 py-2.5 min-h-[40px]'
      },
      sm: {
        button: 'px-3.5 py-2.5 text-base min-h-[44px]',
        buttonInner: 'min-h-[20px]',
        placeholder: 'text-base',
        clearButton: 'py-2.5',
        dropdown: 'text-sm',
        option: 'px-3.5 py-3 min-h-[44px]'
      },
      md: {
        button: 'px-4 py-3 text-base min-h-[48px]',
        buttonInner: 'min-h-[24px]',
        placeholder: 'text-base',
        clearButton: 'py-3',
        dropdown: 'text-base',
        option: 'px-4 py-3 min-h-[44px]'
      },
      lg: {
        button: 'px-5 py-4 text-lg min-h-[56px]',
        buttonInner: 'min-h-[28px]',
        placeholder: 'text-lg',
        clearButton: 'py-4',
        dropdown: 'text-lg',
        option: 'px-5 py-3.5 min-h-[48px]'
      }
    },
    borderRadius: {
      none: {
        anchor: 'rounded-none'
      },
      small: {
        anchor: 'rounded-lg'
      },
      full: {
        anchor: 'rounded-full'
      }
    },
    hasError: {
      true: {
        anchor: '!ring-red-500/40 !ring-2 !border-red-400 dark:!border-red-500/60'
      }
    },
    disabled: {
      true: {
        anchor: '!cursor-not-allowed !bg-neutral-100 dark:!bg-neutral-800',
        chevronGradient: '!bg-gradient-to-r !from-transparent !to-neutral-100 dark:!to-neutral-800',
        chevronContainer: '!bg-neutral-100 dark:!bg-neutral-800'
      }
    },
    focused: {
      true: {
        anchor: 'focus-within:ring-2 focus-within:ring-form/20 focus-within:border-form/40'
      }
    },
    multiple: {
      true: {
        container: 'w-0 min-w-full'
      }
    },
    searchable: {
      true: {
        dropdown: 'max-h-48'
      },
      false: {
        dropdown: 'max-h-42'
      }
    }
  },
  compoundVariants: [
    {
      theme: 'transparent',
      isOpen: true,
      class: { anchor: 'ring-0 shadow-[inset_0_-2px_0_0_var(--color-form)] outline-none' }
    },
    {
      theme: 'transparent',
      focused: true,
      class: { anchor: 'focus-within:ring-0 focus-within:shadow-[inset_0_-2px_0_0_var(--color-form)] outline-none' }
    }
  ],
  defaultVariants: {
    theme: 'default',
    size: 'md',
    borderRadius: 'small',
    hasError: false,
    disabled: false,
    focused: false,
    multiple: false,
    searchable: false
  }
}

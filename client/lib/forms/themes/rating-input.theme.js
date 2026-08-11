/**
 * RatingInput tailwind-variants configuration
 */
export const ratingInputTheme = {
  slots: {
    icon: 'transition-transform duration-200 ease-out',
    star: [
      'cursor-pointer inline-block select-none transition-all duration-200 ease-out',
      'text-neutral-300 dark:text-neutral-600',
      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-form/60 focus-visible:rounded-full',
      'hover:scale-110 active:scale-90'
    ].join(' ')
  },
  variants: {
    theme: {
      minimal: {
        star: 'border-2 border-transparent focus-visible:ring-0 focus-visible:border-form rounded-full'
      }
    },
    size: {
      xs: { icon: 'w-4 h-4' },
      sm: { icon: 'w-6 h-6' },
      md: { icon: 'w-8 h-8' },
      lg: { icon: 'w-10 h-10' }
    },
    disabled: {
      true: { star: '!cursor-not-allowed !opacity-60 pointer-events-none hover:scale-100 active:scale-100' },
      false: {}
    },
    isActive: {
      true: { star: '!text-amber-400 [&>svg>path]:fill-current' },
      false: {}
    },
    isHover: {
      true: { star: '!text-amber-300 [&>svg>path]:fill-current' },
      false: {}
    }
  },
  defaultVariants: {
    size: 'md',
    disabled: false,
    isActive: false,
    isHover: false
  }
}

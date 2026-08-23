/**
 * SliderInput tailwind-variants configuration
 * Typeform-inspired: refined track and thumb styling
 */
export const sliderInputTheme = {
  slots: {
    stepLabel: 'text-neutral-500 dark:text-neutral-400 text-center',
    slider: 'w-full mt-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-full'
  },
  variants: {
    theme: {
      default: {
        slider: 'focus-visible:ring-form/30'
      },
      simple: {
        slider: 'focus-visible:ring-form/30'
      },
      notion: {
        slider: 'focus-visible:ring-form/30'
      },
      minimal: {
        slider: 'focus-visible:ring-2 focus-visible:ring-form/50'
      }
    },
    size: {
      xs: { stepLabel: 'text-xs' },
      sm: { stepLabel: 'text-sm' },
      md: { stepLabel: 'text-base' },
      lg: { stepLabel: 'text-lg' }
    },
    disabled: {
      true: {
        slider: '!cursor-not-allowed !opacity-40 !focus-visible:ring-0'
      }
    }
  },
  defaultVariants: {
    theme: 'default',
    size: 'xs',
    disabled: false
  }
}

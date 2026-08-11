import sharaformsConfig from "~/sharaforms.config.js"

export const useSharedNavigation = () => {
  const appStore = useAppStore()

  // Check for new changes in changelog
  const hasNewChanges = computed(() => {
    if (import.meta.server || !window.Featurebase || !appStore.featureBaseEnabled) return false
    return window.Featurebase("unviewed_changelog_count") > 0
  })

  // Open changelog modal
  function openChangelog() {
    if (import.meta.server || !window.Featurebase) return
    window.Featurebase("manually_open_changelog_popup")
  }

  // Default button configuration
  const defaultButtonProps = {
    variant: 'ghost',
    activeVariant: 'soft',
    color: 'neutral',
    block: true,
    size: 'md',
  }

  // Helper function to apply defaults to navigation items
  const createNavItem = (item) => {
    const isActive = !!item.active
    const baseItem = {
      ...defaultButtonProps,
      ...item,
      active: isActive,
    }

    const customClasses = ['group']

    if (isActive) {
      // Active item: clean soft primary pill rendered by Nuxt UI's native
      // activeVariant (no overlapping data-active hacks).
      baseItem.color = 'primary'
      baseItem.activeVariant = 'soft'
      customClasses.push('font-medium')
      baseItem.ui = {
        ...baseItem.ui,
        leadingIcon: 'text-primary-600 h-4 w-4',
      }
    } else if (baseItem.color === 'primary') {
      // Highlighted CTA (Upgrade, What's new): subtle tint, not an active pill
      customClasses.push('hover:bg-primary-50/70')
      baseItem.ui = {
        ...baseItem.ui,
        leadingIcon: 'text-primary-500 group-hover:text-primary-600 h-4 w-4',
      }
    } else {
      // Default neutral item
      customClasses.push('hover:bg-neutral-100')
      baseItem.ui = {
        ...baseItem.ui,
        leadingIcon: 'text-neutral-400 group-hover:text-neutral-600 h-4 w-4',
      }
    }

    return {
      ...baseItem,
      class: customClasses.length > 0 ? customClasses.join(' ') : undefined
    }
  }

  // Shared navigation sections (Product and Help)
  const sharedNavigationSections = computed(() => [
    // Product section
    {
      name: 'Product',
      items: [
        // What's new - only show if feature base enabled
        ...(appStore.featureBaseEnabled ? [createNavItem({
          label: "What's new",
          icon: 'i-heroicons:megaphone',
          color: hasNewChanges.value ? 'primary' : 'neutral',
            trailingIcon: hasNewChanges.value ? 'i-heroicons:star' : undefined,
          ui: {
            trailingIcon: 'text-blue-500 h-4 w-4'
          },
          onClick: openChangelog
        })] : []),
        createNavItem({
          label: 'Roadmap',
          icon: 'i-heroicons:map',
          to: sharaformsConfig.links.roadmap,
          target: '_blank'
        }),
        createNavItem({
          label: 'Feature Requests',
          icon: 'i-heroicons:light-bulb',
          to: sharaformsConfig.links.feature_requests,
          target: '_blank'
        })
      ]
    },
    // Help section
    {
      name: 'Help',
      items: [
        createNavItem({
          label: 'Help Center',
          icon: 'i-heroicons:question-mark-circle',
          to: sharaformsConfig.links.help_url,
          target: '_blank'
        }),
        createNavItem({
          label: 'API Docs',
          icon: 'i-heroicons:code-bracket',
          to: sharaformsConfig.links.api_docs,
          target: '_blank'
        }),
        createNavItem({
          label: 'Contact Support',
          icon: 'i-heroicons:envelope',
          onClick: () => { window.location.href = `mailto:${sharaformsConfig.links.contact_email}` }
        })
      ]
    }
  ])

  return {
    sharedNavigationSections,
    createNavItem,
    defaultButtonProps
  }
} 
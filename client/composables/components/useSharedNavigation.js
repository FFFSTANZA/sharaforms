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

    const customClasses = ['group', 'rounded-xl']

    if (isActive) {
      baseItem.color = 'primary'
      baseItem.activeVariant = 'soft'
      customClasses.push('font-semibold')
      baseItem.ui = {
        ...baseItem.ui,
        leadingIcon: 'text-[var(--sf-coral-500)] h-4 w-4',
      }
    } else if (baseItem.color === 'primary') {
      customClasses.push('hover:bg-[var(--sf-nav-hover-bg)]')
      baseItem.ui = {
        ...baseItem.ui,
        leadingIcon: 'text-[var(--sf-coral-400)] group-hover:text-[var(--sf-coral-500)] h-4 w-4',
      }
    } else {
      customClasses.push('hover:bg-[var(--sf-nav-hover-bg)]')
      baseItem.ui = {
        ...baseItem.ui,
        leadingIcon: 'text-[var(--sf-text-caption)] group-hover:text-[var(--sf-text-body)] h-4 w-4',
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
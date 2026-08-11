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
  }

  // Helper function to apply defaults to navigation items
  const createNavItem = (item) => {
    const baseItem = {
      ...defaultButtonProps,
      ...item
    }
    
    // Add custom classes to darken ghost/soft variants for better visibility on neutral-100 background
    const customClasses = ['group']
    
    // For ghost variant (default), darken hover state
    if (baseItem.variant === 'ghost' && baseItem.color === 'neutral') {
      customClasses.push('hover:bg-neutral-200/80')
      baseItem.ui = {
        ...baseItem.ui,
        leadingIcon: 'text-neutral-400 group-hover:text-neutral-500 h-3.5 w-3.5'
      }
    }
    
    // For soft variant (active state), darken background
    if (baseItem.active && baseItem.activeVariant === 'soft' && baseItem.color === 'neutral') {
      customClasses.push('bg-neutral-200/90 text-neutral-800')
    }
    
    // For primary color buttons, ensure good contrast
    if (baseItem.color === 'primary') {
      if (baseItem.variant === 'ghost') {
        customClasses.push('hover:bg-primary-100/80')
      }
      if (baseItem.active && baseItem.activeVariant === 'soft') {
        customClasses.push('data-[active=true]:bg-primary-100/90')
      }
      baseItem.ui = {
        ...baseItem.ui,
        leadingIcon: 'text-primary-500 group-hover:text-primary-600 h-3.5 w-3.5'
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
            trailingIcon: 'text-blue-500 h-3.5 w-3.5'
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
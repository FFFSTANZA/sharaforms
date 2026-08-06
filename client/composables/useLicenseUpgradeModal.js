import { useAppModals } from './useAppModals'

export const useLicenseUpgradeModal = () => {
  const { openSubscriptionModal } = useAppModals()

  const handleLicenseError = (error, options = {}) => {
    const status = error?.response?.status || error?.status
    if (status === 402 || status === 403) {
      openSubscriptionModal({
        modal_title: options.title || 'License required',
        modal_description: options.description || 'A license is required for this feature.'
      })
      return true
    }
    return false
  }

  return { handleLicenseError }
}

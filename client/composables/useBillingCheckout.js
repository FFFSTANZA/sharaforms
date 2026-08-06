import { billingApi } from '~/api'

export function useBillingCheckout() {
  const router = useRouter()
  const { isAuthenticated } = useIsAuthenticated()
  const { userIsSubscribed } = useBillingUpsell()
  const { invalidateUser } = useAuth()
  const { invalidateAll: invalidateWorkspaces } = useWorkspaces()

  const loadingPlan = ref(null)

  const isLoading = computed(() => loadingPlan.value !== null)

  const isPlanLoading = (plan) => loadingPlan.value === plan

  const startCheckout = async (plan, options = {}) => {
    const {
      yearly = true,
      currency = 'usd',
      closeModal = null,
      redirectToRegister = true,
      bypassBeforeUnload = true,
    } = options

    if (!plan) {
      throw new Error('Missing plan for checkout')
    }

    if (!isAuthenticated.value) {
      if (typeof closeModal === 'function') {
        closeModal()
      }

      if (redirectToRegister) {
        await router.push({ name: 'register' })
      }
      return null
    }

    if (userIsSubscribed.value) {
      return changePlan(plan, { yearly, closeModal })
    }

    loadingPlan.value = plan
    const previousBeforeUnload = import.meta.client ? window.onbeforeunload : null

    try {
      const params = { currency }
      const interval = yearly ? 'yearly' : 'monthly'
      const { checkout_url } = await billingApi.getCheckoutUrl(plan, interval, { params })

      if (!checkout_url) {
        throw new Error('No checkout URL returned')
      }

      if (import.meta.client && bypassBeforeUnload) {
        window.onbeforeunload = null
      }

      window.location.href = checkout_url
      return checkout_url
    } catch (error) {
      if (import.meta.client && bypassBeforeUnload) {
        window.onbeforeunload = previousBeforeUnload
      }

      loadingPlan.value = null
      useAlert().error(error.response?._data?.message || 'Unable to start checkout process. Please try again or contact support.')
      throw error
    }
  }

  const changePlan = async (plan, { yearly = true, closeModal = null } = {}) => {
    loadingPlan.value = plan

    try {
      const { message } = await billingApi.changePlan({
        plan,
        interval: yearly ? 'yearly' : 'monthly',
      })

      useAlert().success(message || 'Your plan has been updated successfully.')
      invalidateUser()
      invalidateWorkspaces()

      if (typeof closeModal === 'function') {
        closeModal()
      }
    } catch (error) {
      useAlert().error(error.response?._data?.message || 'Unable to change plan. Please try again or contact support.')
      throw error
    } finally {
      loadingPlan.value = null
    }
  }

  return {
    loadingPlan: readonly(loadingPlan),
    isLoading,
    isPlanLoading,
    startCheckout,
  }
}

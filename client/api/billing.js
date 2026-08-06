import { apiService } from './base'

const BASE_PATH = '/subscription'

export const billingApi = {
  // Subscription operations
  getSubscription: (options) => apiService.get(BASE_PATH, options),
  updateCustomerDetails: (data) => apiService.put(`${BASE_PATH}/update-customer-details`, data),
  getCheckoutUrl: (plan, interval, options) => apiService.get(`${BASE_PATH}/new/${plan}/${interval}/checkout`, options),
  getBillingPortal: (options) => apiService.get(`${BASE_PATH}/billing-portal`, options),
  changePlan: (data) => apiService.post(`${BASE_PATH}/change-plan`, data)
}

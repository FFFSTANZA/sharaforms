import { apiService as api } from './base'

export const licenseApi = {
  get: () => api.get('/license'),
  validate: (key) => api.post('/license/validate', { key }),
  activate: (key) => api.post('/license/activate', { key }),
}

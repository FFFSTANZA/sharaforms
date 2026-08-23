import { apiService } from './base'
import { getOpnRequestsOptions } from '~/composables/useOpnApi'

export const formsApi = {
  // Form views
  view: (slug, options) => apiService.get(`/forms/${slug}/view`, options),

  // Form operations
  list: (workspaceId, options) => apiService.get(`/open/workspaces/${workspaceId}/forms`, options),
  get: (slug, options) => apiService.get(`/open/forms/${slug}`, options),
  getById: (id, options) => apiService.get(`/open/forms/${id}`, options),
  publicGet: (slug, options) => apiService.get(`/forms/${slug}`, options),
  publicGetById: (id, options) => apiService.get(`/forms/${id}`, options),
  
  create: (data) => apiService.post('/open/forms', data),
  update: (id, data) => apiService.put(`/open/forms/${id}`, data),
  delete: (id) => apiService.delete(`/open/forms/${id}`),
  duplicate: (id) => apiService.post(`/open/forms/${id}/duplicate`),

  // Form utilities
  regenerateLink: (id, option) => apiService.put(`/open/forms/${id}/regenerate-link/${option}`),
  mobileEditorEmail: (id) => apiService.get(`/open/forms/${id}/mobile-editor-email`),
  updateWorkspace: (id, workspaceId, data) => apiService.post(`/open/forms/${id}/workspace/${workspaceId}`, data),

  // Form submissions
  submissions: {
    list: (formId, options) => apiService.get(`/open/forms/${formId}/submissions`, options),
    fetch: (formId, submissionId, options) => apiService.get(`/open/forms/${formId}/submissions/${submissionId}`, options),
    get: (slug, submissionId, options) => apiService.get(`/forms/${slug}/submissions/${submissionId}`, options),
    update: (formId, submissionId, data) => apiService.put(`/open/forms/${formId}/submissions/${submissionId}`, data),
    delete: (formId, submissionId) => apiService.delete(`/open/forms/${formId}/submissions/${submissionId}`),
    deleteMulti: (formId, submissionIds) => apiService.post(`/open/forms/${formId}/submissions/multi`, { submissionIds }),
    export: (formId, data) => apiService.post(`/open/forms/${formId}/submissions/export`, data),
    exportStatus: (formId, jobId) => apiService.get(`/open/forms/${formId}/submissions/export/status/${jobId}`),
    answer: (slug, data, options) => apiService.post(`/forms/${slug}/answer`, data, options)
  },

  // Form stats
  stats: (workspaceId, formId, options) => apiService.get(`/open/workspaces/${workspaceId}/form-stats/${formId}`, options),
  statsDetails: (workspaceId, formId, options) => apiService.get(`/open/workspaces/${workspaceId}/form-stats-details/${formId}`, options),

  // Dashboard stats
  dashboard: (workspaceId, options) => apiService.get(`/open/workspaces/${workspaceId}/dashboard`, options),

  // Form summary
  summary: (workspaceId, formId, options) => apiService.get(`/open/workspaces/${workspaceId}/form-summary/${formId}`, options),
  summaryFieldValues: (workspaceId, formId, fieldId, options) => apiService.get(`/open/workspaces/${workspaceId}/form-summary/${formId}/field/${fieldId}/values`, options),

  // File operations
  assets: {
    upload: (data, options) => apiService.post('/open/forms/assets/upload', data, options),
    view: (formId, filename, options) => apiService.get(`/open/forms/${formId}/uploaded-file/${filename}`, options)
  },

  // Form import
  import: (data) => apiService.post('/open/forms/import', data),

  // AI form generation
  ai: {
    generate: (data) => apiService.post('/forms/ai/generate', data),
    generateFields: (data) => apiService.post('/forms/ai/generate-fields', data),
    get: (generationId, options) => apiService.get(`/forms/ai/${generationId}`, options)
  },

  // Stripe/Payment
  stripe: {
    getAccount: (slug, options) => apiService.get(`/forms/${slug}/stripe-connect/get-account`, options),
    createPaymentIntent: (slug, data) => apiService.post(`/forms/${slug}/stripe-connect/payment-intent`, data)
  },

  // Integrations
  integrations: {
    list: (formId, options) => apiService.get(`/open/forms/${formId}/integrations`, options),
    create: (formId, data) => apiService.post(`/open/forms/${formId}/integrations`, data),
    update: (formId, integrationId, data) => apiService.put(`/open/forms/${formId}/integrations/${integrationId}`, data),
    delete: (formId, integrationId) => apiService.delete(`/open/forms/${formId}/integrations/${integrationId}`),
    events: (formId, integrationId) => apiService.get(`/open/forms/${formId}/integrations/${integrationId}/events`),
    notionDatabases: (oauthId) => apiService.get('/open/notion/databases', { params: { oauth_id: oauthId } }),
    notionDatabaseProperties: (databaseId, oauthId) => apiService.get(`/open/notion/databases/${databaseId}/properties`, { params: { oauth_id: oauthId } }),
    trelloBoards: (apiKey, apiToken) => apiService.get('/open/trello/boards', { params: { api_key: apiKey, api_token: apiToken } }),
    trelloLists: (apiKey, apiToken, boardId) => apiService.get(`/open/trello/boards/${boardId}/lists`, { params: { api_key: apiKey, api_token: apiToken } }),
    trelloLabels: (apiKey, apiToken, boardId) => apiService.get(`/open/trello/boards/${boardId}/labels`, { params: { api_key: apiKey, api_token: apiToken } }),
    supabaseTables: (apiKey, projectUrl) => apiService.get('/open/supabase/tables', { params: { api_key: apiKey, project_url: projectUrl } }),
    supabaseColumns: (apiKey, projectUrl, tableName) => apiService.get(`/open/supabase/tables/${tableName}/columns`, { params: { api_key: apiKey, project_url: projectUrl } }),
    baserowWorkspaces: (apiKey, baseUrl) => apiService.get('/open/baserow/workspaces', { params: { api_key: apiKey, base_url: baseUrl || undefined } }),
    baserowDatabases: (apiKey, baseUrl, workspaceId) => apiService.get(`/open/baserow/workspaces/${workspaceId}/databases`, { params: { api_key: apiKey, base_url: baseUrl || undefined } }),
    baserowTables: (apiKey, baseUrl, databaseId) => apiService.get(`/open/baserow/databases/${databaseId}/tables`, { params: { api_key: apiKey, base_url: baseUrl || undefined } }),
    baserowFields: (apiKey, baseUrl, tableId) => apiService.get(`/open/baserow/tables/${tableId}/fields`, { params: { api_key: apiKey, base_url: baseUrl || undefined } }),
    linearTeams: (apiKey) => apiService.get('/open/linear/teams', { params: { api_key: apiKey } }),
    linearProjects: (apiKey, teamId) => apiService.get('/open/linear/projects', { params: { api_key: apiKey, team_id: teamId } }),
    linearStates: (apiKey, teamId) => apiService.get('/open/linear/states', { params: { api_key: apiKey, team_id: teamId } }),
    linearLabels: (apiKey, teamId) => apiService.get('/open/linear/labels', { params: { api_key: apiKey, team_id: teamId } }),
    pipedrivePipelines: (apiToken) => apiService.get('/open/pipedrive/pipelines', { params: { api_token: apiToken } }),
    pipedriveStages: (apiToken, pipelineId) => apiService.get(`/open/pipedrive/pipelines/${pipelineId}/stages`, { params: { api_token: apiToken } }),
    planeWorkspaces: (apiKey, baseUrl) => apiService.get('/open/plane/workspaces', { params: { api_key: apiKey, base_url: baseUrl || undefined } }),
    planeProjects: (apiKey, baseUrl, workspaceSlug) => apiService.get(`/open/plane/workspaces/${workspaceSlug}/projects`, { params: { api_key: apiKey, base_url: baseUrl || undefined } }),
    planeStates: (apiKey, baseUrl, workspaceSlug, projectId) => apiService.get(`/open/plane/workspaces/${workspaceSlug}/projects/${projectId}/states`, { params: { api_key: apiKey, base_url: baseUrl || undefined } }),
  },

  // Zapier webhooks
  zapier: {
    store: (data) => apiService.post('/open/forms/webhooks/zapier', data),
    delete: (id) => apiService.delete(`/open/forms/webhooks/zapier/${id}`)
  },

  // PDF Templates
  pdfTemplates: {
    list: (formId, options) => apiService.get(`/open/forms/${formId}/pdf-templates`, options),
    upload: (formId, data, options) => apiService.post(`/open/forms/${formId}/pdf-templates`, data, options),
    get: (formId, templateId, options) => apiService.get(`/open/forms/${formId}/pdf-templates/${templateId}`, options),
    update: (formId, templateId, data) => apiService.put(`/open/forms/${formId}/pdf-templates/${templateId}`, data),
    delete: (formId, templateId) => apiService.delete(`/open/forms/${formId}/pdf-templates/${templateId}`),
    download: (formId, templateId, options) => apiService.get(`/open/forms/${formId}/pdf-templates/${templateId}/download`, options),
    getDownloadRequest: (formId, templateId) => {
      const endpoint = `/open/forms/${formId}/pdf-templates/${templateId}/download`
      const requestOptions = getOpnRequestsOptions(endpoint, {})
      return {
        url: new URL(endpoint, requestOptions.baseURL).toString(),
        httpHeaders: requestOptions.headers,
      }
    },
    getSubmissionSignedUrl: (formId, templateId, submissionId) => apiService.get(`/open/forms/${formId}/pdf-templates/${templateId}/submissions/${submissionId}/signed-url`),
    getPreviewSignedUrl: (formId, templateId) => apiService.get(`/open/forms/${formId}/pdf-templates/${templateId}/preview/signed-url`)
  }
}

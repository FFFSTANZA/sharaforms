import { useQuery } from '@tanstack/vue-query'
import { formsApi } from '~/api/forms'

export function useDashboardStats(workspaceId, { days: daysRef, ...queryOptions } = {}) {
  const days = daysRef || ref(7)

  const { data: dashboard, isLoading, isFetching, isError, error } = useQuery({
    queryKey: ['dashboard', workspaceId, days],
    queryFn: () => formsApi.dashboard(workspaceId, { params: { days: days.value } }),
    enabled: computed(() => import.meta.client && !!workspaceId?.value),
    refetchInterval: 5 * 60 * 1000, // Refetch every 5 minutes
    ...queryOptions,
  })

  return {
    dashboard, // Ref<Data|null> — use .value to access the response
    days,
    isLoading,
    isFetching,
    isError,
    error,
  }
}

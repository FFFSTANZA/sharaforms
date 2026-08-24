import { useQuery } from '@tanstack/vue-query'
import { formsApi } from '~/api/forms'

export function useDashboardStats(workspaceId, { days: daysRef, ...queryOptions } = {}) {
  const days = daysRef || ref(7)
  const resolvedId = computed(() => unref(workspaceId))

  const { data: dashboard, isLoading, isFetching, isError, error } = useQuery({
    queryKey: ['dashboard', resolvedId, days],
    queryFn: () => formsApi.dashboard(resolvedId.value, { params: { days: days.value } }),
    enabled: computed(() => import.meta.client && !!resolvedId.value),
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

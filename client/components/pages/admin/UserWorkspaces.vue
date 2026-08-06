<template>
  <AdminCard
    title="Workspaces"
    icon="lucide:globe"
  >
    <UTable
      :loading-state="{ icon: 'i-lucide-refresh-cw', label: 'Loading...' }"
      :progress="{ color: 'primary', animation: 'carousel' }"
      :empty-state="{ icon: 'i-lucide-layers', label: 'No items.' }"
      :columns="columns"
      :data="rows"
      class="-mx-6"
    >
      <template #plan-cell="{ row }">
        <span
          class="text-xs select-all rounded-md px-2 py-1 border"
          :class="userPlanStyles(row.original.plan_tier)"
        >
          {{ row.original.plan_tier }}{{ row.original.is_trialing ? ' (trialing)' : '' }}
        </span>
      </template>
    </UTable>
    <div 
      v-if="workspaces?.length > pageCount"
      class="flex justify-end px-3 py-3.5 border-t border-neutral-200 dark:border-neutral-700"
    >
      <UPagination
        v-model:page="page"
        :items-per-page="pageCount"
        :total="workspaces?.length"
      />
    </div>
  </AdminCard>
</template>
  
<script setup>

const props = defineProps({
    user: { type: Object, required: true }
})

const workspaces = ref([])
const page = ref(1)
const pageCount = 2

const rows = computed(() => {
    return props.user.workspaces.slice((page.value - 1) * pageCount, (page.value) * pageCount)
})


const columns = [{
    accessorKey: 'id',
    header: 'ID'
}, {
    accessorKey: 'name',
    header: 'Name',
    sortable: true
}, {
    accessorKey: 'plan_tier',
    header: 'Plan',
    sortable: true
}, {
    accessorKey: 'forms_count',
    header: '# of forms',
    sortable: true
}]

function userPlanStyles(plan) {
    switch (plan) {
        case 'pro':
            return 'capitalize text-xs select-all bg-green-50 rounded-md px-2 py-1 border border-green-200 text-green-500'
        case 'enterprise':
            return 'capitalize text-xs select-all bg-blue-50 rounded-md px-2 py-1 border border-blue-200  text-blue-500'
        default:
            return 'capitalize text-xs select-all bg-neutral-50 rounded-md px-2 py-1 border'
    }
}

</script>
  
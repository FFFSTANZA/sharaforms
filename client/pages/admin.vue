<template>
  <div class="p-4">
    <div
      v-if="userInfo"
      class="flex gap-2 items-center flex-wrap"
    >
      <UButton
        icon="i-lucide-arrow-left"
        variant="ghost"
        color="gray"
        @click="clearUser"
      />
      <h1 class="text-xl font-semibold">
        {{ userInfo.name }}
      </h1>
      <UBadge
        color="neutral"
        variant="subtle"
        class="select-all"
      >
        {{ userInfo.id }}
      </UBadge>
      <UBadge
        color="neutral"
        variant="subtle"
        class="select-all"
      >
        {{ userInfo.email }}
      </UBadge>
      <UButton
        v-if="userInfo.stripe_id"
        color="neutral"
        variant="subtle"
        size="xs"
        class="select-all"
        :label="`Billing customer: ${userInfo.stripe_id}`"
        icon="i-lucide-credit-card"
      />
      <UBadge
        v-if="userPlan"
        v-bind="userPlanBadgeProps"
        class="capitalize"
      >
        {{ userPlan }}
      </UBadge>
    </div>
    <h3
      v-else
      class="font-semibold text-2xl text-neutral-900 mb-4"
    >
      Admin settings
    </h3>


    <template v-if="!userInfo">
      <VForm
        size="sm"
        class="pb-8 max-w-lg"
        @submit.prevent="fetchUser"
      >
        <TextInput
          name="identifier"
          :form="fetchUserForm"
          label="Identifier"
          :required="true"
          help="User Id, User Email, Form Slug or View Slug"
        />
        <UButton
          type="submit"
          :loading="loading"
          block
          class="mt-4"
          label="Fetch User"
        />
      </VForm>

      <VForm
        size="sm"
        class="pb-8 max-w-lg"
        @submit.prevent="createTemplate"
      >
        <TextAreaInput
          name="template_prompt"
          :form="createTemplateForm"
          label="Template Description"
          :required="true"
          help="Describe the template you want to create"
        />
        <UButton
          type="submit"
          :loading="templateLoading"
          block
          class="mt-4"
          label="Create Template"
        />
      </VForm>
    </template>

    <div
      v-else
      class="flex flex-col"
    >
      <div
        id="admin-buttons"
        class="flex gap-1 my-4"
      >
        <ImpersonateUser :user="userInfo" />
        <SendPasswordResetEmail :user="userInfo" />
        <ClarityLink :user="userInfo" />
        <ClearUserCacheButton
          :user="userInfo"
          @cache-cleared="refreshUser"
        />
      </div>
      <div
        class="w-full grid gap-2 grid-cols-1 lg:grid-cols-2"
      >
        <DiscountOnSubscription
          v-if="!isDodoBillingCustomer"
          :user="userInfo"
        />
        <BlockUser
          :user="userInfo"
          @user-updated="onUserUpdate"
        />
        <TwoFactorDisable
          :user="userInfo"
          @user-updated="onUserUpdate"
        />
        <ExtendTrial
          v-if="!isDodoBillingCustomer"
          :user="userInfo"
        />
        <BillingCustomer
          :user="userInfo"
        />
        <UserWorkspaces
          :user="userInfo"
        />
        <UserSubscriptions
          :user="userInfo"
          class="lg:col-span-2"
        />
        <UserPayments
          v-if="!isDodoBillingCustomer"
          :user="userInfo"
          class="lg:col-span-2"
        />
        <DeletedForms
          :user="userInfo"
          class="lg:col-span-2"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { adminApi } from '~/api'

definePageMeta({
  middleware: 'moderator',
  layout: 'dashboard'
})

useOpnSeoMeta({
  title: 'Admin'
})

const alert = useAlert()
const router = useRouter()

const userInfo = ref(null)
const userPlan = ref('free')
const isDodoBillingCustomer = true
const fetchUserForm = useForm({
  identifier: ''
})
const createTemplateForm = useForm({
  template_prompt: ''
})
const loading = ref(false)
const templateLoading = ref(false)

const userPlanBadgeProps = computed(() => {
  switch (userPlan.value) {
    case 'pro':
      return { color: 'success', variant: 'subtle' }
    case 'enterprise':
      return { color: 'primary', variant: 'subtle' }
    default:
      return { color: 'neutral', variant: 'subtle' }
  }
})

onMounted(() => {
  // Shortcut link to impersonate users
  const urlSearchParams = new URLSearchParams(window.location.search)
  const params = Object.fromEntries(urlSearchParams.entries())
  if (params.impersonate) {
    fetchUserForm.identifier = params.impersonate
  }
  if (params.user_id) {
    fetchUserForm.identifier = params.user_id
  }
  if (fetchUserForm.identifier) {
    fetchUser()
  }
})

async function fetchUser() {
  if (!fetchUserForm.identifier) {
    alert.error('Identifier is required.')
    return
  }

  loading.value = true
      adminApi.fetchUser(fetchUserForm.identifier).then(async (data) => {
    loading.value = false
    userInfo.value = { ...data.user, workspaces: data.workspaces }
    getUserPlan(data.workspaces)
    alert.success(`User Fetched: ${userInfo.value.name}`)
  })
    .catch((error) => {
      alert.error(error.data.message)
      loading.value = false
    })
}

function onUserUpdate(updatedUser) {
  userInfo.value = updatedUser
}
function getUserPlan(workspaces) {
  const tiers = workspaces.map(w => w.plan_tier)
  if (tiers.includes('enterprise')) {
    userPlan.value = 'enterprise'
  } else if (tiers.includes('business')) {
    userPlan.value = 'business'
  } else if (tiers.includes('pro')) {
    userPlan.value = 'pro'
  } else {
    userPlan.value = 'free'
  }
}

function clearUser() {
  userInfo.value = null
  userPlan.value = 'free'
  fetchUserForm.reset()
}

function refreshUser() {
  if (!fetchUserForm.identifier && userInfo.value?.id) {
    fetchUserForm.identifier = userInfo.value.id
  }
  fetchUser()
}

async function createTemplate() {
  if (!createTemplateForm.template_prompt) {
    alert.error('Template prompt is required.')
    return
  }

  templateLoading.value = true
  adminApi.createTemplate({
    template_prompt: createTemplateForm.template_prompt
  }).then((data) => {
    templateLoading.value = false
    createTemplateForm.reset()
    alert.success('Template created.')
    router.push({ name: 'templates-slug', params: { slug: data.template_slug } })
  })
    .catch((error) => {
      templateLoading.value = false
      alert.error(error.data.message)
    })
}
</script>

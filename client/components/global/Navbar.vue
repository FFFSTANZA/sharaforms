<template>
  <nav
    v-if="hasNavbar"
    :class="navWrapperClasses"
  >
    <div :class="navInnerClasses">
      <NuxtLink
        :to="{ name: user ? 'home' : 'index' }"
        class="shrink-0 flex items-center gap-1.5 hover:no-underline"
      >
        <img src="/img/sharaforms-logo.png" alt="SharaForms logo" class="h-11 w-11 shrink-0 drop-shadow-[0_1px_2px_rgba(0,0,0,0.06)]" />
        <BrandWordmark class="text-[1.45rem]" />
      </NuxtLink>

      <div class="hidden lg:flex flex-1 items-center justify-center gap-7">
        <template v-for="item in primaryNavItems" :key="item.label">
          <NuxtLink
            v-if="!item.href"
            :to="item.to"
            :class="navLinkClasses(item)"
          >
            <span v-if="item.gradientLabel" class="brand-gradient-text font-medium">{{ item.label }}</span>
            <span v-else>{{ item.label }}</span>
          </NuxtLink>
          <NuxtLink
            v-else
            :href="item.href"
            :target="item.target || '_self'"
            :class="navLinkClasses(item)"
          >
            {{ item.label }}
          </NuxtLink>
        </template>
      </div>

      <div class="ml-auto flex items-center gap-2.5">
        <WorkspaceDropdown v-if="user" class="hidden xl:block">
          <template #default="{ workspace }">
            <button
              v-if="workspace"
              :class="workspaceButtonClasses"
            >
              <WorkspaceIcon :workspace="workspace" />
              <p class="max-w-24 truncate">{{ workspace.name }}</p>
            </button>
          </template>
        </WorkspaceDropdown>

        <NuxtLink
          v-if="user"
          :to="{ name: 'home' }"
          :class="myFormsLinkClasses"
        >
          My Forms
        </NuxtLink>

        <div v-if="user">
          <UserDropdown>
            <template #default="{ user }">
              <button
                type="button"
                :class="userButtonClasses"
                dusk="nav-dropdown-button"
              >
                <img :src="user.photo_url" class="h-6 w-6 rounded-full" />
                <p class="hidden md:inline max-w-20 truncate">{{ user.name }}</p>
              </button>
            </template>
          </UserDropdown>
        </div>

        <div v-else class="hidden sm:flex items-center gap-3">
          <UButton
            v-if="$route.name !== 'login'"
            :to="{ name: 'login' }"
            variant="ghost"
            color="neutral"
            label="Log in"
              :class="loginButtonClasses"
            />
          <UButton
            :to="{ name: 'register' }"
            label="Get started"
            :class="getStartedButtonClasses"
          />
        </div>

        <button
          type="button"
          :class="mobileToggleClasses"
          :aria-expanded="mobileMenuOpen"
          aria-label="Toggle menu"
          @click="mobileMenuOpen = !mobileMenuOpen"
        >
          <UIcon
            :name="mobileMenuOpen ? 'i-heroicons:x-mark' : 'i-heroicons:bars-3'"
            class="h-5 w-5"
          />
        </button>
      </div>
    </div>

    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
        <div
          v-if="mobileMenuOpen"
          :class="mobileMenuClasses"
        >
        <WorkspaceDropdown v-if="user" class="mb-3">
          <template #default="{ workspace }">
            <button
              v-if="workspace"
                :class="mobileWorkspaceButtonClasses"
              >
              <WorkspaceIcon :workspace="workspace" />
              <p class="max-w-40 truncate">{{ workspace.name }}</p>
            </button>
          </template>
        </WorkspaceDropdown>

        <div class="flex flex-col gap-1">
          <template v-for="item in mobileNavItems" :key="item.label">
            <NuxtLink
              v-if="!item.href"
              :to="item.to"
              :class="mobileLinkClasses(item)"
              @click="mobileMenuOpen = false"
            >
              <span v-if="item.gradientLabel" class="brand-gradient-text font-medium">{{ item.label }}</span>
              <span v-else>{{ item.label }}</span>
            </NuxtLink>
            <a
              v-else
              :href="item.href"
              :target="item.target || '_self'"
              :class="mobileLinkClasses(item)"
              @click="mobileMenuOpen = false"
            >
              {{ item.label }}
            </a>
          </template>
        </div>

        <div v-if="user" :class="mobileUserSectionClasses">
          <NuxtLink
            :to="{ name: 'home' }"
            :class="mobileMyFormsClasses"
            @click="mobileMenuOpen = false"
          >
            My Forms
          </NuxtLink>
        </div>

        <div v-else class="mt-3 flex flex-col gap-2">
          <UButton
            v-if="$route.name !== 'login'"
            :to="{ name: 'login' }"
            variant="ghost"
            color="neutral"
            label="Log in"
            :class="mobileLoginButtonClasses"
          />
          <UButton
            :to="{ name: 'register' }"
            label="Get started"
            :class="mobileGetStartedButtonClasses"
          />
        </div>
      </div>
    </Transition>
  </nav>
</template>

<script setup>
import { computed, ref, watch } from "vue"
import { useRoute } from "#imports"

import WorkspaceDropdown from "../dashboard/WorkspaceDropdown.vue"
import WorkspaceIcon from "~/components/workspaces/WorkspaceIcon.vue"
import UserDropdown from "../dashboard/UserDropdown.vue"

import sharaformsConfig from "~/sharaforms.config.js"

const { data: user } = useAuth().user()
const isIframe = useIsIframe()
const route = useRoute()
const mobileMenuOpen = ref(false)
const isLandingPage = computed(() => true)

const isFormSlugRoute = computed(
  () => route.name && route.name.startsWith("forms-slug"),
)
const formSlug = computed(() =>
  isFormSlugRoute.value ? route.params.slug : null,
)
const { data: form } = useForms().detail(formSlug.value, {
  usePrivate: true,
  enabled: computed(() => !!formSlug.value),
})

const primaryNavItems = computed(() => {
  const items = []

  items.push(
    {
      label: "Pricing",
      to: { name: "pricing" },
      activeNames: ["pricing"],
    },
    { label: "Integrations", to: { name: "integrations" }, activeNames: ["integrations"] },
    { label: "Support", href: sharaformsConfig.links.help_url, target: "_blank" },
  )

  return items
})

const mobileNavItems = computed(() => primaryNavItems.value)

const isActive = item => {
  if (!item?.activeNames?.length || !route.name) return false
  return item.activeNames.includes(route.name)
}

const navLinkClasses = item => {
  const active = isActive(item)
  return [
    "text-sm transition-colors hover:no-underline",
    isLandingPage.value
      ? active
        ? "font-semibold text-neutral-900"
        : "font-medium text-neutral-600 hover:text-neutral-900"
      : active
        ? "font-semibold text-neutral-900"
        : "font-medium text-neutral-600 hover:text-neutral-900",
  ]
}

const mobileLinkClasses = item => {
  const active = isActive(item)
  return [
    "flex w-full items-center rounded-lg px-4 py-2.5 text-sm transition-colors hover:no-underline",
    isLandingPage.value
      ? active
        ? "bg-neutral-100 font-semibold text-neutral-900"
        : "font-medium text-neutral-700 hover:bg-white/80 hover:text-neutral-900"
      : active
        ? "bg-neutral-100 font-semibold text-neutral-900"
        : "font-medium text-neutral-700 hover:bg-neutral-50 hover:text-neutral-900",
  ]
}

const navWrapperClasses = "sticky top-0 z-40 px-4 pt-3 sm:px-6 lg:px-8"

const navInnerClasses = computed(() =>
  isLandingPage.value
    ? "mx-auto flex h-[60px] max-w-[87rem] items-center gap-3 rounded-[22px] border border-amber-50/70 bg-[rgba(255,252,245,0.88)] px-4 shadow-[0_14px_34px_-28px_rgba(15,23,42,0.14),inset_0_1px_0_rgba(255,255,255,0.46)] backdrop-blur-[20px] backdrop-saturate-130 sm:px-5 lg:px-6"
    : "mx-auto flex h-[60px] max-w-7xl items-center gap-3 rounded-[22px] border border-neutral-200/50 bg-white/40 px-4 shadow-[0_8px_30px_rgba(0,0,0,0.08),0_14px_34px_-28px_rgba(15,23,42,0.14),inset_0_1px_0_rgba(255,255,255,0.46)] backdrop-blur-[20px] backdrop-saturate-130 sm:px-5 lg:px-6",
)

const workspaceButtonClasses = computed(() =>
  isLandingPage.value
    ? "flex items-center gap-2 rounded-xl border border-white/55 bg-white/55 px-3 py-1.5 text-sm text-neutral-700 transition-colors hover:border-white/70 hover:bg-white/72 hover:text-neutral-900"
    : "flex items-center gap-2 rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-sm text-neutral-700 transition-colors hover:border-neutral-300 hover:text-neutral-900",
)

const myFormsLinkClasses = computed(() =>
  isLandingPage.value
    ? "hidden lg:inline-flex items-center rounded-xl px-3 py-1.5 text-sm text-neutral-700 transition-colors hover:bg-white/55 hover:text-neutral-900 hover:no-underline"
    : "hidden lg:inline-flex items-center rounded-lg px-3 py-1.5 text-sm text-neutral-700 transition-colors hover:bg-neutral-100 hover:text-neutral-900 hover:no-underline",
)

const userButtonClasses = computed(() =>
  isLandingPage.value
    ? "flex items-center gap-2 rounded-xl border border-white/55 bg-white/55 px-3 py-1.5 text-sm text-neutral-800 transition-colors hover:border-white/70 hover:bg-white/72 hover:text-neutral-900"
    : "flex items-center gap-2 rounded-lg border border-neutral-200 bg-white px-3 py-1.5 text-sm text-neutral-800 transition-colors hover:border-neutral-300 hover:text-neutral-900",
)

const loginButtonClasses = computed(() =>
  isLandingPage.value
    ? "rounded-xl px-3 py-1.5 text-sm font-medium text-neutral-800 hover:bg-white/42 hover:text-neutral-900"
    : "rounded-lg px-3 py-1.5 text-sm font-medium text-neutral-800",
)

const getStartedButtonClasses = computed(() =>
  isLandingPage.value
    ? "premium-primary-button rounded-xl px-4 py-1.5 text-sm font-medium text-white"
    : "rounded-lg bg-neutral-900 px-4 py-1.5 text-sm font-medium text-white transition-all hover:bg-neutral-800",
)

const mobileToggleClasses = computed(() =>
  isLandingPage.value
    ? "inline-flex lg:hidden items-center justify-center rounded-xl p-2 text-neutral-700 transition-colors hover:bg-white/42 hover:text-neutral-900"
    : "inline-flex lg:hidden items-center justify-center rounded-lg p-2 text-neutral-700 transition-colors hover:bg-neutral-100 hover:text-neutral-900",
)

const mobileMenuClasses = computed(() =>
  isLandingPage.value
    ? "mt-2 rounded-[22px] border border-white/24 bg-[rgba(248,249,252,0.84)] px-4 pb-5 pt-3 shadow-[0_20px_50px_-36px_rgba(15,23,42,0.14),inset_0_1px_0_rgba(255,255,255,0.46)] backdrop-blur-[20px] backdrop-saturate-130 lg:hidden"
    : "border-t border-neutral-100 bg-white px-4 pb-5 pt-3 lg:hidden",
)

const mobileWorkspaceButtonClasses = computed(() =>
  isLandingPage.value
    ? "flex w-full items-center gap-2 rounded-xl border border-white/55 bg-white/55 px-4 py-2.5 text-sm text-neutral-800"
    : "flex w-full items-center gap-2 rounded-lg border border-neutral-200 bg-white px-4 py-2.5 text-sm text-neutral-800",
)

const mobileUserSectionClasses = computed(() =>
  isLandingPage.value
    ? "mt-3 border-t border-neutral-200/80 pt-3"
    : "mt-3 border-t border-neutral-100 pt-3",
)

const mobileMyFormsClasses = computed(() =>
  isLandingPage.value
    ? "flex w-full rounded-xl px-4 py-2.5 text-sm text-neutral-800 transition-colors hover:bg-white/55 hover:text-neutral-900"
    : "flex w-full rounded-lg px-4 py-2.5 text-sm text-neutral-800 transition-colors hover:bg-neutral-100",
)

const mobileLoginButtonClasses = computed(() =>
  isLandingPage.value
    ? "w-full justify-center rounded-xl text-sm font-medium text-neutral-800 hover:bg-white/55 hover:text-neutral-900"
    : "w-full justify-center rounded-lg text-sm font-medium text-neutral-800",
)

const mobileGetStartedButtonClasses = computed(() =>
  isLandingPage.value
    ? "premium-primary-button w-full justify-center rounded-xl px-4 py-2 text-sm font-medium text-white"
    : "w-full justify-center rounded-lg bg-neutral-900 px-4 py-2 text-sm font-medium text-white",
)

const hasNavbar = computed(() => {
  if (isIframe.value) return false

  if (route.name && route.name === "forms-slug") {
    if (form.value || import.meta.server) {
      return false
    }
    return true
  }
  return true
})

watch(
  () => route.fullPath,
  () => {
    mobileMenuOpen.value = false
  },
)
</script>

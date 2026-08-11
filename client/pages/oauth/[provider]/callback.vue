<template>
  <div class="flex flex-grow mt-6 mb-10">
    <two-factor-verification-modal
      v-if="pendingAuthToken"
      :show="showTwoFactorModal"
      :pending-auth-token="pendingAuthToken"
      @verified="handleTwoFactorVerifiedAndRedirect"
      @cancel="handleTwoFactorCancel"
    />

    <div class="w-full md:w-2/3 md:mx-auto md:max-w-md px-4">
      <div
        v-if="loading || showTwoFactorModal"
        class="m-10"
      >
        <h3 class="my-6 text-center">
          {{ showTwoFactorModal ? 'Verifying your code...' : 'Please wait...' }}
        </h3>
        <Loader class="h-6 w-6 mx-auto m-10" />
      </div>
      <div
        v-else
        class="m-6 flex flex-col items-center space-y-4"
      >
        <p class="text-center">
          Unable to sign in at the moment.
        </p>
        <UButton
          :to="{ name: 'login' }"
          label="Back to login"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { WindowMessageTypes } from "~/composables/useWindowMessage"
import { authApi } from "~/api"

const router = useRouter()
const route = useRoute()
const loading = ref(true)
const authFlow = useAuthFlow()
const { showTwoFactorModal, pendingAuthToken, handleTwoFactorVerified, handleTwoFactorCancel: handleTwoFactorCancelFromFlow, handleTwoFactorError } = authFlow

const loginMessage = useWindowMessage(WindowMessageTypes.LOGIN_COMPLETE)
const providerMessage = useWindowMessage(WindowMessageTypes.OAUTH_PROVIDER_CONNECTED)

/**
 * Hand the OAuth result back to the opener page (which routes based on `new_user`
 * and re-initializes auth state from the cookies this popup just wrote), then close
 * the popup. The opener's `login-complete` listener is mounted on the login/register
 * page that started the flow, so the message is handed off via BroadcastChannel.
 */
const notifyOpenerAndClose = async ({ newUser }) => {
  // Best-effort ack wait; the message is already dispatched even if the ack times out
  try {
    await loginMessage.send(window.opener, {
      data: { new_user: newUser },
      waitForAcknowledgment: true,
      timeout: 400
    })
  } catch {
    // BroadcastChannel unavailable — nothing else to do, close anyway
  }
  try {
    await providerMessage.send(window.opener, {
      waitForAcknowledgment: false
    })
  } catch { /* best-effort */ }
  try { window.close() } catch { /* ignore */ }
  // If the window could not be closed, at least stop the spinner
  loading.value = false
}

const redirectInPlace = (newUser) => {
  if (newUser) {
    router.push({ name: "forms-create" })
    useAlert().success("Success! You're now registered with your Google account! Welcome to SharaForms.")
  } else {
    router.push({ name: "home" })
  }
  loading.value = false
}

const handleCallback = async () => {
  const provider = route.params.provider
  
  try {
    let payloadData = {
      code: route.query.code
    }
    
    // Get state token from URL query parameters (OAuth provider includes it)
    if (route.query.state) {
      payloadData.state = route.query.state
    }
    
    // Get invite token from localStorage if it was stored during OAuth initiation
    const inviteToken = localStorage.getItem('oauth_invite_token')
    if (inviteToken) {
      payloadData.invite_token = inviteToken
      localStorage.removeItem('oauth_invite_token')
    }

    // Call the OAuth callback endpoint directly to get the raw response
    let response
    try {
      response = await authApi.oauth.callback(provider, payloadData)
    } catch (error) {
      // Handle 422 responses that indicate 2FA is required (not validation errors)
      const twoFactorResponse = handleTwoFactorError(error)
      if (twoFactorResponse) {
        response = twoFactorResponse
      } else {
        throw error
      }
    }
    
    // Check if this is an authentication response (has token or requires_2fa) or integration response (has provider)
    if (response.token || (response.requires_2fa && response.pending_auth_token)) {
      // Authentication flow - user was not logged in
      // handleAuthSuccess will check for requires_2fa and show modal if needed
      await authFlow.handleAuthSuccess(response, provider, response.new_user)
      
      // If 2FA modal is shown, don't redirect yet (handled in handleTwoFactorVerifiedAndRedirect)
      if (showTwoFactorModal.value) {
        loading.value = false
        return
      }
      
      // Only proceed with redirect if we have a token (2FA not required)
      // If requires_2fa is true, we already returned above
      if (response.token) {
        const isNewUser = !!response.new_user
        const hasOpener = !!window.opener && !window.opener.closed

        // Always notify the opener (both new and existing users) so the page the user
        // started from actually completes the sign-in. New users are routed to
        // /forms/create by the opener's listener via the `new_user` flag.
        if (hasOpener) {
          await notifyOpenerAndClose({ newUser: isNewUser })
          return
        }

        // No opener (callback opened in a tab directly) → complete in this tab.
        redirectInPlace(isNewUser)
      }
    } else if (response.provider) {
      // Integration flow - user was already logged in, provider was connected
      if (window.opener) {
        try {
          await providerMessage.send(window.opener, {
            useMessageChannel: false,
            waitForAcknowledgment: false
          })
          
          if (response.autoClose) {
            window.close()
          } else {
            useAlert().success(`${response.provider.name} account connected successfully!`)
            loading.value = false
          }
        } catch {
          if (!response.autoClose) {
            useAlert().success(`${response.provider.name} account connected successfully!`)
            loading.value = false
          }
        }
      } else {
        // No opener (tab or cross-context) → rely on useWindowMessage (BC under the hood)
        await providerMessage.send(null, { waitForAcknowledgment: false })
        if (response.autoClose) {
          window.close()
        } else {
          useAlert().success(`${response.provider.name} account connected successfully!`)
          router.push({ name: "home" })
        }
      }
    } else {
      throw new Error("Unexpected response format from OAuth callback")
    }
  } catch (error) {
    console.error("[OAuth Callback] Social login error:", error)
    useAlert().error(error.response?._data?.message || "Authentication failed")
    loading.value = false
  }
}

const handleTwoFactorCancel = () => {
  handleTwoFactorCancelFromFlow()
  router.push({ name: 'login' })
}

const handleTwoFactorVerifiedAndRedirect = async (tokenData) => {
  await handleTwoFactorVerified(tokenData)

  const isNewUser = !!tokenData.new_user
  const hasOpener = !!window.opener && !window.opener.closed

  // Hand the result back to the opener (new users route via the `new_user` flag)
  if (hasOpener) {
    await notifyOpenerAndClose({ newUser: isNewUser })
    return
  }
  redirectInPlace(isNewUser)
}

onMounted(() => {
  // Set a timeout to ensure we don't get stuck in loading state
  const timeoutId = setTimeout(() => {
    if (loading.value) {
      loading.value = false
      console.error("Social login timed out")
    }
  }, 10000) // 10 second timeout
  
  handleCallback().finally(() => {
    clearTimeout(timeoutId)
  })
})
</script>
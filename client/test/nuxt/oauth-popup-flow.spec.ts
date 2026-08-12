import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import { useWindowMessage, WindowMessageTypes } from '~/composables/useWindowMessage'
import QuickRegister from '~/components/pages/auth/components/QuickRegister.vue'
import LoginForm from '~/components/pages/auth/components/LoginForm.vue'

/**
 * Shared, per-test-mutable mocks. Auto-imported composables resolve through
 * static `#imports` module references, so vi.stubGlobal is unreliable here;
 * the proven pattern is module-level vi.mock() bound to hoisted holders that
 * each test (re)assigns before mounting.
 */
const h = vi.hoisted(() => {
    const stale = () => { throw new Error('mock holder not initialized for this test') }
    return {
        appStore: {
            quickLoginModal: false,
            quickRegisterModal: false,
            isUnauthorizedError: false,
        },
        alerts: { success: stale, error: stale, info: stale },
        authStore: { token: null, initStore: stale, clearToken: stale },
        router: { push: stale, replace: stale },
        loginMutation: stale,
    }
})

vi.mock('~/stores/app', () => ({
    useAppStore: () => h.appStore,
}))

vi.mock('~/stores/auth', () => ({
    useAuthStore: () => h.authStore,
}))

vi.mock('~/composables/useAlert', () => ({
    useAlert: () => h.alerts,
}))

vi.mock('~/composables/query/useOAuth', () => ({
    useOAuth: () => ({
        guestConnect: vi.fn(),
    }),
}))

vi.mock('~/composables/query/useAuth', () => ({
    useAuth: () => ({
        login: () => h.loginMutation,
        user: () => ({ suspense: vi.fn() }),
    }),
}))

vi.mock('~/composables/useAuthFlow', () => ({
    useAuthFlow: () => ({
        showTwoFactorModal: ref(false),
        pendingAuthToken: ref(null),
        handleTwoFactorVerified: vi.fn(() => Promise.resolve()),
        handleTwoFactorCancel: vi.fn(),
        handleTwoFactorError: vi.fn(() => null),
    }),
    useIsAuthenticated: () => ({
        isAuthenticated: ref(false),
    }),
}))

vi.mock('~/composables/useOidcLinking', () => ({
    useOidcLinking: () => ({
        linkToken: ref(null),
        startLink: vi.fn(),
        clearLinkToken: vi.fn(),
        completeLinkIfNeeded: () => Promise.resolve(false),
    }),
}))

vi.mock('~/composables/useForm', () => ({
    useForm: () => ({
        email: '',
        password: '',
        remember: false,
        busy: false,
        data: () => ({}),
        post: vi.fn(() => Promise.resolve({})),
        mutate: vi.fn(() => Promise.resolve()),
    }),
}))

vi.mock('~/composables/useFeatureFlag', () => ({
    useFeatureFlag: () => false,
}))

vi.mock('~/middleware/01.check-auth.global', () => ({
    default: () => { },
}))
vi.mock('~/middleware/01.check-auth.global.js', () => ({
    default: () => { },
}))

/**
 * BroadcastChannel polyfill — jsdom does not implement it. Real channels share a
 * registry keyed by name; postMessage delivers to every OTHER channel with the
 * same name (mirroring the live behavior across the opener/popup windows).
 */
const channels = new Map()
class BroadcastChannelPolyfill {
    constructor(name) {
        this.name = name
        this.onmessage = null
        if (!channels.has(name)) channels.set(name, new Set())
        channels.get(name).add(this)
    }

    postMessage(data) {
        const peers = channels.get(this.name)
        if (!peers) return
        for (const other of peers) {
            if (other !== this) {
                other.onmessage?.({ data, target: other, source: null, origin: '' })
            }
        }
    }

    close() {
        channels.get(this.name)?.delete(this)
    }
}

const resetChannels = () => channels.clear()

const loginFormStubs = {
    ForgotPasswordModal: true,
    TwoFactorVerificationModal: true,
    VForm: { template: '<form><slot /></form>', props: ['form'] },
    TextInput: true,
    CheckboxInput: true,
    UButton: { template: '<button><slot /></button>', props: ['label', 'color', 'variant', 'to', 'loading', 'block', 'icon', 'native-type', 'type'], emits: ['click'] },
    VTransition: true,
    NuxtLink: true,
    ClientOnly: true,
    GoogleOneTap: true,
}

const UModalStub = {
    name: 'UModal',
    props: ['open', 'dismissible', 'title'],
    emits: ['update:open', 'pointer-down-outside'],
    template: '<div class="umodal"><slot name="body" /></div>',
}

const setup = ({ quickLoginModal = false, quickRegisterModal = false, isUnauthorizedError = false } = {}) => {
    h.appStore = {
        quickLoginModal,
        quickRegisterModal,
        isUnauthorizedError,
    }
    h.alerts = { success: vi.fn(), error: vi.fn(), info: vi.fn() }
    h.authStore = { token: null, initStore: vi.fn(), clearToken: vi.fn() }
    h.router = { push: vi.fn(), replace: vi.fn() }
    h.loginMutation = vi.fn()
    return { appStore: h.appStore, alerts: h.alerts, authStore: h.authStore, router: h.router }
}

const sendLoginComplete = (data) => {
    const sender = useWindowMessage(WindowMessageTypes.LOGIN_COMPLETE)
    sender.send(window, { data, waitForAcknowledgment: false })
}

const listenForAfterLogin = () => {
    const received = []
    const afterLogin = useWindowMessage(WindowMessageTypes.AFTER_LOGIN)
    afterLogin.listen(() => received.push('after-login'))
    return received
}

const tick = (ms = 80) => new Promise((resolve) => setTimeout(resolve, ms))

describe('OAuth popup flow', () => {
    beforeEach(() => {
        resetChannels()
        vi.stubGlobal('BroadcastChannel', BroadcastChannelPolyfill)
        vi.stubGlobal('useCookie', () => ({ value: null }))
        // Pinia-store auto-imports (and router) resolve via globalThis in this
        // test env rather than static #imports, so they need stubGlobals that
        // read the per-test holders (module-level mocks cover the statically
        // imported composables above).
        vi.stubGlobal('useAppStore', () => h.appStore)
        vi.stubGlobal('useAuthStore', () => h.authStore)
        vi.stubGlobal('useRouter', () => h.router)
    })

    afterEach(() => {
        vi.unstubAllGlobals()
        resetChannels()
    })

    it('delivers structured login-complete payloads through event.payload', async () => {
        // Receiver must be attached before the sender posts (mirrors live ordering).
        const received = []
        const receiver = useWindowMessage(WindowMessageTypes.LOGIN_COMPLETE)
        receiver.listen((event) => received.push(event.payload ?? null))

        const sender = useWindowMessage(WindowMessageTypes.LOGIN_COMPLETE)
        sender.send(window, { data: { new_user: true }, waitForAcknowledgment: false })

        await tick(20)
        expect(received).toEqual([{ new_user: true }])
    })

    it('QuickRegister ignores login-complete on the standalone /login page (no double handling)', async () => {
        // No quick modal is in play → QuickRegister must stay silent; LoginForm handles.
        setup({})
        const { alerts, authStore } = h

        const afterLoginMessages = listenForAfterLogin()

        mount(QuickRegister, {
            global: { stubs: { LoginForm: true, RegisterForm: true, UModal: UModalStub, UButton: true } },
        })
        mount(LoginForm, {
            props: { isQuick: false },
            global: { stubs: loginFormStubs },
        })

        sendLoginComplete({ new_user: false })
        await tick()

        // LoginForm handled it (store re-initialized, then it routed home —
        // which re-triggers the global check-auth middleware's own initStore),
        // and it never toasted. What matters for the regression is that
        // QuickRegister's gate kept it completely out of the flow.
        expect(authStore.initStore).toHaveBeenCalled()
        expect(alerts.success).not.toHaveBeenCalled()
        // ...and QuickRegister stayed out of it: no stray AFTER_LOGIN deliverable.
        expect(afterLoginMessages.length).toBe(0)
    })

    it('quick login modal completes once: modal closes, one toast, single AFTER_LOGIN', async () => {
        setup({ quickLoginModal: true })
        const { appStore, alerts, authStore } = h

        const afterLoginMessages = listenForAfterLogin()

        mount(QuickRegister, {
            global: { stubs: { LoginForm: true, RegisterForm: true, UModal: UModalStub, UButton: true } },
        })

        sendLoginComplete({ new_user: false })
        await tick()

        expect(appStore.quickLoginModal).toBe(false)
        expect(authStore.initStore).toHaveBeenCalledTimes(1)
        expect(alerts.success).toHaveBeenCalledTimes(1)
        expect(alerts.success).toHaveBeenCalledWith('Successfully logged in!')
        expect(afterLoginMessages.length).toBe(1)
    })

    it('quick register modal flow completes once (register route into quick context)', async () => {
        setup({ quickRegisterModal: true })
        const { appStore, alerts, authStore } = h

        const afterLoginMessages = listenForAfterLogin()

        mount(QuickRegister, {
            global: { stubs: { LoginForm: true, RegisterForm: true, UModal: UModalStub, UButton: true } },
        })

        sendLoginComplete({ new_user: true })
        await tick()

        expect(appStore.quickRegisterModal).toBe(false)
        expect(authStore.initStore).toHaveBeenCalledTimes(1)
        // Success toast + new-user welcome toast, never duplicated.
        expect(alerts.success).toHaveBeenCalledTimes(2)
        expect(alerts.success).toHaveBeenCalledWith('Successfully logged in!')
        expect(alerts.success).toHaveBeenCalledWith({ title: "Welcome to SharaForms 👋", description: "Time to create your first form!" })
        expect(afterLoginMessages.length).toBe(1)
    })

    it('isQuick LoginForm defers to QuickRegister and does not re-send AFTER_LOGIN', async () => {
        // QuickRegister is NOT mounted here, so if LoginForm (isQuick) re-sent
        // AFTER_LOGIN it would show up as a delivery — regression guard for the
        // duplicate-save path (guest builder).
        setup({ quickLoginModal: true })
        const { authStore } = h

        const afterLoginMessages = listenForAfterLogin()

        mount(LoginForm, {
            props: { isQuick: true },
            global: { stubs: loginFormStubs },
        })

        sendLoginComplete({ new_user: false })
        await tick()

        // Store re-initialized from the shared-stub path, but no AFTER_LOGIN
        // delivery (fresh listener on a clean channel proves it) and no toast.
        expect(authStore.initStore).toHaveBeenCalledTimes(1)
        expect(afterLoginMessages.length).toBe(0)
        expect(h.alerts.success).not.toHaveBeenCalled()
    })
})
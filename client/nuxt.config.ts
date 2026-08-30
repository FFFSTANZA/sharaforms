// https://nuxt.com/docs/api/configuration/nuxt-config
import runtimeConfig from "./runtimeConfig"
import sitemap from "./sitemap"

const isUnitTestMode = !!process.env.VITEST
const isE2EMode = process.env.E2E === '1'
const isDevtoolsEnabled =
  process.env.NUXT_DEVTOOLS === '1' && !isE2EMode && process.env.NODE_ENV !== 'production'

export default defineNuxtConfig({
  loglevel: process.env.NUXT_LOG_LEVEL || 'info',
  devtools: {enabled: isDevtoolsEnabled},
  css: ['~/css/app.css'],

  // Disable certain plugins during testing
  modules: isUnitTestMode ? [] : [
      '@pinia/nuxt', 
      '@vueuse/nuxt', 
      '@vueuse/motion/nuxt', 
      '@nuxtjs/sitemap',
      '@nuxt/ui', 
      'nuxt-utm', 
      '@nuxtjs/i18n',
      '@nuxt/icon', 
      '@zadigetvoltaire/nuxt-gtm',
      'nuxt-gtag',
  ],

  // Skip plugin initialization during tests
  plugins: isUnitTestMode ? [
      // Only include plugins safe for testing
  ] : [
      // Full plugin list for production/dev
  ],

  build: {
      transpile: ["vue-signature-pad", "@zxing/library"],
  },

  i18n: {
      locales: [
        { code: 'ar', name: 'Arabic', iso: 'ar-EG', file: 'ar.json' },
        { code: 'bn', name: 'Bengali', iso: 'bn-BD', file: 'bn.json' },
        { code: 'ca', name: 'Valencian/Catalan', iso: 'ca-ES', file: 'ca.json' },
        { code: 'cs', name: 'Czech', iso: 'cs-CZ', file: 'cs.json' },
        { code: 'de', name: 'German', iso: 'de-DE', file: 'de.json' },
        { code: 'en', name: 'English', iso: 'en-US', file: 'en.json' },
        { code: 'es', name: 'Spanish', iso: 'es-ES', file: 'es.json' },
        { code: 'eu', name: 'Basque', iso: 'eu-ES', file: 'eu.json' },
        { code: 'fr', name: 'French', iso: 'fr-FR', file: 'fr.json' },
        { code: 'gl', name: 'Galician', iso: 'gl-ES', file: 'gl.json' },
        { code: 'hi', name: 'Hindi', iso: 'hi-IN', file: 'hi.json' },
        { code: 'hu', name: 'Hungarian', iso: 'hu-HU', file: 'hu.json' },
        { code: 'it', name: 'Italian', iso: 'it-IT', file: 'it.json' },
        { code: 'ja', name: 'Japanese', iso: 'ja-JP', file: 'ja.json' },
        { code: 'jv', name: 'Javanese', iso: 'jv-ID', file: 'jv.json' },
        { code: 'ko', name: 'Korean', iso: 'ko-KR', file: 'ko.json' },
        { code: 'mr', name: 'Marathi', iso: 'mr-IN', file: 'mr.json' },
        { code: 'nl', name: 'Dutch', iso: 'nl-NL', file: 'nl.json' },
        { code: 'pa', name: 'Punjabi', iso: 'pa-IN', file: 'pa.json' },
        { code: 'pl', name: 'Polish', iso: 'pl-PL', file: 'pl.json' },
        { code: 'pt', name: 'Portuguese', iso: 'pt-BR', file: 'pt.json' },
        { code: 'ru', name: 'Russian', iso: 'ru-RU', file: 'ru.json' },
        { code: 'sk', name: 'Slovak', iso: 'sk-SK', file: 'sk.json' },
        { code: 'sr', name: 'Serbian', iso: 'sr-RS', file: 'sr.json' },
        { code: 'sv', name: 'Swedish', iso: 'sv-SE', file: 'sv.json' },
        { code: 'ta', name: 'Tamil', iso: 'ta-IN', file: 'ta.json' },
        { code: 'te', name: 'Telugu', iso: 'te-IN', file: 'te.json' },
        { code: 'tr', name: 'Turkish', iso: 'tr-TR', file: 'tr.json' },
        { code: 'uk', name: 'Ukrainian', iso: 'uk-UA', file: 'uk.json' },
        { code: 'ur', name: 'Urdu', iso: 'ur-PK', file: 'ur.json' },
        { code: 'vi', name: 'Vietnamese', iso: 'vi-VN', file: 'vi.json' },
        { code: 'zh', name: 'Chinese', iso: 'zh-CN', file: 'zh.json' },
      ],
      defaultLocale: 'en',
      lazy: true,
      langDir: 'lang/',
      strategy: 'no_prefix',
      detectBrowserLanguage: {
          cookieSecure: true
      }
  },

  features: {
      inlineStyles: false,
  },

  experimental: {
      inlineRouteRules: true
  },

  sourcemap: { client: 'hidden' },

  gtag: {
      // Defer loading gtag.js until the visitor grants analytics consent.
      // See plugins/google-analytics.client.js.
      initMode: 'manual',
      id: process.env.NUXT_PUBLIC_GOOGLE_ANALYTICS_CODE,
      initCommands: [
          // Queued before the config command so the initial page view fires
          // with analytics consent granted. Safe because gtag.js only loads
          // after the visitor accepts analytics tracking (initMode: 'manual').
          ['consent', 'update', {
              analytics_storage: 'granted',
          }],
      ],
      config: {
          anonymize_ip: true,
      },
  },

  ui: {
    theme: {
        colors: [
            'primary',
            'secondary',
            'success',
            'error',
            'warning',
            'info',
            'neutral',
            'form'
        ]
    }
  },

  components: [
      {
          path: '~/components/forms/core',
          pathPrefix: false,
          global: true,
      },
      {
          path: '~/components/forms/heavy',
          pathPrefix: false,
          global: false,
      },
      {
          path: '~/components/global',
          pathPrefix: false,
      },
      {
          path: '~/components/pages',
          pathPrefix: false,
      },
      '~/components',
  ],

  colorMode: {
      preference: 'light',
      fallback: 'light',
      classPrefix: '',
  },

  icon: {
      customCollections: [
          {
              prefix: 'sharaforms',
              dir: './public/icons'
          },
      ],
      clientBundle: {
          icons: [
              'ix:mandatory',
              'simple-icons:slack',
              'heroicons:megaphone',
              'heroicons:map',
              'heroicons:question-mark-circle',
          ],
          includeCustomCollections: true,
          scan: {
              globInclude: ['**/*.vue', '**/*.js', '**/*.json'],
          },
      },
    },

  devServer: {
    host: process.env.NUXT_HOST || 'localhost',
    port: Number(process.env.NUXT_PORT) || 3000,
  },

  // Cache marketing pages at the edge (Cloudflare) for 1h to improve TTFB and crawlability.
  // SWR: serve stale content while revalidating in the background.
  routeRules: {
    '/':                       { swr: 3600 },
    '/pricing':                { swr: 3600 },
    '/enterprise':             { swr: 3600 },
    '/templates':              { swr: 3600 },
    '/templates/**':           { swr: 3600 },
    '/integrations':           { swr: 3600 },
    '/integrations/**':        { swr: 3600 },
    '/guides':                 { swr: 3600 },
    '/guides/**':              { swr: 3600 },
    '/comparisons':            { swr: 3600 },
    '/industry':               { swr: 3600 },
    '/ai-form-builder':        { swr: 3600 },
    '/spotlight-forms':        { swr: 3600 },
    '/privacy-policy':         { swr: 86400 },
    '/terms-conditions':       { swr: 86400 },
    '/sharaforms-vs-*':        { swr: 3600 },
  },

  sitemap,
  runtimeConfig,
  compatibilityDate: '2024-10-30'
})

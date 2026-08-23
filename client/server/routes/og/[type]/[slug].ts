import { readFileSync } from 'node:fs'
import { join } from 'node:path'
import satori from 'satori'
import { Resvg } from '@resvg/resvg-js'

// Load Euclid fonts from public dir (no network in dev container)
function loadFont(weight: 400 | 600): ArrayBuffer {
  const name = weight === 600 ? 'Euclid-Circular-B-SemiBold.ttf' : 'Euclid-Circular-B-Regular.ttf'
  return readFileSync(join(process.cwd(), 'public/fonts/euclid-circular-b', name))
}

// Cache fonts in memory across requests
const fontCache = new Map<number, ArrayBuffer>()
function getFont(weight: 400 | 600): ArrayBuffer {
  if (!fontCache.has(weight)) {
    fontCache.set(weight, loadFont(weight))
  }
  return fontCache.get(weight)!
}

function slugToTitle(slug: string): string {
  return slug
    .replace(/-/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase())
}

export default defineEventHandler(async (event) => {
  const type = getRouterParam(event, 'type') as string | null // 'template', 'type', or 'industry'
  const slug = getRouterParam(event, 'slug') as string | null

  if (!type || !slug) {
    throw createError({ statusCode: 400, message: 'Missing type or slug' })
  }

  const query = getQuery(event)
  const title = (typeof query.title === 'string' && query.title) || slugToTitle(slug)
  const desc = (typeof query.desc === 'string' && query.desc) || `Created with SharaForms`
  const badgeLabel = type === 'template' ? 'Template' : type === 'type' ? 'Form Type' : 'Industry'

  // Truncate for OG card
  const displayTitle = title.length > 60 ? title.slice(0, 59) + '…' : title
  const displayDesc = desc.length > 120 ? desc.slice(0, 119) + '…' : desc

  const svg = await satori(
    {
      type: 'div',
      props: {
        children: [
          // Top row: brand + badge
          {
            type: 'div',
            props: {
              children: [
                {
                  type: 'div',
                  props: {
                    children: 'sharaforms',
                    style: { color: '#9ca3af', fontSize: 28, fontWeight: 400, letterSpacing: -0.5 },
                  },
                },
                {
                  type: 'div',
                  props: {
                    children: badgeLabel,
                    style: {
                      background: '#10b981',
                      color: 'white',
                      fontSize: 20,
                      fontWeight: 600,
                      padding: '6px 16px',
                      borderRadius: 8,
                      marginTop: 16,
                    },
                  },
                },
              ],
              style: { display: 'flex', flexDirection: 'column', alignItems: 'flex-start' },
            },
          },
          // Title
          {
            type: 'div',
            props: {
              children: displayTitle,
              style: {
                fontSize: 44,
                fontWeight: 600,
                color: '#111827',
                lineHeight: 1.2,
                marginTop: 24,
                maxWidth: 800,
              },
            },
          },
          // Description
          {
            type: 'div',
            props: {
              children: displayDesc,
              style: {
                fontSize: 24,
                fontWeight: 400,
                color: '#6b7280',
                lineHeight: 1.4,
                marginTop: 16,
                maxWidth: 750,
              },
            },
          },
        ],
        style: {
          display: 'flex',
          flexDirection: 'column',
          padding: 48,
          width: 1200,
          height: 630,
          background: 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)',
          fontFamily: 'Euclid',
        },
      },
    },
    {
      width: 1200,
      height: 630,
      fonts: [
        { name: 'Euclid', data: getFont(400), weight: 400 },
        { name: 'Euclid', data: getFont(600), weight: 600 },
      ],
    },
  )

  const resvg = new Resvg(svg, {
    fitTo: { mode: 'width', value: 1200 },
  })

  const pngData = resvg.render()
  const pngBuffer = pngData.asPng()

  setResponseHeader(event, 'content-type', 'image/png')
  setResponseHeader(event, 'cache-control', 'public, max-age=86400, stale-while-revalidate=604800')

  return pngBuffer
})

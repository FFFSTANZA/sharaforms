import { describe, expect, it } from 'vitest'
import {
  getActiveDesktopFeaturePanelIndex,
  getDesktopFeaturePanelMetrics,
} from '../../lib/welcome/desktopFeaturePanels.js'

describe('desktopFeaturePanels', () => {
  const viewportHeight = 1000

  it('prefers the panel that clearly owns the viewport focus line', () => {
    const nextIndex = getActiveDesktopFeaturePanelIndex([
      { top: -560, bottom: 240, height: 800 },
      { top: 120, bottom: 920, height: 800 },
      { top: 980, bottom: 1780, height: 800 },
    ], 0, { viewportHeight })

    expect(nextIndex).toBe(1)
  })

  it('keeps the current panel when the competing panel is only marginally stronger', () => {
    const nextIndex = getActiveDesktopFeaturePanelIndex([
      { top: -180, bottom: 620, height: 800 },
      { top: 360, bottom: 1160, height: 800 },
      { top: 1200, bottom: 2000, height: 800 },
    ], 0, { hysteresis: 120, viewportHeight })

    expect(nextIndex).toBe(0)
  })

  it('switches once the current panel has moved away and the next one is clearly dominant', () => {
    const nextIndex = getActiveDesktopFeaturePanelIndex([
      { top: -680, bottom: 120, height: 800 },
      { top: 40, bottom: 840, height: 800 },
      { top: 920, bottom: 1720, height: 800 },
    ], 0, { viewportHeight })

    expect(nextIndex).toBe(1)
  })

  it('handles viewport changes consistently for smaller desktop heights', () => {
    const nextIndex = getActiveDesktopFeaturePanelIndex([
      { top: -240, bottom: 300, height: 540 },
      { top: 180, bottom: 720, height: 540 },
      { top: 700, bottom: 1240, height: 540 },
    ], 0, { viewportHeight: 768 })

    expect(nextIndex).toBe(1)
  })

  it('marks a panel visible only while it overlaps the viewport', () => {
    const metrics = getDesktopFeaturePanelMetrics(
      { top: 1100, bottom: 1800, height: 700 },
      viewportHeight,
    )

    expect(metrics.isVisible).toBe(false)
    expect(metrics.score).toBe(Number.NEGATIVE_INFINITY)
  })
})

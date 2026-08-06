export const DESKTOP_FEATURE_PANEL_OBSERVER_THRESHOLDS = [
  0,
  0.15,
  0.3,
  0.45,
  0.6,
  0.75,
  0.9,
]

export function getDesktopFeaturePanelMetrics(rect, viewportHeight, focusLineRatio = 0.46) {
  if (!rect || viewportHeight <= 0 || rect.height <= 0) {
    return {
      isVisible: false,
      score: Number.NEGATIVE_INFINITY,
    }
  }

  const focusLine = viewportHeight * focusLineRatio
  const visibleTop = Math.max(rect.top, 0)
  const visibleBottom = Math.min(rect.bottom, viewportHeight)
  const visibleHeight = Math.max(0, visibleBottom - visibleTop)
  const isVisible = visibleHeight > 0

  if (!isVisible) {
    return {
      isVisible: false,
      score: Number.NEGATIVE_INFINITY,
    }
  }

  const panelCenter = rect.top + rect.height / 2
  const distanceToFocus = Math.abs(panelCenter - focusLine)
  const normalizedVisibleRatio = visibleHeight / Math.min(rect.height, viewportHeight)
  const containsFocusLine = rect.top <= focusLine && rect.bottom >= focusLine
  const score = (containsFocusLine ? 1000 : 0) + normalizedVisibleRatio * 240 - distanceToFocus

  return {
    containsFocusLine,
    distanceToFocus,
    isVisible,
    normalizedVisibleRatio,
    score,
    visibleHeight,
  }
}

export function getActiveDesktopFeaturePanelIndex(panelRects, currentIndex = 0, options = {}) {
  const { focusLineRatio = 0.46, hysteresis = 90, viewportHeight = 0 } = options

  if (!Array.isArray(panelRects) || panelRects.length === 0 || viewportHeight <= 0) {
    return currentIndex
  }

  const panelMetrics = panelRects.map((rect) =>
    getDesktopFeaturePanelMetrics(rect, viewportHeight, focusLineRatio),
  )

  const rankedPanels = panelMetrics
    .map((metrics, index) => ({ index, ...metrics }))
    .filter((panel) => panel.isVisible)
    .sort((panelA, panelB) => panelB.score - panelA.score)

  if (rankedPanels.length === 0) {
    return currentIndex
  }

  const nextPanel = rankedPanels[0]
  const currentPanel = panelMetrics[currentIndex]

  if (!currentPanel?.isVisible) {
    return nextPanel.index
  }

  if (currentIndex === nextPanel.index) {
    return currentIndex
  }

  return nextPanel.score > currentPanel.score + hysteresis
    ? nextPanel.index
    : currentIndex
}

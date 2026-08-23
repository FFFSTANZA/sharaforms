/**
 * SharaForms Embed — popup, slide-in, and bubble modes.
 * All changes to this file should be followed by an update of the minified version (embed-min.js)
 */
!(function () {
  var CSS = `
.nf-main {
  position: fixed;
  width: 100%;
  height: 100vh;
  top: 0; bottom: 0; left: 0; right: 0;
  z-index: 500;
  pointer-events: none;
}
.nf-main .nf-trigger {
  pointer-events: auto;
  cursor: pointer;
  position: fixed;
  bottom: 23px;
  z-index: 999;
  border: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: transform 0.15s ease;
}
.nf-main .nf-trigger:hover { transform: scale(1.08); }
.nf-main .nf-trigger:active { transform: scale(0.95); }
.nf-main.nf-right .nf-trigger { right: 28px; }
.nf-main.nf-left .nf-trigger { left: 28px; }

/* ─── Bubble trigger ─── */
.nf-main .nf-bubble-trigger {
  width: 60px; height: 60px;
  border-radius: 100%;
  display: flex; align-items: center; justify-content: center;
  font-size: 30px;
  color: #fff;
}
.nf-main .nf-bubble-trigger .nf-close-icon { display: none; }
.nf-main.open .nf-bubble-trigger .nf-trigger-icon { display: none !important; }
.nf-main.open .nf-bubble-trigger .nf-close-icon { display: flex !important; }

/* ─── Slide-in trigger ─── */
.nf-main .nf-slidein-trigger {
  display: flex; align-items: center; gap: 8px;
  padding: 12px 20px;
  border-radius: 8px;
  color: #fff;
  font-size: 14px;
  font-weight: 600;
  font-family: system-ui, sans-serif;
}
.nf-main .nf-slidein-trigger .nf-close-icon { display: none; }
.nf-main.open .nf-slidein-trigger .nf-trigger-icon { display: none !important; }
.nf-main.open .nf-slidein-trigger .nf-close-icon { display: flex !important; }

/* ─── Popup trigger (emoji circle) ─── */
.nf-main .nf-popup-trigger {
  width: 60px; height: 60px;
  border-radius: 100%;
  display: flex; align-items: center; justify-content: center;
  font-size: 30px;
  color: #fff;
}
.nf-main .nf-popup-trigger .nf-close-icon { display: none; }
.nf-main.open .nf-popup-trigger .nf-trigger-icon { display: none !important; }
.nf-main.open .nf-popup-trigger .nf-close-icon { display: flex !important; }

/* ─── Panel (popup overlay) ─── */
.nf-panel {
  position: fixed;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 6px rgba(0,0,0,0.02), 0 8px 24px rgba(0,0,0,0.12);
  pointer-events: auto;
  overflow: hidden;
  transition: opacity 0.2s ease, transform 0.25s ease;
  z-index: 1000;
}
.nf-panel iframe {
  width: 100%; height: 100%;
  border: none;
  display: block;
}

/* ─── Popup style (centered overlay) ─── */
.nf-panel-popup {
  top: 50%; left: 50%;
  transform: translate(-50%, -50%) scale(0.95);
  opacity: 0;
  max-width: calc(100vw - 40px);
  max-height: calc(100vh - 40px);
}
.nf-main.open .nf-panel-popup {
  opacity: 1;
  transform: translate(-50%, -50%) scale(1);
}

/* ─── Slide-in style ─── */
.nf-panel-slidein {
  bottom: 0;
  max-height: 80vh;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  transform: translateY(100%);
  opacity: 0;
}
.nf-main.nf-right .nf-panel-slidein { right: 20px; left: 20px; }
.nf-main.nf-left .nf-panel-slidein { left: 20px; right: 20px; }
.nf-main.open .nf-panel-slidein {
  transform: translateY(0);
  opacity: 1;
}

/* ─── Bubble style (expanding panel) ─── */
.nf-panel-bubble {
  bottom: 100px;
  border-radius: 16px;
  max-height: 70vh;
  min-height: 300px;
  transform: translateY(20px) scale(0.95);
  opacity: 0;
  transform-origin: bottom right;
}
.nf-main.nf-right .nf-panel-bubble { right: 20px; }
.nf-main.nf-left .nf-panel-bubble { left: 20px; }
.nf-main.open .nf-panel-bubble {
  transform: translateY(0) scale(1);
  opacity: 1;
}
`

!(function (e) {
    if (e && typeof document !== 'undefined') {
      var head = document.head || document.getElementsByTagName('head')[0]
      var styleEl = document.createElement('style')
      styleEl.type = 'text/css'
      head.appendChild(styleEl)
      styleEl.styleSheet
        ? (styleEl.styleSheet.cssText = e)
        : styleEl.appendChild(document.createTextNode(e))
    }
  })(CSS)
})()

;(function () {
  var script = document.currentScript || document.querySelector('script[data-nf]:last-of-type')
  if (!script) return
  var nfData
  try {
    nfData = JSON.parse(script.getAttribute('data-nf'))
  } catch { return }

  var formUrl = nfData.formurl
  if (
    window.location !== window.parent.location ||
    window.frameElement ||
    !formUrl
  ) {
    return
  }

  formUrl += (formUrl.indexOf('?') === -1 ? '?' : '&') + 'popup=true'

  var type = nfData.type || 'popup'
  var position = nfData.position === 'left' ? 'nf-left' : ''
  var color = nfData.color || '#EA6676'
  var width = parseInt(nfData.width, 10) || 500
  var height = parseInt(nfData.height, 10) || 600
  var icon = nfData.icon || '💬'
  var title = nfData.title || ''

  if (type === 'slide-in' && height > 600) height = 600

  var oldEl = document.body.querySelector('.nf-main')
  if (oldEl) oldEl.remove()

  var mainDiv = document.createElement('div')
  mainDiv.className = 'nf-main ' + position

  /* Trigger button */
  var trigger = document.createElement('div')
  trigger.className = 'nf-trigger'

  if (type === 'slide-in') {
    trigger.className += ' nf-slidein-trigger'
    trigger.innerHTML =
      '<span class="nf-trigger-icon">' +
      (icon ? '<span style="margin-right:6px">' + icon + '</span>' : '') +
      (title || 'Open Form') +
      '</span>' +
      '<span class="nf-close-icon">' +
      '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
      '</span>'
  } else {
    trigger.className += ' nf-bubble-trigger'
    if (type === 'popup') trigger.className = trigger.className.replace('nf-bubble-trigger', 'nf-popup-trigger')
    trigger.innerHTML =
      '<span class="nf-trigger-icon">' + icon + '</span>' +
      '<span class="nf-close-icon">' +
      '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
      '</span>'
  }
  trigger.style.backgroundColor = color

  /* Panel */
  var panel = document.createElement('div')
  panel.className = 'nf-panel nf-panel-' + type
  if (type === 'popup') {
    panel.style.width = Math.min(width, window.innerWidth - 40) + 'px'
    panel.style.height = Math.min(height, window.innerHeight - 40) + 'px'
  } else if (type === 'slide-in') {
    panel.style.width = 'calc(100% - 40px)'
    panel.style.maxWidth = Math.min(width, 600) + 'px'
    if (position === 'nf-left') {
      panel.style.left = '20px'
      panel.style.right = 'auto'
    } else {
      panel.style.right = '20px'
      panel.style.left = 'auto'
    }
    panel.style.height = Math.min(height, window.innerHeight * 0.8) + 'px'
  } else {
    panel.style.width = Math.min(width, window.innerWidth - 80) + 'px'
    panel.style.maxWidth = '420px'
    if (position === 'nf-left') {
      panel.style.left = '20px'
      panel.style.right = 'auto'
    } else {
      panel.style.right = '20px'
      panel.style.left = 'auto'
    }
    panel.style.height = Math.min(height, window.innerHeight * 0.7) + 'px'
    panel.style.minHeight = '300px'
  }

  mainDiv.appendChild(panel)
  mainDiv.appendChild(trigger)

  var iframeCreated = false
  var iframe = null

  function createIframe() {
    if (iframeCreated) return
    iframe = document.createElement('iframe')
    iframe.src = formUrl
    iframe.title = title || 'SharaForms'
    panel.appendChild(iframe)
    iframeCreated = true
  }

  trigger.onclick = function () {
    createIframe()
    mainDiv.classList.toggle('open')
  }

  document.body.appendChild(mainDiv)

  if (type === 'slide-in') {
    createIframe()
    setTimeout(function () { mainDiv.classList.add('open') }, 300)
  }
})()

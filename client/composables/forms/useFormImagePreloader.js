export const useFormImagePreloader = (formRef, stateRef, { skipPreload = false } = {}) => {
  const form = computed(() => formRef?.value || {})
  const state = computed(() => stateRef?.value || {})

  const allBlockImages = computed(() => {
    const properties = form.value?.properties || []
    return properties
      .map(f => f?.image?.url)
      .filter((u) => typeof u === 'string' && u.length > 0)
  })

  const staticImages = computed(() => {
    const imgs = []
    if (form.value?.cover_picture) imgs.push(form.value.cover_picture)
    if (form.value?.logo_picture) imgs.push(form.value.logo_picture)
    return imgs
  })

  const allImageUrls = computed(() => {
    const set = new Set([...(staticImages.value || []), ...(allBlockImages.value || [])])
    return Array.from(set)
  })

  const criticalImageUrls = computed(() => {
    const urls = []
    if (form.value?.cover_picture) urls.push(form.value.cover_picture)
    if (form.value?.logo_picture) urls.push(form.value.logo_picture)
    const properties = form.value?.properties || []
    const start = state.value?.currentPage ?? 0
    // Only preload the current page's image — preloading N pages ahead causes
    // "preloaded ... was not used within a few seconds" warnings because the
    // browser expects those images to appear in the initial paint.
    const url = properties[start]?.image?.url
    if (url) urls.push(url)
    return Array.from(new Set(urls))
  })

  // Skip <link rel="preload"> in demo/embed contexts where the form is below the fold
  // and images are already warmed via the Image() cache below. Preloading below-fold
  // images triggers "preloaded ... was not used within a few seconds" browser warnings.
  if (!skipPreload) {
    useHead(() => ({
      link: (criticalImageUrls.value || []).map((href) => ({ rel: 'preload', as: 'image', href }))
    }))
  }

  function warmImageCache(urls) {
    if (!urls || urls.length === 0) return
    urls.forEach((u) => {
      try {
        const img = new Image()
        img.decoding = 'async'
        img.src = u
      } catch { /* no-op */ }
    })
  }

  onMounted(() => {
    if (import.meta.client) {
      warmImageCache(allImageUrls.value)
    }
  })

  watch(allImageUrls, (urls) => {
    if (import.meta.client) warmImageCache(urls)
  })

  return {
    allImageUrls,
    criticalImageUrls,
  }
}



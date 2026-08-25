export function useComparisonSeo({ competitorName, summary }) {
  const normalizedSummary = summary.trim().replace(/\s+/g, ' ')
  // Unique per-competitor summary keeps descriptions non-templated; the tail is
  // appended only when the combined length stays inside SERP display bounds.
  const comparisonTail = ' Compare pricing, limits, and features side by side.'
  const description = normalizedSummary.length + comparisonTail.length <= 158
    ? normalizedSummary + comparisonTail
    : normalizedSummary

  return useOpnSeoMeta({
    title: `SharaForms vs ${competitorName}: Free Form Builder with Three Modes`,
    description,
    ogType: 'article',
    ogImage: '/share-preview.jpg',
    speakable: ['h1', '.comparison-bottom-line', 'p'],
    breadcrumbs: [
      { name: "Home", item: "/" },
      { name: "Alternatives", item: "/comparisons" },
      { name: `SharaForms vs ${competitorName}` },
    ],
    keywords: [
      `sharaforms vs ${competitorName}`,
      `${competitorName} alternative`,
      `free ${competitorName} alternative`,
      `${competitorName} pricing`,
      `${competitorName} free plan limits`,
      'free form builder',
      'unlimited forms',
      'unlimited submissions',
      'spotlight forms',
      'one question at a time forms',
      'multi-page forms',
      'form builder comparison',
    ].join(', '),
  })
}

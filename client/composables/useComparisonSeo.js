export function useComparisonSeo({ competitorName, summary }) {
  const normalizedSummary = summary.trim().replace(/\s+/g, ' ')

  return useOpnSeoMeta({
    title: `SharaForms vs ${competitorName}: Free Alternative with Unlimited Forms`,
    description: `${normalizedSummary} Compare free plans, pricing, and features side by side. SharaForms gives teams unlimited forms and submissions, built-in calculations, workflow-ready logic, dynamic PDFs, and a generous free tier.`,
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
      'calculated forms',
      'forms with formulas',
      'form builder comparison',
    ].join(', '),
  })
}

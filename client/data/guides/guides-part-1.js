// Guides 1-5: design decisions and logic mechanics.
// Copy rules for this directory: no em dashes, no filler openers,
// front-load the direct answer, keep every FAQ answer self-contained.
export default [
  {
    slug: 'single-page-vs-multi-page-forms',
    title: 'Single Page vs Multi Page Forms: Which One Fits',
    description:
      'Compare single page and multi page forms by length, branching, mobile behavior, and upkeep. Get a clear decision checklist plus a middle path that does both.',
    category: 'Form design',
    readingMinutes: 6,
    intro: [
      'Single page forms suit short requests where people can see the whole task at a glance, like contact forms or RSVPs. Multi page forms win when your form runs long, branches into different paths, or stages sensitive questions after easier ones. The right choice depends on length, logic complexity, and audience.',
    ],
    sections: [
      { type: 'h2', text: 'What a single page form does best' },
      {
        type: 'p',
        text: 'A single page form puts every field on one screen with one submit button. Nothing hides behind a Next button, so respondents can scan the full effort before starting. That visibility matters most when the request is small and expected.',
      },
      {
        type: 'ul',
        items: [
          'Five fields or fewer, such as name, email, and message.',
          'Repeat visitors who fill similar forms often and want speed.',
          'Simple internal requests like supply orders or room bookings.',
          'Situations where people compare options while filling in answers.',
        ],
      },
      { type: 'h2', text: 'When a multi page structure earns its keep' },
      {
        type: 'p',
        text: 'Splitting a form into pages changes how effort feels. Each step asks for one related group of answers, progress becomes visible, and heavy questions arrive only after people have invested time. For anything longer than a quick hello, that staging keeps completion moving.',
      },
      {
        type: 'ul',
        items: [
          'Applications and registrations with more than six or seven fields.',
          'Branching paths, where early answers decide later questions.',
          'Sensitive topics staged after rapport questions instead of leading.',
          'Fields that need lookups, like policy or reference numbers.',
        ],
      },
      { type: 'h2', text: 'The decision table' },
      {
        type: 'table',
        head: ['Factor', 'Single page', 'Multi page'],
        rows: [
          ['Best length', 'Under 7 fields', '8 fields and up'],
          ['Branching logic', 'Gets messy fast', 'Clean per-step branches'],
          ['Mobile scrolling', 'Long thumb work', 'Short focused screens'],
          ['Perceived effort', 'All visible up front', 'Grows step by step'],
          ['Upkeep', 'One screen to edit', 'More pages to maintain'],
        ],
      },
      { type: 'h2', text: 'A middle path most builders never offer' },
      {
        type: 'p',
        text: 'SharaForms treats layout as a presentation choice rather than a rebuild. The same form can run as Classic multi page steps, Focused single question screens, or Spotlight, which keeps every question visible on one page while highlighting the active one. Switch between them any time without touching your fields or logic, then judge results instead of guessing.',
      },
      {
        type: 'callout',
        title: 'Rule of thumb',
        text: 'Start single page if the form fits on two phone screens. Move to multi page the moment logic branches or the field count climbs past seven. Revisit the decision after your first hundred responses.',
      },
    ],
    faqs: [
      {
        question: 'Do multi step forms really get higher completion rates?',
        answer:
          'For long forms they usually do, because splitting reduces perceived effort and progress bars reward momentum. Short forms often perform equally well or better on one page, since extra clicks add friction. Test with your own audience rather than copying a benchmark from a different use case.',
      },
      {
        question: 'How many fields should sit on one form page?',
        answer:
          'Two to five related fields per page works for most audiences. Group by topic so each step reads as one idea, like contact details first and requirements second. Pages holding a single unrelated field feel slow, so merge those unless the question needs privacy or triggers a branch.',
      },
      {
        question: 'Can I switch an existing form between layouts?',
        answer:
          'Yes. SharaForms separates content from presentation, so Classic, Focused, and Spotlight modes reuse the same fields and logic. You can publish one layout today, flip to another next week, and compare completion numbers without rebuilding anything or duplicating the form.',
      },
    ],
  },

  {
    slug: 'one-question-at-a-time-forms',
    title: 'One Question at a Time Forms: When They Work',
    description:
      'One question at a time forms sharpen focus but hide total length. Learn where the format converts best, where it backfires, and the middle path worth trying.',
    category: 'Form design',
    readingMinutes: 5,
    intro: [
      'A one question at a time form shows a single field per screen and moves forward after every answer. The format suits conversational surveys, applications, and quizzes because it removes distraction. It backfires when people want to see total effort up front, like short booking or signup forms.',
    ],
    sections: [
      { type: 'h2', text: 'Why hiding other questions helps some forms' },
      {
        type: 'p',
        text: 'Attention drives completion. With one field on screen there is nothing else to read, judge, or postpone, so each answer gets full focus. Sensitive questions also land more gently when they appear alone, and mobile screens stop feeling cramped because nothing competes for space.',
      },
      { type: 'h2', text: 'Where the format quietly loses responses' },
      {
        type: 'ul',
        items: [
          'Length stays invisible, so a ten question quiz can feel endless.',
          'People cannot jump ahead or answer out of order when they pause.',
          'Quick tasks gain clicks instead of gaining clarity.',
          'Returning respondents repeat the full journey even for one correction.',
        ],
      },
      { type: 'h2', text: 'The middle ground: visible yet focused' },
      {
        type: 'p',
        text: 'SharaForms offers a third layout called Spotlight that keeps all questions listed on one page while the active question takes visual focus. Respondents see total scope and their own progress, yet attention still lands on one thing at a time. It resolves the core tension between transparency and concentration without rebuilding your form.',
      },
      {
        type: 'table',
        head: ['Layout', 'Shows', 'Best for'],
        rows: [
          ['Classic', 'Grouped steps', 'Long structured applications'],
          ['Focused', 'One field at a time', 'Conversational surveys and quizzes'],
          ['Spotlight', 'All questions, one active', 'Mixed-length forms needing overview'],
        ],
      },
      { type: 'h2', text: 'Choosing with intent' },
      {
        type: 'ol',
        items: [
          'Count your questions. Past eight, pure one-at-a-time pacing starts to drag.',
          'Ask whether respondents plan around answers, like schedules or budgets. Overview matters there.',
          'Pick Focused for narrative flows, Spotlight when scope honesty beats drama.',
          'Switch layouts in form settings and watch completion for a week before locking in.',
        ],
      },
    ],
    faqs: [
      {
        question: 'Are one question at a time forms good for mobile?',
        answer:
          'Yes, usually. Single fields fit small screens comfortably and tap targets stay large, which removes scrolling errors entirely. The format costs extra taps for navigation though, so very short forms may convert better as a single compact page that mobile users finish in one pass.',
      },
      {
        question: 'Do these forms take longer to complete?',
        answer:
          'Per question they add a moment of transition, so raw time grows slightly with length. Completion rates often improve anyway because perceived effort drops and momentum builds through progress indicators. Long forms benefit most; short ones rarely justify the extra interactions between fields.',
      },
      {
        question: 'Can I show all questions but highlight one?',
        answer:
          'Yes, that is exactly what Spotlight mode does in SharaForms. Every question stays visible as a list while styling draws the eye to the current one. You get the focus benefits of one-question-at-a-time pacing together with honest scope visibility for respondents.',
      },
    ],
  },

  {
    slug: 'add-calculations-to-a-form',
    title: 'How to Add Calculations to Any Online Form',
    description:
      'Add live calculations to online forms without code. Total quantities, score quizzes, and compute estimates with formula fields that update as respondents type.',
    category: 'Logic & calculations',
    readingMinutes: 7,
    intro: [
      'You can add calculations to a form by inserting a formula field and writing expressions that reference other answers, much like spreadsheet formulas. Totals, scores, and estimates then compute live while the respondent types. SharaForms ships this natively, so no scripts, plugins, or exports to spreadsheets are needed.',
    ],
    sections: [
      { type: 'h2', text: 'Forms that improve the moment math appears' },
      {
        type: 'ul',
        items: [
          'Quizzes that turn correct answers into a live score.',
          'Estimators that price services from quantity and tier inputs.',
          'Assessments that convert ratings into readiness or risk bands.',
          'Expense and timesheet forms that sum line items on entry.',
          'Order-style forms that subtotal choices before any checkout step.',
        ],
      },
      { type: 'h2', text: 'Setting up your first calculated field' },
      {
        type: 'steps',
        items: [
          {
            title: 'Collect the inputs first',
            text: 'Add number, choice, or rating fields for everything the math depends on. Give each a clear label, since labels become your references inside formulas.',
          },
          {
            title: 'Insert a calculation field',
            text: 'Open the builder, add a formula or calculation block where you want the result to appear, and open its editor.',
          },
          {
            title: 'Write the expression',
            text: 'Reference earlier fields by name and combine them with arithmetic. A simple line item total looks like quantity times unit price. Conditional rules let you apply different rates for different tiers.',
          },
          {
            title: 'Preview with real values',
            text: 'Open the live preview, enter sample answers, and confirm the result updates correctly before publishing the form.',
          },
        ],
      },
      { type: 'h2', text: 'Expressions worth knowing' },
      {
        type: 'table',
        head: ['Goal', 'Shape of the formula'],
        rows: [
          ['Line total', 'quantity multiplied by unit price'],
          ['Quiz score', 'sum of points awarded by each scored question'],
          ['Tiered pricing', 'conditional rule picking a rate, then multiplying by volume'],
          ['Weighted rating', 'each rating times its weight, added together'],
        ],
      },
      { type: 'h2', text: 'Mistakes that break trust in the numbers' },
      {
        type: 'ul',
        items: [
          'Leaving inputs empty with no fallback, which shows blank or broken results.',
          'Hiding intermediate math people expect to verify, like per-item prices.',
          'Rounding too early inside multi-step expressions, drifting final totals.',
          'Testing only happy paths instead of zero, maximum, and odd combinations.',
        ],
      },
      {
        type: 'callout',
        title: 'Keep the math honest',
        text: 'Show the inputs beside the result whenever possible. Estimates earn trust when respondents can see quantity, rate, and outcome together rather than receiving a number from a black box.',
      },
    ],
    faqs: [
      {
        question: 'Do respondents need to refresh to see updated totals?',
        answer:
          'No. Formula fields recalculate immediately as answers change, so totals, scores, and estimates stay current throughout the session. What respondents see is always derived from their latest answers without any manual refresh or submit step required.',
      },
      {
        question: 'Can calculations run conditionally?',
        answer:
          'Yes. Formulas support conditional rules, so different rates, weights, or bonuses can apply based on earlier answers. A service estimator might use weekend rates, or an assessment might award bonus points above a threshold, all inside the same expression.',
      },
      {
        question: 'Can I use the computed result somewhere else?',
        answer:
          'Yes. Calculated values behave like normal answers once submitted, so they appear in your dashboard columns, export to CSV and Google Sheets, flow into notification emails, and travel through webhooks to whatever system consumes them next.',
      },
    ],
  },
]

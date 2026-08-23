// Guides 4-7: scored flows, logic patterns, and attribution.
export default [
  {
    slug: 'self-grading-quiz',
    title: 'How to Make a Quiz That Grades Itself Automatically',
    description:
      'Build a quiz that marks itself and shows results instantly. Mark correct options, total points with formulas, and branch outcome messages by score band.',
    category: 'Logic & calculations',
    readingMinutes: 6,
    intro: [
      'A self grading quiz marks every answer at submission, shows participants their score instantly, and can display different results based on performance. You build one by marking correct options on choice questions, totaling points in a formula field, then mapping score ranges to tailored completion messages.',
    ],
    sections: [
      { type: 'h2', text: 'Decide the scoring before you build' },
      {
        type: 'p',
        text: 'Grading logic is easier to set up when it exists on paper first. Write your questions, mark correct answers, and choose whether every question carries equal weight or some earn more. Pick score bands too, such as under half needs review, mid range shows promise, high scores pass.',
      },
      { type: 'h2', text: 'Assembling the quiz' },
      {
        type: 'steps',
        items: [
          {
            title: 'Add choice questions',
            text: 'Use multiple choice or checkbox fields so answers stay machine readable. Open-ended text cannot be auto graded without human review.',
          },
          {
            title: 'Mark correct options',
            text: 'In SharaForms you flag the right choices directly on each question. Correct picks award their point value automatically at submission.',
          },
          {
            title: 'Total the points',
            text: 'Insert a calculation field that sums awarded points across all questions. This becomes the single score you reference everywhere else.',
          },
          {
            title: 'Branch the result message',
            text: 'Use conditional rules on the completion step so different score ranges see different outcomes, from a retake invitation to a pass certificate message.',
          },
        ],
      },
      { type: 'h2', text: 'Details that separate good quizzes from forgettable ones' },
      {
        type: 'ul',
        items: [
          'Name each band honestly. People remember what a score said about them.',
          'Offer one concrete next action per band instead of a generic well done.',
          'Keep quizzes between five and fifteen questions so momentum survives.',
          'Randomize option order if the quiz circulates in groups who compare notes.',
        ],
      },
      { type: 'h2', text: 'Where scores end up after submission' },
      {
        type: 'p',
        text: 'Every attempt lands in your dashboard with its computed score alongside individual answers. Results export to CSV, sync to Google Sheets, and pass through webhooks, so class rosters, training logs, and leaderboards can update themselves without anyone copying numbers around.',
      },
    ],
    faqs: [
      {
        question: 'Can participants see which questions they missed?',
        answer:
          'You decide per setting. Completion messages can reveal full answer breakdowns, show only the final score, or stay silent entirely. Trainers often hide specifics during assessments and share detailed feedback later, while practice quizzes usually benefit from immediate answer transparency.',
      },
      {
        question: 'How do I stop people retaking a quiz for a better score?',
        answer:
          'Limit responses by requiring sign in, or accept open access and judge first attempts only in your results export where every submission keeps its timestamp. For classrooms, collecting an email or student identifier field makes duplicate attempts visible and easy to filter.',
      },
      {
        question: 'Do self grading quizzes work in one question at a time mode?',
        answer:
          'Yes. Scoring runs on submitted values regardless of layout, so Classic pages, Focused single screens, and Spotlight views all grade identically. Choose Focused pacing for exam feel or Spotlight when you want participants to see how many questions remain.',
      },
    ],
  },

  {
    slug: 'lead-qualification-scoring-form',
    title: 'Lead Qualification Forms That Score Before You Call',
    description:
      'Score leads inside the form itself using weighted questions on budget, timeline, authority, and fit. Route hot leads to sales while cooler ones get resources.',
    category: 'Logic & calculations',
    readingMinutes: 6,
    intro: [
      'A lead qualification form grades each submission against your ideal customer profile before sales ever dials. Ask budget, timeline, decision authority, and need as weighted choice questions, total the points in a hidden calculation field, then route hot leads to reps while cooler ones receive helpful material instead.',
    ],
    sections: [
      { type: 'h2', text: 'What actually separates buyers from browsers' },
      {
        type: 'table',
        head: ['Signal', 'Strong answer', 'Weak answer'],
        rows: [
          ['Timeline', 'Buying this quarter', 'Just researching'],
          ['Budget', 'Confirmed range', 'Undetermined'],
          ['Authority', 'I approve purchases', 'Need committee sign off'],
          ['Need', 'Specific problem stated', 'Curiosity only'],
        ],
      },
      { type: 'h2', text: 'Building the scoring model' },
      {
        type: 'steps',
        items: [
          {
            title: 'Convert signals to choices',
            text: 'Phrase each signal as a multiple choice question with three or four honest options. Vague phrasing gets polite non answers, so keep wording concrete.',
          },
          {
            title: 'Assign weights',
            text: 'Give strong answers high points and weak answers low ones through a hidden calculation field. Timeline usually deserves the heaviest weight because urgency predicts conversion better than company size.',
          },
          {
            title: 'Define your bands',
            text: 'Set thresholds before launch, like forty plus routes to sales same day, twenty five to thirty nine goes to nurture, below that enters the newsletter flow.',
          },
          {
            title: 'Route automatically',
            text: 'Conditional logic can branch notification emails by band, so hot leads page a rep while cool leads trigger a resource drip instead.',
          },
        ],
      },
      { type: 'h2', text: 'Questions worth skipping' },
      {
        type: 'ul',
        items: [
          'Company size when your product prices per seat anyway, since billing reveals it.',
          'Phone numbers up front, which suppress honest timeline answers.',
          'Free text budget boxes, which invite junk entries that break scoring math.',
        ],
      },
      {
        type: 'callout',
        title: 'Calibrate monthly',
        text: 'After fifty submissions, compare scores against real outcomes and adjust weights. Most teams find one overweight signal and one dead weight question within the first month.',
      },
    ],
    faqs: [
      {
        question: 'Will asking qualification questions reduce lead volume?',
        answer:
          'Usually yes at the bottom of the funnel, and that is the point. Volume drops while quality rises, so rep time concentrates on reachable deals. Keep the form short and frame questions around helping the respondent, and the loss shrinks considerably.',
      },
      {
        question: 'Should the score be visible to respondents?',
        answer:
          'Rarely. A visible number invites gaming and feels invasive during early research stages. Keep the calculation field hidden, then personalize the thank you page or follow-up email by band so each lead still gets an experience matched to their answers.',
      },
      {
        question: 'Can qualified leads reach my CRM automatically?',
        answer:
          'Yes. Submissions including computed scores flow out through webhooks, native integrations, or the REST API, so HubSpot, Pipedrive, or any custom endpoint receives both answers and points in real time. Your routing thresholds live in the form, not in middleware.',
      },
    ],
  },

  {
    slug: 'conditional-logic-examples',
    title: '9 Conditional Logic Examples That Improve Any Form',
    description:
      'Nine practical conditional logic examples with triggers and actions: skip patterns, branching paths, dynamic totals, personalized endings, and cleaner routing.',
    category: 'Logic & calculations',
    readingMinutes: 7,
    intro: [
      'Conditional logic lets a form react to answers, showing, hiding, or routing elements based on earlier input. The nine examples below cover the patterns worth stealing: skip logic, role branching, staged consent, weighted totals, personalized endings, and routing tags that keep inboxes sorted.',
    ],
    sections: [
      { type: 'h2', text: '1. Skip irrelevant follow-ups' },
      {
        type: 'p',
        text: 'Ask "Did you find everything you needed?" If yes, skip straight to contact details. If no, reveal "What was missing?" Trigger hides questions nobody should answer, and data quality improves because every collected answer means something.',
      },
      { type: 'h2', text: '2. Branch whole journeys by role' },
      {
        type: 'p',
        text: 'A single intake form can serve managers and employees differently. One role question near the top branches into separate page paths, each holding only the fields that audience needs, replacing two forms people might visit by mistake.',
      },
      { type: 'h2', text: '3. Stage consent after context' },
      {
        type: 'p',
        text: 'Show terms acceptance only when a submission will actually be stored or shared. Forms used purely for internal calculations never surface legal language, while public submissions collect it exactly once, right before submit.',
      },
      { type: 'h2', text: '4. Require depth only when it matters' },
      {
        type: 'p',
        text: 'Make detailed descriptions required when someone selects "Other" but optional on standard choices. Required-field rules tied to conditions catch incomplete custom entries without punishing everyone who picked from the list.',
      },
      { type: 'h2', text: '5. Adjust totals with conditional rates' },
      {
        type: 'p',
        text: 'Estimators apply weekend surcharges or bulk discounts inside formula conditions rather than static pricing. The calculated field reads earlier answers, picks the applicable rate, and updates totals as respondents refine selections.',
      },
      { type: 'h2', text: '6. Endings that match the journey' },
      {
        type: 'p',
        text: 'Redirect completed support requests to a documentation hub while application rejections land on a courtesy page with next-cycle dates. Conditional redirects make every ending relevant instead of universal.',
      },
      { type: 'h2', text: '7. Tag notifications before humans read them' },
      {
        type: 'p',
        text: 'Hidden fields set by logic, like priority or department codes, travel with submissions into email subjects and webhook payloads. Downstream filters sort accurately because classification happened at collection time.',
      },
      { type: 'h2', text: '8. Follow up proportionally to satisfaction' },
      {
        type: 'p',
        text: 'Detractors see an open comment box, promoters get a referral link. Matching follow-up intensity to sentiment protects response rates among unhappy customers who abandon long post-feedback asks.',
      },
      { type: 'h2', text: '9. Progressive profiling across visits' },
      {
        type: 'p',
        text: 'Returning visitors skip questions already answered by pre-filling through URL parameters or integrations, revealing only new fields. Each encounter collects fresh details without repeating known ones.',
      },
      {
        type: 'callout',
        title: 'Start with subtraction',
        text: 'The best first use of logic is removing something. Find the question most respondents skip today and hide it behind the condition that actually applies. Instant improvement, zero risk.',
      },
    ],
    faqs: [
      {
        question: 'Does conditional logic slow down form building?',
        answer:
          'It adds planning time up front, usually minutes per rule. Building stays visual: pick a trigger field, define the condition, choose what appears or disappears. Well-named fields make rules self-documenting, which matters when colleagues edit the form months later.',
      },
      {
        question: 'Can logic reference calculated values?',
        answer:
          'Yes. Conditions can read formula outputs, so behavior can react to computed scores rather than raw inputs. A quiz can route passing scores to certificates while low scores trigger study guides, all driven by the same calculated field.',
      },
      {
        question: 'How many conditions can one form handle?',
        answer:
          'Practical limits come from comprehension, not software. Rules interact, so beyond roughly twenty conditions most builders map them on paper first. Group related rules by section and test each path in preview mode to keep behavior predictable.',
      },
    ],
  },

  {
    slug: 'hidden-form-fields-source-tracking',
    title: 'Hidden Form Fields: Track Where Leads Come From',
    description:
      'Capture UTM parameters, referrers, and campaign IDs invisibly with hidden form fields. Pipe source data into notifications, sheets, and your CRM automatically.',
    category: 'Data & tracking',
    readingMinutes: 5,
    intro: [
      'Hidden fields carry data through a form invisibly: campaign tags, referrer paths, or identifiers passed in the URL. Respondents never see them, yet every submission arrives tagged with where it came from, which makes attribution possible without cookies, scripts, or extra questions.',
    ],
    sections: [
      { type: 'h2', text: 'Why attribution breaks on most forms' },
      {
        type: 'p',
        text: 'Marketing knows the ad click worked, sales sees only an anonymous email. The connection lives in the landing URL, and unless something captures those parameters, the context evaporates between click and submission. Hidden fields are that something.',
      },
      { type: 'h2', text: 'Setting up capture' },
      {
        type: 'steps',
        items: [
          {
            title: 'Add hidden fields',
            text: 'Drop hidden fields onto the form for each parameter worth keeping, commonly utm_source, utm_campaign, utm_medium, plus your own identifiers like promo codes.',
          },
          {
            title: 'Read values from the URL',
            text: 'Configure each field to pull its value from matching query parameters, so a landing link ending in utm_source=newsletter fills that value silently.',
          },
          {
            title: 'Provide safe defaults',
            text: 'Set fallback values such as direct for traffic arriving without tags, so reports distinguish tagged campaigns from untagged visits.',
          },
          {
            title: 'Send the data onward',
            text: 'Reference hidden fields in notification email subjects, sheet columns, and webhook payloads. Attribution then travels with every submission automatically.',
          },
        ],
      },
      { type: 'h2', text: 'Uses beyond marketing' },
      {
        type: 'ul',
        items: [
          'Pre-fill employee IDs on internal request forms linked from your intranet.',
          'Attach ticket numbers to feedback forms embedded inside your product.',
          'Track which QR code poster drove event signups by varying one parameter.',
          'Carry session context into multi-step processes so handoffs stay intact.',
        ],
      },
      {
        type: 'callout',
        title: 'Test before launch',
        text: 'Open your form from a tagged test URL and submit once. The dashboard entry should display captured parameters exactly. Thirty seconds of checking prevents weeks of silent data gaps.',
      },
    ],
    faqs: [
      {
        question: 'Do hidden fields work with embedded forms?',
        answer:
          'Yes. Parameters placed on the embed URL behave the same as direct links, so iframe or script embeds can receive campaign context. Values persist through the submission and appear in exports exactly as they would on hosted pages.',
      },
      {
        question: 'Can respondents see or change hidden field values?',
        answer:
          'Values stay invisible in the rendered form, so respondents neither notice nor edit them during filling. Anyone inspecting network traffic could spot them, which is why sensitive data belongs server-side; campaign tags and identifiers are ideal uses.',
      },
      {
        question: 'Which parameters should every form capture?',
        answer:
          'At minimum utm_source, utm_medium, and utm_campaign, since together they explain which channel and promotion produced each lead. Add gclid or fbclid when paid search and social matter, and always include one business-specific code for offline tracking.',
      },
    ],
  },
]

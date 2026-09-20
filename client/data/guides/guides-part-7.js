// Guides 42-47: competitor comparison (user requested), distribution, signatures,
// abandonment diagnosis, alternatives roundup, spam defense. Same copy rules as
// earlier parts: no em dashes, direct answers first, self-contained FAQ responses,
// no recycled filler phrasing. Tables use the `head` key (the renderer ignores
// `headers`). Competitor facts verified against vendor pages in September 2026.
export default [
  {
    slug: 'zoho-spotlight-forms-vs-sharaforms',
    title: 'Zoho Spotlight Forms vs SharaForms: A Practical Comparison',
    description:
      'Zoho Forms launched Spotlight forms in May 2026. Compare focus behavior, layout switching, and free plan limits against SharaForms before you commit.',
    category: 'Comparisons',
    readingMinutes: 7,
    intro: [
      'Zoho Forms added Spotlight forms in May 2026: every field sits on one scrollable page while focus follows the scroll. SharaForms has built its whole product around that same idea, and it keeps two practical advantages: the spotlight view is a switch you flip on any form rather than a separate form type, and the free plan carries no limits on forms or submissions.',
    ],
    sections: [
      { type: 'h2', text: 'What Zoho\u2019s Spotlight form does' },
      {
        type: 'p',
        text: 'Zoho introduced Spotlight forms on May 29, 2026 as a third form type next to Standard and Card. The concept matches what SharaForms has shipped from the start: all fields live on a single page, the field in focus gets highlighted while surrounding fields dim, and respondents scroll back up to review earlier answers without pressing a back button. There is no welcome page, so people start answering immediately.',
      },
      {
        type: 'ul',
        items: [
          'Every field stays visible on one page, so total effort is never hidden.',
          'Focus shifts automatically as the respondent scrolls.',
          'Scrolling replaces Next and Previous buttons entirely.',
          'It joins Zoho\u2019s lineup as a distinct form type, chosen per form alongside Standard and Card.',
        ],
      },
      { type: 'h2', text: 'Where the two spotlight experiences differ' },
      {
        type: 'p',
        text: 'Both products share the core idea, so the honest differences show up in how answered questions behave and how much the layout is locked in.',
      },
      {
        type: 'table',
        head: ['Behavior', 'Zoho Spotlight', 'SharaForms Spotlight'],
        rows: [
          ['Page structure', 'One scrollable page', 'One page built as a card stack'],
          ['How focus moves', 'Scroll position picks the active field', 'Answers advance the active card, with arrows and auto-advance on by default'],
          ['Answered questions', 'Stay full size and dimmed', 'Collapse into compact checkmarked cards you can tap to reopen'],
          ['Choice questions', 'Standard field inputs', 'Large tap-target cards for selects, multi selects, and checkboxes'],
          ['Layout changes', 'Spotlight is a separate form type', 'Classic, Focused, and Spotlight are settings on the same form'],
        ],
      },
      {
        type: 'p',
        text: 'The collapse behavior matters more than it sounds. On a fifteen question form, Zoho respondents scroll past a wall of dimmed fields they have already answered. SharaForms shrinks each finished answer into a small checkmarked card, so the page gets shorter as people progress and the active question is always the biggest thing on screen. Finished questions stay one tap away for corrections.',
      },
      { type: 'h2', text: 'The pricing gap that decides it for most teams' },
      {
        type: 'p',
        text: 'Feature lists rarely close a decision; limits do. Zoho Forms\u2019 free plan covers one user, three forms, and 500 submissions per month. That is workable for evaluation but tight for anything real: a contact page, a newsletter signup, and one survey exhaust the allocation. Paid Zoho tiers scale by monthly submission volume, so every response you receive has a price attached.',
      },
      {
        type: 'p',
        text: 'SharaForms includes Spotlight, Classic, and Focused modes on every plan, and the free plan puts no ceiling on forms or submissions. Run spotlight on your contact page, your survey, your order form, and every template you copy, and the bill stays at zero. Built-in calculations, conditional logic, signature capture, and PDF generation ride along on the free plan too.',
      },
      { type: 'h2', text: 'What Zoho genuinely does well' },
      {
        type: 'p',
        text: 'Comparisons help you most when they stay honest. Zoho Forms is a mature product with real strengths:',
      },
      {
        type: 'ul',
        items: [
          'Deep integration with the Zoho ecosystem, including CRM, Desk, and Zoho One bundles.',
          'Mobile apps built for field work, including offline data collection.',
          'Enterprise-grade admin, reporting, and support from a large, established company.',
        ],
      },
      {
        type: 'p',
        text: 'If your company already runs Zoho One across every department, keeping form data inside that ecosystem has practical value. SharaForms connects through webhooks, Zapier, and a REST API instead, which covers most destinations but will not feel as native as a same-vendor pipeline.',
      },
      { type: 'h2', text: 'Which one to pick' },
      {
        type: 'p',
        text: 'Choose SharaForms when you want spotlight as your default form experience, expect volume, or dislike per-response pricing. You keep the freedom to flip any form into Classic steps or Focused one-at-a-time screens later, which helps when a survey underperforms and you suspect the layout. Choose Zoho when your operations already live inside Zoho apps and your form volume fits comfortably within a paid tier.',
      },
      {
        type: 'callout',
        title: 'Test both on the same form',
        text: 'Build a five question version of a real form in each product, publish both, and send each to half of your audience for a week. Completion rates and the quality of submitted answers will settle the argument faster than any feature table.',
      },
    ],
    faqs: [
      {
        question: 'Does Zoho Forms have spotlight forms?',
        answer:
          'Yes. Zoho Forms introduced Spotlight forms in May 2026 as a third form type alongside Standard and Card. Fields sit on one scrollable page and focus shifts to whichever field the respondent scrolls to. SharaForms has offered the same core experience since launch, along with Classic and Focused modes on the same form.',
      },
      {
        question: 'Is spotlight mode included on the SharaForms free plan?',
        answer:
          'Yes. Every SharaForms plan includes all three presentation modes, and the free plan has no cap on forms or submissions. Zoho\u2019s free plan covers three forms and 500 submissions per month with one user, so heavier usage moves to paid tiers there. On SharaForms you can run spotlight on every form you ever publish.',
      },
      {
        question: 'Can I change layouts without rebuilding my form?',
        answer:
          'In SharaForms, yes. Classic, Focused, and Spotlight are presentation settings on one form, so you can flip between them whenever you like and your fields, logic, and responses stay untouched. In Zoho, Spotlight is a separate form type you pick when creating, so plan your choice there before you build.',
      },
      {
        question: 'Which builder handles high submission volume for free?',
        answer:
          'SharaForms. The free plan carries no submission cap, so a form linked from a viral post or a busy season keeps collecting. Zoho\u2019s free tier stops at 500 submissions per month and its paid plans are priced by monthly volume, so cost scales with every response you receive.',
      },
    ],
  },

  {
    slug: 'how-to-embed-a-form-on-your-website',
    title: 'How to Embed a Form on Your Website (3 Ways)',
    description:
      'Embed a form on any website using an inline block, a popup button, or a plain link. Copy-paste steps, mobile tips, and fixes for the problems embeds cause.',
    category: 'Distribution',
    readingMinutes: 6,
    intro: [
      'You can embed a form on your website three ways: inline, where the form renders inside the page content; as a popup triggered by a button; or as a plain link that opens the form full screen. Inline embeds suit pages built around one action, popups suit pages with other jobs to do, and links reach everywhere code cannot.',
    ],
    sections: [
      { type: 'h2', text: 'Pick the embed style that fits the page' },
      {
        type: 'table',
        head: ['Method', 'Best for', 'Tradeoff'],
        rows: [
          ['Inline embed', 'Contact pages and landing pages built around one action', 'Takes permanent space on the page'],
          ['Popup embed', 'Busy pages where the form is one option among many', 'One more click before the first question'],
          ['Plain link', 'Bios, emails, QR codes, social posts, and docs', 'Leaves your site entirely'],
        ],
      },
      { type: 'h2', text: 'Inline embed, step by step' },
      {
        type: 'steps',
        items: [
          {
            title: 'Open the share panel',
            text: 'In your form builder, find the Share or Embed option and choose the inline or website embed type.',
          },
          {
            title: 'Copy the snippet',
            text: 'You will get a small block of HTML, usually an iframe, that points at your form.',
          },
          {
            title: 'Paste it into the page',
            text: 'Drop the snippet into your CMS content, page builder HTML block, or template file wherever the form should appear.',
          },
          {
            title: 'Set the height',
            text: 'Give the embed enough height for the whole form, or confirm your builder auto-resizes. A cut-off submit button is the most common embed failure.',
          },
          {
            title: 'Test on a phone',
            text: 'Open the published page on a real device and submit once. Check that fields, the submit button, and any date pickers all work with touch.',
          },
        ],
      },
      { type: 'h2', text: 'Popup embeds: useful, with one catch' },
      {
        type: 'p',
        text: 'Popup embeds attach the form to a button, so the page stays clean until a visitor asks for it. They suit support pages, pricing pages, and anywhere the form competes with other content. Timing matters: popups that fire on their own, especially exit-intent overlays, annoy more than they convert.',
      },
      {
        type: 'p',
        text: 'One product note from SharaForms: Focused and Spotlight presentation forms do not support overlay popup embeds, because those modes are designed as full page experiences. Use an inline embed for them, and popups remain available for Classic forms.',
      },
      { type: 'h2', text: 'Keep embeds friendly on mobile' },
      {
        type: 'ul',
        items: [
          'Avoid fixed pixel heights tuned on desktop; small screens need room to grow.',
          'Prefer auto-resize when your builder offers it, so the embed grows with validation messages.',
          'Keep generous space around the embed so taps never hit neighboring links.',
          'If the page is long, lazy-load the embed so it does not drag down initial page speed.',
        ],
      },
      { type: 'h2', text: 'Common embed problems, fixed' },
      {
        type: 'ul',
        items: [
          '**Cut off form:** increase the iframe height or switch to an auto-resizing embed.',
          '**Double scrollbars:** the iframe is taller than its container; set the height to match the form once.',
          '**Blank area:** an https page loading an http form gets blocked by the browser; both must be https.',
          '**Styling clash:** pick a transparent background in the form theme so it inherits your page.',
          '**Nothing appears in a page builder:** paste into an HTML or code block rather than a rich text field.',
        ],
      },
      {
        type: 'callout',
        title: 'Measure the page, not just the form',
        text: 'An embed can underperform while the form itself is fine. Compare views, starts, and completions for the embedded page against a plain link version sent to a slice of your audience. Low starts on the page point at the page, not the form.',
      },
    ],
    faqs: [
      {
        question: 'Can I embed a form without coding?',
        answer:
          'Yes. Most builders, including SharaForms, generate a copy-paste snippet from the share panel. You paste it into any CMS block, page builder HTML element, or website template, and the form renders in place. No plugin, script install, or developer time is needed, and edits to the form appear on the site automatically.',
      },
      {
        question: 'Why is my embedded form cut off?',
        answer:
          'The iframe height is smaller than the form inside it, so the bottom fields and submit button sit beyond the visible area. Raise the height in the snippet, or switch to an auto-resizing embed if your builder supports one, then retest on mobile where error messages and keyboards change the space the form needs.',
      },
      {
        question: 'Can I embed a form in WordPress, Shopify, or Webflow?',
        answer:
          'Yes on all three. WordPress takes the snippet in a Custom HTML block, Shopify accepts it in page content or theme sections, and Webflow uses an Embed element. The steps are identical everywhere: copy the snippet from the form, paste it into an HTML-aware block, publish, and check the result on a phone.',
      },
      {
        question: 'Do embedded forms work on mobile?',
        answer:
          'They do, and most submissions now arrive that way, so test there first. Responsive forms reflow to narrow screens automatically, but the page around them can still break the experience: cramped containers, fixed heights, and neighboring links too close to fields are the usual offenders. Submit one real response from your own phone before announcing the page.',
      },
    ],
  },

  {
    slug: 'how-to-collect-signatures-online',
    title: 'How to Collect Signatures Online Without Extra Software',
    description:
      'Collect signatures online with a form signature field: drawn or typed signing, stored consent, and a PDF record for every signer, set up in minutes.',
    category: 'Toolkits',
    readingMinutes: 6,
    intro: [
      'The fastest way to collect signatures online is a form with a signature field: the signer draws or types their name, the submission stores the signature with a timestamp, and the system generates a PDF record you can file or forward. One form handles a whole class of paperwork: waivers, consents, acknowledgments, and intake agreements.',
    ],
    sections: [
      { type: 'h2', text: 'When a form signature is enough' },
      {
        type: 'p',
        text: 'A form signature covers one signer agreeing to terms you wrote in advance. That description fits most everyday paperwork:',
      },
      {
        type: 'ul',
        items: [
          'Waivers and assumption-of-risk agreements for activities, gyms, rentals, and events.',
          'Consent forms for photos, treatment, data use, and minors handled by guardians.',
          'Acknowledgments, such as policy receipt, equipment condition, or delivery acceptance.',
          'Intake agreements where signing rides along with answering intake questions.',
        ],
      },
      {
        type: 'p',
        text: 'Multi-party contracts with negotiation, witness requirements, or regulated formalities belong in a dedicated e-signature product. Everything on the list above does not.',
      },
      { type: 'h2', text: 'Building a signature form that holds up' },
      {
        type: 'steps',
        items: [
          {
            title: 'Identify the signer',
            text: 'Collect full name and email before the signature so the record ties a signature to a person, not an anonymous session.',
          },
          {
            title: 'Show the terms plainly',
            text: 'Put the agreement text or a summary directly above the signature field. Signers must be able to read what they are agreeing to on the same screen.',
          },
          {
            title: 'Add the signature field',
            text: 'Offer drawing on touch screens and typing as a fallback. Both count as electronic signatures when intent and identity are recorded.',
          },
          {
            title: 'Capture consent explicitly',
            text: 'A short checkbox restating the key commitment, such as agreeing to the terms or confirming authority to sign for a minor, removes doubt later.',
          },
          {
            title: 'Send the record automatically',
            text: 'Turn on the confirmation email with the generated PDF attached, so both parties hold identical copies from minute one.',
          },
        ],
      },
      { type: 'h2', text: 'What makes the record credible' },
      {
        type: 'ul',
        items: [
          'A named signer with their own submitted contact details.',
          'The exact document text, stored per submission rather than only in the template.',
          'A timestamp showing when signing happened.',
          'A PDF copy generated at signing, so later template edits cannot rewrite history.',
          'Restricted access, so signed records are visible to your team only.',
        ],
      },
      { type: 'h2', text: 'Signature mistakes that cause trouble later' },
      {
        type: 'ul',
        items: [
          'Collecting a signature before the terms are visible on screen.',
          'Missing the guardian block wherever minors can participate.',
          'Relying on screenshots or chat messages as the only record.',
          'Letting one generic form cover activities with genuinely different risks.',
          'Skipping the per-signer PDF, leaving only a spreadsheet row as evidence.',
        ],
      },
      {
        type: 'table',
        head: ['Situation', 'Collect', 'Why it matters'],
        rows: [
          ['Activity waiver', 'Risks listed, signature, date', 'Specific risk acknowledgment reads better in a dispute than a blanket release'],
          ['Photo or media release', 'Usage scope, signature, guardian if minor', 'Usage rights need scope and capacity recorded'],
          ['Policy acknowledgment', 'Policy summary, signature, timestamp', 'Proves receipt and timing, not just agreement'],
          ['Intake plus agreement', 'Answers, signature, PDF', 'One record holds both the facts and the commitment'],
        ],
      },
      {
        type: 'callout',
        title: 'This explains practice, not law',
        text: 'Electronic signature rules differ by country and industry. For high-stakes agreements, have a lawyer review your wording and retention habits once; after that, a well-built signature form keeps the evidence consistent.',
      },
    ],
    faqs: [
      {
        question: 'Are online form signatures legally binding?',
        answer:
          'In most jurisdictions, yes, when the signer shows clear intent to sign and the record identifies the signer, the document, and the time. Electronic signature laws recognize this pattern broadly. Enforceability still depends on the agreement type and local rules, so have counsel review wording for anything with serious consequences.',
      },
      {
        question: 'Can people sign on a phone?',
        answer:
          'Yes, and most will. Draw-to-sign works naturally with a finger or stylus on touch screens, and a typed name option covers anyone who struggles with drawing. Large signature areas and a confirmation email with the signed PDF attached keep the mobile experience complete from first field to stored record.',
      },
      {
        question: 'Do I need a separate e-signature app?',
        answer:
          'Usually not. A signature field inside a form handles single-signer agreements where your terms are fixed, and it adds intake answers and automatic PDF records in the same step. Separate e-signature platforms earn their cost when you need multi-party negotiation, certified delivery, or sector-specific certificate formalities.',
      },
      {
        question: 'How do I prove who actually signed?',
        answer:
          'Tie the signature to identity signals you collect deliberately: a named account or submitted email, the full name typed alongside the drawn signature, a timestamp, and the stored PDF. Together these form a reasonable evidence trail. For legal-grade identity proof, expect to need identity verification steps beyond what any standard form provides.',
      },
    ],
  },

  {
    slug: 'why-people-abandon-forms',
    title: 'Why People Abandon Forms (And What to Fix First)',
    description:
      'People abandon forms over hidden length, surprise effort, weak trust, and mobile friction. Learn the seven causes, diagnose yours, and fix in the right order.',
    category: 'Form design',
    readingMinutes: 6,
    intro: [
      'People abandon forms for seven predictable reasons: the length was hidden until they committed, an early question demanded too much, the form looked untrustworthy, privacy felt risky, the phone experience fought back, an error blocked progress, or the moment was simply wrong. Diagnosing which one applies to your form matters, because each cause has a different fix.',
    ],
    sections: [
      { type: 'h2', text: 'The seven causes, in the order they strike' },
      {
        type: 'ol',
        items: [
          '**Hidden length:** the form looked short, then question nine appeared; people quit when scope surprises them.',
          '**Early heavy lifting:** a scan upload, a tax ID, or an open essay before any easy win convinces people the rest will be worse.',
          '**Thin trust signals:** generic design and no explanation of what happens to answers can read like a phishing page.',
          '**Privacy anxiety:** questions about income, health, or identity with no reason why sitting nearby.',
          '**Mobile friction:** tiny tap targets, keyboards covering fields, date pickers that fight thumbs.',
          '**Error dead ends:** a validation message that hides what to fix, or a lost session that wipes twenty minutes of typing.',
          '**Bad timing:** the email arrived at 6pm Friday, or the form demanded attention mid-task.',
        ],
      },
      { type: 'h2', text: 'Find where your form actually leaks' },
      {
        type: 'p',
        text: 'Guessing wastes redesign effort. Three signals localize the problem quickly.',
      },
      {
        type: 'ul',
        items: [
          'Many views, few starts: the top of the form is losing trust, so fix the intro, the design, and the first question.',
          'Many starts, few completions: friction lives inside the form, so cut length and move heavy questions later.',
          'A pile of saved-but-unfinished responses: note the last answered question; everything after it is the problem area.',
        ],
      },
      {
        type: 'p',
        text: 'SharaForms shows views and submissions per form, and editable submissions let people return and finish instead of starting over, which quietly rescues the lost-session crowd.',
      },
      { type: 'h2', text: 'Fixes matched to each cause' },
      {
        type: 'table',
        head: ['Cause', 'First fix', 'Stronger fix'],
        rows: [
          ['Hidden length', 'Show a progress bar', 'Switch to Spotlight or Focused pacing so effort feels staged'],
          ['Early heavy lifting', 'Move hard questions after two easy ones', 'Make them conditional so only relevant people see them'],
          ['Thin trust', 'Add a plain-language line under the title', 'Match your site branding or embed the form on your own domain'],
          ['Privacy anxiety', 'State what happens to answers', 'Collect only what you will actually use this quarter'],
          ['Mobile friction', 'Test on your own phone and fix what annoys you', 'Use large tap targets and keyboards matched to each field type'],
          ['Error dead ends', 'Write error messages that name the fix', 'Turn on editable submissions so people can return'],
          ['Bad timing', 'Resend the link at a better hour', 'Automate one reminder, then stop'],
        ],
      },
      { type: 'h2', text: 'The thirty minute audit' },
      {
        type: 'steps',
        items: [
          {
            title: 'Open the form on your phone',
            text: 'Fill it in as a respondent would. Note every moment of annoyance; you will not remember them later.',
          },
          {
            title: 'Count and mark fields',
            text: 'Total the fields, and mark every one that is required without being necessary. Unmark those.',
          },
          {
            title: 'Read questions aloud',
            text: 'Anything you stumble over, a stranger will misread. Rewrite those in plain words.',
          },
          {
            title: 'Trigger the errors',
            text: 'Submit empty, then submit wrong. Read each message and ask whether it tells you exactly what to do next.',
          },
          {
            title: 'Check the exit',
            text: 'Confirm the submit button is reachable with a thumb, the confirmation page reassures, and the confirmation email lands.',
          },
        ],
      },
      {
        type: 'callout',
        title: 'Fix one thing, then measure',
        text: 'Change a single variable, give it a week of traffic, and compare completion. Stacked changes teach you nothing about which one worked, and some fixes, like trimming required fields, produce results large enough to see without any statistics.',
      },
    ],
    faqs: [
      {
        question: 'What completion rate should a form aim for?',
        answer:
          'There is no honest universal number, because intent, audience, and ask size move it wildly: a two field newsletter signup and a forty field loan application cannot share a benchmark. Compare the form against its own history and against sibling forms in your account, and treat a steady climb as the real goal.',
      },
      {
        question: 'Do progress bars reduce abandonment?',
        answer:
          'On multi page forms they usually help, because people tolerate effort they can see ending. On single page forms they add little, since the whole form is already visible. Layout choice matters as much as the bar itself: Spotlight mode shows every question and highlights the active one, which gives the same sense of progress without hiding anything.',
      },
      {
        question: 'Are long forms always bad?',
        answer:
          'No. Long forms finish when every question earns its place, easy questions come first, and nothing appears for people it does not concern. Applications for housing, grants, and jobs routinely run twenty fields with fine completion rates. What kills long forms is padding: questions asked out of habit that no one on your team will ever read.',
      },
      {
        question: 'Should required fields be limited?',
        answer:
          'Yes, to what you truly cannot proceed without. Every required mark is a small demand on the respondent, and a wall of them reads as unreasonable, especially for optional-feeling requests like feedback. A practical rule: an answer is required only if a person, not a report, will act on its absence this month.',
      },
    ],
  },

  {
    slug: 'best-free-google-forms-alternatives',
    title: '7 Free Google Forms Alternatives Worth Trying',
    description:
      'Seven free Google Forms alternatives compared by design control, logic depth, and limits, with a quick pick list for surveys, quizzes, orders, and intake forms.',
    category: 'Comparisons',
    readingMinutes: 7,
    intro: [
      'Google Forms is free, familiar, and unlimited, and it is still the right call for a quick internal poll. Where it falls behind is design control, branding, advanced logic, and presentation options, which is exactly where the seven alternatives below compete. The quick picks: SharaForms for calculated and spotlight forms, Tally for a lightweight Notion-style builder, Jotform for template volume, Microsoft Forms for Microsoft 365 shops, Fillout for polished multi-step flows, Youform for one-question-at-a-time pages, and Formbricks for self-hosted surveys.',
    ],
    sections: [
      { type: 'h2', text: 'Where Google Forms stops being enough' },
      {
        type: 'ul',
        items: [
          'Styling is nearly fixed: a header image and a color, and every form looks like Google.',
          'No custom domain and no removing Google branding on public forms.',
          'Logic stops at simple section skips, so scored quizzes and tiered pricing need workarounds.',
          'One presentation layout: a long scroll, however long the form is.',
          'Response handling leans on Sheets, which is fine until exports, records, and PDFs matter.',
        ],
      },
      { type: 'h2', text: 'The seven alternatives at a glance' },
      {
        type: 'table',
        head: ['Builder', 'Free tier in one line', 'Choose it when'],
        rows: [
          ['SharaForms', 'Unlimited forms and submissions, three presentation modes, built-in calculations', 'You want spotlight or focused layouts, formulas, signatures, and PDFs without paying'],
          ['Tally', 'Generous free tier with a Notion-style editing feel', 'You want fast, clean forms embedded in modern sites'],
          ['Jotform', 'Free tier with a huge template gallery', 'You want a ready-made form for an unusual niche'],
          ['Microsoft Forms', 'Included with Microsoft 365 accounts', 'Your school or company already lives in Microsoft tools'],
          ['Fillout', 'Free tier with polished multi-step forms', 'Design polish matters more than anything else'],
          ['Youform', 'Free one-question-at-a-time forms', 'You want a conversational feel in under five minutes'],
          ['Formbricks', 'Open source and self-hostable', 'You want survey data on your own infrastructure'],
        ],
      },
      { type: 'h2', text: 'How to choose between them' },
      {
        type: 'ol',
        items: [
          '**Name your heaviest requirement first:** volume, design, logic, or where data must live. One of those decides it; the rest is preference.',
          '**Test the same five questions** in your top two builders, including one branching rule and one phone submission.',
          '**Check the exit path:** how you export responses, and whether records include a PDF, matters more the longer you run.',
          '**Read the limits, not the feature list:** response caps, form caps, and branding rules on the free tier decide what you can actually ship.',
        ],
      },
      { type: 'h2', text: 'Moving off Google Forms' },
      {
        type: 'p',
        text: 'Migration is usually lighter than people fear. Question text, options, and descriptions copy over by hand for most forms, since typical Google Forms run short. Keep the original spreadsheet as your archive, point any embedded links at the new form, and send one notice to anyone who bookmarked the old URL. The full walkthrough lives in our guide to [migrating from Google Forms](/guides/migrate-from-google-forms), and our [Google Forms comparison](/sharaforms-vs-googleforms) covers the switching decision in depth.',
      },
      {
        type: 'callout',
        title: 'Free is a floor, not a ceiling',
        text: 'Every builder here is free to try, but free tiers differ sharply in what happens after you outgrow the demo. Decide the volume you expect this year, then pick the builder whose free tier covers that volume, not the one with the longest feature list.',
      },
    ],
    faqs: [
      {
        question: 'Is there a truly free alternative to Google Forms?',
        answer:
          'Yes. SharaForms offers unlimited forms and unlimited submissions on its free plan, with all three presentation modes, calculations, and conditional logic included. Tally and Youform also carry usable free tiers for lighter needs. Truly free usually trades something, so check response caps and branding rules before you build anything you plan to keep.',
      },
      {
        question: 'Which Google Forms alternative looks the most professional?',
        answer:
          'Professional is mostly fit, not flash: a form that matches your site colors, carries your domain, and paces questions well reads as professional regardless of builder. SharaForms covers branding controls, custom themes, and custom domains, with Spotlight mode adding a distinctive one-focus layout. Fillout and Tally also rank high on pure design polish.',
      },
      {
        question: 'Can I move my existing Google Forms over?',
        answer:
          'Yes, though it is a rebuild rather than a sync. Copy question text and options into the new builder, which takes minutes for typical forms, then reconnect any embeds and links. Keep the original spreadsheet as a permanent archive of past responses. For a form with heavy branching, budget an hour to redo the logic properly.',
      },
      {
        question: 'Are Google Forms alternatives safe for sensitive data?',
        answer:
          'Reputable builders encrypt data in transit and at rest, but safety depends on more than the vendor: what you ask, who can view responses, and how long you retain records. For regulated data, check the vendor\u2019s compliance documentation directly, restrict workspace access, and prefer builders offering self-hosting or clear data residency options.',
      },
    ],
  },

  {
    slug: 'prevent-form-spam',
    title: 'How to Stop Form Spam Without Blocking Real People',
    description:
      'Stop form spam in layers: captcha for bots, passwords and closing rules for exposure, and notification habits for what slips through, without hurting real users.',
    category: 'Form design',
    readingMinutes: 5,
    intro: [
      'Stopping form spam works best in layers: captcha catches automated bots, password protection and closing rules shrink the window of exposure, and disciplined notification handling keeps whatever slips through from wasting your time. No single defense is perfect, and the stack matters because each layer blocks a different kind of junk.',
    ],
    sections: [
      { type: 'h2', text: 'Why your form gets spammed at all' },
      {
        type: 'p',
        text: 'Spam arrives because forms are open endpoints. Bots crawl the web for form URLs and submit them automatically, selling links, seeding scams, or probing for weak inboxes. Human spam farms hit forms with visible outcomes, like comments or testimonials. Once a URL lands on a bot list, submissions arrive daily unless something on the form resists.',
      },
      {
        type: 'ul',
        items: [
          'Automated bots that submit every open form they find, day and night.',
          'Scrapers probing fields for answers they can resell.',
          'Link sellers hoping a submission appears somewhere public.',
          'Low-paid or scripted workers targeting forms with visible results.',
        ],
      },
      { type: 'h2', text: 'The defense stack, in order' },
      {
        type: 'table',
        head: ['Defense', 'What it stops', 'Cost to real users'],
        rows: [
          ['Captcha', 'Most automated bots', 'A checkbox or challenge before submitting'],
          ['Password protection', 'All strangers, bots included', 'The form needs a shared password'],
          ['Closing dates and submission limits', 'Late spam after your deadline', 'None, if set to match your real schedule'],
          ['Smaller exposure', 'Discovery by crawlers in the first place', 'None'],
          ['Notification discipline', 'Nothing incoming; protects your attention', 'None'],
        ],
      },
      { type: 'h2', text: 'Setting up captcha properly' },
      {
        type: 'steps',
        items: [
          {
            title: 'Turn it on in form settings',
            text: 'In SharaForms, enable captcha from the form configuration and save; public submissions now require the check.',
          },
          {
            title: 'Test as a stranger',
            text: 'Open the public form in a private browser window and confirm the widget appears and submits while logged out.',
          },
          {
            title: 'Watch for friction complaints',
            text: 'If real respondents mention the challenge, that signal beats any bot statistic; adjust rather than lose them.',
          },
          {
            title: 'Keep it on, even during quiet months',
            text: 'Spam returns the week protection lapses, usually in bulk.',
          },
        ],
      },
      { type: 'h2', text: 'When captcha is not enough' },
      {
        type: 'p',
        text: 'Targeted human spam ignores captcha. For private forms, password protection removes the public URL entirely, which suits internal requests, client intake, and anything sent to a known list. For public forms, closing the form after its real deadline and setting a submission limit both cap how much junk a long-running campaign can attract.',
      },
      {
        type: 'ul',
        items: [
          'Password protection for forms meant for one team, class, or client list.',
          'Closing dates for events, applications, and anything with a real deadline.',
          'Submission limits so a burst of junk cannot exceed what you will ever process.',
          'Turn off public indexing for forms that do not need search traffic.',
        ],
      },
      { type: 'h2', text: 'Keep the junk from wasting your time' },
      {
        type: 'ul',
        items: [
          'Scan notifications before opening each submission; bot entries share patterns, like mismatched names and links.',
          'Delete junk in batches before exporting, so CSVs and reports stay clean.',
          'Watch analytics for impossible spikes; a thousand views with zero quality means protection failed somewhere.',
          'Never reply to spam submissions, even to complain; replies confirm a live human behind the address.',
        ],
      },
      {
        type: 'callout',
        title: 'Protect the data, not just the inbox',
        text: 'Spam is annoying in notifications and corrosive in analytics: junk responses distort averages, inflate counts, and hide real signals. The cleanup habit matters as much as the blocking, because every report downstream inherits whatever you leave in the dataset.',
      },
    ],
    faqs: [
      {
        question: 'Do captchas stop all form spam?',
        answer:
          'No. Captcha stops most automated bots, which are the bulk of the volume, but determined human spammers and advanced bot farms sometimes pass. Treat captcha as the first layer, then add password protection or closing rules based on who genuinely needs access. Layered defenses decline gracefully when one layer is bypassed.',
      },
      {
        question: 'How do I stop spam without a captcha?',
        answer:
          'Shrink exposure instead: password-protect the form for known audiences, close it after the real deadline, cap submissions, and turn off public indexing if search traffic does not matter. Review notifications in batches and delete junk before exporting. This combination handles quiet forms well, though very public forms almost always need captcha eventually.',
      },
      {
        question: 'Why did my form suddenly get spammed?',
        answer:
          'A bot crawler found your form URL, usually from a public link, an embed, or the sitemap, and added it to a submission list that fires daily. Volume then grows in bursts rather than trickles. The response is the same regardless of trigger: enable captcha, review exposure settings, and clean existing junk from your data.',
      },
      {
        question: 'Can I close a form automatically?',
        answer:
          'Yes. SharaForms supports closing dates and submission limits in form settings, so a form stops accepting responses on the date you choose or once the cap is reached. Both settings also starve late spam campaigns, since closed forms reject everything, and respondents see a clear closed message instead of a broken page.',
      },
    ],
  },
]

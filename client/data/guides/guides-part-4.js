// Guides 11-16: high-pull task clusters (questions banks, events, groups,
// notifications, distribution). Same copy rules: no em dashes, direct answers.
export default [
  {
    slug: 'customer-satisfaction-survey-questions',
    title: 'Customer Satisfaction Survey Questions That Work',
    description:
      'Copy proven customer satisfaction survey questions: overall scores by CSAT and NPS, product fit checks, support quality, and open questions worth asking.',
    category: 'Question banks',
    readingMinutes: 7,
    intro: [
      'A customer satisfaction survey needs surprisingly few questions to be useful: one overall score question, two or three driver questions covering product, support, and value, plus one open comment field. The full bank below gives you tested wording for each slot so you can build yours in minutes.',
    ],
    sections: [
      { type: 'h2', text: 'Start with the score itself' },
      {
        type: 'ul',
        items: [
          '**Overall satisfaction:** How satisfied are you with [product] overall? (Very satisfied to very dissatisfied)',
          '**CSAT wording for transactions:** How would you rate your latest experience with us? (1 to 5)',
          '**Loyalty:** How likely are you to recommend [product] to a colleague? (0 to 10)',
        ],
      },
      {
        type: 'p',
        text: 'Pick exactly one headline metric. Teams that track three overall scores end up reporting none of them well, because every dashboard needs a single number people care about.',
      },
      { type: 'h2', text: 'Driver questions that explain the score' },
      {
        type: 'p',
        text: 'Scores tell you what happened; drivers tell you why. Rotate through these four areas each cycle instead of asking everything at once.',
      },
      {
        type: 'ul',
        items: [
          '**Product:** Which feature do you rely on most? Where does [product] fall short of your expectations?',
          '**Support:** When you contacted us, how quickly did we respond? Did we solve your issue on first contact?',
          '**Value:** Is the current pricing fair for the value you receive? Which capability would justify paying more?',
          '**Effort:** How easy is it to accomplish your main task inside [product]? What slows you down most?',
        ],
      },
      { type: 'h2', text: 'Open questions that earn their length' },
      {
        type: 'ul',
        items: [
          'What almost stopped you from choosing us?',
          'If you could change one thing about [product], what would it be?',
          'Describe a moment when [product] saved you time.',
        ],
      },
      {
        type: 'p',
        text: 'One open question converts far better than five. Place it right after the score question while motivation to explain is highest, and mark it optional so completion never suffers.',
      },
      { type: 'h2', text: 'Assembly rules that protect response rates' },
      {
        type: 'ol',
        items: [
          'Cap the survey at eight questions; completion drops once surveys pass the two minute mark.',
          'Group related questions into sections with a visible progress bar.',
          'Send within 24 hours of the triggering experience while memory is fresh.',
          'Close the loop: publish what changed because of last quarter\'s answers.',
        ],
      },
      {
        type: 'callout',
        title: 'Score bands beat raw averages',
        text: 'Report promoters, passives, and detractors as shares rather than a lone average. Averages hide polarization, and the fix for a polarized audience is completely different from the fix for mild disappointment.',
      },
    ],
    faqs: [
      {
        question: 'How many questions should a customer satisfaction survey have?',
        answer:
          'Five to eight works best for recurring programs. The score question, two or three drivers matched to what you changed recently, and one optional open field cover decision-making needs while keeping completion under two minutes for nearly everyone.',
      },
      {
        question: 'Which is better, CSAT or NPS?',
        answer:
          'They answer different questions. CSAT measures satisfaction with a specific recent interaction, making it ideal after tickets and deliveries. NPS measures relationship-level loyalty over time. Transaction-heavy teams run both; if you must choose one, match it to what you will actually act on.',
      },
      {
        question: 'Should satisfaction surveys be anonymous?',
        answer:
          'For relationship surveys, anonymity raises honesty about sensitive topics. For transactional follow-ups, identifying details help you resolve individual issues quickly. A middle path keeps contact fields optional so respondents decide how reachable they want to be.',
      },
    ],
  },

  {
    slug: 'collect-rsvps-online-free',
    title: 'Collect RSVPs Online Without Email Chains',
    description:
      'Set up online RSVP collection for weddings, parties, and reunions: one link for guests, meal choices, headcounts, and automatic guest-list tracking.',
    category: 'Events & groups',
    readingMinutes: 5,
    intro: [
      'Collecting RSVPs online takes one shared link and a short form: guest name, attendance choice, meal preference, and headcount. Guests tap through on their phones in under a minute, and every reply lands in one organized list instead of scattered texts and email replies you have to count by hand.',
    ],
    sections: [
      { type: 'h2', text: 'What your RSVP form actually needs' },
      {
        type: 'ul',
        items: [
          'Guest or party name, so you know whose reply arrived.',
          'Attending yes or no, phrased warmly since some replies are regrets.',
          'Number of people in the party, when invitations go to households.',
          'Meal or drink choices, only if catering requires counts.',
          'Dietary notes as an optional field rather than mandatory text.',
        ],
      },
      { type: 'h2', text: 'Setting it up in minutes' },
      {
        type: 'steps',
        items: [
          {
            title: 'Start from the RSVP template',
            text: 'The prebuilt structure already carries name, attending, and party size fields. Duplicate it and delete anything your event does not need.',
          },
          {
            title: 'Match fields to your venue requirements',
            text: 'Caterers usually want menu selections and dietary flags. Venues sometimes need arrival times. Add only what someone downstream actually consumes.',
          },
          {
            title: 'Share one link everywhere',
            text: 'Invitations, group chats, and fridge magnets with QR codes can all point to the same URL, which keeps counting centralized.',
          },
          {
            title: 'Watch responses arrive live',
            text: 'Each submission lands timestamped in your dashboard, and export produces a tidy guest sheet for seating charts and catering totals.',
          },
        ],
      },
      { type: 'h2', text: 'Etiquette details hosts forget' },
      {
        type: 'ul',
        items: [
          'State a reply-by date on the invitation itself, not just in reminders.',
          'Let guests say no gracefully; a warm regret option beats ghosting.',
          'For large families, ask party-level counts rather than individual names for children.',
          'Close edits before printing place cards, then freeze the exported list.',
        ],
      },
      {
        type: 'callout',
        title: 'Reminder without nagging',
        text: 'One reminder message a few days before the deadline recovers most stragglers. Because your link never changes, late repliers simply fill the same form and the count updates itself.',
      },
    ],
    faqs: [
      {
        question: 'Can guests RSVP for multiple people at once?',
        answer:
          'Yes. Add a number field for total attendees alongside the primary guest name, or use checkbox lists for named family members. Party-level counting keeps the form short while still producing accurate totals for venues and caterers.',
      },
      {
        question: 'Do guests need an account to submit their RSVP?',
        answer:
          'No. Anyone holding the link can respond immediately, which matters for grandparents and less technical guests. Frictionless links consistently collect more complete guest lists than systems requiring sign-ups.',
      },
      {
        question: 'How do I stop duplicate RSVPs from the same guest?',
        answer:
          'Enable editable submissions so guests update their original entry instead of submitting twice, and keep a timestamp column in your export. If duplicates still appear, the latest timestamped entry wins when you reconcile before the deadline.',
      },
    ],
  },

  {
    slug: 'online-event-registration-guide',
    title: 'Online Event Registration: Set Up Yours in Minutes',
    description:
      'Launch online event registration fast: attendee fields that matter, confirmation emails, capacity signals, and clean check-in exports, all without fees per signup.',
    category: 'Events & groups',
    readingMinutes: 6,
    intro: [
      'Online event registration needs four things working together: a short form collecting only decision-relevant attendee details, an instant confirmation email, a live view of signups, and a clean export for check-in day. Set those up once and registration runs itself while you plan the actual event.',
    ],
    sections: [
      { type: 'h2', text: 'Fields that matter versus fields that annoy' },
      {
        type: 'table',
        head: ['Keep always', 'Add when relevant', 'Skip entirely'],
        rows: [
          ['Attendee name', 'T-shirt sizes', 'Job titles for community events'],
          ['Email for confirmations', 'Session preferences', 'Full postal addresses'],
          ['Party or ticket count', 'Accessibility needs', 'Phone numbers upfront'],
          ['Any dietary flags', 'Company or school name', 'Security questions'],
        ],
      },
      { type: 'h2', text: 'The setup sequence' },
      {
        type: 'steps',
        items: [
          {
            title: 'Duplicate the registration template',
            text: 'Prebuilt attendee fields save the blank-page problem. Trim until every remaining field maps to a real operational need.',
          },
          {
            title: 'Turn on instant confirmations',
            text: 'An automated email carrying date, location, and calendar-worthy details cuts no-show rates and support emails simultaneously.',
          },
          {
            title: 'Publish and distribute',
            text: 'Share the direct link across channels, embed it on the event page, and print QR codes for posters so offline audiences convert too.',
          },
          {
            title: 'Export for check-in',
            text: 'A CSV sorted by name becomes your door list. Timestamps show signup velocity, useful for forecasting supplies and room swaps.',
          },
        ],
      },
      { type: 'h2', text: 'Capacity management without paid tiers' },
      {
        type: 'p',
        text: 'Watch registrations accumulate in the dashboard and set an alert threshold with yourself. When seats fill, swap the form link target to a waitlist version built from the same fields, so latecomers join a queue instead of bouncing off a dead end.',
      },
      { type: 'h2', text: 'Details that separate smooth events from chaotic ones' },
      {
        type: 'ul',
        items: [
          'Test the entire flow on your own phone before promoting the link.',
          'Put start time and timezone in the confirmation, not just the landing page.',
          'Decide your edit policy: editable submissions prevent duplicate registrations.',
          'Name your exports by event and date so next year does not inherit mystery files.',
        ],
      },
    ],
    faqs: [
      {
        question: 'Can attendees register multiple people in one submission?',
        answer:
          'Yes. A numeric party-size field covers households and friend groups, while named checkboxes work when you need individual attendee lists for badges. Choose based on whether check-in treats parties or individuals as the unit.',
      },
      {
        question: 'How do attendees receive proof of registration?',
        answer:
          'Automatic confirmation emails fire on every submission and carry whatever details you include: event summary, location, schedule, and any preparation instructions. For formal events, generate a personalized PDF confirmation attached to the same email.',
      },
      {
        question: 'What happens when my event hits capacity?',
        answer:
          'Registrations keep arriving unless you change the link destination. The practical pattern is monitoring counts in your dashboard, then switching promotion to a waitlist form cloned from the same fields so interested people stay captured for future events.',
      },
    ],
  },

  {
    slug: 'sign-up-sheet-online',
    title: 'Sign Up Sheets Online: Organize Any Group Fast',
    description:
      'Create online sign up sheets for classes, volunteers, snacks, and shifts. One link everyone taps, slots that fill themselves, and a list that stays current.',
    category: 'Events & groups',
    readingMinutes: 4,
    intro: [
      'An online sign up sheet replaces the paper clipboard with one link: people open it, claim a slot or item, and submit in seconds. Teachers use them for classroom helpers, coaches for snack duty, and workplaces for potlucks, because the list updates itself and never gets lost.',
    ],
    sections: [
      { type: 'h2', text: 'Sheet shapes worth copying' },
      {
        type: 'table',
        head: ['Use case', 'Structure that works'],
        rows: [
          ['Parent teacher conferences', 'Time-slot checkboxes, one family per block'],
          ['Classroom volunteering', 'Role list with dates beside each'],
          ['Team snacks and drinks', 'Date rows with item dropdowns'],
          ['Office potluck', 'Dish categories plus servings count'],
          ['Volunteer shifts', 'Shift times capped by role'],
        ],
      },
      { type: 'h2', text: 'Build one in three steps' },
      {
        type: 'ol',
        items: [
          'List the slots, roles, or items as choice options grouped by date where relevant.',
          'Ask only for name and contact method; every extra field loses busy parents.',
          'Share the link where your group already lives: class app, team chat, or printed QR code.',
        ],
      },
      { type: 'h2', text: 'Keeping the list honest' },
      {
        type: 'ul',
        items: [
          'Enable editable submissions so changes update entries instead of duplicating them.',
          'Include timestamps in exports; the latest entry wins any dispute.',
          'Recruit a backup coordinator who holds the same dashboard access.',
        ],
      },
      {
        type: 'callout',
        title: 'Fill the awkward empty slots',
        text: 'When a week stays empty, resend the same link with one line naming the gap. Specific asks like Tuesday still open outperform generic reminders every time.',
      },
    ],
    faqs: [
      {
        question: 'Can two people claim the same slot accidentally?',
        answer:
          'Yes, simultaneous submissions can overlap since the form does not lock slots in real time. Reconcile with timestamps in your export, where the earlier claim stands. For high-contention scheduling, announce windows so claims arrive staggered.',
      },
      {
        question: 'Do participants need to install anything?',
        answer:
          'No. The sheet opens in any phone browser straight from the link or QR code, submissions take seconds, and nobody creates accounts. That zero-friction path is precisely why online sheets outperform paper on participation.',
      },
      {
        question: 'How do I print a clean copy for the wall?',
        answer:
          'Export submissions to CSV and paste into any document, or print your dashboard view directly. Many coordinators run both: digital for convenience, printed for hallway visibility, reconciling weekly from the export.',
      },
    ],
  },

  {
    slug: 'form-submission-notifications',
    title: 'Know Instantly When Someone Submits Your Form',
    description:
      'Set up form submission notifications: instant email alerts, Slack and Discord pings, webhook triggers, and routing rules that reach the right teammate.',
    category: 'Automations',
    readingMinutes: 5,
    intro: [
      'Form notifications close the gap between a submission happening and anyone noticing. Configure email alerts for instant awareness, route urgent submissions to Slack or Discord, and point webhooks at systems that should react automatically. The result: no more discovering leads three days late in a spreadsheet.',
    ],
    sections: [
      { type: 'h2', text: 'Choose alerts by consequence' },
      {
        type: 'table',
        head: ['Submission type', 'Right channel'],
        rows: [
          ['Sales inquiry', 'Instant email plus chat ping'],
          ['Internal request', 'Daily digest mindset'],
          ['Urgent incident report', 'Chat channel with @here culture'],
          ['Routine feedback', 'Dashboard review cadence'],
        ],
      },
      { type: 'h2', text: 'Wiring the common paths' },
      {
        type: 'steps',
        items: [
          {
            title: 'Email alerts first',
            text: 'Every SharaForms form supports notification emails to any address. Include key answers in the body so triage happens from the inbox.',
          },
          {
            title: 'Chat pings for speed',
            text: 'Connect Slack or Discord so submissions hit the channel where your team already works. Urgent forms deserve channels people actually watch.',
          },
          {
            title: 'Webhooks for machinery',
            text: 'Point a webhook at your CRM, ticketing tool, or custom endpoint so submissions trigger workflows without human forwarding.',
          },
          {
            title: 'Route conditionally',
            text: 'Conditional logic can vary recipients by answer: billing issues reach finance, bug reports reach engineering, everything else stays general.',
          },
        ],
      },
      { type: 'h2', text: 'Notification hygiene' },
      {
        type: 'ul',
        items: [
          'Send test submissions after every settings change.',
          'Whitelist sender addresses so alerts dodge spam folders.',
          'Give each form a distinct email subject prefix for inbox filtering.',
          'Audit recipients quarterly; stale addresses silently swallow alerts.',
        ],
      },
    ],
    faqs: [
      {
        question: 'Can different form answers notify different people?',
        answer:
          'Yes. Conditional logic routes notifications by answer values, so department selection or urgency flags decide recipients. Each branch carries its own subject line and content, keeping every alert relevant to whoever receives it.',
      },
      {
        question: 'Will I get notified even if my inbox fails?',
        answer:
          'Submissions always persist safely in your dashboard regardless of delivery, so nothing is lost. Adding a second channel such as Slack provides redundancy for time-critical forms where a missed email has real consequences.',
      },
      {
        question: 'Do notifications include file attachments?',
        answer:
          'Emails reference uploaded files with secure links rather than attaching raw files, protecting deliverability and inbox limits. Recipients click through to retrieve uploads, and dashboard entries retain originals permanently.',
      },
    ],
  },

  {
    slug: 'share-a-form-anywhere',
    title: 'Share a Form Anywhere: Links, QR Codes, Embeds',
    description:
      'Distribute forms everywhere your audience lives: direct links, QR codes for print, website embeds, social bios, and email signatures, with matching tips.',
    category: 'Distribution',
    readingMinutes: 4,
    intro: [
      'A form only collects responses where people can reach it, so distribution decides success more than design does. Direct links suit chats and messages, QR codes bridge physical spaces, embeds turn web pages into collection points, and bio links convert social followers. Match the channel to where attention already is.',
    ],
    sections: [
      { type: 'h2', text: 'Channel cheat sheet' },
      {
        type: 'table',
        head: ['Channel', 'Best practice'],
        rows: [
          ['Direct link', 'Shorten for SMS; preview text earns the tap'],
          ['Printed materials', 'QR code plus one-line promise above it'],
          ['Website page', 'Embed inline where the related content sits'],
          ['Social profiles', 'Link-in-bio pages pointing to your top form'],
          ['Email signature', 'Plain link with action verb, not here'],
        ],
      },
      { type: 'h2', text: 'Matching context to expectation' },
      {
        type: 'p',
        text: 'People complete forms when the ask fits the moment. A feedback QR on a receipt catches customers while impressions are fresh. The same QR on a billboard fails because nobody scans mid-commute. Distribution works when physical effort matches expected payoff.',
      },
      { type: 'h2', text: 'Testing before broadcasting' },
      {
        type: 'ul',
        items: [
          'Open every shared link on both phone and desktop.',
          'Scan printed QR codes from normal viewing distance.',
          'Submit one real test response from each major channel.',
          'Confirm submissions attribute correctly using hidden source fields.',
        ],
      },
      {
        type: 'callout',
        title: 'One link, many doors',
        text: 'Because SharaForms URLs never expire, print materials, old posts, and archived emails keep collecting indefinitely. Build the habit of permanent links early and distribution compounds.',
      },
    ],
    faqs: [
      {
        question: 'Can I embed a form on WordPress or Webflow?',
        answer:
          'Yes. Embed snippets work on any platform accepting custom HTML or script blocks, including WordPress, Webflow, Squarespace, and Ghost. The embedded form inherits your theme styling and submits without navigating visitors away from the page.',
      },
      {
        question: 'Do shared links stop working after events end?',
        answer:
          'No. Links remain active until you close or archive the form, which makes them ideal for evergreen collection points. For finished campaigns, closing the form politely redirects newcomers instead of showing broken pages.',
      },
      {
        question: 'How do QR codes work with my form?',
        answer:
          'Generate any QR encoder pointed at your form URL; scanning opens the form directly in the visitor browser. Print size matters most: codes under two centimeters frustrate phone cameras, while three centimeters and larger scan reliably at arm length.',
      },
    ],
  },
]

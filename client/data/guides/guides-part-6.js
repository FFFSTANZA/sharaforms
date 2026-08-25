// Guides 27-41: task walkthroughs, additional question banks, toolkits,
// migration and compliance explainers. Same copy rules as earlier parts:
// no em dashes, direct answers first, self-contained FAQ responses,
// no recycled filler phrasing, no competitor-comparison intent
// (the comparison pages own that), nothing dependent on A/B testing.
export default [
  {
    slug: 'how-to-create-a-survey',
    title: 'How to Create a Survey in 7 Steps (Free Template)',
    description:
      'Create a survey that produces decisions, not just data: the seven build steps, a question-type cheat sheet, and distribution habits that protect response rates.',
    category: 'Research basics',
    readingMinutes: 7,
    intro: [
      'Creating a survey that people finish and you can act on comes down to seven steps: define the decision, lead with one scored question, add focused drivers, close with an open field, cut everything else, pilot, then ship with a single reminder. This guide walks through each step plus the question-type choices that separate useful surveys from ignored ones.',
    ],
    sections: [
      { type: 'h2', text: 'The seven steps, in order' },
      {
        type: 'ol',
        items: [
          '**Define the one decision** the survey must inform; every question earns its place by feeding it.',
          '**Pick the headline metric**, such as overall satisfaction or likelihood to recommend, and place it first.',
          '**Add three to six driver questions** covering the factors that could realistically move that number.',
          '**Close with one optional open field** so respondents can raise whatever your questions missed.',
          '**Delete every question without a decision attached**; length is the quiet killer of completion.',
          '**Pilot with five colleagues** and rewrite wherever anyone hesitates or asks what a question means.',
          '**Ship to a segmented audience** with one reminder scheduled, then stop asking.',
        ],
      },
      { type: 'h2', text: 'Match question types to what you need to learn' },
      {
        type: 'table',
        headers: ['What you need', 'Question type', 'Example'],
        rows: [
          ['Prioritize improvements', 'Rating scale', 'Rate checkout ease from 1 to 5'],
          ['Choose between options', 'Single choice', 'Which channel do you use most?'],
          ['Understand context', 'Multi-select', 'Which features do you use weekly?'],
          ['Hear the reasoning', 'Open text', 'What almost stopped you from signing up?'],
          ['Track one number over time', 'Scored question', 'How likely are you to recommend us? (0 to 10)'],
        ],
      },
      { type: 'h2', text: 'Build versus duplicate' },
      {
        type: 'p',
        text: 'Starting from a proven survey template inherits tested question order and option structures, so you edit wording instead of designing architecture. Blank canvases invite bloated drafts; templates start lean and force every addition to justify itself against the decision you defined in step one.',
      },
      { type: 'h2', text: 'Distribution habits that protect response rates' },
      {
        type: 'ul',
        items: [
          'Send midweek when inboxes are calmer; Monday competes with pile-up and Friday competes with the weekend.',
          'State the time cost honestly in the invitation, then deliver exactly on it.',
          'Send one reminder after two or three days; repeated chasing burns goodwill faster than it recovers answers.',
          'Share what changed because of the last round; respondents who see impact keep responding.',
        ],
      },
      {
        type: 'callout',
        title: 'The pilot is the survey',
        text: 'Five test responses surface broken wording, missing options, and confusing jumps faster than any planning session. Fixing those problems before launch costs an hour; discovering them after launch costs you the entire dataset and the credibility to ask again.',
      },
    ],
    faqs: [
      {
        question: 'How do I create a survey for free?',
        answer:
          'Duplicate a survey template, replace the placeholder questions with your own, and share the link by email, QR code, or website embed. SharaForms offers unlimited forms and submissions on the free plan, so piloting, iterating, and rerunning surveys never touches a budget or requires software approval.',
      },
      {
        question: 'How many questions should my first survey have?',
        answer:
          'Five to eight questions covers most decisions: one headline metric, three to five drivers, and an optional comment field. That size completes inside three minutes, which protects completion rates, while still producing segments deep enough to base a real decision on.',
      },
      {
        question: 'When is the best time to send a survey?',
        answer:
          'Midweek mornings tend to perform best because inboxes are calmer than Monday and attention outlasts Friday afternoon. More important than the exact hour is consistency: sending each wave at the same time keeps response patterns comparable across months and quarters.',
      },
      {
        question: 'Do I need to offer incentives for survey responses?',
        answer:
          'Small incentives raise response rates but attract some low-effort answers, so match them to survey length. Short feedback surveys usually perform well on goodwill alone, while long research studies justify rewards. Thank every respondent regardless; acknowledgment costs nothing and compounds into future participation.',
      },
    ],
  },
  {
    slug: 'how-to-make-a-registration-form',
    title: 'How to Make a Registration Form That People Finish',
    description:
      'Make a registration form people actually finish: the nine essential fields, conditionals that cut clutter, capacity handling, and confirmations that reduce no-shows.',
    category: 'Events & groups',
    readingMinutes: 6,
    intro: [
      'A good registration form collects exactly what the organizer needs to run the event: who is coming, how many guests they bring, any special requirements, and how to reach them. Nine fields cover almost every event; everything else belongs behind conditional logic so registrants only see questions relevant to their answers. Here is the complete build, from field list to confirmation message.',
    ],
    sections: [
      { type: 'h2', text: 'Fields every registration form needs' },
      {
        type: 'ol',
        items: [
          '**Full name,** split into first and last so sorting and badges stay clean.',
          '**Email address** for confirmations and updates; this is your lifeline when details change.',
          '**Phone number,** optional but valuable for day-of coordination.',
          '**Ticket or session choice** if the event offers formats, tracks, or price tiers.',
          '**Guest count** with a sensible cap so capacity math stays honest.',
          '**Dietary or accessibility needs** framed as optional with a none option included.',
          '**How did you hear about this,** optional, for marketing attribution.',
          '**Consent checkbox** for photos or follow-up communication where your policies require it.',
          '**Anything else we should know,** an optional open field that surfaces edge cases early.',
        ],
      },
      { type: 'h2', text: 'Conditionals that cut clutter' },
      {
        type: 'ul',
        items: [
          'Show meal-choice questions only to attendees who flagged dietary preferences first.',
          'Reveal guest-name fields only when the guest count is greater than zero.',
          'Ask for team or group details only when someone registers as part of one.',
          'Display workshop selections solely for full-weekend ticket holders, not single-day visitors.',
        ],
      },
      { type: 'h2', text: 'Handling capacity without spreadsheets' },
      {
        type: 'p',
        text: 'Decide before launch what happens when spots run out: close the form, open a waitlist, or schedule a second session. A waitlist question added to the same form captures overflow demand automatically, and late submissions become your evidence of interest when booking a bigger venue next time.',
      },
      { type: 'h2', text: 'Confirmation messages that reduce no-shows' },
      {
        type: 'ul',
        items: [
          'Restate the essentials in every confirmation: date, time, location, and what to bring.',
          'Include the details the registrant submitted so mistakes surface while fixes are still cheap.',
          'Add one calendar-ready line they can forward to anyone attending with them.',
        ],
      },
      {
        type: 'callout',
        title: 'Nine required fields maximum',
        text: 'Completion falls with every required field, and most extras can be collected later: shirt sizes at check-in, session picks the week before, parking details in the reminder email. Register people first, enrich gradually, and far more of them make it past the submit button.',
      },
    ],
    faqs: [
      {
        question: 'What fields must a registration form include?',
        answer:
          'Every registration form needs full name, email address, and the specific choices your event depends on, such as ticket type, session, or guest count. Dietary needs, accessibility requests, and consent checkboxes round out the standard set; anything beyond those belongs behind conditional logic or later emails.',
      },
      {
        question: 'Can I cap registrations at a set number?',
        answer:
          'Yes, by watching submissions against your limit and switching the form to a waitlist mode when the cutoff hits. A single waitlist question captures demand beyond capacity automatically, giving you both protection today and a ready-made case for a larger venue next time.',
      },
      {
        question: 'Should registration forms ask for payment upfront?',
        answer:
          'Ask for payment whenever no-shows cost you real money, such as catered events or limited-seat workshops, and keep free registration for community events where friction hurts attendance more than absence does. SharaForms payment forms handle card collection with receipts once you connect processing.',
      },
      {
        question: 'How do I collect guest information too?',
        answer:
          'Use a numeric guest-count field followed by conditional name fields revealed only when the count exceeds zero. This keeps solo registrants at maximum speed while still capturing who is attending with them, which check-in desks and catering orders both depend on.',
      },
    ],
  },
  {
    slug: 'how-to-create-an-order-form',
    title: 'How to Create an Order Form With Calculated Totals',
    description:
      'Create an order form with live calculated totals: product structure, quantity pricing formulas, conditional surcharges, and the fulfillment flow after each submission.',
    category: 'Logic & calculations',
    readingMinutes: 7,
    intro: [
      'A modern order form lists products with prices, multiplies quantities automatically, applies surcharges through simple if-this-then-price rules, and shows customers a live total before they submit. You need no spreadsheet and no checkout page to start; the structure below works for apparel, food, print, and made-to-order goods alike.',
    ],
    sections: [
      { type: 'h2', text: 'Structure that converts browsers into buyers' },
      {
        type: 'ol',
        items: [
          '**Product selection:** one dropdown or checkbox row per item with its base price stated beside it.',
          '**Quantity fields** for each product, defaulting sensibly rather than forcing zeros.',
          '**Variant logic:** size, color, or format options that appear only for the product being ordered.',
          '**Live total block:** a computed line that updates as the customer types, not after submission.',
          '**Delivery section** shown only when physical shipping applies.',
          '**Contact and payment details** collected last, right before the buy moment.',
        ],
      },
      { type: 'h2', text: 'Pricing patterns you can copy' },
      {
        type: 'table',
        headers: ['Pattern', 'Formula shape', 'Example'],
        rows: [
          ['Flat unit price', '{price} * {quantity}', 'Mugs at $12 times quantity ordered'],
          ['Size surcharge', 'IF({size} = "XXL", base + fee, base) * qty', '$15 shirts, $17 for XXL'],
          ['Tiered discount', 'IF({qty} >= 24, rate * 0.9, rate) * {qty}', 'Ten percent off two dozen units'],
          ['Add-on extras', 'base * qty + IF({gift}, wrap_fee, 0)', 'Gift wrapping added per order'],
        ],
      },
      { type: 'h2', text: 'Conditional fields that prevent fulfillment errors' },
      {
        type: 'ul',
        items: [
          'Require the shipping address block only when a physical product sits in the cart.',
          'Show engraving or personalization inputs solely for items that support them.',
          'Surface pickup-time slots exclusively for local delivery options.',
          'Ask for artwork uploads only when custom printing was selected.',
        ],
      },
      { type: 'h2', text: 'After submission: the fulfillment loop' },
      {
        type: 'ul',
        items: [
          'Route instant notifications to whoever packs boxes so production starts immediately.',
          'Export submissions to CSV sorted by product for batch picking and invoicing.',
          'Generate a PDF receipt per order so customers hold a clean record of what they bought.',
        ],
      },
      {
        type: 'callout',
        title: 'Show the money early',
        text: 'Order forms stall when totals stay hidden until the end. A visible running total reassures buyers that quantities registered correctly and removes the last-second price shock that quietly abandons carts, especially on phones where scrolling back is painful.',
      },
    ],
    faqs: [
      {
        question: 'Can an order form calculate totals automatically?',
        answer:
          'Yes. Formula-driven fields multiply unit prices by quantities, apply surcharges through if-then rules, and render the result in a live total that updates while the customer types. SharaForms supports these calculations natively, so totals stay accurate without spreadsheets or manual arithmetic.',
      },
      {
        question: 'How do I accept payments through an order form?',
        answer:
          'Build the order with calculated totals first, then connect a payment integration such as Stripe so customers pay the computed amount on submission. Until then, the same form works as a quote-and-confirm flow: orders arrive with totals, and you invoice manually while volumes stay low.',
      },
      {
        question: 'How do I handle discounts without coupon codes?',
        answer:
          'Encode tiered pricing directly in the formula: apply a reduced rate when quantity crosses your threshold, or add a percentage-off rule for bulk rows. Visible quantity pricing often outperforms hidden coupons anyway, because buyers can see the reward for ordering more.',
      },
      {
        question: 'Do customers get a receipt after ordering?',
        answer:
          'They can. Generate a PDF document from each submission containing the ordered items, quantities, and final total, then send it alongside your confirmation email. Customers keep an unambiguous record of every purchase, and your team stops re-typing order details into invoice templates at month end.',
      },
    ],
  },
  {
    slug: 'migrate-from-google-forms',
    title: 'How to Migrate From Google Forms Without Losing Data',
    description:
      'Move off Google Forms step by step: import existing forms, see what transfers and what needs rebuilding, and switch respondents over without losing history.',
    category: 'Migrations',
    readingMinutes: 6,
    intro: [
      'Migrating from Google Forms takes minutes per form: import the existing form, rebuild the few advanced pieces natively, then swap links wherever respondents arrive from. Your Google response history stays archived in Sheets exactly where it is; migration copies structure, not data, so nothing breaks on the old side while you move.',
    ],
    sections: [
      { type: 'h2', text: 'What imports and what needs a rebuild' },
      {
        type: 'table',
        headers: ['Element', 'Transfers on import?', 'Notes'],
        rows: [
          ['Questions and titles', 'Yes', 'Text carries over cleanly'],
          ['Multiple choice and checkboxes', 'Yes', 'Options map to native choice fields'],
          ['Dropdowns and short answers', 'Yes', 'Become selects and text inputs'],
          ['Section breaks', 'Partly', 'Recreate as pages or blocks where needed'],
          ['Conditional branching', 'No', 'Rebuild with visual logic rules'],
          ['Calculated scores or totals', 'No', 'Rebuild as formula variables'],
          ['Response history', 'No', 'Stays archived in your Google Sheets export'],
        ],
      },
      { type: 'h2', text: 'Importing a form, step by step' },
      {
        type: 'ol',
        items: [
          'Open the form builder and choose the Google Forms import option.',
          'Pick the form from your Drive when the picker opens; imported structure appears in the canvas.',
          'Review each field, since occasional types land as plain text and deserve upgrading to their proper control.',
          'Set your theme, notification routing, and captcha preference before sharing anything.',
        ],
      },
      { type: 'h2', text: 'Worth rebuilding while you are here' },
      {
        type: 'ul',
        items: [
          'Conditional logic: show or require fields based on earlier answers instead of paging everyone through everything.',
          'Calculated totals: quote-style forms and scored quizzes compute results live rather than in a linked Sheet.',
          'Presentation modes: one-question-at-a-time flows lift completion on longer intake forms.',
          'Signature fields: agreements close inside the form instead of a separate signing tool.',
        ],
      },
      { type: 'h2', text: 'Switching respondents over safely' },
      {
        type: 'ul',
        items: [
          'Update website embeds and QR codes first; they carry steady traffic you cannot chase individually.',
          'Keep the original Google form open in view-only fashion if latecomers need to finish in-flight responses.',
          'Export historical responses from Google Sheets and store the archive wherever retention policy keeps records.',
        ],
      },
      {
        type: 'callout',
        title: 'Migrate your highest-friction form first',
        text: 'Start with the form whose current limits annoy you weekly: the quiz graded by hand, the order form totaled in a Sheet, the intake buried in email chains. One visible win builds momentum for moving the rest of the catalog gradually.',
      },
    ],
    faqs: [
      {
        question: 'Is migrating away from Google Forms free?',
        answer:
          'Yes. SharaForms provides unlimited forms and submissions on the free plan, so importing and rebuilding costs nothing. Paid plans exist for teams that want branding removal, workspaces, and collaboration, but a straightforward one-person migration fits comfortably inside the free tier without any card on file.',
      },
      {
        question: 'Will my old responses transfer during import?',
        answer:
          'Import copies the form structure, while response history stays in your Google account. Export the existing sheet to CSV and archive it under your usual retention policy; new submissions then accumulate in SharaForms from switchover day onward without touching the old records.',
      },
      {
        question: 'Which question types survive the move?',
        answer:
          'Text fields, paragraphs, multiple choice, checkboxes, and dropdowns transfer directly and land as editable native fields. Section headers import as text blocks, while branching logic and calculations need rebuilding, which usually takes minutes using the visual rule builder rather than any custom code.',
      },
      {
        question: 'Can I run both platforms during the transition?',
        answer:
          'Yes, and running them in parallel for a couple of weeks is the safest pattern. Point all new traffic at the migrated form, let stragglers finish any process already underway elsewhere, then retire the original once submissions naturally taper off.',
      },
    ],
  },
  {
    slug: 'net-promoter-score-questions',
    title: 'Net Promoter Score Questions, Follow-Ups, and Scoring',
    description:
      'Run NPS properly: the exact score question wording, the scoring math, driver follow-ups that explain movement, and when to use relationship versus transactional NPS.',
    category: 'Question banks',
    readingMinutes: 5,
    intro: [
      'Net Promoter Score runs on one question, how likely are you to recommend us from 0 to 10, plus one reason field that explains every point of movement. The extended bank below adds driver follow-ups that turn a bare number into a to-do list, along with the scoring math and the relationship-versus-transactional decision most teams skip.',
    ],
    sections: [
      { type: 'h2', text: 'The core two-question NPS' },
      {
        type: 'ul',
        items: [
          '**Score:** How likely are you to recommend us to a friend or colleague? (0 = not at all likely, 10 = extremely likely)',
          '**Reason:** What is the main reason for your score? (open text, kept short deliberately)',
        ],
      },
      { type: 'h2', text: 'Scoring, refreshed in four lines' },
      {
        type: 'ol',
        items: [
          'Count promoters: everyone answering 9 or 10.',
          'Count detractors: everyone answering 0 through 6.',
          'Ignore passives: 7s and 8s count toward neither side.',
          'Subtract the detractor percentage from the promoter percentage; the gap is your NPS.',
        ],
      },
      { type: 'h2', text: 'Driver follow-ups that explain the number' },
      {
        type: 'ul',
        items: [
          'Which single improvement would most increase your score? (asked when the score lands below 9)',
          'What do we currently do that you would miss most? (asked of promoters, so strengths get protected)',
          'How easy was it to get help when you needed it? (support driver, rated 1 to 5)',
          'How fair do our prices feel for the value received? (value driver, rated 1 to 5)',
        ],
      },
      { type: 'h2', text: 'Relationship versus transactional NPS' },
      {
        type: 'table',
        headers: ['Flavor', 'Asks about', 'Cadence', 'Best for'],
        rows: [
          ['Relationship NPS', 'The brand overall', 'Twice a year', 'Board metrics and long-run trends'],
          ['Transactional NPS', 'One interaction or purchase', 'Days after each touchpoint', 'Fixing specific journeys fast'],
        ],
      },
      {
        type: 'callout',
        title: 'Always capture the why',
        text: 'A score without its reason tells you that something moved but never what. The open reason field costs respondents ten seconds and hands your team verbatim language for roadmaps, apology emails, and marketing copy, making it the highest-value ten seconds in customer research.',
      },
    ],
    faqs: [
      {
        question: 'What is a good Net Promoter Score?',
        answer:
          'Any positive score means promoters outnumber detractors, which is healthy; sustained scores above roughly thirty are considered strong across many industries. Benchmarks vary widely by sector though, so track your own trend line quarter over quarter and compare against industry data only second.',
      },
      {
        question: 'How often should we measure NPS?',
        answer:
          'Relationship NPS suits twice-yearly measurement so the same customers are not over-surveyed, while transactional NPS rides individual interactions like purchases or support tickets. Running both simultaneously is common practice: relationship NPS steers overall strategy, while transactional NPS tunes specific journeys week to week.',
      },
      {
        question: 'Does the 0 to 10 scale wording matter?',
        answer:
          'Keep the standard eleven-point scale with anchored ends, because comparability with published benchmarks depends on it. Changing the range, dropping points, or reordering labels breaks the classification math separating promoters and detractors and invalidates trend comparisons across every wave you have run.',
      },
      {
        question: 'Should passives be chased or ignored?',
        answer:
          'Ignore them in the arithmetic, since the formula excludes 7s and 8s by design, but read their comments carefully. Passives sit one fixable annoyance away from promotion, and their written reasons frequently name the exact friction worth removing next quarter.',
      },
    ],
  },
  {
    slug: 'exit-interview-questions',
    title: '30 Exit Interview Questions That Surface Real Reasons',
    description:
      'Copy 30 exit interview questions separating push from pull: core reasons, manager dynamics, growth gaps, and forward-looking prompts, plus confidentiality rules.',
    category: 'Question banks',
    readingMinutes: 6,
    intro: [
      'Exit interviews reveal patterns only when questions separate why someone leaves from how the job felt from what would have kept them. The thirty questions below split along those lines and add the confidentiality practices that make departing employees candid instead of polite, so every departure funds one concrete improvement.',
    ],
    sections: [
      { type: 'h2', text: 'Core reason questions' },
      {
        type: 'ul',
        items: [
          'What put you in the market for a new role in the first place?',
          'Which factor ultimately tipped the decision: pay, growth, manager, workload, or something else?',
          'Was there a specific moment when staying stopped feeling realistic?',
          'Did anything ever nearly convince you to stay? What was it?',
        ],
      },
      { type: 'h2', text: 'Manager and team dynamics' },
      {
        type: 'ul',
        items: [
          'How well did your manager understand the obstacles in your day-to-day work?',
          'Did you receive useful feedback often enough to improve while it still mattered?',
          'Who on the team made your work better, and how?',
          'What would your closest teammates say this team tolerates that it should not?',
        ],
      },
      { type: 'h2', text: 'Role design and growth' },
      {
        type: 'ul',
        items: [
          'Which parts of the role matched what the hiring process promised, and which did not?',
          'Where did the job ask for more than one person could sustainably deliver?',
          'What skill did you build here that you value most?',
          'If the company offered one role change tomorrow, what would have kept you?',
        ],
      },
      { type: 'h2', text: 'Forward-looking prompts' },
      {
        type: 'ul',
        items: [
          'What should we change first for the person who takes this seat next?',
          'Which recurring meeting or report deserved to die, and nobody killed it?',
          'Is there anything you were reluctant to raise while employed that matters now?',
          'Would you recommend working here to a friend, honestly? Why or why not?',
        ],
      },
      { type: 'h2', text: 'Running exits so people answer honestly' },
      {
        type: 'ol',
        items: [
          'Schedule during the final week, voluntary by default; pressured exits produce flattery, not signal.',
          'Have HR or a neutral party conduct it, never the departing employee\'s direct manager.',
          'Offer a written form as the alternative for people who will not speak live.',
          'Report themes upward in aggregate only, with small-team results suppressed like any sensitive dataset.',
        ],
      },
      {
        type: 'callout',
        title: 'One improvement per departure',
        text: 'Exit data compounds when each interview ends with a single named change routed to an owner. Twelve departures yielding twelve real fixes rebuild trust faster than any retention slogan, because remaining employees watch whether leaving honestly changed anything.',
      },
    ],
    faqs: [
      {
        question: 'When should an exit interview take place?',
        answer:
          'During the final week of employment, once notice obligations are settled but memories remain fresh. Earlier feels premature while the person is still deciding, and post-departure interviews rarely happen at all. Keep participation voluntary and low-pressure so answers carry genuine weight.',
      },
      {
        question: 'Who should conduct the exit interview?',
        answer:
          'Someone outside the reporting line, typically HR or a people operations partner. Departing employees soften criticism in front of their own manager, which defeats the purpose of holding the conversation at all. The interviewer should promise aggregate-only reporting and then demonstrably honor it.',
      },
      {
        question: 'Are exit interviews confidential?',
        answer:
          'They should be reported thematically rather than quoted individually. State upfront that themes reach leadership without names attached, suppress anything identifiable from small teams, and never let verbatim quotes circulate; one leaked comment poisons the candor of every future departure.',
      },
      {
        question: 'What if a departing employee declines the interview?',
        answer:
          'Respect the decline gracefully and offer a short written form they can complete anonymously if they prefer. Some of the most candid feedback arrives through exactly that channel, and forcing live conversation guarantees politeness instead of the honest truth you actually need.',
      },
    ],
  },
  {
    slug: 'demographics-survey-questions',
    title: 'Demographic Survey Questions to Ask (and to Skip)',
    description:
      'Copy respectful demographic survey questions for age, gender, location, education, and income, with inclusive wording examples and rules for keeping them optional.',
    category: 'Question banks',
    readingMinutes: 6,
    intro: [
      'Demographic questions earn their place only when the analysis needs the split: segmenting satisfaction scores, checking representation, or targeting follow-ups. The bank below covers the standard fields with inclusive wording, and closes with the rules that keep these questions from inflating drop-off, because every unnecessary demographic question costs completions.',
    ],
    sections: [
      { type: 'h2', text: 'Standard fields with suggested options' },
      {
        type: 'table',
        headers: ['Field', 'Suggested options', 'Ask when'],
        rows: [
          ['Age', 'Under 18 / 18-24 / 25-34 / 35-44 / 45-54 / 55-64 / 65+ / Prefer not to say', 'Generational differences matter'],
          ['Gender', 'Woman / Man / Non-binary / Prefer to self-describe / Prefer not to say', 'Representation or segmentation is in scope'],
          ['Location', 'Country dropdown, region or state second', 'Results feed regional decisions'],
          ['Education', 'High school through postgraduate bands', 'Research studies and program fit'],
          ['Household size', 'Numeric ranges, never exact counts', 'Benefits or pricing research'],
        ],
      },
      { type: 'h2', text: 'Wording details that respect respondents' },
      {
        type: 'ul',
        items: [
          'Use age bands instead of birthdates; precision invites privacy worry without improving analysis.',
          'Offer self-description alongside fixed categories wherever identity questions appear.',
          'Place prefer not to say on every demographic item, always visible, never hidden in a menu.',
          'Avoid combining two attributes in one question, such as employment status and student status.',
        ],
      },
      { type: 'h2', text: 'Income and other high-friction fields' },
      {
        type: 'p',
        text: 'Income questions trigger the highest refusal rates in survey research, so request ranges rather than figures, justify the field inside the question itself, and consider dropping it entirely unless pricing or benefits analysis genuinely depends on it. The same restraint applies to ethnicity, religion, and political questions: collect them only with a stated purpose and safe handling story.',
      },
      { type: 'h2', text: 'Rules for asking sensitive demographics' },
      {
        type: 'ol',
        items: [
          'Default every demographic field to optional unless a funded requirement says otherwise.',
          'State why each question exists in one clause beside the question itself.',
          'Position demographics at the end so early completion momentum survives.',
          'Report small segments as aggregates and never cross-tabulate down to identifiable cells.',
        ],
      },
      {
        type: 'callout',
        title: 'Every demographic field must earn its place',
        text: 'Before adding any personal attribute question, write down the decision its answers will inform. If no decision exists, delete the field; if one does, keep the wording minimal, the options inclusive, and the whole block at the end of the instrument.',
      },
    ],
    faqs: [
      {
        question: 'Are demographic questions really necessary in surveys?',
        answer:
          'Only when results must be compared across groups or checked for balance. A product satisfaction pulse may need nothing beyond role and tenure, while program evaluation often requires age and location. Adding demographics without an analytical plan just raises drop-off and refusal rates.',
      },
      {
        question: 'How should I word the gender question inclusively?',
        answer:
          'List Woman, Man, and Non-binary, then add Prefer to self-describe with a blank line and always close with Prefer not to say. This structure includes respondents beyond the binary without forcing disclosure, and it matches practice across major research bodies.',
      },
      {
        question: 'Should demographic questions be required?',
        answer:
          'Keep them optional by default. Required demographics depress completion among exactly the respondents whose inclusion matters most, and forced disclosure creates both ethical problems and legal exposure in some jurisdictions. Require a demographic only under explicit regulatory or funding mandates.',
      },
      {
        question: 'Where in the survey should demographics go?',
        answer:
          'At the end, after substantive questions but before the thank-you note. Early demographic walls cause abandonment before opinions get collected; placing them last means even partial refusals leave your core findings intact and usable for the original decision you set out to inform.',
      },
    ],
  },
  {
    slug: 'job-application-questions',
    title: '25 Job Application Screening Questions Worth Asking',
    description:
      'Copy 25 job application screening questions covering eligibility, skills, motivation, and logistics, with fairness rules and the questions to legally avoid.',
    category: 'Question banks',
    readingMinutes: 7,
    intro: [
      'Strong application forms ask structured screening questions so candidates compare fairly and recruiters stop drowning in unstructured cover letters. The twenty-five questions below cover eligibility, experience, motivation, and logistics, followed by the fairness mechanics that keep scoring consistent and the topics that belong nowhere near an application.',
    ],
    sections: [
      { type: 'h2', text: 'Eligibility and logistics screeners' },
      {
        type: 'ul',
        items: [
          'Are you legally authorized to work in the location of this role?',
          'When could you start, assuming a standard notice period?',
          'Does this position\'s schedule fit your current commitments?',
          'Are you able to commute to the workplace as described?',
          'What salary range do you expect for this role?',
        ],
      },
      { type: 'h2', text: 'Experience and skills probes' },
      {
        type: 'ul',
        items: [
          'Describe the project from your last role that best matches our core work.',
          'Which tools or systems from the job description have you used professionally?',
          'Rate your proficiency in the primary skill for this position, and give one sentence of evidence.',
          'Tell us about a deadline you saved late. What specifically did you do?',
          'Share a piece of work you are proud of and link it if possible.',
        ],
      },
      { type: 'h2', text: 'Motivation questions that reveal fit' },
      {
        type: 'ul',
        items: [
          'Why this role at our company specifically, in three sentences or fewer?',
          'What does an excellent version of this job look like to you?',
          'Which part of the described role would you learn fastest?',
          'Where do you want the next two years to take your skills?',
          'What kind of management brings out your best work?',
        ],
      },
      { type: 'h2', text: 'Structuring for fair screening' },
      {
        type: 'ol',
        items: [
          'Ask every candidate the identical set; ad hoc questions create ad hoc comparisons.',
          'Score answers against a written rubric before reading names or resumes.',
          'Keep the application under ten minutes total, marking essays optional.',
          'Record scores inside submissions so decisions survive audit questions later.',
        ],
      },
      { type: 'h2', text: 'Topics that do not belong on applications' },
      {
        type: 'ul',
        items: [
          'Age, date of birth, or graduation years used to infer age.',
          'Family status, pregnancy, or childcare arrangements in any framing.',
          'Religion, national origin, or citizenship details beyond work authorization.',
          'Health conditions or disability status unrelated to essential job functions.',
        ],
      },
      {
        type: 'callout',
        title: 'Ask only what predicts performance',
        text: 'Every screening question should map to a capability or requirement named in the job posting. Questions failing that test invite bias into the funnel and legal risk onto the company, while contributing nothing that better-designed probes already capture.',
      },
    ],
    faqs: [
      {
        question: 'What questions are illegal to ask on job applications?',
        answer:
          'Questions touching protected characteristics such as age, religion, family plans, disability, or national origin are restricted or prohibited in many jurisdictions, though exact rules vary by country and region. When unsure, consult local employment counsel and default to job-related questions only.',
      },
      {
        question: 'How long should a job application take to complete?',
        answer:
          'Aim for ten minutes or less: contact details, eligibility screeners, resume upload, and three to five structured questions. Applications stretching past twenty minutes lose strong passive candidates who refuse to jump through hoops before knowing whether the role itself is genuinely worth pursuing.',
      },
      {
        question: 'Should cover letters be required?',
        answer:
          'Make them optional. Required letters inflate completion time and filter for writing confidence rather than job ability, while optional ones still surface motivated applicants who choose to write. Structured screening questions deliver comparable evidence far more fairly across the whole candidate pool.',
      },
      {
        question: 'Can I ask about salary expectations?',
        answer:
          'It remains common, but several regions now restrict salary history inquiries and increasingly expect posted ranges instead. Publishing the band upfront and asking candidates to confirm it fits usually produces cleaner conversations than open-ended questions, with far less legal exposure attached.',
      },
    ],
  },
  {
    slug: 'patient-intake-questions',
    title: 'Patient Intake Form Questions Every Practice Should Ask',
    description:
      'Build patient intake forms that save chair time: medical history essentials, medication fields, lifestyle screens, consent language, and privacy handling done right.',
    category: 'Question banks',
    readingMinutes: 7,
    intro: [
      'Patient intake works best when paperwork finishes before arrival, so appointments start with care instead of clipboards. The question set below covers contact basics, medical history, medications, allergies, and lifestyle screens, followed by the consent language and privacy handling that health information demands regardless of practice size.',
    ],
    sections: [
      { type: 'h2', text: 'Contact and logistics essentials' },
      {
        type: 'ul',
        items: [
          'Full legal name plus preferred name, since charts and greetings should both be right.',
          'Date of birth and contact number for identity matching and reminders.',
          'Emergency contact with relationship noted.',
          'Insurance details or self-pay confirmation where applicable.',
          'Preferred appointment channel: phone, message, or portal.',
        ],
      },
      { type: 'h2', text: 'Medical history essentials' },
      {
        type: 'ul',
        items: [
          'Current diagnoses or chronic conditions, with a searchable checkbox list plus free-text space.',
          'Past surgeries or hospitalizations with approximate years.',
          'Current prescriptions, including doses when known.',
          'Over-the-counter medicines and supplements taken regularly.',
          'Known allergies, especially medications, latex, and contrast agents.',
        ],
      },
      { type: 'h2', text: 'Lifestyle and screening questions' },
      {
        type: 'ul',
        items: [
          'Do you smoke, vape, or use nicotine products?',
          'How many alcoholic drinks do you have in a typical week?',
          'Any changes in sleep, appetite, or mood over recent months?',
          'Level of physical activity in an ordinary week.',
          'Anything about your health you want the clinician to know before the visit?',
        ],
      },
      { type: 'h2', text: 'Consent and privacy handling' },
      {
        type: 'ol',
        items: [
          'Collect treatment consent and privacy acknowledgment as separate signed items with timestamps.',
          'Explain who accesses responses and for what purpose in plain language beside the signature box.',
          'Limit form access to staff involved in care; front desk needs insurance facts, not therapy notes.',
          'Set a retention period aligned with your record-keeping obligations and purge on schedule.',
          'Route submission alerts to a monitored clinical inbox rather than a general info address.',
        ],
      },
      {
        type: 'callout',
        title: 'Front-desk time saved is care time gained',
        text: 'Every intake question answered at home is a question the receptionist never types and the clinician never chases. Digital intake routinely turns fifteen minutes of lobby paperwork into a two-minute review, which is capacity you can spend on patients instead.',
      },
    ],
    faqs: [
      {
        question: 'What should a patient intake form include?',
        answer:
          'Contact and insurance basics, current conditions, medications with doses, allergies, relevant lifestyle screens, and signed consent for treatment plus privacy acknowledgment. Specialty practices add focused modules such as orthopedic injury histories or dental concerns, but the core set stays constant.',
      },
      {
        question: 'How long should completing intake take?',
        answer:
          'Plan ten to fifteen minutes for new patients and under five for returning ones reviewing updates. Mobile-friendly forms let patients finish on the bus rather than in the waiting room, and pre-visit completion is precisely what frees chair time for actual care.',
      },
      {
        question: 'Can patients safely complete intake forms online?',
        answer:
          'Yes, when the platform collects data securely, limits access to care-related staff, and retains records per your policies. Confirm your compliance obligations with your regulator or compliance officer, and choose tools that support those requirements rather than working around them.',
      },
      {
        question: 'How often should returning patients update their intake?',
        answer:
          'Request a light annual refresh covering medications, allergies, and any condition changes, with full re-intake reserved for genuinely new problems or unusually long lapses between visits. Short update forms protect record accuracy without punishing loyal patients with repetitive paperwork at every appointment.',
      },
    ],
  },
  {
    slug: 'anonymous-surveys',
    title: 'How to Make an Anonymous Survey People Actually Trust',
    description:
      'Make a survey truly anonymous: remove identifiers, write honesty-building anonymity statements, handle the follow-up problem, and pick formats where anonymity pays.',
    category: 'Research basics',
    readingMinutes: 5,
    intro: [
      'An anonymous survey collects no names, emails, or account links, and proves it through behavior rather than promises: separate sharing links instead of logins, no identifier fields, and plain statements of what is and is not recorded. Done properly, anonymity measurably increases candor; done sloppily, one broken promise poisons every future survey.',
    ],
    sections: [
      { type: 'h2', text: 'What makes a survey genuinely anonymous' },
      {
        type: 'ol',
        items: [
          'Share one open link rather than personalized invitations tied to recipients.',
          'Remove name, email, and employee-ID fields from the form entirely.',
          'Turn off any response tracking that ties submissions to sessions or devices.',
          'Say exactly what gets stored, in one honest sentence near the top.',
          'Suppress any slice where fewer than five responses could identify someone.',
        ],
      },
      { type: 'h2', text: 'Anonymity statements that build trust' },
      {
        type: 'ul',
        items: [
          '**Strong:** This survey collects no names, emails, or identifying details. Results are reported only as group totals of ten or more.',
          '**Weak:** Your responses are anonymous.* (*unless you mention identifying details, which many free-text answers inevitably do).',
          '**Honest middle:** Responses are anonymous unless you choose to leave your contact in the optional final field.',
        ],
      },
      { type: 'h2', text: 'The follow-up problem and how to solve it' },
      {
        type: 'p',
        text: 'Anonymity cuts both ways: serious disclosures cannot be chased, and distressed respondents cannot be helped. Solve this with an optional contact field framed explicitly, something like leave your email only if you would like someone to follow up, so anonymity remains the default while help has a door in. Most respondents stay anonymous; the few who opt in get support.',
      },
      { type: 'h2', text: 'Formats where anonymity pays off most' },
      {
        type: 'ul',
        items: [
          'Engagement pulses measuring manager and culture sentiment, where hierarchy silences honesty.',
          'Suggestion boxes collecting ideas people hesitate to attach names to.',
          'Post-exit feedback gathered after departure formalities conclude.',
          'Sensitive service feedback, including healthcare or complaints channels.',
        ],
      },
      {
        type: 'callout',
        title: 'Promised anonymity must be delivered anonymity',
        text: 'Never collect identifying fields quietly and promise anonymity loudly; respondents check. One contradiction between the statement and the form erases candor immediately and lingers across future surveys, because trust in research travels by word of mouth faster than any correction.',
      },
    ],
    faqs: [
      {
        question: 'How do I make a survey anonymous in practice?',
        answer:
          'Distribute a single open link instead of tracked invitations, strip all identifier fields from the form, disable session-linked tracking, and state plainly what is collected. Report only group-level results, suppressing any segment small enough to identify individuals within it, with no exceptions made.',
      },
      {
        question: 'Are anonymous surveys completely untraceable?',
        answer:
          'Be precise rather than absolute: platforms may record technical metadata such as timestamps, and small groups can expose identities through cross-tabulation. True anonymity comes from collecting no identifiers and reporting aggregates only, not from hoping nobody ever reconstructs who said what afterward.',
      },
      {
        question: 'Should employee engagement surveys be anonymous?',
        answer:
          'Yes, particularly for manager and culture questions, because hierarchy pressures distort attributed answers. Make team selection optional, suppress results from groups smaller than five, and publish actions taken; anonymity combined with visible outcomes is what sustains honest participation cycle after cycle.',
      },
      {
        question: 'Can I follow up with anonymous respondents?',
        answer:
          'Not automatically, by definition. Offer an explicitly optional contact field for respondents who want dialogue, and route crisis-flagged disclosures to a human review process. Everyone else stays anonymous, which is precisely the trade respondents agreed to when they chose to begin answering.',
      },
    ],
  },
  {
    slug: 'how-to-increase-form-completion-rates',
    title: 'How to Increase Form Completion Rates: 12 Fixes',
    description:
      'Increase form completion rates without tricks: cut fields honestly, fix mobile friction, set expectations up front, and write error messages that rescue instead of scold.',
    category: 'Form design',
    readingMinutes: 7,
    intro: [
      'Form completion rises through subtraction, not persuasion: fewer required fields, faster mobile rendering, visible progress on multi-step forms, and error messages that help people recover instead of restarting. The twelve fixes below are ranked by typical impact and need no redesign budget; most ship in an afternoon of edits.',
    ],
    sections: [
      { type: 'h2', text: 'Subtract before you optimize' },
      {
        type: 'ul',
        items: [
          'Delete every field whose answer changes no downstream decision; each removed field lifts completion.',
          'Downgrade nice-to-know fields from required to optional, or move them into post-submission emails.',
          'Replace free-text inputs with dropdowns where a fixed option list serves both sides better.',
          'Merge first name and last name only when your exports never sort by surname anyway.',
        ],
      },
      { type: 'h2', text: 'Fix mobile friction first' },
      {
        type: 'ol',
        items: [
          'Stack fields in a single column; side-by-side layouts shrink tap targets below thumb size.',
          'Use the right keyboard per field: numeric for phones and quantities, email layout for addresses.',
          'Keep primary buttons full width at the thumb zone, never floating beside content.',
          'Test on a real phone over mobile data, not just a resized desktop window.',
        ],
      },
      { type: 'h2', text: 'Set honest expectations early' },
      {
        type: 'ul',
        items: [
          'State the time cost above the first field, then honor it; a promised two minutes must finish in two.',
          'Show step count and a progress bar on any multi-page or one-question-at-a-time flow.',
          'Explain why sensitive fields exist in one clause, such as phone number is used only for delivery updates.',
        ],
      },
      { type: 'h2', text: 'Write errors that rescue' },
      {
        type: 'ul',
        items: [
          'Validate inline as people leave a field, not once at submission when context is lost.',
          'Say precisely what broke and how to fix it: Enter a date like 2026-09-01 beats Invalid input.',
          'Preserve everything already typed; forcing re-entry after one mistake loses more than the mistaken field.',
        ],
      },
      { type: 'h2', text: 'Match presentation to length' },
      {
        type: 'p',
        text: 'Short forms belong on single pages where everything is visible at once. Long intake flows complete better one question at a time or section by section, because focus modes hide the intimidating wall of fields that causes tab-closing. If completion sagged after growth, try splitting before rewriting anything.',
      },
      {
        type: 'callout',
        title: 'Deletion is the fastest lift available',
        text: 'Before A/B-free optimization folklore: print every field, cross out any you cannot defend, and ship the smaller form. Teams routinely remove a quarter of their fields this way and watch completion climb the same week, with zero design work involved.',
      },
    ],
    faqs: [
      {
        question: 'What is a good form completion rate?',
        answer:
          'It depends heavily on length and intent: short newsletter signups often convert at multiples of long loan-style applications. Benchmark against your own history rather than published averages, track the trend monthly, and treat any sudden drop as a signal something broke rather than a mystery.',
      },
      {
        question: 'Why do forms lose so many mobile users?',
        answer:
          'Small tap targets, sideways-scrolling multi-column layouts, wrong keyboards, and slow-loading scripts punish thumbs. Single-column stacking, correct input types, and full-width buttons recover most of that loss, which is why mobile fixes rank well ahead of copy changes in priority order.',
      },
      {
        question: 'How many form fields are too many?',
        answer:
          'More than roughly seven visible fields measurably strains voluntary completions, though intent matters more than counts: people tolerate twenty fields for something they applied for and five for a newsletter. Keep required fields under ten and push enrichment into later touchpoints.',
      },
      {
        question: 'Do progress bars actually improve completion?',
        answer:
          'On multi-step forms they reliably help, because respondents can see the remaining effort and decide to continue with full information. On single-page forms they add nothing, since the whole task sits visibly in view and there is nothing left to forecast.',
      },
    ],
  },
  {
    slug: 'new-hire-paperwork-checklist',
    title: 'New Hire Paperwork Checklist: What to Collect Before Day One',
    description:
      'A new hire paperwork checklist covering identity and payroll details, role agreements, equipment access, and first-week follow-through, all collectible digitally.',
    category: 'Toolkits',
    readingMinutes: 6,
    intro: [
      'New hire paperwork splits into three buckets collected best before day one: identity and payroll details that get people paid, role agreements that set expectations, and equipment or access requests that make work possible. This checklist walks each bucket in order and adds the follow-through that keeps week one from dissolving into admin.',
    ],
    sections: [
      { type: 'h2', text: 'Bucket one: identity and payroll' },
      {
        type: 'ol',
        items: [
          '**Personal details:** legal name, address, date available to start, emergency contact.',
          '**Identity documents** required by your jurisdiction for employment verification, uploaded securely.',
          '**Direct deposit authorization:** account details with a void-check or bank-letter upload attached.',
          '**Tax withholding forms** per local requirements, signed and timestamped digitally.',
        ],
      },
      { type: 'h2', text: 'Bucket two: agreements and policies' },
      {
        type: 'ol',
        items: [
          '**Offer acceptance** confirming role, compensation, and start date in one signed record.',
          '**Telecommuting agreement** where remote work applies: workspace attestation, schedule, equipment responsibilities.',
          '**Handbook acknowledgment** captured as consent with signature, so nobody argues about reading it later.',
          '**Confidentiality acknowledgment** tailored to what the role actually touches.',
        ],
      },
      { type: 'h2', text: 'Bucket three: equipment and access' },
      {
        type: 'ol',
        items: [
          '**Equipment checkout** listing laptop, peripherals, and accessories with condition noted.',
          '**IT access request** bundling accounts, tools, and permission levels per role template.',
          '**Shipping confirmation** for remote hires so laptops land before onboarding calls do.',
        ],
      },
      { type: 'h2', text: 'Week-one follow-through' },
      {
        type: 'ul',
        items: [
          'Track missing items from one dashboard view instead of chasing threads across email.',
          'Schedule payroll verification against the direct deposit record before the first pay cycle closes.',
          'Export the completed packet to PDF for the personnel file, then delete local copies floating around.',
        ],
      },
      {
        type: 'callout',
        title: 'Paperwork before day one buys a real first day',
        text: 'New hires who spend day one signing documents remember onboarding as bureaucracy; those whose packets closed the prior week spend it meeting people and fixing environment setup. The checklist exists to protect that first impression, which retention research says sticks.',
      },
    ],
    faqs: [
      {
        question: 'What documents are legally required for new hires?',
        answer:
          'Most jurisdictions require identity and work-authorization verification plus tax withholding registration, though exact names vary by country. Digital collection with signatures satisfies these needs in many places, but confirm the specifics with local counsel or your payroll provider before standardizing anything anywhere.',
      },
      {
        question: 'When should new hire paperwork be sent?',
        answer:
          'Send the packet within a day of offer acceptance so identity, deposit, and agreement items close during the notice period. Pre-day-one collection converts the first morning into orientation and team introductions instead of a signing session at an empty desk.',
      },
      {
        question: 'What does remote onboarding paperwork add?',
        answer:
          'A telecommuting agreement covering workspace safety attestations, working hours, and equipment responsibilities, plus shipping logistics for hardware. Remote starters also benefit from an IT access bundle requested through one form rather than scattered across chat messages and sticky notes everywhere.',
      },
      {
        question: 'Where should signed onboarding documents live?',
        answer:
          'Export each completed packet to PDF and store it in the personnel filing system your retention policy governs. Keep access limited to HR roles only, and avoid leaving signed files in shared drives or personal folders outside that controlled, audited location.',
      },
    ],
  },
  {
    slug: 'church-forms-toolkit',
    title: 'Church Forms Toolkit: 10 Forms Every Ministry Needs',
    description:
      'Ten church forms covering prayer requests, member registration, event signups, volunteer rosters, VBS programs, facility bookings, and giving, all free to run.',
    category: 'Toolkits',
    readingMinutes: 7,
    intro: [
      'Ministries run on ten recurring forms: pastoral care requests, member records, event signups, volunteer coordination, kids program registration, and facility use. Digitizing them ends Sunday-morning paper chases and gives office administrators one organized place for every moving piece, usually within an afternoon of setup.',
    ],
    sections: [
      { type: 'h2', text: 'Care and connection' },
      {
        type: 'ol',
        items: [
          '**Prayer requests:** confidential submissions routed straight to the pastor\'s inbox, with optional anonymity for those not ready to be named.',
          '**Member and family registration:** household contacts, birthdays, and ministry interests building one clean directory.',
          '**Connection card replacement:** first-time visitor details and follow-up asks captured on phones instead of pew paper.',
        ],
      },
      { type: 'h2', text: 'Events and programs' },
      {
        type: 'ol',
        items: [
          '**Event signup:** headcounts, meal choices, and childcare needs for dinners, retreats, and conferences.',
          '**Vacation Bible School registration:** guardian consents, allergy notes, t-shirt sizes, and pickup authorization per child.',
          '**Volunteer roster:** availability, skills, and serving preferences feeding monthly schedules without group-text archaeology.',
        ],
      },
      { type: 'h2', text: 'Operations and stewardship' },
      {
        type: 'ol',
        items: [
          '**Facility use requests:** building and room bookings with setup needs, routed for approval before calendars fill.',
          '**Maintenance reports:** members flag issues from their phones the moment they spot them, with photos attached.',
          '**Giving intents and pledge tracking:** commitment cards digitized, with totals exported for the finance team.',
          '**Meal train coordination:** care meals scheduled around hospital stays or bereavement without double-booking lasagna.',
        ],
      },
      { type: 'h2', text: 'Sharing with the congregation' },
      {
        type: 'ul',
        items: [
          'Print QR codes in bulletins and post them beside sanctuary doors; camera taps beat URLs from the pulpit.',
          'Link everything from one church hub page so announcements stay short.',
          'Route sensitive submissions, like prayer requests, to private notifications rather than shared dashboards.',
        ],
      },
      {
        type: 'callout',
        title: 'One hub link beats ten announcements',
        text: 'Congregations adopt forms fastest when every announcement ends the same way: the same hub link, every week. Repetition builds the habit; scattered links across bulletins, slides, and newsletters split attention and quietly kill adoption.',
      },
    ],
    faqs: [
      {
        question: 'Are these church forms free to use?',
        answer:
          'Yes. SharaForms offers unlimited forms and submissions on the free plan, so congregations of any size run prayer requests, registrations, and facility bookings without software line items in the budget. Paid tiers add features like branding removal when a church wants them.',
      },
      {
        question: 'Can prayer requests stay confidential?',
        answer:
          'Yes. Route submission notifications exclusively to the pastor or care-team inbox, restrict dashboard access accordingly, and offer an anonymous option for requesters who prefer it. Confidential handling is a deliberate configuration choice here, never a limitation of digital forms themselves.',
      },
      {
        question: 'How do we handle guardian consents for kids programs?',
        answer:
          'Collect guardian signature, allergy notes, photo permissions, and pickup authorization inside each child\'s registration form, timestamped automatically at submission. Export per-event rosters so volunteers hold exactly the medical and consent facts supervision requires during the program, and strictly nothing more beyond that.',
      },
      {
        question: 'Can volunteers sign up for specific serving slots?',
        answer:
          'Yes. Checkbox lists of dates and roles let people claim slots directly in seconds, while conditional questions gather skills or availability details only where relevant. Exports feed the serving roster directly, eliminating the reply-all chaos of long group scheduling email threads.',
      },
    ],
  },
  {
    slug: 'nonprofit-forms-toolkit',
    title: '10 Nonprofit Forms That Replace Paperwork Chaos',
    description:
      'Ten nonprofit forms covering donations, auctions, sponsorships, volunteer applications, grant writing, and beneficiary feedback, with export workflows funders expect.',
    category: 'Toolkits',
    readingMinutes: 7,
    intro: [
      'Nonprofits juggle donors, volunteers, grantors, and beneficiaries through the same recurring paperwork every quarter: donation intake, auction item bids, sponsorship packages, volunteer screening, and program feedback. These ten templates cover that cycle end to end, each exportable in the formats funders and boards ask for.',
    ],
    sections: [
      { type: 'h2', text: 'Fundraising intake' },
      {
        type: 'ol',
        items: [
          '**Donation form:** preset amounts plus custom figures, donor messages, and receipt-ready exports.',
          '**Charity auction pledge sheet:** item bids and paddle numbers digitized so checkout night runs itself.',
          '**Sponsorship application:** package selection, logos, and activation commitments captured in one structured record.',
          '**Grant application intake:** organization profiles, budgets, and supporting documents uploaded once instead of emailed thrice.',
        ],
      },
      { type: 'h2', text: 'Volunteer pipeline' },
      {
        type: 'ol',
        items: [
          '**Volunteer application:** skills, availability, references, and background-check consent screened consistently.',
          '**Shift signup sheets:** dated slot lists claiming themselves, replacing clipboard wars at orientations.',
          '**Volunteer waiver:** liability language signed with timestamps stored alongside the application.',
        ],
      },
      { type: 'h2', text: 'Programs and accountability' },
      {
        type: 'ol',
        items: [
          '**Beneficiary or service feedback:** short pulse surveys proving outcomes to boards and funders alike.',
          '**In-kind donation tracking:** goods pledges logged with fair-value estimates for year-end reporting.',
        ],
      },
      { type: 'h2', text: 'Exports funders actually request' },
      {
        type: 'ul',
        items: [
          'Donation CSVs reconciled monthly against payment processor statements.',
          'Volunteer hour summaries aggregated per program for grant reporting season.',
          'Feedback score trends charted across quarters for board decks.',
        ],
      },
      {
        type: 'callout',
        title: 'Report-ready data starts at intake',
        text: 'Funders rarely reject missions; they reject messy evidence. Collecting donations, hours, and outcomes in structured forms from day one turns the annual report from an archaeology project into a filtered export, which is the cheapest credibility a small nonprofit can buy.',
      },
    ],
    faqs: [
      {
        question: 'Is there a nonprofit discount for form software?',
        answer:
          'SharaForms offers discounted pricing for nonprofits on paid plans, and the free tier already covers unlimited forms and submissions, which handles most organizations entirely. Upgrade only when specific paid features clearly earn their keep against that discounted rate for your organization.',
      },
      {
        question: 'Can we collect donations without a merchant account?',
        answer:
          'You will want some processing connection to charge cards, typically Stripe or similar, linked to your organization\'s own account. Until that is arranged, the donation form still captures pledges and donor details, letting you invoice manually while donation volumes remain modest and manageable.',
      },
      {
        question: 'How do we track volunteer hours for grants?',
        answer:
          'Pair shift signups with a simple hours log or timesheet export, aggregating totals per program and period. Grant reviewers trust consistent timestamped records far more than reconstructed estimates, and these exports take just minutes once intake runs digitally from day one.',
      },
      {
        question: 'Can one form serve multiple campaigns?',
        answer:
          'Duplicate the master donation form per campaign and adjust amounts, messaging, and thank-you text carefully, keeping the field structure identical so yearly comparisons stay clean. Consistent schemas across campaigns are exactly what make board-level trend charts possible later without spreadsheet surgery.',
      },
    ],
  },
  {
    slug: 'liability-waivers-explained',
    title: 'Liability Waivers Explained: What Makes Them Hold Up',
    description:
      'Understand liability waivers: what they legally do, the elements that strengthen enforceability, common failure points, minors rules, and how digital signing fits.',
    category: 'Compliance',
    readingMinutes: 6,
    intro: [
      'A liability waiver is a contract where participants acknowledge specific risks and release claims arising from them. Waivers hold up best when risks are named specifically, language stays plain, signing happens voluntarily before the activity, and adults sign for themselves; children generally cannot waive their own claims, which is where guardian signatures enter. This guide explains the mechanics without pretending to be legal advice.',
    ],
    sections: [
      { type: 'h2', text: 'What a waiver actually does' },
      {
        type: 'p',
        text: 'A waiver shifts certain assumed risks from operator to participant by contract: the climber accepts falling hazards, the gym member accepts equipment strain, the event attendee accepts crowd dynamics. It does not excuse gross negligence, and its reach varies by jurisdiction, which is why courts, not templates, have the final word on any specific dispute.',
      },
      { type: 'h2', text: 'Elements that strengthen enforceability' },
      {
        type: 'ol',
        items: [
          '**Clear labeling:** titled plainly as a waiver and release so nobody claims ambush.',
          '**Specific risk enumeration:** naming the actual hazards instead of all conceivable harm.',
          '**Plain language:** sentences participants can genuinely read at signing speed.',
          '**Explicit assumption paragraph:** an unambiguous statement that risks are accepted.',
          '**Signature and date,** captured with the activity identified on the same document.',
          '**Guardian signature block** wherever participants may be minors.',
        ],
      },
      { type: 'h2', text: 'Common failure points' },
      {
        type: 'ul',
        items: [
          'Overbroad releases covering everything, which courts read skeptically.',
          'Buried clauses hidden mid-paragraph in fine print nobody reasonably noticed.',
          'Pressure signing, such as at the start line with the queue moving.',
          'Minors signing for themselves, which many jurisdictions refuse to enforce.',
          'Stale waivers referencing activities no longer offered.',
        ],
      },
      { type: 'h2', text: 'Digital signatures and storage' },
      {
        type: 'p',
        text: 'Electronic execution is broadly recognized when intent and record integrity hold: a timestamped signature tied to an identifiable document satisfies most operational needs. Generate a PDF copy per signer so the exact wording each person agreed to survives template edits, and retain records per your jurisdiction\'s limitation periods for potential claims.',
      },
      {
        type: 'callout',
        title: 'This is education, not legal advice',
        text: 'Waiver enforceability differs sharply between jurisdictions and activities. Have a qualified attorney in your region draft or review the actual language before relying on it, and revisit that review whenever offerings change materially.',
      },
    ],
    faqs: [
      {
        question: 'Do liability waivers actually hold up in court?',
        answer:
          'Often yes, particularly when risks were specifically described, language was clear, and signing was voluntary, but outcomes depend heavily on jurisdiction and facts. Waivers rarely shield gross negligence, and courts construe ambiguity against the drafter, which is why attorney-reviewed wording matters.',
      },
      {
        question: 'Can minors sign liability waivers?',
        answer:
          'Generally no; most jurisdictions treat minors as lacking capacity to release claims, making pre-injury waivers they sign unenforceable. The practical pattern is a parent or legal guardian signing on the minor\'s behalf, and even those waivers face stricter scrutiny than adult ones.',
      },
      {
        question: 'Are electronic signatures valid on waivers?',
        answer:
          'In most jurisdictions yes, under e-signature legislation recognizing electronic execution when signers show clear intent and records stay tamper-evident. Timestamped digital signatures with stored PDF copies meet operational evidence needs, though heavily regulated sectors should always confirm sector-specific compliance rules first.',
      },
      {
        question: 'What is the difference between a waiver and a consent form?',
        answer:
          'A waiver releases future legal claims arising from assumed risk, while a consent form permits something to happen, such as treatment, photography, or data use. Many activities need both: consent to participate and photograph, plus a waiver allocating responsibility if injury occurs.',
      },
    ],
  },
]

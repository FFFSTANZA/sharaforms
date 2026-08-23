// Guides 8-10: document automation, definitions, and deployment.
export default [
  {
    slug: 'auto-generate-pdf-from-form-responses',
    title: 'Turn Form Responses Into PDF Documents Automatically',
    description:
      'Generate PDFs from form submissions automatically: contracts, intake summaries, certificates, and reports assembled from answers without copy-paste work.',
    category: 'Documents',
    readingMinutes: 5,
    intro: [
      'Form responses can become finished PDF documents at the moment of submission: contracts with names filled in, intake summaries, certificates, and status reports. You design a document template once, map its placeholders to form fields, and every submission produces its own copy delivered by email or kept for records.',
    ],
    sections: [
      { type: 'h2', text: 'Documents worth automating first' },
      {
        type: 'ul',
        items: [
          'Service agreements that repeat with only client details changing.',
          'Patient or client intake summaries routed to practitioners before appointments.',
          'Course completion certificates bearing names and dates.',
          'Inspection and audit reports formatted for compliance archives.',
          'Application confirmations applicants can keep as proof.',
        ],
      },
      { type: 'h2', text: 'How template mapping works' },
      {
        type: 'steps',
        items: [
          {
            title: 'Design the document shell',
            text: 'Lay out the fixed parts once: letterhead, legal clauses, formatting. Anything constant belongs in the template, not in per-response editing.',
          },
          {
            title: 'Place field placeholders',
            text: 'Insert placeholders wherever an answer belongs, then bind each placeholder to its form field so submitted values flow into position automatically.',
          },
          {
            title: 'Handle conditional content',
            text: 'Sections can appear only when relevant answers trigger them, keeping a single template honest across different response paths.',
          },
          {
            title: 'Deliver and archive',
            text: 'Send each generated PDF to the respondent, your team, or both, and keep copies attached to dashboard entries so records never depend on inbox searches.',
          },
        ],
      },
      { type: 'h2', text: 'Details people discover too late' },
      {
        type: 'ul',
        items: [
          'Date formats differ by audience; format explicitly instead of inheriting raw values.',
          'Long free-text answers need room to wrap, or they clip mid-sentence.',
          'Signature images belong inside signature blocks, sized consistently.',
          'File names matter for retrieval; build them from respondent names and timestamps.',
        ],
      },
      {
        type: 'callout',
        title: 'Start with one document',
        text: 'Automate the agreement or summary you rebuild most often this week. One working template proves the pattern faster than planning a full document suite.',
      },
    ],
    faqs: [
      {
        question: 'Can generated PDFs include captured signatures?',
        answer:
          'Yes. Signature fields capture drawn or typed signatures during submission, and those images place directly into designated blocks of the generated document. The result reads like a signed paper form, timestamped and attached to the original submission record.',
      },
      {
        question: 'Do respondents receive their own PDF copy?',
        answer:
          'They can. Automated emails following submission attach the personalized document, giving applicants and clients immediate confirmation of what they submitted. Teams often pair the emailed copy with an internal archive copy for compliance trails.',
      },
      {
        question: 'What happens to attachments like uploaded files?',
        answer:
          'Uploaded files stay linked to their original dashboard entry rather than embedded inline. Documents can reference them, while the dashboard keeps originals downloadable, preserving quality and avoiding oversized PDFs stuffed with high-resolution uploads.',
      },
    ],
  },

  {
    slug: 'survey-vs-questionnaire-vs-poll',
    title: 'Survey vs Questionnaire vs Poll: The Real Difference',
    description:
      'A questionnaire is the question set, a survey adds collection and analysis around it, and a poll asks one quick question. Definitions plus a decision table.',
    category: 'Research basics',
    readingMinutes: 4,
    intro: [
      'A questionnaire is the instrument itself, the ordered list of questions. A survey wraps that instrument in a full process: who gets asked, how responses arrive, and what analysis follows. A poll asks exactly one question and shows where opinion stands right now. Scope separates the three.',
    ],
    sections: [
      { type: 'h2', text: 'The questionnaire: just the questions' },
      {
        type: 'p',
        text: 'A questionnaire is an artifact you can hold: ten questions about onboarding, five rating scales about service. Nothing about the word implies distribution or analysis. Questionnaires also serve purposes beyond research, since medical intake and job applications are questionnaires wearing work clothes.',
      },
      { type: 'h2', text: 'The survey: questions plus everything around them' },
      {
        type: 'p',
        text: 'A survey treats measurement as a project. It decides who receives the questionnaire, when reminders go out, how non-responses count, and which charts summarize results. Two teams can run identical questionnaires as entirely different surveys, and their conclusions will differ accordingly.',
      },
      { type: 'h2', text: 'The poll: one question, instant signal' },
      {
        type: 'p',
        text: 'Polls trade depth for participation. One question, visible options, immediate percentages. They excel at engagement moments during streams, meetings, and community posts, and fail anywhere nuance matters because a single answer rarely explains itself.',
      },
      {
        type: 'table',
        head: ['Aspect', 'Questionnaire', 'Survey', 'Poll'],
        rows: [
          ['Scope', 'Question set only', 'Full measurement process', 'Single question'],
          ['Typical length', 'Any', 'Structured sections', 'One screen'],
          ['Analysis', 'Done separately', 'Built into the project', 'Live percentages'],
          ['Best for', 'Intake and applications', 'Research and feedback cycles', 'Quick pulse checks'],
        ],
      },
      { type: 'h2', text: 'Choosing in practice' },
      {
        type: 'ol',
        items: [
          'Need one answer fast from whoever is present? Build a poll.',
          'Collecting structured information for processing? That is a questionnaire.',
          'Making a decision backed by measured opinions? Run a survey, and the questionnaire inside it becomes one component of a larger plan.',
        ],
      },
    ],
    faqs: [
      {
        question: 'Is Google Forms a survey tool or questionnaire tool?',
        answer:
          'It builds questionnaires and hosts them online; whether that becomes a survey depends on your process for distribution and analysis. SharaForms works the same way: the builder creates the instrument, while presentation modes, sharing options, and exports support whatever scale of survey process you run around it.',
      },
      {
        question: 'Can a poll be part of a survey?',
        answer:
          'As a concept yes, since surveys sometimes include single-question pulses sent between major waves. Within one form the distinction blurs, but the pattern holds: short standalone polls gauge temperature between deeper survey cycles rather than replacing them.',
      },
      {
        question: 'What should I call my customer feedback form?',
        answer:
          'Call it whatever your audience understands; the label matters less than the structure. If it contains a set of questions processed afterward, it functions as a questionnaire feeding a feedback survey, and naming it a feedback form communicates exactly that to customers.',
      },
    ],
  },
]

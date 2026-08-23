// Central registry for the /guides hub. Sitemap, hub page, and detail pages
// all derive from these exports so new posts only ever land in one place.
import guidesPart1 from './guides-part-1'
import guidesPart2 from './guides-part-2'
import guidesPart3 from './guides-part-3'
import guidesPart4 from './guides-part-4'

export const guides = [...guidesPart1, ...guidesPart2, ...guidesPart3, ...guidesPart4]

export const guideSlugs = guides.map((guide) => guide.slug)

export function getGuideBySlug(slug) {
  if (!slug) {
    return null
  }
  return guides.find((guide) => guide.slug === slug) || null
}

export function getRelatedGuides(slug, limit = 3) {
  const current = getGuideBySlug(slug)
  if (!current) {
    return []
  }
  const sameCategory = guides.filter(
    (guide) => guide.slug !== slug && guide.category === current.category,
  )
  const others = guides.filter(
    (guide) => guide.slug !== slug && guide.category !== current.category,
  )
  return [...sameCategory, ...others].slice(0, limit)
}

// Contextual links from each guide to the templates that put its advice into
// practice. Slugs must exist in data/forms/templates/template-slugs.js.
const GUIDE_TEMPLATE_LINKS = {
  'single-page-vs-multi-page-forms': [
    { slug: 'contact-form-template', label: 'Contact form' },
    { slug: 'job-application-form-template', label: 'Job application' },
  ],
  'one-question-at-a-time-forms': [
    { slug: 'nps-survey-template', label: 'NPS survey' },
    { slug: 'quiz-form-template', label: 'Quiz' },
    { slug: 'customer-feedback-survey-template', label: 'Customer feedback survey' },
  ],
  'add-calculations-to-a-form': [
    { slug: 'expense-report-form-template', label: 'Expense report' },
    { slug: 'quiz-form-template', label: 'Scored quiz' },
  ],
  'self-grading-quiz': [
    { slug: 'quiz-form-template', label: 'Quiz' },
  ],
  'lead-qualification-scoring-form': [
    { slug: 'lead-generation-form-template', label: 'Lead generation' },
    { slug: 'contact-form-template', label: 'Contact form' },
  ],
  'conditional-logic-examples': [
    { slug: 'event-registration-template', label: 'Event registration' },
    { slug: 'customer-feedback-survey-template', label: 'Customer feedback survey' },
  ],
  'hidden-form-fields-source-tracking': [
    { slug: 'lead-generation-form-template', label: 'Lead generation' },
    { slug: 'contact-form-template', label: 'Contact form' },
  ],
  'auto-generate-pdf-from-form-responses': [
    { slug: 'liability-waiver-form-template', label: 'Liability waiver' },
    { slug: 'appointment-booking-form-template', label: 'Appointment booking' },
  ],
  'survey-vs-questionnaire-vs-poll': [
    { slug: 'nps-survey-template', label: 'NPS survey' },
    { slug: 'customer-feedback-survey-template', label: 'Customer feedback survey' },
  ],
  'customer-satisfaction-survey-questions': [
    { slug: 'customer-feedback-survey-template', label: 'Customer feedback survey' },
    { slug: 'nps-survey-template', label: 'NPS survey' },
  ],
  'collect-rsvps-online-free': [
    { slug: 'rsvp-form-template', label: 'RSVP form' },
    { slug: 'wedding-rsvp-form-template', label: 'Wedding RSVP' },
  ],
  'online-event-registration-guide': [
    { slug: 'event-registration-template', label: 'Event registration' },
    { slug: 'webinar-registration-form-template', label: 'Webinar registration' },
  ],
  'sign-up-sheet-online': [
    { slug: 'event-registration-template', label: 'Event registration' },
    { slug: 'volunteer-signup-form-template', label: 'Volunteer signup' },
  ],
  'form-submission-notifications': [
    { slug: 'contact-form-template', label: 'Contact form' },
    { slug: 'job-application-form-template', label: 'Job application' },
  ],
  'share-a-form-anywhere': [
    { slug: 'donation-form-template', label: 'Donation form' },
    { slug: 'event-registration-template', label: 'Event registration' },
  ],
}

export function getGuideTemplateLinks(slug) {
  return GUIDE_TEMPLATE_LINKS[slug] || []
}

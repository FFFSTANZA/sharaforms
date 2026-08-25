// Central registry for the /guides hub. Sitemap, hub page, and detail pages
// all derive from these exports so new posts only ever land in one place.
import guidesPart1 from './guides-part-1'
import guidesPart2 from './guides-part-2'
import guidesPart3 from './guides-part-3'
import guidesPart4 from './guides-part-4'
import guidesPart5 from './guides-part-5'
import guidesPart6 from './guides-part-6'

export const guides = [...guidesPart1, ...guidesPart2, ...guidesPart3, ...guidesPart4, ...guidesPart5, ...guidesPart6]

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
  'icebreaker-questions': [
    { slug: 'poll-form-template', label: 'Poll' },
    { slug: 'event-registration-template', label: 'Event registration' },
  ],
  'get-to-know-you-questions': [
    { slug: 'quiz-form-template', label: 'Quiz' },
    { slug: 'questionnaire-template', label: 'Questionnaire' },
  ],
  'employee-engagement-survey-questions': [
    { slug: 'employee-engagement-survey-template', label: 'Engagement pulse survey' },
    { slug: 'nps-survey-template', label: 'NPS survey' },
  ],
  'workshop-feedback-questions': [
    { slug: 'training-evaluation-form-template', label: 'Training evaluation' },
    { slug: 'event-feedback-survey-form-template', label: 'Event feedback survey' },
  ],
  'how-to-write-survey-questions': [
    { slug: 'customer-feedback-survey-template', label: 'Customer feedback survey' },
    { slug: 'questionnaire-template', label: 'Questionnaire' },
  ],
  'how-long-should-a-survey-be': [
    { slug: 'nps-survey-template', label: 'NPS pulse' },
    { slug: 'customer-feedback-survey-template', label: 'CSAT survey' },
  ],
  'order-form-vs-purchase-order-vs-invoice': [
    { slug: 'online-order-form-template', label: 'Order form' },
    { slug: 'purchase-order-form-template', label: 'Purchase order' },
    { slug: 'quote-request-form-template', label: 'Quote request' },
  ],
  'hr-forms-for-small-business': [
    { slug: 'job-application-form-template', label: 'Job application' },
    { slug: 'offer-acceptance-form-template', label: 'Offer acceptance' },
    { slug: 'timesheet-form-template', label: 'Timesheet' },
    { slug: 'leave-request-form-template', label: 'Leave request' },
  ],
  'teacher-forms-toolkit': [
    { slug: 'field-trip-permission-slip-form-template', label: 'Field trip permission slip' },
    { slug: 'school-absence-report-form-template', label: 'Absence report' },
    { slug: 'parent-teacher-conference-form-template', label: 'Conference booking' },
    { slug: 'transcript-request-form-template', label: 'Transcript request' },
  ],
  'collect-customer-testimonials': [
    { slug: 'testimonial-form-template', label: 'Testimonial form' },
    { slug: 'photo-contest-entry-form-template', label: 'Photo submission' },
  ],
  'how-to-create-a-survey': [
    { slug: 'customer-feedback-survey-template', label: 'Customer feedback survey' },
    { slug: 'questionnaire-template', label: 'Questionnaire' },
  ],
  'how-to-make-a-registration-form': [
    { slug: 'event-registration-template', label: 'Event registration' },
    { slug: 'conference-registration-form-template', label: 'Conference registration' },
    { slug: 'webinar-registration-form-template', label: 'Webinar registration' },
  ],
  'how-to-create-an-order-form': [
    { slug: 'online-order-form-template', label: 'Online order form' },
    { slug: 'tshirt-order-form-template', label: 'T-shirt order form' },
    { slug: 'cake-order-form-template', label: 'Cake order form' },
  ],
  'migrate-from-google-forms': [
    { slug: 'contact-form-template', label: 'Contact form' },
    { slug: 'quiz-form-template', label: 'Quiz form' },
    { slug: 'event-registration-template', label: 'Event registration' },
  ],
  'net-promoter-score-questions': [
    { slug: 'nps-survey-template', label: 'NPS survey' },
    { slug: 'customer-feedback-survey-template', label: 'CSAT companion survey' },
  ],
  'exit-interview-questions': [
    { slug: 'exit-interview-form-template', label: 'Exit interview form' },
    { slug: 'resignation-notice-form-template', label: 'Resignation notice' },
  ],
  'demographics-survey-questions': [
    { slug: 'questionnaire-template', label: 'Questionnaire' },
    { slug: 'event-feedback-survey-form-template', label: 'Event feedback survey' },
  ],
  'job-application-questions': [
    { slug: 'job-application-form-template', label: 'Job application form' },
    { slug: 'internship-application-form-template', label: 'Internship application' },
  ],
  'patient-intake-questions': [
    { slug: 'patient-intake-form-template', label: 'Patient intake form' },
    { slug: 'therapy-intake-form-template', label: 'Therapy intake' },
    { slug: 'dental-new-patient-form-template', label: 'Dental new patient' },
  ],
  'anonymous-surveys': [
    { slug: 'employee-engagement-survey-template', label: 'Engagement pulse survey' },
    { slug: 'suggestion-box-form-template', label: 'Suggestion box' },
  ],
  'how-to-increase-form-completion-rates': [
    { slug: 'newsletter-signup-form-template', label: 'Newsletter signup' },
    { slug: 'lead-generation-form-template', label: 'Lead generation' },
  ],
  'new-hire-paperwork-checklist': [
    { slug: 'employee-onboarding-form-template', label: 'Employee onboarding' },
    { slug: 'offer-acceptance-form-template', label: 'Offer acceptance' },
    { slug: 'direct-deposit-form-template', label: 'Direct deposit' },
    { slug: 'telecommuting-agreement-form-template', label: 'Telecommuting agreement' },
  ],
  'church-forms-toolkit': [
    { slug: 'prayer-request-form-template', label: 'Prayer request' },
    { slug: 'vacation-bible-school-registration-form-template', label: 'VBS registration' },
    { slug: 'membership-application-form-template', label: 'Member registration' },
    { slug: 'facility-rental-request-form-template', label: 'Facility use request' },
  ],
  'nonprofit-forms-toolkit': [
    { slug: 'donation-form-template', label: 'Donation form' },
    { slug: 'charity-auction-donation-form-template', label: 'Charity auction pledge' },
    { slug: 'grant-application-form-template', label: 'Grant application' },
    { slug: 'volunteer-signup-form-template', label: 'Volunteer application' },
  ],
  'liability-waivers-explained': [
    { slug: 'liability-waiver-form-template', label: 'Liability waiver' },
    { slug: 'photo-release-form-template', label: 'Photo release' },
  ],
}

export function getGuideTemplateLinks(slug) {
  return GUIDE_TEMPLATE_LINKS[slug] || []
}

<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    private array $templates;

    public function __construct()
    {
        $this->templates = [
            $this->contactForm(),
            $this->eventRegistration(),
            $this->customerFeedback(),
            $this->jobApplication(),
            $this->appointmentBooking(),
            $this->orderForm(),
            $this->donationForm(),
            $this->leadGeneration(),
            $this->patientIntake(),
            $this->realEstateInquiry(),
            $this->courseEnrollment(),
            $this->rsvpForm(),
            $this->fileUpload(),
            $this->consentForm(),
            $this->questionnaire(),
            $this->abstractSubmission(),
            $this->auditForm(),
            $this->awardNomination(),
            $this->calculationForm(),
            $this->checklistForm(),
            $this->contentSubmission(),
            $this->evaluationForm(),
            $this->inspectionForm(),
            $this->interviewForm(),
            $this->legalIntake(),
            $this->membershipApplication(),
            $this->petitionForm(),
            $this->pollForm(),
            $this->quizForm(),
            $this->quoteRequest(),
            $this->recommendationForm(),
            $this->incidentReport(),
            $this->reservationForm(),
            $this->sponsorshipApplication(),
            $this->subscriptionForm(),
            $this->summerCamp(),
            $this->telecommutingForm(),
            $this->trackingForm(),
            $this->votingForm(),
            $this->weddingForm(),
            $this->volunteerForm(),
            $this->alumniForm(),
            $this->petAdoption(),
            $this->bankAccountOpening(),
            $this->insuranceClaim(),
            $this->photographyBooking(),
            $this->seoAudit(),
            $this->sportsRegistration(),
            $this->gamingTournament(),
            $this->requestForm(),
            $this->rentalApplication(),
            $this->webinarRegistration(),
            $this->leaveRequest(),
            $this->expenseReport(),
            $this->timesheetForm(),
            $this->purchaseOrder(),
            $this->reimbursementClaim(),
            $this->tshirtOrder(),
            $this->cateringOrder(),
            $this->liabilityWaiver(),
            $this->photoRelease(),
            $this->employeeOnboarding(),
            $this->exitInterview(),
            $this->selfEvaluation(),
            $this->scholarshipApplication(),
            $this->grantApplication(),
            $this->clientOnboarding(),
            $this->projectBrief(),
            $this->bugReport(),
            $this->maintenanceRequest(),
            $this->coachingIntake(),
            $this->vetNewClient(),
            $this->npsSurvey(),
            $this->testimonialForm(),
            $this->gymMembership(),
            $this->fieldTripPermissionSlip(),
            $this->therapyIntake(),
            $this->raceRegistration(),
            $this->golfTournamentRegistration(),
            $this->vendorApplication(),
            $this->workOrder(),
            $this->warrantyClaim(),
            $this->returnExchange(),
            $this->facilityRentalRequest(),
            $this->parkingPermitApplication(),
            $this->internshipApplication(),
            $this->studentRegistration(),
            $this->preschoolWaitlist(),
            $this->parentTeacherConference(),
            $this->tutoringRequest(),
            $this->newsletterSignup(),
            $this->demoRequest(),
            $this->affiliateProgramApplication(),
            $this->podcastGuestApplication(),
            $this->speakerProposal(),
            $this->complaintForm(),
            $this->suggestionBox(),
            $this->equipmentCheckout(),
            $this->medicationRefillRequest(),
            $this->mealTrainSignup(),
            $this->conferenceRegistration(),
            $this->reunionRegistration(),
            $this->vacationBibleSchoolRegistration(),
            $this->cakeOrder(),
            $this->yearbookOrder(),
            $this->overtimeRequest(),
            $this->travelAuthorization(),
            $this->artCommissionRequest(),
            $this->trainingEvaluation(),
            $this->transcriptRequest(),
            $this->directDeposit(),
            $this->purchaseRequisition(),
            $this->itSupportTicket(),
            $this->creditApplication(),
            $this->wholesaleAccountApplication(),
            $this->insuranceQuoteRequest(),
            $this->taxPreparationIntake(),
            $this->tattooConsent(),
            $this->prayerRequest(),
            $this->openHouseSignIn(),
            $this->hoaArchitecturalRequest(),
            $this->guestPostPitch(),
            $this->employeeOfTheMonthNomination(),
            $this->potluckSignupSheet(),
            $this->fosterAnimalApplication(),
            $this->moveInMoveOutInspection(),
            $this->minorTravelConsent(),
            $this->vaccinationConsent(),
            $this->resignationNotice(),
            $this->offerAcceptance(),
            $this->referenceCheck(),
            $this->shiftSwap(),
            $this->jobRequisition(),
            $this->addressChange(),
            $this->callbackRequest(),
            $this->publicRecordsRequest(),
            $this->orderStatusInquiry(),
            $this->warrantyRegistration(),
            $this->supplierRegistration(),
            $this->schoolAbsenceReport(),
            $this->dentalNewPatient(),
            $this->charityAuctionDonation(),
            $this->donationPickupRequest(),
            $this->eventFeedbackSurvey(),
            $this->employeeEngagementSurvey(),
            $this->courseEvaluation(),
            $this->photoContestEntry(),
            $this->customerReferral(),
            $this->birthdayPartyBooking(),
            $this->storageUnitReservation(),
        ];
    }

    public function run(): void
    {
        $user = User::first();
        $questionsCatalog = $this->questionsCatalog();

        $slugs = [];
        foreach ($this->templates as $data) {
            $data['slug'] ??= \Illuminate\Support\Str::slug($data['name']);
            $data['creator_id'] ??= $user?->id;
            $data['publicly_listed'] = true;
            // Curated per-slug FAQs win over inline sets and generic defaults so
            // every template detail page ships unique, search-shaped questions.
            $data['questions'] = $questionsCatalog[$data['slug']]
                ?? ($data['questions'] ?? $this->defaultQuestions($data['name']));
            $data['structure'] = $this->normalizeStructure($data['structure']);
            $slugs[] = $data['slug'];

            Template::updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }

        $this->deriveRelatedTemplates();

        $this->command?->info('Seeded ' . count($this->templates) . ' templates.');
    }

    /**
     * Populate related_templates for every template by finding up to 4 other
     * templates that share a type or industry. This powers the visible
     * "Related templates" section and the relatedLink structured data, which
     * create the internal-link network between template pages.
     */
    private function deriveRelatedTemplates(): void
    {
        foreach ($this->templates as $data) {
            $slug = $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']);
            $types = $data['types'] ?? [];
            $industries = $data['industries'] ?? [];

            $related = [];
            foreach ($this->templates as $candidate) {
                $candidateSlug = $candidate['slug'] ?? \Illuminate\Support\Str::slug($candidate['name']);
                if ($candidateSlug === $slug) {
                    continue;
                }

                $sharedTypes = array_intersect($types, $candidate['types'] ?? []);
                $sharedIndustries = array_intersect($industries, $candidate['industries'] ?? []);
                if (count($sharedTypes) > 0 || count($sharedIndustries) > 0) {
                    $related[] = $candidateSlug;
                    if (count($related) >= 4) {
                        break;
                    }
                }
            }

            Template::where('slug', $slug)->update(['related_templates' => $related]);
        }
    }

    /**
     * Curated FAQ overrides keyed by template slug, sourced from
     * resources/data/forms/templates/questions.json. Keeps long-tail FAQ
     * content out of the builder methods so every detail page carries
     * unique, search-shaped questions instead of shared defaults.
     */
    private function questionsCatalog(): array
    {
        $path = resource_path('data/forms/templates/questions.json');
        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded)
            ? array_map(static fn ($entry) => $entry['questions'] ?? [], $decoded)
            : [];
    }

    public function defaultQuestions(string $name): array
    {
        return [
            [
                'question' => 'What is the ' . $name . ' template?',
                'answer' => '<p>The ' . $name . ' template is a ready-to-use form designed to help you collect information efficiently. It includes all the necessary fields to get started quickly.</p>',
            ],
            [
                'question' => 'Who can benefit from using this template?',
                'answer' => '<p>This template is ideal for businesses, organizations, and individuals who need to streamline their data collection process. It saves time and ensures you capture all the right information.</p>',
            ],
            [
                'question' => 'How do I customize this template?',
                'answer' => '<p>You can easily customize this template using SharaForms\' intuitive form builder. Add or remove fields, change the design, and adjust settings to match your needs. No coding required.</p>',
            ],
            [
                'question' => 'Why choose SharaForms for this form?',
                'answer' => '<p>SharaForms offers a powerful, free-to-use form builder with features like email notifications, custom branding, integrations with popular tools, and secure data collection. It\'s the perfect platform for creating and managing your forms.</p>',
            ],
        ];
    }

    private function structure(string $title, array $properties, string $color = '#64748b', array $extra = []): array
    {
        return array_merge([
            'title' => $title,
            'properties' => $properties,
            'color' => $color,
            'submitted_text' => '<p>Thank you for your submission!</p>',
            'submit_button_text' => 'Submit',
            're_fillable' => false,
            'use_captcha' => false,
            'uppercase_labels' => false,
            'redirect_url' => null,
            're_fill_button_text' => 'Fill Again',
        ], $extra);
    }

    private function textField(string $id, string $title, bool $required = false, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'text',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ], $extra);
    }

    private function emailField(string $id, string $title = 'Email', bool $required = true, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'email',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ], $extra);
    }

    private function phoneField(string $id, string $title = 'Phone Number', bool $required = false, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'phone_number',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ], $extra);
    }

    private function urlField(string $id, string $title, bool $required = false, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'url',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ], $extra);
    }

    private function selectField(string $id, string $title, array $options, bool $required = false, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'select',
            'title' => $title,
            'required' => $required,
            'help' => '',
            'options' => $options,
        ], $extra);
    }

    private function multiSelectField(string $id, string $title, array $options, bool $required = false): array
    {
        return [
            'id' => $id,
            'type' => 'multi_select',
            'title' => $title,
            'required' => $required,
            'help' => '',
            'options' => $options,
        ];
    }

    private function textareaField(string $id, string $title, bool $required = false, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'text',
            'title' => $title,
            'required' => $required,
            'help' => '',
            'multi_lines' => true,
        ], $extra);
    }

    private function checkboxField(string $id, string $title, bool $required = false, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'checkbox',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ], $extra);
    }

    private function numberField(string $id, string $title, bool $required = false, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'number',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ], $extra);
    }

    private function dateField(string $id, string $title, bool $required = false, array $extra = []): array
    {
        return array_merge([
            'id' => $id,
            'type' => 'date',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ], $extra);
    }

    private function nfText(string $id, string $content, ?string $name = null): array
    {
        $block = [
            'id' => $id,
            'type' => 'nf-text',
            'content' => $content,
        ];
        if ($name !== null) {
            $block['name'] = $name;
        }

        return $block;
    }

    /**
     * A single condition node matching the client/server logic schema
     * (see LogicPropertyValidator + resources/data/open_filters.json for the
     * valid type/operator pairs). Select comparisons compare against the
     * option NAME (the value stored on submit), not the legacy option value.
     */
    private function logicCondition(string $fieldId, string $fieldType, string $operator, mixed $value = null): array
    {
        $condition = [
            'operator' => $operator,
            'property_meta' => ['id' => $fieldId, 'type' => $fieldType],
        ];
        if ($value !== null) {
            $condition['value'] = $value;
        }

        return ['identifier' => $fieldId, 'value' => $condition];
    }

    /**
     * Logic for a hidden field that appears once `logicCondition` is met.
     * With $requiredWhenShown the field also becomes mandatory while visible
     * ('require-answer'), which keeps base validation valid per
     * LogicPropertyValidator::checkActions (hidden fields may only carry
     * show-block / require-answer actions).
     */
    private function revealLogic(array $conditions, string $operatorIdentifier = 'and', bool $requiredWhenShown = false): array
    {
        return [
            'conditions' => [
                'operatorIdentifier' => $operatorIdentifier,
                'children' => $conditions,
            ],
            'actions' => $requiredWhenShown ? ['show-block', 'require-answer'] : ['show-block'],
        ];
    }

    /**
     * Hidden-by-default variant of a field: pass `'logic' => $this->revealLogic([...])`
     * in the field's extra attributes together with 'hidden' => true.
     */
    private function computedVariable(string $id, string $name, string $formula): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'formula' => $formula,
            'result_type' => 'number',
        ];
    }

    /**
     * Rich-text block that renders a computed variable live while the form is
     * filled. The mention span is substituted by useParseMention with the
     * variable's evaluated value (fallback shown until inputs are complete).
     */
    private function totalBlock(string $id, string $cvId, string $label, string $fallback): array
    {
        return [
            'id' => $id,
            'type' => 'nf-text',
            'name' => $label,
            'content' => '<p><strong>' . $label . ': <span mention="true" mention-field-id="' . $cvId . '" mention-field-name="' . $label . '" mention-fallback="' . $fallback . '">' . $fallback . '</span></strong></p>',
        ];
    }

    /**
     * Convert legacy template field schema to the canonical renderer schema.
     *
     * The live renderer (BlockRenderer.vue), the form builder, and the AI field
     * schemas all expect fields shaped with a `name` label, nested type-keyed
     * options ({select|multi_select}.options[{name,id}]), rating_max_value,
     * and no alias types. Older templates were authored with `title`, flat
     * options ({value,text}), `steps`, and alias types (radio/toggle_switch).
     */
    private function normalizeStructure(array $structure): array
    {
        $properties = $structure['properties'] ?? [];
        $normalized = [];

        foreach ($properties as $field) {
            if (!is_array($field)) {
                $normalized[] = $field;
                continue;
            }

            $type = $field['type'] ?? null;

            // Resolve alias types to their actual input types (editor stores these)
            if ($type === 'radio') {
                $type = 'select';
                $field['type'] = 'select';
                $field['without_dropdown'] = true;
            } elseif ($type === 'toggle_switch') {
                $type = 'checkbox';
                $field['type'] = 'checkbox';
                $field['use_toggle_switch'] = true;
            }

            // Rename label key
            if (isset($field['title']) && !isset($field['name'])) {
                $field['name'] = $field['title'];
            }
            unset($field['title']);

            // Every block needs a name (CorePropertyValidator requires it on all
            // blocks, including layout blocks). Derive one for nf-text from its
            // content heading when none is set.
            if (!isset($field['name']) || $field['name'] === '' || $field['name'] === null) {
                if ($type === 'nf-text' && !empty($field['content'])) {
                    $plain = trim(strip_tags(preg_replace('/<br\s*\/?>/i', ' ', $field['content'])));
                    $plain = preg_replace('/\s+/', ' ', $plain);
                    $field['name'] = mb_substr($plain, 0, 60) ?: 'Text Block';
                } else {
                    $field['name'] = ucwords(str_replace(['_', '-'], ' ', (string) ($type ?? 'block')));
                }
            }

            // Nest flat options under the type key with canonical option shape
            if (in_array($type, ['select', 'multi_select'], true) && isset($field['options']) && is_array($field['options']) && !isset($field[$type])) {
                $options = [];
                foreach ($field['options'] as $option) {
                    $options[] = [
                        'name' => $option['text'] ?? $option['name'] ?? $option['value'] ?? '',
                        'id' => $option['value'] ?? $option['id'] ?? $option['text'] ?? $option['name'] ?? '',
                    ];
                }
                $field[$type] = ['options' => $options];
                unset($field['options']);
            }

            // Rating max value key
            if ($type === 'rating' && isset($field['steps'])) {
                $field['rating_max_value'] = $field['steps'];
                unset($field['steps']);
            }

            // Files: renderer drives multi-upload via `multiple`, not max_number_of_files
            if ($type === 'files' && isset($field['max_number_of_files'])) {
                $field['multiple'] = ((int) $field['max_number_of_files']) > 1;
                unset($field['max_number_of_files']);
            }

            // Payment: renderer reads currency/amount at runtime (editor defaults on mount)
            if ($type === 'payment') {
                $field['currency'] ??= 'USD';
                $field['amount'] ??= 10;
            }

            $normalized[] = $field;
        }

        $structure['properties'] = $normalized;

        return $structure;
    }

    private function contactForm(): array
    {
        return [
            'name' => 'Contact Form Template',
            'slug' => 'contact-form-template',
            'short_description' => 'A professional contact form template to collect inquiries and messages from your website visitors.',
            'description' => '<p>Our Contact Form Template provides a polished and effective way for your website visitors to get in touch with you. Whether you run a small business, a blog, or a large corporation, this template helps you capture inquiries, feedback, and support requests without friction.</p><h2>Why and when to use a contact form</h2><p>A contact form is essential for any website that values communication with its audience. It provides a structured way to receive messages, reduces spam compared to displaying raw email addresses, and ensures you capture all the necessary information to respond effectively.</p><h2>Who is this template for</h2><p>This template is perfect for business owners, freelancers, bloggers, and organizations of all sizes who want to provide a professional communication channel on their website.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms makes it easy to customize this contact form template to match your brand. You can add custom fields, set up email notifications, integrate with Slack or Discord, and embed the form on your website without writing any code.</p>',
            'types' => ['contact_forms'],
            'industries' => ['business_forms', 'customer_service_forms'],
            'structure' => $this->structure('Contact Form', [
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->textField('subject', 'Subject', true),
                $this->textareaField('message', 'Message', true),
            ], '#3b82f6'),
        ];
    }

    private function eventRegistration(): array
    {
        return [
            'name' => 'Event Registration Template',
            'slug' => 'event-registration-template',
            'short_description' => 'A complete event registration form template to collect attendee details and manage sign-ups smoothly.',
            'description' => '<p>Our Event Registration Template makes it simple to collect attendee information for conferences, workshops, seminars, and social gatherings. Capture all the details you need to plan a successful event.</p><h2>Why and when to use an event registration form</h2><p>Whether you\'re organizing a corporate conference, a community workshop, or a private party, an event registration form helps you track attendance, collect dietary preferences, and communicate important updates to your guests.</p><h2>Who is this template for</h2><p>Event organizers, conference planners, community managers, and anyone hosting an event that requires guest registration and information collection.</p><h2>Why SharaForms is the best tool for this form</h2><p>With SharaForms, you can customize this registration template, set submission limits, send confirmation emails, and integrate with your favorite tools to streamline event management.</p>',
            'types' => ['event_registration_forms', 'registration_forms'],
            'industries' => ['business_forms', 'entertainment_forms'],
            'structure' => $this->structure('Event Registration', [
                $this->nfText('intro', '<h2>Register for Our Event</h2><p>Fill out the form below to secure your spot. We look forward to seeing you there!</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone'),
                $this->selectField('ticket_type', 'Ticket Type', [
                    ['value' => 'general', 'text' => 'General Admission'],
                    ['value' => 'vip', 'text' => 'VIP'],
                    ['value' => 'student', 'text' => 'Student'],
                ], true),
                $this->selectField('dietary', 'Dietary Preferences', [
                    ['value' => 'none', 'text' => 'None'],
                    ['value' => 'vegetarian', 'text' => 'Vegetarian'],
                    ['value' => 'vegan', 'text' => 'Vegan'],
                    ['value' => 'gluten_free', 'text' => 'Gluten-Free'],
                ]),
                $this->checkboxField('agree_terms', 'I agree to the event terms and conditions', true),
                $this->totalBlock('ticket_total_display', 'cv_ticket_total', 'Ticket Price', '$0'),
            ], '#8b5cf6', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_ticket_total',
                        'Ticket Price',
                        'IF(ISBLANK({ticket_type}),0,IF({ticket_type}="VIP",150,IF({ticket_type}="Student",25,50)))'
                    ),
                ],
            ]),
        ];
    }

    private function customerFeedback(): array
    {
        return [
            'name' => 'Customer Feedback Survey Template',
            'slug' => 'customer-feedback-survey-template',
            'short_description' => 'Gather valuable customer insights with this comprehensive feedback survey template.',
            'description' => '<p>Understanding what your customers think about your products or services is crucial for growth. Our Customer Feedback Survey Template helps you collect actionable insights that drive improvement.</p><h2>Why and when to use a feedback survey</h2><p>After a purchase, service interaction, or product trial, sending a feedback survey shows you value your customers\' opinions. Use the insights to improve your offerings, identify pain points, and measure customer satisfaction.</p><h2>Who is this template for</h2><p>Business owners, product managers, customer success teams, and marketers who want to measure and improve customer satisfaction.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms offers powerful analytics integrations, customizable survey designs, and automated email notifications, making it easy to deploy and analyze customer feedback surveys.</p>',
            'types' => ['survey_templates', 'feedback_forms'],
            'industries' => ['customer_service_forms', 'business_forms'],
            'structure' => $this->structure('Customer Feedback Survey', [
                $this->nfText('intro', '<h2>We Value Your Feedback</h2><p>Please take a moment to share your experience with us.</p>'),
                $this->textField('full_name', 'Your Name'),
                $this->emailField('email'),
                ['id' => 'satisfaction', 'type' => 'rating', 'title' => 'How satisfied are you with our service?', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'what_went_wrong', 'type' => 'text', 'title' => 'We fell short - what went wrong?', 'required' => false, 'help' => '',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('satisfaction', 'rating', 'less_than', 3)])],
                $this->selectField('recommend', 'How likely are you to recommend us?', [
                    ['value' => 'very_likely', 'text' => 'Very Likely'],
                    ['value' => 'likely', 'text' => 'Likely'],
                    ['value' => 'neutral', 'text' => 'Neutral'],
                    ['value' => 'unlikely', 'text' => 'Unlikely'],
                    ['value' => 'very_unlikely', 'text' => 'Very Unlikely'],
                ], true),
                $this->textareaField('improvements', 'What could we do better?'),
                $this->checkboxField('contact_me', 'May we contact you for follow-up?'),
            ], '#10b981'),
        ];
    }

    private function jobApplication(): array
    {
        return [
            'name' => 'Job Application Form Template',
            'slug' => 'job-application-form-template',
            'short_description' => 'A thorough job application form template to collect candidate information and resumes for your hiring process.',
            'description' => '<p>Streamline your recruitment process with our comprehensive Job Application Form Template. Collect resumes, cover letters, and candidate details in a structured format that makes reviewing applicants easy.</p><h2>Why and when to use a job application form</h2><p>Whether you\'re hiring for a single position or running a large recruitment drive, a standardized application form ensures you collect consistent information from all candidates, making comparison and evaluation more efficient.</p><h2>Who is this template for</h2><p>HR professionals, hiring managers, small business owners, and recruitment teams looking to streamline their applicant collection process.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms allows you to collect file uploads (resumes, portfolios), set up email notifications for new applications, and integrate with your HR tools so recruitment management stays organized.</p>',
            'types' => ['application_forms', 'employment_forms'],
            'industries' => ['human_resources_forms', 'business_forms'],
            'structure' => $this->structure('Job Application Form', [
                $this->nfText('intro', '<h2>Apply for a Position</h2><p>We\'re excited that you\'re interested in joining our team!</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textareaField('cover_letter', 'Cover Letter / Why do you want this position?'),
                ['id' => 'resume', 'type' => 'files', 'title' => 'Upload Resume', 'required' => true, 'help' => 'Accepted formats: PDF, DOC, DOCX', 'max_file_size' => 10, 'max_number_of_files' => 1],
                $this->selectField('how_did_you_hear', 'How did you hear about this position?', [
                    ['value' => 'linkedin', 'text' => 'LinkedIn'],
                    ['value' => 'indeed', 'text' => 'Indeed'],
                    ['value' => 'company_website', 'text' => 'Company Website'],
                    ['value' => 'referral', 'text' => 'Employee Referral'],
                    ['value' => 'other', 'text' => 'Other'],
                ]),
                ['id' => 'referral_name', 'type' => 'text', 'title' => 'Who referred you?', 'required' => false, 'help' => 'Name of the employee who referred you',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('how_did_you_hear', 'select', 'equals', 'Employee Referral')])],
                $this->checkboxField('agree', 'I confirm that the information provided is accurate', true),
            ], '#6366f1'),
        ];
    }

    private function appointmentBooking(): array
    {
        return [
            'name' => 'Appointment Booking Form Template',
            'slug' => 'appointment-booking-form-template',
            'short_description' => 'Simplify appointment scheduling with this easy-to-use booking form template for service providers.',
            'description' => '<p>Our Appointment Booking Form Template makes it easy for clients to schedule appointments with your business. Whether you\'re a doctor, dentist, consultant, or salon owner, this template streamlines the booking process.</p><h2>Why and when to use an appointment booking form</h2><p>An online booking form reduces phone calls, prevents double-booking, and gives clients the convenience of scheduling appointments at any time. It\'s essential for any appointment-based business.</p><h2>Who is this template for</h2><p>Healthcare providers, consultants, salon owners, fitness trainers, and any service professional who manages appointments with clients.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms lets you customize this booking template, send confirmation emails, set submission limits per time slot, and integrate with your calendar tools.</p>',
            'types' => ['appointment_forms', 'booking_forms'],
            'industries' => ['healthcare_forms', 'salon_forms', 'services_forms'],
            'structure' => $this->structure('Book an Appointment', [
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('service', 'Select Service', [
                    ['value' => 'consultation', 'text' => 'Initial Consultation'],
                    ['value' => 'follow_up', 'text' => 'Follow-Up Visit'],
                    ['value' => 'checkup', 'text' => 'Regular Checkup'],
                    ['value' => 'other', 'text' => 'Other'],
                ], true),
                $this->dateField('preferred_date', 'Preferred Date', true),
                $this->selectField('preferred_time', 'Preferred Time', [
                    ['value' => 'morning', 'text' => 'Morning (9AM - 12PM)'],
                    ['value' => 'afternoon', 'text' => 'Afternoon (12PM - 5PM)'],
                    ['value' => 'evening', 'text' => 'Evening (5PM - 8PM)'],
                ], true),
                $this->textareaField('notes', 'Additional Notes or Requests'),
            ], '#f59e0b'),
        ];
    }

    private function orderForm(): array
    {
        return [
            'name' => 'Online Order Form Template',
            'slug' => 'online-order-form-template',
            'short_description' => 'A complete order form template for collecting customer orders and processing purchases online.',
            'description' => '<p>Our Online Order Form Template helps businesses collect customer orders efficiently. Whether you run a restaurant, retail store, or service business, this template makes order management simple.</p><h2>Why and when to use an order form</h2><p>An online order form streamlines the purchasing process, reduces errors from manual order taking, and provides customers with a convenient way to place orders from anywhere.</p><h2>Who is this template for</h2><p>Restaurant owners, retail businesses, wholesalers, and any business that needs to collect orders from customers online.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms offers calculation fields for totals, file uploads for custom requests, email notifications for new orders, and integration with payment gateways and order management tools.</p>',
            'types' => ['order_forms', 'payment_forms'],
            'industries' => ['ecommerce_forms', 'business_forms'],
            'structure' => $this->structure('Place Your Order', [
                $this->nfText('intro', '<h2>Order Form</h2><p>Fill in your details and select the items you\'d like to order.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textareaField('shipping_address', 'Shipping Address', true),
                $this->selectField('items', 'Select Items', [
                    ['value' => 'item_1', 'text' => 'Product A - $29.99'],
                    ['value' => 'item_2', 'text' => 'Product B - $49.99'],
                    ['value' => 'item_3', 'text' => 'Product C - $19.99'],
                ], true),
                $this->textareaField('special_instructions', 'Special Instructions'),
            ], '#ef4444'),
        ];
    }

    private function donationForm(): array
    {
        return [
            'name' => 'Donation Form Template',
            'slug' => 'donation-form-template',
            'short_description' => 'A heartfelt donation form template for nonprofits and charities to collect contributions online.',
            'description' => '<p>Our Donation Form Template makes it easy for nonprofits, charities, and fundraising organizations to collect donations online. With a clean, trust-inspiring design, this template helps you convert supporters into donors.</p><h2>Why and when to use a donation form</h2><p>Whether you\'re running a fundraising campaign, accepting ongoing donations, or collecting for a specific cause, an online donation form makes giving convenient and secure for your supporters.</p><h2>Who is this template for</h2><p>Nonprofit organizations, charities, churches, schools, and community groups looking to raise funds online.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms integrates with payment gateways for secure transactions, sends tax receipt emails to donors, and helps you track fundraising progress with submission data.</p>',
            'types' => ['donation_forms', 'payment_forms'],
            'industries' => ['charity_forms', 'church_forms'],
            'structure' => $this->structure('Make a Donation', [
                $this->nfText('intro', '<h2>Support Our Cause</h2><p>Your contribution makes a difference. Thank you for your generosity.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->selectField('donation_amount', 'Donation Amount', [
                    ['value' => '25', 'text' => '$25'],
                    ['value' => '50', 'text' => '$50'],
                    ['value' => '100', 'text' => '$100'],
                    ['value' => '250', 'text' => '$250'],
                    ['value' => 'other', 'text' => 'Other Amount'],
                ], true),
                $this->numberField('custom_amount', 'Custom Amount ($)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('donation_amount', 'select', 'equals', 'Other Amount')], 'and', true),
                ]),
                $this->selectField('frequency', 'Donation Frequency', [
                    ['value' => 'one_time', 'text' => 'One-Time'],
                    ['value' => 'monthly', 'text' => 'Monthly'],
                    ['value' => 'annual', 'text' => 'Annual'],
                ], true),
                $this->textareaField('message', 'Leave a Message (Optional)'),
                $this->checkboxField('anonymous', 'I would like to remain anonymous'),
                $this->checkboxField('newsletter', 'Keep me updated on your impact'),
            ], '#059669'),
        ];
    }

    private function leadGeneration(): array
    {
        return [
            'name' => 'Lead Generation Form Template',
            'slug' => 'lead-generation-form-template',
            'short_description' => 'Capture high-quality leads for your business with this optimized lead generation form template.',
            'description' => '<p>Our Lead Generation Form Template is designed to help you capture potential customers\' information effectively. With strategic field placement and a conversion-focused design, this template maximizes your lead capture rates.</p><h2>Why and when to use a lead generation form</h2><p>Whether you\'re running a marketing campaign, offering a free resource, or collecting prospects for your sales team, a well-designed lead generation form is essential for growing your business.</p><h2>Who is this template for</h2><p>Marketing teams, sales professionals, business owners, and growth managers looking to build their prospect pipeline.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms offers integration with CRM tools, email marketing platforms, and Slack. You can set up instant notifications, track form views and submissions, and nurture leads automatically.</p>',
            'types' => ['lead_generation_forms', 'signup_forms'],
            'industries' => ['marketing_forms', 'business_forms', 'advertising_forms'],
            'structure' => $this->structure('Get Your Free Guide', [
                $this->nfText('intro', '<h2>Download Our Free Guide</h2><p>Enter your details below to receive our comprehensive guide straight to your inbox.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone'),
                $this->textField('company', 'Company Name'),
                $this->selectField('job_role', 'Job Role', [
                    ['value' => 'owner', 'text' => 'Business Owner'],
                    ['value' => 'manager', 'text' => 'Manager'],
                    ['value' => 'director', 'text' => 'Director'],
                    ['value' => 'individual', 'text' => 'Individual'],
                ]),
                $this->checkboxField('agree', 'I agree to receive marketing communications', true),
            ], '#ec4899'),
        ];
    }

    private function patientIntake(): array
    {
        return [
            'name' => 'Patient Intake Form Template',
            'slug' => 'patient-intake-form-template',
            'short_description' => 'A comprehensive patient intake form template for healthcare providers to collect medical history and information.',
            'description' => '<p>Our Patient Intake Form Template helps healthcare providers collect essential patient information before appointments. From personal details to medical history, this template ensures you have all the information needed for quality care.</p><h2>Why and when to use a patient intake form</h2><p>Patient intake forms are essential for healthcare practices to collect medical history, insurance information, and consent forms before treatment. Digital forms reduce paperwork and waiting room time.</p><h2>Who is this template for</h2><p>Medical clinics, dental practices, physical therapists, chiropractors, and any healthcare provider that needs to collect patient information.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms offers HIPAA-compliant data collection, secure file uploads, and customizable medical history fields. You can integrate with practice management systems and receive instant notifications.</p>',
            'types' => ['registration_forms', 'application_forms'],
            'industries' => ['healthcare_forms', 'veterinary_service_forms'],
            'structure' => $this->structure('Patient Intake Form', [
                $this->nfText('intro', '<h2>New Patient Information</h2><p>Please complete this form before your appointment. All information is kept confidential.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->dateField('date_of_birth', 'Date of Birth', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textareaField('address', 'Home Address', true),
                $this->selectField('gender', 'Gender', [
                    ['value' => 'male', 'text' => 'Male'],
                    ['value' => 'female', 'text' => 'Female'],
                    ['value' => 'other', 'text' => 'Other'],
                    ['value' => 'prefer_not', 'text' => 'Prefer Not to Say'],
                ]),
                $this->selectField('has_insurance', 'Do you have health insurance?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ]),
                $this->textField('insurance_provider', 'Insurance Provider', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('has_insurance', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->textField('policy_number', 'Member / Policy ID', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('has_insurance', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->textareaField('medical_history', 'Relevant Medical History'),
                $this->textareaField('medications', 'Current Medications'),
                $this->checkboxField('agree_terms', 'I consent to the collection and use of my health information', true),
            ], '#0ea5e9'),
        ];
    }

    private function realEstateInquiry(): array
    {
        return [
            'name' => 'Real Estate Inquiry Form Template',
            'slug' => 'real-estate-inquiry-form-template',
            'short_description' => 'A professional real estate inquiry form template for agents to capture property leads and buyer information.',
            'description' => '<p>Our Real Estate Inquiry Form Template helps agents and agencies capture qualified leads from potential buyers and sellers. Collect property preferences, contact details, and budget information to match clients with their ideal properties.</p><h2>Why and when to use a real estate inquiry form</h2><p>Whether you\'re listing properties, hosting open houses, or running online ads, an inquiry form captures lead information in a structured way that makes follow-up easy and effective.</p><h2>Who is this template for</h2><p>Real estate agents, property managers, real estate agencies, and property developers looking to capture and qualify leads.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms integrates with CRM platforms, sends instant lead notifications, and allows you to customize fields for different property types. You can also set up automated responses to keep leads engaged.</p>',
            'types' => ['contact_forms', 'lead_generation_forms'],
            'industries' => ['real_estate_forms'],
            'structure' => $this->structure('Property Inquiry', [
                $this->nfText('intro', '<h2>Interested in This Property?</h2><p>Fill out the form below and we\'ll get back to you shortly.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('interest', 'I am interested in', [
                    ['value' => 'buying', 'text' => 'Buying a Property'],
                    ['value' => 'selling', 'text' => 'Selling a Property'],
                    ['value' => 'renting', 'text' => 'Renting a Property'],
                    ['value' => 'valuation', 'text' => 'Property Valuation'],
                ], true),
                ['id' => 'budget', 'type' => 'number', 'title' => 'Budget Range ($)', 'required' => true, 'help' => ''],
                $this->selectField('property_type', 'Property Type', [
                    ['value' => 'apartment', 'text' => 'Apartment/Condo'],
                    ['value' => 'house', 'text' => 'Single Family Home'],
                    ['value' => 'townhouse', 'text' => 'Townhouse'],
                    ['value' => 'commercial', 'text' => 'Commercial'],
                    ['value' => 'land', 'text' => 'Land'],
                ]),
                $this->textareaField('message', 'Message / Questions'),
            ], '#14b8a6'),
        ];
    }

    private function courseEnrollment(): array
    {
        return [
            'name' => 'Course Enrollment Form Template',
            'slug' => 'course-enrollment-form-template',
            'short_description' => 'A complete course enrollment form template for educational institutions to register students.',
            'description' => '<p>Our Course Enrollment Form Template helps educational institutions, training centers, and online course creators register students efficiently. Collect student information, course preferences, and payment details in one seamless flow.</p><h2>Why and when to use a course enrollment form</h2><p>Whether you\'re running a university, a vocational training center, or an online course platform, a digital enrollment form simplifies registration, reduces paperwork, and ensures you capture all necessary student data.</p><h2>Who is this template for</h2><p>Educational institutions, training providers, online course creators, and workshop organizers who need to enroll students.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms offers payment integration for course fees, file uploads for required documents, and automated confirmation emails. You can also set enrollment deadlines and limits.</p>',
            'types' => ['enrollment_forms', 'registration_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Course Enrollment', [
                $this->nfText('intro', '<h2>Enroll in a Course</h2><p>Complete the form below to register for your desired course.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->dateField('date_of_birth', 'Date of Birth', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textareaField('address', 'Address', true),
                $this->selectField('course', 'Select Course', [
                    ['value' => 'web_dev', 'text' => 'Web Development Bootcamp'],
                    ['value' => 'data_science', 'text' => 'Data Science Fundamentals'],
                    ['value' => 'design', 'text' => 'UI/UX Design Masterclass'],
                    ['value' => 'business', 'text' => 'Business Management'],
                ], true),
                $this->selectField('education_level', 'Highest Education Level', [
                    ['value' => 'high_school', 'text' => 'High School'],
                    ['value' => 'bachelor', 'text' => 'Bachelor\'s Degree'],
                    ['value' => 'master', 'text' => 'Master\'s Degree'],
                    ['value' => 'phd', 'text' => 'PhD'],
                    ['value' => 'other', 'text' => 'Other'],
                ]),
                ['id' => 'documents', 'type' => 'files', 'title' => 'Upload Required Documents', 'help' => 'Transcripts, ID, etc.', 'max_file_size' => 10, 'max_number_of_files' => 3],
                $this->checkboxField('agree_terms', 'I agree to the course terms and policies', true),
            ], '#7c3aed'),
        ];
    }

    private function rsvpForm(): array
    {
        return [
            'name' => 'RSVP Form Template',
            'slug' => 'rsvp-form-template',
            'short_description' => 'A simple and elegant RSVP form template for events, weddings, and gatherings.',
            'description' => '<p>Our RSVP Form Template makes it easy to collect guest responses for your event. Whether you\'re planning a wedding, a corporate party, or a casual get-together, this template helps you manage your guest list effortlessly.</p><h2>Why and when to use an RSVP form</h2><p>An online RSVP form eliminates the back-and-forth of phone calls and emails. Guests can respond at their convenience, and you get instant updates on attendance numbers, dietary needs, and plus-ones.</p><h2>Who is this template for</h2><p>Event planners, wedding organizers, party hosts, and anyone planning a gathering that requires guest attendance confirmation.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms allows you to collect dietary preferences, plus-one details, and song requests. You can set submission limits, send confirmation emails, and get real-time attendance tracking.</p>',
            'types' => ['rsvp_forms', 'event_registration_forms'],
            'industries' => ['entertainment_forms'],
            'structure' => $this->structure('RSVP', [
                $this->nfText('intro', '<h2>You\'re Invited!</h2><p>Please let us know if you can make it by filling out the form below.</p>'),
                $this->textField('full_name', 'Your Name', true),
                $this->emailField('email'),
                $this->selectField('attending', 'Will you attend?', [
                    ['value' => 'yes', 'text' => 'Yes, I\'ll be there!'],
                    ['value' => 'no', 'text' => 'Sorry, I can\'t make it'],
                ], true),
                ['id' => 'guests', 'type' => 'number', 'title' => 'Number of Guests (including you)', 'required' => false, 'help' => ''],
                ['id' => 'guest_names', 'type' => 'text', 'title' => 'Names of Additional Guests', 'required' => false, 'help' => '',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('guests', 'number', 'greater_than', 1)])],
                ['id' => 'dietary', 'type' => 'text', 'title' => 'Dietary Restrictions / Allergies', 'required' => false, 'help' => '', 'multi_lines' => true,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('attending', 'select', 'equals', "Yes, I'll be there!")])],
                ['id' => 'message', 'type' => 'text', 'title' => 'Message for the Host', 'required' => false, 'help' => '', 'multi_lines' => true,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('attending', 'select', 'equals', "Yes, I'll be there!")])],
                $this->checkboxField('agree', 'I\'ve read and agree to the event guidelines'),
            ], '#e11d48'),
        ];
    }

    private function fileUpload(): array
    {
        return [
            'name' => 'File Upload Form Template', 
            'slug' => 'file-upload-form-template', 
            'short_description' => 'A simple file upload form template to collect documents, images, and files from your users.', 
            'description' => '<p>Our File Upload Form Template makes it easy to collect documents, images, videos, and other files from your users. Whether you need design assets, tax documents, or project deliverables, this template handles it all.</p><h2>Why and when to use a file upload form</h2><p>File upload forms are essential for receiving documents, accepting portfolio submissions, collecting design assets, and gathering any file-based deliverable from clients, students, or team members.</p><h2>Who is this template for</h2><p>Designers, agencies, educators, HR teams, and any business that needs to collect files in an organized, secure way.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms lets you set file size limits, restrict accepted formats, and receive instant email notifications when files arrive. You can even integrate with Google Drive or Dropbox via webhooks.</p>', 
            'types' => ['file_upload_forms', 'application_forms'],
            'industries' => ['it_forms', 'business_forms'],
            'structure' => $this->structure('Submit Your Files', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'category', 'type' => 'select', 'title' => 'File Category', 'required' => true, 'help' => '', 'options' => [['value' => 'document', 'text' => 'Document'], ['value' => 'image', 'text' => 'Image'], ['value' => 'video', 'text' => 'Video'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'files_upload', 'type' => 'files', 'title' => 'Upload Files', 'required' => true, 'help' => 'Accepted formats: PDF, JPG, PNG, ZIP', 'max_number_of_files' => 5, 'max_file_size' => 25],
                ['id' => 'description', 'type' => 'text', 'title' => 'Description', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'agree', 'type' => 'checkbox', 'title' => 'I confirm these files are safe to share', 'required' => true, 'help' => '']
            ], '#6366f1'),
        ];
    }

    private function consentForm(): array
    {
        return [
            'name' => 'Consent Form Template', 
            'slug' => 'consent-form-template', 
            'short_description' => 'A clear and compliant consent form template for collecting documented permissions.', 
            'description' => '<p>Our Consent Form Template helps you collect documented, auditable permission from participants, patients, parents, or customers. It is built to keep your organization compliant while keeping the experience friendly.</p><h2>Why and when to use a consent form</h2><p>Use a consent form whenever you need documented approval before an activity, treatment, data collection, photo use, or participation. It protects both your organization and the individual.</p><h2>Who is this template for</h2><p>Healthcare providers, photographers, event organizers, schools, researchers, and businesses that need formal consent records.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms captures a timestamped digital signature, stores submissions securely, and can send a copy of the consent to both parties automatically.</p>', 
            'types' => ['consent_forms', 'legal_forms'],
            'industries' => ['healthcare_forms', 'photography_forms'],
            'structure' => $this->structure('Consent Form', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'date_of_birth', 'type' => 'date', 'title' => 'Date of Birth', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'consent_purpose', 'type' => 'text', 'title' => 'What are you consenting to?', 'required' => true, 'help' => ''],
                ['id' => 'understand', 'type' => 'checkbox', 'title' => 'I have read and understand the information provided', 'required' => true, 'help' => ''],
                ['id' => 'questions_asked', 'type' => 'checkbox', 'title' => 'I have had the opportunity to ask questions', 'required' => true, 'help' => ''],
                ['id' => 'signature', 'type' => 'signature', 'title' => 'Digital Signature', 'required' => true, 'help' => '']
            ], '#059669'),
        ];
    }

    private function questionnaire(): array
    {
        return [
            'name' => 'Questionnaire Template', 
            'slug' => 'questionnaire-template', 
            'short_description' => 'A versatile questionnaire template to gather structured insights from your audience.', 
            'description' => '<p>Our Questionnaire Template gives you a flexible starting point for collecting structured information and opinions. Mix open questions, scales, and multiple choice to design the perfect questionnaire.</p><h2>Why and when to use a questionnaire</h2><p>Questionnaires are ideal for market research, academic studies, customer profiling, onboarding, and any situation where you need consistent, comparable answers from many people.</p><h2>Who is this template for</h2><p>Researchers, product teams, marketers, HR professionals, and educators who need structured data collection.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms makes it easy to build multi-step questionnaires, analyze responses with built-in summaries, and export results to your favorite spreadsheet tool.</p>', 
            'types' => ['questionnaire_templates', 'survey_templates'],
            'industries' => ['business_forms', 'education_forms'],
            'structure' => $this->structure('Questionnaire', [
                ['id' => 'age_group', 'type' => 'select', 'title' => 'Age Group', 'required' => true, 'help' => '', 'options' => [['value' => 'under_18', 'text' => 'Under 18'], ['value' => '18_25', 'text' => '18-25'], ['value' => '26_35', 'text' => '26-35'], ['value' => '36_50', 'text' => '36-50'], ['value' => 'over_50', 'text' => 'Over 50']]],
                ['id' => 'satisfaction', 'type' => 'rating', 'title' => 'Overall satisfaction', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'frequency', 'type' => 'scale', 'title' => 'How often do you use our product?', 'required' => true, 'help' => '', 'scale_min_value' => 1, 'scale_max_value' => 5, 'scale_step_value' => 1],
                ['id' => 'features', 'type' => 'text', 'title' => 'Which features do you use most?', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'suggestions', 'type' => 'text', 'title' => 'Suggestions for improvement', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#8b5cf6'),
        ];
    }

    private function abstractSubmission(): array
    {
        return [
            'name' => 'Abstract Submission Form Template', 
            'slug' => 'abstract-submission-form-template', 
            'short_description' => 'A professional abstract submission form template for conferences, journals, and academic events.', 
            'description' => '<p>Our Abstract Submission Form Template helps conference organizers, journals, and academic committees collect structured submissions. Capture author details, abstracts, and keywords in a consistent format.</p><h2>Why and when to use an abstract submission form</h2><p>Conferences and journals receive dozens or hundreds of abstracts. A structured submission form ensures every entry is complete, consistent, and easy to review.</p><h2>Who is this template for</h2><p>Conference organizers, journal editors, research committees, and academic program coordinators.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms lets you collect abstract files, set submission deadlines, and organize reviews through submission data. Email notifications keep authors informed at every step.</p>', 
            'types' => ['abstract_forms', 'application_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Abstract Submission', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Presenting Author', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'institution', 'type' => 'text', 'title' => 'Institution / Affiliation', 'required' => true, 'help' => ''],
                ['id' => 'abstract_title', 'type' => 'text', 'title' => 'Abstract Title', 'required' => true, 'help' => ''],
                ['id' => 'abstract_body', 'type' => 'text', 'title' => 'Abstract', 'required' => true, 'help' => 'Maximum 300 words', 'multi_lines' => true],
                ['id' => 'keywords', 'type' => 'text', 'title' => 'Keywords', 'required' => true, 'help' => 'Comma separated'],
                ['id' => 'presentation_type', 'type' => 'select', 'title' => 'Preferred Presentation', 'required' => true, 'help' => '', 'options' => [['value' => 'oral', 'text' => 'Oral Presentation'], ['value' => 'poster', 'text' => 'Poster'], ['value' => 'either', 'text' => 'Either']]],
                ['id' => 'document', 'type' => 'files', 'title' => 'Upload Full Paper (Optional)', 'required' => false, 'help' => 'PDF or DOCX', 'max_number_of_files' => 1, 'max_file_size' => 20]
            ], '#0ea5e9'),
        ];
    }

    private function auditForm(): array
    {
        return [
            'name' => 'Audit Form Template', 
            'slug' => 'audit-form-template', 
            'short_description' => 'A structured audit form template to inspect compliance, quality, and operational standards.', 
            'description' => '<p>Our Audit Form Template gives auditors and compliance teams a repeatable checklist for inspecting processes, facilities, and systems. Standardize your audits and track findings with ease.</p><h2>Why and when to use an audit form</h2><p>Regular audits help organizations maintain quality, safety, and compliance. Use this template for internal audits, supplier assessments, safety inspections, and regulatory checklists.</p><h2>Who is this template for</h2><p>Compliance officers, quality managers, safety inspectors, and operations teams conducting routine audits.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms lets you build branching checklists, attach photos to findings, and export audit reports to share with stakeholders instantly.</p>', 
            'types' => ['audit_forms', 'inspection_forms'],
            'industries' => ['services_forms', 'insurance_forms'],
            'structure' => $this->structure('Audit Checklist', [
                ['id' => 'auditor_name', 'type' => 'text', 'title' => 'Auditor Name', 'required' => true, 'help' => ''],
                ['id' => 'audit_date', 'type' => 'date', 'title' => 'Audit Date', 'required' => true, 'help' => ''],
                ['id' => 'location', 'type' => 'text', 'title' => 'Location / Site', 'required' => true, 'help' => ''],
                ['id' => 'area', 'type' => 'select', 'title' => 'Audit Area', 'required' => true, 'help' => '', 'options' => [['value' => 'safety', 'text' => 'Safety'], ['value' => 'quality', 'text' => 'Quality'], ['value' => 'compliance', 'text' => 'Compliance'], ['value' => 'operations', 'text' => 'Operations']]],
                ['id' => 'finding_1', 'type' => 'scale', 'title' => 'Documentation is up to date', 'required' => true, 'help' => '', 'scale_min_value' => 1, 'scale_max_value' => 5, 'scale_step_value' => 1],
                ['id' => 'finding_2', 'type' => 'scale', 'title' => 'Processes followed correctly', 'required' => true, 'help' => '', 'scale_min_value' => 1, 'scale_max_value' => 5, 'scale_step_value' => 1],
                ['id' => 'finding_3', 'type' => 'scale', 'title' => 'Equipment in good condition', 'required' => true, 'help' => '', 'scale_min_value' => 1, 'scale_max_value' => 5, 'scale_step_value' => 1],
                ['id' => 'issues', 'type' => 'text', 'title' => 'Issues Found', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'recommendations', 'type' => 'text', 'title' => 'Recommendations', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'signature', 'type' => 'signature', 'title' => 'Auditor Signature', 'required' => true, 'help' => '']
            ], '#f59e0b'),
        ];
    }

    private function awardNomination(): array
    {
        return [
            'name' => 'Award Nomination Form Template', 
            'slug' => 'award-nomination-form-template', 
            'short_description' => 'An award nomination form template to collect submissions for awards, contests, and recognition programs.', 
            'description' => '<p>Our Award Nomination Form Template makes it simple to collect nominations for your awards, contests, and recognition programs. Gather nominee details, achievements, and supporting materials in one place.</p><h2>Why and when to use an award nomination form</h2><p>Whether you run an annual industry award, a community recognition program, or a staff appreciation contest, a nomination form keeps entries organized and complete.</p><h2>Who is this template for</h2><p>Award organizers, HR teams, event planners, media outlets, and industry associations running recognition programs.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms allows you to set nomination deadlines, collect supporting files, and review all submissions in one organized dashboard.</p>', 
            'types' => ['award_forms', 'application_forms'],
            'industries' => ['entertainment_forms', 'business_forms'],
            'structure' => $this->structure('Award Nomination', [
                ['id' => 'nominee_name', 'type' => 'text', 'title' => 'Nominee Name', 'required' => true, 'help' => ''],
                ['id' => 'nominee_email', 'type' => 'email', 'title' => 'Nominee Email', 'required' => true, 'help' => ''],
                ['id' => 'nominator_name', 'type' => 'text', 'title' => 'Your Name', 'required' => true, 'help' => ''],
                ['id' => 'award_category', 'type' => 'select', 'title' => 'Award Category', 'required' => true, 'help' => '', 'options' => [['value' => 'excellence', 'text' => 'Excellence'], ['value' => 'innovation', 'text' => 'Innovation'], ['value' => 'leadership', 'text' => 'Leadership'], ['value' => 'community', 'text' => 'Community Impact']]],
                ['id' => 'achievements', 'type' => 'text', 'title' => 'Key Achievements', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'impact', 'type' => 'text', 'title' => 'Why does this nominee deserve the award?', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'supporting_files', 'type' => 'files', 'title' => 'Supporting Materials', 'required' => false, 'help' => 'Optional', 'max_number_of_files' => 3, 'max_file_size' => 20]
            ], '#e11d48'),
        ];
    }

    private function calculationForm(): array
    {
        return [
            'name' => 'Calculation Form Template', 
            'slug' => 'calculation-form-template', 
            'short_description' => 'A calculation form template with automatic totals for quotes, estimates, and pricing calculators.', 
            'description' => '<p>Our Calculation Form Template shows how SharaForms can automatically compute totals as users answer questions. Perfect for pricing calculators, loan estimators, and order totals.</p><h2>Why and when to use a calculation form</h2><p>Use calculation forms when you need instant results: project quotes, budget estimators, BMI calculators, or order totals. Automatic formulas save your users time and reduce errors.</p><h2>Who is this template for</h2><p>Contractors, agencies, financial advisors, and businesses that quote prices or need self-service calculators.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms includes a formula engine that computes totals in real time, with conditional logic to show or hide fields based on answers.</p>', 
            'types' => ['calculation_forms', 'quote_forms'],
            'industries' => ['services_forms', 'banking_forms'],
            'structure' => $this->structure('Cost Calculator', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'project_type', 'type' => 'select', 'title' => 'Project Type', 'required' => true, 'help' => '', 'options' => [['value' => 'basic', 'text' => 'Basic Package - $499'], ['value' => 'standard', 'text' => 'Standard Package - $899'], ['value' => 'premium', 'text' => 'Premium Package - $1499']]],
                ['id' => 'quantity', 'type' => 'number', 'title' => 'Quantity', 'required' => true, 'help' => 'How many units or licenses do you need?'],
                ['id' => 'addons', 'type' => 'multi_select', 'title' => 'Add-ons', 'required' => false, 'help' => 'Select any additional services', 'options' => [['value' => 'design', 'text' => 'Extra Design - $150'], ['value' => 'support', 'text' => 'Priority Support - $99'], ['value' => 'training', 'text' => 'Training Session - $199']]],
                ['id' => 'budget', 'type' => 'slider', 'title' => 'Your Budget', 'required' => false, 'help' => '', 'slider_min_value' => 0, 'slider_max_value' => 10000, 'slider_step_value' => 100],
                $this->totalBlock('total_display', 'cv_total', 'Your Estimated Total', '$0'),
            ], '#14b8a6', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_total',
                        'Estimated Total',
                        'IF({project_type}="Basic Package - $499",499,IF({project_type}="Standard Package - $899",899,1499))*{quantity}'
                        . '+IF(CONTAINS({addons},"Extra Design - $150"),150,0)'
                        . '+IF(CONTAINS({addons},"Priority Support - $99"),99,0)'
                        . '+IF(CONTAINS({addons},"Training Session - $199"),199,0)'
                    ),
                ],
            ]),
        ];
    }

    private function checklistForm(): array
    {
        return [
            'name' => 'Checklist Form Template', 
            'slug' => 'checklist-form-template', 
            'short_description' => 'A practical checklist form template for task tracking, pre-flight checks, and daily operations.', 
            'description' => '<p>Our Checklist Form Template helps teams standardize recurring tasks and verify every step is complete. Perfect for daily operations, pre-flight checks, and onboarding procedures.</p><h2>Why and when to use a checklist form</h2><p>Checklists reduce errors in repetitive tasks and give you an audit trail that work was completed. Use them for inspections, opening/closing procedures, and team handoffs.</p><h2>Who is this template for</h2><p>Operations teams, shift managers, event staff, and any team that follows repeatable procedures.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms keeps a timestamped record of every checklist, sends reminders, and can alert managers when a task is overdue.</p>', 
            'types' => ['checklist_forms', 'tracking_forms'],
            'industries' => ['business_forms', 'services_forms'],
            'structure' => $this->structure('Task Checklist', [
                ['id' => 'staff_name', 'type' => 'text', 'title' => 'Staff Name', 'required' => true, 'help' => ''],
                ['id' => 'shift', 'type' => 'select', 'title' => 'Shift', 'required' => true, 'help' => '', 'options' => [['value' => 'morning', 'text' => 'Morning'], ['value' => 'afternoon', 'text' => 'Afternoon'], ['value' => 'evening', 'text' => 'Evening']]],
                ['id' => 'check_1', 'type' => 'checkbox', 'title' => 'Area cleaned and sanitized', 'required' => true, 'help' => ''],
                ['id' => 'check_2', 'type' => 'checkbox', 'title' => 'Equipment checked and logged', 'required' => true, 'help' => ''],
                ['id' => 'check_3', 'type' => 'checkbox', 'title' => 'Inventory counted', 'required' => true, 'help' => ''],
                ['id' => 'check_4', 'type' => 'checkbox', 'title' => 'Doors and windows secured', 'required' => true, 'help' => ''],
                ['id' => 'notes', 'type' => 'text', 'title' => 'Notes', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#10b981'),
        ];
    }

    private function contentSubmission(): array
    {
        return [
            'name' => 'Content Submission Form Template', 
            'slug' => 'content-submission-form-template', 
            'short_description' => 'A content submission form template to accept guest posts, user-generated content, and contributions.', 
            'description' => '<p>Our Content Submission Form Template lets you accept guest articles, user stories, photos, and other contributions from your audience in a structured and reviewable way.</p><h2>Why and when to use a content submission form</h2><p>Blogs, magazines, and community platforms use submission forms to gather user-generated content while capturing author details and usage rights.</p><h2>Who is this template for</h2><p>Blog editors, magazine publishers, community managers, and content platforms that accept contributions.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects the content and files together with licensing consent, so you always have permission to publish.</p>', 
            'types' => ['content_forms'],
            'industries' => ['web_design_forms', 'marketing_forms'],
            'structure' => $this->structure('Content Submission', [
                ['id' => 'author_name', 'type' => 'text', 'title' => 'Author Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'content_type', 'type' => 'select', 'title' => 'Content Type', 'required' => true, 'help' => '', 'options' => [['value' => 'article', 'text' => 'Article'], ['value' => 'photo', 'text' => 'Photo'], ['value' => 'video', 'text' => 'Video'], ['value' => 'story', 'text' => 'Personal Story']]],
                ['id' => 'title', 'type' => 'text', 'title' => 'Content Title', 'required' => true, 'help' => ''],
                ['id' => 'content_body', 'type' => 'text', 'title' => 'Content', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'content_files', 'type' => 'files', 'title' => 'Attachments', 'required' => false, 'help' => 'Images, videos, or documents', 'max_number_of_files' => 5, 'max_file_size' => 25],
                ['id' => 'rights', 'type' => 'checkbox', 'title' => 'I grant permission to publish this content', 'required' => true, 'help' => '']
            ], '#ec4899'),
        ];
    }

    private function evaluationForm(): array
    {
        return [
            'name' => 'Employee Evaluation Form Template', 
            'slug' => 'employee-evaluation-form-template', 
            'short_description' => 'An employee evaluation form template for performance reviews, appraisals, and feedback sessions.', 
            'description' => '<p>Our Employee Evaluation Form Template standardizes performance reviews so every team member is assessed on the same criteria. Capture ratings, achievements, and development goals.</p><h2>Why and when to use an evaluation form</h2><p>Use this template for periodic performance reviews, end-of-project assessments, and annual appraisals to ensure fair, consistent feedback.</p><h2>Who is this template for</h2><p>Managers, HR teams, and team leads conducting structured performance evaluations.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms stores review history securely, supports e-signatures, and can trigger follow-up actions through integrations.</p>', 
            'types' => ['evaluation_forms', 'feedback_forms'],
            'industries' => ['human_resources_forms', 'business_forms'],
            'structure' => $this->structure('Employee Evaluation', [
                ['id' => 'employee_name', 'type' => 'text', 'title' => 'Employee Name', 'required' => true, 'help' => ''],
                ['id' => 'reviewer_name', 'type' => 'text', 'title' => 'Reviewer Name', 'required' => true, 'help' => ''],
                ['id' => 'review_period', 'type' => 'text', 'title' => 'Review Period', 'required' => true, 'help' => 'e.g. Q1 2026'],
                ['id' => 'rating_quality', 'type' => 'rating', 'title' => 'Quality of Work', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'rating_productivity', 'type' => 'rating', 'title' => 'Productivity', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'rating_teamwork', 'type' => 'rating', 'title' => 'Teamwork', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'achievements', 'type' => 'text', 'title' => 'Key Achievements', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'improvements', 'type' => 'text', 'title' => 'Areas for Improvement', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'goals', 'type' => 'text', 'title' => 'Goals for Next Period', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'signature', 'type' => 'signature', 'title' => 'Sign-off', 'required' => true, 'help' => '']
            ], '#3b82f6'),
        ];
    }

    private function inspectionForm(): array
    {
        return [
            'name' => 'Inspection Form Template', 
            'slug' => 'inspection-form-template', 
            'short_description' => 'A mobile-friendly inspection form template for property, vehicle, and equipment inspections.', 
            'description' => '<p>Our Inspection Form Template helps you conduct and document inspections on site. Capture condition ratings, photos, and corrective actions in a consistent format.</p><h2>Why and when to use an inspection form</h2><p>Property managers, rental agencies, and maintenance teams use inspections to document condition before and after rentals, sales, or service calls.</p><h2>Who is this template for</h2><p>Property managers, maintenance crews, vehicle fleets, and quality control teams.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms works great on phones, supports photo uploads as evidence, and keeps a complete history of every inspection.</p>', 
            'types' => ['inspection_forms'],
            'industries' => ['real_estate_forms', 'services_forms'],
            'structure' => $this->structure('Inspection Report', [
                ['id' => 'inspector_name', 'type' => 'text', 'title' => 'Inspector Name', 'required' => true, 'help' => ''],
                ['id' => 'inspection_date', 'type' => 'date', 'title' => 'Inspection Date', 'required' => true, 'help' => ''],
                ['id' => 'asset', 'type' => 'text', 'title' => 'Asset / Property', 'required' => true, 'help' => ''],
                ['id' => 'condition_exterior', 'type' => 'scale', 'title' => 'Exterior Condition', 'required' => true, 'help' => '', 'scale_min_value' => 1, 'scale_max_value' => 5, 'scale_step_value' => 1],
                ['id' => 'condition_interior', 'type' => 'scale', 'title' => 'Interior Condition', 'required' => true, 'help' => '', 'scale_min_value' => 1, 'scale_max_value' => 5, 'scale_step_value' => 1],
                ['id' => 'condition_systems', 'type' => 'scale', 'title' => 'Systems Condition', 'required' => true, 'help' => '', 'scale_min_value' => 1, 'scale_max_value' => 5, 'scale_step_value' => 1],
                ['id' => 'photos', 'type' => 'files', 'title' => 'Inspection Photos', 'required' => false, 'help' => 'Attach photos as evidence', 'max_number_of_files' => 10, 'max_file_size' => 25],
                ['id' => 'issues', 'type' => 'text', 'title' => 'Issues / Notes', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'signature', 'type' => 'signature', 'title' => 'Inspector Signature', 'required' => true, 'help' => '']
            ], '#f59e0b'),
        ];
    }

    private function interviewForm(): array
    {
        return [
            'name' => 'Interview Form Template', 
            'slug' => 'interview-form-template', 
            'short_description' => 'An interview feedback form template for recruiters to score and evaluate candidates consistently.', 
            'description' => '<p>Our Interview Form Template gives recruiters a structured way to evaluate candidates. Score competencies, note strengths, and record next steps after every interview.</p><h2>Why and when to use an interview form</h2><p>Structured interviews reduce bias and produce fairer hiring decisions. Use this form after every interview round to capture consistent, comparable feedback.</p><h2>Who is this template for</h2><p>Recruiters, hiring managers, and interview panels evaluating candidates.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms centralizes interview feedback, keeps records for compliance, and can route scores to your hiring pipeline via integrations.</p>', 
            'types' => ['interview_forms', 'evaluation_forms'],
            'industries' => ['human_resources_forms'],
            'structure' => $this->structure('Interview Feedback', [
                ['id' => 'candidate_name', 'type' => 'text', 'title' => 'Candidate Name', 'required' => true, 'help' => ''],
                ['id' => 'position', 'type' => 'text', 'title' => 'Position', 'required' => true, 'help' => ''],
                ['id' => 'interviewer', 'type' => 'text', 'title' => 'Interviewer', 'required' => true, 'help' => ''],
                ['id' => 'score_skills', 'type' => 'rating', 'title' => 'Technical Skills', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'score_communication', 'type' => 'rating', 'title' => 'Communication', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'score_culture', 'type' => 'rating', 'title' => 'Culture Fit', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'strengths', 'type' => 'text', 'title' => 'Strengths', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'concerns', 'type' => 'text', 'title' => 'Concerns', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'recommendation', 'type' => 'select', 'title' => 'Recommendation', 'required' => true, 'help' => '', 'options' => [['value' => 'hire', 'text' => 'Hire'], ['value' => 'maybe', 'text' => 'Maybe'], ['value' => 'no', 'text' => 'Do Not Hire']]]
            ], '#6366f1'),
        ];
    }

    private function legalIntake(): array
    {
        return [
            'name' => 'Legal Intake Form Template', 
            'slug' => 'legal-intake-form-template', 
            'short_description' => 'A confidential legal intake form template for law firms to gather case details from prospective clients.', 
            'description' => '<p>Our Legal Intake Form Template helps law firms collect initial case details from prospective clients in a professional and organized manner.</p><h2>Why and when to use a legal intake form</h2><p>Intake forms let potential clients describe their matter, upload documents, and share contact details before a consultation, saving time for both sides.</p><h2>Who is this template for</h2><p>Law firms, attorneys, and legal aid organizations that need to screen and route new inquiries.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms keeps intake submissions organized, supports secure file uploads, and can route new cases to the right team member automatically.</p>', 
            'types' => ['legal_forms', 'application_forms'],
            'industries' => ['services_forms'],
            'structure' => $this->structure('Legal Intake', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'case_type', 'type' => 'select', 'title' => 'Case Type', 'required' => true, 'help' => '', 'options' => [['value' => 'personal_injury', 'text' => 'Personal Injury'], ['value' => 'family', 'text' => 'Family Law'], ['value' => 'business', 'text' => 'Business / Corporate'], ['value' => 'criminal', 'text' => 'Criminal Defense'], ['value' => 'real_estate', 'text' => 'Real Estate'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'case_details', 'type' => 'text', 'title' => 'Case Description', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'opposing_party', 'type' => 'text', 'title' => 'Opposing Party (if any)', 'required' => false, 'help' => ''],
                ['id' => 'documents', 'type' => 'files', 'title' => 'Upload Documents', 'required' => false, 'help' => 'Optional supporting documents', 'max_number_of_files' => 5, 'max_file_size' => 20],
                ['id' => 'consent', 'type' => 'checkbox', 'title' => 'I consent to being contacted about my legal matter', 'required' => true, 'help' => '']
            ], '#1e293b'),
        ];
    }

    private function membershipApplication(): array
    {
        return [
            'name' => 'Membership Application Form Template', 
            'slug' => 'membership-application-form-template', 
            'short_description' => 'A membership application form template for clubs, associations, and organizations to onboard new members.', 
            'description' => '<p>Our Membership Application Form Template helps clubs, associations, and organizations collect applications from prospective members and manage their onboarding.</p><h2>Why and when to use a membership application form</h2><p>Membership organizations need to capture applicant details, preferences, and agreements before approval. A digital form makes the process quick and consistent.</p><h2>Who is this template for</h2><p>Professional associations, clubs, alumni networks, gyms, and community organizations.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms supports payment collection for membership fees, consent checkboxes for your bylaws, and automated approval notifications.</p>', 
            'types' => ['membership_forms', 'application_forms'],
            'industries' => ['business_forms', 'church_forms'],
            'structure' => $this->structure('Membership Application', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'membership_type', 'type' => 'select', 'title' => 'Membership Type', 'required' => true, 'help' => '', 'options' => [['value' => 'individual', 'text' => 'Individual'], ['value' => 'family', 'text' => 'Family'], ['value' => 'corporate', 'text' => 'Corporate'], ['value' => 'student', 'text' => 'Student']]],
                ['id' => 'referred_by', 'type' => 'text', 'title' => 'How did you hear about us?', 'required' => false, 'help' => ''],
                ['id' => 'interests', 'type' => 'multi_select', 'title' => 'Areas of Interest', 'required' => false, 'help' => '', 'options' => [['value' => 'events', 'text' => 'Events'], ['value' => 'mentorship', 'text' => 'Mentorship'], ['value' => 'volunteering', 'text' => 'Volunteering'], ['value' => 'advocacy', 'text' => 'Advocacy']]],
                ['id' => 'agree_bylaws', 'type' => 'checkbox', 'title' => 'I agree to the organization bylaws', 'required' => true, 'help' => '']
            ], '#7c3aed'),
        ];
    }

    private function petitionForm(): array
    {
        return [
            'name' => 'Petition Form Template', 
            'slug' => 'petition-form-template', 
            'short_description' => 'A petition form template to collect signatures and show support for your cause.', 
            'description' => '<p>Our Petition Form Template helps you collect digital signatures and support for your cause, campaign, or community initiative.</p><h2>Why and when to use a petition form</h2><p>Petitions demonstrate public support to decision-makers. A digital petition form lets supporters sign instantly from any device and share it easily.</p><h2>Who is this template for</h2><p>Activists, community organizers, nonprofits, and advocacy groups collecting support for a cause.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects verified signatories with contact details, lets you export signatures, and integrates with email tools for follow-up campaigns.</p>', 
            'types' => ['petition_forms', 'signup_forms'],
            'industries' => ['business_forms', 'charity_forms'],
            'structure' => $this->structure('Sign the Petition', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'city', 'type' => 'text', 'title' => 'City / Region', 'required' => true, 'help' => ''],
                ['id' => 'comment', 'type' => 'text', 'title' => 'Your Comment (Optional)', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'agree', 'type' => 'checkbox', 'title' => 'I support this petition and consent to my signature being shown', 'required' => true, 'help' => ''],
                ['id' => 'signature', 'type' => 'signature', 'title' => 'Sign here', 'required' => true, 'help' => '']
            ], '#ef4444'),
        ];
    }

    private function pollForm(): array
    {
        return [
            'name' => 'Poll Form Template',
            'slug' => 'poll-form-template',
            'short_description' => 'A lightweight poll form template to gather quick opinions and votes from your audience.',
            'description' => '<p>Our Poll Form Template is perfect for gathering quick opinions from your audience. Ask one focused question, capture the reasoning behind votes with a conditional follow-up, and collect votes instantly.</p><h2>Why and when to use a poll</h2><p>Polls are great for social media, product decisions, community voting, and gathering fast feedback on a single question. The optional follow-up field only appears once someone has voted, so every response stays quick while still giving you the "why" behind the result.</p><h2>Who is this template for</h2><p>Marketers, social media managers, product teams, and community managers running quick opinion polls.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms makes polls easy to share and embed, uses conditional logic to keep the form short, and shows instant submission summaries so you can read results as they come in.</p>',
            'types' => ['polls', 'voting_forms'],
            'industries' => ['marketing_forms', 'entertainment_forms'],
            'structure' => $this->structure('Quick Poll', [
                $this->nfText('intro', '<h2>Quick Poll</h2><p>One question, ten seconds. Help us decide what to build next!</p>'),
                ['id' => 'feature_choice', 'type' => 'radio', 'title' => 'Which feature should we build next?', 'required' => true, 'help' => '', 'options' => [['value' => 'dark_mode', 'text' => 'Dark Mode'], ['value' => 'mobile_app', 'text' => 'Mobile App'], ['value' => 'integrations', 'text' => 'More Integrations'], ['value' => 'custom_reports', 'text' => 'Custom Reports & Exports']]],
                ['id' => 'vote_reason', 'type' => 'text', 'title' => 'Nice! Why does that matter to you?', 'required' => false, 'help' => 'Optional - one line helps us understand the vote', 'multi_lines' => false,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('feature_choice', 'select', 'is_not_empty', true)])],
                $this->emailField('email', 'Email (optional)', false),
                ['id' => 'feedback', 'type' => 'text', 'title' => 'Any other comments?', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#0ea5e9'),
        ];
    }

    private function quizForm(): array
    {
        return [
            'name' => 'Quiz Form Template', 
            'slug' => 'quiz-form-template', 
            'short_description' => 'An engaging quiz form template to test knowledge, run trivia nights, and evaluate learning.', 
            'description' => '<p>Our Quiz Form Template helps you build engaging quizzes for education, training, trivia nights, and knowledge checks.</p><h2>Why and when to use a quiz form</h2><p>Quizzes reinforce learning, assess understanding, and add fun to events. Use them in classrooms, training programs, and team-building activities.</p><h2>Who is this template for</h2><p>Teachers, trainers, event hosts, and companies running knowledge checks or trivia games.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms supports multiple question formats, and you can analyze results to see which questions your audience found challenging.</p>', 
            'types' => ['quiz_forms', 'polls'],
            'industries' => ['education_forms', 'entertainment_forms'],
            'structure' => $this->structure('Knowledge Quiz', [
                ['id' => 'name', 'type' => 'text', 'title' => 'Your Name', 'required' => false, 'help' => ''],
                ['id' => 'q1', 'type' => 'radio', 'title' => 'Question 1: What is the capital of France?', 'required' => true, 'help' => '', 'options' => [['value' => 'paris', 'text' => 'Paris'], ['value' => 'london', 'text' => 'London'], ['value' => 'rome', 'text' => 'Rome'], ['value' => 'berlin', 'text' => 'Berlin']]],
                ['id' => 'q2', 'type' => 'radio', 'title' => 'Question 2: How many continents are there?', 'required' => true, 'help' => '', 'options' => [['value' => 'five', 'text' => '5'], ['value' => 'six', 'text' => '6'], ['value' => 'seven', 'text' => '7'], ['value' => 'eight', 'text' => '8']]],
                ['id' => 'q3', 'type' => 'radio', 'title' => 'Question 3: Which planet is known as the Red Planet?', 'required' => true, 'help' => '', 'options' => [['value' => 'venus', 'text' => 'Venus'], ['value' => 'mars', 'text' => 'Mars'], ['value' => 'jupiter', 'text' => 'Jupiter'], ['value' => 'saturn', 'text' => 'Saturn']]],
                ['id' => 'q4', 'type' => 'radio', 'title' => 'Question 4: What is 7 x 8?', 'required' => true, 'help' => '', 'options' => [['value' => '54', 'text' => '54'], ['value' => '56', 'text' => '56'], ['value' => '58', 'text' => '58'], ['value' => '64', 'text' => '64']]],
                ['id' => 'q5', 'type' => 'radio', 'title' => 'Question 5: Which ocean is the largest?', 'required' => true, 'help' => '', 'options' => [['value' => 'atlantic', 'text' => 'Atlantic'], ['value' => 'indian', 'text' => 'Indian'], ['value' => 'pacific', 'text' => 'Pacific'], ['value' => 'arctic', 'text' => 'Arctic']]],
                $this->totalBlock('score_display', 'cv_score', 'Your Score', '0 / 5'),
            ], '#8b5cf6', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_score',
                        'Quiz Score',
                        'IF({q1}="Paris",1,0)+IF({q2}="7",1,0)+IF({q3}="Mars",1,0)+IF({q4}="56",1,0)+IF({q5}="Pacific",1,0)'
                    ),
                ],
            ]),
        ];
    }

    private function quoteRequest(): array
    {
        return [
            'name' => 'Quote Request Form Template', 
            'slug' => 'quote-request-form-template', 
            'short_description' => 'A quote request form template for businesses to receive detailed pricing inquiries from prospects.', 
            'description' => '<p>Our Quote Request Form Template helps service providers gather the details they need to send accurate, personalized quotes.</p><h2>Why and when to use a quote request form</h2><p>Quote requests collect project scope, budget, and timelines up front, so your team can respond with relevant proposals instead of generic pricing.</p><h2>Who is this template for</h2><p>Contractors, agencies, manufacturers, and any B2B business that responds to pricing inquiries.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms routes quote requests to the right person, collects supporting documents, and can trigger a follow-up email instantly.</p>', 
            'types' => ['quote_forms', 'request_forms'],
            'industries' => ['services_forms', 'business_forms'],
            'structure' => $this->structure('Request a Quote', [
                ['id' => 'company', 'type' => 'text', 'title' => 'Company Name', 'required' => true, 'help' => ''],
                ['id' => 'contact_name', 'type' => 'text', 'title' => 'Contact Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'project_details', 'type' => 'text', 'title' => 'Project Details', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'budget', 'type' => 'select', 'title' => 'Budget Range', 'required' => false, 'help' => '', 'options' => [['value' => 'under_1000', 'text' => 'Under $1,000'], ['value' => '1000_5000', 'text' => '$1,000 - $5,000'], ['value' => '5000_10000', 'text' => '$5,000 - $10,000'], ['value' => 'over_10000', 'text' => 'Over $10,000']]],
                ['id' => 'timeline', 'type' => 'select', 'title' => 'Timeline', 'required' => false, 'help' => '', 'options' => [['value' => 'asap', 'text' => 'ASAP'], ['value' => '1_3_months', 'text' => '1-3 Months'], ['value' => '3_6_months', 'text' => '3-6 Months'], ['value' => 'flexible', 'text' => 'Flexible']]],
                ['id' => 'documents', 'type' => 'files', 'title' => 'Upload Brief / Specs', 'required' => false, 'help' => 'Optional', 'max_number_of_files' => 3, 'max_file_size' => 20]
            ], '#14b8a6'),
        ];
    }

    private function recommendationForm(): array
    {
        return [
            'name' => 'Recommendation Form Template', 
            'slug' => 'recommendation-form-template', 
            'short_description' => 'A recommendation request form template to gather reference letters from mentors and employers.', 
            'description' => '<p>Our Recommendation Form Template helps candidates request structured recommendations from referees and lets referees submit feedback easily.</p><h2>Why and when to use a recommendation form</h2><p>Colleges, graduate programs, and employers require recommendation letters. A structured form makes it easy for referees to respond quickly.</p><h2>Who is this template for</h2><p>Students, job seekers, program applicants, and the educators or employers who recommend them.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms lets referees upload letters, rate candidates on key competencies, and submit everything with one click.</p>', 
            'types' => ['recommendation_forms'],
            'industries' => ['education_forms', 'human_resources_forms'],
            'structure' => $this->structure('Recommendation Letter Request', [
                ['id' => 'candidate_name', 'type' => 'text', 'title' => 'Candidate Name', 'required' => true, 'help' => ''],
                ['id' => 'referee_name', 'type' => 'text', 'title' => 'Your Name', 'required' => true, 'help' => ''],
                ['id' => 'referee_role', 'type' => 'text', 'title' => 'Your Relationship to Candidate', 'required' => true, 'help' => ''],
                ['id' => 'rating_academics', 'type' => 'rating', 'title' => 'Academic Performance', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'rating_character', 'type' => 'rating', 'title' => 'Character & Integrity', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'recommendation_letter', 'type' => 'text', 'title' => 'Your Recommendation', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'letter_file', 'type' => 'files', 'title' => 'Upload Letter (Optional)', 'required' => false, 'help' => 'PDF or DOC', 'max_number_of_files' => 1, 'max_file_size' => 10]
            ], '#10b981'),
        ];
    }

    private function incidentReport(): array
    {
        return [
            'name' => 'Incident Report Form Template', 
            'slug' => 'incident-report-form-template', 
            'short_description' => 'An incident report form template to document accidents, safety events, and near-misses accurately.', 
            'description' => '<p>Our Incident Report Form Template helps teams document accidents, safety events, and near-misses promptly and accurately.</p><h2>Why and when to use an incident report</h2><p>Workplace incidents must be documented quickly for safety compliance, insurance, and prevention. A structured report captures all essential details consistently.</p><h2>Who is this template for</h2><p>Safety officers, HR teams, facility managers, and operations staff responsible for incident documentation.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms timestamps every submission, supports photo evidence, and alerts the right people immediately when an incident is logged.</p>', 
            'types' => ['report_forms'],
            'industries' => ['services_forms', 'insurance_forms'],
            'structure' => $this->structure('Incident Report', [
                ['id' => 'reporter_name', 'type' => 'text', 'title' => 'Reporter Name', 'required' => true, 'help' => ''],
                ['id' => 'reporter_role', 'type' => 'text', 'title' => 'Reporter Role', 'required' => true, 'help' => ''],
                ['id' => 'incident_date', 'type' => 'date', 'title' => 'Incident Date', 'required' => true, 'help' => ''],
                ['id' => 'location', 'type' => 'text', 'title' => 'Incident Location', 'required' => true, 'help' => ''],
                ['id' => 'incident_type', 'type' => 'select', 'title' => 'Incident Type', 'required' => true, 'help' => '', 'options' => [['value' => 'accident', 'text' => 'Accident'], ['value' => 'injury', 'text' => 'Injury'], ['value' => 'property_damage', 'text' => 'Property Damage'], ['value' => 'near_miss', 'text' => 'Near Miss'], ['value' => 'security', 'text' => 'Security Incident']]],
                ['id' => 'severity', 'type' => 'scale', 'title' => 'Severity', 'required' => true, 'help' => '', 'scale_min_value' => 1, 'scale_max_value' => 5, 'scale_step_value' => 1],
                ['id' => 'description', 'type' => 'text', 'title' => 'Incident Description', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'photos', 'type' => 'files', 'title' => 'Evidence Photos', 'required' => false, 'help' => 'Optional', 'max_number_of_files' => 5, 'max_file_size' => 25],
                ['id' => 'corrective_actions', 'type' => 'text', 'title' => 'Immediate Corrective Actions', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#ef4444'),
        ];
    }

    private function reservationForm(): array
    {
        return [
            'name' => 'Reservation Form Template', 
            'slug' => 'reservation-form-template', 
            'short_description' => 'A reservation form template for restaurants, hotels, and venues to accept bookings online.', 
            'description' => '<p>Our Reservation Form Template lets restaurants, hotels, and venues accept bookings online, reducing phone traffic and double-bookings.</p><h2>Why and when to use a reservation form</h2><p>Online reservations give guests the convenience of booking anytime and give staff a clean, structured record of every request.</p><h2>Who is this template for</h2><p>Restaurants, hotels, event venues, and service businesses that manage bookings.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms sends confirmation emails automatically, supports party size and special requests, and can set submission limits per day.</p>', 
            'types' => ['reservation_forms', 'booking_forms'],
            'industries' => ['ecommerce_forms', 'services_forms'],
            'structure' => $this->structure('Make a Reservation', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'reservation_date', 'type' => 'date', 'title' => 'Reservation Date', 'required' => true, 'help' => ''],
                ['id' => 'party_size', 'type' => 'number', 'title' => 'Party Size', 'required' => true, 'help' => ''],
                ['id' => 'time_slot', 'type' => 'select', 'title' => 'Time Slot', 'required' => true, 'help' => '', 'options' => [['value' => 'lunch', 'text' => 'Lunch (12PM - 3PM)'], ['value' => 'dinner_early', 'text' => 'Early Dinner (5PM - 7PM)'], ['value' => 'dinner_late', 'text' => 'Late Dinner (7PM - 10PM)']]],
                ['id' => 'special_requests', 'type' => 'text', 'title' => 'Special Requests', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#f59e0b'),
        ];
    }

    private function sponsorshipApplication(): array
    {
        return [
            'name' => 'Sponsorship Application Form Template', 
            'slug' => 'sponsorship-application-form-template', 
            'short_description' => 'A sponsorship application form template to review and manage sponsorship requests efficiently.', 
            'description' => '<p>Our Sponsorship Application Form Template helps organizations review sponsorship and partnership requests in a consistent, fair way.</p><h2>Why and when to use a sponsorship application</h2><p>Companies receive many sponsorship requests. A structured application captures the event details, audience, and value proposition needed to evaluate each one.</p><h2>Who is this template for</h2><p>Marketing teams, event sponsors, and community relations departments managing sponsorship programs.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms organizes all sponsorship requests, supports budget requirements, and can route approvals to the right decision-makers.</p>', 
            'types' => ['sponsorship_forms', 'application_forms'],
            'industries' => ['marketing_forms', 'sports_forms'],
            'structure' => $this->structure('Sponsorship Application', [
                ['id' => 'organization', 'type' => 'text', 'title' => 'Organization Name', 'required' => true, 'help' => ''],
                ['id' => 'contact_name', 'type' => 'text', 'title' => 'Contact Person', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'event_name', 'type' => 'text', 'title' => 'Event Name', 'required' => true, 'help' => ''],
                ['id' => 'event_date', 'type' => 'date', 'title' => 'Event Date', 'required' => true, 'help' => ''],
                ['id' => 'event_size', 'type' => 'select', 'title' => 'Expected Audience', 'required' => true, 'help' => '', 'options' => [['value' => 'under_100', 'text' => 'Under 100'], ['value' => '100_500', 'text' => '100-500'], ['value' => '500_1000', 'text' => '500-1,000'], ['value' => 'over_1000', 'text' => '1,000+']]],
                ['id' => 'sponsorship_tier', 'type' => 'select', 'title' => 'Requested Tier', 'required' => true, 'help' => '', 'options' => [['value' => 'bronze', 'text' => 'Bronze - $500'], ['value' => 'silver', 'text' => 'Silver - $1,000'], ['value' => 'gold', 'text' => 'Gold - $2,500'], ['value' => 'custom', 'text' => 'Custom']]],
                ['id' => 'benefits', 'type' => 'text', 'title' => 'Benefits for Sponsors', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'proposal', 'type' => 'files', 'title' => 'Upload Proposal (Optional)', 'required' => false, 'help' => 'Optional', 'max_number_of_files' => 1, 'max_file_size' => 20]
            ], '#db2777'),
        ];
    }

    private function subscriptionForm(): array
    {
        return [
            'name' => 'Subscription Form Template', 
            'slug' => 'subscription-form-template', 
            'short_description' => 'A subscription form template to grow your email list and capture newsletter signups.', 
            'description' => '<p>Our Subscription Form Template helps you grow your email list and capture newsletter signups from your website, blog, or landing pages.</p><h2>Why and when to use a subscription form</h2><p>Email marketing starts with list growth. A clean subscription form with a clear offer converts visitors into subscribers you can nurture.</p><h2>Who is this template for</h2><p>Bloggers, marketers, SaaS companies, and businesses building their email audiences.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms integrates with email marketing platforms, sends welcome emails automatically, and can be embedded anywhere on your site.</p>', 
            'types' => ['subscription_forms', 'signup_forms'],
            'industries' => ['marketing_forms', 'advertising_forms'],
            'structure' => $this->structure('Subscribe', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'interests', 'type' => 'multi_select', 'title' => 'What are you interested in?', 'required' => false, 'help' => '', 'options' => [['value' => 'news', 'text' => 'News & Updates'], ['value' => 'offers', 'text' => 'Offers & Promotions'], ['value' => 'tips', 'text' => 'Tips & How-tos'], ['value' => 'events', 'text' => 'Events & Webinars']]],
                ['id' => 'subscribe', 'type' => 'toggle_switch', 'title' => 'Subscribe to our newsletter', 'required' => true, 'help' => '', 'use_toggle_switch' => true],
                ['id' => 'agree', 'type' => 'checkbox', 'title' => 'I agree to the privacy policy', 'required' => true, 'help' => '']
            ], '#3b82f6'),
        ];
    }

    private function summerCamp(): array
    {
        return [
            'name' => 'Summer Camp Registration Form Template', 
            'slug' => 'summer-camp-registration-form-template', 
            'short_description' => 'A summer camp registration form template for camps and youth programs to enroll campers.', 
            'description' => '<p>Our Summer Camp Registration Form Template helps camps and youth programs register campers, collect emergency information, and manage enrollment.</p><h2>Why and when to use a summer camp registration form</h2><p>Camps need camper details, medical information, permissions, and program preferences before each session. A digital form keeps everything organized.</p><h2>Who is this template for</h2><p>Summer camps, youth programs, sports camps, and after-school programs.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects emergency contacts, medical consents, and payments in one place, with confirmation emails sent automatically.</p>', 
            'types' => ['summer_camp_surveys', 'registration_forms'],
            'industries' => ['summer_camps_forms', 'education_forms'],
            'structure' => $this->structure('Summer Camp Registration', [
                ['id' => 'camper_name', 'type' => 'text', 'title' => 'Camper Name', 'required' => true, 'help' => ''],
                ['id' => 'camper_age', 'type' => 'number', 'title' => 'Camper Age', 'required' => true, 'help' => ''],
                ['id' => 'parent_name', 'type' => 'text', 'title' => 'Parent / Guardian Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Parent Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Parent Phone', 'required' => true, 'help' => ''],
                ['id' => 'session', 'type' => 'select', 'title' => 'Camp Session', 'required' => true, 'help' => '', 'options' => [['value' => 'session_1', 'text' => 'Session 1 (June)'], ['value' => 'session_2', 'text' => 'Session 2 (July)'], ['value' => 'session_3', 'text' => 'Session 3 (August)'], ['value' => 'full_summer', 'text' => 'Full Summer']]],
                ['id' => 'activities', 'type' => 'multi_select', 'title' => 'Preferred Activities', 'required' => false, 'help' => '', 'options' => [['value' => 'swimming', 'text' => 'Swimming'], ['value' => 'arts', 'text' => 'Arts & Crafts'], ['value' => 'sports', 'text' => 'Sports'], ['value' => 'nature', 'text' => 'Nature Hikes'], ['value' => 'music', 'text' => 'Music']]],
                ['id' => 'medical_conditions', 'type' => 'text', 'title' => 'Medical Conditions / Allergies', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'emergency_contact', 'type' => 'text', 'title' => 'Emergency Contact', 'required' => true, 'help' => ''],
                ['id' => 'consent', 'type' => 'checkbox', 'title' => 'I consent to my child participating in camp activities', 'required' => true, 'help' => ''],
                $this->totalBlock('camp_fee_display', 'cv_camp_fee', 'Camp Fee', '$0'),
            ], '#f59e0b', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_camp_fee',
                        'Camp Fee',
                        'IF({session}="Full Summer",900,350)'
                    ),
                ],
            ]),
        ];
    }

    private function telecommutingForm(): array
    {
        return [
            'name' => 'Telecommuting Agreement Form Template', 
            'slug' => 'telecommuting-agreement-form-template', 
            'short_description' => 'A telecommuting agreement form template to formalize remote work arrangements with your team.', 
            'description' => '<p>Our Telecommuting Agreement Form Template helps HR teams formalize remote work arrangements, capturing work schedules, equipment needs, and expectations.</p><h2>Why and when to use a telecommuting agreement</h2><p>Remote and hybrid work benefits from clear agreements. Document schedules, communication expectations, and equipment assignments to keep everyone aligned.</p><h2>Who is this template for</h2><p>HR teams, managers, and companies implementing or formalizing remote work policies.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms captures the agreement, collects the employee signature digitally, and keeps a compliant record for your HR files.</p>', 
            'types' => ['telecommuting_forms', 'request_forms'],
            'industries' => ['it_forms', 'human_resources_forms'],
            'structure' => $this->structure('Telecommuting Agreement', [
                ['id' => 'employee_name', 'type' => 'text', 'title' => 'Employee Name', 'required' => true, 'help' => ''],
                ['id' => 'department', 'type' => 'text', 'title' => 'Department', 'required' => true, 'help' => ''],
                ['id' => 'manager', 'type' => 'text', 'title' => 'Manager', 'required' => true, 'help' => ''],
                ['id' => 'work_schedule', 'type' => 'text', 'title' => 'Proposed Work Schedule', 'required' => true, 'help' => 'e.g. Mon-Fri, 9AM-5PM', 'multi_lines' => true],
                ['id' => 'work_location', 'type' => 'select', 'title' => 'Work Location', 'required' => true, 'help' => '', 'options' => [['value' => 'home', 'text' => 'Home Office'], ['value' => 'hybrid', 'text' => 'Hybrid (mix of office & remote)'], ['value' => 'other', 'text' => 'Other Location']]],
                ['id' => 'equipment', 'type' => 'multi_select', 'title' => 'Equipment Needed', 'required' => false, 'help' => '', 'options' => [['value' => 'laptop', 'text' => 'Laptop'], ['value' => 'monitor', 'text' => 'Monitor'], ['value' => 'headset', 'text' => 'Headset'], ['value' => 'desk', 'text' => 'Standing Desk'], ['value' => 'internet', 'text' => 'Internet Stipend']]],
                ['id' => 'agree', 'type' => 'checkbox', 'title' => 'I agree to the telecommuting policy', 'required' => true, 'help' => ''],
                ['id' => 'signature', 'type' => 'signature', 'title' => 'Employee Signature', 'required' => true, 'help' => '']
            ], '#6366f1'),
        ];
    }

    private function trackingForm(): array
    {
        return [
            'name' => 'Asset Tracking Form Template', 
            'slug' => 'asset-tracking-form-template', 
            'short_description' => 'An asset tracking form template to log equipment checkouts, returns, and maintenance.', 
            'description' => '<p>Our Asset Tracking Form Template helps teams log equipment checkouts, returns, and maintenance so nothing gets lost or forgotten.</p><h2>Why and when to use an asset tracking form</h2><p>Laptops, tools, cameras, and vehicles need to be tracked. A checkout form records who has what, when, and in what condition.</p><h2>Who is this template for</h2><p>IT departments, warehouse teams, production crews, and anyone managing shared equipment.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms keeps a timestamped log of every transaction and can alert you when items are overdue.</p>', 
            'types' => ['tracking_forms'],
            'industries' => ['it_forms', 'business_forms'],
            'structure' => $this->structure('Asset Checkout', [
                ['id' => 'employee_name', 'type' => 'text', 'title' => 'Employee Name', 'required' => true, 'help' => ''],
                ['id' => 'asset', 'type' => 'text', 'title' => 'Asset / Item', 'required' => true, 'help' => ''],
                ['id' => 'asset_tag', 'type' => 'text', 'title' => 'Asset Tag / Serial Number', 'required' => true, 'help' => ''],
                ['id' => 'action', 'type' => 'select', 'title' => 'Action', 'required' => true, 'help' => '', 'options' => [['value' => 'checkout', 'text' => 'Check Out'], ['value' => 'return', 'text' => 'Return'], ['value' => 'maintenance', 'text' => 'Maintenance'], ['value' => 'transfer', 'text' => 'Transfer']]],
                ['id' => 'date', 'type' => 'date', 'title' => 'Date', 'required' => true, 'help' => ''],
                ['id' => 'condition', 'type' => 'scale', 'title' => 'Item Condition', 'required' => true, 'help' => '', 'scale_min_value' => 1, 'scale_max_value' => 5, 'scale_step_value' => 1],
                ['id' => 'expected_return', 'type' => 'date', 'title' => 'Expected Return Date (for checkouts)', 'required' => false, 'help' => ''],
                ['id' => 'notes', 'type' => 'text', 'title' => 'Notes', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#0ea5e9'),
        ];
    }

    private function votingForm(): array
    {
        return [
            'name' => 'Voting Form Template',
            'slug' => 'voting-form-template',
            'short_description' => 'A voting form template for elections, award ballots, and community decision-making.',
            'description' => '<p>Our Voting Form Template helps you run elections, award ballots, and community decisions with a simple, structured voting flow: three positions, distinct candidate slates, and an optional write-in.</p><h2>Why and when to use a voting form</h2><p>Use voting forms for board elections, staff decisions, community polls, and award ballots where you need a clear, auditable result. Separate questions per position prevent confusion, and the write-in option keeps the ballot fair without extra forms.</p><h2>Who is this template for</h2><p>Organizations, associations, committees, and communities running structured votes.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms secures votes with authentication options, lets you limit submissions to one per person, and shows the write-in field only to voters who ask for it through conditional logic.</p>',
            'types' => ['voting_forms', 'polls'],
            'industries' => ['business_forms', 'church_forms'],
            'structure' => $this->structure('Official Ballot', [
                $this->nfText('intro', '<h2>Annual Board Election</h2><p>One vote per member. Choose a candidate or abstain for each position.</p>'),
                ['id' => 'voter_name', 'type' => 'text', 'title' => 'Voter Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => 'Used to verify membership'],
                ['id' => 'president_vote', 'type' => 'radio', 'title' => 'President: choose a candidate', 'required' => true, 'help' => '', 'options' => [['value' => 'pres_amara', 'text' => 'Amara Okafor'], ['value' => 'pres_luis', 'text' => 'Luis Fernandez'], ['value' => 'priya_pres', 'text' => 'Priya Sharma'], ['value' => 'abstain_1', 'text' => 'Abstain']]],
                ['id' => 'secretary_vote', 'type' => 'radio', 'title' => 'Secretary: choose a candidate', 'required' => true, 'help' => '', 'options' => [['value' => 'sec_dana', 'text' => 'Dana Whitfield'], ['value' => 'sec_tom', 'text' => 'Tom Becker'], ['value' => 'sec_leila', 'text' => 'Leila Haddad'], ['value' => 'abstain_2', 'text' => 'Abstain']]],
                ['id' => 'treasurer_vote', 'type' => 'radio', 'title' => 'Treasurer: choose a candidate', 'required' => true, 'help' => '', 'options' => [['value' => 'tres_marcus', 'text' => 'Marcus Lee'], ['value' => 'tres_ingrid', 'text' => 'Ingrid Svensson'], ['value' => 'tres_ravi', 'text' => 'Ravi Patel'], ['value' => 'abstain_3', 'text' => 'Abstain']]],
                ['id' => 'write_in', 'type' => 'checkbox', 'title' => 'I would like to write in a candidate', 'required' => false, 'help' => ''],
                ['id' => 'write_in_details', 'type' => 'text', 'title' => 'Write-in Candidate & Position', 'required' => false, 'help' => 'Candidate name and the position you are nominating them for',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('write_in', 'checkbox', 'is_checked')])],
                ['id' => 'comments', 'type' => 'text', 'title' => 'Comments (Optional)', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#059669'),
        ];
    }

    private function weddingForm(): array
    {
        return [
            'name' => 'Wedding RSVP Form Template', 
            'slug' => 'wedding-rsvp-form-template', 
            'short_description' => 'An elegant wedding RSVP form template to collect guest responses and dietary preferences.', 
            'description' => '<p>Our Wedding RSVP Form Template helps couples collect guest responses, meal choices, and song requests in a beautiful, organized way.</p><h2>Why and when to use a wedding RSVP form</h2><p>Managing a guest list is easier with a digital RSVP. Guests respond instantly, and you get accurate headcounts for the venue and caterer.</p><h2>Who is this template for</h2><p>Couples planning weddings, engagement parties, bridal showers, and rehearsal dinners.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects meal preferences, plus-one details, and song requests, with automatic tracking of who has responded.</p>', 
            'types' => ['wedding_forms', 'rsvp_forms'],
            'industries' => ['entertainment_forms'],
            'structure' => $this->structure('Wedding RSVP', [
                ['id' => 'guest_name', 'type' => 'text', 'title' => 'Guest Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'attending', 'type' => 'radio', 'title' => 'Will you attend?', 'required' => true, 'help' => '', 'options' => [['value' => 'yes', 'text' => 'Happily Accepts'], ['value' => 'no', 'text' => 'Regretfully Declines']]],
                ['id' => 'plus_one', 'type' => 'number', 'title' => 'Number of Guests', 'required' => false, 'help' => 'Including yourself'],
                ['id' => 'meal_choice', 'type' => 'select', 'title' => 'Meal Preference', 'required' => false, 'help' => '', 'options' => [['value' => 'chicken', 'text' => 'Chicken'], ['value' => 'fish', 'text' => 'Fish'], ['value' => 'vegetarian', 'text' => 'Vegetarian'], ['value' => 'vegan', 'text' => 'Vegan']],
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('attending', 'select', 'equals', 'Happily Accepts')])],
                ['id' => 'dietary', 'type' => 'text', 'title' => 'Dietary Restrictions / Allergies', 'required' => false, 'help' => '',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('attending', 'select', 'equals', 'Happily Accepts')])],
                ['id' => 'song_request', 'type' => 'text', 'title' => 'Song Request', 'required' => false, 'help' => '',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('attending', 'select', 'equals', 'Happily Accepts')])],
                ['id' => 'message', 'type' => 'text', 'title' => 'Message for the Couple', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#e11d48'),
        ];
    }

    private function volunteerForm(): array
    {
        return [
            'name' => 'Volunteer Signup Form Template', 
            'slug' => 'volunteer-signup-form-template', 
            'short_description' => 'A volunteer signup form template for nonprofits and events to recruit and organize volunteers.', 
            'description' => '<p>Our Volunteer Signup Form Template helps nonprofits, events, and community groups recruit volunteers and match them with the right roles.</p><h2>Why and when to use a volunteer signup form</h2><p>Organizations need to know who is available, their skills, and their availability before events. A signup form collects all of it in one place.</p><h2>Who is this template for</h2><p>Nonprofits, charities, event organizers, and community groups managing volunteers.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects availability, skills, and emergency contacts, and can send shift reminders to volunteers automatically.</p>', 
            'types' => ['volunteer_forms', 'registration_forms'],
            'industries' => ['charity_forms', 'animal_shelter_forms'],
            'structure' => $this->structure('Volunteer Signup', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'interests', 'type' => 'multi_select', 'title' => 'Interested Roles', 'required' => true, 'help' => '', 'options' => [['value' => 'events', 'text' => 'Event Support'], ['value' => 'admin', 'text' => 'Administrative'], ['value' => 'outreach', 'text' => 'Outreach'], ['value' => 'fundraising', 'text' => 'Fundraising'], ['value' => 'logistics', 'text' => 'Logistics']]],
                ['id' => 'availability', 'type' => 'select', 'title' => 'Availability', 'required' => true, 'help' => '', 'options' => [['value' => 'weekdays', 'text' => 'Weekdays'], ['value' => 'weekends', 'text' => 'Weekends'], ['value' => 'evenings', 'text' => 'Evenings'], ['value' => 'flexible', 'text' => 'Flexible']]],
                ['id' => 'experience', 'type' => 'text', 'title' => 'Relevant Experience', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'emergency_contact', 'type' => 'text', 'title' => 'Emergency Contact', 'required' => false, 'help' => '']
            ], '#10b981'),
        ];
    }

    private function alumniForm(): array
    {
        return [
            'name' => 'Alumni Registration Form Template', 
            'slug' => 'alumni-registration-form-template', 
            'short_description' => 'An alumni registration form template for schools and universities to keep graduates connected.', 
            'description' => '<p>Our Alumni Registration Form Template helps schools, colleges, and universities keep their alumni network updated and engaged.</p><h2>Why and when to use an alumni registration form</h2><p>Alumni networks grow stronger with updated contact details. A registration form captures graduates’ current info, career details, and engagement preferences.</p><h2>Who is this template for</h2><p>Alumni associations, universities, schools, and community organizations.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms keeps alumni data organized, supports consent for communications, and can trigger welcome emails to new registrations.</p>', 
            'types' => ['registration_forms', 'signup_forms'],
            'industries' => ['alumni_forms', 'education_forms'],
            'structure' => $this->structure('Alumni Registration', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'graduation_year', 'type' => 'number', 'title' => 'Graduation Year', 'required' => true, 'help' => ''],
                ['id' => 'major', 'type' => 'text', 'title' => 'Major / Program', 'required' => false, 'help' => ''],
                ['id' => 'current_location', 'type' => 'text', 'title' => 'Current Location', 'required' => false, 'help' => ''],
                ['id' => 'job_title', 'type' => 'text', 'title' => 'Job Title', 'required' => false, 'help' => ''],
                ['id' => 'engagement', 'type' => 'multi_select', 'title' => 'How would you like to get involved?', 'required' => false, 'help' => '', 'options' => [['value' => 'mentoring', 'text' => 'Mentoring'], ['value' => 'events', 'text' => 'Events'], ['value' => 'donating', 'text' => 'Donating'], ['value' => 'networking', 'text' => 'Networking']]],
                ['id' => 'consent', 'type' => 'checkbox', 'title' => 'I consent to receive alumni communications', 'required' => true, 'help' => '']
            ], '#7c3aed'),
        ];
    }

    private function petAdoption(): array
    {
        return [
            'name' => 'Pet Adoption Application Form Template', 
            'slug' => 'pet-adoption-application-form-template', 
            'short_description' => 'A pet adoption application form template for shelters and rescues to screen potential adopters.', 
            'description' => '<p>Our Pet Adoption Application Form Template helps animal shelters and rescues screen applicants and match pets with loving homes.</p><h2>Why and when to use a pet adoption application</h2><p>Responsible shelters verify that applicants can provide a safe, stable home. A structured application captures housing, household, and lifestyle details.</p><h2>Who is this template for</h2><p>Animal shelters, rescue organizations, and pet foster programs.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects all adoption criteria, supports reference contacts, and organizes applications for your review team.</p>', 
            'types' => ['application_forms', 'consent_forms'],
            'industries' => ['animal_shelter_forms', 'veterinary_service_forms'],
            'structure' => $this->structure('Pet Adoption Application', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'address', 'type' => 'text', 'title' => 'Home Address', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'housing_type', 'type' => 'select', 'title' => 'Housing Type', 'required' => true, 'help' => '', 'options' => [['value' => 'house', 'text' => 'House'], ['value' => 'apartment', 'text' => 'Apartment'], ['value' => 'condo', 'text' => 'Condo'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'own_home', 'type' => 'select', 'title' => 'Do you own or rent?', 'required' => true, 'help' => '', 'options' => [['value' => 'own', 'text' => 'Own'], ['value' => 'rent', 'text' => 'Rent']]],
                ['id' => 'household', 'type' => 'number', 'title' => 'Number of Adults in Home', 'required' => true, 'help' => ''],
                ['id' => 'children', 'type' => 'number', 'title' => 'Number of Children', 'required' => true, 'help' => ''],
                ['id' => 'existing_pets', 'type' => 'text', 'title' => 'Existing Pets', 'required' => false, 'help' => ''],
                ['id' => 'experience', 'type' => 'text', 'title' => 'Pet Ownership Experience', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'agree', 'type' => 'checkbox', 'title' => 'I agree to the adoption terms and a home visit', 'required' => true, 'help' => '']
            ], '#f59e0b'),
        ];
    }

    private function bankAccountOpening(): array
    {
        return [
            'name' => 'Bank Account Opening Form Template', 
            'slug' => 'bank-account-opening-form-template', 
            'short_description' => 'A bank account opening form template for financial institutions to onboard new customers.', 
            'description' => '<p>Our Bank Account Opening Form Template helps financial institutions collect the information needed to onboard new customers and open accounts.</p><h2>Why and when to use an account opening form</h2><p>Banks and credit unions need verified customer data, KYC details, and account preferences. A structured form speeds up onboarding and reduces errors.</p><h2>Who is this template for</h2><p>Banks, credit unions, and fintech companies onboarding customers.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects customer details and identity documents securely, with options to route new accounts to your back-office team.</p>', 
            'types' => ['application_forms', 'registration_forms'],
            'industries' => ['banking_forms'],
            'structure' => $this->structure('Open an Account', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'date_of_birth', 'type' => 'date', 'title' => 'Date of Birth', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'address', 'type' => 'text', 'title' => 'Residential Address', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'employment_status', 'type' => 'select', 'title' => 'Employment Status', 'required' => true, 'help' => '', 'options' => [['value' => 'employed', 'text' => 'Employed'], ['value' => 'self_employed', 'text' => 'Self-Employed'], ['value' => 'student', 'text' => 'Student'], ['value' => 'retired', 'text' => 'Retired']]],
                ['id' => 'account_type', 'type' => 'select', 'title' => 'Account Type', 'required' => true, 'help' => '', 'options' => [['value' => 'checking', 'text' => 'Checking Account'], ['value' => 'savings', 'text' => 'Savings Account'], ['value' => 'joint', 'text' => 'Joint Account'], ['value' => 'business', 'text' => 'Business Account']]],
                ['id' => 'id_documents', 'type' => 'files', 'title' => 'Identity Documents', 'required' => true, 'help' => 'Government-issued ID', 'max_number_of_files' => 2, 'max_file_size' => 15]
            ], '#0ea5e9'),
        ];
    }

    private function insuranceClaim(): array
    {
        return [
            'name' => 'Insurance Claim Form Template', 
            'slug' => 'insurance-claim-form-template', 
            'short_description' => 'An insurance claim form template for insurers and brokers to collect claim details and documents.', 
            'description' => '<p>Our Insurance Claim Form Template helps insurers and brokers collect claim information, incident details, and supporting documents in a structured format.</p><h2>Why and when to use a claim form</h2><p>Claims need complete, consistent information to process quickly. A structured form ensures policyholders provide all required details the first time.</p><h2>Who is this template for</h2><p>Insurance companies, brokers, and claims departments handling policyholder claims.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects claim details, supporting photos, and banking info, and can alert your claims team immediately.</p>', 
            'types' => ['report_forms', 'request_forms'],
            'industries' => ['insurance_forms', 'banking_forms'],
            'structure' => $this->structure('File an Insurance Claim', [
                ['id' => 'policyholder_name', 'type' => 'text', 'title' => 'Policyholder Name', 'required' => true, 'help' => ''],
                ['id' => 'policy_number', 'type' => 'text', 'title' => 'Policy Number', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'claim_type', 'type' => 'select', 'title' => 'Claim Type', 'required' => true, 'help' => '', 'options' => [['value' => 'auto', 'text' => 'Auto'], ['value' => 'home', 'text' => 'Home'], ['value' => 'health', 'text' => 'Health'], ['value' => 'life', 'text' => 'Life'], ['value' => 'travel', 'text' => 'Travel']]],
                ['id' => 'incident_date', 'type' => 'date', 'title' => 'Incident Date', 'required' => true, 'help' => ''],
                ['id' => 'incident_description', 'type' => 'text', 'title' => 'Incident Description', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'vehicle_details', 'type' => 'text', 'title' => 'Vehicle Details', 'required' => false, 'help' => 'Make, model, year, and plate of the insured vehicle',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('claim_type', 'select', 'equals', 'Auto')])],
                ['id' => 'claim_amount', 'type' => 'number', 'title' => 'Estimated Claim Amount', 'required' => true, 'help' => ''],
                ['id' => 'supporting_documents', 'type' => 'files', 'title' => 'Supporting Documents', 'required' => false, 'help' => 'Photos, receipts, police report', 'max_number_of_files' => 6, 'max_file_size' => 25]
            ], '#f59e0b'),
        ];
    }

    private function photographyBooking(): array
    {
        return [
            'name' => 'Photography Booking Form Template', 
            'slug' => 'photography-booking-form-template', 
            'short_description' => 'A photography booking form template for photographers to manage session bookings and inquiries.', 
            'description' => '<p>Our Photography Booking Form Template helps photographers collect session details, preferences, and client information for bookings.</p><h2>Why and when to use a photography booking form</h2><p>Photographers juggle many inquiries. A booking form captures session type, dates, and expectations so you can quote accurately and plan shoots.</p><h2>Who is this template for</h2><p>Wedding, portrait, event, and commercial photographers managing client bookings.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects session preferences, location details, and inspiration images, with automatic confirmation emails.</p>', 
            'types' => ['booking_forms', 'appointment_forms'],
            'industries' => ['photography_forms'],
            'structure' => $this->structure('Book a Photoshoot', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'session_type', 'type' => 'select', 'title' => 'Session Type', 'required' => true, 'help' => '', 'options' => [['value' => 'wedding', 'text' => 'Wedding'], ['value' => 'portrait', 'text' => 'Portrait'], ['value' => 'family', 'text' => 'Family'], ['value' => 'event', 'text' => 'Event'], ['value' => 'commercial', 'text' => 'Commercial']]],
                ['id' => 'preferred_date', 'type' => 'date', 'title' => 'Preferred Date', 'required' => true, 'help' => ''],
                ['id' => 'location', 'type' => 'text', 'title' => 'Location', 'required' => true, 'help' => ''],
                ['id' => 'vision', 'type' => 'text', 'title' => 'Describe Your Vision', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'inspiration', 'type' => 'files', 'title' => 'Inspiration Images (Optional)', 'required' => false, 'help' => 'Optional', 'max_number_of_files' => 5, 'max_file_size' => 25],
                $this->totalBlock('starting_price_display', 'cv_starting_price', 'Starting Price for Your Session', '$0'),
            ], '#ec4899', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_starting_price',
                        'Starting Price',
                        'IF({session_type}="Wedding",1200,IF({session_type}="Portrait",250,IF({session_type}="Family",350,IF({session_type}="Event",800,1500))))'
                    ),
                ],
            ]),
        ];
    }

    private function seoAudit(): array
    {
        return [
            'name' => 'SEO Audit Request Form Template', 
            'slug' => 'seo-audit-request-form-template', 
            'short_description' => 'An SEO audit request form template for agencies to collect website details from prospective clients.', 
            'description' => '<p>Our SEO Audit Request Form Template helps SEO agencies and consultants gather the website and business details they need to deliver a thorough audit.</p><h2>Why and when to use an SEO audit request</h2><p>An audit request form captures the URL, goals, and current marketing situation so your team can scope work and deliver a valuable audit.</p><h2>Who is this template for</h2><p>SEO agencies, consultants, and marketing teams offering website audits.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects URLs, goals, and access details, and can trigger the audit workflow automatically when a request arrives.</p>', 
            'types' => ['request_forms', 'audit_forms'],
            'industries' => ['seo_forms', 'web_design_forms'],
            'structure' => $this->structure('Request an SEO Audit', [
                ['id' => 'company', 'type' => 'text', 'title' => 'Company Name', 'required' => true, 'help' => ''],
                ['id' => 'contact_name', 'type' => 'text', 'title' => 'Contact Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'website_url', 'type' => 'url', 'title' => 'Website URL', 'required' => true, 'help' => ''],
                ['id' => 'current_traffic', 'type' => 'select', 'title' => 'Monthly Traffic', 'required' => false, 'help' => '', 'options' => [['value' => 'under_1k', 'text' => 'Under 1K visits'], ['value' => '1k_10k', 'text' => '1K - 10K visits'], ['value' => '10k_100k', 'text' => '10K - 100K visits'], ['value' => 'over_100k', 'text' => 'Over 100K visits']]],
                ['id' => 'goals', 'type' => 'multi_select', 'title' => 'Primary Goals', 'required' => true, 'help' => '', 'options' => [['value' => 'rankings', 'text' => 'Improve Rankings'], ['value' => 'traffic', 'text' => 'Increase Traffic'], ['value' => 'leads', 'text' => 'Generate Leads'], ['value' => 'ecommerce', 'text' => 'Increase Sales']]],
                ['id' => 'issues', 'type' => 'text', 'title' => 'Known Issues (Optional)', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#14b8a6'),
        ];
    }

    private function sportsRegistration(): array
    {
        return [
            'name' => 'Sports Team Registration Form Template', 
            'slug' => 'sports-team-registration-form-template', 
            'short_description' => 'A sports team registration form template for leagues and clubs to register players and teams.', 
            'description' => '<p>Our Sports Team Registration Form Template helps leagues, clubs, and schools register players and teams for the season.</p><h2>Why and when to use a sports registration form</h2><p>Registrations need player details, jersey sizes, medical info, and waivers. A digital form makes the whole process fast and error-free.</p><h2>Who is this template for</h2><p>Sports leagues, clubs, schools, and recreational programs managing player registration.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects player details, waivers, and payments, and can enforce age groups and registration deadlines.</p>', 
            'types' => ['registration_forms', 'signup_forms'],
            'industries' => ['sports_forms', 'entertainment_forms'],
            'structure' => $this->structure('Team Registration', [
                ['id' => 'player_name', 'type' => 'text', 'title' => 'Player Name', 'required' => true, 'help' => ''],
                ['id' => 'date_of_birth', 'type' => 'date', 'title' => 'Date of Birth', 'required' => true, 'help' => ''],
                ['id' => 'parent_name', 'type' => 'text', 'title' => 'Parent / Guardian Name', 'required' => false, 'help' => 'For players under 18',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('team', 'select', 'does_not_equal', 'Adult')], 'and', true)],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'team', 'type' => 'select', 'title' => 'Team / Division', 'required' => true, 'help' => '', 'options' => [['value' => 'u10', 'text' => 'Under 10'], ['value' => 'u12', 'text' => 'Under 12'], ['value' => 'u14', 'text' => 'Under 14'], ['value' => 'u16', 'text' => 'Under 16'], ['value' => 'adult', 'text' => 'Adult']]],
                ['id' => 'jersey_size', 'type' => 'select', 'title' => 'Jersey Size', 'required' => true, 'help' => '', 'options' => [['value' => 'ys', 'text' => 'Youth S'], ['value' => 'ym', 'text' => 'Youth M'], ['value' => 'yl', 'text' => 'Youth L'], ['value' => 'as', 'text' => 'Adult S'], ['value' => 'am', 'text' => 'Adult M'], ['value' => 'al', 'text' => 'Adult L'], ['value' => 'axl', 'text' => 'Adult XL']]],
                ['id' => 'medical_conditions', 'type' => 'text', 'title' => 'Medical Conditions', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'waiver', 'type' => 'checkbox', 'title' => 'I accept the league waiver and code of conduct', 'required' => true, 'help' => '']
            ], '#16a34a'),
        ];
    }

    private function gamingTournament(): array
    {
        return [
            'name' => 'Gaming Tournament Registration Form Template', 
            'slug' => 'gaming-tournament-registration-form-template', 
            'short_description' => 'A gaming tournament registration form template for esports events to sign up players and teams.', 
            'description' => '<p>Our Gaming Tournament Registration Form Template helps esports organizers register players, teams, and tournament entries.</p><h2>Why and when to use a gaming tournament registration</h2><p>Tournaments need player handles, team rosters, and game preferences to seed brackets correctly. A registration form collects it all cleanly.</p><h2>Who is this template for</h2><p>Esports organizers, gaming venues, streamers, and community tournament hosts.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms collects player details, platform IDs, and consent, with automatic confirmation and match notifications.</p>', 
            'types' => ['event_registration_forms', 'registration_forms'],
            'industries' => ['gaming_forms', 'entertainment_forms'],
            'structure' => $this->structure('Tournament Registration', [
                ['id' => 'gamer_tag', 'type' => 'text', 'title' => 'Gamer Tag', 'required' => true, 'help' => ''],
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Real Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'platform', 'type' => 'select', 'title' => 'Platform', 'required' => true, 'help' => '', 'options' => [['value' => 'pc', 'text' => 'PC'], ['value' => 'playstation', 'text' => 'PlayStation'], ['value' => 'xbox', 'text' => 'Xbox'], ['value' => 'switch', 'text' => 'Nintendo Switch']]],
                ['id' => 'game', 'type' => 'select', 'title' => 'Game', 'required' => true, 'help' => '', 'options' => [['value' => 'valorant', 'text' => 'Valorant'], ['value' => 'lol', 'text' => 'League of Legends'], ['value' => 'cs2', 'text' => 'Counter-Strike 2'], ['value' => 'rocket_league', 'text' => 'Rocket League']]],
                ['id' => 'team_size', 'type' => 'select', 'title' => 'Entry Type', 'required' => true, 'help' => '', 'options' => [['value' => 'solo', 'text' => 'Solo'], ['value' => 'duo', 'text' => 'Duo'], ['value' => 'squad', 'text' => 'Squad (4 players)'], ['value' => 'clan', 'text' => 'Clan / Full Team']]],
                ['id' => 'rank', 'type' => 'select', 'title' => 'Current Rank (Optional)', 'required' => false, 'help' => '', 'options' => [['value' => 'bronze', 'text' => 'Bronze'], ['value' => 'silver', 'text' => 'Silver'], ['value' => 'gold', 'text' => 'Gold'], ['value' => 'platinum', 'text' => 'Platinum'], ['value' => 'diamond_plus', 'text' => 'Diamond+']]],
                ['id' => 'rules', 'type' => 'checkbox', 'title' => 'I agree to the tournament rules', 'required' => true, 'help' => ''],
                $this->totalBlock('entry_fee_display', 'cv_entry_fee', 'Entry Fee', '$0'),
            ], '#8b5cf6', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_entry_fee',
                        'Entry Fee',
                        'IF(ISBLANK({team_size}),0,IF({team_size}="Duo",18,IF({team_size}="Squad (4 players)",30,IF({team_size}="Clan / Full Team",50,10))))'
                    ),
                ],
            ]),
        ];
    }

    private function requestForm(): array
    {
        return [
            'name' => 'Service Request Form Template', 
            'slug' => 'service-request-form-template', 
            'short_description' => 'A service request form template for businesses to log maintenance, support, and internal requests.', 
            'description' => '<p>Our Service Request Form Template helps teams log and track maintenance, support, and internal requests in a consistent way.</p><h2>Why and when to use a service request form</h2><p>Facilities, IT, and operations teams receive many requests. A structured form captures priority, category, and details so nothing slips through.</p><h2>Who is this template for</h2><p>Facilities managers, IT helpdesks, operations teams, and internal support functions.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms captures request priority and category, supports photo attachments, and can assign requests to team members automatically.</p>', 
            'types' => ['request_forms', 'tracking_forms'],
            'industries' => ['it_forms', 'services_forms'],
            'structure' => $this->structure('Service Request', [
                ['id' => 'requester_name', 'type' => 'text', 'title' => 'Requester Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'department', 'type' => 'text', 'title' => 'Department', 'required' => true, 'help' => ''],
                ['id' => 'category', 'type' => 'select', 'title' => 'Request Category', 'required' => true, 'help' => '', 'options' => [['value' => 'it_support', 'text' => 'IT Support'], ['value' => 'maintenance', 'text' => 'Maintenance'], ['value' => 'supplies', 'text' => 'Supplies'], ['value' => 'access', 'text' => 'Access Request'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'priority', 'type' => 'select', 'title' => 'Priority', 'required' => true, 'help' => '', 'options' => [['value' => 'low', 'text' => 'Low'], ['value' => 'medium', 'text' => 'Medium'], ['value' => 'high', 'text' => 'High'], ['value' => 'urgent', 'text' => 'Urgent']]],
                ['id' => 'description', 'type' => 'text', 'title' => 'Describe the Request', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'location', 'type' => 'text', 'title' => 'Location (if applicable)', 'required' => false, 'help' => ''],
                ['id' => 'photos', 'type' => 'files', 'title' => 'Attach Photos (Optional)', 'required' => false, 'help' => 'Optional', 'max_number_of_files' => 4, 'max_file_size' => 20]
            ], '#6366f1'),
        ];
    }

    private function rentalApplication(): array
    {
        return [
            'name' => 'Rental Application Form Template',
            'slug' => 'rental-application-form-template',
            'short_description' => 'A rental application form template for landlords and property managers to screen prospective tenants quickly and fairly.',
            'description' => '<p>Our Rental Application Form Template helps landlords and property managers collect everything needed to screen prospective tenants in one place.</p><h2>Why and when to use a rental application</h2><p>Every tenancy starts with reliable information. A structured rental application captures identity, employment and income details, rental history, and screening consent so you can compare applicants on the same terms and document your decision process.</p><h2>Who is this template for</h2><p>Independent landlords, property management companies, and real estate agencies that lease apartments, houses, or commercial units.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms gives every applicant a simple link or embedded form, notifies you the moment an application arrives, accepts document uploads such as proof of income and ID, and keeps all applications organized in one dashboard.</p>',
            'types' => ['application_forms'],
            'industries' => ['real_estate_forms'],
            'questions' => [
                [
                    'question' => 'What should a rental application include?',
                    'answer' => '<p>A complete rental application gathers the applicant\'s contact details, current and previous addresses, employment and income information, number of occupants and pets, references from prior landlords, and written consent for credit or background checks.</p>',
                ],
                [
                    'question' => 'Can applicants upload supporting documents?',
                    'answer' => '<p>Yes. This template includes a file upload field so applicants can attach proof of income, identification, or reference letters. You can limit the number and size of files to keep submissions manageable.</p>',
                ],
                [
                    'question' => 'How do I share the application with prospective tenants?',
                    'answer' => '<p>Publish your form and share the unique link by email, SMS, or listing sites, or embed it directly on your property website. Every submission triggers an instant notification so you can follow up while an applicant is still interested.</p>',
                ],
            ],
            'structure' => $this->structure('Rental Application', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Legal Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'property_interest', 'type' => 'text', 'title' => 'Property / Unit of Interest', 'required' => true, 'help' => ''],
                ['id' => 'move_in_date', 'type' => 'date', 'title' => 'Desired Move-in Date', 'required' => true, 'help' => ''],
                ['id' => 'occupants', 'type' => 'number', 'title' => 'Number of Occupants', 'required' => true, 'help' => ''],
                ['id' => 'pets', 'type' => 'select', 'title' => 'Pets', 'required' => true, 'help' => '', 'options' => [['value' => 'none', 'text' => 'No pets'], ['value' => 'dog', 'text' => 'Dog'], ['value' => 'cat', 'text' => 'Cat'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'pet_details', 'type' => 'text', 'title' => 'Pet Details', 'required' => false, 'help' => 'Breed, size, and weight for each pet',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('pets', 'select', 'does_not_equal', 'No pets')])],
                ['id' => 'employment_status', 'type' => 'select', 'title' => 'Employment Status', 'required' => true, 'help' => '', 'options' => [['value' => 'employed', 'text' => 'Employed'], ['value' => 'self_employed', 'text' => 'Self-employed'], ['value' => 'student', 'text' => 'Student'], ['value' => 'retired', 'text' => 'Retired'], ['value' => 'unemployed', 'text' => 'Other']]],
                ['id' => 'monthly_income', 'type' => 'number', 'title' => 'Gross Monthly Income', 'required' => true, 'help' => ''],
                ['id' => 'current_landlord', 'type' => 'text', 'title' => 'Current Landlord Contact', 'required' => false, 'help' => 'Name and phone or email'],
                ['id' => 'documents', 'type' => 'files', 'title' => 'Proof of Income / ID Uploads', 'required' => false, 'help' => 'Pay stubs, employment letter, or ID', 'max_number_of_files' => 5, 'max_file_size' => 10],
                ['id' => 'screening_consent', 'type' => 'checkbox', 'title' => 'I consent to a credit and background screening check', 'required' => true, 'help' => '']
            ], '#2563eb'),
        ];
    }

    private function webinarRegistration(): array
    {
        return [
            'name' => 'Webinar Registration Form Template',
            'slug' => 'webinar-registration-form-template',
            'short_description' => 'A webinar registration form template that captures sign-ups, session preferences, and audience questions before the event.',
            'description' => '<p>Our Webinar Registration Form Template helps marketing teams and speakers register attendees, capture qualifying details, and collect questions ahead of the live session.</p><h2>Why and when to use a webinar registration form</h2><p>Webinars succeed on preparation. Knowing attendee count, job roles, and submitted questions lets you tailor the presentation and plan follow-up campaigns. A registration form replaces scattered email sign-ups with clean, structured data.</p><h2>Who is this template for</h2><p>Marketing teams, SaaS companies, educators, consultants, and community managers hosting online events.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms sends automatic confirmation emails with your join link, works with conditional logic to show different questions per session, and pushes registrations to Slack or your CRM through Zapier and webhooks.</p>',
            'types' => ['event_registration_forms', 'registration_forms'],
            'industries' => ['marketing_forms', 'business_forms'],
            'questions' => [
                [
                    'question' => 'What details should a webinar registration form collect?',
                    'answer' => '<p>At minimum: full name, email address, and company or role if relevant for segmentation. Optional fields such as session preference or a question box help you personalize the event content.</p>',
                ],
                [
                    'question' => 'Do attendees receive a confirmation automatically?',
                    'answer' => '<p>Yes. SharaForms can send an instant confirmation email to every registrant, which you can use to deliver the calendar invite or join link right after sign-up.</p>',
                ],
                [
                    'question' => 'Can I cap the number of attendees?',
                    'answer' => '<p>You can set a submission limit on the form so registration closes automatically once your seat cap is reached, keeping attendance within platform limits.</p>',
                ],
            ],
            'structure' => $this->structure('Webinar Registration', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Work Email', 'required' => true, 'help' => ''],
                ['id' => 'company', 'type' => 'text', 'title' => 'Company', 'required' => false, 'help' => ''],
                ['id' => 'role', 'type' => 'select', 'title' => 'Job Role', 'required' => false, 'help' => '', 'options' => [['value' => 'founder', 'text' => 'Founder / C-level'], ['value' => 'marketing', 'text' => 'Marketing'], ['value' => 'sales', 'text' => 'Sales'], ['value' => 'product', 'text' => 'Product'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'session', 'type' => 'select', 'title' => 'Preferred Session', 'required' => true, 'help' => '', 'options' => [['value' => 'session_1', 'text' => 'Session 1 - Morning (10:00 AM CET)'], ['value' => 'session_2', 'text' => 'Session 2 - Evening (6:00 PM CET)']]],
                ['id' => 'questions', 'type' => 'text', 'title' => 'What would you like to learn?', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'marketing_optin', 'type' => 'checkbox', 'title' => 'Send me future event invitations', 'required' => false, 'help' => '']
            ], '#7c3aed'),
        ];
    }

    private function leaveRequest(): array
    {
        return [
            'name' => 'Leave Request Form Template',
            'slug' => 'leave-request-form-template',
            'short_description' => 'A leave request form template for employees to submit PTO, sick leave, and other absence requests for manager approval.',
            'description' => '<p>Our Leave Request Form Template standardizes how employees request vacation, sick days, parental leave, and other absences.</p><h2>Why and when to use a leave request form</h2><p>Ad-hoc messages get lost and create scheduling conflicts. A structured request captures leave type, dates, and coverage plans in one record, giving HR a clear audit trail and managers the context they need to approve quickly.</p><h2>Who is this template for</h2><p>HR teams, people operations, and team leads at companies of any size that track employee absences.</p><h2>Why SharaForms is the best tool for this form</h2><p>Submissions trigger instant notifications to the approving manager, responses are timestamped for compliance, and you can embed the form in your intranet so it is always one click away.</p>',
            'types' => ['request_forms', 'employment_forms'],
            'industries' => ['human_resources_forms'],
            'questions' => [
                [
                    'question' => 'What is the difference between PTO and sick leave on this form?',
                    'answer' => '<p>The leave type dropdown separates vacation or PTO, sick leave, personal days, parental leave, and unpaid leave so HR can categorize absences accurately without follow-up questions.</p>',
                ],
                [
                    'question' => 'Can employees request leave for a date range?',
                    'answer' => '<p>Yes. The template includes start and end date fields, and employees state the total number of working days requested so payroll stays accurate.</p>',
                ],
                [
                    'question' => 'How do managers approve requests?',
                    'answer' => '<p>Each submission sends an immediate notification to the manager or HR inbox. Approvals happen outside the form, but every request stays logged with its submission time for records.</p>',
                ],
            ],
            'structure' => $this->structure('Leave Request', [
                ['id' => 'employee_name', 'type' => 'text', 'title' => 'Employee Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Work Email', 'required' => true, 'help' => ''],
                ['id' => 'department', 'type' => 'select', 'title' => 'Department', 'required' => true, 'help' => '', 'options' => [['value' => 'engineering', 'text' => 'Engineering'], ['value' => 'sales', 'text' => 'Sales'], ['value' => 'marketing', 'text' => 'Marketing'], ['value' => 'support', 'text' => 'Support'], ['value' => 'operations', 'text' => 'Operations'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'leave_type', 'type' => 'select', 'title' => 'Leave Type', 'required' => true, 'help' => '', 'options' => [['value' => 'pto', 'text' => 'Vacation / PTO'], ['value' => 'sick', 'text' => 'Sick Leave'], ['value' => 'personal', 'text' => 'Personal Day'], ['value' => 'parental', 'text' => 'Parental Leave'], ['value' => 'unpaid', 'text' => 'Unpaid Leave']]],
                ['id' => 'start_date', 'type' => 'date', 'title' => 'First Day of Leave', 'required' => true, 'help' => ''],
                ['id' => 'end_date', 'type' => 'date', 'title' => 'Last Day of Leave', 'required' => true, 'help' => ''],
                ['id' => 'days_requested', 'type' => 'number', 'title' => 'Working Days Requested', 'required' => true, 'help' => ''],
                ['id' => 'coverage', 'type' => 'text', 'title' => 'Coverage / Handover Plan', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'doctor_note', 'type' => 'files', 'title' => "Doctor's Note", 'required' => false, 'help' => 'Required by policy for absences of 3+ sick days', 'max_number_of_files' => 1, 'max_file_size' => 10,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('leave_type', 'select', 'equals', 'Sick Leave')])],
                ['id' => 'policy_ack', 'type' => 'checkbox', 'title' => 'I have read the time-off policy', 'required' => true, 'help' => '']
            ], '#059669'),
        ];
    }

    private function expenseReport(): array
    {
        return [
            'name' => 'Expense Report Form Template',
            'slug' => 'expense-report-form-template',
            'short_description' => 'An expense report form template for employees to submit business expenses with receipts for fast reimbursement.',
            'description' => '<p>Our Expense Report Form Template lets employees log business expenses, attach receipts, and route everything to finance in a consistent format.</p><h2>Why and when to use an expense report</h2><p>Manual spreadsheets slow down reimbursements and hide policy breaches. A structured report captures amount, category, purpose, and receipts per claim, letting finance approve faster and spot out-of-policy spending early.</p><h2>Who is this template for</h2><p>Finance teams, controllers, and any business that reimburses employee spending on travel, meals, software, or supplies.</p><h2>Why SharaForms is the best tool for this form</h2><p>Receipt photos can be uploaded from mobile, numeric fields feed totals into built-in calculations, and completed reports land in your finance inbox instantly with all documentation attached.</p>',
            'types' => ['report_forms', 'payment_forms'],
            'industries' => ['business_forms', 'human_resources_forms'],
            'questions' => [
                [
                    'question' => 'What counts as a valid receipt?',
                    'answer' => '<p>Most policies require an itemized receipt showing vendor, date, and amount. The file upload field accepts photos or PDFs so employees can submit receipts straight from their phone.</p>',
                ],
                [
                    'question' => 'Can this form replace our expense spreadsheet?',
                    'answer' => '<p>Yes for most small teams. Every submission is stored with timestamps and attachments, and you can export submissions to CSV for bookkeeping instead of maintaining a manual sheet.</p>',
                ],
                [
                    'question' => 'How detailed should the business purpose be?',
                    'answer' => '<p>A short sentence is usually enough, for example "Client dinner - Q3 renewal". Clear purposes speed up approval and simplify tax reporting for deductible expenses.</p>',
                ],
            ],
            'structure' => $this->structure('Expense Report', [
                ['id' => 'employee_name', 'type' => 'text', 'title' => 'Employee Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Work Email', 'required' => true, 'help' => ''],
                ['id' => 'department', 'type' => 'text', 'title' => 'Department / Cost Center', 'required' => true, 'help' => ''],
                ['id' => 'expense_category', 'type' => 'select', 'title' => 'Expense Category', 'required' => true, 'help' => '', 'options' => [['value' => 'travel', 'text' => 'Travel'], ['value' => 'meals', 'text' => 'Meals & Entertainment'], ['value' => 'software', 'text' => 'Software / Subscriptions'], ['value' => 'accommodation', 'text' => 'Accommodation'], ['value' => 'supplies', 'text' => 'Office Supplies'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'amount', 'type' => 'number', 'title' => 'Total Amount Claimed', 'required' => true, 'help' => ''],
                ['id' => 'currency', 'type' => 'select', 'title' => 'Currency', 'required' => true, 'help' => '', 'options' => [['value' => 'usd', 'text' => 'USD'], ['value' => 'eur', 'text' => 'EUR'], ['value' => 'gbp', 'text' => 'GBP'], ['value' => 'inr', 'text' => 'INR']]],
                ['id' => 'purpose', 'type' => 'text', 'title' => 'Business Purpose', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'receipts', 'type' => 'files', 'title' => 'Receipt Attachments', 'required' => true, 'help' => 'Photos or PDFs', 'max_number_of_files' => 8, 'max_file_size' => 10],
                ['id' => 'declaration', 'type' => 'checkbox', 'title' => 'I confirm these expenses comply with company policy', 'required' => true, 'help' => '']
            ], '#d97706'),
        ];
    }

    private function timesheetForm(): array
    {
        return [
            'name' => 'Timesheet Form Template',
            'slug' => 'timesheet-form-template',
            'short_description' => 'A weekly timesheet form template for teams to log hours per project and keep payroll and billing accurate.',
            'description' => '<p>Our Timesheet Form Template gives teams a simple weekly form to log hours worked per project, client, or task.</p><h2>Why and when to use a timesheet form</h2><p>Accurate hours drive payroll, client billing, and project costing. A weekly submission rhythm keeps records current without forcing everyone into heavyweight time-tracking software.</p><h2>Who is this template for</h2><p>Agencies, consultancies, contractors, and internal teams that bill clients or run payroll from tracked hours.</p><h2>Why SharaForms is the best tool for this form</h2><p>Numeric hour fields validate input, the week-ending date keeps submissions aligned to pay periods, and exports give payroll a clean CSV at the end of every cycle.</p>',
            'types' => ['tracking_forms'],
            'industries' => ['human_resources_forms', 'business_forms'],
            'questions' => [
                [
                    'question' => 'How often should timesheets be submitted?',
                    'answer' => '<p>Weekly is the most common cadence because it balances accurate recall against admin overhead. The week-ending date field anchors every submission to the correct pay period.</p>',
                ],
                [
                    'question' => 'Can we split hours across multiple projects?',
                    'answer' => '<p>Yes. Duplicate the project-hours fields for each project or client, or ask employees to list them in the notes area. For heavy multi-project tracking, submit one form per project per week.</p>',
                ],
                [
                    'question' => 'Does this work for remote and field staff?',
                    'answer' => '<p>Yes. The form is mobile-friendly and can be filled from anywhere, and submissions arrive with timestamps so nothing depends on office presence.</p>',
                ],
            ],
            'structure' => $this->structure('Weekly Timesheet', [
                ['id' => 'employee_name', 'type' => 'text', 'title' => 'Employee Name', 'required' => true, 'help' => ''],
                ['id' => 'week_ending', 'type' => 'date', 'title' => 'Week Ending Date', 'required' => true, 'help' => ''],
                ['id' => 'project', 'type' => 'text', 'title' => 'Project / Client', 'required' => true, 'help' => ''],
                ['id' => 'hours_mon', 'type' => 'number', 'title' => 'Monday Hours', 'required' => true, 'help' => ''],
                ['id' => 'hours_tue', 'type' => 'number', 'title' => 'Tuesday Hours', 'required' => true, 'help' => ''],
                ['id' => 'hours_wed', 'type' => 'number', 'title' => 'Wednesday Hours', 'required' => true, 'help' => ''],
                ['id' => 'hours_thu', 'type' => 'number', 'title' => 'Thursday Hours', 'required' => true, 'help' => ''],
                ['id' => 'hours_fri', 'type' => 'number', 'title' => 'Friday Hours', 'required' => true, 'help' => ''],
                $this->totalBlock('weekly_hours_display', 'cv_total_hours', 'Total Hours This Week', '0'),
                ['id' => 'notes', 'type' => 'text', 'title' => 'Notes / Overtime Explanation', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'accuracy_ack', 'type' => 'checkbox', 'title' => 'I confirm these hours are accurate', 'required' => true, 'help' => '']
            ], '#0891b2', [
                'computed_variables' => [
                    $this->computedVariable('cv_total_hours', 'Total Hours', 'SUM({hours_mon},{hours_tue},{hours_wed},{hours_thu},{hours_fri})'),
                ],
            ]),
        ];
    }

    private function purchaseOrder(): array
    {
        return [
            'name' => 'Purchase Order Form Template',
            'slug' => 'purchase-order-form-template',
            'short_description' => 'A purchase order form template for teams to request purchases, capture approvals, and keep procurement auditable.',
            'description' => '<p>Our Purchase Order Form Template standardizes purchase requests with supplier details, line items, budget codes, and required approvals.</p><h2>Why and when to use a purchase order form</h2><p>Untracked spending causes budget overruns and duplicate orders. A PO form creates one auditable record per purchase: what is being bought, from whom, at what cost, and who approved it.</p><h2>Who is this template for</h2><p>Procurement teams, office managers, and finance departments in companies that want spend discipline without enterprise ERP overhead.</p><h2>Why SharaForms is the best tool for this form</h2><p>Requests notify procurement instantly, quantity and unit-price fields support basic calculations, and every approved order remains searchable with its full history.</p>',
            'types' => ['order_forms', 'request_forms'],
            'industries' => ['ecommerce_forms', 'business_forms'],
            'questions' => [
                [
                    'question' => 'What information does a purchase order need?',
                    'answer' => '<p>A supplier name, item description, quantity, unit price, required-by date, budget code, and the requester\'s department. Approval checkboxes make sign-off explicit.</p>',
                ],
                [
                    'question' => 'Can finance calculate order totals automatically?',
                    'answer' => '<p>Yes. SharaForms supports number fields and calculations, so total cost can be computed from quantity multiplied by unit price as the requester types.</p>',
                ],
                [
                    'question' => 'How do we keep an audit trail?',
                    'answer' => '<p>Every submission is stored with a timestamp and the requester\'s details, and can be exported to CSV for reconciliation during month-end close or audits.</p>',
                ],
            ],
            'structure' => $this->structure('Purchase Order Request', [
                ['id' => 'requester_name', 'type' => 'text', 'title' => 'Requested By', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Work Email', 'required' => true, 'help' => ''],
                ['id' => 'department', 'type' => 'text', 'title' => 'Department', 'required' => true, 'help' => ''],
                ['id' => 'supplier', 'type' => 'text', 'title' => 'Supplier / Vendor', 'required' => true, 'help' => ''],
                ['id' => 'items', 'type' => 'text', 'title' => 'Item Description & Line Items', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'quantity', 'type' => 'number', 'title' => 'Total Quantity', 'required' => true, 'help' => ''],
                ['id' => 'unit_price', 'type' => 'number', 'title' => 'Unit Price', 'required' => true, 'help' => ''],
                ['id' => 'needed_by', 'type' => 'date', 'title' => 'Required By', 'required' => true, 'help' => ''],
                ['id' => 'budget_code', 'type' => 'text', 'title' => 'Budget Code (Optional)', 'required' => false, 'help' => ''],
                $this->totalBlock('line_total_display', 'cv_line_total', 'Line Total', '$0'),
                ['id' => 'manager_approval', 'type' => 'checkbox', 'title' => 'Manager approval obtained', 'required' => true, 'help' => '']
            ], '#4f46e5', [
                'computed_variables' => [
                    $this->computedVariable('cv_line_total', 'Line Total', '{quantity}*{unit_price}'),
                ],
            ]),
        ];
    }

    private function reimbursementClaim(): array
    {
        return [
            'name' => 'Reimbursement Claim Form Template',
            'slug' => 'reimbursement-claim-form-template',
            'short_description' => 'A reimbursement claim form template for mileage, medical, training, and out-of-pocket claims with receipt uploads.',
            'description' => '<p>Our Reimbursement Claim Form Template covers out-of-pocket claims such as mileage, medical costs, training fees, and client entertainment expenses.</p><h2>Why and when to use a reimbursement claim form</h2><p>When people pay first and claim back later, consistency matters. A dedicated claim form separates these costs from regular expense reports, applies the right policy limits, and speeds up payout.</p><h2>Who is this template for</h2><p>HR and finance teams handling employee reimbursements, plus nonprofits reimbursing volunteer expenses.</p><h2>Why SharaForms is the best tool for this form</h2><p>Claimants attach receipts digitally, choose a claim category up front, and receive email confirmation with a copy of their claim, cutting "did you get my request" emails to zero.</p>',
            'types' => ['payment_forms', 'request_forms'],
            'industries' => ['business_forms', 'charity_forms'],
            'questions' => [
                [
                    'question' => 'Which claims belong on this form?',
                    'answer' => '<p>Mileage, parking and tolls, medical or wellness benefits, course and certification fees, and any pre-approved out-of-pocket purchase. Routine business expenses usually go on a separate expense report.</p>',
                ],
                [
                    'question' => 'How is mileage calculated?',
                    'answer' => '<p>Enter trip distance and your policy rate; the amount claimed can be computed with a calculation or entered manually. Attach route screenshots if your policy requires evidence.</p>',
                ],
                [
                    'question' => 'When will the claim be paid?',
                    'answer' => '<p>That depends on your payment cycle, not the form. Most teams review claims weekly and pay with the next payroll or supplier run. Instant email confirmation means claimants always know their submission arrived.</p>',
                ],
            ],
            'structure' => $this->structure('Reimbursement Claim', [
                ['id' => 'claimant_name', 'type' => 'text', 'title' => 'Claimant Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email for Payment Confirmation', 'required' => true, 'help' => ''],
                ['id' => 'claim_type', 'type' => 'select', 'title' => 'Claim Type', 'required' => true, 'help' => '', 'options' => [['value' => 'mileage', 'text' => 'Mileage / Travel'], ['value' => 'medical', 'text' => 'Medical / Wellness'], ['value' => 'training', 'text' => 'Training / Course Fee'], ['value' => 'client_costs', 'text' => 'Client Entertainment'], ['value' => 'other', 'text' => 'Other Pre-approved Cost']]],
                ['id' => 'mileage_distance', 'type' => 'number', 'title' => 'Trip Distance (miles)', 'required' => false, 'help' => 'Total miles for all trips claimed',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('claim_type', 'select', 'equals', 'Mileage / Travel')])],
                ['id' => 'amount_claimed', 'type' => 'number', 'title' => 'Amount Claimed', 'required' => true, 'help' => ''],
                $this->totalBlock('mileage_estimate_display', 'cv_mileage_estimate', 'Mileage Estimate (at $0.67/mile)', '$0'),
                ['id' => 'expense_date_range', 'type' => 'text', 'title' => 'Date(s) of Expense', 'required' => true, 'help' => 'e.g. 12-14 Aug 2026'],
                ['id' => 'explanation', 'type' => 'text', 'title' => 'Explanation', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'receipts', 'type' => 'files', 'title' => 'Receipts / Evidence', 'required' => true, 'help' => 'Photos or PDFs', 'max_number_of_files' => 6, 'max_file_size' => 10],
                ['id' => 'policy_ack', 'type' => 'checkbox', 'title' => 'I confirm this claim follows the reimbursement policy', 'required' => true, 'help' => '']
            ], '#0f766e', [
                'computed_variables' => [
                    $this->computedVariable('cv_mileage_estimate', 'Mileage Estimate', '{mileage_distance}*0.67'),
                ],
            ]),
        ];
    }

    private function tshirtOrder(): array
    {
        return [
            'name' => 'T-Shirt Order Form Template',
            'slug' => 'tshirt-order-form-template',
            'short_description' => 'A t-shirt order form template for merch drops, team shirts, events, and fundraisers with sizes and quantities.',
            'description' => '<p>Our T-Shirt Order Form Template handles apparel orders for teams, events, merch stores, and fundraisers without a storefront.</p><h2>Why and when to use a t-shirt order form</h2><p>Printing runs need exact size breakdowns and quantities before production starts. An order form collects every choice per buyer and aggregates cleanly, so you order the right inventory mix the first time.</p><h2>Who is this template for</h2><p>Sports teams, schools, event organizers, clothing brands doing limited drops, and fundraising campaigns.</p><h2>Why SharaForms is the best tool for this form</h2><p>Size and color dropdowns prevent invalid combinations, quantity fields feed order totals, and submissions can be exported to CSV for your printer with zero retyping.</p>',
            'types' => ['order_forms'],
            'industries' => ['ecommerce_forms', 'sports_forms'],
            'questions' => [
                [
                    'question' => 'Can buyers order multiple shirts in different sizes?',
                    'answer' => '<p>Yes. Buyers pick size and color per shirt and adjust the quantity, or submit separate entries per design. Exports show exactly how many units of each size to produce.</p>',
                ],
                [
                    'question' => 'Can I collect payment with the order?',
                    'answer' => '<p>SharaForms supports payment fields on paid plans, so buyers can pay at checkout. On free forms, collect cash or transfer outside the form and use it purely for order collection.</p>',
                ],
                [
                    'question' => 'How do buyers receive their shirts?',
                    'answer' => '<p>The delivery method field lets buyers choose pickup or shipping, and an optional address field appears for shipping orders so fulfillment has everything in one record.</p>',
                ],
            ],
            'structure' => $this->structure('T-Shirt Order', [
                ['id' => 'buyer_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'design', 'type' => 'select', 'title' => 'Design', 'required' => true, 'help' => '', 'options' => [['value' => 'classic_logo', 'text' => 'Classic Logo'], ['value' => 'anniversary', 'text' => 'Anniversary Edition'], ['value' => 'minimal', 'text' => 'Minimal Print']]],
                ['id' => 'size', 'type' => 'select', 'title' => 'Size', 'required' => true, 'help' => '', 'options' => [['value' => 'xs', 'text' => 'XS'], ['value' => 's', 'text' => 'S'], ['value' => 'm', 'text' => 'M'], ['value' => 'l', 'text' => 'L'], ['value' => 'xl', 'text' => 'XL'], ['value' => 'xxl', 'text' => 'XXL']]],
                ['id' => 'color', 'type' => 'select', 'title' => 'Color', 'required' => true, 'help' => '', 'options' => [['value' => 'black', 'text' => 'Black'], ['value' => 'white', 'text' => 'White'], ['value' => 'navy', 'text' => 'Navy'], ['value' => 'heather', 'text' => 'Heather Grey']]],
                ['id' => 'quantity', 'type' => 'number', 'title' => 'Quantity', 'required' => true, 'help' => '$15 per shirt, $2 extra for XXL'],
                ['id' => 'delivery_method', 'type' => 'select', 'title' => 'Delivery Method', 'required' => true, 'help' => '', 'options' => [['value' => 'pickup', 'text' => 'Pickup at event'], ['value' => 'ship', 'text' => 'Ship to me (+$5)']]],
                ['id' => 'shipping_address', 'type' => 'text', 'title' => 'Shipping Address', 'required' => false, 'help' => '',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('delivery_method', 'select', 'equals', 'Ship to me (+$5)')], 'and', true)],
                $this->totalBlock('order_total_display', 'cv_order_total', 'Order Total', '$0'),
            ], '#e11d48', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_order_total',
                        'Order Total',
                        '{quantity}*15+IF({size}="XXL",2,0)*{quantity}+IF({delivery_method}="Ship to me (+$5)",5,0)'
                    ),
                ],
            ]),
        ];
    }

    private function cateringOrder(): array
    {
        return [
            'name' => 'Catering Order Form Template',
            'slug' => 'catering-order-form-template',
            'short_description' => 'A catering order form template for restaurants and caterers to capture event menus, headcounts, and delivery details.',
            'description' => '<p>Our Catering Order Form Template helps caterers and restaurants take event food orders with menu selections, guest counts, dietary notes, and delivery logistics.</p><h2>Why and when to use a catering order form</h2><p>Catering quotes fall apart when details arrive over scattered calls. One structured order captures the date, headcount, package, and dietary restrictions up front, so quoting and kitchen prep start immediately.</p><h2>Who is this template for</h2><p>Catering companies, restaurants with corporate lunch programs, bakeries, and food trucks serving events.</p><h2>Why SharaForms is the best tool for this form</h2><p>Orders arrive with instant notifications, guests counts and package choices keep quoting consistent, and repeat customers re-order in seconds since their details are saved.</p>',
            'types' => ['order_forms', 'booking_forms'],
            'industries' => ['services_forms', 'business_forms'],
            'questions' => [
                [
                    'question' => 'What details should a catering order include?',
                    'answer' => '<p>Event date and time, guest count, chosen menu package or items, dietary restrictions, delivery or pickup preference, and the venue address. Contact details let you confirm the quote quickly.</p>',
                ],
                [
                    'question' => 'Can customers request custom menus?',
                    'answer' => '<p>Yes. The dietary restrictions and notes fields capture special requests, and you can follow up by phone or email before finalizing the quote.</p>',
                ],
                [
                    'question' => 'How far in advance should orders be placed?',
                    'answer' => '<p>State your cutoff in the form description, for example 72 hours before the event. The date field makes it obvious which orders are too late to accept.</p>',
                ],
            ],
            'structure' => $this->structure('Catering Order', [
                ['id' => 'contact_name', 'type' => 'text', 'title' => 'Contact Name', 'required' => true, 'help' => ''],
                ['id' => 'company', 'type' => 'text', 'title' => 'Company (if applicable)', 'required' => false, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'event_date', 'type' => 'date', 'title' => 'Event Date', 'required' => true, 'help' => ''],
                ['id' => 'guest_count', 'type' => 'number', 'title' => 'Guest Count', 'required' => true, 'help' => ''],
                ['id' => 'menu_package', 'type' => 'select', 'title' => 'Menu Package', 'required' => true, 'help' => '', 'options' => [['value' => 'breakfast', 'text' => 'Breakfast Spread'], ['value' => 'lunch_buffet', 'text' => 'Lunch Buffet'], ['value' => 'finger_food', 'text' => 'Finger Food & Canapés'], ['value' => 'formal_dinner', 'text' => 'Formal Dinner'], ['value' => 'custom', 'text' => 'Custom Menu']]],
                ['id' => 'dietary_needs', 'type' => 'text', 'title' => 'Dietary Restrictions & Allergies', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'service_type', 'type' => 'select', 'title' => 'Delivery or Pickup', 'required' => true, 'help' => '', 'options' => [['value' => 'delivery', 'text' => 'Delivery to venue'], ['value' => 'pickup', 'text' => 'Pickup'], ['value' => 'onsite_staff', 'text' => 'Delivery + On-site Staff (+$150)']]],
                ['id' => 'venue_address', 'type' => 'text', 'title' => 'Venue Address', 'required' => false, 'help' => '',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('service_type', 'select', 'does_not_equal', 'Pickup')], 'and', true)],
                $this->totalBlock('estimate_display', 'cv_estimate', 'Estimated Quote', '$0'),
            ], '#ca8a04', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_estimate',
                        'Estimated Quote',
                        'IF({menu_package}="Breakfast Spread",18,IF({menu_package}="Lunch Buffet",25,IF({menu_package}="Finger Food & Canapés",32,IF({menu_package}="Formal Dinner",55,0))))*{guest_count}'
                        . '+IF({service_type}="Delivery + On-site Staff (+$150)",150,0)'
                    ),
                ],
            ]),
        ];
    }

    private function liabilityWaiver(): array
    {
        return [
            'name' => 'Liability Waiver Form Template',
            'slug' => 'liability-waiver-form-template',
            'short_description' => 'A liability waiver form template with e-signature for gyms, events, rentals, and activities that carry physical risk.',
            'description' => '<p>Our Liability Waiver Form Template captures signed acknowledgments of risk from participants before they take part in an activity.</p><h2>Why and when to use a liability waiver</h2><p>Any activity with physical risk deserves a documented acknowledgment: gyms and fitness classes, adventure sports, equipment rentals, volunteer events, and youth programs. Paper waivers get lost; a digital waiver creates a timestamped record with a signature every time.</p><h2>Who is this template for</h2><p>Gyms and studios, event organizers, tour and rental operators, sports leagues, and nonprofits running activities.</p><h2>Why SharaForms is the best tool for this form</h2><p>The built-in signature field captures legally styled e-consent, guardian fields support minors, and every signed waiver is stored and searchable if a question ever arises later.</p>',
            'types' => ['consent_forms'],
            'industries' => ['sports_forms', 'entertainment_forms'],
            'questions' => [
                [
                    'question' => 'Is an electronic signature valid for waivers?',
                    'answer' => '<p>In most jurisdictions, yes. E-signature regulations such as ESIGN (US) and eIDAS (EU) recognize electronic signatures, provided the signer clearly indicates agreement. This template pairs a signature field with explicit consent checkboxes and stores the submission time as evidence.</p>',
                ],
                [
                    'question' => 'How do I handle minors?',
                    'answer' => '<p>Include the optional parent or guardian section: a minor indicator plus guardian name and signature. Many organizations require a guardian-signed waiver for anyone under 18.</p>',
                ],
                [
                    'question' => 'Does this template replace legal advice?',
                    'answer' => '<p>No. The template provides the structure, but the waiver language itself should be reviewed by a lawyer for your jurisdiction and activity type. You can edit every field and text block after copying.</p>',
                ],
            ],
            'structure' => $this->structure('Activity Waiver & Release', [
                ['id' => 'participant_name', 'type' => 'text', 'title' => 'Participant Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'activity_date', 'type' => 'date', 'title' => 'Activity Date', 'required' => true, 'help' => ''],
                ['id' => 'is_minor', 'type' => 'checkbox', 'title' => 'Participant is under 18', 'required' => false, 'help' => 'Checking this reveals the guardian section below'],
                ['id' => 'guardian_name', 'type' => 'text', 'title' => 'Parent / Guardian Name', 'required' => false, 'help' => '',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('is_minor', 'checkbox', 'is_checked')], 'and', true)],
                ['id' => 'guardian_signature', 'type' => 'signature', 'title' => 'Parent / Guardian Signature', 'required' => false, 'help' => 'Guardian signs on behalf of the minor',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('is_minor', 'checkbox', 'is_checked')], 'and', true)],
                ['id' => 'emergency_contact', 'type' => 'phone_number', 'title' => 'Emergency Contact Number', 'required' => true, 'help' => ''],
                ['id' => 'health_notes', 'type' => 'text', 'title' => 'Medical Conditions We Should Know About', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'risk_acknowledgment', 'type' => 'checkbox', 'title' => 'I understand the activity involves inherent risks and voluntarily assume them', 'required' => true, 'help' => ''],
                ['id' => 'release_acknowledgment', 'type' => 'checkbox', 'title' => 'I release the organizer from liability for injuries arising from participation', 'required' => true, 'help' => ''],
                ['id' => 'signature', 'type' => 'signature', 'title' => 'Signature', 'required' => true, 'help' => 'Sign here']
            ], '#dc2626'),
        ];
    }

    private function photoRelease(): array
    {
        return [
            'name' => 'Photo Release Form Template',
            'slug' => 'photo-release-form-template',
            'short_description' => 'A photo release form template with e-signature covering model consent and media usage rights.',
            'description' => '<p>Our Photo Release Form Template documents how photos or videos of a person may be used: scope, duration, and compensation, all captured with a signature.</p><h2>Why and when to use a photo release</h2><p>Publishing someone\'s image without documented permission creates legal risk. Photographers, marketers, schools, and nonprofits use releases before images appear on websites, social media, ads, or print.</p><h2>Who is this template for</h2><p>Photographers, marketing teams, agencies, schools, event organizers, and content creators.</p><h2>Why SharaForms is the best tool for this form</h2><p>Usage scope and duration are structured fields instead of free text, signatures are captured digitally, and completed releases stay attached to your project records forever.</p>',
            'types' => ['consent_forms'],
            'industries' => ['photography_forms', 'advertising_forms'],
            'questions' => [
                [
                    'question' => 'What usage rights should a release cover?',
                    'answer' => '<p>Define where images may appear (website, social media, print, advertising), for how long, and whether use is compensated. Structured selections prevent misunderstandings that broad "all media" wording can cause.</p>',
                ],
                [
                    'question' => 'Can I customize which rights are requested?',
                    'answer' => '<p>Yes. After copying the template you can edit options, add your own terms text, or restrict the form to a single usage type for one-off shoots.</p>',
                ],
                [
                    'question' => 'Do parents need to sign for children?',
                    'answer' => '<p>Yes, releases for minors must be signed by a parent or guardian. Include the guardian name and signature fields exactly as this template does.</p>',
                ],
            ],
            'structure' => $this->structure('Photo & Media Release', [
                ['id' => 'model_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'shoot_reference', 'type' => 'text', 'title' => 'Shoot / Project Reference', 'required' => false, 'help' => ''],
                ['id' => 'usage_scope', 'type' => 'select', 'title' => 'Permitted Usage', 'required' => true, 'help' => '', 'options' => [['value' => 'website_social', 'text' => 'Website & Social Media'], ['value' => 'marketing_print', 'text' => 'Marketing & Print'], ['value' => 'all_media', 'text' => 'All Media Including Ads'], ['value' => 'internal', 'text' => 'Internal Use Only']]],
                ['id' => 'usage_duration', 'type' => 'select', 'title' => 'Duration of Rights', 'required' => true, 'help' => '', 'options' => [['value' => 'one_year', 'text' => '1 Year'], ['value' => 'five_years', 'text' => '5 Years'], ['value' => 'indefinite', 'text' => 'Indefinite']]],
                ['id' => 'compensation', 'type' => 'select', 'title' => 'Compensation', 'required' => true, 'help' => '', 'options' => [['value' => 'uncompensated', 'text' => 'Uncompensated'], ['value' => 'fee', 'text' => 'One-time Fee']]],
                ['id' => 'is_minor', 'type' => 'checkbox', 'title' => 'Subject is under 18', 'required' => false, 'help' => 'Checking this reveals the guardian section below'],
                ['id' => 'guardian_name', 'type' => 'text', 'title' => 'Parent / Guardian Name', 'required' => false, 'help' => '',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('is_minor', 'checkbox', 'is_checked')], 'and', true)],
                ['id' => 'guardian_signature', 'type' => 'signature', 'title' => 'Parent / Guardian Signature', 'required' => false, 'help' => 'Guardian consents on behalf of the minor',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('is_minor', 'checkbox', 'is_checked')], 'and', true)],
                ['id' => 'consent_ack', 'type' => 'checkbox', 'title' => 'I grant the usage rights selected above and waive inspection approval', 'required' => true, 'help' => ''],
                ['id' => 'signature', 'type' => 'signature', 'title' => 'Signature', 'required' => true, 'help' => 'Sign here']
            ], '#db2777'),
        ];
    }

    private function employeeOnboarding(): array
    {
        return [
            'name' => 'Employee Onboarding Form Template',
            'slug' => 'employee-onboarding-form-template',
            'short_description' => 'An employee onboarding form template collecting new-hire details, equipment needs, and first-week logistics.',
            'description' => '<p>Our Employee Onboarding Form Template collects everything HR and IT need before day one: personal details, equipment requests, emergency contacts, and intro blurb.</p><h2>Why and when to use an employee onboarding form</h2><p>Great first weeks are engineered. Sending one form right after offer acceptance means laptops arrive configured, accounts exist, teammates know the new face, and nothing depends on chasing details by email.</p><h2>Who is this template for</h2><p>HR teams, people operations, and IT departments preparing for new joiners.</p><h2>Why SharaForms is the best tool for this form</h2><p>Equipment choices route to IT via notifications, bio and photo feed straight into team-page templates, and submissions export cleanly into your HRIS import format.</p>',
            'types' => ['employment_forms'],
            'industries' => ['human_resources_forms'],
            'questions' => [
                [
                    'question' => 'When should the onboarding form be sent?',
                    'answer' => '<p>Ideally within days of offer acceptance, at least two weeks before start date. That leaves time for equipment orders and account setup based on the responses.</p>',
                ],
                [
                    'question' => 'Should the form collect bank or tax details?',
                    'answer' => '<p>We recommend keeping sensitive payroll data out of general forms and using your dedicated, secured payroll portal for it. This template covers everything else needed for day one.</p>',
                ],
                [
                    'question' => 'Can different roles get different questions?',
                    'answer' => '<p>Yes. Conditional logic can show extra questions based on department or role selection, for example developer tooling for engineers or sales software access for account executives.</p>',
                ],
            ],
            'structure' => $this->structure('New Employee Onboarding', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'preferred_name', 'type' => 'text', 'title' => 'Preferred Name', 'required' => false, 'help' => ''],
                ['id' => 'personal_email', 'type' => 'email', 'title' => 'Personal Email', 'required' => true, 'help' => 'Before company account exists'],
                ['id' => 'start_date', 'type' => 'date', 'title' => 'Start Date', 'required' => true, 'help' => ''],
                ['id' => 'role', 'type' => 'text', 'title' => 'Role / Title', 'required' => true, 'help' => ''],
                ['id' => 'equipment', 'type' => 'multi_select', 'title' => 'Equipment Needed', 'required' => true, 'help' => '', 'options' => [['value' => 'laptop', 'text' => 'Laptop'], ['value' => 'monitor', 'text' => 'Monitor'], ['value' => 'headset', 'text' => 'Headset'], ['value' => 'phone', 'text' => 'Company Phone'], ['value' => 'dock', 'text' => 'Docking Station']]],
                ['id' => 'emergency_contact', 'type' => 'phone_number', 'title' => 'Emergency Contact Number', 'required' => true, 'help' => ''],
                ['id' => 'bio', 'type' => 'text', 'title' => 'Short Intro Bio', 'required' => false, 'help' => 'For the team page - hobbies, background, fun fact', 'multi_lines' => true],
                ['id' => 'photo', 'type' => 'files', 'title' => 'Profile Photo', 'required' => false, 'help' => 'Optional', 'max_number_of_files' => 1, 'max_file_size' => 5]
            ], '#0d9488'),
        ];
    }

    private function exitInterview(): array
    {
        return [
            'name' => 'Exit Interview Form Template',
            'slug' => 'exit-interview-form-template',
            'short_description' => 'An exit interview form template to capture honest departure feedback and reduce future attrition.',
            'description' => '<p>Our Exit Interview Form Template gathers candid feedback from departing employees about their experience, reasons for leaving, and what the company could improve.</p><h2>Why and when to use an exit interview form</h2><p>Departing employees give the most honest feedback you will ever receive. Capturing it consistently turns individual departures into patterns you can fix: management issues, pay gaps, or growth ceilings show up across multiple exits.</p><h2>Who is this template for</h2><p>HR teams and people operations at companies that want retention driven by evidence rather than guesswork.</p><h2>Why SharaForms is the best tool for this form</h2><p>Anonymous-friendly design encourages honesty, satisfaction scales quantify sentiment over time, and exports let HR analyze themes across many exits.</p>',
            'types' => ['interview_forms', 'survey_templates'],
            'industries' => ['human_resources_forms'],
            'questions' => [
                [
                    'question' => 'Should exit interviews be anonymous?',
                    'answer' => '<p>Name fields can be removed entirely so responses cannot be linked to individuals. Many companies keep role and tenure but drop identity to increase candor while preserving context.</p>',
                ],
                [
                    'question' => 'When should the form be sent?',
                    'answer' => '<p>During the final week, after the resignation decision is final but while the experience is fresh. Avoid the very last day when attention has already moved on.</p>',
                ],
                [
                    'question' => 'What should we do with the results?',
                    'answer' => '<p>Aggregate quarterly. Single exits are anecdotes; three people citing the same manager or pay band is a signal worth acting on. CSV export makes that analysis straightforward.</p>',
                ],
            ],
            'structure' => $this->structure('Exit Interview', [
                ['id' => 'department', 'type' => 'select', 'title' => 'Department', 'required' => true, 'help' => '', 'options' => [['value' => 'engineering', 'text' => 'Engineering'], ['value' => 'sales', 'text' => 'Sales'], ['value' => 'marketing', 'text' => 'Marketing'], ['value' => 'support', 'text' => 'Support'], ['value' => 'operations', 'text' => 'Operations'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'tenure', 'type' => 'select', 'title' => 'Tenure at Company', 'required' => true, 'help' => '', 'options' => [['value' => 'lt_1y', 'text' => 'Less than 1 year'], ['value' => '1_3y', 'text' => '1-3 years'], ['value' => '3_5y', 'text' => '3-5 years'], ['value' => 'gt_5y', 'text' => 'More than 5 years']]],
                ['id' => 'primary_reason', 'type' => 'select', 'title' => 'Primary Reason for Leaving', 'required' => true, 'help' => '', 'options' => [['value' => 'new_opportunity', 'text' => 'New Opportunity'], ['value' => 'compensation', 'text' => 'Compensation & Benefits'], ['value' => 'management', 'text' => 'Management'], ['value' => 'career_growth', 'text' => 'Limited Career Growth'], ['value' => 'culture', 'text' => 'Culture / Work Environment'], ['value' => 'relocation', 'text' => 'Relocation / Personal']]],
                ['id' => 'satisfaction', 'type' => 'scale', 'title' => 'How satisfied were you working here overall?', 'required' => true, 'help' => '1 = not satisfied, 10 = extremely satisfied', 'scale_min_value' => 1, 'scale_max_value' => 10, 'scale_step_value' => 1],
                ['id' => 'what_improve', 'type' => 'text', 'title' => 'What could we improve for the team you leave behind?', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'would_recommend', 'type' => 'select', 'title' => 'Would you recommend us as an employer?', 'required' => true, 'help' => '', 'options' => [['value' => 'yes', 'text' => 'Yes'], ['value' => 'maybe', 'text' => 'Maybe'], ['value' => 'no', 'text' => 'No']]],
                ['id' => 'comments', 'type' => 'text', 'title' => 'Anything else you would like to share?', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#64748b'),
        ];
    }

    private function selfEvaluation(): array
    {
        return [
            'name' => 'Self-Evaluation Form Template',
            'slug' => 'self-evaluation-form-template',
            'short_description' => 'A self-evaluation form template for employees to reflect on achievements, challenges, and goals ahead of performance reviews.',
            'description' => '<p>Our Self-Evaluation Form Template structures employee reflection before performance reviews: achievements, challenges, skill ratings, and next-period goals.</p><h2>Why and when to use a self-evaluation form</h2><p>Reviews go better when employees arrive prepared. A written self-assessment gives managers context they would otherwise miss, surfaces wins employees remember and managers forgot, and makes the review conversation two-sided.</p><h2>Who is this template for</h2><p>People teams and managers running quarterly or annual review cycles.</p><h2>Why SharaForms is the best tool for this form</h2><p>Structured sections keep reflections comparable across the team, rating scales quantify skill confidence, and submissions pair naturally with your existing review docs.</p>',
            'types' => ['evaluation_forms'],
            'industries' => ['human_resources_forms'],
            'questions' => [
                [
                    'question' => 'What belongs in the achievements section?',
                    'answer' => '<p>Concrete outcomes with numbers where possible: projects shipped, targets beaten, problems solved, or praise received. Specifics make reviews fairer than generic effort statements.</p>',
                ],
                [
                    'question' => 'Is the skills rating visible to my manager?',
                    'answer' => '<p>Submissions go to whoever receives form notifications, typically your manager or HR. Check with your people team on how results are shared in your company.</p>',
                ],
                [
                    'question' => 'How long should answers be?',
                    'answer' => '<p>Two to four sentences per prompt works well. The goal is honest reflection, not essay writing. Bullet points are fine.</p>',
                ],
            ],
            'structure' => $this->structure('Self-Evaluation', [
                ['id' => 'employee_name', 'type' => 'text', 'title' => 'Name', 'required' => true, 'help' => ''],
                ['id' => 'review_period', 'type' => 'select', 'title' => 'Review Period', 'required' => true, 'help' => '', 'options' => [['value' => 'q1', 'text' => 'Q1'], ['value' => 'q2', 'text' => 'Q2'], ['value' => 'q3', 'text' => 'Q3'], ['value' => 'q4', 'text' => 'Q4'], ['value' => 'annual', 'text' => 'Full Year']]],
                ['id' => 'achievements', 'type' => 'text', 'title' => 'Key Achievements This Period', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'challenges', 'type' => 'text', 'title' => 'Challenges & Lessons Learned', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'skills_confidence', 'type' => 'rating', 'title' => 'Confidence in Core Skills', 'required' => true, 'help' => '1 = still learning, 5 = very confident', 'rating_max_value' => 5],
                ['id' => 'goals_next', 'type' => 'text', 'title' => 'Goals for Next Period', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'support_needed', 'type' => 'text', 'title' => 'Support or Resources Needed', 'required' => false, 'help' => '', 'multi_lines' => true]
            ], '#3b82f6'),
        ];
    }

    private function scholarshipApplication(): array
    {
        return [
            'name' => 'Scholarship Application Form Template',
            'slug' => 'scholarship-application-form-template',
            'short_description' => 'A scholarship application form template capturing student details, essays, transcripts, and references.',
            'description' => '<p>Our Scholarship Application Form Template collects applicant details, financial need statements, essays, transcripts, and referee contacts in one organized submission.</p><h2>Why and when to use a scholarship application form</h2><p>Scholarship committees compare dozens of candidates fairly only when applications arrive in the same shape. One form standardizes essays, documents, and eligibility declarations so reviewers score applicants, not formatting choices.</p><h2>Who is this template for</h2><p>Universities, foundations, employers awarding education grants, community organizations, and alumni associations.</p><h2>Why SharaForms is the best tool for this form</h2><p>File uploads accept transcripts and recommendation letters, closing dates enforce deadlines automatically, and every application lands in one dashboard instead of a chaotic inbox.</p>',
            'types' => ['application_forms'],
            'industries' => ['education_forms', 'charity_forms'],
            'questions' => [
                [
                    'question' => 'What documents should applicants attach?',
                    'answer' => '<p>Most scholarships ask for academic transcripts, proof of enrollment, and sometimes recommendation letters. The file upload field accepts PDFs and images up to your size limits.</p>',
                ],
                [
                    'question' => 'How do we enforce the deadline?',
                    'answer' => '<p>SharaForms supports scheduled form closure. Set the closing date once and late submissions are blocked automatically, with no manual switching off.</p>',
                ],
                [
                    'question' => 'Can multiple committee members review applications?',
                    'answer' => '<p>Yes. Export all submissions to CSV for blind scoring rounds, or share the form workspace with committee members so everyone sees the same complete applications.</p>',
                ],
            ],
            'structure' => $this->structure('Scholarship Application', [
                ['id' => 'applicant_name', 'type' => 'text', 'title' => 'Applicant Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'institution', 'type' => 'text', 'title' => 'School / University', 'required' => true, 'help' => ''],
                ['id' => 'program', 'type' => 'text', 'title' => 'Program of Study', 'required' => true, 'help' => ''],
                ['id' => 'gpa', 'type' => 'number', 'title' => 'Current GPA', 'required' => false, 'help' => ''],
                ['id' => 'financial_need', 'type' => 'text', 'title' => 'Financial Need Statement', 'required' => true, 'help' => 'Briefly describe your circumstances', 'multi_lines' => true],
                ['id' => 'essay', 'type' => 'text', 'title' => 'Personal Statement', 'required' => true, 'help' => 'Up to 500 words: your goals and why you deserve this scholarship', 'multi_lines' => true],
                ['id' => 'transcript', 'type' => 'files', 'title' => 'Transcript / Supporting Documents', 'required' => true, 'help' => 'PDF preferred', 'max_number_of_files' => 4, 'max_file_size' => 15],
                ['id' => 'eligibility_consent', 'type' => 'checkbox', 'title' => 'I confirm I meet the eligibility criteria', 'required' => true, 'help' => '']
            ], '#7c3aed'),
        ];
    }

    private function grantApplication(): array
    {
        return [
            'name' => 'Grant Application Form Template',
            'slug' => 'grant-application-form-template',
            'short_description' => 'A grant application form template for foundations collecting project proposals, budgets, and impact plans.',
            'description' => '<p>Our Grant Application Form Template helps foundations and grant programs collect structured proposals: organization profile, project summary, funding request, budget, and expected impact.</p><h2>Why and when to use a grant application form</h2><p>Reviewing proposals from email attachments wastes committee time. A structured form normalizes every application to your criteria, makes side-by-side comparison possible, and keeps the full history for reporting to your own board.</p><h2>Who is this template for</h2><p>Foundations, corporate giving programs, government funds, accelerators, and any organization distributing grants.</p><h2>Why SharaForms is the best tool for this form</h2><p>Budget files attach directly to applications, numeric funding fields enable clean sorting, and closed-date scheduling enforces round deadlines without manual policing.</p>',
            'types' => ['application_forms'],
            'industries' => ['charity_forms', 'education_forms'],
            'questions' => [
                [
                    'question' => 'What should a strong project summary contain?',
                    'answer' => '<p>The problem being addressed, who benefits, what the grant will fund specifically, and the measurable outcome you expect. Reviewers should grasp the project in under a minute.</p>',
                ],
                [
                    'question' => 'Can organizations apply for multiple grants?',
                    'answer' => '<p>Yes, each program gets its own copy of the form with tailored questions and amounts, while all submissions live under your SharaForms workspace.</p>',
                ],
                [
                    'question' => 'How much budget detail is required?',
                    'answer' => '<p>This template asks for a budget document upload plus the headline funding amount. Adjust both fields to match your round requirements, from light-touch micro-grants to full financial reviews.</p>',
                ],
            ],
            'structure' => $this->structure('Grant Application', [
                ['id' => 'organization_name', 'type' => 'text', 'title' => 'Organization Name', 'required' => true, 'help' => ''],
                ['id' => 'contact_person', 'type' => 'text', 'title' => 'Primary Contact', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'org_type', 'type' => 'select', 'title' => 'Organization Type', 'required' => true, 'help' => '', 'options' => [['value' => 'nonprofit', 'text' => 'Nonprofit / NGO'], ['value' => 'social_enterprise', 'text' => 'Social Enterprise'], ['value' => 'startup', 'text' => 'Startup'], ['value' => 'academic', 'text' => 'Academic Institution'], ['value' => 'community', 'text' => 'Community Group']]],
                ['id' => 'project_title', 'type' => 'text', 'title' => 'Project Title', 'required' => true, 'help' => ''],
                ['id' => 'funding_amount', 'type' => 'number', 'title' => 'Funding Amount Requested', 'required' => true, 'help' => ''],
                ['id' => 'project_summary', 'type' => 'text', 'title' => 'Project Summary', 'required' => true, 'help' => 'Problem, beneficiaries, and requested use of funds', 'multi_lines' => true],
                ['id' => 'impact_plan', 'type' => 'text', 'title' => 'Expected Impact & Measurement', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'budget_document', 'type' => 'files', 'title' => 'Budget & Supporting Documents', 'required' => true, 'help' => 'PDF preferred', 'max_number_of_files' => 4, 'max_file_size' => 15],
                ['id' => 'accuracy_declaration', 'type' => 'checkbox', 'title' => 'I confirm the information provided is accurate', 'required' => true, 'help' => '']
            ], '#16a34a'),
        ];
    }

    private function clientOnboarding(): array
    {
        return [
            'name' => 'Client Onboarding Form Template',
            'slug' => 'client-onboarding-form-template',
            'short_description' => 'A client onboarding form template for agencies to gather brand assets, goals, and access details before kickoff.',
            'description' => '<p>Our Client Onboarding Form Template collects the goals, brand materials, audience details, and preferences agencies need before project kickoff.</p><h2>Why and when to use a client onboarding form</h2><p>Kickoff calls stall when basic facts are unknown. Sending one structured form after signing turns week one from information gathering into actual work, and clients love how professional it feels.</p><h2>Who is this template for</h2><p>Marketing agencies, design studios, consultancies, freelancers, and service businesses starting new client engagements.</p><h2>Why SharaForms is the best tool for this form</h2><p>Brand asset uploads land with the rest of the brief, URL fields capture websites and social profiles cleanly, and everything arrives searchable in one place instead of scattered threads.</p>',
            'types' => ['questionnaire_templates'],
            'industries' => ['services_forms', 'marketing_forms'],
            'questions' => [
                [
                    'question' => 'When should clients receive the onboarding form?',
                    'answer' => '<p>Immediately after contract signature, ideally with your welcome email. Early responses mean real work can begin at kickoff instead of after it.</p>',
                ],
                [
                    'question' => 'What brand assets should we request?',
                    'answer' => '<p>Logos in vector formats if available, brand guidelines, fonts, photography, and past campaign examples. The file field accepts multiple uploads so everything arrives together.</p>',
                ],
                [
                    'question' => 'Can the form adapt to different service types?',
                    'answer' => '<p>Yes. Use conditional logic to show SEO-specific questions for search clients or platform questions for social clients, keeping every form relevant to the engagement.</p>',
                ],
            ],
            'structure' => $this->structure('Client Onboarding', [
                ['id' => 'business_name', 'type' => 'text', 'title' => 'Business Name', 'required' => true, 'help' => ''],
                ['id' => 'website_url', 'type' => 'url', 'title' => 'Website', 'required' => true, 'help' => ''],
                ['id' => 'industry', 'type' => 'text', 'title' => 'Industry', 'required' => true, 'help' => ''],
                ['id' => 'primary_contact', 'type' => 'text', 'title' => 'Primary Contact Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Contact Email', 'required' => true, 'help' => ''],
                ['id' => 'goals', 'type' => 'text', 'title' => 'Primary Goals for This Engagement', 'required' => true, 'help' => 'What does success look like?', 'multi_lines' => true],
                ['id' => 'target_audience', 'type' => 'text', 'title' => 'Target Audience', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'competitors', 'type' => 'text', 'title' => 'Main Competitors', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'brand_assets', 'type' => 'files', 'title' => 'Brand Assets Upload', 'required' => false, 'help' => 'Logos, guidelines, fonts, photos', 'max_number_of_files' => 10, 'max_file_size' => 20],
                ['id' => 'communication_pref', 'type' => 'select', 'title' => 'Preferred Communication Channel', 'required' => true, 'help' => '', 'options' => [['value' => 'email', 'text' => 'Email'], ['value' => 'slack', 'text' => 'Slack'], ['value' => 'whatsapp', 'text' => 'WhatsApp'], ['value' => 'calls', 'text' => 'Scheduled Calls']]]
            ], '#ea580c'),
        ];
    }

    private function projectBrief(): array
    {
        return [
            'name' => 'Project Brief Form Template',
            'slug' => 'project-brief-form-template',
            'short_description' => 'A project brief form template for creative and web projects capturing objectives, deliverables, budgets, and deadlines.',
            'description' => '<p>Our Project Brief Form Template turns scattered kickoff notes into one structured brief: objectives, audience, deliverables, budget range, and deadline.</p><h2>Why and when to use a project brief form</h2><p>Scope creep starts with vague briefs. Collecting the essentials in a structured form forces decisions before work begins, gives every stakeholder the same reference point, and protects margins when requests drift beyond scope.</p><h2>Who is this template for</h2><p>Web design studios, branding agencies, video producers, marketing teams briefing internal creatives, and freelancers.</p><h2>Why SharaForms is the best tool for this form</h2><p>Budget ranges stay structured for quoting, deliverable multiselects map directly to statements of work, and completed briefs export cleanly into your project management tool.</p>',
            'types' => ['questionnaire_templates', 'content_forms'],
            'industries' => ['web_design_forms', 'advertising_forms', 'marketing_forms'],
            'questions' => [
                [
                    'question' => 'What makes a good project brief?',
                    'answer' => '<p>A clear objective, a defined audience, specific deliverables, an honest budget range, and a real deadline. If any of those are missing, the brief will generate questions instead of answers.</p>',
                ],
                [
                    'question' => 'Who should fill out the brief?',
                    'answer' => '<p>The person who owns the outcome, usually the client contact or product owner. You can pre-fill known fields by sharing a pre-filled link so clients only complete what you do not know yet.</p>',
                ],
                [
                    'question' => 'Should the budget field be optional?',
                    'answer' => '<p>We recommend keeping it required but offering ranges rather than exact numbers. Ranges feel less exposing for clients while still anchoring scope conversations.</p>',
                ],
            ],
            'structure' => $this->structure('Project Brief', [
                ['id' => 'client_company', 'type' => 'text', 'title' => 'Client / Company', 'required' => true, 'help' => ''],
                ['id' => 'contact_person', 'type' => 'text', 'title' => 'Contact Person', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'project_name', 'type' => 'text', 'title' => 'Project Name', 'required' => true, 'help' => ''],
                ['id' => 'project_type', 'type' => 'select', 'title' => 'Project Type', 'required' => true, 'help' => '', 'options' => [['value' => 'website', 'text' => 'Website'], ['value' => 'branding', 'text' => 'Branding / Identity'], ['value' => 'campaign', 'text' => 'Marketing Campaign'], ['value' => 'video', 'text' => 'Video / Motion'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'objectives', 'type' => 'text', 'title' => 'Objectives & Success Criteria', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'deliverables', 'type' => 'multi_select', 'title' => 'Deliverables Needed', 'required' => true, 'help' => '', 'options' => [['value' => 'logo', 'text' => 'Logo / Identity'], ['value' => 'website', 'text' => 'Website'], ['value' => 'copywriting', 'text' => 'Copywriting'], ['value' => 'video', 'text' => 'Video Content'], ['value' => 'social_kit', 'text' => 'Social Media Kit']]],
                ['id' => 'budget_range', 'type' => 'select', 'title' => 'Budget Range', 'required' => true, 'help' => '', 'options' => [['value' => 'lt_5k', 'text' => 'Under $5,000'], ['value' => '5k_15k', 'text' => '$5,000 - $15,000'], ['value' => '15k_50k', 'text' => '$15,000 - $50,000'], ['value' => 'gt_50k', 'text' => '$50,000+']]],
                ['id' => 'deadline', 'type' => 'date', 'title' => 'Target Launch Date', 'required' => true, 'help' => ''],
                ['id' => 'references_url', 'type' => 'url', 'title' => 'Reference Links', 'required' => false, 'help' => 'Examples you like']
            ], '#0d9488'),
        ];
    }

    private function bugReport(): array
    {
        return [
            'name' => 'Bug Report Form Template',
            'slug' => 'bug-report-form-template',
            'short_description' => 'A bug report form template that captures reproducible steps, severity, environment, and screenshots for faster fixes.',
            'description' => '<p>Our Bug Report Form Template captures everything engineers need to reproduce and fix issues: summary, steps, expected vs actual behavior, environment, and evidence.</p><h2>Why and when to use a bug report form</h2><p>"It does not work" emails cost engineering time in back-and-forth. A structured report front-loads the details that separate a ten-minute fix from a three-day investigation, and severity triage keeps critical issues at the top of the queue.</p><h2>Who is this template for</h2><p>SaaS product teams, QA departments, IT helpdesks, and open-source maintainers collecting issue reports from non-technical users.</p><h2>Why SharaForms is the best tool for this form</h2><p>Screenshots and log files attach directly to reports, severity and environment dropdowns enable instant triage, and reports can stream into Slack or your issue tracker via webhooks.</p>',
            'types' => ['report_forms', 'file_upload_forms'],
            'industries' => ['it_forms', 'business_forms'],
            'questions' => [
                [
                    'question' => 'What should a good bug report contain?',
                    'answer' => '<p>Numbered steps to reproduce, what you expected to happen, what actually happened, where it happened (environment), and screenshots or logs. Reproducible reports get fixed fastest.</p>',
                ],
                [
                    'question' => 'How is severity different from priority?',
                    'answer' => '<p>Severity measures impact: a crash on checkout is critical even if it affects few users; a typo is minor even if visible to everyone. This template captures severity so your team sets priority during triage.</p>',
                ],
                [
                    'question' => 'Can we send reports straight into our tracker?',
                    'answer' => '<p>Yes. Webhooks or Zapier can push each submission into Jira, Linear, GitHub, or Slack as soon as it arrives, so nothing waits in an inbox.</p>',
                ],
            ],
            'structure' => $this->structure('Bug Report', [
                ['id' => 'reporter_email', 'type' => 'email', 'title' => 'Your Email (for follow-up)', 'required' => false, 'help' => ''],
                ['id' => 'area', 'type' => 'select', 'title' => 'Product Area', 'required' => true, 'help' => '', 'options' => [['value' => 'dashboard', 'text' => 'Dashboard'], ['value' => 'forms', 'text' => 'Forms'], ['value' => 'billing', 'text' => 'Billing'], ['value' => 'integrations', 'text' => 'Integrations'], ['value' => 'mobile', 'text' => 'Mobile'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'environment', 'type' => 'select', 'title' => 'Environment', 'required' => true, 'help' => '', 'options' => [['value' => 'production', 'text' => 'Production'], ['value' => 'staging', 'text' => 'Staging'], ['value' => 'development', 'text' => 'Development']]],
                ['id' => 'severity', 'type' => 'select', 'title' => 'Severity', 'required' => true, 'help' => '', 'options' => [['value' => 'critical', 'text' => 'Critical - blocked, no workaround'], ['value' => 'major', 'text' => 'Major - feature unusable, workaround exists'], ['value' => 'minor', 'text' => 'Minor - annoying but usable'], ['value' => 'trivial', 'text' => 'Trivial - cosmetic']]],
                ['id' => 'summary', 'type' => 'text', 'title' => 'One-line Summary', 'required' => true, 'help' => ''],
                ['id' => 'steps', 'type' => 'text', 'title' => 'Steps to Reproduce', 'required' => true, 'help' => '1... 2... 3...', 'multi_lines' => true],
                ['id' => 'expected_vs_actual', 'type' => 'text', 'title' => 'Expected vs Actual Result', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'device_info', 'type' => 'text', 'title' => 'Device / Browser / OS', 'required' => false, 'help' => 'e.g. Chrome 126 on Windows 11'],
                ['id' => 'evidence', 'type' => 'files', 'title' => 'Screenshots / Logs', 'required' => false, 'help' => 'Optional but helpful', 'max_number_of_files' => 6, 'max_file_size' => 15]
            ], '#9f1239'),
        ];
    }

    private function maintenanceRequest(): array
    {
        return [
            'name' => 'Maintenance Request Form Template',
            'slug' => 'maintenance-request-form-template',
            'short_description' => 'A maintenance request form template for tenants to report repairs with photos, urgency, and access permissions.',
            'description' => '<p>Our Maintenance Request Form Template lets tenants report repair issues with location, category, urgency, photos, and entry permission in one submission.</p><h2>Why and when to use a maintenance request form</h2><p>Tenants text photos at midnight and details get lost. A structured request records what broke, where, how urgent it is, and whether the tenant grants entry permission, creating a documented trail that protects both landlord and tenant.</p><h2>Who is this template for</h2><p>Property managers, landlords, HOAs, facilities teams, and co-working operators maintaining physical spaces.</p><h2>Why SharaForms is the best tool for this form</h2><p>New requests notify your maintenance line instantly, photo uploads document condition before repairs, and the full history per property stays searchable for compliance and deposit disputes.</p>',
            'types' => ['request_forms'],
            'industries' => ['real_estate_forms', 'services_forms'],
            'questions' => [
                [
                    'question' => 'What counts as a maintenance emergency?',
                    'answer' => '<p>Burst pipes, gas smells, total power loss, security failures, or anything posing immediate safety risk. Set expectations in the form description: emergencies should also trigger a phone call, not just the form.</p>',
                ],
                [
                    'question' => 'Why ask for entry permission up front?',
                    'answer' => '<p>Most jurisdictions require notice or consent before entering occupied units. Capturing permission in the request itself removes a whole round of phone tag before scheduling contractors.</p>',
                ],
                [
                    'question' => 'Can tenants track their request status?',
                    'answer' => '<p>Submitters receive an instant confirmation email, and you can reply from the same thread as work progresses. For larger portfolios, webhook updates can feed status changes into tenant communication tools.</p>',
                ],
            ],
            'structure' => $this->structure('Maintenance Request', [
                ['id' => 'tenant_name', 'type' => 'text', 'title' => 'Tenant Name', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Contact Number', 'required' => true, 'help' => ''],
                ['id' => 'unit_address', 'type' => 'text', 'title' => 'Property / Unit Address', 'required' => true, 'help' => ''],
                ['id' => 'category', 'type' => 'select', 'title' => 'Issue Category', 'required' => true, 'help' => '', 'options' => [['value' => 'plumbing', 'text' => 'Plumbing'], ['value' => 'electrical', 'text' => 'Electrical'], ['value' => 'hvac', 'text' => 'Heating / Cooling'], ['value' => 'appliance', 'text' => 'Appliance'], ['value' => 'lock_security', 'text' => 'Locks / Security'], ['value' => 'pest', 'text' => 'Pest Control'], ['value' => 'other', 'text' => 'Other']]],
                ['id' => 'urgency', 'type' => 'select', 'title' => 'Urgency', 'required' => true, 'help' => '', 'options' => [['value' => 'emergency', 'text' => 'Emergency - safety risk, call us too'], ['value' => 'high', 'text' => 'High - needs fixing within 24-48h'], ['value' => 'medium', 'text' => 'Medium - within a week'], ['value' => 'low', 'text' => 'Low - whenever convenient']]],
                ['id' => 'description', 'type' => 'text', 'title' => 'Describe the Issue', 'required' => true, 'help' => 'What happened, where exactly, since when', 'multi_lines' => true],
                ['id' => 'emergency_notice', 'type' => 'nf-text', 'name' => 'Emergency Notice', 'content' => '<p><strong>Emergency?</strong> For gas smells, burst pipes, or total power loss, call our 24/7 line at (555) 010-0199 as well - do not rely on this form alone.</p>',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('urgency', 'select', 'equals', 'Emergency - safety risk, call us too')])],
                ['id' => 'photos', 'type' => 'files', 'title' => 'Photos', 'required' => false, 'help' => 'Up to 4 photos help contractors quote faster', 'max_number_of_files' => 4, 'max_file_size' => 10],
                ['id' => 'access_time', 'type' => 'select', 'title' => 'Preferred Access Time', 'required' => true, 'help' => '', 'options' => [['value' => 'morning', 'text' => 'Mornings (8-12)'], ['value' => 'afternoon', 'text' => 'Afternoons (12-17)'], ['value' => 'evening', 'text' => 'Evenings (17-20)'], ['value' => 'anytime', 'text' => 'Anytime']]],
                ['id' => 'entry_permission', 'type' => 'checkbox', 'title' => 'I authorize entry to carry out the repair (notice will be given)', 'required' => true, 'help' => '']
            ], '#0284c7'),
        ];
    }

    private function coachingIntake(): array
    {
        return [
            'name' => 'Coaching Intake Form Template',
            'slug' => 'coaching-intake-form-template',
            'short_description' => 'A coaching intake form template capturing client goals, context, and session preferences before the first session.',
            'description' => '<p>Our Coaching Intake Form Template gathers the context a coach needs before session one: goals, current situation, past coaching experience, and logistics preferences.</p><h2>Why and when to use a coaching intake form</h2><p>First sessions spent collecting basics waste paid time. An intake form sent after booking means the opening conversation starts at insight level, and goal patterns across clients become visible over time.</p><h2>Who is this template for</h2><p>Career coaches, executive and leadership coaches, life coaches, business mentors, and health coaches.</p><h2>Why SharaForms is the best tool for this form</h2><p>Responses arrive before the session automatically, confidentiality consent is captured explicitly, and every client history stays organized in one dashboard instead of email threads.</p>',
            'types' => ['questionnaire_templates'],
            'industries' => ['services_forms', 'human_resources_forms'],
            'questions' => [
                [
                    'question' => 'When should clients complete the intake form?',
                    'answer' => '<p>Ideally right after booking and at least two days before the first session. That gives you time to prepare questions and lets clients answer reflectively rather than on the spot.</p>',
                ],
                [
                    'question' => 'Is the information confidential?',
                    'answer' => '<p>The template includes an explicit confidentiality acknowledgment, making your professional boundary clear from the start. Submissions live in your secured workspace, visible only to you and anyone you invite.</p>',
                ],
                [
                    'question' => 'Can I use this for group programs?',
                    'answer' => '<p>Yes. Copy the form per participant or add fields for group name and program cohort, keeping individual goals tied to the right program.</p>',
                ],
            ],
            'structure' => $this->structure('Coaching Intake', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => false, 'help' => ''],
                ['id' => 'coaching_area', 'type' => 'select', 'title' => 'Coaching Area', 'required' => true, 'help' => '', 'options' => [['value' => 'career', 'text' => 'Career'], ['value' => 'leadership', 'text' => 'Leadership / Executive'], ['value' => 'business', 'text' => 'Business / Entrepreneurship'], ['value' => 'life', 'text' => 'Life & Relationships'], ['value' => 'health', 'text' => 'Health & Wellbeing']]],
                ['id' => 'current_situation', 'type' => 'text', 'title' => 'Where Are You Right Now?', 'required' => true, 'help' => 'Context on your situation today', 'multi_lines' => true],
                ['id' => 'goals', 'type' => 'text', 'title' => 'What Do You Want to Achieve?', 'required' => true, 'help' => 'Be as specific as you can', 'multi_lines' => true],
                ['id' => 'past_coaching', 'type' => 'select', 'title' => 'Previous Coaching Experience', 'required' => true, 'help' => '', 'options' => [['value' => 'none', 'text' => 'None - first time'], ['value' => 'some', 'text' => 'Some experience'], ['value' => 'extensive', 'text' => 'Extensive experience']]],
                ['id' => 'session_format', 'type' => 'select', 'title' => 'Preferred Session Format', 'required' => true, 'help' => '', 'options' => [['value' => 'virtual', 'text' => 'Virtual (video call)'], ['value' => 'in_person', 'text' => 'In person'], ['value' => 'hybrid', 'text' => 'Mix of both']]],
                ['id' => 'availability', 'type' => 'text', 'title' => 'Your Availability', 'required' => false, 'help' => 'Days and times that usually work', 'multi_lines' => true],
                ['id' => 'confidentiality_consent', 'type' => 'checkbox', 'title' => 'I agree to the confidentiality terms of this coaching engagement', 'required' => true, 'help' => '']
            ], '#9333ea'),
        ];
    }

    private function vetNewClient(): array
    {
        return [
            'name' => 'Veterinary New Client Registration Form Template',
            'slug' => 'veterinary-new-client-form-template',
            'short_description' => 'A veterinary new client form template registering pets and owners with medical history and vaccination records.',
            'description' => '<p>Our Veterinary New Client Form Template registers new patients properly: owner contact details, pet profile, medical history, vaccination records, and treatment authorization.</p><h2>Why and when to use a veterinary new client form</h2><p>Front-desk clipboard registration slows appointments and loses handwriting. A digital registration arrives before the visit, so reception confirms insurance and history instead of typing it while the pet waits.</p><h2>Who is this template for</h2><p>Veterinary clinics, mobile vets, animal hospitals, and rescue organizations processing new intakes.</p><h2>Why SharaForms is the best tool for this form</h2><p>Vaccination records upload as files, species-specific profiles keep histories organized per animal, and authorization checkboxes create documented consent for treatment decisions.</p>',
            'types' => ['registration_forms'],
            'industries' => ['veterinary_service_forms'],
            'questions' => [
                [
                    'question' => 'Can I register multiple pets on one form?',
                    'answer' => '<p>This template registers one pet per submission so each animal builds its own clean medical profile. For multi-pet households, submit the form once per pet; owner details repeat but histories stay separate.</p>',
                ],
                [
                    'question' => 'What records should be uploaded?',
                    'answer' => '<p>Previous vaccination certificates, recent lab results, and current medication lists. Photos or PDFs both work and give the treating veterinarian full context before the first exam.</p>',
                ],
                [
                    'question' => 'Does the authorization cover emergency treatment?',
                    'answer' => '<p>The consent checkbox documents general authorization to treat. Clinics typically still call for approval on major procedures unless the owner opts into an emergency-treatment clause; adjust the wording with your practice policy.</p>',
                ],
            ],
            'structure' => $this->structure('New Client & Pet Registration', [
                ['id' => 'owner_name', 'type' => 'text', 'title' => 'Owner Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'address', 'type' => 'text', 'title' => 'Home Address', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'pet_name', 'type' => 'text', 'title' => 'Pet Name', 'required' => true, 'help' => ''],
                ['id' => 'species', 'type' => 'select', 'title' => 'Species', 'required' => true, 'help' => '', 'options' => [['value' => 'dog', 'text' => 'Dog'], ['value' => 'cat', 'text' => 'Cat'], ['value' => 'bird', 'text' => 'Bird'], ['value' => 'rabbit', 'text' => 'Rabbit'], ['value' => 'exotic', 'text' => 'Exotic / Other']]],
                ['id' => 'breed', 'type' => 'text', 'title' => 'Breed', 'required' => false, 'help' => ''],
                ['id' => 'birth_year', 'type' => 'number', 'title' => 'Approximate Age (years)', 'required' => false, 'help' => ''],
                ['id' => 'gender', 'type' => 'select', 'title' => 'Sex', 'required' => true, 'help' => '', 'options' => [['value' => 'male_intact', 'text' => 'Male (intact)'], ['value' => 'male_neutered', 'text' => 'Male (neutered)'], ['value' => 'female_intact', 'text' => 'Female (intact)'], ['value' => 'female_spayed', 'text' => 'Female (spayed)']]],
                ['id' => 'medical_history', 'type' => 'text', 'title' => 'Medical History & Current Medications', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'vaccination_records', 'type' => 'files', 'title' => 'Vaccination Records Upload', 'required' => false, 'help' => 'PDF or photos', 'max_number_of_files' => 5, 'max_file_size' => 10],
                ['id' => 'treatment_authorization', 'type' => 'checkbox', 'title' => 'I authorize the clinic to provide necessary treatment', 'required' => true, 'help' => '']
            ], '#65a30d'),
        ];
    }

    private function npsSurvey(): array
    {
        return [
            'name' => 'NPS Survey Template',
            'slug' => 'nps-survey-template',
            'short_description' => 'An NPS survey template measuring customer loyalty with the standard 0-10 recommend score and follow-up questions.',
            'description' => '<p>Our NPS Survey Template implements the classic Net Promoter Score question with the 0-10 scale, plus follow-up prompts that turn scores into actionable feedback.</p><h2>Why and when to use an NPS survey</h2><p>NPS is the fastest loyalty signal there is: one question customers always answer, one number leadership tracks quarterly. Send it after meaningful milestones such as onboarding completion, renewal, or support resolution.</p><h2>Who is this template for</h2><p>Customer success teams, product managers, founders tracking retention signals, and agencies reporting client satisfaction.</p><h2>Why SharaForms is the best tool for this form</h2><p>The 0-10 scale renders perfectly on mobile, scores export to CSV for trend analysis, and conditional logic can show different follow-ups for promoters versus detractors.</p>',
            'types' => ['survey_templates', 'feedback_forms'],
            'industries' => ['customer_service_forms', 'business_forms'],
            'questions' => [
                [
                    'question' => 'How is NPS calculated?',
                    'answer' => '<p>Respondents answering 9 or 10 count as promoters, 7 or 8 as passives, and 0 through 6 as detractors. NPS equals the percentage of promoters minus the percentage of detractors, producing a score between -100 and +100.</p>',
                ],
                [
                    'question' => 'When is the best time to send an NPS survey?',
                    'answer' => '<p>After a meaningful experience moment: 30 days post-onboarding, after a renewal, or following a resolved support ticket. Avoid batching everyone at once; tie sends to experience moments for honest scores.</p>',
                ],
                [
                    'question' => 'Should respondents be anonymous?',
                    'answer' => '<p>Making email optional is the sweet spot: anonymity increases honesty, but an attached email lets you follow up personally with detractors, which often saves the relationship.</p>',
                ],
            ],
            'structure' => $this->structure('Net Promoter Score Survey', [
                ['id' => 'nps_score', 'type' => 'scale', 'title' => 'How likely are you to recommend us to a friend or colleague?', 'required' => true, 'help' => '0 = not at all likely, 10 = extremely likely', 'scale_min_value' => 0, 'scale_max_value' => 10, 'scale_step_value' => 1],
                ['id' => 'score_reason', 'type' => 'text', 'title' => "What is the main reason for your score?", 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'improvement', 'type' => 'text', 'title' => 'Sorry to hear it - what could we do to earn a higher score?', 'required' => false, 'help' => '',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('nps_score', 'scale', 'less_than', 7)])],
                ['id' => 'highlight', 'type' => 'text', 'title' => 'Wonderful! What did we do especially well?', 'required' => false, 'help' => 'We may feature your answer as a testimonial',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('nps_score', 'scale', 'greater_than', 8)])],
                ['id' => 'email', 'type' => 'email', 'title' => 'Your Email (optional)', 'required' => false, 'help' => 'Only if you would like a personal follow-up'],
                ['id' => 'follow_up_ok', 'type' => 'checkbox', 'title' => 'You may contact me about my feedback', 'required' => false, 'help' => '']
            ], '#2563eb'),
        ];
    }

    private function testimonialForm(): array
    {
        return [
            'name' => 'Testimonial Submission Form Template',
            'slug' => 'testimonial-form-template',
            'short_description' => 'A testimonial submission form template collecting customer quotes, ratings, photos, and publishing permission.',
            'description' => '<p>Our Testimonial Submission Form Template makes collecting social proof painless: customer quotes, star ratings, headshots, and explicit publishing rights in one flow.</p><h2>Why and when to use a testimonial form</h2><p>Testimonials requested over email die in draft folders. A friendly form with a rating widget and clear publishing permission lowers friction, and structured submissions drop straight into your marketing pipeline ready to publish.</p><h2>Who is this template for</h2><p>SaaS companies, agencies, course creators, service businesses, and event organizers gathering post-experience proof.</p><h2>Why SharaForms is the best tool for this form</h2><p>Ratings quantify sentiment alongside words, headshot uploads make published quotes look credible, and the permission checkbox keeps your legal basis documented.</p>',
            'types' => ['content_forms', 'feedback_forms'],
            'industries' => ['marketing_forms', 'customer_service_forms'],
            'questions' => [
                [
                    'question' => 'What makes a testimonial persuasive?',
                    'answer' => '<p>Specific outcomes beat generic praise: "we cut onboarding time in half" converts better than "great tool". The prompt text nudges customers toward concrete results and numbers.</p>',
                ],
                [
                    'question' => 'Can customers submit anonymously?',
                    'answer' => '<p>The display name preference field lets people choose how they appear, from full name down to initials. Fully anonymous testimonials convert poorly, so we recommend requiring at least a first name and role.</p>',
                ],
                [
                    'question' => 'Do I have permission to publish what I receive?',
                    'answer' => '<p>The required publishing-permission checkbox documents explicit consent at submission time, which is exactly the record you want if usage is ever questioned.</p>',
                ],
            ],
            'structure' => $this->structure('Share Your Experience', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Your Name', 'required' => true, 'help' => ''],
                ['id' => 'company_role', 'type' => 'text', 'title' => 'Company & Role', 'required' => true, 'help' => 'e.g. Head of Ops at Acme'],
                ['id' => 'testimonial', 'type' => 'text', 'title' => 'Your Testimonial', 'required' => true, 'help' => 'What result did we help you achieve?', 'multi_lines' => true],
                ['id' => 'rating', 'type' => 'rating', 'title' => 'Overall Rating', 'required' => true, 'help' => '', 'rating_max_value' => 5],
                ['id' => 'display_preference', 'type' => 'select', 'title' => 'How Should We Show Your Name?', 'required' => true, 'help' => '', 'options' => [['value' => 'full', 'text' => 'Full name + company'], ['value' => 'first_name', 'text' => 'First name + role only'], ['value' => 'initials', 'text' => 'Initials only']]],
                ['id' => 'headshot', 'type' => 'files', 'title' => 'Headshot Photo', 'required' => false, 'help' => 'Optional - makes published quotes look great', 'max_number_of_files' => 1, 'max_file_size' => 5],
                ['id' => 'video_link', 'type' => 'url', 'title' => 'Video Testimonial Link', 'required' => false, 'help' => 'YouTube, Vimeo, or Loom'],
                ['id' => 'publish_permission', 'type' => 'checkbox', 'title' => 'I grant permission to publish my testimonial and rating', 'required' => true, 'help' => '']
            ], '#be185d'),
        ];
    }

    private function gymMembership(): array
    {
        return [
            'name' => 'Gym Membership Form Template',
            'slug' => 'gym-membership-form-template',
            'short_description' => 'A gym membership form template handling sign-ups, plan selection, health declarations, and waiver acceptance.',
            'description' => '<p>Our Gym Membership Form Template covers the full sign-up flow for fitness businesses: member details, plan selection, fitness goals, emergency contacts, and waiver acceptance.</p><h2>Why and when to use a gym membership form</h2><p>Sign-ups happen at the front desk, on the sidewalk QR code, and from your website at midnight. One digital form handles all three consistently, captures the health declaration gyms need for safe programming, and gets waiver acceptance documented before the first workout.</p><h2>Who is this template for</h2><p>Gyms, CrossFit boxes, yoga and pilates studios, martial arts schools, and personal training studios.</p><h2>Why SharaForms is the best tool for this form</h2><p>Plan selection feeds your billing process, goal multiselects inform program recommendations, and pairing with our liability waiver template gives you complete risk documentation from day one.</p>',
            'types' => ['membership_forms', 'registration_forms'],
            'industries' => ['sports_forms'],
            'questions' => [
                [
                    'question' => 'Which membership plans should I offer?',
                    'answer' => '<p>Most gyms offer monthly, annual, student, and family tiers plus a trial pass. Adjust the plan options to match your pricing; annual options reduce churn while trials lower the barrier to first visits.</p>',
                ],
                [
                    'question' => 'Do I still need a separate liability waiver?',
                    'answer' => '<p>Yes, ideally. The acceptance checkbox here acknowledges your terms, while our dedicated liability waiver template captures risk acknowledgment and signature for the activities themselves. Use both for complete coverage.</p>',
                ],
                [
                    'question' => 'Why collect health conditions?',
                    'answer' => '<p>Trainers need contraindications before programming sessions, and the declaration protects your facility by documenting disclosure. Responses stay confidential within your workspace.</p>',
                ],
            ],
            'structure' => $this->structure('Gym Membership Sign-up', [
                ['id' => 'member_name', 'type' => 'text', 'title' => 'Member Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'phone', 'type' => 'phone_number', 'title' => 'Phone Number', 'required' => true, 'help' => ''],
                ['id' => 'plan', 'type' => 'select', 'title' => 'Membership Plan', 'required' => true, 'help' => '', 'options' => [['value' => 'monthly', 'text' => 'Monthly - $45'], ['value' => 'annual', 'text' => 'Annual (save 20%) - $450'], ['value' => 'student', 'text' => 'Student - $30'], ['value' => 'family', 'text' => 'Family - $80'], ['value' => 'trial', 'text' => '7-Day Trial - Free']]],
                ['id' => 'start_date', 'type' => 'date', 'title' => 'Preferred Start Date', 'required' => true, 'help' => ''],
                ['id' => 'pt_sessions', 'type' => 'number', 'title' => 'Personal Training Sessions per Month (optional)', 'required' => false, 'help' => '$60 each, billed monthly'],
                ['id' => 'fitness_goals', 'type' => 'multi_select', 'title' => 'Fitness Goals', 'required' => true, 'help' => '', 'options' => [['value' => 'strength', 'text' => 'Strength'], ['value' => 'cardio', 'text' => 'Cardio / Endurance'], ['value' => 'flexibility', 'text' => 'Flexibility / Mobility'], ['value' => 'weight_loss', 'text' => 'Weight Management'], ['value' => 'rehab', 'text' => 'Rehabilitation'], ['value' => 'general', 'text' => 'General Fitness']]],
                ['id' => 'emergency_contact', 'type' => 'phone_number', 'title' => 'Emergency Contact Number', 'required' => true, 'help' => ''],
                ['id' => 'health_conditions', 'type' => 'text', 'title' => 'Injuries or Health Conditions', 'required' => false, 'help' => 'Heart conditions, joint injuries, medication considerations', 'multi_lines' => true],
                ['id' => 'waiver_acceptance', 'type' => 'checkbox', 'title' => 'I accept the membership terms and gym rules', 'required' => true, 'help' => ''],
                $this->totalBlock('monthly_cost_display', 'cv_monthly_cost', 'Your Monthly Cost', '$0'),
            ], '#047857', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_monthly_cost',
                        'Monthly Cost',
                        'IF({plan}="Monthly - $45",45,IF({plan}="Annual (save 20%) - $450",37.5,IF({plan}="Student - $30",30,IF({plan}="Family - $80",80,0))))'
                        . '+IFBLANK({pt_sessions},0)*60'
                    ),
                ],
            ]),
        ];
    }

    private function fieldTripPermissionSlip(): array
    {
        return [
            'name' => 'Field Trip Permission Slip Template',
            'slug' => 'field-trip-permission-slip-form-template',
            'short_description' => 'A field trip permission slip template for teachers collecting parent consent, emergency contacts, and lunch plans online.',
            'description' => '<p>Our Field Trip Permission Slip Template replaces paper slips that get crumpled in backpacks with a digital form parents can complete in two minutes.</p><h2>Why and when to use a permission slip form</h2><p>Every off-campus trip needs documented parental consent, emergency contacts, and medical notes. Collecting them digitally means no lost slips, a clear audit trail of who has responded, and instant chaperone sign-ups from the same form.</p><h2>Who is this template for</h2><p>Classroom teachers, school administrators, homeschool co-ops, youth group leaders, and camp counselors planning any supervised outing.</p><h2>Why SharaForms is the best tool for this form</h2><p>Responses land in one exportable list, so you always know exactly which students still need consent. Conditional fields keep dietary and chaperone questions out of the way until they matter, and confirmation emails give parents a record of what they signed.</p>',
            'types' => ['consent_forms', 'registration_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Field Trip Permission Slip', [
                $this->nfText('intro', '<h2>Field Trip Permission Slip</h2><p>Please complete this form so your child can join our upcoming trip. Every response is time-stamped for the school records.</p>'),
                $this->textField('student_name', 'Student Full Name', true),
                $this->selectField('grade_level', 'Grade Level', [
                    ['value' => 'kinder', 'text' => 'Kindergarten'],
                    ['value' => 'g1', 'text' => '1st Grade'],
                    ['value' => 'g2', 'text' => '2nd Grade'],
                    ['value' => 'g3', 'text' => '3rd Grade'],
                    ['value' => 'g4', 'text' => '4th Grade'],
                    ['value' => 'g5', 'text' => '5th Grade'],
                ], true),
                $this->textField('destination', 'Trip Destination', true),
                $this->dateField('trip_date', 'Trip Date', true),
                $this->selectField('lunch_plan', 'Lunch Plan', [
                    ['value' => 'buying', 'text' => 'Buying lunch at the venue'],
                    ['value' => 'packed', 'text' => 'Bringing a packed lunch'],
                ], true),
                $this->textField('dietary_needs', 'Allergies or Dietary Needs', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('lunch_plan', 'select', 'equals', 'Buying lunch at the venue')], 'and', true),
                ]),
                $this->checkboxField('chaperone_interest', 'I would like to volunteer as a chaperone'),
                $this->phoneField('chaperone_phone', 'Chaperone Contact Number', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('chaperone_interest', 'checkbox', 'is_checked')], 'and', true),
                ]),
                $this->textField('emergency_contact_name', 'Emergency Contact Name', true),
                $this->phoneField('emergency_contact_phone', 'Emergency Contact Phone', true),
                $this->textareaField('medical_notes', 'Medical Notes (medications, allergies, conditions)'),
                $this->checkboxField('consent', 'I give permission for my child to attend this trip and confirm the information above is accurate', true),
            ], '#d97706'),
        ];
    }

    private function therapyIntake(): array
    {
        return [
            'name' => 'Therapy Intake Form Template',
            'slug' => 'therapy-intake-form-template',
            'short_description' => 'A therapy intake form template for counselors and therapists collecting history, medications, and session preferences securely.',
            'description' => '<p>Our Therapy Intake Form Template helps private practices collect client background before the first session, so the opening hour focuses on the client instead of paperwork.</p><h2>Why and when to use a therapy intake form</h2><p>A structured intake documents presenting concerns, previous care, current medications, and contact preferences ahead of session one. Sending the form when an appointment is booked means clients reflect privately and arrive ready to talk.</p><h2>Who is this template for</h2><p>Licensed therapists, counselors, psychologists, social workers, and group practices onboarding new adult clients.</p><h2>Why SharaForms is the best tool for this form</h2><p>Submissions are stored securely within your workspace, conditional fields adapt to each client\'s history, and you control exactly which fields are required. Export intake summaries to CSV for your records system.</p>',
            'types' => ['application_forms', 'registration_forms'],
            'industries' => ['healthcare_forms'],
            'structure' => $this->structure('New Client Intake', [
                $this->nfText('intro', '<h2>Welcome. Let\'s Get Started</h2><p>This confidential intake takes about ten minutes. Your answers help your therapist prepare for your first session.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->dateField('date_of_birth', 'Date of Birth', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('has_previous_therapy', 'Have you been in therapy before?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                ['id' => 'previous_therapy_details', 'type' => 'text', 'name' => 'What did you work on previously, and what felt helpful or unhelpful?', 'required' => false, 'help' => '', 'multi_lines' => true,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('has_previous_therapy', 'select', 'equals', 'Yes')])],
                $this->selectField('takes_medication', 'Do you currently take any medications?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ]),
                ['id' => 'medication_list', 'type' => 'text', 'name' => 'Please list current medications and dosages', 'required' => false, 'help' => '', 'multi_lines' => true,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('takes_medication', 'select', 'equals', 'Yes')], 'and', true)],
                $this->textareaField('primary_concerns', 'What brings you to therapy at this time?', true),
                $this->selectField('preferred_contact', 'Preferred contact method', [
                    ['value' => 'email', 'text' => 'Email'],
                    ['value' => 'phone_call', 'text' => 'Phone call'],
                    ['value' => 'text_message', 'text' => 'Text message'],
                ], true),
                $this->textField('preferred_times', 'Best days and times to reach you'),
                $this->checkboxField('consent', 'I consent to treatment and understand that information shared in sessions is kept confidential within legal and ethical limits', true),
            ], '#0d9488'),
        ];
    }

    private function raceRegistration(): array
    {
        return [
            'name' => 'Race Registration Form Template',
            'slug' => 'race-registration-form-template',
            'short_description' => 'A race registration form template for 5Ks, 10Ks, and fun runs with category fees, shirt add-ons, and live totals.',
            'description' => '<p>Our Race Registration Form Template handles sign-ups for fun runs, charity 5Ks, and timed races with automatic fee totals per category.</p><h2>Why and when to use a race registration form</h2><p>Race directors juggle categories, shirt orders, and waiver signatures while entry counts climb. A single form captures all of it per runner, shows the exact amount due before submission, and exports a clean bib-assignment list.</p><h2>Who is this template for</h2><p>Race directors, running clubs, school athletics programs, charity event committees, and sponsors hosting corporate fun runs.</p><h2>Why SharaForms is the best tool for this form</h2><p>The live total updates as runners pick categories and shirts, the waiver checkbox documents every entry, and submissions export straight into your timing spreadsheet. Pair it with a QR code on your flyers for instant mobile sign-ups.</p>',
            'types' => ['event_registration_forms', 'registration_forms'],
            'industries' => ['sports_forms'],
            'structure' => $this->structure('Race Registration', [
                $this->nfText('intro', '<h2>Register for Race Day</h2><p>Pick your distance, add your shirt, and lock in your spot. Bib pickup opens at 7 AM on race day.</p>'),
                $this->textField('participant_name', 'Participant Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('race_category', 'Race Category', [
                    ['value' => '5k', 'text' => '5K Run - $35'],
                    ['value' => '10k', 'text' => '10K Run - $45'],
                    ['value' => 'half', 'text' => 'Half Marathon - $65'],
                    ['value' => 'fun_run', 'text' => 'Kids Fun Run - $10'],
                ], true),
                $this->selectField('tshirt_addon', 'Race Shirt', [
                    ['value' => 'none', 'text' => 'No shirt'],
                    ['value' => 'add', 'text' => 'Add race shirt (+$15)'],
                ], true),
                $this->selectField('shirt_size', 'Shirt Size', [
                    ['value' => 's', 'text' => 'S'],
                    ['value' => 'm', 'text' => 'M'],
                    ['value' => 'l', 'text' => 'L'],
                    ['value' => 'xl', 'text' => 'XL'],
                    ['value' => 'xxl', 'text' => 'XXL'],
                ], false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('tshirt_addon', 'select', 'equals', 'Add race shirt (+$15)')], 'and', true),
                ]),
                $this->textField('emergency_contact_name', 'Emergency Contact Name', true),
                $this->phoneField('emergency_contact_phone', 'Emergency Contact Phone', true),
                $this->checkboxField('waiver', 'I have read the race waiver and assume all risks associated with participation', true),
                $this->totalBlock('race_total_display', 'cv_race_total', 'Registration Total', '$0'),
            ], '#dc2626', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_race_total',
                        'Registration Total',
                        'IF({race_category}="5K Run - $35",35,IF({race_category}="10K Run - $45",45,IF({race_category}="Half Marathon - $65",65,10)))'
                            . '+IF({tshirt_addon}="Add race shirt (+$15)",15,0)'
                    ),
                ],
            ]),
        ];
    }

    private function golfTournamentRegistration(): array
    {
        return [
            'name' => 'Golf Tournament Registration Form Template',
            'slug' => 'golf-tournament-registration-form-template',
            'short_description' => 'A golf tournament registration form template for outings and fundraisers with player counts, sponsorships, and dinner tickets.',
            'description' => '<p>Our Golf Tournament Registration Form Template covers charity outings, corporate scrambles, and club tournaments: players, dinner guests, hole sponsorships, and a live total.</p><h2>Why and when to use a golf tournament form</h2><p>Golf outings combine player registrations, meal counts, and sponsorship sales in one event. Collecting all three on one form gives your committee a single revenue picture and a clean catering headcount weeks before tee-off.</p><h2>Who is this template for</h2><p>Charity foundations, chambers of commerce, alumni associations, country clubs, and companies hosting client appreciation outings.</p><h2>Why SharaForms is the best tool for this form</h2><p>The total updates live as teams grow and sponsorships get added, dinner counts feed directly to the clubhouse kitchen, and exportable lists make cart assignments painless.</p>',
            'types' => ['event_registration_forms'],
            'industries' => ['sports_forms', 'charity_forms'],
            'structure' => $this->structure('Golf Tournament Sign-up', [
                $this->nfText('intro', '<h2>Tee Off for a Great Cause</h2><p>$125 per player includes greens fees, cart, and post-round dinner. Foursomes encouraged!</p>'),
                $this->textField('contact_name', 'Contact Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->numberField('players', 'Number of Players ($125 each)', true, ['help' => 'Book a full foursome and save your preferred tee time']),
                $this->numberField('dinner_guests', 'Additional Dinner Guests ($30 each)', false, ['help' => 'Beyond the included player dinners']),
                $this->textareaField('dietary_needs', 'Dietary Restrictions for Dinner', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('dinner_guests', 'number', 'greater_than', 0)]),
                ]),
                $this->selectField('hole_sponsorship', 'Hole Sponsorship', [
                    ['value' => 'none', 'text' => 'No sponsorship'],
                    ['value' => 'add', 'text' => 'Add Hole Sponsorship (+$250)'],
                ], true),
                $this->textField('sponsor_company', 'Company Name for Sponsorship Signage', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('hole_sponsorship', 'select', 'equals', 'Add Hole Sponsorship (+$250)')], 'and', true),
                ]),
                $this->numberField('team_handicaps', 'Combined Team Handicap (optional)', false),
                $this->checkboxField('terms', 'I agree to the tournament rules and dress code', true),
                $this->totalBlock('golf_total_display', 'cv_golf_total', 'Entry Total', '$0'),
            ], '#15803d', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_golf_total',
                        'Entry Total',
                        'IFBLANK({players},0)*125+IFBLANK({dinner_guests},0)*30'
                            . '+IF({hole_sponsorship}="Add Hole Sponsorship (+$250)",250,0)'
                    ),
                ],
            ]),
        ];
    }

    private function vendorApplication(): array
    {
        return [
            'name' => 'Vendor Application Form Template',
            'slug' => 'vendor-application-form-template',
            'short_description' => 'A vendor application form template for markets, craft fairs, and expos with booth selection, fees, and permit uploads.',
            'description' => '<p>Our Vendor Application Form Template helps market organizers collect vendor details, booth preferences, and fees with permits attached before acceptance letters go out.</p><h2>Why and when to use a vendor application form</h2><p>Markets, craft fairs, and expos need consistent vendor records: what they sell, how much space they need, whether they draw power or serve food. A structured application replaces email threads and makes booth mapping a ten-minute job instead of a weekend.</p><h2>Who is this template for</h2><p>Farmers market managers, craft fair committees, expo organizers, flea market operators, and school fundraising bazaars.</p><h2>Why SharaForms is the best tool for this form</h2><p>The live booth fee updates as vendors pick sizes and power, food vendors are prompted for health permits automatically, and accepted vendors can be exported straight into your layout spreadsheet.</p>',
            'types' => ['application_forms'],
            'industries' => ['business_forms', 'ecommerce_forms'],
            'structure' => $this->structure('Vendor Application', [
                $this->nfText('intro', '<h2>Become a Market Vendor</h2><p>Complete the application below. Our team reviews submissions every Friday and replies within five business days.</p>'),
                $this->textField('business_name', 'Business Name', true),
                $this->textField('contact_name', 'Contact Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('product_category', 'Product Category', [
                    ['value' => 'handmade', 'text' => 'Handmade goods'],
                    ['value' => 'food_bev', 'text' => 'Food & beverage'],
                    ['value' => 'art_prints', 'text' => 'Art & prints'],
                    ['value' => 'clothing', 'text' => 'Clothing & accessories'],
                    ['value' => 'services', 'text' => 'Services'],
                    ['value' => 'other', 'text' => 'Other'],
                ], true),
                $this->selectField('booth_size', 'Booth Size', [
                    ['value' => 'standard', 'text' => '10x10 Standard - $60'],
                    ['value' => 'corner', 'text' => '10x10 Corner - $85'],
                    ['value' => 'double', 'text' => '10x20 Double - $110'],
                ], true),
                $this->selectField('electricity', 'Electricity Hookup', [
                    ['value' => 'none', 'text' => 'No electricity needed'],
                    ['value' => 'add', 'text' => 'Add electricity (+$25)'],
                ], true),
                $this->textField('power_needs', 'Describe Your Power Needs (wattage, equipment)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('electricity', 'select', 'equals', 'Add electricity (+$25)')], 'and', true),
                ]),
                $this->selectField('food_vendor', 'Will you sell or sample food?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                ['id' => 'health_permit', 'type' => 'files', 'title' => 'Health Permit Upload', 'required' => false, 'help' => 'Required for all food vendors before acceptance', 'max_file_size' => 10, 'max_number_of_files' => 2,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('food_vendor', 'select', 'equals', 'Yes')], 'and', true)],
                $this->textareaField('business_description', 'Describe Your Products', true),
                $this->checkboxField('agreement', 'I agree to the market rules, insurance requirements, and refund policy', true),
                $this->totalBlock('booth_total_display', 'cv_booth_total', 'Booth Total', '$0'),
            ], '#b45309', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_booth_total',
                        'Booth Total',
                        'IF({booth_size}="10x10 Standard - $60",60,IF({booth_size}="10x10 Corner - $85",85,110))'
                            . '+IF({electricity}="Add electricity (+$25)",25,0)'
                    ),
                ],
            ]),
        ];
    }

    private function workOrder(): array
    {
        return [
            'name' => 'Work Order Form Template',
            'slug' => 'work-order-form-template',
            'short_description' => 'A work order form template for maintenance teams capturing job details, priority levels, and cost estimates.',
            'description' => '<p>Our Work Order Form Template standardizes how maintenance requests become dispatchable jobs with priorities, labor estimates, and parts costs in one record.</p><h2>Why and when to use a work order form</h2><p>Facilities and property teams turn reported issues into scheduled work. A proper work order captures the location, category, urgency, and estimated cost up front so dispatch decisions take seconds and monthly cost reporting writes itself.</p><h2>Who is this template for</h2><p>Property managers, facilities teams, maintenance departments, contractors, and internal service desks.</p><h2>Why SharaForms is the best tool for this form</h2><p>Emergency jobs surface an after-hours contact automatically, the estimate block computes labor plus parts live, and completed orders export to CSV for invoice reconciliation.</p>',
            'types' => ['request_forms'],
            'industries' => ['services_forms', 'business_forms'],
            'structure' => $this->structure('Maintenance Work Order', [
                $this->nfText('intro', '<h2>Submit a Work Order</h2><p>Fill in the details below and our dispatch team assigns a technician within one business day.</p>'),
                $this->textField('requested_by', 'Requested By', true),
                $this->emailField('email'),
                $this->textField('site_location', 'Site / Unit Location', true),
                $this->selectField('issue_category', 'Issue Category', [
                    ['value' => 'plumbing', 'text' => 'Plumbing'],
                    ['value' => 'electrical', 'text' => 'Electrical'],
                    ['value' => 'hvac', 'text' => 'HVAC'],
                    ['value' => 'carpentry', 'text' => 'Carpentry'],
                    ['value' => 'painting', 'text' => 'Painting'],
                    ['value' => 'appliance', 'text' => 'Appliance'],
                    ['value' => 'other', 'text' => 'Other'],
                ], true),
                $this->selectField('priority', 'Priority Level', [
                    ['value' => 'low', 'text' => 'Low'],
                    ['value' => 'normal', 'text' => 'Normal'],
                    ['value' => 'high', 'text' => 'High'],
                    ['value' => 'emergency', 'text' => 'Emergency'],
                ], true),
                $this->phoneField('after_hours_contact', 'After-Hours Contact Number', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('priority', 'select', 'equals', 'Emergency')], 'and', true),
                ]),
                $this->textareaField('description', 'Describe the Problem', true),
                ['id' => 'site_photos', 'type' => 'files', 'title' => 'Photos of the Issue', 'required' => false, 'help' => 'Helps technicians arrive prepared', 'max_file_size' => 10, 'max_number_of_files' => 3],
                $this->numberField('est_hours', 'Estimated Labor Hours ($85/hr)', true),
                $this->numberField('parts_cost', 'Estimated Parts Cost ($)', false),
                $this->totalBlock('estimate_display', 'cv_work_estimate', 'Estimated Cost', '$0'),
            ], '#2563eb', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_work_estimate',
                        'Estimated Cost',
                        '{est_hours}*85+IFBLANK({parts_cost},0)'
                    ),
                ],
            ]),
        ];
    }

    private function warrantyClaim(): array
    {
        return [
            'name' => 'Warranty Claim Form Template',
            'slug' => 'warranty-claim-form-template',
            'short_description' => 'A warranty claim form template for manufacturers and retailers collecting product issues, proof of purchase, and photos.',
            'description' => '<p>Our Warranty Claim Form Template collects everything a service team needs to validate coverage: product identifiers, purchase channel details, issue descriptions, and photo evidence.</p><h2>Why and when to use a warranty claim form</h2><p>Warranty claims stall when key facts arrive piecemeal: missing serial numbers, no proof of purchase, blurry photos. One structured claim form gets complete cases into your queue the first time, cutting resolution time and repeat emails.</p><h2>Who is this template for</h2><p>Product manufacturers, appliance brands, electronics retailers, furniture makers, and authorized service centers.</p><h2>Why SharaForms is the best tool for this form</h2><p>Purchase-channel logic asks for order numbers or store locations only when relevant, photo uploads attach directly to each claim, and every case exports with its full evidence trail.</p>',
            'types' => ['request_forms'],
            'industries' => ['ecommerce_forms', 'business_forms'],
            'structure' => $this->structure('Warranty Claim', [
                $this->nfText('intro', '<h2>File a Warranty Claim</h2><p>Tell us what happened and we will get you back to full working order. Claims are reviewed within two business days.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number'),
                $this->textField('product_name', 'Product Name', true),
                $this->textField('model_serial', 'Model / Serial Number', true),
                $this->dateField('purchase_date', 'Purchase Date', true),
                $this->selectField('purchased_from', 'Where did you purchase it?', [
                    ['value' => 'online', 'text' => 'Online store'],
                    ['value' => 'retail', 'text' => 'Physical retail location'],
                    ['value' => 'dealer', 'text' => 'Authorized dealer'],
                ], true),
                $this->textField('order_number', 'Order Number', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('purchased_from', 'select', 'equals', 'Online store')], 'and', true),
                ]),
                $this->textField('store_location', 'Store Location / Dealer Name', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('purchased_from', 'select', 'does_not_equal', 'Online store')], 'and', true),
                ]),
                $this->textareaField('issue_description', 'Describe the Issue', true),
                $this->selectField('resolution_wanted', 'Preferred Resolution', [
                    ['value' => 'repair', 'text' => 'Repair'],
                    ['value' => 'replacement', 'text' => 'Replacement'],
                    ['value' => 'refund', 'text' => 'Refund'],
                ]),
                ['id' => 'claim_photos', 'type' => 'files', 'title' => 'Photos of the Issue and Serial Plate', 'required' => true, 'help' => 'Clear photos speed up validation', 'max_file_size' => 10, 'max_number_of_files' => 4],
            ], '#7c3aed'),
        ];
    }

    private function returnExchange(): array
    {
        return [
            'name' => 'Return & Exchange Form Template',
            'slug' => 'return-exchange-form-template',
            'short_description' => 'A return and exchange form template for online stores handling refunds, replacements, and damage claims cleanly.',
            'description' => '<p>Our Return & Exchange Form Template gives customers a self-serve way to start refunds or swaps while capturing the reason codes your operations team needs.</p><h2>Why and when to use a return form</h2><p>Returns handled over email create back-and-forth and inconsistent data. A structured form collects the order number, reason, and condition up front; exchanges surface replacement fields automatically, and damaged items prompt for photo evidence that protects fulfillment reviews.</p><h2>Who is this template for</h2><p>Ecommerce stores, boutiques with physical products, footwear and apparel brands, and subscription box companies.</p><h2>Why SharaForms is the best tool for this form</h2><p>Reason-code reporting shows why products come back, exchange details route straight to picking lists, and photo uploads settle damage disputes quickly and fairly.</p>',
            'types' => ['order_forms', 'request_forms'],
            'industries' => ['ecommerce_forms'],
            'structure' => $this->structure('Return or Exchange Request', [
                $this->nfText('intro', '<h2>Start Your Return or Exchange</h2><p>Returns are accepted within 30 days of delivery. Exchanges ship as soon as your return scans in transit.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->textField('order_number', 'Order Number', true),
                $this->selectField('request_type', 'What would you like?', [
                    ['value' => 'refund', 'text' => 'Refund'],
                    ['value' => 'exchange', 'text' => 'Exchange'],
                ], true),
                $this->textField('desired_replacement', 'Desired Replacement (size, color, item)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('request_type', 'select', 'equals', 'Exchange')], 'and', true),
                ]),
                $this->selectField('return_reason', 'Reason for Return', [
                    ['value' => 'wrong_size', 'text' => 'Wrong size or fit'],
                    ['value' => 'damaged_transit', 'text' => 'Damaged in transit'],
                    ['value' => 'defective', 'text' => 'Defective product'],
                    ['value' => 'not_described', 'text' => 'Not as described'],
                    ['value' => 'changed_mind', 'text' => 'Changed my mind'],
                ], true),
                ['id' => 'damage_photos', 'type' => 'files', 'title' => 'Photos of the Damage or Defect', 'required' => false, 'help' => 'Required for damaged or defective items', 'max_file_size' => 10, 'max_number_of_files' => 3,
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('return_reason', 'select', 'equals', 'Damaged in transit'),
                        $this->logicCondition('return_reason', 'select', 'equals', 'Defective product'),
                    ], 'or', true)],
                $this->textareaField('comments', 'Anything Else We Should Know?'),
                $this->checkboxField('policy_agreement', 'I have read the return policy and understand return shipping terms', true),
            ], '#e11d48'),
        ];
    }

    private function facilityRentalRequest(): array
    {
        return [
            'name' => 'Facility Rental Request Form Template',
            'slug' => 'facility-rental-request-form-template',
            'short_description' => 'A facility rental request form template for halls, gyms, and venues with hourly rates, add-ons, and instant estimates.',
            'description' => '<p>Our Facility Rental Request Form Template handles community halls, church spaces, school gymnasiums, and event pavilions: space choice, hours, setup help, and equipment needs with a live price estimate.</p><h2>Why and when to use a facility rental request form</h2><p>Rental inquiries arrive with half the details needed to quote. A request form captures the space, duration, headcount, and special needs together, so your coordinator confirms availability and sends one complete answer instead of four follow-up emails.</p><h2>Who is this template for</h2><p>Community centers, churches renting halls, schools renting gymnasiums, event venues, and municipal facilities departments.</p><h2>Why SharaForms is the best tool for this form</h2><p>The estimate updates live by space and hours, AV and kitchen needs trigger detail questions automatically, and alcohol-service answers collect insurance uploads before approval.</p>',
            'types' => ['reservation_forms', 'request_forms'],
            'industries' => ['business_forms', 'church_forms', 'education_forms'],
            'structure' => $this->structure('Facility Rental Request', [
                $this->nfText('intro', '<h2>Request a Space</h2><p>Tell us about your event and preferred space. Our coordinator confirms availability within two business days.</p>'),
                $this->textField('contact_name', 'Contact Name', true),
                $this->textField('organization', 'Organization (if applicable)'),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('space', 'Space Requested', [
                    ['value' => 'hall', 'text' => 'Main Hall - $95/hr'],
                    ['value' => 'meeting', 'text' => 'Meeting Room - $45/hr'],
                    ['value' => 'gym', 'text' => 'Gymnasium - $120/hr'],
                    ['value' => 'pavilion', 'text' => 'Outdoor Pavilion - $75/hr'],
                ], true),
                $this->dateField('event_date', 'Event Date', true),
                $this->numberField('hours', 'Total Rental Hours', true),
                $this->numberField('attendees', 'Expected Attendees', true),
                $this->selectField('setup_help', 'Setup Assistance', [
                    ['value' => 'self', 'text' => 'No, we will set up ourselves'],
                    ['value' => 'staff', 'text' => 'Yes, please provide staff (+$50)'],
                ], true),
                $this->selectField('av_equipment', 'Do you need AV equipment?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('av_details', 'Which AV Equipment Do You Need?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('av_equipment', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('kitchen_access', 'Do you need kitchen access?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('kitchen_needs', 'What Do You Need From the Kitchen?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('kitchen_access', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('alcohol_service', 'Will alcohol be served?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                ['id' => 'insurance_upload', 'type' => 'files', 'title' => 'Liability Insurance Certificate', 'required' => false, 'help' => 'Events serving alcohol require proof of coverage', 'max_file_size' => 10, 'max_number_of_files' => 1,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('alcohol_service', 'select', 'equals', 'Yes')], 'and', true)],
                $this->textareaField('event_purpose', 'Describe Your Event', true),
                $this->checkboxField('rules_agreement', 'I agree to the facility rules and cleaning expectations', true),
                $this->totalBlock('rental_estimate_display', 'cv_rental_estimate', 'Rental Estimate', '$0'),
            ], '#0891b2', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_rental_estimate',
                        'Rental Estimate',
                        'IF({space}="Main Hall - $95/hr",95,IF({space}="Meeting Room - $45/hr",45,IF({space}="Gymnasium - $120/hr",120,75)))*{hours}'
                            . '+IF({setup_help}="Yes, please provide staff (+$50)",50,0)'
                    ),
                ],
            ]),
        ];
    }

    private function parkingPermitApplication(): array
    {
        return [
            'name' => 'Parking Permit Application Form Template',
            'slug' => 'parking-permit-application-form-template',
            'short_description' => 'A parking permit application form template for campuses, offices, and apartments with vehicle details and permit fees.',
            'description' => '<p>Our Parking Permit Application Form Template collects applicant details, vehicle information, and lot preferences with the correct fee calculated per permit type.</p><h2>Why and when to use a parking permit form</h2><p>Campuses, office parks, and residential buildings issue permits every semester or lease cycle. A digital application replaces windshield-sticker paperwork with searchable records, catches second vehicles automatically, and totals fees before the office ever opens the submission.</p><h2>Who is this template for</h2><p>University transportation offices, commercial property managers, HOAs, apartment complexes, and municipal parking authorities.</p><h2>Why SharaForms is the best tool for this form</h2><p>Second-vehicle and accessible-placard fields appear only when needed, the fee block computes by permit duration live, and approvals export straight to your gate-system import.</p>',
            'types' => ['application_forms'],
            'industries' => ['business_forms', 'education_forms'],
            'structure' => $this->structure('Parking Permit Application', [
                $this->nfText('intro', '<h2>Parking Permit Application</h2><p>One application per vehicle. Approved permits are available for pickup at the security office within three business days.</p>'),
                $this->textField('applicant_name', 'Applicant Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('permit_type', 'Permit Type', [
                    ['value' => 'student_semester', 'text' => 'Student Semester - $85'],
                    ['value' => 'employee_annual', 'text' => 'Employee Annual - $220'],
                    ['value' => 'monthly_visitor', 'text' => 'Monthly Visitor - $40'],
                    ['value' => 'motorcycle', 'text' => 'Motorcycle Semester - $50'],
                ], true),
                $this->selectField('lot_preference', 'Preferred Lot', [
                    ['value' => 'lot_a', 'text' => 'Lot A'],
                    ['value' => 'lot_b', 'text' => 'Lot B'],
                    ['value' => 'garage', 'text' => 'Parking Garage'],
                    ['value' => 'none', 'text' => 'No preference'],
                ]),
                $this->textField('vehicle_make_model', 'Vehicle Make and Model', true),
                $this->textField('license_plate', 'License Plate Number', true),
                $this->selectField('second_vehicle', 'Registering a second vehicle?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('second_vehicle_details', 'Second Vehicle Details (make, model, plate)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('second_vehicle', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('accessible_placard', 'Do you hold an accessible parking placard?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('placard_number', 'Placard Number', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('accessible_placard', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->checkboxField('terms', 'I confirm the vehicle information is accurate and agree to parking regulations', true),
                $this->totalBlock('permit_fee_display', 'cv_permit_fee', 'Permit Fee', '$0'),
            ], '#475569', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_permit_fee',
                        'Permit Fee',
                        'IF({permit_type}="Student Semester - $85",85,IF({permit_type}="Employee Annual - $220",220,IF({permit_type}="Monthly Visitor - $40",40,50)))'
                    ),
                ],
            ]),
        ];
    }

    private function internshipApplication(): array
    {
        return [
            'name' => 'Internship Application Form Template',
            'slug' => 'internship-application-form-template',
            'short_description' => 'An internship application form template collecting student details, availability, resumes, and credit arrangements.',
            'description' => '<p>Our Internship Application Form Template helps companies run internship intakes that capture academic status, availability, and resumes consistently across every applicant.</p><h2>Why and when to use an internship application form</h2><p>Internship programs juggle students from multiple schools with different credit requirements and availability windows. One structured application keeps candidates comparable, flags credit-bearing placements early, and gets resumes attached to the right opening from the start.</p><h2>Who is this template for</h2><p>HR teams running seasonal internships, startups hiring first interns, university partnership programs, and nonprofits hosting service-learning placements.</p><h2>Why SharaForms is the best tool for this form</h2><p>Credit-seeking applicants are prompted for school documentation automatically, resume uploads stay organized per posting, and exportable shortlists make interview scheduling quick.</p>',
            'types' => ['employment_forms', 'application_forms'],
            'industries' => ['human_resources_forms', 'education_forms'],
            'structure' => $this->structure('Internship Application', [
                $this->nfText('intro', '<h2>Apply for Our Internship Program</h2><p>Tell us about your studies and what you want to learn. Applications close when positions fill.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textField('school', 'School / University', true),
                $this->textField('major', 'Major / Field of Study', true),
                $this->dateField('graduation_date', 'Expected Graduation Date', true),
                $this->selectField('credit_status', 'Is this internship for academic credit?', [
                    ['value' => 'credit', 'text' => 'For academic credit'],
                    ['value' => 'no_credit', 'text' => 'Not for credit'],
                ], true),
                $this->textareaField('credit_details', 'Credit Requirements (hours needed, supervisor contact)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('credit_status', 'select', 'equals', 'For academic credit')], 'and', true),
                ]),
                $this->selectField('position_interest', 'Area of Interest', [
                    ['value' => 'marketing', 'text' => 'Marketing'],
                    ['value' => 'engineering', 'text' => 'Engineering'],
                    ['value' => 'design', 'text' => 'Design'],
                    ['value' => 'operations', 'text' => 'Operations'],
                    ['value' => 'finance', 'text' => 'Finance'],
                ], true),
                $this->numberField('hours_per_week', 'Available Hours Per Week', true),
                $this->dateField('available_start', 'Earliest Start Date', true),
                ['id' => 'resume', 'type' => 'files', 'title' => 'Resume Upload', 'required' => true, 'help' => 'PDF preferred', 'max_file_size' => 10, 'max_number_of_files' => 1],
                $this->textareaField('why_this_role', 'Why are you interested in this internship?', true),
            ], '#4f46e5'),
        ];
    }

    private function studentRegistration(): array
    {
        return [
            'name' => 'Student Registration Form Template',
            'slug' => 'student-registration-form-template',
            'short_description' => 'A student registration form template for schools collecting guardian contacts, grade placement, transport, and health notes.',
            'description' => '<p>Our Student Registration Form Template covers K-12 enrollment: student details, guardian contacts, previous school records, transport needs, and health notes in one clean submission.</p><h2>Why and when to use a student registration form</h2><p>School offices process registration bursts every spring and mid-year as families move. A structured form means complete records the first time: guardians know which documents to bring, transport sees bus-stop demand early, and health notes reach nurses before day one.</p><h2>Who is this template for</h2><p>K-12 public and private schools, charter networks, international schools, and school districts running centralized enrollment.</p><h2>Why SharaForms is the best tool for this form</h2><p>Transfer students trigger previous-school questions automatically, bus-riders pick their stop during registration, and sibling detection helps place families in one car line instead of two.</p>',
            'types' => ['registration_forms', 'enrollment_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Student Registration', [
                $this->nfText('intro', '<h2>New Student Registration</h2><p>Welcome! Complete this form to register your student. The front office will confirm placement within five school days.</p>'),
                $this->textField('student_name', 'Student Full Name', true),
                $this->dateField('date_of_birth', 'Student Date of Birth', true),
                $this->selectField('grade_level', 'Grade Level Requested', [
                    ['value' => 'kinder', 'text' => 'Kindergarten'],
                    ['value' => 'elementary', 'text' => 'Elementary (1-5)'],
                    ['value' => 'middle', 'text' => 'Middle School (6-8)'],
                    ['value' => 'high', 'text' => 'High School (9-12)'],
                ], true),
                $this->selectField('transferring', 'Is your student transferring from another school?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('previous_school', 'Previous School Name and City', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('transferring', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->textField('parent_name', 'Parent / Guardian Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textareaField('home_address', 'Home Address', true),
                $this->selectField('bus_transport', 'Will your student ride the bus?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->selectField('bus_stop', 'Nearest Bus Stop', [
                    ['value' => 'stop_a', 'text' => 'Stop A: Main St & Oak Ave'],
                    ['value' => 'stop_b', 'text' => 'Stop B: Elm Dr & Park Rd'],
                    ['value' => 'stop_c', 'text' => 'Stop C: Community Center'],
                    ['value' => 'stop_d', 'text' => 'Stop D: Maple Crossing'],
                ], false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('bus_transport', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('siblings_enrolled', 'Do you have other children enrolled here?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('sibling_names', 'Sibling Names and Grades', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('siblings_enrolled', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->textareaField('health_notes', 'Allergies, Medications, or Health Plans'),
                $this->checkboxField('records_consent', 'I authorize the release of my child\'s records from their previous school', true),
            ], '#0284c7'),
        ];
    }

    private function preschoolWaitlist(): array
    {
        return [
            'name' => 'Preschool Waitlist Form Template',
            'slug' => 'preschool-waitlist-form-template',
            'short_description' => 'A preschool waitlist form template for childcare centers capturing child details, schedule preferences, and tour requests.',
            'description' => '<p>Our Preschool Waitlist Form Template helps childcare centers build an organized waitlist with child ages, schedule preferences, and tour requests instead of scattered emails.</p><h2>Why and when to use a preschool waitlist form</h2><p>Childcare spots fill fast and families plan months ahead. An ordered waitlist with birthdates, desired start terms, and schedule needs lets directors offer openings fairly, forecast room utilization, and call the right family the moment a spot appears.</p><h2>Who is this template for</h2><p>Preschools, daycare centers, Montessori schools, nursery programs, and church-affiliated early learning centers.</p><h2>Why SharaForms is the best tool for this form</h2><p>Sibling priority is captured at sign-up, tour requests flow into one list your staff can schedule from, and the list exports by age group so openings match the right children instantly.</p>',
            'types' => ['signup_forms', 'registration_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Preschool Waitlist', [
                $this->nfText('intro', '<h2>Join Our Waitlist</h2><p>We offer placements by waitlist order with sibling priority. Tell us about your little one and we will be in touch when a spot opens.</p>'),
                $this->textField('child_name', 'Child Full Name', true),
                $this->dateField('child_dob', 'Child Date of Birth', true),
                $this->textField('parent_name', 'Parent / Guardian Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('desired_start', 'Desired Start Term', [
                    ['value' => 'fall_26', 'text' => 'Fall 2026'],
                    ['value' => 'spring_27', 'text' => 'Spring 2027'],
                    ['value' => 'fall_27', 'text' => 'Fall 2027'],
                    ['value' => 'asap', 'text' => 'As soon as a spot opens'],
                ], true),
                $this->selectField('schedule_preference', 'Schedule Preference', [
                    ['value' => 'full_time', 'text' => 'Full-time (5 days)'],
                    ['value' => 'part_three', 'text' => 'Part-time (3 days)'],
                    ['value' => 'half_days', 'text' => 'Half-days (5 days)'],
                ], true),
                $this->selectField('sibling_enrolled', 'Do you have another child already enrolled?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('sibling_name', 'Enrolled Sibling\'s Name', false, [
                    'hidden' => true,
                    'help' => 'Sibling priority applies',
                    'logic' => $this->revealLogic([$this->logicCondition('sibling_enrolled', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->checkboxField('tour_requested', 'We would like to schedule a tour before deciding'),
                $this->selectField('hear_about_us', 'How did you hear about us?', [
                    ['value' => 'family_friend', 'text' => 'Family or friend'],
                    ['value' => 'online_search', 'text' => 'Online search'],
                    ['value' => 'social_media', 'text' => 'Social media'],
                    ['value' => 'drive_by', 'text' => 'Passed by the center'],
                    ['value' => 'other', 'text' => 'Other'],
                ]),
                $this->textareaField('questions', 'Questions for Our Team'),
            ], '#db2777'),
        ];
    }

    private function parentTeacherConference(): array
    {
        return [
            'name' => 'Parent-Teacher Conference Form Template',
            'slug' => 'parent-teacher-conference-form-template',
            'short_description' => 'A parent-teacher conference signup template for booking time slots, choosing formats, and focusing discussion topics.',
            'description' => '<p>Our Parent-Teacher Conference Form Template lets parents book their own slot, choose in-person or video, and flag topics ahead of time so teachers walk in prepared.</p><h2>Why and when to use a conference signup form</h2><p>Conference nights collapse into chaos when slots are assigned by paper or reply-all email. Self-serve booking spreads families evenly across slots, video-call requests surface before the night of, and pre-flagged topics turn fifteen minutes into real conversation.</p><h2>Who is this template for</h2><p>Classroom teachers, grade-level teams, school administrators organizing conference nights, and private tutors holding parent check-ins.</p><h2>Why SharaForms is the best tool for this form</h2><p>Families pick their own slots without phone tag, video attendees see joining instructions appear automatically, and topic selections give teachers a head start on every meeting.</p>',
            'types' => ['appointment_forms', 'booking_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Parent-Teacher Conference Booking', [
                $this->nfText('intro', '<h2>Book Your Conference Slot</h2><p>Slots run 20 minutes each. Pick what works for your family; we will confirm by email the same week.</p>'),
                $this->textField('parent_name', 'Parent / Guardian Name', true),
                $this->textField('student_name', 'Student Name', true),
                $this->selectField('teacher', 'Teacher', [
                    ['value' => 'alvarez', 'text' => 'Ms. Alvarez'],
                    ['value' => 'chen', 'text' => 'Mr. Chen'],
                    ['value' => 'okafor', 'text' => 'Mrs. Okafor'],
                    ['value' => 'patel', 'text' => 'Ms. Patel'],
                ], true),
                $this->dateField('conference_date', 'Preferred Date', true),
                $this->selectField('time_slot', 'Time Slot', [
                    ['value' => 'slot_300', 'text' => '3:00 PM'],
                    ['value' => 'slot_320', 'text' => '3:20 PM'],
                    ['value' => 'slot_340', 'text' => '3:40 PM'],
                    ['value' => 'slot_400', 'text' => '4:00 PM'],
                    ['value' => 'slot_420', 'text' => '4:20 PM'],
                ], true),
                $this->selectField('meeting_format', 'Meeting Format', [
                    ['value' => 'in_person', 'text' => 'In person'],
                    ['value' => 'video', 'text' => 'Video call'],
                ], true),
                ['id' => 'video_instructions', 'type' => 'nf-text', 'name' => 'Video Call Instructions',
                    'content' => '<p><strong>Video calls:</strong> we will email your meeting link the morning of your conference. Check spam folders if it has not arrived one hour before your slot.</p>',
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('meeting_format', 'select', 'equals', 'Video call')])],
                $this->multiSelectField('discussion_topics', 'Topics You Would Like to Discuss', [
                    ['value' => 'reading', 'text' => 'Reading progress'],
                    ['value' => 'math', 'text' => 'Math progress'],
                    ['value' => 'behavior', 'text' => 'Behavior and classroom habits'],
                    ['value' => 'social', 'text' => 'Social skills and friendships'],
                    ['value' => 'homework', 'text' => 'Homework support'],
                    ['value' => 'testing', 'text' => 'Test results and assessments'],
                ], true),
                $this->textareaField('questions', 'Specific Questions for the Teacher'),
            ], '#9333ea'),
        ];
    }

    private function tutoringRequest(): array
    {
        return [
            'name' => 'Tutoring Request Form Template',
            'slug' => 'tutoring-request-form-template',
            'short_description' => 'A tutoring request form template matching students with subjects and schedules while estimating monthly session costs.',
            'description' => '<p>Our Tutoring Request Form Template captures student level, subject needs, session frequency, and format preferences, then shows families a monthly estimate before they commit.</p><h2>Why and when to use a tutoring request form</h2><p>Tutoring inquiries mix grade levels, subjects, budgets, and schedules. A structured request routes each student to the right tutor, books realistic session counts up front, and sets billing expectations with a transparent estimate instead of awkward price conversations later.</p><h2>Who is this template for</h2><p>Independent tutors, tutoring centers, test-prep companies, school homework clubs, and libraries coordinating volunteer tutoring.</p><h2>Why SharaForms is the best tool for this form</h2><p>Subject-based rates calculate monthly totals live, online clients get platform questions automatically, and goal fields make first sessions productive from minute one.</p>',
            'types' => ['request_forms', 'booking_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Tutoring Request', [
                $this->nfText('intro', '<h2>Request a Tutor</h2><p>Tell us about your student and goals. We match every request within two business days.</p>'),
                $this->textField('student_name', 'Student Name', true),
                $this->textField('parent_name', 'Parent / Guardian Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('subject', 'Subject Needed', [
                    ['value' => 'elem_math', 'text' => 'Elementary Math - $40/session'],
                    ['value' => 'pre_algebra', 'text' => 'Pre-Algebra - $45/session'],
                    ['value' => 'algebra', 'text' => 'Algebra I & II - $50/session'],
                    ['value' => 'geometry', 'text' => 'Geometry - $50/session'],
                    ['value' => 'sat_prep', 'text' => 'SAT Prep - $65/session'],
                    ['value' => 'reading_writing', 'text' => 'Reading & Writing - $45/session'],
                ], true),
                $this->numberField('sessions_per_month', 'Sessions Per Month', true, ['help' => 'Most families book 4 or 8']),
                $this->selectField('session_format', 'Session Format', [
                    ['value' => 'online', 'text' => 'Online'],
                    ['value' => 'library', 'text' => 'In person (library)'],
                    ['value' => 'home_visit', 'text' => 'In person (home visit, +$10/session)'],
                ], true),
                $this->selectField('platform', 'Preferred Video Platform', [
                    ['value' => 'zoom', 'text' => 'Zoom'],
                    ['value' => 'meet', 'text' => 'Google Meet'],
                    ['value' => 'other_video', 'text' => 'Other'],
                ], false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('session_format', 'select', 'equals', 'Online')], 'and', true),
                ]),
                $this->textareaField('goals', 'What Does Your Student Want to Achieve?', true),
                $this->textField('availability', 'Best Days and Times', true),
                $this->totalBlock('monthly_estimate_display', 'cv_monthly_estimate', 'Estimated Monthly Total', '$0'),
            ], '#ca8a04', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_monthly_estimate',
                        'Estimated Monthly Total',
                        'IF({subject}="Elementary Math - $40/session",40,IF({subject}="Pre-Algebra - $45/session",45,IF({subject}="Algebra I & II - $50/session",50,IF({subject}="Geometry - $50/session",50,IF({subject}="SAT Prep - $65/session",65,45)))))*{sessions_per_month}'
                            . '+IF({session_format}="In person (home visit, +$10/session)",10*{sessions_per_month},0)'
                    ),
                ],
            ]),
        ];
    }

    private function newsletterSignup(): array
    {
        return [
            'name' => 'Newsletter Signup Form Template',
            'slug' => 'newsletter-signup-form-template',
            'short_description' => 'A newsletter signup form template with interest selection, frequency choice, and consent built in for compliant list growth.',
            'description' => '<p>Our Newsletter Signup Form Template grows your email list properly: interest tagging, frequency control, and clear consent language that keeps subscribers engaged instead of unsubscribed.</p><h2>Why and when to use a newsletter signup form</h2><p>A bare email box collects addresses but not intent. Asking what topics people care about and how often they want to hear from you segments your list at the source, which means higher open rates, fewer spam complaints, and content you can actually personalize.</p><h2>Who is this template for</h2><p>Content creators, small businesses, nonprofits, community organizations, and product teams building owned audiences.</p><h2>Why SharaForms is the best tool for this form</h2><p>Interest selections tag subscribers for your email tool via integrations, the referral field shows which channels actually grow your list, and submissions sync to Zapier, webhooks, or Google Sheets automatically.</p>',
            'types' => ['signup_forms'],
            'industries' => ['marketing_forms', 'business_forms'],
            'structure' => $this->structure('Join Our Newsletter', [
                $this->nfText('intro', '<h2>Get the Good Stuff First</h2><p>Practical tips, early access, and zero spam. Pick what interests you and how often we should show up in your inbox.</p>'),
                $this->textField('first_name', 'First Name', true),
                $this->emailField('email'),
                $this->multiSelectField('interests', 'What Are You Interested In?', [
                    ['value' => 'product_updates', 'text' => 'Product updates'],
                    ['value' => 'industry_news', 'text' => 'Industry news'],
                    ['value' => 'events_webinars', 'text' => 'Events & webinars'],
                    ['value' => 'case_studies', 'text' => 'Case studies & customer stories'],
                ], true),
                $this->selectField('frequency', 'How Often Should We Email You?', [
                    ['value' => 'weekly', 'text' => 'Weekly'],
                    ['value' => 'biweekly', 'text' => 'Every two weeks'],
                    ['value' => 'monthly', 'text' => 'Monthly'],
                ], true),
                $this->selectField('referral_source', 'How did you find us?', [
                    ['value' => 'friend_colleague', 'text' => 'Friend or colleague'],
                    ['value' => 'social_media', 'text' => 'Social media'],
                    ['value' => 'search_engine', 'text' => 'Search engine'],
                    ['value' => 'podcast', 'text' => 'Podcast'],
                    ['value' => 'other', 'text' => 'Other'],
                ]),
                $this->textField('referral_other', 'Tell Us Where You Found Us', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('referral_source', 'select', 'equals', 'Other')], 'and', true),
                ]),
                $this->checkboxField('consent', 'I agree to receive the newsletter and understand I can unsubscribe anytime', true),
            ], '#059669'),
        ];
    }

    private function demoRequest(): array
    {
        return [
            'name' => 'Demo Request Form Template',
            'slug' => 'demo-request-form-template',
            'short_description' => 'A demo request form template qualifying B2B leads with company size, current tools, and preferred demo formats.',
            'description' => '<p>Our Demo Request Form Template turns "book a demo" pages into qualified pipeline: company context, current tooling, and the problem to solve arrive before sales even says hello.</p><h2>Why and when to use a demo request form</h2><p>Demos burn selling hours. A qualifying form means reps walk in knowing company size, incumbent tools, and pain points, so the first call starts at value instead of discovery. Requests without budget signals can route to self-serve resources automatically.</p><h2>Who is this template for</h2><p>SaaS companies, B2B service providers, software vendors, and any team whose revenue starts with a product walkthrough.</p><h2>Why SharaForms is the best tool for this form</h2><p>Responses push to Slack, HubSpot-class CRMs, or webhooks instantly, custom-solution answers get captured cleanly, and view-versus-submit analytics reveal friction on your highest-intent page.</p>',
            'types' => ['lead_generation_forms', 'request_forms'],
            'industries' => ['business_forms', 'marketing_forms'],
            'structure' => $this->structure('Request a Demo', [
                $this->nfText('intro', '<h2>See It in Action</h2><p>Tell us a bit about your team and we will tailor a 30-minute walkthrough to your workflow.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('work_email', 'Work Email', true),
                $this->textField('company', 'Company Name', true),
                $this->textField('role', 'Your Role', true),
                $this->selectField('company_size', 'Company Size', [
                    ['value' => '1_10', 'text' => '1-10 employees'],
                    ['value' => '11_50', 'text' => '11-50 employees'],
                    ['value' => '51_200', 'text' => '51-200 employees'],
                    ['value' => '201_1000', 'text' => '201-1,000 employees'],
                    ['value' => '1000_plus', 'text' => '1,000+ employees'],
                ], true),
                $this->selectField('current_solution', 'How do you handle this today?', [
                    ['value' => 'spreadsheets', 'text' => 'Spreadsheets'],
                    ['value' => 'other_software', 'text' => 'Another software tool'],
                    ['value' => 'pen_paper', 'text' => 'Pen and paper'],
                    ['value' => 'nothing_yet', 'text' => 'Nothing formal yet'],
                    ['value' => 'other', 'text' => 'Other'],
                ], true),
                $this->textField('current_solution_detail', 'Which Tool Do You Use Today?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('current_solution', 'select', 'equals', 'Other')], 'and', true),
                ]),
                $this->textareaField('team_need', 'What Are You Hoping to Solve?', true),
                $this->selectField('demo_format', 'Preferred Demo Format', [
                    ['value' => 'live_video', 'text' => 'Live video call'],
                    ['value' => 'recorded', 'text' => 'Recorded walkthrough'],
                ], true),
                $this->selectField('preferred_time', 'Best Time of Day', [
                    ['value' => 'morning', 'text' => 'Morning'],
                    ['value' => 'afternoon', 'text' => 'Afternoon'],
                    ['value' => 'none_pref', 'text' => 'No preference'],
                ]),
            ], '#1d4ed8'),
        ];
    }

    private function affiliateProgramApplication(): array
    {
        return [
            'name' => 'Affiliate Program Application Form Template',
            'slug' => 'affiliate-program-application-form-template',
            'short_description' => 'An affiliate program application form template vetting creators by platform, audience size, and promotion style.',
            'description' => '<p>Our Affiliate Program Application Form Template screens partner applicants by channel, audience size, and promotion methods so your program grows with creators who actually fit.</p><h2>Why and when to use an affiliate application form</h2><p>Open affiliate programs attract spam applications. A structured vetting form filters by audience fit before approval, keeps commission terms acknowledged in writing, and gives you a searchable roster of who promotes what, where.</p><h2>Who is this template for</h2><p>Ecommerce brands running referral commissions, SaaS partner programs, course creators, and influencer marketing teams.</p><h2>Why SharaForms is the best tool for this form</h2><p>Platform questions adapt to each applicant\'s primary channel, audience-size data sorts your pipeline instantly, and accepted partners flow into your CRM through webhooks or Zapier.</p>',
            'types' => ['application_forms', 'signup_forms'],
            'industries' => ['marketing_forms', 'advertising_forms'],
            'structure' => $this->structure('Affiliate Program Application', [
                $this->nfText('intro', '<h2>Partner With Us</h2><p>Earn commission on every sale you refer. Applications are reviewed within one week.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->urlField('website_url', 'Website / Primary Channel URL', true),
                $this->selectField('primary_platform', 'Primary Platform', [
                    ['value' => 'blog', 'text' => 'Blog'],
                    ['value' => 'youtube', 'text' => 'YouTube'],
                    ['value' => 'instagram', 'text' => 'Instagram'],
                    ['value' => 'tiktok', 'text' => 'TikTok'],
                    ['value' => 'newsletter', 'text' => 'Email newsletter'],
                    ['value' => 'podcast', 'text' => 'Podcast'],
                    ['value' => 'other', 'text' => 'Other'],
                ], true),
                $this->textField('platform_other', 'Tell Us About Your Platform', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('primary_platform', 'select', 'equals', 'Other')], 'and', true),
                ]),
                $this->selectField('audience_size', 'Audience Size', [
                    ['value' => 'under_1k', 'text' => 'Under 1,000'],
                    ['value' => '1k_10k', 'text' => '1,000 - 10,000'],
                    ['value' => '10k_50k', 'text' => '10,000 - 50,000'],
                    ['value' => '50k_250k', 'text' => '50,000 - 250,000'],
                    ['value' => 'over_250k', 'text' => 'Over 250,000'],
                ], true),
                $this->multiSelectField('promotion_methods', 'How Do You Plan to Promote Us?', [
                    ['value' => 'reviews', 'text' => 'Product reviews'],
                    ['value' => 'tutorials', 'text' => 'Tutorials & how-tos'],
                    ['value' => 'discount_codes', 'text' => 'Discount codes'],
                    ['value' => 'banner_ads', 'text' => 'Banner ads'],
                    ['value' => 'email_mentions', 'text' => 'Email mentions'],
                ], true),
                $this->textareaField('audience_description', 'Describe Your Audience (interests, demographics)', true),
                $this->checkboxField('terms_agreement', 'I agree to the affiliate program terms and commission structure', true),
            ], '#ea580c'),
        ];
    }

    private function podcastGuestApplication(): array
    {
        return [
            'name' => 'Podcast Guest Application Form Template',
            'slug' => 'podcast-guest-application-form-template',
            'short_description' => 'A podcast guest application form template screening pitches by expertise, availability, and recording setup.',
            'description' => '<p>Our Podcast Guest Application Form Template collects pitches the way hosts actually evaluate them: sharp talking points, proof of past appearances, real availability, and audio setup.</p><h2>Why and when to use a podcast guest form</h2><p>Guest pitches flood host inboxes with walls of text and no signal. A structured application makes candidates comparable at a glance, filters out phone-only audio before scheduling, and builds an episode bank of vetted topics you can record from whenever your calendar opens.</p><h2>Who is this template for</h2><p>Podcast hosts and producers, interview shows, webinar series organizers, and virtual summit coordinators booking speakers.</p><h2>Why SharaForms is the best tool for this form</h2><p>Talking points arrive structured instead of rambling, availability multiselects speed up scheduling across time zones, and sample links let you hear guests before committing studio time.</p>',
            'types' => ['application_forms', 'interview_forms'],
            'industries' => ['entertainment_forms', 'marketing_forms'],
            'structure' => $this->structure('Be Our Guest', [
                $this->nfText('intro', '<h2>Pitch Yourself as a Guest</h2><p>We book episodes four to six weeks out. Give us your sharpest angle and we will be in touch if it fits the show.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->textField('title_role', 'Title / Role', true),
                $this->textField('company_org', 'Company or Organization', true),
                $this->textareaField('expertise', 'What Can You Talk About for 40 Minutes?', true),
                $this->textareaField('talking_points', 'Three Talking Points You Would Bring'),
                $this->urlField('website_url', 'Website or Portfolio', false),
                $this->urlField('sample_link', 'Link to a Previous Talk, Episode, or Interview'),
                $this->multiSelectField('availability', 'When Can You Record?', [
                    ['value' => 'weekday_mornings', 'text' => 'Weekday mornings'],
                    ['value' => 'weekday_afternoons', 'text' => 'Weekday afternoons'],
                    ['value' => 'weekday_evenings', 'text' => 'Weekday evenings'],
                    ['value' => 'weekends', 'text' => 'Weekends'],
                ], true),
                $this->textField('timezone', 'Your Time Zone', true),
                $this->selectField('equipment', 'Recording Setup', [
                    ['value' => 'pro_mic', 'text' => 'Professional microphone'],
                    ['value' => 'headset', 'text' => 'Headset mic'],
                    ['value' => 'phone_only', 'text' => 'Phone only'],
                ], true),
                $this->checkboxField('promo_agreement', 'I can share the episode with my audience when it airs'),
            ], '#7e22ce'),
        ];
    }

    private function speakerProposal(): array
    {
        return [
            'name' => 'Speaker Proposal Form Template',
            'slug' => 'speaker-proposal-form-template',
            'short_description' => 'A speaker proposal form template for conferences and events collecting session pitches, formats, and AV needs.',
            'description' => '<p>Our Speaker Proposal Form Template standardizes call-for-speakers submissions so program committees compare sessions fairly and build balanced agendas fast.</p><h2>Why and when to use a speaker proposal form</h2><p>Reviewing talks from scattered emails means reformatting everything yourself. A uniform proposal captures the session title, format, track, summary, and AV requirements identically for every submitter, which turns agenda planning from archaeology into a checklist.</p><h2>Who is this template for</h2><p>Conference organizers, meetup groups, industry associations, corporate learning teams, and school career-day coordinators.</p><h2>Why SharaForms is the best tool for this form</h2><p>Panel proposals automatically request co-presenter details, AV multiselects feed your venue tech sheet directly, and every submission exports into a review spreadsheet with scoring columns ready.</p>',
            'types' => ['abstract_forms', 'application_forms'],
            'industries' => ['business_forms', 'education_forms'],
            'structure' => $this->structure('Speaker Proposal', [
                $this->nfText('intro', '<h2>Submit Your Session Proposal</h2><p>Our program committee reviews every submission against relevance, originality, and takeaways. Submissions close in six weeks.</p>'),
                $this->textField('speaker_name', 'Speaker Full Name', true),
                $this->emailField('email'),
                $this->textField('job_title', 'Job Title', true),
                $this->textField('organization', 'Organization', true),
                $this->textField('session_title', 'Session Title', true),
                $this->selectField('session_format', 'Session Format', [
                    ['value' => 'solo_30', 'text' => 'Solo talk (30 min)'],
                    ['value' => 'solo_60', 'text' => 'Solo talk (60 min)'],
                    ['value' => 'panel', 'text' => 'Panel discussion'],
                    ['value' => 'workshop', 'text' => 'Hands-on workshop'],
                ], true),
                $this->textareaField('co_presenters', 'Co-Presenters (names and organizations)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('session_format', 'select', 'equals', 'Panel discussion')], 'and', true),
                ]),
                $this->selectField('track', 'Track', [
                    ['value' => 'technology', 'text' => 'Technology'],
                    ['value' => 'leadership', 'text' => 'Leadership'],
                    ['value' => 'marketing', 'text' => 'Marketing'],
                    ['value' => 'education', 'text' => 'Education'],
                    ['value' => 'healthcare', 'text' => 'Healthcare'],
                ], true),
                $this->textareaField('summary', 'Session Summary (what attendees will learn)', true),
                $this->multiSelectField('av_needs', 'AV Requirements', [
                    ['value' => 'projector', 'text' => 'Projector'],
                    ['value' => 'microphone', 'text' => 'Microphone'],
                    ['value' => 'flipchart', 'text' => 'Flipchart'],
                    ['value' => 'hdmi', 'text' => 'HDMI adapter'],
                    ['value' => 'none', 'text' => 'None needed'],
                ]),
                $this->textareaField('bio', 'Speaker Bio (100 words max)', true),
                $this->checkboxField('recording_permission', 'I consent to having my session recorded and shared with attendees'),
            ], '#be123c'),
        ];
    }

    private function complaintForm(): array
    {
        return [
            'name' => 'Complaint Form Template',
            'slug' => 'complaint-form-template',
            'short_description' => 'A complaint form template capturing formal grievances with categories, incident details, and requested resolutions.',
            'description' => '<p>Our Complaint Form Template gives customers and employees a clear, documented path to raise formal issues with all the facts your resolution team needs attached.</p><h2>Why and when to use a complaint form</h2><p>Formal complaints need more than a feedback box: dates, transaction references, prior contact history, and what outcome the complainant actually wants. Capturing these consistently protects your organization, speeds resolution, and creates the audit trail regulators or dispute processes may ask for.</p><h2>Who is this template for</h2><p>Customer service leaders, property managers, healthcare administrators, utilities, schools, and HR departments with formal grievance procedures.</p><h2>Why SharaForms is the best tool for this form</h2><p>Purchase references surface only when relevant, requested outcomes route cases to the right team, and every complaint exports timestamped for compliance reporting.</p>',
            'types' => ['feedback_forms'],
            'industries' => ['customer_service_forms', 'business_forms'],
            'structure' => $this->structure('Formal Complaint', [
                $this->nfText('intro', '<h2>Raise a Formal Complaint</h2><p>We take every complaint seriously. Our resolution team responds within three business days of receipt.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('purchase_made', 'Is your complaint about a specific purchase or service?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('purchase_reference', 'Order Number, Receipt Date, or Booking Reference', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('purchase_made', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('complaint_category', 'Complaint Category', [
                    ['value' => 'product_quality', 'text' => 'Product quality'],
                    ['value' => 'service_experience', 'text' => 'Service experience'],
                    ['value' => 'billing_charges', 'text' => 'Billing or charges'],
                    ['value' => 'delivery_shipping', 'text' => 'Delivery or shipping'],
                    ['value' => 'staff_conduct', 'text' => 'Staff conduct'],
                    ['value' => 'other_category', 'text' => 'Other'],
                ], true),
                $this->dateField('incident_date', 'Date of Incident', true),
                $this->textareaField('description', 'What Happened?', true),
                $this->selectField('prior_contact', 'Have You Contacted Us About This Before?', [
                    ['value' => 'first_contact', 'text' => 'This is my first contact'],
                    ['value' => 'contacted_once', 'text' => 'Yes, once'],
                    ['value' => 'multiple_attempts', 'text' => 'Yes, multiple times'],
                ], true),
                $this->selectField('resolution_wanted', 'What Resolution Are You Seeking?', [
                    ['value' => 'apology_ack', 'text' => 'Apology and acknowledgment'],
                    ['value' => 'refund_credit', 'text' => 'Refund or credit'],
                    ['value' => 'repair_replacement', 'text' => 'Repair or replacement'],
                    ['value' => 'policy_review', 'text' => 'Policy review'],
                    ['value' => 'escalation_mgmt', 'text' => 'Escalation to management'],
                ], true),
                ['id' => 'supporting_docs', 'type' => 'files', 'title' => 'Supporting Documents (receipts, photos, correspondence)', 'required' => false, 'max_file_size' => 10, 'max_number_of_files' => 5],
            ], '#b91c1c'),
        ];
    }

    private function suggestionBox(): array
    {
        return [
            'name' => 'Suggestion Box Form Template',
            'slug' => 'suggestion-box-form-template',
            'short_description' => 'A suggestion box form template with anonymous submissions, categorization, and follow-up contact options.',
            'description' => '<p>Our Suggestion Box Form Template invites honest ideas by making anonymity a first-class choice, then routes categorized suggestions to whoever can act on them.</p><h2>Why and when to use a suggestion box form</h2><p>The best improvement ideas come from people closest to the problem, but only if speaking up feels safe. An anonymous-capable suggestion form surfaces issues leadership never hears otherwise, while optional contact details open the door to follow-up conversations.</p><h2>Who is this template for</h2><p>HR and people-operations teams, company leadership, schools, municipalities, libraries, and customer experience teams collecting product ideas.</p><h2>Why SharaForms is the best tool for this form</h2><p>Contact fields appear only for non-anonymous submitters, category tagging routes each idea to the right owner, and recurring themes become obvious when suggestions are exported and sorted.</p>',
            'types' => ['feedback_forms'],
            'industries' => ['business_forms', 'human_resources_forms'],
            'structure' => $this->structure('Share Your Suggestion', [
                $this->nfText('intro', '<h2>We Want Your Ideas</h2><p>Suggest anything that would make things better. Anonymous submissions are welcome; contact details are entirely your choice.</p>'),
                $this->selectField('submission_about', 'What Is Your Suggestion About?', [
                    ['value' => 'workplace', 'text' => 'The workplace or environment'],
                    ['value' => 'product_service', 'text' => 'Product or service'],
                    ['value' => 'facilities', 'text' => 'Facilities'],
                    ['value' => 'processes', 'text' => 'Processes and efficiency'],
                    ['value' => 'other_topic', 'text' => 'Something else'],
                ], true),
                $this->textField('suggestion_title', 'Suggestion Title', true),
                $this->textareaField('suggestion_details', 'Describe Your Suggestion', true),
                $this->selectField('anonymity', 'Submission Preference', [
                    ['value' => 'anonymous', 'text' => 'Submit anonymously'],
                    ['value' => 'with_contact', 'text' => 'Include my contact details'],
                ], true),
                $this->textField('submitter_name', 'Your Name', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('anonymity', 'select', 'equals', 'Include my contact details')], 'and', true),
                ]),
                $this->emailField('submitter_email', 'Your Email', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('anonymity', 'select', 'equals', 'Include my contact details')], 'and', true),
                ]),
            ], '#16a34a'),
        ];
    }

    private function equipmentCheckout(): array
    {
        return [
            'name' => 'Equipment Checkout Form Template',
            'slug' => 'equipment-checkout-form-template',
            'short_description' => 'An equipment checkout form template for IT, media, and facilities tracking loans with rates, dates, and accountability.',
            'description' => '<p>Our Equipment Checkout Form Template tracks who borrowed what, for how long, and what it costs, replacing the whiteboard-and-honor-system approach to shared gear.</p><h2>Why and when to use an equipment checkout form</h2><p>Laptops, cameras, projectors, and recorders disappear when checkout is informal. A logged form creates accountability per loan, computes expected charges for billing departments, and gives you a searchable history when gear comes back damaged or not at all.</p><h2>Who is this template for</h2><p>IT departments, school media centers, university AV desks, makerspaces, production studios, and equipment rental counters.</p><h2>Why SharaForms is the best tool for this form</h2><p>Daily rates calculate expected charges live, accessory selections travel with each loan, and orientation acknowledgments document that every borrower was trained before walking out the door.</p>',
            'types' => ['request_forms'],
            'industries' => ['it_forms', 'education_forms', 'business_forms'],
            'structure' => $this->structure('Equipment Checkout', [
                $this->nfText('intro', '<h2>Check Out Equipment</h2><p>Complete this form at the service desk. Equipment is due back by 10 AM on the return date.</p>'),
                $this->textField('borrower_name', 'Borrower Name', true),
                $this->emailField('email'),
                $this->selectField('department', 'Department', [
                    ['value' => 'it', 'text' => 'IT'],
                    ['value' => 'media', 'text' => 'Media & Communications'],
                    ['value' => 'facilities', 'text' => 'Facilities'],
                    ['value' => 'classroom', 'text' => 'Classroom / Teaching'],
                    ['value' => 'events', 'text' => 'Events'],
                ], true),
                $this->selectField('item_category', 'Equipment Item', [
                    ['value' => 'laptop', 'text' => 'Laptop - $25/day'],
                    ['value' => 'camera_kit', 'text' => 'Camera Kit - $40/day'],
                    ['value' => 'projector', 'text' => 'Projector - $15/day'],
                    ['value' => 'audio_recorder', 'text' => 'Audio Recorder - $20/day'],
                    ['value' => 'vr_headset', 'text' => 'VR Headset - $30/day'],
                ], true),
                $this->dateField('checkout_date', 'Checkout Date', true),
                $this->dateField('return_date', 'Expected Return Date', true),
                $this->numberField('duration_days', 'Number of Days', true, ['help' => 'Calendar days between checkout and return']),
                $this->textField('purpose', 'Purpose of Use', true),
                $this->multiSelectField('accessories', 'Accessories Needed', [
                    ['value' => 'charger_cables', 'text' => 'Charger & cables'],
                    ['value' => 'carrying_case', 'text' => 'Carrying case'],
                    ['value' => 'tripod', 'text' => 'Tripod'],
                    ['value' => 'spare_battery', 'text' => 'Spare battery'],
                    ['value' => 'no_accessories', 'text' => 'None needed'],
                ]),
                $this->checkboxField('training_completed', 'I have completed the equipment orientation session', true),
                $this->checkboxField('liability_acceptance', 'I accept responsibility for loss or damage beyond normal wear', true),
                $this->totalBlock('checkout_charge_display', 'cv_checkout_total', 'Expected Charge', '$0'),
            ], '#0f766e', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_checkout_total',
                        'Expected Charge',
                        'IF({item_category}="Laptop - $25/day",25,IF({item_category}="Camera Kit - $40/day",40,IF({item_category}="Projector - $15/day",15,IF({item_category}="Audio Recorder - $20/day",20,30))))*{duration_days}'
                    ),
                ],
            ]),
        ];
    }

    private function medicationRefillRequest(): array
    {
        return [
            'name' => 'Medication Refill Request Form Template',
            'slug' => 'medication-refill-request-form-template',
            'short_description' => 'A medication refill request form template for pharmacies and clinics handling renewals, deliveries, and pharmacy transfers.',
            'description' => '<p>Our Medication Refill Request Form Template streamlines renewal requests with prescription details, pickup preferences, and delivery routing so staff process refills in one pass.</p><h2>Why and when to use a medication refill form</h2><p>Phone-in refills interrupt workflow and invite transcription errors. A structured request captures medication, dosage, prescriber, and delivery choice exactly once; transfer requests collect the outside pharmacy details automatically, and delivery orders gather addresses without a second call.</p><h2>Who is this template for</h2><p>Pharmacies, clinic dispensing desks, veterinary practices with pharmacies, long-term-care facilities, and mail-order programs.</p><h2>Why SharaForms is the best tool for this form</h2><p>Delivery requests reveal address fields on their own, pharmacy-transfer questions capture where prescriptions currently live, and instant notifications mean no request waits in a voicemail inbox.</p>',
            'types' => ['request_forms'],
            'industries' => ['healthcare_forms'],
            'structure' => $this->structure('Medication Refill Request', [
                $this->nfText('intro', '<h2>Request a Refill</h2><p>Submit at least three days before you run out. We will confirm by your preferred contact method once processing begins.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->dateField('date_of_birth', 'Date of Birth', true),
                $this->textField('record_number', 'Patient / Medical Record Number', true),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textField('medication_name', 'Medication Name', true),
                $this->textField('dosage', 'Dosage Strength', true),
                $this->textField('prescriber_name', 'Prescribing Doctor', true),
                $this->selectField('pharmacy_location', 'Pharmacy', [
                    ['value' => 'home_branch', 'text' => 'This location'],
                    ['value' => 'different_branch', 'text' => 'A different pharmacy'],
                ], true),
                $this->textField('pharmacy_details', 'Pharmacy Name and Address', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('pharmacy_location', 'select', 'equals', 'A different pharmacy')], 'and', true),
                ]),
                $this->selectField('pickup_method', 'Pickup or Delivery', [
                    ['value' => 'pickup', 'text' => 'Pickup in store'],
                    ['value' => 'local_delivery', 'text' => 'Local delivery'],
                    ['value' => 'mail_order', 'text' => 'Mail order'],
                ], true),
                $this->textareaField('delivery_address', 'Delivery Address', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('pickup_method', 'select', 'equals', 'Local delivery'),
                        $this->logicCondition('pickup_method', 'select', 'equals', 'Mail order'),
                    ], 'or', true),
                ]),
                $this->textareaField('allergies', 'Known Allergies or Reactions'),
                $this->textareaField('notes', 'Notes for the Pharmacist'),
                $this->checkboxField('consent', 'I authorize this pharmacy to prepare and dispense the requested prescription', true),
            ], '#0369a1'),
        ];
    }

    private function mealTrainSignup(): array
    {
        return [
            'name' => 'Meal Train Signup Form Template',
            'slug' => 'meal-train-signup-form-template',
            'short_description' => 'A meal train signup form template coordinating home-cooked support with dates, dishes, dietary capabilities, and drop-off windows.',
            'description' => '<p>Our Meal Train Signup Form Template organizes meal support for new parents, neighbors in recovery, or families in hard seasons: one dish per person, no double-booked nights.</p><h2>Why and when to use a meal train signup form</h2><p>Meal trains fail on coordination, not generosity. A signup form locks one volunteer per date, records what dish they plan so nobody eats lasagna four nights straight, and captures kitchen capabilities plus container logistics before cooking day.</p><h2>Who is this template for</h2><p>Church communities, neighborhood groups, workplaces supporting colleagues, friends organizing postpartum care, and mutual-aid networks.</p><h2>Why SharaForms is the best tool for this form</h2><p>Date conflicts are visible at a glance in submissions, allergen-free capabilities get flagged for the family, and reminder notifications keep every cook on schedule.</p>',
            'types' => ['volunteer_forms', 'signup_forms'],
            'industries' => ['charity_forms', 'church_forms'],
            'structure' => $this->structure('Meal Train Signup', [
                $this->nfText('intro', '<h2>Cook Up Some Kindness</h2><p>Pick a date, claim your dish, and help fill a week with warm meals. Drop-off details confirmed after sign-up.</p>'),
                $this->textField('volunteer_name', 'Your Name', true),
                $this->emailField('email'),
                $this->dateField('delivery_date', 'Date You Can Deliver', true),
                $this->selectField('meal_type', 'What Will You Provide?', [
                    ['value' => 'dinner_main', 'text' => 'Dinner main dish'],
                    ['value' => 'side_salad', 'text' => 'Side or salad'],
                    ['value' => 'dessert', 'text' => 'Dessert'],
                    ['value' => 'breakfast', 'text' => 'Breakfast items'],
                    ['value' => 'snacks_drinks', 'text' => 'Snacks or drinks'],
                ], true),
                $this->textField('dish_description', 'Planned Dish', true, ['help' => 'So the family knows what to expect each night']),
                $this->multiSelectField('dietary_capabilities', 'Kitchen Capabilities', [
                    ['value' => 'allergen_free', 'text' => 'Can cook allergen-free'],
                    ['value' => 'vegetarian', 'text' => 'Vegetarian'],
                    ['value' => 'vegan', 'text' => 'Vegan'],
                    ['value' => 'gluten_free', 'text' => 'Gluten-free'],
                    ['value' => 'unsure', 'text' => 'Not sure'],
                ]),
                $this->selectField('delivery_window', 'Drop-off Window', [
                    ['value' => 'window_4_5', 'text' => '4:00 - 5:00 PM'],
                    ['value' => 'window_5_6', 'text' => '5:00 - 6:00 PM'],
                    ['value' => 'window_6_7', 'text' => '6:00 - 7:00 PM'],
                    ['value' => 'before_five', 'text' => 'Drop off anytime before 5 PM'],
                ], true),
                $this->checkboxField('containers_return', 'My containers will need returning'),
                $this->textareaField('notes', 'Notes for the Family or Organizer'),
            ], '#f59e0b'),
        ];
    }

    private function conferenceRegistration(): array
    {
        return [
            'name' => 'Conference Registration Form Template',
            'slug' => 'conference-registration-form-template',
            'short_description' => 'A conference registration form template with early-bird pricing, workshop add-ons, and invoicing for teams.',
            'description' => '<p>Our Conference Registration Form Template handles multi-tier pricing, workshop passes, dietary logistics, and invoice billing in one clean flow.</p><h2>Why and when to use a conference registration form</h2><p>Conferences stack pricing tiers, add-on passes, and headcount deadlines on top of ordinary attendee details. A structured registration captures every commercial choice per attendee, feeds accurate catering counts, and gives finance a clean list of who still owes what.</p><h2>Who is this template for</h2><p>Conference organizers, industry associations, corporate event teams, and universities running symposiums or annual meetings.</p><h2>Why SharaForms is the best tool for this form</h2><p>The total recalculates live as tiers and workshops are chosen, dietary answers trigger detail fields automatically, and invoice requests collect billing contacts without slowing card-paying attendees down.</p>',
            'types' => ['event_registration_forms', 'registration_forms'],
            'industries' => ['business_forms', 'education_forms'],
            'structure' => $this->structure('Conference Registration', [
                $this->nfText('intro', '<h2>Reserve Your Seat</h2><p>Early-bird pricing ends soon. Every pass includes keynotes, lunch, and access to the expo floor.</p>'),
                $this->textField('full_name', 'Full Name', true),
                $this->emailField('email'),
                $this->textField('company', 'Company / Organization', true),
                $this->textField('job_title', 'Job Title', true),
                $this->selectField('registration_type', 'Registration Type', [
                    ['value' => 'early_bird', 'text' => 'Early Bird - $295'],
                    ['value' => 'standard', 'text' => 'Standard - $395'],
                    ['value' => 'student', 'text' => 'Student - $150'],
                ], true),
                $this->selectField('workshop_addon', 'Workshop Pass', [
                    ['value' => 'none', 'text' => 'No workshops'],
                    ['value' => 'full', 'text' => 'Full Workshop Pass (+$120)'],
                ], true),
                $this->selectField('dietary_needs', 'Any dietary requirements?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('dietary_details', 'Describe Your Dietary Requirements', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('dietary_needs', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('payment_method', 'Payment Method', [
                    ['value' => 'card', 'text' => 'Credit card'],
                    ['value' => 'invoice', 'text' => 'Company invoice'],
                ], true),
                $this->emailField('billing_email', 'Billing Email (AP / accounts payable)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('payment_method', 'select', 'equals', 'Invoice')], 'and', true),
                ]),
                $this->checkboxField('terms', 'I agree to the attendance and cancellation policy', true),
                $this->totalBlock('conference_total_display', 'cv_conference_total', 'Registration Total', '$0'),
            ], '#1e3a8a', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_conference_total',
                        'Registration Total',
                        'IF({registration_type}="Early Bird - $295",295,IF({registration_type}="Standard - $395",395,150))'
                            . '+IF({workshop_addon}="Full Workshop Pass (+$120)",120,0)'
                    ),
                ],
            ]),
        ];
    }

    private function reunionRegistration(): array
    {
        return [
            'name' => 'Reunion Registration Form Template',
            'slug' => 'reunion-registration-form-template',
            'short_description' => 'A reunion registration form template for family and class reunions with per-person fees, shirts, and potluck dishes.',
            'description' => '<p>Our Reunion Registration Form Template handles family reunions and class homecomings: headcounts, commemorative shirts, dish sign-ups, and shared memories in one submission.</p><h2>Why and when to use a reunion registration form</h2><p>Reunion committees plan food, space, and keepsakes against fuzzy headcounts collected over group texts. One form locks real numbers: who is coming, what they are bringing, and how much the committee has raised before booking anything final.</p><h2>Who is this template for</h2><p>Family reunion committees, class reunion planners, military unit homecomings, and company anniversary events.</p><h2>Why SharaForms is the best tool for this form</h2><p>Totals update as families register multiple members, shirt orders roll up automatically for one print run, and the memory field turns submissions into ready-made reunion slideshow material.</p>',
            'types' => ['event_registration_forms', 'rsvp_forms'],
            'industries' => ['alumni_forms', 'entertainment_forms'],
            'structure' => $this->structure('Reunion Registration', [
                $this->nfText('intro', '<h2>See You at the Reunion!</h2><p>$45 per person covers the venue, dinner, and activities. Kids under 6 join free; count only paying attendees below.</p>'),
                $this->textField('contact_name', 'Your Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->numberField('attendee_count', 'Number of Attendees ($45 each)', true),
                $this->selectField('era', 'Which era are you representing?', [
                    ['value' => '70s', 'text' => '1970s'],
                    ['value' => '80s', 'text' => '1980s'],
                    ['value' => '90s', 'text' => '1990s'],
                    ['value' => '00s', 'text' => '2000s'],
                    ['value' => '10s', 'text' => '2010s'],
                ], true),
                $this->selectField('reunion_shirt', 'Commemorative Shirt', [
                    ['value' => 'none', 'text' => 'No shirt this year'],
                    ['value' => 'add', 'text' => 'Add shirts (+$20 each, sizes collected later)'],
                ], true),
                $this->numberField('shirt_quantity', 'How Many Shirts?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('reunion_shirt', 'select', 'equals', 'Add shirts (+$20 each, sizes collected later)')], 'and', true),
                ]),
                $this->selectField('bringing_dish', 'Bringing a potluck dish?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('dish_description', 'What Dish Are You Bringing?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('bringing_dish', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->textareaField('favorite_memory', 'Share a favorite memory for the reunion slideshow'),
                $this->checkboxField('photo_permission', 'I am okay with reunion photos being shared with attendees'),
                $this->totalBlock('reunion_total_display', 'cv_reunion_total', 'Family Total', '$0'),
            ], '#7c2d12', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_reunion_total',
                        'Family Total',
                        '{attendee_count}*45+IFBLANK({shirt_quantity},0)*20'
                    ),
                ],
            ]),
        ];
    }

    private function vacationBibleSchoolRegistration(): array
    {
        return [
            'name' => 'VBS Registration Form Template',
            'slug' => 'vacation-bible-school-registration-form-template',
            'short_description' => 'A VBS registration form template for churches collecting child details, allergies, shirt sizes, and pickup authorization.',
            'description' => '<p>Our VBS Registration Form Template streamlines summer Bible school sign-ups with child details, allergy tracking, t-shirt sizes, and secure pickup authorization.</p><h2>Why and when to use a VBS registration form</h2><p>Vacation Bible School weeks juggle dozens of children, volunteer rosters, snack allergies, and checkout safety. Digital registrations give directors complete rosters early, so group assignments, supply orders, and name tags happen before day one instead of during drop-off chaos.</p><h2>Who is this template for</h2><p>Churches running vacation Bible school, summer ministry programs, kids camps, and midweek children\'s clubs with seasonal enrollment.</p><h2>Why SharaForms is the best tool for this form</h2><p>Allergy alerts surface on every relevant record, shirt sizes roll up into one order sheet, and pickup passwords keep check-out secure without laminated claim tickets. Export rosters by age group for station assignments.</p>',
            'types' => ['registration_forms', 'enrollment_forms'],
            'industries' => ['church_forms', 'charity_forms'],
            'structure' => $this->structure('VBS Registration', [
                $this->nfText('intro', '<h2>VBS Sign-Up</h2><p>Five mornings of songs, stories, games, and crafts. Free for our community; shirts available at pick-up.</p>'),
                $this->textField('child_name', 'Child Full Name', true),
                $this->numberField('child_age', 'Child Age', true),
                $this->selectField('grade_completed', 'Grade Just Completed', [
                    ['value' => 'prek', 'text' => 'Pre-K'],
                    ['value' => 'k', 'text' => 'Kindergarten'],
                    ['value' => 'g1_2', 'text' => '1st-2nd Grade'],
                    ['value' => 'g3_4', 'text' => '3rd-4th Grade'],
                    ['value' => 'g5_6', 'text' => '5th-6th Grade'],
                ], true),
                $this->textField('parent_name', 'Parent / Guardian Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('allergies', 'Does your child have allergies?', [
                    ['value' => 'yes', 'text' => 'Yes'],
                    ['value' => 'no', 'text' => 'No'],
                ], true),
                $this->textField('allergy_details', 'List Allergies and Required Accommodations', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('allergies', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('shirt_size', 'T-Shirt Size (free this year)', [
                    ['value' => 'ys', 'text' => 'Youth S'],
                    ['value' => 'ym', 'text' => 'Youth M'],
                    ['value' => 'yl', 'text' => 'Youth L'],
                    ['value' => 'as', 'text' => 'Adult S'],
                    ['value' => 'am', 'text' => 'Adult M'],
                    ['value' => 'al', 'text' => 'Adult L'],
                ], true),
                $this->textField('pickup_password', 'Pickup Password (a word only your family knows)', true),
                $this->checkboxField('photo_permission', 'I allow my child\'s photo in church communications'),
                $this->checkboxField('sunscreen_permission', 'I authorize volunteers to apply sunscreen before outdoor games'),
            ], '#065f46'),
        ];
    }

    private function cakeOrder(): array
    {
        return [
            'name' => 'Cake Order Form Template',
            'slug' => 'cake-order-form-template',
            'short_description' => 'A cake order form template for bakeries capturing sizes, flavors, inscriptions, delivery dates, and live totals.',
            'description' => '<p>Our Cake Order Form Template captures custom cake orders completely: size, flavor, filling, inscription, and delivery details with an automatic price total.</p><h2>Why and when to use a cake order form</h2><p>Custom cakes die by phone tag: flavors misheard, dates forgotten, prices guessed. A structured order records every specification against a timestamp, bakers confirm once instead of five times, and customers see honest totals before they commit.</p><h2>Who is this template for</h2><p>Home bakeries, cake studios, bakery counters, and pastry chefs taking celebration, wedding, and office party orders.</p><h2>Why SharaForms is the best tool for this form</h2><p>Premium flavors and fillings adjust the total live, delivery orders collect addresses and dates automatically, and inscription text arrives exactly as the customer typed it, not as a phone message was remembered.</p>',
            'types' => ['order_forms'],
            'industries' => ['ecommerce_forms', 'services_forms'],
            'structure' => $this->structure('Custom Cake Order', [
                $this->nfText('intro', '<h2>Order Your Custom Cake</h2><p>Orders need 72 hours notice. Rush orders call us first; everything else starts right here.</p>'),
                $this->textField('customer_name', 'Your Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('cake_size', 'Cake Size', [
                    ['value' => 'six_round', 'text' => '6 inch round, serves 8-12 - $45'],
                    ['value' => 'eight_round', 'text' => '8 inch round, serves 12-20 - $60'],
                    ['value' => 'quarter_sheet', 'text' => 'Quarter sheet, serves 20-30 - $75'],
                    ['value' => 'half_sheet', 'text' => 'Half sheet, serves 40-50 - $110'],
                ], true),
                $this->selectField('flavor', 'Flavor', [
                    ['value' => 'vanilla', 'text' => 'Vanilla bean'],
                    ['value' => 'chocolate', 'text' => 'Chocolate'],
                    ['value' => 'lemon', 'text' => 'Lemon'],
                    ['value' => 'red_velvet', 'text' => 'Red Velvet (+$5)'],
                ], true),
                $this->selectField('filling', 'Filling', [
                    ['value' => 'buttercream', 'text' => 'Vanilla buttercream'],
                    ['value' => 'choc_ganache', 'text' => 'Chocolate ganache (+$6)'],
                    ['value' => 'cream_cheese', 'text' => 'Cream cheese (+$8)'],
                    ['value' => 'fruit', 'text' => 'Seasonal fruit (+$8)'],
                ], true),
                $this->textField('inscription', 'Inscription (exactly as it should read)'),
                $this->selectField('delivery_option', 'Pickup or Delivery', [
                    ['value' => 'pickup', 'text' => 'Pickup from bakery'],
                    ['value' => 'delivery', 'text' => 'Local delivery (+$15)'],
                ], true),
                $this->textareaField('delivery_address', 'Delivery Address', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('delivery_option', 'select', 'equals', 'Local delivery (+$15)')], 'and', true),
                ]),
                $this->dateField('needed_by', 'Date Needed', true),
                $this->textareaField('design_notes', 'Design Notes (colors, theme, allergies)'),
                $this->totalBlock('cake_total_display', 'cv_cake_total', 'Order Total', '$0'),
            ], '#9d174d', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_cake_total',
                        'Order Total',
                        'IF({cake_size}="6 inch round, serves 8-12 - $45",45,IF({cake_size}="8 inch round, serves 12-20 - $60",60,IF({cake_size}="Quarter sheet, serves 20-30 - $75",75,110)))'
                            . '+IF({flavor}="Red Velvet (+$5)",5,0)'
                            . '+IF({filling}="Chocolate ganache (+$6)",6,IF({filling}="Cream cheese (+$8)",8,IF({filling}="Seasonal fruit (+$8)",8,0)))'
                            . '+IF({delivery_option}="Local delivery (+$15)",15,0)'
                    ),
                ],
            ]),
        ];
    }

    private function yearbookOrder(): array
    {
        return [
            'name' => 'Yearbook Order Form Template',
            'slug' => 'yearbook-order-form-template',
            'short_description' => 'A yearbook order form template for schools selling copies, personalization, and parent dedication ads with live totals.',
            'description' => '<p>Our Yearbook Order Form Template handles the spring sales push: copies, cover personalization, and parent dedication ads calculated into one clear total per family.</p><h2>Why and when to use a yearbook order form</h2><p>Yearbook coordinators reconcile paper order envelopes, ad submissions, and cash across hundreds of students. One digital order captures copies, personalization choices, and dedication messages together, and exports tell the printer exactly what to produce.</p><h2>Who is this template for</h2><p>Elementary through high school yearbook advisors, PTA fundraising committees, and student publications teams.</p><h2>Why SharaForms is the best tool for this form</h2><p>Dedication ads reveal message fields automatically, personalization fees calculate without envelope math, and CSV exports sort cleanly by teacher for distribution day.</p>',
            'types' => ['order_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Yearbook Order', [
                $this->nfText('intro', '<h2>Order Your Yearbook</h2><p>Prices rise after March 1. Personalization and dedication ads are optional extras shown in your total.</p>'),
                $this->textField('student_name', 'Student Name', true),
                $this->selectField('grade_level', 'Grade Level', [
                    ['value' => 'elem', 'text' => 'K-5'],
                    ['value' => 'middle', 'text' => '6-8'],
                    ['value' => 'high', 'text' => '9-12'],
                ], true),
                $this->textField('teacher_name', 'Teacher (elementary orders)', false),
                $this->numberField('copies', 'Number of Copies ($30 each)', true),
                $this->selectField('personalization', 'Cover Personalization', [
                    ['value' => 'none', 'text' => 'None'],
                    ['value' => 'stamp', 'text' => 'Name stamped on cover (+$7)'],
                ], true),
                $this->textField('stamped_name', 'Name to Stamp (max 25 characters)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('personalization', 'select', 'equals', 'Name stamped on cover (+$7)')], 'and', true),
                ]),
                $this->selectField('dedication_ad', 'Parent Dedication Ad', [
                    ['value' => 'none', 'text' => 'No dedication'],
                    ['value' => 'eighth', 'text' => 'Eighth-page ad - $25'],
                    ['value' => 'quarter', 'text' => 'Quarter-page ad - $45'],
                    ['value' => 'half', 'text' => 'Half-page ad - $80'],
                ], true),
                $this->textareaField('ad_message', 'Dedication Message (and photo instructions)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('dedication_ad', 'select', 'does_not_equal', 'No dedication')], 'and', true),
                ]),
                $this->emailField('parent_email', 'Confirmation Email'),
                $this->totalBlock('yearbook_total_display', 'cv_yearbook_total', 'Order Total', '$0'),
            ], '#312e81', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_yearbook_total',
                        'Order Total',
                        '{copies}*30+IF({personalization}="Name stamped on cover (+$7)",7,0)'
                            . '+IF({dedication_ad}="Eighth-page ad - $25",25,IF({dedication_ad}="Quarter-page ad - $45",45,IF({dedication_ad}="Half-page ad - $80",80,0)))'
                    ),
                ],
            ]),
        ];
    }

    private function overtimeRequest(): array
    {
        return [
            'name' => 'Overtime Request Form Template',
            'slug' => 'overtime-request-form-template',
            'short_description' => 'An overtime request form template capturing hours, rates, time-and-a-half pay projections, and supervisor approvals.',
            'description' => '<p>Our Overtime Request Form Template standardizes pre-approval for extra hours with automatic pay projections and a documented approval trail.</p><h2>Why and when to use an overtime request form</h2><p>Unplanned overtime wrecks labor budgets and invites compliance questions. A pre-approval form makes expected hours, projected cost, and supervisor sign-off explicit before hours are worked, protecting both payroll accuracy and employees.</p><h2>Who is this template for</h2><p>Operations managers, retail and restaurant supervisors, warehouse leads, manufacturing shift managers, and HR teams enforcing overtime policy.</p><h2>Why SharaForms is the best tool for this form</h2><p>Pay projections compute at time-and-a-half automatically, reason codes reveal context only when needed, and approvals export into payroll review with timestamps intact.</p>',
            'types' => ['request_forms', 'employment_forms'],
            'industries' => ['human_resources_forms', 'business_forms'],
            'structure' => $this->structure('Overtime Request', [
                $this->nfText('intro', '<h2>Overtime Pre-Approval Request</h2><p>Submit before working extra hours unless responding to a declared emergency. Pay projects at 1.5x your base rate.</p>'),
                $this->textField('employee_name', 'Employee Name', true),
                $this->textField('employee_id', 'Employee ID', true),
                $this->selectField('department', 'Department', [
                    ['value' => 'operations', 'text' => 'Operations'],
                    ['value' => 'retail_floor', 'text' => 'Retail Floor'],
                    ['value' => 'warehouse', 'text' => 'Warehouse'],
                    ['value' => 'kitchen', 'text' => 'Kitchen'],
                    ['value' => 'support', 'text' => 'Customer Support'],
                    ['value' => 'other_dept', 'text' => 'Other'],
                ], true),
                $this->dateField('week_ending', 'Week Ending Date', true),
                $this->numberField('overtime_hours', 'Requested Overtime Hours', true),
                $this->numberField('hourly_rate', 'Base Hourly Rate ($)', true),
                $this->selectField('reason_code', 'Reason', [
                    ['value' => 'coverage', 'text' => 'Coverage shortage'],
                    ['value' => 'deadline', 'text' => 'Project deadline'],
                    ['value' => 'equipment', 'text' => 'Equipment repair'],
                    ['value' => 'seasonal', 'text' => 'Seasonal peak'],
                    ['value' => 'other_reason', 'text' => 'Other'],
                ], true),
                $this->textField('reason_detail', 'Explain the Circumstances', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('reason_code', 'select', 'equals', 'Other')], 'and', true),
                ]),
                $this->selectField('coverage_arranged', 'Is remaining coverage arranged?', [
                    ['value' => 'yes_cov', 'text' => 'Yes'],
                    ['value' => 'no_cov', 'text' => 'No, not applicable'],
                ], true),
                $this->checkboxField('supervisor_approval', 'My supervisor has verbally approved this request', true),
                $this->totalBlock('overtime_pay_display', 'cv_overtime_pay', 'Projected Overtime Pay', '$0'),
            ], '#a16207', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_overtime_pay',
                        'Projected Overtime Pay',
                        '{overtime_hours}*{hourly_rate}*1.5'
                    ),
                ],
            ]),
        ];
    }

    private function travelAuthorization(): array
    {
        return [
            'name' => 'Travel Authorization Form Template',
            'slug' => 'travel-authorization-form-template',
            'short_description' => 'A travel authorization form template estimating airfare, hotel, and per diem costs for pre-trip approvals.',
            'description' => '<p>Our Travel Authorization Form Template captures business trip details with automatic cost estimates covering airfare, hotel nights, and per diem allowances.</p><h2>Why and when to use a travel authorization form</h2><p>Trips booked before approval create expense disputes and budget surprises. An authorization form forces the estimate conversation early: finance sees projected spend by category, international trips surface passport requirements, and personal-car mileage gets captured where it belongs.</p><h2>Who is this template for</h2><p>Finance and operations teams, field service businesses, nonprofits with grant-funded travel rules, and any organization requiring pre-trip approvals.</p><h2>Why SharaForms is the best tool for this form</h2><p>Total estimates compute live from nights and per-diem days, international destinations prompt passport validity questions, and approved authorizations export straight into expense-policy files.</p>',
            'types' => ['request_forms', 'employment_forms'],
            'industries' => ['human_resources_forms', 'business_forms'],
            'structure' => $this->structure('Travel Authorization Request', [
                $this->nfText('intro', '<h2>Travel Authorization Request</h2><p>Submit at least two weeks before departure. Estimates use your entered figures plus the standard $55 daily per diem.</p>'),
                $this->textField('employee_name', 'Employee Name', true),
                $this->selectField('department', 'Department', [
                    ['value' => 'sales', 'text' => 'Sales'],
                    ['value' => 'operations', 'text' => 'Operations'],
                    ['value' => 'marketing_travel', 'text' => 'Marketing'],
                    ['value' => 'engineering_t', 'text' => 'Engineering'],
                    ['value' => 'exec', 'text' => 'Executive'],
                    ['value' => 'other_trav', 'text' => 'Other'],
                ], true),
                $this->textField('destination', 'Destination (city, state/country)', true),
                $this->textareaField('purpose', 'Business Purpose', true),
                $this->dateField('departure_date', 'Departure Date', true),
                $this->dateField('return_date', 'Return Date', true),
                $this->numberField('airfare_estimate', 'Estimated Airfare ($)', true),
                $this->numberField('hotel_nights', 'Hotel Nights', true),
                $this->numberField('hotel_rate', 'Nightly Hotel Rate ($)', true),
                $this->numberField('per_diem_days', 'Per-Diem Days ($55/day standard)', true),
                $this->selectField('intl_travel', 'Is this international travel?', [
                    ['value' => 'yes_intl', 'text' => 'Yes'],
                    ['value' => 'no_intl', 'text' => 'No'],
                ], true),
                $this->textField('passport_validity', 'Passport Expiry Date (must be 6+ months past return)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('intl_travel', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('personal_vehicle', 'Driving a personal vehicle instead?', [
                    ['value' => 'yes_car', 'text' => 'Yes'],
                    ['value' => 'no_car', 'text' => 'No'],
                ], true),
                $this->numberField('mileage_miles', 'Estimated Mileage (reimbursed at IRS rate)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('personal_vehicle', 'select', 'equals', 'Yes')]),
                ]),
                $this->checkboxField('policy_acknowledgment', 'I will follow the travel expense policy and submit receipts within 15 days', true),
                $this->totalBlock('trip_total_display', 'cv_trip_total', 'Estimated Trip Cost', '$0'),
            ], '#3730a3', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_trip_total',
                        'Estimated Trip Cost',
                        '{airfare_estimate}+{hotel_nights}*{hotel_rate}+{per_diem_days}*55'
                    ),
                ],
            ]),
        ];
    }

    private function artCommissionRequest(): array
    {
        return [
            'name' => 'Art Commission Request Form Template',
            'slug' => 'art-commission-request-form-template',
            'short_description' => 'An art commission request form template for artists quoting styles, character counts, backgrounds, and usage rights.',
            'description' => '<p>Our Art Commission Request Form Template quotes custom artwork professionally: style tiers, extra characters, backgrounds, rush fees, and commercial licensing in one live estimate.</p><h2>Why and when to use an art commission form</h2><p>Commission inquiries scatter across DMs with vague asks like "how much for something like your last post?" A quote form converts interest into concrete specs, prices scope honestly before work starts, and documents usage rights that protect both artist and client.</p><h2>Who is this template for</h2><p>Freelance illustrators, portrait artists, concept artists, furry and fandom artists, tattoo designers, and small studios taking commission queues.</p><h2>Why SharaForms is the best tool for this form</h2><p>Every add-on adjusts the estimate visibly so clients self-select affordable scopes, reference uploads attach directly to the request, and commercial-license answers document rights conversations in writing.</p>',
            'types' => ['request_forms', 'quote_forms'],
            'industries' => ['services_forms', 'entertainment_forms'],
            'structure' => $this->structure('Commission Request', [
                $this->nfText('intro', '<h2>Request a Commission</h2><p>Queue opens monthly. Build your quote below; I reply to every serious request within a week.</p>'),
                $this->textField('client_name', 'Your Name / Handle', true),
                $this->emailField('email'),
                $this->selectField('commission_type', 'Artwork Type', [
                    ['value' => 'bust', 'text' => 'Bust sketch - $40'],
                    ['value' => 'full_char', 'text' => 'Full character, colored - $85'],
                    ['value' => 'scene', 'text' => 'Scene illustration - $150'],
                ], true),
                $this->numberField('extra_characters', 'Additional Characters ($25 each)', false),
                $this->selectField('background_style', 'Background', [
                    ['value' => 'simple_bg', 'text' => 'Simple / flat color'],
                    ['value' => 'detailed_bg', 'text' => 'Detailed background (+$40)'],
                ], true),
                $this->selectField('rush_order', 'Timeline', [
                    ['value' => 'standard', 'text' => 'Standard queue (2-4 weeks)'],
                    ['value' => 'rush', 'text' => 'Rush delivery (+$50)'],
                ], true),
                $this->dateField('rush_deadline', 'Hard Deadline Date', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('rush_order', 'select', 'equals', 'Rush delivery (+$50)')], 'and', true),
                ]),
                $this->selectField('usage_rights', 'Usage Rights', [
                    ['value' => 'personal', 'text' => 'Personal use'],
                    ['value' => 'commercial', 'text' => 'Commercial license (+$75)'],
                ], true),
                ['id' => 'reference_upload', 'type' => 'files', 'title' => 'Reference Images', 'required' => false, 'help' => 'Characters, poses, color palettes', 'max_file_size' => 10, 'max_number_of_files' => 5],
                $this->textareaField('vision_description', 'Describe Your Vision', true),
                $this->checkboxField('terms_ok', 'I have read the commission terms and revision policy', true),
                $this->totalBlock('commission_total_display', 'cv_commission_total', 'Quote Estimate', '$0'),
            ], '#7048e8', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_commission_total',
                        'Quote Estimate',
                        'IF({commission_type}="Bust sketch - $40",40,IF({commission_type}="Full character, colored - $85",85,150))'
                            . '+IFBLANK({extra_characters},0)*25'
                            . '+IF({background_style}="Detailed background (+$40)",40,0)'
                            . '+IF({rush_order}="Rush delivery (+$50)",50,0)'
                            . '+IF({usage_rights}="Commercial license (+$75)",75,0)'
                    ),
                ],
            ]),
        ];
    }

    private function trainingEvaluation(): array
    {
        return [
            'name' => 'Training Evaluation Form Template',
            'slug' => 'training-evaluation-form-template',
            'short_description' => 'A training evaluation form template scoring sessions on clarity, relevance, pace, and materials with instant tallies.',
            'description' => '<p>Our Training Evaluation Form Template collects session feedback the way L&D teams actually use it: four scored dimensions, a computed score tally, and open comments routed to trainers.</p><h2>Why and when to use a training evaluation form</h2><p>Training programs improve only when feedback is specific enough to act on. Scoring clarity, relevance, pace, and materials separately pinpoints whether a weak session needs better content or better delivery, while computed tallies make cohorts comparable quarter over quarter.</p><h2>Who is this template for</h2><p>Corporate L&D teams, HR training coordinators, workshop facilitators, conference session organizers, and internal trainers.</p><h2>Why SharaForms is the best tool for this form</h2><p>Scores compute instantly for same-day debriefs, low ratings trigger follow-up questions automatically, and exports feed your LMS reporting without manual tallying.</p>',
            'types' => ['evaluation_forms', 'survey_templates'],
            'industries' => ['education_forms', 'business_forms'],
            'structure' => $this->structure('Training Session Evaluation', [
                $this->nfText('intro', '<h2>How Was Your Session?</h2><p>Two minutes, four quick scores, and your comments go straight to the trainer. Responses are reviewed within 48 hours.</p>'),
                $this->textField('session_title', 'Which Session Did You Attend?', true),
                $this->dateField('session_date', 'Session Date', true),
                $this->textField('your_role', 'Your Role / Team (optional)'),
                ['id' => 'q_clarity', 'type' => 'rating', 'title' => 'Clarity: the content was explained clearly', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'q_relevance', 'type' => 'rating', 'title' => 'Relevance: the material applies to my work', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'q_pace', 'type' => 'rating', 'title' => 'Pace: timing and flow worked well', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'q_materials', 'type' => 'rating', 'title' => 'Materials: handouts and resources were useful', 'required' => true, 'help' => '', 'steps' => 5],
                $this->selectField('would_recommend', 'Would you recommend this session?', [
                    ['value' => 'yes_rec', 'text' => 'Yes'],
                    ['value' => 'maybe_rec', 'text' => 'Maybe, with changes'],
                    ['value' => 'no_rec', 'text' => 'No'],
                ], true),
                $this->textareaField('improvement_feedback', 'What Would Have Made This Session Better?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('would_recommend', 'select', 'does_not_equal', 'Yes'),
                        $this->logicCondition('q_clarity', 'rating', 'less_than', 3),
                        $this->logicCondition('q_relevance', 'rating', 'less_than', 3),
                    ], 'or'),
                ]),
                $this->textareaField('topics_next', 'What Topics Should We Cover Next?'),
                $this->totalBlock('training_score_display', 'cv_training_score', 'Session Score', '0 / 20'),
            ], '#134e4a', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_training_score',
                        'Session Score',
                        'SUM({q_clarity},{q_relevance},{q_pace},{q_materials})'
                    ),
                ],
            ]),
        ];
    }

    private function transcriptRequest(): array
    {
        return [
            'name' => 'Transcript Request Form Template',
            'slug' => 'transcript-request-form-template',
            'short_description' => 'A transcript request form template for schools handling delivery methods, rush processing, and third-party releases.',
            'description' => '<p>Our Transcript Request Form Template handles alumni and student record requests with delivery routing, processing-speed pricing, and release authorization built in.</p><h2>Why and when to use a transcript request form</h2><p>Records offices juggle phone requests with missing graduation years and unclear destinations. A structured form captures identity details, exact delivery targets, and consent once; electronic copies go out same-day while mail requests collect addresses automatically.</p><h2>Who is this template for</h2><p>High school registrar offices, university records departments, trade schools, and districts centralizing former-student requests.</p><h2>Why SharaForms is the best tool for this form</h2><p>Delivery choices reveal exactly the fields each method needs, rush pricing sets expectations before submission, and every request carries a documented release consent for compliance.</p>',
            'types' => ['request_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Transcript Request', [
                $this->nfText('intro', '<h2>Request Your Transcript</h2><p>Standard processing takes 3-5 school days. Rush requests are processed within 24 hours of payment confirmation.</p>'),
                $this->textField('full_name', 'Full Name (as enrolled)', true),
                $this->dateField('date_of_birth', 'Date of Birth', true),
                $this->textField('years_attended', 'Years Attended (e.g. 2014-2018)', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('delivery_method', 'Delivery Method', [
                    ['value' => 'email_pdf', 'text' => 'Secure PDF by email'],
                    ['value' => 'mail_self', 'text' => 'Mail to my address'],
                    ['value' => 'mail_third', 'text' => 'Mail to a third party'],
                    ['value' => 'pickup', 'text' => 'Pickup in person'],
                ], true),
                $this->textareaField('mailing_address', 'Mailing Address', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('delivery_method', 'select', 'equals', 'Mail to my address')], 'and', true),
                ]),
                $this->textField('third_party_details', 'Recipient Name and Full Address', false, [
                    'hidden' => true,
                    'help' => 'Employer, university admissions office, or agency',
                    'logic' => $this->revealLogic([$this->logicCondition('delivery_method', 'select', 'equals', 'Mail to a third party')], 'and', true),
                ]),
                $this->selectField('processing_speed', 'Processing Speed', [
                    ['value' => 'standard', 'text' => 'Standard (3-5 days) - $5'],
                    ['value' => 'rush', 'text' => 'Rush (24 hours) - $15'],
                ], true),
                $this->selectField('request_purpose', 'Purpose of Request', [
                    ['value' => 'employment_t', 'text' => 'Employment'],
                    ['value' => 'education_t', 'text' => 'Further education'],
                    ['value' => 'personal_records', 'text' => 'Personal records'],
                    ['value' => 'other_purpose', 'text' => 'Other'],
                ], true),
                $this->numberField('copies_requested', 'Number of Copies', true),
                $this->checkboxField('records_release', 'I authorize the release of my academic records to the destination above', true),
                $this->checkboxField('finaid_note', 'Attach a financial-aid hold check before processing'),
            ], '#0e7490'),
        ];
    }

    private function directDeposit(): array
    {
        return [
            'name' => 'Direct Deposit Form Template',
            'slug' => 'direct-deposit-form-template',
            'short_description' => 'A direct deposit form template for HR enrolling employees with account types, split deposits, and void-check uploads.',
            'description' => '<p>Our Direct Deposit Form Template handles payroll enrollment cleanly: full or split deposits, percentage or fixed allocations, and void-check documentation in one secure submission.</p><h2>Why and when to use a direct deposit form</h2><td>Paper deposit slips get misread, and one transposed digit means a missed payday. A structured digital form validates format expectations up front, documents employee authorization, and gives payroll one consistent record per change.</p></td><h2>Who is this template for</h2><p>HR and payroll teams at companies of any size, staffing agencies onboarding workers quickly, and nonprofits modernizing paper processes.</p><h2>Why SharaForms is the best tool for this form</h2><p>Split-deposit choices reveal allocation fields only when relevant, void-check uploads attach to the authorization record, and submissions timestamp every change for audit trails.</p>',
            'types' => ['employment_forms', 'request_forms'],
            'industries' => ['human_resources_forms', 'banking_forms'],
            'structure' => $this->structure('Direct Deposit Authorization', [
                $this->nfText('intro', '<h2>Payroll Direct Deposit</h2><p>Changes take effect the next pay cycle when submitted before the 15th. Have your routing and account numbers ready.</p>'),
                $this->textField('employee_name', 'Employee Name', true),
                $this->textField('employee_id_dd', 'Employee ID', true),
                $this->emailField('email'),
                $this->selectField('action_type', 'What are you doing?', [
                    ['value' => 'enroll_new', 'text' => 'Enroll in direct deposit'],
                    ['value' => 'change_existing', 'text' => 'Change my existing account'],
                    ['value' => 'cancel_deposit', 'text' => 'Cancel direct deposit'],
                ], true),
                $this->textField('bank_name', 'Bank Name', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('action_type', 'select', 'does_not_equal', 'Cancel direct deposit')], 'and', true),
                ]),
                $this->textField('routing_number', 'Routing Number (9 digits)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('action_type', 'select', 'does_not_equal', 'Cancel direct deposit')], 'and', true),
                ]),
                $this->textField('account_number', 'Account Number', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('action_type', 'select', 'does_not_equal', 'Cancel direct deposit')], 'and', true),
                ]),
                $this->selectField('account_type', 'Account Type', [
                    ['value' => 'checking', 'text' => 'Checking'],
                    ['value' => 'savings', 'text' => 'Savings'],
                ], false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('action_type', 'select', 'does_not_equal', 'Cancel direct deposit')], 'and', true),
                ]),
                $this->selectField('deposit_split', 'Deposit Allocation', [
                    ['value' => 'full_amount', 'text' => 'Full amount to this account'],
                    ['value' => 'percent_split', 'text' => 'Percentage split across accounts'],
                    ['value' => 'fixed_split', 'text' => 'Fixed dollar amount to this account'],
                ], false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('action_type', 'select', 'does_not_equal', 'Cancel direct deposit')], 'and', true),
                ]),
                $this->numberField('percent_allocation', 'Percentage to This Account (%)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('deposit_split', 'select', 'equals', 'Percentage split across accounts')], 'and', true),
                ]),
                $this->numberField('fixed_allocation', 'Fixed Amount per Paycheck ($)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('deposit_split', 'select', 'equals', 'Fixed dollar amount to this account')], 'and', true),
                ]),
                ['id' => 'void_check', 'type' => 'files', 'title' => 'Void Check or Bank Letter', 'required' => false, 'help' => 'Confirms account and routing numbers', 'max_file_size' => 10, 'max_number_of_files' => 1],
                $this->checkboxField('authorization', 'I authorize my employer to deposit wages to the account above and adjust for erroneous entries', true),
            ], '#14532d'),
        ];
    }

    private function purchaseRequisition(): array
    {
        return [
            'name' => 'Purchase Requisition Form Template',
            'slug' => 'purchase-requisition-form-template',
            'short_description' => 'A purchase requisition form template for internal approvals capturing items, costs, vendors, and urgent justifications.',
            'description' => '<p>Our Purchase Requisition Form Template standardizes internal buying requests with live line totals, vendor validation, and approval-ready justifications.</p><h2>Why and when to use a purchase requisition form</h2><p>Purchase orders start as informal requests that finance reconstructs later. A requisition form captures what, how many, from whom, and why before money moves: approvers see computed totals instantly, urgent buys carry written justification, and new vendors get onboarded deliberately instead of accidentally.</p><h2>Who is this template for</h2><p>Procurement teams, office managers, department heads controlling budgets, schools and nonprofits with spending policies, and any organization requiring purchase pre-approval.</p><h2>Why SharaForms is the best tool for this form</h2><p>Totals compute from quantity times unit cost without spreadsheet round-trips, urgent requests explain themselves, and approved requisitions export into your PO process in order.</p>',
            'types' => ['request_forms', 'order_forms'],
            'industries' => ['business_forms'],
            'structure' => $this->structure('Purchase Requisition', [
                $this->nfText('intro', '<h2>Purchase Requisition</h2><p>Submit for approval before purchasing. Requests under $500 need manager sign-off; larger amounts route to finance.</p>'),
                $this->textField('requested_by', 'Requested By', true),
                $this->selectField('department_pr', 'Department', [
                    ['value' => 'ops_pr', 'text' => 'Operations'],
                    ['value' => 'it_pr', 'text' => 'IT'],
                    ['value' => 'facilities_pr', 'text' => 'Facilities'],
                    ['value' => 'marketing_pr', 'text' => 'Marketing'],
                    ['value' => 'hr_pr', 'text' => 'Human Resources'],
                    ['value' => 'other_pr', 'text' => 'Other'],
                ], true),
                $this->textField('item_description', 'Item / Service Description', true),
                $this->numberField('quantity_pr', 'Quantity', true),
                $this->numberField('unit_cost', 'Unit Cost ($)', true),
                $this->dateField('date_needed', 'Date Needed', true),
                $this->selectField('vendor_status', 'Vendor', [
                    ['value' => 'approved_vendor', 'text' => 'From our approved vendor list'],
                    ['value' => 'new_vendor', 'text' => 'New vendor (never used before)'],
                ], true),
                $this->textareaField('new_vendor_details', 'New Vendor Details (company, contact, website)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('vendor_status', 'select', 'equals', 'New vendor (never used before)')], 'and', true),
                ]),
                $this->selectField('is_urgent', 'Is this request urgent?', [
                    ['value' => 'yes_urgent', 'text' => 'Yes'],
                    ['value' => 'no_urgent', 'text' => 'No'],
                ], true),
                $this->textareaField('urgent_justification', 'Why does this exceed normal lead time?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('is_urgent', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->textareaField('business_justification', 'Business Justification', true),
                $this->checkboxField('budget_confirmed', 'I confirm this expense fits within my department budget', true),
                $this->totalBlock('line_total_display', 'cv_line_total', 'Line Total', '$0'),
            ], '#713f12', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_line_total',
                        'Line Total',
                        '{quantity_pr}*{unit_cost}'
                    ),
                ],
            ]),
        ];
    }

    private function itSupportTicket(): array
    {
        return [
            'name' => 'IT Support Ticket Form Template',
            'slug' => 'it-support-ticket-form-template',
            'short_description' => 'An IT support ticket form template triaging issues by category, urgency, device, and business impact.',
            'description' => '<p>Our IT Support Ticket Form Template turns "the internet is broken" Slack messages into structured tickets with categories, urgency, impact statements, and diagnostics attached.</p><h2>Why and when to use an IT support ticket form</h2><p>Support chaos comes from unstructured requests arriving through five channels at once. A ticket form forces the facts that matter: what category, which device, how urgent, who is blocked. High-impact issues surface business-impact descriptions automatically so triage prioritizes correctly.</p><h2>Who is this template for</h2><p>Internal IT departments, MSPs taking client tickets, school technology desks, and shared-services teams tracking request volume.</p><h2>Why SharaForms is the best tool for this form</h2><p>Critical issues arrive with impact context attached, screenshots travel with the first message instead of the third, and ticket volume by category reveals where training beats troubleshooting.</p>',
            'types' => ['request_forms', 'report_forms'],
            'industries' => ['it_forms', 'business_forms'],
            'structure' => $this->structure('IT Support Ticket', [
                $this->nfText('intro', '<h2>Open a Support Ticket</h2><p>Response targets: Critical within 1 hour, High within 4 hours, everything else next business day.</p>'),
                $this->textField('requester_name', 'Your Name', true),
                $this->emailField('email'),
                $this->selectField('issue_category', 'Issue Category', [
                    ['value' => 'hardware_it', 'text' => 'Hardware'],
                    ['value' => 'software_it', 'text' => 'Software / Application'],
                    ['value' => 'network_it', 'text' => 'Network / WiFi / VPN'],
                    ['value' => 'account_it', 'text' => 'Account access / Password reset'],
                    ['value' => 'email_it', 'text' => 'Email'],
                    ['value' => 'printer_it', 'text' => 'Printing'],
                    ['value' => 'other_it', 'text' => 'Other'],
                ], true),
                $this->selectField('device_type', 'Device Affected', [
                    ['value' => 'laptop_it', 'text' => 'Laptop'],
                    ['value' => 'desktop_it', 'text' => 'Desktop / Workstation'],
                    ['value' => 'mobile_it', 'text' => 'Mobile device'],
                    ['value' => 'peripheral', 'text' => 'Peripheral (monitor, dock, headset)'],
                    ['value' => 'na_device', 'text' => 'Not device-specific'],
                ], true),
                $this->selectField('urgency_level', 'Urgency', [
                    ['value' => 'critical_it', 'text' => 'Critical - completely blocked'],
                    ['value' => 'high_it', 'text' => 'High - major disruption'],
                    ['value' => 'medium_it', 'text' => 'Medium - workaround exists'],
                    ['value' => 'low_it', 'text' => 'Low - minor annoyance'],
                ], true),
                $this->textareaField('work_impact', 'Describe the Business Impact', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('urgency_level', 'select', 'equals', 'Critical - completely blocked'),
                        $this->logicCondition('urgency_level', 'select', 'equals', 'High - major disruption'),
                    ], 'or', true),
                ]),
                $this->selectField('work_location', 'Where Are You Working?', [
                    ['value' => 'office_loc', 'text' => 'Main office'],
                    ['value' => 'remote_loc', 'text' => 'Remote'],
                    ['value' => 'branch_loc', 'text' => 'Branch office / client site'],
                ], true),
                $this->textareaField('problem_description', 'What Is Happening?', true),
                $this->textareaField('error_message_text', 'Exact Error Message (if any)'),
                ['id' => 'screenshot_upload', 'type' => 'files', 'title' => 'Screenshots', 'required' => false, 'help' => 'A picture of the error saves a round trip', 'max_file_size' => 10, 'max_number_of_files' => 3],
            ], '#164e63'),
        ];
    }

    private function creditApplication(): array
    {
        return [
            'name' => 'Credit Application Form Template',
            'slug' => 'credit-application-form-template',
            'short_description' => 'A business credit application form template for trade accounts capturing structure, references, limits, and terms.',
            'description' => '<p>Our Credit Application Form Template vets B2B customers for net-terms accounts with structure details, trade references, and requested limits in one documented application.</p><h2>Why and when to use a credit application form</h2><p>Extending trade credit on trust alone ends in collections. A structured application captures the facts credit managers weigh: legal structure, years trading, bank and trade references, and requested exposure, so approval decisions are consistent and defensible.</p><h2>Who is this template for</h2><p>Wholesalers and distributors, manufacturers with dealer networks, commercial service providers, and equipment suppliers offering net-30 or net-60 terms.</p><h2>Why SharaForms is the best tool for this form</h2><p>Sole proprietors are prompted for personal guarantees automatically, reference details arrive structured for checking, and approved accounts export into AR systems without rekeying.</p>',
            'types' => ['application_forms', 'request_forms'],
            'industries' => ['banking_forms', 'business_forms'],
            'structure' => $this->structure('Business Credit Application', [
                $this->nfText('intro', '<h2>Apply for a Trade Credit Account</h2><p>Applications are reviewed within three business days. Approved accounts start on Net 30 terms.</p>'),
                $this->textField('business_name_credit', 'Legal Business Name', true),
                $this->textField('dba_name', 'DBA / Trading Name'),
                $this->textField('contact_name_credit', 'Contact Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textareaField('business_address', 'Business Address', true),
                $this->selectField('business_structure', 'Business Structure', [
                    ['value' => 'sole_prop', 'text' => 'Sole proprietorship'],
                    ['value' => 'partnership_c', 'text' => 'Partnership'],
                    ['value' => 'llc_c', 'text' => 'LLC'],
                    ['value' => 'corporation_c', 'text' => 'Corporation'],
                ], true),
                $this->textField('guarantor_name', 'Personal Guarantor Name and Title', false, [
                    'hidden' => true,
                    'help' => 'Sole proprietorships require a personal guarantee',
                    'logic' => $this->revealLogic([$this->logicCondition('business_structure', 'select', 'equals', 'Sole proprietorship')], 'and', true),
                ]),
                $this->numberField('years_in_business', 'Years in Business', true),
                $this->selectField('annual_revenue_band', 'Annual Revenue', [
                    ['value' => 'under_250k', 'text' => 'Under $250K'],
                    ['value' => '250k_1m', 'text' => '$250K - $1M'],
                    ['value' => '1m_5m', 'text' => '$1M - $5M'],
                    ['value' => 'over_5m', 'text' => 'Over $5M'],
                ], true),
                $this->selectField('requested_limit', 'Requested Credit Limit', [
                    ['value' => 'limit_2500', 'text' => '$2,500'],
                    ['value' => 'limit_5000', 'text' => '$5,000'],
                    ['value' => 'limit_10000', 'text' => '$10,000'],
                    ['value' => 'limit_25000', 'text' => '$25,000'],
                ], true),
                $this->textareaField('trade_references', 'Trade References (two suppliers with contacts)', true),
                $this->textareaField('bank_reference', 'Bank Reference (bank name, branch, account contact)', true),
                $this->checkboxField('terms_acceptance_credit', 'I authorize credit checks on the references provided and agree to the stated payment terms', true),
            ], '#500724'),
        ];
    }

    private function wholesaleAccountApplication(): array
    {
        return [
            'name' => 'Wholesale Account Application Form Template',
            'slug' => 'wholesale-account-application-form-template',
            'short_description' => 'A wholesale account application form template verifying retailers with resale certificates and volume estimates.',
            'description' => '<p>Our Wholesale Account Application Form Template screens retail partners properly: storefront type, resale documentation, product categories, and volume expectations before pricing is unlocked.</p><h2>Why and when to use a wholesale application form</h2><p>Opening wholesale to anyone with an email invites discount-code resellers who wreck brand positioning. A verification-first application collects resale certificates up front, confirms legitimate retail channels, and sets minimum-order and MAP expectations in writing from day one.</p><h2>Who is this template for</h2><p>Consumer brands opening wholesale lines, makers scaling beyond direct sales, food and beverage producers, and boutique brands protecting retail relationships.</p><h2>Why SharaForms is the best tool for this form</h2><p>Online sellers are asked for store URLs automatically, resale certificates attach directly to each application, and category interests help your sales team match new stockists to the right line sheet.</p>',
            'types' => ['application_forms', 'signup_forms'],
            'industries' => ['ecommerce_forms', 'business_forms'],
            'structure' => $this->structure('Wholesale Account Application', [
                $this->nfText('intro', '<h2>Become a Stockist</h2><p>Minimum first order $350. Applications reviewed weekly; approved partners receive line sheets and wholesale login within five days.</p>'),
                $this->textField('business_name_ws', 'Business Name', true),
                $this->textField('contact_name_ws', 'Contact Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('storefront_type', 'Storefront Type', [
                    ['value' => 'online_store', 'text' => 'Online store only'],
                    ['value' => 'physical_store', 'text' => 'Physical store only'],
                    ['value' => 'both_channels', 'text' => 'Both online and physical'],
                ], true),
                $this->urlField('website_url', 'Store / Website URL', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('storefront_type', 'select', 'equals', 'Online store only'),
                        $this->logicCondition('storefront_type', 'select', 'equals', 'Both online and physical'),
                    ], 'or', true),
                ]),
                $this->textareaField('store_address', 'Store Address(es)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('storefront_type', 'select', 'equals', 'Physical store only'),
                        $this->logicCondition('storefront_type', 'select', 'equals', 'Both online and physical'),
                    ], 'or', true),
                ]),
                $this->multiSelectField('product_interest', 'Product Categories of Interest', [
                    ['value' => 'apparel_ws', 'text' => 'Apparel'],
                    ['value' => 'home_goods', 'text' => 'Home goods'],
                    ['value' => 'beauty_ws', 'text' => 'Beauty & skincare'],
                    ['value' => 'food_bev_ws', 'text' => 'Food & beverage'],
                    ['value' => 'accessories_ws', 'text' => 'Accessories'],
                ], true),
                $this->selectField('monthly_volume', 'Estimated Monthly Order Volume', [
                    ['value' => 'vol_350', 'text' => '$350 - $750'],
                    ['value' => 'vol_1500', 'text' => '$750 - $1,500'],
                    ['value' => 'vol_3000', 'text' => '$1,500 - $3,000'],
                    ['value' => 'vol_plus', 'text' => '$3,000+'],
                ], true),
                ['id' => 'resale_certificate', 'type' => 'files', 'title' => 'Resale Certificate Upload', 'required' => true, 'help' => 'Required before wholesale pricing is granted', 'max_file_size' => 10, 'max_number_of_files' => 1],
                $this->checkboxField('map_acknowledgment', 'I agree to the minimum advertised price (MAP) policy and resale terms', true),
            ], '#581c87'),
        ];
    }

    private function insuranceQuoteRequest(): array
    {
        return [
            'name' => 'Insurance Quote Request Form Template',
            'slug' => 'insurance-quote-request-form-template',
            'short_description' => 'An insurance quote request form template routing auto, home, renters, life, and business inquiries with coverage details.',
            'description' => '<p>Our Insurance Quote Request Form Template routes prospect inquiries by coverage type and collects exactly the details each quote needs, so agents respond with numbers instead of questionnaires.</p><h2>Why and when to use an insurance quote request form</h2><p>Quote requests stall when agents call back to collect basics: vehicle year, home age, coverage amounts. A structured request gathers coverage-specific details at first contact through conditional fields, letting prepared agents deliver indicative quotes on the first response.</p><h2>Who is this template for</h2><p>Independent insurance agencies, brokerage teams, captive agents running local landing pages, and MGAs collecting submission pipelines.</p><h2>Why SharaForms is the best tool for this form</h2><p>Coverage selection reveals only relevant questions, renewal dates flag prospects shopping soon, and instant notifications mean fast follow-up while quote intent is hot.</p>',
            'types' => ['quote_forms', 'lead_generation_forms'],
            'industries' => ['insurance_forms'],
            'structure' => $this->structure('Get Your Insurance Quote', [
                $this->nfText('intro', '<h2>Quotes Made Simple</h2><p>Tell us what you need covered. An agent responds within one business day with your indicative quote.</p>'),
                $this->textField('full_name_iq', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('coverage_type', 'What Do You Need Covered?', [
                    ['value' => 'auto_iq', 'text' => 'Auto'],
                    ['value' => 'home_iq', 'text' => 'Homeowners'],
                    ['value' => 'renters_iq', 'text' => 'Renters'],
                    ['value' => 'life_iq', 'text' => 'Life'],
                    ['value' => 'business_iq', 'text' => 'Business'],
                ], true),
                $this->textareaField('auto_details', 'Vehicle Details (year, make, model, primary use)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('coverage_type', 'select', 'equals', 'Auto')], 'and', true),
                ]),
                $this->textareaField('home_details', 'Property Details (address, year built, square footage)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('coverage_type', 'select', 'equals', 'Homeowners')], 'and', true),
                ]),
                $this->textareaField('renters_details', 'Rental Details (landlord, building type, belongings value)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('coverage_type', 'select', 'equals', 'Renters')], 'and', true),
                ]),
                $this->textareaField('life_details', 'Coverage Needs (amount, ages covered, purpose)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('coverage_type', 'select', 'equals', 'Life')], 'and', true),
                ]),
                $this->textareaField('business_details', 'Business Details (type, employees, coverage needs)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('coverage_type', 'select', 'equals', 'Business')], 'and', true),
                ]),
                $this->textField('current_insurer', 'Current Insurer (if any)'),
                $this->dateField('renewal_date', 'Current Policy Renewal Date'),
                $this->selectField('contact_time', 'Best Time to Reach You', [
                    ['value' => 'morning_iq', 'text' => 'Morning'],
                    ['value' => 'afternoon_iq', 'text' => 'Afternoon'],
                    ['value' => 'evening_iq', 'text' => 'Evening'],
                ]),
                $this->checkboxField('consent_contact', 'I consent to being contacted about my quote request', true),
            ], '#831843'),
        ];
    }

    private function taxPreparationIntake(): array
    {
        return [
            'name' => 'Tax Preparation Client Intake Form Template',
            'slug' => 'tax-preparation-client-intake-form-template',
            'short_description' => 'A tax preparation intake form template for accountants collecting filing status, income sources, and document readiness.',
            'description' => '<p>Our Tax Preparation Client Intake Form Template organizes season workload before it starts: filing status, income types, dependents, and special situations captured per client.</p><h2>Why and when to use a tax client intake form</h2><p>Tax season collapses when engagement letters meet missing information. An intake form identifies complexity early: self-employment income triggers business questions, rentals and investments surface before appointments, so preparers schedule realistic time slots instead of discovering K-1s mid-meeting.</p><h2>Who is this template for</h2><p>CPA firms, enrolled agents, tax prep franchises, bookkeepers expanding into tax filing, and virtual tax practices.</p><h2>Why SharaForms is the best tool for this form</h2><p>Income-type selections reveal relevant follow-ups automatically, appointment preferences route scheduling immediately, and completed intakes become the engagement checklist your staff works from.</p>',
            'types' => ['questionnaire_templates', 'application_forms'],
            'industries' => ['banking_forms', 'business_forms'],
            'structure' => $this->structure('Tax Client Intake', [
                $this->nfText('intro', '<h2>Welcome New Clients</h2><p>Complete this intake so your preparer knows what to expect. Gather documents after submitting; we confirm your appointment within one business day.</p>'),
                $this->textField('client_name_tax', 'Full Legal Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('filing_status', 'Filing Status', [
                    ['value' => 'single_f', 'text' => 'Single'],
                    ['value' => 'mfj', 'text' => 'Married filing jointly'],
                    ['value' => 'mfs', 'text' => 'Married filing separately'],
                    ['value' => 'hoh', 'text' => 'Head of household'],
                ], true),
                $this->numberField('dependents_count', 'Number of Dependents', true),
                $this->multiSelectField('income_types', 'Income Types This Year', [
                    ['value' => 'w2_wages', 'text' => 'W-2 wages'],
                    ['value' => 'self_1099', 'text' => 'Self-employment / 1099'],
                    ['value' => 'investments_t', 'text' => 'Investments & dividends'],
                    ['value' => 'rental_income', 'text' => 'Rental property'],
                    ['value' => 'retirement_inc', 'text' => 'Retirement / pensions'],
                    ['value' => 'unemp_inc', 'text' => 'Unemployment benefits'],
                ], true),
                $this->textareaField('self_employed_details', 'Business Details (name, EIN, expense records ready?)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('income_types', 'multi_select', 'contains', 'Self-employment / 1099')], 'and', true),
                ]),
                $this->textareaField('special_situations', 'Special Situations (sold property, crypto, inheritance, K-1s, moved states)'),
                $this->selectField('documents_ready', 'Are your documents gathered?', [
                    ['value' => 'docs_yes', 'text' => 'Yes, all set'],
                    ['value' => 'docs_mostly', 'text' => 'Mostly, a few pending'],
                    ['value' => 'docs_no', 'text' => 'Not yet started'],
                ], true),
                $this->selectField('appointment_preference', 'Appointment Preference', [
                    ['value' => 'office_meet', 'text' => 'In office'],
                    ['value' => 'video_meet', 'text' => 'Video call'],
                    ['value' => 'dropoff_tax', 'text' => 'Document drop-off, no meeting'],
                ], true),
                $this->textField('prior_preparer', 'Prior Year Preparer (if switching to us)'),
                $this->checkboxField('engagement_consent', 'I consent to electronic communication and document exchange', true),
            ], '#422006'),
        ];
    }

    private function tattooConsent(): array
    {
        return [
            'name' => 'Tattoo Consent Form Template',
            'slug' => 'tattoo-consent-form-template',
            'short_description' => 'A tattoo consent form template covering age verification, guardian consent for minors, allergies, and aftercare acknowledgment.',
            'description' => '<p>Our Tattoo Consent Form Template documents informed consent before the needle touches skin: identity, age, health disclosures, placement approval, and aftercare responsibility.</p><h2>Why and when to use a tattoo consent form</h2><p>Studios need documented consent for every session, and minors require guardian involvement that must be provable later. A digital consent form creates timestamped records per client and session, protecting artists with clear health disclosures and aftercare acknowledgments.</p><h2>Who is this template for</h2><p>Tattoo studios, guest artists at conventions, apprentices taking supervised work, and piercing studios wanting consistent consent records.</p><h2>Why SharaForms is the best tool for this form</h2><p>Minors trigger mandatory guardian fields automatically, allergy disclosures sit beside design notes for the artist, and portfolio-permission choices are captured explicitly instead of assumed.</p>',
            'types' => ['consent_forms'],
            'industries' => ['salon_forms', 'services_forms'],
            'structure' => $this->structure('Tattoo Consent & Release', [
                $this->nfText('intro', '<h2>Consent & Release Form</h2><p>Please complete honestly and completely. Your artist reviews this before starting; information here keeps you safe.</p>'),
                $this->textField('client_name_tc', 'Full Legal Name', true),
                $this->dateField('date_of_birth_tc', 'Date of Birth', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('age_status', 'Age Confirmation', [
                    ['value' => 'adult_18', 'text' => 'I am 18 or older'],
                    ['value' => 'minor_age', 'text' => 'I am under 18 (guardian must be present)'],
                ], true),
                $this->textField('guardian_name', 'Parent / Legal Guardian Name', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('age_status', 'select', 'equals', 'I am under 18 (guardian must be present)')], 'and', true),
                ]),
                $this->phoneField('guardian_phone', 'Guardian Phone (present in studio)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('age_status', 'select', 'equals', 'I am under 18 (guardian must be present)')], 'and', true),
                ]),
                $this->checkboxField('guardian_consent', 'As guardian, I consent to this tattoo and will remain on premises', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('age_status', 'select', 'equals', 'I am under 18 (guardian must be present)'),]),
                ]),
                $this->textField('placement_area', 'Placement (body area)', true),
                $this->textareaField('design_description', 'Design Description', true),
                $this->selectField('color_style', 'Color Style', [
                    ['value' => 'black_grey', 'text' => 'Black & grey'],
                    ['value' => 'full_color', 'text' => 'Full color'],
                ], true),
                $this->textareaField('health_disclosure', 'Allergies, Skin Conditions, or Medical Considerations (ink reactions, blood thinners, diabetes)', true),
                $this->checkboxField('aftercare_acknowledge', 'I understand the aftercare instructions provided and accept responsibility for healing', true),
                $this->checkboxField('portfolio_permission', 'The studio may photograph my finished tattoo for portfolio and social media'),
            ], '#3b0764'),
        ];
    }

    private function prayerRequest(): array
    {
        return [
            'name' => 'Prayer Request Form Template',
            'slug' => 'prayer-request-form-template',
            'short_description' => 'A prayer request form template handling sensitive submissions with privacy controls, categories, and follow-up options.',
            'description' => '<p>Our Prayer Request Form Template gives congregations a dignified channel for requests: category tagging, granular sharing permissions, and optional follow-up contact.</p><h2>Why and when to use a prayer request form</h2><p>Prayer requests carry deeply personal details that deserve deliberate handling. A structured form lets people choose exactly who sees their request: team only, congregation, or private. Follow-up preferences ensure care happens without awkward public exposure of private struggles.</p><h2>Who is this template for</h2><p>Churches, campus ministries, hospital chaplaincies, prayer chains, and small groups coordinating care for members.</p><h2>Why SharaForms is the best tool for this form</h2><p>Sharing permissions travel with each request so nothing gets read aloud by mistake, follow-up requests collect contact preferences automatically, and pastoral teams see new needs the moment they are submitted.</p>',
            'types' => ['request_forms'],
            'industries' => ['church_forms', 'charity_forms'],
            'structure' => $this->structure('Submit a Prayer Request', [
                $this->nfText('intro', '<h2>We Would Be Honored to Pray</h2><p>Share as much or as little as you wish. You control exactly who sees your request.</p>'),
                $this->textField('first_name_pr', 'First Name (optional)'),
                $this->emailField('contact_email', 'Email (optional)', false),
                $this->selectField('request_category', 'What Is Your Request About?', [
                    ['value' => 'healing_pr', 'text' => 'Healing & health'],
                    ['value' => 'family_pr', 'text' => 'Family'],
                    ['value' => 'finances_pr', 'text' => 'Finances & work'],
                    ['value' => 'guidance_pr', 'text' => 'Guidance & decisions'],
                    ['value' => 'thanksgiving', 'text' => 'Thanksgiving & praise'],
                    ['value' => 'other_pr', 'text' => 'Other'],
                ], true),
                $this->textareaField('prayer_request_text', 'Your Prayer Request', true),
                $this->selectField('share_permission', 'Who May See This Request?', [
                    ['value' => 'team_only', 'text' => 'Pastoral team only'],
                    ['value' => 'congregation_share', 'text' => 'Share with the congregation'],
                    ['value' => 'fully_private', 'text' => 'Keep completely private'],
                ], true),
                $this->selectField('follow_up_wanted', 'Would you like someone to reach out?', [
                    ['value' => 'yes_followup', 'text' => 'Yes, please'],
                    ['value' => 'no_followup', 'text' => 'No thank you'],
                ], true),
                $this->selectField('contact_preference', 'Best Way to Reach You', [
                    ['value' => 'call_me', 'text' => 'Phone call'],
                    ['value' => 'text_me', 'text' => 'Text message'],
                    ['value' => 'email_me', 'text' => 'Email'],
                ], false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('follow_up_wanted', 'select', 'equals', 'Yes, please')], 'and', true),
                ]),
                $this->phoneField('followup_phone', 'Phone Number for Follow-Up', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('follow_up_wanted', 'select', 'equals', 'Yes, please')], 'and', true),
                ]),
            ], '#052e16'),
        ];
    }

    private function openHouseSignIn(): array
    {
        return [
            'name' => 'Open House Sign-In Sheet Template',
            'slug' => 'open-house-sign-in-form-template',
            'short_description' => 'An open house sign-in sheet template for listing agents capturing buyer contacts, agent status, and financing readiness.',
            'description' => '<p>Our Open House Sign-In Sheet Template replaces the clipboard with a QR-code form that captures qualified buyer context: representation status, financing stage, and genuine interest level.</p><h2>Why and when to use an open house sign-in form</h2><p>Paper sign-in sheets produce illegible names and zero qualification context. A digital sign-in captures clean contact details plus the signals agents actually work with: do they have an agent, are they financed, how soon are they buying. Follow-ups start Sunday evening instead of never.</p><h2>Who is this template for</h2><p>Listings agents running open houses, brokerages standardizing lead capture, new-build sales offices, and FSBO sellers wanting professional intake.</p><h2>Why SharaForms is the best tool for this form</h2><p>Buyer-agent status routes co-broke communication correctly, financing answers separate ready buyers from browsers, and every sign-in lands in your CRM-ready export before you lock the door.</p>',
            'types' => ['signup_forms', 'lead_generation_forms'],
            'industries' => ['real_estate_forms'],
            'structure' => $this->structure('Welcome! Please Sign In', [
                $this->nfText('intro', '<h2>Welcome In!</h2><p>Sign in so we can answer questions and send you the disclosure pack. Details stay with the listing agent only.</p>'),
                $this->textField('property_address_visit', 'Property Address (pre-filled by your agent)', true),
                $this->textField('visitor_name', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('agent_status', 'Are you working with an agent?', [
                    ['value' => 'have_agent', 'text' => 'Yes, I have an agent'],
                    ['value' => 'no_agent', 'text' => 'No, I am shopping solo'],
                    ['value' => 'am_agent', 'text' => 'I am the agent'],
                ], true),
                $this->textField('their_agent_name', "Your Agent's Name", false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('agent_status', 'select', 'equals', 'Yes, I have an agent')], 'and', true),
                ]),
                $this->selectField('financing_stage', 'Financing Status', [
                    ['value' => 'pre_approved', 'text' => 'Pre-approved'],
                    ['value' => 'pre_qualified', 'text' => 'Pre-qualified'],
                    ['value' => 'cash_buyer', 'text' => 'Cash buyer'],
                    ['value' => 'not_started', 'text' => 'Haven\'t started yet'],
                ], true),
                $this->textField('lender_name', 'Lender Name', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('financing_stage', 'select', 'equals', 'Pre-approved'),
                        $this->logicCondition('financing_stage', 'select', 'equals', 'Pre-qualified'),
                    ], 'or'),
                ]),
                $this->selectField('buying_timeline', 'When Are You Looking to Buy?', [
                    ['value' => 'asap_buy', 'text' => 'Within 3 months'],
                    ['value' => 'six_months', 'text' => '3-6 months'],
                    ['value' => 'year_out', 'text' => '6-12 months'],
                    ['value' => 'browsing', 'text' => 'Just browsing'],
                ], true),
                $this->textareaField('feedback_note', 'Questions or Thoughts on the Home?'),
                $this->checkboxField('update_optin', 'Send me updates on this listing and similar homes'),
            ], '#172554'),
        ];
    }

    private function hoaArchitecturalRequest(): array
    {
        return [
            'name' => 'HOA Architectural Request Form Template',
            'slug' => 'hoa-architectural-request-form-template',
            'short_description' => 'An HOA architectural review request form template capturing project specs, contractor insurance, and ARC compliance.',
            'description' => '<p>Our HOA Architectural Request Form Template standardizes improvement applications: project specs, materials, timelines, and contractor documentation reviewed against community guidelines.</p><h2>Why and when to use an HOA architectural request form</h2><p>Architectural review committees drown in inconsistent applications missing key details like paint codes or contractor licensing. A structured request captures everything guidelines require up front, so reviews complete in one cycle and approvals create enforceable records.</p><h2>Who is this template for</h2><p>HOA boards and architectural review committees, property management companies, condominium associations, and planned-community administrators.</p><h2>Why SharaForms is the best tool for this form</h2><p>Licensed-contractor projects surface insurance-upload requirements automatically, material descriptions arrive with specifics committees need, and approved requests become searchable precedent for future applications.</p>',
            'types' => ['request_forms', 'application_forms'],
            'industries' => ['real_estate_forms', 'business_forms'],
            'structure' => $this->structure('Architectural Review Request', [
                $this->nfText('intro', '<h2>ARC Improvement Application</h2><p>The committee meets monthly; submit at least 10 days before the meeting date. Work may not begin before written approval.</p>'),
                $this->textField('homeowner_name_arc', 'Homeowner Name', true),
                $this->textField('property_address_arc', 'Property Address', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('project_type_arc', 'Project Type', [
                    ['value' => 'ext_paint', 'text' => 'Exterior paint'],
                    ['value' => 'roofing_arc', 'text' => 'Roofing'],
                    ['value' => 'fencing_arc', 'text' => 'Fencing'],
                    ['value' => 'landscaping_arc', 'text' => 'Landscaping / hardscaping'],
                    ['value' => 'windows_doors', 'text' => 'Windows & doors'],
                    ['value' => 'solar_arc', 'text' => 'Solar panels'],
                    ['value' => 'other_arc', 'text' => 'Other'],
                ], true),
                $this->textareaField('project_scope', 'Project Description & Scope', true),
                $this->textareaField('materials_colors', 'Materials & Colors (include manufacturer paint codes where applicable)', true),
                $this->dateField('planned_start', 'Planned Start Date', true),
                $this->numberField('duration_weeks', 'Estimated Duration (weeks)', true),
                $this->selectField('contractor_type', 'Who Is Doing the Work?', [
                    ['value' => 'licensed_contractor', 'text' => 'Licensed contractor'],
                    ['value' => 'homeowner_diy', 'text' => 'Homeowner (self-performed)'],
                ], true),
                ['id' => 'contractor_insurance', 'type' => 'files', 'title' => 'Contractor License & Insurance Certificate', 'required' => false, 'help' => 'Required for contractor-performed work', 'max_file_size' => 10, 'max_number_of_files' => 2,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('contractor_type', 'select', 'equals', 'Licensed contractor')], 'and', true)],
                $this->checkboxField('guidelines_acknowledgment', 'I have reviewed the community architectural guidelines and will comply with all decisions', true),
            ], '#1e293b'),
        ];
    }

    private function guestPostPitch(): array
    {
        return [
            'name' => 'Guest Post Pitch Form Template',
            'slug' => 'guest-post-pitch-form-template',
            'short_description' => 'A guest post pitch form template screening article submissions by topic fit, samples, and audience reach.',
            'description' => '<p>Our Guest Post Pitch Form Template screens content contributions consistently: proposed titles, outlines, writing samples, and audience reach, so editors evaluate pitches instead of chasing details.</p><h2>Why and when to use a guest post pitch form</h2><p>"Can I write for you?" emails bury the two facts editors need: what will you write, and can you actually write? A structured pitch captures the proposed angle, proof of past work, and author reach together, making accept/reject decisions fast and fair.</p><h2>Who is this template for</h2><p>Blogs accepting contributors, industry publications, SaaS content teams scaling thought leadership, and newsletter operators trading guest issues.</p><h2>Why SharaForms is the best tool for this form</h2><p>Pitches arrive pre-structured against your editorial criteria, sample links let quality speak before calls, and originality acknowledgments protect your domain from AI-spam submissions.</p>',
            'types' => ['content_forms', 'application_forms'],
            'industries' => ['marketing_forms', 'seo_forms'],
            'structure' => $this->structure('Pitch Your Article', [
                $this->nfText('intro', '<h2>Write for Our Blog</h2><p>We publish practical, original guides our readers can act on. Pitch your angle below; we reply to every serious pitch within two weeks.</p>'),
                $this->textField('author_name_gp', 'Your Name', true),
                $this->emailField('email'),
                $this->urlField('website_url_gp', 'Website / Portfolio URL', true),
                $this->textField('proposed_title', 'Proposed Article Title', true),
                $this->textareaField('topic_outline', 'Outline or Key Points (3-5 bullets)', true),
                $this->urlField('writing_sample_one', 'Link to Your Best Published Piece', true),
                $this->urlField('writing_sample_two', 'Second Sample (optional)'),
                $this->selectField('pitched_before', 'Have you pitched this topic elsewhere?', [
                    ['value' => 'yes_pitched', 'text' => 'Yes'],
                    ['value' => 'no_pitched', 'text' => 'No, exclusive pitch'],
                    ['value' => 'published_already', 'text' => 'A version is already published'],
                ], true),
                $this->urlField('published_link', 'Link to the Published Version', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('pitched_before', 'select', 'equals', 'A version is already published')], 'and', true),
                ]),
                $this->selectField('audience_reach', 'Your Audience Reach (all channels combined)', [
                    ['value' => 'reach_small', 'text' => 'Under 1,000'],
                    ['value' => 'reach_mid', 'text' => '1,000 - 10,000'],
                    ['value' => 'reach_large', 'text' => 'Over 10,000'],
                ], true),
                $this->checkboxField('originality_acknowledgment', 'I confirm this submission will be original, human-written work not published elsewhere', true),
            ], '#7f1d1d'),
        ];
    }

    private function employeeOfTheMonthNomination(): array
    {
        return [
            'name' => 'Employee of the Month Nomination Form Template',
            'slug' => 'employee-of-the-month-nomination-form-template',
            'short_description' => 'An employee of the month nomination form template with categories, specific achievements, and anonymous options.',
            'description' => '<p>Our Employee of the Month Nomination Form Template turns vague praise into award-worthy evidence: category-tagged nominations with specific achievements peers can cite.</p><h2>Why and when to use an employee nomination form</h2><p>Recognition programs fade when nominations feel like homework. A focused form lowers friction: pick a colleague, choose a category, describe one specific moment they shined. Anonymous options surface recognition for colleagues who might hesitate to self-advocate.</p><h2>Who is this template for</h2><p>HR teams running recognition programs, managers building team morale rituals, schools honoring staff members, and frontline operations celebrating shift excellence.</p><h2>Why SharaForms is the best tool for this form</h2><p>Nominations arrive categorized for committee review, achievement descriptions give award announcements real stories, and anonymous submissions keep the door open for every voice.</p>',
            'types' => ['award_forms', 'employment_forms'],
            'industries' => ['human_resources_forms', 'business_forms'],
            'structure' => $this->structure('Employee of the Month Nomination', [
                $this->nfText('intro', '<h2>Nominate a Standout Colleague</h2><p>Great work deserves more than a hallway thank-you. Tell us about a moment when someone on your team went above and beyond.</p>'),
                $this->selectField('nomination_anonymity', 'Nomination Preference', [
                    ['value' => 'anonymous_nom', 'text' => 'Keep my nomination anonymous'],
                    ['value' => 'named_nom', 'text' => 'Include my name'],
                ], true),
                $this->textField('nominator_name', 'Your Name', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('nomination_anonymity', 'select', 'equals', 'Include my name')], 'and', true),
                ]),
                $this->textField('nominee_name_eom', "Nominee's Full Name", true),
                $this->selectField('nominee_department', "Nominee's Department", [
                    ['value' => 'dept_ops_eom', 'text' => 'Operations'],
                    ['value' => 'dept_sales_eom', 'text' => 'Sales'],
                    ['value' => 'dept_support_eom', 'text' => 'Customer Support'],
                    ['value' => 'dept_admin_eom', 'text' => 'Administration'],
                    ['value' => 'dept_prod_eom', 'text' => 'Production / Warehouse'],
                    ['value' => 'dept_other_eom', 'text' => 'Other'],
                ], true),
                $this->selectField('award_month', 'Award Month', [
                    ['value' => 'sep_26', 'text' => 'September 2026'],
                    ['value' => 'oct_26', 'text' => 'October 2026'],
                    ['value' => 'nov_26', 'text' => 'November 2026'],
                    ['value' => 'dec_26', 'text' => 'December 2026'],
                ], true),
                $this->selectField('nomination_category', 'Nomination Category', [
                    ['value' => 'customer_hero', 'text' => 'Customer hero'],
                    ['value' => 'team_player', 'text' => 'Team player'],
                    ['value' => 'innovation_cat', 'text' => 'Innovation & improvement'],
                    ['value' => 'safety_champ', 'text' => 'Safety champion'],
                    ['value' => 'leadership_cat', 'text' => 'Quiet leadership'],
                ], true),
                $this->textareaField('achievement_description', 'Describe the Specific Achievement (what happened, when, and the difference it made)', true),
                $this->textareaField('supporting_quote', 'Optional Quote or Shout-Out to Include in the Announcement'),
            ], '#064e3b'),
        ];
    }

    private function potluckSignupSheet(): array
    {
        return [
            'name' => 'Potluck Signup Sheet Template',
            'slug' => 'potluck-signup-sheet-form-template',
            'short_description' => 'A potluck signup sheet template balancing dish categories, dietary tags, servings, and kitchen logistics.',
            'description' => '<p>Our Potluck Signup Sheet Template coordinates group meals without the spreadsheet chaos: dish categories balance automatically visible, servings count toward headcounts, and dietary tags keep every eater safe.</p><h2>Why and when to use a potluck signup sheet</h2><td>Office potlucks end up as nine desserts and no main course because nobody saw the list. Category-aware signups show what is covered and what is missing, serving counts feed table math, and allergen tags prevent the awkward discovery mid-bite.</p></td><h2>Who is this template for</h2><p>Workplace social committees, church community meals, classroom party parents, club gatherings, and neighborhood associations.</p><h2>Why SharaForms is the best tool for this form</h2><p>Dietary tags roll into one shopping-safe summary, reheating answers tell organizers whether the office kitchen needs booking, and exports become the day-of serving table plan.</p>',
            'types' => ['signup_forms', 'volunteer_forms'],
            'industries' => ['business_forms', 'church_forms'],
            'structure' => $this->structure('Potluck Sign-Up', [
                $this->nfText('intro', '<h2>Add Your Dish to the Table</h2><p>Claim your category below so we get a balanced spread. Label ingredients on the day; future diners thank you.</p>'),
                $this->textField('participant_name_pot', 'Your Name', true),
                $this->emailField('email'),
                $this->selectField('dish_category_pot', 'Dish Category', [
                    ['value' => 'main_dish', 'text' => 'Main dish'],
                    ['value' => 'side_salad_pot', 'text' => 'Side or salad'],
                    ['value' => 'appetizer_pot', 'text' => 'Appetizer'],
                    ['value' => 'dessert_pot', 'text' => 'Dessert'],
                    ['value' => 'drinks_pot', 'text' => 'Drinks'],
                    ['value' => 'supplies_pot', 'text' => 'Paper goods & supplies'],
                ], true),
                $this->textField('dish_name_pot', 'Dish Name', true),
                $this->numberField('servings_count', 'Servings It Feeds', true),
                $this->multiSelectField('dietary_tags', 'Dietary Tags', [
                    ['value' => 'vegetarian_pot', 'text' => 'Vegetarian'],
                    ['value' => 'vegan_pot', 'text' => 'Vegan'],
                    ['value' => 'gluten_free_pot', 'text' => 'Gluten-free'],
                    ['value' => 'nut_free_pot', 'text' => 'Nut-free'],
                    ['value' => 'contains_nuts', 'text' => 'Contains nuts'],
                    ['value' => 'contains_dairy', 'text' => 'Contains dairy'],
                ]),
                $this->selectField('needs_reheating', 'Does your dish need reheating?', [
                    ['value' => 'yes_heat', 'text' => 'Yes, oven or microwave needed'],
                    ['value' => 'no_heat', 'text' => 'No, served as-is'],
                ], true),
                $this->textField('reheat_details', 'Reheating Needs (temp, time, equipment)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('needs_reheating', 'select', 'equals', 'Yes, oven or microwave needed')], 'and', true),
                ]),
                $this->selectField('utensil_plan', 'Serving Utensils', [
                    ['value' => 'bringing_own', 'text' => 'Bringing my own'],
                    ['value' => 'need_provided', 'text' => 'Please provide'],
                ], true),
                $this->textareaField('recipe_notes', 'Notes (allergen details, recipe source, serving suggestions)'),
            ], '#3f6212'),
        ];
    }

    private function fosterAnimalApplication(): array
    {
        return [
            'name' => 'Animal Foster Application Form Template',
            'slug' => 'foster-animal-application-form-template',
            'short_description' => 'An animal foster application form template for shelters vetting homes by pets, residence, hours alone, and experience.',
            'description' => '<p>Our Animal Foster Application Form Template helps shelters place animals into vetted foster homes faster: household readiness, existing pets, landlord permissions, and daily availability captured per applicant.</p><h2>Why and when to use a foster application form</h2><p>Foster programs live and die on placement speed matched with placement safety. A structured application surfaces deal-breakers early: unvaccinated resident pets, landlords who never approved, or households gone ten hours daily. Volunteers screen applications instead of playing phone tag.</p><h2>Who is this template for</h2><p>Animal shelters and rescues, breed-specific foster networks, municipal shelter volunteer programs, and TNR cat colonies placing barn cats.</p><h2>Why SharaForms is the best tool for this form</h2><p>Renters document landlord approval before home visits, current-pet details flag introduction risks, and approved fosters export into your placement tracker with complete household profiles.</p>',
            'types' => ['application_forms', 'volunteer_forms'],
            'industries' => ['animal_shelter_forms'],
            'structure' => $this->structure('Foster Home Application', [
                $this->nfText('intro', '<h2>Open Your Home to an Animal in Need</h2><p>Fosters save lives by freeing shelter space. Applications are reviewed within one week; supplies and medical care are covered by us.</p>'),
                $this->textField('applicant_name_foster', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textareaField('home_address_foster', 'Home Address', true),
                $this->multiSelectField('foster_interest', 'Who Could You Foster?', [
                    ['value' => 'adult_dogs', 'text' => 'Adult dogs'],
                    ['value' => 'puppies', 'text' => 'Puppies'],
                    ['value' => 'adult_cats', 'text' => 'Adult cats'],
                    ['value' => 'kittens_fos', 'text' => 'Kittens'],
                    ['value' => 'special_needs', 'text' => 'Special-needs animals'],
                    ['value' => 'any_animal', 'text' => 'Open to any placement'],
                ], true),
                $this->numberField('household_adults', 'Adults in Household', true),
                $this->numberField('household_children', 'Children in Household (and ages)', false),
                $this->selectField('current_pets_foster', 'Do you have pets at home now?', [
                    ['value' => 'pets_yes', 'text' => 'Yes'],
                    ['value' => 'pets_no', 'text' => 'No'],
                ], true),
                $this->textareaField('pets_description', 'Current Pets (type, age, temperament, vaccinated?)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('current_pets_foster', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('residence_status', 'Do you own or rent?', [
                    ['value' => 'own_home', 'text' => 'Own'],
                    ['value' => 'rent_home', 'text' => 'Rent'],
                ], true),
                $this->textField('landlord_approval', "Landlord Name & Confirmation Pets Are Allowed", false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('residence_status', 'select', 'equals', 'Rent')], 'and', true),
                ]),
                $this->numberField('hours_alone_daily', 'Hours the Animal Would Be Alone Daily', true),
                $this->textareaField('animal_experience', 'Relevant Experience with Animals'),
                $this->checkboxField('care_commitment', 'I commit to providing safe care, transport to vet visits, and honest updates', true),
            ], '#0c4a6e'),
        ];
    }

    private function moveInMoveOutInspection(): array
    {
        return [
            'name' => 'Move-In / Move-Out Inspection Form Template',
            'slug' => 'move-in-move-out-inspection-form-template',
            'short_description' => 'A move-in move-out inspection form template for landlords documenting property condition, damage, and deposit deductions.',
            'description' => '<p>Our Move-In Move-Out Inspection Form Template walks properties room by room, documenting condition with photos and computing deposit deductions transparently.</p><h2>Why and when to use a move-out inspection form</h2><p>Deposit disputes start when condition records are memory instead of evidence. A structured walkthrough captures each room\'s state with timestamps and photos at both ends of a tenancy, making every deduction explainable and every fair return fast.</p><h2>Who is this template for</h2><p>Landlords and property managers, apartment complexes, student housing offices, vacation rental hosts, and tenants protecting their own deposits.</p><h2>Why SharaForms is the best tool for this form</h2><p>Damaged items trigger descriptions and photo uploads automatically, deduction totals compute from documented costs rather than gut feeling, and completed inspections export into tenant files permanently.</p>',
            'types' => ['inspection_forms', 'checklist_forms'],
            'industries' => ['real_estate_forms', 'business_forms'],
            'structure' => $this->structure('Property Condition Inspection', [
                $this->nfText('intro', '<h2>Move-In / Move-Out Inspection</h2><p>Complete one form per unit. Attach photos for anything damaged or exceptionally clean; your future self will thank you.</p>'),
                $this->textField('property_address_insp', 'Property Address & Unit', true),
                $this->textField('tenant_name_insp', 'Tenant Name', true),
                $this->dateField('inspection_date', 'Inspection Date', true),
                $this->selectField('inspection_type', 'Inspection Type', [
                    ['value' => 'move_in', 'text' => 'Move-in (before tenancy)'],
                    ['value' => 'move_out', 'text' => 'Move-out (after tenancy)'],
                ], true),
                $this->selectField('inspector_role', 'Completed By', [
                    ['value' => 'landlord_insp', 'text' => 'Landlord / Property manager'],
                    ['value' => 'tenant_insp', 'text' => 'Tenant'],
                    ['value' => 'joint_insp', 'text' => 'Both parties together'],
                ], true),
                $this->selectField('overall_condition', 'Overall Condition', [
                    ['value' => 'excellent_cond', 'text' => 'Excellent'],
                    ['value' => 'good_cond', 'text' => 'Good, normal wear'],
                    ['value' => 'issues_cond', 'text' => 'Issues found'],
                ], true),
                $this->textareaField('damage_description', 'Describe Each Issue (room, item, severity)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('overall_condition', 'select', 'equals', 'Issues found')], 'and', true),
                ]),
                ['id' => 'damage_photos', 'type' => 'files', 'title' => 'Photos of Issues', 'required' => false, 'help' => 'Required whenever issues are recorded', 'max_file_size' => 10, 'max_number_of_files' => 10,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('overall_condition', 'select', 'equals', 'Issues found')], 'and', true)],
                $this->numberField('damage_cost', 'Estimated Repair Cost ($)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('overall_condition', 'select', 'equals', 'Issues found')]),
                ]),
                $this->numberField('cleaning_cost', 'Additional Cleaning Cost ($)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('overall_condition', 'select', 'equals', 'Issues found')]),
                ]),
                $this->numberField('keys_returned', 'Keys / Fobs Returned (count)', false),
                $this->textareaField('meter_readings', 'Utility Meter Readings (electric, gas, water)'),
                $this->checkboxField('both_acknowledge', 'Both parties acknowledge this inspection record is accurate', true),
                $this->totalBlock('deduction_total_display', 'cv_deposit_deduction', 'Estimated Deductions', '$0'),
            ], '#7f1d1d', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_deposit_deduction',
                        'Estimated Deductions',
                        'IFBLANK({damage_cost},0)+IFBLANK({cleaning_cost},0)'
                    ),
                ],
            ]),
        ];
    }

    private function minorTravelConsent(): array
    {
        return [
            'name' => 'Minor Travel Consent Form Template',
            'slug' => 'minor-travel-consent-form-template',
            'short_description' => 'A child travel consent form template documenting parental permission, accompanying adults, and itinerary for border officials.',
            'description' => '<p>Our Minor Travel Consent Form Template documents permission when a child travels without one or both parents: accompanying adults, itinerary, contactability, and notarization-ready details.</p><h2>Why and when to use a travel consent form</h2><p>Airlines and border officials increasingly require written parental consent for children traveling alone, with one parent, or with groups. A signed consent letter prevents heartbreaking departure-gate refusals and gives guardians the documentation carriers actually ask for.</p><h2>Who is this template for</h2><p>Parents arranging solo travel for children, grandparents taking grandchildren abroad, school trip coordinators, sports teams traveling to tournaments, and exchange program organizers.</p><h2>Why SharaForms is the best tool for this form</h2><p>The absent-parent section captures their explicit consent separately, trip itineraries stay attached to the authorization, and completed forms print cleanly for notarization where destinations require stamped originals.</p>',
            'types' => ['consent_forms', 'legal_forms'],
            'industries' => ['business_forms'],
            'structure' => $this->structure('Child Travel Consent', [
                $this->nfText('intro', '<h2>Travel Consent for a Minor</h2><p>Complete one form per child per trip. Print and sign the confirmation; many destinations additionally require notarization.</p>'),
                $this->textField('child_full_name_tc', 'Child Full Name', true),
                $this->dateField('child_dob_tc', 'Child Date of Birth', true),
                $this->textField('passport_number_tc', 'Passport Number', false),
                $this->textField('parent_one_name', 'Parent / Legal Guardian 1 Name', true),
                $this->phoneField('parent_one_phone', 'Parent 1 Contact Phone', true),
                $this->emailField('parent_one_email', 'Parent 1 Email', true),
                $this->textField('parent_two_name', 'Other Parent / Guardian 2 Name'),
                $this->selectField('travel_scenario', 'Who Is the Child Traveling With?', [
                    ['value' => 'one_parent', 'text' => 'One parent only'],
                    ['value' => 'guardian_third', 'text' => 'An adult who is not a parent'],
                    ['value' => 'alone_minor', 'text' => 'As an unaccompanied minor'],
                    ['value' => 'group_travel', 'text' => 'With a group (school, team, program)'],
                ], true),
                $this->textField('accompanying_adult', 'Accompanying Adult Name & Relationship', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('travel_scenario', 'select', 'equals', 'One parent only'),
                        $this->logicCondition('travel_scenario', 'select', 'equals', 'An adult who is not a parent'),
                    ], 'or', true),
                ]),
                $this->selectField('absent_parent_consent', 'Does the non-traveling parent consent to this trip?', [
                    ['value' => 'consent_yes', 'text' => 'Yes, consent is given'],
                    ['value' => 'consent_na', 'text' => 'Sole custody / not applicable'],
                    ['value' => 'consent_pending', 'text' => 'Consent being arranged separately'],
                ], true),
                $this->textareaField('trip_itinerary', 'Trip Itinerary (destinations, dates, flights if known)', true),
                $this->dateField('departure_date_tc', 'Departure Date', true),
                $this->dateField('return_date_tc', 'Expected Return Date', true),
                $this->textareaField('emergency_contacts_tc', 'Emergency Contacts During Travel', true),
                $this->checkboxField('medical_consent_travel', 'The accompanying adult may authorize routine medical care if I cannot be reached', true),
                $this->checkboxField('accuracy_declaration', 'I declare I am the parent or legal guardian and the information above is accurate', true),
            ], '#1e3a5f'),
        ];
    }

    private function vaccinationConsent(): array
    {
        return [
            'name' => 'Vaccination Consent Form Template',
            'slug' => 'vaccination-consent-form-template',
            'short_description' => 'A vaccination consent form template for clinics and school programs covering eligibility, reactions history, and guardian approval.',
            'description' => '<p>Our Vaccination Consent Form Template documents informed consent for flu clinics, school immunization programs, and workplace health drives: eligibility screening, reaction history, and guardian approval for minors.</p><h2>Why and when to use a vaccination consent form</h2><p>No vaccination should happen without documented informed consent. Structured forms screen contraindications consistently, capture previous adverse reactions that alter clinical decisions, and give school clinics the guardian authority they legally need before any injection.</p><h2>Who is this template for</h2><p>Public health clinics, school-based immunization programs, workplace flu-shot drives, pharmacies offering walk-in vaccines, and travel medicine practices.</p><h2>Why SharaForms is the best tool for this form</h2><p>Minor patients trigger mandatory guardian sections automatically, adverse-reaction histories surface before administration, and batch exports give clinic coordinators exact dose counts to order.</p>',
            'types' => ['consent_forms'],
            'industries' => ['healthcare_forms', 'education_forms'],
            'structure' => $this->structure('Vaccination Consent', [
                $this->nfText('intro', '<h2>Vaccination Consent Form</h2><p>Please answer honestly; your answers keep vaccination safe. Information is shared only with the administering clinical team.</p>'),
                $this->textField('patient_name_vax', 'Patient Full Name', true),
                $this->dateField('patient_dob_vax', 'Patient Date of Birth', true),
                $this->multiSelectField('vaccines_requested', 'Vaccines Being Requested', [
                    ['value' => 'flu_vax', 'text' => 'Influenza (flu)'],
                    ['value' => 'covid_vax', 'text' => 'COVID-19'],
                    ['value' => 'tdap_vax', 'text' => 'Tdap booster'],
                    ['value' => 'hep_b_vax', 'text' => 'Hepatitis B'],
                    ['value' => 'mmr_vax', 'text' => 'MMR'],
                ], true),
                $this->selectField('is_minor_vax', 'Is the patient under 18?', [
                    ['value' => 'adult_vax', 'text' => 'No, 18 or older'],
                    ['value' => 'minor_vax', 'text' => 'Yes, a guardian must complete sections below'],
                ], true),
                $this->textField('guardian_name_vax', 'Parent / Legal Guardian Name', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('is_minor_vax', 'select', 'equals', 'Yes, a guardian must complete sections below')], 'and', true),
                ]),
                $this->phoneField('guardian_phone_vax', 'Guardian Contact Number', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('is_minor_vax', 'select', 'equals', 'Yes, a guardian must complete sections below')], 'and', true),
                ]),
                $this->selectField('prior_reaction', 'Any previous reaction to a vaccine?', [
                    ['value' => 'reaction_no', 'text' => 'No'],
                    ['value' => 'reaction_yes', 'text' => 'Yes'],
                ], true),
                $this->textareaField('reaction_details', 'Describe the Previous Reaction (vaccine and what happened)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('prior_reaction', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('currently_unwell', 'Is the patient unwell today (fever, infection)?', [
                    ['value' => 'unwell_no', 'text' => 'No'],
                    ['value' => 'unwell_yes', 'text' => 'Yes'],
                ], true),
                $this->textareaField('health_conditions_vax', 'Relevant Conditions (immune suppression, pregnancy, bleeding disorders)'),
                $this->checkboxField('consent_administer', 'I consent to the listed vaccinations being administered and understand common side effects', true),
            ], '#155e75'),
        ];
    }

    private function resignationNotice(): array
    {
        return [
            'name' => 'Resignation Notice Form Template',
            'slug' => 'resignation-notice-form-template',
            'short_description' => 'A resignation notice form template standardizing departures with last-day dates, reason categories, and handover plans.',
            'description' => '<p>Our Resignation Notice Form Template turns resignations into orderly departures: formal notice dates, last working days, structured reasons, and knowledge-transfer commitments in one record.</p><h2>Why and when to use a resignation form</h2><p>Resignations handled by hallway conversation leave HR reconstructing facts later. A notice form fixes the official last day, documents the stated reason while feelings are known, and triggers handover planning before knowledge walks out the door.</p><h2>Who is this template for</h2><p>HR departments, managers receiving notices, small businesses without formal HR staff, and employees wanting clean professional documentation of their departure.</p><h2>Why SharaForms is the best tool for this form</h2><p>Notice periods validate automatically against submission dates, reason categories feed honest retention analytics, and exit workflows (IT, payroll, equipment) get triggered from one timestamped record.</p>',
            'types' => ['employment_forms', 'report_forms'],
            'industries' => ['human_resources_forms'],
            'structure' => $this->structure('Resignation Notice', [
                $this->nfText('intro', '<h2>Formal Notice of Resignation</h2><p>Submitting this form starts our offboarding process. We are grateful for your contribution and wish you well.</p>'),
                $this->textField('employee_name_res', 'Employee Name', true),
                $this->textField('employee_id_res', 'Employee ID', true),
                $this->selectField('department_res', 'Department', [
                    ['value' => 'dept_ops_res', 'text' => 'Operations'],
                    ['value' => 'dept_sales_res', 'text' => 'Sales'],
                    ['value' => 'dept_support_res', 'text' => 'Customer Support'],
                    ['value' => 'dept_tech_res', 'text' => 'Technology'],
                    ['value' => 'dept_admin_res', 'text' => 'Administration'],
                    ['value' => 'dept_other_res', 'text' => 'Other'],
                ], true),
                $this->dateField('notice_date', 'Date of This Notice', true),
                $this->dateField('last_working_day', 'Intended Last Working Day', true),
                $this->selectField('reason_category', 'Primary Reason for Leaving', [
                    ['value' => 'new_opportunity', 'text' => 'New opportunity elsewhere'],
                    ['value' => 'personal_reasons', 'text' => 'Personal or family reasons'],
                    ['value' => 'relocation_res', 'text' => 'Relocation'],
                    ['value' => 'study_res', 'text' => 'Returning to education'],
                    ['value' => 'retirement_res', 'text' => 'Retirement'],
                    ['value' => 'other_resign', 'text' => 'Prefer not to specify / other'],
                ], true),
                $this->textField('study_details_res', 'Program & Institution (so we can celebrate and stay in touch)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('reason_category', 'select', 'equals', 'Returning to education')]),
                ]),
                $this->textareaField('resignation_comments', 'Anything You Would Like to Share About Your Decision (optional)'),
                $this->selectField('handover_documented', 'Have you started documenting your responsibilities?', [
                    ['value' => 'handover_yes', 'text' => 'Yes, handover notes in progress'],
                    ['value' => 'handover_will', 'text' => 'Will begin within the notice period'],
                ], true),
                $this->textField('knowledge_transfer_to', 'Colleague Proposed to Receive Handover'),
                $this->checkboxField('return_confirmation', 'I understand company property must be returned on or before my last day', true),
                $this->checkboxField('rehire_interest', 'I would consider returning in the future if circumstances change'),
            ], '#450a0a'),
        ];
    }

    private function offerAcceptance(): array
    {
        return [
            'name' => 'Offer Acceptance Form Template',
            'slug' => 'offer-acceptance-form-template',
            'short_description' => 'An offer acceptance form template confirming employment terms, start dates, and document submissions for new hires.',
            'description' => '<p>Our Offer Acceptance Form Template closes hiring loops cleanly: candidates accept or negotiate terms formally, confirm start dates, and learn exactly which documents bring them to day one.</p><h2>Why and when to use an offer acceptance form</h2><p>Verbal acceptances evaporate; counteroffers arrive by email threads nobody can audit. A formal acceptance form documents agreement to role, compensation band, and start date simultaneously, while declining candidates surface real objections recruiting teams can learn from.</p><h2>Who is this template for</h2><p>Recruiting teams and HR departments, small businesses extending first offers, staffing agencies confirming placements, and contractors formalizing engagements.</p><h2>Why SharaForms is the best tool for this form</h2><p>Negotiation requests reveal what specifically needs discussing, declines capture reasons for pipeline analytics, and accepted offers flow straight into onboarding checklists with confirmed start dates attached.</p>',
            'types' => ['employment_forms'],
            'industries' => ['human_resources_forms'],
            'structure' => $this->structure('Offer Response', [
                $this->nfText('intro', '<h2>Your Offer Response</h2><p>Congratulations on your offer! Confirm below, or tell us what needs adjusting before you sign.</p>'),
                $this->textField('candidate_name_oa', 'Full Legal Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textField('position_offered', 'Position Offered', true),
                $this->selectField('offer_response', 'Your Response', [
                    ['value' => 'accept_offer', 'text' => 'I accept the offer as presented'],
                    ['value' => 'negotiate_offer', 'text' => 'I would like to discuss terms first'],
                    ['value' => 'decline_offer', 'text' => 'I am declining the offer'],
                ], true),
                $this->selectField('negotiation_topic', 'Which Terms Would You Like to Discuss?', [
                    ['value' => 'neg_salary', 'text' => 'Compensation'],
                    ['value' => 'neg_start', 'text' => 'Start date'],
                    ['value' => 'neg_remote', 'text' => 'Work arrangement'],
                    ['value' => 'neg_benefits', 'text' => 'Benefits / PTO'],
                    ['value' => 'neg_other', 'text' => 'Something else'],
                ], false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('offer_response', 'select', 'equals', 'I would like to discuss terms first')], 'and', true),
                ]),
                $this->textareaField('negotiation_notes', 'What Would You Like to Adjust?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('offer_response', 'select', 'equals', 'I would like to discuss terms first')], 'and', true),
                ]),
                $this->selectField('decline_reason_oa', 'Main Reason for Declining', [
                    ['value' => 'decl_comp', 'text' => 'Compensation'],
                    ['value' => 'decl_other_offer', 'text' => 'Accepted another offer'],
                    ['value' => 'decl_personal', 'text' => 'Personal circumstances'],
                    ['value' => 'decl_role_fit', 'text' => 'Role was not the right fit'],
                    ['value' => 'decl_location', 'text' => 'Location / arrangement'],
                ], false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('offer_response', 'select', 'equals', 'I am declining the offer')], 'and', true),
                ]),
                $this->dateField('confirmed_start_date', 'Confirmed Start Date', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('offer_response', 'select', 'equals', 'I accept the offer as presented')], 'and', true),
                ]),
                $this->selectField('documents_ready_oa', 'Do you have your right-to-work documents ready?', [
                    ['value' => 'docs_ready_oa', 'text' => 'Yes'],
                    ['value' => 'docs_need_time', 'text' => 'Need a few days'],
                ], false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('offer_response', 'select', 'equals', 'I accept the offer as presented')], 'and', true),
                ]),
                $this->checkboxField('terms_confirmed_oa', 'I confirm the role, compensation, and start date stated in my written offer letter', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('offer_response', 'select', 'equals', 'I accept the offer as presented')], 'and', true),
                ]),
            ], '#3f3f46'),
        ];
    }

    private function referenceCheck(): array
    {
        return [
            'name' => 'Reference Check Form Template',
            'slug' => 'reference-check-form-template',
            'short_description' => 'A reference check form template structuring referee feedback on candidates: performance, reliability, and rehire answers.',
            'description' => '<p>Our Reference Check Form Template turns reference calls into comparable data: structured questions about performance, strengths, working relationships, and the rehire question that reveals everything.</p><h2>Why and when to use a reference check form</h2><p>Unstructured reference calls produce anecdotes, not evidence. Asking every referee the same scored questions makes candidates genuinely comparable, protects hiring decisions with documented diligence, and catches hesitation patterns a friendly chat would miss.</p><h2>Who is this template for</h2><p>Hiring managers and recruiters, HR teams standardizing due diligence, small businesses making their first key hires, and volunteer organizations screening trust positions.</p><h2>Why SharaForms is the best tool for this form</h2><p>Referees complete forms on their own schedule instead of phone tag, hesitant rehire answers pair automatically with explanation fields, and completed checks attach cleanly to candidate files.</p>',
            'types' => ['interview_forms', 'evaluation_forms'],
            'industries' => ['human_resources_forms'],
            'structure' => $this->structure('Candidate Reference Check', [
                $this->nfText('intro', '<h2>Reference Check Request</h2><p>You have been listed as a reference; thank you! This takes five minutes and your candid answers genuinely help.</p>'),
                $this->textField('candidate_name_rc', 'Candidate You Are Referencing', true),
                $this->textField('referee_name', 'Your Name', true),
                $this->textField('referee_title_company', 'Your Title & Company', true),
                $this->emailField('email'),
                $this->selectField('relationship_rc', 'Relationship to Candidate', [
                    ['value' => 'direct_manager', 'text' => 'Direct manager'],
                    ['value' => 'coworker_ref', 'text' => 'Coworker / peer'],
                    ['value' => 'direct_report', 'text' => 'Direct report'],
                    ['value' => 'client_ref', 'text' => 'Client or customer'],
                    ['value' => 'academic_ref', 'text' => 'Academic (teacher, advisor)'],
                ], true),
                $this->selectField('working_period', 'How Long Did You Work Together?', [
                    ['value' => 'under_year', 'text' => 'Less than 1 year'],
                    ['value' => 'one_three_years', 'text' => '1-3 years'],
                    ['value' => 'over_three_years', 'text' => 'Over 3 years'],
                ], true),
                ['id' => 'performance_rating', 'type' => 'rating', 'title' => 'Overall performance in the role', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'reliability_rating', 'type' => 'rating', 'title' => 'Reliability and punctuality', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'teamwork_rating', 'type' => 'rating', 'title' => 'Collaboration and teamwork', 'required' => true, 'help' => '', 'steps' => 5],
                $this->textareaField('key_strengths', 'The Two Strengths You Would Highlight Most', true),
                $this->textareaField('growth_area', 'One Area Where They Could Still Grow'),
                $this->selectField('would_rehire', 'Would you hire this person again?', [
                    ['value' => 'rehire_yes', 'text' => 'Yes, without hesitation'],
                    ['value' => 'rehire_probably', 'text' => 'Probably'],
                    ['value' => 'rehire_no', 'text' => 'No'],
                ], true),
                $this->textareaField('rehire_explanation', 'Please Explain Your Answer', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('would_rehire', 'select', 'does_not_equal', 'Yes, without hesitation'),
                        $this->logicCondition('performance_rating', 'rating', 'less_than', 4),
                    ], 'or', true),
                ]),
                $this->checkboxField('accuracy_confirmed_rc', 'I confirm these answers reflect my honest assessment', true),
            ], '#5b21b6'),
        ];
    }

    private function shiftSwap(): array
    {
        return [
            'name' => 'Shift Swap Request Form Template',
            'slug' => 'shift-swap-form-template',
            'short_description' => 'A shift swap request form template for hourly teams coordinating schedule changes with coverage confirmation.',
            'description' => '<p>Our Shift Swap Request Form Template brings order to schedule changes: who swaps with whom, which shifts, why, and whether both parties plus supervision have agreed.</p><h2>Why and when to use a shift swap form</h2><p>Schedule chaos breeds no-shows. Group-chat swap deals collapse when one party forgets, leaving managers short-staffed mid-shift. A logged request documents mutual agreement before the rota changes, protecting coverage math and everyone\'s paychecks.</p><h2>Who is this template for</h2><p>Retail managers, restaurant supervisors, warehouse leads, hospital ward coordinators, call centers, and any team running rotating schedules.</p><h2>Why SharaForms is the best tool for this form</h2><p>Both parties confirm within one submission, skill-mismatch notes surface before approval rather than during shifts, and approved swaps export straight into your scheduling tool.</p>',
            'types' => ['request_forms', 'employment_forms'],
            'industries' => ['human_resources_forms', 'services_forms'],
            'structure' => $this->structure('Shift Swap Request', [
                $this->nfText('intro', '<h2>Request a Shift Swap</h2><p>Both employees must agree before submitting. Supervisors approve by end of day; unapproved swaps count as absences.</p>'),
                $this->textField('requester_name_ss', 'Your Name', true),
                $this->textField('swap_partner_name', 'Swap Partner Name', true),
                $this->dateField('your_shift_date', 'Date of YOUR Shift (giving away)', true),
                $this->selectField('your_shift_time', 'Your Shift Time', [
                    ['value' => 'morning_ss', 'text' => 'Morning'],
                    ['value' => 'afternoon_ss', 'text' => 'Afternoon'],
                    ['value' => 'evening_ss', 'text' => 'Evening'],
                    ['value' => 'overnight_ss', 'text' => 'Overnight'],
                ], true),
                $this->dateField('partner_shift_date', "Date of PARTNER'S Shift (taking)", true),
                $this->selectField('partner_shift_time', "Partner's Shift Time", [
                    ['value' => 'morn_take', 'text' => 'Morning'],
                    ['value' => 'aft_take', 'text' => 'Afternoon'],
                    ['value' => 'eve_take', 'text' => 'Evening'],
                    ['value' => 'night_take', 'text' => 'Overnight'],
                ], true),
                $this->selectField('swap_reason', 'Reason for the Swap', [
                    ['value' => 'appointment_ss', 'text' => 'Personal appointment'],
                    ['value' => 'family_ss', 'text' => 'Family commitment'],
                    ['value' => 'study_ss', 'text' => 'Classes or exams'],
                    ['value' => 'health_ss', 'text' => 'Health reasons'],
                    ['value' => 'other_swap', 'text' => 'Other'],
                ], true),
                $this->textField('swap_reason_detail', 'Briefly Explain (optional unless Other)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('swap_reason', 'select', 'equals', 'Other')], 'and', true),
                ]),
                $this->checkboxField('partner_agrees', 'My swap partner has agreed to this exchange', true),
                $this->checkboxField('skills_equivalent', 'Both of us are trained for the roles covered by these shifts', true),
                $this->checkboxField('supervisor_aware', 'I have flagged this verbally with my supervisor'),
            ], '#713f12'),
        ];
    }

    private function jobRequisition(): array
    {
        return [
            'name' => 'Job Requisition Form Template',
            'slug' => 'job-requisition-form-template',
            'short_description' => 'A job requisition form template for managers requesting headcount with role scope, salary bands, and budget sign-off.',
            'description' => '<p>Our Job Requisition Form Template standardizes how hiring starts: managers justify headcount, define role scope, declare salary bands, and secure budget acknowledgment before recruiting spends a dollar.</p><h2>Why and when to use a job requisition form</h2><p>Hiring without a paper trail creates phantom budgets and mis-scoped roles. A requisition forces the business case into writing: why the role exists, what success looks like, what it costs, and whose budget absorbs it, before job ads go live.</p><h2>Who is this template for</h2><p>HR and talent acquisition teams, department heads requesting growth, finance partners approving spend, and startups formalizing their first structured hires.</p><h2>Why SharaForms is the best tool for this form</h2><p>Replacement versus new-role logic asks different follow-up questions, salary bands arrive declared upfront, and approved requisitions become the sourcing checklist recruiters work from.</p>',
            'types' => ['request_forms', 'employment_forms'],
            'industries' => ['human_resources_forms', 'business_forms'],
            'structure' => $this->structure('Job Requisition Request', [
                $this->nfText('intro', '<h2>New Headcount Request</h2><p>Complete all fields so recruiting can launch without follow-up loops. Approvals typically return within three business days.</p>'),
                $this->textField('requesting_manager_jr', 'Requesting Manager', true),
                $this->selectField('department_jr', 'Department', [
                    ['value' => 'jr_ops', 'text' => 'Operations'],
                    ['value' => 'jr_sales', 'text' => 'Sales'],
                    ['value' => 'jr_marketing_jr', 'text' => 'Marketing'],
                    ['value' => 'jr_tech', 'text' => 'Technology'],
                    ['value' => 'jr_support', 'text' => 'Customer Support'],
                    ['value' => 'jr_finance', 'text' => 'Finance'],
                    ['value' => 'jr_other', 'text' => 'Other'],
                ], true),
                $this->textField('role_title_jr', 'Proposed Job Title', true),
                $this->selectField('req_type', 'Type of Request', [
                    ['value' => 'new_role_jr', 'text' => 'New position (growth)'],
                    ['value' => 'backfill_jr', 'text' => 'Backfill (replacing a departure)'],
                ], true),
                $this->textField('replaced_employee', 'Name of Departing Employee', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('req_type', 'select', 'equals', 'Backfill (replacing a departure)')], 'and', true),
                ]),
                $this->selectField('employment_type_jr', 'Employment Type', [
                    ['value' => 'full_time_jr', 'text' => 'Full-time permanent'],
                    ['value' => 'part_time_jr', 'text' => 'Part-time'],
                    ['value' => 'contract_jr', 'text' => 'Fixed-term contract'],
                    ['value' => 'intern_jr', 'text' => 'Internship'],
                ], true),
                $this->selectField('workplace_jr', 'Work Arrangement', [
                    ['value' => 'onsite_jr', 'text' => 'On-site'],
                    ['value' => 'hybrid_jr', 'text' => 'Hybrid'],
                    ['value' => 'remote_jr', 'text' => 'Fully remote'],
                ], true),
                $this->numberField('salary_min_jr', 'Salary Band Minimum ($)', true),
                $this->numberField('salary_max_jr', 'Salary Band Maximum ($)', true),
                $this->textareaField('business_case', 'Business Case (why this role, what breaks without it)', true),
                $this->textareaField('success_criteria', 'What Must This Person Achieve in Year One?', true),
                $this->dateField('target_start_jr', 'Target Start Date', true),
                $this->checkboxField('budget_confirmed_jr', 'I confirm budget exists for the full band stated above', true),
            ], '#334155'),
        ];
    }

    private function addressChange(): array
    {
        return [
            'name' => 'Address Change Form Template',
            'slug' => 'address-change-form-template',
            'short_description' => 'An address change form template updating customer, member, or patient records with verification and effective dates.',
            'description' => '<p>Our Address Change Form Template keeps records current: identity verification, old and new addresses side by side, effective dates, and mail-forwarding preferences in one auditable update.</p><h2>Why and when to use an address change form</h2><td>Misdelivered statements, failed deliveries, and compliance letters bouncing create cost and risk alike. A structured change process verifies identity before editing master records, captures effective dates precisely, and leaves an audit trail regulators and auditors appreciate.</p></td><h2>Who is this template for</h2><p>Banks and credit unions, insurers, healthcare practices, membership organizations, schools, subscription businesses, and any organization mailing anything important.</p><h2>Why SharaForms is the best tool for this form</h2><p>Identity fields match against existing records before updates apply, effective dates prevent retroactive confusion, and every change exports timestamped into your records history.</p>',
            'types' => ['request_forms'],
            'industries' => ['business_forms'],
            'structure' => $this->structure('Change of Address', [
                $this->nfText('intro', '<h2>Update Your Address</h2><p>Changes apply within two business days of submission. Statements already in print cannot be redirected.</p>'),
                $this->textField('account_holder_name', 'Full Name on Account', true),
                $this->textField('account_number_ac', 'Account / Member Number', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Daytime Phone', true),
                $this->selectField('change_type_ac', 'What Are You Changing?', [
                    ['value' => 'mailing_only', 'text' => 'Mailing address only'],
                    ['value' => 'permanent_move', 'text' => 'Permanent address change'],
                    ['value' => 'temporary_forward', 'text' => 'Temporary forwarding'],
                ], true),
                $this->textareaField('current_address', 'Current Address on File', true),
                $this->textareaField('new_address', 'New Address', true),
                $this->dateField('effective_date_ac', 'Effective Date', true),
                $this->dateField('forward_end_date', 'Forwarding End Date', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('change_type_ac', 'select', 'equals', 'Temporary forwarding')], 'and', true),
                ]),
                $this->selectField('billing_address_same', 'Is your billing address also changing?', [
                    ['value' => 'billing_same', 'text' => 'No, billing stays the same'],
                    ['value' => 'billing_changes', 'text' => 'Yes, same as new address above'],
                    ['value' => 'billing_other', 'text' => 'Yes, different from both'],
                ], true),
                $this->textareaField('other_billing_address', 'Alternate Billing Address', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('billing_address_same', 'select', 'equals', 'Yes, different from both')], 'and', true),
                ]),
                $this->checkboxField('verification_declaration', 'I confirm this information is accurate and I am authorized to update this account', true),
            ], '#374151'),
        ];
    }

    private function callbackRequest(): array
    {
        return [
            'name' => 'Callback Request Form Template',
            'slug' => 'callback-request-form-template',
            'short_description' => 'A callback request form template capturing phone-back requests with topics, time windows, and priority routing.',
            'description' => '<p>Our Callback Request Form Template captures call-me-back requests properly: contact details, topic context, preferred windows, and urgency, so your team returns calls prepared instead of blind.</p><h2>Why and when to use a callback form</h2><p>Nobody enjoys hold music. A callback form converts frustrated phone traffic into structured queue entries with context attached: agents see the topic before dialing, customers choose when their phone rings, and nobody loses a sale to an abandoned hold line.</p><h2>Who is this template for</h2><p>Sales teams handling inbound interest, support desks deflecting hold queues, clinics booking advisory calls, financial advisors, and any business whose phones overflow at peak times.</p><h2>Why SharaForms is the best tool for this form</h2><p>Topic selection routes calls to the right team instantly, time-window choices respect customer schedules, and urgent flags surface genuinely pressing calls at the top of every queue.</p>',
            'types' => ['contact_forms', 'request_forms'],
            'industries' => ['business_forms', 'services_forms', 'customer_service_forms'],
            'structure' => $this->structure('Request a Callback', [
                $this->nfText('intro', '<h2>We Will Call You</h2><p>Skip the hold music. Tell us what you need and when suits; we ring you back inside your window.</p>'),
                $this->textField('caller_name_cb', 'Full Name', true),
                $this->phoneField('phone', 'Phone Number for the Callback', true),
                $this->emailField('email'),
                $this->selectField('callback_topic', 'What Is This About?', [
                    ['value' => 'topic_sales', 'text' => 'Products or pricing'],
                    ['value' => 'topic_support', 'text' => 'Support with an existing order'],
                    ['value' => 'topic_billing', 'text' => 'Billing question'],
                    ['value' => 'topic_account', 'text' => 'My account'],
                    ['value' => 'topic_other_cb', 'text' => 'Something else'],
                ], true),
                $this->textField('topic_other_detail', 'Tell Us Briefly What You Need', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('callback_topic', 'select', 'equals', 'Something else')], 'and', true),
                ]),
                $this->textareaField('context_notes', 'Quick Context So We Come Prepared (optional)'),
                $this->selectField('preferred_window', 'Best Time Window', [
                    ['value' => 'window_morning', 'text' => 'Morning (9 AM - 12 PM)'],
                    ['value' => 'window_afternoon_cb', 'text' => 'Afternoon (12 PM - 4 PM)'],
                    ['value' => 'window_late', 'text' => 'Late afternoon (4 PM - 7 PM)'],
                    ['value' => 'window_anytime', 'text' => 'Anytime today'],
                ], true),
                $this->selectField('urgency_cb', 'How Urgent Is It?', [
                    ['value' => 'urgent_today', 'text' => 'Urgent - please prioritize'],
                    ['value' => 'normal_cb', 'text' => 'Normal - this week is fine'],
                ], true),
                $this->checkboxField('sms_first', 'Send me a text before calling'),
                $this->checkboxField('consent_call_cb', 'I consent to being contacted by phone about this request', true),
            ], '#0f172a'),
        ];
    }

    private function publicRecordsRequest(): array
    {
        return [
            'name' => 'Public Records Request Form Template',
            'slug' => 'public-records-request-form-template',
            'short_description' => 'A public records request form template for agencies handling FOIA-style requests with scope, format, and fee details.',
            'description' => '<p>Our Public Records Request Form Template structures freedom-of-information style requests: precise record descriptions, format preferences, fee waiver considerations, and response tracking that keeps agencies compliant.</p><h2>Why and when to use a public records request form</h2><p>Vague requests stall in legal review while statutory clocks run. Structured intake captures exactly which records, which date ranges, and which format, letting staff locate documents instead of interrogating requesters, and creating the response-timestamp trail compliance requires.</p><h2>Who is this template for</h2><p>Government agencies and municipalities, school districts, police departments with disclosure units, universities, and journalists or citizens filing requests.</p><h2>Why SharaForms is the best tool for this form</h2><p>Fee-waiver claims reveal supporting justification automatically, format choices set production expectations upfront, and timestamped submissions prove statutory-clock start dates definitively.</p>',
            'types' => ['request_forms', 'legal_forms'],
            'industries' => ['business_forms'],
            'structure' => $this->structure('Public Records Request', [
                $this->nfText('intro', '<h2>Public Records Request</h2><p>We respond within the statutory window from receipt. Specific requests get faster answers; describe the records as precisely as you can.</p>'),
                $this->textField('requester_name_prr', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number'),
                $this->textField('organization_prr', 'Organization (if requesting on behalf of one)'),
                $this->textareaField('records_description', 'Describe the Records Requested (be specific: document types, subjects, programs)', true),
                $this->dateField('date_range_start', 'Records Start Date', false),
                $this->dateField('date_range_end', 'Records End Date', false),
                $this->selectField('delivery_format', 'Preferred Format', [
                    ['value' => 'electronic_prr', 'text' => 'Electronic copies (email or download link)'],
                    ['value' => 'paper_prr', 'text' => 'Paper copies (pickup or mail)'],
                    ['value' => 'inspection_prr', 'text' => 'On-site inspection only'],
                ], true),
                $this->textareaField('mailing_address_prr', 'Mailing Address for Paper Copies', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('delivery_format', 'select', 'equals', 'Paper copies (pickup or mail)')], 'and', true),
                ]),
                $this->selectField('fee_waiver_request', 'Are you requesting a fee waiver?', [
                    ['value' => 'waiver_yes', 'text' => 'Yes'],
                    ['value' => 'waiver_no', 'text' => 'No, standard fees accepted'],
                ], true),
                $this->textareaField('waiver_justification', 'Waiver Justification (public interest, journalistic, noncommercial use)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('fee_waiver_request', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->selectField('purpose_category', 'General Purpose (helps us locate relevant records)', [
                    ['value' => 'journalism_prr', 'text' => 'Journalism / media'],
                    ['value' => 'research_prr', 'text' => 'Academic research'],
                    ['value' => 'legal_prr', 'text' => 'Legal proceedings'],
                    ['value' => 'personal_prr', 'text' => 'Personal interest'],
                    ['value' => 'business_prr', 'text' => 'Business purposes'],
                ]),
                $this->checkboxField('fee_responsibility', 'I understand copying costs beyond the free threshold may apply and will be quoted first', true),
            ], '#1c1917'),
        ];
    }

    private function orderStatusInquiry(): array
    {
        return [
            'name' => 'Order Status Inquiry Form Template',
            'slug' => 'order-status-inquiry-form-template',
            'short_description' => 'An order status inquiry form template deflating where-is-my-order tickets with structured lookup details.',
            'description' => '<p>Our Order Status Inquiry Form Template captures where-is-my-order questions completely: order numbers, purchase dates, issue categories, and desired outcomes, so support resolves in one touch.</p><h2>Why and when to use an order status form</h2><p>WISMO tickets dominate e-commerce support volume, and most arrive missing the two facts needed to answer them. Structured inquiry forms collect order numbers and issue types together, letting agents (or automations) respond same-hour instead of trading three emails first.</p><h2>Who is this template for</h2><p>E-commerce support teams, fulfillment operations during peak seasons, subscription box companies, and marketplaces coordinating third-party sellers.</p><h2>Why SharaForms is the best tool for this form</h2><p>Issue categories route each case correctly, damaged-item answers trigger photo requests automatically, and resolution-preference fields tell you what actually closes the ticket happily.</p>',
            'types' => ['tracking_forms', 'request_forms'],
            'industries' => ['ecommerce_forms', 'customer_service_forms'],
            'structure' => $this->structure('Order Status Inquiry', [
                $this->nfText('intro', '<h2>Where Is My Order?</h2><p>Most orders ship within two business days with tracking emailed automatically. Complete this form and our team investigates within four business hours.</p>'),
                $this->textField('customer_name_wismo', 'Full Name', true),
                $this->emailField('email'),
                $this->textField('order_number_wismo', 'Order Number', true),
                $this->dateField('order_date', 'Order Placement Date', true),
                $this->selectField('inquiry_type', 'What Is the Issue?', [
                    ['value' => 'no_update', 'text' => 'No tracking updates yet'],
                    ['value' => 'stuck_transit', 'text' => 'Tracking stuck in transit'],
                    ['value' => 'marked_delivered', 'text' => 'Marked delivered but not received'],
                    ['value' => 'damaged_arrival', 'text' => 'Arrived damaged'],
                    ['value' => 'wrong_item', 'text' => 'Wrong item received'],
                    ['value' => 'part_missing', 'text' => 'Part of my order missing'],
                ], true),
                $this->textareaField('issue_details_wismo', 'Details That Help Us Investigate', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('inquiry_type', 'select', 'equals', 'Marked delivered but not received'),
                        $this->logicCondition('inquiry_type', 'select', 'equals', 'Arrived damaged'),
                        $this->logicCondition('inquiry_type', 'select', 'equals', 'Wrong item received'),
                        $this->logicCondition('inquiry_type', 'select', 'equals', 'Part of my order missing'),
                    ], 'or', true),
                ]),
                ['id' => 'evidence_photos', 'type' => 'files', 'title' => 'Photos (packaging damage, wrong item received)', 'required' => false, 'max_file_size' => 10, 'max_number_of_files' => 4,
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('inquiry_type', 'select', 'equals', 'Arrived damaged'),
                        $this->logicCondition('inquiry_type', 'select', 'equals', 'Wrong item received'),
                    ], 'or')],
                $this->selectField('desired_resolution', 'Preferred Resolution', [
                    ['value' => 'reship_quick', 'text' => 'Reship the item(s)'],
                    ['value' => 'refund_original', 'text' => 'Refund to original payment'],
                    ['value' => 'store_credit_opt', 'text' => 'Store credit (bonus value)'],
                    ['value' => 'just_info', 'text' => 'Just need status information'],
                ], true),
                $this->checkboxField('carrier_contacted', 'I have contacted the carrier about this delivery'),
            ], '#9a3412'),
        ];
    }

    private function warrantyRegistration(): array
    {
        return [
            'name' => 'Warranty Registration Form Template',
            'slug' => 'warranty-registration-form-template',
            'short_description' => 'A product registration form template activating warranties with purchase proof, model details, and owner contacts.',
            'description' => '<p>Our Warranty Registration Form Template activates product coverage at purchase: model and serial capture, purchase-channel logic, owner contacts, and marketing opt-ins kept honestly separate.</p><h2>Why and when to use a product registration form</h2><p>Registered products mean faster warranty service, recall reachability, and honest ownership data. Customers registering at purchase skip receipt-hunting later; manufacturers gain accurate install bases for safety communications and accessory demand planning.</p><h2>Who is this template for</h2><p>Appliance and electronics brands, power-tool manufacturers, furniture makers, bicycle and sporting-goods companies, and any product carrying a written warranty.</p><h2>Why SharaForms is the best tool for this form</h2><p>Purchase-channel questions adapt automatically between online orders and store receipts, serial numbers validate ownership instantly at claim time, and safety recalls reach registered owners first.</p>',
            'types' => ['registration_forms', 'request_forms'],
            'industries' => ['ecommerce_forms', 'business_forms'],
            'structure' => $this->structure('Product Warranty Registration', [
                $this->nfText('intro', '<h2>Activate Your Warranty</h2><p>Registration takes two minutes and speeds up any future service dramatically. Keep your receipt; registration plus receipt equals bulletproof coverage.</p>'),
                $this->textField('owner_name_wr', 'Owner Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textField('product_model_wr', 'Product Model', true),
                $this->textField('serial_number_wr', 'Serial Number', true),
                $this->dateField('purchase_date_wr', 'Purchase Date', true),
                $this->selectField('purchase_channel_wr', 'Where Was It Purchased?', [
                    ['value' => 'wr_online', 'text' => 'Online store (our website)'],
                    ['value' => 'wr_marketplace', 'text' => 'Marketplace (Amazon, eBay, etc.)'],
                    ['value' => 'wr_retail', 'text' => 'Physical retail store'],
                    ['value' => 'wr_gift', 'text' => 'It was a gift'],
                ], true),
                $this->textField('order_number_wr', 'Order Number', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('purchase_channel_wr', 'select', 'equals', 'Online store (our website)')], 'and', true),
                ]),
                $this->textField('retailer_name_wr', 'Retailer / Marketplace Name', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('purchase_channel_wr', 'select', 'equals', 'Marketplace (Amazon, eBay, etc.)'),
                        $this->logicCondition('purchase_channel_wr', 'select', 'equals', 'Physical retail store'),
                    ], 'or', true),
                ]),
                $this->selectField('usage_type_wr', 'Primary Usage', [
                    ['value' => 'personal_use_wr', 'text' => 'Personal / household'],
                    ['value' => 'professional_use', 'text' => 'Professional / commercial'],
                ], true),
                $this->checkboxField('recall_notifications', 'Notify me if a safety recall affects this product'),
                $this->checkboxField('marketing_optin_wr', 'Send me product tips and offers (separate from warranty; optional)'),
            ], '#3f6212'),
        ];
    }

    private function supplierRegistration(): array
    {
        return [
            'name' => 'Supplier Registration Form Template',
            'slug' => 'supplier-registration-form-template',
            'short_description' => 'A supplier registration form template onboarding vendors with compliance docs, capabilities, and payment details.',
            'description' => '<p>Our Supplier Registration Form Template onboards procurement vendors properly: company profiles, capability declarations, compliance documents, and banking details collected once, verified centrally, and reusable across every future engagement.</p><h2>Why and when to use a supplier registration form</h2><p>Procurement without vendor master data drowns in scattered spreadsheets. A structured registration collects tax documentation, insurance certificates, and capability statements together, so compliance checks happen once at onboarding instead of frantically during every new purchase.</p><h2>Who is this template for</h2><p>Procurement teams, construction main contractors managing subcontractor lists, hospitality groups onboarding suppliers across properties, and public-sector organizations with vendor compliance duties.</p><h2>Why SharaForms is the best tool for this form</h2><p>Diversity-certification uploads appear only for qualifying suppliers, insurance expiry dates surface before coverage lapses, and approved vendors export into ERP-ready vendor master files.</p>',
            'types' => ['application_forms'],
            'industries' => ['business_forms', 'banking_forms'],
            'structure' => $this->structure('Supplier Registration', [
                $this->nfText('intro', '<h2>Become a Registered Supplier</h2><p>Complete this profile to join our approved vendor list. Reviews complete within ten business days; approved suppliers receive portal credentials.</p>'),
                $this->textField('company_legal_name_sr', 'Legal Company Name', true),
                $this->textField('trading_name_sr', 'Trading Name (if different)'),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->urlField('website_url_sr', 'Company Website', true),
                $this->dateField('year_established', 'Year Established', true),
                $this->selectField('business_structure_sr', 'Business Structure', [
                    ['value' => 'sr_sole', 'text' => 'Sole proprietorship'],
                    ['value' => 'sr_partnership', 'text' => 'Partnership'],
                    ['value' => 'sr_llc', 'text' => 'LLC / Ltd'],
                    ['value' => 'sr_corp', 'text' => 'Corporation'],
                    ['value' => 'sr_other_struct', 'text' => 'Other'],
                ], true),
                $this->multiSelectField('supply_categories', 'Supply Categories', [
                    ['value' => 'raw_materials', 'text' => 'Raw materials'],
                    ['value' => 'components_sr', 'text' => 'Components & parts'],
                    ['value' => 'it_equipment', 'text' => 'IT & equipment'],
                    ['value' => 'professional_services_sr', 'text' => 'Professional services'],
                    ['value' => 'logistics_sr', 'text' => 'Logistics & freight'],
                    ['value' => 'facilities_sr', 'text' => 'Facilities & maintenance'],
                ], true),
                $this->selectField('certification_status', 'Do you hold diversity certifications (MBE, WBE, etc.)?', [
                    ['value' => 'cert_yes', 'text' => 'Yes'],
                    ['value' => 'cert_no', 'text' => 'No'],
                ], true),
                ['id' => 'certification_upload', 'type' => 'files', 'title' => 'Certification Documents', 'required' => false, 'max_file_size' => 10, 'max_number_of_files' => 3,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('certification_status', 'select', 'equals', 'Yes')])],
                ['id' => 'insurance_certificate_sr', 'type' => 'files', 'title' => 'Certificate of Insurance', 'required' => true, 'help' => 'General liability minimum per your terms', 'max_file_size' => 10, 'max_number_of_files' => 2],
                $this->dateField('insurance_expiry', 'Insurance Expiry Date', true),
                $this->selectField('payment_terms_requested', 'Requested Payment Terms', [
                    ['value' => 'net_15_sr', 'text' => 'Net 15'],
                    ['value' => 'net_30_sr', 'text' => 'Net 30'],
                    ['value' => 'net_60_sr', 'text' => 'Net 60'],
                ], true),
                $this->textareaField('references_supplied', 'Two Commercial References (customers or contracts)', true),
                $this->checkboxField('compliance_declaration_sr', 'I confirm our business complies with applicable labor, safety, and trade regulations', true),
            ], '#292524'),
        ];
    }

    private function schoolAbsenceReport(): array
    {
        return [
            'name' => 'School Absence Report Form Template',
            'slug' => 'school-absence-report-form-template',
            'short_description' => 'A school absence report form template for parents notifying same-day absences with reasons and doctor-note uploads.',
            'description' => '<p>Our School Absence Report Form Template digitizes morning attendance calls: same-day absence notifications, reason categories, expected durations, and automatic doctor-note requests for extended absences.</p><h2>Why and when to use a school absence report form</h2><p>Morning phone queues jam exactly when offices verify attendance. An absence form timestamps every notification, categorizes reasons for truancy reporting, and automatically demands doctor notes once absences cross policy thresholds, keeping records audit-ready without office staff chasing calls.</p><h2>Who is this template for</h2><p>School front offices, district attendance administrators, private schools without attendance software, preschools, and summer programs tracking daily check-ins.</p><h2>Why SharaForms is the best tool for this form</h2><p>Absences beyond three consecutive days trigger doctor-documentation requirements automatically, reason data feeds state reporting formats directly, and every submission carries a verifiable timestamp parents cannot backdate.</p>',
            'types' => ['report_forms'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Student Absence Notification', [
                $this->nfText('intro', '<h2>Report an Absence</h2><p>Submit before 8:30 AM on the day of absence. Same-day notifications do not require follow-up calls from our office.</p>'),
                $this->textField('student_name_abs', 'Student Full Name', true),
                $this->selectField('grade_abs', 'Grade / Class', [
                    ['value' => 'abs_k', 'text' => 'Kindergarten'],
                    ['value' => 'abs_1_2', 'text' => 'Grades 1-2'],
                    ['value' => 'abs_3_4', 'text' => 'Grades 3-4'],
                    ['value' => 'abs_5_6', 'text' => 'Grades 5-6'],
                    ['value' => 'abs_ms', 'text' => 'Middle school'],
                    ['value' => 'abs_hs', 'text' => 'High school'],
                ], true),
                $this->textField('parent_name_abs', 'Parent / Guardian Name', true),
                $this->phoneField('contact_phone_abs', 'Contact Phone', true),
                $this->dateField('absence_date_first', 'First Day of Absence', true),
                $this->numberField('days_absent', 'Number of Days Absent', true, ['help' => 'Today only counts as 1']),
                $this->selectField('absence_reason', 'Reason for Absence', [
                    ['value' => 'illness_abs', 'text' => 'Illness'],
                    ['value' => 'medical_appt', 'text' => 'Medical appointment'],
                    ['value' => 'family_emerg', 'text' => 'Family emergency'],
                    ['value' => 'family_travel', 'text' => 'Family travel / observance'],
                    ['value' => 'other_reason_abs', 'text' => 'Other'],
                ], true),
                $this->textareaField('symptom_details', 'Symptoms (illness absences help us track outbreaks)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('absence_reason', 'select', 'equals', 'Illness')]),
                ]),
                $this->textareaField('other_reason_details', 'Please Describe the Reason', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('absence_reason', 'select', 'equals', 'Other')], 'and', true),
                ]),
                ['id' => 'doctor_note_upload', 'type' => 'files', 'title' => 'Doctor\'s Note Upload', 'required' => false, 'help' => 'Required for absences of 3+ consecutive days', 'max_file_size' => 10, 'max_number_of_files' => 1,
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('days_absent', 'number', 'greater_than_or_equal_to', 3)], 'and', true)],
                $this->selectField('homework_request', 'Should teachers assemble homework?', [
                    ['value' => 'hw_yes', 'text' => 'Yes, please prepare work'],
                    ['value' => 'hw_no', 'text' => 'No need'],
                ], true),
                $this->checkboxField('info_accurate_abs', 'I certify this information is accurate as the student\'s parent or guardian', true),
            ], '#7c2d12'),
        ];
    }

    private function dentalNewPatient(): array
    {
        return [
            'name' => 'Dental New Patient Form Template',
            'slug' => 'dental-new-patient-form-template',
            'short_description' => 'A dental new patient form template capturing dental history, insurance, anxieties, and medical flags before first visits.',
            'description' => '<p>Our Dental New Patient Form Template prepares practices for first appointments: dental history, current concerns, insurance details, medical flags, and anxiety levels that shape gentler care.</p><h2>Why and when to use a dental patient intake form</h2><p>First visits run late when histories arrive on clipboards mid-waiting-room. Digital intake collects dental histories and insurance verification details ahead of time, surfaces medical conditions dentists must know, and reveals anxious patients who deserve a different welcome.</p><h2>Who is this template for</h2><p>Dental practices, orthodontists, pediatric dentists, dental hygienist studios, and multi-chair clinics standardizing intake across locations.</p><h2>Why SharaForms is the best tool for this form</h2><p>Insurance details arrive ready for eligibility checks, medical-flag answers alert clinicians before treatment planning, and anxiety self-reports let teams schedule first-timers with extra care slots.</p>',
            'types' => ['registration_forms'],
            'industries' => ['healthcare_forms'],
            'structure' => $this->structure('New Dental Patient Registration', [
                $this->nfText('intro', '<h2>Welcome to Our Practice</h2><p>Complete this before your first visit so we dedicate your appointment to your teeth, not paperwork.</p>'),
                $this->textField('patient_name_dn', 'Full Name', true),
                $this->dateField('dob_dental', 'Date of Birth', true),
                $this->phoneField('phone', 'Phone Number', true),
                $this->emailField('email'),
                $this->textareaField('address_dental', 'Home Address', true),
                $this->selectField('last_visit_dental', 'Last Dental Visit', [
                    ['value' => 'within_year', 'text' => 'Within the last year'],
                    ['value' => 'one_three_years_d', 'text' => '1-3 years ago'],
                    ['value' => 'over_three_years_d', 'text' => 'Over 3 years ago'],
                    ['value' => 'never_visited', 'text' => 'First-ever dental visit'],
                ], true),
                $this->textareaField('dental_concerns', 'Current Dental Concerns (pain, sensitivity, cosmetic goals)'),
                $this->multiSelectField('dental_history_items', 'Which Apply to You?', [
                    ['value' => 'gum_bleeding', 'text' => 'Gums bleed when brushing'],
                    ['value' => 'grinding_teeth', 'text' => 'Grind or clench teeth'],
                    ['value' => 'jaw_pain', 'text' => 'Jaw pain or clicking'],
                    ['value' => 'sensitive_teeth', 'text' => 'Sensitive teeth'],
                    ['value' => 'orthodontic_past', 'text' => 'Past orthodontic treatment'],
                    ['value' => 'none_apply', 'text' => 'None of these'],
                ]),
                $this->selectField('anxiety_level', 'How do you feel about dental visits?', [
                    ['value' => 'relaxed_d', 'text' => 'Relaxed, no problem'],
                    ['value' => 'slight_nervous', 'text' => 'A little nervous'],
                    ['value' => 'very_anxious', 'text' => 'Very anxious; please go gently with me'],
                ], true),
                $this->selectField('insurance_status_d', 'Insurance Status', [
                    ['value' => 'insured_d', 'text' => 'I have dental insurance'],
                    ['value' => 'no_insurance_d', 'text' => 'No insurance; self-pay'],
                ], true),
                $this->textField('insurance_provider_d', 'Insurance Provider', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('insurance_status_d', 'select', 'equals', 'I have dental insurance')], 'and', true),
                ]),
                $this->textField('member_id_dental', 'Member ID', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('insurance_status_d', 'select', 'equals', 'I have dental insurance')], 'and', true),
                ]),
                $this->textareaField('medical_flags', 'Medical Conditions We Should Know (heart conditions, diabetes, pregnancy, blood thinners)', true),
                $this->selectField('allergies_dental', 'Any medication allergies?', [
                    ['value' => 'allergy_no_d', 'text' => 'No'],
                    ['value' => 'allergy_yes_d', 'text' => 'Yes'],
                ], true),
                $this->textField('allergy_details_d', 'List Allergies', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('allergies_dental', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->checkboxField('treatment_consent_d', 'I consent to examination and diagnostic imaging as clinically needed', true),
            ], '#0e7490'),
        ];
    }

    private function charityAuctionDonation(): array
    {
        return [
            'name' => 'Charity Auction Donation Form Template',
            'slug' => 'charity-auction-donation-form-template',
            'short_description' => 'A charity auction donation form template collecting item pledges, valuations, logistics, and donor recognition choices.',
            'description' => '<p>Our Charity Auction Donation Form Template organizes silent and live auction contributions: item descriptions, valuations, pickup logistics, and recognition preferences that make gala catalogs build themselves.</p><h2>Why and when to use an auction donation form</h2><p>Gala committees chase auction items through email until catalogs print incomplete. A pledge form captures each donation with its estimated value, photos, display description, and delivery plan together, turning catalog assembly into an export instead of an archaeology project.</p><h2>Who is this template for</h2><p>Gala and fundraiser committees, school PTAs running silent auctions, charity foundations, animal shelters hosting benefit events, and chambers of commerce organizing community auctions.</p><h2>Why SharaForms is the best tool for this form</h2><p>Valuations feed receipt letters donors need for tax season, anonymous-bidding preferences get documented explicitly, and item exports sort into live versus silent lots instantly.</p>',
            'types' => ['donation_forms'],
            'industries' => ['charity_forms', 'business_forms'],
            'structure' => $this->structure('Auction Item Donation', [
                $this->nfText('intro', '<h2>Donate to the Auction</h2><p>Your contribution funds our mission directly. Tell us about your item and we handle valuation letters, display copy, and logistics.</p>'),
                $this->textField('donor_name_auction', 'Donor Name or Business', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textField('item_title_auction', 'Item or Experience Title', true),
                $this->textareaField('item_description_auction', 'Catalog Description (what bidders will read)', true),
                $this->selectField('item_category_auction', 'Donation Category', [
                    ['value' => 'experience_auc', 'text' => 'Experience (travel, dining, lessons)'],
                    ['value' => 'physical_item', 'text' => 'Physical item'],
                    ['value' => 'service_donated', 'text' => 'Professional service'],
                    ['value' => 'gift_basket', 'text' => 'Gift basket / bundle'],
                    ['value' => 'monetary_gift', 'text' => 'Monetary contribution instead'],
                ], true),
                $this->numberField('estimated_value', 'Estimated Fair Market Value ($)', true),
                ['id' => 'item_photos_auc', 'type' => 'files', 'title' => 'Photos of the Item', 'required' => false, 'help' => 'Great photos raise bids', 'max_file_size' => 10, 'max_number_of_files' => 4],
                $this->selectField('delivery_method_auc', 'How Will the Item Reach Us?', [
                    ['value' => 'donor_delivers', 'text' => 'I will drop it off'],
                    ['value' => 'committee_picks', 'text' => 'Please arrange pickup'],
                    ['value' => 'shipped_in', 'text' => 'Shipping it to you'],
                ], true),
                $this->textareaField('pickup_address_auc', 'Pickup Address & Availability', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('delivery_method_auc', 'select', 'equals', 'Please arrange pickup')], 'and', true),
                ]),
                $this->selectField('recognition_preference', 'How May We Recognize Your Generosity?', [
                    ['value' => 'full_credit', 'text' => 'Full name in catalog and signage'],
                    ['value' => 'anonymous_bid', 'text' => 'List as Anonymous Donor'],
                    ['value' => 'business_credit', 'text' => 'Business name and logo'],
                ], true),
                $this->checkboxField('receipt_needed', 'Yes, I need a donation receipt letter for tax purposes'),
            ], '#166534'),
        ];
    }

    private function donationPickupRequest(): array
    {
        return [
            'name' => 'Donation Pickup Request Form Template',
            'slug' => 'donation-pickup-request-form-template',
            'short_description' => 'A donation pickup request form template scheduling thrift collections with item lists, photos, and access details.',
            'description' => '<p>Our Donation Pickup Request Form Template schedules charitable collections efficiently: item inventories, photo verification, access instructions, and preferred windows so trucks route full, not hopeful.</p><h2>Why and when to use a donation pickup form</h2><p>Pickup trucks arriving to nothing but trash cost charities real money. Structured requests with item lists and photos let dispatchers accept only genuine donations, group stops geographically, and arrive knowing whether a piano or a pillowcase awaits.</p><h2>Who is this template for</h2><p>Thrift store operators, furniture banks, clothing collection charities, animal shelter resale shops, and reuse nonprofits running scheduled collection routes.</p><h2>Why SharaForms is the best tool for this form</h2><p>Photo uploads verify donations before trucks roll, stair-and-access answers prevent two-person jobs arriving solo, and accepted pickups export into route-planning order automatically.</p>',
            'types' => ['request_forms', 'donation_forms'],
            'industries' => ['charity_forms'],
            'structure' => $this->structure('Schedule a Donation Pickup', [
                $this->nfText('intro', '<h2>Donate; We Collect</h2><p>We collect sellable goods within 15 miles of our store. List your items below; photos speed approval dramatically.</p>'),
                $this->textField('donor_name_dp', 'Your Name', true),
                $this->phoneField('phone', 'Phone Number', true),
                $this->emailField('email'),
                $this->textareaField('pickup_address_dp', 'Pickup Address', true),
                $this->multiSelectField('item_types', 'What Are You Donating?', [
                    ['value' => 'furniture_dp', 'text' => 'Furniture'],
                    ['value' => 'clothing_dp', 'text' => 'Clothing & textiles'],
                    ['value' => 'electronics_work', 'text' => 'Working electronics'],
                    ['value' => 'housewares_dp', 'text' => 'Housewares & decor'],
                    ['value' => 'books_media', 'text' => 'Books & media'],
                    ['value' => 'toys_dp', 'text' => 'Toys & games'],
                ], true),
                $this->textareaField('item_inventory', 'Item List (describe each piece and its condition)', true),
                ['id' => 'item_photos_dp', 'type' => 'files', 'title' => 'Photos of Items', 'required' => false, 'help' => 'Especially furniture and electronics', 'max_file_size' => 10, 'max_number_of_files' => 6],
                $this->selectField('access_stairs', 'Stair Situation at Pickup Location', [
                    ['value' => 'ground_floor', 'text' => 'Ground floor / easy access'],
                    ['value' => 'stairs_present', 'text' => 'Stairs involved'],
                    ['value' => 'elevator_building', 'text' => 'Elevator building'],
                ], true),
                $this->numberField('stair_flights', 'How Many Flights of Stairs?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('access_stairs', 'select', 'equals', 'Stairs involved')], 'and', true),
                ]),
                $this->selectField('preferred_day_dp', 'Preferred Pickup Day', [
                    ['value' => 'day_mon', 'text' => 'Monday'],
                    ['value' => 'day_wed', 'text' => 'Wednesday'],
                    ['value' => 'day_fri', 'text' => 'Friday'],
                    ['value' => 'day_sat', 'text' => 'Saturday'],
                ], true),
                $this->selectField('time_window_dp', 'Time Window', [
                    ['value' => 'win_morning_dp', 'text' => 'Morning (8 AM - 12 PM)'],
                    ['value' => 'win_afternoon_dp', 'text' => 'Afternoon (12 PM - 5 PM)'],
                    ['value' => 'win_any_dp', 'text' => 'Either works'],
                ], true),
                $this->checkboxField('tax_receipt_dp', 'I would like a donation receipt for taxes'),
                $this->checkboxField('access_permission_dp', 'Someone 18+ will be home, or items will be accessible outside (weatherproof)', true),
            ], '#14532d'),
        ];
    }

    private function eventFeedbackSurvey(): array
    {
        return [
            'name' => 'Event Feedback Survey Template',
            'slug' => 'event-feedback-survey-form-template',
            'short_description' => 'An event feedback survey template scoring sessions, venue, and value with computed experience scores.',
            'description' => '<p>Our Event Feedback Survey Template converts attendee experiences into planning data: scored dimensions with computed totals, session highlights, and honest improvement input collected while memories are fresh.</p><h2>Why and when to use an event feedback form</h2><p>Post-event debriefs run on vibes unless attendees speak quantitatively. Scoring overall experience, venue, and value separately shows organizers exactly what next year\'s budget should fix first, while open comments capture the color behind the numbers.</p><h2>Who is this template for</h2><p>Conference organizers, festival committees, corporate event planners, community event teams, and venues measuring recurring programming success.</p><h2>Why SharaForms is the best tool for this form</h2><p>Scores compute into shareable experience indexes instantly, disappointed attendees explain what went wrong through automatic follow-ups, and exports compare events year over year on identical measures.</p>',
            'types' => ['survey_templates', 'feedback_forms'],
            'industries' => ['entertainment_forms', 'business_forms'],
            'structure' => $this->structure('How Was Your Event Experience?', [
                $this->nfText('intro', '<h2>Tell Us About Your Experience</h2><p>Three quick scores and a comment field: sixty seconds that shape next year\'s event directly.</p>'),
                $this->textField('event_name_ef', 'Which Event Did You Attend?', true),
                $this->dateField('event_date_ef', 'Event Date', true),
                $this->selectField('attendee_type_ef', 'You Attended As', [
                    ['value' => 'general_attendee', 'text' => 'General attendee'],
                    ['value' => 'speaker_ef', 'text' => 'Speaker / performer'],
                    ['value' => 'vendor_ef', 'text' => 'Vendor / exhibitor'],
                    ['value' => 'volunteer_ef', 'text' => 'Volunteer / staff'],
                ], true),
                ['id' => 'r_overall', 'type' => 'rating', 'title' => 'Overall experience', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'r_venue', 'type' => 'rating', 'title' => 'Venue and logistics', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'r_value', 'type' => 'rating', 'title' => 'Value for time and money', 'required' => true, 'help' => '', 'steps' => 5],
                $this->totalBlock('experience_score_display', 'cv_experience_score', 'Experience Score', '0 / 15'),
                $this->selectField('return_next_year', 'Will you attend again?', [
                    ['value' => 'definitely_return', 'text' => 'Definitely'],
                    ['value' => 'probably_return', 'text' => 'Probably'],
                    ['value' => 'unsure_return', 'text' => 'Unsure'],
                    ['value' => 'no_return', 'text' => 'Probably not'],
                ], true),
                $this->textareaField('what_dissatisfied', 'What Fell Short for You?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('r_overall', 'rating', 'less_than', 4),
                        $this->logicCondition('return_next_year', 'select', 'equals', 'Probably not'),
                        $this->logicCondition('return_next_year', 'select', 'equals', 'Unsure'),
                    ], 'or'),
                ]),
                $this->textareaField('favorite_moment', 'What Was Your Favorite Part?'),
                $this->multiSelectField('future_topics', 'What Would You Want More Of Next Time?', [
                    ['value' => 'more_sessions', 'text' => 'More sessions / programming'],
                    ['value' => 'more_networking', 'text' => 'Networking opportunities'],
                    ['value' => 'more_food', 'text' => 'Better food options'],
                    ['value' => 'more_breaks', 'text' => 'Longer breaks'],
                    ['value' => 'more_virtual', 'text' => 'Virtual attendance options'],
                ]),
            ], '#86198f', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_experience_score',
                        'Experience Score',
                        'SUM({r_overall},{r_venue},{r_value})'
                    ),
                ],
            ]),
        ];
    }

    private function employeeEngagementSurvey(): array
    {
        return [
            'name' => 'Employee Engagement Survey Template',
            'slug' => 'employee-engagement-survey-template',
            'short_description' => 'An employee engagement survey template measuring belonging, growth, recognition, and support with computed indexes.',
            'description' => '<p>Our Employee Engagement Survey Template measures what actually drives retention: five scored engagement dimensions, anonymity respected structurally, and improvement areas routed straight to leadership.</p><h2>Why and when to use an engagement survey</h2><p>Exit interviews arrive too late to save anyone. Quarterly pulse measurement across purpose, growth, recognition, support, and belonging surfaces disengagement while intervention is still possible, and trend lines prove which fixes actually moved the needle.</p><h2>Who is this template for</h2><p>HR and people-operations teams, leadership running culture initiatives, managers measuring team health after reorganizations, and companies preparing retention budgets.</p><h2>Why SharaForms is the best tool for this form</h2><p>Computed engagement indexes make quarters comparable at a glance, low-scoring dimensions trigger targeted follow-up questions, and anonymous submissions still produce exportable aggregate trends.</p>',
            'types' => ['survey_templates', 'feedback_forms'],
            'industries' => ['human_resources_forms'],
            'structure' => $this->structure('Quarterly Engagement Pulse', [
                $this->nfText('intro', '<h2>Engagement Pulse Check</h2><p>Five questions, three minutes, complete honesty welcome. Aggregate results only are shared; individual answers stay confidential.</p>'),
                $this->selectField('team_survey_eng', 'Team / Department (optional, aids trend analysis)', [
                    ['value' => 'team_ops_eng', 'text' => 'Operations'],
                    ['value' => 'team_sales_eng', 'text' => 'Sales'],
                    ['value' => 'team_tech_eng', 'text' => 'Technology'],
                    ['value' => 'team_support_eng', 'text' => 'Customer Support'],
                    ['value' => 'team_admin_eng', 'text' => 'Administration'],
                    ['value' => 'prefer_not_say', 'text' => 'Prefer not to say'],
                ]),
                ['id' => 'q_purpose', 'type' => 'rating', 'title' => 'My work connects to a purpose I care about', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'q_growth_e', 'type' => 'rating', 'title' => 'I am growing professionally in this role', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'q_recognition_e', 'type' => 'rating', 'title' => 'Good work gets recognized here', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'q_support_e', 'type' => 'rating', 'title' => 'My manager supports my success', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'q_belonging', 'type' => 'rating', 'title' => 'I feel I belong on this team', 'required' => true, 'help' => '', 'steps' => 5],
                $this->totalBlock('engagement_index_display', 'cv_engagement_index', 'Engagement Index', '0 / 25'),
                $this->textareaField('improvement_priority', 'What One Change Would Most Improve Your Work Life?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([
                        $this->logicCondition('q_purpose', 'rating', 'less_than', 3),
                        $this->logicCondition('q_growth_e', 'rating', 'less_than', 3),
                        $this->logicCondition('q_recognition_e', 'rating', 'less_than', 3),
                        $this->logicCondition('q_support_e', 'rating', 'less_than', 3),
                        $this->logicCondition('q_belonging', 'rating', 'less_than', 3),
                    ], 'or'),
                ]),
                $this->textareaField('shoutout_colleague', 'Shout Out a Colleague Who Made Your Week Better (optional)'),
                $this->checkboxField('followup_ok_eng', 'Leadership may contact me about my answers'),
            ], '#312e81', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_engagement_index',
                        'Engagement Index',
                        'SUM({q_purpose},{q_growth_e},{q_recognition_e},{q_support_e},{q_belonging})'
                    ),
                ],
            ]),
        ];
    }

    private function courseEvaluation(): array
    {
        return [
            'name' => 'Course Evaluation Form Template',
            'slug' => 'course-evaluation-form-template',
            'short_description' => 'A course evaluation form template collecting student feedback on instruction, workload, materials, and outcomes.',
            'description' => '<p>Our Course Evaluation Form Template captures end-of-term student feedback that instructors can act on: teaching effectiveness, workload realism, material usefulness, and outcome confidence, all structured for department comparison.</p><h2>Why and when to use a course evaluation form</h2><p>End-of-term evaluations shape teaching careers and course design alike, yet paper forms yield scribbles nobody analyzes. Digital evaluation standardizes questions across sections, separates instructor feedback from curriculum feedback, and gives departments comparable data per course code.</p><h2>Who is this template for</h2><p>Universities and colleges, training academies, online course creators closing cohorts, bootcamps reporting outcomes, and continuing-education programs.</p><h2>Why SharaForms is the best tool for this form</h2><p>Low ratings trigger constructive-comment prompts automatically, workload complaints separate from teaching complaints, and section-by-section exports reveal where course design versus delivery needs attention.</p>',
            'types' => ['evaluation_forms', 'survey_templates'],
            'industries' => ['education_forms'],
            'structure' => $this->structure('Course Evaluation', [
                $this->nfText('intro', '<h2>Evaluate This Course</h2><p>Your candid feedback shapes future versions of this course. Responses go to the department after grading concludes.</p>'),
                $this->textField('course_code_ce', 'Course Code & Section', true),
                $this->selectField('attendance_ce', 'How Often Did You Attend?', [
                    ['value' => 'always_ce', 'text' => 'Nearly always'],
                    ['value' => 'mostly_ce', 'text' => 'Most classes'],
                    ['value' => 'sometimes_ce', 'text' => 'Sometimes'],
                    ['value' => 'rarely_ce', 'text' => 'Rarely'],
                ], true),
                ['id' => 'ce_instruction', 'type' => 'rating', 'title' => 'Instruction quality: concepts explained effectively', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'ce_materials', 'type' => 'rating', 'title' => 'Materials: readings and resources supported learning', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'ce_workload', 'type' => 'rating', 'title' => 'Workload was reasonable for the credit hours', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'ce_feedback_q', 'type' => 'rating', 'title' => 'Assignments were graded and returned with useful feedback', 'required' => true, 'help' => '', 'steps' => 5],
                ['id' => 'ce_outcomes', 'type' => 'rating', 'title' => 'I achieved the stated learning outcomes', 'required' => true, 'help' => '', 'steps' => 5],
                $this->selectField('grade_expectation', 'Expected Grade', [
                    ['value' => 'exp_a', 'text' => 'A range'],
                    ['value' => 'exp_b', 'text' => 'B range'],
                    ['value' => 'exp_c', 'text' => 'C range'],
                    ['value' => 'exp_below_c', 'text' => 'Below C'],
                ]),
                $this->textareaField('instructor_strengths', 'What Did the Instructor Do Well?', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('ce_instruction', 'rating', 'greater_than_or_equal_to', 4)]),
                ]),
                $this->textareaField('instruction_improve', 'How Could Instruction Improve? Be Specific', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('ce_instruction', 'rating', 'less_than_or_equal_to', 2)], 'and', true),
                ]),
                $this->textareaField('course_advice', 'Advice for Future Students Taking This Course'),
            ], '#1e40af'),
        ];
    }

    private function photoContestEntry(): array
    {
        return [
            'name' => 'Photo Contest Entry Form Template',
            'slug' => 'photo-contest-entry-form-template',
            'short_description' => 'A photo contest entry form template handling submissions, categories, releases, and guardian consent for minors.',
            'description' => '<p>Our Photo Contest Entry Form Template runs competitions cleanly: categorized submissions with high-resolution uploads, model releases, originality declarations, and guardian consent handled automatically for young entrants.</p><h2>Why and when to use a photo contest form</h2><p>Contests collapse under email attachments, missing releases, and unanswerable "how old are you" threads. A structured entry collects the image, its category, consent documentation, and contact details together, keeping judging fair and legal exposure zero.</p><h2>Who is this template for</h2><p>Marketing teams running UGC campaigns, tourism boards promoting destinations, camera clubs, schools hosting art competitions, and brands celebrating customer communities.</p><h2>Why SharaForms is the best tool for this form</h2><p>Under-18 entries trigger mandatory guardian fields automatically, model-release checkboxes document people-photography permissions, and submission exports hand judges consistent formats instead of forty different attachment types.</p>',
            'types' => ['content_forms', 'file_upload_forms'],
            'industries' => ['marketing_forms', 'entertainment_forms'],
            'structure' => $this->structure('Photo Contest Entry', [
                $this->nfText('intro', '<h2>Enter the Contest</h2><p>One entry per person. Winners announced on social media and contacted directly; read the rules link before entering.</p>'),
                $this->textField('entrant_name_pc', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('contest_category', 'Entry Category', [
                    ['value' => 'landscape_cat', 'text' => 'Landscape / nature'],
                    ['value' => 'people_cat', 'text' => 'People & portraits'],
                    ['value' => 'pets_cat', 'text' => 'Pets & animals'],
                    ['value' => 'urban_cat', 'text' => 'Urban / architecture'],
                    ['value' => 'macro_cat', 'text' => 'Macro & detail'],
                ], true),
                $this->textField('photo_title', 'Photo Title', true),
                $this->textareaField('photo_story', 'The Story Behind the Shot (optional)'),
                ['id' => 'photo_upload_entry', 'type' => 'files', 'title' => 'Upload Your Photo', 'required' => true, 'help' => 'High resolution JPEG or PNG, max 25MB', 'max_file_size' => 25, 'max_number_of_files' => 1],
                $this->selectField('age_confirmation_pc', 'Age Confirmation', [
                    ['value' => 'adult_entrant', 'text' => 'I am 18 or older'],
                    ['value' => 'minor_entrant', 'text' => 'I am under 18'],
                ], true),
                $this->textField('guardian_name_pc', 'Parent / Guardian Name', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('age_confirmation_pc', 'select', 'equals', 'I am under 18')], 'and', true),
                ]),
                $this->emailField('guardian_email_pc', 'Guardian Email', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('age_confirmation_pc', 'select', 'equals', 'I am under 18')], 'and', true),
                ]),
                $this->checkboxField('model_release_pc', 'Anyone recognizable in this photo has permitted contest use'),
                $this->checkboxField('originality_declaration', 'This photo is my original work, unedited beyond basic adjustments', true),
                $this->checkboxField('usage_rights_pc', 'I grant the organizer rights to display and promote this entry with credit'),
            ], '#9f1239'),
        ];
    }

    private function customerReferral(): array
    {
        return [
            'name' => 'Customer Referral Form Template',
            'slug' => 'customer-referral-form-template',
            'short_description' => 'A customer referral form template capturing word-of-mouth leads with reward selection and consent from both sides.',
            'description' => '<p>Our Customer Referral Form Template turns happy customers into a channel: referred-friend details, referrer rewards, and dual consent captured so outreach feels like a favor, not a cold call.</p><h2>Why and when to use a referral form</h2><p>Referred customers convert better and churn less, yet most programs run on forgotten hallway promises. A structured referral captures both parties\' details plus reward preferences in one submission, letting sales open warm conversations with context instead of awkward cold intros.</p><h2>Who is this template for</h2><p>B2B sales teams with referral incentives, local service businesses living on word of mouth, financial advisors, real estate agents, and SaaS products rewarding advocacy.</p><h2>Why SharaForms is the best tool for this form</h2><p>Reward choices match incentives to what customers actually value, friend-consent fields keep outreach compliant and welcomed, and successful referrals track back to referrers for payout accuracy.</p>',
            'types' => ['lead_generation_forms', 'signup_forms'],
            'industries' => ['marketing_forms', 'business_forms'],
            'structure' => $this->structure('Refer a Friend', [
                $this->nfText('intro', '<h2>Share the Love, Earn Rewards</h2><p>You get rewarded when they become a customer. Simple as that.</p>'),
                $this->textField('referrer_name_cr', 'Your Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Your Phone', true),
                $this->textField('customer_since_cr', 'Customer Since (year)'),
                $this->textField('friend_name_cr', "Friend's Name", true),
                $this->phoneField('friend_phone', "Friend's Phone", true),
                $this->emailField('friend_email', "Friend's Email", true),
                $this->selectField('friend_interest', 'What Does Your Friend Need?', [
                    ['value' => 'same_service', 'text' => 'Same service as you'],
                    ['value' => 'different_need', 'text' => 'Something different'],
                    ['value' => 'just_browsing_ref', 'text' => 'Not sure yet'],
                ], true),
                $this->textareaField('context_for_intro', 'Context That Helps Us Open Well (their situation, timing)', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('friend_interest', 'select', 'equals', 'Something different')], 'and', true),
                ]),
                $this->selectField('reward_preference', 'Your Reward Preference Upon Conversion', [
                    ['value' => 'account_credit', 'text' => 'Account credit'],
                    ['value' => 'gift_card_ref', 'text' => 'Gift card'],
                    ['value' => 'discount_month', 'text' => 'Free month of service'],
                    ['value' => 'donate_reward', 'text' => 'Donate it to charity'],
                ], true),
                $this->checkboxField('friend_consents_cr', 'My friend has agreed to be contacted by us', true),
                $this->checkboxField('referral_terms_cr', 'I understand the referral program terms and reward conditions', true),
            ], '#be185d'),
        ];
    }

    private function birthdayPartyBooking(): array
    {
        return [
            'name' => 'Birthday Party Booking Form Template',
            'slug' => 'birthday-party-booking-form-template',
            'short_description' => 'A birthday party booking form template for venues handling packages, extra guests, themes, and live totals.',
            'description' => '<p>Our Birthday Party Booking Form Template handles venue party packages completely: package selection with live pricing, guest counts beyond included numbers, theme choices, allergy alerts, and deposit acknowledgment.</p><h2>Why and when to use a birthday party booking form</h2><p>Venue parties juggle packages, headcounts, food allergies, and setup preferences across excited, distracted parents. A structured booking captures everything in one pass, shows honest totals before commitment, and hands staff a complete run sheet for party day.</p><h2>Who is this template for</h2><p>Kids\' entertainment venues, trampoline parks, bowling alleys, play centers, museums hosting parties, and restaurants with party rooms.</p><h2>Why SharaForms is the best tool for this form</h2><p>Extra-guest fees calculate visibly against package inclusions, allergy details reach kitchen staff automatically, and themed-setup answers land on the decorating team\'s prep list without phone tag.</p>',
            'types' => ['booking_forms', 'reservation_forms'],
            'industries' => ['entertainment_forms', 'services_forms'],
            'structure' => $this->structure('Birthday Party Booking', [
                $this->nfText('intro', '<h2>Book Your Celebration</h2><p>Pick a package, tell us your crew size, and watch your total build. Deposits confirm bookings; balance due on party day.</p>'),
                $this->textField('parent_name_bp', 'Parent / Guardian Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->textField('birthday_child_name', "Birthday Star's Name & Age", true),
                $this->dateField('party_date', 'Preferred Party Date', true),
                $this->selectField('package_choice', 'Party Package', [
                    ['value' => 'weekday_mini', 'text' => 'Weekday Mini: 2 hours, 10 kids - $199'],
                    ['value' => 'weekend_classic', 'text' => 'Weekend Classic: 2 hours, 15 kids - $299'],
                    ['value' => 'weekend_deluxe', 'text' => 'Weekend Deluxe: 3 hours, 20 kids - $449'],
                ], true),
                $this->selectField('party_time_slot', 'Time Slot', [
                    ['value' => 'slot_10am', 'text' => '10:00 AM - 12:00 PM'],
                    ['value' => 'slot_1pm', 'text' => '1:00 PM - 3:00 PM'],
                    ['value' => 'slot_4pm', 'text' => '4:00 PM - 6:00 PM'],
                ], true),
                $this->numberField('extra_children', 'Extra Children Beyond Package ($12 each)', false),
                $this->selectField('theme_selection', 'Decoration Theme', [
                    ['value' => 'theme_pirates', 'text' => 'Pirates & mermaids'],
                    ['value' => 'theme_space', 'text' => 'Space adventure'],
                    ['value' => 'theme_princess', 'text' => 'Princess & knights'],
                    ['value' => 'theme_dino', 'text' => 'Dinosaur expedition'],
                    ['value' => 'theme_generic', 'text' => 'Surprise us!'],
                ], true),
                $this->numberField('goodie_bags', 'Goodie Bags ($8 each)', false),
                $this->selectField('bringing_cake', 'Bringing your own cake?', [
                    ['value' => 'cake_yes', 'text' => 'Yes, outside cake (+$10 plating fee)'],
                    ['value' => 'cake_addon', 'text' => 'Order one from you'],
                    ['value' => 'cake_no', 'text' => 'No cake needed'],
                ], true),
                $this->selectField('food_allergies_bp', 'Any allergies among the guests?', [
                    ['value' => 'allergy_yes_bp', 'text' => 'Yes'],
                    ['value' => 'allergy_no_bp', 'text' => 'No'],
                ], true),
                $this->textareaField('allergy_details_bp', 'Describe Allergies So Our Kitchen Can Prepare Safely', false, [
                    'hidden' => true,
                    'logic' => $this->revealLogic([$this->logicCondition('food_allergies_bp', 'select', 'equals', 'Yes')], 'and', true),
                ]),
                $this->checkboxField('deposit_acknowledged', 'I understand a $100 deposit confirms this booking and is refundable up to 7 days before', true),
                $this->totalBlock('party_total_display', 'cv_party_total', 'Estimated Party Total', '$0'),
            ], '#c2410c', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_party_total',
                        'Estimated Party Total',
                        'IF({package_choice}="Weekday Mini: 2 hours, 10 kids - $199",199,IF({package_choice}="Weekend Classic: 2 hours, 15 kids - $299",299,449))'
                            . '+IFBLANK({extra_children},0)*12+IFBLANK({goodie_bags},0)*8'
                            . '+IF({bringing_cake}="Yes, outside cake (+$10 plating fee)",10,0)'
                    ),
                ],
            ]),
        ];
    }

    private function storageUnitReservation(): array
    {
        return [
            'name' => 'Storage Unit Reservation Form Template',
            'slug' => 'storage-unit-reservation-form-template',
            'short_description' => 'A storage unit reservation form template sizing units, pricing climate control and insurance, and reserving move-in dates.',
            'description' => '<p>Our Storage Unit Reservation Form Template converts browsers into reserved tenants: unit sizing guidance, transparent monthly rates with climate and insurance options, and move-in scheduling with access-plan selection.</p><h2>Why and when to use a storage reservation form</h2><p>Storage customers compare three facilities by price per square foot and book whichever answers fastest. A reservation form quotes honestly by unit size, adds climate and insurance options transparently, and locks the move-in date before competitors call back.</p><h2>Who is this template for</h2><p>Self-storage facilities, mobile storage container companies, wine and document storage specialists, and property managers adding tenant storage options.</p><h2>Why SharaForms is the best tool for this form</h2><p>Monthly rates compute from unit selections with add-ons visible, vehicle-access needs surface for drive-up planning, and reservations export into gate-access systems with start dates attached.</p>',
            'types' => ['reservation_forms', 'booking_forms'],
            'industries' => ['business_forms', 'real_estate_forms'],
            'structure' => $this->structure('Reserve Your Storage Unit', [
                $this->nfText('intro', '<h2>Reserve a Unit</h2><p>No credit card needed to reserve; units hold for 48 hours. Month-to-month terms, leave anytime.</p>'),
                $this->textField('renter_name_su', 'Full Name', true),
                $this->emailField('email'),
                $this->phoneField('phone', 'Phone Number', true),
                $this->selectField('unit_size_su', 'Unit Size', [
                    ['value' => 'size_5x5', 'text' => '5x5 (closet: boxes, small furniture) - $45/mo'],
                    ['value' => 'size_5x10', 'text' => '5x10 (walk-in: studio apartment) - $65/mo'],
                    ['value' => 'size_10x10', 'text' => '10x10 (bedroom: one-bedroom flat) - $95/mo'],
                    ['value' => 'size_10x20', 'text' => '10x20 (garage: house contents) - $150/mo'],
                ], true),
                $this->selectField('climate_controlled_su', 'Climate Control (+$25/mo)', [
                    ['value' => 'climate_no', 'text' => 'Standard unit'],
                    ['value' => 'climate_yes', 'text' => 'Climate controlled (+$25)'],
                ], true),
                $this->selectField('insurance_plan', 'Contents Protection', [
                    ['value' => 'facility_plan', 'text' => 'Facility plan (+$12/mo)'],
                    ['value' => 'own_policy', 'text' => 'Covered by my homeowner/renter policy'],
                    ['value' => 'decide_later', 'text' => 'Decide at move-in'],
                ], true),
                $this->textField('own_policy_details', 'Insurer Name & Policy Number', false, [
                    'hidden' => true,
                    'help' => 'We document coverage on file for your unit',
                    'logic' => $this->revealLogic([$this->logicCondition('insurance_plan', 'select', 'equals', 'Covered by my homeowner/renter policy')], 'and', true),
                ]),
                $this->dateField('move_in_date_su', 'Target Move-In Date', true),
                $this->selectField('access_needs', 'Typical Access Pattern', [
                    ['value' => 'access_weekday', 'text' => 'Weekdays during business hours'],
                    ['value' => 'access_weekend', 'text' => 'Weekends'],
                    ['value' => 'access_frequent', 'text' => 'Frequent visits, varied times'],
                ], true),
                $this->selectField('vehicle_access_su', 'Will You Need Vehicle Drive-Up Access?', [
                    ['value' => 'drive_up_yes', 'text' => 'Yes, drive-up unit preferred'],
                    ['value' => 'interior_ok', 'text' => 'Interior hallway unit is fine'],
                ], true),
                $this->textareaField('stored_items_su', 'What Will You Store? (helps us suggest the right size)'),
                $this->checkboxField('terms_reservation', 'I understand the 48-hour hold and month-to-month terms', true),
                $this->totalBlock('monthly_rate_display', 'cv_monthly_rate', 'Monthly Rate', '$0/mo'),
            ], '#7f6000', [
                'computed_variables' => [
                    $this->computedVariable(
                        'cv_monthly_rate',
                        'Monthly Rate',
                        'IF({unit_size_su}="5x5 (closet: boxes, small furniture) - $45/mo",45,IF({unit_size_su}="5x10 (walk-in: studio apartment) - $65/mo",65,IF({unit_size_su}="10x10 (bedroom: one-bedroom flat) - $95/mo",95,150)))'
                            . '+IF({climate_controlled_su}="Climate controlled (+$25)",25,0)'
                            . '+IF({insurance_plan}="Facility plan (+$12/mo)",12,0)'
                    ),
                ],
            ]),
        ];
    }
}

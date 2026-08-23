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

    private function emailField(string $id, string $title = 'Email', bool $required = true): array
    {
        return [
            'id' => $id,
            'type' => 'email',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ];
    }

    private function phoneField(string $id, string $title = 'Phone Number', bool $required = false): array
    {
        return [
            'id' => $id,
            'type' => 'phone_number',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ];
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

    private function textareaField(string $id, string $title, bool $required = false): array
    {
        return [
            'id' => $id,
            'type' => 'text',
            'title' => $title,
            'required' => $required,
            'help' => '',
            'multi_lines' => true,
        ];
    }

    private function checkboxField(string $id, string $title, bool $required = false): array
    {
        return [
            'id' => $id,
            'type' => 'checkbox',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ];
    }

    private function dateField(string $id, string $title, bool $required = false): array
    {
        return [
            'id' => $id,
            'type' => 'date',
            'title' => $title,
            'required' => $required,
            'help' => '',
        ];
    }

    private function nfText(string $id, string $content): array
    {
        return [
            'id' => $id,
            'type' => 'nf-text',
            'content' => $content,
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
            'description' => '<p>Our Contact Form Template provides a polished and effective way for your website visitors to get in touch with you. Whether you run a small business, a blog, or a large corporation, this template helps you capture inquiries, feedback, and support requests seamlessly.</p><h2>Why and when to use a contact form</h2><p>A contact form is essential for any website that values communication with its audience. It provides a structured way to receive messages, reduces spam compared to displaying raw email addresses, and ensures you capture all the necessary information to respond effectively.</p><h2>Who is this template for</h2><p>This template is perfect for business owners, freelancers, bloggers, and organizations of all sizes who want to provide a professional communication channel on their website.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms makes it easy to customize this contact form template to match your brand. You can add custom fields, set up email notifications, integrate with Slack or Discord, and embed the form on your website — all without writing any code.</p>',
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
            'short_description' => 'A complete event registration form template to collect attendee details and manage sign-ups effortlessly.',
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
            ], '#8b5cf6'),
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
            'description' => '<p>Streamline your recruitment process with our comprehensive Job Application Form Template. Collect resumes, cover letters, and candidate details in a structured format that makes reviewing applicants easy.</p><h2>Why and when to use a job application form</h2><p>Whether you\'re hiring for a single position or running a large recruitment drive, a standardized application form ensures you collect consistent information from all candidates, making comparison and evaluation more efficient.</p><h2>Who is this template for</h2><p>HR professionals, hiring managers, small business owners, and recruitment teams looking to streamline their applicant collection process.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms allows you to collect file uploads (resumes, portfolios), set up email notifications for new applications, and integrate with your HR tools — making recruitment management seamless.</p>',
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
                $this->textareaField('dietary', 'Dietary Restrictions / Allergies'),
                $this->textareaField('message', 'Message for the Host'),
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
            'description' => '<p>Our Calculation Form Template shows how SharaForms can automatically compute totals as users answer questions. Perfect for pricing calculators, loan estimators, and order totals.</p><h2>Why and when to use a calculation form</h2><p>Use calculation forms when you need instant results — project quotes, budget estimators, BMI calculators, or order totals. Automatic formulas save your users time and reduce errors.</p><h2>Who is this template for</h2><p>Contractors, agencies, financial advisors, and businesses that quote prices or need self-service calculators.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms includes a formula engine that computes totals in real time, with conditional logic to show or hide fields based on answers.</p>', 
            'types' => ['calculation_forms', 'quote_forms'],
            'industries' => ['services_forms', 'banking_forms'],
            'structure' => $this->structure('Cost Calculator', [
                ['id' => 'full_name', 'type' => 'text', 'title' => 'Full Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'project_type', 'type' => 'select', 'title' => 'Project Type', 'required' => true, 'help' => '', 'options' => [['value' => 'basic', 'text' => 'Basic Package - $499'], ['value' => 'standard', 'text' => 'Standard Package - $899'], ['value' => 'premium', 'text' => 'Premium Package - $1499']]],
                ['id' => 'quantity', 'type' => 'number', 'title' => 'Quantity', 'required' => true, 'help' => ''],
                ['id' => 'addons', 'type' => 'multi_select', 'title' => 'Add-ons', 'required' => false, 'help' => 'Select any additional services', 'options' => [['value' => 'design', 'text' => 'Extra Design - $150'], ['value' => 'support', 'text' => 'Priority Support - $99'], ['value' => 'training', 'text' => 'Training Session - $199']]],
                ['id' => 'budget', 'type' => 'slider', 'title' => 'Your Budget', 'required' => false, 'help' => '', 'slider_min_value' => 0, 'slider_max_value' => 10000, 'slider_step_value' => 100]
            ], '#14b8a6'),
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
            'description' => '<p>Our Poll Form Template is perfect for gathering quick opinions from your audience. Ask one focused question and collect votes instantly.</p><h2>Why and when to use a poll</h2><p>Polls are great for social media, product decisions, community voting, and gathering fast feedback on a single question.</p><h2>Who is this template for</h2><p>Marketers, social media managers, product teams, and community managers running quick opinion polls.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms makes polls easy to share and embed, with instant submission summaries so you can read results as they come in.</p>', 
            'types' => ['polls', 'voting_forms'],
            'industries' => ['marketing_forms', 'entertainment_forms'],
            'structure' => $this->structure('Quick Poll', [
                ['id' => 'question_1', 'type' => 'radio', 'title' => 'Which option do you prefer?', 'required' => true, 'help' => '', 'options' => [['value' => 'option_a', 'text' => 'Option A'], ['value' => 'option_b', 'text' => 'Option B'], ['value' => 'option_c', 'text' => 'Option C']]],
                ['id' => 'feedback', 'type' => 'text', 'title' => 'Any comments?', 'required' => false, 'help' => '', 'multi_lines' => true]
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
                ['id' => 'q5', 'type' => 'radio', 'title' => 'Question 5: Which ocean is the largest?', 'required' => true, 'help' => '', 'options' => [['value' => 'atlantic', 'text' => 'Atlantic'], ['value' => 'indian', 'text' => 'Indian'], ['value' => 'pacific', 'text' => 'Pacific'], ['value' => 'arctic', 'text' => 'Arctic']]]
            ], '#8b5cf6'),
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
                ['id' => 'consent', 'type' => 'checkbox', 'title' => 'I consent to my child participating in camp activities', 'required' => true, 'help' => '']
            ], '#f59e0b'),
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
            'description' => '<p>Our Voting Form Template helps you run elections, award ballots, and community decisions with a simple, structured voting flow.</p><h2>Why and when to use a voting form</h2><p>Use voting forms for board elections, staff decisions, community polls, and award ballots where you need a clear, auditable result.</p><h2>Who is this template for</h2><p>Organizations, associations, committees, and communities running structured votes.</p><h2>Why SharaForms is the best tool for this form</h2><p>SharaForms secures votes with authentication options and lets you limit submissions to one per person.</p>', 
            'types' => ['voting_forms', 'polls'],
            'industries' => ['business_forms', 'church_forms'],
            'structure' => $this->structure('Cast Your Vote', [
                ['id' => 'voter_name', 'type' => 'text', 'title' => 'Voter Name', 'required' => true, 'help' => ''],
                ['id' => 'email', 'type' => 'email', 'title' => 'Email', 'required' => true, 'help' => ''],
                ['id' => 'candidate_1', 'type' => 'radio', 'title' => 'Position 1: Choose a candidate', 'required' => true, 'help' => '', 'options' => [['value' => 'candidate_a', 'text' => 'Candidate A'], ['value' => 'candidate_b', 'text' => 'Candidate B'], ['value' => 'candidate_c', 'text' => 'Candidate C'], ['value' => 'abstain', 'text' => 'Abstain']]],
                ['id' => 'candidate_2', 'type' => 'radio', 'title' => 'Position 2: Choose a candidate', 'required' => true, 'help' => '', 'options' => [['value' => 'candidate_a', 'text' => 'Candidate A'], ['value' => 'candidate_b', 'text' => 'Candidate B'], ['value' => 'candidate_c', 'text' => 'Candidate C'], ['value' => 'abstain', 'text' => 'Abstain']]],
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
                ['id' => 'meal_choice', 'type' => 'select', 'title' => 'Meal Preference', 'required' => false, 'help' => '', 'options' => [['value' => 'chicken', 'text' => 'Chicken'], ['value' => 'fish', 'text' => 'Fish'], ['value' => 'vegetarian', 'text' => 'Vegetarian'], ['value' => 'vegan', 'text' => 'Vegan']]],
                ['id' => 'dietary', 'type' => 'text', 'title' => 'Dietary Restrictions / Allergies', 'required' => false, 'help' => ''],
                ['id' => 'song_request', 'type' => 'text', 'title' => 'Song Request', 'required' => false, 'help' => ''],
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
                ['id' => 'inspiration', 'type' => 'files', 'title' => 'Inspiration Images (Optional)', 'required' => false, 'help' => 'Optional', 'max_number_of_files' => 5, 'max_file_size' => 25]
            ], '#ec4899'),
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
                ['id' => 'parent_name', 'type' => 'text', 'title' => 'Parent / Guardian Name', 'required' => true, 'help' => ''],
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
                ['id' => 'rules', 'type' => 'checkbox', 'title' => 'I agree to the tournament rules', 'required' => true, 'help' => '']
            ], '#8b5cf6'),
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
                ['id' => 'notes', 'type' => 'text', 'title' => 'Notes / Overtime Explanation', 'required' => false, 'help' => '', 'multi_lines' => true],
                ['id' => 'accuracy_ack', 'type' => 'checkbox', 'title' => 'I confirm these hours are accurate', 'required' => true, 'help' => '']
            ], '#0891b2'),
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
                ['id' => 'manager_approval', 'type' => 'checkbox', 'title' => 'Manager approval obtained', 'required' => true, 'help' => '']
            ], '#4f46e5'),
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
                ['id' => 'amount_claimed', 'type' => 'number', 'title' => 'Amount Claimed', 'required' => true, 'help' => ''],
                ['id' => 'expense_date_range', 'type' => 'text', 'title' => 'Date(s) of Expense', 'required' => true, 'help' => 'e.g. 12-14 Aug 2026'],
                ['id' => 'explanation', 'type' => 'text', 'title' => 'Explanation', 'required' => true, 'help' => '', 'multi_lines' => true],
                ['id' => 'receipts', 'type' => 'files', 'title' => 'Receipts / Evidence', 'required' => true, 'help' => 'Photos or PDFs', 'max_number_of_files' => 6, 'max_file_size' => 10],
                ['id' => 'policy_ack', 'type' => 'checkbox', 'title' => 'I confirm this claim follows the reimbursement policy', 'required' => true, 'help' => '']
            ], '#0f766e'),
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
                ['id' => 'quantity', 'type' => 'number', 'title' => 'Quantity', 'required' => true, 'help' => ''],
                ['id' => 'delivery_method', 'type' => 'select', 'title' => 'Delivery Method', 'required' => true, 'help' => '', 'options' => [['value' => 'pickup', 'text' => 'Pickup at event'], ['value' => 'ship', 'text' => 'Ship to me']]],
                ['id' => 'shipping_address', 'type' => 'text', 'title' => 'Shipping Address', 'required' => false, 'help' => 'Only if shipping', 'multi_lines' => true]
            ], '#e11d48'),
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
                ['id' => 'service_type', 'type' => 'select', 'title' => 'Delivery or Pickup', 'required' => true, 'help' => '', 'options' => [['value' => 'delivery', 'text' => 'Delivery to venue'], ['value' => 'pickup', 'text' => 'Pickup'], ['value' => 'onsite_staff', 'text' => 'Delivery + On-site Staff']]],
                ['id' => 'venue_address', 'type' => 'text', 'title' => 'Venue Address', 'required' => false, 'help' => 'Required for delivery', 'multi_lines' => true]
            ], '#ca8a04'),
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
                ['id' => 'is_minor', 'type' => 'checkbox', 'title' => 'Participant is under 18', 'required' => false, 'help' => ''],
                ['id' => 'guardian_name', 'type' => 'text', 'title' => 'Parent / Guardian Name', 'required' => false, 'help' => 'Required for minors'],
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
                ['id' => 'is_minor', 'type' => 'checkbox', 'title' => 'Subject is under 18', 'required' => false, 'help' => ''],
                ['id' => 'guardian_signature_note', 'type' => 'text', 'title' => 'Parent / Guardian Name (if minor)', 'required' => false, 'help' => ''],
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
                ['id' => 'improvement', 'type' => 'text', 'title' => 'What could we do to earn a higher score?', 'required' => false, 'help' => '', 'multi_lines' => true],
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
                ['id' => 'plan', 'type' => 'select', 'title' => 'Membership Plan', 'required' => true, 'help' => '', 'options' => [['value' => 'monthly', 'text' => 'Monthly'], ['value' => 'annual', 'text' => 'Annual (save 20%)'], ['value' => 'student', 'text' => 'Student'], ['value' => 'family', 'text' => 'Family'], ['value' => 'trial', 'text' => '7-Day Trial']]],
                ['id' => 'start_date', 'type' => 'date', 'title' => 'Preferred Start Date', 'required' => true, 'help' => ''],
                ['id' => 'fitness_goals', 'type' => 'multi_select', 'title' => 'Fitness Goals', 'required' => true, 'help' => '', 'options' => [['value' => 'strength', 'text' => 'Strength'], ['value' => 'cardio', 'text' => 'Cardio / Endurance'], ['value' => 'flexibility', 'text' => 'Flexibility / Mobility'], ['value' => 'weight_loss', 'text' => 'Weight Management'], ['value' => 'rehab', 'text' => 'Rehabilitation'], ['value' => 'general', 'text' => 'General Fitness']]],
                ['id' => 'emergency_contact', 'type' => 'phone_number', 'title' => 'Emergency Contact Number', 'required' => true, 'help' => ''],
                ['id' => 'health_conditions', 'type' => 'text', 'title' => 'Injuries or Health Conditions', 'required' => false, 'help' => 'Heart conditions, joint injuries, medication considerations', 'multi_lines' => true],
                ['id' => 'waiver_acceptance', 'type' => 'checkbox', 'title' => 'I accept the membership terms and gym rules', 'required' => true, 'help' => '']
            ], '#047857'),
        ];
    }
}

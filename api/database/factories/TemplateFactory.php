<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        return [];
    }

    public function seeded(array $overrides = []): Template
    {
        $data = array_merge([
            'publicly_listed' => true,
        ], $overrides);

        if (!isset($data['questions'])) {
            $data['questions'] = $this->defaultQuestions($data['name'] ?? '');
        }

        return Template::create($data);
    }

    private function defaultQuestions(string $name): array
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
}

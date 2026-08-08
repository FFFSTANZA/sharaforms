<?php

namespace App\Service\AI\Prompts\Form;

use App\Service\AI\Prompts\Prompt;

class GenerateFormFieldsPrompt extends Prompt
{
    protected ?float $temperature = null;

    protected ?int $maxTokens = null;

    protected string $model = 'gpt-5.4-mini';

    /**
     * The prompt template for generating forms
     */
    public const PROMPT_TEMPLATE = <<<'EOD'
        Generate form field(s) based on the description below.

        <field_description>
            {formPrompt}
        </field_description>

        <current_form_context>
        Form Title: {formTitle}
        Existing Fields: {existingFields}
        </current_form_context>
        
        Return a single JSON object with exactly one key: "properties" — an array of field objects that fit the context and complement the existing form structure. Consider the form's purpose and existing fields when generating new fields. Do NOT wrap the array in any other object and do NOT add form-level keys (no "title", "re_fillable", "submit_button_text", ...).

        Presentation mode and constraints:
        {modeConstraints}

        Available field types (mode-aware):
        {allowedFieldTypesList}

        Field JSON contract — the "properties" value must follow this exactly:
        {fieldContract}

        
        HTML formatting for nf-text blocks:
        - Headers: <h1>, <h2> for section titles and subtitles
        - Text formatting: <b> or <strong> for bold, <i> or <em> for italic, <u> for underline, <s> for strikethrough
        - Links: <a href="url">link text</a> for hyperlinks
        - Lists: <ul><li>item</li></ul> for bullet lists, <ol><li>item</li></ol> for numbered lists
        - Colors: <span style="color: #hexcode">colored text</span> for colored text
        - Paragraphs: <p>paragraph text</p> for text blocks with spacing
        Use these HTML tags to create well-structured and visually appealing form content.

        Field generation guidelines:
        - Generate only the fields needed - do not repeat fields already present in the existing form context
        - Choose the most appropriate field type based on the data being collected
        - Consider the existing form context and avoid duplicating fields
        - Use logical field names that clearly describe the data being collected
        - Add helpful placeholder text and help text for complex fields
        - Set appropriate validation (required fields where necessary - do not add * to the field name if required - it's done automatically)
        - Use appropriate width settings for better layout
        - For select/multi-select fields, provide relevant options based on the context
        - For number fields, consider if rating, scale, or slider would be more appropriate
        - For rich text fields, consider if multi-line, matrix, or barcode input would be useful
        
        The generated fields should:
        - Match the field description requirements
        - Complement the existing form structure
        - Use appropriate field types and configurations
        - Include proper validation and help text (leave empty if not needed)
        - Follow the form's overall purpose and flow
    EOD;

    /**
     * JSON schema for form output
     */
    protected ?array $jsonSchema = [
        'type' => 'object',
        'required' => ['properties'],
        'additionalProperties' => false,
        'properties' => [
            'properties' => [
                'type' => 'array',
                'description' => 'Array of form fields and elements',
                'items' => [
                    'anyOf' => [
                        ['$ref' => '#/$defs/textProperty'],
                        ['$ref' => '#/$defs/richTextProperty'],
                        ['$ref' => '#/$defs/dateProperty'],
                        ['$ref' => '#/$defs/urlProperty'],
                        ['$ref' => '#/$defs/phoneNumberProperty'],
                        ['$ref' => '#/$defs/emailProperty'],
                        ['$ref' => '#/$defs/checkboxProperty'],
                        ['$ref' => '#/$defs/selectProperty'],
                        ['$ref' => '#/$defs/multiSelectProperty'],
                        ['$ref' => '#/$defs/matrixProperty'],
                        ['$ref' => '#/$defs/numberProperty'],
                        ['$ref' => '#/$defs/ratingProperty'],
                        ['$ref' => '#/$defs/scaleProperty'],
                        ['$ref' => '#/$defs/sliderProperty'],
                        ['$ref' => '#/$defs/filesProperty'],
                        ['$ref' => '#/$defs/signatureProperty'],
                        ['$ref' => '#/$defs/barcodeProperty'],
                        ['$ref' => '#/$defs/nfTextProperty'],
                        ['$ref' => '#/$defs/nfPageBreakProperty'],
                        ['$ref' => '#/$defs/nfDividerProperty'],
                        ['$ref' => '#/$defs/nfImageProperty'],
                        ['$ref' => '#/$defs/nfVideoProperty'],
                        ['$ref' => '#/$defs/nfCodeProperty']
                    ]
                ]
            ]
        ],
        '$defs' => FormFieldSchemas::FIELD_TYPE_DEFINITIONS
    ];

    public function __construct(
        public string $formPrompt,
        public string $formTitle = '',
        public array $existingFields = [],
        public array $params = []
    ) {
        parent::__construct();
    }

    protected function getSystemMessage(): ?string
    {
        return 'You are an AI assistant specialized in generating form fields. Create appropriate field objects that fit the context and complement existing form structures. Focus on choosing the right field types and configurations based on the data being collected.';
    }

    protected function getPromptTemplate(): string
    {
        return self::PROMPT_TEMPLATE;
    }

    /**
     * Override the execute method to automatically process the output
     */
    public function execute(): array
    {
        $formData = parent::execute();
        return $this->processOutput($formData);
    }

    /**
     * Override getPromptVariables to handle complex formatting
     */
    protected function getPromptVariables(): array
    {
        $variables = parent::getPromptVariables();

        // Add the formatted existing fields
        $variables['{existingFields}'] = $this->formatExistingFields();

        $rules = PresentationRules::buildContext($this->params);
        $variables['{modeConstraints}'] = $rules['constraintsText'];
        $variables['{widthGuidance}'] = $rules['mode'] === PresentationRules::MODE_FOCUSED
            ? "In focused mode, do not use width options. Each step contains a single full-width question."
            : "Field width options:\n- width: \"full\" (default)\n- width: \"1/2\"\n- width: \"1/3\"\n- width: \"2/3\"\n- width: \"1/4\"\n- width: \"3/4\"\nFields can share rows when room allows.";
        $variables['{allowedFieldTypesList}'] = $this->formatAllowedTypes($rules['allowedFieldTypes']);
        $variables['{fieldContract}'] = $this->buildFieldsContract();
        $variables['{layoutGuidance}'] = PresentationRules::buildLayoutGuidance($rules['mode']);

        return $variables;
    }

    /**
     * The top-level contract for the fields endpoint: a wrapper object whose
     * only key is "properties" (providers without schema enforcement follow
     * this literal shape).
     */
    private function buildFieldsContract(): string
    {
        return <<<'EOD'
        {
          "properties": [
            {
              "type": "text",
              "name": "Full name",
              "help": "So we can address you personally.",
              "placeholder": "Jane Doe",
              "hidden": false,
              "required": true,
              "width": "1/2",
              "multi_lines": false,
              "generates_uuid": false,
              "max_char_limit": 500,
              "hide_field_name": false,
              "show_char_limit": false
            },
            {
              "type": "select",
              "name": "How did you hear about us?",
              "help": "",
              "placeholder": "",
              "hidden": false,
              "required": true,
              "width": "full",
              "without_dropdown": true,
              "select": { "options": [ { "name": "Search engine", "id": "search-engine" }, { "name": "Social media", "id": "social-media" } ] }
            },
            {
              "type": "checkbox",
              "name": "Subscribe to newsletter",
              "help": "",
              "placeholder": "",
              "hidden": false,
              "required": false,
              "width": "full",
              "use_toggle_switch": false
            }
          ]
        }
        EOD;
    }

    private function formatAllowedTypes(array $types): string
    {
        $map = [
            'text' => 'text: Text input (use multi_lines: true for multi-line text)',
            'rich_text' => 'rich_text: Rich text input',
            'date' => 'date: Date picker (use with_time: true to include time selection)',
            'url' => 'url: URL input with validation',
            'phone_number' => 'phone_number: Phone number input',
            'email' => 'email: Email input with validation',
            'checkbox' => 'checkbox: Single checkbox for yes/no (use use_toggle_switch: true for toggle switch)',
            'select' => 'select: Dropdown selection (use without_dropdown: true for radio buttons, recommended for <5 options)',
            'multi_select' => 'multi_select: Multiple selection (use without_dropdown: true for checkboxes, recommended for <5 options)',
            'matrix' => 'matrix: Matrix input with rows and columns',
            'number' => 'number: Numeric input',
            'rating' => 'rating: Star rating',
            'scale' => 'scale: Numeric scale',
            'slider' => 'slider: Slider selection',
            'files' => 'files: File upload',
            'signature' => 'signature: Signature pad',
            'barcode' => 'barcode: Barcode scanner',
            'nf-text' => 'nf-text: Rich text content (not an input field)',
            'nf-page-break' => 'nf-page-break: Page break for multi-page forms',
            'nf-divider' => 'nf-divider: Visual divider (not an input field)',
            'nf-image' => 'nf-image: Image element',
            'nf-video' => 'nf-video: Video element',
            'nf-code' => 'nf-code: Code block',
        ];

        $lines = array_map(function ($t) use ($map) {
            return '- ' . ($map[$t] ?? $t);
        }, $types);

        return implode("\n", $lines);
    }

    /**
     * Format existing fields for display in the prompt
     */
    private function formatExistingFields(): string
    {
        if (empty($this->existingFields)) {
            return 'No existing fields';
        }

        $formatted = [];
        foreach ($this->existingFields as $field) {
            $fieldInfo = $field['name'] ?? 'Unnamed field';
            $fieldType = $field['type'] ?? 'Unknown type';
            $formatted[] = "- {$fieldInfo} ({$fieldType})";
        }

        return implode("\n", $formatted);
    }

    /**
     * Process the AI output to ensure it meets our requirements
     */
    public function processOutput(array $formFields): array
    {
        // Models without schema enforcement may return a bare array of
        // properties instead of the {"properties": [...]} wrapper.
        $properties = array_is_list($formFields) ? $formFields : ($formFields['properties'] ?? []);
        // Normalize model-invented keys onto the app contract before anything else.
        $properties = FormSchemaNormalizer::normalizeFields($properties);
        $processedFields = FormFieldSchemas::processFields($properties);

        // Optimize fields for focused mode
        $mode = $this->params['presentation_style'] ?? PresentationRules::MODE_CLASSIC;
        $optimizedFormData = FocusedFormOptimizer::optimizeFormProperties(
            ['properties' => $processedFields],
            $mode
        );

        return $optimizedFormData['properties'];
    }
}

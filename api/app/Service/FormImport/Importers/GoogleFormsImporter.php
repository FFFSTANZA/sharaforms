<?php

namespace App\Service\FormImport\Importers;

use App\Service\FormImport\FormImportException;

class GoogleFormsImporter extends AbstractImporter
{
    public function import(array $importData): array
    {
        $formId = $importData['form_id'] ?? null;
        if (! is_string($formId) || $formId === '') {
            $formId = $this->extractFormId($importData['url'] ?? '');
        }

        if (! $formId) {
            throw new FormImportException('Could not extract a form ID from the URL. Please use a Google Forms URL like docs.google.com/forms/d/FORM_ID/edit.');
        }

        $html = $this->fetchHtml($this->buildViewformUrl($formId));

        return $this->parseFormData($this->extractFormData($html));
    }

    public function validate(array $importData): bool
    {
        if (is_string($importData['form_id'] ?? null) && $importData['form_id'] !== '') {
            return true;
        }

        return parent::validate($importData);
    }

    public function allowedDomains(): array
    {
        return ['docs.google.com'];
    }

    private function buildViewformUrl(string $formId): string
    {
        return "https://docs.google.com/forms/d/e/{$formId}/viewform";
    }

    private function extractFormId(string $url): ?string
    {
        // Published URLs use /forms/d/e/{formId}/viewform — "e" is a literal
        // segment, so look one past it.
        if (preg_match('#/forms/d/e/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        // Edit URLs: /forms/d/{formId}/...
        if (preg_match('#/forms/d/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractFormData(string $html): array
    {
        // Match a balanced bracket expression so a literal "];" inside a
        // string (e.g. an option label like "Use [A]; [B]") cannot truncate
        // the JSON at the first "];".
        if (! preg_match('/FB_PUBLIC_LOAD_DATA_\s*=\s*(\[(?:[^\[\]]+|(?1))*\]);/s', $html, $matches)) {
            throw new FormImportException('Could not find form data in the page. Make sure the form is public and its URL is correct.');
        }

        $data = json_decode($matches[1], true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            throw new FormImportException('Failed to parse form data from page.');
        }

        return $data;
    }

    private function parseFormData(array $data): array
    {
        $title = $this->sanitizeText($data[1][8] ?? 'Imported Google Form', 255);
        $description = $this->sanitizeText($data[1][0] ?? '', 8000);
        $items = $data[1][1] ?? [];

        $properties = [];

        if ($description !== '') {
            $properties[] = $this->mapTextItem([
                'title' => '',
                'description' => $description,
            ]);
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $mapped = $this->mapItem($item);
            if ($mapped !== null) {
                $properties[] = $mapped;
            }
        }

        return [
            'title' => $title,
            'properties' => $properties,
        ];
    }

    private function mapTextItem(array $item): ?array
    {
        $title = $this->sanitizeText($item['title'] ?? '', 255);
        $description = $this->sanitizeText($item['description'] ?? '', 8000);

        $content = '';
        if ($title !== '') {
            $content .= '<p><strong>' . e($title) . '</strong></p>';
        }
        if ($description !== '') {
            $content .= '<p>' . e($description) . '</p>';
        }

        if ($content === '') {
            return null;
        }

        return [
            'id' => $this->generateFieldId(),
            'name' => $title ?: 'Text',
            'type' => 'nf-text',
            'content' => $content,
        ];
    }

    private function mapItem(array $item): ?array
    {
        $text = $this->sanitizeText($item[1] ?? '', 255);
        $help = $this->sanitizeText($item[2] ?? '', 1000);
        $typeCode = (int) ($item[3] ?? -1);
        $extra = $item[4] ?? null;

        if ($typeCode === 6) {
            $content = '';
            if ($text !== '') {
                $content .= '<p><strong>' . e($text) . '</strong></p>';
            }
            // Google puts the section header's description at item[2]; its
            // extra slot (item[4]) is null for section headers.
            $description = $this->sanitizeText($item[2] ?? '', 8000);
            if ($description !== '') {
                $content .= '<p>' . e($description) . '</p>';
            }

            if ($content === '') {
                return null;
            }

            return [
                'id' => $this->generateFieldId(),
                'name' => $text !== '' ? $text : 'Text',
                'type' => 'nf-text',
                'content' => $content,
            ];
        }

        $property = $this->baseProperty($text !== '' ? $text : 'Untitled', 'text', false);

        if ($help !== '') {
            $property['help'] = $help;
        }

        $required = $this->isRequired($extra);
        $property['required'] = $required;

        $labels = [];
        if (is_array($extra) && is_array($extra[0][1] ?? null)) {
            foreach ($extra[0][1] as $option) {
                if (! is_array($option)) {
                    continue;
                }
                $label = $this->sanitizeText($option[0] ?? '', 255);
                // Google flags the "Other" choice with an empty label and
                // option[4] === 1 — surface it as a regular "Other" option
                // instead of dropping it with the empty-label filter.
                if ($label === '' && ($option[4] ?? null) === 1) {
                    $label = 'Other';
                }
                if ($label !== '') {
                    $labels[] = $label;
                }
            }
        }

        return match ($typeCode) {
            0 => $this->mapTextQuestion($property),
            1 => $this->mapParagraphQuestion($property),
            2 => $this->mapRadioQuestion($property, $labels),
            3 => $this->mapDropdownQuestion($property, $labels),
            4 => $this->mapCheckboxesQuestion($property, $labels),
            5 => $this->mapScaleQuestion($property, $extra),
            7, 10 => $this->mapGridQuestion($item, $typeCode, $required),
            8 => $this->mapTimeQuestion($property),
            9 => $this->mapDateQuestion($property),
            default => $property,
        };
    }

    private function isRequired(mixed $extra): bool
    {
        if (! is_array($extra) || ! is_array($extra[0] ?? null)) {
            return false;
        }

        return (bool) ($extra[0][2] ?? 0);
    }

    private function mapTextQuestion(array $property): array
    {
        return $property;
    }

    private function mapParagraphQuestion(array $property): array
    {
        $property['multi_lines'] = true;

        return $property;
    }

    private function mapRadioQuestion(array $property, array $labels): array
    {
        $property['type'] = 'select';
        $property['without_dropdown'] = true;

        return $this->attachOptions($property, $labels);
    }

    private function mapDropdownQuestion(array $property, array $labels): array
    {
        $property['type'] = 'select';

        return $this->attachOptions($property, $labels);
    }

    private function mapCheckboxesQuestion(array $property, array $labels): array
    {
        $property['type'] = 'multi_select';
        $property['without_dropdown'] = true;

        return $this->attachOptions($property, $labels);
    }

    private function mapScaleQuestion(array $property, array $extra): array
    {
        $property['type'] = 'scale';

        // Real shape: extra[0] = [entryId, [["1"],["2"],...], required, [lowLabel, highLabel]]
        $points = $extra[0][1] ?? [];
        $min = 1;
        $max = 5;
        if (is_array($points) && $points !== []) {
            $first = is_array($points[0] ?? null) ? ($points[0][0] ?? null) : null;
            $last = is_array($points[count($points) - 1] ?? null) ? ($points[count($points) - 1][0] ?? null) : null;
            if (is_numeric($first)) {
                $min = (int) $first;
            }
            if (is_numeric($last)) {
                $max = (int) $last;
            }
        }

        $property['scale_min_value'] = $min;
        $property['scale_max_value'] = $max;
        $property['scale_step_value'] = 1;

        if ($property['scale_max_value'] <= $property['scale_min_value']) {
            $property['scale_max_value'] = $property['scale_min_value'] + 5;
        }

        $labels = $extra[0][3] ?? null;
        $lowLabel = is_array($labels) ? $this->sanitizeText($labels[0] ?? '', 255) : '';
        $highLabel = is_array($labels) ? $this->sanitizeText($labels[1] ?? '', 255) : '';
        if ($lowLabel !== '' || $highLabel !== '') {
            $property['help'] = trim($lowLabel . ' → ' . $highLabel);
        }

        return $property;
    }

    private function mapDateQuestion(array $property): array
    {
        $property['type'] = 'date';

        return $property;
    }

    private function mapTimeQuestion(array $property): array
    {
        $property['type'] = 'date';
        $property['with_time'] = true;

        return $property;
    }

    private function mapGridQuestion(array $item, int $typeCode, bool $required): ?array
    {
        // Real shape: item[4] (extra) is a list of row entries, one per grid
        // row. Each entry: [colEntryId, [["col1"],["col2"],...], required,
        // [rowLabel], ...]. Columns come from the first row; every row carries
        // the full column list.
        $entries = $item[4] ?? null;
        if (! is_array($entries) || $entries === []) {
            return null;
        }

        $rows = [];
        $columns = [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }
            $rowLabel = $this->sanitizeText($entry[3][0] ?? '', 255);
            if ($rowLabel !== '') {
                $rows[] = $rowLabel;
            }
            if ($columns === [] && is_array($entry[1] ?? null)) {
                foreach ($entry[1] as $column) {
                    $label = $this->sanitizeText(is_array($column) ? ($column[0] ?? '') : $column, 255);
                    if ($label !== '' && ! in_array($label, $columns, true)) {
                        $columns[] = $label;
                    }
                }
            }
        }

        if ($rows === [] || $columns === []) {
            return null;
        }

        $title = $this->sanitizeText($item[1] ?? 'Grid', 255);

        return [
            'id' => $this->generateFieldId(),
            'name' => $title !== '' ? $title : 'Grid',
            'type' => 'matrix',
            'required' => $required,
            'hidden' => false,
            'rows' => $rows,
            'columns' => $columns,
        ];
    }

    private function attachOptions(array $property, array $labels): array
    {
        if ($labels !== []) {
            $property[$property['type']]['options'] = array_map(
                fn ($label) => ['id' => $this->generateFieldId(), 'name' => $label],
                $labels
            );
        }

        return $property;
    }

    private function baseProperty(string $name, string $type, bool $required): array
    {
        return [
            'id' => $this->generateFieldId(),
            'name' => $name,
            'type' => $type,
            'required' => $required,
            'hidden' => false,
        ];
    }
}

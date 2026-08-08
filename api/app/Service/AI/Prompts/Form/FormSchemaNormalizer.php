<?php

namespace App\Service\AI\Prompts\Form;

/**
 * Normalizes AI-generated form output into the app's exact form/property
 * contract, regardless of the AI provider or how loosely the model labeled
 * its output (label, help_text, submit_text, nested options, …).
 *
 * This is the safety net that guarantees non-OpenAI providers (Gemini/Groq),
 * which cannot honor strict JSON-schema mode, still produce forms that
 * validate against FormPropertiesRule and render in the editor.
 */
class FormSchemaNormalizer
{
    public const VALID_WIDTHS = ['full', '1/2', '1/3', '2/3', '3/4', '1/4'];

    /**
     * Keys the models like to invent, mapped onto the app's contract.
     */
    private const FIELD_KEY_ALIASES = [
        'label' => 'name',
        'question' => 'name',
        'field_name' => 'name',
        'title' => 'name',
        'help_text' => 'help',
        'helper_text' => 'help',
        'instructions' => 'help',
        'hint' => 'help',
    ];

    /**
     * @return array<string, string>
     */
    private const FORM_KEY_ALIASES = [
        'submit_text' => 'submit_button_text',
        'button_text' => 'submit_button_text',
        'title_text' => 'submitted_text',
        'success_text' => 'submitted_text',
        'thanks_text' => 'submitted_text',
        'refill_button_text' => 're_fill_button_text',
        'next_btn_text' => 'submit_button_text',
    ];

    private const BOOLEAN_TRUE = [true, 1, '1', 'true', 'yes', 'on'];

    /**
     * HTML the AI may emit for nf-text/submitted_text, with dangerous
     * constructs removed (scripts, event handlers, javascript: URLs,
     * non-widget attributes). Anything else is stripped to plain text.
     */
    private const ALLOWED_HTML_TAGS = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'p', 'b', 'strong', 'i', 'em', 'u', 's',
        'a', 'ul', 'ol', 'li', 'br', 'hr', 'span', 'div',
    ];

    private const BLOCKED_HTML_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'link', 'meta', 'base', 'form', 'input', 'button', 'canvas'];

    public static function normalizeFormData(array $formData): array
    {
        foreach (self::FORM_KEY_ALIASES as $alias => $target) {
            if (isset($formData[$alias]) && !isset($formData[$target])) {
                $formData[$target] = $formData[$alias];
                unset($formData[$alias]);
            }
        }

        foreach (['re_fillable', 'use_captcha', 'uppercase_labels'] as $booleanKey) {
            if (array_key_exists($booleanKey, $formData)) {
                $formData[$booleanKey] = self::toBoolean($formData[$booleanKey]);
            }
        }

        if (isset($formData['submit_button_text'])) {
            $formData['submit_button_text'] = (string) $formData['submit_button_text'];
        }

        if (isset($formData['re_fill_button_text'])) {
            $formData['re_fill_button_text'] = (string) $formData['re_fill_button_text'];
        }

        if (isset($formData['submitted_text'])) {
            $formData['submitted_text'] = self::sanitizeHtml((string) $formData['submitted_text']);
        }

        $formData['properties'] = self::normalizeProperties($formData['properties'] ?? []);

        return $formData;
    }

    /**
     * Normalize a standalone array of generated fields.
     */
    public static function normalizeFields(array $fields): array
    {
        return self::normalizeProperties($fields);
    }

    public static function normalizeProperties(array $properties): array
    {
        return array_values(array_map(
            static fn ($property) => is_array($property) ? self::normalizeProperty($property) : $property,
            $properties
        ));
    }

    private static function normalizeProperty(array $property): array
    {
        // Flatten an eventual "core" wrapper (some providers nest base keys).
        if (isset($property['core']) && is_array($property['core'])) {
            foreach ($property['core'] as $coreKey => $coreValue) {
                if (!isset($property[$coreKey])) {
                    $property[$coreKey] = $coreValue;
                }
            }
        }

        // Alias common model-invented keys onto our contract.
        foreach (self::FIELD_KEY_ALIASES as $alias => $target) {
            if (!isset($property[$target]) && isset($property[$alias]) && is_string($property[$alias])) {
                $property[$target] = trim($property[$alias]);
            }
        }

        $type = $property['type'] ?? null;
        $property['type'] = $type === null ? 'text' : (string) $type;

        // String defaults.
        foreach (['name', 'help', 'placeholder'] as $stringKey) {
            if (!isset($property[$stringKey])) {
                $property[$stringKey] = '';
            } elseif (!is_string($property[$stringKey])) {
                $property[$stringKey] = (string) $property[$stringKey];
            }
        }

        // Boolean defaults & coercion.
        foreach (['hidden', 'required', 'multi_lines', 'generates_uuid', 'hide_field_name', 'show_char_limit', 'use_toggle_switch', 'without_dropdown', 'use_focused_selector', 'use_focused_toggle', 'allow_creation'] as $booleanKey) {
            if (array_key_exists($booleanKey, $property)) {
                $property[$booleanKey] = self::toBoolean($property[$booleanKey]);
            }
        }

        // Width must be one of the allowed values.
        if (!isset($property['width']) || !in_array($property['width'], self::VALID_WIDTHS, true)) {
            $property['width'] = 'full';
        }

        // Drop keys models invent that are not part of our contract and would
        // linger in the JSON (e.g. min/max/accept on inputs).
        foreach (['min', 'max', 'accept', 'format', 'separator', 'items', 'enum'] as $inventedKey) {
            if (array_key_exists($inventedKey, $property)) {
                unset($property[$inventedKey]);
            }
        }

        // Nested option lists: accept strings or {label}/{name}/{id} shapes.
        if (in_array($property['type'], ['select', 'multi_select'], true)) {
            $property = self::normalizeOptions($property);
        }

        // Type-specific defaults.
        $property = self::applyTypeDefaults($property);

        return $property;
    }

    private static function normalizeOptions(array $property): array
    {
        $type = $property['type'];
        $options = $property[$type]['options'] ?? $property['options'] ?? null;

        if (!is_array($options)) {
            unset($property['options']);
            if (!isset($property[$type]['options'])) {
                $property[$type]['options'] = [['name' => 'Option 1', 'id' => 'option-1']];
            }

            return $property;
        }

        $normalizedOptions = [];
        foreach ($options as $option) {
            if (is_string($option)) {
                $normalizedOptions[] = ['name' => $option, 'id' => $option];
                continue;
            }
            if (!is_array($option)) {
                continue;
            }

            $name = $option['name'] ?? $option['label'] ?? $option['text'] ?? '';
            $id = $option['id'] ?? $name;

            if ($name === '') {
                continue;
            }

            $normalized = ['name' => (string) $name, 'id' => (string) ($id !== '' ? $id : $name)];
            if (isset($option['image'])) {
                $normalized['image'] = $option['image'];
            }
            $normalizedOptions[] = $normalized;
        }

        $property[$type]['options'] = $normalizedOptions !== [] ? $normalizedOptions : [['name' => 'Option 1', 'id' => 'option-1']];
        unset($property['options']);

        return $property;
    }

    private static function applyTypeDefaults(array $property): array
    {
        switch ($property['type']) {
            case 'text':
            case 'rich_text':
                if (!array_key_exists('multi_lines', $property)) {
                    $property['multi_lines'] = false;
                }
                if (!array_key_exists('max_char_limit', $property)) {
                    $property['max_char_limit'] = 500;
                } elseif (is_string($property['max_char_limit']) && is_numeric($property['max_char_limit'])) {
                    $property['max_char_limit'] = (int) $property['max_char_limit'];
                }
                break;

            case 'rating':
                if (!array_key_exists('rating_max_value', $property) || !is_numeric($property['rating_max_value'])) {
                    $property['rating_max_value'] = 5;
                }
                break;

            case 'scale':
                $property['scale_min_value'] = self::intOrDefault($property['scale_min_value'] ?? null, 1);
                $property['scale_max_value'] = self::intOrDefault($property['scale_max_value'] ?? null, 5);
                $property['scale_step_value'] = self::intOrDefault($property['scale_step_value'] ?? null, 1);
                break;

            case 'slider':
                $property['slider_min_value'] = self::intOrDefault($property['slider_min_value'] ?? null, 0);
                $property['slider_max_value'] = self::intOrDefault($property['slider_max_value'] ?? null, 50);
                $property['slider_step_value'] = self::intOrDefault($property['slider_step_value'] ?? null, 1);
                break;

            case 'barcode':
                $property['decoders'] = is_array($property['decoders'] ?? null) && $property['decoders'] !== []
                    ? array_values(array_map('strval', $property['decoders']))
                    : ['ean_reader', 'ean_8_reader'];
                break;

            case 'nf-text':
                if (!isset($property['content']) || !is_string($property['content'])) {
                    $property['content'] = '<p>' . e($property['name'] ?? '') . '</p>';
                } else {
                    $property['content'] = self::sanitizeHtml($property['content']);
                }
                break;

            case 'nf-code':
                // The AI never needs to inject raw code: drop any content it
                // invented so it can not smuggle markup into the v-html renderer.
                unset($property['content'], $property['code']);
                break;

            case 'nf-page-break':
                $property['next_btn_text'] = (string) ($property['next_btn_text'] ?? 'Next');
                $property['previous_btn_text'] = (string) ($property['previous_btn_text'] ?? 'Previous');
                break;

            case 'matrix':
                $property['rows'] = is_array($property['rows'] ?? null) ? array_values($property['rows']) : ['Row 1'];
                $property['columns'] = is_array($property['columns'] ?? null) ? array_values($property['columns']) : ['1', '2', '3'];
                break;
        }

        return $property;
    }

    private static function intOrDefault(mixed $value, int $default): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Sanitize AI-produced HTML down to the formatting tags the product
     * supports, removing scripts, event handlers and dangerous URLs.
     */
    public static function sanitizeHtml(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        // Remove dangerous tags together with their content.
        $blocked = implode('|', self::BLOCKED_HTML_TAGS);
        $html = preg_replace('/<\s*(' . $blocked . ')\b[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $html);
        $html = preg_replace('/<\s*(' . $blocked . ')\b[^>]*\/?\s*>/i', '', $html);

        // Strip event handler attributes and dangerous URL schemes.
        $html = preg_replace('/\s+on[a-z][a-z0-9_]*\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace_callback('/\s+(href|src|action|background|xlink:href)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', function (array $m): string {
            return preg_match('/\b(?:javascript|vbscript|data)\s*:/i', $m[2]) === 1
                ? " {$m[1]}=\"#\""
                : $m[0];
        }, $html);

        // Rebuild every remaining tag from a strict allowlist.
        $html = preg_replace_callback('/<(\/?)([a-zA-Z][a-zA-Z0-9-]*)((?:\s+[^<>]*?)?)(\/?)>/', function ($matches) {
            $closing = $matches[1] !== '';
            $tag = strtolower($matches[2]);
            $selfClosing = $matches[4] !== '';

            if ($closing) {
                return in_array($tag, self::ALLOWED_HTML_TAGS, true) ? "</{$tag}>" : '';
            }

            if (! in_array($tag, self::ALLOWED_HTML_TAGS, true)) {
                return '';
            }

            $attrs = self::sanitizeTagAttributes($tag, $matches[3]);

            return $attrs === ''
                ? ($selfClosing ? "<{$tag}/>" : "<{$tag}>")
                : "<{$tag} {$attrs}>";
        }, $html);

        // Any angle brackets that survived are inert plain text: they no longer
        // form valid tags (all allowed tags were rebuilt above) and browsers
        // render them literally.
        return $html;
    }

    private static function sanitizeTagAttributes(string $tag, string $rawAttrs): string
    {
        $allowed = [];

        if (preg_match_all('/([a-z:_-]+)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', $rawAttrs, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = strtolower($match[1]);
                $value = trim($match[2], "\"'");

                if ($name === 'href' && $tag === 'a' && self::isSafeUrl($value)) {
                    $allowed[] = "href=\"" . self::escapeAttr($value) . "\"";
                } elseif ($name === 'style' && $tag === 'span' && self::isSafeStyle($value)) {
                    $allowed[] = "style=\"" . self::escapeAttr($value) . "\"";
                }
            }
        }

        return implode(' ', $allowed);
    }

    private static function isSafeUrl(string $url): bool
    {
        return preg_match('/^(?:https?:|mailto:|tel:|#|\/)/i', $url) === 1
            || ! preg_match('/^[a-z][a-z0-9+.-]*:/i', $url);
    }

    private static function isSafeStyle(string $style): bool
    {
        return preg_match('/^(?:color|background-color)\s*:\s*#[0-9a-f]{3,8}\s*;?$/i', $style) === 1;
    }

    private static function escapeAttr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_string($value)) {
            return in_array(strtolower((string) $value), self::BOOLEAN_TRUE, true);
        }

        return (bool) $value;
    }
}
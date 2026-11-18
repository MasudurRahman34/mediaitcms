<?php

namespace App\Services\Sections;

/**
 * Single source of truth for every page-builder section type.
 *
 * The same definition drives:
 *  - the Blade partial that renders the section on the public site,
 *  - the admin builder's generated form controls,
 *  - server-side validation of section payloads.
 */
class SectionRegistry
{
    /**
     * @return array<string, array{
     *     label: string,
     *     description: string,
     *     icon: string,
     *     fields: array<int, array<string, mixed>>
     * }>
     */
    public function all(): array
    {
        return [
            'hero' => [
                'label' => 'Hero banner',
                'description' => 'Primary headline, supporting copy and calls to action.',
                'icon' => 'layout-panel-top',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow', 'Small label above the heading'),
                    $this->text('heading', 'Heading', 'Main page headline', true),
                    $this->textarea('subheading', 'Subheading', 'Supporting paragraph'),
                    $this->cta('primary_cta', 'Primary button'),
                    $this->cta('secondary_cta', 'Secondary button'),
                    $this->image('image', 'Side image'),
                    $this->select('align', 'Alignment', ['left' => 'Content left', 'center' => 'Centered'], 'left'),
                    $this->boolean('show_gradient', 'Show brand gradient background', true),
                ],
            ],
            'rich_text' => [
                'label' => 'Rich text',
                'description' => 'Long-form copy with an optional supporting image.',
                'icon' => 'text',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->richtext('body', 'Body copy', true),
                    $this->image('image', 'Image'),
                    $this->select('image_position', 'Image position', ['left' => 'Left', 'right' => 'Right'], 'right'),
                ],
            ],
            'stats' => [
                'label' => 'Statistics',
                'description' => 'Animated counters highlighting company numbers.',
                'icon' => 'chart-no-axes-column',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->textarea('intro', 'Intro'),
                    $this->repeater('items', 'Statistics', [
                        $this->text('value', 'Value', 'Numeric value, e.g. 120'),
                        $this->text('suffix', 'Suffix', 'e.g. + or %'),
                        $this->text('label', 'Label', 'e.g. Projects delivered'),
                    ], true),
                ],
            ],
            'features_grid' => [
                'label' => 'Features grid',
                'description' => 'Card grid for benefits, values or capabilities.',
                'icon' => 'layout-grid',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->textarea('intro', 'Intro'),
                    $this->select('columns', 'Columns', ['2' => '2 columns', '3' => '3 columns', '4' => '4 columns'], '3'),
                    $this->repeater('items', 'Cards', [
                        $this->icon('icon', 'Icon'),
                        $this->text('title', 'Title', null, true),
                        $this->textarea('description', 'Description'),
                    ], true),
                ],
            ],
            'services_grid' => [
                'label' => 'Services preview',
                'description' => 'Pull services from the CMS into this page.',
                'icon' => 'briefcase',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->textarea('intro', 'Intro'),
                    $this->select('source', 'Selection', ['featured' => 'Featured services', 'latest' => 'Latest services', 'manual' => 'Choose manually'], 'featured'),
                    $this->number('limit', 'Maximum items', 3),
                    $this->boolean('show_cta', 'Show "view all" link', true),
                ],
            ],
            'solutions_grid' => [
                'label' => 'Solutions preview',
                'description' => 'Showcase software solutions built by the company.',
                'icon' => 'layers',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->textarea('intro', 'Intro'),
                    $this->select('source', 'Selection', ['featured' => 'Featured solutions', 'latest' => 'Latest solutions', 'manual' => 'Choose manually'], 'featured'),
                    $this->number('limit', 'Maximum items', 3),
                    $this->boolean('show_cta', 'Show "view all" link', true),
                ],
            ],
            'articles_grid' => [
                'label' => 'Articles',
                'description' => 'Featured or latest articles, placed by the CMS.',
                'icon' => 'newspaper',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->textarea('intro', 'Intro'),
                    $this->select('source', 'Selection', ['home_featured' => 'Placed as Home Featured', 'home_latest' => 'Placed as Home Latest', 'latest' => 'Latest published'], 'latest'),
                    $this->number('limit', 'Maximum items', 3),
                    $this->boolean('show_cta', 'Show "view all" link', true),
                ],
            ],
            'team_grid' => [
                'label' => 'Team',
                'description' => 'Team member cards from the CMS.',
                'icon' => 'users',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->textarea('intro', 'Intro'),
                    $this->number('limit', 'Maximum members', 4),
                ],
            ],
            'faq' => [
                'label' => 'FAQ',
                'description' => 'Expandable question and answer list.',
                'icon' => 'circle-help',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->textarea('intro', 'Intro'),
                    $this->repeater('items', 'Questions', [
                        $this->text('question', 'Question', null, true),
                        $this->textarea('answer', 'Answer', null, true),
                    ], true),
                ],
            ],
            'logos' => [
                'label' => 'Logo strip',
                'description' => 'Client or partner logos.',
                'icon' => 'badge-check',
                'fields' => [
                    $this->text('heading', 'Heading'),
                    $this->repeater('items', 'Logos', [
                        $this->image('media_id', 'Logo'),
                        $this->text('label', 'Name'),
                    ], true),
                ],
            ],
            'gallery' => [
                'label' => 'Gallery',
                'description' => 'Image grid with lightbox.',
                'icon' => 'images',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->repeater('items', 'Images', [
                        $this->image('media_id', 'Image', true),
                        $this->text('caption', 'Caption'),
                    ], true),
                ],
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'description' => 'Client quotes.',
                'icon' => 'quote',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading'),
                    $this->repeater('items', 'Quotes', [
                        $this->textarea('quote', 'Quote', null, true),
                        $this->text('name', 'Name', null, true),
                        $this->text('role', 'Role / company'),
                        $this->image('avatar', 'Avatar'),
                    ], true),
                ],
            ],
            'cta' => [
                'label' => 'Call to action',
                'description' => 'Closing conversion block with buttons.',
                'icon' => 'megaphone',
                'fields' => [
                    $this->text('eyebrow', 'Eyebrow'),
                    $this->text('heading', 'Heading', null, true),
                    $this->textarea('body', 'Body'),
                    $this->cta('primary_cta', 'Primary button'),
                    $this->cta('secondary_cta', 'Secondary button'),
                    $this->select('style', 'Background', ['gradient' => 'Brand gradient', 'panel' => 'Panel', 'minimal' => 'Minimal'], 'gradient'),
                ],
            ],
            'contact_details' => [
                'label' => 'Contact details',
                'description' => 'Company contact cards from Settings.',
                'icon' => 'map-pin',
                'fields' => [
                    $this->text('heading', 'Heading'),
                    $this->textarea('intro', 'Intro'),
                    $this->boolean('show_form', 'Show enquiry form on this page', false),
                ],
            ],
        ];
    }

    public function has(string $type): bool
    {
        return array_key_exists($type, $this->all());
    }

    /** @return array<string, mixed>|null */
    public function definition(string $type): ?array
    {
        return $this->all()[$type] ?? null;
    }

    /** @return array<int, string> */
    public function types(): array
    {
        return array_keys($this->all());
    }

    /**
     * Default payload for a freshly added section.
     *
     * @return array<string, mixed>
     */
    public function defaults(string $type): array
    {
        $definition = $this->definition($type);

        if (! $definition) {
            return [];
        }

        $content = [];

        foreach ($definition['fields'] as $field) {
            $content[$field['name']] = $field['default'] ?? match ($field['type']) {
                'boolean' => false,
                'number' => 3,
                'repeater' => [],
                'select' => array_key_first($field['options'] ?? ['none' => 'None']),
                'cta' => ['label' => '', 'url' => ''],
                default => '',
            };
        }

        return $content;
    }

    /**
     * Validation rules derived from the field schema, keyed by `content.*`.
     *
     * @return array<string, mixed>
     */
    public function rules(string $type): array
    {
        $definition = $this->definition($type);

        if (! $definition) {
            return [];
        }

        $rules = [];

        foreach ($definition['fields'] as $field) {
            $key = 'content.'.$field['name'];
            $rules[$key] = $field['rules'] ?? ($field['required'] ?? false ? ['required'] : ['nullable']);
        }

        return $rules;
    }

    private function text(string $name, string $label, ?string $hint = null, bool $required = false): array
    {
        return [
            'name' => $name,
            'type' => 'text',
            'label' => $label,
            'hint' => $hint,
            'required' => $required,
            'rules' => $required ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'],
        ];
    }

    private function textarea(string $name, string $label, ?string $hint = null, bool $required = false): array
    {
        return [
            'name' => $name,
            'type' => 'textarea',
            'label' => $label,
            'hint' => $hint,
            'required' => $required,
            'rules' => $required ? ['required', 'string', 'max:2000'] : ['nullable', 'string', 'max:2000'],
        ];
    }

    private function richtext(string $name, string $label, bool $required = false): array
    {
        return [
            'name' => $name,
            'type' => 'richtext',
            'label' => $label,
            'required' => $required,
            'rules' => $required ? ['required', 'string'] : ['nullable', 'string'],
        ];
    }

    private function image(string $name, string $label, bool $required = false): array
    {
        return [
            'name' => $name,
            'type' => 'image',
            'label' => $label,
            'required' => $required,
            'rules' => $required ? ['required', 'integer', 'exists:media,id'] : ['nullable', 'integer', 'exists:media,id'],
        ];
    }

    private function icon(string $name, string $label): array
    {
        return [
            'name' => $name,
            'type' => 'icon',
            'label' => $label,
            'rules' => ['nullable', 'string', 'max:64'],
        ];
    }

    private function number(string $name, string $label, int $default = 3): array
    {
        return [
            'name' => $name,
            'type' => 'number',
            'label' => $label,
            'default' => $default,
            'rules' => ['nullable', 'integer', 'min:1', 'max:24'],
        ];
    }

    private function boolean(string $name, string $label, bool $default = false): array
    {
        return [
            'name' => $name,
            'type' => 'boolean',
            'label' => $label,
            'default' => $default,
            'rules' => ['boolean'],
        ];
    }

    private function select(string $name, string $label, array $options, string $default): array
    {
        return [
            'name' => $name,
            'type' => 'select',
            'label' => $label,
            'options' => $options,
            'default' => $default,
            'rules' => ['nullable', 'string', 'in:'.implode(',', array_keys($options))],
        ];
    }

    private function cta(string $name, string $label): array
    {
        return [
            'name' => $name,
            'type' => 'cta',
            'label' => $label,
            'rules' => ['nullable', 'array'],
            'fields' => [
                $this->text('label', 'Label'),
                $this->text('url', 'URL'),
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function repeater(string $name, string $label, array $fields, bool $required = false): array
    {
        return [
            'name' => $name,
            'type' => 'repeater',
            'label' => $label,
            'required' => $required,
            'fields' => $fields,
            'rules' => $required ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
        ];
    }
}

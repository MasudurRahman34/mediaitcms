<?php

/*
|--------------------------------------------------------------------------
| CMS settings schema
|--------------------------------------------------------------------------
| Single source of truth for editable website settings: drives the admin
| Settings screen and server-side validation. Values live in `settings`.
*/

return [
    'groups' => [
        'general' => [
            'label' => 'Company',
            'fields' => [
                'company_name' => ['label' => 'Company name', 'type' => 'text', 'default' => 'Media IT Soft'],
                'tagline' => ['label' => 'Tagline', 'type' => 'text', 'default' => 'Software, AI & IT solutions'],
                'logo' => ['label' => 'Logo', 'type' => 'media', 'hint' => 'Replaces the built-in SVG wordmark'],
                'favicon' => ['label' => 'Favicon', 'type' => 'media'],
                'footer_text' => ['label' => 'Footer text', 'type' => 'textarea', 'default' => 'We design, build and support software that moves businesses forward.'],
            ],
        ],
        'contact' => [
            'label' => 'Contact',
            'fields' => [
                'contact_email' => ['label' => 'Email', 'type' => 'text', 'default' => 'info@mediaitsoft.co.uk'],
                'contact_phone' => ['label' => 'Phone', 'type' => 'text', 'default' => '44 7903 306305'],
                'contact_address' => ['label' => 'Address', 'type' => 'textarea', 'default' => 'Rivermead Drive, Westlea, Swindon, SN5 7EX, United Kingdom'],
                'contact_hours' => ['label' => 'Opening hours', 'type' => 'text', 'default' => 'Mon–Fri, 09:00–18:00 (GMT)'],
                'contact_map_url' => ['label' => 'Map embed URL', 'type' => 'text', 'hint' => 'Optional Google Maps embed URL'],
                'notification_email' => ['label' => 'Notifications inbox', 'type' => 'text', 'default' => 'info@mediaitsoft.co.uk'],
            ],
        ],
        'social' => [
            'label' => 'Social links',
            'fields' => [
                'social_linkedin' => ['label' => 'LinkedIn', 'type' => 'text'],
                'social_twitter' => ['label' => 'X / Twitter', 'type' => 'text'],
                'social_facebook' => ['label' => 'Facebook', 'type' => 'text'],
                'social_instagram' => ['label' => 'Instagram', 'type' => 'text'],
                'social_youtube' => ['label' => 'YouTube', 'type' => 'text'],
                'social_github' => ['label' => 'GitHub', 'type' => 'text'],
            ],
        ],
        'seo' => [
            'label' => 'SEO defaults',
            'fields' => [
                'seo_title_suffix' => ['label' => 'Title suffix', 'type' => 'text', 'default' => 'Media IT Soft'],
                'seo_default_description' => [
                    'label' => 'Default meta description',
                    'type' => 'textarea',
                    'default' => 'Media IT Soft builds software, AI solutions and IT services for ambitious organisations across the UK.',
                ],
                'seo_default_image' => ['label' => 'Default social image', 'type' => 'media'],
                'seo_analytics_id' => ['label' => 'Analytics ID', 'type' => 'text', 'hint' => 'e.g. G-XXXXXXX'],
            ],
        ],
        'cta' => [
            'label' => 'Global CTA',
            'fields' => [
                'cta_heading' => ['label' => 'Heading', 'type' => 'text', 'default' => 'Ready to build something exceptional?'],
                'cta_body' => ['label' => 'Body', 'type' => 'textarea', 'default' => 'Tell us about your project and we will come back with a clear plan.'],
                'cta_button_label' => ['label' => 'Button label', 'type' => 'text', 'default' => 'Start a conversation'],
                'cta_button_url' => ['label' => 'Button URL', 'type' => 'text', 'default' => '/contact'],
            ],
        ],
    ],
];

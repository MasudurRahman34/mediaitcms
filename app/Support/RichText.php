<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Sanitises rich-text HTML coming from TipTap (or any CMS input) so that
 * only a safe allowlist of tags/attributes ever reaches the database or
 * the rendered page. Applied on write and again on read.
 */
class RichText
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html === null ? null : '';
        }

        return self::purifier()->purify($html);
    }

    /** Plain-text version, handy for excerpts and meta descriptions. */
    public static function plain(?string $html, int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)) ?? '');

        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit - 1).'…' : $text;
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier instanceof HTMLPurifier) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', implode(',', [
            'p[style]', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'del',
            'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'blockquote', 'pre', 'code',
            'a[href|title|target|rel]',
            'img[src|alt|title|width|height]',
            'hr',
            'table', 'thead', 'tbody', 'tr', 'th[style]', 'td[style]',
            'span[style]', 'div[style]',
        ]));
        $config->set('CSS.AllowedProperties', 'text-align,font-weight,font-style,text-decoration');
        $config->set('AutoFormat.RemoveEmpty', false);
        $config->set('AutoFormat.Linkify', false);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', false);
        $config->set('Core.RemoveInvalidImg', false);

        return self::$purifier = new HTMLPurifier($config);
    }
}

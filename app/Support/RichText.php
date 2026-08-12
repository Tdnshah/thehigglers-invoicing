<?php

namespace App\Support;

/**
 * Line item descriptions are authored in a rich text editor and stored as HTML,
 * so they have to be printed unescaped. This keeps that to a small, safe subset:
 * anything that can execute script or pull in a remote resource is removed.
 */
class RichText
{
    protected const ALLOWED_TAGS = '<p><br><b><strong><i><em><u><s><ol><ul><li><h3><h4><h5><span><div>';

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $clean = strip_tags($html, self::ALLOWED_TAGS);

        // Drop inline event handlers and javascript: payloads left on the allowed tags.
        $clean = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
        $clean = preg_replace('/javascript\s*:/i', '', $clean);

        return $clean;
    }

    /**
     * Plain text version, used where markup would get in the way (e.g. email previews).
     */
    public static function toText(?string $html): string
    {
        return trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

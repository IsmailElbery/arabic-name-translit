<?php

declare(strict_types=1);

namespace Ismail\NameTranslit;

/**
 * Static facade for the common case, plus a fluent entry point via make().
 *
 * This package transliterates Arabic personal names; it NEVER translates
 * them. `أمير` -> "Amir", never "prince".
 *
 * @see CLAUDE.md §1 §2
 */
final class Transliterator
{
    /**
     * @param string      $name     The Arabic name.
     * @param string      $standard common | icao | din31635 | ala_lc
     * @param string|null $style    Regional spelling (common standard only).
     */
    public static function name(
        string $name,
        string $standard = 'common',
        ?string $style = null,
    ): string {
        return self::make()->name($name, $standard, $style);
    }

    /**
     * Fluent builder for explicit control.
     *
     * @param array<string, mixed> $config Optional config overrides.
     */
    public static function make(array $config = []): TransliteratorManager
    {
        return new TransliteratorManager($config);
    }
}

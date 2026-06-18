<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Support;

/**
 * Normalizes Arabic input before every lookup, and normalizes dictionary keys
 * at build time, so the two always match.
 *
 * Notably does NOT fold taa marbuta (ة -> ه): the terminal taa marbuta is
 * semantically important for the ending rule and must survive to the
 * diacritics step.
 *
 * @see CLAUDE.md §4
 */
final class Normalizer
{
    /** Harakat / tashkeel range: fatha..sukun, plus shadda/tanwin. */
    private const TASHKEEL = '/[\x{064B}-\x{0652}\x{0670}]/u';

    /** Tatweel (kashida). */
    private const TATWEEL = "\u{0640}";

    public function __construct(
        private readonly bool $normalizeAlifMaqsura = true,
    ) {
    }

    public function normalize(string $name): string
    {
        // Strip tashkeel / harakat (fatha, kasra, damma, sukun, shadda, tanwin,
        // superscript alif).
        $name = (string) preg_replace(self::TASHKEEL, '', $name);

        // Strip tatweel.
        $name = str_replace(self::TATWEEL, '', $name);

        // Hamza-carrying alif -> bare alif.
        $name = strtr($name, [
            "\u{0623}" => "\u{0627}", // أ -> ا
            "\u{0625}" => "\u{0627}", // إ -> ا
            "\u{0622}" => "\u{0627}", // آ -> ا
        ]);

        // Alif maqsura -> ya (configurable).
        if ($this->normalizeAlifMaqsura) {
            $name = str_replace("\u{0649}", "\u{064A}", $name); // ى -> ي
        }

        // Trim and collapse internal whitespace.
        $name = trim($name);
        $name = (string) preg_replace('/\s+/u', ' ', $name);

        return $name;
    }
}

<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Support;

/**
 * Letter -> Latin maps used by the rule engine, keyed by standard.
 *
 * These cover consonants and the "default" reading of long vowels. Vowel
 * length nuances and ending rules are layered on top by {@see Diacritics}
 * and each standard's overrides.
 *
 * @see CLAUDE.md §5 §6
 */
final class CharMap
{
    /**
     * Human-readable map (common standard). Long vowels use their shorter
     * single-letter reading by default; styles may lengthen them.
     *
     * @return array<string, string>
     */
    public static function common(): array
    {
        return [
            "\u{0621}" => '',   // ء hamza (bare) — silent by default
            "\u{0627}" => 'a',  // ا alif
            "\u{0628}" => 'b',  // ب
            "\u{062A}" => 't',  // ت
            "\u{062B}" => 'th', // ث
            "\u{062C}" => 'j',  // ج
            "\u{062D}" => 'h',  // ح
            "\u{062E}" => 'kh', // خ
            "\u{062F}" => 'd',  // د
            "\u{0630}" => 'dh', // ذ
            "\u{0631}" => 'r',  // ر
            "\u{0632}" => 'z',  // ز
            "\u{0633}" => 's',  // س
            "\u{0634}" => 'sh', // ش
            "\u{0635}" => 's',  // ص
            "\u{0636}" => 'd',  // ض
            "\u{0637}" => 't',  // ط
            "\u{0638}" => 'z',  // ظ
            "\u{0639}" => 'a',  // ع ayn — rendered as a vowel-ish in names
            "\u{063A}" => 'gh', // غ
            "\u{0641}" => 'f',  // ف
            "\u{0642}" => 'q',  // ق
            "\u{0643}" => 'k',  // ك
            "\u{0644}" => 'l',  // ل
            "\u{0645}" => 'm',  // م
            "\u{0646}" => 'n',  // ن
            "\u{0647}" => 'h',  // ه
            "\u{0648}" => 'u',  // و waw (long oo -> u by default)
            "\u{064A}" => 'i',  // ي ya (long ee -> i by default)
            "\u{0629}" => 'a',  // ة taa marbuta (terminal handled separately)
            ' '        => ' ',
        ];
    }

    /**
     * ICAO Doc 9303 map. Deterministic, uppercase applied in post-process.
     * Digraphs follow the common Latin readings used by MRZ practice.
     *
     * @return array<string, string>
     */
    public static function icao(): array
    {
        return [
            "\u{0621}" => '',
            "\u{0627}" => 'A',
            "\u{0628}" => 'B',
            "\u{062A}" => 'T',
            "\u{062B}" => 'TH',
            "\u{062C}" => 'J',
            "\u{062D}" => 'H',
            "\u{062E}" => 'KH',
            "\u{062F}" => 'D',
            "\u{0630}" => 'DH',
            "\u{0631}" => 'R',
            "\u{0632}" => 'Z',
            "\u{0633}" => 'S',
            "\u{0634}" => 'SH',
            "\u{0635}" => 'S',
            "\u{0636}" => 'D',
            "\u{0637}" => 'T',
            "\u{0638}" => 'Z',
            "\u{0639}" => 'A',
            "\u{063A}" => 'GH',
            "\u{0641}" => 'F',
            "\u{0642}" => 'Q',
            "\u{0643}" => 'K',
            "\u{0644}" => 'L',
            "\u{0645}" => 'M',
            "\u{0646}" => 'N',
            "\u{0647}" => 'H',
            "\u{0648}" => 'U',
            "\u{064A}" => 'I',
            "\u{0629}" => '',   // taa marbuta dropped (Doc 9303 default)
            ' '        => '',   // compounds joined without spaces
        ];
    }
}

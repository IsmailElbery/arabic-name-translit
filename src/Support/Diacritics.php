<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Support;

/**
 * Letter-level constants and ending-rule helpers shared by the rule engine
 * and the standards.
 *
 * @see CLAUDE.md §5
 */
final class Diacritics
{
    public const ALIF          = "\u{0627}"; // ا
    public const WAW           = "\u{0648}"; // و
    public const YA            = "\u{064A}"; // ي
    public const TAA_MARBUTA   = "\u{0629}"; // ة
    public const LAM           = "\u{0644}"; // ل
    public const DEFINITE_AL   = "\u{0627}\u{0644}"; // ال

    /**
     * Split a terminal taa marbuta off the end of a normalized token.
     *
     * @return array{0: string, 1: bool} [body without trailing ة, hadTaaMarbuta]
     */
    public static function splitTerminalTaaMarbuta(string $arabic): array
    {
        if ($arabic !== '' && mb_substr($arabic, -1) === self::TAA_MARBUTA) {
            return [mb_substr($arabic, 0, mb_strlen($arabic) - 1), true];
        }

        return [$arabic, false];
    }

    /**
     * Does this token carry the leading definite article ال?
     * Guards against the token being only "ال".
     */
    public static function hasDefiniteArticle(string $arabic): bool
    {
        return mb_strlen($arabic) > 2 && mb_substr($arabic, 0, 2) === self::DEFINITE_AL;
    }

    public static function stripDefiniteArticle(string $arabic): string
    {
        return self::hasDefiniteArticle($arabic)
            ? mb_substr($arabic, 2)
            : $arabic;
    }
}

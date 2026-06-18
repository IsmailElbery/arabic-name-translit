<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Engine;

use Ismail\NameTranslit\Standards\StandardInterface;
use Ismail\NameTranslit\Support\Diacritics;

/**
 * Phonetic fallback for genuinely unknown names. Maps letters via the active
 * standard's char map and applies the terminal taa-marbuta ending rule.
 *
 * This is the LAST resort in the pipeline (§3). It never consults a
 * translation source — it only sounds the letters out.
 *
 * @see CLAUDE.md §3 §5
 */
final class RuleEngine
{
    public function transliterate(string $normalized, StandardInterface $standard): string
    {
        if ($normalized === '') {
            return '';
        }

        // Peel a terminal taa marbuta so the standard's ending rule applies
        // instead of the generic letter map.
        [$body, $hadTaaMarbuta] = Diacritics::splitTerminalTaaMarbuta($normalized);

        $map = $standard->charMap();
        $out = '';

        foreach ($this->chars($body) as $char) {
            // Map known letters; pass through anything we don't recognize
            // (e.g. already-Latin characters).
            $out .= $map[$char] ?? $char;
        }

        if ($hadTaaMarbuta) {
            $out .= $standard->terminalTaaMarbuta();
        }

        return $standard->postProcess($out);
    }

    /**
     * @return list<string>
     */
    private function chars(string $s): array
    {
        return preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}

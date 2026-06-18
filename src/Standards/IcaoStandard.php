<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Standards;

use Ismail\NameTranslit\Support\CharMap;

/**
 * ICAO Doc 9303 (MRZ) transliteration. Deterministic and uppercase; ignores
 * `style`. Drops the terminal taa-marbuta vowel and joins compound names
 * without spaces. May consult the dictionary for a base reading, but always
 * enforces ICAO post-processing on top.
 *
 * @see CLAUDE.md §6
 */
final class IcaoStandard implements StandardInterface
{
    public function key(): string
    {
        return 'icao';
    }

    public function charMap(): array
    {
        return CharMap::icao();
    }

    public function terminalTaaMarbuta(): string
    {
        return '';
    }

    public function definiteArticle(): string
    {
        return 'AL';
    }

    public function joinCompound(array $parts): string
    {
        // Doc 9303: compound names joined without spaces.
        return implode('', array_map($this->postProcess(...), $parts));
    }

    public function postProcess(string $out): string
    {
        // Uppercase, ASCII letters only, strip spaces/punctuation.
        $out = mb_strtoupper($out);

        return (string) preg_replace('/[^A-Z]/u', '', $out);
    }

    public function dictionaryColumn(): string
    {
        return 'common';
    }

    public function selectReading(array $entry): string
    {
        // ICAO uses the base reading; post-processing enforces compliance.
        return $entry['common'] ?? '';
    }
}

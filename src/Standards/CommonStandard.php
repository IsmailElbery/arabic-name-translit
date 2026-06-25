<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Standards;

use Ismail\NameTranslit\Support\CharMap;
use Ismail\NameTranslit\Support\Diacritics;

/**
 * Human-readable transliteration. Honors a regional `style` (gulf, egyptian)
 * for both dictionary alternates and rule-engine vowel length.
 *
 * @see CLAUDE.md §6
 */
final class CommonStandard implements StandardInterface
{
    /**
     * @param array<string, mixed> $config The nametranslit config array.
     */
    public function __construct(
        private readonly ?string $style = null,
        private readonly array $config = [],
    ) {
    }

    public function key(): string
    {
        return 'common';
    }

    public function charMap(): array
    {
        $map = CharMap::common();

        // Apply style-specific long-vowel lengths for the rule-engine fallback.
        $vowels = $this->config['rule_vowels'][$this->style ?? 'default']
            ?? $this->config['rule_vowels']['default']
            ?? ['ya' => 'i', 'waw' => 'u'];

        $map[Diacritics::YA]  = $vowels['ya'];
        $map[Diacritics::WAW] = $vowels['waw'];

        return $map;
    }

    public function terminalTaaMarbuta(): string
    {
        return 'a';
    }

    public function definiteArticle(): string
    {
        // Render the leading article ال on a name, e.g. السيد -> "Al-Sayed"
        // (default) or "El-Sayed" (egyptian). Configurable per style.
        $articles = $this->config['common_article'] ?? ['default' => 'Al-'];

        return $articles[$this->style ?? 'default']
            ?? $articles['default']
            ?? 'Al-';
    }

    public function joinCompound(array $parts): string
    {
        $parts = array_values(array_filter($parts, static fn ($p) => $p !== ''));
        $join = $this->config['common_compound_join'] ?? 'spaced';

        if ($join === 'joined') {
            return implode('', $parts);
        }

        return implode(' ', $parts);
    }

    public function postProcess(string $out): string
    {
        // Title-case each whitespace-separated word; leave the rest intact.
        return (string) preg_replace_callback(
            '/\b\p{L}[\p{L}\']*/u',
            static fn (array $m) => mb_strtoupper(mb_substr($m[0], 0, 1)) . mb_substr($m[0], 1),
            mb_strtolower($out)
        );
    }

    public function dictionaryColumn(): string
    {
        return 'common';
    }

    public function selectReading(array $entry): string
    {
        $base = $entry['common'] ?? '';

        if ($this->style === null) {
            return $base;
        }

        $hints = $this->config['styles'][$this->style]['alt_hints'] ?? [];
        if ($hints === []) {
            return $base;
        }

        foreach ((array) ($entry['alt'] ?? []) as $alt) {
            foreach ($hints as $pattern) {
                if (preg_match($pattern, $alt) === 1) {
                    return $alt;
                }
            }
        }

        return $base;
    }
}

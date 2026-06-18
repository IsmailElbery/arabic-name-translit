<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Standards;

interface StandardInterface
{
    /**
     * Arabic letter => Latin map used by the rule engine.
     *
     * @return array<string, string>
     */
    public function charMap(): array;

    /**
     * What a terminal taa marbuta (ة) renders as for this standard.
     * e.g. common => "a", icao => "".
     */
    public function terminalTaaMarbuta(): string;

    /**
     * How the definite article ال renders when it prefixes a compound
     * component. e.g. common => "" (dropped), icao => "AL".
     */
    public function definiteArticle(): string;

    /**
     * Join already-resolved compound parts into the final string.
     *
     * @param list<string> $parts
     */
    public function joinCompound(array $parts): string;

    /**
     * Final casing / cleanup pass applied to every output.
     */
    public function postProcess(string $out): string;

    /**
     * Which dictionary column holds this standard's base reading.
     */
    public function dictionaryColumn(): string;

    /**
     * Choose the rendering for a dictionary entry (base column vs. a
     * style-specific alternate), before post-processing.
     *
     * @param array{common: string, alt?: list<string>} $entry
     */
    public function selectReading(array $entry): string;

    /**
     * Canonical name used to look this standard up.
     */
    public function key(): string;
}

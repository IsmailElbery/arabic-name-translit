<?php

declare(strict_types=1);

/*
 * Compound name markers. Keys are NORMALIZED Arabic standalone tokens:
 *   - أبو is keyed ابو (hamza folded)
 *   - the family particle آل is keyed ال (hamza folded). A *standalone* token
 *     equal to ال is treated as the family particle "Al"; the SAME letters as
 *     a prefix on a longer word are the definite article (handled separately
 *     in CompoundResolver / Diacritics).
 *
 * `render` is the base Latin form; the active standard's postProcess() applies
 * casing (common -> "Abdul", icao -> "ABDUL").
 *
 * `absorbs_article`: عبد swallows the definite article of the following word
 * so عبد الرحمن -> "Abdul Rahman" (not "Abdul Al Rahman").
 *
 * @see CLAUDE.md §7
 */

return [
    'عبد' => ['render' => 'Abdul', 'absorbs_article' => true],
    'ابو' => ['render' => 'Abu'],
    'ابن' => ['render' => 'Ibn'],
    'بن'  => ['render' => 'Bin'],
    'بنت' => ['render' => 'Bint'],
    'ال'  => ['render' => 'Al'],
];

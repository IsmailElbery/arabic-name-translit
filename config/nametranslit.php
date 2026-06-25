<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default standard
    |--------------------------------------------------------------------------
    | One of: common | icao | din31635 | ala_lc
    | `common` is human-readable and honors `style`. ICAO is deterministic.
    */
    'standard' => 'common',

    /*
    |--------------------------------------------------------------------------
    | Default style (common standard only)
    |--------------------------------------------------------------------------
    | Regional spelling convention. null = the dictionary's `common` rendering.
    | Built-in: gulf, egyptian.
    */
    'style' => null,

    /*
    |--------------------------------------------------------------------------
    | Style -> alternate preference
    |--------------------------------------------------------------------------
    | For a given style, prefer a dictionary `alt` whose spelling matches one
    | of these regex hints (case-insensitive, tried in order). The first alt
    | that matches wins; otherwise the `common` rendering is used.
    |
    | These also steer the rule-engine fallback for unknown names (see below).
    */
    'styles' => [
        'gulf' => [
            // long ee/oo doubled: Ameer, Noor, Mohammad
            'alt_hints' => ['/ee/i', '/oo/i', '/mohammad/i'],
        ],
        'egyptian' => [
            // Mohamed, single vowels
            'alt_hints' => ['/mohamed/i'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule-engine vowel length per style (fallback for unknown names)
    |--------------------------------------------------------------------------
    | Controls how long vowels ي / و render when a name is NOT in the
    | dictionary and falls through to the phonetic rule engine.
    */
    'rule_vowels' => [
        'default'  => ['ya' => 'i', 'waw' => 'u'],
        'gulf'     => ['ya' => 'ee', 'waw' => 'oo'],
        'egyptian' => ['ya' => 'i', 'waw' => 'ou'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Compound article handling (common standard)
    |--------------------------------------------------------------------------
    | When joining compound names like `عبد الرحمن` under the common standard:
    |   'spaced'      => "Abdul Rahman"
    |   'joined'      => "Abdulrahman"
    */
    'common_compound_join' => 'spaced',

    /*
    |--------------------------------------------------------------------------
    | Definite article ال rendering (common standard)
    |--------------------------------------------------------------------------
    | How a leading ال on a name renders under the common standard, per style.
    | e.g. السيد -> "Al-Sayed" (default) or "El-Sayed" (egyptian).
    | Set a value to '' to drop the article entirely.
    | ICAO always renders the article as "AL" (Doc 9303), regardless of this.
    */
    'common_article' => [
        'default'  => 'Al-',
        'egyptian' => 'El-',
    ],

    /*
    |--------------------------------------------------------------------------
    | Normalize alif maqsura (ى -> ي)
    |--------------------------------------------------------------------------
    | Some names legitimately end in ى. Disable to preserve it.
    */
    'normalize_alif_maqsura' => true,
];

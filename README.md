# Arabic Name Transliterator

[![Packagist Version](https://img.shields.io/packagist/v/ismail/arabic-name-translit.svg)](https://packagist.org/packages/ismail/arabic-name-translit)
[![Tests](https://img.shields.io/github/actions/workflow/status/IsmailElbery/arabic-name-translit/tests.yml?branch=master&label=tests)](https://github.com/IsmailElbery/arabic-name-translit/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ismail/arabic-name-translit.svg)](https://packagist.org/packages/ismail/arabic-name-translit)
[![PHP Version](https://img.shields.io/packagist/php-v/ismail/arabic-name-translit.svg)](https://packagist.org/packages/ismail/arabic-name-translit)
[![License](https://img.shields.io/packagist/l/ismail/arabic-name-translit.svg)](LICENSE)

Transliterate Arabic personal names into Latin script — **the right way**.

```php
Transliterator::name('أمير');   // "Amir"   (not "prince")
Transliterator::name('أميرة');  // "Amira"  (not "princess")
```

## Why this exists

Generic engines *translate* names instead of *transliterating* them. Ask Google to render `أمير` and you get **"prince"**; `أميرة` becomes **"princess"** — because those words also *mean* prince and princess. Same trap with `جميل` ("beautiful"), `نور` ("light"), `سعيد` ("happy").

A name is a phonetic token, never a semantic one. This package treats it that way and **never consults a translation source**. Known names are mapped directly from a curated dictionary; unknown names fall back to a phonetic rule engine.

## Installation

```bash
composer require ismail/arabic-name-translit
```

Requires PHP 8.1+. Laravel users get auto-discovery (service provider + facade) out of the box.

## Usage

### Quick

```php
use Ismail\NameTranslit\Transliterator;

Transliterator::name('أمير');                         // "Amir"
Transliterator::name('أميرة');                        // "Amira"
Transliterator::name('فاطمة');                        // "Fatima"
Transliterator::name('محمد', style: 'gulf');          // "Mohammad"
Transliterator::name('محمد', style: 'egyptian');      // "Mohamed"
Transliterator::name('عبد الرحمن', standard: 'icao');  // "ABDULRAHMAN"
```

### Fluent

```php
Transliterator::make()
    ->standard('common')   // common | icao | din31635 | ala_lc
    ->style('gulf')        // regional spelling convention
    ->name('أميرة');
```

## Standards

| Standard   | Output         | Use case                                   |
|------------|----------------|--------------------------------------------|
| `common`   | `Amir`         | Human-readable; respects regional `style`. |
| `icao`     | `AMIR`         | Doc 9303 / MRZ — passports, manifests.     |
| `din31635` | *(v1.1)*       | Academic, diacritic-based.                 |
| `ala_lc`   | *(v1.1)*       | Library cataloguing standard.              |

The `icao` standard is deterministic: uppercase output, `AL` article handling, no trailing taa-marbuta vowel, and compound names joined per Doc 9303. This is the path for compliance-driven systems (government records, Hajj/Umrah manifests, travel documents).

## Styles (common standard only)

| Style      | `محمد`     | `أمير`   |
|------------|-----------|---------|
| default    | Mohammed  | Amir    |
| `gulf`     | Mohammad  | Ameer   |
| `egyptian` | Mohamed   | Amir    |

## How it resolves

Each name is normalized (hamza/tashkeel/tatweel stripped), then resolved in order:

1. **Dictionary** — exact lookup of known names. The primary path; this is what prevents the prince/princess bug.
2. **Compound** — splits on particles (`عبد`, `أبو`, `ابن`, `بنت`, `آل`) and the definite article `ال`, then re-resolves each part.
3. **Rule engine** — phonetic fallback for genuinely unknown names.

Handles taa marbuta endings (`ة` → `a`), initial hamza, long vowels (`ي`/`و`), and the definite article per the active standard.

## Contributing

The dictionary is the heart of the package — the more curated names it holds, the better it performs. Additions to `data/names.male.php` and `data/names.female.php` (with regional alternates) are the most valuable contribution you can make. Keys must be stored **normalized** (no hamza on alif, no tashkeel).

## Roadmap

- **v1.0** — Common + ICAO standards, dictionary + rule engine, Laravel integration.
- **v1.1** — DIN 31635 and ALA-LC standards, expanded regional styles.
- **Experimental** — reverse transliteration (Latin → Arabic). Stubbed, not yet implemented.

## License

MIT

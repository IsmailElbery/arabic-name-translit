# CLAUDE.md — `arabic-name-translit`

Implementation spec and working context for building a PHP package that **transliterates** Arabic personal names into Latin script. Read this fully before writing or modifying code.

---

## 1. What this package is (and is not)

**Is:** a name-aware transliteration engine. Given an Arabic name, it returns a phonetic Latin rendering (`أمير` → `Amir`).

**Is NOT:** a translator. It must **never** resolve a name to its dictionary meaning. The bug that motivated this package: generic engines render `أمير` → "prince" and `أميرة` → "princess" because those words *mean* prince/princess. A name is a phonetic token, never a semantic one. No code path may consult a translation source.

### Core guarantees
- `أمير` → `Amir`, never "prince".
- `أميرة` → `Amira`, never "princess".
- `جميل` → `Jameel`, never "beautiful". `نور` → `Noor`, never "light". `سعيد` → `Saeed`, never "happy".
- Deterministic output for a given (name, standard, style) triple.

---

## 2. Public API

Static facade for the common case:

```php
use Ismail\NameTranslit\Transliterator;

Transliterator::name('أمير');                        // "Amir"
Transliterator::name('أميرة');                       // "Amira"
Transliterator::name('محمد', style: 'gulf');         // "Mohammad"
Transliterator::name('محمد', style: 'egyptian');     // "Mohamed"
Transliterator::name('عبد الرحمن', standard: 'icao'); // "ABDULRAHMAN"
```

Fluent builder for explicit control:

```php
Transliterator::make()
    ->standard('common')   // common | icao | din31635 | ala_lc
    ->style('gulf')        // regional spelling convention
    ->name('أميرة');
```

Method signature:

```php
public static function name(
    string $name,
    string $standard = 'common',
    ?string $style = null
): string;
```

`style` is only meaningful for the `common` standard. ICAO/DIN/ALA-LC are deterministic and ignore `style`.

---

## 3. Resolution pipeline (strict order, stop at first hit)

Every name is first **normalized** (§4), then resolved:

1. **DictionaryResolver** — exact lookup of the normalized name in the curated tables (`data/names.*.php`). This is the primary path and the thing that kills the translate-vs-transliterate bug. Known names never reach the rule engine.
2. **CompoundResolver** — if no direct hit, split on particles (`عبد`, `أبو`, `ابن`, `بنت`, `آل`) and the definite article `ال`. Re-resolve each component through step 1, then join per the active standard's joining rules (e.g. ICAO joins `عبد الرحمن` → `ABDULRAHMAN`; common → `Abdul Rahman` or `Abdulrahman` per config).
3. **RuleEngine** — phonetic fallback for genuinely unknown names only. Maps letters via the active standard's `CharMap` and applies `Diacritics` rules (§5).

If a name partially resolves (some compound parts known, some not), known parts use the dictionary and unknown parts use the rule engine.

---

## 4. Normalization (runs before every lookup)

Implement in `Support/Normalizer.php`. Applied to input AND to dictionary keys at build time so they always match.

- Hamza-carrying alif → bare alif: `أ`, `إ`, `آ` → `ا`
- Strip all harakat / tashkeel (fatha, kasra, damma, sukun, shadda, tanwin): `\x{064B}-\x{0652}`
- Strip tatweel `ـ` (`\x{0640}`)
- Normalize alif maqsura `ى` → `ي` (configurable; some names legitimately end in `ى`)
- Trim, collapse internal whitespace
- Do NOT fold `ة` → `ه` at this stage — taa marbuta is semantically important for the ending rule (§5) and must survive to the diacritics step.

Dictionary keys are stored already-normalized, e.g. key `اميرة` (not `أميرة`).

---

## 5. Diacritics & ending rules (`Support/Diacritics.php`)

These are the rules the rule engine and the standards apply. Each standard may override.

- **Taa marbuta `ة` (terminal):** `common` → `a` (`فاطمة` → `Fatima`, `أميرة` → `Amira`). `icao` → dropped or `H` per Doc 9303 profile (default: drop, no trailing vowel). Make this a method `terminalTaaMarbuta()` on the standard.
- **Hamza on initial alif `أ`:** drops to the bare vowel — `أمير` → `A...` → `Amir`. Already handled by normalization stripping the hamza; the leading `ا` maps to `A`.
- **Long vowels:**
  - `ي` (long ee) → `i` (common default) or `ee` (gulf style): `أمير` → `Amir` / `Ameer`.
  - `و` (long oo) → `u` / `oo`.
  - `ا` (long aa) → `a`.
- **Definite article `ال`:** elided/assimilated per standard. ICAO → `AL`. Common → typically dropped or lowercased when joining compounds.
- **Shadda (gemination):** doubles the consonant when present in input (rare in names but handle gracefully — usually already stripped, so this is best-effort).

---

## 6. Standards

`Standards/StandardInterface.php`:

```php
interface StandardInterface
{
    public function charMap(): array;          // arabic letter => latin
    public function terminalTaaMarbuta(): string;
    public function joinCompound(array $parts): string;
    public function postProcess(string $out): string; // casing etc.
    public function dictionaryColumn(): string;  // which column to read from data tables
}
```

Implementations for v1:
- **CommonStandard** — human-readable, respects `style`. `dictionaryColumn()` returns `common` (or the style-specific alt). Mixed case (`Amir`).
- **IcaoStandard** — Doc 9303 deterministic. Uppercase output, `AL` article, no trailing taa-marbuta vowel, compound names joined without spaces. This is the compliance/monetizable path (passports, Hajj manifests). Keep it strictly rule-based and standards-faithful; it may still consult the dictionary for the base reading but must enforce ICAO post-processing.

Stubs only (throw `NotImplementedException`, ship in v1.1): **Din31635Standard**, **AlaLcStandard**.

---

## 7. Dictionary data format

Plain PHP arrays (no parse step, fastest load), keyed by **normalized** Arabic. Split male/female so the taa-marbuta default and gendered alternates stay clean.

```php
// data/names.female.php
return [
    'اميرة' => ['common' => 'Amira',  'alt' => ['Ameera']],
    'فاطمة' => ['common' => 'Fatima', 'alt' => ['Fatma', 'Fatimah']],
    'نور'   => ['common' => 'Noor',   'alt' => ['Nour', 'Nur']],
];

// data/names.male.php
return [
    'امير'  => ['common' => 'Amir',     'alt' => ['Ameer']],
    'محمد'  => ['common' => 'Mohammed', 'alt' => ['Muhammad', 'Mohamed', 'Mohammad']],
    'سعيد'  => ['common' => 'Saeed',    'alt' => ['Said', 'Saʿid']],
    'جميل'  => ['common' => 'Jameel',   'alt' => ['Jamil']],
];
```

Style → alt mapping lives in config: e.g. `gulf` prefers `Mohammad`, `egyptian` prefers `Mohamed`. If a style has no specific alt, fall back to `common`.

`data/particles.php` holds the compound markers and their canonical renderings (`عبد` → `Abdul`/`Abd`, `أبو` → `Abu`, `ابن`/`بن` → `Ibn`/`Bin`, `بنت` → `Bint`, `آل` → `Al`).

---

## 8. Directory layout

```
src/
  Transliterator.php            // facade / entry point
  TransliteratorManager.php     // fluent builder backing make()
  Engine/
    DictionaryResolver.php      // step 1
    CompoundResolver.php        // step 2
    RuleEngine.php              // step 3 fallback
  Standards/
    StandardInterface.php
    CommonStandard.php
    IcaoStandard.php
    Din31635Standard.php        // stub
    AlaLcStandard.php           // stub
  Support/
    Normalizer.php
    CharMap.php
    Diacritics.php
  Exceptions/
    NotImplementedException.php
  Laravel/
    NameTranslitServiceProvider.php
    Facades/Transliterator.php  // optional Laravel facade alias
data/
  names.male.php
  names.female.php
  particles.php
config/
  nametranslit.php              // default standard + style, article handling, publishable
tests/
  DictionaryResolverTest.php
  CompoundResolverTest.php
  RuleEngineTest.php
  NormalizerTest.php
  IcaoStandardTest.php
composer.json
README.md
CLAUDE.md
```

---

## 9. composer.json essentials

- PSR-4: `"Ismail\\NameTranslit\\": "src/"`
- `require`: `php: ^8.1` (typed enums/named args used in API). Optional `ext-intl` only if used for normalization — prefer pure PHP to keep it dependency-light.
- `require-dev`: `phpunit/phpunit`, `friendsofphp/php-cs-fixer`.
- Laravel auto-discovery under `extra.laravel.providers` → `NameTranslitServiceProvider`, and the facade alias.
- `autoload.files` is NOT used for data; load `data/*.php` lazily via `require` inside the resolvers, cached in a static property.

---

## 10. Testing priorities

Must-pass cases (these are the reason the package exists):

```php
['أمير',   'common', null,      'Amir'];
['أميرة',  'common', null,      'Amira'];
['جميل',   'common', null,      'Jameel'];
['نور',    'common', null,      'Noor'];
['محمد',   'common', 'gulf',    'Mohammad'];
['محمد',   'common', 'egyptian','Mohamed'];
['عبد الرحمن', 'icao', null,    'ABDULRAHMAN'];
['فاطمة',  'common', null,      'Fatima'];   // taa marbuta → a
```

Also test: normalization idempotence (input with/without tashkeel yields same result), unknown-name fallback hits the rule engine, compound partial resolution, ICAO uppercase + article joining.

---

## 11. Scope discipline

**v1.0 ships:** Normalizer, DictionaryResolver, CompoundResolver, RuleEngine, CommonStandard (+ gulf/egyptian styles), IcaoStandard, Laravel integration, starter dictionary (~a few hundred high-frequency names), full test suite for the cases in §10.

**Defer to v1.1+:** DIN 31635, ALA-LC, reverse transliteration (Latin → Arabic — much harder, leave a `reverse()` stub marked experimental, do not implement), expanded regional styles (levantine, maghrebi).

A tight, correct v1 with a solid dictionary beats a sprawling one that's wrong on edge cases. The dictionary is the moat; grow it over time.

---

## 12. Conventions

- PHP 8.1+, strict types (`declare(strict_types=1);` in every file).
- PSR-12 formatting, enforced by php-cs-fixer.
- All Arabic string handling is UTF-8; use `mb_*` functions everywhere, never byte-level `str*` on Arabic.
- Resolvers are stateless and injectable; the manager wires them. No global state except the lazily-loaded, cached data arrays.
- Never introduce a network call or a translation dependency. If a future contributor adds one, that is a bug by definition (§1).

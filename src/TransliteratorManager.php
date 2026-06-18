<?php

declare(strict_types=1);

namespace Ismail\NameTranslit;

use Ismail\NameTranslit\Engine\CompoundResolver;
use Ismail\NameTranslit\Engine\DictionaryResolver;
use Ismail\NameTranslit\Engine\RuleEngine;
use Ismail\NameTranslit\Standards\AlaLcStandard;
use Ismail\NameTranslit\Standards\CommonStandard;
use Ismail\NameTranslit\Standards\Din31635Standard;
use Ismail\NameTranslit\Standards\IcaoStandard;
use Ismail\NameTranslit\Standards\StandardInterface;
use Ismail\NameTranslit\Support\Normalizer;
use InvalidArgumentException;

/**
 * Fluent builder backing the {@see Transliterator} facade. Wires the
 * normalizer and the three resolvers, and runs the strict resolution
 * pipeline (§3).
 *
 * Stateless resolvers; the only mutable state is the selected standard/style.
 */
final class TransliteratorManager
{
    /** Sentinel: "inherit the currently-set style" (distinct from null = no style). */
    private const INHERIT = "\0inherit\0";

    /** @var array<string, mixed> */
    private array $config;

    private string $standard;

    private ?string $style;

    private string $dataDir;

    private Normalizer $normalizer;

    private DictionaryResolver $dictionary;

    private RuleEngine $ruleEngine;

    private CompoundResolver $compound;

    /**
     * @param array<string, mixed> $config Overrides merged over the defaults.
     */
    public function __construct(array $config = [], ?string $dataDir = null)
    {
        $this->config = $config + self::defaultConfig();
        $this->dataDir = $dataDir ?? \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';

        $this->standard = (string) $this->config['standard'];
        $this->style = $this->config['style'] ?? null;

        $this->normalizer = new Normalizer(
            (bool) ($this->config['normalize_alif_maqsura'] ?? true),
        );
        $this->dictionary = new DictionaryResolver($this->dataDir);
        $this->ruleEngine = new RuleEngine();
        $this->compound = new CompoundResolver($this->dictionary, $this->ruleEngine, $this->dataDir);
    }

    /**
     * Return a fresh builder (clone) for fluent configuration.
     */
    public function make(): self
    {
        return clone $this;
    }

    public function standard(string $standard): self
    {
        $clone = clone $this;
        $clone->standard = $standard;

        return $clone;
    }

    public function style(?string $style): self
    {
        $clone = clone $this;
        $clone->style = $style;

        return $clone;
    }

    /**
     * Transliterate a single Arabic name through the pipeline.
     *
     * `$standard` / `$style` override the fluent-set values for this call
     * only. Omit `$style` to inherit the current style; pass null explicitly
     * to force "no style".
     */
    public function name(
        string $name,
        ?string $standard = null,
        ?string $style = self::INHERIT,
    ): string {
        $standardName = $standard ?? $this->standard;
        $styleName = $style === self::INHERIT ? $this->style : $style;

        $standard = $this->makeStandard($standardName, $styleName);
        $normalized = $this->normalizer->normalize($name);

        if ($normalized === '') {
            return '';
        }

        // 1. Dictionary (exact, normalized lookup) — the primary path.
        $hit = $this->dictionary->resolve($normalized, $standard);
        if ($hit !== null) {
            return $hit;
        }

        // 2. Compound resolution (particles + definite article).
        $hit = $this->compound->resolve($normalized, $standard);
        if ($hit !== null) {
            return $hit;
        }

        // 3. Phonetic rule-engine fallback for genuinely unknown names.
        return $this->ruleEngine->transliterate($normalized, $standard);
    }

    /**
     * Reverse transliteration (Latin -> Arabic). Experimental; not implemented.
     *
     * @see CLAUDE.md §11
     */
    public function reverse(string $latin): string
    {
        throw new \Ismail\NameTranslit\Exceptions\NotImplementedException(
            'Reverse transliteration is experimental and not implemented in v1.',
        );
    }

    private function makeStandard(string $name, ?string $style): StandardInterface
    {
        return match ($name) {
            'common'   => new CommonStandard($style, $this->config),
            'icao'     => new IcaoStandard(),
            'din31635' => new Din31635Standard(),
            'ala_lc'   => new AlaLcStandard(),
            default    => throw new InvalidArgumentException("Unknown standard: {$name}"),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function defaultConfig(): array
    {
        /** @var array<string, mixed> $config */
        $config = require \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config'
            . DIRECTORY_SEPARATOR . 'nametranslit.php';

        return $config;
    }
}

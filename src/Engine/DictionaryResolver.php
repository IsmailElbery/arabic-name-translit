<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Engine;

use Ismail\NameTranslit\Standards\StandardInterface;

/**
 * Step 1 of the pipeline: exact lookup of a normalized name in the curated
 * tables. This is the primary path and the thing that kills the
 * translate-vs-transliterate bug — known names never reach the rule engine.
 *
 * @see CLAUDE.md §3 §7
 */
final class DictionaryResolver
{
    /**
     * Lazily-loaded, cached dictionary keyed by data directory.
     *
     * @var array<string, array<string, array{common: string, alt?: list<string>}>>
     */
    private static array $cache = [];

    public function __construct(
        private readonly string $dataDir,
    ) {
    }

    /**
     * Resolve a normalized name to its rendering under the given standard,
     * or null if the name is not in the dictionary.
     */
    public function resolve(string $normalized, StandardInterface $standard): ?string
    {
        $entry = $this->lookup($normalized);

        if ($entry === null) {
            return null;
        }

        return $standard->postProcess($standard->selectReading($entry));
    }

    /**
     * @return array{common: string, alt?: list<string>}|null
     */
    public function lookup(string $normalized): ?array
    {
        return $this->table()[$normalized] ?? null;
    }

    public function has(string $normalized): bool
    {
        return isset($this->table()[$normalized]);
    }

    /**
     * @return array<string, array{common: string, alt?: list<string>}>
     */
    private function table(): array
    {
        if (isset(self::$cache[$this->dataDir])) {
            return self::$cache[$this->dataDir];
        }

        $table = [];
        foreach (['names.male.php', 'names.female.php'] as $file) {
            $path = $this->dataDir . DIRECTORY_SEPARATOR . $file;
            if (is_file($path)) {
                /** @var array<string, array{common: string, alt?: list<string>}> $data */
                $data = require $path;
                // Later files do not clobber earlier ones unless intentional;
                // male/female key spaces are effectively disjoint.
                $table += $data;
            }
        }

        return self::$cache[$this->dataDir] = $table;
    }

    /**
     * Clear the static cache (used in tests).
     */
    public static function flush(): void
    {
        self::$cache = [];
    }
}

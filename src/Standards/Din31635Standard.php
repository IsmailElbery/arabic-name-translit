<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Standards;

use Ismail\NameTranslit\Exceptions\NotImplementedException;

/**
 * DIN 31635 (academic, diacritic-based). Stubbed for v1.1.
 *
 * @see CLAUDE.md §6 §11
 */
final class Din31635Standard implements StandardInterface
{
    private function fail(): never
    {
        throw new NotImplementedException('The DIN 31635 standard ships in v1.1.');
    }

    public function key(): string
    {
        return 'din31635';
    }

    public function charMap(): array
    {
        $this->fail();
    }

    public function terminalTaaMarbuta(): string
    {
        $this->fail();
    }

    public function definiteArticle(): string
    {
        $this->fail();
    }

    public function joinCompound(array $parts): string
    {
        $this->fail();
    }

    public function postProcess(string $out): string
    {
        $this->fail();
    }

    public function dictionaryColumn(): string
    {
        $this->fail();
    }

    public function selectReading(array $entry): string
    {
        $this->fail();
    }
}

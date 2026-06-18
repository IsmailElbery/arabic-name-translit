<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Standards;

use Ismail\NameTranslit\Exceptions\NotImplementedException;

/**
 * ALA-LC (library cataloguing). Stubbed for v1.1.
 *
 * @see CLAUDE.md §6 §11
 */
final class AlaLcStandard implements StandardInterface
{
    private function fail(): never
    {
        throw new NotImplementedException('The ALA-LC standard ships in v1.1.');
    }

    public function key(): string
    {
        return 'ala_lc';
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

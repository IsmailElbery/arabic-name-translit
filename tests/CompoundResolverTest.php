<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Tests;

use Ismail\NameTranslit\Engine\CompoundResolver;
use Ismail\NameTranslit\Engine\DictionaryResolver;
use Ismail\NameTranslit\Engine\RuleEngine;
use Ismail\NameTranslit\Standards\CommonStandard;
use Ismail\NameTranslit\Standards\IcaoStandard;
use PHPUnit\Framework\TestCase;

final class CompoundResolverTest extends TestCase
{
    private CompoundResolver $resolver;

    /** @var array<string, mixed> */
    private array $config;

    protected function setUp(): void
    {
        DictionaryResolver::flush();
        $dataDir = \dirname(__DIR__) . '/data';
        $dictionary = new DictionaryResolver($dataDir);
        $this->resolver = new CompoundResolver($dictionary, new RuleEngine(), $dataDir);
        $this->config = require \dirname(__DIR__) . '/config/nametranslit.php';
    }

    private function common(?string $style = null): CommonStandard
    {
        return new CommonStandard($style, $this->config);
    }

    public function test_abd_compound_with_article_common(): void
    {
        // عبد absorbs the article of الرحمن
        self::assertSame('Abdul Rahman', $this->resolver->resolve('عبد الرحمن', $this->common()));
    }

    public function test_abd_compound_with_article_icao_joins_without_spaces(): void
    {
        self::assertSame('ABDULRAHMAN', $this->resolver->resolve('عبد الرحمن', new IcaoStandard()));
    }

    public function test_abu_particle(): void
    {
        // أبو normalizes to ابو; زيد is in the dictionary as Zaid
        self::assertSame('Abu Zaid', $this->resolver->resolve('ابو زيد', $this->common()));
    }

    public function test_single_bare_token_is_not_a_compound(): void
    {
        // A lone known name is not the compound resolver's job — defer.
        self::assertNull($this->resolver->resolve('امير', $this->common()));
    }

    public function test_partial_resolution_uses_rule_engine_for_unknown_part(): void
    {
        // علي (known -> Ali) + an unknown token falls back per-part.
        $out = $this->resolver->resolve('علي قطبان', $this->common());
        self::assertNotNull($out);
        self::assertStringStartsWith('Ali ', $out);
    }
}

<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Tests;

use Ismail\NameTranslit\Engine\DictionaryResolver;
use Ismail\NameTranslit\Standards\CommonStandard;
use Ismail\NameTranslit\Standards\IcaoStandard;
use PHPUnit\Framework\TestCase;

final class DictionaryResolverTest extends TestCase
{
    private DictionaryResolver $resolver;

    /** @var array<string, mixed> */
    private array $config;

    protected function setUp(): void
    {
        DictionaryResolver::flush();
        $this->resolver = new DictionaryResolver(\dirname(__DIR__) . '/data');
        $this->config = require \dirname(__DIR__) . '/config/nametranslit.php';
    }

    public function test_exact_lookup_returns_entry(): void
    {
        $entry = $this->resolver->lookup('امير');
        self::assertNotNull($entry);
        self::assertSame('Amir', $entry['common']);
    }

    public function test_unknown_name_returns_null(): void
    {
        self::assertNull($this->resolver->lookup('xyzقطب123'));
        self::assertNull($this->resolver->resolve('xyzقطب123', new CommonStandard(null, $this->config)));
    }

    public function test_resolve_applies_common_post_processing(): void
    {
        self::assertSame('Fatima', $this->resolver->resolve('فاطمة', new CommonStandard(null, $this->config)));
    }

    public function test_resolve_applies_style_alternate(): void
    {
        self::assertSame('Mohammad', $this->resolver->resolve('محمد', new CommonStandard('gulf', $this->config)));
        self::assertSame('Mohamed', $this->resolver->resolve('محمد', new CommonStandard('egyptian', $this->config)));
    }

    public function test_resolve_applies_icao_post_processing(): void
    {
        self::assertSame('AMIR', $this->resolver->resolve('امير', new IcaoStandard()));
    }

    public function test_has(): void
    {
        self::assertTrue($this->resolver->has('نور'));
        self::assertFalse($this->resolver->has('نوور'));
    }
}

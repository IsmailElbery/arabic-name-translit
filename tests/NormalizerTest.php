<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Tests;

use Ismail\NameTranslit\Support\Normalizer;
use PHPUnit\Framework\TestCase;

final class NormalizerTest extends TestCase
{
    private Normalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new Normalizer();
    }

    public function test_folds_hamza_carrying_alif_to_bare_alif(): void
    {
        self::assertSame('امير', $this->normalizer->normalize('أمير'));
        self::assertSame('احمد', $this->normalizer->normalize('أحمد'));
        self::assertSame('ابراهيم', $this->normalizer->normalize('إبراهيم'));
    }

    public function test_strips_tashkeel(): void
    {
        // أَمِير with fatha + kasra
        self::assertSame('امير', $this->normalizer->normalize("\u{0623}\u{064E}\u{0645}\u{0650}\u{064A}\u{0631}"));
    }

    public function test_strips_tatweel(): void
    {
        self::assertSame('محمد', $this->normalizer->normalize("\u{0645}\u{062D}\u{0640}\u{0645}\u{062F}"));
    }

    public function test_folds_alif_maqsura_to_ya(): void
    {
        self::assertSame('ليلي', $this->normalizer->normalize('ليلى'));
    }

    public function test_can_preserve_alif_maqsura_when_disabled(): void
    {
        $normalizer = new Normalizer(normalizeAlifMaqsura: false);
        self::assertSame('ليلى', $normalizer->normalize('ليلى'));
    }

    public function test_preserves_taa_marbuta(): void
    {
        self::assertSame('فاطمة', $this->normalizer->normalize('فَاطِمَة'));
    }

    public function test_collapses_whitespace_and_trims(): void
    {
        self::assertSame('عبد الرحمن', $this->normalizer->normalize('  عبد   الرحمن  '));
    }

    public function test_is_idempotent(): void
    {
        $once = $this->normalizer->normalize('أَميرة');
        $twice = $this->normalizer->normalize($once);
        self::assertSame($once, $twice);
    }
}

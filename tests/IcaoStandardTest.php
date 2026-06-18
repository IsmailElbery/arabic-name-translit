<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Tests;

use Ismail\NameTranslit\Transliterator;
use PHPUnit\Framework\TestCase;

final class IcaoStandardTest extends TestCase
{
    public function test_output_is_uppercase(): void
    {
        self::assertSame('AMIR', Transliterator::name('أمير', 'icao'));
        self::assertSame('FATIMA', Transliterator::name('فاطمة', 'icao'));
    }

    public function test_no_trailing_taa_marbuta_vowel_for_unknown_names(): void
    {
        // Known فاطمة keeps its dictionary reading FATIMA; an unknown ة-name
        // drops the vowel via the rule engine (بهجة is not in the dictionary).
        self::assertSame('BHJ', Transliterator::name('بهجة', 'icao'));
    }

    public function test_compound_article_joining(): void
    {
        self::assertSame('ABDULRAHMAN', Transliterator::name('عبد الرحمن', 'icao'));
    }

    public function test_is_deterministic_and_ignores_style(): void
    {
        $a = Transliterator::name('محمد', 'icao', 'gulf');
        $b = Transliterator::name('محمد', 'icao', 'egyptian');
        $c = Transliterator::name('محمد', 'icao');

        self::assertSame($a, $b);
        self::assertSame($b, $c);
        self::assertSame('MOHAMMED', $a);
    }
}

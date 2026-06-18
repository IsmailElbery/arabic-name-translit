<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Tests;

use Ismail\NameTranslit\Transliterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The must-pass cases from CLAUDE.md §10 — the reason the package exists.
 * These assert we transliterate, never translate.
 */
final class TransliteratorTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string, 2: ?string, 3: string}>
     */
    public static function mustPassCases(): array
    {
        return [
            'amir -> Amir (not prince)'        => ['أمير', 'common', null, 'Amir'],
            'amira -> Amira (not princess)'    => ['أميرة', 'common', null, 'Amira'],
            'jameel -> Jameel (not beautiful)' => ['جميل', 'common', null, 'Jameel'],
            'noor -> Noor (not light)'         => ['نور', 'common', null, 'Noor'],
            'mohammad gulf'                    => ['محمد', 'common', 'gulf', 'Mohammad'],
            'mohamed egyptian'                 => ['محمد', 'common', 'egyptian', 'Mohamed'],
            'abdulrahman icao'                 => ['عبد الرحمن', 'icao', null, 'ABDULRAHMAN'],
            'fatima taa-marbuta -> a'          => ['فاطمة', 'common', null, 'Fatima'],
        ];
    }

    #[DataProvider('mustPassCases')]
    public function test_must_pass_cases(string $name, string $standard, ?string $style, string $expected): void
    {
        self::assertSame($expected, Transliterator::name($name, $standard, $style));
    }

    public function test_never_returns_a_translation_meaning(): void
    {
        // Belt-and-suspenders: the outputs must be phonetic, never the gloss.
        self::assertNotSame('Prince', Transliterator::name('أمير'));
        self::assertNotSame('Princess', Transliterator::name('أميرة'));
        self::assertNotSame('Beautiful', Transliterator::name('جميل'));
        self::assertNotSame('Light', Transliterator::name('نور'));
    }

    public function test_fluent_builder(): void
    {
        $out = Transliterator::make()
            ->standard('common')
            ->style('gulf')
            ->name('محمد');

        self::assertSame('Mohammad', $out);
    }

    public function test_style_is_ignored_for_icao(): void
    {
        self::assertSame('AMIR', Transliterator::name('أمير', 'icao', 'gulf'));
    }

    public function test_empty_input_returns_empty(): void
    {
        self::assertSame('', Transliterator::name('   '));
    }
}

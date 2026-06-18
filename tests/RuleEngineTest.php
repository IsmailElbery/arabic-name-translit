<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Tests;

use Ismail\NameTranslit\Engine\RuleEngine;
use Ismail\NameTranslit\Standards\CommonStandard;
use Ismail\NameTranslit\Standards\IcaoStandard;
use PHPUnit\Framework\TestCase;

final class RuleEngineTest extends TestCase
{
    private RuleEngine $engine;

    /** @var array<string, mixed> */
    private array $config;

    protected function setUp(): void
    {
        $this->engine = new RuleEngine();
        $this->config = require \dirname(__DIR__) . '/config/nametranslit.php';
    }

    public function test_maps_letters_phonetically(): void
    {
        // ك-ت-ب -> k-t-b, title-cased by the common standard.
        self::assertSame('Ktb', $this->engine->transliterate('كتب', new CommonStandard(null, $this->config)));
    }

    public function test_terminal_taa_marbuta_becomes_a_in_common(): void
    {
        // An unknown name ending in ة: ...-a
        self::assertSame('Hba', $this->engine->transliterate('هبة', new CommonStandard(null, $this->config)));
    }

    public function test_terminal_taa_marbuta_dropped_in_icao(): void
    {
        self::assertSame('HB', $this->engine->transliterate('هبة', new IcaoStandard()));
    }

    public function test_long_vowel_length_follows_style(): void
    {
        // و -> u (default) vs oo (gulf)
        $default = $this->engine->transliterate('نوبي', new CommonStandard(null, $this->config));
        $gulf = $this->engine->transliterate('نوبي', new CommonStandard('gulf', $this->config));

        self::assertSame('Nubi', $default);
        self::assertSame('Noobee', $gulf);
    }

    public function test_icao_output_is_uppercase_ascii(): void
    {
        self::assertMatchesRegularExpression('/^[A-Z]+$/', $this->engine->transliterate('كتب', new IcaoStandard()));
    }
}

<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Laravel facade alias for the transliterator manager.
 *
 * @method static string name(string $name, ?string $standard = null, ?string $style = null)
 * @method static \Ismail\NameTranslit\TransliteratorManager make()
 * @method static \Ismail\NameTranslit\TransliteratorManager standard(string $standard)
 * @method static \Ismail\NameTranslit\TransliteratorManager style(?string $style)
 *
 * @see \Ismail\NameTranslit\TransliteratorManager
 */
final class Transliterator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nametranslit';
    }
}

<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Laravel;

use Illuminate\Support\ServiceProvider;
use Ismail\NameTranslit\TransliteratorManager;

/**
 * Laravel auto-discovered service provider. Binds the manager as the
 * `nametranslit` container singleton and publishes the config file.
 */
final class NameTranslitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'nametranslit');

        $this->app->singleton('nametranslit', static function ($app): TransliteratorManager {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('nametranslit', []);

            return new TransliteratorManager($config);
        });

        $this->app->alias('nametranslit', TransliteratorManager::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->configPath() => $this->app->configPath('nametranslit.php'),
            ], 'nametranslit-config');
        }
    }

    /**
     * @return list<string>
     */
    public function provides(): array
    {
        return ['nametranslit', TransliteratorManager::class];
    }

    private function configPath(): string
    {
        return \dirname(__DIR__, 2) . '/config/nametranslit.php';
    }
}

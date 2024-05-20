<?php

namespace Zbiller\CrudhubLang;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Zbiller\CrudhubLang\Commands\InstallCommand;
use Zbiller\CrudhubLang\Contracts\LanguageModelContract;
use Zbiller\CrudhubLang\Contracts\TranslationModelContract;
use Zbiller\CrudhubLang\Contracts\TranslationServiceContract;
use Zbiller\CrudhubLang\Models\Language;
use Zbiller\CrudhubLang\Models\Translation;
use Zbiller\CrudhubLang\Services\TranslationService;

class CrudhubLangServiceProvider extends BaseServiceProvider
{
    /**
     * @var ConfigRepository
     */
    protected ConfigRepository $config;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->config;
    }

    /**
     * @param Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        Schema::defaultStringLength(125);

        $this->publishConfigs();
        $this->publishMigrations();
        $this->publishSeeders();
        $this->registerCommands();
        $this->registerRouteBindings();
        $this->registerRoutes();
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->mergeConfigs();
        $this->registerModelBindings();
        $this->registerServiceBindings();
    }

    /**
     * @return void
     */
    protected function publishConfigs()
    {
        $this->publishes([
            __DIR__ . '/../config/bindings.php' => config_path('crudhub-lang/bindings.php'),
        ], 'crudhub-lang-config');
    }

    /**
     * @return void
     */
    protected function publishMigrations()
    {
        if (empty(File::glob(database_path('migrations/*_create_crudhub_lang_tables.php')))) {
            $timestamp = date('Y_m_d_His', time() + 60);

            $this->publishes([
                __DIR__ . '/../database/migrations/create_crudhub_lang_tables.php' => database_path() . "/migrations/{$timestamp}_create_crudhub_lang_tables.php",
            ], 'crudhub-lang-migrations');
        }
    }

    /**
     * @return void
     */
    protected function publishSeeders()
    {
        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders/CrudhubLang'),
        ], 'crudhub-lang-seeders');
    }

    /**
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    /**
     * @return void
     */
    protected function registerRouteBindings()
    {
        Route::model('language', LanguageModelContract::class);
        Route::model('translation', TranslationModelContract::class);
    }

    /**
     * @return void
     */
    protected function registerRoutes()
    {
        Route::macro('crudhubLang', function () {
            require __DIR__ . '/../routes/routes.php';
        });
    }

    /**
     * @return void
     */
    protected function mergeConfigs()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bindings.php', 'crudhub-lang.bindings');
    }

    /**
     * @return void
     */
    protected function registerModelBindings()
    {
        $binding = $this->config['crudhub-lang.bindings.models'];

        $this->app->bind(LanguageModelContract::class, $binding['language_model'] ?? Language::class);
        $this->app->bind(TranslationModelContract::class, $binding['translation_model'] ?? Translation::class);
    }

    /**
     * @return void
     */
    protected function registerServiceBindings()
    {
        $binding = $this->config['crudhub-lang.bindings.services'];

        $this->app->bind(TranslationServiceContract::class, $binding['translation_service'] ?? TranslationService::class);
    }
}

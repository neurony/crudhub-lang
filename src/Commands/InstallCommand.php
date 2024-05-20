<?php

namespace Zbiller\CrudhubLang\Commands;

use Database\Seeders\CrudhubLang\LanguageSeeder;
use Database\Seeders\CrudhubLang\PermissionSeeder;
use Exception;
use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\OptimizeClearCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class InstallCommand extends Command
{
    use InteractsWithIO;

    /**
     * @var string
     */
    protected $signature = 'crudhub-lang:install    {--overwrite        : Overwrite files}
                                                    {--no-migrate       : Do not run migrations}
                                                    {--no-seed          : Do not run seeders}';

    /**
     * @var string
     */
    protected $description = 'Install Crudhub Lang';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var array|string[]
     */
    protected array $flags = [
        'success' => '<fg=green;options=bold>SUCCESS</>',
        'warning' => '<fg=yellow;options=bold>WARNING</>',
        'error' => '<fg=red;options=bold>ERROR</>',
        'info' => '<fg=blue;options=bold>INFO</>',
        'skipped' => '<fg=yellow;options=bold>SKIPPED</>',
    ];

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * @return int
     */
    public function handle()
    {
        if (!$this->healthyDatabaseConnection()) {
            $this->components->error('In order to install Crudhub Lang, please check and configure the database connection');

            return Command::FAILURE;
        }

        $this->publishFiles();
        $this->registerRoutes();
        $this->appendAdminMenu();
        $this->modifyViteConfig();
        $this->modifyTailwindConfig();
        $this->updateNodePackages();
        $this->migrateDatabase();
        $this->seedDatabase();
        $this->optimizeClear();

        return Command::SUCCESS;
    }

    /**
     * @return bool
     */
    public function healthyDatabaseConnection(): bool
    {
        try {
            $this->components->task('Checking the database connection', function () {
                DB::connection()->getPdo();
            });

            return true;
        } catch (\Exception $e) {
            $this->components->error('Could not connect to the database.  Please check your configuration.');
            $this->components->error($e->getMessage());

            return false;
        }
    }

    /**
     * @return void
     */
    protected function publishFiles()
    {
        $this->components->info('Publishing necessary files.');

        $this->components->task('Publishing config files inside the "config/crudhub-lang/" directory', function () {
            $this->callSilent('vendor:publish', ['--tag' => 'crudhub-lang-config']);
        });

        $this->components->task('Publishing migration files inside the "database/migrations/" directory', function () {
            $this->callSilent('vendor:publish', ['--tag' => 'crudhub-lang-migrations']);
        });

        $this->components->task('Publishing the seeders the "database/seeders/" directory', function () {
            $this->callSilent('vendor:publish', ['--tag' => 'crudhub-lang-seeders']);
        });

        $this->components->task('Publishing JS resources inside the "resources/js/crudhub" directory', function () {
            $this->files->ensureDirectoryExists(resource_path('js/crudhub/Pages/Languages'));
            $this->files->ensureDirectoryExists(resource_path('js/crudhub/Pages/Translations'));

            if ($this->option('overwrite')) {
                $this->files->copyDirectory(__DIR__ . '/../../stubs/resources/js/Pages/Languages', resource_path('js/crudhub/Pages/Languages'));
                $this->files->copyDirectory(__DIR__ . '/../../stubs/resources/js/Pages/Translations', resource_path('js/crudhub/Pages/Translations'));
            } else {
                $this->copyIfNotExistFile(__DIR__ . '/../../stubs/resources/js/Pages/Languages', resource_path('js/crudhub/Pages/Languages'));
                $this->copyIfNotExistFile(__DIR__ . '/../../stubs/resources/js/Pages/Translations', resource_path('js/crudhub/Pages/Translations'));
            }
        });
    }

    /**
     * @return void
     */
    public function registerRoutes(): void
    {
        $this->components->info('Registering routes.');

        try {
            $routes = $this->files->get(base_path('routes/web.php'));

            $this->components->task('Registering the "crudhubLang" route macro', function () use ($routes) {
                if (!Str::contains($routes, 'Route::crudhubLang()')) {
                    $this->files->append(base_path('routes/web.php'), "\nRoute::crudhubLang();\n");
                }
            });
        } catch (Throwable $e) {
            $this->components->twoColumnDetail('Unable to register routes', $this->flags['error']);
            $this->components->twoColumnDetail('Please manually append this to your "routes/web.php": Route::crudhubLang()', $this->flags['info']);
        }
    }

    /**
     * @return void
     */
    protected function appendAdminMenu(): void
    {
        $this->components->info('Appending to the admin menu.');

        try {
            $configPath = config_path('/crudhub/menu.php');
            $configContent = $this->files->get($configPath);

            $stubPath = __DIR__ . '/../../stubs/config/menu.stub';
            $stubContent = $this->files->get($stubPath);

            $this->components->task('Append "crudhub-lang" menu items', function () use ($configPath, &$configContent, $stubPath, &$stubContent) {
                if (Str::contains($configContent, "'heading' => 'Multi language'")) {
                    return;
                }

                $lastPos = strlen($configContent);
                $posCounter = $thirdToLastPos = 0;

                // find the third-to-last position of "]"
                while (($lastPos = strrpos(substr($configContent, 0, $lastPos), ']')) !== false) {
                    $posCounter++;

                    if ($posCounter == 3) {
                        $thirdToLastPos = $lastPos;

                        break;
                    }

                    $lastPos = $lastPos - 1;
                }

                if ($thirdToLastPos !== 0) {
                    $nextCharIsComma = $configContent[$thirdToLastPos + 1] === ',';
                    $posToInsert = $thirdToLastPos + 1 + ($nextCharIsComma ? 1 : 0);
                    $newContent = $nextCharIsComma ? ("\n" . $stubContent) : (",\n" . $stubContent);
                    $newConfigContent = substr_replace($configContent, $newContent, $posToInsert, 0);

                    $this->files->put($configPath, $newConfigContent);
                } else {
                    throw new Exception;
                }
            });
        } catch (FileNotFoundException $e) {
            $this->components->twoColumnDetail('The "config/crudhub/menu.php" file does not exist', $this->flags['error']);

            return;
        } catch (Exception $e) {
            $this->components->twoColumnDetail('Could not append the "multi language" menu', $this->flags['error']);

            return;
        }
    }

    /**
     * @return void
     */
    protected function modifyViteConfig(): void
    {
        $this->components->info('Modifying the Vite config.');

        try {
            $viteFile = base_path('/crudhub.vite.config.js');
            $viteContent = $this->files->get($viteFile);

            $this->components->task('Append "crudhub-lang" alias to "crudhub.vite.config.js"', function () use ($viteFile, &$viteContent) {
                if (Str::contains($viteContent, '"crudhub-lang": resolve(__dirname, "./vendor/neurony/crudhub-lang/resources/js")')) {
                    return;
                }

                if (Str::contains($viteContent, '"crudhub": resolve(__dirname, "./vendor/neurony/crudhub/resources/js"),')) {
                    $viteContent = str_replace(
                        '"crudhub": resolve(__dirname, "./vendor/neurony/crudhub/resources/js"),',
                        '"crudhub": resolve(__dirname, "./vendor/neurony/crudhub/resources/js"),' . "\n" . '            "crudhub-lang": resolve(__dirname, "./vendor/neurony/crudhub-lang/resources/js"),' . "\n" . '            "laravel-vue-i18n": resolve(__dirname, "./node_modules/laravel-vue-i18n"),',
                        $viteContent
                    );
                } elseif (Str::contains($viteContent, '"crudhub": resolve(__dirname, "./vendor/neurony/crudhub/resources/js")')) {
                    $viteContent = str_replace(
                        '"crudhub": resolve(__dirname, "./vendor/neurony/crudhub/resources/js")',
                        '"crudhub": resolve(__dirname, "./vendor/neurony/crudhub/resources/js"),' . "\n" . '            "crudhub-lang": resolve(__dirname, "./vendor/neurony/crudhub-lang/resources/js"),' . "\n" . '            "laravel-vue-i18n": resolve(__dirname, "./node_modules/laravel-vue-i18n"),',
                        $viteContent
                    );
                } else {
                    throw new Exception;
                }

                $this->files->put($viteFile, $viteContent);
            });
        } catch (FileNotFoundException $e) {
            $this->components->twoColumnDetail('The "crudhub.vite.config.js" does not exist', $this->flags['error']);

            return;
        } catch (Exception $e) {
            $this->components->twoColumnDetail('Could not add alias "crudhub-lang" inside "crudhub.vite.config.js"', $this->flags['error']);
            $this->components->twoColumnDetail('Please manually add the following to "crudhub.vite.config.js" -> resolve.alias section', $this->flags['info']);
            $this->components->twoColumnDetail('"crudhub": resolve(__dirname, "./vendor/neurony/crudhub/resources/js")', $this->flags['info']);
            $this->components->twoColumnDetail('"crudhub-lang": resolve(__dirname, "./vendor/neurony/crudhub-lang/resources/js")', $this->flags['info']);

            return;
        }
    }

    /**
     * @return void
     */
    protected function modifyTailwindConfig(): void
    {
        $this->components->info('Modifying the Tailwind config.');

        try {
            $tailwindFile = base_path('/crudhub.tailwind.config.js');
            $tailwindContent = $this->files->get($tailwindFile);

            $this->components->task('Append "crudhub-lang" to "crudhub.tailwind.config.js"', function () use ($tailwindFile, &$tailwindContent) {
                if (Str::contains($tailwindContent, '"./vendor/neurony/crudhub-lang/resources/js/**/*.{js,vue}"')) {
                    return;
                }

                if (Str::contains($tailwindContent, '"./vendor/neurony/crudhub/resources/js/**/*.{js,vue}",')) {
                    $tailwindContent = str_replace(
                        '"./vendor/neurony/crudhub/resources/js/**/*.{js,vue}",',
                        '"./vendor/neurony/crudhub/resources/js/**/*.{js,vue}",' . "\n" . '        "./vendor/neurony/crudhub-lang/resources/js/**/*.{js,vue}",',
                        $tailwindContent
                    );
                } elseif (Str::contains($tailwindContent, '"./vendor/neurony/crudhub/resources/js/**/*.{js,vue}"')) {
                    $tailwindContent = str_replace(
                        '"./vendor/neurony/crudhub/resources/js/**/*.{js,vue}"',
                        '"./vendor/neurony/crudhub/resources/js/**/*.{js,vue}",' . "\n" . '        "./vendor/neurony/crudhub-lang/resources/js/**/*.{js,vue}",',
                        $tailwindContent
                    );
                } else {
                    throw new Exception;
                }

                $this->files->put($tailwindFile, $tailwindContent);
            });
        } catch (FileNotFoundException $e) {
            $this->components->twoColumnDetail('The "crudhub.tailwind.config.js" does not exist', $this->flags['error']);

            return;
        } catch (Exception $e) {
            $this->components->twoColumnDetail('Could not add alias "crudhub-lang" inside "crudhub.tailwind.config.js"', $this->flags['error']);
            $this->components->twoColumnDetail('Please manually add the following to "crudhub.tailwind.config.js" -> resolve.alias section', $this->flags['info']);
            $this->components->twoColumnDetail('"./vendor/neurony/crudhub/resources/js/**/*.{js,vue}"', $this->flags['info']);
            $this->components->twoColumnDetail('"./vendor/neurony/crudhub-lang/resources/js/**/*.{js,vue}"', $this->flags['info']);

            return;
        }
    }

    /**
     * @return void
     */
    public function updateNodePackages(): void
    {
        $this->components->info('Updating Node packages.');

        $this->editPackageJson(function ($packages) {
            return [
                "laravel-vue-i18n" => "^2.7.1",
            ] + $packages;
        });
    }

    /**
     * @return void
     */
    public function migrateDatabase(): void
    {
        if ($this->option('no-migrate')) {
            return;
        }

        $this->call(MigrateCommand::class);
    }

    /**
     * @return void
     */
    public function seedDatabase(): void
    {
        if ($this->option('no-seed')) {
            return;
        }

        $this->components->info('Seeding database.');

        $this->components->task('Seeding permissions', function () {
            $this->callSilent(SeedCommand::class, [
                'class' => PermissionSeeder::class,
            ]);
        });

        $this->components->task('Seeding languages', function () {
            $this->callSilent(SeedCommand::class, [
                'class' => LanguageSeeder::class,
            ]);
        });
    }

    /**
     * @return void
     */
    public function optimizeClear(): void
    {
        $this->call(OptimizeClearCommand::class);
    }

    /**
     * @param callable $callback
     * @param bool $dev
     * @return void
     */
    protected function editPackageJson(callable $callback, bool $dev = true)
    {
        if (! $this->files->exists(base_path('package.json'))) {
            $this->components->twoColumnDetail('File package.json not found, was not able to install additional packages', $this->flags['error']);

            return;
        }

        $this->components->task('Updating the "package.json" configuration and dependencies', function () use ($callback, $dev) {
            $file = base_path('package.json');
            $key = $dev ? 'devDependencies' : 'dependencies';
            $packages = json_decode($this->files->get($file), true);

            $packages['scripts'] = $packages['scripts'] + [
                "crudhub:dev" => "vite --config crudhub.vite.config.js",
                "crudhub:build" => "vite build --config crudhub.vite.config.js",
            ];

            $packages[$key] = $callback(array_key_exists($key, $packages) ? $packages[$key] : [], $key);

            ksort($packages[$key]);

            $this->files->put($file, json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
        });
    }

    /**
     * @param string $path
     * @param string $destination
     * @return void
     */
    private function copyIfNotExistFile(string $path, string $destination): void
    {
        $items = new FilesystemIterator($path);

        collect($items)->each(function ($item) use ($path, $destination) {
            $baseName = $item->getBaseName();
            $destinationPath = "$destination/$baseName";
            $filePath = "$path/$baseName";

            if ($item->isDir()) {
                if (! $this->files->exists($destinationPath)) {
                    $this->files->ensureDirectoryExists($destinationPath);
                }

                $this->copyIfNotExistFile($filePath, $destinationPath);
            }

            if (! file_exists($destinationPath)) {
                $this->files->copy($filePath, $destinationPath);

                return true;
            }
        });
    }
}

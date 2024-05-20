<?php

namespace Zbiller\CrudhubLang\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Zbiller\CrudhubLang\Contracts\LanguageModelContract;
use Zbiller\CrudhubLang\Contracts\TranslationModelContract;
use Zbiller\CrudhubLang\Contracts\TranslationServiceContract;

class TranslationService implements TranslationServiceContract
{
    /**
     * @var Application
     */
    protected Application $app;

    /**
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * @var TranslationModelContract
     */
    protected TranslationModelContract $translationModel;

    /**
     * @var LanguageModelContract
     */
    protected LanguageModelContract $languageModel;

    /**
     * @const
     */
    const JSON_GROUP = 'json';

    /**
     * @param Application $app
     * @param Filesystem $files
     * @param TranslationModelContract $translationModel
     * @param LanguageModelContract $languageModel
     */
    public function __construct(Application $app, Filesystem $files, TranslationModelContract $translationModel, LanguageModelContract $languageModel)
    {
        $this->app = $app;
        $this->files = $files;

        $this->translationModel = $translationModel;
        $this->languageModel = $languageModel;
    }

    /**
     * @param bool $replace
     * @return void
     */
    public function importTranslations(bool $replace = false): void
    {
        $this->importDirectoryTranslations($replace);
        $this->importJsonTranslations($replace);
    }

    /**
     * @return void
     */
    public function exportTranslations(): void
    {
        $this->exportFileTranslations();
        $this->exportJsonTranslations();
    }

    /**
     * @param bool $replace
     * @return void
     */
    protected function importDirectoryTranslations(bool $replace = false): void
    {
        foreach ($this->files->directories($this->app['path.lang']) as $path) {
            $locale = basename($path);

            foreach ($this->files->allFiles($path) as $file) {
                $this->importFileTranslations($file, $path, $locale, $replace);
            }
        }
    }

    /**
     * @param string $file
     * @param string $path
     * @param string $locale
     * @param bool $replace
     * @return void
     */
    protected function importFileTranslations(string $file, string $path, string $locale, bool $replace = false): void
    {
        $info = pathinfo($file);
        $dir = str_replace($path . DIRECTORY_SEPARATOR, "", $info['dirname']);
        $group = $path == $dir ? $info['filename'] : $dir . '/' . $info['filename'];
        $translations = Lang::getLoader()->load($locale, $group);

        if ($translations && is_array($translations)) {
            foreach (Arr::dot($translations) as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                $this->storeTranslation($key, (string)$value, $locale, $group, $replace);
            }
        }
    }

    /**
     * @param bool $replace
     * @return void
     */
    protected function importJsonTranslations(bool $replace = false): void
    {
        foreach ($this->files->files($this->app['path.lang']) as $file) {
            if (!str_contains($file, '.json')) {
                continue;
            }

            $locale = basename($file, '.json');
            $translations = Lang::getLoader()->load($locale, '*', '*');

            if ($translations && is_array($translations)) {
                foreach ($translations as $key => $value) {
                    $this->storeTranslation($key, $value, $locale, self::JSON_GROUP, $replace);
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function exportFileTranslations(): void
    {
        $translations = $this->translationModel
            ->withoutGroup(self::JSON_GROUP)
            ->withValue()
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        if ($translations->isEmpty()) {
            return;
        }

        $tree = $this->toTree($translations);

        foreach ($tree as $locale => $groups) {
            foreach ($groups as $group => $translations) {
                $file = $this->app['path.lang'] . '/' . $locale . '/' . $group . '.php';
                $output = "<?php\n\nreturn " . var_export($translations, true) . ";\n";

                $this->files->put($file, $output);
            }
        }
    }

    /**
     * @return void
     */
    protected function exportJsonTranslations(): void
    {
        $translations = $this->translationModel
            ->withGroup(self::JSON_GROUP)
            ->withValue()
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        if ($translations->isEmpty()) {
            return;
        }

        $tree = $this->toTree($translations, true);

        foreach ($tree as $locale => $groups) {
            $translations = $groups[self::JSON_GROUP] ?? [];

            if (empty($translations)) {
                continue;
            }

            $path = $this->app['path.lang'] . '/' . $locale . '.json';
            $output = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            $this->files->put($path, $output);
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @param string $locale
     * @param string $group
     * @param bool $replace
     * @return void
     */
    protected function storeTranslation(string $key, string $value, string $locale, string $group, bool $replace = false): void
    {
        $translation = $this->translationModel->firstOrNew([
            'locale' => $locale,
            'group' => $group,
            'key' => $key,
        ]);

        if (!$translation->value || $replace) {
            $translation->value = $value;
        }

        $translation->save();
    }

    /**
     * @param Collection $translations
     * @param bool $json
     * @return array
     */
    protected function toTree(Collection $translations, bool $json = false): array
    {
        $array = [];

        if ($json) {
            foreach ($translations as $translation) {
                $this->jsonSet(
                    $array[$translation->locale][$translation->group],
                    $translation->key,
                    $translation->value
                );
            }

            return $array;
        }

        foreach ($translations as $translation) {
            Arr::set(
                $array[$translation->locale][$translation->group],
                $translation->key,
                $translation->value
            );
        }

        return $array;
    }

    /**
     * @param array|null $array
     * @param ?string $key
     * @param ?string $value
     * @return array|string
     */
    public function jsonSet(?array &$array, ?string $key, ?string $value): array|string
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $array[$key] = $value;

        return $array;
    }
}

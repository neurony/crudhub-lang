<?php

namespace Zbiller\CrudhubLang\Singletons;

use Exception;
use Illuminate\Support\Facades\App;
use Zbiller\CrudhubLang\Contracts\LanguageModelContract;

class LocaleSingleton
{
    /**
     * @var string
     */
    protected static string $defaultLocale;

    /**
     * @var string[]
     */
    protected static array $activeLocales;

    /**
     * @var string[]
     */
    protected static array $allLocales;

    /**
     * @return void
     */
    private function __construct() {}

    /**
     * return void
     */
    private function __clone() {}

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize a singleton');
    }

    /**
     * @return string
     */
    public static function getDefaultLocale(): string
    {
        if (!isset(self::$defaultLocale)) {
            $defaultLanguage = App::make(LanguageModelContract::class)
                ->onlyDefault()
                ->first();

            if ($defaultLanguage instanceof LanguageModelContract && $defaultLanguage->exists) {
                self::$defaultLocale = $defaultLanguage->code;
            } else {
                self::$defaultLocale = app()->getLocale();
            }
        }

        return self::$defaultLocale;
    }

    /**
     * @return array
     */
    public static function getActiveLocales(): array
    {
        if (!isset(self::$activeLocales)) {
            $activeLanguages = App::make(LanguageModelContract::class)
                ->onlyActive()
                ->orderBy('default', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            self::$activeLocales = $activeLanguages->pluck('code')->toArray();
        }

        return self::$activeLocales;
    }

    /**
     * @return array
     */
    public static function getAllLocales(): array
    {
        if (!isset(self::$allLocales)) {
            $allLanguages = App::make(LanguageModelContract::class)
                ->orderBy('default', 'desc')
                ->orderBy('active', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            self::$allLocales = $allLanguages->pluck('code')->toArray();
        }

        return self::$allLocales;
    }
}

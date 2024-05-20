<?php

namespace Zbiller\CrudhubLang\Singletons;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Zbiller\CrudhubLang\Contracts\LanguageModelContract;

class LanguageSingleton
{
    /**
     * @var array<string, LanguageModelContract>
     */
    protected static array $languages;

    /**
     * @var LanguageModelContract|null
     */
    protected static ?LanguageModelContract $defaultLanguage;

    /**
     * @var Collection<int, LanguageModelContract>
     */
    protected static Collection $activeLanguages;

    /**
     * @var Collection<int, LanguageModelContract>
     */
    protected static Collection $allLanguages;

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
     * @param string|int|null $idOrCode
     * @return LanguageModelContract|null
     */
    public static function getLanguage(string|int|null $idOrCode): ?LanguageModelContract
    {
        if ($idOrCode === null) {
            return null;
        }

        if (!isset(self::$languages[$idOrCode])) {
            self::$languages[$idOrCode] = App::make(LanguageModelContract::class)
                ->where(is_numeric($idOrCode) ? 'id' : 'code', $idOrCode)
                ->first();
        }

        return self::$languages[$idOrCode];
    }

    /**
     * @return LanguageModelContract|null
     */
    public static function getDefaultLanguage(): ?LanguageModelContract
    {
        if (!isset(self::$defaultLanguage)) {
            self::$defaultLanguage = App::make(LanguageModelContract::class)
                ->onlyDefault()
                ->first();
        }

        return self::$defaultLanguage;
    }

    /**
     * @return Collection<int, LanguageModelContract>
     */
    public static function getActiveLanguages(): Collection
    {
        if (empty(self::$activeLanguages)) {
            self::$activeLanguages = App::make(LanguageModelContract::class)
                ->onlyActive()
                ->orderBy('default', 'desc')
                ->orderBy('name', 'asc')
                ->get();
        }

        return self::$activeLanguages;
    }

    /**
     * @return Collection<int, LanguageModelContract>
     */
    public static function getAllLanguages(): Collection
    {
        if (empty(self::$allLanguages)) {
            self::$allLanguages = App::make(LanguageModelContract::class)
                ->orderBy('default', 'desc')
                ->orderBy('active', 'desc')
                ->orderBy('name', 'asc')
                ->get();
        }

        return self::$allLanguages;
    }
}

<?php

namespace Zbiller\CrudhubLang\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Zbiller\CrudhubLang\Contracts\LanguageModelContract;
use Zbiller\CrudhubLang\Exceptions\LanguageException;
use Zbiller\CrudhubLang\Singletons\LanguageSingleton;
use Zbiller\Crudhub\Traits\FiltersRecords;
use Zbiller\Crudhub\Traits\SortsRecords;

class Language extends Model implements LanguageModelContract
{
    use FiltersRecords;
    use SortsRecords;

    /**
     * The database table.
     *
     * @var string
     */
    protected $table = 'languages';

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'code',
        'default',
        'active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'default' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->getOriginal('default') == true && $model->getAttribute('default') == false) {
                throw new LanguageException('A default language is required at all times!');
            }

            if ($model->isDirty('active') && $model->getAttribute('default') == true && $model->getAttribute('active') == false) {
                throw new LanguageException('Deactivating the default language is restricted!');
            }

            if ($model->isDirty('default') && $model->getAttribute('default') == true) {
                $model->active = true;

                static::where($model->getKeyName(), '!=', $model->getKey())->update([
                    'default' => false
                ]);
            }
        });

        static::deleting(function ($model) {
            if ($model->getAttribute('default') == true) {
                throw new LanguageException('Deleting the default language is restricted!');
            }
        });
    }

    /**
     * @param Builder $query
     * @return void
     */
    public function scopeOnlyDefault(Builder $query): void
    {
        $query->where('default', true);
    }

    /**
     * @param Builder $query
     * @return void
     */
    public function scopeExcludingDefault(Builder $query): void
    {
        $query->where('default', false);
    }

    /**
     * @param Builder $query
     * @return void
     */
    public function scopeOnlyActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * @param Builder $query
     * @return void
     */
    public function scopeOnlyInactive(Builder $query): void
    {
        $query->where('active', false);
    }

    /**
     * @param Builder $query
     * @return void
     */
    public function scopeAlphabetically(Builder $query): void
    {
        $query->orderBy('name');
    }

    /**
     * @return LanguageModelContract
     */
    public static function getDefaultLanguage(): LanguageModelContract
    {
        return LanguageSingleton::getDefaultLanguage();
    }

    /**
     * @return string|null
     */
    public static function getDefaultLocale(): ?string
    {
        return optional(static::getDefaultLanguage())->code ?: null;
    }
}

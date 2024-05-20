<?php

namespace Zbiller\CrudhubLang\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Zbiller\CrudhubLang\Contracts\TranslationModelContract;
use Zbiller\Crudhub\Traits\FiltersRecords;
use Zbiller\Crudhub\Traits\SortsRecords;

class Translation extends Model implements TranslationModelContract
{
    use FiltersRecords;
    use SortsRecords;

    /**
     * @var string
     */
    protected $table = 'translations';

    /**
     * @var string[]
     */
    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    /**
     * @param string $value
     */
    public function setValueAttribute(string $value)
    {
        $this->attributes['value'] = $value ?: null;
    }

    /**
     * @param Builder $query
     */
    public function scopeWithValue(Builder $query): void
    {
        $query->where('value', '!=', '')->whereNotNull('value');
    }

    /**
     * @param Builder $query
     * @return void
     */
    public function scopeWithoutValue(Builder $query): void
    {
        $query->where('value', '')->orWhereNull('value');
    }

    /**
     * @param Builder $query
     * @param string $group
     * @return void
     */
    public function scopeWithGroup(Builder $query, string $group): void
    {
        $query->where('group', $group);
    }

    /**
     * @param Builder $query
     * @param string $group
     * @return void
     */
    public function scopeWithoutGroup(Builder $query, string $group): void
    {
        $query->where('group', '!=', $group);
    }

    /**
     * @param Builder $query
     * @return void
     */
    public function scopeDistinctGroup(Builder $query): void
    {
        $query->select('group')->distinct();
    }
}

<?php

namespace Database\Seeders\CrudhubLang;

use Illuminate\Database\Seeder;
use Spatie\Permission\Contracts\Permission as PermissionModelContract;

class PermissionSeeder extends Seeder
{
    /**
     * @var array|string[][]
     */
    protected array $permissions = [
        // languages
        [
            'name' => 'languages-list',
            'guard_name' => 'admin'
        ],
        [
            'name' => 'languages-add',
            'guard_name' => 'admin'
        ],
        [
            'name' => 'languages-edit',
            'guard_name' => 'admin'
        ],
        [
            'name' => 'languages-delete',
            'guard_name' => 'admin'
        ],

        // translations
        [
            'name' => 'translations-list',
            'guard_name' => 'admin'
        ],
        [
            'name' => 'translations-edit',
            'guard_name' => 'admin'
        ],
        [
            'name' => 'translations-delete',
            'guard_name' => 'admin'
        ],
        [
            'name' => 'translations-import',
            'guard_name' => 'admin'
        ],
        [
            'name' => 'translations-export',
            'guard_name' => 'admin'
        ],

        // locales
        [
            'name' => 'locales-save',
            'guard_name' => 'admin'
        ],
    ];

    /**
     * @param PermissionModelContract $permissionModel
     * @return void
     */
    public function run(PermissionModelContract $permissionModel)
    {
        foreach ($this->permissions as $data) {
            $permission = $permissionModel->where($data)->first();

            if (!($permission instanceof PermissionModelContract && $permission->exists)) {
                $permissionModel->create($data);
            }
        }
    }
}

<?php

use Illuminate\Support\Facades\Route;
use Zbiller\CrudhubLang\Controllers\LanguageController;
use Zbiller\CrudhubLang\Controllers\TranslationController;

$prefix = config('crudhub.admin.prefix', 'admin');

$controllers = [
    'language' => '\\' . config('crudhub.bindings.controllers.language_controller', LanguageController::class),
    'translation' => '\\' . config('crudhub.bindings.controllers.translation_controller', TranslationController::class),
];

Route::prefix($prefix)->middleware([
    'web',
    'crudhub.inertia.handle_requests',
])->group(function () use ($controllers) {
    /**
     * Language routes
     */
    Route::prefix('languages')->middleware([
        'auth:admin',
    ])->group(function () use ($controllers) {
        Route::post('save', [$controllers['language'], 'save'])->name('admin.languages.save')->middleware('permission:languages-edit');
    });

    /**
     * Translation routes
     */
    Route::prefix('translations')->middleware([
        'auth:admin',
    ])->group(function () use ($controllers) {
        Route::get('', [$controllers['translation'], 'index'])->name('admin.translations.index')->middleware('permission:translations-list');
        Route::get('{translation}/edit', [$controllers['translation'], 'edit'])->name('admin.translations.edit')->middleware('permission:translations-edit');
        Route::put('{translation}', [$controllers['translation'], 'update'])->name('admin.translations.update')->middleware('permission:translations-edit');
        Route::post('import', [$controllers['translation'], 'import'])->name('admin.translations.import')->middleware('permission:translations-import');
        Route::post('export', [$controllers['translation'], 'export'])->name('admin.translations.export')->middleware('permission:translations-export');
    });
});

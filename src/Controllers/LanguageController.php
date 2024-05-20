<?php

namespace Zbiller\CrudhubLang\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Zbiller\CrudhubLang\Contracts\LanguageModelContract;
use Zbiller\CrudhubLang\Exceptions\LanguageException;
use Zbiller\Crudhub\Facades\Flash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class LanguageController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->saveActiveLanguages($request);
            $this->saveDefaultLanguage($request);

            DB::commit();

            Flash::success('Operation successful!');
        } catch (LanguageException $e) {
            DB::rollBack();

            Flash::error($e->getMessage(), $e);
        } catch (Throwable $e) {
            DB::rollBack();

            Flash::error(exception: $e);
        }

        return Redirect::previous();
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function saveDefaultLanguage(Request $request): void
    {
        if (!$request->filled('default_language')) {
            return;
        }

        $language = App::make(LanguageModelContract::class)
            ->find((int)$request->get('default_language'));

        if ($language instanceof LanguageModelContract && $language->exists) {
            $language->default = true;
            $language->active = true;
            $language->save();
        }
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function saveActiveLanguages(Request $request): void
    {
        if (!$request->filled('active_languages')) {
            return;
        }

        App::make(LanguageModelContract::class)
            ->query()
            ->update([
                'active' => false,
            ]);

        $languages = App::make(LanguageModelContract::class)
            ->whereIn('id', (array)$request->get('active_languages'))
            ->get();

        foreach ($languages as $language) {
            $language->active = true;
            $language->save();
        }
    }
}

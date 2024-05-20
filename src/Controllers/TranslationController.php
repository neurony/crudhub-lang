<?php

namespace Zbiller\CrudhubLang\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Zbiller\CrudhubLang\Contracts\TranslationModelContract;
use Zbiller\CrudhubLang\Contracts\TranslationServiceContract;
use Zbiller\Crudhub\Facades\Flash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use Zbiller\CrudhubLang\Requests\TranslationRequest;
use Zbiller\Crudhub\Resources\Resource;
use Zbiller\CrudhubLang\Singletons\LanguageSingleton;
use Zbiller\CrudhubLang\Singletons\LocaleSingleton;

class TranslationController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $items = App::make(TranslationModelContract::class)
            ->when(!empty((array)$request->get('groups')), function ($query) use ($request) {
                $query->whereIn('group', (array)$request->get('groups'));
            })
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $query->whereIn('key', function ($q) use ($request) {
                    $q->select('key')
                        ->from(App::make(TranslationModelContract::class)->getTable())
                        ->where('value', 'LIKE', '%' . $request->get('keyword') . '%');
                });
            })
            ->get();

        return Inertia::render('Translations/Index', [
            'data' => $this->buildPaginatedData($items, $request->get('page', 1), $request->get('per_page', 10)),
            'options' => [
                'languages' => LanguageSingleton::getAllLanguages(),
                'translation_groups' => $this->getTranslationGroups(),
            ],
        ]);
    }

    /**
     * @param TranslationModelContract $translation
     * @return \Inertia\Response
     */
    public function edit(TranslationModelContract $translation)
    {
        return Inertia::render('Translations/Edit', [
            'item' => Resource::make('translation_resource', $translation, 'crudhub-lang'),
        ]);
    }

    /**
     * @param TranslationModelContract $translation
     * @return RedirectResponse|void
     */
    public function update(TranslationModelContract $translation)
    {
        /** @var TranslationRequest $request */
        $request = $this->initRequest();

        try {
            DB::beginTransaction();

            $translation->update($request->all());

            DB::commit();

            Flash::success('Record updated successfully!');

            return Redirect::previous();
        } catch (Throwable $e) {
            DB::rollBack();

            Flash::error(exception: $e);
        }
    }

    /**
     * @param TranslationServiceContract $service
     * @return RedirectResponse|void
     */
    public function import(TranslationServiceContract $service)
    {
        try {
            DB::beginTransaction();

            $service->importTranslations();

            DB::commit();

            Flash::success('Imported successfully!');
        } catch (Throwable $e) {
            DB::rollBack();

            Flash::error('Failed importing!', $e);
        }

        return Redirect::route('admin.translations.index');
    }

    /**
     * @param TranslationServiceContract $service
     * @return RedirectResponse|void
     */
    public function export(TranslationServiceContract $service)
    {
        try {
            $service->exportTranslations();

            Flash::success('Exported successfully!');
        } catch (Throwable $e) {
            Flash::error('Failed exporting!', $e);
        }

        return Redirect::route('admin.translations.index');
    }

    /**
     * @return mixed
     */
    protected function initRequest(): mixed
    {
        $request = config('crudhub-lang.bindings.form_requests.translation_form_request', TranslationRequest::class);

        return App::make($request);
    }

    /**
     * @return array
     */
    protected function getTranslationGroups(): array
    {
        return App::make(TranslationModelContract::class)
            ->distinctGroup()
            ->get()
            ->pluck('group', 'group')
            ->toArray();
    }

    /**
     * @param Collection $items
     * @param int $page
     * @param int $per
     * @return array
     */
    protected function buildPaginatedData(Collection $items, int $page = 1, int $per = 10): array
    {
        $locales = array_values(LocaleSingleton::getActiveLocales());
        $offset = ($page - 1) * $per;
        $data = [];

        foreach ($items as $item) {
            $data[$item->group . '.' . $item->key][$item->locale] = $item->only([
                'id', 'locale', 'group', 'key', 'value'
            ]);
        }

        $items = array_slice($data, $offset, $per, true);

        foreach ($items as $key => $values) {
            uksort($values, function($a, $b) use ($locales) {
                return array_search($a, $locales) - array_search($b, $locales);
            });

            $items[$key] = $values;
        }

        $paginator = new LengthAwarePaginator($items, count($data), $per, $page);

        $paginator->withPath(route('admin.translations.index'));
        $paginator->appends(request()->query());

        return [
            'items' => $items,
            'paginator' => $paginator,
        ];
    }
}

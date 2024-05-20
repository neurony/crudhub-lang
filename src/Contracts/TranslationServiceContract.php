<?php

namespace Zbiller\CrudhubLang\Contracts;

interface TranslationServiceContract
{
    /**
     * @param bool $replace
     * @return void
     */
    public function importTranslations(bool $replace = false): void;

    /**
     * @return void
     */
    public function exportTranslations(): void;
}

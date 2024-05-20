<?php

/*
| ------------------------------------------------------------------------------------------------------------------
| Class Bindings
| ------------------------------------------------------------------------------------------------------------------
|
| FQNs of the classes used by the Crudhub platform internally to achieve different functionalities.
| Each of these classes represents a concrete implementation that is bound to the Laravel IoC container.
|
| If you need to extend or modify a functionality, you can swap the implementation below with your own class.
| Swapping the implementation, requires some steps, like extending the core class, or implementing an interface.
|
*/
return [

    /*
    | --------------------------------------------------------------------------------------------------------------
    | Model Class Bindings
    | --------------------------------------------------------------------------------------------------------------
    */
    'models' => [

        /*
        |
        | Concrete implementation for the "language model".
        | To extend or replace this functionality, change the value below with your full "language model" FQN.
        |
        */
        'language_model' => \Zbiller\CrudhubLang\Models\Language::class,

        /*
        |
        | Concrete implementation for the "translation model".
        | To extend or replace this functionality, change the value below with your full "translation model" FQN.
        |
        */
        'translation_model' => \Zbiller\CrudhubLang\Models\Translation::class,

    ],

    /*
    | --------------------------------------------------------------------------------------------------------------
    | Resource Class Bindings
    | --------------------------------------------------------------------------------------------------------------
    */
    'resources' => [

        /*
        |
        | Concrete implementation for the "language resource".
        | To extend or replace this functionality, change the value below with your full "language resource" FQN.
        |
        */
        'language_resource' => \Zbiller\CrudhubLang\Resources\LanguageResource::class,

        /*
        |
        | Concrete implementation for the "translation resource".
        | To extend or replace this functionality, change the value below with your full "translation resource" FQN.
        |
        */
        'translation_resource' => \Zbiller\CrudhubLang\Resources\TranslationResource::class,

    ],

    /*
    | --------------------------------------------------------------------------------------------------------------
    | Controller Class Bindings
    | --------------------------------------------------------------------------------------------------------------
    */
    'controllers' => [

        /*
        |
        | Concrete implementation for the "language controller".
        | To extend or replace this functionality, change the value below with your full "language controller" FQN.
        |
        */
        'language_controller' => \Zbiller\CrudhubLang\Controllers\LanguageController::class,

        /*
        |
        | Concrete implementation for the "translation controller".
        | To extend or replace this functionality, change the value below with your full "translation controller" FQN.
        |
        */
        'translation_controller' => \Zbiller\CrudhubLang\Controllers\TranslationController::class,

    ],

    'form_requests' => [

        /*
        |
        | Concrete implementation for the "translation form request".
        | To extend or replace this functionality, change the value below with your full "translation form request" FQN.
        |
        */
        'translation_form_request' => \Zbiller\CrudhubLang\Requests\TranslationRequest::class,

    ],

    /*
    | --------------------------------------------------------------------------------------------------------------
    | Service Class Bindings
    | --------------------------------------------------------------------------------------------------------------
    */
    'services' => [

        /*
        |
        | Concrete implementation for the "translation service".
        | To extend or replace this functionality, change the value below with your full "translation service" FQN.
        |
        */
        'translation_service' => \Zbiller\CrudhubLang\Services\TranslationService::class,

    ],

];

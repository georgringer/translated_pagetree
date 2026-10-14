<?php

use GeorgRinger\TranslatedPagetree\Controller\LanguageController;

return [
    'translated_pagetree_language' => [
        'path' => '/translated-pagetree/language',
        'methods' => ['POST'],
        'target' => LanguageController::class . '::storeAction',
    ],
];

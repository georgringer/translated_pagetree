<?php

declare(strict_types=1);

use GeorgRinger\TranslatedPagetree\Language\LanguagePreference;
use GeorgRinger\TranslatedPagetree\Processor\SiteLanguageItemsProcessor;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$field = LanguagePreference::FIELD;

$GLOBALS['TCA']['be_users']['columns'][$field] = [
    'label' => 'LLL:EXT:translated_pagetree/Resources/Private/Language/locallang.xlf:be_users.language',
    'description' => 'LLL:EXT:translated_pagetree/Resources/Private/Language/locallang.xlf:be_users.language.description',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'default' => 0,
        'items' => [
            [
                'label' => 'LLL:EXT:translated_pagetree/Resources/Private/Language/locallang.xlf:be_users.language.default',
                'value' => 0,
            ],
        ],
        'itemsProcessors' => [
            100 => [
                'class' => SiteLanguageItemsProcessor::class,
            ],
        ],
    ],
];

// Backend user record, e.g. for admins
ExtensionManagementUtility::addToAllTCAtypes('be_users', $field, '', 'after:lang');

// User settings, so that editors can choose the language themselves
ExtensionManagementUtility::addUserSetting(
    $field,
    [
        'inheritFromParent' => true,
    ],
    'after:--div--;core.form.tabs:backend_appearance'
);

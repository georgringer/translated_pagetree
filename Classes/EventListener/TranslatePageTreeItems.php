<?php

declare(strict_types=1);

namespace GeorgRinger\TranslatedPagetree\EventListener;

use GeorgRinger\TranslatedPagetree\Language\LanguagePreference;
use TYPO3\CMS\Backend\Controller\Event\AfterPageTreeItemsPreparedEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;

/**
 * Replaces the titles in the page tree with the title of the translation in the
 * language the backend user has chosen.
 *
 * Renaming a page in the tree always writes to the page of the default language.
 * Therefore all items showing a translated title are made read only.
 */
#[AsEventListener]
final readonly class TranslatePageTreeItems
{
    private const LLL = 'LLL:EXT:translated_pagetree/Resources/Private/Language/locallang.xlf:';

    public function __construct(
        private LanguagePreference $languagePreference,
        private ConnectionPool $connectionPool,
        private ExtensionConfiguration $extensionConfiguration,
    ) {}

    public function __invoke(AfterPageTreeItemsPreparedEvent $event): void
    {
        $languageId = $this->languagePreference->get();
        if ($languageId <= 0) {
            return;
        }

        $items = $event->getItems();
        $pageIds = [];
        foreach ($items as $item) {
            if (($item['recordType'] ?? '') === 'pages' && (int)$item['identifier'] > 0) {
                $pageIds[] = (int)$item['identifier'];
            }
        }
        if ($pageIds === []) {
            return;
        }

        $translations = $this->fetchTranslations(array_unique($pageIds), $languageId);
        if ($translations === []) {
            return;
        }

        $labelRendering = $this->getLabelRendering($languageId);
        $lockedHint = $this->getLanguageService()?->sL(self::LLL . 'tree.editLocked') ?? '';
        foreach ($items as &$item) {
            if (($item['recordType'] ?? '') !== 'pages') {
                continue;
            }
            $translation = $translations[(int)$item['identifier']] ?? null;
            if ($translation === null) {
                continue;
            }
            $title = $this->getTitle($translation, (string)($item['nameSourceField'] ?? 'title'));
            if ($title === '') {
                continue;
            }

            $item['name'] = $labelRendering !== null ? str_replace('%s', $title, $labelRendering) : $title;
            $item['editable'] = false;
            if ($lockedHint !== '') {
                $item['tooltip'] = trim(($item['tooltip'] ?? '') . ' - ' . $lockedHint, ' -');
            }
        }
        unset($item);

        $event->setItems($items);
    }

    /**
     * @param int[] $pageIds uids of the pages in the default language
     * @return array<int, array<string, mixed>> translation record by uid of the default language page
     */
    private function fetchTranslations(array $pageIds, int $languageId): array
    {
        $workspaceId = (int)($GLOBALS['BE_USER']->workspace ?? 0);

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        // hidden translations are shown too, as editors see them in the backend as well
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, $workspaceId));
        $rows = $queryBuilder
            ->select('uid', 'l10n_parent', 'title', 'nav_title', 't3ver_wsid', 't3ver_state')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in('l10n_parent', $queryBuilder->createNamedParameter($pageIds, Connection::PARAM_INT_ARRAY)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($languageId, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $translations = [];
        foreach ($rows as $row) {
            $parent = (int)$row['l10n_parent'];
            $isWorkspaceVersion = (int)$row['t3ver_wsid'] > 0;
            // a version in the current workspace wins over the live record
            if (isset($translations[$parent]) && !$isWorkspaceVersion) {
                continue;
            }
            $translations[$parent] = $row;
        }

        return array_filter(
            $translations,
            static fn(array $row): bool => VersionState::tryFrom((int)$row['t3ver_state']) !== VersionState::DELETE_PLACEHOLDER
        );
    }

    private function getTitle(array $translation, string $nameSourceField): string
    {
        $title = '';
        if ($nameSourceField === 'nav_title') {
            $title = trim((string)$translation['nav_title']);
        }

        return $title !== '' ? $title : trim((string)$translation['title']);
    }

    /**
     * Format for a language like "%s [DE]", configured in the extension settings as "1=%s [DE],2=%s [FR]".
     */
    private function getLabelRendering(int $languageId): ?string
    {
        try {
            $configuration = (string)$this->extensionConfiguration->get('translated_pagetree', 'labelRendering');
        } catch (\Exception) {
            return null;
        }
        foreach (GeneralUtility::trimExplode(',', $configuration, true) as $entry) {
            $parts = GeneralUtility::trimExplode('=', $entry, true, 2);
            if (count($parts) === 2 && (int)$parts[0] === $languageId && str_contains($parts[1], '%s')) {
                return $parts[1];
            }
        }

        return null;
    }

    private function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}

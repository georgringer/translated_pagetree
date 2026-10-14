<?php

declare(strict_types=1);

namespace GeorgRinger\TranslatedPagetree\Language;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * The language in which the current backend user wants to see the page tree.
 * 0 means the default language, so the tree is rendered as usual.
 */
final readonly class LanguagePreference
{
    public const FIELD = 'tx_translatedpagetree_language';

    public function __construct(
        private ConnectionPool $connectionPool,
        private SiteLanguageProvider $siteLanguageProvider,
    ) {}

    public function get(?BackendUserAuthentication $backendUser = null): int
    {
        $backendUser ??= $GLOBALS['BE_USER'] ?? null;

        return (int)($backendUser?->user[self::FIELD] ?? 0);
    }

    /**
     * @return bool false if the language is unknown and therefore was not stored
     */
    public function set(int $languageId, BackendUserAuthentication $backendUser): bool
    {
        if (!$this->siteLanguageProvider->isKnownLanguage($languageId)) {
            return false;
        }

        $this->connectionPool->getConnectionForTable('be_users')->update(
            'be_users',
            [self::FIELD => $languageId],
            ['uid' => (int)$backendUser->user['uid']],
        );
        $backendUser->user[self::FIELD] = $languageId;

        return true;
    }
}

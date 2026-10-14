<?php

declare(strict_types=1);

namespace GeorgRinger\TranslatedPagetree\Language;

use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Collects the languages of all sites, merged by language id.
 */
final class SiteLanguageProvider
{
    /** @var array<int, array{label: string, value: int, icon: string}>|null */
    private ?array $languages = null;

    public function __construct(
        private readonly SiteFinder $siteFinder,
    ) {}

    /**
     * Languages above the default language (id 0), keyed by language id.
     *
     * @return array<int, array{label: string, value: int, icon: string}>
     */
    public function getTranslationLanguages(): array
    {
        return array_filter($this->getAll(), static fn(array $language): bool => $language['value'] > 0);
    }

    /**
     * @return array<int, array{label: string, value: int, icon: string}>
     */
    public function getAll(): array
    {
        if ($this->languages !== null) {
            return $this->languages;
        }

        $languages = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            foreach ($site->getAllLanguages() as $language) {
                $languageId = $language->getLanguageId();
                if ($languageId < 0) {
                    continue;
                }
                $siteLabel = $language->getTitle() . ' [' . $site->getIdentifier() . ']';
                if (isset($languages[$languageId])) {
                    // same language id in another site: only extend the label
                    $languages[$languageId]['label'] .= ', ' . $siteLabel;
                    continue;
                }
                $languages[$languageId] = [
                    'label' => $siteLabel,
                    'value' => $languageId,
                    'icon' => $language->getFlagIdentifier(),
                ];
            }
        }
        ksort($languages);

        return $this->languages = $languages;
    }

    public function isKnownLanguage(int $languageId): bool
    {
        return $languageId === 0 || isset($this->getAll()[$languageId]);
    }
}

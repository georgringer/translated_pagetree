<?php

declare(strict_types=1);

namespace GeorgRinger\TranslatedPagetree\Processor;

use GeorgRinger\TranslatedPagetree\Language\SiteLanguageProvider;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\DataHandling\ItemsProcessorContext;
use TYPO3\CMS\Core\DataHandling\ItemsProcessorInterface;
use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Schema\Struct\SelectItemCollection;

/**
 * Adds the languages of all sites to the select field of the preferred tree language.
 */
#[Autoconfigure(public: true)]
final readonly class SiteLanguageItemsProcessor implements ItemsProcessorInterface
{
    public function __construct(
        private SiteLanguageProvider $siteLanguageProvider,
    ) {}

    public function processItems(SelectItemCollection $items, ItemsProcessorContext $context): SelectItemCollection
    {
        foreach ($this->siteLanguageProvider->getTranslationLanguages() as $language) {
            $items->add(new SelectItem(
                type: 'select',
                label: $language['label'],
                value: $language['value'],
                icon: $language['icon'] ?: null,
            ));
        }

        return $items;
    }
}

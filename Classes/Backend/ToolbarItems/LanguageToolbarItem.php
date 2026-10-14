<?php

declare(strict_types=1);

namespace GeorgRinger\TranslatedPagetree\Backend\ToolbarItems;

use GeorgRinger\TranslatedPagetree\Language\LanguagePreference;
use GeorgRinger\TranslatedPagetree\Language\SiteLanguageProvider;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Toolbar\RequestAwareToolbarItemInterface;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Toolbar dropdown to switch the language of the page tree.
 */
#[Autoconfigure(public: true)]
final class LanguageToolbarItem implements ToolbarItemInterface, RequestAwareToolbarItemInterface
{
    private ServerRequestInterface $request;

    public function __construct(
        private readonly BackendViewFactory $backendViewFactory,
        private readonly SiteLanguageProvider $siteLanguageProvider,
        private readonly LanguagePreference $languagePreference,
        private readonly PageRenderer $pageRenderer,
    ) {}

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * Switching makes only sense if there is something to switch to.
     */
    public function checkAccess(): bool
    {
        return $this->siteLanguageProvider->getTranslationLanguages() !== [];
    }

    public function getItem(): string
    {
        $this->pageRenderer->loadJavaScriptModule('@georgringer/translated-pagetree/language-switch.js');

        return $this->createView()->render('ToolbarItems/ToolbarItem');
    }

    public function hasDropDown(): bool
    {
        return true;
    }

    public function getDropDown(): string
    {
        $view = $this->createView();
        $view->assignMultiple([
            'languages' => $this->siteLanguageProvider->getAll(),
            'selected' => $this->languagePreference->get(),
        ]);

        return $view->render('ToolbarItems/DropDown');
    }

    public function getAdditionalAttributes(): array
    {
        return [];
    }

    public function getIndex(): int
    {
        return 30;
    }

    private function createView(): \TYPO3\CMS\Core\View\ViewInterface
    {
        return $this->backendViewFactory->create($this->request, ['georgringer/translated_pagetree']);
    }
}

<?php

declare(strict_types=1);

namespace GeorgRinger\TranslatedPagetree\Controller;

use GeorgRinger\TranslatedPagetree\Language\LanguagePreference;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Http\JsonResponse;

#[AsController]
final readonly class LanguageController
{
    public function __construct(
        private LanguagePreference $languagePreference,
    ) {}

    public function storeAction(ServerRequestInterface $request): ResponseInterface
    {
        $languageId = (int)(($request->getParsedBody()['language'] ?? 0));
        if (!$this->languagePreference->set($languageId, $GLOBALS['BE_USER'])) {
            return new JsonResponse(['success' => false], 400);
        }

        return new JsonResponse(['success' => true, 'language' => $languageId]);
    }
}

# TYPO3 extension `translated_pagetree`

Show the titles of the backend page tree in a language of your choice.

Editors working in a translated site often know the pages by their translated
titles. With this extension every backend user can choose a language, and the
page tree shows the title of the translation, if one exists. Pages without a
translation keep their default title.

Supported TYPO3 version: 14.3 LTS

## Installation

```bash
composer require georgringer/translated_pagetree
```

The extension is not available on Packagist yet. Until then, add the repository
to your `composer.json` first:

```bash
composer config repositories.translated_pagetree vcs https://github.com/georgringer/translated_pagetree
composer require georgringer/translated_pagetree:dev-main
```

Afterwards run the extension setup to create the database field:

```bash
vendor/bin/typo3 extension:setup
```

## Usage

The language can be chosen in three places:

- **Toolbar:** the language icon in the top bar switches the language directly.
  It is only shown if your sites have additional languages.
- **User settings:** field "Language of the page tree".
- **Backend user record:** the same field, e.g. to set it for an editor.

The choice is stored per user in `be_users.tx_translatedpagetree_language`.
`0` is the default language, the tree then looks like in a plain TYPO3.

### Renaming pages in the tree

A double click in the page tree renames the page in the **default language**.
To avoid overwriting the default title with a translated one, pages showing a
translated title can not be renamed in the tree. Switch back to the default
language to rename them.

### Search

The search of the page tree is not changed. TYPO3 already finds pages by the
title of their translations.

## Configuration

Extension setting `labelRendering` adds a suffix or prefix to translated titles,
so they can be told apart from default titles. It is a comma separated list of
`languageId=format`, `%s` is replaced by the translated title:

```
1=%s [DA],2=%s [FR]
```

Leave it empty to show the plain translated title.

## License

GPL-2.0-or-later

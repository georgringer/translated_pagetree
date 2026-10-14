import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

document.addEventListener('click', (event) => {
  const link = event.target.closest('.t3js-translatedpagetree-switch');
  if (link === null) {
    return;
  }
  event.preventDefault();

  const body = new FormData();
  body.append('language', link.dataset.language);

  // refresh the tree only after the language has been stored, otherwise the tree
  // may be reloaded with the previous language
  new AjaxRequest(TYPO3.settings.ajaxUrls.translated_pagetree_language)
    .post(body)
    .then(() => {
      document.querySelectorAll('.t3js-translatedpagetree-switch').forEach((element) => {
        element.classList.toggle('active', element === link);
      });
      document.dispatchEvent(new CustomEvent('typo3:pagetree:refresh'));
    });
});

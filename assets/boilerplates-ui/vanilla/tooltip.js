/* Généré par `make ui` depuis @kewinmarchand/boilerplates-ui, ne pas modifier à la main. */
/**
 * Info-bulle : affichée en CSS au survol et au focus, liée au déclencheur par `aria-describedby`.
 * Échap la masque sans déplacer le focus (WCAG 1.4.13), elle revient au survol ou au focus suivant.
 */

/** @param {KeyboardEvent} event */
const handleKeydown = (event) => {
  if (event.key !== 'Escape') return
  for (const tooltip of document.querySelectorAll('[data-bui-tooltip]')) {
    if (tooltip.matches(':hover, :focus-within')) tooltip.setAttribute('data-dismissed', '')
  }
}

/** @param {Event} event */
const handleLeave = (event) => {
  if (event.target instanceof Element && event.target.matches('[data-bui-tooltip]'))
    event.target.removeAttribute('data-dismissed')
}

/** @param {FocusEvent} event */
const handleFocusOut = (event) => {
  if (event.target instanceof Element)
    event.target.closest('[data-bui-tooltip]')?.removeAttribute('data-dismissed')
}

/**
 * Branche la fermeture par Échap des info-bulles. Renvoie la fonction qui débranche.
 * @param {Document | HTMLElement} root
 */
export const initTooltips = (root = document) => {
  const keydown = /** @type {EventListener} */ (handleKeydown)
  const focusout = /** @type {EventListener} */ (handleFocusOut)
  root.addEventListener('keydown', keydown)
  root.addEventListener('mouseleave', handleLeave, true)
  root.addEventListener('focusout', focusout)
  return () => {
    root.removeEventListener('keydown', keydown)
    root.removeEventListener('mouseleave', handleLeave, true)
    root.removeEventListener('focusout', focusout)
  }
}

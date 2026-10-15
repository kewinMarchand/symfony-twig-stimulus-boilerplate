/* Généré par `make ui` depuis @kewinmarchand/boilerplates-ui, ne pas modifier à la main. */
/**
 * Comportement de la modale sur `<dialog>` natif : `showModal()` rend le reste de la page inerte
 * (piège du focus) et ferme sur Échap. Le focus revient au déclencheur à la fermeture.
 * Ouverture : `<button data-bui-modal-open="id-de-la-modale">`. Fermeture : `data-bui-modal-close`.
 */

/** @param {HTMLDialogElement} dialog */
export const openModal = (dialog) => {
  const opener = document.activeElement
  dialog.addEventListener(
    'close',
    () => {
      if (opener instanceof HTMLElement) opener.focus()
    },
    { once: true },
  )
  dialog.showModal()
}

/** @param {Event} event */
const handleClick = (event) => {
  if (!(event.target instanceof Element)) return
  const opener = event.target.closest('[data-bui-modal-open]')
  const target = opener && document.getElementById(opener.getAttribute('data-bui-modal-open') ?? '')
  if (target instanceof HTMLDialogElement) {
    openModal(target)
    return
  }
  if (event.target.closest('[data-bui-modal-close]')) event.target.closest('dialog')?.close()
}

/**
 * Branche les modales par délégation d'événement. Renvoie la fonction qui débranche.
 * @param {Document | HTMLElement} root
 */
export const initModals = (root = document) => {
  root.addEventListener('click', handleClick)
  return () => root.removeEventListener('click', handleClick)
}

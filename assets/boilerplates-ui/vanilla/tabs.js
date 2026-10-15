/* Généré par `make ui` depuis @kewinmarchand/boilerplates-ui, ne pas modifier à la main. */
/**
 * Onglets, motif ARIA tabs avec activation automatique : flèches gauche et droite, Début et Fin.
 * Un seul onglet est dans l'ordre de tabulation (tabindex mobile).
 */

/** @param {HTMLElement} tab */
export const selectTab = (tab) => {
  const list = tab.closest('[role="tablist"]')
  if (!list) return
  for (const other of list.querySelectorAll('[role="tab"]')) {
    const selected = other === tab
    other.setAttribute('aria-selected', String(selected))
    other.setAttribute('tabindex', selected ? '0' : '-1')
    const panel = document.getElementById(other.getAttribute('aria-controls') ?? '')
    if (panel) panel.hidden = !selected
  }
  tab.focus()
}

const KEYS = ['ArrowLeft', 'ArrowRight', 'Home', 'End']

/** @param {Event} event */
const handleClick = (event) => {
  const tab =
    event.target instanceof Element && event.target.closest('[data-bui-tabs] [role="tab"]')
  if (tab instanceof HTMLElement) selectTab(tab)
}

/** @param {KeyboardEvent} event */
const handleKeydown = (event) => {
  const tab =
    event.target instanceof Element && event.target.closest('[data-bui-tabs] [role="tab"]')
  if (!(tab instanceof HTMLElement) || !KEYS.includes(event.key)) return
  const tabs = [...(tab.closest('[role="tablist"]')?.querySelectorAll('[role="tab"]') ?? [])]
  const index = tabs.indexOf(tab)
  const next = {
    ArrowLeft: tabs[(index - 1 + tabs.length) % tabs.length],
    ArrowRight: tabs[(index + 1) % tabs.length],
    Home: tabs[0],
    End: tabs[tabs.length - 1],
  }[event.key]
  if (next instanceof HTMLElement) {
    event.preventDefault()
    selectTab(next)
  }
}

/**
 * Branche les onglets `[data-bui-tabs]` par délégation. Renvoie la fonction qui débranche.
 * @param {Document | HTMLElement} root
 */
export const initTabs = (root = document) => {
  root.addEventListener('click', handleClick)
  root.addEventListener('keydown', /** @type {EventListener} */ (handleKeydown))
  return () => {
    root.removeEventListener('click', handleClick)
    root.removeEventListener('keydown', /** @type {EventListener} */ (handleKeydown))
  }
}

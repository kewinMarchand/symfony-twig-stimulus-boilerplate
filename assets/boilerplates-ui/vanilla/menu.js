/* Généré par `make ui` depuis @kewinmarchand/boilerplates-ui, ne pas modifier à la main. */
/**
 * Menu déroulant, motif ARIA menu button. Entrée, Espace ou flèche basse ouvrent sur le premier
 * élément, flèche haute sur le dernier. Dans le menu : flèches, Début, Fin, Échap et Tab ferment.
 * Choisir un élément émet `bui-menu-select` (detail : identifiant) sur `[data-bui-menu]` ; un choix exclusif
 * (`menuitemradio`) est d'abord coché (`aria-checked`), les autres décochés.
 */

const ITEM = '[role="menuitem"], [role="menuitemradio"]'

/** @param {Element} menu */
const parts = (menu) => {
  const trigger = /** @type {HTMLButtonElement} */ (menu.querySelector('.bui-menu__trigger'))
  const list = /** @type {HTMLElement} */ (menu.querySelector('[role="menu"]'))
  const items = /** @type {HTMLElement[]} */ ([...list.querySelectorAll(ITEM)])
  return { trigger, list, items }
}

/** @param {Element} menu @param {'first' | 'last'} focus */
export const openMenu = (menu, focus = 'first') => {
  const { trigger, list, items } = parts(menu)
  list.hidden = false
  trigger.setAttribute('aria-expanded', 'true')
  items[focus === 'first' ? 0 : items.length - 1]?.focus()
}

/** @param {Element} menu @param {boolean} restoreFocus */
export const closeMenu = (menu, restoreFocus = true) => {
  const { trigger, list } = parts(menu)
  list.hidden = true
  trigger.setAttribute('aria-expanded', 'false')
  if (restoreFocus) trigger.focus()
}

/** @param {Event} event */
const handleClick = (event) => {
  if (!(event.target instanceof Element)) return
  const menu = event.target.closest('[data-bui-menu]')
  for (const open of document.querySelectorAll('[data-bui-menu]')) {
    if (open !== menu && open.querySelector('[aria-expanded="true"]')) closeMenu(open, false)
  }
  if (!menu) return
  const item = event.target.closest(ITEM)
  if (item) {
    if (item.getAttribute('role') === 'menuitemradio') {
      for (const choice of parts(menu).items)
        choice.setAttribute('aria-checked', String(choice === item))
    }
    menu.dispatchEvent(
      new CustomEvent('bui-menu-select', {
        detail: item.getAttribute('data-bui-menu-item'),
        bubbles: true,
      }),
    )
    closeMenu(menu)
  } else if (event.target.closest('.bui-menu__trigger')) {
    if (parts(menu).list.hidden) openMenu(menu)
    else closeMenu(menu)
  }
}

/** @param {KeyboardEvent} event */
const handleKeydown = (event) => {
  if (!(event.target instanceof HTMLElement)) return
  const menu = event.target.closest('[data-bui-menu]')
  if (!menu) return
  const { trigger, items } = parts(menu)
  if (event.target === trigger) {
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
      event.preventDefault()
      openMenu(menu, event.key === 'ArrowDown' ? 'first' : 'last')
    }
    return
  }
  const index = items.indexOf(event.target)
  if (index === -1) return
  const next = {
    ArrowDown: items[(index + 1) % items.length],
    ArrowUp: items[(index - 1 + items.length) % items.length],
    Home: items[0],
    End: items[items.length - 1],
  }[event.key]
  if (next) {
    event.preventDefault()
    next.focus()
  } else if (event.key === 'Escape') {
    closeMenu(menu)
  } else if (event.key === 'Tab') {
    closeMenu(menu, false)
  }
}

/**
 * Branche les menus `[data-bui-menu]` par délégation. Renvoie la fonction qui débranche.
 * @param {Document | HTMLElement} root
 */
export const initMenus = (root = document) => {
  const keydown = /** @type {EventListener} */ (handleKeydown)
  root.addEventListener('click', handleClick)
  root.addEventListener('keydown', keydown)
  return () => {
    root.removeEventListener('click', handleClick)
    root.removeEventListener('keydown', keydown)
  }
}

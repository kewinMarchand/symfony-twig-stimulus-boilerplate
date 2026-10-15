/* Généré par `make ui` depuis @kewinmarchand/boilerplates-ui, ne pas modifier à la main. */
/**
 * Tri d'un tableau par les boutons `[data-bui-sort]` de l'en-tête : trie les lignes du corps
 * sur le texte de la colonne, pose `aria-sort` sur la seule colonne triée et change l'icône.
 */

const ICONS = { ascending: 'arrow-up', descending: 'arrow-down', none: 'arrow-up-down' }

/** @param {HTMLButtonElement} button */
export const sortTable = (button) => {
  const header = /** @type {HTMLTableCellElement} */ (button.closest('th'))
  const table = /** @type {HTMLTableElement} */ (button.closest('table'))
  const body = table.tBodies[0]
  if (!body) return
  const direction = header.getAttribute('aria-sort') === 'ascending' ? 'descending' : 'ascending'
  const column = header.cellIndex

  for (const cell of table.tHead?.rows[0]?.cells ?? []) {
    cell.removeAttribute('aria-sort')
    cell.querySelector('use')?.setAttribute('href', iconHref(cell, ICONS.none))
  }
  header.setAttribute('aria-sort', direction)
  header.querySelector('use')?.setAttribute('href', iconHref(header, ICONS[direction]))

  const text = (/** @type {HTMLTableRowElement} */ row) => row.cells[column]?.textContent ?? ''
  const rows = [...body.rows].sort(
    (a, b) =>
      text(a).localeCompare(text(b), 'fr', { numeric: true }) *
      (direction === 'ascending' ? 1 : -1),
  )
  body.append(...rows)
}

/** @param {Element} cell @param {string} name */
const iconHref = (cell, name) =>
  `${(cell.querySelector('use')?.getAttribute('href') ?? '/icons.svg#').split('#')[0]}#${name}`

/** @param {Event} event */
const handleClick = (event) => {
  const button =
    event.target instanceof Element && event.target.closest('.bui-table [data-bui-sort]')
  if (button instanceof HTMLButtonElement) sortTable(button)
}

/**
 * Branche le tri des tableaux par délégation. Renvoie la fonction qui débranche.
 * @param {Document | HTMLElement} root
 */
export const initTables = (root = document) => {
  root.addEventListener('click', handleClick)
  return () => root.removeEventListener('click', handleClick)
}

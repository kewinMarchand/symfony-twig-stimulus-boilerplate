import { existsSync, mkdirSync, readdirSync, readFileSync, writeFileSync } from 'node:fs'
import { dirname, join } from 'node:path'

/**
 * Copie de boilerplates-ui dans `assets/boilerplates-ui/`, versionnée : la production n'a pas Node et AssetMapper
 * sert les fichiers tels quels. La feuille réunit les tokens et les composants en un seul fichier, chaque `@import`
 * étant une requête de plus qui bloque l'affichage. `--check` ne réécrit rien et échoue si la copie est périmée.
 */
const LIBRARY = 'node_modules/@kewinmarchand/boilerplates-ui'
const OUTPUT = 'assets/boilerplates-ui'
const HEADER = '/* Généré par `make ui` depuis @kewinmarchand/boilerplates-ui, ne pas modifier à la main. */\n'

const read = (path) => readFileSync(join(LIBRARY, path), 'utf8')

const inlineImports = (path) =>
  read(path).replace(/^@import '\.\/(.+)';$/gm, (_, file) => inlineImports(join(dirname(path), file)).trim())

const vanilla = readdirSync(join(LIBRARY, 'packages/vanilla/src')).filter((file) => file.endsWith('.js'))

const outputs = {
  [`${OUTPUT}/boilerplates-ui.css`]: `${HEADER}${read('packages/tokens/generated/tokens.css').replace(/^\/\*.*\*\/\n/, '')}\n${inlineImports('packages/css/index.css')}`,
  ...Object.fromEntries(
    vanilla.map((file) => [`${OUTPUT}/vanilla/${file}`, `${HEADER}${read(`packages/vanilla/src/${file}`)}`]),
  ),
}

const version = JSON.parse(read('package.json')).version

if (process.argv.includes('--check')) {
  const stale = Object.entries(outputs)
    .filter(([path, content]) => !existsSync(path) || readFileSync(path, 'utf8') !== content)
    .map(([path]) => path)
  if (stale.length > 0) {
    console.error(`Copie de boilerplates-ui périmée (${stale.join(', ')}) : lancer make ui.`)
    process.exit(1)
  }
  console.log(`boilerplates-ui ${version} à jour dans ${OUTPUT}/.`)
} else {
  mkdirSync(`${OUTPUT}/vanilla`, { recursive: true })
  for (const [path, content] of Object.entries(outputs)) writeFileSync(path, content)
  console.log(`boilerplates-ui ${version} copiée dans ${OUTPUT}/ (${Object.keys(outputs).length} fichiers).`)
}

import { defineConfig, globalIgnores } from 'eslint/config'
import simpleImportSort from 'eslint-plugin-simple-import-sort'

export default defineConfig([
  globalIgnores(['assets/boilerplates-ui/', 'assets/vendor/', 'assets/sentry/', 'public/', 'var/', 'vendor/']),
  {
    files: ['**/*.js'],
    plugins: { 'simple-import-sort': simpleImportSort },
    rules: {
      'simple-import-sort/imports': [
        'error',
        {
          // Ordre de standards-front : 1 tiers (Node, paquets et importmap), 2 relatifs, 3 effets de bord en dernier
          // (feuilles de style d'app.js), que le plugin ne réordonne pas entre eux : leur ordre fixe la cascade.
          groups: [['^node:', '^@?\\w'], ['^\\.'], ['^\\u0000']],
        },
      ],
      'simple-import-sort/exports': 'error',
    },
  },
])

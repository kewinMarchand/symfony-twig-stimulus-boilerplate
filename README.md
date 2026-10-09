# symfony-twig-stimulus-boilerplate

Point de départ pour une application **Symfony 7.4 LTS** rendue côté serveur avec **Twig**, enrichie par **Stimulus** et **Turbo**, avec toute la chaîne qualité : lint, format, analyse statique, tests unitaires et fonctionnels, end-to-end, accessibilité et Lighthouse. Tout tourne dans Docker : aucun PHP ni Composer n'est nécessaire sur le poste.

Les pages de démonstration montrent chaque brique en situation :

| Page                       | Ce qu'elle montre                                                                                                   |
| -------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| `/`                        | Hero pleine largeur (image LCP prioritaire), carrousel Embla en contrôleur Stimulus, grille d'articles rendue serveur |
| `/catalogue/...`           | Catalogue à facettes : formulaire GET natif, état dans l'URL, Turbo Frame, panneau de filtres modal sur mobile        |
| `/taches`                  | Liste chargée à la demande dans un Turbo Frame, avec ses trois états : chargement, erreur avec relance, liste vide    |
| `/contact`                 | Symfony Form et Validator, erreurs liées aux champs, envoi par Turbo Stream, fonctionne sans JavaScript              |
| Pages légales              | Mentions légales, données personnelles, déclaration d'accessibilité calculée depuis la config, plan du site          |
| `/charte-graphique`        | Charte graphique, en développement uniquement (404 en production)                                                    |

Un bouton « Accessibilité renforcée » dans l'en-tête agrandit le texte, espace le contenu (WCAG 1.4.12), renforce les contrastes et coupe les animations. Le choix est mémorisé dans le navigateur.

## Stack

| Brique                                      | Rôle                                                                    |
| ------------------------------------------- | ----------------------------------------------------------------------- |
| Symfony 7.4 LTS, PHP 8.4 (FrankenPHP)       | Routage, contrôleurs, formulaires, validation, pages d'erreur            |
| Twig 3                                      | Rendu serveur, templates rangés par domaine                              |
| AssetMapper + importmap                     | JavaScript et CSS sans bundler, versionnés à la compilation              |
| Stimulus (stimulus-bundle), Turbo (ux-turbo) | Comportements ciblés, navigation et formulaires sans rechargement        |
| Embla Carousel 8                            | Carrousel (glisser, swipe, molette), derrière un seul contrôleur          |
| symfony/ux-icons (lucide)                   | Icônes SVG inline, derrière un seul partial Twig                          |
| CSS maison, tokens en variables CSS         | Thème, mode renforcé, aucune dépendance CSS                               |
| PHP-CS-Fixer, PHPStan (niveau max)          | Style et analyse statique                                                |
| PHPUnit 12, node:test                       | Tests unitaires (domaine, fonctions pures) et fonctionnels (WebTestCase) |
| Playwright + axe-core                       | Tests end-to-end et accessibilité, desktop et mobile, deux modes         |
| Lighthouse CI                               | Performance, accessibilité, SEO, bonnes pratiques                         |

## Prérequis

- Docker et Docker Compose
- Node.js 24.14 ou plus (`.nvmrc`) et Yarn 1, pour Playwright, Lighthouse et le test unitaire du mode renforcé (`node:test`, lancé par `make test-unit`)

## Démarrage

```sh
git clone https://github.com/kewinMarchand/symfony-twig-stimulus-boilerplate.git
cd symfony-twig-stimulus-boilerplate
make install   # image de dev, dépendances PHP et Node, navigateur Chromium des tests
make up        # http://localhost:8095
```

Variables d'environnement (`.env`, à surcharger dans `.env.local` non versionné ou par de vraies variables) :

| Variable      | Rôle                                                                              |
| ------------- | --------------------------------------------------------------------------------- |
| `SITE_URL`    | URL publique : URL canoniques, Open Graph, JSON-LD, sitemap                        |
| `APP_SECRET`  | Secret Symfony, obligatoire en production                                          |
| `HTTP_PORT`   | Port publié par Docker (8095 par défaut)                                           |
| `MAINTENANCE` | `1` sert la page statique de maintenance en 503 avec `Retry-After`, sans PHP        |

## Commandes

`make help` liste toutes les commandes. Les principales :

| Commande                               | Effet                                                                                     |
| -------------------------------------- | ----------------------------------------------------------------------------------------- |
| `make up` / `make down`                | Serveur de développement, arrêt des conteneurs                                             |
| `make qa`                              | QA rapide : lint, format, analyse statique, tests unitaires et fonctionnels                |
| `make qa-full`                         | QA complète : QA rapide, e2e, accessibilité, Lighthouse                                    |
| `make format`                          | PHP-CS-Fixer en écriture                                                                   |
| `make typecheck`                       | PHPStan niveau max                                                                         |
| `make test-unit`                       | PHPUnit et test du mode renforcé                                                           |
| `make test-e2e`                        | Playwright sur l'image de production, puis les tests `@dev` sur le serveur de développement |
| `make test-a11y`                       | axe-core, WCAG 2.1 AA, chaque page dans les deux modes                                     |
| `make metrics`                         | Lighthouse CI sur l'image de production (port 3230) : échoue sous 90 en performance ou sous 100 en accessibilité et SEO |
| `make docker-build` / `make docker-up` | Image de production, sur le port `HTTP_PORT`                                               |

Toutes les commandes PHP tournent dans un conteneur jetable (`docker compose run --rm php`). En CI, `make qa EXEC=` les exécute directement sur la machine.

## Architecture

```
src/
  Domain/            un dossier par domaine, sans aucune dépendance au framework
    Catalog/
      Model/         entités, value objects, enums
      Repository/    interfaces (ports)
      Service/       fonctions pures : filtre, comptage disjonctif, tri, pagination
      Exception/
  Infrastructure/    adaptateurs : dépôts en mémoire, envoi de message par logger
  UI/
    Http/Controller/ couche de routage : un contrôleur mince par page, il délègue à une vue Twig
    Http/Form/       formulaires et leurs contraintes
    Http/Catalog/    état du catalogue porté par l'URL
    Seo/             construction unique des métadonnées et du fil d'Ariane
    Twig/            fonctions et filtres Twig
templates/
  core/              layout, en-tête, menus, pied de page, icône, image responsive
  features/          briques agnostiques (carrousel)
  <domaine>/         vues de chaque page
assets/
  controllers/       contrôleurs Stimulus
  styles/app.css     tokens et styles
tests/
  Unit/ Functional/  PHPUnit
  js/                node:test
  e2e/ a11y/         Playwright
```

Le sens des dépendances est `UI` vers `Domain` et `Infrastructure` vers `Domain`, jamais l'inverse. `make lint` vérifie que `src/Domain` n'importe ni Symfony, ni Doctrine, ni Twig, ni PSR, et que `ux_icon()` n'est appelé que dans `templates/core/ui/_icon.html.twig`.

Les dépôts sont en mémoire avec une latence simulée. Le jour où l'API est disponible, on écrit un adaptateur HttpClient dans `src/Infrastructure/` et on change l'alias dans `config/services.yaml`, sans toucher au domaine ni aux vues.

Les conventions de code viennent du `CLAUDE.md` global de l'auteur. Le `CLAUDE.md` de ce dépôt note les choix propres au projet, les versions épinglées et les pièges connus.

## Recettes

### Ajouter une page

1. Créer le contrôleur dans `src/UI/Http/Controller/`, avec sa route en attribut. Il ne fait qu'appeler le domaine et rendre une vue.
2. Créer la vue `templates/<domaine>/index.html.twig`, qui commence par `{% set seo = seo_page({title: '…', description: '…'}) %}`.
3. Ajouter la route à `config/packages/site.yaml` (navigation, sitemap, plan du site, fil d'Ariane) et à `tests/e2e/routes.ts` (tests de réponse, SEO, débordement et accessibilité), puis à `.lighthouserc.json`.
4. Écrire `tests/e2e/<page>.e2e.ts` et un test fonctionnel dans `tests/Functional/`.

### Charger des données à la demande avec un Turbo Frame

Le pattern est dans le domaine Tasks :

- `src/Domain/Tasks/Repository/TaskRepository.php` est le port, `src/Infrastructure/InMemory/InMemoryTaskRepository.php` l'adaptateur.
- `TasksController::index()` rend la page avec un `<turbo-frame loading="lazy">` qui contient le squelette de chargement.
- `TasksController::list()` rend le contenu du frame : liste, état vide, ou erreur métier avec un bouton « Réessayer » (simple formulaire GET, sans JavaScript dédié).

### Ajouter un champ de formulaire

1. Ajouter la propriété et ses contraintes, avec leur message en français, dans `src/UI/Http/Form/ContactFormData.php`.
2. L'ajouter dans `ContactType.php`, puis dans `templates/contact/_form.html.twig` avec son `data-testid`. Symfony relie l'erreur au champ par `aria-describedby` et pose `aria-invalid`.

### Ajouter une icône

`docker compose run --rm php bin/console ux:icons:import lucide:<nom>`, puis `{{ include('core/ui/_icon.html.twig', {name: '<nom>'}) }}`. Les SVG sont versionnés dans `assets/icons/lucide/` : la production ne contacte jamais Iconify.

## Tests

- **Unitaires et fonctionnels** : `tests/Unit` (domaine et fonctions pures, sans conteneur), `tests/Functional` (WebTestCase, avec des dépôts remplacés pour les états vide et erreur), `tests/js` (mode renforcé).
- **End-to-end** : Playwright sur l'image de production servie sur le port 8095. Les éléments sont ciblés par `data-testid`, préfixé par le domaine (`catalog-product`, `contact-submit`). Chaque rendu conditionnel est testé présent et absent. Les tests marqués `@dev` (page 500, charte graphique) tournent ensuite sur le serveur de développement.
- **Accessibilité** : `tests/a11y/` passe axe-core sur chaque route, en mode standard et renforcé, desktop et mobile, menus ouverts compris. Axe ne couvre qu'une partie du RGAA, un audit manuel reste nécessaire.
- **Captures** : `tests/e2e/layout.e2e.ts` enregistre l'accueil et le catalogue dans les deux modes, à 375, 768 et 1280 px, dans `screenshots/`.

## Intégration continue

`.github/workflows/ci.yml` lance `make qa` sur PHP installé par `setup-php`, puis les tests e2e, accessibilité et Lighthouse dans Docker, à chaque push sur `main` et sur chaque pull request. En cas d'échec, le rapport Playwright est joint au run.

## Licence

MIT

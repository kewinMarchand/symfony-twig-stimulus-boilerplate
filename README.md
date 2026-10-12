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
| Sprite SVG tiré de Lucide                   | Icônes, générées par `make icons`, derrière un seul partial Twig          |
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
| `GLITCHTIP_DSN` | Suivi des erreurs GlitchTip (DSN au format Sentry). Vide : rien n'est chargé ni envoyé |
| `GLITCHTIP_ENVIRONMENT` | Environnement des erreurs remontées, par défaut celui de Symfony |
| `MATOMO_URL`, `MATOMO_SITE_ID` | Mesure d'audience Matomo sans cookie. Les deux sont nécessaires, vides : rien n'est chargé ni envoyé |
| `MAINTENANCE` | `1` sert la page statique de maintenance en 503 avec `Retry-After`, sans PHP        |

## Démarrer un projet à partir de ce dépôt

### Créer le dépôt

La voie recommandée est le template GitHub : le nouveau dépôt part d'un historique neuf, sans lien avec celui-ci. Bouton « Use this template » sur GitHub, ou en ligne de commande :

```sh
gh repo create mon-projet --template kewinMarchand/symfony-twig-stimulus-boilerplate --private --clone
cd mon-projet
make install
make up
```

Pour suivre les évolutions du boilerplate ou y contribuer, passer plutôt par un fork, qui garde l'historique commun :

```sh
gh repo fork kewinMarchand/symfony-twig-stimulus-boilerplate --clone --fork-name mon-projet
```

GitHub n'autorise pas un fork dans le compte qui possède déjà le dépôt : depuis ce compte, passer par le template, ou forker vers une organisation avec `--org`.

### Récupérer les mises à jour

Avec un fork, l'historique est commun : `gh repo fork --clone` a déjà déclaré le dépôt d'origine comme remote `upstream`.

```sh
git fetch upstream
git merge upstream/main
```

Avec un template, l'historique n'est pas commun et une fusion n'a pas de sens. On reprend les commits utiles un par un :

```sh
git remote add upstream https://github.com/kewinMarchand/symfony-twig-stimulus-boilerplate.git
git fetch upstream
git log --oneline upstream/main
git cherry-pick <sha>
```

### Personnaliser

| Sujet | Fichiers |
| --- | --- |
| Nom du projet et du paquet | `composer.json` (`name`, `description`), `package.json` (`name`, `description`), `compose.yaml` (`name:` du projet Compose, image `…:prod`), `compose.override.yaml` (image `…:dev`), `Makefile.qa.mk` (`LHCI_PROJECT`), `LICENSE`, titres de `README.md` et `CLAUDE.md` |
| Config du site et identité | `config/packages/site.yaml` (nom, description, couleur du thème, navigation), `templates/seo/manifest.webmanifest.twig` (`short_name`), `src/Shared/UI/Seo/SeoPageFactory.php` (texte alternatif de l'image de partage), `templates/home/index.html.twig` (sur-titre et boutons du hero), `src/Site/Home/InMemorySlideRepository.php` (diapositives). Le nom du site est aussi vérifié par `tests/Functional/PagesTest.php` (`SITE`) et `tests/e2e/navigation.e2e.ts` |
| Ports | `Makefile` (`HTTP_PORT`, 8095), `compose.yaml` (port par défaut), `.env` (`SITE_URL`), `playwright.config.ts`, `Makefile.qa.mk` (`LHCI_PORT`, 3230) et `.lighthouserc.json` |
| Logo et favicons | `assets/images/logo-mark.svg`, `public/favicon.ico`, `public/favicon.svg`, `public/apple-touch-icon.png`, `public/icon-192.png`, `public/icon-512.png`, `public/og-image.jpg` |
| Pages légales | `config/packages/site.yaml` (`publisher`, `host`, et `site.accessibility` une fois l'audit RGAA réalisé), textes de `templates/legal/` |
| URL Lighthouse | `.lighthouserc.json` (une URL par page à mesurer, sur le port 3230) |
| CI | `.github/workflows/ci.yml` |

### Retirer les modules d'exemple

`Catalog`, `Blog` et `Task` sont des exemples. `Contact` sert de module restant, et `Site` et `Shared` restent en place. Pour les retirer sans casser deptrac ni `make arch-selftest` :

1. **Code** : supprimer `src/Catalog/`, `src/Blog/` et `src/Task/`, puis `assets/controllers/catalog_controller.js` et `assets/controllers/category_menu_controller.js`. Dans `src/Site/UI/HomeController.php`, ne garder que les diapositives.
2. **Templates** : supprimer `templates/catalog/`, `templates/tasks/`, `templates/legal/_category_tree.html.twig`, et dans `templates/shared/layout/`, `_category_menu.html.twig`, `_category_menu_column.html.twig` et `_mobile_menu_level.html.twig`. Puis retirer :
   - dans `templates/shared/layout/_header.html.twig`, l'appel `catalog_tree()` et la branche `catalog` de la navigation
   - dans `_mobile_menu.html.twig`, la branche `catalog` et les niveaux du catalogue
   - dans `templates/home/index.html.twig`, la section des articles et le bouton vers `/taches`
   - dans `templates/legal/sitemap.html.twig`, la section Catalogue
   - dans `templates/seo/sitemap.xml.twig`, la boucle `catalog_paths()` qui produisait les entrées de catégories
   - dans `templates/bundles/TwigBundle/Exception/error404.html.twig`, le lien vers le catalogue
   - dans `templates/styleguide/index.html.twig`, les liens `path('catalog')` et le filtre `price`.
3. **Routes et services** : les routes disparaissent avec leurs contrôleurs. Retirer les entrées `catalog` et `tasks` de la navigation dans `config/packages/site.yaml` (menu, sitemap, plan du site, fil d'Ariane), et les alias des ports de `Task`, `Blog` et `Catalog` dans `config/services.yaml`.
4. **Architecture** : dans `deptrac.yaml`, supprimer les couches et les règles `Catalog*`, `Blog*` et `Task*`, et réduire la règle de `Site` à `[ContactDomain, Shared, Framework]`. Dans `tools/arch-selftest.sh`, repointer les cinq violations PHP vers `Contact`, en gardant leurs noms de classe :

   | Violation | Fichier | Dépendance interdite |
   | --- | --- | --- |
   | `ArchSelftestDomainToInfrastructure` | `src/Contact/Domain/` | `App\Contact\Infrastructure\Logger\LoggerContactMessageSender` |
   | `ArchSelftestDomainToFramework` | `src/Contact/Domain/` | `Symfony\Component\HttpFoundation\Response`, inchangée |
   | `ArchSelftestModuleToModule` | `src/Contact/Domain/` | `App\Site\Home\Slide` (sans second module, le module vise `Site`) |
   | `ArchSelftestSharedToModule` | `src/Shared/UI/` | `App\Contact\Domain\ContactMessage` |
   | `ArchSelftestUiToInfrastructure` | `src/Contact/Infrastructure/` | `App\Contact\UI\ContactController` (l'UI de `Contact` peut appeler son infrastructure, on teste donc le sens interdit) |
5. **Tests** :
   - supprimer `tests/Unit/Catalog/`, `tests/Unit/Task/`, `tests/Functional/CatalogControllerTest.php` et `tests/Functional/TasksControllerTest.php`
   - dans `tests/Functional/PagesTest.php`, retirer les pages catalogue, sous-catégorie et tâches, et l'URL de catégorie du sitemap
   - dans `tests/Functional/HomeControllerTest.php`, retirer les tests du blog
   - côté Playwright, supprimer `tests/e2e/catalogue.e2e.ts`, `taches.e2e.ts` et `menu.e2e.ts`, et adapter `routes.ts`, `accueil.e2e.ts`, `breadcrumb.e2e.ts`, `pages.e2e.ts`, `layout.e2e.ts` et `tests/a11y/pages.a11y.ts`.
6. **Lighthouse** : retirer `/catalogue`, `/catalogue/plantes-interieur/feuillages` et `/taches` de `.lighthouserc.json`.
7. **Facultatif** : les images `assets/images/product-*` et `blog-*`, et les styles du catalogue dans `assets/styles/app.css`.

Terminer par `make qa`, puis `make test-e2e test-a11y`. Cette procédure a été déroulée sur une copie jetable du dépôt jusqu'à un `make qa` vert : 0 violation deptrac, 7 violations volontaires sur 7 détectées, 40 tests PHPUnit.

## Commandes

`make help` liste toutes les commandes. Les principales :

| Commande                               | Effet                                                                                     |
| -------------------------------------- | ----------------------------------------------------------------------------------------- |
| `make up` / `make down`                | Serveur de développement, arrêt des conteneurs                                             |
| `make qa`                              | QA rapide : lint, architecture, format, analyse statique, tests unitaires et fonctionnels  |
| `make arch`                            | Règles d'architecture : deptrac (`deptrac.yaml`) et bibliothèques tierces confinées à leur wrapper |
| `make arch-selftest`                   | Écrit une violation volontaire de chaque règle, exige qu'elles soient toutes détectées, puis les retire |
| `make icons`                          | Génère le sprite `public/icons.svg` et la liste des noms depuis `icons.json`                |
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

Découpage par module métier d'abord, par couche ensuite, selon l'architecture commune aux boilerplates de l'auteur (décisions D1 à D12, règle R12 pour PHP).

```
src/
  Catalog/           module complet : catalogue de la jardinerie
    Domain/          modèle sans framework : Category, CategoryTree, CategoryPath, CatalogQuery, ProductFilters,
                     Product, Price (centimes), le port CatalogRepository
    Application/     ShowCatalogPage : résout la catégorie, interroge le dépôt, décide (introuvable, page
                     hors bornes, échec de chargement), sans framework
    Infrastructure/  InMemory/ : dépôt en mémoire et CatalogSearch, le simulateur de l'API (filtre, comptage
                     disjonctif, tri, pagination), qui disparaîtra avec lui au branchement
    UI/              contrôleur, état de recherche porté par l'URL (euros), fenêtre de pagination, extension Twig
  Blog/              forme réduite : Domain/ (Article, port, exception), Infrastructure/InMemory/
  Contact/           forme réduite : Domain/, Infrastructure/Logger/, UI/ (contrôleur, formulaire)
  Task/              forme réduite : Domain/ (Task protège son invariant), Infrastructure/InMemory/, UI/
  Site/              pages sans métier propre : UI/ (accueil, légal, SEO, charte), Home/ (diapositives),
                     Legal/ (conformité d'accessibilité), Styleguide/ (contraste, tokens)
  Shared/            socle sans métier : UI/Seo/ (métadonnées, fil d'Ariane), UI/Twig/
templates/
  shared/            layout, en-tête, menus, pied de page, icône, image responsive, carrousel
  <page>/            vues de chaque page (catalog, contact, home, legal, seo, styleguide, tasks)
assets/
  controllers/       contrôleurs Stimulus
  styles/app.css     tokens et styles
tests/
  Unit/<Module>/     PHPUnit, rangés comme src/
  Functional/        WebTestCase
  js/                node:test
  e2e/ a11y/         Playwright
tools/
  arch-selftest.sh   violations volontaires de make arch-selftest
```

Les règles de dépendance sont vérifiées par deptrac (`make arch`) : `Domain` ne dépend que de lui-même, ni framework, ni autre couche, ni autre module. `Infrastructure` dépend du `Domain` de son module et des frameworks. `UI` du `Domain` de son module, de `Shared` et des frameworks, et de l'`Infrastructure` seulement dans les modules sans couche application. `Site` compose les modules, `Shared` n'en importe aucun. `make arch` vérifie aussi que le sprite `icons.svg` n'est utilisé que dans `templates/shared/ui/_icon.html.twig` et qu'Embla n'est importé que dans `assets/controllers/carousel_controller.js`. `make arch-selftest` écrit une violation de chaque règle et exige qu'elles soient toutes détectées.

Les dépôts sont en mémoire avec une latence simulée. Le jour où l'API est disponible, on écrit un adaptateur HttpClient dans `src/<Module>/Infrastructure/Http/` et on change l'alias dans `config/services.yaml`, sans toucher au domaine ni aux vues.

Les conventions de code viennent du `CLAUDE.md` global de l'auteur. Le `CLAUDE.md` de ce dépôt note les choix propres au projet, les versions épinglées et les pièges connus.

## Recettes

### Ajouter une page

1. Créer le contrôleur dans `src/<Module>/UI/` (ou `src/Site/UI/` pour une page sans métier), avec sa route en attribut. Il ne fait qu'appeler le domaine et rendre une vue.
2. Créer la vue dans le dossier de templates de la page, `templates/<page>/index.html.twig` (comme `templates/contact/` ou `templates/legal/`), qui commence par `{% set seo = seo_page({title: '…', description: '…'}) %}`.
3. Ajouter la route à `config/packages/site.yaml` (navigation, sitemap, plan du site, fil d'Ariane) et à `tests/e2e/routes.ts` (tests de réponse, SEO, débordement et accessibilité), puis à `.lighthouserc.json`.
4. Écrire `tests/e2e/<page>.e2e.ts` et un test fonctionnel dans `tests/Functional/`.

### Charger des données à la demande avec un Turbo Frame

Le pattern est dans le domaine Tasks :

- `src/Task/Domain/TaskRepository.php` est le port, `src/Task/Infrastructure/InMemory/InMemoryTaskRepository.php` l'adaptateur.
- `TasksController::index()` rend la page avec un `<turbo-frame loading="lazy">` qui contient le squelette de chargement.
- `TasksController::list()` rend le contenu du frame : liste, état vide, ou erreur métier avec un bouton « Réessayer » (simple formulaire GET, sans JavaScript dédié).

### Ajouter un champ de formulaire

1. Ajouter la propriété et ses contraintes, avec leur message en français, dans `src/Contact/UI/Form/ContactFormData.php`.
2. L'ajouter dans `ContactType.php`, puis dans `templates/contact/_form.html.twig` avec son `data-testid`. Symfony relie l'erreur au champ par `aria-describedby` et pose `aria-invalid`.

### Ajouter une icône

Ajouter son nom Lucide à `icons.json`, lancer `make icons`, puis `{{ include('shared/ui/_icon.html.twig', {name: '<nom>'}) }}`. Le sprite `public/icons.svg` et `src/Shared/UI/IconNames.php` sont régénérés et versionnés, et `make icons-check` vérifie en QA qu'ils restent à jour.

## Tests

- **Unitaires et fonctionnels** : `tests/Unit` (domaine et fonctions pures, sans conteneur), `tests/Functional` (WebTestCase, avec des dépôts remplacés pour les états vide et erreur), `tests/js` (mode renforcé).
- **End-to-end** : Playwright sur l'image de production servie sur le port 8095. Les éléments sont ciblés par `data-testid`, préfixé par le domaine (`catalog-product`, `contact-submit`). Chaque rendu conditionnel est testé présent et absent. Les tests marqués `@dev` (page 500, charte graphique) tournent ensuite sur le serveur de développement.
- **Accessibilité** : `tests/a11y/` passe axe-core sur chaque route, en mode standard et renforcé, desktop et mobile, menus ouverts compris. Axe ne couvre qu'une partie du RGAA, un audit manuel reste nécessaire.
- **Captures** : `tests/e2e/layout.e2e.ts` enregistre l'accueil et le catalogue dans les deux modes, à 375, 768 et 1280 px, dans `screenshots/`.

## Intégration continue

`.github/workflows/ci.yml` lance `make qa` sur PHP installé par `setup-php`, puis les tests e2e, accessibilité et Lighthouse dans Docker, à chaque push sur `main` et sur chaque pull request. En cas d'échec, le rapport Playwright est joint au run.

## Licence

MIT

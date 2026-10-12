# CLAUDE.md, symfony-twig-stimulus-boilerplate

Boilerplate personnel. Les conventions générales sont dans `~/.claude/CLAUDE.md` : ce fichier ne note que les choix propres au projet.

## Choix du projet

- **Type** : application Symfony rendue côté serveur. Twig produit tout le HTML, Stimulus et Turbo ne font qu'enrichir. Chaque page fonctionne sans JavaScript : le carrousel défile nativement et ses boutons sont masqués, les filtres du catalogue et le contact sont des formulaires classiques, la page Tâches remplace son squelette par un lien vers `/taches/liste`, qui rend alors la page complète (noindex, canonique vers `/taches`) au lieu du seul contenu du frame.
- **Architecture** : découpage par module puis par couche (`src/<Module>/<Couche>/`), selon l'architecture DDD commune aux boilerplates (D1 à D12). Modules métier : `Catalog` (forme complète), `Blog`, `Contact`, `Task` (forme réduite, sans couche Application : l'UI appelle directement le port du domaine). `Site` porte les pages sans métier (accueil, légal, SEO, charte) et compose les modules. `Shared` est le socle sans métier (SEO, fil d'Ariane). Les couches portent leur nom DDD : `Domain` (sans framework), `Application`, `Infrastructure`, `UI` (contrôleurs minces, la couche de routage, et leurs formulaires et extensions Twig). Côté templates, `templates/shared/` remplace `core/` et `features/`.
- **Chemin de catégorie** : `App\Catalog\Domain\CategoryPath` est le seul point qui transforme le paramètre d'URL `plantes-interieur/feuillages` en catégorie (`resolve()`, `null` si un slug est inconnu ou si la profondeur dépasse 3). Le contrôleur en fait un 404, les templates en tirent le fil d'Ariane (`steps`), les liens enfants (`child`), le menu mobile et le sitemap (`catalog_paths()`).
- **Données** : aucune base. Les dépôts sont en mémoire avec une latence simulée de 300 ms. Une API commune (API Platform, dépôt séparé) les remplacera par des adaptateurs HttpClient : seul l'alias dans `config/services.yaml` changera.
- **Config du site** : un seul fichier, `config/packages/site.yaml` (nom, URL, éditeur, hébergeur, audit d'accessibilité, navigation). La navigation alimente le menu, le sitemap, le plan du site et le fil d'Ariane.
- **SEO** : `App\Shared\UI\Seo\SeoPageFactory` est le seul point de construction des métadonnées. Chaque vue commence par `{% set seo = seo_page({...}) %}`, le layout ne fait qu'afficher.
- **Erreurs** : classes `XxxException` du domaine avec un message métier en français. PHP réserve le suffixe `Error` aux erreurs du moteur, d'où l'écart de nommage avec le front.
- **Icônes** : lucide via `symfony/ux-icons`, uniquement dans `templates/shared/ui/_icon.html.twig` (paramètre `icon_label` pour une icône porteuse de sens). Les SVG sont versionnés dans `assets/icons/lucide/`.
- **CSS** : maison, tokens en variables CSS dans `assets/styles/app.css`. Le mode renforcé ne fait que surcharger des tokens sous `:root[data-a11y-mode='enhanced']`. Les espacements WCAG 1.4.12 ne s'appliquent qu'au texte de contenu de `<main>`.
- **Unité de police** : `px`. Police Inter variable (fontsource 5.3.0) servie localement depuis `assets/fonts/`, licence OFL jointe.
- **Cibles tactiles** : 44 px minimum (`--touch-target`).
- **Langue** : français uniquement, URL en français.
- **Hook git** : aucun. Toutes les commandes passent par Docker, un hook de pré-commit imposerait la stack démarrée à chaque commit ou serait contourné. La CI fait foi.

## Versions retenues, et pourquoi

| Brique                          | Version          | Raison                                                                                     |
| ------------------------------- | ---------------- | ------------------------------------------------------------------------------------------ |
| Symfony                         | `7.4.*` (LTS)    | 8.1 est la dernière stable mais maintenue jusqu'en janvier 2027 seulement. 7.4 LTS l'est jusqu'en novembre 2028 (sécurité 2029) : un boilerplate de démarrage doit tenir plusieurs années sans montée de version forcée |
| PHP                             | `>=8.4`          | image FrankenPHP 1.x en PHP 8.4, `array_find()` utilisé dans le domaine                     |
| FrankenPHP                      | `1-php8.4-bookworm` | serveur web et PHP dans un seul conteneur, image officielle mise en avant par Symfony     |
| PHPStan                         | niveau `max`     | le code est neuf et petit, autant partir du niveau le plus strict                           |
| PHPUnit                         | `^12.5`          | version courante, attributs PHP pour les data providers                                     |
| Embla Carousel                  | `8.6.0`          | imposé par la spec commune, cœur vanilla dans l'importmap                                    |
| embla-carousel-wheel-gestures   | `8.1.0`          | la molette et le glissé au trackpad ne sont pas dans le cœur d'Embla                        |
| symfony/translation             | `7.4.*`          | messages intégrés de Symfony (CSRF, validation) en français                                |
| symfony/http-client             | `7.4.*`, dev     | requis par `ux:icons:import`, absent en production                                          |
| ext-intl                        | `*`              | `NumberFormatter` pour les prix au format français                                          |
| deptrac/deptrac                 | `^4.7`, dev      | vérifie les règles de dépendance entre modules et couches (`deptrac.yaml`). Le `grep` précédent ne voyait ni un nom pleinement qualifié dans le code, ni une dépendance `Domain` vers `Infrastructure`, ni un module qui en importe un autre. Même version que l'API Platform des boilerplates |

## Ports et projet Docker

- Nom de projet Compose explicite (`name:` dans `compose.yaml`) pour ne jamais toucher aux autres stacks de la machine.
- Port 8095 pour le dev et Playwright (`HTTP_PORT`), lié à `127.0.0.1`. Lighthouse lance un second projet Compose sur le port 3230.
- Aucune base, donc aucun port PostgreSQL.

## Règles d'architecture et de lint propres au projet

Vérifiées par `make arch`, lancé par `make qa` et donc en CI :

- **deptrac** (`deptrac.yaml`, règle R12) : `Domain` ne dépend que de lui-même (ni framework, ni autre couche, ni autre module), y compris par un nom pleinement qualifié. `Application` dépend du `Domain` de son module. `Infrastructure` du `Domain` et de l'`Application` de son module et des frameworks. `UI` du `Domain` et de l'`Application` de son module, de `Shared` et des frameworks, et de l'`Infrastructure` seulement pour `Contact` et `Task`, qui n'ont pas de couche Application. `Site` peut dépendre du `Domain` et de l'`Application` des modules. `Shared` ne dépend que des frameworks. Les classes natives de PHP (`\DateTimeImmutable`, `\NumberFormatter`, exceptions) ne sont dans aucune couche et restent permises partout : elles ne sont pas un framework.
- **Wrappers** (règle R10, `grep`) : `ux_icon()` n'est appelé que dans `templates/shared/ui/_icon.html.twig`, Embla n'est importé que dans `assets/controllers/carousel_controller.js`.
- **Auto-test** : `make arch-selftest` (`tools/arch-selftest.sh`) écrit 7 violations volontaires à leur emplacement réel (Domain vers Infrastructure, Domain vers Symfony par nom qualifié, module vers module, Shared vers module, UI du catalogue vers son Infrastructure, `ux_icon()` hors du partial, Embla hors de son contrôleur), exige que chacune soit citée, puis retire les fichiers. Une règle qu'aucun test ne viole peut être débranchée sans bruit.

Écarts assumés par rapport à l'arbre cible de l'architecture commune :

- `AccessibilityExtension` (fonction Twig `accessibility_compliance()`) vit dans `Site/Legal/`, pas dans `Shared/UI/Twig/` : elle lit la règle de conformité de `Site/Legal`, et `Shared` ne dépend ni des modules ni de `Site`.
- Les templates ne sont pas couverts par deptrac : les fonctions Twig sont globales, et l'en-tête partagé (`templates/shared/layout/_header.html.twig`) appelle `catalog_tree()` du module catalogue. C'est la composition faite par le layout du site, l'équivalent du menu passé en propriétés par la couche de routage des fronts JavaScript.
- Pas de règle de couches pour les contrôleurs Stimulus : l'architecture ne découpe pas `assets/controllers/` en modules, seule R10 (une bibliothèque tierce dans son seul wrapper) s'y applique.

PHP-CS-Fixer : `@Symfony` plus `declare_strict_types` (règle risquée, activée pour garantir `declare(strict_types=1)` partout).

## Pièges connus, réellement rencontrés

- **Port 8080 occupé** sur la machine de l'auteur (un serveur uvicorn), d'où 8095.
- **Clés répétées dans l'URL** : PHP écrase `?taille=S&taille=M` en ne gardant que la dernière valeur. `CatalogUrlState` lit la query string brute avec `HeaderUtils::parseQuery($query, true)`. Toute URL non canonique (ordre, valeurs vides ou invalides, défauts) est redirigée vers sa forme canonique.
- **Variable d'environnement réelle contre `.env.test`** : Compose injecte `SITE_URL`, qui gagne sur `.env.test`. `phpunit.dist.xml` la force avec `force="true"`.
- **WebTestCase redémarre le noyau entre deux requêtes** : un service remplacé par `getContainer()->set()` disparaît au second appel. `$client->disableReboot()` quand un test enchaîne GET puis POST.
- **Page 404 personnalisée invisible en test** : l'environnement `test` est en debug et affiche la page d'exception. Créer le client avec `['debug' => false]`.
- **`include` Twig transmet le contexte** : une variable `label` du template appelant ressortait comme `aria-label` de l'icône. Le paramètre s'appelle `icon_label`.
- **Spécificité de `.button`** : une règle `display: none` sur une classe déclarée avant `.button` est écrasée. Les bascules d'affichage préfixent par `.header` ou `.js`.
- **Glisser au carrousel en e2e** : la souris de Playwright n'agit que dans le viewport, faire `scrollIntoViewIfNeeded()` avant de mesurer la piste. Les images portent `-webkit-user-drag: none` pour ne pas déclencher le glisser natif.
- **Pages qui n'existent qu'en dev** : `/_error/500` et `/charte-graphique` sont déclarées sous `when@dev` et `when@test`. Les e2e tournent sur l'image de production, puis `make test-e2e` relance le serveur de dev pour les tests marqués `@dev`. En fin de commande, c'est donc le serveur de dev qui tourne. Chaque cible démarre elle-même l'environnement qu'elle attend, dans sa recette et non en prérequis : dans une seule invocation (`make test-e2e test-a11y`), make ne rejoue pas un prérequis déjà satisfait, et `test-a11y` tombait sur le serveur de dev, où la 404 est la page d'exception de debug.
- **Cache de test sans debug** : le test de la 404 crée un noyau `['debug' => false]`, dont le conteneur compilé n'est jamais revalidé. Après un renommage de classes, il pointait encore vers les anciennes. `make test-unit` vide `var/cache/test` avant PHPUnit.
- **PHPStan et Symfony** : `typecheck` réchauffe le cache dev avant l'analyse, sinon `containerXmlPath` n'existe pas.
- **Charge machine** : avec plusieurs suites en parallèle sur le poste, Playwright dépassait ses délais. Il tourne sur 4 workers en local, 2 en CI.
- **Performance Lighthouse** : l'accueil et le catalogue tournent autour de 0,90 à 0,93. Lighthouse simule un réseau lent où tous les téléchargements simultanés se partagent la bande passante, et le serveur local est en HTTP/1.1 (6 connexions). Causes mesurées : les diapositives du carrousel hors écran se chargeaient malgré `loading="lazy"` (seuil de Chrome), d'où `content-visibility: auto` sur les diapositives (deux images de moins sur mobile). La première image produit de `feuillages` est l'illustration monstera, qui pèse 159 Ko en 800w, c'est le plancher actuel de cette page. Les mesures en place : préchargement de l'image LCP en tête du `<head>`, `fetchpriority="high"` sur la première image produit, pas de lazy sur les quatre premières, `sizes` alignés sur la mise en page réelle, contrôleurs du carrousel et du catalogue chargés à la demande. Lighthouse fait 3 runs et l'assertion porte sur la médiane, avec un seuil de 0,90 bloquant. La latence simulée des dépôts n'y est pour rien : mesurée à 0 ms, le score de l'accueil ne bouge pas.
- **`catalog_tree()` dans l'en-tête** : il est appelé sur chaque page rendue, pages d'erreur 404 et 500 comprises. Tant que le dépôt est en mémoire, c'est gratuit. Une fois l'API branchée, ce sera un appel HTTP par page : le mettre en cache (cache pool Symfony avec une durée courte) au moment du branchement.
- **`APP_SECRET` obligatoire** : `compose.yaml` l'exige (`${APP_SECRET:?}`), sans défaut connu. Compose interpole `compose.yaml` avant de fusionner l'override, donc un défaut dans `compose.override.yaml` ne suffit pas. Le Makefile génère un secret aléatoire à chaque invocation quand la variable n'est pas fournie. Sans `make`, exporter `APP_SECRET` avant `docker compose`.
- **Composer dans le conteneur** : `composer require --dev` a écrit des contraintes `*`, corrigées à la main en `^x.y`.
- **Lighthouse** : Chrome est lancé avec `--no-sandbox` (Ubuntu restreint les user namespaces). Les rapports restent en local dans `.lighthouseci/`.

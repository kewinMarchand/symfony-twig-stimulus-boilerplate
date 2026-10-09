# CLAUDE.md, symfony-twig-stimulus-boilerplate

Boilerplate personnel. Les conventions générales sont dans `~/.claude/CLAUDE.md` : ce fichier ne note que les choix propres au projet.

## Choix du projet

- **Type** : application Symfony rendue côté serveur. Twig produit tout le HTML, Stimulus et Turbo ne font qu'enrichir. Chaque page fonctionne sans JavaScript : le carrousel défile nativement et ses boutons sont masqués, les filtres du catalogue et le contact sont des formulaires classiques, la page Tâches remplace son squelette par un lien vers `/taches/liste`, qui rend alors la page complète (noindex, canonique vers `/taches`) au lieu du seul contenu du frame.
- **Architecture** : hexagonale légère. `src/Domain/<Domaine>/` (Model, Repository, Service, Exception) sans aucune dépendance au framework, `src/Infrastructure/` pour les adaptateurs, `src/UI/Http/Controller/` pour des contrôleurs minces (la couche de routage). Pas de couche Application : les contrôleurs appellent directement les ports du domaine, une couche de cas d'usage s'ajoutera quand un cas le justifiera.
- **Données** : aucune base. Les dépôts sont en mémoire avec une latence simulée de 300 ms. Une API commune (API Platform, dépôt séparé) les remplacera par des adaptateurs HttpClient : seul l'alias dans `config/services.yaml` changera.
- **Config du site** : un seul fichier, `config/packages/site.yaml` (nom, URL, éditeur, hébergeur, audit d'accessibilité, navigation). La navigation alimente le menu, le sitemap, le plan du site et le fil d'Ariane.
- **SEO** : `App\UI\Seo\SeoPageFactory` est le seul point de construction des métadonnées. Chaque vue commence par `{% set seo = seo_page({...}) %}`, le layout ne fait qu'afficher.
- **Erreurs** : classes `XxxException` du domaine avec un message métier en français. PHP réserve le suffixe `Error` aux erreurs du moteur, d'où l'écart de nommage avec le front.
- **Icônes** : lucide via `symfony/ux-icons`, uniquement dans `templates/core/ui/_icon.html.twig` (paramètre `icon_label` pour une icône porteuse de sens). Les SVG sont versionnés dans `assets/icons/lucide/`.
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

## Ports et projet Docker

- Nom de projet Compose explicite (`name:` dans `compose.yaml`) pour ne jamais toucher aux autres stacks de la machine.
- Port 8095 pour le dev et Playwright (`HTTP_PORT`), lié à `127.0.0.1`. Lighthouse lance un second projet Compose sur le port 3230.
- Aucune base, donc aucun port PostgreSQL.

## Règles de lint propres au projet

Vérifiées par `make lint` (grep, sans dépendance) :

- `src/Domain` n'importe ni `Symfony`, ni `Doctrine`, ni `Psr`, ni `Twig`.
- `ux_icon()` n'est appelé que dans `templates/core/ui/_icon.html.twig`.

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
- **PHPStan et Symfony** : `typecheck` réchauffe le cache dev avant l'analyse, sinon `containerXmlPath` n'existe pas.
- **Charge machine** : avec plusieurs suites en parallèle sur le poste, Playwright dépassait ses délais. Il tourne sur 4 workers en local, 2 en CI.
- **Performance Lighthouse** : l'accueil et le catalogue plafonnent vers 0,90 à cause de la latence simulée des dépôts (300 ms de TTFB). Les contrôleurs du carrousel et du catalogue sont chargés à la demande (`stimulusFetch: 'lazy'`) et la première image produit porte `fetchpriority="high"` pour libérer la bande passante de l'image LCP.
- **`catalog_tree()` dans l'en-tête** : il est appelé sur chaque page rendue, pages d'erreur 404 et 500 comprises. Tant que le dépôt est en mémoire, c'est gratuit. Une fois l'API branchée, ce sera un appel HTTP par page : le mettre en cache (cache pool Symfony avec une durée courte) au moment du branchement.
- **`APP_SECRET` obligatoire** : `compose.yaml` l'exige (`${APP_SECRET:?}`), sans défaut connu. Compose interpole `compose.yaml` avant de fusionner l'override, donc un défaut dans `compose.override.yaml` ne suffit pas. Le Makefile génère un secret aléatoire à chaque invocation quand la variable n'est pas fournie. Sans `make`, exporter `APP_SECRET` avant `docker compose`.
- **Composer dans le conteneur** : `composer require --dev` a écrit des contraintes `*`, corrigées à la main en `^x.y`.
- **Lighthouse** : Chrome est lancé avec `--no-sandbox` (Ubuntu restreint les user namespaces). Les rapports restent en local dans `.lighthouseci/`.

LHCI_PROJECT := symfony-twig-stimulus-boilerplate-lhci
LHCI_PORT := 3230
# Valeurs factices de la phase @observabilite : un faux GlitchTip local et un traceur Matomo intercepté
# (tests/e2e/observability.e2e.ts), jamais une instance réelle.
OBSERVABILITY_ENV := GLITCHTIP_DSN=http://clefactice@localhost:3231/1 MATOMO_URL=http://matomo.test/ MATOMO_SITE_ID=1

qa: lint arch arch-selftest icons-check format-check typecheck test-unit ## QA rapide : lint, architecture, sprite d'icônes, format, analyse statique, tests unitaires

qa-full: qa test-e2e test-a11y metrics ## QA complète : QA rapide + e2e + a11y + Lighthouse

lint: ## Composer, conteneur, Twig et YAML
	$(EXEC) sh -c "composer validate --strict && bin/console lint:container && bin/console lint:twig templates && bin/console lint:yaml config --parse-tags"

arch: arch-deptrac arch-wrappers ## Règles d'architecture : deptrac et bibliothèques tierces confinées à leur wrapper

arch-deptrac: ## Règles de dépendance entre modules et couches (deptrac.yaml)
	$(EXEC) vendor/bin/deptrac analyse --no-progress

arch-wrappers: ## Le sprite d'icônes et Embla ne sont utilisés que dans leur wrapper
	@status=0; \
	if grep -rn "icons\.svg" templates assets --exclude-dir=vendor | grep -v "^templates/shared/ui/_icon.html.twig:"; then \
		echo "Le sprite icons.svg ne s'utilise que dans templates/shared/ui/_icon.html.twig"; status=1; fi; \
	if grep -rnE "from '(embla-carousel|wheel-gestures)" assets --exclude-dir=vendor | grep -v "^assets/controllers/carousel_controller.js:"; then \
		echo "Embla ne s'importe que dans assets/controllers/carousel_controller.js"; status=1; fi; \
	exit $$status

icons-check: ## Échoue si public/icons.svg ou src/Shared/UI/IconNames.php ne correspondent plus à icons.json
	$(NODE) tools/icons.js --check

arch-selftest: ## Écrit des violations volontaires, exige que make arch les détecte toutes, puis les retire
	@sh tools/arch-selftest.sh

format: ## PHP-CS-Fixer (écriture)
	$(EXEC) vendor/bin/php-cs-fixer fix

format-check: ## PHP-CS-Fixer (vérification)
	$(EXEC) vendor/bin/php-cs-fixer fix --dry-run --diff

typecheck: ## Analyse statique PHPStan (niveau max)
	$(EXEC) sh -c "bin/console cache:warmup --env=dev -q && vendor/bin/phpstan analyse --no-progress --memory-limit=1G"

test: test-unit test-e2e test-a11y ## Tous les tests

test-unit: ## Tests unitaires et fonctionnels (PHPUnit) et test du mode accessibilité (node:test)
	$(EXEC) sh -c "rm -rf var/cache/test && vendor/bin/phpunit"
	$(NODE) --test 'tests/js/**/*.test.js'

test-e2e: ## Tests end-to-end desktop et mobile : image de production, puis avec traceurs factices, puis serveur de dev (Playwright)
	$(UP_PROD)
	$(RUN_NODE) yarn playwright test tests/e2e --grep-invert "@dev|@observabilite"
	$(OBSERVABILITY_ENV) $(UP_PROD)
	$(RUN_NODE) yarn playwright test tests/e2e --grep @observabilite
	$(UP_DEV)
	$(RUN_NODE) yarn playwright test tests/e2e --grep @dev

test-a11y: ## Tests d'accessibilité WCAG 2.1 AA, deux modes, prod puis dev (axe-core + Playwright)
	$(UP_PROD)
	$(RUN_NODE) yarn playwright test tests/a11y --grep-invert @dev
	$(UP_DEV)
	$(RUN_NODE) yarn playwright test tests/a11y --grep @dev

metrics: docker-build ## Métriques Lighthouse CI sur l'image de production (perf ≥ 90, a11y et SEO = 100)
	HTTP_PORT=$(LHCI_PORT) $(COMPOSE_PROD) -p $(LHCI_PROJECT) up -d --wait
	$(RUN_NODE) sh -c 'CHROME_PATH=$$(node -e "console.log(require(\"@playwright/test\").chromium.executablePath())") yarn lhci autorun'; \
		status=$$?; HTTP_PORT=$(LHCI_PORT) $(COMPOSE_PROD) -p $(LHCI_PROJECT) down; exit $$status

.PHONY: qa qa-full lint arch arch-deptrac arch-wrappers arch-selftest icons-check format format-check typecheck test test-unit test-e2e test-a11y metrics

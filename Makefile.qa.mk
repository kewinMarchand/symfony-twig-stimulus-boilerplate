LHCI_PROJECT := symfony-twig-stimulus-boilerplate-lhci
LHCI_PORT := 3230

qa: lint arch arch-selftest format-check typecheck test-unit ## QA rapide : lint, architecture, format, analyse statique, tests unitaires

qa-full: qa test-e2e test-a11y metrics ## QA complète : QA rapide + e2e + a11y + Lighthouse

lint: ## Composer, conteneur, Twig et YAML
	$(EXEC) sh -c "composer validate --strict && bin/console lint:container && bin/console lint:twig templates && bin/console lint:yaml config --parse-tags"

arch: arch-deptrac arch-wrappers ## Règles d'architecture : deptrac et bibliothèques tierces confinées à leur wrapper

arch-deptrac: ## Règles de dépendance entre modules et couches (deptrac.yaml)
	$(EXEC) vendor/bin/deptrac analyse --no-progress

arch-wrappers: ## ux_icon() et Embla ne sont appelés que dans leur wrapper
	@status=0; \
	if grep -rn "ux_icon(" templates | grep -v "^templates/shared/ui/_icon.html.twig:"; then \
		echo "ux_icon() ne s'appelle que dans templates/shared/ui/_icon.html.twig"; status=1; fi; \
	if grep -rnE "from '(embla-carousel|wheel-gestures)" assets --exclude-dir=vendor | grep -v "^assets/controllers/carousel_controller.js:"; then \
		echo "Embla ne s'importe que dans assets/controllers/carousel_controller.js"; status=1; fi; \
	exit $$status

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
	node --test 'tests/js/**/*.test.js'

test-e2e: ## Tests end-to-end desktop et mobile, image de production puis serveur de dev (Playwright)
	$(UP_PROD)
	yarn playwright test tests/e2e --grep-invert @dev
	$(UP_DEV)
	yarn playwright test tests/e2e --grep @dev

test-a11y: ## Tests d'accessibilité WCAG 2.1 AA, deux modes, prod puis dev (axe-core + Playwright)
	$(UP_PROD)
	yarn playwright test tests/a11y --grep-invert @dev
	$(UP_DEV)
	yarn playwright test tests/a11y --grep @dev

metrics: docker-build ## Métriques Lighthouse CI sur l'image de production (perf ≥ 90, a11y et SEO = 100)
	HTTP_PORT=$(LHCI_PORT) $(COMPOSE_PROD) -p $(LHCI_PROJECT) up -d --wait
	CHROME_PATH=$$(node -e 'console.log(require("@playwright/test").chromium.executablePath())') yarn lhci autorun; \
		status=$$?; HTTP_PORT=$(LHCI_PORT) $(COMPOSE_PROD) -p $(LHCI_PROJECT) down; exit $$status

.PHONY: qa qa-full lint arch arch-deptrac arch-wrappers arch-selftest format format-check typecheck test test-unit test-e2e test-a11y metrics

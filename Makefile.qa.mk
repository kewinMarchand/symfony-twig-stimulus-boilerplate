LHCI_PROJECT := symfony-twig-stimulus-boilerplate-lhci
LHCI_PORT := 3230

qa: lint format-check typecheck test-unit ## QA rapide : lint, format, analyse statique, tests unitaires

qa-full: qa test-e2e test-a11y metrics ## QA complète : QA rapide + e2e + a11y + Lighthouse

lint: ## Composer, conteneur, Twig, YAML et règles d'architecture
	$(EXEC) sh -c "composer validate --strict && bin/console lint:container && bin/console lint:twig templates && bin/console lint:yaml config --parse-tags"
	@if grep -rn "ux_icon(" templates | grep -v "^templates/core/ui/_icon.html.twig:"; then \
		echo "ux_icon() ne s'appelle que dans templates/core/ui/_icon.html.twig"; exit 1; fi
	@if grep -rnE "^use (Symfony|Doctrine|Psr|Twig)\\\\" src/Domain; then \
		echo "src/Domain ne dépend d'aucun framework"; exit 1; fi

format: ## PHP-CS-Fixer (écriture)
	$(EXEC) vendor/bin/php-cs-fixer fix

format-check: ## PHP-CS-Fixer (vérification)
	$(EXEC) vendor/bin/php-cs-fixer fix --dry-run --diff

typecheck: ## Analyse statique PHPStan (niveau max)
	$(EXEC) sh -c "bin/console cache:warmup --env=dev -q && vendor/bin/phpstan analyse --no-progress --memory-limit=1G"

test: test-unit test-e2e test-a11y ## Tous les tests

test-unit: ## Tests unitaires et fonctionnels (PHPUnit) et test du mode accessibilité (node:test)
	$(EXEC) vendor/bin/phpunit
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

.PHONY: qa qa-full lint format format-check typecheck test test-unit test-e2e test-a11y metrics

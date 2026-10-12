.DEFAULT_GOAL := help

COMPOSE := docker compose
COMPOSE_PROD := docker compose -f compose.yaml
UP_PROD := $(COMPOSE_PROD) up -d --build --wait
UP_DEV := $(COMPOSE) up -d --wait
EXEC ?= $(COMPOSE) run --rm php
NODE ?= docker run --rm -u $(HOST_UID):$(HOST_GID) -v $(CURDIR):/app -w /app node:24-alpine node

export HOST_UID := $(shell id -u)
export HOST_GID := $(shell id -g)
export HTTP_PORT ?= 8095

ifndef APP_SECRET
APP_SECRET := $(shell od -An -N16 -tx1 /dev/urandom | tr -d ' \n')
endif
export APP_SECRET

include Makefile.qa.mk

help: ## Affiche cette aide
	@grep -hE '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2}'

install: ## Construit l'image de dev, installe les dépendances PHP et Node et le navigateur des tests e2e
	$(COMPOSE) build
	$(EXEC) composer install
	yarn install --frozen-lockfile
	npx playwright install chromium

up: ## Lance le serveur de développement (http://localhost:8095)
	$(UP_DEV)
	@echo "http://localhost:$(HTTP_PORT)"

down: ## Arrête les conteneurs
	$(COMPOSE) down --remove-orphans

icons: ## Génère public/icons.svg et src/Shared/UI/IconNames.php depuis icons.json (noms Lucide)
	$(NODE) tools/icons.js

docker-build: ## Construit l'image Docker de production
	$(COMPOSE_PROD) build

docker-up: ## Lance l'image de production à la place du serveur de dev (port HTTP_PORT, 8095 par défaut)
	$(UP_PROD)

docker-down: ## Arrête le conteneur
	$(COMPOSE) down --remove-orphans

.PHONY: help install up down icons docker-build docker-up docker-down

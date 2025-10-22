.PHONY: help up down build restart logs shell composer test console phpstan cs-check cs-fix

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

up: ## Start containers
	docker compose up -d

down: ## Stop containers
	docker compose down

build: ## Build containers
	docker compose build --no-cache

restart: down up ## Restart containers

logs: ## View all container logs
	docker compose logs -f

logs-php: ## View PHP logs
	docker compose logs -f php

logs-nginx: ## View Nginx logs
	docker compose logs -f nginx

shell: ## Access PHP shell
	docker compose exec php bash

composer: ## Install Composer dependencies
	docker compose exec php composer install

test: ## Run tests
	docker compose exec php php bin/phpunit

console: ## Access Symfony console
	docker compose exec php php bin/console

phpstan: ## Run PHPStan static analysis
	docker compose exec php vendor/bin/phpstan analyse

cs-check: ## Check code style (dry-run)
	docker compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Fix code style
	docker compose exec php vendor/bin/php-cs-fixer fix

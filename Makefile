.PHONY: help setup up down build restart logs shell composer test console phpstan cs-check cs-fix

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

setup: ## Initial project setup (first time only)
	@echo "🚀 Setting up Siroko Cart project..."
	@echo ""
	@echo "📝 Step 1: Creating .env file..."
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "✅ .env file created. Please review and update if needed."; \
	else \
		echo "⚠️  .env already exists, skipping..."; \
	fi
	@echo ""
	@echo "🐳 Step 2: Building Docker containers..."
	docker compose build --no-cache
	@echo ""
	@echo "🐳 Step 3: Starting containers..."
	docker compose up -d
	@echo ""
	@echo "⏳ Step 4: Waiting for database to be ready..."
	@sleep 10
	@echo ""
	@echo "📦 Step 5: Installing Composer dependencies..."
	docker compose exec php composer install
	@echo ""
	@echo "🗄️  Step 6: Running database migrations..."
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction || echo "⚠️  No migrations found yet"
	@echo ""
	@echo "🔧 Step 7: Installing Git hooks..."
	./.githooks/install.sh
	@echo ""
	@echo "✅ Setup complete!"
	@echo ""
	@echo "📚 Useful commands:"
	@echo "  make up        - Start containers"
	@echo "  make down      - Stop containers"
	@echo "  make shell     - Access PHP container"
	@echo "  make test      - Run tests"
	@echo "  make phpstan   - Run static analysis"
	@echo "  make cs-fix    - Fix code style"
	@echo ""
	@echo "🌐 Access points:"
	@echo "  Application: http://localhost:8082"
	@echo "  phpMyAdmin:  http://localhost:8081"

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

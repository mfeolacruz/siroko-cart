.PHONY: help setup up down build restart logs shell composer test test-unit test-integration test-functional test-coverage console phpstan cs-check cs-fix db-create db-migrate db-test-migrate db-reset

# Load environment variables from .env file
ifneq (,$(wildcard ./.env))
    include .env
    export
endif

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
	@if [ ! -f .env.test ]; then \
		echo "APP_ENV=test" > .env.test; \
		echo "APP_SECRET=$${APP_SECRET}" >> .env.test; \
		echo "DATABASE_URL=\"mysql://$${MYSQL_USER}:$${MYSQL_PASSWORD}@$${MYSQL_HOST}:$${MYSQL_PORT}/$${MYSQL_TEST_DATABASE}?serverVersion=8.0.32&charset=utf8mb4\"" >> .env.test; \
		echo "✅ .env.test file created"; \
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
	@echo "🗄️  Step 6: Creating databases..."
	$(MAKE) db-create
	@echo ""
	@echo "🗄️  Step 7: Running database migrations..."
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction || echo "⚠️  No migrations found yet"
	@echo ""
	@echo "🔧 Step 8: Installing Git hooks..."
	./.githooks/install.sh
	@echo ""
	@echo "✅ Setup complete!"
	@echo ""
	@echo "📚 Useful commands:"
	@echo "  make up               - Start containers"
	@echo "  make down             - Stop containers"
	@echo "  make test             - Run all tests"
	@echo "  make test-unit        - Run unit tests"
	@echo "  make test-integration - Run integration tests"
	@echo "  make test-functional  - Run functional tests"
	@echo "  make db-reset         - Reset test database"
	@echo ""
	@echo "🌐 Access points:"
	@echo "  Application: http://localhost:$${NGINX_PORT}"
	@echo "  phpMyAdmin:  http://localhost:$${PHPMYADMIN_PORT}"

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

test: ## Run all tests
	docker compose exec php php bin/phpunit

test-unit: ## Run unit tests only
	docker compose exec php php bin/phpunit --testsuite=Unit

test-integration: ## Run integration tests only
	docker compose exec php php bin/phpunit --testsuite=Integration

test-functional: ## Run functional tests only
	docker compose exec php php bin/phpunit --testsuite=Functional

test-coverage: ## Run tests with coverage report
	docker compose exec php php bin/phpunit --coverage-html coverage

console: ## Access Symfony console
	docker compose exec php php bin/console

phpstan: ## Run PHPStan static analysis
	docker compose exec php vendor/bin/phpstan analyse

cs-check: ## Check code style (dry-run)
	docker compose exec php vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Fix code style
	docker compose exec php vendor/bin/php-cs-fixer fix

db-create: ## Create databases
	@docker compose exec database mysql -u root -p$${MYSQL_ROOT_PASSWORD} -e "CREATE DATABASE IF NOT EXISTS $${MYSQL_DATABASE};"
	@docker compose exec database mysql -u root -p$${MYSQL_ROOT_PASSWORD} -e "CREATE DATABASE IF NOT EXISTS $${MYSQL_TEST_DATABASE};"
	@docker compose exec database mysql -u root -p$${MYSQL_ROOT_PASSWORD} -e "GRANT ALL PRIVILEGES ON $${MYSQL_DATABASE}.* TO '$${MYSQL_USER}'@'%';"
	@docker compose exec database mysql -u root -p$${MYSQL_ROOT_PASSWORD} -e "GRANT ALL PRIVILEGES ON $${MYSQL_TEST_DATABASE}.* TO '$${MYSQL_USER}'@'%';"
	@echo "✅ Databases created"

db-migrate: ## Run migrations on main database
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

db-test-migrate: ## Run migrations on test database
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction --env=test

db-reset: ## Reset test database
	docker compose exec php php bin/console doctrine:database:drop --force --env=test || true
	docker compose exec php php bin/console doctrine:database:create --env=test
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction --env=test

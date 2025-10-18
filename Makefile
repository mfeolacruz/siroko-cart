# Siroko Cart - Makefile
.PHONY: help up down build restart logs test clean install quality phpstan benchmark cs-fix cs-check

# Default target
help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Docker commands
up: ## Start all services
	docker-compose up -d

down: ## Stop all services
	docker-compose down

build: ## Build Docker images
	docker-compose build --no-cache

restart: ## Restart all services
	docker-compose restart

logs: ## Show container logs
	docker-compose logs -f

# Application commands
install: ## Install Composer dependencies
	docker-compose exec app composer install

test: ## Run PHPUnit tests
	docker-compose exec app php bin/phpunit

test-coverage: ## Run tests with coverage report
	docker-compose exec app php bin/phpunit --coverage-html var/coverage

# Code quality commands
quality: phpstan cs-check ## Run all quality checks

phpstan: ## Run PHPStan static analysis
	docker-compose exec app vendor/bin/phpstan analyse src --level=8

benchmark: ## Run Apache Benchmark performance tests
	@echo "Running performance benchmark on main endpoints..."
	ab -n 1000 -c 10 http://localhost:${NGINX_PORT:-8080}/api/health || echo "Health endpoint not ready"
	ab -n 100 -c 5 http://localhost:${NGINX_PORT:-8080}/api/carts/test-cart || echo "Cart endpoint not ready"

cs-check: ## Check code style with PHP CS Fixer
	docker-compose exec app vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Fix code style with PHP CS Fixer
	docker-compose exec app vendor/bin/php-cs-fixer fix

# Utility commands
shell: ## Access application container shell
	docker-compose exec app bash

mysql: ## Access MySQL shell
	docker-compose exec mysql mysql -u $(MYSQL_USER) -p$(MYSQL_PASSWORD) $(MYSQL_DATABASE)

redis: ## Access Redis CLI
	docker-compose exec redis redis-cli

clean: ## Clean up containers and volumes
	docker-compose down -v
	docker system prune -f

# Development commands
migration: ## Create new Doctrine migration
	docker-compose exec app php bin/console doctrine:migrations:generate

migrate: ## Run Doctrine migrations
	docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction

schema-validate: ## Validate Doctrine schema
	docker-compose exec app php bin/console doctrine:schema:validate
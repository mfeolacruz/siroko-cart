# Siroko Cart API

Shopping cart and checkout API for Siroko e-commerce platform.

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- Make (optional, but recommended)

### Installation

1. Clone the repository:
```bash
git clone https://github.com/mfeolacruz/siroko-cart.git
cd siroko-cart
```

2. Run the setup command:
```bash
make setup
```

This will:
- Create `.env` file from `.env.example`
- Build Docker containers
- Start all services
- Install Composer dependencies
- Run database migrations
- Install Git hooks

3. Access the application:
- **API**: http://localhost:8082
- **phpMyAdmin**: http://localhost:8081

## 📝 Available Commands
```bash
make help          # Show all available commands
make up            # Start containers
make down          # Stop containers
make restart       # Restart containers
make shell         # Access PHP container shell
make test          # Run tests
make phpstan       # Run static analysis
make cs-check      # Check code style
make cs-fix        # Fix code style
make logs          # View logs
```

## 🏗️ Architecture

This project follows **Hexagonal Architecture** (Ports & Adapters) with **Domain-Driven Design** principles:
```
src/
├── Domain/          # Business logic, entities, value objects
├── Application/     # Use cases, commands, queries (CQRS)
└── Infrastructure/  # Framework, persistence, controllers
```

## 🧪 Testing
```bash
# Run all tests
make test

# Run with coverage
docker compose exec php php bin/phpunit --coverage-html coverage
```

## 🔍 Code Quality
```bash
# Static analysis (PHPStan level 9)
make phpstan

# Check code style (PSR-12)
make cs-check

# Fix code style
make cs-fix
```

## 🔧 Git Hooks

Git hooks are automatically installed during setup. They will:
- Run PHPStan before each commit
- Check code style compliance
- Validate commit message format (Conventional Commits)

## 🛠️ Technology Stack

- PHP 8.4
- Symfony 7.3
- MySQL 8.0
- Docker & Docker Compose
- PHPUnit (TDD)
- PHPStan (Level 9)
- PHP CS Fixer (PSR-12)

## 📚 Documentation

- [API Documentation](http://localhost:8082/api/doc) (Swagger UI)
- [Domain Model](docs/domain-model.md)
- [Architecture Decision Records](docs/adr/)

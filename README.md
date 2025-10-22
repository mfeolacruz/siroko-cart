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
- Create `.env` and `.env.test` files from examples
- Build Docker containers
- Start all services (PHP, Nginx, MySQL, phpMyAdmin)
- Install Composer dependencies
- Create databases
- Run database migrations
- Install Git hooks

3. Access the application:
- Check the ports configured in your `.env` file (default: `NGINX_PORT=8082`, `PHPMYADMIN_PORT=8081`)
- **API**: `http://localhost:${NGINX_PORT}`
- **phpMyAdmin**: `http://localhost:${PHPMYADMIN_PORT}`

## 📝 Available Commands

### Container Management
```bash
make help          # Show all available commands
make up            # Start containers
make down          # Stop containers
make build         # Build containers from scratch
make restart       # Restart containers
make shell         # Access PHP container shell
make logs          # View all container logs
make logs-php      # View PHP logs
make logs-nginx    # View Nginx logs
```

### Development
```bash
make composer      # Install Composer dependencies
make console       # Access Symfony console
```

### Testing
```bash
make test                # Run all tests
make test-unit           # Run unit tests only
make test-integration    # Run integration tests only
make test-functional     # Run functional tests only
make test-coverage       # Generate coverage report (output in coverage/)
```

### Code Quality
```bash
make phpstan       # Run static analysis (PHPStan level 9)
make cs-check      # Check code style (PSR-12)
make cs-fix        # Fix code style automatically
```

### Database
```bash
make db-create         # Create main and test databases
make db-migrate        # Run migrations on main database
make db-test-migrate   # Run migrations on test database
make db-reset          # Reset test database (drop, create, migrate)
```

## 🏗️ Architecture

This project follows **Hexagonal Architecture** (Ports & Adapters) with **Domain-Driven Design** principles:
```
src/
├── Domain/          # Business logic, entities, value objects, domain events
├── Application/     # Use cases, commands, queries (CQRS), DTOs
└── Infrastructure/  # Framework, persistence, HTTP controllers, repositories
    └── Symfony/     # Symfony-specific code (Kernel, config)
```

### Testing Strategy
```
tests/
├── Unit/            # Unit tests for domain logic (entities, VOs, services)
├── Integration/     # Integration tests (repositories, database)
└── Functional/      # Functional/E2E tests (API endpoints)
```

## 🧪 Testing

### Running Tests
```bash
# Run all test suites
make test

# Run specific test suite
make test-unit           # Domain logic tests
make test-integration    # Database/repository tests  
make test-functional     # API endpoint tests

# Generate HTML coverage report
make test-coverage
open coverage/index.html
```

### Test Database

Tests use a separate database configured in `.env` (default: `siroko_cart_test`) that is automatically created during setup.

To reset the test database:
```bash
make db-reset
```

## 🔍 Code Quality

This project maintains high code quality standards:

### Static Analysis (PHPStan Level 9)
```bash
make phpstan
```

### Code Style (PSR-12)
```bash
# Check code style
make cs-check

# Automatically fix code style issues
make cs-fix
```

## 🔧 Git Hooks

Git hooks are automatically installed during setup. They enforce:
- ✅ PHPStan analysis (level 9) before each commit
- ✅ Code style compliance (PSR-12)
- ✅ Conventional commit message format

### Manual Installation
```bash
./.githooks/install.sh
```

### Commit Message Format
```bash
<type>(<scope>): <subject>

# Types: feat, fix, docs, style, refactor, test, chore, perf
# Examples:
feat: add cart creation endpoint
fix(cart): resolve quantity calculation bug
test: add unit tests for cart entity
```

## 🛠️ Technology Stack

- **PHP 8.4** with PHP-FPM
- **Symfony 7.3** (API skeleton)
- **MySQL 8.0**
- **Nginx** (web server)
- **Docker & Docker Compose**
- **PHPUnit** (TDD with 3 test suites)
- **PHPStan** (level 9 static analysis)
- **PHP CS Fixer** (PSR-12 compliance)
- **Xdebug** (debugging & coverage)

## 📚 API Documentation

Once the application is running, access the interactive API documentation at the configured port (check `.env` for `NGINX_PORT`):

- **Swagger UI**: `http://localhost:${NGINX_PORT}/api/doc`

## 🗄️ Database Access

**phpMyAdmin** is available at the configured port (check `.env` for `PHPMYADMIN_PORT`):
- URL: `http://localhost:${PHPMYADMIN_PORT}`
- Server: Value of `MYSQL_HOST` in `.env` (default: `database`)
- Username: Value of `MYSQL_USER` in `.env`
- Password: Value of `MYSQL_PASSWORD` in `.env`
- Databases:
    - Value of `MYSQL_DATABASE` in `.env` (development)
    - Value of `MYSQL_TEST_DATABASE` in `.env` (testing)

## 🔐 Configuration

All configuration is managed through environment variables in `.env` file:

- **NGINX_PORT**: Nginx exposed port (default: 8082)
- **PHPMYADMIN_PORT**: phpMyAdmin exposed port (default: 8081)
- **MYSQL_DATABASE**: Main database name
- **MYSQL_TEST_DATABASE**: Test database name
- **MYSQL_USER**: Database user
- **MYSQL_PASSWORD**: Database password
- **MYSQL_HOST**: Database host (use `database` for Docker, `127.0.0.1` for local)
- **MYSQL_PORT**: Database port (default: 3306)

Copy `.env.example` to `.env` and customize as needed.

## 🤝 Contributing

1. Create a feature branch from `develop`
2. Follow conventional commits
3. Ensure all tests pass (`make test`)
4. Run code quality checks (`make phpstan` and `make cs-check`)
5. Create a Pull Request to `develop`

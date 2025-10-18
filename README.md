# 🛒 Siroko Cart Microservice

Un microservicio moderno de carrito y checkout construido con **Domain-Driven Design (DDD)** y **Arquitectura Hexagonal** usando Symfony 7.

## 🏗️ Arquitectura

Este proyecto sigue los principios de **Arquitectura Hexagonal** con clara separación de responsabilidades:

```
src/
└── Shared/
    └── Infrastructure/
        └── Symfony/
            └── Kernel.php          # Infraestructura de Symfony
└── (Futuros bounded contexts: Cart/, Order/)
```

### Principios de Diseño

- **Domain-Driven Design (DDD)**: Lógica de negocio aislada del framework
- **Arquitectura Hexagonal**: Patrón Puertos & Adaptadores
- **CQRS**: Separación de Responsabilidades entre Comandos y Consultas
- **Event-Driven**: Eventos de dominio para desacoplar
- **Bounded Contexts**: Contextos Cart, Order y Shared

## 🚀 Inicio Rápido

### Prerequisitos

- Docker & Docker Compose
- Make (para comandos simplificados)

### Configuración Inicial

```bash
# Clonar el repositorio
git clone https://github.com/mfeolacruz/siroko-cart.git
cd siroko-cart

# Configuración completa (construye contenedores, instala dependencias, configura hooks)
make setup
```

¡Eso es todo! La aplicación estará disponible en **http://localhost:8081**

### Configuración Manual (si prefieres paso a paso)

```bash
# 1. Copiar configuración de entorno
cp .env.example .env

# 2. Construir e iniciar contenedores
make build
make up

# 3. Instalar dependencias
make install

# 4. Instalar Git hooks para calidad de código
make install-hooks
```

## 🛠️ Comandos de Desarrollo

### Comandos Esenciales

| Comando | Descripción |
|---------|-------------|
| `make help` | Mostrar todos los comandos disponibles |
| `make setup` | Configuración completa del proyecto (primera vez) |
| `make up` | Iniciar todos los servicios |
| `make down` | Detener todos los servicios |
| `make logs` | Mostrar logs de contenedores |

### Flujo de Desarrollo

| Comando | Descripción |
|---------|-------------|
| `make shell` | Acceder al contenedor de la aplicación |
| `make install` | Instalar dependencias de Composer |
| `make cache-clear` | Limpiar y calentar caché de Symfony |
| `make mysql` | Acceder a la consola de MySQL |
| `make redis` | Acceder a la CLI de Redis |

### Calidad de Código

| Comando | Descripción |
|---------|-------------|
| `make quality` | Ejecutar todas las verificaciones de calidad |
| `make test` | Ejecutar tests de PHPUnit |
| `make test-coverage` | Ejecutar tests con reporte de cobertura |
| `make phpstan` | Ejecutar análisis estático con PHPStan |
| `make cs-check` | Verificar estilo de código |
| `make cs-fix` | Corregir problemas de estilo de código |

### Base de Datos y Migraciones

| Comando | Descripción |
|---------|-------------|
| `make migration` | Crear nueva migración de Doctrine |
| `make migrate` | Ejecutar migraciones pendientes |
| `make schema-validate` | Validar esquema de Doctrine |

### Rendimiento y Benchmarking

| Comando | Descripción |
|---------|-------------|
| `make benchmark` | Ejecutar tests de Apache Benchmark |

## 🔧 Configuración

### Variables de Entorno

El proyecto usa variables de entorno para la configuración. Copia `.env.example` a `.env` y ajusta según sea necesario:

```bash
# Configuración Docker
NGINX_PORT=8081
MYSQL_EXTERNAL_PORT=3306
REDIS_PORT=6379

# Base de Datos
MYSQL_DATABASE=siroko_cart
MYSQL_USER=siroko
MYSQL_PASSWORD=siroko123

# Symfony
APP_ENV=dev
DATABASE_URL=mysql://siroko:siroko123@mysql:3306/siroko_cart
```

### Servicios

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| **Web** | 8081 | Nginx + PHP 8.4 con Xdebug |
| **MySQL** | 3306 | Base de datos MySQL 8.0 |
| **Redis** | 6379 | Caché Redis |

## 🧪 Testing

### Entorno de Testing

- **Tests Unitarios**: PHPUnit con SQLite para ejecución rápida
- **Tests de Integración**: MySQL para testing de base de datos
- **Cobertura**: Reportes HTML generados en `var/coverage/`

```bash
# Ejecutar todos los tests
make test

# Ejecutar tests con reporte de cobertura
make test-coverage

# Acceder al reporte de cobertura
open var/coverage/index.html
```

### Base de Datos de Testing

Los tests usan **SQLite** para velocidad y aislamiento:
- Cada test obtiene una base de datos limpia
- No requiere dependencias externas
- Limpieza automática después de los tests

## 📋 Calidad de Código

### Gates de Calidad Automatizados

Cada commit es verificado automáticamente para:

- **Estilo de Código**: PHP CS Fixer con estándares de Symfony
- **Análisis Estático**: PHPStan nivel 8
- **Tests**: Suite de tests PHPUnit
- **Mensajes de Commit**: Formato Conventional Commits

### Git Hooks

Los hooks de pre-commit se instalan y ejecutan automáticamente:

```bash
# Instalar hooks (se hace automáticamente con make setup)
make install-hooks

# Saltar hooks temporalmente (no recomendado)
git commit --no-verify
```

### Conventional Commits

Los mensajes de commit deben seguir el formato [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: añadir funcionalidad de carrito de compras
fix(auth): resolver problema de validación de login
docs: actualizar documentación de API
refactor(cart): simplificar lógica de cálculo de items
test: añadir tests unitarios para servicio de carrito
```

## 🐳 Entorno Docker

### Arquitectura de Contenedores

```yaml
services:
  app:        # PHP 8.4-FPM con Xdebug
  nginx:      # Nginx 1.25 con configuración optimizada
  mysql:      # MySQL 8.0 con inicialización personalizada
  redis:      # Redis 7.2 para caché
```

### Características de Desarrollo

- **Hot Reload**: Los cambios se reflejan inmediatamente
- **Xdebug**: Listo para debugging (puerto 9003)
- **Optimizado**: Configuración lista para producción
- **Aislado**: Cada servicio en su propio contenedor

## 📊 Rendimiento

### Benchmarking

Testing de rendimiento integrado con Apache Benchmark:

```bash
# Ejecutar benchmarks de rendimiento
make benchmark
```

### Características de Optimización

- **OPcache**: Habilitado para mejor rendimiento
- **Redis**: Capa de caché lista
- **MySQL**: Optimizado con indexación adecuada
- **Nginx**: Compresión Gzip y caché de assets estáticos

## 🏗️ Estructura del Proyecto

```
├── .githooks/              # Git hooks para calidad de código
├── config/                 # Configuración de Symfony
├── docker/                 # Archivos de configuración Docker
├── public/                 # Directorio web raíz
├── src/                    # Código fuente (arquitectura hexagonal)
│   └── Shared/
│       └── Infrastructure/
│           └── Symfony/
├── tests/                  # Suite de tests
├── var/                    # Caché, logs, reportes de cobertura
├── .env.example           # Plantilla de entorno
├── Makefile               # Comandos de desarrollo
├── phpstan.neon           # Configuración de análisis estático
├── .php-cs-fixer.dist.php # Configuración de estilo de código
└── README.md              # Este archivo
```

# API Siroko Cart

API de carrito de compras y checkout para la plataforma e-commerce de Siroko. Implementa arquitectura hexagonal con principios DDD, proporcionando gestión de carrito y funcionalidad de checkout.

## 🚀 Inicio Rápido

```bash
# Clonar y configurar
git clone https://github.com/mfeolacruz/siroko-cart.git
cd siroko-cart
make setup

# Ejecutar tests
make test
```

**Acceso a la API**: `http://localhost:8082` (puerto por defecto)

## 📚 Especificación OpenAPI

Documentación interactiva de la API disponible en:
- **Swagger UI**: `http://localhost:8082/api/doc`

## 🏗️ Modelado del Dominio

**Arquitectura Hexagonal** con **Domain-Driven Design**:

```
src/
├── Domain/          # Lógica de negocio, entidades, value objects
│   ├── Cart/        # Agregado Cart, entidades, eventos
│   ├── Checkout/    # Agregado Order, proceso de checkout  
│   └── Shared/      # Value objects compartidos (Money, UUID)
├── Application/     # Casos de uso, comandos, queries (CQRS)
└── Infrastructure/  # Framework, persistencia, controladores
```

**Agregados Principales:**
- **Cart**: Gestiona los artículos del carrito y operaciones
- **Order**: Maneja el proceso de checkout y creación de pedidos

## 🛠️ Stack Tecnológico

- **PHP 8.4** con PHP-FPM
- **Symfony 7.3** (esqueleto API)
- **MySQL 8.0** 
- **Docker & Docker Compose**
- **PHPUnit** (3 suites de test: Unit, Integration, Functional)
- **PHPStan** (nivel 9), **PHP CS Fixer** (PSR-12)
- **Apache Benchmark** (testing de performance)

## 📊 Performance y Cobertura

### Testing de Performance
Integración con Apache Benchmark que proporciona métricas de rendimiento:

```bash
make benchmark-quick    # Test rápido: ~39 req/seg, ~25ms promedio
make benchmark          # Test estándar (100 requests, 10 concurrentes)
make benchmark-load     # Test de carga (1000 requests, 50 concurrentes)
```

**Performance Actual**: ~39 requests/segundo, ~25ms tiempo de respuesta promedio para creación de carrito.

### Cobertura de Tests
```bash
make test-coverage      # Reportes HTML + consola
```

**Cobertura Actual**: 82.65% líneas, 76.39% métodos, 221 tests en 3 suites
- **Capa Domain**: 100% cobertura (lógica de negocio)
- **Capa Application**: 100% cobertura (casos de uso) 
- **Capa Infrastructure**: Cobertura parcial (tipos Doctrine, controladores)

## ⚠️ Deuda Técnica

**Limitaciones conocidas de esta demo:**

1. **Textos Hardcodeados**: Los mensajes de error y validación no están internacionalizados
2. **Gestión de Excepciones**: Respuestas HTTP básicas, podrían ser más sofisticadas
3. **Auth & Seguridad**: No hay autenticación/autorización implementada
4. **Persistencia**: Limpieza automática de carritos expirados no implementada
5. **Eventos de Dominio**: Los eventos se disparan pero no hay handlers implementados
6. **Versionado de API**: No implementado
7. **Validación de Input**: Validación básica, podría ser más completa

*En un entorno de producción, esto se abordaría con i18n apropiado, manejo completo de errores, capas de seguridad, trabajos en segundo plano, event sourcing y estrategias de versionado de API.*

## 🔍 Calidad de Código

### Análisis Estático
**PHPStan nivel 9** garantiza código libre de errores:
```bash
make phpstan            # Análisis estático completo
```

### Estilo de Código
**PHP CS Fixer** con estándar **PSR-12**:
```bash
make cs-check           # Verificar estilo de código
make cs-fix             # Corregir automáticamente
```

### Git Hooks
Se instalan automáticamente y validan antes de cada commit:
- ✅ PHPStan (nivel 9)
- ✅ Estilo de código (PSR-12)
- ✅ Formato de commit (conventional commits)

### Conventional Commits
Formato estándar para mensajes de commit:
```bash
<tipo>(<scope>): <descripción>

# Tipos: feat, fix, docs, style, refactor, test, chore, perf
# Ejemplos:
feat: añadir endpoint de creación de carrito
fix(cart): resolver bug de cálculo de cantidad
test: añadir tests unitarios para entidad cart
```

## 📝 Comandos de Desarrollo

```bash
# Comandos esenciales
make setup              # Configuración inicial del proyecto
make test               # Ejecutar todos los tests
make up/down            # Iniciar/parar contenedores
make shell              # Acceder al contenedor PHP
make phpstan            # Análisis estático
make cs-fix             # Corregir estilo de código
```

## 🔄 Git Flow

**Estrategia de branching:**
1. **main**: Código de producción estable
2. **develop**: Rama de desarrollo principal
3. **feature/***: Nuevas características desde develop
4. **hotfix/***: Correcciones urgentes desde main

**Flujo de trabajo:**
1. Crear rama feature desde `develop`
2. Seguir conventional commits
3. Asegurar que pasen todos los tests (`make test`)
4. Ejecutar validaciones de calidad (`make phpstan`, `make cs-check`)
5. Crear Pull Request hacia `develop`

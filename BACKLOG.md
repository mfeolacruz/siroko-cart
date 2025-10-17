# BACKLOG - Siroko Cart Microservice

## Priorización
Las tareas están ordenadas por prioridad de ejecución. Seguir este orden garantiza un desarrollo incremental funcional.

---

## EPIC 0: Technical Setup [PRIORITY: 0]
*Setup inicial del proyecto con arquitectura hexagonal y Docker*

### Feature 0.1: Docker Environment
- **[TASK-001]** Crear docker-compose.yml con PHP 8.4, Nginx, MySQL 8.0
- **[TASK-002]** Configurar Dockerfile para desarrollo con Xdebug
- **[TASK-003]** Crear Makefile con comandos básicos (up, down, tests, etc.)

### Feature 0.2: Symfony & Hexagonal Structure
- **[TASK-004]** Instalar Symfony 7.x skeleton
- **[TASK-005]** Crear estructura hexagonal (Domain, Application, Infrastructure)
- **[TASK-006]** Configurar services.yaml para autowiring por capas
- **[TASK-007]** Setup básico de testing (PHPUnit + estructura de tests)

---

## EPIC 1: Cart Management [PRIORITY: 1]
*Gestión completa del carrito de compra*

### Feature 1.1: Cart Domain Model
**User Story**: As a developer, I want a robust cart domain model so that business rules are enforced

- **[TASK-008]** Crear agregado Cart con entidades CartItem y value objects (CartId, ProductId, Quantity, Money)
- **[TASK-009]** Implementar invariantes de negocio (cantidad mínima, máxima por producto)
- **[TASK-010]** Crear eventos de dominio (ProductAddedToCart, CartItemUpdated, CartItemRemoved)

### Feature 1.2: Add Products to Cart
**User Story**: As a customer, I want to add products to my cart so that I can purchase them later

- **[TASK-011]** Crear caso de uso AddProductToCart (Command + Handler)
- **[TASK-012]** Implementar endpoint POST /api/carts/{cartId}/items
- **[TASK-013]** Tests unitarios y de integración

### Feature 1.3: Update Cart Items
**User Story**: As a customer, I want to update product quantities in my cart so that I can adjust my purchase

- **[TASK-014]** Crear caso de uso UpdateCartItemQuantity
- **[TASK-015]** Implementar endpoint PUT /api/carts/{cartId}/items/{productId}
- **[TASK-016]** Tests unitarios y de integración

### Feature 1.4: Remove Cart Items
**User Story**: As a customer, I want to remove products from my cart so that I only buy what I need

- **[TASK-017]** Crear caso de uso RemoveProductFromCart
- **[TASK-018]** Implementar endpoint DELETE /api/carts/{cartId}/items/{productId}
- **[TASK-019]** Tests unitarios y de integración

### Feature 1.5: View Cart
**User Story**: As a customer, I want to view my cart contents so that I can review before checkout

- **[TASK-020]** Crear query GetCart con proyección optimizada (CQRS)
- **[TASK-021]** Implementar endpoint GET /api/carts/{cartId}
- **[TASK-022]** Tests de integración

---

## EPIC 2: Checkout & Order [PRIORITY: 2]
*Procesamiento del pago y generación de órdenes*

### Feature 2.1: Order Domain Model
**User Story**: As a developer, I want an Order aggregate so that purchases are properly recorded

- **[TASK-023]** Crear agregado Order con value objects (OrderId, OrderNumber, OrderStatus)
- **[TASK-024]** Implementar eventos de dominio (OrderCreated, OrderConfirmed)

### Feature 2.2: Checkout Process
**User Story**: As a customer, I want to checkout my cart so that I can complete my purchase

- **[TASK-025]** Crear caso de uso ProcessCheckout (convierte Cart en Order)
- **[TASK-026]** Implementar endpoint POST /api/carts/{cartId}/checkout
- **[TASK-027]** Implementar idempotencia en checkout
- **[TASK-028]** Tests unitarios y de integración

### Feature 2.3: Order Persistence
**User Story**: As a business, I want orders stored persistently so that we have purchase records

- **[TASK-029]** Implementar OrderRepository con Doctrine
- **[TASK-030]** Crear migración para tabla orders
- **[TASK-031]** Tests de persistencia

---

## EPIC 3: Quality & Performance [PRIORITY: 3]
*Optimización y calidad del código*

### Feature 3.1: Performance Optimization
- **[TASK-032]** Implementar cache para consultas de carrito (Redis)
- **[TASK-033]** Añadir índices en base de datos
- **[TASK-034]** Implementar bulk operations donde sea posible

### Feature 3.2: Observability
- **[TASK-035]** Añadir logging estructurado
- **[TASK-036]** Implementar health check endpoint
- **[TASK-037]** Añadir métricas de performance en endpoints críticos

### Feature 3.3: Documentation
- **[TASK-038]** Generar OpenAPI specification
- **[TASK-039]** Documentar modelo de dominio en README
- **[TASK-040]** Añadir ejemplos de uso en la documentación

---

## Out of Scope (para esta prueba técnica)
- Autenticación/Autorización (asumimos cartId como identificador)
- Gestión de productos (asumimos catálogo externo)
- Integración real con pasarela de pagos
- Multi-moneda
- Descuentos y cupones
- Stock management
- Notificaciones

---

## Definition of Done
- [ ] Código implementado siguiendo DDD y arquitectura hexagonal
- [ ] Tests unitarios con cobertura >80%
- [ ] Tests de integración para endpoints
- [ ] Documentación en OpenAPI
- [ ] PR creada y mergeada a develop
- [ ] Sin dependencias del framework en Domain
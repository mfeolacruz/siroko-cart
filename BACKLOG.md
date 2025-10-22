# BACKLOG - API Carrito de Compra Siroko

## Visión del Producto
Desarrollar una API de carrito de compra que permita a los clientes de Siroko añadir productos, gestionar su carrito y completar el proceso de compra de forma rápida y eficiente.

---

## EPIC 1: Preparación del Entorno de Desarrollo
Como equipo de desarrollo, necesitamos un entorno robusto y automatizado para garantizar la calidad del código y la productividad.

### FEATURE 1.1: Instalación de Symfony y Configuración del Entorno Docker
**Historias de Usuario:**
- Como desarrollador, quiero instalar Symfony 7.x como base del proyecto
- Como desarrollador, quiero un entorno Docker con PHP 8.4, Nginx, MySQL y phpMyAdmin para desarrollar de forma consistente
- Como desarrollador, quiero Xdebug configurado para poder depurar el código
- Como desarrollador, quiero un Makefile con comandos útiles para agilizar las tareas comunes

**Criterios de Aceptación:**
- Symfony 7.x instalado y funcional
- Docker-compose funcional con todos los servicios
- Xdebug operativo para debugging
- Makefile con comandos básicos (up, down, test, etc.)

### FEATURE 1.2: Herramientas de Calidad de Código
**Historias de Usuario:**
- Como desarrollador, quiero git hooks que validen la calidad del código antes de cada commit
- Como desarrollador, quiero que los commits sigan convenciones para mantener un historial limpio

**Criterios de Aceptación:**
- PHPStan nivel 9 configurado
- PHP_CodeSniffer con PSR-12
- Git hooks validando commits convencionales
- PHPUnit configurado para TDD

### FEATURE 1.3: Estructura Hexagonal
**Historias de Usuario:**
- Como arquitecto de software, quiero una estructura de carpetas que respete la arquitectura hexagonal
- Como desarrollador, quiero que el dominio esté completamente desacoplado del framework

**Criterios de Aceptación:**
- Estructura src/ con Domain, Application e Infrastructure
- Composer.json con namespaces correctamente configurados
- Autoloading PSR-4 funcional

---

## EPIC 2: Gestión del Carrito de Compra
Como cliente de Siroko, quiero poder gestionar mi carrito de compra para seleccionar los productos que deseo adquirir.

### FEATURE 2.1: Crear Carrito de Compra
**Historias de Usuario:**
- Como cliente, quiero poder crear un carrito de compra para empezar mi proceso de selección de productos

**Criterios de Aceptación (TDD):**
- Test: El carrito se crea con un ID único
- Test: El carrito se crea vacío
- Test: El carrito tiene un timestamp de creación
- Endpoint: POST /api/carts
- Respuesta: 201 Created con el ID del carrito

### FEATURE 2.2: Añadir Productos al Carrito
**Historias de Usuario:**
- Como cliente, quiero añadir productos a mi carrito para poder comprarlos posteriormente

**Criterios de Aceptación (TDD):**
- Test: Se puede añadir un producto con cantidad válida
- Test: No se puede añadir cantidad negativa o cero
- Test: Se valida que el producto existe
- Test: Si añado el mismo producto, se suma la cantidad
- Endpoint: POST /api/carts/{cartId}/items
- Respuesta: 200 OK con el carrito actualizado

### FEATURE 2.3: Actualizar Cantidad de Productos
**Historias de Usuario:**
- Como cliente, quiero poder modificar la cantidad de un producto en mi carrito sin tener que eliminarlo y volverlo a añadir

**Criterios de Aceptación (TDD):**
- Test: Se puede actualizar la cantidad a un valor válido
- Test: No se puede actualizar a cantidad negativa o cero
- Test: Se valida que el item existe en el carrito
- Endpoint: PUT /api/carts/{cartId}/items/{itemId}
- Respuesta: 200 OK con el item actualizado

### FEATURE 2.4: Eliminar Productos del Carrito
**Historias de Usuario:**
- Como cliente, quiero poder eliminar productos de mi carrito si cambio de opinión

**Criterios de Aceptación (TDD):**
- Test: Se puede eliminar un item existente
- Test: Error si el item no existe en el carrito
- Test: El carrito recalcula el total tras eliminar
- Endpoint: DELETE /api/carts/{cartId}/items/{itemId}
- Respuesta: 204 No Content

### FEATURE 2.5: Visualizar Carrito
**Historias de Usuario:**
- Como cliente, quiero ver el contenido de mi carrito con el detalle de productos y el total a pagar

**Criterios de Aceptación (TDD):**
- Test: Se muestran todos los items del carrito
- Test: Se calcula el subtotal por item (precio × cantidad)
- Test: Se calcula el total del carrito
- Test: Carrito vacío devuelve total 0
- Endpoint: GET /api/carts/{cartId}
- Respuesta: 200 OK con el contenido completo

---

## EPIC 3: Proceso de Checkout
Como cliente de Siroko, quiero completar mi compra para recibir mis productos.

### FEATURE 3.1: Procesar Pago (Checkout)
**Historias de Usuario:**
- Como cliente, quiero confirmar mi compra y generar un pedido para recibir mis productos

**Criterios de Aceptación (TDD):**
- Test: Se genera una orden con los items del carrito
- Test: La orden tiene un ID único
- Test: La orden guarda el total del momento del checkout
- Test: El carrito se vacía tras el checkout exitoso
- Test: No se puede hacer checkout de carrito vacío
- Endpoint: POST /api/carts/{cartId}/checkout
- Respuesta: 201 Created con el ID de la orden

### FEATURE 3.2: Persistencia de Órdenes
**Historias de Usuario:**
- Como empresa, necesito que las órdenes queden guardadas permanentemente para gestión y contabilidad

**Criterios de Aceptación (TDD):**
- Test: La orden se guarda en base de datos
- Test: La orden incluye todos los detalles de productos
- Test: La orden tiene timestamp de creación
- Test: Se puede recuperar una orden por su ID

---

## EPIC 4: Documentación y Entrega
Como consumidor de la API, necesito documentación clara para integrarme correctamente.

### FEATURE 4.1: Documentación OpenAPI
**Historias de Usuario:**
- Como desarrollador frontend, quiero documentación OpenAPI para entender cómo consumir la API

**Criterios de Aceptación:**
- Especificación OpenAPI 3.0 completa
- Todos los endpoints documentados
- Ejemplos de request/response
- Códigos de error documentados

### FEATURE 4.2: README y Documentación Técnica
**Historias de Usuario:**
- Como evaluador técnico, quiero un README claro con instrucciones de instalación y arquitectura

**Criterios de Aceptación:**
- Descripción del proyecto
- Modelado del dominio con diagrama
- Instrucciones Docker
- Comandos para tests
- Métricas de performance

---

## Definición de "Hecho" (Definition of Done)

Para cada feature:
- [ ] Tests escritos ANTES del código (TDD)
- [ ] Tests unitarios con cobertura > 90%
- [ ] Tests de integración para los repositorios
- [ ] Tests funcionales para los endpoints
- [ ] Código cumple PSR-12 (validado por CodeSniffer)
- [ ] PHPStan nivel 9 sin errores
- [ ] Performance medida con Apache Benchmark
- [ ] Documentación actualizada
- [ ] PR revisada siguiendo gitflow
- [ ] Commits con formato convencional
- [ ] Merged a develop

---

## Notas Técnicas de Implementación

### Orden de Desarrollo (Inside-Out con TDD)
1. **Dominio**: Empezar por las entidades y value objects con sus tests
2. **Aplicación**: Implementar casos de uso (Commands/Queries) con sus tests
3. **Infraestructura**: Finalmente los controladores Symfony y persistencia

### Stack Tecnológico
- PHP 8.4
- Symfony 7.x
- MySQL 8.0
- Docker & Docker Compose
- PHPUnit (TDD)
- PHPStan + CodeSniffer
- Apache Benchmark

### Patrones y Arquitectura
- Arquitectura Hexagonal
- Domain-Driven Design (DDD)
- CQRS (Command Query Responsibility Segregation)
- Repository Pattern
- Domain Events

### Simplificaciones Acordadas
- Sin autenticación de usuarios (carrito por ID)
- Sin integración real de pasarela de pago
- Sin gestión de stock
- Sin catálogo de productos (solo ID, nombre, precio)
- Sin notificaciones por email

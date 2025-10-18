# Siroko **Senior** Code Challenge

Siroko es una marca que vende productos de deporte - especialmente relacionados con ciclismo y fitness - a través de su plataforma *e-commerce*.

Como parte de la plataforma, necesitamos diseñar una **cesta de compra (carrito)** que permita a cualquier persona interesada comprar de forma **rápida** y **eficiente** y, a continuación, **completar el proceso de pago** generando una **orden**.

El equipo ha decidido que la mejor forma de implementar todo esto es partir de una **API** desacoplada de la UI.

Tu misión consiste en iniciar el desarrollo de ese **carrito + checkout**, que después consumirá la interfaz de usuario.

---

## Requerimientos obligatorios

- Gestión de productos que permita **añadir, actualizar y eliminar** ítems del carrito.
- Obtener productos del carrito.
- **Procesar el pago** (checkout) y generar una **orden** persistente al confirmar la compra.
- El diseño de dominio es libre, siempre que el mismo esté **desacoplado** del framework.

---

## ¿Qué valoramos?

1. **Código limpio, simple y fácil de entender... pero con previsión de escala.**
2. **Conocimientos de Arquitectura Hexagonal y DDD**. Aplicar entidades, agregados, value objects, eventos de dominio, etc...
3. **Soltura aplicando CQRS.**
4. **Testing exhaustivo**: máxima cobertura de casos de uso.
5. **Time to market**: preferimos una solución fácil de evolucionar a la perfección académica.
6. **Performance, performance, performance**...medida y justificada con datos.
7. No valoraremos la **UI**; concéntrate en la **API**.
8. Symfony framework, dominio desacoplado del mismo.
9. Uso sólido de **Git** y un historial de commits comprensible (feature branches + PR).

Si algo no está claro, **pregunta**.

---

## Entrega

Sube el código a un repositorio público y añade en el **README**:

- Breve descripción del proyecto.
- OpenAPI Specification.
- Modelado del dominio.
- Tecnología utilizada.
- Instrucciones para levantar el entorno con `docker-compose up`.
- Comando para lanzar los tests.

---

*¡Manos a la obra y suerte!*

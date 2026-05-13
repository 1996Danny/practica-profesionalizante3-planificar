# 🛠 Reglas de Contribución y Guía del Proyecto

Este documento establece las normas para mantener la consistencia y la calidad del código en este proyecto.

---

## 🌿 Flujo de Ramas (Git Flow)

Para mantener la rama `main` siempre estable, utilizamos el siguiente esquema de nombres:

*   **`main`**: Solo código en producción. Nadie sube cambios directamente aquí.
*   **`develop`**: Rama de integración. Aquí se mezclan las nuevas funcionalidades.
*   **`feature/nombre-de-la-tarea`**: Para nuevas funcionalidades (ej: `feature/login-google`).
*   **`fix/error-reportado`**: Para corrección de errores (ej: `fix/error-registro-api`).
*   **`docs/descripcion`**: Cambios exclusivos en documentación.

> **Regla de Oro:** Siempre crea una rama desde `develop` y haz un Pull Request (PR) de vuelta a `develop`.

---

## 📝 Estándar de Commits

Usamos mensajes claros para que el historial sea legible:

*   **Format:** `tipo: descripción corta`
*   **Tipos comunes:**
    *   `feat`: Nueva funcionalidad.
    *   `fix`: Corrección de un error.
    *   `refactor`: Mejora de código que no añade funciones ni arregla errores.
    *   `docs`: Cambios en la documentación.

*Ejemplo:* `feat: implementar validación de RUT en el registro de usuarios`

---

## 📋 Guía para Issues e Historias de Usuario

Al crear un **Issue** en GitHub, asegúrate de seguir esta estructura basada en nuestras Historias de Usuario:

1.  **Título:** Breve y descriptivo.
2.  **Historia de Usuario:** 
    *   "Como [rol], quiero [acción] para [beneficio]."
3.  **Criterios de Aceptación:**
    *   [ ] Debe validar X cosa.
    *   [ ] Debe retornar un error 404 si no existe.
4.  **Etiquetas:** Usa las etiquetas de GitHub (`bug`, `enhancement`, `documentation`).

---

## 💻 Reglas de Código (Laravel Style)

Para que todos escribamos igual:

1.  **Nombres:** Variables y funciones en `camelCase`, Clases en `PascalCase`.
2.  **Idioma:** Todo el código (variables, métodos, comentarios) debe estar en **Inglés** (opcional, ajusta si prefieres español).
3.  **Type Hinting:** Usa tipos en los argumentos y retornos de funciones siempre que sea posible.
    ```php
    public function saveUser(User $user): bool { ... }
    ```
4.  **No dejar basura:** Elimina comentarios de código muerto o `dd()` antes de hacer el commit.

---

## ✅ Checklist antes de enviar un Pull Request

Antes de solicitar una revisión, asegúrate de:
- [ ] Tu código no rompe la aplicación.
- [ ] Has ejecutado los tests (si existen) con `php artisan test`.
- [ ] Tu rama está actualizada con `develop`.
- [ ] El código sigue las reglas de estilo mencionadas arriba.

Guía de Historias de Usuario - PlanificAR
Este documento contiene las especificaciones funcionales para el desarrollo de la plataforma, organizadas por roles. Cada historia debe ser tratada como un Issue dentro del repositorio.

🔐 Generales (Acceso y Perfil)
HU01: Login de Usuarios
Historia de Usuario:
"Como usuario, quiero ingresar con mi correo y clave para acceder a mis datos de forma segura".

Criterios de Aceptación:

[ ] Debe validar que el formato de email sea correcto.

[ ] Debe incluir una opción funcional de "Recuperar contraseña".

[ ] Debe denegar el acceso ante credenciales incorrectas.

Etiquetas: enhancement security

HU02: Gestión de Perfil
Historia de Usuario:
"Como usuario, quiero ver y editar mis datos básicos para mantener mi información actualizada".

Criterios de Aceptación:

[ ] El usuario debe poder cambiar su nombre y contraseña.

[ ] Debe permitir la carga o actualización de una foto de perfil.

Etiquetas: enhancement

🍎 Rol: Docente
HU03: Carga de Planificaciones
Historia de Usuario:
"Como docente, quiero cargar archivos o completar un formulario de planificación para que el director la revise".

Criterios de Aceptación:

[ ] Soporte para subir archivos en formatos PDF y Word.

[ ] Selección obligatoria de grado/sección y área curricular.

Etiquetas: feature

HU04: Consulta de Historial
Historia de Usuario:
"Como docente, quiero ver mis planificaciones anteriores para reutilizar material o consultar temas dados".

Criterios de Aceptación:

[ ] Implementar un buscador funcional por año o mes.

[ ] Opción para descargar archivos cargados previamente.

Etiquetas: feature ux

HU05: Seguimiento de Estado y Feedback
Historia de Usuario:
"Como docente, quiero ver el estado (Pendiente, Aprobado, Observado) para saber si debo realizar correcciones".

Criterios de Aceptación:

[ ] Notificación automática al docente cuando el estado cambie.

[ ] Sección visible para leer los comentarios y observaciones del director.

Etiquetas: feature notifications

📋 Rol: Director
HU06: Visualización de Plantel
Historia de Usuario:
"Como director, quiero ver la lista de docentes vinculados a mi escuela para tener un control de la nómina".

Criterios de Aceptación:

[ ] Vista de lista completa con filtros por turno y grado.

[ ] Los datos del docente deben ser legibles y actualizados.

Etiquetas: feature admin

HU07: Gestión de Docentes (Altas/Bajas)
Historia de Usuario:
"Como director, quiero dar de alta o baja a docentes para mantener el equipo actualizado según el ciclo lectivo".

Criterios de Aceptación:

[ ] Confirmación de seguridad antes de procesar una baja (Baja lógica).

[ ] Sistema de invitación por correo electrónico para nuevos docentes.

Etiquetas: feature admin

HU08: Revisión y Corrección
Historia de Usuario:
"Como director, quiero cambiar el estado de las planificaciones y agregar observaciones para guiar la labor docente".

Criterios de Aceptación:

[ ] Selector con los estados: "Aprobado", "A corregir" y "Rechazado".

[ ] Campo de texto obligatorio al seleccionar "A corregir" o "Rechazado" para justificar la decisión.

Etiquetas: feature admin

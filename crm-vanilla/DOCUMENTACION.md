# CRM Vanilla PHP — Documentación completa

> Sistema CRM de gestión de pipeline de ventas construido en **PHP puro + JavaScript vanilla**, sin frameworks, sin Composer y sin proceso de build. Diseñado específicamente para funcionar en **hosting compartido** (Hostinger y similares).

**Versión de esta documentación:** julio 2026 · Rama: `claude/pipeline-management-system-JQGgE`

---

## Índice

1. [Contexto del proyecto](#1-contexto-del-proyecto)
2. [Stack técnico y requisitos](#2-stack-técnico-y-requisitos)
3. [Estructura de carpetas](#3-estructura-de-carpetas)
4. [Arquitectura](#4-arquitectura)
5. [Instalación desde cero en Hostinger](#5-instalación-desde-cero-en-hostinger)
6. [Instalación en entorno local](#6-instalación-en-entorno-local)
7. [Configuración: el archivo .env](#7-configuración-el-archivo-env)
8. [Base de datos](#8-base-de-datos)
9. [Roles y permisos](#9-roles-y-permisos)
10. [Módulos de la aplicación](#10-módulos-de-la-aplicación)
11. [API interna (JSON)](#11-api-interna-json)
12. [Seguridad implementada](#12-seguridad-implementada)
13. [Rendimiento](#13-rendimiento)
14. [Mantenimiento y operación](#14-mantenimiento-y-operación)
15. [Solución de problemas](#15-solución-de-problemas)
16. [Limitaciones conocidas y mejoras futuras](#16-limitaciones-conocidas-y-mejoras-futuras)

---

## 1. Contexto del proyecto

### Origen

Este proyecto nació como una aplicación **Laravel 11 + Livewire 3** (que sigue existiendo en la raíz del repositorio). Se decidió portarla a **PHP puro** para poder desplegarla en hosting compartido económico, donde no siempre hay acceso a Composer, Node.js, colas de trabajo ni configuración del servidor.

La versión vanilla (`crm-vanilla/`) replica todas las funcionalidades del original:

- Autenticación con roles
- Gestión de negocios (deals) y clientes
- Tablero Kanban con drag & drop
- Dashboard con métricas y gráficos
- Reportes con exportación CSV/PDF
- Administración de usuarios, verticales de negocio y etapas de venta

### Decisiones de diseño

| Decisión | Motivo |
|---|---|
| Sin Composer ni dependencias PHP | El autoload es un `spl_autoload_register` de 5 líneas; el hosting no necesita CLI |
| Tailwind CSS + Chart.js + SortableJS por CDN | Cero proceso de build; se sube y funciona |
| PDO con consultas preparadas | Único requisito: extensión `pdo_mysql` (estándar en todo hosting) |
| Variables de entorno en `$_ENV`, no `putenv()` | `putenv()` suele estar deshabilitado en hosting compartido |
| Rate limiting basado en archivos | No hay Redis/APCu en hosting compartido |
| Detección automática de URL y subdirectorio | Funciona en `public_html/` o en `public_html/cualquier-carpeta/` sin configurar nada |

### El repositorio contiene dos aplicaciones

```
CRM/                      ← repositorio
├── app/, routes/, ...    ← aplicación Laravel ORIGINAL (no se despliega en compartido)
└── crm-vanilla/          ← ESTA aplicación (la que se sube al hosting)
```

**Para el despliegue en Hostinger solo se usa el contenido de `crm-vanilla/`.**

---

## 2. Stack técnico y requisitos

### En el servidor

| Componente | Requisito | Notas |
|---|---|---|
| PHP | **8.1 o superior** (recomendado 8.2) | Usa `match`, `str_starts_with`, tipos union, `never` |
| Extensiones PHP | `pdo_mysql`, `mbstring` | Activas por defecto en Hostinger |
| Base de datos | MySQL 5.7+ / MariaDB 10.3+ | InnoDB, utf8mb4 |
| Servidor web | Apache o LiteSpeed con `mod_rewrite` | Hostinger usa LiteSpeed (compatible con `.htaccess`) |
| Escritura en disco | Carpeta `storage/` escribible | Para logs y control de intentos de login |

### En el navegador (vía CDN, sin instalación)

- **Tailwind CSS** (`cdn.tailwindcss.com`) — estilos
- **Chart.js 4** (`cdn.jsdelivr.net`) — gráficos del dashboard
- **SortableJS 1.15** (`cdn.jsdelivr.net`) — drag & drop del Kanban

> Los CDN requieren que los usuarios finales tengan internet (obvio en un CRM web). La política CSP de la app ya permite exactamente esos tres orígenes.

---

## 3. Estructura de carpetas

```
crm-vanilla/
├── index.php              # Punto de entrada único: bootstrap + definición de rutas
├── .htaccess              # Rewrites, bloqueo de archivos sensibles, caché de assets
├── .env.example           # Plantilla de configuración (copiar a .env)
├── .env                   # ← LO CREAS TÚ en el servidor. Nunca se sube al repo
│
├── config/
│   ├── app.php            # Nombre, URL, timezone, debug, duración de sesión
│   └── database.php       # Credenciales MySQL (leídas del .env)
│
├── src/
│   ├── Core/              # Mini-framework
│   │   ├── Router.php     # Router con patrones {param} y _method tunneling
│   │   ├── Database.php   # Singleton PDO + helpers query/insert/update/transaction
│   │   ├── Auth.php       # Sesión, login/logout, roles, caché de usuario
│   │   ├── Session.php    # Mensajes flash y old input
│   │   ├── View.php       # Render de vistas con layouts
│   │   └── helpers.php    # env(), url(), e(), csrf, formatos, etc.
│   ├── Controllers/
│   │   ├── AuthController.php        # Login con rate limiting, logout
│   │   ├── DashboardController.php
│   │   ├── DealController.php
│   │   ├── ClientController.php
│   │   ├── KanbanController.php
│   │   ├── ReportController.php      # Export CSV (con protección de fórmulas) y PDF
│   │   ├── Admin/
│   │   │   ├── UserController.php
│   │   │   └── VerticalController.php
│   │   └── Api/
│   │       ├── DealController.php    # move / metrics / addActivity (JSON)
│   │       └── StageController.php
│   └── Models/            # Deal, Client, User, Vertical, Stage, Activity
│
├── views/
│   ├── layouts/           # app.php (con sidebar) y auth.php (login)
│   ├── dashboard/, deals/, clients/, kanban/, reports/, admin/, auth/, errors/
│
├── assets/
│   ├── css/custom.css
│   └── js/                # app.js (helpers), kanban.js, charts.js, deals.js
│
├── database/
│   ├── schema.sql         # Estructura: 6 tablas con índices y FK
│   └── seed.sql           # Datos iniciales: usuarios, verticales, etapas, demos
│
└── storage/               # ÚNICA carpeta con escritura. Bloqueada vía .htaccess
    ├── logs/              # php_errors.log
    └── cache/             # login_*.json (rate limiting)
```

---

## 4. Arquitectura

### Ciclo de una petición

1. `.htaccess` envía toda URL que no sea un archivo real a `index.php`.
2. `index.php` registra el autoloader, carga `helpers.php`, lee el `.env` a `$_ENV`, configura errores/logs, envía cabeceras de seguridad y abre la sesión.
3. Se registran todas las rutas en el `Router`.
4. Se recorta el prefijo del subdirectorio (si la app no está en la raíz del dominio) y se despacha: el router encuentra el patrón, instancia el controlador y llama al método con los parámetros de la URL.
5. El controlador valida sesión/rol/CSRF, consulta los modelos y renderiza una vista (`View::render`) o responde JSON (`View::json`).

### Convenciones

- **Rutas**: se definen en `index.php` como `$router->get('/deals/{id}', 'DealController@show')`. El namespace `Controllers\` se antepone automáticamente; los subdirectorios se indican con `/` (ej. `Admin/UserController@index`).
- **Modelos**: clases estáticas que encapsulan el SQL. No hay ORM; cada método es una consulta preparada explícita.
- **Vistas**: PHP plano. **Toda salida de datos de usuario debe pasar por `e()`** (htmlspecialchars). Los layouts envuelven el contenido; una vista puede elegir layout con `'layout' => 'auth'`.
- **Formularios POST**: siempre incluyen `<?= csrf_field() ?>` y el controlador llama `verify_csrf()`.
- **Peticiones AJAX**: usan el helper `apiFetch()` de `assets/js/app.js`, que adjunta el token CSRF en la cabecera `X-CSRF-TOKEN` automáticamente.

### Soporte de subdirectorios (importante en hosting compartido)

La app funciona igual en `https://dominio.com` que en `https://dominio.com/crm/` sin tocar configuración:

- `app_base_path()` detecta la carpeta desde `SCRIPT_NAME`.
- `base_url()` construye la URL completa (o usa `APP_URL` del `.env` si está definida).
- El dispatcher recorta el prefijo antes de buscar la ruta.
- La cookie de sesión se limita a esa ruta.

---

## 5. Instalación desde cero en Hostinger

### Paso 0 — Qué necesitas tener a mano

- Acceso a hPanel de Hostinger.
- El pack de la aplicación: ZIP `crm-vanilla-hostinger.zip` o descarga de la rama del repo:
  `https://github.com/Nicknovela/CRM/archive/refs/heads/claude/pipeline-management-system-JQGgE.zip`
  (en ese ZIP la app está dentro de la subcarpeta `crm-vanilla/`).

### Paso 1 — Configurar PHP

hPanel → **Avanzado → Configuración PHP** → seleccionar **PHP 8.2**.
En "Extensiones PHP", verificar que `pdo_mysql` y `mbstring` estén activas (lo están por defecto).

### Paso 2 — SSL

hPanel → **Seguridad → SSL** → instalar certificado → activar **Force HTTPS**.
(No añadas redirecciones HTTPS al `.htaccess`: el toggle del panel lo hace mejor y evita bucles detrás del proxy de Hostinger.)

### Paso 3 — Crear la base de datos

1. hPanel → **Bases de datos → MySQL**.
2. Crear base de datos + usuario + contraseña. **Anota los tres valores completos** (llevan prefijo, ej. `u123456789_crm`).
3. `DB_HOST` normalmente es `127.0.0.1`; si tu plan usa un host distinto, aparece en esa misma pantalla.

### Paso 4 — Importar el SQL (el orden importa)

Desde esa pantalla, botón **Entrar a phpMyAdmin**:

1. En el panel izquierdo, **haz clic en el nombre de tu base de datos** (debe quedar seleccionada).
2. Pestaña **Importar** → seleccionar `database/schema.sql` → **Importar**. *(Crea las 6 tablas.)*
3. Pestaña **Importar** → seleccionar `database/seed.sql` → **Importar**. *(Crea usuarios, verticales, etapas y datos demo.)*
4. Verificar: 6 tablas, y `users` con 3 filas.

### Paso 5 — Subir los archivos

1. Administrador de Archivos → `public_html/`.
2. **Activa "Mostrar archivos ocultos"** (engranaje de ajustes) — sin esto no verás `.htaccess` ni `.env.example`.
3. Sube el ZIP del pack y extráelo ahí mismo (clic derecho → Extract). Los archivos quedan directamente en su lugar.
4. Borra el ZIP del servidor.

> ¿Prefieres una subcarpeta? Extrae en `public_html/crm/` — la app detecta la ruta sola y todo funciona en `https://tudominio.com/crm`.

### Paso 6 — Crear el `.env`

1. Copia `.env.example` → renómbralo **exactamente** `.env` (con el punto, sin extensión).
2. Edítalo y completa solo la base de datos:

```env
APP_URL=
APP_NAME=CRM
APP_DEBUG=false
APP_TIMEZONE=America/La_Paz
ASSET_VERSION=1

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_crm
DB_USERNAME=u123456789_crm
DB_PASSWORD=la_contraseña_que_creaste
```

`APP_URL` puede quedar vacía (se autodetecta).

### Paso 7 — Primer arranque y limpieza (OBLIGATORIO)

1. Abre `https://tudominio.com` → debe aparecer el login.
2. Entra con **`admin@crm.test` / `password`**.
3. Inmediatamente, en **Usuarios**:
   - Cambia el correo y la contraseña del administrador (mínimo 8 caracteres).
   - Elimina o desactiva `gerente@crm.test` y `vendedor@crm.test` (comparten la misma contraseña demo).
4. (Opcional) Borra los datos de ejemplo desde la app, o en phpMyAdmin:
   ```sql
   DELETE FROM activities;
   DELETE FROM deals;
   DELETE FROM clients;
   ```
   Las verticales y etapas conviene conservarlas como punto de partida y editarlas en **Verticales**.

### Paso 8 — Checklist de verificación

| Prueba | Resultado esperado |
|---|---|
| `https://tudominio.com/.env` | **403 Forbidden** |
| `https://tudominio.com/config/database.php` | **403 Forbidden** |
| `https://tudominio.com/storage/logs/php_errors.log` | **403 Forbidden** |
| Login correcto | Entra al dashboard |
| 5 logins fallidos seguidos | Bloqueo de 15 minutos |
| Arrastrar un negocio en el Kanban | Se mueve y persiste al recargar |
| Exportar CSV en Reportes | Descarga el archivo |

---

## 6. Instalación en entorno local

Con XAMPP/Laragon/MAMP (PHP 8.1+ y MySQL):

1. Copia `crm-vanilla/` a la carpeta pública del servidor local (ej. `htdocs/crm/`).
2. Crea una base local (`crm_db`) e importa `schema.sql` + `seed.sql`.
3. Copia `.env.example` a `.env` con tus credenciales locales y `APP_DEBUG=true`.
4. Abre `http://localhost/crm`. No hay que configurar nada más: la app detecta el subdirectorio.

También sirve el servidor embebido de PHP (sin `.htaccess`, útil para pruebas rápidas):

```bash
cd crm-vanilla
php -S localhost:8080 index.php
```

---

## 7. Configuración: el archivo .env

| Variable | Obligatoria | Descripción |
|---|---|---|
| `APP_URL` | No | URL pública sin barra final (`https://dominio.com` o `https://dominio.com/crm`). **Vacía = autodetección** (recomendado). Defínela solo si generas URLs detrás de un proxy raro |
| `APP_NAME` | No | Nombre mostrado en el título y el sidebar. Por defecto: `CRM` |
| `APP_DEBUG` | No | `true` muestra errores en pantalla. **En producción siempre `false`** (los errores igual quedan en `storage/logs/php_errors.log`) |
| `APP_TIMEZONE` | No | Zona horaria PHP. Por defecto `America/La_Paz` |
| `ASSET_VERSION` | No | Súbelo (`1` → `2`...) cada vez que modifiques CSS/JS: invalida la caché del navegador de los usuarios |
| `DB_HOST` | Sí | Normalmente `127.0.0.1` en Hostinger |
| `DB_PORT` | No | Por defecto `3306` |
| `DB_DATABASE` | **Sí** | Nombre completo con prefijo (`u123456789_crm`) |
| `DB_USERNAME` | **Sí** | Usuario completo con prefijo |
| `DB_PASSWORD` | Sí | Contraseña del usuario MySQL |

Notas de formato: una variable por línea (`CLAVE=valor`), `#` para comentarios, comillas opcionales si el valor tiene espacios. Si `DB_DATABASE` o `DB_USERNAME` quedan vacíos, la app muestra un mensaje claro en lugar de intentar conectar.

**El `.env` nunca debe ser accesible por web** — el `.htaccess` lo bloquea por dos vías (rewrite + `<Files>`), y el `.gitignore` impide subirlo al repositorio.

---

## 8. Base de datos

### Tablas y relaciones

```
users ──────┐ (assigned_to, ON DELETE SET NULL)
            ▼
clients ─► deals ◄─ verticals ◄─ stages (stages.vertical_id)
            │           (deals.vertical_id, deals.stage_id)
            ▼
        activities (historial: notas, llamadas, cambios de etapa)
```

| Tabla | Contenido | Detalles |
|---|---|---|
| `users` | Usuarios del sistema | `role`: admin/manager/vendedor/viewer · `is_active` · contraseña bcrypt cost 12 |
| `verticals` | Líneas de negocio | Cada una con su propio pipeline de etapas · `track_commission` |
| `stages` | Etapas de venta por vertical | `position` (orden), `is_won`, `is_lost` |
| `clients` | Clientes/contactos | Empresa, industria, contacto |
| `deals` | Negocios/oportunidades | Monto, moneda, probabilidad, fechas de cierre esperada/real, comisión, **soft delete** (`deleted_at`) |
| `activities` | Historial por negocio | Tipo (note/call/email/meeting/stage_change) y descripción |

Índices relevantes: por etapa, vertical, responsable, `deleted_at`, fecha esperada de cierre y el compuesto `(client_id, deleted_at, amount)` para los agregados de la lista de clientes.

### Datos del seed

- **3 usuarios** (contraseña `password` en los tres — cambiarla al instalar):
  - `admin@crm.test` (admin), `gerente@crm.test` (manager), `vendedor@crm.test` (vendedor)
- **3 verticales** con pipelines de ejemplo: Software (7 etapas), Consultoría (5), Hardware (4)
- **5 clientes** y **5 negocios** de demostración

### Actualizar una base ya existente

Si creaste la base con una versión anterior del `schema.sql` (antes de julio 2026), añade el índice compuesto manualmente:

```sql
ALTER TABLE deals ADD INDEX idx_deals_client_active (client_id, deleted_at, amount);
```

---

## 9. Roles y permisos

| Acción | admin | manager | vendedor | viewer |
|---|:---:|:---:|:---:|:---:|
| Ver dashboard, negocios, clientes, kanban | ✅ | ✅ | ✅ (solo lo suyo*) | ✅ |
| Crear/editar negocios y clientes | ✅ | ✅ | ✅ (solo lo suyo*) | ❌ |
| Mover negocios en el Kanban | ✅ | ✅ | ✅ (solo los suyos) | ❌ |
| Eliminar negocios/clientes | ✅ | ✅ | ❌ | ❌ |
| Reportes y exportación | ✅ | ✅ | ❌ | ❌ |
| Gestionar etapas de venta | ✅ | ✅ | ❌ | ❌ |
| Gestionar verticales | ✅ | ❌ | ❌ | ❌ |
| Gestionar usuarios | ✅ | ❌ | ❌ | ❌ |

\* Un **vendedor** solo ve y opera los negocios donde figura como responsable (`assigned_to`). Esto se aplica en las consultas SQL y también en los endpoints de la API (no se puede saltar manipulando peticiones).

Otras reglas: un admin no puede eliminarse ni desactivarse a sí mismo; no se puede eliminar un usuario con negocios asignados, un cliente con negocios, ni una vertical/etapa con negocios dentro.

---

## 10. Módulos de la aplicación

### Dashboard (`/dashboard`)
KPIs del período (valor del pipeline, tasa de conversión, ticket promedio, días promedio de cierre), embudo por etapas (Chart.js) y próximos cierres (7 días). Filtrable por vertical y período.

### Negocios (`/deals`)
Listado paginado (20/página) con búsqueda por título/cliente/empresa y filtros por vertical, etapa y responsable. Ordenable por columnas. Ficha del negocio con historial de actividades y formulario para añadir notas/llamadas/reuniones. La eliminación es **soft delete** (recuperable vía SQL con `UPDATE deals SET deleted_at = NULL WHERE id = X`).

### Clientes (`/clients`)
CRUD con búsqueda, contador de negocios y valor de pipeline por cliente. La ficha muestra todos sus negocios.

### Kanban (`/kanban`)
Columnas = etapas de la vertical seleccionada. Arrastrar una tarjeta (SortableJS) llama a la API, actualiza la etapa y registra la actividad automáticamente. Al soltar en una etapa "ganada" se fija fecha de cierre real y probabilidad 100%; en una "perdida", probabilidad 0%.

### Reportes (`/reports`) — admin y manager
Tabla filtrable por vertical y rango de fechas. Exporta:
- **CSV** (separador `;`, UTF-8 con BOM para Excel, celdas protegidas contra inyección de fórmulas)
- **PDF** (vista imprimible: usar "Guardar como PDF" del navegador)

### Administración
- **Usuarios** (`/admin/users`): crear/editar/activar/desactivar/eliminar, asignar rol.
- **Verticales** (`/admin/verticals`): CRUD de verticales y de sus etapas, reordenamiento drag & drop, marcar etapas como ganada/perdida.

---

## 11. API interna (JSON)

Endpoints usados por el propio frontend (no es una API pública):

| Método | Ruta | Función | Protección |
|---|---|---|---|
| GET | `/api/stages?vertical_id=N` | Etapas de una vertical (para selects dependientes) | Sesión |
| GET | `/api/metrics?period=30&vertical_id=N` | KPIs del dashboard | Sesión |
| POST | `/api/deals/{id}/move` | Mover negocio de etapa (Kanban) | Sesión + CSRF + propiedad |
| POST | `/api/activities` | Registrar actividad en un negocio | Sesión + CSRF + propiedad |

Los POST requieren la cabecera `X-CSRF-TOKEN` (el helper `apiFetch()` de `app.js` la envía solo). Respuestas de error: `400` parámetros inválidos, `403` sin permiso, `419` CSRF inválido.

---

## 12. Seguridad implementada

- **Contra fuerza bruta**: máximo 5 intentos de login fallidos por IP; bloqueo de 15 minutos. Persistido en `storage/cache/` (no depende de la cookie de sesión, no se evade borrándola).
- **Contraseñas**: bcrypt costo 12. Mínimo 8 caracteres validado en servidor.
- **CSRF**: token por sesión en todos los formularios POST y en la API JSON (cabecera). Comparación con `hash_equals`.
- **XSS**: todo dato de usuario se escapa con `e()`; los valores inyectados a JavaScript pasan por `json_encode`.
- **SQL injection**: 100% consultas preparadas PDO (sin emulación); columnas de ordenamiento validadas contra lista blanca.
- **IDOR**: los endpoints verifican propiedad del recurso además del rol (un vendedor no puede operar negocios ajenos ni por API).
- **Sesión**: cookie `HttpOnly`, `SameSite=Lax`, `Secure` (detecta HTTPS incluso detrás del proxy de Hostinger vía `X-Forwarded-Proto`), `session.use_strict_mode`, regeneración de ID al iniciar sesión.
- **Cabeceras**: `Content-Security-Policy` (solo self + los 3 CDN), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`.
- **Archivos**: `.htaccess` bloquea `.env`, dotfiles, `config/`, `src/`, `views/`, `database/` y `storage/`; `_method` limitado a PUT/PATCH/DELETE; errores internos no exponen clases ni rutas (van al log).
- **CSV**: celdas que empiezan con `=`, `+`, `-`, `@` se neutralizan (inyección de fórmulas de Excel).
- **Logout**: por POST con CSRF (no se puede desloguear a alguien con un link).
- **Integridad**: no se eliminan usuarios/clientes/verticales/etapas con negocios asociados; el borrado de negocios es soft delete.

---

## 13. Rendimiento

Pensado para los límites típicos del hosting compartido (~128 MB RAM, ~30 s por petición, CPU compartida):

- **Sin N+1**: Kanban y administración de verticales cargan todo en 1 consulta y agrupan en PHP.
- **Métricas del dashboard**: 2 consultas agregadas (con `SUM(CASE WHEN...)`) en lugar de 5.
- **Todo listado tiene LIMIT**: negocios/clientes paginados (20), reportes máx. 5000 filas, selector de clientes máx. 500.
- **Caché por petición**: el usuario autenticado y la config se leen una sola vez.
- **Concurrencia AJAX**: los endpoints JSON liberan el lock de sesión (`session_write_close`) para no serializar las peticiones del Kanban.
- **Transacciones**: el reordenamiento de etapas agrupa sus UPDATEs.
- **Assets**: cabeceras de caché de 1 mes + parámetro `?v=` controlado por `ASSET_VERSION` para invalidarla al actualizar.

---

## 14. Mantenimiento y operación

### Logs
Errores PHP en `storage/logs/php_errors.log`. Revísalo ante cualquier pantalla en blanco o error 500. Puede borrarse sin problema cuando crezca (se recrea solo).

### Rate limiting
Archivos `login_*.json` en `storage/cache/`. Para desbloquear manualmente una IP, borra su archivo. Pueden borrarse todos sin efectos secundarios.

### Backups
Lo único con estado es **la base de datos** (los archivos son código reemplazable):
- Manual: phpMyAdmin → Exportar → SQL.
- Automático: hPanel → **Archivos → Copias de seguridad** (Hostinger genera semanales; verifica tu plan).

### Actualizar la aplicación
1. Sube los archivos nuevos encima de los existentes (el `.env` no se toca porque no viene en el pack).
2. Incrementa `ASSET_VERSION` en el `.env` si cambió CSS/JS.
3. Aplica los `ALTER TABLE` que indique el changelog, si los hay.

### Crear un usuario admin manualmente (emergencia)
Genera un hash en local: `php -r "echo password_hash('TuClaveSegura', PASSWORD_BCRYPT, ['cost'=>12]);"` y ejecútalo en phpMyAdmin:

```sql
INSERT INTO users (name, email, password, role, is_active, timezone, created_at, updated_at)
VALUES ('Admin', 'tu@correo.com', '<hash generado>', 'admin', 1, 'America/La_Paz', NOW(), NOW());
```

---

## 15. Solución de problemas

| Síntoma | Causa probable | Solución |
|---|---|---|
| **"Base de datos no configurada. Copia .env.example a .env..."** | No existe `.env` junto a `index.php`, se llama distinto (`env`, `.env.txt`), o `DB_DATABASE`/`DB_USERNAME` están vacíos | Activa "Mostrar archivos ocultos" en el Administrador de Archivos; verifica nombre exacto y valores con prefijo `u..._` |
| **"Error de conexión a la base de datos. Revisa las credenciales en .env"** | Credenciales incorrectas o host equivocado | Compara con hPanel → MySQL. El detalle exacto está en `storage/logs/php_errors.log` |
| **"Demasiados intentos fallidos"** | Rate limiting activado (5 fallos) | Espera 15 min o borra `storage/cache/login_*.json` |
| **No funciona la contraseña demo `password`** | Base importada con un `seed.sql` anterior a julio 2026 (traía un hash inválido) | `UPDATE users SET password = '$2y$12$v2g8L5r/mfrTLaKWr8cRCenudfyVYb7ZQmIMpcimEoH./wbzo.382' WHERE email LIKE '%@crm.test';` |
| **404 en todas las rutas menos la portada** | `.htaccess` no se subió (archivo oculto) o mod_rewrite desactivado | Verifica que `public_html/.htaccess` exista; en Hostinger rewrite está siempre activo |
| **Pantalla en blanco / error 500** | Error PHP silenciado en producción | Mira `storage/logs/php_errors.log`; temporalmente `APP_DEBUG=true` (y vuelve a `false`) |
| **Estilos rotos tras actualizar** | Caché del navegador con assets viejos | Incrementa `ASSET_VERSION` en el `.env` |
| **El seed falla al importar** | Se importó antes que el schema, o se reimportó sobre datos existentes | Orden: schema → seed. Para reimportar: vacía las tablas o recrea la base |
| **Redirecciones raras o bucle de login en subdirectorio** | `APP_URL` definida con valor incorrecto | Déjala vacía (autodetección) o pon la URL exacta incluyendo la subcarpeta, sin barra final |
| **`.env` visible por web** | `.htaccess` ausente | Vuelve a subirlo; es la primera prueba del checklist del paso 8 |

---

## 16. Limitaciones conocidas y mejoras futuras

1. **Tailwind por CDN (build de desarrollo)** — Es el mayor cuello de botella de carga (~300 KB de JS que genera estilos en el navegador). Funciona, pero para producción seria conviene compilar una vez el CSS con el CLI de Tailwind en una máquina local y servir el archivo estático desde `assets/css/`. No requiere Node en el servidor, solo una vez para generar.
2. **PDF por impresión del navegador** — El "export PDF" es una vista imprimible, no un PDF generado en servidor (evita dependencias como dompdf).
3. **Sin recuperación de contraseña por email** — El hosting compartido complica el envío fiable de correo. Un admin puede resetear contraseñas desde **Usuarios**, o vía SQL (sección 14).
4. **Sin API pública ni tokens** — La API interna es solo para el frontend (sesión + CSRF). Exponer una API REST requeriría autenticación por token.
5. **Rate limiting por IP** — Detrás de una misma oficina/NAT, un bloqueo afecta a todos los que compartan IP. Es el compromiso razonable sin infraestructura adicional.
6. **Papelera de negocios sin interfaz** — El soft delete existe, pero restaurar requiere SQL. Una vista de "papelera" sería la mejora natural.

---

*Documentación generada para el despliegue en Hostinger · Aplicación en `crm-vanilla/` · Rama `claude/pipeline-management-system-JQGgE`*

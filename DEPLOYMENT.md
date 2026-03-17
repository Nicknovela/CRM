# Despliegue en Hostinger Shared Hosting

## Requisitos del servidor

- PHP 8.2+ con extensiones: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, exif, gd, zip
- MySQL 5.7+ / MariaDB 10.3+
- SSH o acceso FTP

## Estructura en Hostinger

Hostinger expone `public_html/` como raíz web. El código Laravel debe vivir **fuera** de `public_html`.

```
~/
├── laravel/          ← todos los archivos del proyecto excepto public/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── ...
└── public_html/      ← raíz web de Hostinger
    ├── index.php     ← modificado (ver abajo)
    ├── .htaccess
    ├── build/        ← copiado desde laravel/public/build/
    └── favicon.ico
```

## Paso 1: Subir archivos

1. Subir todo el contenido del proyecto (sin `public/`) a `~/laravel/` vía FTP o Git.
2. Copiar el contenido de `public/` a `~/public_html/`.

## Paso 2: Modificar public_html/index.php

Reemplazar el contenido de `public_html/index.php`:

```php
<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/../laravel/vendor/autoload.php';

$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

$app->bind('path.public', fn() => __DIR__);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);
```

## Paso 3: Configurar .htaccess en public_html/

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

## Paso 4: Crear base de datos

1. En el panel de Hostinger → MySQL Databases → crear base de datos `crm_pipeline`.
2. Crear usuario y asignar todos los privilegios.

## Paso 5: Configurar .env de producción

En `~/laravel/.env`:

```env
APP_NAME="CRM Pipeline"
APP_ENV=production
APP_KEY=base64:...  # generar con: php artisan key:generate
APP_DEBUG=false
APP_URL=https://tudominio.com
APP_TIMEZONE=America/La_Paz
APP_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_pipeline
DB_USERNAME=usuario_db
DB_PASSWORD=contraseña_db

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=465
MAIL_USERNAME=noreply@tudominio.com
MAIL_PASSWORD=contraseña_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@tudominio.com"
MAIL_FROM_NAME="CRM Pipeline"
```

## Paso 6: Ejecutar migraciones y seeders (SSH)

```bash
cd ~/laravel
php artisan migrate --force
php artisan db:seed --force   # solo la primera vez
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan permission:cache-reset
```

## Paso 7: Permisos de directorios

```bash
chmod -R 755 ~/laravel/storage
chmod -R 755 ~/laravel/bootstrap/cache
```

## Paso 8: Compilar assets

En tu máquina local:

```bash
npm run build
```

Luego subir el contenido de `public/build/` a `~/public_html/build/` en Hostinger.

## Paso 9: Cron job (Scheduler de Laravel)

En el panel de Hostinger → Cron Jobs, agregar:

```
* * * * * php ~/laravel/artisan schedule:run >> /dev/null 2>&1
```

## Actualizaciones futuras

1. Subir nuevos archivos vía FTP/Git (excluir `vendor/`, `public/build/`, `.env`)
2. Vía SSH:
   ```bash
   cd ~/laravel
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   php artisan permission:cache-reset
   ```
3. Recompilar assets localmente y subir `public/build/` a `public_html/build/`

## Credenciales de prueba (eliminar en producción)

| Usuario | Email | Contraseña | Rol |
|---------|-------|------------|-----|
| Administrador | admin@crm.test | password | admin |
| Gerente Comercial | gerente@crm.test | password | manager |
| Carlos Méndez | vendedor@crm.test | password | vendedor |

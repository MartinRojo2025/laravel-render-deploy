# Despliegue de Laravel en Render con Docker

## ¿Por qué Docker en Render?

Render soporta múltiples formas de despliegue, pero Docker ofrece:
- Control total sobre el entorno (PHP, nginx, extensiones)
- Consistencia entre desarrollo y producción
- Ya tienes Docker configurado localmente

## Archivos Creados y Modificaciones

### 1. `Dockerfile.render`

**¿Qué hace?**
```dockerfile
FROM php:8.3-fpm
```
- Usa PHP 8.3 con PHP-FPM (FastCGI Process Manager) como base

```dockerfile
RUN apt-get update && apt-get install -y \
    nginx git curl unzip libzip-dev libpng-dev \
    && docker-php-ext-install pdo_mysql zip gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
```
- Instala nginx (servidor web) en el mismo contenedor que PHP
- Instala extensiones necesarias: `pdo_mysql` (base de datos), `zip`, `gd` (imágenes)
- Limpia cache de apt para reducir tamaño de imagen

```dockerfile
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
```
- Copia Composer desde una imagen oficial (multi-stage build)

```dockerfile
COPY src/ /var/www/html/
RUN composer install --no-dev --optimize-autoloader
```
- Copia tu código Laravel
- Instala dependencias sin paquetes de desarrollo (`--no-dev`)
- Optimiza el autoloader para mejor rendimiento

```dockerfile
COPY docker/nginx/render.conf /etc/nginx/sites-available/default
```
- Usa configuración de nginx específica para producción

```dockerfile
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
```
- Da permisos correctos a directorios que Laravel necesita escribir

```dockerfile
EXPOSE 80
CMD ["/start.sh"]
```
- Expone puerto 80 (HTTP)
- Ejecuta script de inicio

**¿Por qué un solo contenedor?**
- Render cobra por servicio. Un contenedor = más económico
- Simplifica el despliegue (no necesitas orquestar múltiples servicios)
- Suficiente para la mayoría de aplicaciones Laravel

---

### 2. `docker/nginx/render.conf`

**Diferencias con `default.conf`:**

```nginx
fastcgi_pass 127.0.0.1:9000;  # Antes: php:9000
```
- En desarrollo: nginx y PHP están en contenedores separados, usa hostname `php`
- En producción (Render): están en el mismo contenedor, usa `127.0.0.1` (localhost)

```nginx
server_name _;  # Antes: localhost
```
- `_` acepta cualquier nombre de dominio (Render te da un subdominio automático)

**¿Por qué un archivo separado?**
- Mantiene tu configuración de desarrollo intacta
- Evita conflictos entre entornos local y producción

---

### 3. `docker/scripts/start.sh`

```bash
#!/bin/bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
- **Cacheo de configuración**: Laravel carga config/routes/views en memoria en vez de leerlos del disco
- Mejora drásticamente el rendimiento en producción

```bash
php artisan migrate --force
```
- Ejecuta migraciones de base de datos automáticamente
- `--force` permite migraciones en producción sin confirmación

```bash
service nginx start
php-fpm
```
- Inicia nginx en background
- Inicia PHP-FPM en foreground (mantiene el contenedor vivo)

**¿Por qué este orden?**
- Primero optimiza Laravel, luego ejecuta migraciones, finalmente inicia servicios web
- Si las migraciones fallan, el contenedor no arranca (seguridad)

---

### 4. `render.yaml`

```yaml
services:
  - type: web
    name: mi-proyecto-laravel
    runtime: docker
    dockerfilePath: ./Dockerfile.render
```
- Define un servicio web que usa Docker
- Render buscará `Dockerfile.render` en la raíz

```yaml
envVars:
  - key: APP_KEY
    generateValue: true
```
- Variables de entorno para Laravel
- `APP_KEY`: Render genera valor automáticamente (NOTA: Necesitas reemplazarlo manualmente, ver pasos siguientes)

```yaml
  - key: DB_HOST
    fromDatabase:
      name: laravel-db
      property: host
```
- Vincula variables de entorno con la base de datos creada por Render
- Render inyecta automáticamente: host, puerto, nombre BD, usuario, contraseña

```yaml
databases:
  - name: laravel-db
    databaseName: laravel
    user: alumno
```
- Render crea una base de datos MySQL automáticamente
- Se vincula al servicio web mediante las variables de entorno

**¿Por qué render.yaml?**
- Infraestructura como código (versionable en Git)
- Despliegue automático: solo conectas el repo y Render configura todo
- Alternativa: configurar manualmente desde el dashboard (más lento y propenso a errores)

---

## Lo que te queda por hacer

### 1. Preparar el repositorio

```bash
# Asegúrate de que .env NO esté en Git
git add .
git commit -m "Configuración para despliegue en Render"
git push origin main
```

**Archivos que DEBEN estar en Git:**
- `Dockerfile.render`
- `render.yaml`
- `docker/nginx/render.conf`
- `docker/scripts/start.sh`
- Todo el código de `src/`

**Archivos que NO deben estar:**
- `.env` (contiene secretos)
- `src/vendor/` (se genera con composer)
- `src/node_modules/`

---

### 2. Generar APP_KEY

**Localmente con Docker:**
```bash
cd mi-proyecto-laravel
docker compose up -d
docker compose exec php php artisan key:generate --show
```

**Salida ejemplo:**
```
base64:fH8Kl9M3nP7qR2sT5vW8xYzA1bC4dE6fG9hJ0kL3mN6o=
```

**Guarda este valor**, lo necesitarás en Render.

---

### 3. Crear cuenta y proyecto en Render

1. Ve a https://render.com y crea cuenta (gratis)
2. Conecta tu cuenta de GitHub/GitLab
3. Click en **"New +"** → **"Blueprint"**
4. Selecciona tu repositorio `mi-proyecto-laravel`
5. Render detectará automáticamente el `render.yaml`

---

### 4. Configurar variables de entorno en Render

En el dashboard de Render, antes de desplegar:

1. Ve a tu servicio web → **Environment**
2. **Reemplaza** `APP_KEY` con el valor generado en el paso 2:
   ```
   APP_KEY=base64:fH8Kl9M3nP7qR2sT5vW8xYzA1bC4dE6fG9hJ0kL3mN6o=
   ```
3. Verifica que las variables de base de datos estén vinculadas automáticamente:
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`

**Opcional (variables adicionales recomendadas):**
```
APP_URL=https://tu-app.onrender.com
LOG_CHANNEL=stderr
SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

---

### 5. Desplegar

1. Click en **"Create Blueprint"** o **"Deploy"**
2. Render hará:
   - Crea base de datos MySQL (tarda ~2-3 minutos)
   - Construye la imagen Docker (~5-10 minutos primera vez)
   - Ejecuta el contenedor
   - Ejecuta migraciones automáticamente

3. Monitorea los logs para detectar errores

---

### 6. Verificar despliegue

1. Render te da una URL: `https://mi-proyecto-laravel.onrender.com`
2. Visita la URL y verifica que Laravel funcione
3. Revisa los logs en Render → **Logs**

---

## Troubleshooting

### Error: "Permission denied" en storage/
**Solución:** El Dockerfile ya lo maneja con `chown`, pero si persiste:
```dockerfile
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### Error: "Please provide a valid app key"
**Causa:** `APP_KEY` no configurada o inválida
**Solución:** Verifica que copiaste correctamente la key con el prefijo `base64:`

### Error: "SQLSTATE[HY000] [2002] Connection refused"
**Causa:** Base de datos no lista o variables mal configuradas
**Solución:** Espera a que la BD termine de crear, verifica variables en Environment

### El build es muy lento
**Causa:** Render descarga todo cada vez
**Optimización:** Añade layers de cache al Dockerfile:
```dockerfile
COPY src/composer.json src/composer.lock /var/www/html/
RUN composer install --no-dev --no-scripts
COPY src/ /var/www/html/
RUN composer dump-autoload --optimize
```

---

## Costos en Render

**Plan gratuito incluye:**
- 750 horas/mes de servicio web (suficiente para 1 app 24/7)
- Base de datos gratuita con límite de almacenamiento
- El servicio "duerme" después de 15 min sin tráfico (tarda ~30s en despertar)

**Plan de pago ($7/mes):**
- Sin hibernación
- Más recursos (CPU/RAM)
- Backups automáticos de BD

---

## Próximos pasos (opcional)

1. **Dominio personalizado:** Configura tu dominio en Render → Settings → Custom Domain
2. **HTTPS automático:** Render lo proporciona gratis con Let's Encrypt
3. **CI/CD:** Cada push a `main` redespliega automáticamente
4. **Monitoreo:** Integra con servicios como Sentry para rastrear errores
5. **Optimización:** Añade Redis/Memcached para cache y sesiones

---

## Resumen visual del flujo

```
┌─────────────┐
│   Git Push  │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│  Render detecta │
│  render.yaml    │
└────────┬────────┘
         │
         ├─────────────────────┐
         │                     │
         ▼                     ▼
┌────────────────┐    ┌───────────────┐
│  Crea MySQL DB │    │ Build Docker  │
│                │    │ (Dockerfile)  │
└────────┬───────┘    └───────┬───────┘
         │                    │
         │                    ▼
         │            ┌───────────────┐
         │            │ composer      │
         │            │ install       │
         │            └───────┬───────┘
         │                    │
         ▼                    ▼
┌────────────────────────────────┐
│  Variables ENV inyectadas      │
│  (DB_HOST, DB_PASSWORD, etc)   │
└────────┬───────────────────────┘
         │
         ▼
┌────────────────┐
│  start.sh      │
│  - cache       │
│  - migrate     │
│  - nginx+php   │
└────────┬───────┘
         │
         ▼
┌────────────────┐
│  🚀 App Live   │
└────────────────┘
```

¡Listo para desplegar! 🎉

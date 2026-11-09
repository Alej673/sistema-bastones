#!/bin/bash
set -e # Detiene el script si algn comando crtico falla inesperadamente

echo "=========================================="
echo "  Iniciando Despliegue DE Arte Titi_Val   "
echo "=========================================="

# 1. Asegurar que estamos en el directorio raz del proyecto
cd "$(dirname "$0")"

# 2. Respaldar archivos crticos de produccin antes de actualizar
echo "--> Respaldando configuraciones del servidor..."
cp docker-compose.yml docker-compose.yml.bak 2>/dev/null || true
cp docker/nginx/default.conf docker/nginx/default.conf.bak 2>/dev/null || true

# 3. Limpiar working tree de Git y traer la ltima versin de GitHub
echo "--> Sincronizando con rama main de GitHub..."
git fetch origin main
# Descarta diferencias temporales en archivos rastreados para evitar bloqueos
git reset --hard origin/main

# 4. Restaurar configuraciones de produccin si el repositorio traa versiones sin SSL
echo "--> Verificando configuraciones de Nginx y Docker..."
if [ -f docker-compose.yml.bak ]; then
    cp docker-compose.yml.bak docker-compose.yml
    rm -f docker-compose.yml.bak
fi
if [ -f docker/nginx/default.conf.bak ]; then
    cp docker/nginx/default.conf.bak docker/nginx/default.conf
    rm -f docker/nginx/default.conf.bak
fi

# 5. Compilar frontend (Vite)
echo "--> Instalando paquetes de Node y compilando con Vite..."
npm install --silent
npm run build

# 6. Mantenimiento del contenedor PHP / Laravel
echo "--> Actualizando dependencias de Composer..."
docker exec arte_titival-app-1 composer install --no-interaction --prefer-dist --optimize-autoloader

echo "--> Ejecutando migraciones de base de datos..."
docker exec arte_titival-app-1 php artisan migrate --force

echo "--> Asegurando enlace simblico de almacenamiento..."
docker exec arte_titival-app-1 php artisan storage:link 2>/dev/null || true

echo "--> Purgando todas las cachs de Laravel..."
docker exec arte_titival-app-1 php artisan optimize:clear
docker exec arte_titival-app-1 php artisan view:clear
docker exec arte_titival-app-1 php artisan config:clear
docker exec arte_titival-app-1 php artisan route:clear

# 7. Garantizar permisos de escritura para la subida de fotos
echo "--> Ajustando permisos de storage y cache..."
docker exec arte_titival-app-1 chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker exec arte_titival-app-1 chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Validar sintaxis de Nginx antes de reiniciar
echo "--> Probando configuración de Nginx..."
docker exec arte_titival-web-1 nginx -t

# Si pasa el test, procede al reinicio seguro
echo "--> Reiniciando contenedores web y app..."
docker restart arte_titival-app-1 arte_titival-web-1

# 8. Reiniciar contenedores para vaciar OPcache y aplicar cambios de Nginx
echo "--> Reiniciando contenedores web y app..."
docker restart arte_titival-app-1 arte_titival-web-1

echo "=========================================="
echo "  Despliegue completado con xito!       "
echo "=========================================="

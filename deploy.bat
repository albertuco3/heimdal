@echo off

echo Instalando dependencias de npm...
npm install

echo Generando los assets de producción con Symfony Encore...
npm run build

:: echo Generando los assets de producción...
:: php bin/console asset:install --env=prod
:: php bin/console assets:install --symlink --relative public
:: php bin/console assetic:dump --env=prod

echo Limpiando la caché...
php bin/console cache:clear --env=prod

echo Ejecutando migraciones de base de datos...
php bin/console doctrine:migrations:migrate --no-interaction

echo Proceso completado.
pause
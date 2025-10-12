# sigpae
Proyecto de laboratorio de software 2025

# Guia de instalación
En la consola sin comillas:
1. Escribir "cd SIGPAE" para acceder a la carpeta del proyecto
2. Escribir "cp .env.example .env" para clonar el .env de ejemplo
3. Escribir "composer install" para instalar las dependencias de PHP  Si se queda colgado en "Generating optmized autoload files" ejecutar "composer install --no-scripts". Si se hace esto, luego del paso 4 ejecutar "composer dump-autoload"
4. Escribir "php artisan key:generate" para generar la clave de seguridad. Se guarda automaticamente en APP_KEY en .env
5. Abrir .env y escribir en APP_URL: http://localhost:8000
6. En .env modificar DB_CONNECTION=sqlite por DB_CONNECTION=pgsql
7. Asegurar que se tiene instalado el postgre, entrar al postgre recordando bien el usuario y contraseña. Crear un nuevo server que sea: Nombre: Postre_Local. En host poner localhost, en puerto el default, y en usuario postgre, y en contraseña la suya de postgre.
8. Crear una database en el servidor creado (click derecho, crear database), de nombre se sugiere ponerle sigpae_bd. 
9. En el .env completar los campos usuario y contraseña por postgre y su contraseña. En DB_DATABASE pongan el nombre de la database, debería ser sigpae_bd
10. Ejecutar npm install
11. Ejecutar npm run dev, darle ctrl-c cuando se ejecute
12. Ejecutar php artisan serve
13. Instalar en addons de vsc [Blade](https://marketplace.visualstudio.com/items?itemName=amirmarmul.laravel-blade-vscode)
14. Ir a ajustes, y poner settings, en el buscador poner emmet en include language poner blade y html
#SIGPAE: Proyecto de Laboratorio de Software 2025

Bienvenido al repositorio del proyecto **SIGPAE** (Sistema Integral para Gabinete Psicopedagógico y de Asistencia Escolar)
El presente trabajo tiene como objetivo el desarrollo de una herramienta informática destinada a optimizar y facilitar la gestión de información en los gabinetes psicopedagógicos y de asistencia escolar en instituciones de nivel primario e inicial. Con esta propuesta se busca, además, favorecer el trabajo colaborativo entre los profesionales del gabinete y lograr mejoras tanto en el tiempo como en la calidad de las decisiones que se toman en el acompañamiento de los alumnos. 
El sistema ha sido concebido para adaptarse a la dinámica de trabajo de los profesionales dentro de un establecimiento educativo, organizando la información de manera clara y segura, lo que favorece la gestión de los datos y la coordinación entre los equipos de trabajo.
La elección de este tema responde a la relevancia social y educativa del acompañamiento integral de los alumnos, así como a la oportunidad de aplicar conocimientos técnicos en un contexto realista, contribuyendo a la mejora de la coordinación interdisciplinaria y la organización de la información escolar de interés.
Si bien a futuro el sistema podría evolucionar hacia un modelo multi-institución, permitiendo que distintas escuelas compartan la misma plataforma manteniendo sus datos de forma independiente, en el presente trabajo se lo abordará bajo el enfoque de una única institución con un único servidor. Esta decisión responde a la necesidad de simplificar el desarrollo inicial, concentrar los esfuerzos en la implementación de las funciones centrales y garantizar un entorno de prueba controlado antes de escalar a escenarios más complejos

---

##Requisitos

* **Git**
* **Composer** (Gestor de dependencias de PHP)
* **Node.js y npm** (para dependencias de frontend)
* **PostgreSQL** (Sistema de gestión de bases de datos)

---

## Guía de Instalación y Configuración Local

Sigue los pasos a continuación 

### I. Entorno y Dependencias de PHP

1.  **Instalar Servidor Web y PHP:**
    * Descarga e instala **XAMPP** desde [https://www.apachefriends.org/es/index.html](https://www.apachefriends.org/es/index.html). Asegúrate de que la instalación incluya **PHP**.

2.  **Acceder a la Carpeta del Proyecto:**
        ```bash
        cd SIGPAE
        ```

3.  **Configurar el Archivo de Entorno (`.env`):**
    * Copia el archivo de ejemplo para crear tu configuración local:
        ```bash
        cp .env.example .env
        ```

4.  **Instalar Dependencias de PHP (Composer):**
    * Ejecuta la instalación estándar:
        ```bash
        composer install
        ```
    *  **Solución de Problemas:** Si se queda atascado en `"Generating optimized autoload files"`, usa:
        ```bash
        composer install --no-scripts
        ```
    * Si usaste `--no-scripts`, **después del paso 5** deberás ejecutar:
        ```bash
        composer dump-autoload
        ```

5.  **Generar Clave de Seguridad de la Aplicación:**
        ```bash
        php artisan key:generate
        ```

6.  **Configuración Inicial del Archivo `.env`:**
    * Abre el archivo `.env` y edita las siguientes líneas:
        ```
        APP_URL=http://localhost:8000
        DB_CONNECTION=pgsql  # Cambiar de 'sqlite' a 'pgsql'
        ```

### II. Configuración de la Base de Datos (PostgreSQL)

7.  **Preparar PostgreSQL y pgAdmin:**
    * Asegura que **PostgreSQL** esté instalado y abre tu herramienta de administración (ej. **pgAdmin**).
    * Crea una nueva conexión/servidor (ej. **Postgre_Local**) usando `localhost` y tus credenciales (`usuario: postgres`, `contraseña: tu-contraseña`).

8.  **Crear la Base de Datos del Proyecto:**
    * Crea una nueva base de datos en el servidor configurado.
    * **Nombre Sugerido:** `sigpae_bd`

9.  **Completar la Configuración de la DB en `.env`:**
    * Completa los detalles de conexión en el archivo `.env`:
        ```
        DB_HOST=localhost
        DB_PORT=5432
        DB_DATABASE=sigpae_bd
        DB_USERNAME=postgres
        DB_PASSWORD=TU_CONTRASEÑA_POSTGRES
        ```

10. **Ejecutar Migraciones:**
    * Crea las tablas en la base de datos:
        ```bash
        php artisan migrate
        ```
    *  **Solución de Problemas de Migraciones:** Si aparecen errores sobre tablas que ya existen (`password_reset_tokens`, `sessions`, etc.), elimínalas manualmente desde pgAdmin y vuelve a ejecutar `php artisan migrate`.

### III. Dependencias y Compilación de Frontend

11. **Instalar Dependencias de Node.js (npm):**
    ```bash
    npm install
    ```

12. **Compilar Assets de Frontend (CSS/JS):**
    * Ejecuta el compilador de desarrollo y presióna `Ctrl+C` cuando termine.
        ```bash
        npm run dev
        ```

### IV. Configuración Opcional para Visual Studio Code (VSC)

13. **Instalar Extensión Blade:**
    * Instala el *addon* de VSC para soporte de sintaxis Blade: [Laravel Blade Snippets](https://marketplace.visualstudio.com/items?itemName=amirmarmul.laravel-blade-vscode).

14. **Habilitar Emmet para Blade:**
    * Ve a **Ajustes** (Settings) de VSC.
    * Busca `emmet.includeLanguages` y añade `blade` y `html` a la lista de lenguajes incluidos.

### V. Puesta en Marcha y Pruebas

15. **Ejecutar migraciones:**
    * Ejecutar en la raiz del proyecto:
        ```bash
        php artisan migrate
        ```
        ⚠️ Si aparece “la tabla ya existe”, usar migrate:fresh para borrarlas y recrearlas.
      ```bash
        php artisan migrate:fresh
        ```
    * Ejecutar el seeder institucional para poblar la base de datos:
        ```php
        php artisan db:seed
        ```
      Esto pobla la base con datos de prueba.
   * Validar datos creados: Debería devolver “Lucía González”.
      ```php
        php artisan tinker
         >>> \App\Models\Profesional::first()->persona->descripcion;
        ```

16. **Iniciar el Servidor de Desarrollo:**
    ```bash
    php artisan serve
    ```

17. **Acceder a la Aplicación:**
    * Abre tu navegador web y ve a: **http://127.0.0.1:8000/**.
    * **Credenciales de Prueba:**
        * **Usuario:** `lucia.g`
        * **Contraseña:** `segura123`

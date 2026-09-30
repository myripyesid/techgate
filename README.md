# 🚀 TechGate - Core Domain & Interface (Sprint 1)

## 📌 Descripción del Proyecto
Monolito web desarrollado en **Laravel 13** para la gestión de productos, categorías y evaluación de compatibilidad de componentes en **TechGate** (tienda de tecnología).

---

## 🛠️ Cambios e Implementaciones en este Push

### 1. 🗄️ Modelos y Base de Datos (Eloquent & Migraciones)
* **Entidades Principales:** Creados los modelos y migraciones correspondientes para:
  * `Category`: Gestión de categorías de productos.
  * `Product`: Relacionado directamente con su categoría (`belongsTo`).
  * `ProductComparator`: Lógica para comparación de características.
  * `ComponentCompatibility`: Reglas de validación y compatibilidad entre hardware/componentes.

### 2. 🎮 Controladores Web (MVC Monolítico)
* **`ProductController`**: Métodos para listado (`index`), renderizado del formulario (`create`) y almacenamiento (`store`).
* **`CategoryController`**: Métodos para gestión de categorías (`index`, `create`, `store`).
* **`ProductComparatorController` & `ComponentCompatibilityController`**: Controladores de soporte para el dominio core.

### 3. 🎨 Interfaz de Usuario (Vistas con Blade & Bootstrap 5)
* **Plantilla Base (`resources/views/layouts/app.blade.php`)**: Layout principal con barra de navegación integrada mediante CDN de Bootstrap 5.
* **Módulo de Productos (`resources/views/products/`)**:
  * `index.blade.php`: Tabla de catálogo de productos.
  * `create.blade.php`: Formulario de registro con manejo de errores y selección dinámica de categorías.
* **Módulo de Categorías (`resources/views/categories/`)**:
  * `index.blade.php`: Listado de categorías registradas.
  * `create.blade.php`: Formulario de ingreso de nuevas categorías.

### 4. 🛣️ Rutas Web (`routes/web.php`)
* Configuración de rutas resource para `products` y `categories`.
* Redirección básica de la raíz (`/`) al catálogo principal.

### 5. 🧪 Testing & Automatización
* **Pruebas Unitarias (`tests/Unit/ProductTest.php`)**:
  * Validación de la reducción de stock y manejo de stock insuficiente.
* **Pruebas de Integración (`tests/Feature/ProductWebTest.php`)**:
  * Verificación del flujo de creación de productos a través del formulario web HTTP.
* **Ajustes en `tests/Feature/ExampleTest.php`**:
  * Adaptación para validar la redirección correcta de la ruta principal.

---

## ⚡ Instrucciones de Ejecución Local

# TechGate

## Installation and Setup

Follow these steps to run TechGate locally after cloning the repository.

### 1. Clone the repository

```bash
git clone https://github.com/myripyesid/techgate.git
cd techgate
```

### 2. Install PHP dependencies

Install all Laravel dependencies using Composer:

```bash
composer install
```

### 3. Configure the environment

Create the `.env` file from the example configuration:

```bash
copy .env.example .env
```

> **Note:** On Linux/macOS, use:
>
> ```bash
> cp .env.example .env
> ```

### 4. Generate the application key

Generate Laravel's application encryption key:

```bash
php artisan key:generate
```

### 5. Clear cached configuration

Clear Laravel's configuration cache:

```bash
php artisan config:clear
```

### 6. Create the SQLite database

TechGate uses SQLite for its database.

Create the database file:

```bash
New-Item database/database.sqlite -ItemType File
```

> **Linux/macOS:**
>
> ```bash
> touch database/database.sqlite
> ```

### 7. Run database migrations

Create the database tables:

```bash
php artisan migrate
```

### 8. Populate the database

Run the database seeders to create the required initial data:

```bash
php artisan db:seed
```

### 9. Clear the application cache

Clear the application cache after configuring the database:

```bash
php artisan cache:clear
```

### 10. Start the development server

Run the Laravel development server:

```bash
php artisan serve
```

The application should now be available at:

```text
http://127.0.0.1:8000
```

---

## Complete Setup

For convenience, the complete setup sequence is:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan config:clear
New-Item database/database.sqlite -ItemType File
php artisan migrate
php artisan db:seed
php artisan cache:clear
php artisan serve
```

If you are using **Linux/macOS**, replace the Windows-specific commands with:

```bash
cp .env.example .env
touch database/database.sqlite
```

## Requirements

Before installing TechGate, make sure you have the following installed:

* PHP
* Composer
* SQLite
* Git

Make sure your PHP version is compatible with the Laravel version used by this project.

## Troubleshooting

### Database file does not exist

If Laravel reports:

```text
Database file at path [...] database.sqlite does not exist.
```

Make sure the SQLite database file has been created:

```bash
New-Item database/database.sqlite -ItemType File
```

Then run:

```bash
php artisan migrate
```

### Configuration problems

If changes to `.env` do not seem to take effect, clear the configuration cache:

```bash
php artisan config:clear
```

### Application cache problems

You can clear the application cache with:

```bash
php artisan cache:clear
```


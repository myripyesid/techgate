# TechGate — Comparador profesional + Armador de PC + Reglas de compatibilidad

Este repositorio contiene el trabajo desarrollado sobre **TechGate** (rama
`exposicion` de `myripyesid/techgate`): mejora del comparador de productos,
una nueva pestaña "Arma tu PC" y la ampliación de las reglas de compatibilidad.

> Es una **entrega de archivos**, no una copia completa del proyecto. Incluye
> solo los archivos nuevos/modificados (con su ruta original) más un paquete
> `.zip` y un parche `.diff` para aplicarlo sobre tu copia de TechGate.

---

## 📦 Cómo usar esta entrega

Tienes tres formas de incorporar los cambios a tu proyecto TechGate:

### Opción A — Copiar los archivos (recomendada)
Cada archivo está en su ruta original (`app/...`, `resources/...`, etc.).
Cópialos sobre tu proyecto TechGate respetando esas rutas, sobrescribiendo
los existentes. Los nuevos se crearán solos.

### Opción B — Aplicar el parche
Desde la raíz de tu proyecto TechGate (rama `exposicion`):

```bash
git apply _entrega/techgate-comparador-armador.diff
```

### Opción C — Descomprimir el ZIP
```bash
unzip _entrega/techgate-comparador-armador.zip -d /ruta/a/techgate
```

### Después de aplicar los cambios (en cualquier opción)
```bash
php artisan migrate:fresh --seed   # necesario: recarga el catálogo con la longitud de las GPU
php artisan serve
```
Entra como **usuario@techgate.com / usuario1234** → verás **Comparador** (con
gráficos) y **🧩 Arma tu PC** en el menú.

---

## 🗂️ Archivos incluidos

### Nuevos
| Archivo | Qué es |
|---|---|
| `app/Services/PerformanceScoreService.php` | Índice de rendimiento estimado (0–100) ponderado por las specs del catálogo. |
| `app/Services/PcBuilderService.php` | Lógica del armador de PC (slots, compatibilidad par a par, consumo, total). |
| `app/Http/Controllers/PcBuilderController.php` | Pestaña "Arma tu PC", evaluación en vivo (AJAX) y "agregar todo al carrito". |
| `resources/views/builder/index.blade.php` | Vista del armador. |
| `tests/Feature/PcBuilderFlowTest.php` | Pruebas del flujo del armador y los gráficos del comparador. |

### Modificados
| Archivo | Cambio |
|---|---|
| `app/Services/CompatibilityService.php` | **Reglas de compatibilidad ampliadas** (ver abajo). |
| `app/Http/Controllers/ProductComparatorController.php` | Inyecta el índice de rendimiento para los gráficos. |
| `app/Providers/AppServiceProvider.php` | Helper de iconos por categoría, compartido con las vistas. |
| `config/catalogo.php` | Nueva spec `longitud_mm` en Tarjetas gráficas. |
| `database/seeders/ProductSeeder.php` | Stock de demostración (≥10) para que el carrito/armador operen. |
| `database/seeders/data/catalogo.json` | Longitud real (`longitud_mm`) de las 14 GPU. |
| `public/css/techgate.css` | Estilos del comparador y del armador (paleta TechGate). |
| `resources/views/comparator/index.blade.php` | Comparador rediseñado + gráficos Chart.js. |
| `resources/views/layouts/app.blade.php` | Enlace "Arma tu PC" en el menú. |
| `routes/web.php` | Rutas del armador. |
| `tests/Unit/CompatibilityServiceTest.php` | 9 pruebas nuevas de las reglas ampliadas. |

---

## ⚙️ Qué se desarrolló

### 1. Comparador profesional (con gráficos)
- Índice de rendimiento **estimado 0–100** ponderado con los pesos de
  `config/catalogo.php` (no es un benchmark real; está etiquetado como tal).
- Tres gráficos con **Chart.js** (CDN): barras de índice global, radar por
  característica y barras por especificación.
- Cabecera "versus", veredicto y tabla detallada rediseñados.

### 2. Nueva pestaña "Arma tu PC"
- Slots: Procesador, Placa (chipset), RAM, GPU, Almacenamiento, Fuente,
  Refrigeración y Gabinete.
- **Compatibilidad en vivo** (AJAX) con semáforo ✅/⚠️/❌ por cada par.
- **Medidor de consumo** del equipo vs. la fuente.
- Botón **"Agregar todo al carrito"** (agrega el build completo de una vez).

### 3. Reglas de compatibilidad ampliadas
Se pasó de **11 a 13 pares** con regla técnica específica. Nuevas reglas:

| Regla | Valida |
|---|---|
| `gpu \| gabinete` | Longitud de la gráfica vs. espacio del gabinete (antes estaba inactiva por falta de datos; se añadió la longitud real a cada GPU). |
| `cpu \| gpu` | Generación PCIe de la CPU frente a la de la GPU. |
| `cpu \| fuente` | Consumo del procesador + margen vs. potencia de la fuente. |
| `chipset \| fuente` | Recomienda fuente ATX 3.x en plataformas PCIe 5.0. |
| `cpu \| almacenamiento` | SSD NVMe PCIe 5.0 vs. carriles PCIe del procesador. |

Los pares sin una restricción técnica real (p. ej. `ram | gpu`) se mantienen
como "compatibles" a propósito, sin inventar incompatibilidades.

---

## ✅ Pruebas
`php artisan test` → **81 pruebas, 216 aserciones, todas en verde**
(68 originales + 13 nuevas).


---

# Entregable #2 — Arquitectura MVC + Servicios + DI + Docker

Implementación de los requisitos técnicos del entregable #2 (se omiten las
correcciones del docente y los diagramas, según lo acordado).

## 1. Dos idiomas (i18n con LANG)
- Español (por defecto) e Inglés. Archivos en `lang/es/messages.php` y `lang/en/messages.php`.
- Middleware `App\Http\Middleware\SetLocale` aplica el idioma guardado en sesión.
- Selector **ES | EN** en la barra superior (ruta `/idioma/{locale}`).
- Ningún texto quemado en las vistas principales: todo pasa por `__('messages.*')`.

## 2. Servicio web propio (JSON)
- `GET /api/productos` → lista de productos en stock a la venta, con el enlace
  directo a la ficha de cada producto (`url`). Listo para que otro equipo lo consuma.
- Controlador: `App\Http\Controllers\Api\ProductApiController`.

## 3. Consumo del servicio del equipo precedente
- Ruta `/productos-aliados` (`App\Http\Controllers\AlliedProductController`).
- Configura la URL del equipo aliado en `ALLIED_PRODUCTS_URL` (archivo `.env`).
  Si queda vacía, consume el propio `/api/productos` como demostración.

## 4. Consumo de servicio de tercero
- Clima de Medellín vía **Open-Meteo** (gratis, sin API key), mostrado en la
  cabecera. Servicio: `App\Services\WeatherService` (con caché de 15 min y
  degradación elegante si el servicio no responde).

## 5. Inversión de dependencias (DI)
- Interfaz `App\Services\Reports\ReportGenerator` con **dos** implementaciones:
  `CsvReportGenerator` y `PdfReportGenerator`.
- Binding por defecto en `AppServiceProvider`. Descarga: `/reportes/catalogo/{pdf|csv}`.

## 6. Dos pruebas unitarias
- `tests/Feature/Entregable2Test.php` (servicio JSON, consumo aliado, idioma, DI).
- Suite completa: `php artisan test` → **86 pruebas en verde**.

## 7. Docker
- `Dockerfile` (PHP 8.4 + Apache), `docker-compose.yml` y `docker/entrypoint.sh`.
- Local: `docker compose up --build` → la app queda en `http://localhost:8080`.
- Para GCP: construir y subir esta imagen (Cloud Run / Compute Engine).

## 8. Usabilidad
- Estructura visual consistente (mismo layout, colores y tipografía en todas las vistas).
- Formularios con `old()` → no se vacían al fallar la validación.
- Menú principal, menú de usuario desplegable y pie de página accesibles.

## Puesta en marcha local (sin Docker)
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

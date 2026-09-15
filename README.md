# Sistema ERP & Cotizador BTO — Taller Arte Titi_Val

Plataforma web integral bajo modelo Built-to-Order (BTO) para la gestión operativa y manufactura a medida de bastones institucionales y manualidades. Centraliza desde el catálogo público de captación y el cotizador reactivo hasta el control de inventario (Kardex) y el módulo de despacho.

---

## 📖 Contexto y Reto de Negocio

El taller operaba con estimaciones empíricas de insumos (lana, elásticos, bases pre-cortadas), sin trazabilidad de mermas ni control digital de inventario. Esto derivaba en paros imprevistos de producción y presupuestos inexactos.

**Solución:** Un sistema web desacoplado en dos capas:

- **Capa Pública / BTO:** Catálogo dinámico que genera prospectos sin exponer la lógica de costos ni los márgenes del taller.
- **Capa ERP Interna:** Panel administrativo con motor de cálculo reactivo, control de inventario en unidades mínimas de consumo (gramos/unidades) y módulo de despacho con tolerancia a fallos.

---

## 🛠️ Stack Tecnológico

- **Backend:** PHP 8.x / Laravel 11 (MVC, Eloquent ORM)
- **Base de Datos:** MySQL (InnoDB, transacciones ACID, Soft Deletes)
- **Frontend:** Blade, JavaScript ES6 modular (Vite), Bootstrap 5
- **Componentes UI:** Select2 (AJAX), SweetAlert2, Fetch API
- **Utilidades:** barryvdh/laravel-dompdf (PDFs en RAM), Compressor.js (optimización de imágenes en cliente)
- **Seguridad:** Google Socialite (OAuth 2.0), Google reCAPTCHA v3

---

## 🏗️ Decisiones Arquitectónicas Clave

### 1. Desacoplamiento MRP
El cotizador y el guardado de pedidos operan como una reserva matemática. El descuento físico en bodega solo se ejecuta de forma transaccional (`DB::beginTransaction`) cuando el pedido pasa a estado "Realizado/Despachado".

### 2. Doble Perímetro de Seguridad
Aislamiento total mediante middlewares por rol (`super_admin`, `admin`, `cliente`). Los clientes acceden a su historial de proformas sin acceso a endpoints internos de costos, inventario o fórmulas de producción.

### 3. Despacho Resiliente (Deuda de Inventario)
Algoritmo de búsqueda inteligente (`LIKE` y mapeo por categoría) que tolera variaciones de nomenclatura. Si el stock físico es insuficiente, el sistema aplica un modelo *Soft Fail*: asume saldos negativos controlados y emite alertas visuales sin frenar la cadena logística.

### 4. Modularización Frontend (SoC)
El formulario de cotización se refactorizó de un archivo monolítico a módulos ES6 independientes (`modulo_lana.js`, `modulo_cortinas.js`, etc.) orquestados por un script principal y optimizados con Vite.

---

## 🧩 Módulos del Sistema

| Módulo | Responsabilidad |
|--------|-----------------|
| **Kardex** | Registro continuo de entradas/salidas en unidad mínima. Motor traductor visual (ej. madejas/rollos en UI, gramos/metros en BD). |
| **Cotizador** | Motor de cálculo reactivo con costeo fraccional, reglas de negocio dinámicas (Cost-Plus) y blindaje contra *race conditions*. |
| **Puente BTO** | Sincronización transaccional bidireccional entre solicitudes web (`quote_requests`) y órdenes de producción (`pedidos`). |
| **Ventas y Despacho** | Vista rápida asíncrona, trazabilidad histórica y ejecución del algoritmo de descuento cruzado de inventario. |
| **Catálogo y Reseñas** | Landing page B2C, carga asíncrona de recursos, sistema de calificación AJAX e integración directa con API de WhatsApp. |
| **Configuración Global** | Panel SuperAdmin basado en diccionario clave-valor para modificar márgenes, precios base y datos de contacto sin tocar código fuente. |

---

## 📸 Capturas de Pantalla

| Kardex / Inventario | Cotizador Reactivo |
| :---: | :---: |
| *Trazabilidad de insumos en tiempo real* | *Cálculo de costos con costeo fraccional* |
| ![Kardex](InventarioKardex.png) | ![Cotizador](Calculadora.png) |

| Panel de Control | Catálogo Público BTO |
| :---: | :---: |
| *KPIs, centro de alertas y gestión de ventas* | *Vitrinas interactivas y solicitudes de cotización* |
| ![Dashboard](GestionVentas.png) | ![Catalogo](Catalogo.png) |

---

## 🚀 Instalación Local

Clona el repositorio e instala las dependencias:

```bash
git clone https://github.com/Alej673/sistema-bastones.git
cd sistema-bastones
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configura tu base de datos en el archivo `.env`. De forma opcional pero recomendada, configura también tus credenciales de `MAIL_` (Mailtrap), `GOOGLE_CLIENT_ID` (OAuth) y `RECAPTCHA_SITE_KEY`.

Luego, crea el enlace simbólico de almacenamiento, migra la base de datos y compila los assets:

```bash
# Enlace simbólico obligatorio para visualizar las imágenes del catálogo
php artisan storage:link

# Migrar y poblar la base de datos
php artisan migrate --seed

# Compilar los assets del frontend para producción
npm run build

# Iniciar el servidor local
php artisan serve
```
---

### 🔑 Credenciales de Acceso (Demo Local)

Al ejecutar las migraciones con `--seed`, el sistema genera automáticamente dos cuentas de prueba para evaluar los distintos perímetros de seguridad:

**1. Super Administrador (Acceso total y Configuración Global)**
- **Correo:** `admin@demo.com`
- **Contraseña:** `admin123`

**2. Administrador de Taller (Kardex, Cotizador y Despachos)**
- **Correo:** `taller@demo.com`
- **Contraseña:** `admin123`

*(Para registrar una cuenta con rol de "Cliente", puedes utilizar el flujo de registro normal en la pantalla de inicio).*

## 🔗 Enlaces del Proyecto

- **Repositorio:** [github.com/Alej673/sistema-bastones](https://github.com/Alej673/sistema-bastones)
- **Video Demostración Técnica:** [Ver en YouTube (Arquitectura y Módulos)](https://www.youtube.com/watch?v=OHmGes--sms)
- **Manual / Guía de Usuario:** [Enlace Drive](https://drive.google.com/file/d/1ENVcdZkvP_v1N2SrP-pfbQPsirlW1aK3/view?usp=sharing)
))_

---

**Autor:** Alejandro Larco
[LinkedIn](https://www.linkedin.com/in/alejandro-larco-03297b42a/) · [GitHub](https://github.com/Alej673)

Proyecto de Integración Curricular (PTIC) — Titulación en Desarrollo de Software.

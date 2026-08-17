# Sistema Integrado ERP y Cotizador BTO — Taller de Bastones[cite: 1]

## Descripción del Proyecto
Aplicativo web integral diseñado bajo el modelo Built-to-Order (BTO) para la manufactura a medida[cite: 1]. El sistema evolucionó desde una herramienta interna de control de inventarios hacia una plataforma web bidireccional[cite: 1]. Conecta un catálogo público interactivo y un portal de clientes con un motor de cotización avanzado, permitiendo captar prospectos sin exponer los márgenes internos del taller[cite: 1].

## Tecnologías Utilizadas
* **Backend:** Laravel (PHP) implementando el patrón MVC y Eloquent ORM[cite: 1].
* **Base de Datos:** MySQL (InnoDB) con aplicación estricta de transacciones atómicas[cite: 1].
* **Frontend:** Interfaces Blade con lógica en JavaScript ES6 modular orquestado por Vite[cite: 1].
* **Librerías UI/UX:** Bootstrap 5, jQuery, Select2 para búsquedas asíncronas y SweetAlert2[cite: 1].
* **Seguridad:** Autenticación vía Laravel Breeze con un middleware de doble perímetro basado en roles[cite: 1].
* **Utilidades:** `barryvdh/laravel-dompdf` para renderizado de documentos en memoria y Compressor.js para optimización de imágenes en el cliente[cite: 1].

## Arquitectura y Lógica de Negocio
* **Arquitectura MRP (Material Requirements Planning):** Mantiene una separación estricta entre la cotización y la afectación del inventario[cite: 1]. Los pedidos en estado "pendiente" realizan reservas matemáticas, y el stock físico solo se descuenta al momento del despacho[cite: 1].
* **Doble Perímetro de Seguridad:** El sistema aísla por completo la capa pública de la administrativa[cite: 1]. El motor de costos reales jamás se ejecuta en el navegador del usuario final[cite: 1].
* **Tolerancia a Fallos Operativos:** Implementación de "Deuda de Inventario", permitiendo saldos negativos controlados durante el despacho para garantizar la continuidad operativa sin bloqueos del sistema[cite: 1].

## Módulos Principales
* **Kardex de Inventarios:** Trazabilidad en tiempo real utilizando la unidad mínima de consumo y resguardado por borrados lógicos (Soft Deletes)[cite: 1].
* **Cotizador Automático:** Motor reactivo en JavaScript que aplica algoritmos de costeo fraccional y reglas de mayoreo en milisegundos[cite: 1].
* **Puente de Sincronización BTO:** Conecta las solicitudes web de los prospectos con los pedidos del ERP interno de forma transaccional, unificando estados y precios[cite: 1].
* **Panel de Control:** Dashboard asíncrono con KPIs de rendimiento financiero y un centro interactivo para la resolución de alertas de stock[cite: 1].
* **Plataforma Pública:** Catálogo dinámico con sistema AJAX de reseñas y portal de autoservicio para el seguimiento de pedidos y descarga de proformas[cite: 1].

## Logros Técnicos Destacados
* Refactorización de interfaces monolíticas hacia módulos ES6 independientes con separación de responsabilidades (SoC)[cite: 1].
* Desarrollo de motores de búsqueda inteligente con tolerancia a variaciones tipográficas en el módulo de despacho[cite: 1].
* Blindaje contra condiciones de carrera (race conditions) en el frontend para evitar solicitudes duplicadas[cite: 1].

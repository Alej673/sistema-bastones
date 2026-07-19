import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0', // Permite conexiones externas en la red local
        port: 5173,
    },

    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/estilos.css',
                'resources/css/inventario.css',
                'resources/js/inventario.js',
                'resources/css/cotizador.css',
                'resources/css/ventas.css',
                'resources/js/cotizador.js',      // ¡Agregado! (Muy importante para que funcione tu cotizador)
                'resources/css/estilos_nav.css', 
                'resources/js/historial.js',      // ¡Agregado! (Muy importante para que funcione tu historial)
            ],
            refresh: true,
        }),
    ],
});
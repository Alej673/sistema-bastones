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
                'resources/css/inventario.css',
                'resources/js/inventario.js',
                'resources/css/cotizador.css',
                'resources/css/ventas.css',
                'resources/js/cotizador.js',   
                'resources/css/estilos_nav.css', 
                'resources/js/historial.js',
                'resources/css/inico.css',
                'resources/css/formulario.css',
                'resources/css/welcome.css',
                'resources/css/auth-neumorphism.css',
                'resources/css/variables.css',
                'resources/js/catalogo.js',
                'resources/js/inventario.js',
                'resources/js/cotizador_rapido.js',
                'resources/js/formulario.js',
                'resources/css/gestion_usuarios.css',
                'resources/js/gestion_usuarios.js',
            ],
            refresh: true,
        }),
    ],
});
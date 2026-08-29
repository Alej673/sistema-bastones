import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '10s', target: 5 },  // Aumenta a 5 usuarios concurrentes
    { duration: '20s', target: 5 },  // Mantiene la carga
    { duration: '10s', target: 0 },  // Termina la prueba
  ],
  thresholds: {
    // El 95% de las páginas deben cargar en menos de 800ms
    http_req_duration: ['p(95)<800'],
  },
};

const BASE_URL = 'http://127.0.0.1:8000';

export default function () {
  // 1. Visitar la Landing Page (Carrusel, Destacados, etc.)
  const resInicio = http.get(`${BASE_URL}/`);
  
  check(resInicio, {
    'Inicio cargó 200': (r) => r.status === 200,
  });

  // Extraer el Token CSRF del HTML (ajusta la regex si tu meta tag es diferente)
  const csrfMatch = resInicio.body.match(/<meta name="csrf-token" content="([^"]+)"/);
  const csrfToken = csrfMatch ? csrfMatch[1] : null;

  sleep(2); // El usuario simula leer la página por 2 segundos

  // 2. Visitar el Catálogo Público
  const resCatalogo = http.get(`${BASE_URL}/catalogo`);
  check(resCatalogo, {
    'Catálogo cargó 200': (r) => r.status === 200,
  });

  sleep(2);

  // 3. Simular una consulta de producto (Ruta POST)
  if (csrfToken) {
    const payload = Object.assign({
      _token: csrfToken,
      mensaje: 'Hola, me interesa este modelo. ¿Tienen disponibilidad?',
      // Añade aquí otros campos requeridos por tu CatalogController@registrarConsulta
    });

    // Simulamos consultar el producto con ID 1
    const resConsulta = http.post(`${BASE_URL}/productos/1/consultar`, payload);
    
    check(resConsulta, {
      // Laravel suele devolver 302 (Redirect) tras un POST exitoso usando web.php
      'Consulta enviada (200 o 302)': (r) => r.status === 200 || r.status === 302,
    });
  }
}
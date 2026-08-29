import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '10s', target: 5 },  // 5 administradores usando el ERP
    { duration: '20s', target: 5 },  // Carga sostenida
    { duration: '10s', target: 0 },
  ],
  thresholds: {
    // Las operaciones internas deben responder en menos de 500ms
    http_req_duration: ['p(95)<500'],
  },
};

const BASE_URL = 'http://127.0.0.1:8000';

export function setup() {
  const resLoginView = http.get(`${BASE_URL}/login`);
  
  // CORRECCIÓN: Buscamos el input oculto generado por @csrf en Blade
  const csrfMatch = resLoginView.body.match(/name="_token" value="([^"]+)"/);
  const csrfToken = csrfMatch ? csrfMatch[1] : null;

  if (!csrfToken) {
    throw new Error('No se pudo obtener el token CSRF. Verifica que la página de login carga correctamente.');
  }

  const resLogin = http.post(`${BASE_URL}/login`, {
    _token: csrfToken,
    email: 'admin@demo.com',
    password: 'admin123',
  });

  // Retornamos las cookies de sesión para que los VUs las usen
  return { cookies: resLogin.cookies };
}

export default function (data) {
  // Adjuntamos las cookies de la sesión del admin a todas las peticiones
  const params = {
    cookies: data.cookies,
  };

  // 1. Dashboard
  const resDashboard = http.get(`${BASE_URL}/inicio`, params);
  check(resDashboard, { 'Dashboard cargó 200': (r) => r.status === 200 });
  sleep(2); 

  // 2. Kardex
  const resKardex = http.get(`${BASE_URL}/insumos`, params);
  check(resKardex, { 'Kardex cargó 200': (r) => r.status === 200 });
  sleep(2); 

  // 3. Búsqueda Select2 (AJAX)
  const resAjax = http.get(`${BASE_URL}/buscar-cintas?term=satin`, {
    cookies: data.cookies,
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  });
  check(resAjax, { 'Select2 AJAX respondió 200': (r) => r.status === 200 });
  sleep(3); 
}
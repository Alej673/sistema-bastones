import http from 'k6/http';
import { check, sleep } from 'k6';

// Configuración de la carga
export const options = {
  stages: [
    { duration: '15s', target: 10 }, // Sube a 10 usuarios concurrentes
    { duration: '30s', target: 10 }, // Mantiene la carga
    { duration: '15s', target: 0 },  // Baja a 0 usuarios
  ],
  thresholds: {
    // El 95% de las peticiones deben responder en menos de 500ms
    http_req_duration: ['p(95)<500'], 
  },
};

// URL base de tu proyecto Laravel local
const BASE_URL = 'http://127.0.0.1:8000/api';

export default function () {
  // Headers necesarios para que Laravel entienda que es una petición JSON
  const params = {
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      // 'Authorization': 'Bearer TU_TOKEN_DE_SANCTUM' // Descomenta si requieres auth
    },
  };

  // 1. Simular la consulta del inventario general
  const resKardex = http.get(`${BASE_URL}/kardex`, params);
  
  check(resKardex, {
    'GET kardex status es 200': (r) => r.status === 200,
    'tiempo kardex < 300ms': (r) => r.timings.duration < 300,
  });

  sleep(1); // Tiempo de lectura del usuario antes de la siguiente acción

  // 2. Simular el registro de una cotización o salida de inventario
  const payload = JSON.stringify({
    articulo_id: 3,
    cantidad: 15,
    tipo: 'cotizacion',
    notas: 'Prueba de carga con k6'
  });

  const resPost = http.post(`${BASE_URL}/cotizaciones`, payload, params);
  
  check(resPost, {
    'POST cotización exitoso (200 o 201)': (r) => r.status === 200 || r.status === 201,
  });

  sleep(1);
}
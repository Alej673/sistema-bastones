import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '15s', target: 20 },   // Fase 1: Calentamiento (20 usuarios)
    { duration: '30s', target: 100 },  // Fase 2: Estrés agresivo (Sube a 100 usuarios)
    { duration: '30s', target: 200 },  // Fase 3: Pico extremo de sobresaturación (200 usuarios)
    { duration: '20s', target: 0 },    // Fase 4: Recuperación (Baja a 0 para ver si el servidor sobrevive)
  ],
};

const BASE_URL = 'http://127.0.0.1:8000';

export default function () {
  // 1. Visitantes entran masivamente a la Landing Page (carga de carrusel y destacados)
  const resInicio = http.get(`${BASE_URL}/`);
  check(resInicio, { 'Inicio respondió 200': (r) => r.status === 200 });
  
  sleep(1); // Simula el tiempo que el usuario tarda en hacer clic al catálogo

  // 2. Entran al Catálogo Público (Consulta fuerte a la base de datos)
  const resCatalogo = http.get(`${BASE_URL}/catalogo`);
  check(resCatalogo, { 'Catálogo respondió 200': (r) => r.status === 200 });
  
  sleep(2); // Revisan los productos

  // 3. Aplican un filtro por categoría (fuerza una consulta WHERE en Eloquent)
  const resFiltro = http.get(`${BASE_URL}/catalogo?categoria=base_baston`);
  check(resFiltro, { 'Filtro respondió 200': (r) => r.status === 200 });
  
  sleep(1);
}z
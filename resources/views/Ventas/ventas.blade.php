@extends('layouts.admin')

@push('css')
    @vite(['resources/css/ventas.css'])
@endpush

@section('contenido')
<div class="container-fluid py-4" style="background-color: #1b0f28; min-height: 100vh; color: #f5eaff;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-accent">Historial de Ventas y Cotizaciones</h2>
        <span class="badge" style="background-color: #5b21b6;">Módulo Operativo</span>
    </div>

    @include('Ventas.partials._kpis')
    @include('Ventas.partials._filtros')
    @include('Ventas.partials._tabla')

</div>
@endsection

{{-- @section('scripts')
    @vite(['resources/js/historial.js'])
@endsection --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta de Producción - Pedido #{{ $pedido->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .encabezado {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .encabezado h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .datos-pedido {
            width: 100%;
            margin-bottom: 20px;
        }
        .datos-pedido td {
            padding: 5px 0;
        }
        .tabla-materiales {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .tabla-materiales th, .tabla-materiales td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }
        .tabla-materiales th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .badge-traduccion {
            font-weight: bold;
            color: #000;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="encabezado">
        <h1>Receta de Producción (Bodega)</h1>
        <p style="margin-top: 5px; font-size: 16px;"><strong>Orden de Ensamblaje #{{ $pedido->id }}</strong></p>
    </div>

    <table class="datos-pedido">
        <tr>
            <td><strong>Fecha de Registro:</strong> {{ $pedido->created_at->format('d/m/Y H:i') }}</td>
            <td style="text-align: right;"><strong>Total de Bastones a Fabricar:</strong> <span style="font-size: 18px; font-weight: bold;">{{ $pedido->cantidad_total_bastones }} unidades</span></td>
        </tr>
    </table>

    <table class="tabla-materiales">
        <thead>
            <tr>
                <th style="width: 60%;">Material Requerido</th>
                <th style="width: 40%;">Cantidad Física Neta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->materiales as $mat)
                <tr>
                    <td>{{ $mat->nombre_material }}</td>
                    <td>
                        {{-- Lógica de Abstracción Gramática para el taller --}}
                        @if(stripos($mat->nombre_material, 'lana') !== false)
                            
                            {{-- Si es lana, mostramos los gramos exactos y la equivalencia comercial en madejas --}}
                            {{ number_format($mat->cantidad_requerida, 1) }} g 
                            <br>
                            <span class="badge-traduccion">(&approx; {{ ceil($mat->cantidad_requerida / 100) }} Madejas)</span>
                            
                        @elseif(stripos($mat->nombre_material, 'elástico') !== false || stripos($mat->nombre_material, 'elastico') !== false)
                            
                            {{-- Si es elástico, mostramos en metros --}}
                            {{ number_format($mat->cantidad_requerida, 2) }} metros
                            
                        @else
                            
                            {{-- Para bases, cinchos, apliques y cortinas, son unidades enteras --}}
                            {{ round($mat->cantidad_requerida) }} unidades
                            
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Documento de uso interno del Taller de Bastones. Los detalles financieros han sido omitidos por seguridad.
    </div>

</body>
</html>
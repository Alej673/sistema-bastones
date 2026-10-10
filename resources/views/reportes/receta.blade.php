<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta de Producción - Pedido #{{ $pedido->id }}</title>
    <style>
        @page { margin: 26px 32px; }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            color: #34303d;
            margin: 0;
            padding: 0;
        }

        table.layout {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        table.layout td { vertical-align: top; padding: 0; }

        /* Paleta: morado #7b3fa0 | oscuro #4a2565 | lila #f5effa | borde #e3d3ef | gris #7a7585 */

        /* ===== HEADER ===== */
        .header-banda {
            background-color: #f5effa;
            border: 1px solid #e3d3ef;
            border-radius: 18px;
            padding: 14px 22px;
        }
        table.layout td.col-logo { width: 95px; vertical-align: middle; }
        table.layout td.col-titulo { text-align: right; vertical-align: middle; }

        .logo {
            width: 76px;
            height: 76px;
            border-radius: 38px;
            border: 3px solid #ffffff;
        }
        .titulo {
            margin: 0;
            font-size: 21px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #4a2565;
        }
        .subtitulo {
            margin: 4px 0 0 0;
            font-size: 10px;
            color: #7a7585;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .pildora {
            display: inline-block;
            margin-top: 9px;
            padding: 4px 16px;
            background-color: #7b3fa0;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            border-radius: 12px;
        }

        .espacio { height: 16px; font-size: 1px; line-height: 16px; }

        /* ===== TARJETA DE MATERIALES ===== */
        .tarjeta {
            border: 1px solid #e3d3ef;
            border-radius: 14px;
            background-color: #ffffff;
        }
        .tarjeta-titulo {
            background-color: #4a2565;
            color: #ffffff;
            padding: 9px 16px;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: bold;
            border-radius: 13px 13px 0 0;
        }

        table.materiales {
            width: 100%;
            border-collapse: collapse;
        }
        table.materiales th {
            background-color: #f5effa;
            color: #4a2565;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: left;
            padding: 9px 10px;
            border-bottom: 1px solid #e3d3ef;
        }
        table.materiales td {
            padding: 10px;
            font-size: 11.5px;
            color: #2b2733;
            border-bottom: 1px solid #f0e8f6;
            vertical-align: middle;
        }
        table.materiales tr.alterna td { background-color: #fcfaff; }
        .nombre { font-weight: bold; }
        .subtotal { font-weight: bold; color: #4a2565; }

        /* Estados */
        .estado {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        .estado-ok { background-color: #e3f6ea; color: #1c7a3f; }
        .estado-falta { background-color: #fde6e6; color: #c0392b; }

        /* ===== RESUMEN FINANCIERO ===== */
        .resumen-card {
            border: 1px solid #e3d3ef;
            border-radius: 14px;
            background-color: #faf6fd;
        }
        table.layout td.fila {
            padding: 10px 18px;
            font-size: 11.5px;
            vertical-align: middle;
            border-bottom: 1px solid #eadff3;
        }
        .lbl { color: #7a7585; }
        .val { text-align: right; font-weight: bold; color: #2b2733; }
        .fila-total {
            background-color: #7b3fa0;
            border-radius: 0 0 13px 13px;
            padding: 13px 18px;
        }
        table.layout td.total-texto {
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            vertical-align: middle;
        }
        table.layout td.total-monto {
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            vertical-align: middle;
        }

        .footer {
            margin-top: 18px;
            text-align: center;
            font-size: 9.5px;
            color: #9a94a6;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <!-- ===== HEADER ===== -->
    <div class="header-banda">
        <table class="layout">
            <tr>
                <td class="col-logo">
                    <img src="{{ public_path('image/logo.png') }}" class="logo" alt="Arte Titi_Val">
                </td>
                <td class="col-titulo">
                    <h1 class="titulo">Receta de Producción</h1>
                    <p class="subtitulo">Bodega · Orden de Ensamblaje</p>
                    <span class="pildora">N.º {{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="espacio">&nbsp;</div>

    <!-- ===== MATERIALES ===== -->
    <div class="tarjeta">
        <div class="tarjeta-titulo">Material Requerido</div>
        <table class="materiales">
            <thead>
                <tr>
                    <th style="width: 30%;">Material</th>
                    <th style="width: 13%;">Costo unit.</th>
                    <th style="width: 13%;">Subtotal</th>
                    <th style="width: 13%;">Cant. neta</th>
                    <th style="width: 11%;">Stock</th>
                    <th style="width: 20%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 0; @endphp
                @foreach($pedido->materiales as $mat)
                    @if($mat->es_diseno)
                        @continue
                    @endif
                    <tr class="{{ $i % 2 == 1 ? 'alterna' : '' }}">
                        <td class="nombre">{{ $mat->nombre_material }}</td>
                        <td>{{ $mat->precio_unitario_visual }}</td>
                        <td class="subtotal">{{ $mat->subtotal_visual }}</td>
                        <td>{{ $mat->requerido_visual }}</td>
                        <td>{{ $mat->stock_visual }}</td>
                        <td>
                            @if($mat->falta_comprar_num > 0)
                                <span class="estado estado-falta">Faltan {{ $mat->falta_visual }} · Comprar</span>
                            @else
                                <span class="estado estado-ok">Stock completo</span>
                            @endif
                        </td>
                    </tr>
                    @php $i++; @endphp
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="espacio">&nbsp;</div>

    <!-- ===== RESUMEN FINANCIERO (alineado a la derecha) ===== -->
    <table class="layout">
        <tr>
            <td style="width: 45%;"></td>
            <td style="width: 55%; padding-left: 7px;">
                <div class="resumen-card">
                    <table class="layout">
                        <tr>
                            <td class="fila lbl">Costo base insumos + extras</td>
                            <td class="fila val">${{ number_format($costoTotalMateriales, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fila lbl">Mano de obra (ganancia base)</td>
                            <td class="fila val">${{ number_format($costoManoObra, 2) }}</td>
                        </tr>
                        @if($costoDisenoPersonalizado > 0)
                        <tr>
                            <td class="fila lbl">Diseño personalizado (extra)</td>
                            <td class="fila val">${{ number_format($costoDisenoPersonalizado, 2) }}</td>
                        </tr>
                        @endif
                    </table>
                    <div class="fila-total">
                        <table class="layout">
                            <tr>
                                <td class="total-texto">Costo total producción</td>
                                <td class="total-monto">${{ number_format($costoTotalProduccion, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ===== FOOTER ===== -->
    <div class="footer">
        Documento de uso interno del Taller de Bastones.<br>
        Arte Titi_Val
    </div>

</body>
</html>
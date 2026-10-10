<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Venta - Pedido #{{ $pedido->id }}</title>
    <style>
        @page { margin: 26px 32px; }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            color: #34303d;
            margin: 0;
            padding: 0;
        }

        /* Tablas solo para ubicar columnas. El espacio entre columnas se da con padding en las celdas. */
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
        table.layout td.col-logo { width: 115px; vertical-align: middle; }
        table.layout td.col-titulo { text-align: right; vertical-align: middle; }

        .logo {
            width: 92px;
            height: 92px;
            border-radius: 46px;
            border: 3px solid #ffffff;
        }
        .empresa {
            margin: 0;
            font-size: 26px;
            letter-spacing: 3px;
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
        .contacto {
            margin: 8px 0 0 0;
            font-size: 11px;
            color: #7b3fa0;
            font-weight: bold;
        }

        .espacio { height: 14px; font-size: 1px; line-height: 14px; }

        /* ===== TARJETAS DE DATOS ===== */
        .dato {
            background-color: #ffffff;
            border: 1px solid #e3d3ef;
            border-radius: 10px;
            padding: 9px 12px;
        }
        .etiqueta {
            display: block;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #7b3fa0;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .valor { display: block; font-size: 12px; color: #2b2733; }

        /* ===== TARJETAS GRANDES ===== */
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
        .tarjeta-cuerpo { padding: 12px 18px; height: 235px; }

        .materiales { margin: 0; padding: 0; list-style: none; }
        .materiales li {
            padding: 7px 0;
            border-bottom: 1px dotted #d9c3e8;
            font-size: 12px;
            color: #2b2733;
        }
        .nota-materiales {
            margin: 10px 0 0 0;
            font-size: 9.5px;
            color: #7a7585;
            font-style: italic;
        }

        .imagen-wrap { text-align: center; padding: 10px; height: 235px; }
        .imagen-wrap img { max-width: 100%; max-height: 230px; border-radius: 12px; }
        .sin-imagen { padding-top: 95px; color: #a9a3b3; font-size: 11px; text-align: center; }

        /* ===== TOTALES ===== */
        .total-card {
            border: 1px solid #e3d3ef;
            border-radius: 14px;
            background-color: #faf6fd;
        }
        table.layout td.fila-unit {
            padding: 12px 18px;
            font-size: 12px;
            vertical-align: middle;
        }
        .lbl { color: #7a7585; }
        .val { text-align: right; font-weight: bold; color: #2b2733; }
        .fila-total {
            background-color: #7b3fa0;
            border-radius: 0 0 13px 13px;
            padding: 12px 18px;
        }
        table.layout td.total-texto {
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            vertical-align: middle;
        }

        /* ===== PAGOS ===== */
        .pagos-titulo {
            text-align: center;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: bold;
            color: #4a2565;
            margin-bottom: 8px;
        }
        .banco {
            background-color: #faf6fd;
            border: 1px solid #e3d3ef;
            border-radius: 12px;
            padding: 11px 18px;
        }
        .banco-nombre {
            margin: 0 0 6px 0;
            font-size: 11.5px;
            font-weight: bold;
            color: #7b3fa0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .banco p { margin: 3px 0; font-size: 11px; color: #4a4655; }

        .correo {
            margin-top: 10px;
            text-align: center;
            padding: 8px;
            background-color: #f5effa;
            border-radius: 14px;
            font-size: 11px;
            color: #7b3fa0;
            font-weight: bold;
        }

        .footer {
            margin-top: 14px;
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
                    <h1 class="empresa">Arte Titi_Val</h1>
                    <p class="subtitulo">Nota de Venta Oficial</p>
                    <span class="pildora">N.º {{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</span>
                    <p class="contacto">WhatsApp / Contacto: 0999856725</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="espacio">&nbsp;</div>

    <!-- ===== DATOS DEL CLIENTE (4 columnas, 3 separaciones) ===== -->
    <table class="layout">
        <tr>
            <td style="width: 25%; padding-right: 6px;">
                <div class="dato">
                    <span class="etiqueta">Cliente / Institución</span>
                    <span class="valor">{{ $pedido->cliente_nombre }}</span>
                </div>
            </td>
            <td style="width: 25%; padding-left: 3px; padding-right: 3px;">
                <div class="dato">
                    <span class="etiqueta">Correo electrónico</span>
                    <span class="valor">{{ $pedido->correo_cliente ?? 'No registrado' }}</span>
                </div>
            </td>
            <td style="width: 25%; padding-left: 3px; padding-right: 3px;">
                <div class="dato">
                    <span class="etiqueta">Fecha de emisión</span>
                    <span class="valor">{{ $pedido->created_at->format('d/m/Y') }}</span>
                </div>
            </td>
            <td style="width: 25%; padding-left: 6px;">
                <div class="dato">
                    <span class="etiqueta">Cantidad total</span>
                    <span class="valor">{{ $pedido->cantidad_total_bastones }} {{ $pedido->cantidad_total_bastones == 1 ? 'bastón' : 'bastones' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="espacio">&nbsp;</div>

    <!-- ===== DETALLE + IMAGEN ===== -->
    <table class="layout">
        <tr>
            <td style="width: 50%; padding-right: 7px;">
                <div class="tarjeta">
                    <div class="tarjeta-titulo">Detalle del Diseño</div>
                    <div class="tarjeta-cuerpo">
                        <ul class="materiales">
                            @foreach($pedido->materiales as $mat)
                                @if(stripos($mat->nombre_material, 'cincho') === false && stripos($mat->nombre_material, 'elástico') === false && stripos($mat->nombre_material, 'elastico') === false)
                                    <li>{{ $mat->nombre_material }}</li>
                                @endif
                            @endforeach
                        </ul>
                        <p class="nota-materiales">* Incluye ensamblaje profesional y materiales estructurales internos.</p>
                    </div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 7px;">
                <div class="tarjeta">
                    <div class="tarjeta-titulo">Referencia Visual</div>
                    <div class="imagen-wrap">
                        @if(isset($rutaImagenPdf) && $rutaImagenPdf)
                            <img src="{{ $rutaImagenPdf }}" alt="Referencia">
                        @else
                            <div class="sin-imagen">Sin imagen de referencia</div>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="espacio">&nbsp;</div>

    <!-- ===== TOTALES ===== -->
    <table class="layout">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; padding-left: 7px;">
                <div class="total-card">
                    <table class="layout">
                        <tr>
                            <td class="fila-unit lbl">Costo sugerido por unidad</td>
                            <td class="fila-unit val">$ {{ number_format($pedido->costo_unitario, 2) }}</td>
                        </tr>
                    </table>
                    <div class="fila-total">
                        <table class="layout">
                            <tr>
                                <td class="total-texto">Total a pagar</td>
                                <td class="total-texto" style="text-align: right;">$ {{ number_format($pedido->costo_total, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="espacio">&nbsp;</div>

    <!-- ===== DATOS DE PAGO ===== -->
    <div class="pagos-titulo">Datos para Transferencia / Depósito</div>
    <table class="layout">
        <tr>
            <td style="width: 50%; padding-right: 7px;">
                <div class="banco">
                    <p class="banco-nombre">Banco Pichincha</p>
                    <p>Alejandro David Larco</p>
                    <p>Cta. N.- 2213966885</p>
                    <p>CI.- 1729090272</p>
                </div>
            </td>
            <td style="width: 50%; padding-left: 7px;">
                <div class="banco">
                    <p class="banco-nombre">Banco Guayaquil</p>
                    <p>Alejandro David Larco</p>
                    <p>Cta. N.- 20569845</p>
                    <p>CI.- 1729090272</p>
                </div>
            </td>
        </tr>
    </table>
    <div class="correo">alejandrolarco2020@hotmail.com</div>

    <!-- ===== FOOTER ===== -->
    <div class="footer">
        Gracias por confiar en nuestro trabajo.<br>
        Documento generado automáticamente por el Sistema de Gestión.
    </div>

</body>
</html>
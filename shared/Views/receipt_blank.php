<?php
/**
 * Recibo de Egreso en Blanco
 * Para imprimir y llenar a mano
 */
// Ruta del logo ajustada según tu estructura de carpetas
$logoPath = '../../../public/logo ium rojo (3).png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Egreso en Blanco</title>
    <style>
        /* Configuración de página igual al recibo de egresos */
        @page { size: Letter portrait !important; margin: 0 !important; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10.5px; line-height: 1.15; background: #f2f2f2; padding: 0; }
        
        .page { width: 100%; max-width: 8.5in; height: 13.4cm; padding: 0.2in 0.25in; position: relative; background: white; display: flex; flex-direction: column; box-shadow: 0 4px 16px rgba(0,0,0,0.08); border: 1px solid #e5e5e5; border-radius: 4px; page-break-inside: avoid; overflow: hidden; margin: 20px auto; }
        
        @media print {
            body { margin: 0; background: white; padding: 0; }
            .no-print { display: none; }
            .page { box-shadow: none; border: none; width: 100%; max-width: 8.5in; height: 13.4cm; margin: 0; border-radius: 0; }
            html, body { width: 100%; max-width: 8.5in; height: 13.4cm; }
        }

        /* Estilos del Encabezado */
        .header { display: table; width: 100%; margin-bottom: 8px; }
        .header-left { display: table-cell; width: 35%; vertical-align: top; }
        .header-right { display: table-cell; width: 65%; vertical-align: top; text-align: right; }
        
        .logo-box { display: inline-block; background: #9e1b32; padding: 5px 9px; border-radius: 4px; }
        .logo-box img { height: 34px; vertical-align: middle; }
        
        .institution { font-size: 8px; color: #333; margin-top: 3px; font-weight: bold; letter-spacing: 0.2px; }
        .doc-title { font-size: 14px; font-weight: 800; color: #1a1a1a; margin-bottom: 2px; letter-spacing: 0.3px; }
        .folio { font-size: 11px; color: #9e1b32; font-weight: 700; margin-bottom: 4px; }

        .divider { height: 2px; background: linear-gradient(90deg, #9e1b32, #c62828 60%, #9e1b32); margin: 6px 0 8px; border-radius: 2px; }

        .content { flex: 1; display: flex; flex-direction: column; }

        /* Grid y Celdas */
        .grid { display: table; width: 100%; margin-bottom: 6px; }
        .grid-row { display: table-row; }
        .grid-cell { display: table-cell; padding: 4px 8px 4px 0; vertical-align: top; }
        .grid-cell.full { width: 100%; }
        .grid-cell.half { width: 50%; }

        .label { font-size: 6px; color: #666; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px; }
        
        /* Líneas para escribir (Estilo Específico para versión en blanco) */
        .blank-line { border-bottom: 1px solid #333; height: 18px; width: 100%; }
        
        /* Sección de Monto */
        .monto-section { background: #f8f9fa; border: 2px solid #9e1b32; padding: 10px; text-align: center; margin: 10px 0; border-radius: 6px; }
        .monto-label { font-size: 9px; color: #666; font-weight: 700; margin-bottom: 4px; letter-spacing: 0.2px; }
        .monto-currency { font-size: 9px; color: #666; margin-top: 6px; }
        .monto-blank { border-bottom: 2px solid #333; height: 24px; width: 60%; margin: 0 auto; }

        /* Cajas de texto vacías */
        .letra-box { background: #fffdf3; border: 1px solid #e6c565; padding: 8px; margin: 8px 0; border-radius: 4px; min-height: 35px; }
        .payment-box { background: #f5f5f5; border: 1px solid #ddd; padding: 7px; margin: 6px 0; border-radius: 4px; min-height: 28px; }
        .description-box { border: 1px solid #ddd; padding: 9px; min-height: 60px; background: #fafafa; margin: 8px 0; border-radius: 4px; flex: 1; }

        /* Firma */
        .signature-section { margin-top: auto; padding-top: 18px; text-align: center; }
        .signature-line { border-top: 1px solid #444; width: 58%; margin: 0 auto 6px auto; }
        .signature-label { font-size: 10px; font-weight: 700; color: #333; letter-spacing: 0.2px; }
        .signature-name-blank { height: 16px; } /* Espacio vacío para el nombre */

        .footer { font-size: 8px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 5px; margin-top: 8px; }

        /* Botón imprimir */
        .no-print { position: fixed; left: 50%; bottom: 12px; transform: translateX(-50%); z-index: 9999; }
        .print-btn { background: #2b7be4; color: #fff; border: none; border-radius: 4px; padding: 10px 20px; font-size: 14px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.15); font-weight: bold; }
        .print-btn:hover { background: #1a66c7; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">Imprimir Recibo en Blanco</button>
    </div>
    
    <div class="page">
        <div class="header">
            <div class="header-left">
                <div class="logo-box">
                    <img src="<?php echo $logoPath; ?>" alt="IUM">
                </div>
                <div class="institution">Instituto Universitario Morelia</div>
            </div>
            <div class="header-right">
                <div class="doc-title">COMPROBANTE DE EGRESO</div>
                <div class="folio">Folio: __________________</div>
                <div style="font-size: 8px; color: #333; margin-top: 4px;">Fecha: _____________________________</div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="content">
            <div class="grid">
                <div class="grid-row">
                    <div class="grid-cell half">
                        <span class="label">Proveedor</span>
                        <div class="blank-line"></div>
                    </div>
                    <div class="grid-cell half">
                        <span class="label">Método de Pago</span>
                        <div class="blank-line"></div>
                    </div>
                </div>
            </div>
            
            <div class="grid">
                <div class="grid-row">
                    <div class="grid-cell full">
                        <span class="label">Categoría</span>
                        <div class="blank-line"></div>
                    </div>
                </div>
            </div>
            
            <div class="monto-section">
                <div class="monto-label">MONTO TOTAL</div>
                <div class="monto-blank"></div>
                <div class="monto-currency">PESOS MEXICANOS (MXN)</div>
            </div>
            
            <div>
                <span class="label">Cantidad con letra</span>
                <div class="letra-box"></div>
            </div>
            
            <div class="grid">
                <div class="grid-row">
                    <div class="grid-cell full">
                        <span class="label">Descripción</span>
                        <div class="description-box"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="signature-section">
            <div class="signature-line"></div>
            <div class="signature-label">FIRMA DE QUIEN RECIBIÓ</div>
            <div class="signature-name-blank"></div>
        </div>
        
        <div class="footer">
            Este documento es un comprobante interno de egreso del Instituto Universitario Morelia.
        </div>
    </div>
</body>
</html>
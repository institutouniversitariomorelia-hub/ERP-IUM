<?php
/**
 * Plantilla: Recibo en Blanco (Ingreso)
 * Basado en el estilo de ingreso_diario.php pero sin datos ni consulta a BD
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo en Blanco - Ingreso</title>
    <style>
        @page { size: Letter portrait !important; margin: 0 !important; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, sans-serif; font-size: 10.5px; line-height: 1.15; background: #f2f2f2; }
        .page { width: 100%; max-width: 8.5in; height: 13.4cm; padding: 0.2in 0.25in; background: white; border-radius: 4px; overflow: hidden; display: flex; flex-direction: column; margin: 8px auto; }
        .header { display: table; width: 100%; margin-bottom: 4px; }
        .header-left { display: table-cell; width: 35%; vertical-align: top; }
        .header-right { display: table-cell; width: 65%; vertical-align: top; text-align: right; }
        .logo-box { display: inline-block; background: #9e1b32; padding: 3px 6px; border-radius: 3px; }
        .logo-box span { color:#fff; font-weight:bold; }
        .institution { font-size: 8px; font-weight: bold; }
        .doc-title { font-size: 12px; font-weight: bold; }
        .folio { font-size: 11px; font-weight: bold; color: #9e1b32; }
        .divider { height: 2px; background: #9e1b32; margin: 4px 0; }
        .grid { display: table; width: 100%; margin-bottom: 2px; }
        .grid-row { display: table-row; }
        .grid-cell { display: table-cell; padding: 2px 4px 2px 0; vertical-align: top; }
        .label { font-size: 10px; color: #444; font-weight: bold; }
        .value { font-size: 11px; border-bottom: 1px solid #ccc; min-height: 9px; }
        .monto-section { text-align: right; margin: 4px 0; }
        .monto-label { font-size: 8px; }
        .monto-value { font-size: 16px; font-weight: bold; color: #9e1b32; }
        .monto-currency { font-size: 8px; }
        .payment-box { background: #f8f8f8; border: 1px solid #ccc; padding: 4px; border-radius: 3px; font-size: 12px; margin-bottom: 4px; }
        .description-box { border: 1px solid #ddd; padding: 6px; min-height: 35px; background: #fafafa; border-radius: 3px; font-size: 8px; }
        .signature-section { margin-top: auto; padding-top: 6px; text-align: center; }
        .signature-line { border-top: 1px solid #444; width: 55%; margin: 6px auto; }
        .signature-label { font-size: 9px; font-weight: bold; text-align: center; }
        .signature-name { font-size: 10.5px; font-weight: bold; margin-top: 2px; text-align: center; }
        .letra-box { margin-top: 4px; margin-bottom: 4px; font-size: 10px; }
        .footer { font-size: 7.5px; margin-top: 4px; text-align: center; border-top: 1px solid #ddd; padding-top: 3px; }
        @media print { body { background: white; } .no-print { display:none !important; } .page { border:none; box-shadow:none; } }
        .print-btn { background: #9e1b32; color: #fff; border: none; border-radius: 4px; padding: 8px 12px; font-size: 12px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
        .print-btn:hover { background: #b7213c; }
        .print-container { text-align:center; margin-top:8px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="header-left">
                <div class="logo-box"><span>IUM</span></div>
                <div class="institution">Instituto Universitario Morelia</div>
            </div>
            <div class="header-right">
                <div class="doc-title">RECIBO DE INGRESO</div>
                <div class="folio">Folio: __________</div>
                <div style="font-size: 10px; color: #666; margin-top: 4px;">Fecha: ____/____/______</div>
            </div>
        </div>
        <div class="divider"></div>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell half">
                    <span class="label">Recibido de</span>
                    <div class="value">______________________________</div>
                </div>
                <div class="grid-cell half">
                    <span class="label">Matrícula</span>
                    <div class="value">______________________________</div>
                </div>
            </div>
        </div>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell half">
                    <span class="label">Nivel</span>
                    <div class="value">______________________________</div>
                </div>
                <div class="grid-cell half">
                    <span class="label">Programa</span>
                    <div class="value">______________________________</div>
                </div>
            </div>
        </div>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width: 25%;">
                    <span class="label">Grado</span>
                    <div class="value">________________</div>
                </div>
                <div class="grid-cell" style="width: 25%;">
                    <span class="label">Grupo</span>
                    <div class="value">________________</div>
                </div>
                <div class="grid-cell" style="width: 50%;">
                    <span class="label">Modalidad</span>
                    <div class="value">______________________________</div>
                </div>
            </div>
        </div>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell full">
                    <span class="label">Categoría</span>
                    <div class="value">______________________________</div>
                </div>
            </div>
        </div>
        <div style="display: table; width: 100%; margin: 6px 0;">
            <div style="display: table-cell; width: 50%; padding-right: 10px;">
                <div class="label">Cantidad con letra</div>
                <div class="value" style="font-size: 10px; font-style: italic; min-height: 30px; line-height: 1.4;">______________________________________________</div>
            </div>
            <div style="display: table-cell; width: 50%; text-align: right;">
                <div class="monto-section">
                    <div class="monto-label">Monto Total</div>
                    <div class="monto-value">$ ____________</div>
                    <div class="monto-currency">MXN</div>
                </div>
            </div>
        </div>
        <div class="payment-box">
            <span class="label">Método de Pago</span>
            <div style="font-size: 11px; font-weight: bold;">______________________________</div>
        </div>
        <div>
            <span class="label">Observaciones</span>
            <div class="description-box">__________________________________________________________</div>
        </div>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell half">
                    <span class="label">Mes Correspondiente</span>
                    <div class="value">________________</div>
                </div>
                <div class="grid-cell half">
                    <span class="label">Año</span>
                    <div class="value">____________</div>
                </div>
            </div>
        </div>
        <div class="signature-section">
            <div class="logo-box" style="margin-bottom: 8px;">
                <span style="color: white; font-weight: bold;">IUM</span>
            </div>
            <div class="signature-line"></div>
            <div class="signature-label">FIRMA DE QUIEN RECIBIÓ</div>
            <div class="signature-name">______________________________</div>
        </div>
        <div class="footer">
            Este documento es un comprobante interno de ingreso del Instituto Universitario Morelia.
        </div>
    </div>
    <div class="no-print print-container">
        <button class="print-btn" onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>

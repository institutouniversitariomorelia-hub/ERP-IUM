<?php
/**
 * Recibo de Ingreso en Blanco
 * Para imprimir y llenar a mano
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo de Ingreso en Blanco</title>
    <style>
        @page { size: 8.5in 5.5in; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 7px; line-height: 1.2; }
        .page { width: 8.5in; height: 5.5in; padding: 0.15in 0.2in; position: relative; background: white; display: flex; flex-direction: column; }
        
        .header { display: table; width: 100%; margin-bottom: 8px; }
        .header-left { display: table-cell; width: 30%; vertical-align: top; }
        .header-right { display: table-cell; width: 70%; vertical-align: top; text-align: right; }
        .logo-box { display: inline-block; background: #9e1b32; padding: 4px 8px; border-radius: 3px; }
        .logo-box img { height: 32px; vertical-align: middle; }
        .institution { font-size: 7px; color: #333; margin-top: 2px; font-weight: bold; }
        .doc-title { font-size: 13px; font-weight: bold; color: #1a1a1a; margin-bottom: 1px; }
        .folio { font-size: 11px; color: #9e1b32; font-weight: bold; }
        
        .divider { height: 2px; background: #9e1b32; margin: 6px 0; }
        
        .content { flex: 1; display: flex; flex-direction: column; }
        
        .grid { display: table; width: 100%; margin-bottom: 6px; }
        .grid-row { display: table-row; }
        .grid-cell { display: table-cell; padding: 3px 6px 3px 0; vertical-align: top; }
        .grid-cell.full { width: 100%; }
        .grid-cell.half { width: 50%; }
        
        .label { font-size: 7px; color: #666; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 1px; }
        .blank-line { border-bottom: 1px solid #333; height: 18px; }
        .blank-line-short { border-bottom: 1px solid #333; height: 16px; }
        
        .monto-section { border: 2px solid #9e1b32; padding: 8px; text-align: center; margin: 8px 0; border-radius: 4px; min-height: 50px; }
        .monto-label { font-size: 8px; color: #666; font-weight: bold; margin-bottom: 4px; }
        .monto-blank { border-bottom: 2px solid #333; min-height: 22px; }
        
        .letra-box { border: 1px solid #999; padding: 8px; margin: 6px 0; min-height: 40px; border-radius: 3px; flex: 1; }
        
        .payment-box { border: 1px solid #ddd; padding: 8px; margin: 6px 0; min-height: 35px; border-radius: 3px; background: #fafafa; }
        
        .signature-section { margin-top: auto; padding-top: 20px; text-align: center; }
        .signature-line { border-top: 1px solid #333; width: 55%; margin: 0 auto 6px auto; }
        .signature-label { font-size: 9px; font-weight: bold; color: #333; }
        .signature-name { font-size: 10px; color: #000; margin-top: 3px; font-weight: bold; }
        
        .footer { font-size: 7px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 4px; margin-top: 8px; }
        
        .signature-section { margin-top: 6px; text-align: center; }
        .signature-line { border-top: 1px solid #333; width: 45%; margin: 15px auto 2px auto; }
        .signature-label { font-size: 7px; font-weight: bold; color: #333; }
        .signature-name { font-size: 8px; color: #000; margin-top: 1px; font-weight: bold; }
        
        .footer { position: absolute; bottom: 0.1in; left: 0.15in; right: 0.15in; font-size: 5px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 2px; }
        
        @media print { body { margin: 0; } .no-print { display: none; } .page { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="header-left">
                <div class="logo-box">
                    <img src="../../public/logo ium blanco.png" alt="IUM">
                </div>
                <div class="institution">Instituto Universitario Morelia</div>
            </div>
            <div class="header-right">
                <div class="doc-title">RECIBO DE INGRESO</div>
                <div class="folio">Folio: __________</div>
                <div style="font-size: 10px; color: #666; margin-top: 4px;">Fecha: ___________________</div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell half">
                    <span class="label">Recibido de</span>
                    <div class="blank-line"></div>
                </div>
                <div class="grid-cell half">
                    <span class="label">Matrícula</span>
                    <div class="blank-line"></div>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell half">
                    <span class="label">Nivel</span>
                    <div class="blank-line-short"></div>
                </div>
                <div class="grid-cell half">
                    <span class="label">Programa</span>
                    <div class="blank-line-short"></div>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width: 25%;">
                    <span class="label">Grado</span>
                    <div class="blank-line-short"></div>
                </div>
                <div class="grid-cell" style="width: 25%;">
                    <span class="label">Grupo</span>
                    <div class="blank-line-short"></div>
                </div>
                <div class="grid-cell" style="width: 50%;">
                    <span class="label">Modalidad</span>
                    <div class="blank-line-short"></div>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell full">
                    <span class="label">Concepto</span>
                    <div class="blank-line"></div>
                </div>
            </div>
        </div>
        
        <!-- Monto -->
        <div class="monto-section">
            <div class="monto-label">MONTO TOTAL</div>
            <div class="monto-blank"></div>
            <div style="font-size: 10px; color: #666; margin-top: 4px;">PESOS MEXICANOS (MXN)</div>
        </div>
        
        <!-- Cantidad con letra -->
        <div>
            <span class="label">Cantidad con letra</span>
            <div class="letra-box"></div>
        </div>
        
        <!-- Método de Pago -->
        <div>
            <span class="label">Método de Pago</span>
            <div class="payment-box"></div>
        </div>
        
        <!-- Observaciones -->
        <div>
            <span class="label">Observaciones</span>
            <div style="border: 1px solid #ddd; padding: 8px; min-height: 50px; border-radius: 4px;"></div>
        </div>
        
        <!-- Firma -->
        <div class="signature-section">
            <div class="logo-box" style="margin-bottom: 8px;">
                <span style="color: white; font-weight: bold;">IUM</span>
            </div>
            <div class="signature-line"></div>
            <div class="signature-label">FIRMA DE QUIEN RECIBIÓ</div>
            <div class="signature-name">Ing. Ricardo Valdés Morales</div>
        </div>
        
        <div class="footer">
            Este documento es un comprobante interno de ingreso del Instituto Universitario Morelia.
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" style="background: #2b7be4; color: white; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Imprimir Recibo en Blanco
        </button>
    </div>
</body>
</html>

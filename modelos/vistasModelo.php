<?php
// ===============================================
// Archivo: modelos/vistasModelo.php
// CORRECCIÓN: La ruta del login ahora es "LOGIN/login.html".
// ===============================================

class vistasModelo {
    protected function obtener_vistas_modelo($vistas) {
        
        $listaBlanca = [
            "mi_perfil",
            "egresos",
            "ingresos",
            "categorias",
            "presupuestos",
            "auditoria"
        ];

        // 1. Manejo del Login 
        if ($vistas == "login") {
            // 🚨 RUTA CORREGIDA: Apunta al nuevo subdirectorio
            $contenido_html = "LOGIN/login.html"; 
        
        // 2. Manejo de Vistas Válidas
        } else if (in_array($vistas, $listaBlanca)) {
            // Devuelve la ruta de la plantilla principal
            $contenido_html = "vistas/plantilla.php"; 
        
        // 3. Manejo de Error 404
        } else {
            $contenido_html = "404";
        }

        return $contenido_html;
    }
}



//CODIGO PARA EJECUTAR HTML :D

// ===============================================
// Archivo: modelos/vistasModelo.php
// ===============================================
// Archivo: modelos/vistasModelo.php
// SOLUCIÓN FINAL: Solo devuelve rutas a archivos HTML en RECURSOS/.
// ===============================================

// ARCHIVO: modelos/vistasModelo.php

// ARCHIVO: modelos/vistasModelo.php

// ARCHIVO: modelos/vistasModelo.php

// ARCHIVO: modelos/vistasModelo.php

// ARCHIVO: modelos/vistasModelo.php (FINAL Y CRÍTICO)

/*
class vistasModelo {
    protected function obtener_vistas_modelo($vistas) {
        
        $listaBlanca = [
            "mi_perfil", "egresos", "ingresos", 
            "categorias", "presupuestos", "auditoria",
            "index",
            "dashboard" // <-- LA CLAVE QUE FALTABA
        ];

        if ($vistas == "login") {
            // RUTA ABSOLUTA AL ARCHIVO QUE FUNCIONA
            $contenido_html = "/RECURSOS/login.html"; 
        } else if (in_array($vistas, $listaBlanca)) {
            // RUTA ABSOLUTA AL ARCHIVO QUE FUNCIONA
            $contenido_html = "/RECURSOS/dashboard.html"; 
        } else {
            $contenido_html = "404"; 
        }

        return $contenido_html;
    }
}*/
?>


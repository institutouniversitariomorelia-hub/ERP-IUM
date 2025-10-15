<?php
// ARCHIVO: controladores/vistasControlador.php

require_once __DIR__ . "/../modelos/vistasModelo.php"; 

class vistasControlador extends vistasModelo {

    public function obtener_plantilla_controlador() {
        // ...
    }
    
    // Obtiene la vista solicitada y llama al modelo.
    public function obtener_vistas_controlador() {
        if(isset($_GET['views'])) {
            $ruta = explode("/", $_GET['views']); 
            $respuesta = $this->obtener_vistas_modelo($ruta[0]); 
        } else {
            // Si no hay parámetro views, pide la vista 'login'
            $respuesta = $this->obtener_vistas_modelo("login"); 
        }
        return $respuesta; 
    }
}

/*
// CODIGO PARA EJECUTAR HTML :D
// ===============================================
// Archivo: controladores/vistasControlador.php
// DOCUMENTACIÓN DEL CAMBIO: SIMPLIFICACIÓN
// - El método obtener_plantilla_controlador() ha sido eliminado.
// - La función principal de obtener la vista sigue intacta.
// ===============================================

// Incluye el modelo de vistas usando ruta absoluta del sistema de archivos
// ARCHIVO: controladores/vistasControlador.php

// Incluir el modelo de vistas (Ruta ABSOLUTA es segura aquí)
// ARCHIVO: controladores/vistasControlador.php

require_once __DIR__ . "/../modelos/vistasModelo.php"; 

class vistasControlador extends vistasModelo {

    public function obtener_vistas_controlador() {
        if(isset($_GET['views'])) {
            $ruta = explode("/", $_GET['views']); 
            $respuesta = $this->obtener_vistas_modelo($ruta[0]); 
        } else {
            // Se asegura de obtener "RECURSOS/login.html" para la carga inicial
            $respuesta = $this->obtener_vistas_modelo("login"); 
        }
        return $respuesta; 
    }
}*/
?>

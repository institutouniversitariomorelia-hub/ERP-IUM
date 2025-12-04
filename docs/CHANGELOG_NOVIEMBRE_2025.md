Aquí tienes la Versión Definitiva. He fusionado la claridad operativa del primer texto con la profundidad técnica del segundo.

Esta versión está estructurada para ser tu Documento Maestro: arriba tiene lo que necesitas para trabajar hoy, y abajo tiene toda la referencia técnica (SQL, listas, archivos) para consultas futuras.

📋 CHANGELOG MAESTRO - Sistema ERP-IUM
📅 Fecha de Corte: 2025-12-04 🌿 Branch Actual: development (Fusionado con fixes de testing)

🧭 1. SNAPSHOT: Estado Actual del Proyecto
Resumen ejecutivo para inicio de sesión.

✅ Estado Frontend (Presupuestos & UI)
Sub-Presupuestos: Modal #modalSubPresupuesto operativo. Selección automática de presupuesto padre vía data-parent-id. Corrección de promesas AJAX y selectores vacíos.

Limpieza UI: Eliminado campo "Nombre" en Presupuesto General (backend lo admite opcional). Eliminados textos temporales "Formulario NUEVO".

Modularización: app.js consolidado usando módulos (PresupuestosModule, AlertasPresupuestosModule, etc.).

✅ Estado Backend & Base de Datos
Categorías: Refactor completo. Tablas ingresos y egresos limpias de campos obsoletos (concepto, activo_fijo).

Consultas: Implementado y probado getCategoriasEgreso en CategoriaController.

Integridad: 12 Triggers recreados y BD Espejo sincronizada.

Migraciones: Scripts en database/migrations/00_active aplicados.

📝 2. PROTOCOLO DE INICIO (NEWCHAT)
Instrucciones para la IA al iniciar un nuevo chat con este proyecto.

Leer Estado: Revisar este CHANGELOG para entender que la BD ya está refactorizada (Fase 3.3 completada).

Copiar Contexto: Utilizar la siguiente lista de tareas como guía de trabajo.

Restricción: No intentar conexiones remotas ni usar credenciales reales sin autorización explícita.

📌 Lista de Tareas Activas (Fases 3.3 y 3.4)
Marcar progreso en cada sesión.

[ ] Verificación Final BD: Revisar esquema final (tablas principales + espejo).

[ ] Flujos de Presupuestos:

[ ] Crear/Editar/Eliminar Presupuesto General.

[ ] Crear/Editar/Eliminar Sub-presupuesto (Validar asignación de montos).

[ ] Presupuesto por Categoría.

[ ] Dashboard y Auditoría: Verificar visualización de datos.

[ ] Gestión de Usuarios: Roles y cambio de contraseña (autocomplete).

[ ] Documentación: Actualizar Manual Técnico y de Usuario (eliminar etiquetas "Simulado").

[ ] Backup: Realizar respaldo físico "Post-Refactor".

📜 3. REPORTE TÉCNICO DETALLADO: El "Gran Refactor"
Detalle de los cambios profundos realizados a finales de Noviembre 2025.

🛠 Cambios en Base de Datos (SQL)

1. Tabla categorias

Estructura Final: id_categoria, nombre, tipo, concepto (ENUM), descripcion, no_borrable (BOOL), id_user.

Lógica: Se eliminó id_presupuesto. Se añadieron 41 categorías protegidas (no_borrable = 1).

2. Limpieza de Tablas Movimientos

ALTER TABLE ingresos DROP COLUMN concepto; (Ahora el concepto lo dicta la categoría).

ALTER TABLE egresos DROP COLUMN activo_fijo; (Reemplazado por categoría).

3. Triggers

Total: 12 triggers recreados (6 Ingresos, 6 Egresos).

Función: Auditoría y replicación a espejo sin referencias a columnas borradas.

🎨 Sistema de Recibos (Diseño & Archivos)
Formato: Media Carta Horizontal (8.5" x 5.5").

Tecnología: CSS Flexbox para ajustar contenido y pie de firma.

Seguridad: Marca de agua "REIMPRESIÓN" rotada a -45deg.

Archivos Generadores PHP:

generate_receipt_ingreso_diario.php (Concepto: Registro Diario)

generate_receipt_ingreso_titulacion.php (Concepto: Titulaciones)

generate_receipt_ingreso_inscripcion.php (Concepto: Inscripciones)

generate_receipt_egreso.php (Proveedores/Gastos)

generate_receipt_blanco.php (Manual)

🐛 Historial de Bugs Críticos Resueltos
Bug: SyntaxError: Identifier 'presParentId' has already been declared.

Solución: Centralización de variables en app.js y limpieza de handlers duplicados.

Bug: Selects vacíos en Sub-presupuestos.

Solución: Implementación de getCategoriasEgreso + Corrección de cadena de promesas .then().

Bug: Error ArgumentCountError en bind_param.

Solución: Se ajustaron los tipos de datos en los Modelos (Ingreso/Egreso) para coincidir exactamente con las columnas de la BD refactorizada.

📎 APÉNDICE: Referencia de Datos
📂 A. Categorías Predefinidas (Protegidas)
Egresos (30):

IUM COMISIONES, IUM IMPUESTOS, IUM INVERSIÓN INMOBILIARIA, IUM NÓMINA, IUM REPARACIONES, IUM SERVICIOS, IUM SUMINISTROS, PLANTEL CFE, PLANTEL CONMUTADOR, PLANTEL CONTROL DE PLAGAS, PLANTEL COPIAS, PLANTEL GASOLINA, PLANTEL INTERNET, PLANTEL LIMPIEZA, PLANTEL MENSAJERÍA, PLANTEL PAPELERÍA, PLANTEL PAQUETERÍA, PLANTEL PUBLICIDAD, PLANTEL SERVICIOS VARIOS, PLANTEL TRANSPORTE, PLANTEL UNIFORMES, PERSONAL APOYO, PERSONAL CAPACITACIÓN, PERSONAL DOCENTES, PERSONAL NÓMINA, PERSONAL PRESTACIONES, SERVICIOS ESCOLARES CERTIFICACIONES, SERVICIOS ESCOLARES TITULACIONES, SERVICIOS ESCOLARES VIÁTICOS, VENTANILLA DEVOLUCIONES.

Ingresos (11):

COLEGIATURA, INSCRIPCIÓN, REINSCRIPCIÓN, PAGO EXTEMPORÁNEO, REVALIDACIÓN, EQUIVALENCIA, DUPLICADO DE DOCUMENTOS (Concepto: Registro Diario/Inscripciones). CERTIFICADO PARCIAL, CERTIFICADO TOTAL, TÍTULO, CÉDULA (Concepto: Titulaciones).

🖌 B. Especificación CSS Básica (Recibos)
CSS

@page { size: 8.5in 5.5in; margin: 0; }
body { font-family: Arial, sans-serif; font-size: 7px; line-height: 1.2; }
.watermark {
position: absolute; top: 50%; left: 50%;
transform: translate(-50%, -50%) rotate(-45deg);
content: "REIMPRESIÓN"; color: rgba(220, 53, 69, 0.12);
}
🗄 C. Migraciones Ejecutadas (Orden Cronológico)
2025-11-20_refactor_categorias.sql

insert_categorias_predefinidas.sql

limpieza_total.sql (DELETE masivo, conserva protegidas)

2025-11-21_remove_concepto_from_ingresos.sql

2025-11-21_remove_activo_fijo_from_egresos.sql

2025-11-21_fix_triggers_ingresos_egresos.sql

📑 REPORTE TÉCNICO DE INCIDENCIAS Y PRUEBAS (QA)Proyecto: Sistema ERP-IUMMódulo: Refactorización de Categorías y PresupuestosPeriodo de Pruebas: 20 al 28 de Noviembre de 2025Estatus Final: ✅ APROBADO1. INCIDENCIAS DE BASE DE DATOS Y MIGRACIÓN🔴 Incidencia #DB-01: Inconsistencia Referencial (Foreign Keys)Síntoma: Errores al intentar insertar movimientos debido a categorías referenciadas que habían sido eliminadas manualmente.Diagnóstico: Registros huérfanos en tablas de movimientos apuntando a id_categoria inexistentes.Solución Aplicada: Ejecución del script limpieza_total.sql. Se purgaron tablas transaccionales y se establecieron 41 categorías "protegidas" con el flag no_borrable = 1.Resultado de Prueba:SELECT COUNT(\*) FROM categorias WHERE no_borrable = 1; -> Resultado: 41 (Correcto).Integridad referencial restaurada.🔴 Incidencia #DB-02: Fallo en Triggers por Campos ObsoletosSíntoma: La base de datos Espejo dejó de sincronizarse. Errores SQL al intentar borrar o actualizar registros.Diagnóstico: Los triggers de auditoría (before_delete, update) intentaban leer las columnas concepto (ingresos) y activo_fijo (egresos) que ya habían sido eliminadas de la estructura (DROP COLUMN).Solución Aplicada: Recreación total de 12 triggers (6 de Ingresos, 6 de Egresos) eliminando las referencias a columnas obsoletas.Resultado de Prueba:Inserción en tabla principal -> Réplica inmediata en tabla espejo (Validado).SHOW TRIGGERS -> Muestra triggers actualizados al 21-Nov.2. INCIDENCIAS DE BACKEND (LÓGICA)🔴 Incidencia #BE-01: Error ArgumentCountError en ModelosSíntoma: Pantalla blanca o error 500 al guardar un nuevo Ingreso/Egreso.Causa Raíz: La función bind_param en PHP recibía un número de variables distinto al definido en la cadena de tipos (ej. "sssd..."). Desajuste tras quitar columnas.Solución Aplicada:Ingresos: Ajuste de cadena tipos a 15 caracteres (ssssdssisisssii).Egresos: Ajuste de cadena tipos a 10 caracteres.Resultado de Prueba: Creación exitosa de registros sin excepciones de argumentos.🔴 Incidencia #BE-02: Validación de "Concepto Inválido"Síntoma: El formulario de Ingresos rechazaba el guardado indicando que faltaba el concepto.Diagnóstico: IngresoController.php seguía validando concepto como campo obligatorio en $requiredFields, aunque el campo ya no existía en el formulario (ahora se deriva de la categoría).Solución Aplicada: Eliminación de 'concepto' del array de validación requerida en el controlador.Resultado de Prueba: Guardado exitoso de ingresos dejando que la categoría defina el concepto internamente.🔴 Incidencia #BE-03: Categorías de Egreso no disponibles (404/Empty)Síntoma: Al abrir el modal de Sub-Presupuesto, el select de categorías aparecía vacío.Diagnóstico: El controlador CategoriaController no tenía implementado el método getCategoriasEgreso o este no retornaba el JSON correctamente.Solución Aplicada: Implementación del método filtrando WHERE tipo = 'Egreso' y retorno en formato JSON compatible con Select2/HTML.Resultado de Prueba:Log: getCategoriasEgreso: returning categories.UI: El desplegable muestra correctamente las 30 categorías de egreso.3. INCIDENCIAS DE FRONTEND (INTERFAZ)🔴 Incidencia #FE-01: Confusión UI "Activo Fijo"Síntoma: Usuarios confundidos al ver el label "Activo Fijo" en gastos generales (ej. Papelería).Diagnóstico: La vista layout.php mantenía el label antiguo.Solución Aplicada: Cambio de etiqueta <label> a "Categoría" y reemplazo del input text por un select dinámico.Resultado de Prueba: Inspección visual de formularios de Egresos confirmada.🔴 Incidencia #FE-02: Error JS Identifier 'presParentId' has already been declaredSíntoma: El modal de Sub-Presupuestos no abría; la consola del navegador mostraba error de sintaxis.Diagnóstico: Declaración duplicada de la variable let presParentId en app.js debido a fusiones de código previas.Solución Aplicada: Refactorización de app.js para declarar variables al inicio del ámbito o usar bloques limpios, eliminando duplicados.Resultado de Prueba: Carga limpia de app.js sin errores en consola (F12).🔴 Incidencia #FE-03: Formato de Impresión de RecibosSíntoma: Los recibos se imprimían en dos hojas o con mucho espacio en blanco.Solución Aplicada:Rediseño CSS @page { size: 8.5in 5.5in; } (Media carta horizontal).Implementación de Flexbox para empujar la firma al final sin saltos de página.Resultado de Prueba: Impresión física y PDF generados correctamente en una sola hoja media carta.4. EVIDENCIA DE VALIDACIÓN (CHECKLIST FINAL)Para el reporte, se certifica que se ejecutaron las siguientes pruebas de aceptación:ID PruebaDescripciónResultado EsperadoResultado ObtenidoEstatusVAL-01Protección de CategoríasIntentar borrar "Nómina" (ID protegido). El sistema debe impedirlo.Mensaje de error: "Categoría protegida". Registro permanece.✅ PASÓVAL-02Flujo Sub-PresupuestoAbrir modal desde Presupuesto General ID 5. El select padre debe marcar ID 5 automáticamente.El modal abre y pre-selecciona el padre correcto.✅ PASÓVAL-03Recibo de IngresoGenerar recibo de "Colegiatura". Debe mostrar concepto "Registro Diario".Recibo PDF muestra concepto correcto según la categoría.✅ PASÓVAL-04ReimpresiónReimprimir un recibo existente.El PDF incluye marca de agua "REIMPRESIÓN" a 45 grados.✅ PASÓVAL-05Integridad BDRevisar tablas tras operaciones CRUD.No hay referencias a id_presupuesto ni columnas fantasma.✅ PASÓEste documento sirve como anexo técnico al Reporte de Cierre de la Fase 3.3.

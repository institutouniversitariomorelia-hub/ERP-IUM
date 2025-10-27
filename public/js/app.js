/**
 * app.js - Lógica del Frontend para el ERP IUM (Versión MVC Final Completa v4 - Corrección Ingresos)
 * Maneja interacciones de usuario (clics, formularios) para TODOS los módulos
 * y se comunica con los controladores PHP vía AJAX.
 */

/**
 * Función genérica para hacer llamadas AJAX a los controladores vía el enrutador index.php.
 * @param {string} controller Nombre del controlador.
 * @param {string} action Nombre de la acción/método.
 * @param {object} [data={}] Datos a enviar (vía POST).
 * @param {string} [method='POST'] Método HTTP (GET simulado por URL).
 * @returns {jqXHR} Promesa jQuery AJAX.
 */
function ajaxCall(controller, action, data = {}, method = 'POST') {
    let url = `${BASE_URL}index.php?controller=${controller}&action=${action}`;
    if (method.toUpperCase() === 'GET' && Object.keys(data).length > 0) {
        url += '&' + $.param(data);
        data = {};
    }
    const ajaxOptions = { url: url, type: 'POST', dataType: 'json', data: data };
    if (method.toUpperCase() !== 'GET' && method.toUpperCase() !== 'POST') {
         ajaxOptions.data._method = method.toUpperCase();
    }
    console.log("AJAX Call:", method, url, ajaxOptions.data); // Ayuda para depuración
    return $.ajax(ajaxOptions);
}

/**
 * Muestra un mensaje de error genérico en la consola y un alert.
 * @param {string} action Descripción de la acción que falló.
 * @param {object | null} jqXHR Objeto de error de jQuery AJAX (opcional).
 */
function mostrarError(action, jqXHR = null) {
    let errorMsg = `Ocurrió un error al ${action}.`;
    let serverError = 'Error desconocido o sin conexión.';
    if (jqXHR) {
        if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
            serverError = jqXHR.responseJSON.error;
        } else if (jqXHR.responseText) {
            console.error(`Respuesta cruda del servidor (${action}):`, jqXHR.responseText);
             const match = jqXHR.responseText.match(/<b>(?:Fatal error|Warning)<\/b>:(.*?)<br \/>/); // Captura Fatal error o Warning
             if (match && match[1]) {
                 serverError = `Error PHP: ${match[1].trim()}`;
             } else {
                 serverError = `Respuesta del servidor no válida (ver consola).`;
             }
        } else if (jqXHR.statusText) {
             serverError = `Error: ${jqXHR.statusText} (${jqXHR.status})`;
        }
    }
    errorMsg += `\nDetalle: ${serverError}`;
    console.error(`Error durante ${action}:`, serverError, jqXHR);
    alert(errorMsg + "\nPor favor, revise la consola (F12) para más detalles técnicos.");
}


/* ==========================================================================
   EVENTOS Y LÓGICA PARA CADA MÓDULO
   ========================================================================== */

// --- Eventos Módulo: Mi Perfil ---
$('#modalUsuario').on('show.bs.modal', function (event) { const button = event.relatedTarget; const userId = button ? $(button).data('id') : null; const $form = $('#formUsuario'); if (!$form.length) { console.error("Form #formUsuario no encontrado."); return; } $form[0].reset(); $('#usuario_id').val(''); if (userId) { $('#modalUsuarioTitle').text('Editar Usuario'); $('#usuario_password').prop('required', false).attr('placeholder', 'Dejar en blanco para no cambiar'); $('#usuario_id').val(userId); $('#usuario_nombre').val($(button).data('nombre')); $('#usuario_username').val($(button).data('username')); $('#usuario_rol').val($(button).data('rol')); } else { $('#modalUsuarioTitle').text('Registrar Nuevo Usuario'); $('#usuario_password').prop('required', true).attr('placeholder', 'Contraseña (obligatoria)'); } });
$(document).on('submit', '#formUsuario', function(e) { e.preventDefault(); ajaxCall('user', 'save', $(this).serialize()).done(r => { if (r.success) window.location.reload(); else alert('Error al guardar: ' + (r.error || 'Verifique datos.')); }).fail((xhr) => mostrarError('guardar usuario', xhr)); });
$(document).on('click', '.btn-delete-user', function() { const id = $(this).data('id'); if (confirm('¿Eliminar este usuario?')) { ajaxCall('user', 'delete', { id: id }).done(r => { if (r.success) window.location.reload(); else alert('Error al eliminar: ' + (r.error || 'No se pudo eliminar.')); }).fail((xhr) => mostrarError('eliminar usuario', xhr)); }});
$('#modalCambiarPassword').on('show.bs.modal', function (event) { const button = event.relatedTarget; const username = button ? $(button).data('username') || CURRENT_USER.username : CURRENT_USER.username; const $form = $('#formCambiarPassword'); if(!$form.length) { console.error("Form #formCambiarPassword no encontrado."); return; } $form[0].reset(); $('#change_pass_username').val(username); $('#username_display').text(username); });
$(document).on('submit', '#formCambiarPassword', function(e) { e.preventDefault(); ajaxCall('auth', 'changePassword', $(this).serialize()).done(r => { if (r.success) { $('#modalCambiarPassword').modal('hide'); alert('Contraseña actualizada.'); } else { alert('Error: ' + (r.error || 'No se pudo cambiar.')); } }).fail((xhr) => mostrarError('cambiar contraseña', xhr)); });

// --- Eventos Módulo: Egresos ---
$('#modalEgreso').on('show.bs.modal', function (event) { const button = event.relatedTarget; const egresoId = button ? $(button).data('id') : null; const $form = $('#formEgreso'); const $selectCat = $('#eg_id_categoria'); if (!$form.length) { console.error("Form #formEgreso no encontrado."); return; } $form[0].reset(); $('#egreso_id').val(''); $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true); ajaxCall('egreso', 'getCategoriasEgreso', {}, 'GET') .done(categorias => { $selectCat.empty().append('<option value="">Seleccione...</option>'); if (categorias && Array.isArray(categorias) && categorias.length > 0) { categorias.forEach(cat => { if (cat && cat.id !== undefined && cat.nombre !== undefined) { $selectCat.append(`<option value="${cat.id}">${cat.nombre}</option>`); } else { console.warn("Cat Egreso formato incorrecto:", cat); } }); } else { $selectCat.append('<option value="">-- No hay categorías --</option>'); console.warn("No cats egreso válidas."); } $selectCat.prop('disabled', false); if (egresoId) { $('#modalEgresoTitle').text('Editar Egreso'); ajaxCall('egreso', 'getEgresoData', { id: egresoId }, 'GET') .done(data => { if(data && !data.error && data.folio_egreso !== undefined) { $('#egreso_id').val(data.folio_egreso); $('#eg_fecha').val(data.fecha); $('#eg_monto').val(data.monto); $selectCat.val(data.id_categoria); $('#eg_proveedor').val(data.proveedor); $('#eg_destinatario').val(data.destinatario); $('#eg_forma_pago').val(data.forma_pago); $('#eg_documento_de_amparo').val(data.documento_de_amparo); $('#eg_activo_fijo').val(data.activo_fijo); $('#eg_descripcion').val(data.descripcion); } else { $('#modalEgreso').modal('hide'); alert('Error al cargar: '+(data.error||'')); } }).fail((xhr) => {mostrarError('cargar datos egreso', xhr); $('#modalEgreso').modal('hide');}); } else { $('#modalEgresoTitle').text('Registrar Nuevo Egreso'); $('#eg_activo_fijo').val('NO'); } }).fail((xhr) => { mostrarError('cargar cats egreso', xhr); $selectCat.empty().append('<option value="">Error</option>').prop('disabled', false); }); });
$(document).on('submit', '#formEgreso', function(e) { e.preventDefault(); ajaxCall('egreso', 'save', $(this).serialize()).done(r => { if(r.success) window.location.reload(); else alert('Error al guardar: ' + (r.error || 'Verifique datos.')); }).fail((xhr) => mostrarError('guardar egreso', xhr)); });
$(document).on('click', '.btn-del-egreso', function() { const id = $(this).data('id'); if (confirm('¿Eliminar este egreso?')) { ajaxCall('egreso', 'delete', { id: id }).done(r => { if(r.success) window.location.reload(); else alert('Error al eliminar: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('eliminar egreso', xhr)); } });

// --- Eventos Módulo: Ingresos (ACTUALIZADO CON NUEVOS CAMPOS v2) ---
$('#modalIngreso').on('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const ingresoId = button ? $(button).data('id') : null; // Este ID es folio_ingreso
    const $form = $('#formIngreso');
    const $selectCat = $('#in_id_categoria'); // ID correcto del select

    if (!$form.length) { console.error("Formulario #formIngreso no encontrado."); return; }
    $form[0].reset();
    $('#ingreso_id').val(''); // Campo oculto para folio_ingreso en update
    $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

    console.log("Preparando modal Ingreso. ID:", ingresoId);

    // 1. Cargar categorías de Ingreso (ID y Nombre)
    ajaxCall('ingreso', 'getCategoriasIngreso', {}, 'GET')
        .done(categorias => {
            console.log("Categorías de Ingreso recibidas:", categorias);
            $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');
            if (categorias && Array.isArray(categorias) && categorias.length > 0) {
                 // Usar ID de categoría como valor
                categorias.forEach(cat => {
                     if (cat && cat.id !== undefined && cat.nombre !== undefined) {
                        $selectCat.append(`<option value="${cat.id}">${cat.nombre}</option>`);
                    } else { console.warn("Categoría Ingreso con formato incorrecto:", cat); }
                });
            } else {
                 $selectCat.append('<option value="">-- No hay categorías de ingreso --</option>');
                 console.warn("No se recibieron categorías de ingreso válidas.");
            }
            $selectCat.prop('disabled', false);

            // 2. Si es edición, cargar datos del ingreso (incluyendo TODOS los campos)
            if (ingresoId) {
                $('#modalIngresoTitle').text('Editar Ingreso');
                ajaxCall('ingreso', 'getIngresoData', { id: ingresoId }, 'GET') // Pide datos usando folio_ingreso
                    .done(data => {
                         console.log("Datos de Ingreso para editar recibidos:", data);
                        if(data && !data.error && data.folio_ingreso !== undefined) {
                            // Rellenar TODOS los campos del formulario
                            $('#ingreso_id').val(data.folio_ingreso); // Guardar PK en campo oculto 'id'
                            $('#in_fecha').val(data.fecha);
                            $('#in_monto').val(data.monto);
                            $('#in_metodo_de_pago').val(data.metodo_de_pago);
                            $('#in_alumno').val(data.alumno);
                            $('#in_matricula').val(data.matricula);
                            $('#in_nivel').val(data.nivel);
                            $('#in_programa').val(data.programa);
                            $('#in_grado').val(data.grado);
                            $('#in_modalidad').val(data.modalidad);
                            $('#in_grupo').val(data.grupo);
                            $selectCat.val(data.id_categoria); // Seleccionar categoría por ID
                            $('#in_concepto').val(data.concepto);
                            $('#in_mes_correspondiente').val(data.mes_correspondiente);
                            $('#in_año').val(data.año);
                            $('#in_dia_pago').val(data.dia_pago);
                            $('#in_observaciones').val(data.observaciones);
                            // $('#in_folio').val(data.folio); // Si hubiera un campo folio adicional
                        } else {
                            $('#modalIngreso').modal('hide');
                            alert('Error al cargar datos del ingreso: '+(data.error || 'Registro no encontrado o inválido.'));
                         }
                    }).fail((xhr) => {
                        mostrarError('cargar datos ingreso', xhr);
                        $('#modalIngreso').modal('hide');
                    });
            } else {
                $('#modalIngresoTitle').text('Registrar Nuevo Ingreso');
                // Podrías poner valores por defecto aquí si lo necesitas para CREAR
                // Ejemplo: $('#in_año').val(new Date().getFullYear());
            }
        }).fail((xhr) => {
            mostrarError('cargar categorías ingreso', xhr);
            $selectCat.empty().append('<option value="">Error al cargar categorías</option>').prop('disabled', false);
        });
});
$(document).on('submit', '#formIngreso', function(e) { e.preventDefault(); ajaxCall('ingreso', 'save', $(this).serialize()).done(r => { if(r.success) window.location.reload(); else alert('Error al guardar: ' + (r.error || 'Verifique datos.')); }).fail((xhr) => mostrarError('guardar ingreso', xhr)); });
$(document).on('click', '.btn-del-ingreso', function() { const id = $(this).data('id'); if (confirm('¿Eliminar este ingreso?')) { ajaxCall('ingreso', 'delete', { id: id }).done(r => { if(r.success) window.location.reload(); else alert('Error al eliminar: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('eliminar ingreso', xhr)); } });

// --- Eventos Módulo: Categorías ---
$('#modalCategoria').on('show.bs.modal', function(event) { const button = event.relatedTarget; const catId = button ? $(button).data('id') : null; const $form = $('#formCategoria'); if (!$form.length) { console.error("Form #formCategoria no encontrado."); return; } $form[0].reset(); $('#categoria_id').val(''); if (catId) { $('#modalCategoriaTitle').text('Editar Categoría'); ajaxCall('categoria', 'getCategoriaData', { id: catId }, 'GET').done(data => { if(data && !data.error) { $('#categoria_id').val(data.id); $('#cat_nombre').val(data.nombre); $('#cat_tipo').val(data.tipo); $('#cat_descripcion').val(data.descripcion); } else { $('#modalCategoria').modal('hide'); alert('Error al cargar: '+(data.error||'')); } }).fail((xhr) => {mostrarError('cargar datos categoría', xhr); $('#modalCategoria').modal('hide');}); } else { $('#modalCategoriaTitle').text('Agregar Nueva Categoría'); } });
$(document).on('submit', '#formCategoria', function(e) { e.preventDefault(); ajaxCall('categoria', 'save', $(this).serialize()).done(r => { if(r.success) window.location.reload(); else alert('Error: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('guardar categoría', xhr)); });
$(document).on('click', '.btn-del-categoria', function() { if (confirm('¿Eliminar esta categoría?')) { ajaxCall('categoria', 'delete', { id: $(this).data('id') }).done(r => { if(r.success) window.location.reload(); else alert('Error: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('eliminar categoría', xhr)); } });
$(document).on('click', '#btnRefrescarCategorias', () => window.location.reload());

// --- Eventos Módulo: Presupuestos ---
$('#modalPresupuesto').on('show.bs.modal', function(event) { const button = event.relatedTarget; const presId = button ? $(button).data('id') : null; const $form = $('#formPresupuesto'); const $selectCat = $('#pres_categoria'); if (!$form.length) { console.error("Form #formPresupuesto no encontrado."); return; } $form[0].reset(); $('#presupuesto_id').val(''); $selectCat.empty().append('<option>Cargando...</option>').prop('disabled', true); ajaxCall('presupuesto', 'getAllCategorias', {}, 'GET').done(cats => { $selectCat.empty().append('<option value="">Seleccione...</option>'); if (cats && cats.length > 0) { cats.forEach(c => $selectCat.append(`<option value="${c.nombre}">${c.nombre} (${c.tipo})</option>`)); } $selectCat.prop('disabled', false); if (presId) { $('#modalPresupuestoTitle').text('Editar Presupuesto'); ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET').done(data => { if(data && !data.error) { $('#presupuesto_id').val(data.id); $selectCat.val(data.categoria); $('#pres_monto').val(data.monto); $('#pres_fecha').val(data.fecha); } else { $('#modalPresupuesto').modal('hide'); alert('Error al cargar: '+(data.error||'')); } }).fail((xhr) => {mostrarError('cargar datos presupuesto', xhr); $('#modalPresupuesto').modal('hide');}); } else { $('#modalPresupuestoTitle').text('Asignar/Actualizar'); } }).fail((xhr) => { mostrarError('cargar categorías ppto', xhr); $selectCat.empty().append('<option>Error</option>'); }); });
$(document).on('submit', '#formPresupuesto', function(e) { e.preventDefault(); ajaxCall('presupuesto', 'save', $(this).serialize()).done(r => { if(r.success) window.location.reload(); else alert('Error: ' + (r.error || 'Verifique si ya existe.')); }).fail((xhr) => mostrarError('guardar presupuesto', xhr)); });
$(document).on('click', '.btn-del-presupuesto', function() { if (confirm('¿Eliminar este presupuesto?')) { ajaxCall('presupuesto', 'delete', { id: $(this).data('id') }).done(r => { if(r.success) window.location.reload(); else alert('Error: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('eliminar presupuesto', xhr)); } });

// --- Eventos Módulo: Auditoría ---
// (Sin cambios, usa submit normal)

/* ========================   INICIALIZACIÓN   ======================== */
$(document).ready(function() {
    console.log("ERP MVC: app.js cargado y listo.");
});
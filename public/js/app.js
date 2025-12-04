/**
 * app.js - Lógica del Frontend para el ERP IUM
 * Versión consolidada (Testing Stable)
 */

/* ==========================================================================
   UTILIDADES GLOBALES Y NOTIFICACIONES
   ========================================================================== */

/**
 * Sistema de notificaciones visuales para mostrar logs y mensajes del sistema
 */
function showNotification(message, type = 'info', timeout = 4000) {
    // Crea el contenedor si no existe
    let $container = $('#notification-container');
    if (!$container.length) {
        $container = $('<div id="notification-container" style="position:fixed;top:20px;right:20px;z-index:9999;max-width:350px;"></div>');
        $('body').append($container);
    }
    // Estilos por tipo
    let bgColor = '#17a2b8', icon = 'information-circle-outline';
    if (type === 'success') { bgColor = '#28a745'; icon = 'checkmark-circle-outline'; }
    if (type === 'danger' || type === 'error') { bgColor = '#dc3545'; icon = 'close-circle-outline'; }
    if (type === 'warning') { bgColor = '#ffc107'; icon = 'alert-circle-outline'; }
    
    // Notificación HTML
    const $notif = $(`<div class="notification shadow-sm mb-2" style="background:${bgColor};color:#fff;padding:12px 18px;border-radius:8px;display:flex;align-items:center;gap:10px;font-size:1rem;animation:fadeInNotif 0.3s;"><ion-icon name="${icon}" style="font-size:1.3em;"></ion-icon><span>${message}</span></div>`);
    $container.append($notif);
    setTimeout(() => { $notif.fadeOut(400, () => $notif.remove()); }, timeout);
}

// Animación fadeInNotif
const styleNotif = document.createElement('style');
styleNotif.innerHTML = `@keyframes fadeInNotif{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}`;
document.head.appendChild(styleNotif);

/**
 * Función genérica para hacer llamadas AJAX a los controladores vía el enrutador index.php.
 */
function ajaxCall(controller, action, data = {}, method = 'POST') {
    let url = `${BASE_URL}index.php?controller=${controller}&action=${action}`;
    // Si el método es GET explícito y hay datos, los añadimos a la URL
    if (method.toUpperCase() === 'GET' && Object.keys(data).length > 0) {
        url += '&' + $.param(data);
        data = {}; // Limpiar datos para que no se envíen en el body
    }

    const ajaxOptions = {
        url: url,
        type: 'POST', // Siempre usamos POST para el envío
        dataType: 'json',
        data: data
    };

    // Simulamos otros métodos si es necesario (PUT, DELETE)
    if (method.toUpperCase() !== 'GET' && method.toUpperCase() !== 'POST') {
         ajaxOptions.data._method = method.toUpperCase();
    }

    console.log("AJAX Call:", method, url, ajaxOptions.data); 
    return $.ajax(ajaxOptions);
}

/**
 * Muestra un mensaje de error genérico en la consola y un alert.
 */
function mostrarError(action, jqXHR = null) {
    let errorMsg = `Ocurrió un error al ${action}.`;
    let serverError = 'Error desconocido o sin conexión.';
    if (jqXHR) {
        if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
            serverError = jqXHR.responseJSON.error;
        } else if (jqXHR.responseText) {
            console.error(`Respuesta cruda del servidor (${action}):`, jqXHR.responseText);
             const match = jqXHR.responseText.match(/<b>(?:Fatal error|Warning|Exception)<\/b>:(.*?)<br \/>/i);
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
    showNotification(errorMsg + " Por favor, revise la consola (F12) para más detalles técnicos.", 'danger');
}

/**
 * Asegura que un input numérico sea editable por el usuario
 */
function ensureNumberEditable(selector) {
    const $el = $(selector);
    if (!$el.length) return;
    $el.prop('readonly', false).prop('disabled', false).removeAttr('readonly').removeAttr('disabled');
    $el.off('input.ensureNum').on('input.ensureNum', function() {
        let val = $(this).val();
        val = val.replace(',', '.');
        const cleaned = val.replace(/[^0-9.]/g, '');
        const parts = cleaned.split('.');
        if (parts.length > 2) {
            $(this).val(parts[0] + '.' + parts.slice(1).join(''));
        } else {
            $(this).val(cleaned);
        }
    });
}

// Debug helpers
var _lastCatsResponseText = '';
var _lastPresResponseText = '';

// Reusable poblador para el modal de Presupuesto por Categoría.
function populatePresupuestoCategoria(presId) {
    var $form = $('#formPresupuestoCategoria');
    var $selectCat = $('#pres_categoria_categoria');
    var $selectParent = $('#pres_parent_categoria');
    if (!$form.length || !$selectCat.length || !$selectParent.length) {
        return $.Deferred().reject(new Error('Elementos del modal no encontrados')).promise();
    }

    $form[0].reset();
    $('#presupuesto_categoria_id').val('');
    $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
    $selectParent.empty().append('<option value="">Cargando presupuestos generales...</option>').prop('disabled', true);

    var pCats = ajaxCall('presupuesto', 'getAllCategorias', {}, 'GET');
    var pPres = ajaxCall('presupuesto', 'getAllPresupuestos', {}, 'GET');

    pCats.always(function(data, textStatus, jqXHR) {
        try { _lastCatsResponseText = jqXHR && jqXHR.responseText ? jqXHR.responseText : JSON.stringify(data); } catch(e) { _lastCatsResponseText = String(data); }
    });
    pPres.always(function(data, textStatus, jqXHR) {
        try { _lastPresResponseText = jqXHR && jqXHR.responseText ? jqXHR.responseText : JSON.stringify(data); } catch(e) { _lastPresResponseText = String(data); }
    });

    return $.when(pCats, pPres).then(function(catsRes, presRes) {
        var cats = catsRes && catsRes[0] ? catsRes[0] : (catsRes || []);
        var presupuestos = presRes && presRes[0] ? presRes[0] : (presRes || []);

        $selectCat.empty().append('<option value="">Seleccione...</option>');
        if (cats && Array.isArray(cats)) {
            cats.forEach(function(c){
                var catId = c.id_categoria !== undefined ? c.id_categoria : (c.id || '');
                var nombre = c.nombre || (c.cat_nombre || '');
                $selectCat.append($('<option></option>').attr('value', catId).text(nombre));
            });
        }
        $selectCat.prop('disabled', false);

        $selectParent.empty().append('<option value="">Seleccione presupuesto general...</option>');
        if (presupuestos && Array.isArray(presupuestos)) {
            var found = false;
            presupuestos.forEach(function(p){
                var id = p.id || p.id_presupuesto || '';
                var catVal = (p.id_categoria === null || p.id_categoria === undefined || p.id_categoria === '') ? null : p.id_categoria;
                if (catVal === null) {
                    var monto = p.monto_limite || p.monto || '';
                    var label = monto !== '' ? (monto + ' — ' + (p.fecha || '')) : ('Presupuesto ' + id);
                    $selectParent.append($('<option>').val(id).text(label));
                    found = true;
                }
            });
            if (!found) {
                $selectParent.empty().append('<option value="">-- No hay presupuestos generales --</option>');
            }
        }
        $selectParent.prop('disabled', false);

        // Si pasaron presId (edit), cargar datos
        if (presId) {
            return ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET').then(function(data){
                if (data && !data.error) {
                    $('#presupuesto_categoria_id').val(data.id_presupuesto || data.id);
                    if (data.parent_presupuesto) $selectParent.val(String(data.parent_presupuesto));
                    if (data.id_categoria) $selectCat.val(String(data.id_categoria));
                    $('#pres_monto_categoria').val(data.monto_limite || data.monto || '');
                    $('#pres_fecha_categoria').val(data.fecha || '');
                }
                return { cats: cats, presupuestos: presupuestos };
            });
        }

        return { cats: cats, presupuestos: presupuestos };
    }, function(err){
        throw err;
    });
}


/* ==========================================================================
   EVENTOS Y LÓGICA PARA CADA MÓDULO
   ========================================================================== */

// --- Eventos Módulo: Mi Perfil ---

// Toggle de la tabla de usuarios registrados
$(document).ready(function() {
    $('#btnToggleUsuarios').on('click', function(e) {
        e.preventDefault();
        
        var $seccion = $('#seccionUsuariosRegistrados');
        var $icon = $('#toggleUsuariosIcon');
        var $text = $('#toggleUsuariosText');
        
        if ($seccion.is(':visible')) {
            $seccion.slideUp(300);
            $icon.attr('name', 'chevron-down-outline');
            $text.text('Ver Usuarios Registrados');
        } else {
            $seccion.slideDown(300);
            $icon.attr('name', 'chevron-up-outline');
            $text.text('Ocultar Usuarios Registrados');
        }
    });
    
    // Botón de la tabla de usuarios (admin): debe tener data-username
    $(document).on('click', '.btn-cambiar-pass-usuario', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const username = $(this).data('username');
        if (!username) {
            showNotification('No se encontró el usuario a modificar.', 'danger');
            return;
        }
        var modalEditarPerfil = bootstrap.Modal.getInstance(document.getElementById('modalEditarMiPerfil'));
        if (modalEditarPerfil) { modalEditarPerfil.hide(); } else { $('#modalEditarMiPerfil').modal('hide'); }
        setTimeout(function() {
            $('#change_pass_username').val(username);
            $('#username_display').text(username);
            $('#modalCambiarPassword').modal('show');
        }, 400);
    });

    // Botón de perfil propio
    $(document).on('click', '#btnAbrirCambiarPassword', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var modalEditarPerfil = bootstrap.Modal.getInstance(document.getElementById('modalEditarMiPerfil'));
        if (modalEditarPerfil) { modalEditarPerfil.hide(); } else { $('#modalEditarMiPerfil').modal('hide'); }
        setTimeout(function() {
            $('#changepass_username').val(CURRENT_USER.user_username || CURRENT_USER.username);
            $('#modalCambiarPasswordNuevo').modal('show');
        }, 400);
    });
});

// Modal: Editar Mi Perfil
$('#modalEditarMiPerfil').on('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const userId = button ? $(button).data('id') : CURRENT_USER.id;
    const $form = $('#formEditarMiPerfil');
    
    if (!$form.length) { console.error("Form #formEditarMiPerfil no encontrado."); return; }
    
    $form[0].reset();
    $('#perfil_id').val('');
    
    if (userId && userId != CURRENT_USER.id) {
        $('#modalEditarMiPerfilTitle').text('Editar Usuario');
        $('#perfil_id').val(userId);
        $('#perfil_nombre').val($(button).data('nombre'));
        $('#perfil_username').val($(button).data('username'));
        $('#perfil_rol').val($(button).data('rol'));
        if ($('#perfil_username_readonly').length) $('#perfil_username_readonly').val($(button).data('username'));
        if ($('#perfil_rol_readonly').length) $('#perfil_rol_readonly').val($(button).data('rol'));
    } else {
        $('#modalEditarMiPerfilTitle').text('Editar Mi Perfil');
        $('#perfil_id').val(CURRENT_USER.id);
        $('#perfil_nombre').val(CURRENT_USER.nombre);
        $('#perfil_username').val(CURRENT_USER.username);
        $('#perfil_rol').val(CURRENT_USER.rol);
        if ($('#perfil_username_readonly').length) $('#perfil_username_readonly').val(CURRENT_USER.username);
        if ($('#perfil_rol_readonly').length) $('#perfil_rol_readonly').val(CURRENT_USER.rol);
    }
});

// Submit: Editar Mi Perfil
$(document).on('submit', '#formEditarMiPerfil', function(e) {
    e.preventDefault();
    ajaxCall('user', 'save', $(this).serialize())
        .done(r => {
            if (r.success) {
                $('#modalEditarMiPerfil').modal('hide');
                window.location.reload();
            } else {
                showNotification('Error al guardar: ' + (r.error || 'Verifique datos.'), 'danger');
            }
        })
        .fail((xhr) => mostrarError('guardar perfil', xhr));
});

// Modal: Registrar Nuevo Usuario
$('#modalUsuario').on('show.bs.modal', function (event) {
    const $form = $('#formUsuario');
    if (!$form.length) { console.error("Form #formUsuario no encontrado."); return; }
    $form[0].reset();
    $('#usuario_id').val('');
    $('#usuario_password').prop('required', true);
});

$(document).on('submit', '#formUsuario', function(e) { 
    e.preventDefault(); 
    ajaxCall('user', 'save', $(this).serialize()).done(r => { 
        if (r.success) {
            $('#modalUsuario').modal('hide');
            window.location.reload();
        } else {
            showNotification('Error al guardar: ' + (r.error || 'Verifique datos.'), 'danger'); 
        }
    }).fail((xhr) => mostrarError('guardar usuario', xhr)); 
});

// Eliminar usuario
$(document).on('click', '.btn-delete-user', function() { 
    const id = $(this).data('id'); 
    if (confirm('¿Eliminar este usuario?')) { 
        ajaxCall('user', 'delete', { id: id }).done(r => { 
            if (r.success) window.location.reload(); 
            else showNotification('Error al eliminar: ' + (r.error || 'No se pudo eliminar.'), 'danger'); 
        }).fail((xhr) => mostrarError('eliminar usuario', xhr)); 
    }
});

// Modal: Cambiar Contraseña (Versión Admin)
$('#modalCambiarPassword').on('show.bs.modal', function (event) { 
    const button = event.relatedTarget; 
    const username = button ? $(button).data('username') || CURRENT_USER.username : CURRENT_USER.username; 
    const $form = $('#formCambiarPassword'); 
    if(!$form.length) { console.error("Form #formCambiarPassword no encontrado."); return; } 
    $form[0].reset(); 
    $('#change_pass_username').val(username); 
    $('#username_display').text(username); 
});

$(document).on('submit', '#formCambiarPassword', function(e) { 
    e.preventDefault(); 
    ajaxCall('auth', 'changePassword', $(this).serialize()).done(r => { 
        if (r.success) { 
            $('#modalCambiarPassword').modal('hide'); 
            showNotification('Contraseña actualizada.', 'success'); 
        } else { 
            showNotification('Error: ' + (r.error || 'No se pudo cambiar.'), 'danger'); 
        } 
    }).fail((xhr) => mostrarError('cambiar contraseña', xhr)); 
});

// --- Eventos Módulo: Egresos ---
$('#modalEgreso').on('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const egresoId = button ? $(button).data('id') : null; 
    const $form = $('#formEgreso');
    const $selectCat = $('#eg_id_categoria'); 
    const $selectPres = $('#eg_id_presupuesto'); 

    if (!$form.length) { console.error("Formulario #formEgreso no encontrado."); return; }
    $form[0].reset(); $('#egreso_id').val('');
    $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
    $selectPres.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

    ensureNumberEditable('#eg_monto');

    // Cargar SUB-presupuestos primero
    ajaxCall('presupuesto', 'getSubPresupuestos', {}, 'GET')
        .done(presupuestos => {
            $selectPres.empty().append('<option value="">Seleccione un presupuesto...</option>');
            if (presupuestos && Array.isArray(presupuestos) && presupuestos.length > 0) {
                presupuestos.forEach(p => {
                    const id = p.id_presupuesto || '';
                    const nombre = p.nombre || 'Sin nombre';
                    const fecha = p.fecha || '';
                    const categoria = p.cat_nombre || 'Sin categoría';
                    const label = `${nombre} — ${fecha} (${categoria})`;
                    $selectPres.append(`<option value="${id}" data-categoria="${p.id_categoria || ''}">${label}</option>`);
                });
            } else {
                $selectPres.append('<option value="">-- No hay presupuestos --</option>');
            }
            $selectPres.prop('disabled', false);

            $selectPres.off('change.presupuestoSync').on('change.presupuestoSync', function() {
                const catId = $(this).find(':selected').data('categoria');
                if (catId) { $selectCat.val(catId.toString()); }
            });

            // Cargar categorías de Egreso
            return ajaxCall('egreso', 'getCategoriasEgreso', {}, 'GET');
        }).done(categorias => {
            $selectCat.empty().append('<option value="">Seleccione...</option>');
            if (categorias && Array.isArray(categorias) && categorias.length > 0) {
                categorias.forEach(cat => {
                    const catId = cat.id_categoria !== undefined ? cat.id_categoria : (cat.id || '');
                    const pres = cat.id_presupuesto !== undefined ? cat.id_presupuesto : '';
                    const nombre = cat.nombre !== undefined ? cat.nombre : (cat.cat_nombre || '');
                    $selectCat.append(`<option value="${catId}" data-presupuesto="${pres}">${nombre}</option>`);
                });
            } else {
                $selectCat.append('<option value="">-- No hay categorías --</option>');
            }
            $selectCat.prop('disabled', false);

            $selectCat.off('change.egresoPres').on('change.egresoPres', function() {
                const presId = $(this).find(':selected').data('presupuesto');
                if (presId) { $selectPres.val(presId.toString()); }
            });

            if (egresoId) {
                $('#modalEgresoTitle').text('Editar Egreso');
                ajaxCall('egreso', 'getEgresoData', { id: egresoId }, 'GET')
                    .done(data => {
                        if(data && !data.error && data.folio_egreso !== undefined) {
                            $('#egreso_id').val(data.folio_egreso); 
                            $('#eg_fecha').val(data.fecha);
                            $('#eg_monto').val(data.monto);
                            $selectCat.val(data.id_categoria);
                            if (data.id_presupuesto) {
                                $selectPres.val(data.id_presupuesto);
                            } else {
                                const presFromCat = $selectCat.find(':selected').data('presupuesto');
                                if (presFromCat) $selectPres.val(presFromCat.toString());
                            }
                            $('#eg_proveedor').val(data.proveedor);
                            $('#eg_destinatario').val(data.destinatario);
                            $('#eg_forma_pago').val(data.forma_pago);
                            $('#eg_documento_de_amparo').val(data.documento_de_amparo);
                            $('#eg_descripcion').val(data.descripcion);
                        } else { $('#modalEgreso').modal('hide'); showNotification('Error al cargar: '+(data.error||''), 'danger'); }
                    }).fail((xhr) => {mostrarError('cargar datos egreso', xhr); $('#modalEgreso').modal('hide');});
            } else {
                $('#modalEgresoTitle').text('Registrar Nuevo Egreso');
            }
        }).fail((xhr) => {
            console.warn('Error al cargar presupuestos/ cats egreso, intentando fallback...', xhr);
            mostrarError('cargar cats egreso/presupuestos', xhr);
        });
});

$(document).on('submit', '#formEgreso', function(e) { 
    e.preventDefault(); 
    ajaxCall('egreso', 'save', $(this).serialize())
        .done(r => { 
            if(r.success) {
                showNotification('Egreso guardado correctamente', 'success');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                showNotification('Error al guardar: ' + (r.error || 'Verifique datos.'), 'danger');
            }
        })
        .fail((xhr) => mostrarError('guardar egreso', xhr)); 
});

$(document).on('click', '.btn-del-egreso', function() { 
    const id = $(this).data('id'); 
    if (confirm('¿Eliminar este egreso?')) { 
        ajaxCall('egreso', 'delete', { id: id })
            .done(r => { 
                if(r.success) {
                    $(document).trigger('egresoEliminado'); 
                    window.location.reload(); 
                } else {
                    showNotification('Error al eliminar: ' + (r.error || 'Error.'), 'danger'); 
                }
            })
            .fail((xhr) => mostrarError('eliminar egreso', xhr)); 
    } 
});


// --- Eventos Módulo: Ingresos (CON SISTEMA DE PAGOS DIVIDIDOS) ---
const MAX_PAGOS = 5;
let contadorPagos = 0;

$('#modalIngreso').on('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const ingresoId = button ? $(button).data('id') : null;
    const $form = $('#formIngreso');
    const $selectCat = $('#in_id_categoria');

    if (!$form.length) { console.error("Formulario #formIngreso no encontrado."); return; }
    
    $form[0].reset();
    $('#ingreso_id').val('');
    contadorPagos = 0;
    $('#contenedor_pagos_parciales').empty();
    $('#seccion_pago_unico').show();
    $('#seccion_cobro_dividido').hide();
    $('#toggleCobroDividido').prop('checked', false);
    $('#in_monto').prop('readonly', false).prop('disabled', false).css('background-color', '');
    $('#in_metodo_unico').val('');
    $('#in_monto_unico').val('');
    
    $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

    ajaxCall('ingreso', 'getCategoriasIngreso', {}, 'GET')
        .done(categorias => {
            $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');
            if (categorias && Array.isArray(categorias) && categorias.length > 0) {
                categorias.forEach(cat => {
                    if (cat && cat.id_categoria !== undefined && cat.nombre !== undefined) {
                        $selectCat.append(`<option value="${cat.id_categoria}">${cat.nombre}</option>`);
                    }
                });
            }
            $selectCat.prop('disabled', false);

            if (ingresoId) {
                $('#modalIngresoTitle').text('Editar Ingreso');
                ajaxCall('ingreso', 'getIngresoData', { id: ingresoId }, 'GET')
                    .done(data => {
                        if(data && !data.error && data.folio_ingreso !== undefined) {
                            $('#ingreso_id').val(data.folio_ingreso);
                            $('#in_fecha').val(data.fecha);
                            $('#in_monto').val(data.monto);
                            $('#in_alumno').val(data.alumno);
                            $('#in_matricula').val(data.matricula);
                            $('#in_nivel').val(data.nivel);
                            $('#in_programa').val(data.programa);
                            $('#in_grado').val(data.grado);
                            $('#in_modalidad').val(data.modalidad);
                            $('#in_grupo').val(data.grupo);
                            $selectCat.val(data.id_categoria);
                            $('#in_mes_correspondiente').val(data.mes_correspondiente);
                            $('#in_anio').val(data.anio);
                            $('#in_observaciones').val(data.observaciones);
                            
                            if (data.pagos_parciales && data.pagos_parciales.length > 0) {
                                $('#toggleCobroDividido').prop('checked', true).trigger('change');
                                data.pagos_parciales.forEach(pago => {
                                    agregarFilaPago(pago.metodo_pago, pago.monto);
                                });
                            } else if (data.metodo_de_pago && data.metodo_de_pago !== 'Mixto') {
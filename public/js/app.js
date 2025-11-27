/**
 * app.js - Lógica del Frontend para el ERP IUM (Versión MVC Final Corregida)
 * Maneja interacciones de usuario (clics, formularios) para TODOS los módulos
 * y se comunica con los controladores PHP vía AJAX.
 */

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
 * @param {string} controller Nombre del controlador.
 * @param {string} action Nombre de la acción/método.
 * @param {object} [data={}] Datos a enviar (vía POST).
 * @param {string} [method='POST'] Método HTTP (GET simulado por URL).
 * @returns {jqXHR} Promesa jQuery AJAX.
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
             // Intenta extraer el error de PHP si es una respuesta HTML
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
 * Asegura que un input numérico sea editable por el usuario (quita disabled/readonly
 * y normaliza el evento input para permitir solo dígitos, punto y comas conversion).
 */
function ensureNumberEditable(selector) {
    const $el = $(selector);
    if (!$el.length) return;
    $el.prop('readonly', false).prop('disabled', false).removeAttr('readonly').removeAttr('disabled');
    $el.off('input.ensureNum').on('input.ensureNum', function() {
        let val = $(this).val();
        // Reemplazar comas por punto para facilitar entrada con teclado local
        val = val.replace(',', '.');
        // Permitir sólo números y un punto decimal
        const cleaned = val.replace(/[^0-9.]/g, '');
        // Evitar múltiples puntos
        const parts = cleaned.split('.');
        if (parts.length > 2) {
            $(this).val(parts[0] + '.' + parts.slice(1).join(''));
        } else {
            $(this).val(cleaned);
        }
    });
}

// Debug helpers (kept global so other handlers can access the last AJAX raw text)
var _lastCatsResponseText = '';
var _lastPresResponseText = '';

// Reusable poblador para el modal de Presupuesto por Categoría.
// Acepta opcionalmente presId (para edición) y retorna una Promise.
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

    // Capture raw responseText for debugging
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
        // Propagar error
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
        console.log('Toggle button clicked!');
        
        var $seccion = $('#seccionUsuariosRegistrados');
        var $icon = $('#toggleUsuariosIcon');
        var $text = $('#toggleUsuariosText');
        
        console.log('Sección encontrada:', $seccion.length);
        console.log('Visible actualmente:', $seccion.is(':visible'));
        
        if ($seccion.is(':visible')) {
            console.log('Ocultando sección...');
            $seccion.slideUp(300);
            $icon.attr('name', 'chevron-down-outline');
            $text.text('Ver Usuarios Registrados');
        } else {
            console.log('Mostrando sección...');
            $seccion.slideDown(300);
            $icon.attr('name', 'chevron-up-outline');
            $text.text('Ocultar Usuarios Registrados');
        }
    });
    
    // También registrar con $(document).on por si acaso
    $(document).off('click.toggleUsuarios').on('click.toggleUsuarios', '#btnToggleUsuarios', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Event delegation toggle clicked');
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
    
    // Si viene de editar otro usuario desde la tabla
    if (userId && userId != CURRENT_USER.id) {
        $('#modalEditarMiPerfilTitle').text('Editar Usuario');
        $('#perfil_id').val(userId);
        $('#perfil_nombre').val($(button).data('nombre'));
        $('#perfil_username').val($(button).data('username'));
        $('#perfil_rol').val($(button).data('rol'));
        // Llenar campos readonly si existen
        if ($('#perfil_username_readonly').length) {
            $('#perfil_username_readonly').val($(button).data('username'));
        }
        if ($('#perfil_rol_readonly').length) {
            $('#perfil_rol_readonly').val($(button).data('rol'));
        }
    } else {
        // Editar mi propio perfil
        $('#modalEditarMiPerfilTitle').text('Editar Mi Perfil');
        $('#perfil_id').val(CURRENT_USER.id);
        $('#perfil_nombre').val(CURRENT_USER.nombre);
        $('#perfil_username').val(CURRENT_USER.username);
        $('#perfil_rol').val(CURRENT_USER.rol);
        // Llenar campos readonly si existen (usuarios no-SU)
        if ($('#perfil_username_readonly').length) {
            $('#perfil_username_readonly').val(CURRENT_USER.username);
        }
        if ($('#perfil_rol_readonly').length) {
            $('#perfil_rol_readonly').val(CURRENT_USER.rol);
        }
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



// Refuerzo: solo el modal correcto para cada caso
$(document).ready(function() {
    // Botón amarillo editar usuario: solo abre el modal de editar usuario
    $(document).on('click', '.btn-edit-user', function(e) {
        // No hacer nada especial, Bootstrap maneja el modal
        // Si hay algún modal abierto, se cierra automáticamente
    });

    // Botón candado cambiar contraseña: solo abre el modal de contraseña
    $(document).on('click', '.btn-change-pass', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const username = $(this).data('username');
        if (!username) {
            showNotification('No se encontró el usuario para cambiar contraseña.', 'danger');
            return;
        }
        // Cerrar modal de perfil si está abierto
        var modalEditarPerfil = bootstrap.Modal.getInstance(document.getElementById('modalEditarMiPerfil'));
        if (modalEditarPerfil) {
            modalEditarPerfil.hide();
        } else {
            $('#modalEditarMiPerfil').modal('hide');
        }
        // Resetear y setear datos antes de mostrar el modal
        var $form = $('#formCambiarPassword');
        if ($form.length) $form[0].reset();
        $('#change_pass_username').val(username);
        $('#username_display').text(username);
        $('#modalCambiarPassword').modal('show');
    });

    // Botón de perfil propio (sin data-username): usa el mismo modal universal
    $(document).on('click', '#btnAbrirCambiarPassword', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Cerrar modal de perfil si está abierto
        var modalEditarPerfil = bootstrap.Modal.getInstance(document.getElementById('modalEditarMiPerfil'));
        if (modalEditarPerfil) {
            modalEditarPerfil.hide();
        } else {
            $('#modalEditarMiPerfil').modal('hide');
        }
        // Resetear y setear datos SIEMPRE antes de mostrar el modal
        var username = CURRENT_USER.user_username || CURRENT_USER.username;
        var $form = $('#formCambiarPassword');
        if ($form.length) $form[0].reset();
        $('#change_pass_username').val(username);
        $('#username_display').text(username);
        console.log('[DEBUG] Abriendo modal #modalCambiarPassword para:', username);
        console.log('[DEBUG] Valor en input oculto:', $('#change_pass_username').val());
        console.log('[DEBUG] Texto en label:', $('#username_display').text());
        $('#modalCambiarPassword').modal('show');
    });
});


// Modal: Registrar Nuevo Usuario (formulario separado)
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

// Modal: Cambiar Contraseña (Versión Admin - para otros usuarios)
// Eliminado el handler 'show.bs.modal' para evitar sobreescribir el username correcto.

$(document).on('submit', '#formCambiarPassword', function(e) { 
    e.preventDefault();
    var username = $('#change_pass_username').val();
    var password = $('#change_pass_password').val();
    console.log('[DEBUG][submit] Enviando cambio de contraseña para:', username, 'con password:', password);
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
    const egresoId = button ? $(button).data('id') : null; // Este ID es folio_egreso
    const $form = $('#formEgreso');
    const $selectCat = $('#eg_id_categoria'); // select de categorías
    const $selectPres = $('#eg_id_presupuesto'); // nuevo select de presupuestos

    if (!$form.length) { console.error("Formulario #formEgreso no encontrado."); return; }
    $form[0].reset(); $('#egreso_id').val('');
    $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
    $selectPres.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

    // Asegurar que el campo monto es editable (algunas versiones de navegadores y estilos podían marcarlo como no editable)
    ensureNumberEditable('#eg_monto');

    // 1. Cargar SUB-presupuestos primero (solo presupuestos con parent, para poblar el select)
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

            // Auto-sync: cuando se selecciona un presupuesto, auto-seleccionar su categoría
            $selectPres.off('change.presupuestoSync').on('change.presupuestoSync', function() {
                const catId = $(this).find(':selected').data('categoria');
                if (catId) {
                    $selectCat.val(catId.toString());
                }
            });

            // 2. Cargar categorías de Egreso (ID, Nombre y su id_presupuesto si existe)
            return ajaxCall('egreso', 'getCategoriasEgreso', {}, 'GET');
        }).done(categorias => {
            $selectCat.empty().append('<option value="">Seleccione...</option>');
            if (categorias && Array.isArray(categorias) && categorias.length > 0) {
                categorias.forEach(cat => {
                    // cat may include id_presupuesto
                    const catId = cat.id_categoria !== undefined ? cat.id_categoria : (cat.id || '');
                    const pres = cat.id_presupuesto !== undefined ? cat.id_presupuesto : '';
                    const nombre = cat.nombre !== undefined ? cat.nombre : (cat.cat_nombre || '');
                    $selectCat.append(`<option value="${catId}" data-presupuesto="${pres}">${nombre}</option>`);
                });
            } else {
                $selectCat.append('<option value="">-- No hay categorías --</option>');
            }
            $selectCat.prop('disabled', false);

            // Cuando cambie la categoría, intentar seleccionar el presupuesto asociado
            $selectCat.off('change.egresoPres').on('change.egresoPres', function() {
                const presId = $(this).find(':selected').data('presupuesto');
                if (presId) {
                    $selectPres.val(presId.toString());
                }
            });

            // 3. Si es edición, cargar datos del egreso
            if (egresoId) {
                $('#modalEgresoTitle').text('Editar Egreso');
                ajaxCall('egreso', 'getEgresoData', { id: egresoId }, 'GET')
                    .done(data => {
                        if(data && !data.error && data.folio_egreso !== undefined) {
                            $('#egreso_id').val(data.folio_egreso); // Guardar PK
                            $('#eg_fecha').val(data.fecha);
                            $('#eg_monto').val(data.monto);
                            $selectCat.val(data.id_categoria);
                            // Si el egreso trae id_presupuesto, seleccionarlo en el select
                            if (data.id_presupuesto) {
                                $selectPres.val(data.id_presupuesto);
                            } else {
                                // si no, seleccionar el presupuesto asociado a la categoría (si existe)
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
            // Intentar fallback: si falla el endpoint combinado, solicitar categorías desde el controlador 'categoria'
            console.warn('Error al cargar presupuestos/ cats egreso, intentando fallback a controlador categoria...', xhr);
            mostrarError('cargar cats egreso/presupuestos', xhr);
            // Intento fallback para categorías (usar el endpoint de Presupuestos que lista categorías)
            ajaxCall('presupuesto', 'getAllCategorias', {}, 'GET')
                .done(catsFallback => {
                    $selectCat.empty().append('<option value="">Seleccione...</option>');
                    if (catsFallback && Array.isArray(catsFallback) && catsFallback.length > 0) {
                        catsFallback.forEach(cat => {
                            const catId = cat.id_categoria !== undefined ? cat.id_categoria : (cat.id || '');
                            const pres = cat.id_presupuesto !== undefined ? cat.id_presupuesto : '';
                            const nombre = cat.nombre !== undefined ? cat.nombre : (cat.cat_nombre || '');
                            $selectCat.append(`<option value="${catId}" data-presupuesto="${pres}">${nombre}</option>`);
                        });
                    } else {
                        $selectCat.append('<option value="">-- No hay categorías --</option>');
                    }
                    $selectCat.prop('disabled', false);
                    // desbloquear también el select de presupuestos si quedó bloqueado
                    $selectPres.prop('disabled', false);
                }).fail(() => {
                    $selectCat.empty().append('<option value="">Error</option>').prop('disabled', false);
                    $selectPres.empty().append('<option value="">Error</option>').prop('disabled', false);
                });
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
                    $(document).trigger('egresoEliminado'); // Trigger para actualizar alertas
                    window.location.reload(); 
                } else {
                    showNotification('Error al eliminar: ' + (r.error || 'Error.'), 'danger'); 
                }
            })
            .fail((xhr) => mostrarError('eliminar egreso', xhr)); 
    } 
});


// --- Eventos Módulo: Ingresos (CON SISTEMA DE PAGOS DIVIDIDOS) ---

// Variable global para contar pagos parciales
const MAX_PAGOS = 5; // Límite máximo de métodos de pago en cobro dividido
let contadorPagos = 0;

// Evento cuando se abre el modal de ingreso
$('#modalIngreso').on('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const ingresoId = button ? $(button).data('id') : null;
    const $form = $('#formIngreso');
    const $selectCat = $('#in_id_categoria');

    if (!$form.length) { console.error("Formulario #formIngreso no encontrado."); return; }
    
    // Reset del formulario
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

    // Cargar categorías de Ingreso
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

            // Si es edición, cargar datos
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
                            
                            // Cargar pagos parciales si existen
                            if (data.pagos_parciales && data.pagos_parciales.length > 0) {
                                $('#toggleCobroDividido').prop('checked', true).trigger('change');
                                data.pagos_parciales.forEach(pago => {
                                    agregarFilaPago(pago.metodo_pago, pago.monto);
                                });
                            } else if (data.metodo_de_pago && data.metodo_de_pago !== 'Mixto') {
                                $('#toggleCobroDividido').prop('checked', false).trigger('change');
                                $('#in_metodo_unico').val(data.metodo_de_pago);
                            }
                        } else {
                            $('#modalIngreso').modal('hide');
                            showNotification('Error al cargar datos del ingreso: '+(data.error || 'Registro no encontrado.'), 'danger');
                        }
                    }).fail((xhr) => {
                        mostrarError('cargar datos ingreso', xhr);
                        $('#modalIngreso').modal('hide');
                    });
            } else {
                $('#modalIngresoTitle').text('Registrar Nuevo Ingreso');
                $('#in_anio').val(new Date().getFullYear());
                // Asegurar que inicia en modo pago único
                $('#seccion_pago_unico').show();
                $('#seccion_cobro_dividido').hide();
                $('#toggleCobroDividido').prop('checked', false);
            }
        }).fail((xhr) => {
            mostrarError('cargar categorías ingreso', xhr);
        });
});

// Toggle entre pago único y cobro dividido
$(document).on('change', '#toggleCobroDividido', function() {
    const esDividido = $(this).is(':checked');
    const monto = parseFloat($('#in_monto').val()) || 0;
    
    console.log('Toggle cambió a:', esDividido);
    
    // Asegurar que el campo monto SIEMPRE esté habilitado
    $('#in_monto').prop('readonly', false).prop('disabled', false).css('background-color', '');
    
    if (esDividido) {
        console.log('Activando cobro dividido...');
        // Activar cobro dividido
        $('#seccion_pago_unico').hide();
        $('#seccion_cobro_dividido').show();
        
        console.log('Contenedor pagos parciales tiene', $('#contenedor_pagos_parciales').children().length, 'hijos');
        
        // Siempre limpiar y agregar exactamente 2 filas
        $('#contenedor_pagos_parciales').empty();
        contadorPagos = 0;
        console.log('Agregando primera fila...');
        agregarFilaPago('', ''); // Primera fila vacía
        console.log('Agregando segunda fila...');
        agregarFilaPago('', ''); // Segunda fila vacía
        console.log('Filas agregadas. Total ahora:', $('.pago-parcial-item').length);
        
        actualizarResumenPagos();
    } else {
        console.log('Desactivando cobro dividido...');
        // Desactivar cobro dividido (volver a pago único)
        $('#seccion_pago_unico').show();
        $('#seccion_cobro_dividido').hide();
        
        // Sincronizar monto único con el total
        if (monto > 0) {
            $('#in_monto_unico').val(monto.toFixed(2));
        }
        
        // Limpiar pagos parciales
        $('#contenedor_pagos_parciales').empty();
        contadorPagos = 0;
    }
});

// Actualizar monto único cuando cambia el monto total
$(document).on('input', '#in_monto', function() {
    const monto = parseFloat($(this).val()) || 0;
    
    // Si está en modo pago único, sincronizar el monto
    if (!$('#toggleCobroDividido').is(':checked')) {
        $('#in_monto_unico').val(monto.toFixed(2));
    }
    
    // Si está en modo cobro dividido, actualizar resumen
    if ($('#toggleCobroDividido').is(':checked')) {
        actualizarResumenPagos();
    }
});

// Agregar nueva fila de pago parcial (con límite)
$(document).on('click', '#btnAgregarPago', function() {
    const totalActual = $('.pago-parcial-item').length;
    if (totalActual >= MAX_PAGOS) {
        showNotification(`Ha alcanzado el máximo de ${MAX_PAGOS} métodos de pago.`, 'warning');
        $(this).prop('disabled', true);
        return;
    }
    agregarFilaPago();
});

// Función para agregar fila de pago
function agregarFilaPago(metodo = '', monto = '') {
    const totalActual = $('.pago-parcial-item').length;
    if (totalActual >= MAX_PAGOS) {
        showNotification(`No se pueden agregar más de ${MAX_PAGOS} métodos de pago.`, 'warning');
        $('#btnAgregarPago').prop('disabled', true);
        return;
    }
    contadorPagos++;
    const html = `
        <div class="row g-3 mb-2 pago-parcial-item" data-pago-id="${contadorPagos}">
            <div class="col-md-6">
                <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm pago-metodo" required>
                    <option value="">Seleccione...</option>
                    <option value="Efectivo" ${metodo === 'Efectivo' ? 'selected' : ''}>Efectivo</option>
                    <option value="Transferencia" ${metodo === 'Transferencia' ? 'selected' : ''}>Transferencia</option>
                    <option value="Tarjeta Crédito" ${metodo === 'Tarjeta Crédito' ? 'selected' : ''}>Tarjeta Crédito</option>
                    <option value="Tarjeta Débito" ${metodo === 'Tarjeta Débito' ? 'selected' : ''}>Tarjeta Débito</option>
                    <option value="Depósito" ${metodo === 'Depósito' ? 'selected' : ''}>Depósito</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Monto <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" min="0.01" class="form-control pago-monto" placeholder="0.00" value="${monto}" required>
                    <button class="btn btn-outline-danger btn-eliminar-pago ${contadorPagos === 1 ? 'd-none' : ''}" type="button" title="Eliminar">
                        <ion-icon name="close-circle-outline"></ion-icon>
                    </button>
                </div>
            </div>
        </div>
    `;
    $('#contenedor_pagos_parciales').append(html);
    actualizarResumenPagos();
    actualizarBotonesEliminar();

    // Deshabilitar botón si alcanzamos el máximo
    if ($('.pago-parcial-item').length >= MAX_PAGOS) {
        $('#btnAgregarPago').prop('disabled', true);
    } else {
        $('#btnAgregarPago').prop('disabled', false);
    }
}

// Actualizar visibilidad de botones eliminar
function actualizarBotonesEliminar() {
    const totalFilas = $('.pago-parcial-item').length;
    if (totalFilas === 1) {
        $('.btn-eliminar-pago').addClass('d-none');
    } else {
        $('.btn-eliminar-pago').removeClass('d-none');
    }
}

// Eliminar fila de pago
$(document).on('click', '.btn-eliminar-pago', function() {
    if ($('.pago-parcial-item').length > 1) {
        $(this).closest('.pago-parcial-item').remove();
        contadorPagos = $('.pago-parcial-item').length;
        actualizarResumenPagos();
        actualizarBotonesEliminar();
    } else {
        showNotification('Debe mantener al menos un método de pago en el cobro dividido.', 'warning');
    }
});

// Asegurar que al eliminar también se reactive el botón de añadir si está por debajo del máximo
$(document).on('DOMNodeRemoved', '#contenedor_pagos_parciales', function() {
    if ($('.pago-parcial-item').length < MAX_PAGOS) {
        $('#btnAgregarPago').prop('disabled', false);
    }
});

// Actualizar resumen cuando cambian los montos
$(document).on('input', '.pago-monto', function() {
    actualizarResumenPagos();
});

// Función para actualizar el resumen de pagos
function actualizarResumenPagos() {
    const montoTotal = parseFloat($('#in_monto').val()) || 0;
    let sumaParciales = 0;
    
    $('.pago-monto').each(function() {
        const valor = parseFloat($(this).val()) || 0;
        sumaParciales += valor;
    });
    
    const diferencia = montoTotal - sumaParciales;
    
    $('#display_monto_total').text('$' + montoTotal.toFixed(2));
    $('#display_suma_parciales').text('$' + sumaParciales.toFixed(2));
    $('#display_diferencia').text('$' + Math.abs(diferencia).toFixed(2));
    
    // Cambiar colores y etiquetas según el estado
    const $displayDif = $('#display_diferencia');
    const $labelDif = $('#label_diferencia');
    
    if (Math.abs(diferencia) < 0.01) {
        // Cuadrado - Verde
        $displayDif.removeClass('text-danger text-warning').addClass('text-success');
        $labelDif.html('✓ Cuadrado <span class="badge bg-success ms-2">OK</span>');
    } else if (diferencia > 0) {
        // Falta - Rojo
        $displayDif.removeClass('text-success text-warning').addClass('text-danger');
        $labelDif.html('⚠ Pendiente <span class="badge bg-danger ms-2">FALTA</span>');
    } else {
        // Exceso - Amarillo
        $displayDif.removeClass('text-success text-danger').addClass('text-warning');
        $labelDif.html('⚠ Exceso <span class="badge bg-warning text-dark ms-2">SOBRA</span>');
    }
}

// Envío de formulario con validación de pagos
$(document).on('submit', '#formIngreso', function(e) { 
    e.preventDefault();
    
    const esDividido = $('#toggleCobroDividido').is(':checked');
    
    let formData = $(this).serializeArray();
    let dataObj = {};
    formData.forEach(item => {
        dataObj[item.name] = item.value;
    });
    
    // Según el tipo de pago, agregar método de pago
    if (!esDividido) {
        // Pago único
        const metodoUnico = $('#in_metodo_unico').val();
        if (!metodoUnico) {
            showNotification('Debe seleccionar un método de pago.', 'warning');
            return;
        }
        dataObj.metodo_de_pago = metodoUnico;
        dataObj.pagos = JSON.stringify([{
            metodo: metodoUnico,
            monto: parseFloat($('#in_monto').val())
        }]);
    } else {
        // Cobro dividido
        const montoTotal = parseFloat($('#in_monto').val()) || 0;
        let sumaParciales = 0;
        const pagos = [];
        
        let valido = true;
        $('.pago-parcial-item').each(function() {
            const metodo = $(this).find('.pago-metodo').val();
            const monto = parseFloat($(this).find('.pago-monto').val()) || 0;
            
            if (!metodo || monto <= 0) {
                valido = false;
                return false;
            }
            
            sumaParciales += monto;
            pagos.push({ metodo: metodo, monto: monto });
        });
        
        if (!valido) {
            showNotification('Todos los pagos parciales deben tener un método y un monto válido.', 'warning');
            return;
        }
        
        const diferencia = Math.abs(montoTotal - sumaParciales);
        if (diferencia >= 0.01) {
            showNotification(`La suma de los pagos parciales ($${sumaParciales.toFixed(2)}) no coincide con el monto total ($${montoTotal.toFixed(2)}). Diferencia: $${diferencia.toFixed(2)}`, 'warning');
            return;
        }
        
        dataObj.metodo_de_pago = 'Mixto';
        dataObj.pagos = JSON.stringify(pagos);
    }
    
    ajaxCall('ingreso', 'save', dataObj).done(r => { 
        if(r.success) {
            showNotification('Ingreso guardado correctamente', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showNotification('Error al guardar: ' + (r.error || 'Verifique datos.'), 'danger');
        }
    }).fail((xhr) => mostrarError('guardar ingreso', xhr)); 
});

// Eliminación de ingreso
$(document).on('click', '.btn-del-ingreso', function() { 
    const id = $(this).data('id'); 
    if (confirm('¿Eliminar este ingreso? Se eliminarán también todos los pagos parciales asociados.')) { 
        ajaxCall('ingreso', 'delete', { id: id }).done(r => { 
            if(r.success) window.location.reload(); 
            else showNotification('Error al eliminar: ' + (r.error || 'Error.'), 'danger'); 
        }).fail((xhr) => mostrarError('eliminar ingreso', xhr)); 
    }
});


// --- Eventos Módulo: Categorías ---

// Toggle visibilidad del campo concepto según tipo
$(document).on('change', '#cat_tipo', function() {
    const tipo = $(this).val();
    if (tipo === 'Ingreso') {
        $('#div_cat_concepto').slideDown(200);
        $('#cat_concepto').prop('required', true);
    } else {
        $('#div_cat_concepto').slideUp(200);
        $('#cat_concepto').val('').prop('required', false);
    }
});

$('#modalCategoria').on('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const catId = button ? $(button).data('id') : null;
    const $form = $('#formCategoria');
    
    if (!$form.length) {
        console.error("Form #formCategoria no encontrado.");
        return;
    }
    
    // Reset form y ocultar alertas
    $form[0].reset();
    $('#categoria_id').val('');
    $('#categoria_no_borrable').val('0');
    $('#div_cat_concepto').hide();
    $('#alert_categoria_protegida').hide();
    $('#cat_concepto').prop('required', false);
    
    if (catId) {
        $('#modalCategoriaTitle').text('Editar Categoría');
        ajaxCall('categoria', 'getCategoriaData', { id: catId }, 'GET').done(data => {
            if(data && !data.error) {
                $('#categoria_id').val(data.id_categoria);
                $('#cat_nombre').val(data.nombre);
                $('#cat_tipo').val(data.tipo);
                $('#cat_descripcion').val(data.descripcion);
                $('#categoria_no_borrable').val(data.no_borrable || 0);
                
                // Manejar campo concepto
                if (data.tipo === 'Ingreso') {
                    $('#div_cat_concepto').show();
                    $('#cat_concepto').val(data.concepto || '').prop('required', true);
                }
                
                // Mostrar alerta si es categoría protegida
                if (data.no_borrable == 1) {
                    $('#alert_categoria_protegida').show();
                }
            } else {
                $('#modalCategoria').modal('hide');
                showNotification('Error al cargar: '+(data.error||''), 'danger');
            }
        }).fail((xhr) => {
            mostrarError('cargar datos categoría', xhr);
            $('#modalCategoria').modal('hide');
        });
    } else {
        $('#modalCategoriaTitle').text('Agregar Nueva Categoría');
        // Por defecto mostrar campo concepto para nuevas categorías tipo Ingreso
        if ($('#cat_tipo').val() === 'Ingreso') {
            $('#div_cat_concepto').show();
            $('#cat_concepto').prop('required', true);
        }
    }
});

$(document).on('submit', '#formCategoria', function(e) {
    e.preventDefault();
    
    // Validación adicional: si es Ingreso, concepto es requerido
    const tipo = $('#cat_tipo').val();
    const concepto = $('#cat_concepto').val();
    
    if (tipo === 'Ingreso' && !concepto) {
        showNotification('El campo Concepto es requerido para categorías de tipo Ingreso.', 'warning');
        return;
    }
    
    ajaxCall('categoria', 'save', $(this).serialize()).done(r => {
        if(r.success) {
            window.location.reload();
        } else {
            alert('Error: ' + (r.error || 'Error al guardar.'));
        }
    }).fail((xhr) => mostrarError('guardar categoría', xhr));
});

$(document).on('click', '.btn-del-categoria', function() {
    const noBorrable = $(this).data('no-borrable');
    
    if (noBorrable == 1) {
        alert('Esta categoría es del sistema y no puede ser eliminada.');
        return;
    }
    
    if (confirm('¿Eliminar esta categoría?')) {
        ajaxCall('categoria', 'delete', { id: $(this).data('id') }).done(r => {
            if(r.success) {
                window.location.reload();
            } else {
                alert('Error: ' + (r.error || 'Esta categoría no puede ser eliminada porque está en uso.'));
            }
        }).fail((xhr) => mostrarError('eliminar categoría', xhr));
    }
});

$(document).on('click', '#btnRefrescarCategorias', () => window.location.reload());

// --- Eventos Módulo: Presupuestos ---

// Handler para botón "Nuevo Presupuesto General"
$(document).on('click', '#btnNuevoPresupuestoGeneral, #btnPrimerPresupuesto', function() {
    // Forzar tipo general al abrir el modal
    setTimeout(function() {
        $('#pres_tipo').val('general').trigger('change');
    }, 100);
});

// Handler para botón "Agregar Sub-presupuesto"
$(document).on('click', '.btn-add-sub-presupuesto', function() {
    const parentId = $(this).data('parent-id');
    // Pre-seleccionar el presupuesto padre en el modal de categoría
    $('#modalPresupuestoCategoria').one('shown.bs.modal', function() {
        setTimeout(function() {
            $('#pres_parent_categoria').val(parentId);
        }, 100);
    });
});

$('#modalPresupuesto').on('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const presId = button ? $(button).data('id') : null;
    const $form = $('#formPresupuesto');
    const $selectCat = $('#pres_categoria');
    if (!$form.length) { console.error("Form #formPresupuesto no encontrado."); return; }
    $form[0].reset();
    $('#presupuesto_id').val('');
    // Debug: asegurar que el contenedor parent y el input monto no queden bloqueados
    console.log('DEBUG modalPresupuesto: asegurar visibilidad y editabilidad iniciales');
    // Mantener el contenedor oculto por defecto (se mostrará si el tipo es 'categoria')
    if ($('#div_parent_presupuesto').length) {
        // no forzamos visibilidad inmediata, dejamos que toggleTipo controle la visibilidad
        $('#div_parent_presupuesto').hide();
    }
    if ($('#pres_parent').length) {
        $('#pres_parent').prop('disabled', true).removeAttr('readonly');
    }
    if ($('#pres_monto').length) {
        $('#pres_monto').prop('disabled', false).prop('readonly', false).removeAttr('readonly');
    }
    $selectCat.empty().append('<option>Cargando...</option>').prop('disabled', true);

    // Cargar categorías y además cargar presupuestos generales para el select padre
    const cargarCategorias = ajaxCall('presupuesto', 'getAllCategorias', {}, 'GET');
    const cargarGenerales = ajaxCall('presupuesto', 'getAllPresupuestos', {}, 'GET');

    $.when(cargarCategorias, cargarGenerales).done(function(catsRes, presRes) {
    const cats = catsRes[0];
    const presupuestos = presRes[0];
    console.log('DEBUG modalPresupuesto - cats:', cats);
    console.log('DEBUG modalPresupuesto - presupuestos (raw):', presupuestos);

        $selectCat.empty().append('<option value="">Seleccione...</option>');
        if (cats && cats.length > 0) {
            cats.forEach(c => {
                const catId = c.id_categoria !== undefined ? c.id_categoria : (c.id || '');
                const nombre = c.nombre || (c.cat_nombre || '');
                const tipo = c.tipo || '';
                $selectCat.append($('<option></option>').attr('value', catId).text(`${nombre}${tipo ? ' ('+tipo+')' : ''}`));
            });
        }
        $selectCat.prop('disabled', false);

        // Poblar select de presupuestos generales (solo aquellos sin id_categoria)
        const $selectParent = $('#pres_parent');
        $selectParent.empty().append('<option value="">Seleccione presupuesto general...</option>');
        if (presupuestos && Array.isArray(presupuestos) && presupuestos.length > 0) {
            let foundGeneral = false;
            presupuestos.forEach(p => {
                const id = p.id || p.id_presupuesto || '';
                // Detectar presupuesto general: id_categoria NULL/undefined/empty
                const catVal = (p.id_categoria === null || p.id_categoria === undefined || p.id_categoria === '' ) ? null : p.id_categoria;
                if (catVal === null) {
                    const monto = p.monto_limite || p.monto || '';
                    const label = monto !== '' ? `${monto} — ${p.fecha || ''}` : `Presupuesto ${id}`;
                    $selectParent.append(`<option value="${id}">${label}</option>`);
                    foundGeneral = true;
                }
            });
            if (!foundGeneral) {
                console.warn('No se encontraron presupuestos generales para poblar #pres_parent');
            } else {
                console.log('DEBUG #pres_parent options count after populate:', $selectParent.find('option').length);
            }
        }
        $selectParent.prop('disabled', false);

        // Inicializar tipo (general / categoria) y toggle UI
        const $tipo = $('#pres_tipo');
        function toggleTipo() {
            const val = $tipo.val();
            console.log('DEBUG pres_tipo change ->', val);
            if (val === 'general') {
                console.log('DEBUG toggleTipo -> ocultando parent y categoria');
                $('#div_parent_presupuesto').hide();
                $('#div_categoria').hide();
                $('#pres_categoria').prop('required', false);
                $('#pres_parent').prop('required', false);
                // Deshabilitar select padre cuando es general
                $('#pres_parent').prop('disabled', true);
            } else {
                console.log('DEBUG toggleTipo -> mostrando parent y categoria');
                // Mostrar claramente el contenedor y el select padre
                $('#div_parent_presupuesto').show().css('display', 'block');
                $('#div_categoria').show();
                $('#pres_categoria').prop('required', true);
                // Asegurar que el select padre esté habilitado y editable
                $('#pres_parent').prop('required', true).prop('disabled', false).prop('readonly', false).removeAttr('readonly');
                // Asegurar que el input de monto esté editable
                $('#pres_monto').prop('disabled', false).prop('readonly', false).removeAttr('readonly');
            }
        }
        $tipo.off('change.presTipo').on('change.presTipo', toggleTipo);
        toggleTipo();

        if (presId) {
            // Verificar si es un presupuesto general o por categoría para decidir qué modal usar
            ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET').done(data => {
                if (data && !data.error) {
                    const isGeneral = !data.id_categoria || data.id_categoria === null;
                    
                    if (isGeneral) {
                        // Es presupuesto general - usar modal actual
                        $('#modalPresupuestoTitle').text('Editar Presupuesto General');
                        $('#presupuesto_id').val(data.id_presupuesto || data.id);
                        $('#pres_monto').val(data.monto_limite || data.monto || '');
                        $('#pres_fecha').val(data.fecha || '');
                        $tipo.val('general');
                        toggleTipo();
                    } else {
                        // Es presupuesto por categoría - cerrar este modal y abrir el correcto
                        $('#modalPresupuesto').modal('hide');
                        setTimeout(() => {
                            $('#modalPresupuestoCategoria').modal('show');
                            // Los datos se cargarán cuando se abra ese modal
                        }, 300);
                        return;
                    }
                } else {
                    $('#modalPresupuesto').modal('hide');
                    alert('Error al cargar: ' + (data.error || ''));
                }
            }).fail((xhr) => {mostrarError('cargar datos presupuesto', xhr); $('#modalPresupuesto').modal('hide');});
        } else {
            $('#modalPresupuestoTitle').text('Nuevo Presupuesto General');
            // Por defecto, establecer como general
            $tipo.val('general');
            toggleTipo();
        }

    }).fail(function() {
        mostrarError('cargar categorías o presupuestos generales');
    });
});
// Asegurar que el campo monto del presupuesto sea editable
ensureNumberEditable('#pres_monto');
$(document).on('submit', '#formPresupuesto', function(e) {
    e.preventDefault();
    // Preparar datos según tipo
    const tipo = $('#pres_tipo').val();
    if (tipo === 'general') {
        // Forzar valores para que el servidor lo considere general
        $('#pres_parent').val('');
        $('#pres_categoria').val('');
        const formData = $(this).serialize();
        console.log('=== DEPURACIÓN PRESUPUESTO (general) ===');
        console.log('FormData serializado:', formData);
        ajaxCall('presupuesto', 'save', formData).done(r => {
            if (r.success) window.location.reload();
            else alert('Error: ' + (r.error || 'Verifique si ya existe o los límites.'));
        }).fail((xhr) => mostrarError('guardar presupuesto', xhr));
        return;
    }

    // Por categoría: asegurar que exista parent y categoría; intentar autocompletar parent si está vacío
    if (!$('#pres_parent').val()) {
        // Intentar obtener presupuestos y seleccionar el primer Presupuesto General disponible
        ajaxCall('presupuesto', 'getAllPresupuestos', {}, 'GET').done(function(presupuestos) {
            try {
                if (!presupuestos || !Array.isArray(presupuestos)) {
                    alert('No se pudieron cargar los presupuestos. Intente recargar la página.');
                    return;
                }
                let firstGeneral = null;
                presupuestos.forEach(p => {
                    const cat = (p.id_categoria === null || p.id_categoria === undefined || p.id_categoria === '') ? null : p.id_categoria;
                    if (cat === null && !firstGeneral) {
                        firstGeneral = p;
                    }
                });
                if (firstGeneral) {
                    const id = firstGeneral.id || firstGeneral.id_presupuesto || '';
                    $('#pres_parent').val(String(id));
                    // ahora procedemos a validar categoría y enviar
                    if (!$('#pres_categoria').val()) {
                        alert('Seleccione la categoría para este presupuesto por categoría.');
                        return;
                    }
                    const formData = $('#formPresupuesto').serialize();
                    console.log('=== DEPURACIÓN PRESUPUESTO (autocompletado parent) ===');
                    console.log('FormData serializado:', formData);
                    ajaxCall('presupuesto', 'save', formData).done(r => {
                        if (r.success) window.location.reload();
                        else alert('Error: ' + (r.error || 'Verifique si ya existe o los límites.'));
                    }).fail((xhr) => mostrarError('guardar presupuesto', xhr));
                } else {
                    alert('No existe ningún Presupuesto General. Cree primero un Presupuesto General antes de asignar presupuestos por categoría.');
                }
            } catch (e) {
                console.error('Error en fallback submit presupuesto:', e);
                alert('Ocurrió un error al procesar el formulario. Ver consola.');
            }
        }).fail(function(xhr) {
            mostrarError('cargar presupuestos', xhr);
        });
        return;
    }

    // Si ya tiene parent seleccionado y categoría ok, proceder normalmente
    if (!$('#pres_categoria').val()) {
        alert('Seleccione la categoría para este presupuesto por categoría.');
        return;
    }
    const formData = $(this).serialize();
    console.log('=== DEPURACIÓN PRESUPUESTO ===');
    console.log('FormData serializado:', formData);
    ajaxCall('presupuesto', 'save', formData).done(r => {
        if (r.success) window.location.reload();
        else alert('Error: ' + (r.error || 'Verifique si ya existe o los límites.'));
    }).fail((xhr) => mostrarError('guardar presupuesto', xhr));
});
$(document).on('click', '.btn-del-presupuesto', function() { if (confirm('¿Eliminar este presupuesto?')) { ajaxCall('presupuesto', 'delete', { id: $(this).data('id') }).done(r => { if(r.success) window.location.reload(); else alert('Error: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('eliminar presupuesto', xhr)); } });

// Nuevo: Modal y formulario específico para Presupuesto por Categoría
$('#modalPresupuestoCategoria').on('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const presId = button ? $(button).data('id') : null;
    
    // Verificar si se llamó desde un botón de editar sub-presupuesto
    if (button && $(button).hasClass('btn-edit-presupuesto') && presId) {
        // Es edición - actualizar título
        $('#modalPresupuestoCategoriaTitle').text('Editar Sub-presupuesto');
    } else {
        // Es nuevo - titulo normal
        $('#modalPresupuestoCategoriaTitle').text('Nuevo Sub-presupuesto por Categoría');
    }
    
    // Usar la función reutilizable que garantiza capturar la respuesta cruda
    populatePresupuestoCategoria(presId).then(function(){
        console.log('populatePresupuestoCategoria: poblado correctamente');
    }).catch(function(err){
        console.error('populatePresupuestoCategoria error:', err);
        const $body = $('#modalPresupuestoCategoria .modal-body');
        if ($body.find('.alert-presupuesto-empty').length === 0) {
            $body.prepend('<div class="alert alert-danger alert-presupuesto-empty">Error al cargar datos desde el servidor. Revise la consola (F12) y la pestaña Network.</div>');
        }
    });
});

// Asegurar que el monto sea editable
ensureNumberEditable('#pres_monto_categoria');

// Submit para el formulario específico de categoría
// Ahora usamos un botón con click handler para evitar envío nativo que redirige si JS falla
$(document).on('click', '#btnGuardarPresCategoria', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    // Mostrar modal con respuesta cruda cuando se hace click en el botón
    $(document).off('click.showRawPresCat').on('click.showRawPresCat', '#btnVerRawPresCat', function() {
        try {
            $('#raw_response_cats').text((_lastCatsResponseText) || 'No hay respuesta disponible para getAllCategorias');
            $('#raw_response_pres').text((_lastPresResponseText) || 'No hay respuesta disponible para getAllPresupuestos');
            const modal = new bootstrap.Modal(document.getElementById('modalRawResponse'));
            modal.show();
        } catch(e) { console.error('Error mostrando raw response modal', e); alert('Error al abrir visor de respuesta cruda. Ver consola.'); }
    });
    const $form = $('#formPresupuestoCategoria');
    if (!$form.length) { console.error('No se encontró #formPresupuestoCategoria'); return; }
    // Validaciones básicas
    if (!$('#pres_parent_categoria').val()) {
        alert('Seleccione un Presupuesto General (padre) antes de guardar.');
        return;
    }
    if (!$('#pres_categoria_categoria').val()) {
        alert('Seleccione la categoría.');
        return;
    }
    const formData = $form.serialize();
    console.log('=== DEPURACIÓN PRESUPUESTO CATEGORÍA (click) ===');
    console.log('FormData serializado:', formData);

    ajaxCall('presupuesto', 'save', formData).done(r => {
        console.log('Respuesta save presupuesto categoria:', r);
        if (r && r.success) window.location.reload();
        else {
            alert('Error: ' + (r && r.error ? r.error : 'Verifique si ya existe o los límites.'));
            // Mostrar detalle dentro del modal
            const $body = $('#modalPresupuestoCategoria .modal-body');
            $body.find('.alert-presupuesto-empty').remove();
            $body.prepend('<div class="alert alert-danger alert-presupuesto-empty">' + (r && r.error ? r.error : 'Error al guardar presupuesto') + '</div>');
        }
    }).fail((xhr) => {
        mostrarError('guardar presupuesto categoría', xhr);
        console.error('AJAX fail guardar presupuesto categoria:', xhr);
        const $body = $('#modalPresupuestoCategoria .modal-body');
        $body.find('.alert-presupuesto-empty').remove();
        $body.prepend('<div class="alert alert-danger alert-presupuesto-empty">Error al comunicarse con el servidor. Revise la consola y Network.</div>');
    });
});

// Ensure modal elements are forced editable/visible when modal is shown (double-safety)
$(document).on('shown.bs.modal', '#modalPresupuesto', function() {
    try {
        console.log('DEBUG modalPresupuesto: shown event fired');
        const tipo = $('#pres_tipo').val();
        if (tipo === 'categoria') {
            $('#div_parent_presupuesto').show().css('display', 'block');
            $('#pres_parent').prop('disabled', false).prop('readonly', false).removeAttr('readonly');
        } else {
            $('#div_parent_presupuesto').hide();
            $('#pres_parent').prop('disabled', true);
        }

        // Ensure monto is editable and focused
        $('#pres_monto').prop('disabled', false).prop('readonly', false).removeAttr('readonly').focus();

        // Robust fallback: si el select #pres_parent no tiene opciones (o sólo el placeholder), solicitar los presupuestos ahora
        const $selectParent = $('#pres_parent');
        const optsCount = $selectParent.find('option').length;
        console.log('DEBUG modalPresupuesto: pres_parent optsCount=', optsCount);
        if (optsCount <= 1) {
            console.log('DEBUG modalPresupuesto: fallback - solicitando getAllPresupuestos para poblar #pres_parent');
            ajaxCall('presupuesto', 'getAllPresupuestos', {}, 'GET').done(function(presupuestos) {
                try {
                    if (!presupuestos || !Array.isArray(presupuestos)) {
                        console.warn('Fallback getAllPresupuestos devolvió datos inesperados', presupuestos);
                        return;
                    }
                    // limpiar y volver a poblar
                    $selectParent.empty().append('<option value="">Seleccione presupuesto general...</option>');
                    let found = false;
                    presupuestos.forEach(p => {
                        const id = p.id || p.id_presupuesto || '';
                        const cat = p.id_categoria === null || p.id_categoria === undefined || p.id_categoria === '' ? null : p.id_categoria;
                        if (cat === null) {
                            const monto = p.monto_limite || p.monto || '';
                            const label = monto !== '' ? `${monto} — ${p.fecha || ''}` : `Presupuesto ${id}`;
                            $selectParent.append(`<option value="${id}">${label}</option>`);
                            found = true;
                        }
                    });
                    if (!found) console.warn('Fallback: no se encontraron presupuestos generales');
                    $selectParent.prop('disabled', false);
                    console.log('DEBUG modalPresupuesto: pres_parent options after fallback=', $selectParent.find('option').length);
                } catch(e) { console.error('Error al poblar #pres_parent en fallback', e); }
            }).fail(function(xhr) {
                console.error('Fallback getAllPresupuestos falló', xhr);
            });
        }
    } catch(e) { console.error('DEBUG modalPresupuesto shown handler error', e); }
});

// --- Eventos Módulo: Auditoría ---
// (Sin cambios, usa submit normal)

/* ========================   INICIALIZACIÓN   ======================== */
$(document).ready(function() {
    console.log("=== ERP MVC: app.js cargado y listo ===");
    console.log("jQuery version:", $.fn.jquery);
    console.log("Bootstrap disponible:", typeof bootstrap !== 'undefined');
    console.log("CURRENT_USER:", CURRENT_USER);
    console.log("BASE_URL:", BASE_URL);
    
    // Verificar que los elementos existen
    setTimeout(function() {
        console.log("\n=== Verificando elementos del DOM ===");
        console.log("Botón toggle usuarios (#btnToggleUsuarios):", $('#btnToggleUsuarios').length);
        console.log("Sección usuarios registrados (#seccionUsuariosRegistrados):", $('#seccionUsuariosRegistrados').length);
        console.log("Botón abrir cambiar password (#btnAbrirCambiarPassword):", $('#btnAbrirCambiarPassword').length);
        console.log("Modal editar perfil (#modalEditarMiPerfil):", $('#modalEditarMiPerfil').length);
        console.log("Modal cambiar password (#modalCambiarPassword):", $('#modalCambiarPassword').length);
        
        // Agregar eventos de click para debugging
        if ($('#btnToggleUsuarios').length === 0) {
            console.error("ERROR: Botón toggle usuarios NO encontrado!");
        } else {
            console.log("✓ Botón toggle usuarios encontrado");
        }
        
        if ($('#btnAbrirCambiarPassword').length === 0) {
            console.warn("ADVERTENCIA: Botón cambiar contraseña no encontrado (normal si no está en el modal abierto)");
        }
    }, 1000);
    
    // Si el usuario pulsa un link del sidebar en móvil, cerrar el sidebar para mostrar contenido
    $('body').on('click', '#sidebar .nav-link', function() {
        try {
            if (window.innerWidth < 992) {
                $('#sidebar').removeClass('open').addClass('closed');
                $('#sidebarOverlay').hide();
                document.body.style.overflow = '';
            }
        } catch(e) { console.error('Error en sidebar:', e); }
    });

    // Comprobación rápida: Si NO existen Presupuestos Generales, deshabilitar el botón "Por Categoría"
    try {
        ajaxCall('presupuesto', 'getAllPresupuestos', {}, 'GET').done(function(res) {
            try {
                if (!res || !Array.isArray(res)) return;
                const hasGeneral = res.some(p => (p.id_categoria === null || p.id_categoria === undefined || p.id_categoria === ''));
                if (!hasGeneral) {
                    const $btn = $('#btnNuevoPresCategoria');
                    if ($btn.length) {
                        $btn.prop('disabled', true).addClass('disabled');
                        $btn.attr('title', 'Cree primero un Presupuesto General para habilitar presupuestos por categoría');
                        // añadir pequeño mensaje visual
                        $btn.after('<small id="prescat_hint" class="text-muted ms-2 d-none d-md-inline">(Crear Presupuesto General primero)</small>');
                        $('#prescat_hint').show();
                    }
                }
            } catch(e) { console.error('Error en comprobación presupuestos generales:', e); }
        }).fail(function(xhr){ console.warn('No se pudo comprobar existencia de presupuestos generales', xhr); });
    } catch(e) { console.error('Error iniciando comprobación presupuestos generales', e); }
});

// Handler para abrir modal de auditoría y cargar detalles
$(document).on('click', '.aud-row', function() {
    const id = $(this).data('id');
    if (!id) return;
    
    // Mapa de nombres técnicos a etiquetas legibles en español
    const fieldLabels = {
        'id_ingreso': 'ID Ingreso', 'id_egreso': 'ID Egreso', 'id_presupuesto': 'ID Presupuesto',
        'id_categoria': 'ID Categoría', 'id_usuario': 'ID Usuario',
        'folio_ingreso': 'Folio Ingreso', 'folio_egreso': 'Folio Egreso',
        'nombre_completo': 'Nombre Completo', 'apellido_paterno': 'Apellido Paterno', 'apellido_materno': 'Apellido Materno',
        'matricula': 'Matrícula', 'nivel_academico': 'Nivel Académico', 'programa': 'Programa',
        'grado': 'Grado', 'grupo': 'Grupo', 'concepto': 'Concepto', 'metodo_pago': 'Método de Pago',
        'modalidad': 'Modalidad', 'fecha_pago': 'Fecha de Pago', 'mes_pago': 'Mes', 'anio_pago': 'Año',
        'dia_pago': 'Día de Pago', 'tipo_ingreso': 'Tipo Ingreso', 'observaciones': 'Observaciones',
        'monto': 'Monto', 'monto_previo': 'Monto Previo', 'numero_factura': 'No. Factura',
        'proveedor': 'Proveedor', 'descripcion': 'Descripción', 'fecha_egreso': 'Fecha Egreso',
        'categoria': 'Categoría', 'periodo_inicio': 'Periodo Inicio', 'periodo_fin': 'Periodo Fin',
        'monto_total': 'Monto Total', 'monto_gastado': 'Monto Gastado', 'estado': 'Estado',
        'nombre_usuario': 'Usuario', 'email': 'Email', 'rol': 'Rol', 'activo': 'Activo',
        'fecha_creacion': 'Fecha Creación', 'ultima_modificacion': 'Última Modificación'
    };
    
    // Mostrar modal inmediatamente
    $('#modalAuditoriaDetalle').modal('show');
    $('#aud_det_fecha, #aud_det_usuario, #aud_det_seccion').text('Cargando...');
    $('#aud_det_accion').text('');
    $('#aud_compare_container, #aud_det_detalles_container, #aud_raw_consulta').hide();

    ajaxCall('auditoria', 'getLogAjax', { id: id }, 'GET')
        .done(res => {
            if (res && res.success && res.data) {
                const d = res.data;
                
                // Formatear fecha
                let fecha = d.fecha || '';
                try { fecha = new Date(fecha).toLocaleString('es-MX'); } catch(e) {}
                $('#aud_det_fecha').text(fecha);
                $('#aud_det_usuario').text(d.usuario || 'Sistema');
                $('#aud_det_seccion').text(d.seccion || '-');
                
                // Badge de acción con colores
                const actRaw = d.accion || '-';
                const actLower = String(actRaw).toLowerCase();
                let badgeClass = 'bg-secondary', label = actRaw;
                if (actLower.indexOf('inser') !== -1 || actLower.indexOf('registro') !== -1) {
                    badgeClass = 'bg-success'; label = 'Registro';
                } else if (actLower.indexOf('actual') !== -1 || actLower.indexOf('update') !== -1) {
                    badgeClass = 'bg-warning text-dark'; label = 'Actualización';
                } else if (actLower.indexOf('elim') !== -1 || actLower.indexOf('delete') !== -1) {
                    badgeClass = 'bg-danger'; label = 'Eliminación';
                }
                $('#aud_det_accion').html(`<span class="badge ${badgeClass}">${escapeHtml(label)}</span>`);
                
                // Helpers locales
                function getFieldLabel(key) {
                    return fieldLabels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                }
                
                function formatValue(v) {
                    if (v === null || v === undefined || v === '') return '-';
                    if (typeof v === 'object') {
                        try {
                            const parts = [];
                            Object.keys(v).forEach(k => parts.push(getFieldLabel(k) + ': ' + String(v[k])));
                            return parts.join(', ');
                        } catch(e) { return JSON.stringify(v); }
                    }
                    return String(v);
                }
                
                function renderSimpleList(obj) {
                    const $dl = $('<dl class="row mb-0">');
                    Object.keys(obj).forEach(k => {
                        const v = obj[k];
                        $dl.append(`<dt class="col-sm-5 text-muted">${escapeHtml(getFieldLabel(k))}</dt><dd class="col-sm-7">${escapeHtml(formatValue(v))}</dd>`);
                    });
                    return $dl;
                }
                
                // Parsear JSON
                let oldObj = null, newObj = null;
                try { if (d.old_valor) oldObj = JSON.parse(d.old_valor); } catch(e) {}
                try { if (d.new_valor) newObj = JSON.parse(d.new_valor); } catch(e) {}
                
                // Limpiar contenedores
                $('#aud_old_values, #aud_new_values, #aud_det_detalles').empty();
                
                // Decisión de layout según tipo de acción
                if (actLower.indexOf('actual') !== -1 && oldObj && newObj && typeof oldObj === 'object' && typeof newObj === 'object') {
                    // ACTUALIZACIÓN: Comparación lado a lado
                    $('#aud_compare_container').show();
                    $('#aud_det_detalles_container').hide();
                    
                    // Obtener solo los campos que cambiaron
                    const changedKeys = [];
                    const allKeys = Array.from(new Set(Object.keys(oldObj).concat(Object.keys(newObj))));
                    allKeys.forEach(k => {
                        const ov = oldObj[k] === undefined ? '' : oldObj[k];
                        const nv = newObj[k] === undefined ? '' : newObj[k];
                        if (String(ov) !== String(nv)) changedKeys.push(k);
                    });
                    
                    // Si hay cambios, mostrar solo esos
                    const keysToShow = changedKeys.length > 0 ? changedKeys : allKeys;
                    
                    const $oldDl = $('<dl class="row mb-0">');
                    const $newDl = $('<dl class="row mb-0">');
                    
                    keysToShow.forEach(k => {
                        const ov = oldObj[k] === undefined ? '' : oldObj[k];
                        const nv = newObj[k] === undefined ? '' : newObj[k];
                        const label = getFieldLabel(k);
                        
                        $oldDl.append(`<dt class="col-12 text-muted small">${escapeHtml(label)}</dt><dd class="col-12">${escapeHtml(formatValue(ov))}</dd>`);
                        $newDl.append(`<dt class="col-12 text-muted small">${escapeHtml(label)}</dt><dd class="col-12">${escapeHtml(formatValue(nv))}</dd>`);
                    });
                    
                    $('#aud_old_values').append($oldDl);
                    $('#aud_new_values').append($newDl);
                    
                } else if (actLower.indexOf('inser') !== -1 && newObj && typeof newObj === 'object') {
                    // INSERCIÓN: Solo valores nuevos
                    $('#aud_compare_container').hide();
                    $('#aud_det_detalles_container').show();
                    $('#aud_det_detalles').html('<div class="text-success mb-2"><strong>✓ Registro Creado</strong></div>').append(renderSimpleList(newObj));
                    
                } else if (actLower.indexOf('elim') !== -1 && oldObj && typeof oldObj === 'object') {
                    // ELIMINACIÓN: Solo valores antiguos
                    $('#aud_compare_container').hide();
                    $('#aud_det_detalles_container').show();
                    $('#aud_det_detalles').html('<div class="text-danger mb-2"><strong>✗ Registro Eliminado</strong></div>').append(renderSimpleList(oldObj));
                    
                } else {
                    // FALLBACK: Mostrar lo que haya
                    $('#aud_compare_container').hide();
                    $('#aud_det_detalles_container').show();
                    if (newObj && typeof newObj === 'object') {
                        $('#aud_det_detalles').append(renderSimpleList(newObj));
                    } else if (oldObj && typeof oldObj === 'object') {
                        $('#aud_det_detalles').append(renderSimpleList(oldObj));
                    } else {
                        const raw = d.detalles || ((d.old_valor || '') + ' → ' + (d.new_valor || '')) || '-';
                        const cleaned = String(raw).replace(/[{}\[\]"]+/g, '').replace(/\s*,\s*/g, ', ');
                        $('#aud_det_detalles').text(cleaned);
                    }
                }
                
                // Preparar toggle para JSON raw
                const raw = (d.detalles && d.detalles.length) ? d.detalles : ((d.old_valor || '') + ' → ' + (d.new_valor || ''));
                $('#aud_raw_consulta').text(String(raw) || '-').hide();
                $('#aud_toggle_raw').off('click.aud').on('click.aud', function() {
                    const $pre = $('#aud_raw_consulta');
                    if ($pre.is(':visible')) {
                        $pre.slideUp();
                        $(this).html('<ion-icon name="code-outline"></ion-icon> Ver datos técnicos (JSON)');
                    } else {
                        $pre.slideDown();
                        $(this).html('<ion-icon name="code-outline"></ion-icon> Ocultar datos técnicos');
                    }
                });
                
            } else {
                $('#aud_detalle_body').html('<div class="alert alert-warning">No se pudo cargar el registro.</div>');
            }
        }).fail(xhr => {
            $('#aud_detalle_body').html('<div class="alert alert-danger">Error al cargar el detalle.</div>');
            mostrarError('cargar detalle auditoría', xhr);
        });
});

// Helper para escapar HTML
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

/* ==========================================================================
   BUSCADORES EN TIEMPO REAL
   ========================================================================== */

/**
 * Buscador para el módulo de Ingresos
 * Filtra por: Folio, Alumno y Fecha
 */
$(document).ready(function() {
    const $searchInput = $('#searchIngresos');
    const $clearBtn = $('#clearSearchIngresos');
    const $fechaInicio = $('#fechaInicioIngresos');
    const $fechaFin = $('#fechaFinIngresos');
    const $clearDateBtn = $('#clearDateIngresos');
    const $resultCount = $('#resultCountIngresos');
    const $tableBody = $('#tablaIngresos');
    
    if ($searchInput.length) {
        console.log('Buscador de Ingresos inicializado');
        
        // Función de filtrado combinado
        function filtrarIngresos() {
            const searchTerm = $searchInput.val().toLowerCase().trim();
            const fechaInicio = $fechaInicio.val();
            const fechaFin = $fechaFin.val();
            
            console.log('Búsqueda Ingresos:', searchTerm, 'Fecha Inicio:', fechaInicio, 'Fecha Fin:', fechaFin);
            
            // Mostrar/ocultar botones de limpiar
            if (searchTerm.length > 0) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }
            
            if (fechaInicio || fechaFin) {
                $clearDateBtn.show();
            } else {
                $clearDateBtn.hide();
            }
            
            let visibleCount = 0;
            let totalCount = 0;
            
            $tableBody.find('tr').each(function() {
                const $row = $(this);
                
                // Saltar la fila de "no hay registros"
                if ($row.find('td[colspan]').length > 0) {
                    return;
                }
                
                totalCount++;
                
                // Obtener solo Alumno (primera columna visible)
                const $cells = $row.find('td');
                const alumno = $cells.eq(0).text().toLowerCase();
                
                // Obtener el folio desde el botón de editar o eliminar
                let folio = '';
                const $editBtn = $row.find('.btn-edit-ingreso');
                if ($editBtn.length) {
                    folio = $editBtn.data('id').toString().toLowerCase();
                }
                
                // Obtener la fecha desde el atributo data-fecha
                let fechaRegistro = $row.attr('data-fecha');
                
                // Buscar en folio y alumno
                const searchableText = folio + ' ' + alumno;
                let matchText = true;
                let matchDate = true;
                
                // Filtro de texto
                if (searchTerm.length > 0) {
                    matchText = searchableText.includes(searchTerm);
                }
                
                // Filtro de fecha
                if (fechaRegistro && (fechaInicio || fechaFin)) {
                    if (fechaInicio && fechaFin) {
                        // Rango de fechas
                        matchDate = fechaRegistro >= fechaInicio && fechaRegistro <= fechaFin;
                    } else if (fechaInicio) {
                        // Solo fecha inicio (desde)
                        matchDate = fechaRegistro >= fechaInicio;
                    } else if (fechaFin) {
                        // Solo fecha fin (hasta)
                        matchDate = fechaRegistro <= fechaFin;
                    }
                }
                
                // Mostrar solo si cumple ambos criterios
                if (matchText && matchDate) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            // Actualizar contador de resultados
            if (searchTerm.length > 0 || fechaInicio || fechaFin) {
                if (visibleCount === 0) {
                    $resultCount.html('<ion-icon name="alert-circle-outline" style="vertical-align:middle;"></ion-icon> No se encontraron resultados');
                    $resultCount.addClass('text-danger').removeClass('text-success');
                } else {
                    $resultCount.html(`<ion-icon name="checkmark-circle-outline" style="vertical-align:middle;"></ion-icon> Mostrando ${visibleCount} de ${totalCount} ingresos`);
                    $resultCount.addClass('text-success').removeClass('text-danger');
                }
            } else {
                $resultCount.html('');
                $resultCount.removeClass('text-success text-danger');
            }
        }
        
        // Evento de búsqueda en tiempo real
        $searchInput.on('keyup', filtrarIngresos);
        
        // Eventos de cambio de fecha
        $fechaInicio.on('change', filtrarIngresos);
        $fechaFin.on('change', filtrarIngresos);
        
        // Botón para limpiar búsqueda de texto
        $clearBtn.on('click', function() {
            $searchInput.val('');
            filtrarIngresos();
            $searchInput.focus();
        });
        
        // Botón para limpiar fechas
        $clearDateBtn.on('click', function() {
            $fechaInicio.val('');
            $fechaFin.val('');
            filtrarIngresos();
        });
        
        // Limpiar con tecla ESC
        $searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('');
                filtrarIngresos();
            }
        });
    }
});

/**
 * Buscador para el módulo de Egresos
 * Filtra por: Folio, Destinatario y Fecha
 */
$(document).ready(function() {
    const $searchInput = $('#searchEgresos');
    const $clearBtn = $('#clearSearchEgresos');
    const $fechaInicio = $('#fechaInicioEgresos');
    const $fechaFin = $('#fechaFinEgresos');
    const $clearDateBtn = $('#clearDateEgresos');
    const $resultCount = $('#resultCountEgresos');
    const $tableBody = $('#tablaEgresos');
    
    if ($searchInput.length) {
        console.log('Buscador de Egresos inicializado');
        
        // Función de filtrado combinado
        function filtrarEgresos() {
            const searchTerm = $searchInput.val().toLowerCase().trim();
            const fechaInicio = $fechaInicio.val();
            const fechaFin = $fechaFin.val();
            
            console.log('Búsqueda Egresos:', searchTerm, 'Fecha Inicio:', fechaInicio, 'Fecha Fin:', fechaFin);
            
            // Mostrar/ocultar botones de limpiar
            if (searchTerm.length > 0) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }
            
            if (fechaInicio || fechaFin) {
                $clearDateBtn.show();
            } else {
                $clearDateBtn.hide();
            }
            
            let visibleCount = 0;
            let totalCount = 0;
            
            $tableBody.find('tr').each(function() {
                const $row = $(this);
                
                // Saltar la fila de "no hay registros"
                if ($row.find('td[colspan]').length > 0) {
                    return;
                }
                
                totalCount++;
                
                // Obtener el destinatario (tercera columna: Fecha, Categoría, Destinatario)
                const $cells = $row.find('td');
                const destinatario = $cells.eq(2).text().toLowerCase();
                
                // Obtener el folio desde el botón de editar o eliminar
                let folio = '';
                const $editBtn = $row.find('.btn-edit-egreso');
                if ($editBtn.length) {
                    folio = $editBtn.data('id').toString().toLowerCase();
                }
                
                // Obtener la fecha desde el atributo data-fecha
                let fechaRegistro = $row.attr('data-fecha');
                
                // Buscar en folio y destinatario
                const searchableText = folio + ' ' + destinatario;
                let matchText = true;
                let matchDate = true;
                
                // Filtro de texto
                if (searchTerm.length > 0) {
                    matchText = searchableText.includes(searchTerm);
                }
                
                // Filtro de fecha
                if (fechaRegistro && (fechaInicio || fechaFin)) {
                    if (fechaInicio && fechaFin) {
                        // Rango de fechas
                        matchDate = fechaRegistro >= fechaInicio && fechaRegistro <= fechaFin;
                    } else if (fechaInicio) {
                        // Solo fecha inicio (desde)
                        matchDate = fechaRegistro >= fechaInicio;
                    } else if (fechaFin) {
                        // Solo fecha fin (hasta)
                        matchDate = fechaRegistro <= fechaFin;
                    }
                }
                
                // Mostrar solo si cumple ambos criterios
                if (matchText && matchDate) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            // Actualizar contador de resultados
            if (searchTerm.length > 0 || fechaInicio || fechaFin) {
                if (visibleCount === 0) {
                    $resultCount.html('<ion-icon name="alert-circle-outline" style="vertical-align:middle;"></ion-icon> No se encontraron resultados');
                    $resultCount.addClass('text-danger').removeClass('text-success');
                } else {
                    $resultCount.html(`<ion-icon name="checkmark-circle-outline" style="vertical-align:middle;"></ion-icon> Mostrando ${visibleCount} de ${totalCount} egresos`);
                    $resultCount.addClass('text-success').removeClass('text-danger');
                }
            } else {
                $resultCount.html('');
                $resultCount.removeClass('text-success text-danger');
            }
        }
        
        // Evento de búsqueda en tiempo real
        $searchInput.on('keyup', filtrarEgresos);
        
        // Eventos de cambio de fecha
        $fechaInicio.on('change', filtrarEgresos);
        $fechaFin.on('change', filtrarEgresos);
        
        // Botón para limpiar búsqueda de texto
        $clearBtn.on('click', function() {
            $searchInput.val('');
            filtrarEgresos();
            $searchInput.focus();
        });
        
        // Botón para limpiar fechas
        $clearDateBtn.on('click', function() {
            $fechaInicio.val('');
            $fechaFin.val('');
            filtrarEgresos();
        });
        
        // Limpiar con tecla ESC
        $searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('');
                filtrarEgresos();
            }
        });
    }
});

// ============================================================================
// SISTEMA DE ALERTAS DE PRESUPUESTOS (>=90% CONSUMIDOS)
// ============================================================================

/**
 * Actualiza el badge de alertas de presupuestos en el sidebar
 */
function actualizarBadgeAlertas() {
    const $badge = $('#badgeAlertasPresupuestos');
    if (!$badge.length) return; // Si no existe el badge, no hacer nada
    
    ajaxCall('presupuesto', 'getAlertasCount', {}, 'GET')
        .done(response => {
            if (response && response.count !== undefined) {
                if (response.count > 0) {
                    $badge.text(response.count).show().addClass('pulse-animation');
                } else {
                    $badge.hide().removeClass('pulse-animation');
                }
            }
        })
        .fail(xhr => {
            console.error('Error al obtener alertas de presupuestos:', xhr);
        });
}

// Actualizar badge al cargar la página
$(document).ready(function() {
    actualizarBadgeAlertas();
    
    // Actualizar cada 30 segundos
    setInterval(actualizarBadgeAlertas, 30000);
});

// Listener para eventos personalizados (egresoGuardado y egresoEliminado)
$(document).on('egresoGuardado egresoEliminado', function() {
    actualizarBadgeAlertas();
});


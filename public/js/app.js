/**
 * app.js - Lógica del Frontend para el ERP IUM (Versión MVC Final Corregida)
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
    alert(errorMsg + "\nPor favor, revise la consola (F12) para más detalles técnicos.");
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
                alert('Error al guardar: ' + (r.error || 'Verifique datos.'));
            }
        })
        .fail((xhr) => mostrarError('guardar perfil', xhr));
});

// Botón para abrir modal de cambiar contraseña desde el modal de editar perfil
$(document).ready(function() {
    $('#btnAbrirCambiarPassword, #modalEditarMiPerfil').on('click', '#btnAbrirCambiarPassword', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Botón cambiar contraseña clickeado!');
        
        // Cerrar el modal de editar perfil
        var modalEditarPerfil = bootstrap.Modal.getInstance(document.getElementById('modalEditarMiPerfil'));
        if (modalEditarPerfil) {
            modalEditarPerfil.hide();
        } else {
            $('#modalEditarMiPerfil').modal('hide');
        }
        
        // Esperar a que se cierre y abrir el de cambiar contraseña
        setTimeout(function() {
            console.log('Abriendo modal de cambiar contraseña...');
            $('#changepass_username').val(CURRENT_USER.user_username || CURRENT_USER.username);
            $('#modalCambiarPasswordNuevo').modal('show');
        }, 500);
    });
    
    // Event delegation adicional
    $(document).off('click.cambiarPass').on('click.cambiarPass', '#btnAbrirCambiarPassword', function(e) {
        e.preventDefault();
        console.log('Event delegation - cambiar password');
    });
});

// Modal: Cambiar Contraseña (Nueva Versión con 3 campos)
$('#modalCambiarPasswordNuevo').on('show.bs.modal', function (event) {
    const $form = $('#formCambiarPasswordNuevo');
    if(!$form.length) { console.error("Form #formCambiarPasswordNuevo no encontrado."); return; }
    
    $form[0].reset();
    // Si no se estableció antes, usar el usuario actual
    if (!$('#changepass_username').val()) {
        $('#changepass_username').val(CURRENT_USER.user_username || CURRENT_USER.username);
    }
    $('#passwordMatchMessage').text('').removeClass('text-success text-danger');
    $('#btnGuardarPasswordNueva').prop('disabled', false);
});

// Toggle mostrar/ocultar contraseñas
$(document).on('click', '#togglePasswordActual', function() {
    const $input = $('#password_actual');
    const $icon = $(this).find('ion-icon');
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.attr('name', 'eye-off-outline');
    } else {
        $input.attr('type', 'password');
        $icon.attr('name', 'eye-outline');
    }
});

$(document).on('click', '#togglePasswordNueva', function() {
    const $input = $('#password_nueva');
    const $icon = $(this).find('ion-icon');
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.attr('name', 'eye-off-outline');
    } else {
        $input.attr('type', 'password');
        $icon.attr('name', 'eye-outline');
    }
});

$(document).on('click', '#togglePasswordConfirmar', function() {
    const $input = $('#password_confirmar');
    const $icon = $(this).find('ion-icon');
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.attr('name', 'eye-off-outline');
    } else {
        $input.attr('type', 'password');
        $icon.attr('name', 'eye-outline');
    }
});

// Validación en tiempo real de contraseñas coincidentes
$(document).on('input', '#password_nueva, #password_confirmar', function() {
    const nueva = $('#password_nueva').val();
    const confirmar = $('#password_confirmar').val();
    const $message = $('#passwordMatchMessage');
    const $btnSubmit = $('#btnGuardarPasswordNueva');
    
    if (confirmar.length > 0) {
        if (nueva === confirmar) {
            $message.text('✓ Las contraseñas coinciden').removeClass('text-danger').addClass('text-success');
            $btnSubmit.prop('disabled', false);
        } else {
            $message.text('✗ Las contraseñas no coinciden').removeClass('text-success').addClass('text-danger');
            $btnSubmit.prop('disabled', true);
        }
    } else {
        $message.text('').removeClass('text-success text-danger');
        $btnSubmit.prop('disabled', false);
    }
});

// Submit: Cambiar Contraseña (Nueva Versión)
$(document).on('submit', '#formCambiarPasswordNuevo', function(e) {
    e.preventDefault();
    console.log('Formulario de cambiar contraseña enviado');
    
    const passwordActual = $('#password_actual').val();
    const passwordNueva = $('#password_nueva').val();
    const passwordConfirmar = $('#password_confirmar').val();
    
    console.log('Validando contraseñas...');
    
    if (passwordNueva !== passwordConfirmar) {
        alert('Las contraseñas no coinciden.');
        return;
    }
    
    if (!passwordActual || !passwordNueva) {
        alert('Todos los campos son requeridos.');
        return;
    }
    
    console.log('Enviando petición al servidor...');
    
    ajaxCall('auth', 'changePasswordWithValidation', $(this).serialize())
        .done(r => {
            console.log('Respuesta recibida:', r);
            if (r.success) {
                $('#modalCambiarPasswordNuevo').modal('hide');
                alert('Contraseña actualizada correctamente.');
                // Si cambió su propia contraseña, redirigir al login
                window.location.href = BASE_URL + 'index.php?controller=auth&action=logout';
            } else {
                alert('Error: ' + (r.error || 'No se pudo cambiar la contraseña.'));
            }
        })
        .fail((xhr) => mostrarError('cambiar contraseña', xhr));
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
            alert('Error al guardar: ' + (r.error || 'Verifique datos.')); 
        }
    }).fail((xhr) => mostrarError('guardar usuario', xhr)); 
});

// Eliminar usuario
$(document).on('click', '.btn-delete-user', function() { 
    const id = $(this).data('id'); 
    if (confirm('¿Eliminar este usuario?')) { 
        ajaxCall('user', 'delete', { id: id }).done(r => { 
            if (r.success) window.location.reload(); 
            else alert('Error al eliminar: ' + (r.error || 'No se pudo eliminar.')); 
        }).fail((xhr) => mostrarError('eliminar usuario', xhr)); 
    }
});

// Modal: Cambiar Contraseña (Versión Admin - para otros usuarios)
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
            alert('Contraseña actualizada.'); 
        } else { 
            alert('Error: ' + (r.error || 'No se pudo cambiar.')); 
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

    // 1. Cargar presupuestos primero (para poblar el select)
    ajaxCall('presupuesto', 'getAllPresupuestos', {}, 'GET')
        .done(presupuestos => {
            $selectPres.empty().append('<option value="">Seleccione un presupuesto...</option>');
            if (presupuestos && Array.isArray(presupuestos) && presupuestos.length > 0) {
                presupuestos.forEach(p => {
                    const id = p.id || '';
                    const monto = p.monto_limite || p.monto || '';
                    const label = monto !== '' ? `${monto} — ${p.fecha || ''}` : `Presupuesto ${id}`;
                    $selectPres.append(`<option value="${id}">${label}</option>`);
                });
            } else {
                $selectPres.append('<option value="">-- No hay presupuestos --</option>');
            }
            $selectPres.prop('disabled', false);

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
                            $('#eg_activo_fijo').val(data.activo_fijo);
                            $('#eg_descripcion').val(data.descripcion);
                        } else { $('#modalEgreso').modal('hide'); alert('Error al cargar: '+(data.error||'')); }
                    }).fail((xhr) => {mostrarError('cargar datos egreso', xhr); $('#modalEgreso').modal('hide');});
            } else {
                $('#modalEgresoTitle').text('Registrar Nuevo Egreso');
                $('#eg_activo_fijo').val('NO'); // Valor por defecto
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
$(document).on('submit', '#formEgreso', function(e) { e.preventDefault(); ajaxCall('egreso', 'save', $(this).serialize()).done(r => { if(r.success) window.location.reload(); else alert('Error al guardar: ' + (r.error || 'Verifique datos.')); }).fail((xhr) => mostrarError('guardar egreso', xhr)); });
$(document).on('click', '.btn-del-egreso', function() { const id = $(this).data('id'); if (confirm('¿Eliminar este egreso?')) { ajaxCall('egreso', 'delete', { id: id }).done(r => { if(r.success) window.location.reload(); else alert('Error al eliminar: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('eliminar egreso', xhr)); } });


// --- Eventos Módulo: Ingresos (ACTUALIZADO CON NUEVOS CAMPOS) ---
$('#modalIngreso').on('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const ingresoId = button ? $(button).data('id') : null; // Este ID es folio_ingreso
    const $form = $('#formIngreso');
    const $selectCat = $('#in_id_categoria'); // ID correcto del select

    if (!$form.length) { console.error("Formulario #formIngreso no encontrado."); return; }
    $form[0].reset();
    $('#ingreso_id').val(''); // Campo oculto para folio_ingreso en update
    $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
    // Asegurar que el campo monto sea editable
    ensureNumberEditable('#in_monto');

    // 1. Cargar categorías de Ingreso (ID y Nombre)
    ajaxCall('ingreso', 'getCategoriasIngreso', {}, 'GET')
        .done(categorias => {
            $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');
            if (categorias && Array.isArray(categorias) && categorias.length > 0) {
                 // Usar id_categoria como valor (de tu tabla categorias)
                categorias.forEach(cat => {
                     if (cat && cat.id_categoria !== undefined && cat.nombre !== undefined) {
                        $selectCat.append(`<option value="${cat.id_categoria}">${cat.nombre}</option>`);
                    } else { console.warn("Categoría Ingreso con formato incorrecto:", cat); }
                });
            } else { $selectCat.append('<option value="">-- No hay categorías de ingreso --</option>'); }
            $selectCat.prop('disabled', false);

            // 2. Si es edición, cargar datos del ingreso
            if (ingresoId) {
                $('#modalIngresoTitle').text('Editar Ingreso');
                ajaxCall('ingreso', 'getIngresoData', { id: ingresoId }, 'GET') // Pide datos usando folio_ingreso
                    .done(data => {
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
                            $('#in_anio').val(data.anio); // Corregido a 'anio' (con n)
                            $('#in_dia_pago').val(data.dia_pago);
                            $('#in_observaciones').val(data.observaciones);
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
                $('#in_anio').val(new Date().getFullYear()); // Poner año actual por defecto
            }
        }).fail((xhr) => {
            mostrarError('cargar categorías ingreso', xhr);
            $selectCat.empty().append('<option value="">Error al cargar categorías</option>').prop('disabled', false);
        });
});
// Envío de formulario (usa 'anio' del form)
$(document).on('submit', '#formIngreso', function(e) { 
    e.preventDefault(); 
    const formData = $(this).serialize();
    console.log('FormIngreso enviando:', formData);
    console.log('Modalidad value:', $('#in_modalidad').val());
    console.log('Observaciones value:', $('#in_observaciones').val());
    
    ajaxCall('ingreso', 'save', formData).done(r => { 
        if(r.success) window.location.reload(); 
        else alert('Error al guardar: ' + (r.error || 'Verifique datos.')); 
    }).fail((xhr) => mostrarError('guardar ingreso', xhr)); 
});
// Eliminación (usa 'data-id' que es 'folio_ingreso')
$(document).on('click', '.btn-del-ingreso', function() { const id = $(this).data('id'); if (confirm('¿Eliminar este ingreso?')) { ajaxCall('ingreso', 'delete', { id: id }).done(r => { if(r.success) window.location.reload(); else alert('Error al eliminar: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('eliminar ingreso', xhr)); } });


// --- Eventos Módulo: Categorías ---
$('#modalCategoria').on('show.bs.modal', function(event) { const button = event.relatedTarget; const catId = button ? $(button).data('id') : null; const $form = $('#formCategoria'); if (!$form.length) { console.error("Form #formCategoria no encontrado."); return; } $form[0].reset(); $('#categoria_id').val(''); if (catId) { $('#modalCategoriaTitle').text('Editar Categoría'); ajaxCall('categoria', 'getCategoriaData', { id: catId }, 'GET').done(data => { if(data && !data.error) { $('#categoria_id').val(data.id_categoria); $('#cat_nombre').val(data.nombre); $('#cat_tipo').val(data.tipo); $('#cat_descripcion').val(data.descripcion); } else { $('#modalCategoria').modal('hide'); alert('Error al cargar: '+(data.error||'')); } }).fail((xhr) => {mostrarError('cargar datos categoría', xhr); $('#modalCategoria').modal('hide');}); } else { $('#modalCategoriaTitle').text('Agregar Nueva Categoría'); } });
$(document).on('submit', '#formCategoria', function(e) { e.preventDefault(); ajaxCall('categoria', 'save', $(this).serialize()).done(r => { if(r.success) window.location.reload(); else alert('Error: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('guardar categoría', xhr)); });
$(document).on('click', '.btn-del-categoria', function() { if (confirm('¿Eliminar esta categoría?')) { ajaxCall('categoria', 'delete', { id: $(this).data('id') }).done(r => { if(r.success) window.location.reload(); else alert('Error: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('eliminar categoría', xhr)); } });
$(document).on('click', '#btnRefrescarCategorias', () => window.location.reload());

// --- Eventos Módulo: Presupuestos ---
$('#modalPresupuesto').on('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const presId = button ? $(button).data('id') : null;
    const $form = $('#formPresupuesto');
    const $selectCat = $('#pres_categoria');
    if (!$form.length) { console.error("Form #formPresupuesto no encontrado."); return; }
    $form[0].reset();
    $('#presupuesto_id').val('');
    $selectCat.empty().append('<option>Cargando...</option>').prop('disabled', true);

    ajaxCall('presupuesto', 'getAllCategorias', {}, 'GET')
        .done(cats => {
            $selectCat.empty().append('<option value="">Seleccione...</option>');
            if (cats && cats.length > 0) {
                cats.forEach(c => {
                    // CRÍTICO: usar id_categoria numérico, NO el nombre
                    const catId = c.id_categoria !== undefined ? c.id_categoria : (c.id || '');
                    const nombre = c.nombre || (c.cat_nombre || '');
                    const tipo = c.tipo || '';
                    // Asegurar que value sea el ID numérico
                    $selectCat.append($('<option></option>')
                        .attr('value', catId)
                        .text(`${nombre}${tipo ? ' ('+tipo+')' : ''}`));
                });
            }
            $selectCat.prop('disabled', false);

            if (presId) {
                $('#modalPresupuestoTitle').text('Editar Presupuesto');
                ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET').done(data => {
                    if (data && !data.error) {
                        $('#presupuesto_id').val(data.id_presupuesto || data.id);
                        // Esperamos id_categoria con el nuevo backend
                        if (data.id_categoria) { $selectCat.val(String(data.id_categoria)); }
                        else if (data.categoria && !isNaN(data.categoria)) { $selectCat.val(String(data.categoria)); }
                        $('#pres_monto').val(data.monto_limite || data.monto || '');
                        $('#pres_fecha').val(data.fecha || '');
                    } else {
                        $('#modalPresupuesto').modal('hide');
                        alert('Error al cargar: ' + (data.error || ''));
                    }
                }).fail((xhr) => {mostrarError('cargar datos presupuesto', xhr); $('#modalPresupuesto').modal('hide');});
            } else {
                $('#modalPresupuestoTitle').text('Asignar/Actualizar');
            }
        })
        .fail((xhr) => { mostrarError('cargar categorías ppto', xhr); $selectCat.empty().append('<option>Error</option>'); });
});
// Asegurar que el campo monto del presupuesto sea editable
ensureNumberEditable('#pres_monto');
$(document).on('submit', '#formPresupuesto', function(e) { 
    e.preventDefault(); 
    const formData = $(this).serialize();
    console.log('=== DEPURACIÓN PRESUPUESTO ===');
    console.log('FormData serializado:', formData);
    console.log('Valor select #pres_categoria:', $('#pres_categoria').val());
    console.log('Texto select #pres_categoria:', $('#pres_categoria option:selected').text());
    ajaxCall('presupuesto', 'save', formData).done(r => { 
        if(r.success) window.location.reload(); 
        else alert('Error: ' + (r.error || 'Verifique si ya existe.')); 
    }).fail((xhr) => mostrarError('guardar presupuesto', xhr)); 
});
$(document).on('click', '.btn-del-presupuesto', function() { if (confirm('¿Eliminar este presupuesto?')) { ajaxCall('presupuesto', 'delete', { id: $(this).data('id') }).done(r => { if(r.success) window.location.reload(); else alert('Error: ' + (r.error || 'Error.')); }).fail((xhr) => mostrarError('eliminar presupuesto', xhr)); } });

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
        console.log("Modal cambiar password (#modalCambiarPasswordNuevo):", $('#modalCambiarPasswordNuevo').length);
        
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
 * Filtra por: Folio y Alumno
 */
$(document).ready(function() {
    const $searchInput = $('#searchIngresos');
    const $clearBtn = $('#clearSearchIngresos');
    const $resultCount = $('#resultCountIngresos');
    const $tableBody = $('#tablaIngresos');
    
    if ($searchInput.length) {
        console.log('Buscador de Ingresos inicializado');
        
        // Evento de búsqueda en tiempo real
        $searchInput.on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase().trim();
            console.log('Búsqueda Ingresos:', searchTerm);
            
            if (searchTerm.length > 0) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
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
                
                // Buscar SOLO en folio y alumno
                const searchableText = folio + ' ' + alumno;
                
                if (searchableText.includes(searchTerm)) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            // Actualizar contador de resultados
            if (searchTerm.length > 0) {
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
        });
        
        // Botón para limpiar búsqueda
        $clearBtn.on('click', function() {
            $searchInput.val('').trigger('keyup').focus();
        });
        
        // Limpiar con tecla ESC
        $searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('').trigger('keyup');
            }
        });
    }
});

/**
 * Buscador para el módulo de Egresos
 * Filtra por: Folio y Destinatario
 */
$(document).ready(function() {
    const $searchInput = $('#searchEgresos');
    const $clearBtn = $('#clearSearchEgresos');
    const $resultCount = $('#resultCountEgresos');
    const $tableBody = $('#tablaEgresos');
    
    if ($searchInput.length) {
        console.log('Buscador de Egresos inicializado');
        
        // Evento de búsqueda en tiempo real
        $searchInput.on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase().trim();
            console.log('Búsqueda Egresos:', searchTerm);
            
            if (searchTerm.length > 0) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
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
                
                // Buscar SOLO en folio y destinatario
                const searchableText = folio + ' ' + destinatario;
                
                if (searchableText.includes(searchTerm)) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            // Actualizar contador de resultados
            if (searchTerm.length > 0) {
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
        });
        
        // Botón para limpiar búsqueda
        $clearBtn.on('click', function() {
            $searchInput.val('').trigger('keyup').focus();
        });
        
        // Limpiar con tecla ESC
        $searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('').trigger('keyup');
            }
        });
    }
});

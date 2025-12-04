/**
 * ============================================================================
 * ERP IUM - Sistema de Gestión Financiera
 * app.js - Frontend Modular (Versión 3.0 Master Extended)
 * ============================================================================
 * BASE: Versión 2.2 (1710 líneas) recuperada.
 * AGREGADO: Módulo de Auditoría extendido (Gráficas y Reportes).
 * MANTENIDO: Modales legacy, validaciones estrictas, Easter Egg.
 * ============================================================================
 */

'use strict';

// ============================================================================
// MÓDULO: Utilidades Globales
// ============================================================================
const ERPUtils = (function() {
    /**
     * Realiza llamadas AJAX genéricas al backend
     */
    function ajaxCall(controller, action, data = {}, method = 'POST') {
        let url = `${BASE_URL}index.php?controller=${controller}&action=${action}`;
        
        if (method.toUpperCase() === 'GET' && Object.keys(data).length > 0) {
            url += '&' + $.param(data);
            data = {};
        }

        const ajaxOptions = {
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data
        };

        if (method.toUpperCase() !== 'GET' && method.toUpperCase() !== 'POST') {
            ajaxOptions.data._method = method.toUpperCase();
        }

        console.log(`[AJAX] ${method} ${controller}/${action}`, ajaxOptions.data);
        return $.ajax(ajaxOptions);
    }

    /**
     * Muestra mensajes de error amigables
     */
    function mostrarError(action, jqXHR = null) {
        let errorMsg = `Ocurrió un error al ${action}.`;
        let serverError = 'Error desconocido o sin conexión.';

        if (jqXHR) {
            if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                serverError = jqXHR.responseJSON.error;
            } else if (jqXHR.responseText) {
                console.error(`[ERROR] ${action}:`, jqXHR.responseText);
                const match = jqXHR.responseText.match(/<b>(?:Fatal error|Warning|Exception)<\/b>:(.*?)<br \/>/i);
                if (match && match[1]) {
                    serverError = `Error PHP: ${match[1].trim()}`;
                } else {
                    serverError = 'Respuesta del servidor no válida (ver consola).';
                }
            } else if (jqXHR.statusText) {
                serverError = `${jqXHR.statusText} (${jqXHR.status})`;
            }
        }

        console.error(`[ERROR] ${action}:`, serverError, jqXHR);
        if (typeof showError === 'function') {
            showError(`${errorMsg} Detalle: ${serverError}.`, { autoClose: 7000 });
        } else {
            alert(`${errorMsg}\nDetalle: ${serverError}`);
        }
    }

    /**
     * Asegura que un campo numérico sea editable
     */
    function ensureNumberEditable(selector) {
        const $el = $(selector);
        if (!$el.length) return;
        
        $el.prop('readonly', false)
           .prop('disabled', false)
           .removeAttr('readonly')
           .removeAttr('disabled');
        
        $el.off('input.ensureNum').on('input.ensureNum', function() {
            let val = $(this).val().replace(',', '.');
            const cleaned = val.replace(/[^0-9.]/g, '');
            const parts = cleaned.split('.');
            
            if (parts.length > 2) {
                $(this).val(parts[0] + '.' + parts.slice(1).join(''));
            } else {
                $(this).val(cleaned);
            }
        });
    }

    /**
     * Escapa caracteres HTML para prevenir XSS
     */
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /* Sistema de notificaciones (toasts) simple, top-right */
    function showNotification(type, message, options = {}) {
        try {
            const container = document.getElementById('app_notifications');
            if (!container) {
                console.warn('Contenedor de notificaciones no encontrado, fallback a alert');
                return;
            }

            const autoClose = options.autoClose !== undefined ? options.autoClose : 4500;
            const id = 'notif_' + Date.now() + '_' + Math.floor(Math.random() * 1000);

            const notif = document.createElement('div');
            notif.className = 'app-notif ' + (type === 'success' ? 'app-notif-success' : 'app-notif-error');
            notif.id = id;

            const msgSpan = document.createElement('div');
            msgSpan.className = 'notif-msg';
            msgSpan.textContent = message;

            const close = document.createElement('div');
            close.className = 'notif-close';
            close.innerHTML = '<ion-icon name="close-outline" style="font-size:1.1rem;color:rgba(255,255,255,0.95);"></ion-icon>';
            close.onclick = function() { if (notif && notif.parentNode) notif.parentNode.removeChild(notif); };

            notif.appendChild(msgSpan);
            notif.appendChild(close);

            if (container.firstChild) container.insertBefore(notif, container.firstChild);
            else container.appendChild(notif);

            if (autoClose > 0) {
                setTimeout(() => { try { if (notif && notif.parentNode) notif.parentNode.removeChild(notif); } catch(e){} }, autoClose);
            }
        } catch (e) {
            console.error('Error mostrando notificación:', e);
        }
    }

    function showSuccess(message, options) { showNotification('success', message, options); }
    function showError(message, options) { showNotification('error', message, options); }

    /**
     * Muestra un modal de confirmación reutilizable.
     */
    function showConfirm(message, options = {}) {
        return new Promise((resolve) => {
            try {
                const modalEl = document.getElementById('modalConfirm');
                if (!modalEl || typeof bootstrap === 'undefined') {
                    const result = confirm(message);
                    resolve(result);
                    return;
                }

                const msgEl = modalEl.querySelector('.confirm-msg');
                const btnYes = modalEl.querySelector('#modalConfirmYes');
                const btnNo = modalEl.querySelector('#modalConfirmNo');

                if (msgEl) msgEl.textContent = message;

                const bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

                function cleanup(result) {
                    try {
                        btnYes.removeEventListener('click', onYes);
                        btnNo.removeEventListener('click', onNo);
                    } catch (e) {}
                    try { bsModal.hide(); } catch (e) {}
                    resolve(result);
                }

                function onYes() { cleanup(true); }
                function onNo() { cleanup(false); }

                btnYes.addEventListener('click', onYes);
                btnNo.addEventListener('click', onNo);

                bsModal.show();
            } catch (e) {
                console.error('showConfirm error:', e);
                const result = confirm(message);
                resolve(result);
            }
        });
    }

    return {
        ajaxCall,
        mostrarError,
        ensureNumberEditable,
        escapeHtml,
        showNotification,
        showSuccess,
        showError,
        showConfirm
    };
})();

// Exponer helpers globalmente
window.ajaxCall = ERPUtils.ajaxCall;
window.mostrarError = ERPUtils.mostrarError;
window.showError = ERPUtils.showError;
window.showSuccess = ERPUtils.showSuccess;
window.showNotification = ERPUtils.showNotification;
window.showConfirm = ERPUtils.showConfirm;

// ============================================================================
// MÓDULO: Gestión de Usuarios y Perfil
// ============================================================================
const UsuariosModule = (function() {
    const { ajaxCall, mostrarError, showSuccess, showError, showConfirm } = ERPUtils;

    function initToggleUsuarios() {
        $(document).on('click', '#btnToggleUsuarios', function(e) {
            e.preventDefault();
            const $seccion = $('#seccionUsuariosRegistrados');
            const $icon = $('#toggleUsuariosIcon');
            const $text = $('#toggleUsuariosText');
            
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
    }

    function initModalEditarPerfil() {
        $('#modalEditarMiPerfil').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button ? $(button).data('id') : CURRENT_USER.id;
            const $form = $('#formEditarMiPerfil');
            
            if (!$form.length) return;
            
            $form[0].reset();
            $('#perfil_id').val('');
            $('#perfil_rol').prop('disabled', false);
            
            if (userId && userId != CURRENT_USER.id) {
                $('#modalEditarMiPerfilTitle').text('Editar Usuario');
                $('#perfil_id').val(userId);
                $('#perfil_nombre').val($(button).data('nombre'));
                $('#perfil_username').val($(button).data('username'));
                $('#perfil_rol').val($(button).data('rol')).prop('disabled', true);
                $('#btnAbrirCambiarPassword').hide();
            } else {
                $('#modalEditarMiPerfilTitle').text('Editar Mi Perfil');
                $('#perfil_id').val(CURRENT_USER.id);
                $('#perfil_nombre').val(CURRENT_USER.nombre);
                $('#perfil_username').val(CURRENT_USER.username);
                $('#perfil_rol').val(CURRENT_USER.rol);
                $('#btnAbrirCambiarPassword').show();

                try {
                    if (CURRENT_USER.rol !== 'SU') {
                        $('#perfil_rol').prop('disabled', true);
                    }
                } catch (e) {}
            }
        });
    }

    function initSubmitEditarPerfil() {
        $(document).on('submit', '#formEditarMiPerfil', function(e) {
            e.preventDefault();
            ajaxCall('user', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $('#modalEditarMiPerfil').modal('hide');
                        showSuccess('Perfil guardado correctamente.');
                        setTimeout(() => { window.location.href = BASE_URL + 'index.php?controller=user&action=list'; }, 900);
                    } else {
                        showError('No se pudo guardar el perfil. ' + (r.error || 'Revise los datos.'));
                    }
                })
                .fail(xhr => mostrarError('guardar perfil', xhr));
        });
    }

    function initCambiarPassword() {
        $(document).on('click', '#btnAbrirCambiarPassword', function(e) {
            e.preventDefault();
            const modalEditarPerfil = bootstrap.Modal.getInstance(document.getElementById('modalEditarMiPerfil'));
            if (modalEditarPerfil) { modalEditarPerfil.hide(); } else { $('#modalEditarMiPerfil').modal('hide'); }

            setTimeout(function() {
                $('#own_username').val(CURRENT_USER.username || CURRENT_USER.user_username);
                $('#modalCambiarPasswordOwn').modal('show');
            }, 300);
        });

        $('#modalCambiarPasswordOwn').on('show.bs.modal', function() {
            const $form = $('#formCambiarPasswordOwn');
            if (!$form.length) return;
            $form[0].reset();
            if (!$('#own_username').val()) {
                $('#own_username').val(CURRENT_USER.username || CURRENT_USER.user_username);
            }
            $('#ownPasswordMatchMessage').text('').removeClass('text-success text-danger');
            $('#btnGuardarPasswordOwn').prop('disabled', false);
        });

        $('#modalCambiarPasswordUser').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const username = button ? $(button).data('username') : '';
            const $form = $('#formCambiarPasswordUser');
            if (!$form.length) return;
            $form[0].reset();
            $('#target_username').val(username);
            $('#target_username_display').text(username || '-');
            $('#userPasswordMatchMessage').text('').removeClass('text-success text-danger');
            $('#btnGuardarPasswordUser').prop('disabled', false);
        });

        $(document).on('click', '#toggleOwnActual, #toggleOwnNueva, #toggleOwnConfirmar, #toggleUserNueva, #toggleUserConfirmar', function() {
            const $input = $(this).closest('.input-group').find('input');
            const $icon = $(this).find('ion-icon');
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.attr('name', 'eye-off-outline');
            } else {
                $input.attr('type', 'password');
                $icon.attr('name', 'eye-outline');
            }
        });

        $(document).on('input', '#own_password_nueva, #own_password_confirmar', function() {
            const nueva = $('#own_password_nueva').val();
            const confirmar = $('#own_password_confirmar').val();
            const $message = $('#ownPasswordMatchMessage');
            const $btnSubmit = $('#btnGuardarPasswordOwn');
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

        $(document).on('input', '#target_password_new, #target_password_confirm', function() {
            const nueva = $('#target_password_new').val();
            const confirmar = $('#target_password_confirm').val();
            const $message = $('#userPasswordMatchMessage');
            const $btnSubmit = $('#btnGuardarPasswordUser');
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

        $(document).on('submit', '#formCambiarPasswordOwn', function(e) {
            e.preventDefault();
            if ($('#own_password_nueva').val() !== $('#own_password_confirmar').val()) { showError('Las contraseñas no coinciden.'); return; }
            ajaxCall('auth', 'changePasswordWithValidation', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $('#modalCambiarPasswordOwn').modal('hide');
                        showSuccess('Contraseña actualizada. Cerrando sesión...');
                        setTimeout(() => { window.location.href = BASE_URL + 'index.php?controller=auth&action=logout'; }, 900);
                    } else {
                        showError('No se pudo cambiar la contraseña. ' + (r.error || ''));
                    }
                })
                .fail(xhr => mostrarError('cambiar contraseña', xhr));
        });

        $(document).on('submit', '#formCambiarPasswordUser', function(e) {
            e.preventDefault();
            if ($('#target_password_new').val() !== $('#target_password_confirm').val()) { showError('Las contraseñas no coinciden.'); return; }
            ajaxCall('auth', 'changePassword', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $('#modalCambiarPasswordUser').modal('hide');
                        showSuccess('Contraseña actualizada correctamente.');
                    } else {
                        showError('Error: ' + (r.error || ''));
                    }
                })
                .fail(xhr => mostrarError('cambiar contraseña usuario', xhr));
        });
    }

    function initGestionUsuarios() {
        $('#modalUsuario').on('show.bs.modal', function() {
            const $form = $('#formUsuario');
            if (!$form.length) return;
            $form[0].reset();
            $('#usuario_id').val('');
            $('#usuario_password').prop('required', true);
        });

        $(document).on('submit', '#formUsuario', function(e) {
            e.preventDefault();
            ajaxCall('user', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $('#modalUsuario').modal('hide');
                        showSuccess('Usuario guardado correctamente.');
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('Error al guardar: ' + (r.error || ''));
                    }
                })
                .fail(xhr => mostrarError('guardar usuario', xhr));
        });

        $(document).on('click', '.btn-delete-user', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este usuario?').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('user', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            showSuccess('Usuario eliminado correctamente.');
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('Error al eliminar: ' + (r.error || ''));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar usuario', xhr));
            });
        });
    }

    function init() {
        initToggleUsuarios();
        initModalEditarPerfil();
        initSubmitEditarPerfil();
        initCambiarPassword();
        initGestionUsuarios();
        console.log('[✓] Módulo Usuarios inicializado');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Gestión de Ingresos
// ============================================================================
const IngresosModule = (function() {
    const { ajaxCall, mostrarError, showSuccess, showError, showConfirm } = ERPUtils;
    let contadorPagos = 0;

    function agregarFilaPago(metodo = '', monto = '') {
        contadorPagos++;
        const html = `
            <div class="row g-3 mb-2 pago-parcial-item" data-pago-id="${contadorPagos}">
                <div class="col-md-6">
                    <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm pago-metodo" required>
                        <option value="">Seleccione...</option>
                        <option value="Efectivo" ${metodo === 'Efectivo' ? 'selected' : ''}>Efectivo</option>
                        <option value="Transferencia" ${metodo === 'Transferencia' ? 'selected' : ''}>Transferencia</option>
                        <option value="Depósito" ${metodo === 'Depósito' ? 'selected' : ''}>Depósito</option>
                        <option value="Tarjeta Débito" ${metodo === 'Tarjeta Débito' ? 'selected' : ''}>Tarjeta Débito</option>
                        <option value="Tarjeta Crédito" ${metodo === 'Tarjeta Crédito' ? 'selected' : ''}>Tarjeta Crédito</option>
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
    }

    function actualizarBotonesEliminar() {
        const totalFilas = $('.pago-parcial-item').length;
        if (totalFilas === 1) { $('.btn-eliminar-pago').addClass('d-none'); } else { $('.btn-eliminar-pago').removeClass('d-none'); }
    }

    function actualizarResumenPagos() {
        const montoTotal = parseFloat($('#in_monto').val()) || 0;
        let sumaParciales = 0;
        $('.pago-monto').each(function() { sumaParciales += parseFloat($(this).val()) || 0; });
        const diferencia = montoTotal - sumaParciales;
        $('#display_monto_total').text('$' + montoTotal.toFixed(2));
        $('#display_suma_parciales').text('$' + sumaParciales.toFixed(2));
        $('#display_diferencia').text('$' + Math.abs(diferencia).toFixed(2));
        
        const $displayDif = $('#display_diferencia');
        const $labelDif = $('#label_diferencia');
        if (Math.abs(diferencia) < 0.01) {
            $displayDif.removeClass('text-danger text-warning').addClass('text-success');
            $labelDif.html('✓ Cuadrado <span class="badge bg-success ms-2">OK</span>');
        } else if (diferencia > 0) {
            $displayDif.removeClass('text-success text-warning').addClass('text-danger');
            $labelDif.html('⚠ Pendiente <span class="badge bg-danger ms-2">FALTA</span>');
        } else {
            $displayDif.removeClass('text-success text-danger').addClass('text-warning');
            $labelDif.html('⚠ Exceso <span class="badge bg-warning text-dark ms-2">SOBRA</span>');
        }
    }

    function initModalIngreso() {
        $('#modalIngreso').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const ingresoId = button ? $(button).data('id') : null;
            const $form = $('#formIngreso');
            const $selectCat = $('#in_id_categoria');

            if (!$form.length) return;

            $form.find('input, select, textarea').not(':button, :submit, :reset, :hidden').val('');
            $('#ingreso_id').val('');
            $('#in_monto').prop('readonly', false).prop('disabled', false).css('background-color', '');
            $('#seccion_pago_unico').show();
            $('#seccion_cobro_dividido').hide();
            $('#toggleCobroDividido').prop('checked', false);
            $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

            ajaxCall('ingreso', 'getCategoriasIngreso', {}, 'GET')
                .done(categorias => {
                    $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');
                    if (categorias && Array.isArray(categorias)) {
                        categorias.forEach(cat => {
                            if (cat && cat.id_categoria) {
                                $selectCat.append(`<option value="${cat.id_categoria}">${cat.nombre}</option>`);
                            }
                        });
                    }
                    $selectCat.prop('disabled', false);

                    if (ingresoId) {
                        $('#modalIngresoTitle').text('Editar Ingreso');
                        ajaxCall('ingreso', 'getIngresoData', { id: ingresoId }, 'GET')
                            .done(data => {
                                if (data && !data.error && data.folio_ingreso) {
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

                                    $('#contenedor_pagos_parciales').empty();
                                    contadorPagos = 0;

                                    if (data.pagos_parciales && data.pagos_parciales.length > 0) {
                                        $('#toggleCobroDividido').prop('checked', true).trigger('change');
                                        $('#contenedor_pagos_parciales').empty();
                                        data.pagos_parciales.forEach(pago => {
                                            agregarFilaPago(pago.metodo_pago, pago.monto);
                                        });
                                    } else if (data.metodo_de_pago && data.metodo_de_pago !== 'Mixto') {
                                        $('#toggleCobroDividido').prop('checked', false).trigger('change');
                                        $('#in_metodo_unico').val(data.metodo_de_pago);
                                    }
                                } else {
                                    $('#modalIngreso').modal('hide');
                                    showError('Error al cargar datos: ' + (data.error || ''));
                                }
                            })
                            .fail(xhr => { mostrarError('cargar datos ingreso', xhr); $('#modalIngreso').modal('hide'); });
                    } else {
                        $('#modalIngresoTitle').text('Registrar Nuevo Ingreso');
                        $('#in_anio').val(new Date().getFullYear());
                        $('#seccion_pago_unico').show();
                        $('#seccion_cobro_dividido').hide();
                        $('#toggleCobroDividido').prop('checked', false);
                        $('#contenedor_pagos_parciales').empty();
                        contadorPagos = 0;
                    }
                })
                .fail(xhr => mostrarError('cargar categorías ingreso', xhr));
        });
    }

    function initTogglePagosDivididos() {
        $(document).on('change', '#toggleCobroDividido', function() {
            const esDividido = $(this).is(':checked');
            const monto = parseFloat($('#in_monto').val()) || 0;
            $('#in_monto').prop('readonly', false).prop('disabled', false).css('background-color', '');
            
            if (esDividido) {
                $('#seccion_pago_unico').hide();
                $('#seccion_cobro_dividido').show();
                $('#contenedor_pagos_parciales').empty();
                contadorPagos = 0;
                agregarFilaPago('', '');
                agregarFilaPago('', '');
                actualizarResumenPagos();
            } else {
                $('#seccion_pago_unico').show();
                $('#seccion_cobro_dividido').hide();
                if (monto > 0) { $('#in_monto_unico').val(monto.toFixed(2)); }
                $('#contenedor_pagos_parciales').empty();
                contadorPagos = 0;
            }
        });

        $(document).on('input', '#in_monto', function() {
            const monto = parseFloat($(this).val()) || 0;
            if (!$('#toggleCobroDividido').is(':checked')) { $('#in_monto_unico').val(monto.toFixed(2)); }
            if ($('#toggleCobroDividido').is(':checked')) { actualizarResumenPagos(); }
        });

        $(document).on('click', '#btnAgregarPago', function() { agregarFilaPago(); });

        $(document).on('click', '.btn-eliminar-pago', function() {
            if ($('.pago-parcial-item').length > 1) {
                $(this).closest('.pago-parcial-item').remove();
                contadorPagos = $('.pago-parcial-item').length;
                actualizarResumenPagos();
                actualizarBotonesEliminar();
            } else {
                showError('Debe mantener al menos un método de pago en el cobro dividido.');
            }
        });

        $(document).on('input', '.pago-monto', function() { actualizarResumenPagos(); });
    }

    function initSubmitIngreso() {
        $(document).on('submit', '#formIngreso', function(e) {
            e.preventDefault();
            const esDividido = $('#toggleCobroDividido').is(':checked');
            let formData = $(this).serializeArray();
            let dataObj = {};
            formData.forEach(item => { dataObj[item.name] = item.value; });
            
            if (!esDividido) {
                const metodoUnico = $('#in_metodo_unico').val();
                if (!metodoUnico) { showError('Debe seleccionar un método de pago.'); return; }
                dataObj.metodo_de_pago = metodoUnico;
                dataObj.pagos = JSON.stringify([{ metodo: metodoUnico, monto: parseFloat($('#in_monto').val()) }]);
            } else {
                const montoTotal = parseFloat($('#in_monto').val()) || 0;
                let sumaParciales = 0;
                const pagos = [];
                let valido = true;
                $('.pago-parcial-item').each(function() {
                    const metodo = $(this).find('.pago-metodo').val();
                    const monto = parseFloat($(this).find('.pago-monto').val()) || 0;
                    if (!metodo || monto <= 0) { valido = false; return false; }
                    sumaParciales += monto;
                    pagos.push({ metodo: metodo, monto: monto });
                });
                if (!valido) { showError('Todos los pagos parciales deben tener método y monto válido.'); return; }
                const diferencia = Math.abs(montoTotal - sumaParciales);
                if (diferencia >= 0.01) { showError(`La suma de pagos parciales no coincide con el total.`); return; }
                dataObj.metodo_de_pago = 'Mixto';
                dataObj.pagos = JSON.stringify(pagos);
            }
            
            ajaxCall('ingreso', 'save', dataObj)
                .done(r => {
                    if (r.success) {
                        showSuccess('Ingreso guardado correctamente.');
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('Error al guardar: ' + (r.error || 'Verifique datos.'));
                    }
                })
                .fail(xhr => mostrarError('guardar ingreso', xhr));
        });
    }

    function initEliminarIngreso() {
        $(document).on('click', '.btn-del-ingreso', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este ingreso? Se borrarán sus pagos parciales.').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('ingreso', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            showSuccess('Ingreso eliminado correctamente.');
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('Error al eliminar: ' + (r.error || 'Error.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar ingreso', xhr));
            });
        });
    }

    function initBuscadorIngresos() {
        const $searchInput = $('#searchIngresos');
        if (!$searchInput.length) return;
        const $clearBtn = $('#clearSearchIngresos');
        const $fechaInicio = $('#fechaInicioIngresos');
        const $fechaFin = $('#fechaFinIngresos');
        const $clearDateBtn = $('#clearDateIngresos');
        const $resultCount = $('#resultCountIngresos');
        const $tableBody = $('#tablaIngresos');
        
        function filtrarIngresos() {
            const searchTerm = $searchInput.val().toLowerCase().trim();
            const fechaInicio = $fechaInicio.val();
            const fechaFin = $fechaFin.val();
            $clearBtn.toggle(searchTerm.length > 0);
            $clearDateBtn.toggle(!!(fechaInicio || fechaFin));
            let visibleCount = 0;
            let totalCount = 0;
            
            $tableBody.find('tr').each(function() {
                const $row = $(this);
                if ($row.find('td[colspan]').length > 0) return;
                totalCount++;
                const alumno = $row.find('td').eq(0).text().toLowerCase();
                let folio = '';
                const $editBtn = $row.find('.btn-edit-ingreso');
                if ($editBtn.length) folio = $editBtn.data('id').toString().toLowerCase();
                const fechaRegistro = $row.attr('data-fecha');
                const searchableText = folio + ' ' + alumno;
                let matchText = !searchTerm || searchableText.includes(searchTerm);
                let matchDate = true;
                if (fechaRegistro && (fechaInicio || fechaFin)) {
                    if (fechaInicio && fechaFin) matchDate = fechaRegistro >= fechaInicio && fechaRegistro <= fechaFin;
                    else if (fechaInicio) matchDate = fechaRegistro >= fechaInicio;
                    else if (fechaFin) matchDate = fechaRegistro <= fechaFin;
                }
                if (matchText && matchDate) { $row.show(); visibleCount++; } else { $row.hide(); }
            });
            
            if (searchTerm.length > 0 || fechaInicio || fechaFin) {
                if (visibleCount === 0) {
                    $resultCount.html('<ion-icon name="alert-circle-outline" style="vertical-align:middle;"></ion-icon> No se encontraron resultados').addClass('text-danger').removeClass('text-success');
                } else {
                    $resultCount.html(`<ion-icon name="checkmark-circle-outline" style="vertical-align:middle;"></ion-icon> Mostrando ${visibleCount} de ${totalCount} ingresos`).addClass('text-success').removeClass('text-danger');
                }
            } else {
                $resultCount.html('').removeClass('text-success text-danger');
            }
        }
        
        $searchInput.on('keyup', filtrarIngresos);
        $fechaInicio.on('change', filtrarIngresos);
        $fechaFin.on('change', filtrarIngresos);
        $clearBtn.on('click', function() { $searchInput.val(''); filtrarIngresos(); $searchInput.focus(); });
        $clearDateBtn.on('click', function() { $fechaInicio.val(''); $fechaFin.val(''); filtrarIngresos(); });
        $searchInput.on('keydown', function(e) { if (e.key === 'Escape') { $(this).val(''); filtrarIngresos(); } });
    }

    function init() {
        initModalIngreso();
        initTogglePagosDivididos();
        initSubmitIngreso();
        initEliminarIngreso();
        initBuscadorIngresos();
        console.log('[✓] Módulo Ingresos inicializado');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Gestión de Egresos
// ============================================================================
const EgresosModule = (function() {
    const { ajaxCall, mostrarError, ensureNumberEditable, showSuccess, showError, showConfirm } = ERPUtils;

    function initModalEgreso() {
        $('#modalEgreso').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const egresoId = button ? $(button).data('id') : null;
            const $form = $('#formEgreso');
            const $selectCat = $('#eg_id_categoria');
            const $selectPres = $('#eg_id_presupuesto');

            if (!$form.length) return;
            
            $form[0].reset();
            $('#egreso_id').val('');
            $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
            $selectPres.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
            ensureNumberEditable('#eg_monto');

            ajaxCall('presupuesto', 'getSubPresupuestos', {}, 'GET')
                .done(presupuestos => {
                    $selectPres.empty().append('<option value="">Seleccione un presupuesto...</option>');
                    if (presupuestos && Array.isArray(presupuestos)) {
                        presupuestos.forEach(p => {
                            const label = `${p.nombre} — ${p.fecha} (${p.cat_nombre})`;
                            $selectPres.append(`<option value="${p.id_presupuesto}" data-categoria="${p.id_categoria}">${label}</option>`);
                        });
                    } else {
                        $selectPres.append('<option value="">-- No hay presupuestos --</option>');
                    }
                    $selectPres.prop('disabled', false);

                    $selectPres.off('change.presupuestoSync').on('change.presupuestoSync', function() {
                        const catId = $(this).find(':selected').data('categoria');
                        if (catId) $selectCat.val(catId.toString());
                    });

                    return ajaxCall('egreso', 'getCategoriasEgreso', {}, 'GET');
                })
                .then(categorias => {
                    $selectCat.empty().append('<option value="">Seleccione...</option>');
                    if (categorias && Array.isArray(categorias)) {
                        categorias.forEach(cat => {
                            const cid = cat.id_categoria || cat.id;
                            const nom = cat.nombre || cat.cat_nombre;
                            const pid = cat.id_presupuesto || '';
                            $selectCat.append(`<option value="${cid}" data-presupuesto="${pid}">${nom}</option>`);
                        });
                    } else {
                        $selectCat.append('<option value="">-- No hay categorías --</option>');
                    }
                    $selectCat.prop('disabled', false);

                    $selectCat.off('change.egresoPres').on('change.egresoPres', function() {
                        const presId = $(this).find(':selected').data('presupuesto');
                        if (presId) $selectPres.val(presId.toString());
                    });

                    if (egresoId) {
                        $('#modalEgresoTitle').text('Editar Egreso');
                        ajaxCall('egreso', 'getEgresoData', { id: egresoId }, 'GET')
                            .done(data => {
                                if (data && !data.error && data.folio_egreso) {
                                    $('#egreso_id').val(data.folio_egreso);
                                    $('#eg_fecha').val(data.fecha);
                                    $('#eg_monto').val(data.monto);
                                    $selectCat.val(data.id_categoria);
                                    if (data.id_presupuesto) $selectPres.val(data.id_presupuesto);
                                    else {
                                        const presFromCat = $selectCat.find(':selected').data('presupuesto');
                                        if (presFromCat) $selectPres.val(presFromCat.toString());
                                    }
                                    $('#eg_proveedor').val(data.proveedor);
                                    $('#eg_destinatario').val(data.destinatario);
                                    $('#eg_forma_pago').val(data.forma_pago);
                                    $('#eg_documento_de_amparo').val(data.documento_de_amparo);
                                    $('#eg_descripcion').val(data.descripcion);
                                }
                            });
                    } else {
                        $('#modalEgresoTitle').text('Registrar Nuevo Egreso');
                    }
                });
        });
    }

    function initSubmitEgreso() {
        $(document).on('submit', '#formEgreso', function(e) {
            e.preventDefault();
            ajaxCall('egreso', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $(document).trigger('egresoGuardado');
                        showSuccess('Egreso guardado correctamente.');
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('Error al guardar: ' + (r.error || ''));
                    }
                })
                .fail(xhr => mostrarError('guardar egreso', xhr));
        });
    }

    function initEliminarEgreso() {
        $(document).on('click', '.btn-del-egreso', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este egreso?').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('egreso', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            $(document).trigger('egresoEliminado');
                            showSuccess('Egreso eliminado correctamente.');
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('Error al eliminar: ' + (r.error || ''));
                        }
                    });
            });
        });
    }

    function initBuscadorEgresos() {
        const $searchInput = $('#searchEgresos');
        if (!$searchInput.length) return;
        const $clearBtn = $('#clearSearchEgresos');
        const $fechaInicio = $('#fechaInicioEgresos');
        const $fechaFin = $('#fechaFinEgresos');
        const $clearDateBtn = $('#clearDateEgresos');
        const $resultCount = $('#resultCountEgresos');
        const $tableBody = $('#tablaEgresos');
        
        function filtrarEgresos() {
            const searchTerm = $searchInput.val().toLowerCase().trim();
            const fechaInicio = $fechaInicio.val();
            const fechaFin = $fechaFin.val();
            $clearBtn.toggle(searchTerm.length > 0);
            $clearDateBtn.toggle(!!(fechaInicio || fechaFin));
            let visibleCount = 0;
            let totalCount = 0;
            
            $tableBody.find('tr').each(function() {
                const $row = $(this);
                if ($row.find('td[colspan]').length > 0) return;
                totalCount++;
                const destinatario = $row.find('td').eq(2).text().toLowerCase();
                let folio = '';
                const $editBtn = $row.find('.btn-edit-egreso');
                if ($editBtn.length) folio = $editBtn.data('id').toString().toLowerCase();
                const fechaRegistro = $row.attr('data-fecha');
                const searchableText = folio + ' ' + destinatario;
                let matchText = !searchTerm || searchableText.includes(searchTerm);
                let matchDate = true;
                if (fechaRegistro && (fechaInicio || fechaFin)) {
                    if (fechaInicio && fechaFin) matchDate = fechaRegistro >= fechaInicio && fechaRegistro <= fechaFin;
                    else if (fechaInicio) matchDate = fechaRegistro >= fechaInicio;
                    else if (fechaFin) matchDate = fechaRegistro <= fechaFin;
                }
                if (matchText && matchDate) { $row.show(); visibleCount++; } else { $row.hide(); }
            });
            
            if (searchTerm.length > 0 || fechaInicio || fechaFin) {
                if (visibleCount === 0) {
                    $resultCount.html('<ion-icon name="alert-circle-outline" style="vertical-align:middle;"></ion-icon> No se encontraron resultados').addClass('text-danger').removeClass('text-success');
                } else {
                    $resultCount.html(`<ion-icon name="checkmark-circle-outline" style="vertical-align:middle;"></ion-icon> Mostrando ${visibleCount} de ${totalCount} egresos`).addClass('text-success').removeClass('text-danger');
                }
            } else {
                $resultCount.html('').removeClass('text-success text-danger');
            }
        }
        
        $searchInput.on('keyup', filtrarEgresos);
        $fechaInicio.on('change', filtrarEgresos);
        $fechaFin.on('change', filtrarEgresos);
        $clearBtn.on('click', function() { $searchInput.val(''); filtrarEgresos(); $searchInput.focus(); });
        $clearDateBtn.on('click', function() { $fechaInicio.val(''); $fechaFin.val(''); filtrarEgresos(); });
        $searchInput.on('keydown', function(e) { if (e.key === 'Escape') { $(this).val(''); filtrarEgresos(); } });
    }

    function init() {
        initModalEgreso();
        initSubmitEgreso();
        initEliminarEgreso();
        initBuscadorEgresos();
        console.log('[✓] Módulo Egresos inicializado');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Gestión de Categorías
// ============================================================================
const CategoriasModule = (function() {
    const { ajaxCall, mostrarError, showConfirm, showSuccess, showError } = ERPUtils;

    function initModalCategoria() {
        $('#modalCategoria').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const catId = button ? $(button).data('id') : null;
            const $form = $('#formCategoria');
            
            if (!$form.length) return;
            
            $form[0].reset();
            $('#categoria_id').val('');
            $('#div_cat_concepto').hide();
            $('#cat_concepto').val('');
            
            if (catId) {
                $('#modalCategoriaTitle').text('Editar Categoría');
                ajaxCall('categoria', 'getCategoriaData', { id: catId }, 'GET')
                    .done(data => {
                        if (data && !data.error) {
                            $('#categoria_id').val(data.id_categoria);
                            $('#cat_nombre').val(data.nombre);
                            $('#cat_tipo').val(data.tipo);
                            $('#cat_descripcion').val(data.descripcion);
                            $('#categoria_no_borrable').val(data.no_borrable || 0);
                            
                            if (data.tipo === 'Ingreso') {
                                $('#div_cat_concepto').show();
                                $('#cat_concepto').val(data.concepto || '');
                            } else {
                                $('#div_cat_concepto').hide();
                                $('#cat_concepto').val('');
                            }
                            
                            if (data.no_borrable == 1) { $('#alert_categoria_protegida').show(); }
                            else { $('#alert_categoria_protegida').hide(); }
                        }
                    });
            } else {
                $('#modalCategoriaTitle').text('Agregar Nueva Categoría');
                if ($('#cat_tipo').val() === 'Ingreso') { $('#div_cat_concepto').show(); }
            }

            $('#cat_tipo').off('change.categoria').on('change.categoria', function() {
                if ($(this).val() === 'Ingreso') { $('#div_cat_concepto').show(); }
                else { $('#div_cat_concepto').hide(); $('#cat_concepto').val(''); }
            });
        });
    }

    function initSubmitCategoria() {
        $(document).on('submit', '#formCategoria', function(e) {
            e.preventDefault();
            const tipo = $('#cat_tipo').val();
            const concepto = $('#cat_concepto').val();
            if (tipo === 'Ingreso' && !concepto) {
                showError('Debes seleccionar un concepto para las categorías de tipo Ingreso.');
                return false;
            }

            ajaxCall('categoria', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        showSuccess('Categoría guardada correctamente.');
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('Error: ' + (r.error || 'Intenta de nuevo.'));
                    }
                })
                .fail(xhr => mostrarError('guardar categoría', xhr));
        });
    }

    function initEliminarCategoria() {
        $(document).on('click', '.btn-del-categoria', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar esta categoría?').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('categoria', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            showSuccess('Categoría eliminada correctamente.');
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('Error: ' + (r.error || 'Intenta de nuevo.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar categoría', xhr));
            });
        });
    }

    function init() {
        initModalCategoria();
        initSubmitCategoria();
        initEliminarCategoria();
        console.log('[✓] Módulo Categorías inicializado');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Gestión de Presupuestos (General + Sub-Presupuestos)
// ============================================================================
const PresupuestosModule = (function() {
    const { ajaxCall, mostrarError, ensureNumberEditable, escapeHtml, showSuccess, showError, showConfirm } = ERPUtils;

    function initModalSubPresupuestoExclusivo() {
        $('#modalSubPresupuesto').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const presId = button ? $(button).data('id') : null;
            const presParentId = button ? ($(button).data('parentId') || $(button).data('parent-id') || $(button).data('parent')) : null;
            const $form = $('#formSubPresupuesto');
            const $selectCat = $('#subpres_categoria');
            const $selectPadre = $('#subpres_parent');
            const $alert = $('#subpresupuestoAlert');
            const $msgNoCat = $('#msgNoCategoriasEgreso');

            if (!$form.length) return;

            $form[0].reset();
            $alert.addClass('d-none').text('');
            $('#subpresupuesto_id').val('');
            ensureNumberEditable('#subpres_monto');
            $msgNoCat.addClass('d-none');

            $selectPadre.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

            ajaxCall('presupuesto', 'getPresupuestosGenerales', {}, 'GET')
                .then(presupuestos => {
                    $selectPadre.empty().append('<option value="">Seleccione un presupuesto general...</option>');
                    if (presupuestos && Array.isArray(presupuestos)) {
                        presupuestos.forEach(p => {
                            $selectPadre.append(`<option value="${p.id_presupuesto}">${escapeHtml(p.nombre || 'Sin nombre')} — ${p.fecha}</option>`);
                        });
                    }
                    $selectPadre.prop('disabled', false);
                    if (presParentId) $selectPadre.val(presParentId);

                    return ajaxCall('categoria', 'getCategoriasEgreso', {}, 'GET');
                })
                .then(categorias => {
                    $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');
                    let countEgreso = 0;
                    const idsAgregados = new Set();

                    if (categorias && Array.isArray(categorias)) {
                        categorias.forEach(cat => {
                            const catId = cat.id_categoria || cat.id;
                            const nombre = cat.nombre || cat.cat_nombre || 'Sin nombre';
                            if (cat.tipo === 'Egreso' && catId && !idsAgregados.has(catId)) {
                                $selectCat.append(`<option value="${catId}">${escapeHtml(nombre)}</option>`);
                                idsAgregados.add(catId);
                                countEgreso++;
                            }
                        });
                    }

                    if (countEgreso === 0) $msgNoCat.removeClass('d-none');
                    $selectCat.prop('disabled', false);

                    if (presId) {
                        $('#modalSubPresupuestoTitle').text('Editar Sub-Presupuesto');
                        ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET').done(data => {
                            if (data && !data.error) {
                                $('#subpresupuesto_id').val(data.id_presupuesto || data.id);
                                $('#subpres_nombre').val(data.nombre);
                                $('#subpres_monto').val(data.monto_limite || data.monto);
                                if (data.id_categoria) $selectCat.val(data.id_categoria);
                                if (data.parent_presupuesto) $selectPadre.val(data.parent_presupuesto);
                                $('#subpres_fecha').val(data.fecha);
                            }
                        });
                    } else {
                        $('#modalSubPresupuestoTitle').text('Agregar Sub-Presupuesto');
                    }
                })
                .fail(xhr => mostrarError('cargar datos para modal subpresupuesto', xhr));
        });
    }

    function initSubmitSubPresupuestoExclusivo() {
        $(document).on('submit', '#formSubPresupuesto', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $alert = $('#subpresupuestoAlert');
            $alert.addClass('d-none').text('');

            const parent = $('#subpres_parent').val();
            const cat = $('#subpres_categoria').val();
            const monto = $('#subpres_monto').val();
            const fecha = $('#subpres_fecha').val();

            if (!parent || !cat || !monto || !fecha) {
                $alert.removeClass('d-none').text('Todos los campos marcados con * son obligatorios.');
                return;
            }

            const $btn = $('#btnGuardarSubPresupuesto');
            $btn.prop('disabled', true);

            ajaxCall('presupuesto', 'save', $form.serialize())
                .done(r => {
                    if (r.success) {
                        showSuccess('Sub-presupuesto guardado correctamente.');
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        $alert.removeClass('d-none').text(r.error || 'Error al guardar.');
                    }
                })
                .fail(xhr => {
                    mostrarError('guardar sub-presupuesto', xhr);
                    $alert.removeClass('d-none').text('Error inesperado al guardar.');
                })
                .always(() => { $btn.prop('disabled', false); });
        });
    }

    function populatePresupuestoCategoria(presId = null) {
        const $selectCat = $('#pres_categoria');
        if (!$selectCat.length) return Promise.reject('Selector no encontrado');
        $selectCat.empty().append('<option value="">Cargando categorías...</option>').prop('disabled', true);
        return ajaxCall('presupuesto', 'getCategoriasPresupuesto', {}, 'GET')
            .then(categorias => {
                $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');
                if (categorias && Array.isArray(categorias)) {
                    categorias.forEach(cat => {
                        const catId = cat.id_categoria || cat.id || '';
                        const presup = cat.id_presupuesto || '';
                        const nombre = cat.nombre || cat.cat_nombre || 'Sin nombre';
                        $selectCat.append(`<option value="${catId}" data-presupuesto="${presup}">${escapeHtml(nombre)}</option>`);
                    });
                } else { $selectCat.append('<option value="">-- No hay categorías --</option>'); }
                $selectCat.prop('disabled', false);
                return categorias;
            });
    }

    function initModalPresupuestoGeneral() {
        $('#modalPresupuestoGeneral').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const presId = button ? $(button).data('id') : null;
            const $form = $('#formPresupuestoGeneral');
            if (!$form.length) return;
            $form[0].reset();
            $('#presgen_id').val('');
            
            if (presId) {
                $('#modalPresupuestoGeneralTitle').text('Editar Presupuesto General');
                ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET').done(data => {
                    if (data && !data.error) {
                        $('#presgen_id').val(data.id_presupuesto ?? data.id ?? '');
                        const montoVal = (typeof data.monto_limite !== 'undefined') ? data.monto_limite : (data.monto || '');
                        $('#presgen_monto').val(montoVal);
                        $('#presgen_fecha').val(data.fecha ?? '');
                        $('#presgen_descripcion').val(data.descripcion ?? '');
                    }
                });
            } else {
                $('#modalPresupuestoGeneralTitle').text('Agregar Presupuesto General');
            }
        });
    }

    function initSubmitPresupuestoGeneral() {
        $(document).on('submit', '#formPresupuestoGeneral', function(e) {
            e.preventDefault();
            ajaxCall('presupuesto', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        showSuccess('Presupuesto guardado correctamente.');
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('Error: ' + (r.error || 'Intenta de nuevo.'));
                    }
                })
                .fail(xhr => mostrarError('guardar presupuesto general', xhr));
        });
    }

    function initModalSubPresupuesto() {
        $('#modalPresupuesto').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const presId = button ? $(button).data('id') : null;
            const $form = $('#formPresupuesto');
            const $selectCat = $('#pres_categoria');
            const $selectPadre = $('#pres_parent');
            const $alert = $('#presupuestoAlert');

            if (!$form.length) return;
            $form[0].reset();
            $alert.addClass('d-none').text('');
            $('#presupuesto_id').val('');
            ensureNumberEditable('#pres_monto');

            $selectPadre.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

            ajaxCall('presupuesto', 'getPresupuestosGenerales', {}, 'GET')
                .then(presupuestos => {
                    $selectPadre.empty().append('<option value="">Seleccione un presupuesto general...</option>');
                    if (presupuestos && Array.isArray(presupuestos)) {
                        presupuestos.forEach(p => {
                            const label = `${p.nombre} — ${p.fecha}`;
                            $selectPadre.append(`<option value="${p.id_presupuesto}">${escapeHtml(label)}</option>`);
                        });
                    }
                    $selectPadre.prop('disabled', false);
                    return populatePresupuestoCategoria(presId);
                })
                .done(() => {
                    if (presId) {
                        $('#modalPresupuestoTitle').text('Editar Sub-Presupuesto');
                        ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET').done(data => {
                            if (data && !data.error) {
                                $('#presupuesto_id').val(data.id_presupuesto || data.id);
                                $('#pres_nombre').val(data.nombre);
                                $('#pres_monto').val(data.monto_limite || data.monto);
                                if (data.id_categoria) $selectCat.val(data.id_categoria);
                                if (data.parent_presupuesto) $selectPadre.val(data.parent_presupuesto);
                                $('#pres_fecha').val(data.fecha);
                            }
                        });
                    } else {
                        $('#modalPresupuestoTitle').text('Agregar Nuevo Sub-Presupuesto');
                    }
                });
        });
    }

    function initSubmitSubPresupuesto() {
        $(document).on('submit', '#formPresupuesto', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $alert = $('#presupuestoAlert');
            $alert.addClass('d-none').text('');
            
            if (!$('#pres_parent').val() || !$('#pres_categoria').val() || !$('#pres_monto').val() || !$('#pres_fecha').val()) {
                $alert.removeClass('d-none').text('Todos los campos son obligatorios.');
                return;
            }

            ajaxCall('presupuesto', 'save', $form.serialize())
                .done(r => {
                    if (r.success) {
                        showSuccess('Guardado correctamente.');
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        $alert.removeClass('d-none').text(r.error || 'Error al guardar.');
                    }
                })
                .fail(xhr => {
                    mostrarError('guardar sub-presupuesto', xhr);
                    $alert.removeClass('d-none').text('Error inesperado.');
                });
        });
    }
    
    function initModalPresupuestoCategoria() {
        $('#modalPresupuestoCategoria').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const presId = button ? $(button).data('id') : null;
            const $selectCat = $('#pres_categoria_categoria');
            const $selectPadre = $('#pres_parent_categoria');
            
            $('#formPresupuestoCategoria')[0].reset();
            $('#presupuesto_categoria_id').val('');
            $selectPadre.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
            
            ajaxCall('presupuesto', 'getPresupuestosGenerales', {}, 'GET')
                .then(presupuestos => {
                    $selectPadre.empty().append('<option value="">Seleccione...</option>');
                    if (presupuestos && Array.isArray(presupuestos)) {
                        presupuestos.forEach(p => {
                            $selectPadre.append(`<option value="${p.id_presupuesto}">${p.nombre} — ${p.fecha}</option>`);
                        });
                    }
                    $selectPadre.prop('disabled', false);
                    return ajaxCall('presupuesto', 'getCategoriasPresupuesto', {}, 'GET');
                })
                .then(categorias => {
                    $selectCat.empty().append('<option value="">Seleccione...</option>');
                    if (categorias && Array.isArray(categorias)) {
                        categorias.forEach(cat => {
                            $selectCat.append(`<option value="${cat.id_categoria}">${escapeHtml(cat.nombre)}</option>`);
                        });
                    }
                    $selectCat.prop('disabled', false);
                    
                    if (presId) {
                        $('#modalPresupuestoCategoriaTitle').text('Editar Presupuesto');
                        ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET').done(data => {
                            if (data && !data.error) {
                                $('#presupuesto_categoria_id').val(data.id_presupuesto || data.id);
                                $('#pres_nombre_categoria').val(data.nombre);
                                $('#pres_monto_categoria').val(data.monto_limite || data.monto);
                                if(data.id_categoria) $selectCat.val(data.id_categoria);
                                if(data.parent_presupuesto) $selectPadre.val(data.parent_presupuesto);
                                $('#pres_fecha_categoria').val(data.fecha);
                            }
                        });
                    } else {
                         $('#modalPresupuestoCategoriaTitle').text('Asignar Presupuesto');
                    }
                });
        });
    }

    function initSubmitPresupuestoCategoria() {
        $(document).on('click', '#btnGuardarPresCategoria', function(e) {
            e.preventDefault();
            const formData = $('#formPresupuestoCategoria').serialize();
            ajaxCall('presupuesto', 'save', formData).done(r => {
                if (r.success) {
                    showSuccess('Presupuesto guardado.');
                    setTimeout(() => { window.location.reload(); }, 900);
                } else {
                    showError('Error: ' + (r.error || 'Intenta de nuevo.'));
                }
            }).fail(xhr => mostrarError('guardar presupuesto', xhr));
        });
    }

    function initEliminarPresupuesto() {
        $(document).on('click', '.btn-del-presgen', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este presupuesto general? Se eliminarán todos los sub-presupuestos asociados.').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('presupuesto', 'deletePresupuestoGeneral', { id: id })
                    .done(r => {
                        if (r.success) {
                            showSuccess('Eliminado correctamente.');
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('Error: ' + (r.error || 'Error.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar presupuesto general', xhr));
            });
        });
        
        $(document).on('click', '.btn-del-presupuesto', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este sub-presupuesto?').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('presupuesto', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            showSuccess('Eliminado correctamente.');
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('Error: ' + (r.error || 'Error.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar sub-presupuesto', xhr));
            });
        });
    }

    function initRefrescarPresupuestos() {
        $(document).on('click', '#btnRefrescarPresupuestos', () => window.location.reload());
    }

    function init() {
        initModalPresupuestoGeneral();
        initSubmitPresupuestoGeneral();
        initModalSubPresupuesto();
        initSubmitSubPresupuesto();
        initModalPresupuestoCategoria();
        initSubmitPresupuestoCategoria();
        initEliminarPresupuesto();
        initRefrescarPresupuestos();
        // NUEVO: Inicializar el flujo exclusivo de subpresupuestos
        initModalSubPresupuestoExclusivo();
        initSubmitSubPresupuestoExclusivo();
        console.log('[✓] Módulo Presupuestos inicializado');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Sistema de Alertas de Presupuestos
// ============================================================================
const AlertasPresupuestosModule = (function() {
    const { ajaxCall } = ERPUtils;

    function actualizarBadgeAlertas() {
        const $badge = $('#badgeAlertasPresupuestos');
        if (!$badge.length) return;
        
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
            .fail(xhr => console.error('[ERROR] Alertas presupuestos:', xhr));
    }

    function init() {
        actualizarBadgeAlertas();
        setInterval(actualizarBadgeAlertas, 30000);
        $(document).on('egresoGuardado egresoEliminado', actualizarBadgeAlertas);
        console.log('[✓] Sistema de Alertas de Presupuestos inicializado');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Dashboard
// ============================================================================
const DashboardModule = (function() {
    function init() {
        if (typeof cargarResumenMensual === 'undefined' && $('#chartIngresosEgresos').length > 0) {
            console.log('[INFO] Dashboard detectado.');
        }
        console.log('[✓] Módulo Dashboard inicializado');
    }
    return { init };
})();

// ============================================================================
// MÓDULO: Auditoría (Visor de Detalles, Gráficas y Reportes)
// ============================================================================
const AuditoriaModule = (function() {
    const { ajaxCall, escapeHtml, showNotification, showError } = ERPUtils;
    let datosReporteAuditoria = null;
    let chartAuditoriaPorSeccion = null;
    let chartAuditoriaPorAccion = null;
    let chartAuditoriaPorUsuario = null;

    // --- FUNCIONES DE MODAL DE DETALLES (Lo que ya tenías en v2.2) ---
    function initModalDetalleAuditoria() {
        $('#modalDetalleAuditoria').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const auditoriaId = button ? $(button).data('id') : null;
            const $body = $('#detalleAuditoriaBody');
            
            if (!$body.length) return;
            $body.html('<p class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Cargando...</p>');
            
            if (!auditoriaId) {
                $body.html('<p class="text-danger">Error: ID no especificado.</p>');
                return;
            }
            
            ajaxCall('auditoria', 'getDetalle', { id: auditoriaId }, 'GET')
                .done(data => {
                    if (data && !data.error && data.id_auditoria) {
                        let html = '<div class="audit-detail-content">';
                        html += `<div class="row mb-3"><div class="col-md-6"><strong>ID Auditoría:</strong> ${escapeHtml(data.id_auditoria)}</div><div class="col-md-6"><strong>Fecha/Hora:</strong> ${escapeHtml(data.fecha_hora)}</div></div>`;
                        html += `<div class="row mb-3"><div class="col-md-6"><strong>Usuario:</strong> ${escapeHtml(data.usuario_nombre || 'N/A')}</div><div class="col-md-6"><strong>Tabla:</strong> ${escapeHtml(data.tabla_afectada)}</div></div>`;
                        html += `<div class="row mb-3"><div class="col-12"><strong>Acción:</strong> <span class="badge bg-info">${escapeHtml(data.accion)}</span></div></div>`;
                        
                        if (data.datos_anteriores && data.datos_anteriores !== '{}') {
                            html += `<div class="row mb-3"><div class="col-12"><strong>Datos Anteriores:</strong><pre class="bg-light p-2 mt-2" style="max-height:200px;overflow:auto;">${escapeHtml(data.datos_anteriores)}</pre></div></div>`;
                        }
                        
                        if (data.datos_nuevos && data.datos_nuevos !== '{}') {
                            html += `<div class="row mb-3"><div class="col-12"><strong>Datos Nuevos:</strong><pre class="bg-light p-2 mt-2" style="max-height:200px;overflow:auto;">${escapeHtml(data.datos_nuevos)}</pre></div></div>`;
                        }
                        html += '</div>';
                        $body.html(html);
                    } else {
                        $body.html('<p class="text-danger">Error: No se pudo obtener el detalle.</p>');
                    }
                })
                .fail(() => { $body.html('<p class="text-danger">Error al cargar detalle.</p>'); });
        });
    }

    // --- FUNCIONES DE REPORTES Y GRÁFICAS (Lo que quitamos de la vista y agregamos aquí) ---
    
    function formatDateAud(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr + 'T00:00:00');
        return date.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function formatDateTimeAud(dateTimeStr) {
        if (!dateTimeStr) return '-';
        const date = new Date(dateTimeStr);
        return date.toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function renderChartPorSeccion(data) {
        const ctx = document.getElementById('chartAuditoriaPorSeccion');
        if (!ctx) return;
        if (chartAuditoriaPorSeccion) chartAuditoriaPorSeccion.destroy();
        chartAuditoriaPorSeccion = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data),
                datasets: [{ data: Object.values(data), backgroundColor: ['#007bff','#28a745','#ffc107','#dc3545','#17a2b8'] }]
            },
            options: { responsive: true, plugins: { title: { display: true, text: 'Por Sección' }, legend: { position: 'bottom' } } }
        });
    }

    function renderChartPorAccion(data) {
        const ctx = document.getElementById('chartAuditoriaPorAccion');
        if (!ctx) return;
        if (chartAuditoriaPorAccion) chartAuditoriaPorAccion.destroy();
        chartAuditoriaPorAccion = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: Object.keys(data),
                datasets: [{ data: Object.values(data), backgroundColor: ['#28a745','#ffc107','#dc3545'] }]
            },
            options: { responsive: true, plugins: { title: { display: true, text: 'Por Acción' }, legend: { position: 'bottom' } } }
        });
    }

    function renderChartPorUsuario(data) {
        const ctx = document.getElementById('chartAuditoriaPorUsuario');
        if (!ctx) return;
        if (chartAuditoriaPorUsuario) chartAuditoriaPorUsuario.destroy();
        chartAuditoriaPorUsuario = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(data),
                datasets: [{ label: 'Movimientos', data: Object.values(data), backgroundColor: '#17a2b8' }]
            },
            options: { responsive: true, plugins: { title: { display: true, text: 'Por Usuario' }, legend: { display: false } } }
        });
    }

    function mostrarReporte(data) {
        $('#resultadoReporteAuditoriaContainer').slideDown();
        document.getElementById('resultadoReporteAuditoriaContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });

        const tipoTexto = data.tipo === 'semanal' ? 'Semanal (últimos 7 días)' : 'Personalizado';
        $('#headerReporteAuditoria').html(`<div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">Tipo: <span class="badge bg-info">${tipoTexto}</span></h6><p class="mb-0 text-muted">Período: ${formatDateAud(data.fechaInicio)} - ${formatDateAud(data.fechaFin)}</p></div></div>`);
        $('#resumenReporteAuditoria').html(`<div class="row text-center"><div class="col-md-4"><div class="card bg-light"><div class="card-body"><h6 class="text-muted mb-2">Total</h6><h3 class="text-primary mb-0">${data.totalLogs}</h3></div></div></div><div class="col-md-4"><div class="card bg-light"><div class="card-body"><h6 class="text-muted mb-2">Secciones</h6><h3 class="text-info mb-0">${Object.keys(data.porSeccion).length}</h3></div></div></div><div class="col-md-4"><div class="card bg-light"><div class="card-body"><h6 class="text-muted mb-2">Usuarios</h6><h3 class="text-success mb-0">${Object.keys(data.porUsuario).length}</h3></div></div></div></div>`);

        renderChartPorSeccion(data.porSeccion);
        renderChartPorAccion(data.porAccion);
        renderChartPorUsuario(data.porUsuario);

        let tablaHTML = `<h6 class="mb-3">Detalle de Movimientos</h6><div class="table-responsive"><table class="table table-hover table-sm"><thead class="table-info"><tr><th>Fecha</th><th>Usuario</th><th>Sección</th><th>Acción</th><th>Detalles</th></tr></thead><tbody>`;
        if (data.movimientos && data.movimientos.length > 0) {
            data.movimientos.forEach(log => {
                const accLower = (log.accion || '').toLowerCase();
                let badge = 'bg-secondary';
                if (accLower.includes('inser') || accLower.includes('registro')) badge = 'bg-success';
                else if (accLower.includes('actual') || accLower.includes('update')) badge = 'bg-warning text-dark';
                else if (accLower.includes('elim') || accLower.includes('delete')) badge = 'bg-danger';
                tablaHTML += `<tr><td><small>${formatDateTimeAud(log.fecha_hora)}</small></td><td>${log.usuario_nombre || 'Sistema'}</td><td>${log.seccion || '-'}</td><td><span class="badge ${badge}">${log.accion}</span></td><td><small>${(log.detalles || '-').substring(0,50)}...</small></td></tr>`;
            });
        } else {
            tablaHTML += '<tr><td colspan="5" class="text-center">No hay movimientos</td></tr>';
        }
        tablaHTML += '</tbody></table></div>';
        $('#tablaReporteAuditoria').html(tablaHTML);
    }

    // Funciones públicas expuestas al ámbito global para que funcionen los 'onclick' del HTML
    function exponerFuncionesGlobales() {
        window.generarReporteAuditoria = function(tipo) {
            const url = `${BASE_URL}index.php?controller=auditoria&action=generarReporte&tipo=${tipo}`;
            fetch(url).then(r => r.json()).then(data => {
                if (data.success) {
                    data.tipo = tipo;
                    datosReporteAuditoria = data;
                    mostrarReporte(data);
                } else {
                    showNotification(data.error || 'Error al generar reporte', 'error');
                }
            }).catch(e => { console.error(e); showNotification('Error de conexión', 'error'); });
        };

        window.generarReporteAuditoriaPersonalizado = function(event) {
            event.preventDefault();
            const ini = $('#auditoria_reporte_fecha_inicio').val();
            const fin = $('#auditoria_reporte_fecha_fin').val();
            if (new Date(ini) > new Date(fin)) { showNotification('La fecha inicio no puede ser mayor a la fecha fin', 'error'); return; }
            const url = `${BASE_URL}index.php?controller=auditoria&action=generarReporte&tipo=personalizado&fecha_inicio=${ini}&fecha_fin=${fin}`;
            fetch(url).then(r => r.json()).then(data => {
                if (data.success) {
                    data.tipo = 'personalizado';
                    data.fechaInicio = ini;
                    data.fechaFin = fin;
                    datosReporteAuditoria = data;
                    mostrarReporte(data);
                    $('#collapseReportePersonalizado').collapse('hide');
                } else {
                    showNotification(data.error || 'Error al generar reporte', 'error');
                }
            }).catch(e => { console.error(e); showNotification('Error de conexión', 'error'); });
        };

        window.cerrarReporteAuditoria = function() {
            $('#resultadoReporteAuditoriaContainer').slideUp();
        };

        window.exportarReporteExcel = function() {
            if(!datosReporteAuditoria) return;
            const url = `${BASE_URL}generate_reporte_auditoria.php?tipo=${datosReporteAuditoria.tipo}&fecha_inicio=${datosReporteAuditoria.fechaInicio||''}&fecha_fin=${datosReporteAuditoria.fechaFin||''}&formato=excel`;
            window.location.href = url;
        };

        window.imprimirReporteAuditoria = function() {
            if(!datosReporteAuditoria) return;
            const url = `${BASE_URL}generate_reporte_auditoria.php?tipo=${datosReporteAuditoria.tipo}&fecha_inicio=${datosReporteAuditoria.fechaInicio||''}&fecha_fin=${datosReporteAuditoria.fechaFin||''}&formato=html`;
            window.open(url, '_blank');
        };
    }

    function init() {
        initModalDetalleAuditoria();
        exponerFuncionesGlobales();
        // Event listener para abrir modal desde filas (delegación)
        $(document).on('click', '.aud-row', function() {
            const id = $(this).data('id');
            if (id) abrirModalDetalleAuditoria(id); // Reutiliza la lógica AJAX de arriba o llama al modal
            // Nota: initModalDetalleAuditoria ya bindebaa el show.bs.modal, pero el clic directo ayuda si el data-toggle falla
            $('#modalAuditoriaDetalle').data('id', id).modal('show');
        });
        console.log('[✓] Módulo Auditoría inicializado (con reportes)');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Sidebar
// ============================================================================
const SidebarModule = (function() {
    function init() {
        $('body').on('click', '#sidebar .nav-link', function() {
            try {
                if (window.innerWidth < 992) {
                    $('#sidebar').removeClass('open').addClass('closed');
                    $('#sidebarOverlay').hide();
                    document.body.style.overflow = '';
                }
            } catch(e) { console.error('Sidebar error', e); }
        });

        // Easter Egg: 5 clics en el logo
        let clickCount = 0;
        let resetTimer = null;
        const logo = document.getElementById('logoContainer');
        if (logo) {
            logo.addEventListener('click', function() {
                clickCount++;
                this.style.transform = 'scale(0.95)';
                setTimeout(() => { this.style.transform = 'scale(1)'; }, 100);
                clearTimeout(resetTimer);
                resetTimer = setTimeout(() => { clickCount = 0; }, 2000);
                if (clickCount === 5) {
                    clickCount = 0;
                    // Cerrar sidebar si está abierto
                    $('#sidebar').removeClass('open').addClass('closed');
                    $('#sidebarOverlay').hide();
                    const modal = new bootstrap.Modal(document.getElementById('modalDesarrolladores'));
                    modal.show();
                    try { new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLaiTcIGWi77eefTRAMUKfj8LZjHAY4ktfyynksheB').play(); } catch(e){}
                }
            });
        }
        console.log('[✓] Módulo Sidebar inicializado');
    }
    return { init };
})();

// ============================================================================
// INICIALIZACIÓN GLOBAL
// ============================================================================
$(document).ready(function() {
    console.log('============================================================================');
    console.log('ERP IUM - Sistema de Gestión Financiera v3.1 (Master Extended)');
    console.log('============================================================================');
    
    try {
        ERPUtils; 
        UsuariosModule.init();
        IngresosModule.init();
        EgresosModule.init();
        CategoriasModule.init();
        PresupuestosModule.init();
        AlertasPresupuestosModule.init();
        DashboardModule.init();
        AuditoriaModule.init(); // Ahora incluye reportes y gráficas
        SidebarModule.init();   // Ahora incluye Easter Egg
        
        console.log('[✓] SISTEMA COMPLETAMENTE INICIALIZADO');
    } catch(e) {
        console.error('[✗] ERROR CRÍTICO AL INICIALIZAR:', e);
        alert('Error al inicializar el sistema. Por favor, recargue la página.');
    }
});
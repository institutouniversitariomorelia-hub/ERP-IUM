/**
 * ============================================================================
 * ERP IUM - Sistema de Gestión Financiera
 * app-optimized.js - Frontend Modular Encapsulado
 * ============================================================================
 * Versión: 2.0 Optimizada
 * Fecha: 20 de Noviembre de 2025
 * 
 * Este archivo contiene toda la lógica del frontend organizada en módulos
 * independientes para facilitar el mantenimiento y escalabilidad.
 * ============================================================================
 */

"use strict";

// ============================================================================
// MÓDULO: Utilidades Globales (ERPUtils)
// ============================================================================
const ERPUtils = (function() {
    /**
     * Wrapper genérico para llamadas AJAX
     * @param {string} controller
     * @param {string} action
     * @param {object} data
     * @param {string} method
     * @returns {jqXHR}
     */
    function ajaxCall(controller, action, data = {}, method = 'POST') {
        return $.ajax({
            url: BASE_URL + 'index.php',
            method: method,
            dataType: 'json',
            data: Object.assign({}, data, {
                controller: controller,
                action: action
            })
        });
    }

    /**
     * Manejo estándar de errores AJAX
     * @param {string} contexto
     * @param {jqXHR} xhr
     */
    function mostrarError(contexto, xhr) {
        console.error(`[ERROR] Falló la operación (${contexto})`, xhr);
        let errorMsg = 'Ocurrió un error al comunicarse con el servidor.';
        let serverError = '';

        try {
            if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                serverError = xhr.responseJSON.error;
            } else if (xhr && typeof xhr.responseText === 'string' && xhr.responseText.trim() !== '') {
                serverError = xhr.responseText.substring(0, 400);
            }
        } catch (e) {
            console.warn('No se pudo procesar el mensaje de error del servidor:', e);
        }

        // Mostrar notificación de error amigable
        showError(`${errorMsg} Detalle: ${serverError}. Revise la consola (F12) para más información.`, { autoClose: 7000 });
    }

    /**
     * Asegura que un campo numérico sea editable
     * @param {string} selector - Selector jQuery del elemento
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
     * @param {string} str - Cadena a escapar
     * @returns {string} Cadena escapada
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

    /* Sistema de notificaciones (toasts) simple, top-left */
    function showNotification(type, message, options = {}) {
        try {
            const container = document.getElementById('app_notifications');
            if (!container) {
                console.warn('Contenedor de notificaciones no encontrado');
                console.warn('Notificación:', message);
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

            // Insert on top
            if (container.firstChild) container.insertBefore(notif, container.firstChild);
            else container.appendChild(notif);

            if (autoClose > 0) {
                setTimeout(() => { try { if (notif && notif.parentNode) notif.parentNode.removeChild(notif); } catch(e){} }, autoClose);
            }
        } catch (e) {
            console.error('Error mostrando notificación:', e);
            console.warn('Notificación (fallback):', message);
        }
    }

    function showSuccess(message, options) { showNotification('success', message, options); }
    function showError(message, options) { showNotification('error', message, options); }

    /**
     * Muestra un modal de confirmación reutilizable. Devuelve una Promise<boolean>.
     * @param {string} message
     * @param {object} options
     * @returns {Promise<boolean>}
     */
    function showConfirm(message, options = {}) {
        return new Promise((resolve) => {
            try {
                const modalEl = document.getElementById('modalConfirm');
                if (!modalEl || typeof bootstrap === 'undefined') {
                    // Fallback a confirm nativo
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

    // Exponer métodos públicos
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
// Exponer ajaxCall y helpers de notificación globalmente para compatibilidad con vistas
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
    const { ajaxCall, mostrarError } = ERPUtils;

    /**
     * Inicializa el toggle de tabla de usuarios
     */
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

    /**
     * Maneja la apertura del modal de editar perfil
     */
    function initModalEditarPerfil() {
        $('#modalEditarMiPerfil').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button ? $(button).data('id') : CURRENT_USER.id;
            const $form = $('#formEditarMiPerfil');
            
            if (!$form.length) {
                console.error('[ERROR] Formulario #formEditarMiPerfil no encontrado');
                return;
            }
            
            $form[0].reset();
            $('#perfil_id').val('');
            // Asegurar estado por defecto: habilitar selector de rol (se bloqueará si corresponde)
            $('#perfil_rol').prop('disabled', false);
            
            if (userId && userId != CURRENT_USER.id) {
                // Editar otro usuario (modal usado desde la lista)
                $('#modalEditarMiPerfilTitle').text('Editar Usuario');
                $('#perfil_id').val(userId);
                $('#perfil_nombre').val($(button).data('nombre'));
                $('#perfil_username').val($(button).data('username'));
                // Mostrar el rol pero BLOQUEAR el select para edición desde la lista
                $('#perfil_rol').val($(button).data('rol')).prop('disabled', true);
                // En edición de otro usuario no mostrar el botón de cambiar contraseña (usar modal de lista)
                $('#btnAbrirCambiarPassword').hide();
            } else {
                // Editar mi perfil
                $('#modalEditarMiPerfilTitle').text('Editar Mi Perfil');
                $('#perfil_id').val(CURRENT_USER.id);
                $('#perfil_nombre').val(CURRENT_USER.nombre);
                $('#perfil_username').val(CURRENT_USER.username);
                $('#perfil_rol').val(CURRENT_USER.rol);
                // Mostrar el botón de cambiar contraseña sólo cuando se edita el propio perfil
                $('#btnAbrirCambiarPassword').show();

                // Habilitar/deshabilitar el select de rol según reglas del usuario actual (SU puede editar)
                try {
                    if (CURRENT_USER.rol === 'SU') {
                        $('#perfil_rol').prop('disabled', false);
                    } else {
                        $('#perfil_rol').prop('disabled', true);
                    }
                } catch (e) {
                    console.warn('No se pudo aplicar bloqueo de rol en el modal (propio):', e);
                }
            }
        });
    }

    /**
     * Guarda los cambios del perfil
     */
    function initSubmitEditarPerfil() {
        $(document).on('submit', '#formEditarMiPerfil', function(e) {
            e.preventDefault();
            
            ajaxCall('user', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $('#modalEditarMiPerfil').modal('hide');
                        try { showSuccess('Perfil guardado correctamente.'); } catch(e) {}
                        setTimeout(() => { window.location.href = BASE_URL + 'index.php?controller=user&action=list'; }, 900);
                    } else {
                        showError('No se pudo guardar el perfil. ' + (r.error || 'Revise los datos e intente nuevamente.'));
                    }
                })
                .fail(xhr => mostrarError('guardar perfil', xhr));
        });
    }

    /**
     * Inicializa el sistema de cambio de contraseña
     */
    function initCambiarPassword() {
        // Abrir modal propio desde el botón en el modal de perfil
        $(document).on('click', '#btnAbrirCambiarPassword', function(e) {
            e.preventDefault();

            const modalEditarPerfil = bootstrap.Modal.getInstance(document.getElementById('modalEditarMiPerfil'));
            if (modalEditarPerfil) {
                modalEditarPerfil.hide();
            } else {
                $('#modalEditarMiPerfil').modal('hide');
            }

            setTimeout(function() {
                $('#own_username').val(CURRENT_USER.username || CURRENT_USER.user_username);
                $('#modalCambiarPasswordOwn').modal('show');
            }, 300);
        });

        // Preparar modal propio al mostrarse
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

        // Preparar modal de cambio para otro usuario (se abre desde el listado)
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

        // Toggle mostrar/ocultar contraseñas (propio y usuario)
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

        // Validación en tiempo real: propio
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

        // Validación en tiempo real: usuario objetivo
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

        // Submit: propio (valida contraseña actual en backend)
        $(document).on('submit', '#formCambiarPasswordOwn', function(e) {
            e.preventDefault();
            const passwordActual = $('#own_password_actual').val();
            const passwordNueva = $('#own_password_nueva').val();
            const passwordConfirmar = $('#own_password_confirmar').val();
            if (passwordNueva !== passwordConfirmar) { showError('Las contraseñas no coinciden.'); return; }
            if (!passwordActual || !passwordNueva) { showError('Complete todos los campos requeridos.'); return; }
            ajaxCall('auth', 'changePasswordWithValidation', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $('#modalCambiarPasswordOwn').modal('hide');
                        try { showSuccess('Tu contraseña ha sido actualizada correctamente.'); } catch(e) {}
                        // Forzar cierre de sesión para que use la nueva contraseña (mostrar toast antes)
                        setTimeout(() => { window.location.href = BASE_URL + 'index.php?controller=auth&action=logout'; }, 900);
                    } else {
                        showError('No se pudo cambiar la contraseña. ' + (r.error || 'Intenta de nuevo.'));
                    }
                })
                .fail(xhr => mostrarError('cambiar contraseña', xhr));
        });

        // Submit: cambiar contraseña de otro usuario (por SU)
        $(document).on('submit', '#formCambiarPasswordUser', function(e) {
            e.preventDefault();
            const passwordNueva = $('#target_password_new').val();
            const passwordConfirm = $('#target_password_confirm').val();
            if (passwordNueva !== passwordConfirm) { showError('Las contraseñas no coinciden.'); return; }
            if (!passwordNueva) { showError('La nueva contraseña es requerida.'); return; }
            ajaxCall('auth', 'changePassword', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $('#modalCambiarPasswordUser').modal('hide');
                        showSuccess('Contraseña del usuario actualizada correctamente.');
                    } else {
                        showError('No se pudo cambiar la contraseña del usuario. ' + (r.error || 'Intenta de nuevo.'));
                    }
                })
                .fail(xhr => mostrarError('cambiar contraseña usuario', xhr));
        });
    }

    /**
     * Gestión de usuarios (crear/eliminar)
     */
    function initGestionUsuarios() {
        // Modal registrar nuevo usuario
        $('#modalUsuario').on('show.bs.modal', function() {
            const $form = $('#formUsuario');
            if (!$form.length) return;
            
            $form[0].reset();
            $('#usuario_id').val('');
            $('#usuario_password').prop('required', true);
        });

        // Submit nuevo usuario
        $(document).on('submit', '#formUsuario', function(e) {
            e.preventDefault();
            
            ajaxCall('user', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $('#modalUsuario').modal('hide');
                        try { showSuccess('Usuario guardado correctamente.'); } catch(e) {}
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('No se pudo crear/actualizar el usuario. ' + (r.error || 'Revise los datos e intente nuevamente.'));
                    }
                })
                .fail(xhr => mostrarError('guardar usuario', xhr));
        });

        // Eliminar usuario (usar confirm modal)
        $(document).on('click', '.btn-delete-user', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este usuario?').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('user', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            try { showSuccess('Usuario eliminado correctamente.'); } catch(e) {}
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('No se pudo eliminar el usuario. ' + (r.error || 'Intente de nuevo.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar usuario', xhr));
            });
        });
    }

    /**
     * Inicializa todos los componentes del módulo
     */
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
// MÓDULO: Gestión de Ingresos (con Sistema de Pagos Divididos)
// ============================================================================
const IngresosModule = (function() {
    const { ajaxCall, mostrarError } = ERPUtils;
    let contadorPagos = 0;

    /**
     * Agrega una fila de pago parcial al formulario
     */
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
                        <input type="number" step="0.01" min="0.01" class="form-control pago-monto" 
                               placeholder="0.00" value="${monto}" required>
                        <button class="btn btn-outline-danger btn-eliminar-pago ${contadorPagos === 1 ? 'd-none' : ''}" 
                                type="button" title="Eliminar">
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

    /**
     * Actualiza la visibilidad de los botones eliminar
     */
    function actualizarBotonesEliminar() {
        const totalFilas = $('.pago-parcial-item').length;
        
        if (totalFilas === 1) {
            $('.btn-eliminar-pago').addClass('d-none');
        } else {
            $('.btn-eliminar-pago').removeClass('d-none');
        }
    }

    /**
     * Actualiza el resumen de pagos parciales
     */
    function actualizarResumenPagos() {
        const montoTotal = parseFloat($('#in_monto').val()) || 0;
        let sumaParciales = 0;
        
        $('.pago-monto').each(function() {
            sumaParciales += parseFloat($(this).val()) || 0;
        });
        
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

    /**
     * Maneja la apertura del modal de ingreso
     */
    function initModalIngreso() {
        $('#modalIngreso').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const ingresoId = button ? $(button).data('id') : null;
            const $form = $('#formIngreso');
            const $selectCat = $('#in_id_categoria');

            if (!$form.length) {
                console.error('[ERROR] Formulario #formIngreso no encontrado');
                return;
            }

            // Limpiar solo campos editables, no el DOM ni los métodos de pago aún
            $form.find('input, select, textarea').not(':button, :submit, :reset, :hidden').val('');
            $('#ingreso_id').val('');
            $('#in_monto').prop('readonly', false).prop('disabled', false).css('background-color', '');
            $('#in_metodo_unico').val('');
            $('#in_monto_unico').val('');
            $('#seccion_pago_unico').show();
            $('#seccion_cobro_dividido').hide();
            $('#toggleCobroDividido').prop('checked', false);
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
                                // Esperamos un objeto con los datos del ingreso
                                $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');
                                if (!data || data.error) {
                                    console.error('[ERROR] getIngresoData:', data && data.error ? data.error : 'Respuesta inválida');
                                    $selectCat.append('<option value="">-- No hay categorías --</option>');
                                    $selectCat.prop('disabled', false);
                                    return;
                                }

                                // Si el servidor devuelve categoría seleccionada, la marcamos
                                if (data.id_categoria) {
                                    $selectCat.append(`<option value="${data.id_categoria}">${escapeHtml(data.cat_nombre || data.nombre || 'Seleccionado')}</option>`);
                                    $selectCat.val(String(data.id_categoria));
                                }
                                $selectCat.prop('disabled', false);
                            })
                            .fail(xhr => {
                                mostrarError('cargar datos ingreso', xhr);
                                $('#modalIngreso').modal('hide');
                            });
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

    /**
     * Maneja el toggle entre pago único y cobro dividido
     */
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
                
                if (monto > 0) {
                    $('#in_monto_unico').val(monto.toFixed(2));
                }
                
                $('#contenedor_pagos_parciales').empty();
                contadorPagos = 0;
            }
        });

        // Actualizar monto único cuando cambia el total
        $(document).on('input', '#in_monto', function() {
            const monto = parseFloat($(this).val()) || 0;
            
            if (!$('#toggleCobroDividido').is(':checked')) {
                $('#in_monto_unico').val(monto.toFixed(2));
            }
            
            if ($('#toggleCobroDividido').is(':checked')) {
                actualizarResumenPagos();
            }
        });

        // Agregar nueva fila de pago
        $(document).on('click', '#btnAgregarPago', function() {
            agregarFilaPago();
        });

        // Eliminar fila de pago
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

        // Actualizar resumen al cambiar montos
        $(document).on('input', '.pago-monto', function() {
            actualizarResumenPagos();
        });
    }
    /**
     * Maneja el envío del formulario de ingreso
     */
    function initSubmitIngreso() {
        $(document).on('submit', '#formIngreso', function(e) {
            e.preventDefault();
            
            const esDividido = $('#toggleCobroDividido').is(':checked');
            let formData = $(this).serializeArray();
            let dataObj = {};
            
            formData.forEach(item => {
                dataObj[item.name] = item.value;
            });
            
            if (!esDividido) {
                // Pago único
                const metodoUnico = $('#in_metodo_unico').val();
                if (!metodoUnico) {
                    showError('Selecciona un método de pago.');
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
                    showError('Cada pago parcial requiere un método y un monto válido.');
                    return;
                }
                
                const diferencia = Math.abs(montoTotal - sumaParciales);
                if (diferencia >= 0.01) {
                    showError(`La suma de los pagos parciales ($${sumaParciales.toFixed(2)}) no coincide con el monto total ($${montoTotal.toFixed(2)}). Diferencia: $${diferencia.toFixed(2)}`);
                    return;
                }
                
                dataObj.metodo_de_pago = 'Mixto';
                dataObj.pagos = JSON.stringify(pagos);
            }
            
            ajaxCall('ingreso', 'save', dataObj)
                .done(r => {
                    if (r.success) {
                        try { showSuccess('Ingreso guardado correctamente.'); } catch(e) {}
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('No se pudo guardar el ingreso. ' + (r.error || 'Revise los datos e intente nuevamente.'));
                    }
                })
                .fail(xhr => mostrarError('guardar ingreso', xhr));
        });
    }

    /**
     * Maneja la eliminación de ingresos
     */
    function initEliminarIngreso() {
        $(document).on('click', '.btn-del-ingreso', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este ingreso? Se eliminarán también todos los pagos parciales asociados.').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('ingreso', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            try { showSuccess('Ingreso eliminado correctamente.'); } catch(e) {}
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('No se pudo eliminar el ingreso. ' + (r.error || 'Intenta nuevamente.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar ingreso', xhr));
            });
        });
    }

    /**
     * Inicializa el buscador de ingresos
     */
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
            
            // Mostrar/ocultar botones de limpiar
            $clearBtn.toggle(searchTerm.length > 0);
            $clearDateBtn.toggle(!!(fechaInicio || fechaFin));
            
            let visibleCount = 0;
            let totalCount = 0;
            
            $tableBody.find('tr').each(function() {
                const $row = $(this);
                
                if ($row.find('td[colspan]').length > 0) return;
                
                totalCount++;
                
                const $cells = $row.find('td');
                const alumno = $cells.eq(0).text().toLowerCase();
                
                let folio = '';
                const $editBtn = $row.find('.btn-edit-ingreso');
                if ($editBtn.length) {
                    folio = $editBtn.data('id').toString().toLowerCase();
                }
                
                const fechaRegistro = $row.attr('data-fecha');
                const searchableText = folio + ' ' + alumno;
                
                let matchText = !searchTerm || searchableText.includes(searchTerm);
                let matchDate = true;
                
                if (fechaRegistro && (fechaInicio || fechaFin)) {
                    if (fechaInicio && fechaFin) {
                        matchDate = fechaRegistro >= fechaInicio && fechaRegistro <= fechaFin;
                    } else if (fechaInicio) {
                        matchDate = fechaRegistro >= fechaInicio;
                    } else if (fechaFin) {
                        matchDate = fechaRegistro <= fechaFin;
                    }
                }
                
                if (matchText && matchDate) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            // Actualizar contador
            if (searchTerm.length > 0 || fechaInicio || fechaFin) {
                if (visibleCount === 0) {
                    $resultCount.html('<ion-icon name="alert-circle-outline" style="vertical-align:middle;"></ion-icon> No se encontraron resultados')
                               .addClass('text-danger').removeClass('text-success');
                } else {
                    $resultCount.html(`<ion-icon name="checkmark-circle-outline" style="vertical-align:middle;"></ion-icon> Mostrando ${visibleCount} de ${totalCount} ingresos`)
                               .addClass('text-success').removeClass('text-danger');
                }
            } else {
                $resultCount.html('').removeClass('text-success text-danger');
            }
        }
        
        $searchInput.on('keyup', filtrarIngresos);
        $fechaInicio.on('change', filtrarIngresos);
        $fechaFin.on('change', filtrarIngresos);
        
        $clearBtn.on('click', function() {
            $searchInput.val('');
            filtrarIngresos();
            $searchInput.focus();
        });
        
        $clearDateBtn.on('click', function() {
            $fechaInicio.val('');
            $fechaFin.val('');
            filtrarIngresos();
        });
        
        $searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('');
                filtrarIngresos();
            }
        });
    }

    /**
     * Inicializa todos los componentes del módulo
     */
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
    const { ajaxCall, mostrarError, ensureNumberEditable } = ERPUtils;

    /**
     * Maneja la apertura del modal de egreso
     */
    function initModalEgreso() {
        $('#modalEgreso').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const egresoId = button ? $(button).data('id') : null;
            const $form = $('#formEgreso');
            const $selectCat = $('#eg_id_categoria');
            const $selectPres = $('#eg_id_presupuesto');

            if (!$form.length) {
                console.error('[ERROR] Formulario #formEgreso no encontrado');
                return;
            }
            
            $form[0].reset();
            $('#egreso_id').val('');
            $selectCat.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
            $selectPres.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

            ensureNumberEditable('#eg_monto');

            // Cargar sub-presupuestos
            ajaxCall('presupuesto', 'getSubPresupuestos', {}, 'GET')
                .then(presupuestos => {
                    console.log('[DEBUG] Sub-presupuestos recibidos:', presupuestos);
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

                    // Auto-sync categoría al seleccionar presupuesto
                    $selectPres.off('change.presupuestoSync').on('change.presupuestoSync', function() {
                        const catId = $(this).find(':selected').data('categoria');
                        if (catId) {
                            $selectCat.val(catId.toString());
                        }
                    });

                    return ajaxCall('egreso', 'getCategoriasEgreso', {}, 'GET');
                })
                .then(categorias => {
                    console.log('[DEBUG] Categorías egreso recibidas:', categorias);
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

                    // Auto-sync presupuesto al cambiar categoría
                    $selectCat.off('change.egresoPres').on('change.egresoPres', function() {
                        const presId = $(this).find(':selected').data('presupuesto');
                        if (presId) {
                            $selectPres.val(presId.toString());
                        }
                    });

                    // Si es edición, cargar datos
                    if (egresoId) {
                        $('#modalEgresoTitle').text('Editar Egreso');
                        ajaxCall('egreso', 'getEgresoData', { id: egresoId }, 'GET')
                            .done(data => {
                                if (data && !data.error && data.folio_egreso !== undefined) {
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
                                    $('#eg_activo_fijo').val(data.activo_fijo);
                                    $('#eg_descripcion').val(data.descripcion);
                                } else {
                                    $('#modalEgreso').modal('hide');
                                    showError('Error al cargar datos del egreso. ' + (data.error || 'Verifique la consola.'));
                                }
                            })
                            .fail(xhr => {
                                mostrarError('cargar datos egreso', xhr);
                                $('#modalEgreso').modal('hide');
                            });
                    } else {
                        $('#modalEgresoTitle').text('Registrar Nuevo Egreso');
                        $('#eg_activo_fijo').val('NO');
                    }
                })
                .fail(xhr => mostrarError('cargar datos egreso', xhr));
        });
    }

    /**
     * Maneja el envío del formulario de egreso
     */
    function initSubmitEgreso() {
        $(document).on('submit', '#formEgreso', function(e) {
            e.preventDefault();
            
            ajaxCall('egreso', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $(document).trigger('egresoGuardado');
                        try { showSuccess('Egreso guardado correctamente.'); } catch(e) {}
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('No se pudo guardar el egreso. ' + (r.error || 'Revise los datos e intente nuevamente.'));
                    }
                })
                .fail(xhr => mostrarError('guardar egreso', xhr));
        });
    }

    /**
     * Maneja la eliminación de egresos
     */
    function initEliminarEgreso() {
        $(document).on('click', '.btn-del-egreso', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este egreso?').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('egreso', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            $(document).trigger('egresoEliminado');
                            try { showSuccess('Egreso eliminado correctamente.'); } catch(e) {}
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('No se pudo eliminar el egreso. ' + (r.error || 'Intenta nuevamente.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar egreso', xhr));
            });
        });
    }

    /**
     * Inicializa el buscador de egresos
     */
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
                
                const $cells = $row.find('td');
                const destinatario = $cells.eq(2).text().toLowerCase();
                
                let folio = '';
                const $editBtn = $row.find('.btn-edit-egreso');
                if ($editBtn.length) {
                    folio = $editBtn.data('id').toString().toLowerCase();
                }
                
                const fechaRegistro = $row.attr('data-fecha');
                const searchableText = folio + ' ' + destinatario;
                
                let matchText = !searchTerm || searchableText.includes(searchTerm);
                let matchDate = true;
                
                if (fechaRegistro && (fechaInicio || fechaFin)) {
                    if (fechaInicio && fechaFin) {
                        matchDate = fechaRegistro >= fechaInicio && fechaRegistro <= fechaFin;
                    } else if (fechaInicio) {
                        matchDate = fechaRegistro >= fechaInicio;
                    } else if (fechaFin) {
                        matchDate = fechaRegistro <= fechaFin;
                    }
                }
                
                if (matchText && matchDate) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            if (searchTerm.length > 0 || fechaInicio || fechaFin) {
                if (visibleCount === 0) {
                    $resultCount.html('<ion-icon name="alert-circle-outline" style="vertical-align:middle;"></ion-icon> No se encontraron resultados')
                               .addClass('text-danger').removeClass('text-success');
                } else {
                    $resultCount.html(`<ion-icon name="checkmark-circle-outline" style="vertical-align:middle;"></ion-icon> Mostrando ${visibleCount} de ${totalCount} egresos`)
                               .addClass('text-success').removeClass('text-danger');
                }
            } else {
                $resultCount.html('').removeClass('text-success text-danger');
            }
        }
        
        $searchInput.on('keyup', filtrarEgresos);
        $fechaInicio.on('change', filtrarEgresos);
        $fechaFin.on('change', filtrarEgresos);
        
        $clearBtn.on('click', function() {
            $searchInput.val('');
            filtrarEgresos();
            $searchInput.focus();
        });
        
        $clearDateBtn.on('click', function() {
            $fechaInicio.val('');
            $fechaFin.val('');
            filtrarEgresos();
        });
        
        $searchInput.on('keydown', function(e) {
            if (e.key === 'Escape') {
                $(this).val('');
                filtrarEgresos();
            }
        });
    }

    /**
     * Inicializa todos los componentes del módulo
     */
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
const CategoriasModule = (function () {
    const { ajaxCall, mostrarError } = ERPUtils;

    /**
     * Maneja la apertura del modal de categoría
     */
    function initModalCategoria() {
        $('#modalCategoria').on('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const catId = button ? $(button).data('id') : null;
            const $form = $('#formCategoria');

            if (!$form.length) {
                console.error('[ERROR] Formulario #formCategoria no encontrado');
                return;
            }

            $form[0].reset();
            $('#categoria_id').val('');
            // Ocultar campo concepto por defecto
            $('#div_cat_concepto').hide();
            $('#cat_concepto').val('');
            $('#alert_categoria_protegida').hide();

            const $submitBtn = $form.find('button[type="submit"], .btn-submit-categoria');

            if (catId) {
                $('#modalCategoriaTitle').text('Editar Categoría');
                if ($submitBtn.length) {
                    $submitBtn.text('Actualizar Categoría');
                }
                ajaxCall('categoria', 'getCategoriaData', { id: catId }, 'GET')
                    .done(data => {
                        if (data && !data.error) {
                            $('#categoria_id').val(data.id_categoria);
                            $('#cat_nombre').val(data.nombre);
                            $('#cat_tipo').val(data.tipo);
                            $('#cat_descripcion').val(data.descripcion);
                            $('#categoria_no_borrable').val(data.no_borrable || 0);

                            // Mostrar campo concepto si es ingreso y setear valor
                            if (data.tipo === 'Ingreso') {
                                $('#div_cat_concepto').show();
                                $('#cat_concepto').val(data.concepto || '');
                            } else {
                                $('#div_cat_concepto').hide();
                                $('#cat_concepto').val('');
                            }

                            // Mostrar alerta si es protegida
                            if (data.no_borrable == 1) {
                                $('#alert_categoria_protegida').show();
                            } else {
                                $('#alert_categoria_protegida').hide();
                            }
                        } else {
                            $('#modalCategoria').modal('hide');
                            showError('Error al cargar la categoría. ' + (data.error || 'Verifique la consola.'));
                        }
                    })
                    .fail(xhr => {
                        mostrarError('cargar datos categoría', xhr);
                        $('#modalCategoria').modal('hide');
                    });
            } else {
                $('#modalCategoriaTitle').text('Agregar Nueva Categoría');
                if ($submitBtn.length) {
                    $submitBtn.text('Guardar Categoría');
                }
                // Por defecto, si tipo es Ingreso, mostrar campo concepto
                if ($('#cat_tipo').val() === 'Ingreso') {
                    $('#div_cat_concepto').show();
                } else {
                    $('#div_cat_concepto').hide();
                }
            }

            // Evento al cambiar tipo
            $('#cat_tipo').off('change.categoria').on('change.categoria', function () {
                if ($(this).val() === 'Ingreso') {
                    $('#div_cat_concepto').show();
                } else {
                    $('#div_cat_concepto').hide();
                    $('#cat_concepto').val('');
                }
            });
        });
    }

    /**
     * Maneja el envío del formulario de categoría
     */
    function initSubmitCategoria() {
        // Evitar cualquier envío nativo del formulario
        $(document).off('submit', '#formCategoria');

        $(document).on('submit', '#formCategoria', function (e) {
            e.preventDefault();

            const $form = $(this);

            // Validación: si tipo es Ingreso, concepto es obligatorio
            const tipo = $('#cat_tipo').val();
            const concepto = $('#cat_concepto').val();
            if (tipo === 'Ingreso' && !concepto) {
                showError('Selecciona un concepto para las categorías de tipo Ingreso.');
                $('#cat_concepto').focus();
                return false;
            }

            // Llamada AJAX directa al endpoint save
            $.ajax({
                url: BASE_URL + 'index.php?controller=categoria&action=save',
                method: 'POST',
                dataType: 'json',
                data: $form.serialize()
            })
            .done(function (r) {
                if (r && r.success) {
                    try { showSuccess('Categoría guardada correctamente.'); } catch (e) {}
                    setTimeout(function () { window.location.reload(); }, 900);
                } else {
                    showError('No fue posible guardar la categoría. ' + (r && r.error ? r.error : 'Intenta de nuevo.'));
                }
            })
            .fail(function (xhr) {
                mostrarError('guardar categoría', xhr);
            });

            return false;
        });
    }

    /**
     * Maneja la eliminación de categorías
     */
    function initEliminarCategoria() {
        // Evitar handlers duplicados
        $(document).off('click', '.btn-del-categoria');

        $(document).on('click', '.btn-del-categoria', function () {
            const id = $(this).data('id');
            const $row = $(this).closest('tr');
            if (!id) {
                showError('ID de categoría inválido.');
                return;
            }

            showConfirm('¿Eliminar esta categoría?').then(confirmed => {
                if (!confirmed) return;

                // Llamada AJAX directa al endpoint delete
                $.ajax({
                    url: BASE_URL + 'index.php?controller=categoria&action=delete',
                    method: 'POST',
                    dataType: 'json',
                    data: { id: id }
                })
                .done(function (r) {
                    if (r && r.success) {
                        try { showSuccess('Categoría eliminada correctamente.'); } catch (e) {}
                        if ($row && $row.length) {
                            $row.fadeOut(300, function () { $(this).remove(); });
                        }
                    } else {
                        showError('No se pudo eliminar la categoría. ' + (r && r.error ? r.error : 'Intenta de nuevo.'));
                    }
                })
                .fail(function (xhr) {
                    mostrarError('eliminar categoría', xhr);
                });
            });
        });
    }

    /**
     * Botón refrescar categorías
     */
    function initRefrescarCategorias() {
        $(document).on('click', '#btnRefrescarCategorias', () => window.location.reload());
    }

    /**
     * Inicializa todos los componentes del módulo
     */
    function init() {
        initModalCategoria();
        initSubmitCategoria();
        initEliminarCategoria();
        initRefrescarCategorias();
        console.log('[✓] Módulo Categorías inicializado');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Gestión de Presupuestos (General + Sub-Presupuestos)
// ============================================================================
const PresupuestosModule = (function() {
    const { ajaxCall, mostrarError, ensureNumberEditable, escapeHtml } = ERPUtils;

    // --- FUNCIONES NUEVAS INTEGRADAS ---

    /**
     * Maneja la apertura del modal exclusivo de subpresupuesto
     */
    function initModalSubPresupuestoExclusivo() {
        $('#modalSubPresupuesto').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            // presId: when editing an existing sub-presupuesto this is the sub id
            const presId = button ? $(button).data('id') : null;
            // presParentId: when opening from a parent card's "Agregar Sub-presupuesto" button
            // the template sets data-parent-id="..." on that button — check multiple data keys for safety
            const presParentId = button ? ($(button).data('parentId') || $(button).data('parent-id') || $(button).data('parent') || null) : null;
            const $form = $('#formSubPresupuesto');
            const $selectCat = $('#subpres_categoria');
            const $selectPadre = $('#subpres_parent');
            const $alert = $('#subpresupuestoAlert');
            const $msgNoCat = $('#msgNoCategoriasEgreso');

            if (!$form.length) {
                console.error('[ERROR] Formulario #formSubPresupuesto no encontrado');
                return;
            }

            $form[0].reset();
            $alert.addClass('d-none').text('');
            $('#subpresupuesto_id').val('');
            ensureNumberEditable('#subpres_monto'); // Corregido para usar la referencia local
            $msgNoCat.addClass('d-none');

            // Cargar presupuestos generales como padres
            $selectPadre.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

            ajaxCall('presupuesto', 'getPresupuestosGenerales', {}, 'GET')
                .then(presupuestos => {
                    $selectPadre.empty().append('<option value="">Seleccione un presupuesto general...</option>');
                    if (presupuestos && Array.isArray(presupuestos) && presupuestos.length > 0) {
                        presupuestos.forEach(p => {
                            const id = p.id_presupuesto || '';
                            const nombre = p.nombre || 'Sin nombre';
                            const fecha = p.fecha || '';
                            const label = `${nombre} — ${fecha}`;
                            $selectPadre.append(`<option value="${id}">${escapeHtml(label)}</option>`);
                        });
                    } else {
                        $selectPadre.append('<option value="">-- No hay presupuestos generales --</option>');
                    }
                    $selectPadre.prop('disabled', false);
                    // Cargar solo categorías de egreso
                    return ajaxCall('categoria', 'getCategoriasEgreso', {}, 'GET');
                })
                .then(categorias => {
                    $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');

                    let countEgreso = 0;
                    if (categorias && Array.isArray(categorias) && categorias.length > 0) {
                        categorias.forEach(cat => {
                            const catId = (cat && (cat.id_categoria !== undefined && cat.id_categoria !== null)) ? cat.id_categoria : (cat && (cat.id !== undefined && cat.id !== null) ? cat.id : null);
                            const nombre = (cat && (cat.nombre || cat.cat_nombre)) ? (cat.nombre || cat.cat_nombre) : (typeof cat === 'string' ? cat : 'Sin nombre');
                            if (catId !== null && catId !== '') {
                                $selectCat.append(`<option value="${escapeHtml(String(catId))}">${escapeHtml(nombre)}</option>`);
                                countEgreso++;
                            }
                        });
                    }

                    if (countEgreso === 0) {
                        $msgNoCat.removeClass('d-none');
                    }
                    $selectCat.prop('disabled', false);

                    if (presId) {
                        $('#modalSubPresupuestoTitle').text('Editar Sub-Presupuesto');
                        ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET')
                            .done(data => {
                                if (data && !data.error) {
                                    $('#subpresupuesto_id').val(data.id_presupuesto || data.id);
                                    $('#subpres_nombre').val(data.nombre);
                                    $('#subpres_monto').val(data.monto_limite || data.monto);
                                    if (data.id_categoria) {
                                        $selectCat.val(data.id_categoria);
                                    }
                                    if (data.parent_presupuesto) {
                                        $selectPadre.val(data.parent_presupuesto);
                                    }
                                    $('#subpres_fecha').val(data.fecha);
                                } else {
                                    $('#modalSubPresupuesto').modal('hide');
                                    showError('Error al cargar sub-presupuesto. ' + (data.error || 'Verifique la consola.'));
                                }
                            })
                            .fail(xhr => {
                                mostrarError('cargar sub-presupuesto', xhr);
                                $('#modalSubPresupuesto').modal('hide');
                            });
                    } else {
                        $('#modalSubPresupuestoTitle').text('Agregar Sub-Presupuesto');
                    }
                })
                .fail(xhr => mostrarError('cargar datos subpresupuesto', xhr));
        });
    }

    /**
     * Maneja el envío del formulario exclusivo de subpresupuesto
     */
    function initSubmitSubPresupuestoExclusivo() {
        $(document).on('submit', '#formSubPresupuesto', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $alert = $('#subpresupuestoAlert');
            $alert.addClass('d-none').text('');

            // Validación visual de campos requeridos
            const parent = $('#subpres_parent').val();
            const cat = $('#subpres_categoria').val();
            const monto = $('#subpres_monto').val();
            const fecha = $('#subpres_fecha').val();
            if (!parent || !cat || !monto || !fecha) {
                $alert.removeClass('d-none').text('Todos los campos marcados con * son obligatorios.');
                return;
            }

            // Deshabilitar botón para evitar doble envío
            const $btn = $('#btnGuardarSubPresupuesto');
            $btn.prop('disabled', true);

            ajaxCall('presupuesto', 'save', $form.serialize())
                .done(r => {
                    if (r.success) {
                        try { showSuccess('Sub-presupuesto guardado correctamente.'); } catch(e) {}
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        $alert.removeClass('d-none').text(r.error || 'Error al guardar.');
                    }
                })
                .fail(xhr => {
                    mostrarError('guardar sub-presupuesto', xhr);
                    $alert.removeClass('d-none').text('Error inesperado al guardar.');
                })
                .always(() => {
                    $btn.prop('disabled', false);
                });
        });
    }

    // --- FIN FUNCIONES NUEVAS ---

    /**
     * Popula el selector de categorías en el modal de presupuesto
     * @param {number} presId - ID del presupuesto (para edición)
     */
    function populatePresupuestoCategoria(presId = null) {
        const $selectCat = $('#pres_categoria');
        if (!$selectCat.length) return Promise.reject('Selector no encontrado');

        $selectCat.empty().append('<option value="">Cargando categorías...</option>').prop('disabled', true);

        return ajaxCall('presupuesto', 'getCategoriasPresupuesto', {}, 'GET')
            .then(categorias => {
                // console.log('[DEBUG] Categorías presupuesto recibidas:', categorias);
                $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');
                
                if (categorias && Array.isArray(categorias) && categorias.length > 0) {
                    categorias.forEach(cat => {
                        const catId = cat.id_categoria || cat.id || '';
                        const presup = cat.id_presupuesto || '';
                        const nombre = cat.nombre || cat.cat_nombre || 'Sin nombre';
                        $selectCat.append(`<option value="${catId}" data-presupuesto="${presup}">${escapeHtml(nombre)}</option>`);
                    });
                } else {
                    $selectCat.append('<option value="">-- No hay categorías disponibles --</option>');
                }
                
                $selectCat.prop('disabled', false);
                return categorias;
            })
            .catch(xhr => {
                console.error('[ERROR] Cargar categorías presupuesto:', xhr);
                $selectCat.empty().append('<option value="">Error al cargar</option>').prop('disabled', false);
                throw xhr;
            });
    }

    /**
     * Maneja la apertura del modal de presupuesto general
     */
    function initModalPresupuestoGeneral() {
        $('#modalPresupuestoGeneral').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const presId = button ? $(button).data('id') : null;
            const $form = $('#formPresupuestoGeneral');
            
            if (!$form.length) {
                console.error('[ERROR] Formulario #formPresupuestoGeneral no encontrado');
                return;
            }
            
            $form[0].reset();
            $('#presgen_id').val('');
            
            if (presId) {
                $('#modalPresupuestoGeneralTitle').text('Editar Presupuesto General');
                ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET')
                    .done(data => {
                        if (data && !data.error) {
                            $('#presgen_id').val(data.id_presupuesto ?? data.id ?? '');
                            // El backend devuelve `monto_limite` (nombre de columna); usarlo si existe, si no usar `monto` como fallback
                            const montoVal = (typeof data.monto_limite !== 'undefined') ? data.monto_limite : (data.monto || '');
                            $('#presgen_monto').val(montoVal);
                            $('#presgen_fecha').val(data.fecha ?? '');
                            $('#presgen_descripcion').val(data.descripcion ?? '');
                        } else {
                            $('#modalPresupuestoGeneral').modal('hide');
                            showError('Error al cargar el presupuesto. ' + (data.error || 'Verifique la consola.'));
                        }
                    })
                    .fail(xhr => {
                        mostrarError('cargar presupuesto general', xhr);
                        $('#modalPresupuestoGeneral').modal('hide');
                    });
            } else {
                $('#modalPresupuestoGeneralTitle').text('Agregar Presupuesto General');
            }
        });
    }

    /**
     * Maneja el envío del formulario de presupuesto general
     */
    function initSubmitPresupuestoGeneral() {
        $(document).on('submit', '#formPresupuestoGeneral', function(e) {
            e.preventDefault();
            ajaxCall('presupuesto', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        try { showSuccess('Presupuesto guardado correctamente.'); } catch(e) {}
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('No fue posible guardar el presupuesto. ' + (r.error || 'Intenta de nuevo.'));
                    }
                })
                .fail(xhr => mostrarError('guardar presupuesto general', xhr));
        });}

    /**
     * Maneja la apertura del modal de sub-presupuesto
     */
    function initModalSubPresupuesto() {
        $('#modalPresupuesto').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const presId = button ? $(button).data('id') : null;
            const $form = $('#formPresupuesto');
            const $selectCat = $('#pres_categoria');
            const $selectPadre = $('#pres_parent');
            const $alert = $('#presupuestoAlert');

            if (!$form.length) {
                console.error('[ERROR] Formulario #formPresupuesto no encontrado');
                return;
            }

            $form[0].reset();
            $alert.addClass('d-none').text('');
            $('#presupuesto_id').val('');
            ensureNumberEditable('#pres_monto');

            // Cargar presupuestos generales como padres
            $selectPadre.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

            ajaxCall('presupuesto', 'getPresupuestosGenerales', {}, 'GET')
                .then(presupuestos => {
                    $selectPadre.empty().append('<option value="">Seleccione un presupuesto general...</option>');
                    if (presupuestos && Array.isArray(presupuestos) && presupuestos.length > 0) {
                        presupuestos.forEach(p => {
                            const id = p.id_presupuesto || '';
                            const nombre = p.nombre || 'Sin nombre';
                            const fecha = p.fecha || '';
                            const label = `${nombre} — ${fecha}`;
                            $selectPadre.append(`<option value="${id}">${escapeHtml(label)}</option>`);
                        });
                    } else {
                        $selectPadre.append('<option value="">-- No hay presupuestos generales --</option>');
                    }
                    $selectPadre.prop('disabled', false);

                    // Si la apertura proviene de un botón que indica explícitamente el padre (data-parent-id),
                    // seleccionar ese presupuesto padre automáticamente.
                    if (presParentId) {
                        $selectPadre.val(presParentId);
                    } else if (!presId) {
                        // Si estamos CREANDO un nuevo sub-presupuesto y NO se indicó padre explícito,
                        // seleccionar automáticamente el Presupuesto General de Enero 2027 si existe.
                        try {
                            const targetPrefix = '2027-01';
                            const match = (presupuestos || []).find(p => (p.fecha || '').startsWith(targetPrefix));
                            if (match) {
                                const matchId = match.id_presupuesto || match.id || '';
                                if (matchId) {
                                    $selectPadre.val(matchId);
                                }
                            }
                        } catch (e) {
                            console.warn('Auto-select Enero 2027 falló:', e);
                        }
                    }

                    return populatePresupuestoCategoria(presId);
                })
                .done(() => {
                    $selectPadre.off('change.subpresSync').on('change.subpresSync', function() {
                        // Si se quiere, aquí se puede auto-filtrar categorías según el padre
                    });
                    if (presId) {
                        $('#modalPresupuestoTitle').text('Editar Sub-Presupuesto');
                        ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET')
                            .done(data => {
                                if (data && !data.error) {
                                    $('#presupuesto_id').val(data.id_presupuesto || data.id);
                                    $('#pres_nombre').val(data.nombre);
                                    $('#pres_monto').val(data.monto_limite || data.monto);
                                    if (data.id_categoria) {
                                        $selectCat.val(data.id_categoria);
                                    }
                                    if (data.parent_presupuesto) {
                                        $selectPadre.val(data.parent_presupuesto);
                                    }
                                    $('#pres_fecha').val(data.fecha);
                                    if (data.descripcion) $('#pres_descripcion').val(data.descripcion);
                                } else {
                                    $('#modalPresupuesto').modal('hide');
                                    showError('Error al cargar sub-presupuesto. ' + (data.error || 'Verifique la consola.'));
                                }
                            })
                            .fail(xhr => {
                                mostrarError('cargar sub-presupuesto', xhr);
                                $('#modalPresupuesto').modal('hide');
                            });
                    } else {
                        $('#modalPresupuestoTitle').text('Agregar Nuevo Sub-Presupuesto');
                    }
                })
                .fail(xhr => mostrarError('cargar datos presupuesto', xhr));
        });
    }

    /**
     * Maneja el envío del formulario de sub-presupuesto
     */
    function initSubmitSubPresupuesto() {
        $(document).on('submit', '#formPresupuesto', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $alert = $('#presupuestoAlert');
            $alert.addClass('d-none').text('');

            // Validación visual de campos requeridos
            const parent = $('#pres_parent').val();
            const cat = $('#pres_categoria').val();
            const monto = $('#pres_monto').val();
            const fecha = $('#pres_fecha').val();
            if (!parent || !cat || !monto || !fecha) {
                $alert.removeClass('d-none').text('Todos los campos marcados con * son obligatorios.');
                return;
            }

            // Deshabilitar botón para evitar doble envío
            const $btn = $('#btnGuardarPresupuesto');
            $btn.prop('disabled', true);

            ajaxCall('presupuesto', 'save', $form.serialize())
                .done(r => {
                    if (r.success) {
                        try { showSuccess('Presupuesto guardado correctamente.'); } catch(e) {}
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        $alert.removeClass('d-none').text(r.error || 'Error al guardar.');
                    }
                })
                .fail(xhr => {
                    mostrarError('guardar sub-presupuesto', xhr);
                    $alert.removeClass('d-none').text('Error inesperado al guardar.');
                })
                .always(() => {
                    $btn.prop('disabled', false);
                });
        });
    }

    /**
     * Maneja la apertura del modal de presupuesto por categoría (modal separado)
     */
    function initModalPresupuestoCategoria() {
        $('#modalPresupuestoCategoria').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const presId = button ? $(button).data('id') : null;
            const $form = $('#formPresupuestoCategoria');
            const $selectCat = $('#pres_categoria_categoria');
            const $selectPadre = $('#pres_parent_categoria');
            
            if (!$form.length) {
                console.error('[ERROR] Formulario #formPresupuestoCategoria no encontrado');
                return;
            }
            
            $form[0].reset();
            $('#presupuesto_categoria_id').val('');
            
            // Cargar presupuestos generales como padres
            $selectPadre.empty().append('<option value="">Cargando...</option>').prop('disabled', true);
            
            ajaxCall('presupuesto', 'getPresupuestosGenerales', {}, 'GET')
                .then(presupuestos => {
                    $selectPadre.empty().append('<option value="">Seleccione un presupuesto general...</option>');
                    
                    if (presupuestos && Array.isArray(presupuestos) && presupuestos.length > 0) {
                        presupuestos.forEach(p => {
                            const id = p.id_presupuesto || '';
                            const nombre = p.nombre || 'Sin nombre';
                            const fecha = p.fecha || '';
                            const label = `${nombre} — ${fecha}`;
                            $selectPadre.append(`<option value="${id}">${escapeHtml(label)}</option>`);
                        });
                    } else {
                        $selectPadre.append('<option value="">-- No hay presupuestos generales --</option>');
                    }
                    $selectPadre.prop('disabled', false);
                    
                    // Cargar categorías
                    return ajaxCall('presupuesto', 'getCategoriasPresupuesto', {}, 'GET');
                })
                .then(categorias => {
                    $selectCat.empty().append('<option value="">Seleccione una categoría...</option>');
                    
                    if (categorias && Array.isArray(categorias) && categorias.length > 0) {
                        categorias.forEach(cat => {
                            const catId = cat.id_categoria || cat.id || '';
                            const nombre = cat.nombre || cat.cat_nombre || 'Sin nombre';
                            $selectCat.append(`<option value="${catId}">${escapeHtml(nombre)}</option>`);
                        });
                    } else {
                        $selectCat.append('<option value="">-- No hay categorías disponibles --</option>');
                    }

                    $selectCat.prop('disabled', false);
                    
                    // Si es edición, cargar datos
                    if (presId) {
                        $('#modalPresupuestoCategoriaTitle').text('Editar Presupuesto por Categoría');
                        ajaxCall('presupuesto', 'getPresupuestoData', { id: presId }, 'GET')
                            .done(data => {
                                if (data && !data.error) {
                                    $('#presupuesto_categoria_id').val(data.id_presupuesto || data.id);
                                    $('#pres_nombre_categoria').val(data.nombre);
                                    $('#pres_monto_categoria').val(data.monto_limite || data.monto);
                                    if (data.id_categoria) {
                                        $selectCat.val(data.id_categoria);
                                    }
                                    if (data.parent_presupuesto) {
                                        $selectPadre.val(data.parent_presupuesto);
                                    }
                                    $('#pres_fecha_categoria').val(data.fecha);
                                } else {
                                    $('#modalPresupuestoCategoria').modal('hide');
                                    showError('Error al cargar presupuesto por categoría. ' + (data.error || 'Verifique la consola.'));
                                }
                            })
                            .fail(xhr => {
                                mostrarError('cargar presupuesto por categoría', xhr);
                                $('#modalPresupuestoCategoria').modal('hide');
                            });
                    } else {
                        $('#modalPresupuestoCategoriaTitle').text('Asignar Presupuesto por Categoría');
                    }
                })
                .fail(xhr => mostrarError('cargar datos presupuesto por categoría', xhr));
        });
    }

    /**
     * Maneja el envío del formulario de presupuesto por categoría
     */
    function initSubmitPresupuestoCategoria() {
        $(document).on('click', '#btnGuardarPresCategoria', function(e) {
            e.preventDefault();
            
            const formData = $('#formPresupuestoCategoria').serialize();
            
            ajaxCall('presupuesto', 'save', formData)
                .done(r => {
                    if (r.success) {
                        $('#modalPresupuestoCategoria').modal('hide');
                        try { showSuccess('Presupuesto asignado correctamente.'); } catch(e) {}
                        setTimeout(() => { window.location.reload(); }, 900);
                    } else {
                        showError('No fue posible guardar el presupuesto. ' + (r.error || 'Intenta de nuevo.'));
                    }
                })
                .fail(xhr => mostrarError('guardar presupuesto por categoría', xhr));
        });
    }

    /**
     * Maneja la eliminación de presupuestos (general o sub)
     */
    function initEliminarPresupuesto() {
        // Eliminar presupuesto general
        $(document).on('click', '.btn-del-presgen', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este presupuesto general? Se eliminarán todos los sub-presupuestos asociados.').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('presupuesto', 'deletePresupuestoGeneral', { id: id })
                    .done(r => {
                        if (r.success) {
                            try { showSuccess('Presupuesto general eliminado correctamente.'); } catch(e) {}
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('No se pudo eliminar el presupuesto general. ' + (r.error || 'Intenta nuevamente.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar presupuesto general', xhr));
            });
        });
        
        // Eliminar sub-presupuesto
        $(document).on('click', '.btn-del-presupuesto', function() {
            const id = $(this).data('id');
            showConfirm('¿Eliminar este sub-presupuesto?').then(confirmed => {
                if (!confirmed) return;
                ajaxCall('presupuesto', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            $(document).trigger('egresoEliminado');
                            try { showSuccess('Sub-presupuesto eliminado correctamente.'); } catch(e) {}
                            setTimeout(() => { window.location.reload(); }, 900);
                        } else {
                            showError('No se pudo eliminar el sub-presupuesto. ' + (r.error || 'Intenta nuevamente.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar sub-presupuesto', xhr));
            });
        });
    }

    /**
     * Botón refrescar presupuestos
     */
    function initRefrescarPresupuestos() {
        $(document).on('click', '#btnRefrescarPresupuestos', () => window.location.reload());
    }

    /**
     * Inicializa todos los componentes del módulo
     */

    function init() {
        initModalPresupuestoGeneral();
        initSubmitPresupuestoGeneral();
        initModalSubPresupuesto();
        initSubmitSubPresupuesto();
        initModalPresupuestoCategoria();
        initSubmitPresupuestoCategoria();
        initEliminarPresupuesto();
        // AHORA SÍ: Inicializar el flujo exclusivo de subpresupuestos porque las funciones YA EXISTEN dentro del módulo
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

    /**
     * Actualiza el badge de alertas en el sidebar
     */
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

    /**
     * Inicializa el sistema de alertas
     */
    function init() {
        actualizarBadgeAlertas();
        setInterval(actualizarBadgeAlertas, 30000);
        
        $(document).on('egresoGuardado egresoEliminado', actualizarBadgeAlertas);
        
        console.log('[✓] Sistema de Alertas de Presupuestos inicializado');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Dashboard (Gráficas y Estadísticas)
// ============================================================================
const DashboardModule = (function() {
    const { ajaxCall } = ERPUtils;

    /**
     * Inicializa el dashboard si estamos en esa página
     */
    function init() {
        // Solo inicializar si estamos en la página del dashboard
        if (typeof cargarResumenMensual === 'undefined' && $('#chartIngresosEgresos').length > 0) {
            console.log('[INFO] Dashboard detectado, pero el código ya está en la vista');
        }
        console.log('[✓] Módulo Dashboard inicializado');
    }

    return { init };
})();
// ============================================================================
// INICIALIZACIÓN GLOBAL DE MÓDULOS
// ============================================================================
$(function () {
    try { UsuariosModule.init(); } catch (e) { console.warn('UsuariosModule.init error', e); }
    try { IngresosModule.init(); } catch (e) { console.warn('IngresosModule.init error', e); }
    try { EgresosModule.init(); } catch (e) { console.warn('EgresosModule.init error', e); }
    try { CategoriasModule.init(); } catch (e) { console.warn('CategoriasModule.init error', e); }
    try { PresupuestosModule.init(); } catch (e) { console.warn('PresupuestosModule.init error', e); }
    try { AlertasPresupuestosModule.init(); } catch (e) { console.warn('AlertasPresupuestosModule.init error', e); }
    try { DashboardModule.init(); } catch (e) { console.warn('DashboardModule.init error', e); }
});

// (Nota: definición duplicada/corrupta de AuditoriaModule eliminada.
// La definición correcta del módulo de Auditoría ya existe más arriba
// en este archivo con `initModalDetalleAuditoria` e `init()`.)

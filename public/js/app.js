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

'use strict';

// ============================================================================
// MÓDULO: Utilidades Globales
// ============================================================================
const ERPUtils = (function() {
    /**
     * Realiza llamadas AJAX genéricas al backend
     * @param {string} controller - Nombre del controlador PHP
     * @param {string} action - Acción/método a ejecutar
     * @param {object} data - Datos a enviar
     * @param {string} method - Método HTTP (GET/POST)
     * @returns {jqXHR} Promesa jQuery AJAX
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
     * @param {string} action - Descripción de la acción
     * @param {object} jqXHR - Objeto de error jQuery
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
        alert(`${errorMsg}\nDetalle: ${serverError}\n\nRevise la consola (F12) para más información.`);
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

    // Exponer métodos públicos
    return {
        ajaxCall,
        mostrarError,
        ensureNumberEditable,
        escapeHtml
    };
})();

// Exponer ajaxCall globalmente para compatibilidad con código en vistas
window.ajaxCall = ERPUtils.ajaxCall;
window.mostrarError = ERPUtils.mostrarError;

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
            
            if (userId && userId != CURRENT_USER.id) {
                // Editar otro usuario
                $('#modalEditarMiPerfilTitle').text('Editar Usuario');
                $('#perfil_id').val(userId);
                $('#perfil_nombre').val($(button).data('nombre'));
                $('#perfil_username').val($(button).data('username'));
                $('#perfil_rol').val($(button).data('rol'));
            } else {
                // Editar mi perfil
                $('#modalEditarMiPerfilTitle').text('Editar Mi Perfil');
                $('#perfil_id').val(CURRENT_USER.id);
                $('#perfil_nombre').val(CURRENT_USER.nombre);
                $('#perfil_username').val(CURRENT_USER.username);
                $('#perfil_rol').val(CURRENT_USER.rol);
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
                        window.location.reload();
                    } else {
                        alert('Error al guardar: ' + (r.error || 'Verifique datos.'));
                    }
                })
                .fail(xhr => mostrarError('guardar perfil', xhr));
        });
    }

    /**
     * Inicializa el sistema de cambio de contraseña
     */
    function initCambiarPassword() {
        // Abrir modal de cambiar contraseña
        $(document).on('click', '#btnAbrirCambiarPassword', function(e) {
            e.preventDefault();
            
            const modalEditarPerfil = bootstrap.Modal.getInstance(document.getElementById('modalEditarMiPerfil'));
            if (modalEditarPerfil) {
                modalEditarPerfil.hide();
            } else {
                $('#modalEditarMiPerfil').modal('hide');
            }
            
            setTimeout(function() {
                $('#changepass_username').val(CURRENT_USER.user_username || CURRENT_USER.username);
                $('#modalCambiarPasswordNuevo').modal('show');
            }, 500);
        });

        // Modal cambiar contraseña abierto
        $('#modalCambiarPasswordNuevo').on('show.bs.modal', function() {
            const $form = $('#formCambiarPasswordNuevo');
            if (!$form.length) return;
            
            $form[0].reset();
            if (!$('#changepass_username').val()) {
                $('#changepass_username').val(CURRENT_USER.user_username || CURRENT_USER.username);
            }
            $('#passwordMatchMessage').text('').removeClass('text-success text-danger');
            $('#btnGuardarPasswordNueva').prop('disabled', false);
        });

        // Toggle mostrar/ocultar contraseñas
        $(document).on('click', '#togglePasswordActual, #togglePasswordNueva, #togglePasswordConfirmar', function() {
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

        // Validación en tiempo real
        $(document).on('input', '#password_nueva, #password_confirmar', function() {
            const nueva = $('#password_nueva').val();
            const confirmar = $('#password_confirmar').val();
            const $message = $('#passwordMatchMessage');
            const $btnSubmit = $('#btnGuardarPasswordNueva');
            
            if (confirmar.length > 0) {
                if (nueva === confirmar) {
                    $message.text('✓ Las contraseñas coinciden')
                           .removeClass('text-danger')
                           .addClass('text-success');
                    $btnSubmit.prop('disabled', false);
                } else {
                    $message.text('✗ Las contraseñas no coinciden')
                           .removeClass('text-success')
                           .addClass('text-danger');
                    $btnSubmit.prop('disabled', true);
                }
            } else {
                $message.text('').removeClass('text-success text-danger');
                $btnSubmit.prop('disabled', false);
            }
        });

        // Submit cambiar contraseña
        $(document).on('submit', '#formCambiarPasswordNuevo', function(e) {
            e.preventDefault();
            
            const passwordActual = $('#password_actual').val();
            const passwordNueva = $('#password_nueva').val();
            const passwordConfirmar = $('#password_confirmar').val();
            
            if (passwordNueva !== passwordConfirmar) {
                alert('Las contraseñas no coinciden.');
                return;
            }
            
            if (!passwordActual || !passwordNueva) {
                alert('Todos los campos son requeridos.');
                return;
            }
            
            ajaxCall('auth', 'changePasswordWithValidation', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        $('#modalCambiarPasswordNuevo').modal('hide');
                        alert('Contraseña actualizada correctamente.');
                        window.location.href = BASE_URL + 'index.php?controller=auth&action=logout';
                    } else {
                        alert('Error: ' + (r.error || 'No se pudo cambiar la contraseña.'));
                    }
                })
                .fail(xhr => mostrarError('cambiar contraseña', xhr));
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
                        window.location.reload();
                    } else {
                        alert('Error al guardar: ' + (r.error || 'Verifique datos.'));
                    }
                })
                .fail(xhr => mostrarError('guardar usuario', xhr));
        });

        // Eliminar usuario
        $(document).on('click', '.btn-delete-user', function() {
            const id = $(this).data('id');
            
            if (confirm('¿Eliminar este usuario?')) {
                ajaxCall('user', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            window.location.reload();
                        } else {
                            alert('Error al eliminar: ' + (r.error || 'No se pudo eliminar.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar usuario', xhr));
            }
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
                alert('Debe mantener al menos un método de pago en el cobro dividido.');
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
                    alert('Debe seleccionar un método de pago.');
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
                    alert('Todos los pagos parciales deben tener un método y un monto válido.');
                    return;
                }
                
                const diferencia = Math.abs(montoTotal - sumaParciales);
                if (diferencia >= 0.01) {
                    alert(`La suma de los pagos parciales ($${sumaParciales.toFixed(2)}) no coincide con el monto total ($${montoTotal.toFixed(2)}).\nDiferencia: $${diferencia.toFixed(2)}`);
                    return;
                }
                
                dataObj.metodo_de_pago = 'Mixto';
                dataObj.pagos = JSON.stringify(pagos);
            }
            
            ajaxCall('ingreso', 'save', dataObj)
                .done(r => {
                    if (r.success) {
                        window.location.reload();
                    } else {
                        alert('Error al guardar: ' + (r.error || 'Verifique datos.'));
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
            
            if (confirm('¿Eliminar este ingreso? Se eliminarán también todos los pagos parciales asociados.')) {
                ajaxCall('ingreso', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            window.location.reload();
                        } else {
                            alert('Error al eliminar: ' + (r.error || 'Error.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar ingreso', xhr));
            }
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
                                    alert('Error al cargar: ' + (data.error || ''));
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
                        window.location.reload();
                    } else {
                        alert('Error al guardar: ' + (r.error || 'Verifique datos.'));
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
            
            if (confirm('¿Eliminar este egreso?')) {
                ajaxCall('egreso', 'delete', { id: id })
                    .done(r => {
                        if (r.success) {
                            $(document).trigger('egresoEliminado');
                            window.location.reload();
                        } else {
                            alert('Error al eliminar: ' + (r.error || 'Error.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar egreso', xhr));
            }
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
const CategoriasModule = (function() {
    const { ajaxCall, mostrarError } = ERPUtils;

    /**
     * Maneja la apertura del modal de categoría
     */
    function initModalCategoria() {
        $('#modalCategoria').on('show.bs.modal', function(event) {
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
                            alert('Error al cargar: ' + (data.error || ''));
                        }
                    })
                    .fail(xhr => {
                        mostrarError('cargar datos categoría', xhr);
                        $('#modalCategoria').modal('hide');
                    });
            } else {
                $('#modalCategoriaTitle').text('Agregar Nueva Categoría');
                // Por defecto, si tipo es Ingreso, mostrar campo concepto
                if ($('#cat_tipo').val() === 'Ingreso') {
                    $('#div_cat_concepto').show();
                } else {
                    $('#div_cat_concepto').hide();
                }
            }
            // Evento al cambiar tipo
            $('#cat_tipo').off('change.categoria').on('change.categoria', function() {
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
        $(document).on('submit', '#formCategoria', function(e) {
            e.preventDefault();
            // Validación: si tipo es Ingreso, concepto es obligatorio
            const tipo = $('#cat_tipo').val();
            const concepto = $('#cat_concepto').val();
            if (tipo === 'Ingreso' && !concepto) {
                alert('Debes seleccionar un concepto para las categorías de tipo Ingreso.');
                $('#cat_concepto').focus();
                return false;
            }

            ajaxCall('categoria', 'save', $(this).serialize())
                .done(r => {
                    if (r.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + (r.error || 'Error.'));
                    }
                })
                .fail(xhr => mostrarError('guardar categoría', xhr));
        });
    }

    /**
     * Maneja la eliminación de categorías
     */
    function initEliminarCategoria() {
        $(document).on('click', '.btn-del-categoria', function() {
            if (confirm('¿Eliminar esta categoría?')) {
                ajaxCall('categoria', 'delete', { id: $(this).data('id') })
                    .done(r => {
                        if (r.success) {
                            window.location.reload();
                        } else {
                            alert('Error: ' + (r.error || 'Error.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar categoría', xhr));
            }
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
            const presId = button ? $(button).data('id') : null;
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
                                    alert('Error: ' + (data.error || ''));
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
                        window.location.reload();
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
                            alert('Error: ' + (data.error || ''));
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
                        window.location.reload();
                    } else {
                        alert('Error: ' + (r.error || 'Error.'));
                    }
                })
                .fail(xhr => mostrarError('guardar presupuesto general', xhr));
        });
    }

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
                                    alert('Error: ' + (data.error || ''));
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
                        window.location.reload();
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
                                    alert('Error: ' + (data.error || ''));
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
                        window.location.reload();
                    } else {
                        alert('Error: ' + (r.error || 'Error al guardar.'));
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
            if (confirm('¿Eliminar este presupuesto general? Se eliminarán todos los sub-presupuestos asociados.')) {
                ajaxCall('presupuesto', 'deletePresupuestoGeneral', { id: $(this).data('id') })
                    .done(r => {
                        if (r.success) {
                            window.location.reload();
                        } else {
                            alert('Error: ' + (r.error || 'Error.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar presupuesto general', xhr));
            }
        });
        
        // Eliminar sub-presupuesto
        $(document).on('click', '.btn-del-presupuesto', function() {
            if (confirm('¿Eliminar este sub-presupuesto?')) {
                ajaxCall('presupuesto', 'delete', { id: $(this).data('id') })
                    .done(r => {
                        if (r.success) {
                            $(document).trigger('egresoEliminado');
                            window.location.reload();
                        } else {
                            alert('Error: ' + (r.error || 'Error.'));
                        }
                    })
                    .fail(xhr => mostrarError('eliminar sub-presupuesto', xhr));
            }
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
        initRefrescarPresupuestos();
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
// MÓDULO: Auditoría (Visor de Detalles)
// ============================================================================
const AuditoriaModule = (function() {
    const { ajaxCall, mostrarError, escapeHtml } = ERPUtils;

    /**
     * Maneja la apertura del modal de detalle de auditoría
     */
    function initModalDetalleAuditoria() {
        $('#modalDetalleAuditoria').on('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const auditoriaId = button ? $(button).data('id') : null;
            const $body = $('#detalleAuditoriaBody');
            
            if (!$body.length) {
                console.error('[ERROR] #detalleAuditoriaBody no encontrado');
                return;
            }
            
            $body.html('<p class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Cargando...</p>');
            
            if (!auditoriaId) {
                $body.html('<p class="text-danger">Error: ID de auditoría no especificado.</p>');
                return;
            }
            
            ajaxCall('auditoria', 'getDetalle', { id: auditoriaId }, 'GET')
                .done(data => {
                    if (data && !data.error && data.id_auditoria) {
                        let html = '<div class="audit-detail-content">';
                        html += `<div class="row mb-3">`;
                        html += `<div class="col-md-6"><strong>ID Auditoría:</strong> ${escapeHtml(data.id_auditoria)}</div>`;
                        html += `<div class="col-md-6"><strong>Fecha/Hora:</strong> ${escapeHtml(data.fecha_hora)}</div>`;
                        html += `</div>`;
                        html += `<div class="row mb-3">`;
                        html += `<div class="col-md-6"><strong>Usuario:</strong> ${escapeHtml(data.usuario_nombre || 'N/A')}</div>`;
                        html += `<div class="col-md-6"><strong>Tabla:</strong> ${escapeHtml(data.tabla_afectada)}</div>`;
                        html += `</div>`;
                        html += `<div class="row mb-3">`;
                        html += `<div class="col-12"><strong>Acción:</strong> <span class="badge bg-info">${escapeHtml(data.accion)}</span></div>`;
                        html += `</div>`;
                        
                        if (data.datos_anteriores && data.datos_anteriores.trim() !== '' && data.datos_anteriores !== '{}') {
                            html += `<div class="row mb-3">`;
                            html += `<div class="col-12"><strong>Datos Anteriores:</strong><pre class="bg-light p-2 mt-2" style="max-height:200px;overflow:auto;">${escapeHtml(data.datos_anteriores)}</pre></div>`;
                            html += `</div>`;
                        }
                        
                        if (data.datos_nuevos && data.datos_nuevos.trim() !== '' && data.datos_nuevos !== '{}') {
                            html += `<div class="row mb-3">`;
                            html += `<div class="col-12"><strong>Datos Nuevos:</strong><pre class="bg-light p-2 mt-2" style="max-height:200px;overflow:auto;">${escapeHtml(data.datos_nuevos)}</pre></div>`;
                            html += `</div>`;
                        }
                        
                        if (data.ip_address && data.ip_address.trim() !== '') {
                            html += `<div class="row mb-3">`;
                            html += `<div class="col-12"><strong>IP:</strong> ${escapeHtml(data.ip_address)}</div>`;
                            html += `</div>`;
                        }
                        
                        html += '</div>';
                        $body.html(html);
                    } else {
                        $body.html('<p class="text-danger">Error: ' + (data.error || 'No se pudo obtener el detalle.') + '</p>');
                    }
                })
                .fail(xhr => {
                    console.error('[ERROR] Cargar detalle auditoría:', xhr);
                    $body.html('<p class="text-danger">Error al cargar detalle. Verifique la consola (F12).</p>');
                });
        });
    }

    /**
     * Inicializa todos los componentes del módulo
     */
    function init() {
        initModalDetalleAuditoria();
        console.log('[✓] Módulo Auditoría inicializado');
    }

    return { init };
})();

// ============================================================================
// MÓDULO: Gestión del Sidebar (Responsive)
// ============================================================================
const SidebarModule = (function() {
    /**
     * Inicializa el comportamiento del sidebar en móviles
     */
    function init() {
        $('body').on('click', '#sidebar .nav-link', function() {
            try {
                if (window.innerWidth < 992) {
                    $('#sidebar').removeClass('open').addClass('closed');
                    $('#sidebarOverlay').hide();
                    document.body.style.overflow = '';
                }
            } catch(e) {
                console.error('[ERROR] Sidebar:', e);
            }
        });
        
        console.log('[✓] Módulo Sidebar inicializado');
    }

    return { init };
})();

// ============================================================================
// INICIALIZACIÓN GLOBAL
// ============================================================================
$(document).ready(function() {
    console.log('============================================================================');
    console.log('ERP IUM - Sistema de Gestión Financiera v2.0');
    console.log('============================================================================');
    console.log('[INFO] jQuery:', $.fn.jquery);
    console.log('[INFO] Bootstrap:', typeof bootstrap !== 'undefined' ? 'Disponible' : 'NO disponible');
    console.log('[INFO] Usuario:', CURRENT_USER);
    console.log('[INFO] BASE_URL:', BASE_URL);
    console.log('----------------------------------------------------------------------------');
    
    // Inicializar todos los módulos
    try {
        ERPUtils; // Verificar que existe
        UsuariosModule.init();
        IngresosModule.init();
        EgresosModule.init();
        CategoriasModule.init();
        PresupuestosModule.init();
        AlertasPresupuestosModule.init();
        DashboardModule.init();
        AuditoriaModule.init();
        SidebarModule.init();
        
        console.log('============================================================================');
        console.log('[✓] SISTEMA COMPLETAMENTE INICIALIZADO');
        console.log('============================================================================');
    } catch(e) {
        console.error('[✗] ERROR CRÍTICO AL INICIALIZAR:', e);
        alert('Error al inicializar el sistema. Por favor, recargue la página.\nSi el problema persiste, contacte al administrador.');
    }
});
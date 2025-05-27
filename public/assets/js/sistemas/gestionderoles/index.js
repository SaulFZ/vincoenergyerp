
         // JavaScript actualizado para el formulario
        $(document).ready(function() {
            // Manejar toggle de módulos completos
            $('.module-toggle').on('change', function() {
                const module = $(this).data('module');
                const isChecked = $(this).prop('checked');

                // Si el módulo está activado, mostrar su contenido
                if (isChecked) {
                    $(`#${module}-body`).addClass('active');
                } else {
                    // Si se desactiva el módulo, desmarcar todos los permisos y ocultar el cuerpo
                    $(`input[name^="permissions[${module}]"]`).prop('checked', false);
                    $(`#${module}-body`).removeClass('active');
                }
            });

            // Manejar cambios en los permisos individuales
            $('input[name^="permissions"]').on('change', function() {
                const moduleKey = $(this).attr('name').split('[')[1].split(']')[0];
                const allModulePermissions = $(`input[name^="permissions[${moduleKey}]"]`);
                const checkedModulePermissions = $(`input[name^="permissions[${moduleKey}]"]:checked`);

                // Si hay algún permiso marcado, asegurarnos que el toggle del módulo esté activado
                if (checkedModulePermissions.length > 0) {
                    $(`.module-toggle[data-module="${moduleKey}"]`).prop('checked', true);
                    $(`#${moduleKey}-body`).addClass('active');
                }
            });

            // Manejar el envío del formulario
            $('#permissionsForm').on('submit', function(e) {
                    e.preventDefault();

                    // Validar el formulario primero
                    let isValid = true;
                    const requiredFields = ['name', 'username', 'email', 'password'];

                    // Verificar campos requeridos
                    for (const field of requiredFields) {
                        if (!$(`#${field}`).val().trim()) {
                            $(`#${field}`).addClass('is-invalid');
                            isValid = false;
                        } else {
                            $(`#${field}`).removeClass('is-invalid');
                        }
                    }

                    // Validar formato de email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test($('#email').val())) {
                        $('#email').addClass('is-invalid');
                        isValid = false;
                    }

                    if (!isValid) {
                        Swal.fire({
                            title: 'Error de validación',
                            text: 'Por favor, complete todos los campos requeridos correctamente',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#3498db'
                        });
                        return;
                    }

                    // Recopilar datos del formulario
                    const formData = {
                        name: $('#name').val(),
                        username: $('#username').val(),
                        email: $('#email').val(),
                        password: $('#password').val(),
                        permissions: {}
                    };

                    // Recorre los toggles marcados (módulos activos)
                    $('.module-toggle:checked').each(function() {
                        const module = $(this).data('module');
                        formData.permissions[module] = {}; // Siempre agregar aunque esté vacío

                        // Buscar los permisos marcados dentro del módulo
                        $(`input[name^="permissions[${module}]"]:checked`).each(function() {
                            const nameAttr = $(this).attr('name');
                            const match = nameAttr.match(/permissions\[(.*?)\]\[(.*?)\]/);
                            if (match) {
                                const permission = match[2];
                                formData.permissions[module][permission] = true;
                            }
                        });
                    });


                    // Mostrar loader durante el proceso
                    Swal.fire({
                        title: 'Guardando...',
                        text: 'Espere mientras se guardan los datos',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Registrar para depuración
                    console.log('Enviando permisos:', formData);

                    // Enviar datos al servidor
                    $.ajax({
                            url: '/sistemas/roles', // Esta ruta debe coincidir con la definida en web.php
                            type: 'POST',
                            data: JSON.stringify(formData),
                            contentType: 'application/json', // Importante
                            headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.close();

                            if (response.success) {
                                Swal.fire({
                                    title: '¡Éxito!',
                                    text: response.message ||
                                        'Los permisos han sido guardados correctamente',
                                    icon: 'success',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#3498db'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '/sistemas/roles';
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message ||
                                        'Hubo un error al guardar los datos',
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#3498db'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            let errorMessage = 'Hubo un error al procesar la solicitud';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            // Si hay errores de validación
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                let errorList = '<ul class="text-start">';

                                for (const field in errors) {
                                    $(`#${field}`).addClass('is-invalid');
                                    errorList += `<li>${errors[field][0]}</li>`;
                                }

                                errorList += '</ul>';

                                Swal.fire({
                                    title: 'Error de validación',
                                    html: errorList,
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#3498db'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: errorMessage,
                                    icon: 'error',
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonColor: '#3498db'
                                });
                            }
                        }
                    });
            });

        // Botones de cancelar y volver
        $('#btn-cancel, #btn-back').on('click', function() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Se perderán los cambios no guardados',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3498db',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/sistemas/roles';
                }
            });
        });
        });


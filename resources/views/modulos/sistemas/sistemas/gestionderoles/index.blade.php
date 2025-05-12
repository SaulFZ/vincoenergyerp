<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vinco - Gestión de Roles</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/sistemas.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .container-roles {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .card-roles {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0069d9;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            animation: modalopen 0.3s;
        }

        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-check {
            margin-bottom: 10px;
        }

        .area-title {
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .sistemas-group {
            margin-left: 20px;
            margin-bottom: 15px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .modal-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: right;
        }
    </style>
</head>

<body>
    @include('layouts.header')

    <main class="container-roles">
        <div class="card-roles">
            <h1>Gestión de Usuarios y Roles</h1>
            <p>Crear y administrar usuarios con acceso a diferentes áreas y sistemas de Vinco Energy.</p>

            <button id="btnAddUser" class="btn-primary">
                <i class="fas fa-user-plus"></i> Agregar Nuevo Usuario
            </button>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Áreas de Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->areas->count() > 0)
                                @foreach($user->areas->take(2) as $area)
                                    <span class="badge-area">{{ $area->area }}</span>
                                @endforeach
                                @if($user->areas->count() > 2)
                                    <span class="badge-more">+{{ $user->areas->count() - 2 }}</span>
                                @endif
                            @else
                                <span class="badge-none">Sin áreas asignadas</span>
                            @endif
                        </td>
                        <td class="actions">
                            <button class="btn-primary btn-sm btn-edit" data-id="{{ $user->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-danger btn-sm btn-delete" data-id="{{ $user->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal para Crear/Editar Usuario -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Agregar Nuevo Usuario</h2>
                <span class="close">&times;</span>
            </div>

            <form id="userForm" method="POST" action="{{ route('sistemas.usuarios.store') }}">
                @csrf
                <input type="hidden" id="userId" name="user_id">

                <div class="form-group">
                    <label for="name">Nombre Completo:</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small id="passwordHelp" class="form-text text-muted">Dejar en blanco para mantener la misma contraseña al editar.</small>
                </div>

                <h3>Permisos de Acceso</h3>

                <div class="permissions-container">
                    <!-- Administración -->
                    <div class="area">
                        <div class="form-check">
                            <input class="form-check-input area-checkbox" type="checkbox" id="area_administracion" name="areas[]" value="administracion">
                            <label class="form-check-label area-title" for="area_administracion">
                                Administración
                            </label>
                        </div>

                        <div class="sistemas-group">
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_admin_1" name="sistemas[]" value="admin_reportes" data-area="administracion">
                                <label class="form-check-label" for="sistema_admin_1">Reportes administrativos</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_admin_2" name="sistemas[]" value="admin_facturas" data-area="administracion">
                                <label class="form-check-label" for="sistema_admin_2">Gestión de facturas</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_admin_3" name="sistemas[]" value="admin_contabilidad" data-area="administracion">
                                <label class="form-check-label" for="sistema_admin_3">Sistema de contabilidad</label>
                            </div>
                        </div>
                    </div>

                    <!-- QHSE -->
                    <div class="area">
                        <div class="form-check">
                            <input class="form-check-input area-checkbox" type="checkbox" id="area_qhse" name="areas[]" value="qhse">
                            <label class="form-check-label area-title" for="area_qhse">
                                QHSE
                            </label>
                        </div>

                        <div class="sistemas-group">
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_qhse_1" name="sistemas[]" value="qhse_seguridad" data-area="qhse">
                                <label class="form-check-label" for="sistema_qhse_1">Seguridad industrial</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_qhse_2" name="sistemas[]" value="qhse_ambiental" data-area="qhse">
                                <label class="form-check-label" for="sistema_qhse_2">Gestión ambiental</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_qhse_3" name="sistemas[]" value="qhse_calidad" data-area="qhse">
                                <label class="form-check-label" for="sistema_qhse_3">Control de calidad</label>
                            </div>
                        </div>
                    </div>

                    <!-- Ventas -->
                    <div class="area">
                        <div class="form-check">
                            <input class="form-check-input area-checkbox" type="checkbox" id="area_ventas" name="areas[]" value="ventas">
                            <label class="form-check-label area-title" for="area_ventas">
                                Ventas
                            </label>
                        </div>

                        <div class="sistemas-group">
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_ventas_1" name="sistemas[]" value="ventas_clientes" data-area="ventas">
                                <label class="form-check-label" for="sistema_ventas_1">Gestión de clientes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_ventas_2" name="sistemas[]" value="ventas_cotizaciones" data-area="ventas">
                                <label class="form-check-label" for="sistema_ventas_2">Sistema de cotizaciones</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_ventas_3" name="sistemas[]" value="ventas_reportes" data-area="ventas">
                                <label class="form-check-label" for="sistema_ventas_3">Reportes de ventas</label>
                            </div>
                        </div>
                    </div>

                    <!-- Recursos Humanos -->
                    <div class="area">
                        <div class="form-check">
                            <input class="form-check-input area-checkbox" type="checkbox" id="area_rh" name="areas[]" value="recursoshumanos">
                            <label class="form-check-label area-title" for="area_rh">
                                Recursos Humanos
                            </label>
                        </div>

                        <div class="sistemas-group">
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_rh_1" name="sistemas[]" value="rh_personal" data-area="recursoshumanos">
                                <label class="form-check-label" for="sistema_rh_1">Gestión de personal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_rh_2" name="sistemas[]" value="rh_nomina" data-area="recursoshumanos">
                                <label class="form-check-label" for="sistema_rh_2">Sistema de nómina</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_rh_3" name="sistemas[]" value="rh_capacitacion" data-area="recursoshumanos">
                                <label class="form-check-label" for="sistema_rh_3">Capacitación y desarrollo</label>
                            </div>
                        </div>
                    </div>

                    <!-- Suministro -->
                    <div class="area">
                        <div class="form-check">
                            <input class="form-check-input area-checkbox" type="checkbox" id="area_suministro" name="areas[]" value="suministro">
                            <label class="form-check-label area-title" for="area_suministro">
                                Suministro
                            </label>
                        </div>

                        <div class="sistemas-group">
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_suministro_1" name="sistemas[]" value="suministro_compras" data-area="suministro">
                                <label class="form-check-label" for="sistema_suministro_1">Gestión de compras</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_suministro_2" name="sistemas[]" value="suministro_proveedores" data-area="suministro">
                                <label class="form-check-label" for="sistema_suministro_2">Gestión de proveedores</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_suministro_3" name="sistemas[]" value="suministro_logistica" data-area="suministro">
                                <label class="form-check-label" for="sistema_suministro_3">Logística y distribución</label>
                            </div>
                        </div>
                    </div>

                    <!-- Operaciones -->
                    <div class="area">
                        <div class="form-check">
                            <input class="form-check-input area-checkbox" type="checkbox" id="area_operaciones" name="areas[]" value="operaciones">
                            <label class="form-check-label area-title" for="area_operaciones">
                                Operaciones
                            </label>
                        </div>

                        <div class="sistemas-group">
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_operaciones_1" name="sistemas[]" value="operaciones_proyectos" data-area="operaciones">
                                <label class="form-check-label" for="sistema_operaciones_1">Gestión de proyectos</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_operaciones_2" name="sistemas[]" value="operaciones_mantenimiento" data-area="operaciones">
                                <label class="form-check-label" for="sistema_operaciones_2">Mantenimiento</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_operaciones_3" name="sistemas[]" value="operaciones_produccion" data-area="operaciones">
                                <label class="form-check-label" for="sistema_operaciones_3">Control de producción</label>
                            </div>
                        </div>
                    </div>

                    <!-- Sistemas -->
                    <div class="area">
                        <div class="form-check">
                            <input class="form-check-input area-checkbox" type="checkbox" id="area_sistemas" name="areas[]" value="sistemas">
                            <label class="form-check-label area-title" for="area_sistemas">
                                Sistemas
                            </label>
                        </div>

                        <div class="sistemas-group">
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_sistemas_1" name="sistemas[]" value="sistemas_soporte" data-area="sistemas">
                                <label class="form-check-label" for="sistema_sistemas_1">Soporte técnico</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_sistemas_2" name="sistemas[]" value="sistemas_desarrollo" data-area="sistemas">
                                <label class="form-check-label" for="sistema_sistemas_2">Desarrollo de software</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_sistemas_3" name="sistemas[]" value="sistemas_admin_usuarios" data-area="sistemas">
                                <label class="form-check-label" for="sistema_sistemas_3">Administración de usuarios</label>
                            </div>
                        </div>
                    </div>

                    <!-- Almacén -->
                    <div class="area">
                        <div class="form-check">
                            <input class="form-check-input area-checkbox" type="checkbox" id="area_almacen" name="areas[]" value="almacen">
                            <label class="form-check-label area-title" for="area_almacen">
                                Almacén
                            </label>
                        </div>

                        <div class="sistemas-group">
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_almacen_1" name="sistemas[]" value="almacen_inventario" data-area="almacen">
                                <label class="form-check-label" for="sistema_almacen_1">Control de inventario</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_almacen_2" name="sistemas[]" value="almacen_despacho" data-area="almacen">
                                <label class="form-check-label" for="sistema_almacen_2">Despacho y recepción</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_almacen_3" name="sistemas[]" value="almacen_reportes" data-area="almacen">
                                <label class="form-check-label" for="sistema_almacen_3">Reportes de almacén</label>
                            </div>
                        </div>
                    </div>

                    <!-- Geociencias -->
                    <div class="area">
                        <div class="form-check">
                            <input class="form-check-input area-checkbox" type="checkbox" id="area_geociencias" name="areas[]" value="geociencias">
                            <label class="form-check-label area-title" for="area_geociencias">
                                Geociencias
                            </label>
                        </div>

                        <div class="sistemas-group">
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_geo_1" name="sistemas[]" value="geo_estudios" data-area="geociencias">
                                <label class="form-check-label" for="sistema_geo_1">Estudios geológicos</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_geo_2" name="sistemas[]" value="geo_mapas" data-area="geociencias">
                                <label class="form-check-label" for="sistema_geo_2">Mapas y cartografía</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input sistema-checkbox" type="checkbox" id="sistema_geo_3" name="sistemas[]" value="geo_analisis" data-area="geociencias">
                                <label class="form-check-label" for="sistema_geo_3">Análisis de datos</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-danger" id="btnCancel">Cancelar</button>
                    <button type="submit" class="btn-success" id="btnSave">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    @include('layouts.footer')

    <script>
        $(document).ready(function() {
            // Variables para el modal
            const modal = document.getElementById('userModal');
            const btnAddUser = document.getElementById('btnAddUser');
            const btnCancel = document.getElementById('btnCancel');
            const closeBtn = document.getElementsByClassName('close')[0];

            // Abrir modal para nuevo usuario
            btnAddUser.onclick = function() {
                $('#modalTitle').text('Agregar Nuevo Usuario');
                $('#userForm').attr('action', '{{ route("sistemas.usuarios.store") }}');
                $('#userForm')[0].reset();
                $('#userId').val('');
                $('#password').attr('required', true);
                $('#passwordHelp').hide();
                modal.style.display = 'block';
            }

            // Cerrar modal
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }

            btnCancel.onclick = function() {
                modal.style.display = 'none';
                return false;
            }

            // Cerrar al hacer clic fuera del modal
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }

            // Gestionar la relación entre checkboxes de área y sistemas
            $('.area-checkbox').change(function() {
                const area = $(this).val();
                const isChecked = $(this).is(':checked');

                // Marcar/desmarcar todos los sistemas del área
                $(`.sistema-checkbox[data-area=${area}]`).prop('checked', isChecked);
            });

            $('.sistema-checkbox').change(function() {
                const area = $(this).data('area');

                // Si se marca algún sistema, también se marca el área
                if ($(this).is(':checked')) {
                    $(`#area_${area}`).prop('checked', true);
                } else {
                    // Verificar si hay algún otro sistema marcado para esta área
                    const anyChecked = $(`.sistema-checkbox[data-area=${area}]:checked`).length > 0;

                    // Si no hay ningún sistema marcado, desmarcar el área
                    if (!anyChecked) {
                        $(`#area_${area}`).prop('checked', false);
                    }
                }
            });

            // Editar usuario
            $('.btn-edit').click(function() {
                const userId = $(this).data('id');

                // Limpiar selecciones previas
                $('#userForm')[0].reset();

                // Cambiar el título del modal y la acción del formulario
                $('#modalTitle').text('Editar Usuario');
                $('#userForm').attr('action', `{{ url('sistemas/usuarios/update') }}/${userId}`);
                $('#userId').val(userId);

                // La contraseña no es requerida en edición
                $('#password').attr('required', false);
                $('#passwordHelp').show();

                // Cargar datos del usuario mediante AJAX
                $.ajax({
                    url: `{{ url('sistemas/usuarios/edit') }}/${userId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // Llenar el formulario con los datos del usuario
                        $('#name').val(data.user.name);
                        $('#username').val(data.user.username);
                        $('#email').val(data.user.email);

                        // Marcar las áreas y sistemas asignados
                        if (data.permissions) {
                            // Marcar áreas
                            if (data.permissions.areas) {
                                data.permissions.areas.forEach(area => {
                                    $(`#area_${area}`).prop('checked', true);
                                });
                            }

                            // Marcar sistemas
                            if (data.permissions.sistemas) {
                                data.permissions.sistemas.forEach(sistema => {
                                    $(`input[name="sistemas[]"][value="${sistema}"]`).prop('checked', true);
                                });
                            }
                        }

                        // Mostrar el modal
                        modal.style.display = 'block';
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron cargar los datos del usuario',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            });

            // Eliminar usuario
            $('.btn-delete').click(function() {
                const userId = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar solicitud para eliminar el usuario
                        $.ajax({
                            url: `{{ url('sistemas/usuarios/delete

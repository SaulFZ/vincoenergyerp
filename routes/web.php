<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RecursosHumanos\LoadChart\ApprovalController;
/* CONTROLADORES DE RECURSOS HUMANOS */
use App\Http\Controllers\RecursosHumanos\LoadChart\AssignmentController;
use App\Http\Controllers\RecursosHumanos\LoadChart\CalendarController;
use App\Http\Controllers\RecursosHumanos\LoadChart\FortnightlyConfigController;
use App\Http\Controllers\RecursosHumanos\LoadChart\InfoServicesController;
use App\Http\Controllers\RecursosHumanos\LoadChart\SquadController;
use App\Http\Controllers\Sistemas\RoleController;

/* CONTROLADORES DE SISTEMAS */
use Illuminate\Support\Facades\Route;

// ===================================================
// RUTAS DE AUTENTICACIÓN
// ===================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirección de la página principal según estado de autenticación
Route::get('/', function () {
    return session()->has('auth')
    ? redirect()->route('home')
    : redirect()->route('login');
});

// ===================================================
// RUTAS DE INTERFAZ DE USUARIO
// ===================================================
// Componente de pnatalla de inicio de sesion

Route::get('/splash', function () {
    return view('components.ui.splash');
})->name('splash');

// ===================================================
// RUTAS PROTEGIDAS POR VERIFICACIÓN DE SESIÓN
// ===================================================
Route::middleware(['web', 'auth'])->group(function () {
    // Ruta principal del panel
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    // ===================================================
    // MÓDULO: ADMINISTRACIÓN
    // ===================================================
    Route::get('/administracion', function () {
        return view('modulos.administracion.administracionhome');
    })
        ->middleware('check.permission:administracion')
        ->name('modulo.administracion');

    // ===================================================
    // MÓDULO: QHSE Y SUBSISTEMAS
    // ===================================================
    Route::prefix('qhse')
        ->middleware('check.permission:qhse')
        ->group(function () {
            // Página principal QHSE
            Route::get('/', function () {
                return view('modulos.qhse.qhsehome');
            })->name('modulo.qhse');

            // Subsistemas de QHSE
            Route::get('/vescap', function () {
                return view('modulos.qhse.sistemas.vescap.index');
            })
                ->middleware('check.permission:qhse,vescap')
                ->name('qhse.vescap');

            Route::get('/incidencias', function () {
                return view('modulos.qhse.sistemas.incidencias.index');
            })
                ->middleware('check.permission:qhse,incidencias')
                ->name('qhse.incidencias');

            Route::get('/auditorias', function () {
                return view('modulos.qhse.sistemas.auditorias.index');
            })
                ->middleware('check.permission:qhse,auditorias')
                ->name('qhse.auditorias');
        });

// ===================================================
// MÓDULO: RECURSOS HUMANOS Y SUBSISTEMAS
// ===================================================
    Route::prefix('recursoshumanos')
        ->middleware('check.permission:recursoshumanos')
        ->group(function () {
            // Página principal RRHH
            Route::get('/', function () {
                return view('modulos.recursoshumanos.recursoshumanoshome');
            })->name('modulo.recursoshumanos');

            // Subsistemas de RRHH
            Route::get('/altasempleados', function () {
                return view('modulos.recursoshumanos.sistemas.altasempleados.index');
            })
                ->middleware('check.permission:recursoshumanos,altasempleados')
                ->name('recursoshumanos.altasempleados');

            Route::prefix('loadchart')->group(function () {
                // Redirigir la ruta principal a calendar
                Route::get('/', function () {
                    return redirect()->route('loadchart.calendar');
                })
                    ->name('recursoshumanos.loadchart')
                    ->middleware('check.permission:recursoshumanos,loadchart');

                // Esta ruta carga la vista HTML completa para la primera vez.
                Route::get('/calendar', [CalendarController::class, 'index'])->name('loadchart.calendar');

                // Esta es la ruta AJAX para cargar los datos del calendario.
                Route::get('/calendar-data', [CalendarController::class, 'getCalendarData']);

                // NUEVA RUTA: Guardar actividad diaria
                Route::post('/save-activity', [CalendarController::class, 'saveActivity'])->name('loadchart.save_activity');

                // NUEVA RUTA: Obtener actividades mensuales
                Route::get('/monthly-activities', [CalendarController::class, 'getMonthlyActivities'])->name('loadchart.monthly_activities');

                // Las rutas existentes para la configuración quincenal se mantienen.
                Route::get('/fortnightly-config/{year}/{month}', [CalendarController::class, 'getFortnightlyConfig']);
                Route::post('/fortnightly-config/generate-default', [CalendarController::class, 'generateDefaultConfig']);
                Route::post('/fortnightly-config', [CalendarController::class, 'storeFortnightlyConfig']);

                Route::get('/approval', function () {
                    return view('modulos.recursoshumanos.sistemas.loadchart.approval');
                })->name('loadchart.approval');

                Route::get('/approval', [SquadController::class, 'index'])->name('loadchart.approval');
                Route::get('/recursos-humanos/loadchart/get-operadores', [SquadController::class, 'getOperadores'])->name('squads.get_operadores');
                Route::get('/recursos-humanos/loadchart/get-squads', [SquadController::class, 'getSquads'])->name('squads.get_squads');
                Route::post('/squads/store', [SquadController::class, 'store'])->name('squads.store');
                Route::delete('/squads/{squadNumber}', [SquadController::class, 'destroy'])->name('squads.destroy');
                Route::get('/squads/{squadNumber}', [SquadController::class, 'show'])->name('squads.show');
                Route::get('/info-services', [InfoServicesController::class, 'getServicesAndBonuses'])->name('info.services.json');
                // Ruta principal de aprobaciones
                Route::get('/approval', [ApprovalController::class, 'index'])->name('loadchart.approval');

                // Ruta AJAX para obtener datos de un mes específico
                Route::get('/approval-data/{year}/{month}', [ApprovalController::class, 'getApprovalData'])
                    ->name('loadchart.approval.data')
                    ->where(['year' => '[0-9]{4}', 'month' => '[0-9]{1,2}']);

                // Ruta AJAX para actualizar estado de aprobación
                Route::post('/approval-status', [ApprovalController::class, 'updateApprovalStatus'])
                    ->name('loadchart.approval.status');
                // Agrega esta ruta en tu archivo web.php
                Route::get('/recursoshumanos/loadchart/approval-data/{year}/{month}', [ApprovalController::class, 'getApprovalData'])
                    ->name('approval.data');


    // Ruta para actualizar el estado de aprobación
    Route::post('/update-approval-status', [ApprovalController::class, 'updateApprovalStatus'])->name('loadchart.update.approval.status');

    Route::post('/update-multiple-statuses', [ApprovalController::class, 'updateMultipleStatuses'])->name('loadchart.update.multiple.statuses');

                // Ruta para obtener la configuración de un mes y año específicos
                Route::get('fortnightly-config/{year}/{month}', [FortnightlyConfigController::class, 'getConfig']);
                // Ruta para guardar o actualizar la configuración
                Route::post('fortnightly-config', [FortnightlyConfigController::class, 'store']);
                // Ruta para eliminar una configuración (opcional, pero buena práctica)
                Route::delete('fortnightly-config/{year}/{month}', [FortnightlyConfigController::class, 'destroy']);
                // Ruta para obtener todas las configuraciones de un año
                Route::get('fortnightly-config/year/{year}', [FortnightlyConfigController::class, 'getYearConfigs']);
                // Ruta para generar una configuración por defecto
                Route::post('fortnightly-config/generate-default', [FortnightlyConfigController::class, 'generateDefault']);

                Route::get('/history', function () {
                    return view('modulos.recursoshumanos.sistemas.loadchart.history');
                })->name('loadchart.history');

                Route::get('/stats', function () {
                    return view('modulos.recursoshumanos.sistemas.loadchart.stats');
                })->name('loadchart.stats');

                Route::get('/review_assignments', function () {
                    return view(
                        'modulos.recursoshumanos.sistemas.loadchart.review_assignments'
                    );
                })->name('loadchart.review_assignments');

                Route::get('/review_assignments', [
                    AssignmentController::class,
                    'index',
                ])
                    ->name('loadchart.review_assignments')
                    ->middleware(
                        'check.permission:recursoshumanos,loadchart,review_assignments'
                    );

                Route::get('/review_assignments/employees', [
                    AssignmentController::class,
                    'getEmployees',
                ])
                    ->name('loadchart.getEmployees')
                    ->middleware(
                        'check.permission:recursoshumanos,loadchart,review_assignments'
                    );

                Route::post('/review_assignments/existing', [
                    AssignmentController::class,
                    'getExistingAssignments',
                ])
                    ->name('loadchart.getExistingAssignments')
                    ->middleware(
                        'check.permission:recursoshumanos,loadchart,review_assignments'
                    );

                Route::post('/review_assignments/save', [
                    AssignmentController::class,
                    'saveAssignment',
                ])
                    ->name('loadchart.saveAssignment')
                    ->middleware(
                        'check.permission:recursoshumanos,loadchart,review_assignments'
                    );
            });
        });
    // ===================================================
    // MÓDULO: SISTEMAS Y SUBSISTEMAS
    // ===================================================
    Route::prefix('sistemas')
        ->middleware('check.permission:sistemas')
        ->group(function () {
            // Gestión de roles
            Route::get('/gestionderoles', function () {
                return view('modulos.sistemas.sistemas.gestionderoles.index');
            });

            // CRUD de roles (RoleController)
            Route::resource('roles', RoleController::class)
                ->except(['show'])
                ->names([
                    'index'   => 'sistemas.roles.index',
                    'create'  => 'sistemas.roles.create',
                    'store'   => 'sistemas.roles.store',
                    'edit'    => 'sistemas.roles.edit',
                    'update'  => 'sistemas.roles.update',
                    'destroy' => 'sistemas.roles.destroy',
                ]);

            // Ruta para obtener permisos
            Route::get('get-permissions', [RoleController::class, 'getPermissions']);

            // Ruta para obtener roles
            Route::get('get-roles', [RoleController::class, 'getRoles']);

            Route::get('search-employees', [
                RoleController::class,
                'searchEmployees',
            ]);
        });

    // ===================================================
    // MÓDULO: VENTAS Y SUBSISTEMAS
    // ===================================================
    Route::prefix('ventas')
        ->middleware('check.permission:ventas')
        ->group(function () {
            // Página principal Ventas
            Route::get('/', function () {
                return view('modulos.ventas.ventashome');
            })->name('modulo.ventas');

            // Subsistemas de Ventas
            Route::get('/clientes', function () {
                return view('modulos.ventas.sistemas.clientes.index');
            })
                ->middleware('check.permission:ventas,clientes')
                ->name('ventas.clientes');

            Route::get('/cotizaciones', function () {
                return view('modulos.ventas.sistemas.cotizaciones.index');
            })
                ->middleware('check.permission:ventas,cotizaciones')
                ->name('ventas.cotizaciones');

            Route::get('/oportunidades', function () {
                return view('modulos.ventas.sistemas.oportunidades.index');
            })
                ->middleware('check.permission:ventas,oportunidades')
                ->name('ventas.oportunidades');
        });

    // ===================================================
    // MÓDULO: SUMINISTRO
    // ===================================================
    Route::get('/suministro', function () {
        return view('modulos.suministros.suministroshome');
    })
        ->middleware('check.permission:suministro')
        ->name('modulo.suministro');

    // ===================================================
    // MÓDULO: OPERACIONES
    // ===================================================
    Route::get('/operaciones', function () {
        return view('modulos.operaciones.operacioneshome');
    })
        ->middleware('check.permission:operaciones')
        ->name('modulo.operaciones');

    // ===================================================
    // MÓDULO: ALMACÉN
    // ===================================================
    Route::get('/almacen', function () {
        return view('modulos.almacen.index');
    })
        ->middleware('check.permission:almacen')
        ->name('modulo.almacen');

    // ===================================================
    // MÓDULO: GEOCIENCIAS Y SUBSISTEMAS
    // ===================================================
    Route::prefix('geociencias')
        ->middleware('check.permission:geociencias')
        ->group(function () {
            // Página principal Geociencias
            Route::get('/', function () {
                return view('modulos.geociencias.geocienciashome');
            })->name('modulo.geociencias');

            // Subsistemas de Geociencias
            Route::get('/exploraciones', function () {
                return view('modulos.geociencias.sistemas.exploraciones.index');
            })
                ->middleware('check.permission:geociencias,exploraciones')
                ->name('geociencias.exploraciones');

            Route::get('/analisis', function () {
                return view('modulos.geociencias.sistemas.analisis.index');
            })
                ->middleware('check.permission:geociencias,analisis')
                ->name('geociencias.analisis');
        });
});

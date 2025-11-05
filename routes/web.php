<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RecursosHumanos\LoadChart\ApprovalController;
/* CONTROLADORES DE RECURSOS HUMANOS */
use App\Http\Controllers\RecursosHumanos\LoadChart\AssignmentController;
use App\Http\Controllers\RecursosHumanos\LoadChart\CalendarController;
use App\Http\Controllers\RecursosHumanos\LoadChart\EmployeeVacationBalanceController;
use App\Http\Controllers\RecursosHumanos\LoadChart\FieldBonusController;
use App\Http\Controllers\RecursosHumanos\LoadChart\FortnightlyConfigController;
use App\Http\Controllers\RecursosHumanos\LoadChart\InfoServicesController;
use App\Http\Controllers\RecursosHumanos\LoadChart\SquadController;
/* CONTROLADORES DE SISTEMAS */
use App\Http\Controllers\Sistemas\RoleController;
use Illuminate\Support\Facades\Route;

// ===================================================
// RUTAS DE AUTENTICACIÓN
// ===================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Rutas para restablecer contraseña
// PASO 1: Obtener el correo asociado al nombre de usuario (AJAX desde Login)
Route::post('password/get-email', [LoginController::class, 'getUserEmail'])->name('password.getUserEmail');

// PASO 2: Enviar código de 6 dígitos al correo (AJAX desde Login)
Route::post('password/send-code', [LoginController::class, 'sendResetCode'])->name('password.sendCode');

// PASO 3: Verificar el código ingresado (AJAX desde Login)
Route::post('password/verify-code', [LoginController::class, 'verifyResetCode'])->name('password.verifyCode');

// NUEVA RUTA: Mostrar el formulario de restablecimiento, sin parámetros en la URL (PASO 4.a)
// El email y el token se obtendrán de la sesión.
Route::get('password/reset', [LoginController::class, 'showResetForm'])->name('password.resetForm');

// RUTA FINAL: Actualizar la contraseña (POST desde reset.blade.php)
Route::post('password/update', [LoginController::class, 'resetPassword'])->name('password.update');

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
            // Subsistemas de RRHH (Rutas que no son de LoadChart)
            Route::get('/altasempleados', function () {
                return view('modulos.recursoshumanos.sistemas.altasempleados.index');
            })
                ->middleware('check.permission:recursoshumanos,altasempleados')
                ->name('recursoshumanos.altasempleados');

            // ===================================================
            // GRUPO LOADCHART
            // Prefijo: /recursoshumanos/loadchart
            // ===================================================
            Route::prefix('loadchart')->group(function () {
                // Redirigir la ruta principal a calendar
                Route::get('/', function () {
                    return redirect()->route('loadchart.calendar');
                })
                    ->name('recursoshumanos.loadchart')
                    ->middleware('check.permission:recursoshumanos,loadchart');

                // --- RUTAS GESTIONADAS POR CalendarController ---
                Route::controller(CalendarController::class)->group(function () {
                    // Una sola ruta para ambos casos
                    Route::get('/calendar', 'index')->name('loadchart.calendar');

                    // Las demás rutas auxiliares
                    Route::get('/calendar-data', 'getCalendarData');
                    Route::post('/save-activity', 'saveActivity')->name('loadchart.save_activity');
                    Route::get('/monthly-activities', 'getMonthlyActivities')->name('loadchart.monthly_activities');
                    Route::get('/balances-data', 'getEmployeeBalancesAjax')->name('loadchart.balances.data');
                });

                // --- RUTAS DE APROBACIÓN (ApprovalController) ---
                Route::controller(ApprovalController::class)->group(function () {
                    Route::get('/approval', 'index')->name('loadchart.approval');
                    Route::get('/approval-data/{year}/{month}', 'getApprovalData')
                        ->name('loadchart.approval.data')
                        ->where(['year' => '[0-9]{4}', 'month' => '[0-9]{1,2}']);
                    Route::post('/approval-status', 'updateApprovalStatus')->name('loadchart.approval.status');
                    // La ruta repetida 'approval-data/{year}/{month}' se eliminó
                    Route::post('check-updates', 'checkUpdates')->name('loadchart.check-updates');
                    Route::post('/update-approval-status', 'updateApprovalStatus')->name('loadchart.update.approval.status');
                    Route::post('/update-multiple-statuses', 'updateMultipleStatuses')->name('loadchart.update.multiple.statuses');
                });


                // --- RUTAS DE History (HistoryController) ---
                Route::get('/history', function () {
                    return view('modulos.recursoshumanos.sistemas.loadchart.history');
                })->name('loadchart.history');

                // --- RUTAS GESTIONADAS POR FortnightlyConfigController ---
                Route::controller(FortnightlyConfigController::class)->group(function () {
                    Route::get('fortnightly-config/{year}/{month}', 'getConfig');
                    Route::post('fortnightly-config', 'store');
                    Route::delete('fortnightly-config/{year}/{month}', 'destroy');
                    Route::get('fortnightly-config/year/{year}', 'getYearConfigs');
                    Route::post('fortnightly-config/generate-default', 'generateDefault')->name('loadchart.fortnightly-config.generate-default');
                });

                // --- RUTAS DE SQUADS (SquadController) ---
                // Nota: Aquí asumimos que las rutas "get-operadores" y "get-squads" también deben simplificarse.
                Route::controller(SquadController::class)->group(function () {
                    // La ruta a la vista approval ya fue definida en ApprovalController, esta puede ser redundante o para AJAX
                    // Route::get('/approval', 'index')->name('loadchart.approval');

                    // Ajustamos las rutas eliminando el prefijo redundante
                    Route::get('/get-operadores', 'getOperadores')->name('squads.get_operadores');
                    Route::get('/get-squads', 'getSquads')->name('squads.get_squads');
                    Route::post('/squads/store', 'store')->name('squads.store');
                    Route::delete('/squads/{squadNumber}', 'destroy')->name('squads.destroy');
                    Route::get('/squads/{squadNumber}', 'show')->name('squads.show');
                });

                // --- RUTAS DE SERVICIOS ADICIONALES (InfoServicesController) ---
                Route::get('/info-services', [InfoServicesController::class, 'getServicesAndBonuses'])->name('info.services.json');

                // --- RUTAS DE ASIGNACIONES (AssignmentController) ---
                Route::controller(AssignmentController::class)->group(function () {
                    Route::get('/review_assignments', 'index')
                        ->name('loadchart.review_assignments')
                        ->middleware('check.permission:recursoshumanos,loadchart,review_assignments');
                    Route::get('/review_assignments/employees', 'getEmployees')
                        ->name('loadchart.getEmployees')
                        ->middleware('check.permission:recursoshumanos,loadchart,review_assignments');
                    Route::post('/review_assignments/existing', 'getExistingAssignments')
                        ->name('loadchart.getExistingAssignments')
                        ->middleware('check.permission:recursoshumanos,loadchart,review_assignments');
                    Route::post('/review_assignments/save', 'saveAssignment')
                        ->name('loadchart.saveAssignment')
                        ->middleware('check.permission:recursoshumanos,loadchart,review_assignments');
                });

                // --- RUTAS DE BONOS DE CAMPO (FieldBonusController) ---
                Route::controller(FieldBonusController::class)->group(function () {
                    Route::get('/field_bonuses', 'index')->name('field_bonuses');
                    Route::get('/field-bonuses-data', 'getBonuses');
                    Route::get('/field-bonuses/{id}/edit', 'edit');
                    Route::post('/field-bonuses', 'store');
                    Route::put('/field-bonuses/{id}', 'update');
                    Route::delete('/field-bonuses/{id}', 'destroy');
                    Route::post('/field-bonuses/{id}/toggle-status', 'toggleStatus');
                });

                // --- RUTAS DE BALANCE DE VACACIONES (EmployeeVacationBalanceController) ---
                Route::prefix('employee_vacation_balance')->controller(EmployeeVacationBalanceController::class)->group(function () {
                    Route::get('/', 'index')->name('vacation_balance.index');
                    Route::post('/', 'store');
                    Route::get('/{id}/edit', 'edit');
                    Route::put('/{id}', 'update');
                    Route::delete('/{id}', 'destroy');
                    Route::post('/force-update-years', 'forceUpdateYears');
                });


                Route::get('/stats', function () {
                    return view('modulos.recursoshumanos.sistemas.loadchart.stats');
                })->name('loadchart.stats');
            });
        });
// El cierre del paréntesis final (}); ya no es necesario aquí si se incluye en el archivo principal.

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

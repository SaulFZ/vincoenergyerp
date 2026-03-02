<?php

use App\Http\Controllers\Auth\LoginController;
/* CONTROLADORES DE RECURSOS QHSE */
use App\Http\Controllers\Qhse\Gerenciamiento\DriverLicenseController;
use App\Http\Controllers\Qhse\Gerenciamiento\JourneyController;
use App\Http\Controllers\Qhse\Gerenciamiento\JourneyQueryController;
use App\Http\Controllers\Qhse\Gerenciamiento\JourneyStatusController;
use App\Http\Controllers\Qhse\Gerenciamiento\JourneyStoreController;
use App\Http\Controllers\Qhse\Gerenciamiento\StatsController;


/* CONTROLADORES DE RECURSOS HUMANOS */
use App\Http\Controllers\RecursosHumanos\LoadChart\ApprovalController;
use App\Http\Controllers\RecursosHumanos\LoadChart\AssignmentController;
use App\Http\Controllers\RecursosHumanos\LoadChart\CalendarController;
use App\Http\Controllers\RecursosHumanos\LoadChart\EmployeeVacationBalanceController;
use App\Http\Controllers\RecursosHumanos\LoadChart\FieldBonusController;
use App\Http\Controllers\RecursosHumanos\LoadChart\FortnightlyConfigController;
use App\Http\Controllers\RecursosHumanos\LoadChart\HistoryController;
use App\Http\Controllers\RecursosHumanos\LoadChart\InfoServicesController;
use App\Http\Controllers\RecursosHumanos\LoadChart\SquadController;
/* CONTROLADORES DE SISTEMAS */
use App\Http\Controllers\Sistemas\RoleController;
use App\Http\Controllers\Sistemas\Tickets\TicketController;
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

// En routes/web.php
Route::post('/session-ping', function () {
    // Esto refresca automáticamente la sesión de Laravel
    request()->session()->put('last_activity', now());

    return response()->json([
        'status'    => 'success',
        'message'   => 'Session Refreshed',
        'timestamp' => now()->toDateTimeString(),
    ]);
})->middleware(['auth', 'web']); // Asegúrate de usar los middlewares correctos

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
    // MÓDULO: SISTEMAS Y SUBSISTEMAS
    // ===================================================
    Route::prefix('sistemas')
        ->middleware('check.permission:sistemas')
        ->group(function () {
            // ===================================================
            // GRUPO: GESTIÓN DE ROLES
            // Prefijo: /sistemas/gestionderoles
            // ===================================================
            Route::prefix('gestionderoles')->group(function () {
                // 1. Redirección automática:
                // Si el usuario entra a /sistemas/gestionderoles,
                // lo mandamos a la lista principal de roles.
                Route::get('/', function () {
                    return redirect()->route('sistemas.roles.index');
                })
                    ->name('sistemas.gestionderoles')
                    ->middleware('check.permission:sistemas,gestionderoles');

                // --- RUTAS DE RECURSOS (CRUD) ---
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

                // --- RUTAS AUXILIARES (RoleController) ---
                Route::controller(RoleController::class)->group(function () {
                    Route::get('get-permissions', 'getPermissions')->name('sistemas.roles.permissions');
                    Route::get('get-roles', 'getRoles')->name('sistemas.roles.list');
                    Route::get('search-employees', 'searchEmployees')->name('sistemas.roles.search');
                });
            });

            // ---------------------------------------------------
            // SUBSISTEMA 2: GESTIÓN DE TICKETS (SOPORTE)
            // ---------------------------------------------------
            Route::prefix('tickets')->group(function () {
                // Redirección automática a la vista principal de tickets
                Route::get('/', function () {
                    return redirect()->route('tickets.index');
                })
                    ->name('sistemas.tickets')
                    ->middleware('check.permission:sistemas,tickets');

                // Rutas gestionadas por TicketController
                // Nota: He usado 'index' para el dashboard de soporte
                Route::controller(TicketController::class)->group(function () {
                    Route::get('/management_tickets', 'index')->name('tickets.index');
                });
            });
        });

    // ===================================================
    // MÓDULO: SISTEMAS Y SUBSISTEMAS QHSE
    // ===================================================
    Route::prefix('qhse')
        ->middleware(['auth', 'check.permission:qhse']) // Agregamos 'auth' aquí por seguridad global
        ->group(function () {
            // ===================================================
            // GRUPO GERENCIAMIENTO DE VIAJES
            // Prefijo: /qhse/gerenciamiento
            // ===================================================
            Route::prefix('gerenciamiento')->group(function () {
                // 1. Redirección automática
                Route::get('/', function () {
                    return redirect()->route('gerenciamiento.journey');
                })
                    ->name('qhse.gerenciamiento')
                    ->middleware('check.permission:qhse,gerenciamiento');

                // ---------------------------------------------------
                // 2. VISTAS Y CARGA DE DATOS (Dropdowns, catálogos)
                // Controlador: JourneyController
                // ---------------------------------------------------
                Route::controller(JourneyController::class)->group(function () {
                    Route::get('/journey', 'index')->name('gerenciamiento.journey');
                    Route::get('/employees', 'getEmployees')->name('gerenciamiento.empleados');
                    Route::get('/get-destinations', 'getDestinations')->name('gerenciamiento.destinations');
                    Route::get('/conductores', 'getConductores')->name('gerenciamiento.conductores');
                    Route::get('/vehicles', 'getVehicles')->name('gerenciamiento.vehicles');
                    Route::get('/autorizadores/{nivel}', 'getAutorizadores')->name('gerenciamiento.autorizadores');
                });

                // ---------------------------------------------------
                // 3. GUARDADO DE NUEVO VIAJE (Transaccional)
                // Controlador: JourneyStoreController
                // ---------------------------------------------------
                // IMPORTANTE: Esta es la ruta que llama el fetch() en JS
                Route::post('/journeys/store', [JourneyStoreController::class, 'store'])
                    ->name('gerenciamiento.store');

                // ---------------------------------------------------
                // 4. CONSULTAS Y ESTADÍSTICAS (Tablas y Dashboard)
                // Controlador: JourneyQueryController
                // ---------------------------------------------------
                Route::controller(JourneyQueryController::class)->group(function () {
                    Route::get('/journeys', 'index')->name('gerenciamiento.list');
                    // 👇 AQUÍ ESTÁ EL CAMBIO: Le agregamos /journeys/ antes de stats
                    Route::get('/journeys/stats', 'getStats')->name('gerenciamiento.journeys.stats');
                    Route::get('/journeys/next-folio', 'getNextFolio')->name('gerenciamiento.next-folio');
                    Route::get('/journeys/last-inspection/{economic_number}', 'getLastInspectionDate')->name('gerenciamiento.last-inspection');
                    Route::get('/destinations', 'getDestinations')->name('gerenciamiento.destinations');
                    Route::get('/journeys/{id}', 'show')->name('gerenciamiento.show');
                });


                // ---------------------------------------------------
                // 5. ACTUALIZACIÓN DE ESTADOS Y BITÁCORA EN RUTA
                // Controlador: JourneyStatusController
                // ---------------------------------------------------
                Route::controller(JourneyStatusController::class)->group(function () {
                    Route::put('/journeys/{id}/approval-status', 'updateApprovalStatus')->name('gerenciamiento.approval_status');
                    Route::put('/journeys/{id}/journey-status', 'updateJourneyStatus')->name('gerenciamiento.journey_status');
                    Route::post('/journeys/{id}/log-event', 'logEvent')->name('gerenciamiento.log_event');
                });

                // ---------------------------------------------------
                // 6. GESTIÓN DE LICENCIAS Y CREDENCIALES
                // Controlador: DriverLicenseController
                // ---------------------------------------------------
                Route::controller(DriverLicenseController::class)->group(function () {
                    // Ruta para ver la tabla (la que pusimos en el nav)
                    Route::get('/driver_licenses', 'index')->name('gerenciamiento.licenses');

                    // Ruta POST para guardar los datos desde el modal (AJAX)
                    Route::post('/empleados/{id}/actualizar-licencias', 'updateLicenses')->name('gerenciamiento.update_licenses');
                });

                // ---------------------------------------------------
                // 7. DASHBOARD Y ESTADÍSTICAS
                // Controlador: StatsController
                // ---------------------------------------------------
                Route::controller(StatsController::class)->group(function () {
                    // Usamos el método 'index' porque es la vista principal de este controlador
                    Route::get('/stats', 'index')->name('gerenciamiento.stats');
                });
            });
        });

    // ===================================================
    // MÓDULO: RECURSOS HUMANOS Y SUBSISTEMAS
    // ===================================================
    Route::prefix('recursoshumanos')
        ->middleware('check.permission:recursoshumanos')
        ->group(function () {
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
                Route::controller(HistoryController::class)->group(function () {
                    // Ruta para cargar la vista inicial (GET)
                    Route::get('/history', 'index')->name('loadchart.history');

                    // Ruta para cargar los datos del historial con filtros y paginación (GET, para AJAX)
                    // Nota: El prefijo de URL debe coincidir con la configuración de tu grupo de rutas.
                    Route::get('/history/data', 'getHistoryData')->name('loadchart.history.data');
                });

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

                Route::prefix('employee_vacation_balance')->controller(EmployeeVacationBalanceController::class)->group(function () {
                    Route::get('/', 'index')->name('vacation_balance.index');
                    Route::post('/', 'store');
                    Route::get('/{id}/edit', 'edit');
                    Route::put('/{id}', 'update');
                    Route::delete('/{id}', 'destroy');
                    Route::post('/force-update-years', 'forceUpdateYears');
                    // 🥇 NUEVA RUTA PARA GENERAR EL REPORTE
                    Route::post('/generate-report', 'generateReport')->name('vacation_balance.generate_report');
                });

                Route::get('/stats', function () {
                    return view('modulos.recursoshumanos.sistemas.loadchart.stats');
                })->name('loadchart.stats');
            });

            // Subsistemas de RRHH (Rutas que no son de LoadChart)
            Route::get('/altasempleados', function () {
                return view('modulos.recursoshumanos.sistemas.altasempleados.index');
            })
                ->middleware('check.permission:recursoshumanos,altasempleados')
                ->name('recursoshumanos.altasempleados');
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

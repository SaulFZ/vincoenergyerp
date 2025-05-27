
<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Sistemas\RoleController;
use Illuminate\Support\Facades\Route;

// ===================================================
// RUTAS DE AUTENTICACIÓN
// ===================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirección de la página principal según estado de autenticación
Route::get('/', function () {
    return session()->has('auth_user')
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
Route::middleware(['web', 'auth.session'])->group(function () {
    // Ruta principal del panel
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    // Componente de pnatalla de carga
Route::get('/transition', function () {
    return view('components.ui.transition');
})->name('transition');

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
    Route::prefix('qhse')->middleware('check.permission:qhse')->group(function () {
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
            return view('modulos.qhse.sistemas.tacisqhse.index');
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
    Route::prefix('recursoshumanos')->middleware('check.permission:recursoshumanos')->group(function () {
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

        Route::get('/loandchart', function () {
            return view('modulos.recursoshumanos.sistemas.loandchart.index');
        })
            ->middleware('check.permission:recursoshumanos,loandchart')
            ->name('recursoshumanos.loandchart');
    });

    // ===================================================
    // MÓDULO: SISTEMAS Y SUBSISTEMAS
    // ===================================================
    Route::prefix('sistemas')->middleware('check.permission:sistemas')->group(function () {
        // Página principal Sistemas
        Route::get('/', function () {
            return view('modulos.sistemas.sistemashome');
        })->name('modulo.sistemas');

        // Gestión de roles
        Route::get('/gestionderoles', function () {
            return view('modulos.sistemas.sistemas.gestionderoles.index');
        })
            ->middleware('check.permission:sistemas,gestionderoles')
            ->name('sistemas.gestionderoles');

        // CRUD de roles (RoleController)
        Route::resource('roles', RoleController::class)
            ->except(['show'])
            ->names([
                'index' => 'sistemas.roles.index',
                'create' => 'sistemas.roles.create',
                'store' => 'sistemas.roles.store',
                'edit' => 'sistemas.roles.edit',
                'update' => 'sistemas.roles.update',
                'destroy' => 'sistemas.roles.destroy',
            ])
            ->middleware('check.permission:sistemas,gestionderoles');
    });

    // ===================================================
    // MÓDULO: VENTAS Y SUBSISTEMAS
    // ===================================================
    Route::prefix('ventas')->middleware('check.permission:ventas')->group(function () {
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
    Route::prefix('geociencias')->middleware('check.permission:geociencias')->group(function () {
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

    // ===================================================
    // API DE VERIFICACIÓN DE PERMISOS
    // ===================================================
    Route::get('/api/check-permission/{module}/{permission?}', function ($module, $permission = 'general') {
        if (!session()->has('auth_user')) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $userId = session('auth_user.id');

        if ($permission === 'general') {
            if (!\App\Models\Sistemas\UserPermission::hasModuleAccess($userId, $module)) {
                return response()->json(['message' => 'Sin permiso al módulo.'], 403);
            }
        } else {
            if (!\App\Models\Sistemas\UserPermission::hasPermission($userId, $module, $permission)) {
                return response()->json(['message' => 'Sin permiso al sistema.'], 403);
            }
        }

        return response()->json(['message' => 'Permiso concedido.']);
    });
});



@extends('modules.rh.altas.index')

@push('styles')
    <link href="{{ asset('assets/css/rh/altas/index.css') }}" rel="stylesheet">
@endpush
<style>/* Layout Principal */
.employees-dashboard {
    padding: 10px;
    animation: fadeIn 0.4s ease-in-out;
}

.page-title {
    color: var(--slate-dark);
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.page-subtitle {
    color: var(--slate-medium);
    font-size: 0.9rem;
    margin-bottom: 25px;
}

.employees-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 25px;
}

/* Tarjetas (Cards) con fondo blanco puro */
.custom-card {
    background-color: var(--surface);
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0,0,0,0.05);
    overflow: hidden;
}

.card-header-custom {
    background-color: var(--slate-dark);
    color: var(--surface);
    padding: 15px 20px;
}

.card-header-custom h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.card-body-custom {
    padding: 20px;
}

/* Formulario */
.form-group {
    margin-bottom: 18px;
}

.form-row {
    display: flex;
    gap: 15px;
}

.half-width {
    width: 50%;
}

.form-group label {
    display: block;
    color: var(--slate-dark);
    font-weight: 600;
    font-size: 0.85rem;
    margin-bottom: 8px;
}

.custom-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background-color: var(--surface-alt);
    color: var(--slate-dark);
    font-size: 0.9rem;
    transition: all 0.3s;
}

.custom-input:focus {
    outline: none;
    border-color: var(--teal-medium);
    background-color: var(--surface);
    box-shadow: 0 0 0 3px var(--teal-light);
}

.btn-submit {
    width: 100%;
    padding: 12px;
    background-color: var(--teal-medium);
    color: var(--surface);
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.1s;
    margin-top: 10px;
}

.btn-submit:hover {
    background-color: var(--teal-dark);
}

.btn-submit:active {
    transform: scale(0.98);
}

/* Tabla */
.table-header {
    background-color: var(--surface);
    color: var(--slate-dark);
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.search-wrapper {
    position: relative;
}

.search-wrapper i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--slate-medium);
}

.search-wrapper input {
    padding: 8px 10px 8px 30px;
    border: 1px solid #cbd5e1;
    border-radius: 20px;
    font-size: 0.85rem;
    width: 200px;
}

.table-container {
    overflow-x: auto;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
}

.custom-table th {
    background-color: var(--surface-alt);
    color: var(--slate-medium);
    font-weight: 600;
    padding: 12px 20px;
    text-align: left;
    font-size: 0.85rem;
    text-transform: uppercase;
}

.custom-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f5f9;
    color: var(--slate-dark);
}

.employee-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar {
    width: 38px;
    height: 38px;
    background-color: var(--teal-light);
    color: var(--teal-dark);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.details {
    display: flex;
    flex-direction: column;
}

.details .name {
    font-weight: 600;
}

.details .id {
    font-size: 0.75rem;
    color: var(--slate-medium);
}

.status-badge.active {
    background-color: #dcfce7;
    color: #166534;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-action {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    padding: 5px;
    border-radius: 4px;
    transition: 0.2s;
}

.btn-action.edit { color: var(--slate-medium); }
.btn-action.edit:hover { color: var(--teal-medium); background-color: var(--teal-light); }

.btn-action.delete { color: #ef4444; }
.btn-action.delete:hover { background-color: #fee2e2; }

/* Responsive para que en móviles el formulario quede arriba y la tabla abajo */
@media (max-width: 992px) {
    .employees-grid {
        grid-template-columns: 1fr;
    }
}</style>
@section('content')
<div class="employees-dashboard">
    <div class="page-header">
        <h2 class="page-title"><i class="fas fa-users-cog"></i> Gestión de Personal</h2>
        <p class="page-subtitle">Registra nuevos ingresos y administra el directorio de empleados.</p>
    </div>

    <div class="employees-grid">
        <div class="form-section">
            <div class="custom-card form-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-user-plus"></i> Nuevo Registro</h5>
                </div>
                <div class="card-body-custom">
                    <form id="employeeForm" >
                        @csrf
                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="full_name" class="custom-input" placeholder="Ej: Carlos Mendoza" required>
                        </div>

                        <div class="form-group">
                            <label>Correo Corporativo</label>
                            <input type="email" name="email" class="custom-input" placeholder="carlos@vincoenergy.com" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group half-width">
                                <label>Puesto</label>
                                <input type="text" name="job_position" class="custom-input" placeholder="Ej: Ingeniero" required>
                            </div>
                            <div class="form-group half-width">
                                <label>Fecha Ingreso</label>
                                <input type="date" name="hiring_date" class="custom-input" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Guardar Empleado
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-section">
            <div class="custom-card">
                <div class="card-header-custom table-header">
                    <h5>Directorio Activo</h5>
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar empleado...">
                    </div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Puesto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="employee-info">
                                        <div class="avatar">CM</div>
                                        <div class="details">
                                            <span class="name">Carlos Mendoza</span>
                                            <span class="id">ID: VE-1042</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Supervisión</td>
                                <td><span class="status-badge active">Activo</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action edit" title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="btn-action delete" title="Dar de baja"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

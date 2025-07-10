@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <!-- Tarjeta contenedora con buscador y botón agregar -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <!-- Header con buscador a la izquierda y botón a la derecha -->
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Buscador más largo -->
                <div class="search-box">
                    <div class="input-group">
                        <input type="text" class="form-control border-end-0" placeholder="Buscar usuarios..." aria-label="Buscar">
                        <span class="input-group-text bg-white border-start-0">
                            <i class="fas fa-search text-secondary"></i>
                        </span>
                    </div>
                </div>

                <!-- Botón Agregar con nuevo estilo -->
                <a href="#" class="add-btn text-primary">
                    <i class="fas fa-plus me-2"></i>
                    <span>Agregar nuevo</span>
                </a>
            </div>
        </div>

        <!-- Cuerpo con tabla -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">ID</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">Nombre</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">Email</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">Estado</th>
                            <th class="fw-bold text-secondary text-uppercase border-light-subtle">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-light-subtle">
                            <td class="py-3 small">001</td>
                            <td class="py-3 small">Ana López</td>
                            <td class="py-3 small">ana@example.com</td>
                            <td class="py-3">
                                <span class="badge-status bg-success bg-opacity-10 text-success">Activo</span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex">
                                    <a href="#" class="action-btn text-primary me-3">
                                        <i class="fas fa-edit"></i>
                                        <span class="ms-2">Actualizar</span>
                                    </a>
                                    <a href="#" class="action-btn text-danger">
                                        <i class="fas fa-trash"></i>
                                        <span class="ms-2">Eliminar</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-light-subtle">
                            <td class="py-3 small">002</td>
                            <td class="py-3 small">Carlos Ruiz</td>
                            <td class="py-3 small">carlos@example.com</td>
                            <td class="py-3">
                                <span class="badge-status bg-warning bg-opacity-10 text-warning">Pendiente</span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex">
                                    <a href="#" class="action-btn text-primary me-3">
                                        <i class="fas fa-edit"></i>
                                        <span class="ms-2">Actualizar</span>
                                    </a>
                                    <a href="#" class="action-btn text-danger">
                                        <i class="fas fa-trash"></i>
                                        <span class="ms-2">Eliminar</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-light-subtle">
                            <td class="py-3 small">003</td>
                            <td class="py-3 small">María González</td>
                            <td class="py-3 small">maria@example.com</td>
                            <td class="py-3">
                                <span class="badge-status bg-danger bg-opacity-10 text-danger">Inactivo</span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex">
                                    <a href="#" class="action-btn text-primary me-3">
                                        <i class="fas fa-edit"></i>
                                        <span class="ms-2">Actualizar</span>
                                    </a>
                                    <a href="#" class="action-btn text-danger">
                                        <i class="fas fa-trash"></i>
                                        <span class="ms-2">Eliminar</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- Fuente Google Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        /* Aplicar fuente a todo el sistema */
        body, .main-header, .main-sidebar, .content-wrapper,
        .card, .btn, .form-control, .table, h1, h2, h3, h4, h5, h6 {
            font-family: 'Roboto', sans-serif !important;
        }

        /* Estilos adicionales para la tabla */
        .border-light-subtle {
            border-color: #f0f0f0 !important;
        }

        .table-hover tbody tr:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        .rounded-3 {
            border-radius: 12px !important;
        }

        /* Mejoras en jerarquía tipográfica */
        .table thead th {
            font-weight: 700 !important;
            font-size: 0.85rem;
        }

        .table tbody td {
            font-size: 0.875rem;
        }

        /* Buscador sin contorno azul */
        .search-box .input-group {
            width: 380px;
        }

        .search-box .form-control {
            border-radius: 20px 0 0 20px !important;
            border-right: none;
            padding: 0.5rem 1.5rem;
            height: 42px;
            font-size: 0.95rem;
        }

        .search-box .input-group-text {
            border-radius: 0 20px 20px 0 !important;
            border-left: none;
            background-color: white;
            padding: 0 1.25rem;
            font-size: 1rem;
        }

        /* Quitar contorno azul al enfocar */
        .search-box .form-control:focus {
            border-color: #dee2e6 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        /* Badges simétricos */
        .badge-status {
            display: inline-block;
            min-width: 90px;
            padding: 0.5em 0.75em;
            text-align: center;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Botones de acción */
        .action-btn {
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none !important;
            transition: all 0.2s ease;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
        }

        .action-btn i {
            font-size: 0.9rem;
            transition: transform 0.2s ease;
        }

        .action-btn:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .action-btn:hover i {
            transform: scale(1.1);
        }

        .action-btn.text-primary:hover {
            color: #0d62c9 !important;
        }

        .action-btn.text-danger:hover {
            color: #c21807 !important;
        }

        /* Botón Agregar sin animación */
        .add-btn {
            display: inline-flex;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none !important;
            transition: all 0.2s ease;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            color: #1a73e8 !important;
        }

        .add-btn:hover {
            background-color: rgba(26, 115, 232, 0.08);
            color: #0d62c9 !important;
        }
    </style>
@stop

@section('js')
    <script>
        // Efecto hover suave para las filas
        document.querySelectorAll('.table-hover tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.2s ease';
                this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.05)';
                this.style.transform = 'translateY(-1px)';
            });

            row.addEventListener('mouseleave', function() {
                this.style.boxShadow = 'none';
                this.style.transform = 'translateY(0)';
            });
        });

        // Foco automático al buscador
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-box .form-control');
            if (searchInput) {
                searchInput.focus();
            }
        });
    </script>
@stop

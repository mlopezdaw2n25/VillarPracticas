<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <title>Panel de Administración</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/estadisticas.css') }}">

</head>

<body>

    <!-- ================= NAVBAR SUPERIOR ================= -->
    <header class="navbar">
        <div class="navbar-container">
            <div class="logo">
                <img src="{{ asset('img/image.png') }}" alt="Centre Villar">
            </div>

            <nav class="menu">
                <a href="https://centrevillar.com/el-centro/">El Centro</a>
                <a href="https://centrevillar.com/proyecto-educativo/">Proyecto Educativo</a>
                <a href="https://centrevillar.com/secretaria-es/">Secretaría</a>
                <a href="https://centrevillar.com/contacto/">Contacto</a>
            </nav>
        </div>
    </header>

    <div class="layout">

        <!-- ===== SIDEBAR / BOTTOM BAR ===== -->
        <aside class="side-navbar">
            <h3>Gestion</h3>
            <a href="{{ url('/Admin/gestion/usuarios') }}">
                <button class="navbtn" type="button">Usuarios</button>
            </a>
            <a href="{{ url('/Admin/gestion/materiales') }}">
                <button class="navbtn" type="button">Material</button>
            </a>
            <a href="{{ url('/Admin/gestion/espacios') }}">
                <button class="navbtn" type="button">Espacios</button>
            </a>
            <a href="{{ url('/Admin/gestion/reservas') }}">
                <button class="navbtn" type="button">Reservas</button>
            </a>

            <h3 style="margin-top: 1.2rem;">Averias</h3>
            <a href="{{ url('/Admin/averias/material') }}">
                <button class="navbtn" type="button">Material</button>
            </a>

            <h3 style="margin-top: 1.2rem;">Estadisticas</h3>
            <a href="{{ url('/Admin/estadisticas/espacios') }}">
                <button class="navbtn" type="button">Espacio</button>
            </a>
            <a href="{{ url('/Admin/estadisticas/material') }}">
                <button class="navbtn active" type="button">Material</button>
            </a>

            <div class="bottom-actions">
                <a href="{{ route('logout') }}">
                    <button class="logout" type="button">Cerrar sesión</button>
                </a>
            </div>
        </aside>

        <!-- ===== CONTENIDO PRINCIPAL ===== -->
        <main class="content">
            <section class="panel">

                <div class="panel-header">
                    <div class="panel-title">
                        <h2>Estadisticas de material</h2>
                        <p>Boceto inicial con indicadores clave de uso y estado del material.</p>
                    </div>

                    <div class="toolbar-right">
                        <button class="btn btn-outline" type="button" disabled>Exportar</button>
                    </div>
                </div>

                <div class="panel-body">

                    <div class="kpi-grid">
                        <article class="kpi-card">
                            <p class="kpi-label">Material total</p>
                            <p class="kpi-value">124</p>
                            <p class="kpi-help">Inventario registrado</p>
                        </article>
                        <article class="kpi-card">
                            <p class="kpi-label">En uso hoy</p>
                            <p class="kpi-value">37</p>
                            <p class="kpi-help">Prestamos activos</p>
                        </article>
                        <article class="kpi-card">
                            <p class="kpi-label">Disponible</p>
                            <p class="kpi-value">74</p>
                            <p class="kpi-help">Listo para reservar</p>
                        </article>
                        <article class="kpi-card">
                            <p class="kpi-label">Incidencias</p>
                            <p class="kpi-value">13</p>
                            <p class="kpi-help">Pendiente de revision</p>
                        </article>
                    </div>

                    <div class="stats-layout">
                        <section class="card chart-card">
                            <div class="card-head">
                                <h3>Uso por tipo de material</h3>
                            </div>
                            <div class="card-body">
                                <div class="bar-list">
                                    <div class="bar-row">
                                        <span>Camaras</span>
                                        <div class="bar-track"><div class="bar-fill" style="width: 82%;"></div></div>
                                        <strong>82%</strong>
                                    </div>
                                    <div class="bar-row">
                                        <span>Portatiles</span>
                                        <div class="bar-track"><div class="bar-fill" style="width: 69%;"></div></div>
                                        <strong>69%</strong>
                                    </div>
                                    <div class="bar-row">
                                        <span>Audio</span>
                                        <div class="bar-track"><div class="bar-fill" style="width: 55%;"></div></div>
                                        <strong>55%</strong>
                                    </div>
                                    <div class="bar-row">
                                        <span>Iluminacion</span>
                                        <div class="bar-track"><div class="bar-fill" style="width: 47%;"></div></div>
                                        <strong>47%</strong>
                                    </div>
                                    <div class="bar-row">
                                        <span>Accesorios</span>
                                        <div class="bar-track"><div class="bar-fill" style="width: 33%;"></div></div>
                                        <strong>33%</strong>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="card side-metrics">
                            <div class="card-head">
                                <h3>Estado actual</h3>
                            </div>
                            <div class="card-body">
                                <ul class="metric-list">
                                    <li><span>Activo</span><strong>93</strong></li>
                                    <li><span>En mantenimiento</span><strong>18</strong></li>
                                    <li><span>Fuera de servicio</span><strong>13</strong></li>
                                </ul>
                                <div class="divider"></div>
                                <p class="muted-note">Boceto visual: estos datos son de ejemplo hasta conectar con base de datos.</p>
                            </div>
                        </section>
                    </div>

                    <section class="card table-card">
                        <div class="card-head">
                            <h3>Top material mas reservado (ultimo mes)</h3>
                        </div>
                        <div class="card-body">
                            <div style="overflow:auto; max-height: 360px;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Material</th>
                                            <th>Tipo</th>
                                            <th>Reservas</th>
                                            <th>Ultimo uso</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Camara Sony A7 III</td>
                                            <td>Camara</td>
                                            <td>26</td>
                                            <td>Hace 2 horas</td>
                                            <td><span class="pill ok">Activo</span></td>
                                        </tr>
                                        <tr>
                                            <td>Portatil Dell 5420</td>
                                            <td>Portatil</td>
                                            <td>22</td>
                                            <td>Hoy</td>
                                            <td><span class="pill ok">Activo</span></td>
                                        </tr>
                                        <tr>
                                            <td>Microfono Rode NT1</td>
                                            <td>Audio</td>
                                            <td>17</td>
                                            <td>Ayer</td>
                                            <td><span class="pill warn">Revision</span></td>
                                        </tr>
                                        <tr>
                                            <td>Foco LED Godox</td>
                                            <td>Iluminacion</td>
                                            <td>14</td>
                                            <td>Hace 3 dias</td>
                                            <td><span class="pill ok">Activo</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        </main>
    </div>

</body>

</html>

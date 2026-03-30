<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <title>Panel de Administración</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/gestion.css') }}">

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
                <button class="navbtn active" type="button">Espacios</button>
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
                <button class="navbtn" type="button">Material</button>
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
                        <h2>Espacios</h2>
                        <p>Gestión de espacios</p>
                    </div>

                    <div class="toolbar-right">

                    </div>
                </div>

                <div class="panel-body">

                    <!-- SEARCH -->
                    <div class="toolbar">
                        <div class="search">
                            <span>Buscar</span>
                            <input type="text" placeholder="Buscar por nombre o ID">
                        </div>
                    </div>

                    <!-- ESPACIOS -->
                    <div class="view" id="view-espacios">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Lista de espacios</h3>
                                </div>
                                <div class="card-body">
                                    <div style="overflow:auto; max-height: 430px;">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nombre</th>
                                                    <th>Cantidad</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                 @foreach($espacios as $espacio)
                                                    <tr>
                                                        <td>{{ $espacio['id'] }}</td>
                                                        <td>{{ $espacio['name'] }}</td>
                                                        <td>{{ $espacio['total_units'] }}</td>
                                                        <td>{{ $espacio['active'] }}</td>
                                                    </tr>
                                                 @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-head">
                                    <h3>Crear espacio</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="/Admin/gestion/espacios">
                                        @csrf
                                        @method('POST')
                                        <div class="form">
                                            <div>
                                                <label for="eNombre">Nombre</label>
                                                <input id="eNombre" name="nom" placeholder="Ej: Aula 109">
                                            </div>
                                        </div>

                                        <div class="divider"></div>
                                        <button class="btn" type="submit">Crear espacio</button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

</body>

</html>

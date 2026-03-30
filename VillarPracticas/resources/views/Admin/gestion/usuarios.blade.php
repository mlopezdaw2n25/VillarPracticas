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
                <button class="navbtn active" type="button">Usuarios</button>
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
                        <h2>Usuarios</h2>
                        <p>Gestión de usuarios</p>
                    </div>

                    <div class="toolbar-right">

                    </div>
                </div>

                <div class="panel-body">

                    <!-- SEARCH -->
                    <div class="toolbar">
                        <div class="search">
                            <span>Buscar</span>
                            <input type="text" placeholder="Buscar por nombre">
                            <button class="btn" type="submit">Buscar</button>
                        </div>
                    </div>

                    <!-- USUARIOS -->
                    <div class="view" id="view-usuarios">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Lista de usuarios</h3>
                                </div>
                                <div class="card-body">
                                    <div style="overflow:auto; max-height: 430px;">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nombre</th>
                                                    <th>Email</th>
                                                    <th>Rol</th>
                                                    <th>Activo</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($usuaris as $usuari)
                                                    <tr>
                                                        <td>{{ $usuari['id'] }}</td>
                                                        <td>{{ $usuari['nom'] }}</td>
                                                        <td>{{ $usuari['email'] }}</td>
                                                        <td>{{ $usuari['rol'] }}</td>
                                                        <td>{{ $usuari['activo'] }}</td>
                                                        <td>
                                                            @if ($usuari['activo'] === 'si')
                                                                <form method="POST" action="#"
                                                                    style="display:inline;">
                                                                    @csrf
                                                                    @method('PAUSAR')
                                                                    <button type="submit"
                                                                        class="btn-action btn-pause">Pausar</button>
                                                                </form>
                                                            @else
                                                                <form method="POST" action="#"
                                                                    style="display:inline;">
                                                                    @csrf
                                                                    @method('REACTIVAR')
                                                                    <button type="submit"
                                                                        class="btn-action btn-reactivate">Reactivar</button>
                                                                </form>
                                                            @endif

                                                            <form method="POST" action="#" style="display:inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn-action btn-delete">Eliminar</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-head">
                                    <h3>Crear usuario</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="/Admin/gestion/usuarios">
                                        @csrf
                                        @method('POST')
                                        <div class="form">
                                            <div>
                                                <label for="uNombre">Nombre</label>
                                                <input id="uNombre" name="nom" placeholder="Ej: Albert Villar">
                                            </div>

                                            <div>
                                                <label for="uEmail">Email</label>
                                                <input id="uEmail" name="email"
                                                    placeholder="Ej: user@centrevillar.com">
                                            </div>

                                            <div>
                                                <label for="rol">Rol</label>
                                                <select id="rol" name="rol">
                                                    <option value="Profesor">Profesor</option>
                                                    <option value="Admin">Admin</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label for="password">Contraseña</label>
                                                <input id="password" name="contrasenya"
                                                    placeholder="Ej: Contrasenya123">
                                            </div>


                                        </div>

                                        <div class="divider"></div>
                                        <button class="btn" type="submit">Crear usuario</button>
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

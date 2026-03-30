<!DOCTYPE html>
<html lang="es">

<head>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <title>Panel de Administración</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>

<body>

    <!-- NAVBAR TOP -->
    <header class="navbar">
        <div class="navbar-container">
            <div class="logo">
            <img src="{{ asset('img/image.png') }}" alt="Centre Villar">
                <span>Administración</span>
            </div>

            <div class="right-actions">
                <div class="chip">Admin</div>
                <a href="{{ route('logout') }}">
                    <button class="btn btn-outline">Cerrar sesión</button>
                </a>
            </div>
        </div>
    </header>

    <div class="layout">

        <!-- SIDEBAR -->
        <aside class="side-navbar">
            <h3>Gestión</h3>
            <button class="navbtn active" data-view="usuarios">Usuarios</button>
            <button class="navbtn" data-view="espacios">Espacios</button>
            <button class="navbtn" data-view="materiales">Materiales</button>
            <button class="navbtn" data-view="reservas">Reservas</button>

            <h3 style="margin-top:1.2rem;">Averías</h3>
            <button class="navbtn" data-view="averiasMaterial">Averías material</button>
            <button class="navbtn" data-view="averiasIT">Averías informáticas</button>

            <h3 style="margin-top:1.2rem;">Informes</h3>
            <button class="navbtn" data-view="statsAveriasIT">Estadísticas IT</button>
            <button class="navbtn" data-view="statsEspacios">Estadísticas espacios</button>
            <button class="navbtn" data-view="statsMaterial">Estadísticas material</button>

            <h3 style="margin-top:1.2rem;">Configuración</h3>
            <button class="navbtn" data-view="desplegables">Desplegables y formularios</button>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="content">
            <section class="panel">

                <div class="panel-header">
                    <div class="panel-title">
                        <h2 id="viewTitle">Usuarios</h2>
                        <p id="viewSubtitle">
                            Gestión de usuarios: alta, pausa y eliminación.
                        </p>
                    </div>

                    <div class="toolbar-right">
                        <button class="btn btn-outline" id="btnRefresh">Recargar</button>
                        <button class="btn" id="btnPrimaryAction">Crear</button>
                    </div>
                </div>

                <div class="panel-body">

                    <!-- SEARCH -->
                    <div class="toolbar">
                        <div class="search">
                            <span>Buscar</span>
                            <input id="searchInput" type="text" placeholder="Buscar... (id, nombre, estado, etc.)">
                        </div>
                        <div class="muted" id="countHint">—</div>
                    </div>

                    <!-- VIEWS -->

                    <!-- USUARIOS -->
                    <div class="view" id="view-usuarios">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Lista de usuarios</h3>
                                </div>
                                <div class="card-body">
                                    <div style="overflow:auto; max-height: 430px;">
                                        <table id="tblUsuarios"></table>
                                    </div>
                                </div>
                            </div>

                            <div class="card" id="cardTresFilas">
                                <div class="card-head">
                                    <h3>Crear usuario</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form">
                                        <div>
                                            <label>Nombre</label>
                                            <input id="uNombre" placeholder="Ej: Albert Canela">
                                        </div>
                                        <div>
                                            <label>Email</label>
                                            <input id="uEmail" placeholder="Ej: albert@centrevillar.com">
                                        </div>
                                        <div>
                                            <label>Rol</label>
                                            <select id="uRol">
                                                <option>Profesor</option>
                                                <option>Administrador</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="divider"></div>
                                    <button class="btn" onclick="crearUsuario()">Crear usuario</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ESPACIOS -->
                    <div class="view hidden" id="view-espacios">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Lista de espacios</h3>
                                </div>
                                <div class="card-body">
                                    <div style="overflow:auto; max-height: 430px;">
                                        <table id="tblEspacios"></table>
                                    </div>
                                </div>
                            </div>

                            <div class="card" id="cardCrearEspacio">
                                <div class="card-head">
                                    <h3>Crear espacio</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form">
                                        <div>
                                            <label>Nombre</label>
                                            <input id="eNombre" placeholder="Ej: Aula 109">
                                        </div>

                                        <div>
                                            <label>Descripcion</label>
                                            <input id="eDesc" placeholder="Ej: Plató con escenario">
                                        </div>

                                        <div>
                                            <label>Capacidad</label>
                                            <input id="eCapacidad" type="number" min="1"
                                                placeholder="Ej: 30">
                                        </div>

                                        <div>
                                            <label>Ubicación</label>
                                            <input id="eUbicacion" placeholder="Ej: Primera planta">
                                        </div>
                                    </div>

                                    <div class="divider"></div>
                                    <button class="btn" onclick="crearEspacio()">Crear espacio</button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- MATERIALES -->
                    <div class="view hidden" id="view-materiales">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Lista de materiales</h3>
                                </div>
                                <div class="card-body">
                                    <div style="overflow:auto; max-height: 430px;">
                                        <table id="tblMateriales"></table>
                                    </div>
                                </div>
                            </div>

                            <div class="card" id="cardCrearMaterial">
                                <div class="card-head">
                                    <h3>Crear material</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form">
                                        <div>
                                            <label>Nombre</label>
                                            <input id="mNombre" placeholder="Ej: Portátil ASUS #12">
                                        </div>
                                        <div>
                                            <label>Tipo</label>
                                            <select id="mTipo">
                                                <option>Portátil</option>
                                                <option>Tablet</option>
                                                <option>Proyector</option>
                                                <option>Microscopio</option>
                                                <option>Altavoces</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label>Ubicación</label>
                                            <input id="mUbicacion" placeholder="Ej: Aula 2.3">
                                        </div>
                                    </div>
                                    <div class="divider"></div>
                                    <button class="btn" onclick="crearMaterial()">Crear material</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RESERVAS -->
                    <div class="view hidden" id="view-reservas">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Reservas</h3>
                                    <span class="muted">Acciones: eliminar</span>
                                </div>
                                <div class="card-body">
                                    <div style="overflow:auto; max-height: 520px;">
                                        <table id="tblReservas"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AVERIAS MATERIAL -->
                    <div class="view hidden" id="view-averiasMaterial">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Averías de material</h3>
                                    <span class="muted">Cambiar estado de incidencia</span>
                                </div>
                                <div class="card-body">
                                    <div style="overflow:auto; max-height: 520px;">
                                        <table id="tblAveriasMaterial"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AVERIAS IT -->
                    <div class="view hidden" id="view-averiasIT">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Averías informáticas</h3>
                                    <span class="muted">Cambiar estado de incidencia</span>
                                </div>
                                <div class="card-body">
                                    <div style="overflow:auto; max-height: 520px;">
                                        <table id="tblAveriasIT"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STATS IT -->
                    <div class="view hidden" id="view-statsAveriasIT">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Informes estadísticos: averías informáticas</h3>
                                    <span class="muted">Vista demo</span>
                                </div>
                                <div class="card-body">
                                    <div class="stats" id="statsIT"></div>
                                    <div class="divider"></div>
                                    <p class="muted">
                                        *(Aquí normalmente irían gráficos tipo Chart.js / ApexCharts conectados a BD.)*
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STATS ESPACIOS -->
                    <div class="view hidden" id="view-statsEspacios">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Informes estadísticos: reservas de espacios</h3>
                                    <span class="muted">Vista demo</span>
                                </div>
                                <div class="card-body">
                                    <div class="stats" id="statsEspacios"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STATS MATERIAL -->
                    <div class="view hidden" id="view-statsMaterial">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Informes estadísticos: reservas de material</h3>
                                    <span class="muted">Vista demo</span>
                                </div>
                                <div class="card-body">
                                    <div class="stats" id="statsMaterial"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DESPLEGABLES -->
                    <div class="view hidden" id="view-desplegables">
                        <div class="grid">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Administrar desplegables opcionales</h3>
                                    <span class="muted">Cursos, grupos, asignaturas...</span>
                                </div>
                                <div class="card-body">
                                    <div class="form">
                                        <div>
                                            <label>Tipo de lista</label>
                                            <select id="ddlTipoLista" onchange="renderDesplegables()">
                                                <option value="cursos">Cursos</option>
                                                <option value="grupos">Grupos</option>
                                                <option value="asignaturas">Asignaturas</option>
                                                <option value="camposExtra">Campos extra formulario</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label>Nuevo valor</label>
                                            <input id="ddlNuevoValor" placeholder="Ej: Edición de vídeo">
                                        </div>
                                    </div>
                                    <div class="divider"></div>
                                    <button class="btn" onclick="addDesplegable()">Añadir</button>

                                    <div class="divider"></div>
                                    <div style="overflow:auto; max-height: 320px;">
                                        <table id="tblDesplegables"></table>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-head">
                                    <h3>Campos de formulario</h3>
                                    <span class="muted">Activar/Desactivar (demo)</span>
                                </div>
                                <div class="card-body">
                                    <div class="muted" style="margin-bottom:.8rem;">
                                        Simula que en ciertos formularios hay campos opcionales.
                                    </div>
                                    <div style="overflow:auto;">
                                        <table id="tblCampos"></table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </section>
        </main>
    </div>
    <script src="{{ asset('js/admin.js') }}"></script>

</body>

</html>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reservar recursos</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/listado.css') }}">
</head>

<body>

    <!-- NAVBAR -->
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

    <!-- LAYOUT -->
    <div class="layout">

        <!-- NAVBAR LATERAL -->
        <aside class="side-navbar">
            <h3>Reserva</h3>

            <a href="{{ url('Profesors/profesor/' . $id) }}">
                <button type="button">⬅ Volver</button>
            </a>

            <a href="{{ route('logout') }}">
                <button type="button">Cerrar sesión</button>
            </a>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="content">
            <section class="calendar-container">

                <!-- LEFT -->
                <div class="calendar-left">
                    <div class="title">
                        <h2>Selecciona materiales / espacios</h2>
                        <p>Elige la cantidad de cada recurso por franja horaria.</p>
                    </div>

                    <div class="selected-info" id="summaryBox">
                        Cargando...
                    </div>

                    <div style="margin-top:1rem;">
                        <button id="btnReserve" class="mini primary" style="width:100%; padding:.9rem;" disabled>
                            Confirmar reserva
                        </button>
                    </div>

                    <div class="resources-gallery" id="resourceGallery">


                    </div>

                    <div id="errorBox" style="margin-top:1rem;color:#b91c1c;font-weight:700;"></div>
                </div>

                <!-- RIGHT -->
                <div class="calendar-right">
                    <div class="title">
                        <h2>Listado</h2>
                        <p>Selecciona recursos para tus franjas.</p>
                    </div>
                    <div
                        style="margin: 12px 0; padding: 10px; background: #fff; border-radius: 12px; border: 1px solid #eee;">
                        <strong>Franjas seleccionadas:</strong>
                        <div id="selectedSlotsBox" style="margin-top:6px; color:#374151;"></div>
                    </div>
                    <div id="resourcesContainer"></div>
                </div>

            </section>
        </main>
    </div>

    <!-- Variables necesarias para JS -->
    <script>
        window.__USER_ID__ = @json($id);
    </script>

    <script src="{{ asset('js/listado.js') }}"></script>
</body>

</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar Hora</title>

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/profe.css') }}">
</head>

<body>

<!-- NAVBAR SUPERIOR -->
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
        <a href="../profesor/{{$id}}">
            <button type="button">Inicio</button>
        </a>

        <a href="{{ route('logout') }}">
            <button type="button">Cerrar sesión</button>
        </a>
    </aside>

    <!-- CONTENIDO -->
    <main class="content">

        <!-- Reutilizamos el card del CSS -->
        <div class="calendar-container">

            <!-- COLUMNA IZQUIERDA -->
            <section class="calendar-left">
                <div class="title">
                    <h2>Reservar Hora</h2>
                    <p>Confirma los datos de tu reserva</p>
                </div>

                <!-- Reutilizamos selected-info -->
                <div class="selected-info">
                    <span><strong>Día:</strong> Lunes</span>
                    <span><strong>Hora:</strong> 09:00 - 10:00</span>
                </div>

                <div style="margin-top:1rem;">
                    <a href="../profesor/{{$id}}">
                        <button type="button" class="mini">← Volver al calendario</button>
                    </a>
                </div>
            </section>

            <!-- COLUMNA DERECHA -->
            <section class="calendar-right">

                <div class="title">
                    <h2>Detalles</h2>
                    <p>Completa la información para reservar</p>
                </div>

                <form>
                    <label>Nombre</label>
                    <input type="text" placeholder="Tu nombre" required style="width:100%; padding:.85rem 1rem; border:1px solid var(--border); border-radius:12px;">

                    <br><br>

                    <label>Plató</label>
                    <select required style="width:100%; padding:.85rem 1rem; border:1px solid var(--border); border-radius:12px; background:#fff;">
                        <option value="">Selecciona un plató</option>
                        <option>Plató 1</option>
                        <option>Plató 2</option>
                        <option>Plató Superior</option>
                    </select>

                    <br><br>

                    <label>Material</label>
                    <select required style="width:100%; padding:.85rem 1rem; border:1px solid var(--border); border-radius:12px; background:#fff;">
                        <option value="">Selecciona material</option>
                        <option>Cámara</option>
                        <option>Micrófonos</option>
                        <option>Cables varios</option>
                    </select>

                    <br><br>

                    <label>Observaciones</label>
                    <textarea placeholder="Opcional" style="width:100%; min-height:120px; padding:.85rem 1rem; border:1px solid var(--border); border-radius:12px;"></textarea>

                    <br><br>

                    <!-- Botón con estilo del CSS -->
                    <button type="submit" class="mini primary" style="width:100%; padding: .85rem;">
                        Confirmar reserva
                    </button>
                </form>

            </section>

        </div>
    </main>

</div>

</body>
</html>

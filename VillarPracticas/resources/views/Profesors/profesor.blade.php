<!DOCTYPE html>
<html lang="es">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">

  <title>Panel Principal</title>

  <!-- Fuente -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- CSS -->
  <link rel="stylesheet" href="{{ asset('css/profe.css') }}">

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

<!-- ================= LAYOUT ================= -->

<div class="layout">

  <!-- ===== SIDEBAR / BOTTOM BAR ===== -->
  <aside class="side-navbar">

    <h3>Panel</h3>

    <button class="navbtn active" type="button">
      Inicio
    </button>

    <div class="bottom-actions">

      <a href="{{ url('Profesors/misreservas/'.$id) }}">
        <button class="navbtn" type="button">
          Mis reservas
        </button>
      </a>

      <a href="{{ route('logout') }}">
        <button class="logout" type="button">
          Cerrar sesión
        </button>
      </a>

    </div>

  </aside>

  <!-- ===== CONTENIDO PRINCIPAL ===== -->

  <main class="content">

    <section class="calendar-container">

      <!-- IZQUIERDA -->
      <div class="calendar-left">

        <div class="title">
          <h2>Reserva una franja horaria</h2>
          <p>Selecciona una fecha y elige una hora disponible.</p>
        </div>

        <div class="date-row">
          <div style="width:100%">
            <label for="datePicker">Calendario</label>
            <input type="date" id="datePicker">
          </div>
        </div>

        <div class="selected-info" id="selectedInfo">
          Selecciona una hora para ver tu reserva aquí.
        </div>

      </div>

      <!-- DERECHA -->
      <div class="calendar-right">

        <div class="slots-header">

          <h3>Horas disponibles</h3>

          <div style="display:flex; gap:.6rem; align-items:center;">
            <span id="slotsHint">—</span>

            <button id="btnContinue" class="mini primary" disabled>
              Hacer reserva
            </button>
          </div>

        </div>

        <div id="slots"></div>

      </div>

    </section>

  </main>

</div>

<!-- ================= JS ================= -->

<script>
  window.__USER_ID__ = @json($id);
</script>

<script src="{{ asset('js/profe.js') }}"></script>

</body>
</html>

<!DOCTYPE html> 
<html lang="es">
<head>
  
  <link rel="stylesheet" href="{{ asset('css/profe.css') }}">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel Principal</title>

  <!-- Fuente -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
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

    <!-- Navbar lateral -->
<aside class="side-navbar">
  <h3>Panel</h3>

  <button class="navbtn active" data-view="inicio">Inicio</button>
  <button class="navbtn" data-view="misreservas">Mis reservas</button>

<a href="{{ route('logout') }}">
    <button type="button">Cerrar sesión</button>
</a>
</aside>


    <!-- Contenido -->
<main class="content">

  <!-- ===================== -->
  <!-- VISTA INICIO (CALENDARIO) -->
  <!-- ===================== -->
  <section class="view" id="view-inicio">
    <section class="calendar-container">

      <!-- LEFT -->
      <div class="calendar-left">
        <div class="title">
          <h2>Reserva una franja horaria</h2>
          <p>Selecciona una fecha y elige una hora disponible.</p>
        </div>

        <div class="date-row">
          <div style="width:100%">
            <label for="datePicker">Calendario</label>
            <input type="date" id="datePicker" />
          </div>
        </div>

        <div class="selected-info" id="selectedInfo">
          Selecciona una hora para ver tu reserva aquí.
        </div>
      </div>

      <!-- RIGHT -->
      <div class="calendar-right">
        <div class="slots-header">
          <h3>Horas disponibles</h3>
          <span id="slotsHint">—</span>
        </div>

        <div id="slots"></div>
      </div>
    </section>
  </section>

  <!-- ===================== -->
  <!-- VISTA MIS RESERVAS -->
  <!-- ===================== -->
  <section class="view hidden" id="view-misreservas" style="width:min(980px,100%);">
    <div style="background: rgba(255,255,255,.98); border-radius: 20px; box-shadow: var(--shadow); overflow:hidden;">
      <div style="padding: 1.6rem 1.8rem 1.2rem; border-bottom: 1px solid var(--border);">
        <h2 style="font-size:1.3rem; font-weight:800;">Mis reservas</h2>
        <p style="color:var(--muted); margin-top:.35rem;">Aquí puedes modificar o anular tus reservas.</p>
      </div>

      <div style="padding: 1.6rem 1.8rem 2rem;">
        <div style="overflow:auto; max-height: 520px;">
          <table style="width:100%; border-collapse:separate; border-spacing:0;" id="tblMisReservas"></table>
        </div>
      </div>
    </div>
  </section>

</main>
  </div>
<div id="modalEdit" class="hidden" style="
  position:fixed; inset:0; background:rgba(0,0,0,.45);
  display:flex; align-items:center; justify-content:center;
  z-index:999;
">
  <div style="background:#fff; width:min(520px,95%); border-radius:20px; padding:1.4rem; box-shadow:var(--shadow);">
    <h3 style="font-size:1.1rem; font-weight:900;">Modificar reserva</h3>
    <p style="color:var(--muted); margin:.35rem 0 1rem;">Cambia la fecha y/o la hora.</p>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:.8rem;">
      <div>
        <label>Nueva fecha</label>
        <input type="date" id="editFecha">
      </div>
      <div>
        <label>Nueva hora</label>
        <select id="editHora"></select>
      </div>
    </div>

    <div style="display:flex; gap:.6rem; justify-content:flex-end; margin-top:1.1rem;">
      <button class="mini" onclick="closeModal()">Cancelar</button>
      <button class="mini primary" onclick="saveEdit()">Guardar</button>
    </div>
  </div>
</div>
<script src="{{ asset('js/profe.js') }}"></script>
</body>
</html>
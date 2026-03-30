<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Reservas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/misreservas.css') }}">
</head>

<body>

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

  <!-- SIDEBAR -->
  <aside class="side-navbar">
    <h3>Panel</h3>

    <a href="{{ url('Profesors/profesor/'.$id) }}">
      <button type="button">Inicio</button>
    </a>

    <button type="button" class="active">Mis reservas</button>

    <a href="{{ route('logout') }}">
      <button type="button">Cerrar sesión</button>
    </a>
  </aside>

  <!-- CONTENIDO -->
  <main class="content">

    <!-- GRID -->
    <div class="reservas-grid">

      <!-- ===================== -->
      <!-- MIS RESERVAS -->
      <!-- ===================== -->
      <div class="reservas-card primary">

        <div class="reservas-card-header">
          <h2>
            Mis reservas
            <span class="badge-main">Principal</span>
          </h2>
          <p>Aquí puedes modificar o anular tus reservas.</p>
        </div>

        <div class="reservas-card-body">
          <div style="overflow:auto;max-height:620px;">
            <table style="width:100%;border-collapse:separate;border-spacing:0;"
                   id="tblMisReservas"></table>
          </div>
        </div>

      </div>

      <!-- ===================== -->
      <!-- TODAS LAS RESERVAS -->
      <!-- ===================== -->
      <div class="reservas-card">

        <div class="reservas-card-header">
          <h2>Todas las reservas</h2>
          <p>Reservas realizadas por todos los usuarios.</p>
        </div>

        <div class="reservas-card-body">
          <div style="overflow:auto;max-height:620px;">
            <table style="width:100%;border-collapse:separate;border-spacing:0;"
                   id="tblAllReservas"></table>
          </div>
        </div>

      </div>

    </div>

  </main>

</div>

<!-- ===================== -->
<!-- MODAL EDITAR -->
<!-- ===================== -->
<div id="modalEdit" style="display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.45);
  align-items:center;
  justify-content:center;
  z-index:999;
  padding:1rem;">

  <div style="background:#fff;
              width:min(560px,95%);
              border-radius:20px;
              padding:1.6rem;
              box-shadow:var(--shadow);
              max-height:90vh;
              overflow-y:auto;">

    <h3 style="font-size:1.1rem;font-weight:800;">Modificar materiales</h3>

    <p style="color:var(--muted);margin-bottom:1rem;">
      Puedes modificar cantidades. El mínimo permitido es 1.
    </p>

    <div id="editErrorBox" style="color:#b91c1c;font-weight:600;margin-bottom:.8rem;"></div>

    <div id="editItemsContainer"></div>

    <div id="editLoading" style="display:none;text-align:center;margin-top:1rem;">
      Guardando cambios...
    </div>

    <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.2rem;">
      <button class="mini" id="btnCancelEdit">Cancelar</button>
      <button class="mini primary" id="btnSaveEdit">Guardar cambios</button>
    </div>

  </div>
</div>

<div id="modalConfirmReturn" style="display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.45);
  align-items:center;
  justify-content:center;
  z-index:999;
  padding:1rem;">

  <div style="background:#fff;
              width:min(520px,95%);
              border-radius:20px;
              padding:1.6rem;
              box-shadow:var(--shadow);
              max-height:90vh;
              overflow-y:auto;">

    <h3 style="font-size:1.1rem;font-weight:800;margin-bottom:.4rem;">
      Confirmar devolución
    </h3>

    <p style="color:var(--muted);margin-bottom:1rem;font-size:.9rem;">
      Indica si la devolución ha tenido alguna incidencia y añade un comentario opcional.
    </p>

    <div style="display:flex;flex-direction:column;gap:.35rem;margin-bottom:1rem;">
      <label style="display:flex;align-items:center;gap:.4rem;font-size:.9rem;">
        <input type="radio" name="returnIncident" id="returnIncidentNo" value="0" checked>
        Sin incidencias
      </label>
      <label style="display:flex;align-items:center;gap:.4rem;font-size:.9rem;">
        <input type="radio" name="returnIncident" id="returnIncidentYes" value="1">
        Con incidencia
      </label>
    </div>

    <textarea id="returnComment"
              placeholder="Comentario (opcional)"
              style="width:100%;
                     min-height:90px;
                     padding:.6rem .75rem;
                     border-radius:12px;
                     border:1px solid var(--border);
                     font-family:inherit;
                     font-size:.9rem;
                     resize:vertical;"></textarea>

    <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:1.2rem;">
      <button class="mini" id="btnCancelReturn">Cancelar</button>
      <button class="mini primary" id="btnConfirmReturn">Confirmar devolución</button>
    </div>

  </div>
</div>


<!-- VARIABLES -->
<script>
  window.__USER_ID__ = @json($id);
</script>

<script src="{{ asset('js/misreservas.js') }}"></script>

</body>
</html>

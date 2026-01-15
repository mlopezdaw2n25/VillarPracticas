  const slotsContainer = document.getElementById("slots");        // Contenedor donde se pintan los botones de horas
  const datePicker = document.getElementById("datePicker");       // Input type="date"
  const selectedInfo = document.getElementById("selectedInfo");   // Texto donde mostramos la reserva seleccionada
  const slotsHint = document.getElementById("slotsHint");         // Texto “Para el día X”
  const logoutBtn = document.getElementById("logoutBtn");         // Botón cerrar sesión

  // DATOS DE EJEMPLO (SIMULAN BD/API)

  const booked = {
    "2026-01-15": ["10:00", "13:30"], //Ejemplo de hora reservada
  };

  // Reservas del usuario (tabla “Mis reservas”)
  let misReservas = [
    { id: 1, fecha: "2026-01-15", hora: "10:00", recurso: "Aula 109", tipo: "Espacio" },
    { id: 2, fecha: "2026-01-18", hora: "12:00", recurso: "Cámara Canon #2", tipo: "Material" },
  ];

  // FUNCIONES PARA HORAS Y CALENDARIO
 function generateSlots() {
  return [
    "08:00",
    "09:00",
    "10:00",
    "11:30",
    "12:30",
    "13:30",
    "14:30",
    "15:00",
    "16:00",
    "17:00",
    "18:00",
    "19:00",
    "20:00"
  ];
}

  // Quita la clase "active" a todos los botones de hora
  function clearActiveSlots() {
    document.querySelectorAll(".slot").forEach(btn => btn.classList.remove("active"));
  }

  // Pinta los botones de hora para una fecha concreta
  function renderSlots(date) {
    const slots = generateSlots();
    const reservedTimes = booked[date] || []; // horas ocupadas ese día (si no hay, array vacío)

    // Limpia el contenedor antes de volver a pintar
    slotsContainer.innerHTML = "";

    // Crear un botón por cada hora
    slots.forEach(time => {
      const btn = document.createElement("button");
      btn.className = "slot";
      btn.textContent = time;

      const isBooked = reservedTimes.includes(time);

      // Si está ocupada, marcamos el botón y lo dejamos sin acción
      if (isBooked) btn.classList.add("booked");

      btn.onclick = () => {
        if (isBooked) return; // no se puede seleccionar si ya está reservada

        clearActiveSlots();
        btn.classList.add("active");

        selectedInfo.textContent = `Reserva seleccionada: ${date} a las ${time}`;
        alert(`Tenemos que enviar esta reserva a la base de datos. ${date} a las ${time}`);
      };

      slotsContainer.appendChild(btn);
    });

    //Texto de arriba
    slotsHint.textContent = date ? `Para el día ${date}` : "—";
  }

  // Pone el datePicker en la fecha de hoy y renderiza las horas
  function setTodayAndRender() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0"); //+1 porque da el mes del 0 al 11 y padStart es que tenga 2 digitos y rellene con 0
    const dd = String(today.getDate()).padStart(2, "0");
    const formatted = `${yyyy}-${mm}-${dd}`;

    datePicker.value = formatted;
    renderSlots(formatted);
  }

  // Cada vez que el usuario cambie la fecha:
  datePicker.addEventListener("change", () => {
    selectedInfo.textContent = "Selecciona una hora para ver tu reserva aquí.";
    renderSlots(datePicker.value); //se generan las horas
  });

  // NAVEGACIÓN ENTRE VISTAS (Inicio / Mis reservas)
  //Selecionamos del menu
  const navBtns = document.querySelectorAll(".navbtn");
  const views = document.querySelectorAll(".view");

  function setView(viewKey) {
    // Oculta todas las vistas
    views.forEach(v => v.classList.add("hidden"));

    // Muestra solo la vista elegida
    document.getElementById("view-" + viewKey).classList.remove("hidden");

    // Marca el botón activo
    navBtns.forEach(b => b.classList.remove("active"));
    document.querySelector(`.navbtn[data-view="${viewKey}"]`)?.classList.add("active");

    // Si entramos en "misreservas", renderizamos la tabla
    if (viewKey === "misreservas") renderMisReservas();
  }

  // Click en botones de navegación
  navBtns.forEach(btn => {
    btn.addEventListener("click", () => setView(btn.dataset.view));
  });

   // "MIS RESERVAS" - TABLA Y ACCIONES
  function renderMisReservas() {
    const tbl = document.getElementById("tblMisReservas");

    // Si no hay reservas
    if (misReservas.length === 0) {
      tbl.innerHTML = `
        <thead><tr><th>Mis reservas</th></tr></thead>
        <tbody>
          <tr>
            <td style="padding:1rem; color:var(--muted);">
              No tienes reservas todavía.
            </td>
          </tr>
        </tbody>
      `;
      return;
    }

    // Si hay reservas, construimos la tabla con map()
    tbl.innerHTML = `
      <thead>
        <tr>
          <th>ID</th>
          <th>Tipo</th>
          <th>Recurso</th>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        ${misReservas
          .map(r => `
            <tr>
              <td>${r.id}</td>
              <td>${r.tipo}</td>
              <td><b>${r.recurso}</b></td>
              <td>${r.fecha}</td>
              <td>${r.hora}</td>
              <td>
                <div class="actions">
                  <button class="mini primary" onclick="openEdit(${r.id})">Modificar</button>
                  <button class="mini danger" onclick="cancelReserva(${r.id})">Anular</button>
                </div>
              </td>
            </tr>
          `)
          .join("")}
      </tbody>
    `;
  }

  // Elimina una reserva por id
  function cancelReserva(id) {
    if (!confirm("¿Seguro que quieres anular esta reserva?")) return;

    misReservas = misReservas.filter(r => r.id !== id); //Recorre toda la array de reservas y se queda con todas excepto la que queremos borrar
    renderMisReservas();
  }

  // MODAL PARA EDITAR RESERVA
  let currentEditId = null;

  // Abre el modal y precarga datos de la reserva
  function openEdit(id) {
    currentEditId = id;

    const reserva = misReservas.find(r => r.id === id);
    if (!reserva) return;

    // Seteamos fecha
    document.getElementById("editFecha").value = reserva.fecha;

    // Cargamos select de horas
    const horas = generateSlots();
    const sel = document.getElementById("editHora");

    sel.innerHTML = horas.map(h => `<option value="${h}">${h}</option>`).join("");
    sel.value = reserva.hora;

    // Mostrar modal
    document.getElementById("modalEdit").classList.remove("hidden");
  }

  // Cierra modal
  function closeModal() {
    document.getElementById("modalEdit").classList.add("hidden");
    currentEditId = null;
  }

  // Guarda cambios en la reserva editada
  function saveEdit() {
    const fecha = document.getElementById("editFecha").value;
    const hora = document.getElementById("editHora").value;

    if (!fecha || !hora) {
      alert("Selecciona fecha y hora.");
      return;
    }

    const r = misReservas.find(x => x.id === currentEditId);
    if (!r) return;

    r.fecha = fecha;
    r.hora = hora;

    closeModal();
    renderMisReservas();
    alert("Reserva modificada");
  };

  //Logout
  function logout() {
    window.location.href = "{{ route('home') }}";
  }

  if (logoutBtn) logoutBtn.addEventListener("click", logout);

  //ARRANQUE INICIAL
  setTodayAndRender();
  setView("inicio");
document.addEventListener("DOMContentLoaded", () => {

  const userId = window.__USER_ID__ ?? null;

  // TABLAS
  const tblMine = document.getElementById("tblMisReservas");
  const tblAll  = document.getElementById("tblAllReservas");

  // MODAL
  const modal = document.getElementById("modalEdit");
  const editFecha = document.getElementById("editFecha");
  const editHora = document.getElementById("editHora");
  const btnCancelEdit = document.getElementById("btnCancelEdit");
  const btnSaveEdit = document.getElementById("btnSaveEdit");

  let currentReservationId = null;

  // CSRF
  const csrfToken =
    document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

  // -------------------------
  function openModal() {
    modal.classList.remove("hidden");
  }

  function closeModal() {
    modal.classList.add("hidden");
    currentReservationId = null;
  }

  btnCancelEdit?.addEventListener("click", closeModal);

  // -------------------------
  function setMineEmpty(msg) {
    tblMine.innerHTML =
      `<tr><td style="padding:1rem;color:#6b7280;">${msg}</td></tr>`;
  }

  function setAllEmpty(msg) {
    if (!tblAll) return;
    tblAll.innerHTML =
      `<tr><td style="padding:1rem;color:#6b7280;">${msg}</td></tr>`;
  }

  // -------------------------
  async function loadSlotsOptions() {
    editHora.innerHTML = "";
    for (let i = 1; i <= 20; i++) {
      const opt = document.createElement("option");
      opt.value = i;
      opt.textContent = "Slot #" + i;
      editHora.appendChild(opt);
    }
  }

  // =========================
  // FETCH
  // =========================

  async function fetchMyReservations() {
    const res = await fetch(`/api/my-reservations`, {
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    });

    const data = await res.json();
    if (!res.ok) throw new Error(data?.message || "Error cargando reservas");

    return data;
  }

  async function fetchAllReservations() {
    if (!tblAll) return [];

    const res = await fetch(`/api/all-reservations`, {
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    });

    const data = await res.json();
    if (!res.ok) throw new Error(data?.message || "Error cargando todas");

    return data;
  }

  // =========================
  // RENDER MIS RESERVAS
  // =========================

  function renderMine(rows) {

    if (!rows || rows.length === 0) {
      setMineEmpty("No tienes reservas.");
      return;
    }

    tblMine.innerHTML = `
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Recursos</th>
          <th style="text-align:right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        ${rows.map(r => `
          <tr>
            <td>${r.date}</td>
            <td>${r.start_time.slice(0,5)} - ${r.end_time.slice(0,5)}</td>
            <td>
              ${(r.items || []).map(i =>
                `${i.name} (${i.type}) × ${i.quantity}`
              ).join("<br>")}
            </td>
            <td style="text-align:right;">
              <button class="mini" data-edit="${r.id}">Modificar</button>
              <button class="mini danger" data-del="${r.id}">Anular</button>
            </td>
          </tr>
        `).join("")}
      </tbody>
    `;

    // ANULAR
    tblMine.querySelectorAll("[data-del]").forEach(btn => {
      btn.addEventListener("click", async () => {

        const id = btn.dataset.del;
        if (!confirm("¿Anular reserva?")) return;

        const res = await fetch(`/api/reservations/${id}`, {
          method: "DELETE",
          credentials: "same-origin",
          headers: {
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest"
          }
        });

        const out = await res.json();
        if (!res.ok) {
          alert(out?.message || "Error al anular");
          return;
        }

        init();
      });
    });

    // MODIFICAR
    tblMine.querySelectorAll("[data-edit]").forEach(btn => {
      btn.addEventListener("click", async () => {
        currentReservationId = btn.dataset.edit;
        editFecha.value = new Date().toISOString().slice(0,10);
        await loadSlotsOptions();
        openModal();
      });
    });
  }

  // =========================
  // RENDER TODAS
  // =========================

  function renderAll(rows) {

    if (!tblAll) return;

    if (!rows || rows.length === 0) {
      setAllEmpty("No hay reservas.");
      return;
    }

    tblAll.innerHTML = `
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Recursos</th>
        </tr>
      </thead>
      <tbody>
        ${rows.map(r => `
          <tr>
            <td>${r.user}</td>
            <td>${r.date}</td>
            <td>${r.start_time.slice(0,5)} - ${r.end_time.slice(0,5)}</td>
            <td>
              ${(r.items || []).map(i =>
                `${i.name} (${i.type}) × ${i.quantity}`
              ).join("<br>")}
            </td>
          </tr>
        `).join("")}
      </tbody>
    `;
  }

  // =========================
  // GUARDAR MODIFICACIÓN
  // =========================

  btnSaveEdit?.addEventListener("click", async () => {

    if (!currentReservationId) return;

    const payload = {
      date: editFecha.value,
      time_slot_id: parseInt(editHora.value, 10)
    };

    const res = await fetch(`/api/reservations/${currentReservationId}`, {
      method: "PUT",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        "X-Requested-With": "XMLHttpRequest"
      },
      body: JSON.stringify(payload)
    });

    const out = await res.json();
    if (!res.ok) {
      alert(out?.message || "Error al modificar");
      return;
    }

    closeModal();
    init();
  });

  // =========================
  // INIT
  // =========================

  async function init() {
    try {

      setMineEmpty("Cargando reservas...");
      if (tblAll) setAllEmpty("Cargando reservas...");

      const mine = await fetchMyReservations();
      renderMine(mine);

      const all = await fetchAllReservations();
      renderAll(all);

    } catch (e) {
      console.error(e);
      setMineEmpty("Error cargando reservas");
      if (tblAll) setAllEmpty("Error cargando reservas");
    }
  }

  init();

});

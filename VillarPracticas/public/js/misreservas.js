document.addEventListener("DOMContentLoaded", () => {

  const tblMine = document.getElementById("tblMisReservas");
  const tblAll  = document.getElementById("tblAllReservas");

  const modal = document.getElementById("modalEdit");
  const btnCancelEdit = document.getElementById("btnCancelEdit");
  const btnSaveEdit = document.getElementById("btnSaveEdit");
  const editItemsContainer = document.getElementById("editItemsContainer");
  const editErrorBox = document.getElementById("editErrorBox");
  const editLoading = document.getElementById("editLoading");

  let currentReservationId = null;

  const csrfToken =
    document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

  // =========================
  // MODAL CONTROL
  // =========================

  function openModal() {
    modal.style.display = "flex";
  }

  function closeModal() {
    modal.style.display = "none";
    currentReservationId = null;
    editItemsContainer.innerHTML = "";
    editErrorBox.textContent = "";
    editLoading.style.display = "none";
  }

  btnCancelEdit?.addEventListener("click", closeModal);

  // =========================
  // HELPERS
  // =========================

  function setMineEmpty(msg) {
    tblMine.innerHTML =
      `<tr><td style="padding:1rem;color:#6b7280;">${msg}</td></tr>`;
  }

  function setAllEmpty(msg) {
    if (!tblAll) return;
    tblAll.innerHTML =
      `<tr><td style="padding:1rem;color:#6b7280;">${msg}</td></tr>`;
  }

  // =========================
  // FETCH FUNCTIONS
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

  async function fetchReservationDetail(id) {
    const res = await fetch(`/api/reservations/${id}`, {
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    });

    const data = await res.json();
    if (!res.ok) throw new Error(data?.message || "Error cargando detalle");
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

    // =========================
    // ANULAR
    // =========================

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

    // =========================
    // MODIFICAR
    // =========================

    tblMine.querySelectorAll("[data-edit]").forEach(btn => {
      btn.addEventListener("click", async () => {

        currentReservationId = btn.dataset.edit;

        try {

          editItemsContainer.innerHTML = "Cargando...";
          editErrorBox.textContent = "";
          openModal();

          const data = await fetchReservationDetail(currentReservationId);

          const items = data?.reservation?.items ?? [];

          editItemsContainer.innerHTML = "";

          if (items.length === 0) {
            editItemsContainer.innerHTML = "<p>No hay recursos en esta reserva.</p>";
            return;
          }

          items.forEach(item => {
            editItemsContainer.innerHTML += `
              <div style="margin-bottom:1rem;">
                <strong>${item.name}</strong><br>
                <input type="number"
                  data-resource="${item.resource_id}"
                  min="0"
                  value="${item.quantity}"
                  style="width:120px;padding:.4rem;">
              </div>
            `;
          });

        } catch (e) {
          console.error(e);
          editErrorBox.textContent = "No se pudo cargar la reserva";
        }

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
  // GUARDAR CAMBIOS
  // =========================

  btnSaveEdit?.addEventListener("click", async () => {

    if (!currentReservationId) return;

    editErrorBox.textContent = "";
    editLoading.style.display = "block";

    const inputs = document.querySelectorAll("#editItemsContainer input");

    const items = [];

    inputs.forEach(input => {
      const qty = parseInt(input.value) || 0;
      items.push({
        resource_id: parseInt(input.dataset.resource),
        quantity: qty
      });
    });

    try {

      const res = await fetch(`/api/reservations/${currentReservationId}/items`, {
        method: "PUT",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
          "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({ items })
      });

      const out = await res.json();

      if (!res.ok) {
        editLoading.style.display = "none";
        editErrorBox.textContent = out?.message || "Error al modificar";
        return;
      }

      closeModal();
      init();

    } catch (e) {
      editLoading.style.display = "none";
      editErrorBox.textContent = "Error de conexión";
    }

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

document.addEventListener("DOMContentLoaded", () => {

  const tblMine = document.getElementById("tblMisReservas");
  const tblAll  = document.getElementById("tblAllReservas");

  const modal = document.getElementById("modalEdit");
  const btnCancelEdit = document.getElementById("btnCancelEdit");
  const btnSaveEdit = document.getElementById("btnSaveEdit");
  const editItemsContainer = document.getElementById("editItemsContainer");
  const editErrorBox = document.getElementById("editErrorBox");
  const editLoading = document.getElementById("editLoading");

  const modalReturn = document.getElementById("modalConfirmReturn");
  const returnIncidentYes = document.getElementById("returnIncidentYes");
  const returnIncidentNo = document.getElementById("returnIncidentNo");
  const returnComment = document.getElementById("returnComment");
  const btnCancelReturn = document.getElementById("btnCancelReturn");
  const btnConfirmReturn = document.getElementById("btnConfirmReturn");

  let currentReservationId = null;
  let pendingReturnResolve = null;
  // reservationId -> lastPromptTimestampMs (para no spamear diálogos)
  const autoPromptedReturns = new Map();

  const csrfToken =
    document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

  /* ==========================================================
     MODAL
  ========================================================== */

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

  function openReturnModal(defaultIncident = false, defaultComment = "") {
    if (!modalReturn) return Promise.resolve({ confirmed: false });

    modalReturn.style.display = "flex";

    if (returnIncidentYes && returnIncidentNo) {
      if (defaultIncident) {
        returnIncidentYes.checked = true;
      } else {
        returnIncidentNo.checked = true;
      }
    }

    if (returnComment) {
      returnComment.value = defaultComment || "";
      returnComment.focus();
    }

    return new Promise(resolve => {
      pendingReturnResolve = resolve;
    });
  }

  function closeReturnModal() {
    if (modalReturn) {
      modalReturn.style.display = "none";
    }
    pendingReturnResolve = null;
  }

  btnCancelReturn?.addEventListener("click", () => {
    if (pendingReturnResolve) {
      pendingReturnResolve({ confirmed: false });
    }
    closeReturnModal();
  });

  btnConfirmReturn?.addEventListener("click", () => {
    if (!pendingReturnResolve) {
      closeReturnModal();
      return;
    }

    const hasIncident = !!(returnIncidentYes && returnIncidentYes.checked);
    const comment = (returnComment?.value || "").trim();

    pendingReturnResolve({
      confirmed: true,
      hasIncident,
      comment
    });

    closeReturnModal();
  });

  /* ==========================================================
     HELPERS
  ========================================================== */

  function setMineEmpty(msg) {
    tblMine.innerHTML =
      `<tr><td style="padding:1rem;color:#6b7280;">${msg}</td></tr>`;
  }

  function setAllEmpty(msg) {
    if (!tblAll) return;
    tblAll.innerHTML =
      `<tr><td style="padding:1rem;color:#6b7280;">${msg}</td></tr>`;
  }

  function statusBadge(status) {
    if (status === "pendiente_devolucion")
      return `<span style="color:#b45309;font-weight:600;">🟠 Pendiente</span>`;

    if (status === "finalizada")
      return `<span style="color:#16a34a;font-weight:600;">🔵 Finalizada</span>`;

    return `<span style="color:#2563eb;font-weight:600;">🟢 Activa</span>`;
  }

  async function safeJson(res) {
    try { return await res.json(); }
    catch { return {}; }
  }

  /* ==========================================================
     FETCH
  ========================================================== */

  async function fetchMyReservations() {
    const res = await fetch(`/api/my-reservations`, {
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    });

    const data = await safeJson(res);
    if (!res.ok) throw new Error(data?.message);
    return data;
  }

  async function fetchAllReservations() {
    if (!tblAll) return [];

    const res = await fetch(`/api/all-reservations`, {
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    });

    const data = await safeJson(res);
    if (!res.ok) throw new Error(data?.message);
    return data;
  }

  async function fetchReservationDetail(id) {
    const res = await fetch(`/api/reservations/${id}`, {
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" }
    });

    const data = await safeJson(res);
    if (!res.ok) throw new Error(data?.message);
    return data;
  }

  /* ==========================================================
     RENDER MIS RESERVAS
  ========================================================== */

  function renderMine(rows) {

    // En "Mis reservas" ocultamos las reservas con devolución confirmada (finalizadas)
    const visibleRows = (rows || []).filter(r => r?.status !== "finalizada");

    if (!visibleRows || visibleRows.length === 0) {
      setMineEmpty("No tienes reservas activas.");
      return;
    }

    tblMine.innerHTML = `
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Recursos</th>
          <th>Estado</th>
          <th style="text-align:right;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        ${visibleRows.map(r => `
          <tr>
            <td>${r.date}</td>
            <td>${r.start_time.slice(0,5)} - ${r.end_time.slice(0,5)}</td>
            <td>
              ${(r.items || []).map(i =>
                `${i.name} (${i.type}) × ${i.quantity}`
              ).join("<br>")}
            </td>
            <td>${statusBadge(r.status)}</td>
            <td style="text-align:right;display:flex;gap:.4rem;justify-content:flex-end;flex-wrap:wrap;">

              ${r.status !== "finalizada" ? `
                <button class="mini" data-edit="${r.id}">
                  Modificar
                </button>
              ` : ""}

              ${r.status === "activa" ? `
                <button class="mini danger" data-return="${r.id}">
                  Anular
                </button>
              ` : ""}

              ${r.status === "pendiente_devolucion" ? `
                <button class="mini primary" data-confirm="${r.id}">
                  Confirmar devolución
                </button>
              ` : ""}

            </td>
          </tr>
        `).join("")}
      </tbody>
    `;

    /* ===============================
       SOLICITAR DEVOLUCIÓN
    =============================== */

    tblMine.querySelectorAll("[data-return]").forEach(btn => {
      btn.addEventListener("click", async () => {

        if (!confirm("¿Solicitar devolución de esta reserva?")) return;

        btn.disabled = true;

        const res = await fetch(`/api/reservations/${btn.dataset.return}`, {
          method: "DELETE",
          credentials: "same-origin",
          headers: {
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest"
          }
        });

        const out = await safeJson(res);

        if (!res.ok) {
          alert(out?.message || "Error");
          btn.disabled = false;
          return;
        }

        init();
      });
    });

    /* ===============================
       CONFIRMAR DEVOLUCIÓN
    =============================== */

    tblMine.querySelectorAll("[data-confirm]").forEach(btn => {
      btn.addEventListener("click", async () => {

        const modalResult = await openReturnModal(false, "");
        if (!modalResult.confirmed) return;

        btn.disabled = true;

        const res = await fetch(
          `/api/reservations/${btn.dataset.confirm}/confirm-return`,
          {
            method: "PUT",
            credentials: "same-origin",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": csrfToken,
              "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
              incident: modalResult.hasIncident,
              comment: modalResult.comment
            })
          }
        );

        const out = await safeJson(res);

        if (!res.ok) {
          alert(out?.message || "Error");
          btn.disabled = false;
          return;
        }

        init();
      });
    });

    /* ===============================
       MODIFICAR
    =============================== */

    tblMine.querySelectorAll("[data-edit]").forEach(btn => {
      btn.addEventListener("click", async () => {

        currentReservationId = btn.dataset.edit;
        openModal();
        editItemsContainer.innerHTML = "Cargando...";

        try {

          const data = await fetchReservationDetail(currentReservationId);
          const items = data?.reservation?.items ?? [];

          editItemsContainer.innerHTML = "";

          if (!items.length) {
            editItemsContainer.innerHTML =
              "<p>No hay recursos en esta reserva.</p>";
            return;
          }

          items.forEach(item => {
            editItemsContainer.innerHTML += `
              <div style="margin-bottom:1rem;">
                <strong>${item.name}</strong><br>
                <input type="number"
                  data-resource="${item.resource_id}"
                  min="1"
                  value="${item.quantity}"
                  style="width:120px;padding:.4rem;">
              </div>
            `;
          });

        } catch {
          editItemsContainer.innerHTML =
            "<p style='color:red'>Error cargando reserva</p>";
        }
      });
    });

    // Tras pintar la tabla, comprobamos si hay alguna reserva
    // en estado "pendiente_devolucion" para lanzar un aviso automático.
    // El aviso automático solo aplica a reservas pendientes de devolución
    checkAutoReturnPrompt(visibleRows);
  }

  /* ==========================================================
     AVISO AUTOMÁTICO DE DEVOLUCIÓN
  ========================================================== */

  function checkAutoReturnPrompt(rows) {

    if (!rows || !rows.length) return;

    const nowMs = Date.now();
    const COOLDOWN_MS = 2 * 60 * 1000; // 2 minutos

    // Busca la primera reserva pendiente de devolución
    const pending = rows.find(r =>
      r.status === "pendiente_devolucion" &&
      (!autoPromptedReturns.has(r.id) || (nowMs - autoPromptedReturns.get(r.id)) >= COOLDOWN_MS)
    );

    if (!pending) return;

    autoPromptedReturns.set(pending.id, nowMs);

    // Primero confirmamos si ya se ha devuelto (para no finalizar automáticamente).
    const returned = confirm(
      "Tu reserva ha finalizado.\n\n" +
      "¿Has devuelto ya el material/espacio?\n\n" +
      "Aceptar = Sí, ya lo he devuelto\n" +
      "Cancelar = Todavía no"
    );

    if (!returned) return;

    // Preguntamos si la devolución ha tenido incidencia o no
    const hasIncident = confirm(
      "¿Ha habido alguna incidencia con el material/espacio?\n\n" +
      "Aceptar = con incidencia\n" +
      "Cancelar = sin incidencia"
    );

    let comentario = "";

    if (hasIncident) {
      comentario = prompt(
        "Describe brevemente la incidencia.\n(Puedes dejarlo vacío)"
      );
      if (comentario === null) return;
    } else {
      comentario = "Sin incidencias";
    }

    (async () => {
      try {
        const res = await fetch(
          `/api/reservations/${pending.id}/confirm-return`,
          {
            method: "PUT",
            credentials: "same-origin",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": csrfToken,
              "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ incident: hasIncident, comment: comentario })
          }
        );

        const out = await safeJson(res);

        if (!res.ok) {
          alert(out?.message || "Error al confirmar la devolución");
          return;
        }

        // Recargamos las reservas para actualizar estados
        init();

      } catch (e) {
        console.error(e);
        alert("Error al confirmar la devolución");
      }
    })();
  }

  /* ==========================================================
     RENDER TODAS
  ========================================================== */

  function renderAll(rows) {

    if (!tblAll) return;

    // En "Todas las reservas" solo mostramos las activas (las que han finalizado o están
    // pendientes de devolución se ocultan aquí, aunque el usuario aún no las haya confirmado).
    const visibleRows = (rows || []).filter(r => r?.status === "activa");

    if (!visibleRows || visibleRows.length === 0) {
      setAllEmpty("No hay reservas activas.");
      return;
    }

    tblAll.innerHTML = `
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Recursos</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        ${visibleRows.map(r => `
          <tr>
            <td>${r.user}</td>
            <td>${r.date}</td>
            <td>${r.start_time.slice(0,5)} - ${r.end_time.slice(0,5)}</td>
            <td>
              ${(r.items || []).map(i =>
                `${i.name} (${i.type}) × ${i.quantity}`
              ).join("<br>")}
            </td>
            <td>${statusBadge(r.status)}</td>
          </tr>
        `).join("")}
      </tbody>
    `;
  }

  /* ==========================================================
     GUARDAR CAMBIOS
  ========================================================== */

  btnSaveEdit?.addEventListener("click", async () => {

    if (!currentReservationId) return;

    editLoading.style.display = "block";
    editErrorBox.textContent = "";

    const inputs =
      document.querySelectorAll("#editItemsContainer input");

    const items = [];

    inputs.forEach(input => {
      const qty = parseInt(input.value);
      items.push({
        resource_id: parseInt(input.dataset.resource),
        quantity: Number.isFinite(qty) ? qty : 0
      });
    });

    if (!items.length) {
      editErrorBox.textContent = "La reserva debe tener al menos 1 recurso.";
      editLoading.style.display = "none";
      return;
    }

    if (items.some(i => !i.quantity || i.quantity < 1)) {
      editErrorBox.textContent = "Las cantidades deben ser mínimo 1 (no se permite 0).";
      editLoading.style.display = "none";
      return;
    }

    const res = await fetch(
      `/api/reservations/${currentReservationId}/items`,
      {
        method: "PUT",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
          "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({ items })
      }
    );

    const out = await safeJson(res);

    editLoading.style.display = "none";

    if (!res.ok) {
      editErrorBox.textContent =
        out?.message || "Error al modificar";
      return;
    }

    closeModal();
    init();
  });

  /* ==========================================================
     INIT
  ========================================================== */

  async function init() {
    try {

      setMineEmpty("Cargando...");
      if (tblAll) setAllEmpty("Cargando...");

      const mine = await fetchMyReservations();
      renderMine(mine);

      const all = await fetchAllReservations();
      renderAll(all);

    } catch (e) {
      console.error(e);
      setMineEmpty("Error cargando reservas");
    }
  }

  init();

  // Refresco suave para detectar cuando una reserva pasa a pendiente_devolucion
  // mientras el usuario está en MisReservas (sin tener que recargar la página).
  setInterval(async () => {
    try {
      const mine = await fetchMyReservations();
      renderMine(mine);
    } catch {
      // silencioso
    }
  }, 30000);

});
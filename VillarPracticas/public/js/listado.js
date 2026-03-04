(function () {

  const params = new URLSearchParams(window.location.search);
  const date = params.get("date");
  const slots = params.get("slots"); // "1,3,5"

  const container = document.getElementById("resourcesContainer");
  const summaryBox = document.getElementById("summaryBox");
  const btnReserve = document.getElementById("btnReserve");
  const errorBox = document.getElementById("errorBox");
  const gallery = document.getElementById("resourceGallery");

  const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

  // Hay 7 imágenes (img1.png ... img7.png) en /public/img.
  const GALLERY_IMAGE_COUNT = 7;

  // state => { slotId: { resourceId: qty } }
  const state = {};

  // Guardamos los datos del servidor para usarlos en el resumen
  let slotsData = {};
  let resourcesData = {};

  /* ============================
     HELPERS
  ============================ */

  function setError(msg) {
    errorBox.textContent = msg || "";
  }

  function updateSummary() {

    if (!date || !slots) {
      summaryBox.innerHTML = "Falta información (fecha/slots).";
      btnReserve.disabled = true;
      return;
    }

    const lines = [];
    let totalItems = 0;

    Object.entries(state).forEach(([slotId, resources]) => {

      const items = Object.entries(resources)
        .filter(([, q]) => q > 0);

      if (!items.length) return;

      totalItems += items.reduce((a, [, q]) => a + q, 0);

      // Obtener info del slot
      const slotInfo = slotsData[slotId] || {};
      const slotTime = slotInfo.start_time && slotInfo.end_time 
        ? `${slotInfo.start_time.slice(0,5)} - ${slotInfo.end_time.slice(0,5)}`
        : `Slot ${slotId}`;

      // Construir descripción de items con nombres descriptivos
      const itemsDesc = items.map(([rid, q]) => {
        const resourceInfo = resourcesData[rid] || {};
        const resourceName = resourceInfo.name || `Recurso ${rid}`;
        return `${resourceName} x ${q}`;
      }).join(", ");

      lines.push(`
        <div>
          <strong>${slotTime}</strong>: 
          ${itemsDesc}
        </div>
      `);
    });

    if (totalItems === 0) {
      summaryBox.innerHTML = `<strong>${date}</strong><br>
      Selecciona cantidades para al menos un recurso.`;
      btnReserve.disabled = true;
      return;
    }

    summaryBox.innerHTML = `<strong>${date}</strong><br>${lines.join("")}`;
    btnReserve.disabled = false;
  }

  /* ============================
     GALERIA VISUAL
  ============================ */

  function createOrUpdateGalleryItem(res) {

    if (!gallery) return;

    let el = document.getElementById("gal_" + res.id);

    // 🔥 IMAGEN DIFERENTE POR RECURSO:
    // usamos el id del recurso para elegir siempre
    // la misma miniatura, independientemente de la franja.
    const base = parseInt(res.id, 10) || 1;
    const imgIndex = ((base - 1) % GALLERY_IMAGE_COUNT) + 1;
    const src = `/img/img${imgIndex}.png`;

    if (!el) {
      el = document.createElement("div");
      el.id = "gal_" + res.id;
      el.className = "resource-thumb";
      el.innerHTML = `<img src="${src}" alt="${res.name}">`;
      gallery.appendChild(el);
      return;
    }

    // Si ya existe (por varias franjas), actualizamos la imagen igualmente.
    const img = el.querySelector("img");
    if (img) {
      img.src = src;
      img.alt = res.name;
    } else {
      el.innerHTML = `<img src="${src}" alt="${res.name}">`;
    }
  }

  function updateGalleryFromState() {

    document
      .querySelectorAll(".resource-thumb")
      .forEach(el => el.classList.remove("active"));

    for (const [, resources] of Object.entries(state)) {
      for (const [rid, qty] of Object.entries(resources)) {
        if (qty > 0) {
          const el = document.getElementById("gal_" + rid);
          if (el) el.classList.add("active");
        }
      }
    }
  }

  /* ============================
     INPUT CHANGE
  ============================ */

  function onQtyChange(slotId, resourceId, value, max) {

    const qty = Math.max(0, Math.min(parseInt(value || "0"), max));

    if (!state[slotId]) state[slotId] = {};
    state[slotId][resourceId] = qty;

    updateSummary();
    updateGalleryFromState();
  }

  /* ============================
     UI BUILDERS
  ============================ */

  function resourceCard(res, slotId) {

    createOrUpdateGalleryItem(res);

    const id = `qty_${slotId}_${res.id}`;
    const badge = res.type === "space" ? "Espacio" : "Material";
    const hasKnownReturnInfo =
      res.last_return_defectuoso !== undefined &&
      res.last_return_defectuoso !== null ||
      res.last_return_status !== undefined &&
      res.last_return_status !== null;

    const hasIncident =
      res.last_return_defectuoso === true ||
      res.last_return_defectuoso === 1 ||
      res.last_return_defectuoso === "1" ||
      res.last_return_status === "con_incidencia" ||
      // compatibilidad con posibles campos antiguos
      res.last_incident_status === "incidencia" ||
      res.last_incident_status === "con_incidencia" ||
      res.last_incident === true ||
      res.last_incident === 1 ||
      res.last_incident === "1";

    // Verde por defecto
    let incidentCircle = `
      <span
        title="${hasKnownReturnInfo ? "Última devolución sin incidencias" : "Sin devoluciones registradas"}"
        style="
          display:inline-block;
          width:10px;
          height:10px;
          border-radius:999px;
          background:#22c55e;
          margin-left:.35rem;
          vertical-align:middle;
        ">
      </span>
    `;

    // Solo si el backend marca incidencia, lo ponemos rojo
    if (hasIncident) {
      incidentCircle = `
        <span
          title="Última devolución con incidencia"
          style="
            display:inline-block;
            width:10px;
            height:10px;
            border-radius:999px;
            background:#ef4444;
            margin-left:.35rem;
            vertical-align:middle;
          ">
        </span>
      `;
    }

    return `
      <div style="border:1px solid var(--border);
                  border-radius:14px;
                  padding:.85rem;
                  margin:.6rem 0;
                  background:#fff;">

        <div style="display:flex;
                    justify-content:space-between;
                    align-items:center;
                    gap:1rem;">

          <div>
            <div style="font-weight:700;">${res.name}</div>
            <div style="font-size:.82rem;opacity:.7;">
              ${badge} - disponibles: <strong>${res.remaining}</strong>${incidentCircle}
            </div>
          </div>

          <input
            id="${id}"
            type="number"
            min="0"
            max="${res.remaining}"
            value="0"
            style="width:90px;
                   padding:.6rem;
                   border-radius:10px;
                   border:1px solid var(--border);">
        </div>
      </div>
    `;
  }

  function slotBlock(slot, resources) {

    const title = `${slot.start_time.slice(0,5)} - ${slot.end_time.slice(0,5)}`;
    const slotId = slot.slot_id;

    const list = resources
      .filter(r => r.remaining > 0)
      .map(r => resourceCard(r, slotId))
      .join("");

    return `
      <div style="margin-bottom:1.5rem;">
        <h3>${title}</h3>
        ${list || `<div style="color:#6b7280;">No hay recursos disponibles.</div>`}
      </div>
    `;
  }

  /* ============================
     LOAD DATA
  ============================ */

  async function load() {

    if (!date || !slots) {
      container.innerHTML = "Faltan parámetros.";
      return;
    }

    summaryBox.innerHTML = `<strong>${date}</strong><br>Cargando...`;

    const res = await fetch(
      `/listado-data?date=${encodeURIComponent(date)}&slots=${encodeURIComponent(slots)}`,
      {
        credentials: "same-origin",
        headers: { "X-Requested-With": "XMLHttpRequest" }
      }
    );

    const data = await res.json();

    if (!res.ok) {
      setError(data?.message || "Error cargando datos");
      return;
    }

    // Guardar datos para usar en updateSummary()
    data.slots.forEach(slot => {
      slotsData[slot.slot_id] = slot;
    });

    for (const [slotIdStr, resources] of Object.entries(data.resourcesBySlot)) {
      resources.forEach(res => {
        resourcesData[res.id] = res;
      });
    }

    container.innerHTML = data.slots.map(slot => {
      const resources = data.resourcesBySlot[String(slot.slot_id)] || [];
      return slotBlock(slot, resources);
    }).join("");

    data.slots.forEach(slot => {
      const slotId = slot.slot_id;
      const resources = data.resourcesBySlot[String(slotId)] || [];

      resources.forEach(r => {
        const input = document.getElementById(`qty_${slotId}_${r.id}`);
        if (!input) return;

        input.addEventListener("input", () =>
          onQtyChange(slotId, r.id, input.value, r.remaining)
        );
      });
    });

    updateSummary();
  }

  /* ============================
     SUBMIT
  ============================ */

  btnReserve.addEventListener("click", async () => {

    setError("");

    const payloadSlots = [];

    for (const [slotId, resources] of Object.entries(state)) {

      const items = Object.entries(resources)
        .filter(([, q]) => q > 0)
        .map(([rid, q]) => ({
          resource_id: parseInt(rid),
          quantity: q
        }));

      if (items.length) {
        payloadSlots.push({
          slot_id: parseInt(slotId),
          items
        });
      }
    }

    if (payloadSlots.length === 0) {
      setError("Selecciona al menos un recurso.");
      return;
    }

    btnReserve.disabled = true;
    btnReserve.textContent = "Reservando...";

    const r = await fetch("/reservations", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        "X-Requested-With": "XMLHttpRequest"
      },
      body: JSON.stringify({ date, slots: payloadSlots })
    });

    const out = await r.json();

    if (!r.ok) {
      btnReserve.disabled = false;
      btnReserve.textContent = "Confirmar reserva";
      setError(out?.message || "Error al reservar");
      return;
    }

    alert("Reserva creada correctamente.");
    window.location.href = `/Profesors/profesor/${window.__USER_ID__}`;
  });

  /* ============================
     INIT
  ============================ */

  load();

})();

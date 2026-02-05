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

  // state => { slotId: { resourceId: qty } }
  const state = {};

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

      lines.push(`
        <div>
          <strong>Slot ${slotId}</strong>: 
          ${items.map(([rid, q]) => `R${rid} x ${q}`).join(", ")}
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

  function createOrUpdateGalleryItem(res, index) {

    if (!gallery) return;

    let el = document.getElementById("gal_" + res.id);

    if (!el) {

      el = document.createElement("div");
      el.id = "gal_" + res.id;
      el.className = "resource-thumb";

      // 🔥 IMAGEN DIFERENTE POR ORDEN
      const imgIndex = index + 1;

      el.innerHTML = `
        <img src="/img/img${imgIndex}.png" alt="${res.name}">
      `;

      gallery.appendChild(el);
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

  function resourceCard(res, slotId, index) {

    createOrUpdateGalleryItem(res, index);

    const id = `qty_${slotId}_${res.id}`;
    const badge = res.type === "space" ? "Espacio" : "Material";

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
              ${badge} - disponibles: <strong>${res.remaining}</strong>
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
      .map((r, i) => resourceCard(r, slotId, i))
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

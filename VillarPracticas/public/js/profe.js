document.addEventListener("DOMContentLoaded", () => {

  const slotsContainer = document.getElementById("slots");
  const datePicker = document.getElementById("datePicker");
  const selectedInfo = document.getElementById("selectedInfo");
  const slotsHint = document.getElementById("slotsHint");
  const btnContinue = document.getElementById("btnContinue");

  const USER_ID = window.__USER_ID__ ?? null;

  let selectedDate = null;
  let selectedSlots = [];

  // =============================
  // UTILIDADES FECHA
  // =============================
  function formatToday() {
    const d = new Date();
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const dd = String(d.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
  }

  function isPastDate(dateStr) {
    if (!dateStr) return true;
    return dateStr < formatToday();
  }

  // =============================
  // UI
  // =============================
  function updateContinueButton() {
    if (!btnContinue) return;
    btnContinue.disabled = !(selectedDate && selectedSlots.length > 0);
  }

  function updateSelectedInfo() {
    if (!selectedInfo) return;

    if (selectedSlots.length === 0) {
      selectedInfo.textContent =
        "Selecciona una hora para ver tu reserva aquí.";
      return;
    }

    const ordered = [...selectedSlots].sort((a, b) =>
      a.start_time.localeCompare(b.start_time)
    );

    selectedInfo.innerHTML = ordered
      .map(s => `${s.start_time.slice(0,5)} - ${s.end_time.slice(0,5)}`)
      .join("<br>");
  }

  // =============================
  // API
  // =============================
  async function fetchAvailability(date) {
    const url = `/availability?date=${encodeURIComponent(date)}`;
    const res = await fetch(url, {
      headers: { "X-Requested-With": "XMLHttpRequest" }
    });
    if (!res.ok) throw new Error("Error cargando disponibilidad");
    return await res.json();
  }

  // =============================
  // RENDER SLOTS
  // =============================
  async function renderSlots(date) {

    selectedDate = date;
    selectedSlots = [];
    updateSelectedInfo();
    updateContinueButton();

    if (slotsHint) slotsHint.textContent = `Para el día ${date}`;
    if (!slotsContainer) return;

    slotsContainer.innerHTML = "Cargando franjas...";

    let data;
    try {
      data = await fetchAvailability(date);
    } catch (e) {
      slotsContainer.innerHTML = "Error cargando franjas";
      return;
    }

    const slots = data.slots || [];
    slotsContainer.innerHTML = "";

    if (slots.length === 0) {
      slotsContainer.innerHTML = "No hay franjas disponibles";
      return;
    }

    slots.forEach(slot => {

      // =============================
      // BLOQUEAR HORAS PASADAS HOY
      // =============================
      let isPast = false;

      if (date === formatToday()) {
        const now = new Date();
        const nowMinutes = now.getHours() * 60 + now.getMinutes();

        const [h, m] = slot.start_time.split(":");
        const slotMinutes = parseInt(h) * 60 + parseInt(m);

        if (slotMinutes <= nowMinutes) {
          isPast = true;
        }
      }

      // =============================
      // BOTÓN
      // =============================
      const btn = document.createElement("button");
      btn.type = "button";
      btn.classList.add("slot");
      btn.textContent =
        `${slot.start_time.slice(0,5)} - ${slot.end_time.slice(0,5)}`;

      // slot sin recursos
      if (slot.disabled) {
        btn.disabled = true;
        btn.classList.add("booked");
      }

      // slot pasado por hora
      if (isPast) {
        btn.disabled = true;
        btn.classList.add("booked");
      }

      btn.addEventListener("click", () => {

        const slotId = slot.slot_id;
        const idx = selectedSlots.findIndex(s => s.slot_id === slotId);

        if (idx !== -1) {
          selectedSlots.splice(idx, 1);
          btn.classList.remove("active");
        } else {
          selectedSlots.push({
            slot_id: slotId,
            start_time: slot.start_time,
            end_time: slot.end_time
          });
          btn.classList.add("active");
        }

        updateSelectedInfo();
        updateContinueButton();
      });

      slotsContainer.appendChild(btn);
    });
  }

  // =============================
  // CONTINUAR
  // =============================
  if (btnContinue) {
    btnContinue.addEventListener("click", () => {

      if (!USER_ID || !selectedDate || selectedSlots.length === 0) return;

      const ids = [...selectedSlots]
        .sort((a,b) => a.start_time.localeCompare(b.start_time))
        .map(s => s.slot_id)
        .join(",");

      const url =
        `/Profesors/listado/${USER_ID}?date=${encodeURIComponent(selectedDate)}&slots=${encodeURIComponent(ids)}`;

      window.location.href = url;
    });
  }

  // =============================
  // INIT
  // =============================
  if (!datePicker || !slotsContainer) return;

  // Bloquea calendario días pasados
  datePicker.min = formatToday();

  // Valor inicial
  datePicker.value = formatToday();
  renderSlots(datePicker.value);

  // Cambio por calendario
  datePicker.addEventListener("change", () => {

    const value = datePicker.value;

    if (isPastDate(value)) {
      datePicker.value = formatToday();
      renderSlots(formatToday());
      return;
    }

    renderSlots(value);
  });

  // Escritura manual
  datePicker.addEventListener("input", () => {

    if (isPastDate(datePicker.value)) {
      datePicker.value = formatToday();
    }
  });

});

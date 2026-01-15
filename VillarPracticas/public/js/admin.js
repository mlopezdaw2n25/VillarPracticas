/* =========================
   "BD" SIMULADA (DEMO)
==========================*/
let dataStore = {
  usuarios: [
    {id:1, nombre:"Laura Canela", email:"laura@centrevillar.es", rol:"Profesor", grupo:"—", estado:"activo"},
    {id:2, nombre:"Marc Sort", email:"marc@centrevillar.es", rol:"Administrador", grupo:"-", estado:"activo"},
    {id:3, nombre:"Núria Serra", email:"nuria@centrevillar.es", rol:"Profesor", grupo:"—", estado:"pausado"},
  ],
  espacios: [
    {id:1, nombre:"Plató 1", tipo:"Plató", capacidad:30, ubicacion:"2ª planta", estado:"activo"},
    {id:2, nombre:"Plató 2", tipo:"Plató", capacidad:24, ubicacion:"1ª planta", estado:"activo"},
    {id:3, nombre:"Sala Actos", tipo:"Sala", capacidad:120, ubicacion:"Planta baja", estado:"pausado"},
  ],
  materiales: [
    {id:1, nombre:"Cámara Canon #1", tipo:"Cámara", ubicacion:"Armario 2", estado:"activo"},
    {id:2, nombre:"Microfono Rode", tipo:"Microfono", ubicacion:"Aula 105", estado:"activo"},
    {id:3, nombre:"Cámara Canon #2", tipo:"Cámara", ubicacion:"Biblioteca", estado:"pausado"},
  ],
  reservas: [
    {id:101, usuario:"Laura Canela", recurso:"Cámara Canon", tipo:"Material", fecha:"01-01-2026", hora:"10:00", comentario:"Haremos una práctica de la asignatura MP0483"},
    {id:102, usuario:"Marc Vidal", recurso:"Sala Actos", tipo:"Espacio", fecha:"31-05-2026", hora:"12:00", comentario:"Exposición proyecto final"},
    {id:103, usuario:"Núria Serra", recurso:"Cámara Canon", tipo:"Material", fecha:"08-01-2026", hora:"09:00", comentario:"Sin comentario"},
  ],
  averiasMaterial: [
    {id:201, item:"Cámara Canon #2", ubicacion:"Secretaria", descripcion:"No enciende", fecha:"10-01-2026", estado:"recibido"},
    {id:202, item:"Microfono Rode", ubicacion:"Plató 2", descripcion:"Se escucha borroso", fecha:"21-12-2025", estado:"trabajando"},
    {id:203, item:"Altavoces", ubicacion:"Sala Actos", descripcion:"Sonido intermitente", fecha:"15-12-2025", estado:"ok"},
  ],
  averiasIT: [
    {id:301, item:"PC Aula", ubicacion:"Aula 103", descripcion:"Pantalla azul", fecha:"11-01-2026", estado:"recibido"},
    {id:302, item:"WiFi", ubicacion:"2ª planta", descripcion:"Sin conexión", fecha:"14-01-2026", estado:"trabajando"},
    {id:303, item:"Impresora", ubicacion:"Secretaría", descripcion:"Atasco constante", fecha:"15-01-2026", estado:"fallo"},
  ],
  desplegables: {
    cursos: ["Edición de vídeo","Disc-Jockey","Animación 3D","Sonido"],
    grupos: ["A","B","C"],
    asignaturas: [
      "Producción audiovisual","Montaje y edición","Guion y narrativa","Postproducción y efectos",
      "Iluminación y fotografía","Sonido para audiovisuales","Animación 3D","Texturizado y materiales",
    ],
    camposExtra: ["Teléfono tutor", "Observaciones", "Necesidades especiales"]
  },
  camposFormulario: [
    {id:1, campo:"Teléfono", activo:true},
    {id:2, campo:"Observaciones", activo:true},
    {id:3, campo:"Adjuntar archivo", activo:false},
    {id:4, campo:"Prioridad", activo:true},
  ]
};

const ESTADOS_AVERIA = ["recibido", "trabajando", "ok", "fallo"];

//HELPERS

// Atajos para seleccionar elementos del DOM
const $  = (s)=>document.querySelector(s);     // Devuelve el primer elemento que coincida con el selector
const $$ = (s)=>document.querySelectorAll(s);  // Devuelve todos los elementos que coincidan con el selector

// Normaliza un texto para buscar sin problemas
const normalize = (s)=>String(s||"").toLowerCase();

// Referencias a elementos fijos de la interfaz (se usan muchas veces)
const viewTitle = $("#viewTitle");
const viewSubtitle = $("#viewSubtitle");
const searchInput = $("#searchInput");
const countHint = $("#countHint");
const btnPrimaryAction = $("#btnPrimaryAction");
const btnRefresh = $("#btnRefresh");

// Vista actual (qué sección se está mostrando)
let currentView = "usuarios";

/**
 * Genera el "badge" (etiqueta) para estados generales tipo:
 * - activo
 * - pausado
 */
const badgeGeneral = (st) => {
  if (st === "activo") {
    return `<span class="badge b-active">Activo</span>`;
  } else {
    return `<span class="badge b-paused">Pausado</span>`;
  }
};

/**
 * Genera el badge para el estado de una avería.
 * Traduce un estado interno ("recibido", "trabajando"...)
 * a (clase CSS, texto visible).
 */
const badgeAveria = (st)=>{
  const map = {
    recibido:   ["b-recibido", "Recibido"],
    trabajando: ["b-trabajando", "Trabajando"],
    ok:         ["b-ok", "Cierre exitoso"],
    fallo:      ["b-fallo", "Cierre fallido"]
  };
  // Si no está en el mapa, usa un estilo por defecto y muestra el estado tal cual
  const [cls, label] = map[st] || ["b-paused", st];
  return `<span class="badge ${cls}">${label}</span>`;
};

//Cuando lo conectemos con la base de datos le daremos el id de ahí
let id = 0;
function nextId(){
  return id++;
}

/**
 * Te lleva al sitio de creación de usuario y remarca el sitio durante un tiempo
 */
function scrollToCard(sel){
  const el = document.querySelector(sel);
  if(!el) return;

  el.scrollIntoView({behavior:"smooth", block:"start"});
  el.style.outline="4px solid rgba(102,126,234,.25)";
  setTimeout(()=> el.style.outline="none", 800);
}

/**
 * Filtrado por búsqueda:
 * - toma el texto del input searchInput
 * - si está vacío => devuelve la lista original
 * - si no => filtra por coincidencias (includes) en ciertos campos
 */
function filterSearch(array, fields){
  const q = normalize(searchInput.value.trim());
  if(!q) return array;

  // Incluye un elemento si alguno de los campos contiene el texto buscado
  return array.filter(it => fields.some(f => normalize(it[f]).includes(q)));
}

// CONFIG POR VISTA
const meta = {
  usuarios: { 
    title:"Usuarios", 
    subtitle:"Gestión usuarios.", 
    actionText:"Crear", 
    actionFn:()=>scrollToCard("#cardTresFilas") 
  },
  espacios: { 
    title:"Espacios", 
    subtitle:"Gestión espacios.", 
    actionText:"Crear", 
    actionFn:()=>scrollToCard("#cardCrearEspacio") 
  },
  materiales:{ 
    title:"Materiales", 
    subtitle:"Gestión materiales.", 
    actionText:"Crear", 
    actionFn:()=>scrollToCard("#cardCrearMaterial") 
  },
  reservas: { 
    title:"Reservas", 
    subtitle:"Consulta/eliminación.", 
    actionText:"—", 
    actionFn:null 
  },
  averiasMaterial:{ 
    title:"Averías material", 
    subtitle:"Gestión estados averías.", 
    actionText:"—", 
    actionFn:null 
  },
  averiasIT:{ 
    title:"Averías IT", 
    subtitle:"Gestión estados averías.", 
    actionText:"—", 
    actionFn:null 
  },
  statsAveriasIT:{ 
    title:"Estadísticas IT", 
    subtitle:"Estadísticas de las incidencias informáticas.", 
    actionText:"Recalcular", 
    actionFn:()=>renderStats() 
  },
  statsEspacios:{ 
    title:"Estadísticas espacios", 
    subtitle:"", 
    actionText:"Recalcular", 
    actionFn:()=>renderStats() 
  },
  statsMaterial:{ 
    title:"Estadísticas material", 
    subtitle:"", 
    actionText:"Recalcular", 
    actionFn:()=>renderStats() 
  },
  desplegables:{ 
    title:"Desplegables y formularios", 
    subtitle:"Listas + campos formulario.", 
    actionText:"—", 
    actionFn:null 
  }
};

/**
 * Configuración genérica de tablas:
 * Para cada vista con tabla se define UNA SOLA VEZ:
 * - selector de tabla
 * - de dónde se saca la información (source)
 * - campos donde buscar (searchFields)
 * - columnas: [textoCabecera, campo|funciónRender]
 *
 * Nota: si en cols el segundo elemento es función, genera HTML personalizado.
 */
const tableConfig = {
  usuarios: {
    tbl:"#tblUsuarios",
    source: ()=>dataStore.usuarios,
    searchFields:["nombre","email","rol","grupo","estado"],
    cols:[
      ["ID","id"],
      ["Nombre",(x)=>`<b>${x.nombre}</b>`],
      ["Email","email"],
      ["Rol","rol"],
      ["Grupo","grupo"],
      ["Estado",(x)=>badgeGeneral(x.estado)],
      ["Acciones",(x)=>actionsCRUD("usuarios", x.id, x.estado)]
    ]
  },

  espacios: {
    tbl:"#tblEspacios",
    source: ()=>dataStore.espacios,
    searchFields:["nombre","tipo","ubicacion","estado"],
    cols:[
      ["ID","id"],
      ["Nombre",(x)=>`<b>${x.nombre}</b>`],
      ["Tipo","tipo"],
      ["Capacidad","capacidad"],
      ["Ubicación","ubicacion"],
      ["Estado",(x)=>badgeGeneral(x.estado)],
      ["Acciones",(x)=>actionsCRUD("espacios", x.id, x.estado)]
    ]
  },

  materiales: {
    tbl:"#tblMateriales",
    source: ()=>dataStore.materiales,
    searchFields:["nombre","tipo","ubicacion","estado"],
    cols:[
      ["ID","id"],
      ["Nombre",(x)=>`<b>${x.nombre}</b>`],
      ["Tipo","tipo"],
      ["Ubicación","ubicacion"],
      ["Estado",(x)=>badgeGeneral(x.estado)],
      ["Acciones",(x)=>actionsCRUD("materiales", x.id, x.estado)]
    ]
  },

  reservas: {
    tbl:"#tblReservas",
    source: ()=>dataStore.reservas,
    searchFields:["usuario","recurso","tipo","fecha","hora"],
    cols:[
      ["ID","id"],
      ["Usuario",(x)=>`<b>${x.usuario}</b>`],
      ["Tipo","tipo"],
      ["Recurso","recurso"],
      ["Fecha","fecha"],
      ["Hora","hora"],
      ["Acciones",(x)=>`<button class="mini danger" onclick="delItem('reservas', ${x.id})">Eliminar</button>`]
    ]
  },

  averiasMaterial: {
    tbl:"#tblAveriasMaterial",
    source: ()=>dataStore.averiasMaterial,
    searchFields:["item","ubicacion","descripcion","fecha","estado"],
    cols:[
      ["ID","id"],
      ["Material",(x)=>`<b>${x.item}</b>`],
      ["Ubicación","ubicacion"],
      ["Descripción","descripcion"],
      ["Fecha","fecha"],
      ["Estado",(x)=>badgeAveria(x.estado)],
      ["Cambiar estado",(x)=>estadoButtons("material", x.id, x.estado)]
    ]
  },

  averiasIT: {
    tbl:"#tblAveriasIT",
    source: ()=>dataStore.averiasIT,
    searchFields:["item","ubicacion","descripcion","fecha","estado"],
    cols:[
      ["ID","id"],
      ["Equipo/Servicio",(x)=>`<b>${x.item}</b>`],
      ["Ubicación","ubicacion"],
      ["Descripción","descripcion"],
      ["Fecha","fecha"],
      ["Estado",(x)=>badgeAveria(x.estado)],
      ["Cambiar estado",(x)=>estadoButtons("it", x.id, x.estado)]
    ]
  }
};

// RENDER del BUSCADOR

/**
 * Renderiza una tabla completa en base a su configuración.
 * viewKey identifica la vista: "usuarios", "espacios"...
 */
function renderTable(viewKey){
  const cfg = tableConfig[viewKey];
  if(!cfg) return;

  // 1) obtener datos desde dataStore
  let arr = cfg.source();

  // 2) aplicar búsqueda
  arr = filterSearch(arr, cfg.searchFields);

  // 3) mostrar contador
  countHint.textContent = `${arr.length} resultado(s)`;

  // 4) construir thead (cabecera)
  const thead = `<thead><tr>${cfg.cols.map(c=>`<th>${c[0]}</th>`).join("")}</tr></thead>`;

  // 5) construir tbody (filas)
  const tbody = `<tbody>${
    arr.map(row=>{
      // genera cada celda usando el campo o función de render
      const tds = cfg.cols.map(([,field])=>{
        const value = typeof field === "function" ? field(row) : row[field];
        return `<td>${value ?? ""}</td>`;
      }).join("");

      return `<tr>${tds}</tr>`;
    }).join("")
  }</tbody>`;

  // 6) inyectar HTML en la tabla
  $(cfg.tbl).innerHTML = thead + tbody;
}

function renderAll(){
  // Si la vista tiene tabla configurada => pintar tabla
  if(tableConfig[currentView]) renderTable(currentView);

  // Si la vista es estadística => pintar stats
  if(["statsAveriasIT","statsEspacios","statsMaterial"].includes(currentView)) renderStats();

  // Si es vista de desplegables => pintar ambos bloques
  if(currentView==="desplegables"){ 
    renderDesplegables(); 
    renderCampos(); 
  }
}

/* =========================
   ACCIONES GENÉRICAS
==========================*/

/**
 * Genera HTML con botones de acciones comunes:
 * - Pausar/Reactivar
 * - Eliminar
 */
function actionsCRUD(key, id, estado){
  // Botón cambia según el estado actual
  const pauseBtn = estado==="activo"
    ? `<button class="mini warning" onclick="setEstado('${key}', ${id}, 'pausado')">Pausar</button>`
    : `<button class="mini info" onclick="setEstado('${key}', ${id}, 'activo')">Reactivar</button>`;

  return `
    <div class="actions">
      ${pauseBtn}
      <button class="mini danger" onclick="delItem('${key}', ${id})">Eliminar</button>
    </div>
  `;
}

/**
 * Cambia estado de un elemento dentro de dataStore[key]
 * (ej. usuarios/espacios/materiales)
 */
function setEstado(key, id, st){
  const arr = dataStore[key];
  const it = arr.find(x=>x.id===id);
  if(!it) return;

  it.estado = st;
  renderAll();
}

/**
 * Elimina un elemento por id dentro de dataStore[key]
 * mostrando confirmación antes.
 */
function delItem(key, id){
  if(!confirm("¿Eliminar?")) return;

  dataStore[key] = dataStore[key].filter(x=>x.id!==id);
  renderAll();
}

/* =========================
   AVERÍAS (cambio de estado específico)
==========================*/

/**
 * Crea los botones para cambiar el estado de una avería.
 * tipo = "material" o "it"
 */
function estadoButtons(tipo, id, estadoActual){
  // Función interna: crea un botón con estado destino
  const mk = (label, st, cls)=>{
    // Si ya está en ese estado, se deshabilita el botón
    const disabled = estadoActual===st 
      ? "disabled style='opacity:.55; cursor:not-allowed;'" 
      : "";

    return `<button class="mini ${cls}" ${disabled} onclick="cambiarEstadoAveria('${tipo}', ${id}, '${st}')">${label}</button>`;
  };

  return `
    ${mk("Recibido","recibido","info")}
    ${mk("Trabajando","trabajando","warning")}
    ${mk("Cierre OK","ok","primary")}
    ${mk("Cierre FALLO","fallo","danger")}
  `;
}

/**
 * Actualiza el estado de una avería (material o IT) y re-renderiza.
 */
function cambiarEstadoAveria(tipo, id, st){
  const key = (tipo==="material") ? "averiasMaterial" : "averiasIT";
  const arr = dataStore[key];
  const a = arr.find(x=>x.id===id);
  if(!a) return;

  a.estado = st;
  renderAll();
}

/* =========================
   STATS
==========================*/

/**
 * Genera una "caja" de estadística (HTML)
 * k = título
 * v = valor
 * h = ayuda / descripción
 */
function statBox(k,v,h){
  return `<div class="stat"><div class="k">${k}</div><div class="v">${v}</div><div class="h">${h}</div></div>`;
}

/**
 * Renderiza estadísticas en las vistas stats:
 * - stats IT (averías)
 * - stats espacios (reservas)
 * - stats material (reservas)
 */
function renderStats(){
  // Datos principales
  const averiasIT = dataStore.averiasIT;
  const reservas = dataStore.reservas;

  // ---- Estadísticas IT ----
  const totalIT = averiasIT.length;

  // Función para contar por estado
  const count = (st)=>averiasIT.filter(a=>a.estado===st).length;

  $("#statsIT").innerHTML = `
    ${statBox("Total incidencias", totalIT, "Averías registradas")}
    ${statBox("Pendientes", count("recibido"), "Recibido")}
    ${statBox("En curso", count("trabajando"), "Trabajando")}
    ${statBox("Cierre OK", count("ok"), "Resueltas")}
    ${statBox("Cierre Fallo", count("fallo"), "No resueltas")}
    ${statBox("Tasa éxito", totalIT? Math.round((count("ok")/totalIT)*100) + "%":"—", "OK / Total")}
  `;

  // ---- Estadísticas reservas ----
  const totalRes = reservas.length;
  const resEsp = reservas.filter(r=>r.tipo==="Espacio").length;
  const resMat = reservas.filter(r=>r.tipo==="Material").length;

  // Reservas de espacios
  $("#statsEspacios").innerHTML = `
    ${statBox("Total reservas", totalRes, "Todas")}
    ${statBox("Reservas espacios", resEsp, "Aulas, lab...")}
    ${statBox("Ratio espacios", totalRes? Math.round((resEsp/totalRes)*100) + "%":"—", "Espacios / Total")}
  `;

  // Reservas de material
  $("#statsMaterial").innerHTML = `
    ${statBox("Total reservas", totalRes, "Todas")}
    ${statBox("Reservas material", resMat, "Portátiles, proyector...")}
    ${statBox("Ratio material", totalRes? Math.round((resMat/totalRes)*100) + "%":"—", "Material / Total")}
  `;
}

/* =========================
   DESPLEGABLES + CAMPOS (configuración de formularios)
==========================*/

/**
 * Renderiza lista de valores de un desplegable.
 * Según el "tipo" seleccionado en #ddlTipoLista.
 */
function renderDesplegables(){
  const tipo = $("#ddlTipoLista").value; // qué lista estamos editando
  const arr = dataStore.desplegables[tipo] || [];
  countHint.textContent = `${arr.length} elemento(s)`;

  $("#tblDesplegables").innerHTML = `
    <thead><tr><th>#</th><th>Valor</th><th>Acciones</th></tr></thead>
    <tbody>
      ${arr.map((v,i)=>`
        <tr>
          <td>${i+1}</td>
          <td><b>${v}</b></td>
          <td><div class="actions">
            <button class="mini danger" onclick="deleteDesplegable('${tipo}', ${i})">Eliminar</button>
          </div></td>
        </tr>
      `).join("")}
    </tbody>
  `;
}

/**
 * Añade un valor nuevo al desplegable seleccionado.
 */
function addDesplegable(){
  const tipo = $("#ddlTipoLista").value;
  const v = $("#ddlNuevoValor").value.trim();
  if(!v) return alert("Escribe un valor.");

  dataStore.desplegables[tipo].push(v);
  $("#ddlNuevoValor").value="";
  renderDesplegables();
}

/**
 * Elimina un valor del desplegable por índice.
 */
function deleteDesplegable(tipo, idx){
  if(!confirm("¿Eliminar elemento?")) return;

  dataStore.desplegables[tipo].splice(idx,1);
  renderDesplegables();
}

/**
 * Renderiza tabla de campos de formulario (activar/desactivar).
 * Aplica búsqueda por el campo "campo".
 */
function renderCampos(){
  const arr = filterSearch(dataStore.camposFormulario, ["campo"]);

  $("#tblCampos").innerHTML = `
    <thead><tr><th>Campo</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
      ${arr.map(c=>`
        <tr>
          <td><b>${c.campo}</b></td>
          <td>${c.activo 
              ? `<span class="badge b-active">Activo</span>` 
              : `<span class="badge b-paused">Desactivado</span>`}</td>
          <td><div class="actions">
            <button class="mini ${c.activo?'warning':'info'}" onclick="toggleCampo(${c.id})">
              ${c.activo?'Desactivar':'Activar'}
            </button>
          </div></td>
        </tr>
      `).join("")}
    </tbody>
  `;
}

/**
 * Activa/Desactiva un campo por id.
 */
function toggleCampo(id){
  const c = dataStore.camposFormulario.find(x=>x.id===id);
  if(!c) return;

  c.activo = !c.activo;
  renderCampos();
}

/* =========================
   VIEW SWITCH
==========================*/

/**
 * Cambia de sección/vista del panel.
 * - actualiza currentView
 * - muestra/oculta bloques
 * - marca botón activo
 * - actualiza titulo/subtitulo y botón principal
 * - limpia buscador
 * - re-renderiza
 */
function setView(key){
  currentView = key;

  // Oculta todas las vistas
  $$(".view").forEach(v => v.classList.add("hidden"));

  // Muestra la vista elegida
  $("#view-" + key).classList.remove("hidden");

  // Marca navegación activa
  $$(".navbtn").forEach(b => b.classList.remove("active"));
  document.querySelector(`.navbtn[data-view="${key}"]`)?.classList.add("active");

  // Actualiza títulos
  viewTitle.textContent = meta[key].title;
  viewSubtitle.textContent = meta[key].subtitle;

  // Configura botón principal según la vista
  if(meta[key].actionFn){
    btnPrimaryAction.classList.remove("hidden");
    btnPrimaryAction.textContent = meta[key].actionText;
    btnPrimaryAction.onclick = meta[key].actionFn;
  }else{
    btnPrimaryAction.classList.add("hidden");
    btnPrimaryAction.onclick = null;
  }

  // Limpia búsqueda y re-renderiza contenido
  searchInput.value = "";
  renderAll();
}

/* =========================
   EVENTS + INIT (eventos e inicio)
==========================*/

// Click en botones de navegación
$$(".navbtn").forEach(btn => 
  btn.addEventListener("click", ()=>setView(btn.dataset.view))
);

// Al escribir en el buscador => vuelve a renderizar (aplica filtro)
searchInput.addEventListener("input", renderAll);

// Botón refresh
btnRefresh.addEventListener("click", ()=>alert("NO IMPLEMENTADO. CONECTAR CON BASE DE DATOS."));

// Vista inicial
setView("usuarios");
renderAll();
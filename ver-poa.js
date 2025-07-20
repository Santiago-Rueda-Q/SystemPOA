document.addEventListener("DOMContentLoaded", () => {
  const lista = document.getElementById("actividad-lista");
  const filtroNombre = document.getElementById("filtroNombre");
  const filtroSemestre = document.getElementById("filtroSemestre");

  function cargarActividades() {
    const actividades = JSON.parse(localStorage.getItem("actividadesPOA")) || [];

    const nombreFiltro = filtroNombre.value.toLowerCase();
    const semestreFiltro = filtroSemestre.value;

    const filtradas = actividades.filter(act => {
      const nombreCoincide = !nombreFiltro || (act.tipo && act.tipo.toLowerCase() === nombreFiltro);
      const semestreCoincide = !semestreFiltro || (act.semestre && act.semestre == semestreFiltro);
      return nombreCoincide && semestreCoincide;
    });

    lista.innerHTML = "";

    if (filtradas.length === 0) {
      lista.innerHTML = "<p>No se encontraron actividades.</p>";
      return;
    }

    filtradas.forEach((act, i) => {
      const div = document.createElement("div");
      div.classList.add("actividad");
      div.innerHTML = `
        <strong>Tipo:</strong> ${act.tipo} <br>
        <strong>Descripción:</strong> ${act.descripcion || "No definida"} <br>
        <strong>Fecha:</strong> ${act.fecha || "No definida"} <br>
        <strong>Responsable:</strong> ${act.responsable || "No definido"} <br>
        <strong>Resultado:</strong> ${act.resultado || "No definido"} <br>
        <strong>Estado:</strong> ${act.estado || "Pendiente"} <br>
        <strong>Semestre:</strong> ${act.semestre || "N/A"}
      `;
      lista.appendChild(div);
    });
  }

  filtroNombre.addEventListener("change", cargarActividades);
  filtroSemestre.addEventListener("change", cargarActividades);

  cargarActividades();
});

function exportarPDF() {
  const elemento = document.getElementById("actividad-lista");
  const opt = {
    margin: 0.5,
    filename: 'actividades_poa.pdf',
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
  };
  html2pdf().from(elemento).set(opt).save();
}

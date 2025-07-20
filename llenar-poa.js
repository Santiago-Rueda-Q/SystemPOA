const categorias = [
  "unificacion", "ppa", "visita_regional", "visita_nacional", "charlas", "feria", "congreso", "evento_nacional",
  "biblio", "profesor_visitante", "actividades_interculturales", "actividades_universidades_internacionales",
  "productos_en_ingles", "ppa_traducidos", "coil", "clase_espejo", "reto_empresa", "grupo_focal",
  "estudio_tendencias", "analisis_contexto", "autoevaluacion_mejoras", "empresas_practicas",
  "graduados", "mesa_sector", "mejoras_practicas", "diplomado_grado", "formacion_continua", "mbc_refuerzos",
  "taller_refuerzo_saber", "proyecto_grado", "visita_aula", "estudio_semilleros_estudiantes",
  "estudio_semilleros_docentes", "pep", "herramientas", "micrositio", "modalidad_virtual", "sitio_interaccion",
  "as", "seguimiento_estudiantes", "matricula_estudiantes_antiguos"
];

const categoriasList = document.getElementById("poa-categories");
const poaForm = document.getElementById("poa-form");
const formTitle = document.getElementById("form-title");

categorias.forEach(cat => {
  const li = document.createElement("li");
  li.textContent = cat.replaceAll("_", " ").toUpperCase();
  li.onclick = () => mostrarFormulario(cat);
  categoriasList.appendChild(li);
});

function mostrarFormulario(nombreCategoria) {
  formTitle.textContent = `Formulario: ${nombreCategoria.replaceAll("_", " ").toUpperCase()}`;
  poaForm.classList.remove("hidden");
  poaForm.setAttribute("data-categoria", nombreCategoria);
}

poaForm.addEventListener("submit", function (e) {
  e.preventDefault();
  const formData = new FormData(poaForm);
  const actividad = {
    categoria: poaForm.getAttribute("data-categoria"),
    descripcion: formData.get("descripcion"),
    responsable: formData.get("responsable"),
    fecha: formData.get("fecha"),
    resultado: formData.get("resultado"),
    estado: formData.get("estado"),
    evidencia_link: formData.get("evidencia_link"),
    nivel_formacion: formData.get("nivel_formacion"),
    semestre: formData.get("semestre"),
    id: Date.now()
  };

  const actividades = JSON.parse(localStorage.getItem("actividadesPOA") || "[]");
  actividades.push(actividad);
  localStorage.setItem("actividadesPOA", JSON.stringify(actividades));
  alert("Actividad guardada con éxito.");
  poaForm.reset();
  poaForm.classList.add("hidden");
});

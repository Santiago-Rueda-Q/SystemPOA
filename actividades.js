
// actividades.js

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formActividad");
  const lista = document.getElementById("listaActividades");
  const grafico = document.getElementById("graficoAvance");

  let actividades = JSON.parse(localStorage.getItem("actividadesPOA")) || [];

  function guardarActividades() {
    localStorage.setItem("actividadesPOA", JSON.stringify(actividades));
  }

  function renderizarActividades() {
    lista.innerHTML = "";
    actividades.forEach((act, index) => {
      const item = document.createElement("div");
      item.className = "actividad-item" + (act.completada ? " completed" : "");
      item.innerHTML = `
        <h3>${act.tipo} - ${act.fecha}</h3>
        <p><strong>Responsable:</strong> ${act.responsable}</p>
        <p><strong>Descripción:</strong> ${act.descripcion}</p>
        <p><strong>Resultado:</strong> ${act.resultado}</p>
        <input type="checkbox" class="check-completo" ${act.completada ? "checked" : ""} data-index="${index}">
      `;
      lista.appendChild(item);
    });

    document.querySelectorAll(".check-completo").forEach(checkbox => {
      checkbox.addEventListener("change", function () {
        const idx = this.getAttribute("data-index");
        actividades[idx].completada = this.checked;
        guardarActividades();
        renderizarActividades();
        actualizarGrafico();
      });
    });
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const tipo = document.getElementById("tipo").value;
    const descripcion = document.getElementById("descripcion").value;
    const fecha = document.getElementById("fecha").value;
    const responsable = document.getElementById("responsable").value;
    const resultado = document.getElementById("resultado").value;

    if (tipo && descripcion && fecha && responsable && resultado) {
      actividades.push({
        tipo,
        descripcion,
        fecha,
        responsable,
        resultado,
        completada: false
      });
      guardarActividades();
      renderizarActividades();
      actualizarGrafico();
      form.reset();
    } else {
      alert("Por favor, completa todos los campos.");
    }
  });

  // =====================
  // GRÁFICO Chart.js
  // =====================
  let chart;

  function actualizarGrafico() {
    const total = actividades.length;
    const completadas = actividades.filter(a => a.completada).length;

    const data = {
      labels: ["Completadas", "Pendientes"],
      datasets: [{
        data: [completadas, total - completadas],
        backgroundColor: ["#195da2", "#85b7e9"],
        borderWidth: 1
      }]
    };

    if (chart) {
      chart.data = data;
      chart.update();
    } else {
      chart = new Chart(grafico, {
        type: 'doughnut',
        data: data,
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                color: "#07396b",
                font: {
                  weight: 'bold'
                }
              }
            }
          }
        }
      });
    }
  }

  // =====================
  // PDF - html2pdf
  // =====================
  window.generarPDF = function () {
    const elemento = document.querySelector(".container");
    html2pdf()
      .set({
        margin: 1,
        filename: "reporte_actividades_POA.pdf",
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
      })
      .from(elemento)
      .save();
  }

  // Inicializar
  renderizarActividades();
  actualizarGrafico();
});

// dashboard.js
let charts = {};
const colors = {
    primary: '#3b8cde',
    secondary: '#195da2',
    tertiary: '#406e9b',
    quaternary: '#07396b',
    light: '#85b7e9',
    neutral: '#e5e7eb'
};

document.addEventListener('DOMContentLoaded', () => {
    console.log('SystemPOA Dashboard cargado');
    cargarDatos();
    setInterval(cargarDatos, 300000); // cada 5 minutos
});

// prueba
crearGraficoCategorias(data.por_categoria);  // ya lo tienes
crearGraficoComponentes(data.por_componente); // ya lo tienes
crearGraficoNiveles(data.por_nivel); // ya lo tienes
crearGraficoInternacionalizacion(data.internacionalizacion); // NUEVO

//prueba hasta aqui

async function cargarDatos() {
    mostrarLoading(true);
    try {
        const response = await fetch('dashboard-data.php');

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        if (data.error) throw new Error(data.error);

        actualizarMetricas(data);
        crearGraficos(data);

        mostrarNotificacion('Datos cargados correctamente', 'success');
    } catch (error) {
        console.error('Error cargando datos:', error);
        mostrarNotificacion('Error cargando datos: ' + error.message, 'error');
    } finally {
        mostrarLoading(false);
    }
}

function actualizarMetricas(data) {
    document.getElementById('total-actividades').textContent = data.total_actividades || 0;
    document.getElementById('actividades-completadas').textContent = data.actividades_completadas || 0;
    document.getElementById('porcentaje-completado').textContent =
        data.total_actividades > 0 ?
        Math.round((data.actividades_completadas / data.total_actividades) * 100) + '%' :
        '0%';
    document.getElementById('actividades-progreso').textContent =
        (data.total_actividades || 0) - (data.actividades_completadas || 0);

    document.getElementById('ppa-registrados').textContent = data.por_categoria?.ppa || 0;
    document.getElementById('ultima-actualizacion').textContent = new Date().toLocaleString('es-ES');
}

function crearGraficos(data) {
    crearGraficoCategorias(data.por_categoria);
    crearGraficoNiveles(data.por_nivel);
    crearGraficosComponentes(data.por_componente);

    //prueba
    function crearGraficoInternacionalizacion(datos) {
    const labels = [];
    const valores = [];
    const bg = [];

    const nombres = {
        ppa: 'PPA',
        visita_regional: 'Visitas',
        charlas: 'Charlas',
        productos_ingles: 'Prod. Inglés',
        ppa_traducidos: 'PPA Traducidos',
        otros: 'Otros'
    };

    Object.entries(datos || {}).forEach(([key, val], i) => {
        if (val > 0) {
            labels.push(nombres[key] || key);
            valores.push(val);
            bg.push(Object.values(colors)[i % 5]);
        }
    });

    if (valores.length === 0) {
        labels.push('Sin datos');
        valores.push(1);
        bg.push(colors.neutral);
    }

    renderChart('chart-internacionalizacion', 'bar', {
        labels: labels,
        datasets: [{
            label: 'Actividades Internacionalización',
            data: valores,
            backgroundColor: bg,
            borderWidth: 1
        }]
    });
}

    //prueba hasta aqui 
}

function crearGraficoCategorias(categorias) {
    const labels = [];
    const valores = [];
    const bg = [];

    const nombres = {
        ppa: 'PPA',
        visita_regional: 'Visitas Regionales',
        charlas: 'Charlas',
        proyecto_grado: 'Proyectos de Grado',
        otros: 'Otros'
    };

    Object.entries(categorias || {}).forEach(([key, val], i) => {
        if (val > 0) {
            labels.push(nombres[key] || key);
            valores.push(val);
            bg.push(Object.values(colors)[i % 5]);
        }
    });

    if (valores.length === 0) {
        labels.push('Sin datos');
        valores.push(1);
        bg.push(colors.neutral);
    }

    renderChart('chart-categorias', 'doughnut', {
        labels: labels,
        datasets: [{
            data: valores,
            backgroundColor: bg,
            borderWidth: 1,
            borderColor: '#fff'
        }]
    });
}
//prueba 

function crearGraficointernacionalizacion (internacionalizacion ) {
    const labels = [];
    const valores = [];
    const bg = [];

    const nombres = {
        ppa: 'PPA',
        visita_regional: 'Visitas Regionales',
        charlas: 'Charlas',
        proyecto_grado: 'Proyectos de Grado',
        otros: 'Otros'
    };

    Object.entries(internacionalizacion  || {}).forEach(([key, val], i) => {
        if (val > 0) {
            labels.push(nombres[key] || key);
            valores.push(val);
            bg.push(Object.values(colors)[i % 5]);
        }
    });

    if (valores.length === 0) {
        labels.push('Sin datos');
        valores.push(1);
        bg.push(colors.neutral);
    }

    renderChart('chart-categorias', 'doughnut', {
        labels: labels,
        datasets: [{
            data: valores,
            backgroundColor: bg,
            borderWidth: 1,
            borderColor: '#fff'
        }]
    });
}


//hasta aqui prueba 
function crearGraficoNiveles(niveles) {
    const labels = [];
    const valores = [];

    Object.entries(niveles || {}).forEach(([nivel, val]) => {
        if (val > 0) {
            labels.push(nivel);
            valores.push(val);
        }
    });

    if (valores.length === 0) {
        labels.push('Sin datos');
        valores.push(1);
    }

    renderChart('chart-niveles', 'bar', {
        labels,
        datasets: [{
            label: 'Actividades',
            data: valores,
            backgroundColor: colors.primary,
            borderRadius: 8
        }]
    }, {
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: '#e5e7eb' }
            },
            x: {
                grid: { display: false }
            }
        }
    });
}

// desde aqui prueba 
function crearGraficointernacionalizacion (data) {
    const labels = [];
    const valores = [];
    const bg = [];

    const nombres = {
        ppa: 'PPA',
        visita_regional: 'Visitas Regionales',
        ferias: 'Ferias',
        coil: 'COIL',
        clase_espejo: 'Clase Espejo',
        productos_en_ingles: 'Productos en Inglés',
        ppa_traducidos: 'PPA Traducidos',
        otros: 'Otros'
    };

    Object.entries(data || {}).forEach(([key, val], i) => {
        if (val > 0) {
            labels.push(nombres[key] || key);
            valores.push(val);
            bg.push(Object.values(colors)[i % 5]);
        }
    });

    if (valores.length === 0) {
        labels.push('Sin datos');
        valores.push(1);
        bg.push(colors.neutral);
    }

    renderChart('chart-internacionalizacion', 'doughnut', {
        labels: labels,
        datasets: [{
            data: valores,
            backgroundColor: bg,
            borderWidth: 1,
            borderColor: '#fff'
        }]
    }, {
        plugins: {
            legend: { position: 'bottom' }
        }
    });
}


// hasta aqui 


function crearGraficosComponentes(componentes) {
    const ids = ['1', '2', '3', '4', '5'];
    ids.forEach(id => {
        const valor = componentes?.[id] || 0;
        const chartData = valor > 0 ? [valor, Math.max(1, 10 - valor)] : [0, 1];
        const chartColors = valor > 0 ? [colors.secondary, colors.neutral] : [colors.neutral, '#f9fafb'];

        renderChart(`chart-${id}`, 'doughnut', {
            datasets: [{
                data: chartData,
                backgroundColor: chartColors,
                borderWidth: 0,
                cutout: '70%'
            }]
        }, {
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        });

        const countEl = document.getElementById(`count-${id}`);
        if (countEl) countEl.textContent = valor;
    });
}

function renderChart(id, type, data, options = {}) {
    const ctx = document.getElementById(id);
    if (!ctx) return;

    if (charts[id]) charts[id].destroy();

    charts[id] = new Chart(ctx, {
        type,
        data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: type === 'line' ? 'top' : 'bottom',
                    labels: { boxWidth: 12, padding: 10 }
                }
            },
            ...options
        }
    });
}

function mostrarLoading(show = true) {
    const overlay = document.getElementById('loading-overlay');
    const icon = document.getElementById('refresh-icon');
    if (overlay) {
        overlay.classList.toggle('hidden', !show);
        overlay.classList.toggle('flex', show);
    }
    if (icon) icon.classList.toggle('fa-spin', show);
}

function mostrarNotificacion(msg, tipo = 'info') {
    const colores = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    const iconos = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };

    let noti = document.createElement('div');
    noti.className = `fixed top-4 right-4 z-50 px-4 py-3 text-white rounded shadow-lg flex items-center space-x-3 ${colores[tipo] || colores.info}`;
    noti.innerHTML = `
        <i class="${iconos[tipo] || iconos.info}"></i>
        <span>${msg}</span>
        <button onclick="this.parentElement.remove()" class="ml-2 text-lg font-bold">&times;</button>
    `;
    document.body.appendChild(noti);
    setTimeout(() => noti.remove(), 5000);
}

// dashboard.js

document.addEventListener("DOMContentLoaded", function() {
    // Datos para los gráficos usando valores del dashboard (ajusta según tus datos reales)
    const dataCounts = {
      localesEnServicio: parseInt(document.getElementById("totalLocalesEnServicio").textContent),
      regulares: parseInt(document.getElementById("totalRegulares").textContent),
      irregulares: parseInt(document.getElementById("totalIrregulares").textContent)
    };
  
    // Gráfico de Dona: Distribución de Locales
    const ctxPie = document.getElementById("pieChart").getContext("2d");
    new Chart(ctxPie, {
      type: "doughnut",
      data: {
        labels: ["Locales en Servicio", "Locales Regulares", "Locales Irregulares"],
        datasets: [{
          data: [dataCounts.localesEnServicio, dataCounts.regulares, dataCounts.irregulares],
          backgroundColor: ["#7e57c2", "#ab47bc", "#ce93d8"]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom"
          },
          title: {
            display: true,
            text: "Distribución de Locales"
          }
        }
      }
    });
  
    // Gráfico de Barras: Cantidad de Locales por Categoría
    const ctxBar = document.getElementById("barChart").getContext("2d");
    new Chart(ctxBar, {
      type: "bar",
      data: {
        labels: ["Locales en Servicio", "Locales Regulares", "Locales Irregulares"],
        datasets: [{
          label: "Cantidad de Locales",
          data: [dataCounts.localesEnServicio, dataCounts.regulares, dataCounts.irregulares],
          backgroundColor: ["#7e57c2", "#ab47bc", "#ce93d8"]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { precision: 0 }
          }
        },
        plugins: {
          title: {
            display: true,
            text: "Locales por Categoría"
          }
        }
      }
    });
  });
  
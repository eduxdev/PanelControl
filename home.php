<?php
// home.php - Dashboard

// Aquí puedes incluir la lógica PHP para obtener datos del API y calcular totales
// Ejemplo (utiliza la lógica que ya tienes):
date_default_timezone_set('America/Mexico_City');

// Obtener todos los contratos de la API (sin paginación)
$curl = curl_init();
$httpheader = ['DOLAPIKEY: web123456789'];
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts?limit=0";
curl_setopt_array($curl, [
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => $httpheader
]);
$result = curl_exec($curl);
curl_close($curl);

$contracts = json_decode($result, true);
if (json_last_error() !== JSON_ERROR_NONE) {
  echo "<div class='error'>Error al decodificar JSON: " . json_last_error_msg() . "</div>";
  exit;
}

// Inicializamos contadores (Ejemplo: adapta según tus criterios)
$totalLocalesEnServicio = 0;
$totalRegulares = 0;
$totalIrregulares = 0;

// Recorremos la lista de contratos
foreach ($contracts as $data) {
  if ($data["nbofservicesopened"] >= 1) {
    $totalLocalesEnServicio++;

    // Obtenemos la línea más reciente
    $recentLine = null;
    if (isset($data["lines"]) && is_array($data["lines"])) {
      foreach ($data["lines"] as $line) {
        $dateStart = DateTime::createFromFormat('U', $line["date_start"]);
        $dateEnd   = DateTime::createFromFormat('U', $line["date_end"]);
        if ($dateStart && $dateEnd) {
          if (!$recentLine || $dateStart > $recentLine["dateStart"]) {
            $recentLine = [
              'id' => $line["id"],
              'dateStart' => $dateStart,
              'dateEnd' => $dateEnd
            ];
          }
        }
      }
    }
    if (!$recentLine) continue;

    // Clasificación: si nbofservicesexpired != 1, es regular; si es 1, es irregular
    if ($data["nbofservicesexpired"] != 1) {
      $totalRegulares++;
    } else {
      // Para irregulares, evaluamos si el contrato ya está vencido o vence mañana
      $hoy = new DateTime();
      $finContrato = clone $recentLine["dateEnd"];
      $finContrato->setTime(0, 0);
      $hoy->setTime(0, 0);
      $diff = $hoy->diff($finContrato);
      $diasRestantes = $diff->days * ($diff->invert ? -1 : 1);
      if ($diasRestantes <= 0 || $diasRestantes == 1) {
        $totalIrregulares++;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Plaza Shopping Center</title>
  <!-- Enlaza la hoja de estilos externa -->
  <link rel="stylesheet" href="css/dashboard.css">
  <!-- Incluir Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <?php include 'sidebar.php'; ?>

  <div class="main-content">
    <!-- Información de Totales -->
    <div class="dashboard-container">
      <div class="info-box">
        <h2>Total Locales en Servicio</h2>
        <p id="totalLocalesEnServicio"><?php echo $totalLocalesEnServicio; ?></p>
      </div>
      <div class="info-box">
        <h2>Locales Regulares</h2>
        <p id="totalRegulares"><?php echo $totalRegulares; ?></p>
      </div>
      <div class="info-box">
        <h2>Locales Irregulares</h2>
        <p id="totalIrregulares"><?php echo $totalIrregulares; ?></p>
      </div>
      <div class="info-box">
        <h2>Total Locales (Regulares + Irregulares)</h2>
        <p id="totalSuma"><?php echo $totalRegulares + $totalIrregulares; ?></p>
      </div>
    </div>

    <!-- Gráficos -->
    <div class="dashboard-container">
      <div class="chart-container">
        <canvas id="pieChart"></canvas>
      </div>
      <div class="chart-container">
        <canvas id="barChart"></canvas>
      </div>
    </div>
  </div>
  <!-- Enlaza el archivo JavaScript externo -->
  <script src="js/dashboard.js"></script>
</body>

</html>
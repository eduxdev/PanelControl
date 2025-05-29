<?php
// regulares.php

// Inicializamos la solicitud cURL para obtener los contratos
$curl = curl_init();
$httpheader = ['DOLAPIKEY: web123456789'];
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts?limit=0"; // Se agrega ?limit=0 para eliminar el límite
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
$result = curl_exec($curl);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos - Clientes que ya pagaron</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Contratos - Clientes que ya pagaron</h1>
        </div>
        <div class="contracts-container">
            <?php
            if ($result === false) {
                echo "<div class='error'>Error en la solicitud cURL: " . curl_error($curl) . "</div>";
            } else {
                $contracts = json_decode($result, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    foreach ($contracts as $data) {
                        // Verificamos que el contrato tenga servicios abiertos
                        if ($data["nbofservicesopened"] >= 1) {
                            // Obtenemos la línea más reciente del contrato
                            $recentLine = null;
                            foreach ($data["lines"] as $line) {
                                if ($recentLine === null || $line["date_start"] > $recentLine["date_start"]) {
                                    $recentLine = $line;
                                }
                            }
                            if (!$recentLine) continue;

                            // Si el contrato tiene servicio vencido, se omite (ya que no representa un pago completo)
                            if ($data["nbofservicesexpired"] == 1) {
                                continue;
                            }
                            
                            // Obtenemos el estado de pago: buscamos el campo "statut" o "status"
                            $status = $recentLine["statut"] ?? $recentLine["status"] ?? null;
                            
                            // Solo mostramos contratos que ya pagaron (status == 4)
                            if ($status == 4) {
                                $currentTime = time();
                                
                                // Calculamos la duración en días del periodo de la línea
                                $duration = ($recentLine["date_end"] - $recentLine["date_start"]) / 86400;
                                
                                // Si la duración es mayor a 35 días (más de 1 mes), calculamos los días restantes de forma mensual
                                if ($duration > 35) {
                                    // Creamos un objeto DateTime a partir de la fecha de inicio
                                    $startDate = new DateTime();
                                    $startDate->setTimestamp($recentLine["date_start"]);
                                    
                                    // Clonamos la fecha de inicio para calcular el próximo vencimiento mensual
                                    $dueDate = clone $startDate;
                                    while ($dueDate->getTimestamp() <= $currentTime) {
                                        $dueDate->modify('+1 month');
                                    }
                                    $daysLeft = ceil(($dueDate->getTimestamp() - $currentTime) / 86400);
                                } else {
                                    // Si la duración es aproximadamente de un mes, usamos la fecha final real
                                    $daysLeft = ceil(($recentLine["date_end"] - $currentTime) / 86400);
                                }
                                
                                if ($daysLeft < 0) {
                                    $daysLeft = 0;
                                }
                                
                                // Mostrar la información con el estilo deseado
                                echo "<div class='contract-card'>";
                                echo "<h2><i class='fas fa-store'></i> " . $data["ref_customer"] . "</h2>";
                                echo "<p><i class='fas fa-hashtag'></i> Referencia: " . $data["ref"] . "</p>";
                                echo "<p><i class='fas fa-calendar-alt'></i> Inicio: " . date("d-m-Y", $recentLine["date_start"]) . "</p>";
                                echo "<p><i class='fas fa-calendar-times'></i> Fin: " . date("d-m-Y", $recentLine["date_end"]) . "</p>";
                                echo "<p class='status pagado'><i class='fas fa-info-circle'></i> YA PAGÓ (" . $daysLeft . " días restantes)</p>";
                                echo "</div>";
                            }
                        }
                    }
                } else {
                    echo "<div class='error'>Error al decodificar la respuesta JSON</div>";
                }
            }
            curl_close($curl);
            ?>
        </div> <!-- Cierre del contenedor de cards -->
    </div> <!-- Cierre del main-content -->
</body>
</html>

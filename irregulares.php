<?php

use function PHPSTORM_META\map;

date_default_timezone_set('America/Mexico_City');

// Obtener contratos con al menos 1 servicio expirado
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts?limit=0",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['DOLAPIKEY: web123456789']
]);
$result = curl_exec($curl);
curl_close($curl);

if ($result !== false) {
    $contracts = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $filteredContracts = array_filter($contracts, function($contract) {
            return isset($contract['nbofservicesexpired']) && $contract['nbofservicesexpired'] >= 1;
        });
    } else {
        echo "<div class='error-msg'>Error al decodificar los datos de contratos.</div>";
    }
} else {
    echo "<div class='error-msg'>Error al obtener los datos de la API.</div>";
}

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Contratos Irregulares</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .btn-api, .btn-whatsapp {
            display: inline-block;
            padding: 8px 12px;
            margin: 5px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-api {
            background-color: #007bff;
            color: white;
            border: none;
        }
        
        .btn-api:hover {
            background-color: #0056b3;
        }
        
        .btn-whatsapp {
            background-color: #25D366;
            color: white;
            border: none;
        }
        
        .btn-whatsapp:hover {
            background-color: #128C7E;
        }

        .whatsapp-links-container {
            margin-top: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }

        .whatsapp-links-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 300px;
            overflow-y: auto;
        }

        .whatsapp-links-list li {
            padding: 8px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-whatsapp-small {
            background-color: #25D366;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 0.9em;
        }

        .btn-whatsapp-small:hover {
            background-color: #128C7E;
        }
    </style>
</head>
<body>';

// Se incluye el sidebar (igual que en home.php)
include 'sidebar.php';

echo '
<div class="main-content">
    <div class="header">
        <h1><i class="fas fa-chart-line"></i> Contratos Irregulares</h1>
    </div>';

// Formulario global para enviar mensaje a todos los irregulares
echo '<div class="global-send">
        <div class="whatsapp-links-container">
            <button id="toggleWhatsappLinks" class="btn-whatsapp"><i class="fab fa-whatsapp"></i> Ver Enlaces Directos</button>
            <div id="whatsappLinks" style="display: none; margin-top: 10px;">';

// Agregar enlaces directos de WhatsApp para cada contrato irregular
if (!empty($filteredContracts)) {
    echo '<ul class="whatsapp-links-list">';
    foreach ($filteredContracts as $data) {
        // Solo consideramos contratos con servicios abiertos
        if ($data["nbofservicesopened"] < 1) continue;
        
        // Buscar la línea más reciente del contrato
        $recentLine = null;
        foreach ($data["lines"] as $line) {
            $dateStart = DateTime::createFromFormat('U', $line["date_start"]);
            $dateEnd   = DateTime::createFromFormat('U', $line["date_end"]);
            if ($dateStart && $dateEnd) {
                if (!$recentLine || $dateStart > $recentLine["dateStart"]) {
                    $recentLine = [
                        'id'        => $line["id"],
                        'dateStart' => $dateStart,
                        'dateEnd'   => $dateEnd
                    ];
                }
            }
        }
        if (!$recentLine) continue;
        
        // Calcular los días restantes
        $hoy = new DateTime();
        $finContrato = clone $recentLine['dateEnd'];
        $finContrato->setTime(0, 0);
        $hoy->setTime(0, 0);
        
        $diferencia = $hoy->diff($finContrato);
        $diasRestantes = $diferencia->days * ($diferencia->invert ? -1 : 1);
        
        // Filtrar: mostrar solo si el contrato está vencido (<=0) o vence mañana (==1)
        if (!($diasRestantes <= 0 || $diasRestantes == 1)) continue;
        
        // Definir el mensaje según los días restantes
        $mensaje = "";
        if ($diasRestantes == 1) {
            $mensaje = "Estimado arrendatario del local {$data['ref_customer']}, sus datos indican que su renta vence el día de mañana. Si ya ha pagado ignore este mensaje.";
        } elseif ($diasRestantes <= 0) {
            $diasVencidos = abs($diasRestantes);
            if ($diasVencidos == 0) {
                $mensaje = "Estimado arrendatario del local {$data['ref_customer']}, sus datos indican que su renta vence hoy. Por favor, pase a pagar. Si ya ha pagado ignore este mensaje. Gracias.";
            } else {
                $mensaje = "Estimado arrendatario del local {$data['ref_customer']}, sus datos indican que su renta está vencida desde hace $diasVencidos día(s). Por favor, pase a pagar. Si ya ha pagado ignore este mensaje. Gracias.";
            }
        }
        
        // Obtener y limpiar el número de teléfono
        $telefono = $data["array_options"]["options_numero_de_telefono_"] ?? '';
        $telefonoLimpio = preg_replace('/[\s\(\)\-\+]/', '', $telefono);
        
        if (!empty($telefonoLimpio)) {
            echo "<li>";
            echo "<span>{$data['ref_customer']}</span>: ";
            echo "<a href='https://wa.me/$telefonoLimpio?text=" . urlencode($mensaje) . "' target='_blank' class='btn-whatsapp-small'>";
            echo "<i class='fab fa-whatsapp'></i> Contactar</a>";
            echo "</li>";
        }
    }
    echo '</ul>';
}

echo '      </div>
        </div>
      </div>';

echo '<div class="contracts-container">';

if (!empty($filteredContracts)) {
    foreach ($filteredContracts as $data) {
        // Solo consideramos contratos con servicios abiertos
        if ($data["nbofservicesopened"] < 1) continue;
        
        // Buscar la línea más reciente del contrato
        $recentLine = null;
        foreach ($data["lines"] as $line) {
            $dateStart = DateTime::createFromFormat('U', $line["date_start"]);
            $dateEnd   = DateTime::createFromFormat('U', $line["date_end"]);
            if ($dateStart && $dateEnd) {
                if (!$recentLine || $dateStart > $recentLine["dateStart"]) {
                    $recentLine = [
                        'id'        => $line["id"],
                        'dateStart' => $dateStart,
                        'dateEnd'   => $dateEnd
                    ];
                }
            }
        }
        if (!$recentLine) continue;
        
        // Calcular los días restantes
        $hoy = new DateTime();
        $finContrato = clone $recentLine['dateEnd'];
        $finContrato->setTime(0, 0);
        $hoy->setTime(0, 0);
        
        $diferencia = $hoy->diff($finContrato);
        $diasRestantes = $diferencia->days * ($diferencia->invert ? -1 : 1);
        
        // Filtrar: mostrar solo si el contrato está vencido (<=0) o vence mañana (==1)
        if (!($diasRestantes <= 0 || $diasRestantes == 1)) continue;
        
        // Definir el mensaje según los días restantes
        $mensaje = "";
        if ($diasRestantes == 1) {
            $mensaje = "Estimado arrendatario del local {$data['ref_customer']}, sus datos indican que su renta vence el día de mañana. Si ya ha pagado ignore este mensaje.";
        } elseif ($diasRestantes <= 0) {
            $diasVencidos = abs($diasRestantes);
            if ($diasVencidos == 0) {
                $mensaje = "Estimado arrendatario del local {$data['ref_customer']}, sus datos indican que su renta vence hoy. Por favor, pase a pagar. Si ya ha pagado ignore este mensaje. Gracias.";
            } else {
                $mensaje = "Estimado arrendatario del local {$data['ref_customer']}, sus datos indican que su renta está vencida desde hace $diasVencidos día(s). Por favor, pase a pagar. Si ya ha pagado ignore este mensaje. Gracias.";
            }
        }
        
        // Definir el estado y la clase para mostrarlo
        $estado = "";
        $estadoClass = "desconocido";
        if ($diasRestantes < 0) {
            $estado = "VENCIDO HACE " . abs($diasRestantes) . " DÍAS";
            $estadoClass = "no-pagado";
        } elseif ($diasRestantes == 0) {
            $estado = "VENCIDO HOY";
            $estadoClass = "pagar";
        } elseif ($diasRestantes == 1) {
            $estado = "VENCE MAÑANA";
            $estadoClass = "pagar";
        }
        
        echo "<div class='contract-card'>";
        echo "<h2><i class='fas fa-store'></i> {$data['ref_customer']}</h2>";
        echo "<p><i class='fas fa-hashtag'></i> Referencia: {$data['ref']}</p>";
        echo "<p><i class='fas fa-calendar-alt'></i> Inicio: " . $recentLine['dateStart']->format('d-m-Y') . "</p>";
        echo "<p><i class='fas fa-calendar-times'></i> Fin: " . $recentLine['dateEnd']->format('d-m-Y') . "</p>";
        echo "<p class='status $estadoClass'><i class='fas fa-info-circle'></i> $estado</p>";
        
        // Obtener el número de teléfono y limpiar el formato
        $telefono = $data["array_options"]["options_numero_de_telefono_"] ?? '';
        // Eliminar espacios, paréntesis, guiones y el signo +
        $telefonoLimpio = preg_replace('/[\s\(\)\-\+]/', '', $telefono);
        
        // Botón para abrir chat directo de WhatsApp
        if (!empty($telefonoLimpio)) {
            echo "<div style='margin-top: 10px;'>";
            echo "<a href='https://wa.me/$telefonoLimpio?text=" . urlencode($mensaje) . "' target='_blank' class='btn-whatsapp'>";
            echo "<i class='fab fa-whatsapp'></i> Chat Directo</a>";
            echo "</div>";
        }
        
        echo "</div>";
    }
} else {
    echo "<div class='info-msg'>No se encontraron contratos con servicios vencidos.</div>";
}

echo '</div>';

echo '</div>';

// Al final del archivo, antes del cierre del body, añadir el script JavaScript
echo '<script>
document.getElementById("toggleWhatsappLinks").addEventListener("click", function() {
    var whatsappLinks = document.getElementById("whatsappLinks");
    if (whatsappLinks.style.display === "none") {
        whatsappLinks.style.display = "block";
        this.innerHTML = "<i class=\'fab fa-whatsapp\'></i> Ocultar Enlaces Directos";
    } else {
        whatsappLinks.style.display = "none";
        this.innerHTML = "<i class=\'fab fa-whatsapp\'></i> Ver Enlaces Directos";
    }
});
</script>';

echo '</div></body></html>';
?>
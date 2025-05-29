<?php

use function PHPSTORM_META\map;

date_default_timezone_set('America/Mexico_City');

// Función para enviar mensajes de WhatsApp
function enviarWhatsApp($token, $to, $body) {
    $params = array(
        'token' => $token,
        'to'    => $to,
        'body'  => $body
    );

    $ultramsgCurl = curl_init();
    curl_setopt_array($ultramsgCurl, array(
        CURLOPT_URL => "https://api.ultramsg.com/instance112284/messages/chat",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded"
        ),
    ));
    
    $response = curl_exec($ultramsgCurl);
    $err = curl_error($ultramsgCurl);
    curl_close($ultramsgCurl);
    
    return [$err, $response];
}

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

// Ajuste para mostrar el mensaje de éxito o error dentro de la página principal sin mover el contenido
$sendResult = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['telefono']) && isset($_POST['mensaje'])) {
    $telefono = trim($_POST['telefono']);
    $mensaje  = trim($_POST['mensaje']);

    if (!empty($telefono) && !empty($mensaje)) {
        $params = array(
            'token' => 'kx9mtxhgwmoycxbm',
            'to'    => $telefono,
            'body'  => $mensaje
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ultramsg.com/instance112284/messages/chat",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $sendResult = "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> Error al enviar: $err</div>";
        } else {
            $sendResult = "<div class='success-msg'><i class='fas fa-check-circle'></i> Mensaje enviado correctamente a $telefono!</div>";
        }
    } else {
        $sendResult = "<div class='error-msg'><i class='fas fa-phone-slash'></i> Por favor selecciona un arrendatario y escribe un mensaje.</div>";
    }
}

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Contratos Irregulares</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <form method="post">
            <input type="hidden" name="send_all_irregulares" value="1">
            <button type="submit">Enviar Mensaje a Todos</button>
        </form>
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
            $mensaje = "Estimado cliente del {$data['ref_customer']}, le recordamos que su renta vence el día de mañana. Si ya ha pagado ignore este mensje";
        } elseif ($diasRestantes <= 0) {
            $mensaje = "Estimado cliente del {$data['ref_customer']}, le recordamos que su renta está vencida. Por favor, pase a pagar. Si ya ha pagado ignore este mensaje. Gracias.";
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
        
        // Formulario individual para enviar el mensaje (se enviará al presionar el botón)
        echo "<form method='post' style='margin-top: 10px;'>";
        echo "<input type='hidden' name='contract_line_id' value='{$recentLine['id']}'>";
        echo "<input type='hidden' name='telefono' value='" . ($data["array_options"]["options_numero_de_telefono_"] ?? '') . "'>";
        echo "<input type='hidden' name='mensaje' value='$mensaje'>";
        echo "<button type='submit'>Enviar Mensaje</button>";
        echo "</form>";
        
        echo "</div>";
    }
} else {
    echo "<div class='info-msg'>No se encontraron contratos con servicios vencidos.</div>";
}

echo '</div>';

if (!empty($sendResult)) {
    echo $sendResult;
}

echo '</div></body></html>';
?>
<?php
// Función para enviar mensajes de WhatsApp
function enviarWhatsApp($token, $to, $body) {
    $params = array(
        'token' => $token,
        'to' => $to,
        'body' => $body
    );

    $ultramsgCurl = curl_init();
    curl_setopt_array($ultramsgCurl, array(
        CURLOPT_URL => "https://api.ultramsg.com/instance106245/messages/chat",
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

// Archivo para almacenar las Líneas ID procesadas
$archivoLineas = 'lineas_procesadas.txt';
$lineasProcesadas = file_exists($archivoLineas) 
    ? file($archivoLineas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) 
    : [];

// Solicitud cURL para obtener todos los contratos
$curl = curl_init();
$httpheader = ['DOLAPIKEY: web123456789'];
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
$result = curl_exec($curl);

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perfil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <h2><i class=""></i>Plaza Shopping Center</h2>
    <ul>
        <li><a href="home.php"><i class="fas fa-home"></i> Inicio</a></li>
        <li><a href="cambiar.php"><i class="fas fa-key"></i> Cambiar Contraseña</a></li>
        <li><a href="cerrar_sesion.php"><i class="fas fa-unlock"></i> Cerrar Sesion</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <h1><i class="fas fa-chart-line"></i> Contratos Activos</h1>
    </div>
    <div class="contracts-container">';

if ($result === false) {
    echo "<div class='error'>Error en la solicitud cURL: " . curl_error($curl) . "</div>";
} else {
    $contracts = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        foreach ($contracts as $data) {
            if ($data["nbofservicesopened"] >= 1) {
                // Obtener la última línea
                $recentLine = null;
                foreach ($data["lines"] as $line) {
                    if ($recentLine === null || $line["date_start"] > $recentLine["date_start"]) {
                        $recentLine = $line;
                    }
                }
                
                if (!$recentLine) continue;
                
                // Procesar mensaje WhatsApp si es necesario
                $mensajeEnviado = false;
                $errorMensaje = '';
                if ($data["nbofservicesexpired"] == 1 && !in_array($recentLine["id"], $lineasProcesadas)) {
                    if (!empty($data["array_options"]["options_numero_de_telefono_"] ?? '')) {
                        list($err, $response) = enviarWhatsApp(
                            'ekcyr2opsuz1wo6z',
                            $data["array_options"]["options_numero_de_telefono_"],
                            "Estimado cliente del local " . $data["ref_customer"] . ", le recordamos que su renta está vencida. Por favor, pase a pagar. Gracias."
                        );
                        
                        if (!$err) {
                            file_put_contents($archivoLineas, $recentLine["id"] . PHP_EOL, FILE_APPEND);
                            $lineasProcesadas[] = $recentLine["id"];
                            $mensajeEnviado = true;
                        } else {
                            $errorMensaje = "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> Error al enviar: " . $err . "</div>";
                        }
                    } else {
                        $errorMensaje = "<div class='error-msg'><i class='fas fa-phone-slash'></i> Teléfono no registrado</div>";
                    }
                }
                
                // Mostrar tarjeta
                echo "<div class='contract-card'>";
                echo "<h2><i class='fas fa-store'></i> " . $data["ref_customer"] . "</h2>";
                echo "<p><i class='fas fa-hashtag'></i> Referencia: " . $data["ref"] . "</p>";
                echo "<p><i class='fas fa-calendar-alt'></i> Inicio: " . date("d-m-Y", $recentLine["date_start"]) . "</p>";
                echo "<p><i class='fas fa-calendar-times'></i> Fin: " . date("d-m-Y", $recentLine["date_end"]) . "</p>";
                
                // Determinar estado
                if ($data["nbofservicesexpired"] == 1) {
                    $estado = "PASE A PAGAR";
                    $estadoClass = "pagar";
                } else {
                    $status = $recentLine["statut"] ?? $recentLine["status"] ?? null;
                    if ($status == 4) {
                        $estado = "YA PAGÓ";
                        $estadoClass = "pagado";
                    } elseif ($status == 5) {
                        $estado = "NO HA PAGADO";
                        $estadoClass = "no-pagado";
                    } else {
                        $estado = "ESTADO DESCONOCIDO";
                        $estadoClass = "desconocido";
                    }
                }
                
                // Mostrar estado y notificaciones
                echo "<p class='status $estadoClass'><i class='fas fa-info-circle'></i> Estado: " . $estado . "</p>";
                if ($mensajeEnviado) {
                    echo "<div class='success-msg'><i class='fas fa-check-circle'></i> Mensaje enviado!</div>";
                }
                if (!empty($errorMensaje)) {
                    echo $errorMensaje;
                }
                if (in_array($recentLine["id"], $lineasProcesadas)) {
                    echo "<div class='info-msg'><i class='fas fa-info-circle'></i> Mensaje ya enviado</div>";
                }
                
                echo "</div>"; // Cierre de la card
            }
        }
    } else {
        echo "<div class='error'>Error al decodificar la respuesta JSON</div>";
    }
}

echo '</div> <!-- Cierre del contenedor de cards -->
</div> <!-- Cierre del main-content -->
</body>
</html>';

curl_close($curl);
?>
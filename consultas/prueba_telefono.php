<?php
date_default_timezone_set('America/Mexico_City');

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

// Reiniciar archivo diariamente a las 10:00 AM
$archivoLineas = 'lineas_procesadas.txt';
$horaReinicio = '10:00';

// Verificar si necesitamos reiniciar el archivo
if (file_exists($archivoLineas)) {
    $ultimaModificacion = filemtime($archivoLineas);
    $hoy10AM = strtotime(date('Y-m-d') . ' ' . $horaReinicio);
    
    if (time() >= $hoy10AM && $ultimaModificacion < $hoy10AM) {
        file_put_contents($archivoLineas, '');
    }
}

// Solicitud cURL para obtener UN SOLO CONTRATO
$curl = curl_init();
$httpheader = ['DOLAPIKEY: web123456789'];
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts/117";
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
        <li><a href="cerrar_sesion.php"><i class="fas fa-unlock"></i> Cerrar Sesión</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1><i class="fas fa-file-contract"></i> Detalle del Contrato</h1>
    </div>
    <div class="contracts-container">';

if ($result === false) {
    echo "<div class='error'>Error en la solicitud: " . curl_error($curl) . "</div>";
} else {
    $data = json_decode($result, true);
    
    if (json_last_error() === JSON_ERROR_NONE && isset($data["nbofservicesopened"])) {
        if ($data["nbofservicesopened"] >= 1) {
            // Obtener última línea
            $recentLine = null;
            foreach ($data["lines"] as $line) {
                if (!$recentLine || $line["date_start"] > $recentLine["date_start"]) {
                    $recentLine = $line;
                }
            }

            if ($recentLine) {
                $lineaId = $recentLine["id"];
                $fechaFin = new DateTime(date('Y-m-d', $recentLine["date_end"]));
                $hoy = new DateTime();
                $diferencia = $hoy->diff($fechaFin);
                $diasRestantes = $diferencia->days * ($diferencia->invert ? -1 : 1);
                
                $mensajeEnviado = false;
                $errorMensaje = '';
                $lineasProcesadas = file_exists($archivoLineas) 
                    ? file($archivoLineas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) 
                    : [];

                // Determinar tipo de mensaje
                $tipoMensaje = '';
                $mensaje = '';
                
                if ($diasRestantes == 1) {
                    $tipoMensaje = 'recordatorio';
                    $mensaje = "Estimado cliente del local " . $data["ref_customer"] . ", le recordamos que su renta vence el día de mañana.";
                } elseif ($diasRestantes == 0) {
                    $tipoMensaje = 'vencido';
                    $mensaje = "Estimado cliente del local " . $data["ref_customer"] . ", le recordamos que su renta está vencida. Por favor, pase a pagar. Gracias.";
                }

                if (!empty($tipoMensaje) && !empty($mensaje)) {
                    $claveRegistro = $tipoMensaje . '_' . $lineaId;
                    
                    if (!in_array($claveRegistro, $lineasProcesadas)) {
                        if (!empty($data["array_options"]["options_numero_de_telefono_"] ?? '')) {
                            list($err, $response) = enviarWhatsApp(
                                'ekcyr2opsuz1wo6z',
                                $data["array_options"]["options_numero_de_telefono_"],
                                $mensaje
                            );
                            
                            if (!$err) {
                                file_put_contents($archivoLineas, $claveRegistro . PHP_EOL, FILE_APPEND);
                                $mensajeEnviado = true;
                            } else {
                                $errorMensaje = "<div class='error-msg'>Error al enviar: $err</div>";
                            }
                        } else {
                            $errorMensaje = "<div class='error-msg'>Teléfono no registrado</div>";
                        }
                    }
                }

                // Mostrar tarjeta
                echo "<div class='contract-card'>";
                echo "<h2><i class='fas fa-store'></i> " . $data["ref_customer"] . "</h2>";
                echo "<p><i class='fas fa-hashtag'></i> Referencia: " . $data["ref"] . "</p>";
                echo "<p><i class='fas fa-calendar-alt'></i> Inicio: " . date("d-m-Y", $recentLine["date_start"]) . "</p>";
                echo "<p><i class='fas fa-calendar-times'></i> Fin: " . date("d-m-Y", $recentLine["date_end"]) . "</p>";
                
                // Mostrar estado
                $estadoClass = '';
                if ($diasRestantes > 1) {
                    $estado = "VIGENTE ($diasRestantes días restantes)";
                    $estadoClass = "vigente";
                } elseif ($diasRestantes == 1) {
                    $estado = "VENCE MAÑANA";
                    $estadoClass = "por-vencer";
                } elseif ($diasRestantes == 0) {
                    $estado = "VENCIDO HOY";
                    $estadoClass = "vencido";
                } else {
                    $estado = "VENCIDO HACE " . abs($diasRestantes) . " DÍAS";
                    $estadoClass = "vencido";
                }
                
                echo "<p class='status $estadoClass'><i class='fas fa-info-circle'></i> $estado</p>";
                
                // Mostrar notificaciones
                if ($mensajeEnviado) {
                    echo "<div class='success-msg'><i class='fas fa-check'></i> Mensaje enviado</div>";
                }
                if (!empty($errorMensaje)) {
                    echo $errorMensaje;
                }
                
                echo "</div>"; // Cierre de la card
            }
        }
    } else {
        echo "<div class='error'>Error en los datos del contrato</div>";
    }
}

echo '</div></div></body></html>';
curl_close($curl);
?>
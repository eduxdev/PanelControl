<?php
date_default_timezone_set('America/Mexico_City');


$archivoLineas = 'lineas_procesadas.txt';
$lineasProcesadas = file_exists($archivoLineas) 
    ? file($archivoLineas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) 
    : [];


function enviarWhatsApp($token, $to, $body) {
    $params = array(
        'token' => $token,
        'to'    => $to,
        'body'  => $body
    );

    $ultramsgCurl = curl_init();
    curl_setopt_array($ultramsgCurl, array(
        CURLOPT_URL            => "https://api.ultramsg.com/instance112284/messages/chat",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => http_build_query($params),
        CURLOPT_HTTPHEADER     => array(
            "content-type: application/x-www-form-urlencoded"
        ),
    ));

    $response = curl_exec($ultramsgCurl);
    $err      = curl_error($ultramsgCurl);
    curl_close($ultramsgCurl);

    return [$err, $response];
}

// Obtener todos los contratos
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts?limit=0",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['DOLAPIKEY: web123456789']
]);

$result = curl_exec($curl);
if ($result === false) {
    $apiError = curl_error($curl);
}
curl_close($curl);

echo '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Envío Masivo de Mensajes</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
      .contract-card {
          background: white;
          padding: 20px;
          margin: 10px;
          border-radius: 10px;
          box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }
      .success-msg { color: #388E3C; margin-top: 5px; }
      .error-msg   { color: #D32F2F; margin-top: 5px; }
      .info-msg    { color: #1976D2; margin-top: 5px; }
  </style>
</head>
<body>
<div class="sidebar">
  <h2>Plaza Shopping Center</h2>
  <ul>
      <li><a href="home.php"><i class="fas fa-home"></i> Inicio</a></li>
      <li><a href="cambiar.php"><i class="fas fa-key"></i> Cambiar Contraseña</a></li>
      <li><a href="cerrar_sesion.php"><i class="fas fa-unlock"></i> Cerrar Sesión</a></li>
  </ul>
</div>

<div class="main-content">
  <div class="header">
      <h1><i class="fas fa-bell"></i> Envío de Mensajes</h1>
  </div>
  <div class="contracts-container">';

if (isset($apiError)) {
    echo "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> Error al obtener datos: $apiError</div>";
} else {
    $contracts = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> Error al decodificar JSON</div>";
    } else {
        foreach ($contracts as $data) {
            // Procesar solo los contratos con nbofservicesopened >= 1
            if ($data["nbofservicesopened"] < 1) {
                continue;
            }
            
            // Verificar si ya se envió el mensaje para este contrato
            if (in_array($data['id'], $lineasProcesadas)) {
                $mensajeInfo = "<div class='info-msg'><i class='fas fa-info-circle'></i> Mensaje ya enviado previamente.</div>";
            } else {
                // Nuevo mensaje a enviar
                $mensaje = "Buenos días. Para actualizar nuestros registros, por favor envíanos tu número de teléfono, si ya cuentas con contrato de luz y el número de tu local. Agradecemos mucho tu apoyo.";

                $telefono = $data["array_options"]["options_numero_de_telefono_"] ?? '';
                $mensajeEnviado = false;
                $errorMensaje   = '';

                if (!empty($telefono)) {
                    list($err, $response) = enviarWhatsApp('kx9mtxhgwmoycxbm', $telefono, $mensaje);
                    if (!$err) {
                        $mensajeEnviado = true;
                        // Guardar el ID del contrato en el archivo para evitar reenvío
                        file_put_contents($archivoLineas, $data['id'] . PHP_EOL, FILE_APPEND);
                    } else {
                        $errorMensaje = "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> Error al enviar: $err</div>";
                    }
                } else {
                    $errorMensaje = "<div class='error-msg'><i class='fas fa-phone-slash'></i> Teléfono no registrado</div>";
                }
            }

            // Mostrar tarjeta del contrato
            echo "<div class='contract-card'>";
            echo "<h2><i class='fas fa-store'></i> {$data['ref_customer']}</h2>";
            echo "<p><i class='fas fa-hashtag'></i> Referencia: {$data['ref']}</p>";

            if (isset($mensajeInfo)) {
                echo $mensajeInfo;
                unset($mensajeInfo);
            } else {
                if ($mensajeEnviado) {
                    echo "<div class='success-msg'><i class='fas fa-check-circle'></i> Mensaje enviado!</div>";
                }
                if (!empty($errorMensaje)) {
                    echo $errorMensaje;
                }
            }
            echo "</div>";
        }
    }
}

echo '</div></div></body></html>';
?>

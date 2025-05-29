<?php
// Función para enviar mensajes de WhatsApp usando UltraMsg
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

// Leer líneas ya procesadas
$lineasProcesadas = [];
if (file_exists($archivoLineas)) {
    $lineasProcesadas = file($archivoLineas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

// Solicitud cURL para obtener el contrato 117
$curl = curl_init();
$httpheader = ['DOLAPIKEY: web123456789'];
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts/117";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
$result = curl_exec($curl);

if ($result === false) {
    echo "Error en la solicitud cURL: " . curl_error($curl);
} else {
    $data = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if ($data["nbofservicesopened"] >= 1) {
            echo "LOCAL CON EL NÚMERO: " . $data["ref_customer"] . "<br>";
            echo "Referencia: " . $data["ref"] . "<br>";
            
            // Obtener la ÚLTIMA Línea ID del contrato (la más reciente)
            $recentLine = null;
            foreach ($data["lines"] as $line) {
                if ($recentLine === null || $line["date_start"] > $recentLine["date_start"]) {
                    $recentLine = $line;
                }
            }
            
            if ($recentLine) {
                $lineaId = $recentLine["id"]; // ID de la última línea
                echo "<h3>Línea ID: " . $lineaId . "</h3>";
                echo "Fecha de inicio: " . date("Y-m-d", $recentLine["date_start"]) . "<br>";
                echo "Fecha de fin: " . date("Y-m-d", $recentLine["date_end"]) . "<br>";
                
                // Determinar el estado basado en la fecha de fin
                $fechaFin = new DateTime(date('Y-m-d', $recentLine["date_end"]));
                $hoy = new DateTime();
                $diferencia = $hoy->diff($fechaFin);
                $diasRestantes = $diferencia->days * ($diferencia->invert ? -1 : 1);
                
                if ($diasRestantes == 1) {
                    $estado = "VENCE MAÑANA";
                    $mensaje = "Estimado cliente del local " . $data["ref_customer"] . ", le recordamos que su renta vence el día de mañana.";
                } elseif ($diasRestantes == 0) {
                    $estado = "VENCIDO HOY";
                    $mensaje = "Estimado cliente del local " . $data["ref_customer"] . ", le recordamos que su renta está vencida. Por favor, pase a pagar. Gracias.";
                } elseif ($diasRestantes < 0) {
                    $estado = "VENCIDO HACE " . abs($diasRestantes) . " DÍAS";
                    $mensaje = "Estimado cliente del local " . $data["ref_customer"] . ", le recordamos que su renta está vencida. Por favor, pase a pagar. Gracias.";
                } else {
                    $estado = "VIGENTE ($diasRestantes días restantes)";
                }
                
                echo "Estado: " . $estado . "<br>";
                
                // Enviar mensaje si corresponde
                if (isset($mensaje) && !in_array($lineaId, $lineasProcesadas)) {
                    if (!empty($data["array_options"]["options_numero_de_telefono_"] ?? '')) {
                        list($err, $response) = enviarWhatsApp(
                            'ekcyr2opsuz1wo6z',
                            $data["array_options"]["options_numero_de_telefono_"],
                            $mensaje
                        );
                        
                        if (!$err) {
                            echo "Mensaje enviado correctamente.<br>";
                            // Registrar la Línea ID en el archivo
                            file_put_contents($archivoLineas, $lineaId . PHP_EOL, FILE_APPEND);
                            $lineasProcesadas[] = $lineaId; // Actualizar array en memoria
                        } else {
                            echo "Error al enviar el mensaje: " . $err . "<br>";
                        }
                    } else {
                        echo "Error: Número de teléfono no definido.<br>";
                    }
                } elseif (isset($mensaje)) {
                    echo "Este mensaje ya fue enviado (Línea ID $lineaId).<br>";
                }
            }
        }
    } else {
        echo "Error al decodificar la respuesta JSON.";
    }
}

curl_close($curl);
?>
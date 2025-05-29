<?php
date_default_timezone_set('America/Mexico_City');

$sendResult = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);

    if (!empty($mensaje)) {
        // Obtener todos los contratos desde la API
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts?limit=0",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['DOLAPIKEY: web123456789']
        ]);
        $result = curl_exec($curl);
        curl_close($curl);

        $contracts = json_decode($result, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $sentCount = 0;
            $errorCount = 0;

            foreach ($contracts as $data) {
                if ($data["nbofservicesopened"] < 1) continue;

                $telefono = $data["array_options"]["options_numero_de_telefono_"] ?? '';
                if (!empty($telefono)) {
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
                        $errorCount++;
                    } else {
                        $sentCount++;
                    }
                }
            }

            $sendResult = "<div class='success-msg'><i class='fas fa-check-circle'></i> Mensaje enviado a $sentCount arrendatarios.</div>";
            if ($errorCount > 0) {
                $sendResult .= "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> Ocurrieron errores al enviar a $errorCount arrendatarios.</div>";
            }
        } else {
            $sendResult = "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> Error al decodificar los datos de contratos.</div>";
        }
    } else {
        $sendResult = "<div class='error-msg'><i class='fas fa-phone-slash'></i> Por favor ingrese un mensaje.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Mensajes a Todos - Plaza Shopping Center</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        /* Se omite la definición del sidebar ya que éste se incluye desde sidebar.php */
        .main-content {
            
            padding: 20px;
        }
        .header h1 { margin-top: 0; }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
        }
        /* From Uiverse.io by adamgiebl */ 
        button {
                font-size: 14px;
                letter-spacing: 2px;
                text-transform: uppercase;
                display: inline-block;
                text-align: center;
                font-weight: bold;
                padding: 0.7em 2em;
                border: 3px solid rgb(108, 77, 129);
                border-radius: 18.2px;
                position: relative;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.1);
                color:rgb(140, 0, 255);
                text-decoration: none;
                transition: 0.3s ease all;
                z-index: 1;
                }

                button:before {
                border-radius: 18.2px;
                transition: 0.5s all ease;
                position: absolute;
                top: 0;
                left: 50%;
                right: 50%;
                bottom: 0;
                opacity: 0;
                content: "";
                background-color:rgb(79, 63, 90);
                z-index: -1;
                }

                button:hover, button:focus {
                color: white;
                }

                button:hover:before, button:focus:before {
                transition: 0.5s all ease;
                left: 0;
                right: 0;
                opacity: 1;
                }

                button:active {
                transform: scale(0.9);
                }

        button:hover { background: #155a9c; }
        .success-msg { color: #388E3C; margin-top: 10px; }
        .error-msg { color: #D32F2F; margin-top: 10px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-envelope"></i> Enviar Mensajes a Todos</h1>
        </div>
        <?php
            if (!empty($sendResult)) {
                echo $sendResult;
            }
        ?>
        <div class="form-container">
            <form action="comunicados.php" method="post">
                <label for="mensaje"><strong>Escribe el mensaje para enviar a todos los arrendatarios:</strong></label>
                <textarea name="mensaje" id="mensaje" placeholder="Escribe aquí tu mensaje..."></textarea>
                <button type="submit">Enviar Mensaje</button>
            </form>
        </div>
    </div>
</body>
</html>
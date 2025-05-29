<?php
$curl = curl_init();
$httpheader = ['DOLAPIKEY: web123456789'];
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts/1";
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
            
            $recentLine = null;
            foreach ($data["lines"] as $line) {
                if ($recentLine === null || $line["date_start"] > $recentLine["date_start"]) {
                    $recentLine = $line;    
                }
            }
            
            if ($recentLine) {
                echo "<h3>Línea ID: " . $recentLine["id"] . "</h3>";
                echo "Fecha de inicio: " . date("Y-m-d", $recentLine["date_start"]) . "<br>";
                echo "Fecha de fin: " . date("Y-m-d", $recentLine["date_end"]) . "<br>";
                
                if ($data["nbofservicesexpired"] == 1) {
                    $estado = "PASE A PAGAR";
                } elseif ($recentLine["statut"] == 4 || $recentLine["status"] == 4) {
                    $estado = "YA PAGÓ";
                } elseif ($recentLine["statut"] == 5 || $recentLine["status"] == 5) {
                    $estado = "NO HA PAGADO";
                } else {
                    $estado = "ESTADO DESCONOCIDO";
                }
                
                echo "Estado: " . $estado . "<br>";
            }

            // Mostrar el contenido de "linkedObjectsIds"
            if (isset($data["linkedObjectsIds"])) {
                echo "<h3>Objetos vinculados (linkedObjectsIds):</h3>";
                echo "<pre>";
                print_r($data["linkedObjectsIds"]);
                echo "</pre>";

                // Si deseas procesar los objetos vinculados
                foreach ($data["linkedObjectsIds"] as $objectType => $objectIds) {
                    echo "<h4>Tipo de objeto: " . $objectType . "</h4>";
                    echo "IDs: " . implode(", ", $objectIds) . "<br>";
                }
            } else {
                echo "No se encontraron objetos vinculados (linkedObjectsIds).<br>";
            }
        }
    } else {
        echo "Error al decodificar la respuesta JSON.";
    }
}

curl_close($curl);
?>
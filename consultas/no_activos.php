<?php
$curl = curl_init();
$httpheader = ['DOLAPIKEY: web123456789'];
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
$result = curl_exec($curl);

if ($result === false) {
    echo "Error en la solicitud cURL: " . curl_error($curl);
} else {
    $contracts = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        foreach ($contracts as $data) {
            if ($data["nbofservicesopened"] == 0) {
                echo "<hr>";
                echo "LOCAL CON EL NÚMERO: " . $data["ref_customer"] . "<br>";
                echo "Estado: LOCAL DISPONIBLE<br>";
            }
        }
    } else {
        echo "Error al decodificar la respuesta JSON.";
    }
}

curl_close($curl);
?>

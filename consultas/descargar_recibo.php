<?php
$curl = curl_init();
$httpheader = ['DOLAPIKEY: web123456789'];
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/invoices/1";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
$result = curl_exec($curl);

if ($result === false) {
    echo "Error en la solicitud cURL: " . curl_error($curl);
} else {
    $invoices = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        foreach ($invoices as $data) {
            echo "<hr>";
            echo "PRODUCTO REFERENCIA: " . $data["product_ref"] . "<br>";
            
            if (!empty($data["date"])) {
                echo "Fecha de pago: " . date("Y-m-d", $data["date"]). "<br>";
            }
            
            if (!empty($data["online_payment_url"])) {
                echo '<a href="' . $data["online_payment_url"] . '" target="_blank">Descargar Recibo</a><br>';
            } else {
                echo "No hay recibo disponible.<br>";
            }
        }
    } else {
        echo "Error al decodificar la respuesta JSON.";
    }
}

curl_close($curl);
?>
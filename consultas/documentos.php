<?php
// URL para descargar el documento PDF de la factura
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/documents/download?modulepart=facture&original_file=IN2502-0381";

// Iniciar cURL para realizar la solicitud de descarga
$curl = curl_init($url);

// Configurar cURL para obtener el archivo
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

// Realizar la solicitud y obtener el contenido del archivo PDF
$response = curl_exec($curl);

// Verificar si hay errores en la solicitud
if (curl_errno($curl)) {
    echo "Error en la solicitud: " . curl_error($curl);
    curl_close($curl);
    exit;
}

// Verificar si la respuesta es un archivo PDF
$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
curl_close($curl);

// Asegurarse de que la respuesta es un archivo PDF
if (strpos($contentType, 'pdf') !== false) {
    // Definir el nombre del archivo para guardar
    $filename = "IN2502-0381.pdf";

    // Establecer las cabeceras para la descarga del archivo
    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=" . $filename);
    header("Content-Length: " . strlen($response));

    // Enviar el contenido del archivo PDF al navegador
    echo $response;
} else {
    echo "No se pudo obtener el archivo PDF o no es un PDF válido.";
}
?>
    
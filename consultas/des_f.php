

<?php
$curl = curl_init();
$httpheader = ['DOLAPIKEY: web123456789'];
$url = "https://erp.plazashoppingcenter.store/htdocs/api/index.php/invoices/1";


curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);

$result = curl_exec($curl);
curl_close($curl);
//print_r($result);
$data = json_decode($result, true);
print_r($data)

?>
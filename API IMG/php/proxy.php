<?php
if (!isset($_GET['query']) || empty($_GET['query'])) {
    http_response_code(400);
    echo json_encode(["error" => "No se proporcionó un término de búsqueda."]);
    exit;
}


$query = urlencode($_GET['query']);
$url = "https://world.openfoodfacts.org/cgi/search.pl?search_terms={$query}&json=1&sort_by=popularity&action=process&fields=product_name,brands,countries,image_url,code,product_quantity&page_size=125";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(["error" => "Error al obtener datos de la API. Código HTTP: $httpCode"]);
    curl_close($ch);
    exit;
}

curl_close($ch);
header('Content-Type: application/json');
echo $response;
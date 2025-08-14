<?php
// lacak_paket.php
header('Content-Type: application/json');

// Validasi input
if (!isset($_GET['kurir']) || !isset($_GET['resi'])) {
    echo json_encode(['status' => 400, 'message' => 'Parameter kurir dan resi dibutuhkan.']);
    exit();
}

$courier = $_GET['kurir'];
$awb = $_GET['resi'];
$apiKey = '36843c54dcc7d76da4cf46fdf7193c93fd19a5c0f4cbb1ee054042e02710010d'; // Ganti dengan API Key Anda

// URL Endpoint Binderbyte
$url = "https://api.binderbyte.com/v1/track?api_key={$apiKey}&courier={$courier}&awb={$awb}";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(['status' => 500, 'message' => 'Error cURL: ' . $err]);
} else {
    // Langsung teruskan response dari Binderbyte ke frontend
    echo $response;
}
?>
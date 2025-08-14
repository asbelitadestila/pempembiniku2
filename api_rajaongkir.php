<?php
// api_rajaongkir.php

header('Content-Type: application/json');

// Rahasiakan API Key Anda
// $apiKey = 'PHsqgmBg2c34bd2cf137e7f40uzdrVFQ'; #punya asbel
$apiKey ='e85bd58f9f3be9e66e62c2c8973e6fcc'; #punya gw


// Asal Pengiriman (atur ID kota asal toko Anda)
// Anda bisa mencari ID kota di dokumentasi RajaOngkir
$originCityId = '775'; // Contoh: ID untuk Kota Depok

$action = $_GET['action'] ?? '';

function callRajaOngkir($url, $apiKey, $method = 'GET', $data = []) {
    $curl = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            "key: " . $apiKey
        ],
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        $options[CURLOPT_HTTPHEADER][] = "content-type: application/x-www-form-urlencoded";
    }

    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return json_encode(['error' => "cURL Error #:" . $err]);
    } else {
        return $response;
    }
}

switch ($action) {
    case 'get_provinsi':
        $url = "https://rajaongkir.komerce.id/api/v1/destination/province";
        echo callRajaOngkir($url, $apiKey);
        break;

    case 'get_kota':
        $provinceId = $_GET['province_id'] ?? '';
        if ($provinceId) {
            $url = "https://rajaongkir.komerce.id/api/v1/destination/city/" . $provinceId;
            echo callRajaOngkir($url, $apiKey);
        } else {
            echo json_encode(['error' => 'Province ID is required']);
        }
        break;
    
     case 'get_kecamatan':
        $cityId = $_GET['city_id'] ?? '';
        if ($cityId) {
            $url = "https://rajaongkir.komerce.id/api/v1/destination/district/" . $cityId;
            echo callRajaOngkir($url, $apiKey);
        } else {
            echo json_encode(['error' => 'Province ID is required']);
        }
        break;

    case 'cek_biaya':
        $destinationId = $_POST['destination_id'] ?? '';
        $courier = $_POST['courier'] ?? '';
        // Untuk tipe starter, berat minimal adalah 1000 gram (1kg)
        $weight = 1000; 

        if ($destinationId && $courier) {
            $url = "https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost";
            $data = [
                'origin' => $originCityId,
                'destination' => $destinationId,
                'weight' => $weight,
                'courier' => $courier
            ];
            echo callRajaOngkir($url, $apiKey, 'POST', $data);
        } else {
            echo json_encode(['error' => 'Destination and courier are required']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
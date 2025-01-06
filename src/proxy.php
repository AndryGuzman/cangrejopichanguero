<?php
if (isset($_GET['ruc'])) {
    $ruc = $_GET['ruc'];
    $url = "https://dniruc.apisperu.com/api/v1/ruc/$ruc?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImFuZHJ5Y2FzaWVsZ3JAZ21haWwuY29tIn0.P-0dhbLBJOkVAXqBRLE60j4Nq6qmnNwFDy7ClNio-8A";


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(["error" => "Error al conectar con la API: $error"]);
    } else {
        header('Content-Type: application/json');
        echo $response;
    }
} else {
    echo json_encode(["error" => "RUC no proporcionado"]);
}

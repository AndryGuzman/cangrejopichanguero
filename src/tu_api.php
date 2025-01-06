<?php
// tu_api.php
function obtenerDatosPorDni($dni) {
    $token = 'apis-token-10991.GVT9dx9z8fHX6mk6aLB28EJJ0HOOjRk7';
    $url = "https://api.apis.net.pe/v2/reniec/dni?numero=$dni";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        "Authorization: Bearer $token"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $data = json_decode($response, true);
        return [
            'nombres' => $data['nombres'] ?? '',
            'apellidos' => ($data['apellidoPaterno'] ?? '') . ' ' . ($data['apellidoMaterno'] ?? '')
        ];
    } elseif ($httpCode == 404) {
        return ['error' => 'DNI no encontrado.'];
    } elseif ($httpCode == 422) {
        return ['error' => 'El DNI no cumple las reglas de validación.'];
    } else {
        return ['error' => 'Error al consultar la API.'];
    }
}

// Ejemplo de uso
if (isset($_GET['dni'])) {
    $dni = $_GET['dni'];
    $result = obtenerDatosPorDni($dni);
    echo json_encode($result);
}
?>

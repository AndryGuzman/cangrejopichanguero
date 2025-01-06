<?php
include "../conexion.php"; // Incluye la conexión a la base de datos

$criterio = $_GET['criterio'] ?? ''; // Validar si se pasa el criterio
$query = $_GET['query'] ?? '';       // Validar el término de búsqueda

if (empty($query)) {
    echo "<p class='text-danger'>Debe ingresar un valor para la búsqueda.</p>";
    exit;
}

$output = ""; // Inicializamos la variable de salida

if ($criterio === "dni") {
    // Búsqueda por DNI en la base de datos
    $stmt = $conexion->prepare("SELECT id, documento, nombres, apellidos FROM cliente WHERE documento = ?");
    $stmt->bind_param("s", $query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cliente encontrado
        $output = "<ul class='list-group'>";
        while ($row = $result->fetch_assoc()) {
            $output .= "<li class='list-group-item d-flex justify-content-between align-items-center'>
                            <span>" . htmlspecialchars($row['documento']) . " - " . htmlspecialchars($row['nombres']) . " " . htmlspecialchars($row['apellidos']) . "</span>
                            <button class='btn btn-primary btn-sm' onclick='seleccionarCliente(" . $row['id'] . ", \"" . addslashes(htmlspecialchars($row['nombres'])) . "\", \"" . addslashes(htmlspecialchars($row['apellidos'])) . "\")'>Seleccionar</button>
                        </li>";
        }
        $output .= "</ul>";
    } else {
        // Consultar API de RENIEC si no se encuentra en la base de datos
        $url = "https://api.apis.net.pe/v2/reniec/dni?numero=$query";
        $token = "apis-token-10991.GVT9dx9z8fHX6mk6aLB28EJJ0HOOjRk7";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));
        $response = curl_exec($ch);

        if ($response === false) {
            $output = "<p class='text-danger'>Error al consultar el API de RENIEC: " . curl_error($ch) . "</p>";
        } else {
            curl_close($ch);
            $data = json_decode($response, true);

            if (isset($data['nombres'])) {
                // Datos encontrados en RENIEC
                $output = "<div class='alert alert-info'>
                            Cliente encontrado en RENIEC: " . htmlspecialchars($data['nombres']) . " " . htmlspecialchars($data['apellidoPaterno']) . " " . htmlspecialchars($data['apellidoMaterno']) . "
                            <form action='registrar_cliente.php' method='POST' id='registroClienteForm'>
                                <input type='hidden' name='documento' value='" . htmlspecialchars($query) . "'>
                                <input type='hidden' name='nombres' value='" . htmlspecialchars($data['nombres']) . "'>
                                <input type='hidden' name='apellidos' value='" . htmlspecialchars($data['apellidoPaterno']) . " " . htmlspecialchars($data['apellidoMaterno']) . "'>
                                
                                <div class='form-group'>
                                    <label for='sexo'>Sexo:</label>
                                    <select class='form-control' id='sexo' name='sexo'>
                                        <option value='M'>Masculino</option>
                                        <option value='F'>Femenino</option>
                                    </select>
                                </div>

                                <div class='form-group'>
                                    <label for='direccion'>Dirección:</label>
                                    <input type='text' class='form-control' id='direccion' name='direccion'>
                                </div>

                                <div class='form-group'>
                                    <label for='celular'>Celular:</label>
                                    <input type='text' class='form-control' id='celular' name='celular'>
                                </div>

                                <div class='form-group'>
                                    <label for='fecha_nacimiento'>Fecha de Nacimiento:</label>
                                    <input type='date' class='form-control' id='fecha_nacimiento' name='fecha_nacimiento'>
                                </div>
                                 
                                <button type='submit' class='btn btn-primary'>Registrar Cliente</button>
                            </form>
                        </div>";
            } else {
                // API no devuelve datos válidos
                $output = "<p class='text-danger'>No se encontró información para el DNI proporcionado en RENIEC.</p>";
            }
        }
    }
} else if ($criterio === "nombres") {
    // Búsqueda por nombres
    $stmt = $conexion->prepare("SELECT id, documento, nombres, apellidos FROM cliente WHERE nombres LIKE ?");
    $searchQuery = "%" . $query . "%";
    $stmt->bind_param("s", $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    $output = "<ul class='list-group'>";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $output .= "<li class='list-group-item d-flex justify-content-between align-items-center'>
                            <span>" . htmlspecialchars($row['documento']) . " - " . htmlspecialchars($row['nombres']) . " " . htmlspecialchars($row['apellidos']) . "</span>
                            <button class='btn btn-primary btn-sm' onclick='seleccionarCliente(" . $row['id'] . ", \"" . addslashes(htmlspecialchars($row['nombres'])) . "\", \"" . addslashes(htmlspecialchars($row['apellidos'])) . "\")'>Seleccionar</button>
                        </li>";
        }
        $output .= "</ul>";
    } else {
        $output .= "<p class='text-danger'>No se encontraron resultados.</p>";
    }
}

echo $output; // Devolver el resultado
?>

<?php
include "../conexion.php"; // Incluye la conexión a la base de datos

// Verifica si se recibieron los datos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibe los datos del formulario
    $documento = $_POST['documento'] ?? null;
    $nombres = $_POST['nombres'] ?? null;
    $apellidos = $_POST['apellidos'] ?? null;
    $sexo = $_POST['sexo'] ?? null;
    $direccion = $_POST['direccion'] ?? null;
    $celular = $_POST['celular'] ?? null;
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;

    // Validación básica de los campos obligatorios
    if (empty($documento) || empty($nombres) || empty($apellidos)) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'buscar_cliente.php') . '?error=CamposObligatorios');
        exit;
    }

    try {
        // Prepara la consulta SQL
        $stmt = $conexion->prepare("
            INSERT INTO cliente (documento, nombres, apellidos, sexo, direccion, celular, fecha_nacimiento) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssss", $documento, $nombres, $apellidos, $sexo, $direccion, $celular, $fecha_nacimiento);

        // Ejecuta la consulta
        if ($stmt->execute()) {
            // Obtiene el ID del cliente recién agregado
            $clienteId = $stmt->insert_id;

            // Captura la URL previa
            $urlAnterior = $_SERVER['HTTP_REFERER'] ?? 'buscar_cliente.php';

            // Agrega el ID del cliente a la URL como parámetro
            $urlRedireccion = $urlAnterior . (strpos($urlAnterior, '?') === false ? '?' : '&') . "id_cliente=$clienteId";

            // Redirige silenciosamente
            header("Location: $urlRedireccion");
            exit;
        } else {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'buscar_cliente.php') . '?error=ErrorRegistro');
            exit;
        }

        // Cierra la conexión
        $stmt->close();
    } catch (Exception $e) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'buscar_cliente.php') . '?error=Exception');
        exit;
    }
} else {
    header('Location: buscar_cliente.php?error=AccesoNoPermitido');
    exit;
}
?>

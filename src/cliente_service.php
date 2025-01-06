<?php
session_start();
if (empty($_SESSION['active'])) {
    header('Location: ../');
    exit;
}

include '../conexion.php';

// Inicializar variables
$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : '';
unset($_SESSION['mensaje']); // Limpiar el mensaje después de usarlo
$clientes = [];

// Obtener clientes
function obtenerClientes($conexion, $buscar = null) {
    $sql = "SELECT * FROM cliente WHERE estado = 1";
    if ($buscar) {
        $buscar = mysqli_real_escape_string($conexion, $buscar);
        $sql = "SELECT * FROM cliente WHERE (documento LIKE '%$buscar%' OR nombres LIKE '%$buscar%' OR apellidos LIKE '%$buscar%') AND estado = 1";
    }
    $result = mysqli_query($conexion, $sql);
    $clientes = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $clientes[] = $row;
        }
    }
    return $clientes;
}

// Guardar cliente
function guardarCliente($conexion, $data) {
    extract($data);
    $documento = mysqli_real_escape_string($conexion, $documento);
    $nombres = mysqli_real_escape_string($conexion, $nombres);
    $apellidos = mysqli_real_escape_string($conexion, $apellidos);
    $sexo = mysqli_real_escape_string($conexion, $sexo);

    if ($sexo !== 'M' && $sexo !== 'F') {
        return "Por favor, selecciona un valor válido para el sexo (M o F).";
    }

    $verificar_dni = mysqli_query($conexion, "SELECT * FROM cliente WHERE documento = '$documento'");
    if (mysqli_num_rows($verificar_dni) > 0) {
        return "El cliente con DNI $documento ya está registrado.";
    }

    $sql = "INSERT INTO cliente (documento, nombres, apellidos, sexo";
    $values = "VALUES ('$documento', '$nombres', '$apellidos', '$sexo'";

    if (!empty($direccion)) {
        $direccion = mysqli_real_escape_string($conexion, $direccion);
        $sql .= ", direccion";
        $values .= ", '$direccion'";
    }
    if (!empty($celular)) {
        $celular = mysqli_real_escape_string($conexion, $celular);
        $sql .= ", celular";
        $values .= ", '$celular'";
    }
    if (!empty($fecha_nacimiento)) {
        $fecha_nacimiento = mysqli_real_escape_string($conexion, $fecha_nacimiento);
        $sql .= ", fecha_nacimiento";
        $values .= ", '$fecha_nacimiento'";
    }

    $sql .= ") $values)";
    return mysqli_query($conexion, $sql) ? "Cliente guardado con éxito." : "Error: " . mysqli_error($conexion);
}

// Actualizar cliente
function editarCliente($conexion, $data) {
    extract($data);
    $sql = "UPDATE cliente SET 
            documento='$documento', 
            nombres='$nombres', 
            apellidos='$apellidos', 
            sexo='$sexo', 
            direccion='$direccion', 
            celular='$celular', 
            fecha_nacimiento='$fecha_nacimiento' 
            WHERE id='$id'";
    return mysqli_query($conexion, $sql) ? "Cliente actualizado con éxito." : "Error: " . mysqli_error($conexion);
}

// Procesar solicitudes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['guardar_cliente'])) {
        $_SESSION['mensaje'] = guardarCliente($conexion, $_POST);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['editar_cliente'])) {
        $_SESSION['mensaje'] = editarCliente($conexion, $_POST);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['buscar'])) {
        $clientes = obtenerClientes($conexion, $_POST['buscar']);
    }
} else {
    $clientes = obtenerClientes($conexion);
}

?>

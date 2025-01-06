<?php
session_start();
if (empty($_SESSION['active'])) {
    header('Location: ../');
}

include '../conexion.php';

$mensaje = '';
$cliente = null;

// Verificar si se recibió un ID
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    $sql = "SELECT * FROM cliente WHERE id = '$id'";
    $result = mysqli_query($conexion, $sql);
    $cliente = mysqli_fetch_assoc($result);
}

// Actualizar cliente en la base de datos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_cliente'])) {
    // Recoger los datos del formulario y validar
    // Similar a cómo se hace en tu código para guardar el cliente
    // Agregar lógica de actualización aquí (ej. UPDATE cliente SET ... WHERE id = '$id')
}

mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Include your header and styles -->
</head>
<body>
    <h3>Editar Cliente</h3>
    <form method="POST" action="">
        <!-- Aquí irán los campos del cliente, pre-llenados con los datos del $cliente -->
        <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
        <!-- Resto de los campos: DNI, Nombres, Apellidos, etc. -->
        <button type="submit" name="actualizar_cliente">Actualizar</button>
    </form>
</body>
</html>

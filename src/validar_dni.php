<?php
session_start();
include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['documento'])) {
    $dni = mysqli_real_escape_string($conexion, $_POST['documento']);
    $verificar_dni = mysqli_query($conexion, "SELECT * FROM cliente WHERE documento = '$dni'");
    
    if (mysqli_num_rows($verificar_dni) > 0) {
        echo 'existe'; // El DNI ya está registrado
    } else {
        echo 'disponible'; // El DNI está disponible
    }
}

mysqli_close($conexion);
?>

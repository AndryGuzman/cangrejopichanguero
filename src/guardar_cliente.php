<?php
include "../conexion.php";

$dni = mysqli_real_escape_string($conexion, $_POST['dni']);
$nombres = mysqli_real_escape_string($conexion, $_POST['nombres']);
$apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos']);
$direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
$celular = mysqli_real_escape_string($conexion, $_POST['celular']);
$sexo = mysqli_real_escape_string($conexion, $_POST['sexo']);
$fecha_nacimiento = mysqli_real_escape_string($conexion, $_POST['fecha_nacimiento']);

$query = "INSERT INTO cliente (dni, nombres, apellidos, direccion, celular, sexo, fecha_nacimiento)
          VALUES ('$dni', '$nombres', '$apellidos', '$direccion', '$celular', '$sexo', '$fecha_nacimiento')";

if (mysqli_query($conexion, $query)) {
    echo "success";
} else {
    echo "error";
}
?>

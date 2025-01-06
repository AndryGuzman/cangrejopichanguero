<?php
include "../conexion.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = mysqli_query($conexion, "SELECT * FROM productos WHERE id = $id");
    $result = mysqli_fetch_assoc($query);

    if ($result) {
        echo json_encode($result);  // Devuelve los datos del plato en formato JSON
    } else {
        echo json_encode(['error' => 'Plato no encontrado']);
    }
}
?>

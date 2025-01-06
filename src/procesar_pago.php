<?php
session_start();
if (empty($_SESSION['active'])) {
    header('Location: ../');
    exit;
}
include('../conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanear entradas
    $id_pedido = mysqli_real_escape_string($conexion, $_POST['id_pedido']);
    $medio_pago = mysqli_real_escape_string($conexion, $_POST['medio_pago']);
    $tipo_comprobante = mysqli_real_escape_string($conexion, $_POST['tipo_comprobante']); // Nuevo campo
    $numero_operacion = isset($_POST['numero_operacion']) ? mysqli_real_escape_string($conexion, $_POST['numero_operacion']) : null;
    $id_sesion = mysqli_real_escape_string($conexion, $_POST['id_sesion']); 

    // Validar el ID del pedido y su estado
    $query = "SELECT id, estado FROM pedidos WHERE id = '$id_pedido' AND estado = 'FINALIZADO'";
    $result = mysqli_query($conexion, $query);

    if (mysqli_num_rows($result) > 0) {
        // Insertar el pago en la tabla pagos, incluyendo id_caja
        $insertQuery = "INSERT INTO pago (id_pedido, medio_pago, num_operacion, comprobante, id_sesion,fecha) 
                        VALUES ('$id_pedido', '$medio_pago', ";

        // Verificar si se debe insertar número de operación
        if ($medio_pago === 'plin' || $medio_pago === 'yape' || $medio_pago === 'tarjeta') {
            $insertQuery .= "'$numero_operacion', ";
        } else {
            $insertQuery .= "NULL, ";
        }

        $insertQuery .= "'$tipo_comprobante', '$id_sesion', NOW())"; // Añadir id_caja

        // Ejecutar la inserción
        if (mysqli_query($conexion, $insertQuery)) {
            // Actualizar el estado del pedido a 'PAGADO'
            $updateQuery = "UPDATE pedidos SET estado = 'PAGADO' WHERE id = '$id_pedido'";

            if (mysqli_query($conexion, $updateQuery)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al actualizar el estado del pedido.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al registrar el pago.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Pedido no encontrado o ya pagado.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método de solicitud inválido.']);
}
?>

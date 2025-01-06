<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    require_once "../conexion.php";
    include_once "includes/header.php"; // Encabezado para estilos y scripts generales

    if (isset($_GET['id'])) {
        $pedidoId = intval($_GET['id']); // Sanitizar el parámetro

        // Usar consultas preparadas para evitar inyección SQL
        $stmtGeneral = $conexion->prepare("
            SELECT p.fecha, p.total, u.nombre AS vendedor, 
                   CONCAT(c.nombres, ' ', c.apellidos) AS cliente, 
                   pa.medio_pago
            FROM pedidos p
            INNER JOIN usuarios u ON u.id = p.id_usuario
            INNER JOIN cliente c ON c.id = p.id_cliente
            INNER JOIN pago pa ON pa.id_pedido = p.id
            WHERE p.id = ?
        ");
        $stmtGeneral->bind_param("i", $pedidoId);
        $stmtGeneral->execute();
        $resultGeneral = $stmtGeneral->get_result();

        if ($resultGeneral->num_rows > 0) {
            $generalData = $resultGeneral->fetch_assoc();

            // Segunda consulta: detalles del pedido
            $stmtDetalle = $conexion->prepare("
                SELECT p.nombre, dp.precio, dp.cantidad
                FROM detalle_pedidos dp
                INNER JOIN productos p ON p.id = dp.id_producto
                WHERE dp.id_pedido = ?
            ");
            $stmtDetalle->bind_param("i", $pedidoId);
            $stmtDetalle->execute();
            $resultDetalle = $stmtDetalle->get_result();
?>
            <div class="container mt-5">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Detalles del Pedido #<?php echo $pedidoId; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Fecha:</strong> <?php echo $generalData['fecha']; ?></p>
                                <p><strong>Cliente:</strong> <?php echo $generalData['cliente']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Vendedor:</strong> <?php echo $generalData['vendedor']; ?></p>
                                <p><strong>Medio de Pago:</strong> <?php echo $generalData['medio_pago']; ?></p>
                                <p><strong>Total:</strong> S/ <?php echo number_format($generalData['total'], 2); ?></p>
                            </div>
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Plato</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($detalle = $resultDetalle->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo $detalle['nombre']; ?></td>
                                            <td>S/ <?php echo number_format($detalle['precio'], 2); ?></td>
                                            <td><?php echo $detalle['cantidad']; ?></td>
                                            <td>S/ <?php echo number_format($detalle['precio'] * $detalle['cantidad'], 2); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="lista_ventas.php" class="btn btn-secondary">Volver al Historial</a>
                </div>
            </div>
<?php
        } else {
            echo "<div class='alert alert-danger text-center'>No se encontró información para el pedido.</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>ID de pedido no especificado.</div>";
    }
} else {
    header('Location: permisos.php');
    exit;
}
include_once "includes/footer.php"; // Pie de página para scripts adicionales
?>

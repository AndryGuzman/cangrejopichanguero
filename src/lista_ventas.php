<?php
session_start();

// Validación de sesión y roles permitidos
function verificarRol($rolesPermitidos) {
    return isset($_SESSION['rol']) && in_array($_SESSION['rol'], $rolesPermitidos);
}

if (verificarRol([1, 2, 3])) {
    require_once "../conexion.php";
    $id_user = $_SESSION['idUser'];

    // Consulta optimizada
    $query = mysqli_query($conexion, "SELECT p.*, s.nombre AS sala, s.mesas AS num_mesa, u.nombre 
                                      FROM pedidos p
                                      INNER JOIN salas s ON p.id_sala = s.id
                                      INNER JOIN usuarios u ON p.id_usuario = u.id");

    include_once "includes/header.php";
?>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>Historial de Pedidos</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tbl">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Sala</th>
                                <th>Mesa</th>
                                <th>Fecha</th>
                                <th>Total (S/.)</th>
                                <th>Usuario</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($query)) {
                                // Determinar el estado y el badge
                                switch ($row['estado']) {
                                    case 'PENDIENTE':
                                        $estadoBadge = '<span class="badge badge-danger">Pendiente</span>';
                                        $link = "mesas.php?id_sala=" . $row['id_sala'] . "&mesas=" . $row['num_mesa'] . "&id_TipoPedido=" . $row['id_tipoPedido'];
                                        break;
                                    case 'FINALIZADO':
                                        $estadoBadge = '<span class="badge badge-success">Completado</span>';
                                        $link = "detalle_pedido.php?id=" . $row['id'];
                                        break;
                                    case 'PAGADO':
                                        $estadoBadge = '<span class="badge badge-info">Pagado</span>';
                                        $link = "detalle_pedido.php?id=" . $row['id'];
                                        break;
                                    default:
                                        $estadoBadge = '<span class="badge badge-secondary">Sin estado</span>';
                                        $link = "#";
                                }
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['sala']); ?></td>
                                    <td><?php echo htmlspecialchars($row['num_mesa']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['total'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($link); ?>" class="btn btn-sm btn-primary">
                                            <?php echo $estadoBadge; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php
    include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
    exit;
}
?>
<?php
session_start();

// Verificar permisos
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    require_once "../conexion.php";

    // Procesar ajustes de caja
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajustar_caja'])) {
        $id_sesion = $_POST['id_sesion'];
        $tipo_ajuste = $_POST['tipo_ajuste'];
        $monto_ajuste = $_POST['monto_ajuste'];
        $descripcion_ajuste = $_POST['descripcion_ajuste'];

        // Determinar cómo ajustar el monto en caja
        if ($tipo_ajuste === 'SOBRANTE') {
            // Aumentar el monto en caja si es sobrante
            $monto_cierre = "monto_cierre + $monto_ajuste";
        } else {
            // Restar el monto en caja para otros tipos de ajuste
            $monto_cierre = "monto_cierre - $monto_ajuste";
        }

        // Registrar el ajuste en la tabla ajustes_caja
        $stmt = $conexion->prepare("
            INSERT INTO ajustes_caja (id_sesion, tipo, monto, descripcion)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isds", $id_sesion, $tipo_ajuste, $monto_ajuste, $descripcion_ajuste);
        $stmt->execute();
        $stmt->close();

        // Actualizar el monto de la caja en la sesión correspondiente
        $stmt_update = $conexion->prepare("
            UPDATE caja_sesion
            SET monto_cierre = $monto_cierre
            WHERE id = ?
        ");
        $stmt_update->bind_param("i", $id_sesion);
        $stmt_update->execute();
        $stmt_update->close();

        // Redireccionar para evitar reenvío de formulario
        header("Location: caja.php");
        exit();
    }

    // Consulta para las sesiones abiertas
    $query_abiertas = "
        SELECT cs.id, c.nombre AS caja, cs.turno, cs.fecha_apertura, cs.monto_apertura, 
               COALESCE(SUM(cs.monto_apertura + p.total), cs.monto_apertura) AS saldo_actual
        FROM caja_sesion cs
        JOIN caja c ON cs.id_caja = c.id
        LEFT JOIN pago pa ON pa.id_caja = c.id
        LEFT JOIN pedidos p ON pa.id_pedido = p.id
        WHERE cs.estado = 'ABIERTO'
        GROUP BY cs.id
        ORDER BY cs.fecha_apertura ASC";
    $result_abiertas = $conexion->query($query_abiertas);
?>

<!-- Incluir Header -->
<?php include 'includes/header.php'; ?>

<div class="container mt-5">

    <h3 class="text-center">Administración de Caja</h3>

    <!-- Sesiones Abiertas -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5>Cajas Abiertas</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Caja</th>
                        <th>Turno</th>
                        <th>Fecha Apertura</th>
                        <th>Monto Apertura</th>
                        <th>Monto en Caja</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_abiertas->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['caja']; ?></td>
                            <td><?php echo $row['turno']; ?></td>
                            <td><?php echo $row['fecha_apertura']; ?></td>
                            <td>S/ <?php echo number_format($row['monto_apertura'], 2); ?></td>
                            <td>S/ <?php echo number_format($row['saldo_actual'], 2); ?></td>
                            <td>
                                <!-- Botón para abrir el modal de ajuste -->
                                <button class="btn btn-warning" data-toggle="modal" data-target="#ajusteModal<?php echo $row['id']; ?>">Ajustar Caja</button>
                                
                                <!-- Modal de Ajuste de Caja -->
                                <div class="modal fade" id="ajusteModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="ajusteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="ajusteModalLabel">Ajuste de Caja</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="ajustar_caja.php">
                                                    <input type="hidden" name="id_sesion" value="<?php echo $row['id']; ?>">
                                                    
                                                    <div class="form-group">
                                                        <label for="tipo_ajuste">Tipo de Ajuste</label>
                                                        <select name="tipo_ajuste" id="tipo_ajuste" class="form-control" required>
                                                            <option value="GASTO">Gasto</option>
                                                            <option value="ERROR">Error</option>
                                                            <option value="SOBRANTE">Sobranete</option>
                                                            <option value="FALTANTE">Faltante</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="monto_ajuste">Monto del Ajuste (S/)</label>
                                                        <input type="number" name="monto_ajuste" id="monto_ajuste" class="form-control" required step="0.01">
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="descripcion_ajuste">Descripción</label>
                                                        <textarea name="descripcion_ajuste" id="descripcion_ajuste" class="form-control" required></textarea>
                                                    </div>

                                                    <button type="submit" name="ajustar_caja" class="btn btn-primary">Registrar Ajuste</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Botón de Cierre de Caja -->
                                <form method="POST" action="caja.php" class="mt-3">
                                    <input type="hidden" name="id_sesion" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="cerrar_caja" class="btn btn-danger">Cerrar Caja</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
} else {
    echo "No tienes permisos suficientes para acceder a esta página.";
}
?>

<!-- Scripts de Bootstrap -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</body>
</html>

<?php
session_start();

// Verificar permisos
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    require_once "../conexion.php";
    $id_usuario = $_SESSION['idUser'];

    // Abrir una nueva sesión de caja
    if (isset($_POST['abrir_caja'])) {
        $id_caja = $_POST['id_caja'];
        $turno = $_POST['turno'];
        $monto_apertura = $_POST['monto_apertura'];
    
        // Verificar si ya hay una sesión abierta para la misma caja y turno
        $stmt_verificar = $conexion->prepare("
            SELECT COUNT(*) AS total 
            FROM caja_sesion 
            WHERE id_caja = ? AND turno = ? AND estado = 'ABIERTO'
        ");
        $stmt_verificar->bind_param("is", $id_caja, $turno);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        $row_verificar = $result_verificar->fetch_assoc();
    
        if ($row_verificar['total'] > 0) {
            echo "<script>alert('La caja ya está abierta para este turno.'); window.location.href='caja.php';</script>";
        } else {
            // Insertar nueva sesión de caja
            $stmt = $conexion->prepare("
                INSERT INTO caja_sesion (id_caja, turno, fecha_apertura, estado, monto_apertura, id_usuario)
                VALUES (?, ?, NOW(), 'ABIERTO', ?, ?)
            ");
            $stmt->bind_param("isdi", $id_caja, $turno, $monto_apertura, $id_usuario);
            if ($stmt->execute()) {
                echo "<script>alert('Caja abierta correctamente.'); window.location.href='caja.php';</script>";
            } else {
                echo "<script>alert('Error al abrir la caja.'); window.location.href='caja.php';</script>";
            }
            $stmt->close();
        }
    
        $stmt_verificar->close();
    }
    

    // Consulta para sesiones abiertas
    $query_abiertas = "
              SELECT 
        cs.id, 
        c.nombre AS caja, 
        cs.turno, 
        cs.fecha_apertura, 
        cs.monto_apertura, 
        cs.monto_apertura + COALESCE(SUM(p.total), 0) AS saldo_actual
    FROM caja_sesion cs
    JOIN caja c ON cs.id_caja = c.id
    LEFT JOIN pago pa ON pa.id_sesion = cs.id
    LEFT JOIN pedidos p ON pa.id_pedido = p.id
    WHERE cs.estado = 'ABIERTO'
    GROUP BY cs.id, c.nombre, cs.turno, cs.fecha_apertura, cs.monto_apertura
    ORDER BY cs.fecha_apertura ASC";
    $result_abiertas = $conexion->query($query_abiertas);


    if (isset($_GET['cerrar'])) {
        $id_sesion = intval($_GET['cerrar']); // Convertir el ID de sesión a entero
    
        // Calcular monto_actual
        $sql_calculo = "
    SELECT 
        cs.id, 
        c.id AS 'caja', 
        (cs.monto_apertura + 
         COALESCE(SUM(p.total), 0) - 
         (SELECT COALESCE(SUM(aj.monto), 0) 
          FROM ajustes_caja aj 
          WHERE aj.id_sesion = cs.id)) AS monto_actual
    FROM caja_sesion cs
    INNER JOIN caja c ON c.id = cs.id_caja
    LEFT JOIN pago pa ON pa.id_sesion = cs.id
    LEFT JOIN pedidos p ON pa.id_pedido = p.id
    WHERE cs.id = ? AND cs.estado = 'ABIERTO'
    GROUP BY cs.id, c.id, cs.monto_apertura;
";
        $stmt_calculo = $conexion->prepare($sql_calculo);
        $stmt_calculo->bind_param("i", $id_sesion);
        $stmt_calculo->execute();
        $result_calculo = $stmt_calculo->get_result();
    
        if ($row_calculo = $result_calculo->fetch_assoc()) {
            $monto_actual = $row_calculo['monto_actual'];
    
            // Actualizar la sesión con el monto calculado
            $stmt_cerrar = $conexion->prepare("
                UPDATE caja_sesion 
                SET fecha_cierre = NOW(), monto_cierre = ?, estado = 'CERRADO'
                WHERE id = ? AND estado = 'ABIERTO'
            ");
            $stmt_cerrar->bind_param("di", $monto_actual, $id_sesion);
            if ($stmt_cerrar->execute()) {
                echo "<script>alert('Caja cerrada correctamente.'); window.location.href='caja.php';</script>";
            } else {
                echo "<script>alert('Error al cerrar la caja.'); window.location.href='caja.php';</script>";
            }
            $stmt_cerrar->close();
        } else {
            echo "<script>alert('No se pudo calcular el monto actual.'); window.location.href='caja.php';</script>";
        }
    
        $stmt_calculo->close();
    }
    
    // Consulta para las últimas 3 sesiones cerradas
    $query_cerradas = "
        SELECT cs.id, c.nombre AS caja, cs.turno, cs.fecha_apertura, cs.fecha_cierre, cs.monto_apertura, 
               cs.monto_cierre, cs.saldo_final
        FROM caja_sesion cs
        JOIN caja c ON cs.id_caja = c.id
        WHERE cs.estado = 'CERRADO'
        ORDER BY cs.fecha_cierre DESC
        LIMIT 3";
    $result_cerradas = $conexion->query($query_cerradas);

    // Filtros: Aplicar búsqueda en sesiones cerradas
    $filtros_aplicados = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtrar_cerradas'])) {
        $desde = $_POST['desde'] ?? null;
        $hasta = $_POST['hasta'] ?? null;
        $turno = $_POST['turno'] ?? 'TODOS';

        $where_clauses = ["cs.estado = 'CERRADO'"];
        if (!empty($desde) && !empty($hasta)) {
            $where_clauses[] = "cs.fecha_cierre BETWEEN '$desde 00:00:00' AND '$hasta 23:59:59'";
        }
        if ($turno !== 'TODOS') {
            $where_clauses[] = "cs.turno = '$turno'";
        }

        $query_cerradas = "
            SELECT cs.id, c.nombre AS caja, cs.turno, cs.fecha_apertura, cs.fecha_cierre, cs.monto_apertura, 
                   cs.monto_cierre
            FROM caja_sesion cs
            JOIN caja c ON cs.id_caja = c.id
            WHERE " . implode(" AND ", $where_clauses) . "
            ORDER BY cs.fecha_cierre DESC";
        $result_cerradas = $conexion->query($query_cerradas);
        $filtros_aplicados = true;
    }
?>

<!-- Incluir Header -->
<?php include 'includes/header.php'; ?>

<div class="container mt-5">

    <h3 class="text-center">Administración de Caja</h3>

    <!-- Apertura de Caja -->
    <ul class="nav nav-tabs" id="cajaTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="apertura-tab" data-toggle="tab" href="#apertura" role="tab">Apertura de Caja</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="cerradas-tab" data-toggle="tab" href="#cerradas" role="tab">Sesiones Cerradas</a>
        </li>
    </ul>

    <!-- Contenido de Pestañas -->
    <div class="tab-content" id="cajaTabsContent">
        <!-- Apertura de Caja -->
        <div class="tab-pane fade show active" id="apertura" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header bg-primary text-white">Apertura de Caja</div>
                <div class="card-body">
                    <form method="POST" action="caja.php">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="id_caja">Seleccionar Caja</label>
                                <select name="id_caja" id="id_caja" class="form-control" required>
                                    <option value="">Seleccione una caja</option>
                                    <?php
                                    $cajas_result = $conexion->query("SELECT * FROM caja");
                                    while ($caja = $cajas_result->fetch_assoc()) {
                                        echo "<option value='{$caja['id']}'>{$caja['nombre']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="turno">Turno</label>
                                <select name="turno" id="turno" class="form-control" required>
                                    <option value="MAÑANA">Mañana</option>
                                    <option value="TARDE">Tarde</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="monto_apertura">Monto de Apertura (S/)</label>
                                <input type="number" name="monto_apertura" id="monto_apertura" class="form-control" required step="0.01">
                            </div>
                        </div>
                        <button type="submit" name="abrir_caja" class="btn btn-success mt-3">Abrir Caja</button>
                    </form>
                </div>
            </div>
        

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
                    <th>Total esperado</th>
                    <th>Monto actual</th> <!-- Agregado para mostrar el monto cierre -->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while ($row = $result_abiertas->fetch_assoc()) {
                    // Obtener los ajustes de caja para la sesión
                    $sql_ajustes = "SELECT SUM(monto) AS total_ajustes 
                                    FROM ajustes_caja 
                                    WHERE id_sesion = ?";
                    $stmt_ajustes = $conexion->prepare($sql_ajustes);
                    $stmt_ajustes->bind_param("i", $row['id']);
                    $stmt_ajustes->execute();
                    $result_ajustes = $stmt_ajustes->get_result();
                    $row_ajustes = $result_ajustes->fetch_assoc();
                    $total_ajustes = $row_ajustes['total_ajustes'] ?? 0;

                    // Calcular el monto de cierre
                    $monto_actual = $row['saldo_actual'] - $total_ajustes;
                ?>
                    <tr>
                        <td><?php echo $row['caja']; ?></td>
                        <td><?php echo $row['turno']; ?></td>
                        <td><?php echo $row['fecha_apertura']; ?></td>
                        <td>S/ <?php echo number_format($row['monto_apertura'], 2); ?></td>
                        <td>S/ <?php echo number_format($row['saldo_actual'], 2); ?></td>
                        <td>S/ <?php echo number_format($monto_actual, 2); ?></td> <!-- Mostrar monto cierre -->

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
                                                        <option value="SOBRANTE">Sobrante</option>
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
                            
                           <!-- Botón para cerrar caja -->
        <a href="?cerrar=<?php echo $row['id']; ?>" class="btn btn-danger" > Cerrar caja</a>
        <a href="reporte_sesion.php?id_sesion=<?php echo $row['id']; ?>" class="btn btn-info" target="_blank">Reporte</a>

                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</div>
 <!-- Filtros para Cajas Cerradas -->
 <div class="tab-pane fade" id="cerradas" role="tabpanel">
 <div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5>Filtrar Cajas Cerradas</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="caja.php">
            <div class="d-flex justify-content-between align-items-end">
                <div class="form-group col-md-3 me-2">
                    <label for="desde" class="form-label">Desde</label>
                    <input type="date" name="desde" id="desde" class="form-control">
                </div>
                <div class="form-group col-md-3 me-2">
                    <label for="hasta" class="form-label">Hasta</label>
                    <input type="date" name="hasta" id="hasta" class="form-control">
                </div>
                <div class="form-group col-md-3 me-2">
                    <label for="turno" class="form-label">Turno</label>
                    <select name="turno" id="turno" class="form-select">
                        <option value="TODOS">Todos</option>
                        <option value="MAÑANA">Mañana</option>
                        <option value="TARDE">Tarde</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <button type="submit" name="filtrar_cerradas" class="btn btn-primary w-100">Aplicar Filtros</button>
                </div>
            </div>
        </form>
    </div>
</div>


    <!-- Sesiones Cerradas -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5>Cajas Cerradas</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Caja</th>
                        <th>Turno</th>
                        <th>Fecha Apertura</th>
                        <th>Fecha Cierre</th>
                        <th>Monto Apertura</th>
                        <th>Monto Cierre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_cerradas->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['caja']; ?></td>
                            <td><?php echo $row['turno']; ?></td>
                            <td><?php echo $row['fecha_apertura']; ?></td>
                            <td><?php echo $row['fecha_cierre']; ?></td>
                            <td>S/ <?php echo number_format($row['monto_apertura'], 2); ?></td>
                            <td>S/ <?php echo number_format($row['monto_cierre'], 2); ?></td>
                            <td>
                                <a href="reporte_sesion.php?id_sesion=<?php echo $row['id']; ?>" class="btn btn-info" target="_blank">Reporte</a>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if ($result_cerradas->num_rows == 0 && $filtros_aplicados) { ?>
                        <tr>
                            <td colspan="7" class="text-center">No se encontraron resultados con los filtros aplicados.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<?php } ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
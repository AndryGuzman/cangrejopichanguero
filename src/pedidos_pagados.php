<?php
session_start();
if (empty($_SESSION['active'])) {
    header('Location: ../');
    exit;
}
include('../conexion.php');
// Si se ha enviado un filtro por fecha, usamos esa fecha
$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d'); // Default es la fecha actual

// Consulta para obtener todos los pedidos pagados, ordenados por fecha (más reciente primero)
$query = "SELECT p.id AS id_pedido, p.total, pa.medio_pago, pa.num_operacion, c.nombres, c.apellidos, p.fecha,pa.comprobante
          FROM pedidos p
          INNER JOIN cliente c ON p.id_cliente = c.id
          INNER JOIN Pago pa on pa.id_pedido = p.id
          WHERE p.estado = 'PAGADO' AND p.id_cliente IS NOT NULL AND DATE(p.fecha) = '$fecha_filtro'
          ORDER BY p.fecha DESC"; // Ordenar por fecha de más reciente a más antiguo
$result = mysqli_query($conexion, $query);

if (!$result) {
    die('Error en la consulta: ' . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Pagados</title>
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            text-decoration: none;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .date-picker {
            padding: 5px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>

    <div class="container">
        <h1>Pedidos Pagados</h1>

        <!-- Filtro de fecha -->
        <form method="GET" action="">
            <label for="fecha">Filtrar por Fecha:</label>
            <input type="date" id="fecha" name="fecha" class="date-picker" value="<?php echo $fecha_filtro; ?>">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Medio de Pago</th>
                        <th>Número de Operación</th>
                        <th>Comprobante</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pedido = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $pedido['id_pedido']; ?></td>
                            <td><?php echo $pedido['nombres'] . ' ' . $pedido['apellidos']; ?></td>
                            <td>S/. <?php echo number_format($pedido['total'], 2); ?></td>
                            <td><?php echo ucfirst($pedido['medio_pago']); ?></td>
                            <td><?php echo $pedido['num_operacion']; ?></td>
                            <td><?php echo $pedido['comprobante']; ?></td>
                            <td>
                                <a href="generar_boleta.php?id_pedido=<?php echo $pedido['id_pedido']; ?>" class="btn btn-primary">
                                    <i class="fas fa-file-invoice"></i> Generar Boleta
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <br>
        <a href="cobros.php" class="btn btn-primary mb-3"><i class="fas fa-arrow-left"></i> Regresar</a>
    </div>
<?php include('includes/footer.php'); ?>
</body>
</html>

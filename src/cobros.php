<?php
session_start();
if (empty($_SESSION['active'])) {
    header('Location: ../');
    exit;
}
include('../conexion.php');
// Verificar si hay una sesión activa de caja
$query_sesion_activa = "SELECT id FROM caja_sesion WHERE estado = 'ABIERTO' LIMIT 1";
$result_sesion_activa = mysqli_query($conexion, $query_sesion_activa);

// Consulta para obtener todos los pedidos finalizados con id_cliente
$query = "SELECT p.id AS id_pedido, p.total, c.nombres, c.apellidos
          FROM pedidos p
          INNER JOIN cliente c ON p.id_cliente = c.id
          WHERE p.estado = 'FINALIZADO' AND p.id_cliente IS NOT NULL";
$result = mysqli_query($conexion, $query);

if (!$result) {
    die('Error en la consulta: ' . mysqli_error($conexion));
}

$sesion_activa = mysqli_num_rows($result_sesion_activa) > 0; // true si hay sesión activa, false si no
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cobros Pendientes</title>
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            margin-top: 0px;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        .btn-primary, .btn-success {
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        td.center {
            text-align: center;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .alert {
            padding: 15px;
            margin-top: 20px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Header incluido desde el archivo header.php -->
    <?php include('includes/header.php'); ?>

    <div class="container">
        <div class="text-center"> 
            <h1 class="mb-3" style="color: #007bff;">Cobros Pendientes</h1>
        </div>
        <!-- Botones de navegación -->
        <a href="pedidos_pagados.php" class="btn btn-primary mb-3"><i class="fas fa-eye"></i> Ver Pedidos Pagados</a>

        <?php if (!$sesion_activa): ?>
            <div class="alert">
                <strong>¡Atención!</strong> Debe aperturar una sesión de caja antes de poder realizar pagos.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th class="center">ID Pedido</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th class="center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pedido = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="center"><?php echo $pedido['id_pedido']; ?></td>
                            <td><?php echo $pedido['nombres'] . ' ' . $pedido['apellidos']; ?></td>
                            <td>S/. <?php echo number_format($pedido['total'], 2); ?></td>
                            <td class="center">
                                <?php if ($sesion_activa): ?>
                                    <a href="pagos.php?id=<?php echo $pedido['id_pedido']; ?>" class="btn btn-success">
                                        <i class="fas fa-money-bill-wave"></i> Pagar
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-success" disabled>
                                        <i class="fas fa-money-bill-wave"></i> Pagar
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer incluido desde el archivo footer.php -->
    <?php include('includes/footer.php'); ?>
</body>
</html>

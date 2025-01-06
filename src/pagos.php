<?php
session_start();
if (empty($_SESSION['active'])) {
    header('Location: ../');
    exit;
}
include('../conexion.php');

if (isset($_GET['id'])) {
    $id_pedido = $_GET['id'];

    // Obtener los detalles del pedido
    $query = "SELECT p.id, p.total, c.nombres AS cliente_nombre
              FROM pedidos p
              INNER JOIN cliente c ON p.id_cliente = c.id
              WHERE p.id = '$id_pedido'";
    $result = mysqli_query($conexion, $query);
    $pedido = mysqli_fetch_assoc($result);
    if (!$pedido) {
        echo "Pedido no encontrado.";
        exit;
    }
} else {
    echo "No se ha especificado un pedido.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar Pedido</title>
    <!-- Agregar Bootstrap para un diseño más limpio -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9; /* Fondo gris claro */
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 40px;
        }
        .form-group label {
            font-weight: bold;
            font-size: 1rem;
        }
        .form-control {
            border-radius: 5px;
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ccc;
        }
        .form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 5px rgba(0, 86, 179, 0.5);
        }

        /* Resaltar los campos específicos */
        .titulo-pedido {
            font-size: 2.2rem;
            color: #333; /* Color oscuro para un look profesional */
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .cliente-info {
            font-size: 1.4rem;
            color: #555; /* Gris suave para el nombre del cliente */
            font-weight: normal;
            margin-bottom: 15px;
            text-align: center;
        }

        .total-pago {
            font-size: 2rem;
            color: #007bff; /* Azul para resaltar el total */
            font-weight: bold;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-group label#label_caja {
            color: #333;
        }
        .form-group label#label_comprobante {
            color: #333;
        }
        .form-group label#label_pago {
            color: #333;
        }
        .form-group label#label_efectivo {
            color: #333;
        }
        .form-group label#label_ruc {
            color: #333;
        }

        .btn-cancel, .btn-success {
            font-size: 1.1rem;
            padding: 12px 22px;
            border-radius: 6px;
            transition: background-color 0.3s;
        }
        .btn-cancel {
            background-color: #f44336;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #d32f2f;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #218838;
        }

        .card {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 30px;
            background-color: white;
            margin-top: 20px;
        }

        .text-center {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <div class="titulo-pedido">Pagar Pedido</div>
        <div class="card p-4 shadow-sm">
            <div class="cliente-info"><strong>Cliente:</strong> <?php echo $pedido['cliente_nombre']; ?></div>
            <div class="total-pago"><strong>Total:</strong> S/. <?php echo number_format($pedido['total'], 2); ?></div>

            <form id="ComprobanteForm">
                <div class="form-group">
                    <label for="id_sesion" id="label_caja">Selecciona la caja:</label>
                    <select id="id_sesion" name="id_sesion" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <?php
                        // Consulta para obtener las sesiones abiertas
                        $query = "SELECT cs.id AS id_sesion, c.nombre, cs.estado 
                                  FROM caja c
                                  INNER JOIN caja_sesion cs ON cs.id_caja = c.id
                                  WHERE cs.estado = 'ABIERTO'"; // Filtra sesiones con estado "ABIERTO"
                        $result = mysqli_query($conexion, $query);

                        // Verificar si hay sesiones abiertas
                        if (mysqli_num_rows($result) > 0) {
                            // Mostrar las sesiones en el selector
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . $row['id_sesion'] . "'>" . $row['nombre'] . "</option>";
                            }
                        } else {
                            // Si no hay sesiones abiertas
                            echo "<option value=''>No hay sesiones abiertas disponibles</option>";
                        }
                        ?>
                    </select>
                </div>

                <input type="hidden" id="id_pedido" value="<?php echo $id_pedido; ?>">

                <!-- Selección del tipo de comprobante -->
                <div class="form-group">
                    <label for="tipo_comprobante" id="label_comprobante">Selecciona el comprobante de pago:</label>
                    <select id="tipo_comprobante" name="tipo_comprobante" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="boleta">Boleta</option>
                        <option value="factura">Factura</option>
                    </select>
                </div>
            </form>

            <form id="pagoForm">
                <!-- Selección del medio de pago -->
                <div class="form-group">
                    <label for="medio_pago" id="label_pago">Selecciona el medio de pago:</label>
                    <select id="medio_pago" name="medio_pago" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="plin">PLIN</option>
                        <option value="yape">YAPE</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>

                <!-- Campo de efectivo -->
                <div id="efectivoFields" style="display: none;">
                    <div class="form-group">
                        <label for="monto_cliente" id="label_efectivo">Monto recibido:</label>
                        <input type="number" id="monto_cliente" name="monto_cliente" class="form-control" min="0" step="0.01">
                    </div>
                    <div id="vuelto" style="display: none;">
                        <label>Vuelto:</label>
                        <input type="text" id="monto_vuelto" name="monto_vuelto" class="form-control" readonly>
                    </div>
                </div>

                <!-- Campos para PLIN, YAPE, TARJETA -->
                <div id="operacionFields" style="display: none;">
                    <div class="form-group">
                        <label for="numero_operacion">Número de operación:</label>
                        <input type="text" id="numero_operacion" name="numero_operacion" class="form-control">
                    </div>
                </div>

                <!-- Campo para consultar RUC (solo cuando se selecciona Factura) -->
                <div id="rucFields" style="display: none;">
                    <div class="form-group">
                        <label for="ruc" id="label_ruc">Consultar RUC:</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="ruc" name="ruc" class="form-control" placeholder="Ingrese el RUC" maxlength="11">
                            <button type="button" id="btnBuscarRUC" class="btn btn-info">Buscar</button>
                        </div>
                    </div>

                    <!-- Resultados de la consulta del RUC -->
                    <div id="rucResult" style="display: none;">
                        <p><strong>Razón Social:</strong> <span id="razon_social"></span></p>
                        <p><strong>Dirección:</strong> <span id="direccion"></span></p>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <!-- Botón de pago -->
                    <button type="button" id="btnPagar" class="btn btn-success btn-lg">Confirmar Pago</button>
                    <!-- Botón cancelar -->
                    <a href="cobros.php" class="btn btn-danger btn-lg">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>

    <!-- Datos de pedido para pasar a JS -->
    <div id="datos-pedido" 
         data-total="<?php echo $pedido['total']; ?>" 
         data-id-pedido="<?php echo $id_pedido; ?>">
    </div>

    <script src="../assets/js/pagos.js"></script>
</body>
</html>

<?php
session_start();
if ($_SESSION['rol'] != 1) {
    header('Location: permisos.php');
    exit;
}

include "../conexion.php";

// Variables de filtros
$fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '2024-11-12';
$fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d');

// Consultas (same as before)
$query_clientes_compras = "
    SELECT c.nombres, SUM(p.total) AS total_comprado
    FROM pedidos p
    INNER JOIN cliente c ON c.id = p.id_cliente
    WHERE p.fecha BETWEEN '$fecha_inicio' AND NOW()
    GROUP BY c.id
    ORDER BY total_comprado DESC
    LIMIT 10
";
$clientes_compras = mysqli_query($conexion, $query_clientes_compras);

$query_usuarios_ventas = "
    SELECT u.nombre, SUM(p.total) AS total_vendido
    FROM pedidos p
    INNER JOIN usuarios u ON u.id = p.id_usuario
    WHERE p.fecha BETWEEN '$fecha_inicio' AND NOW()
    GROUP BY u.id
    ORDER BY total_vendido DESC
    LIMIT 10
";
$usuarios_ventas = mysqli_query($conexion, $query_usuarios_ventas);

$query_categoria_ventas = "
    SELECT c.nombre AS categoria, SUM(dp.precio * dp.cantidad) AS monto_ventas
    FROM detalle_pedidos dp
    INNER JOIN productos p ON p.id = dp.id_producto
    INNER JOIN categoria c ON c.id = p.id_categoria
    INNER JOIN pedidos ped ON ped.id = dp.id_pedido
    WHERE ped.estado = 'PAGADO' 
    AND ped.fecha BETWEEN '$fecha_inicio' AND NOW()
    GROUP BY c.id
    ORDER BY monto_ventas DESC
";
$categoria_ventas = mysqli_query($conexion, $query_categoria_ventas);

$query_platos_vendidos = "
    SELECT pl.nombre AS plato, SUM(dp.cantidad) AS cantidad_vendida
    FROM detalle_pedidos dp
    INNER JOIN pedidos p ON p.id = dp.id_pedido
    INNER JOIN productos pl ON pl.id = dp.id_producto
    WHERE p.estado = 'PAGADO' 
    AND p.fecha BETWEEN '$fecha_inicio' AND NOW()
    GROUP BY pl.nombre
    ORDER BY cantidad_vendida DESC
    LIMIT 10
";
$platos_vendidos = mysqli_query($conexion, $query_platos_vendidos);

include_once "includes/header.php"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
<!-- Tabs Navigation -->
<ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="graficos-tab" data-toggle="tab" data-target="#graficos" type="button" role="tab">Gráficos</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="adicional-tab" data-toggle="tab" data-target="#adicional" type="button" role="tab">Reportes</button>
    </li>
</ul>

<div class="tab-content" id="dashboardTabsContent">
    <!-- Gráficos Tab -->
    <div class="tab-pane fade show active" id="graficos" role="tabpanel">
        <!-- Formulario de Filtros -->
        <form method="post" class="mb-4">
            <div class="form-row align-items-center">
                <div class="col-md-3">
                    <label for="fecha_inicio">Fecha de Inicio:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin">Fecha de Fin:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary mt-4">Generar Reportes</button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="card-title">Clientes con Más Compras</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="clientesComprasChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title">Usuarios con Más Ventas</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="usuariosVentasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="card-title">Montos de Ventas por Categoría</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="categoriaVentasChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="card-title">Platos Más Vendidos</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="platosVendidosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adicional Tab -->
    <div class="tab-pane fade" id="adicional" role="tabpanel">


   

    <?php
// Definir módulos
$modulos = [
    1 => 'Administracion',
    2 => 'Ventas', 
    3 => 'Caja'
];

// Definir reportes por módulo
$reportes = [
    1 => [ // Administración
        'ventas_totales' => 'Ventas Totales',
        'ingresos_categoria' => 'Ingresos por Categoría',
        'rendimiento_empleados' => 'Rendimiento de Empleados',
        'reporte_caja' => 'Reporte Detallado de Caja',
    ],
    2 => [ // Ventas
        'ventas_tipo_pedido' => 'Ventas por Tipo de Pedido',
        'ordenes_atendidas' => 'Órdenes Atendidas',
        'productos_mas_vendidos' => 'Productos Más Vendidos',
        'ocupacion_mesas' => 'Análisis de Ocupación de Mesas'
    ],
    3 => [ // Caja
        'medios_pago' => 'Resumen de Medios de Pago',
        'transacciones_caja' => 'Detalle de Transacciones',
        'arqueo_diario' => 'Arqueo de Caja Diario',
        'ajustes_caja' => 'Ajustes de Caja'
    ]
];

// Tipos de reporte
$tipos_reporte = [
    'pdf' => 'PDF',
    'excel' => 'Excel'
];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Generador de Reportes</h3>
                </div>
                <div class="card-body">
                    <form id="reportForm" method="POST" action="generar_reporte.php">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Seleccionar Módulo</label>
                                    <select id="moduloSelect" name="modulo" class="form-control" required>
                                        <?php foreach ($modulos as $key => $nombre): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Seleccionar Reporte</label>
                                    <select name="reporte" id="reportSelect" class="form-control" required>
                                        <option value="">Seleccione un reporte</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Formato de Reporte</label>
                                    <select name="formato" class="form-control" required>
                                        <option value="">Seleccione un formato</option>
                                        <?php foreach ($tipos_reporte as $codigo => $nombre): ?>
                                            <option value="<?php echo $codigo; ?>"><?php echo $nombre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Rango de Fechas</label>
                                    <div class="input-group">
                                        <input type="date" name="fecha_inicio" class="form-control" required>
                                        <input type="date" name="fecha_fin" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-file-download"></i> Generar Reporte
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const moduloSelect = document.getElementById('moduloSelect');
    const reportSelect = document.getElementById('reportSelect');
    
    // Reportes disponibles en PHP como objeto JSON
    const reportes = <?php echo json_encode($reportes); ?>;

    // Función para actualizar los reportes
    function actualizarReportes() {
        // Limpiar opciones actuales
        reportSelect.innerHTML = '<option value="">Seleccione un reporte</option>';
        
        // Obtener reportes para el módulo seleccionado
        const moduloSeleccionado = moduloSelect.value;
        const reportesModulo = reportes[moduloSeleccionado];
        
        // Agregar nuevas opciones
        for (const [codigo, nombre] of Object.entries(reportesModulo)) {
            const option = document.createElement('option');
            option.value = codigo;
            option.textContent = nombre;
            reportSelect.appendChild(option);
        }
    }

    // Agregar event listener para cambios en el módulo
    moduloSelect.addEventListener('change', actualizarReportes);

    // Inicializar reportes al cargar la página
    actualizarReportes();
});
</script>

<?php include_once "includes/footer.php"; ?>

<script>
// Función para generar colores dinámicos
function generarColores(cantidad) {
    let colores = [];
    for (let i = 0; i < cantidad; i++) {
        let r = Math.floor(Math.random() * 255);
        let g = Math.floor(Math.random() * 255);
        let b = Math.floor(Math.random() * 255);
        colores.push(`rgba(${r}, ${g}, ${b}, 0.6)`);
    }
    return colores;
}

// Función para crear un gráfico
function crearGrafico(id, etiquetas, datos, tipo, colores) {
    var ctx = document.getElementById(id).getContext('2d');
    new Chart(ctx, {
        type: tipo,
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Cantidad',
                data: datos,
                backgroundColor: colores,
                borderColor: colores.map(c => c.replace('0.6', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
}
<?php 
$labels_clientes = []; $data_clientes = [];
while ($cliente = mysqli_fetch_assoc($clientes_compras)) {
    $labels_clientes[] = $cliente['nombres'];
    $data_clientes[] = $cliente['total_comprado'];
}
$labels_usuarios = []; $data_usuarios = [];
while ($usuario = mysqli_fetch_assoc($usuarios_ventas)) {
    $labels_usuarios[] = $usuario['nombre'];
    $data_usuarios[] = $usuario['total_vendido'];
}
$labels_categorias = []; $data_categorias = [];
while ($categoria = mysqli_fetch_assoc($categoria_ventas)) {
    $labels_categorias[] = $categoria['categoria'];
    $data_categorias[] = $categoria['monto_ventas'];
}
$labels_platos = []; $data_platos = [];
while ($plato = mysqli_fetch_assoc($platos_vendidos)) {
    $labels_platos[] = $plato['plato'];
    $data_platos[] = $plato['cantidad_vendida'];
}
?>

crearGrafico('clientesComprasChart', <?php echo json_encode($labels_clientes); ?>, <?php echo json_encode($data_clientes); ?>, 'bar', generarColores(<?php echo count($labels_clientes); ?>));
crearGrafico('usuariosVentasChart', <?php echo json_encode($labels_usuarios); ?>, <?php echo json_encode($data_usuarios); ?>, 'horizontalBar', generarColores(<?php echo count($labels_usuarios); ?>));
crearGrafico('categoriaVentasChart', <?php echo json_encode($labels_categorias); ?>, <?php echo json_encode($data_categorias); ?>, 'pie', generarColores(<?php echo count($labels_categorias); ?>));
crearGrafico('platosVendidosChart', <?php echo json_encode($labels_platos); ?>, <?php echo json_encode($data_platos); ?>, 'bar', generarColores(<?php echo count($labels_platos); ?>));
</script>
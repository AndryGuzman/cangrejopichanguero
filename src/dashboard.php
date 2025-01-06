<?php
session_start();
include_once "includes/header.php";
include "../conexion.php";
$query1 = mysqli_query($conexion, "SELECT COUNT(id) AS total FROM salas WHERE estado = 1");
$totalSalas = mysqli_fetch_assoc($query1);
$query2 = mysqli_query($conexion, "SELECT COUNT(id) AS total FROM productos WHERE estado = 1");
$totalPlatos = mysqli_fetch_assoc($query2);
$query3 = mysqli_query($conexion, "SELECT COUNT(id) AS total FROM usuarios WHERE estado = 1");
$totalUsuarios = mysqli_fetch_assoc($query3);
$query4 = mysqli_query($conexion, "SELECT COUNT(id) AS total FROM pedidos WHERE estado = 1");
$totalPedidos = mysqli_fetch_assoc($query4);

$query5 = mysqli_query($conexion, "SELECT SUM(total) AS total FROM pedidos");
$totalVentas = mysqli_fetch_assoc($query5);
?>
<div class="card">
    <div class="card-header text-center">
        Dashboard
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo $totalPlatos['total']; ?></h3>

                        <p>Productos</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="productos.php" class="small-box-footer">Mas informacion <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo $totalSalas['total']; ?></h3>

                        <p>Salas</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="salas.php" class="small-box-footer">Mas informacion <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo $totalUsuarios['total']; ?></h3>

                        <p>Usuarios</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="usuarios.php" class="small-box-footer">Mas informacion <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo $totalPedidos['total']; ?></h3>

                        <p>Pedidos</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="lista_ventas.php" class="small-box-footer">Mas informacion <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Ventas</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex">
                            <p class="d-flex flex-column">
                                <span class="text-bold text-lg">S/.<?php echo $totalVentas['total']; ?></span>
                                <span>Total</span>
                            </p>
                        </div>
                        <!-- /.d-flex -->

                        <div class="position-relative mb-4">
                            <canvas id="sales-chart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botón flotante con diseño mejorado -->
<a href="javascript:void(0);" class="btn btn-primary" id="btnQR" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; border-radius: 50%; padding: 15px 20px; font-size: 24px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); transition: all 0.3s;">
    <i class="fas fa-qrcode"></i> <!-- Icono de QR -->
</a>

<!-- Modal para mostrar el código QR -->
<div class="modal" tabindex="-1" id="qrModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Código QR - Carta de Platos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align: center;"> <!-- Centrado del contenido -->
                <p>Aquí está el código QR para la carta de platos de la cevichería:</p>
                <div id="qrCodeContainer" style="margin: 0 auto; display: inline-block;"></div> <!-- Contenedor centrado -->
            </div>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>

<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

<script>
    // Variable para verificar si el QR ya se ha generado
    let qrGenerated = false;

    // Código para mostrar el QR cuando se hace clic en el botón
    document.getElementById('btnQR').onclick = function() {
        // Mostrar el modal
        $('#qrModal').modal('show');

        // Si el QR no ha sido generado aún
        if (!qrGenerated) {
            // Crear el código QR
            var qrcode = new QRCode(document.getElementById("qrCodeContainer"), {
                text: "https://drive.google.com/file/d/1EfI4LwAlzA7VsqS4eqHGh1NAYRc4rFll/view?usp=sharing", // Sustituye esta URL con la URL de tu carta de platos
                width: 128,
                height: 128
            });

            // Marcar que el QR ya ha sido generado
            qrGenerated = true;
        }
    };
</script>

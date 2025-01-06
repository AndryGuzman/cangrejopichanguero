<?php
if (empty($_SESSION['active'])) {
    header('Location: ../');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de restaurante</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <!-- IonIcons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="dashboard.php" class="brand-link">
                <img src="../assets/img/logo.jpg" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Cangrejo Pichanguero</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle fa-2x text-info"></i>
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo $_SESSION['nombre']; ?></a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <!-- Menu de Ventas -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-pizza-slice"></i>
                                <p>
                                    Ventas
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
    <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2 || $_SESSION['rol'] == 3): ?>
        <li class="nav-item">
            <a 
                href="<?php echo $_SESSION['rol'] == 3 ? 'index.php' : 'tipoVenta.php'; ?>" 
                class="nav-link"
                id="nuevaVentaLink">
                <i class="far fa-circle nav-icon"></i>
                <p>Nueva venta</p>
            </a>
        </li>
    <?php endif; ?>

    <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2 || $_SESSION['rol'] == 3): ?>
        <li class="nav-item">
            <a href="lista_ventas.php" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Historial ventas</p>
            </a>
        </li>
    <?php endif; ?>
</ul>


                                              <!-- Menu de Caja (solo para rol 1 y 2) -->
<?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
    echo '<li class="nav-item">
        <a href="caja.php" class="nav-link">
            <i class="nav-icon fas fa-cash-register"></i>
            <p>Caja</p>
        </a>
    </li>';
} ?>

                        <!-- Menu de Cobros (solo para rol 2) -->
                        <?php if ($_SESSION['rol'] == 2 || $_SESSION['rol'] == 1) {
                            echo '<li class="nav-item">
                                <a href="cobros.php" class="nav-link">
                                    <i class="nav-icon fas fa-credit-card"></i>
                                    <p>Cobros</p>
                                </a>
                            </li>';
                        } ?>
                          <!-- Menu de Gestionar Clientes (solo para rol 1) -->
                          <?php if ($_SESSION['rol'] == 1) {
                            echo '<li class="nav-item">
                                <a href="cliente.php" class="nav-link">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Clientes</p>
                                </a>
                            </li>';
                        } ?>

                        <!-- Menu de  (solo para rol 1 y 2) -->
                        <?php if ($_SESSION['rol'] == 1) {
                            echo '<li class="nav-item">
                                <a href="productos.php" class="nav-link">
                                    <i class="nav-icon fas fa-coffee"></i>
                                    <p>Productos</p>
                                </a>
                            </li>';
                        } ?>




                        <!-- Menu de Administrador (solo para rol 1) -->
                        <?php if ($_SESSION['rol'] == 1) {
                            echo '<li class="nav-item">
                                <a href="salas.php" class="nav-link">
                                    <i class="nav-icon fas fa-door-open"></i>
                                    <p>Salas</p>
                                </a>
                            </li>
                            <li class="nav-item">
        <a href="reportes.php" class="nav-link">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Reportes</p>
        </a>
    </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="nav-icon fas fa-user-cog"></i>
                                    <p>Ajustes<i class="fas fa-angle-left right"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="usuarios.php" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Usuarios</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="config.php" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Configuración</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>';
                        } ?>

                        <li class="nav-item">
                            <a href="salir.php" class="nav-link">
                                <i class="nav-icon fas fa-power-off"></i>
                                <p>Salir</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Main content -->
            <div class="content">
                <div class="container-fluid py-2">
                    <!-- Aquí va el contenido principal -->
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

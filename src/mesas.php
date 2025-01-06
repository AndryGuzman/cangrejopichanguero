<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    $id = $_GET['id_sala'];
    $mesas = $_GET['mesas'];
    $id_TipoPedido = isset($_GET['id_TipoPedido']) ? $_GET['id_TipoPedido'] : 1; // Asigna un valor predeterminado si no está definido
    include_once "includes/header.php";
?>
    <div class="card">
        <div class="card-header text-center">
            Mesas
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                include "../conexion.php";
                $query = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id");
                $result = mysqli_num_rows($query);
                if ($result > 0) {
                    $data = mysqli_fetch_assoc($query);
                    if ($data['mesas'] == $mesas) {
                        $item = 1;
                        for ($i = 0; $i < $mesas; $i++) {
                            // Consulta para encontrar el pedido pendiente de la mesa actual
                            $consulta = mysqli_query($conexion, "SELECT id FROM pedidos WHERE id_sala = $id AND num_mesa = $item AND estado = 'PENDIENTE'");
                            $resultPedido = mysqli_fetch_assoc($consulta);
                            $id_pedido = $resultPedido ? $resultPedido['id'] : null;
                ?>
                            <div class="col-md-3">
                                <div class="card card-widget widget-user">
                                    <div class="widget-user-header bg-<?php echo empty($resultPedido) ? 'success' : 'danger'; ?>">
                                        <h3 class="widget-user-username">MESA</h3>
                                        <h5 class="widget-user-desc"><?php echo $item; ?></h5>
                                    </div>
                                    <div class="widget-user-image">
                                        <img class="img-circle elevation-2" src="../assets/img/mesa.jpg" alt="User Avatar">
                                    </div>
                                    <div class="card-footer">
                                        <div class="description-block">
                                            <?php 
                                            if (empty($id_pedido)) {
                                                echo '<a class="btn btn-outline-info" href="pedido.php?id_sala=' . $id . '&mesa=' . $item . '&id_TipoPedido=' . $id_TipoPedido . '">Atender</a>';
                                            } else {
                                                echo '<a class="btn btn-outline-success" href="finalizar.php?id_sala=' . $id . '&mesa=' . $item . '&id_TipoPedido=' . $id_TipoPedido . '&id_pedido=' . $id_pedido . '">Finalizar</a>';
                                                echo '<a class="btn btn-outline-primary" href="pedido.php?id_sala=' . $id . '&mesa=' . $item . '&id_TipoPedido=' . $id_TipoPedido . '&id_pedido=' . $id_pedido . '">Añadir</a>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                <?php 
                            $item++;
                        }
                    }
                } ?>
            </div>
        </div>
    </div>
<?php 
    include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
} 
?>

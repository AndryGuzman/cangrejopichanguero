<?php
session_start();
if (empty($_SESSION['active'])) {
    header('Location: ../');
    exit();
}

include_once "includes/header.php";

// Obtenemos el rol del usuario desde la sesión
$rolUsuario = $_SESSION['rol'];
?>

<!-- Incluir CSS de Bootstrap -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- Incluir jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Incluir JavaScript de Bootstrap -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Modal para seleccionar tipo de venta -->
<div class="modal fade" id="ventaModal" tabindex="-1" role="dialog" aria-labelledby="ventaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ventaModalLabel">Seleccione el Tipo de Venta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <?php if ($rolUsuario != 2): ?>
                    <!-- Mostrar el botón de "Mesa" solo si el rol no es 2 -->
                    <button onclick="redirigirVenta('mesa')" class="btn btn-primary m-2">Mesa</button>
                <?php endif; ?>
                <!-- Botones comunes para todos -->
                <button onclick="redirigirVenta('delivery')" class="btn btn-info m-2">Delivery</button>
                <button onclick="redirigirVenta('llevar')" class="btn btn-success m-2">Para Llevar</button>
            </div>
        </div>
    </div>
</div>

<!-- Script para redirigir según el tipo de venta y mostrar el modal al cargar la página -->
<script type="text/javascript">
    $(document).ready(function(){
        $('#ventaModal').modal('show');
    });

    function redirigirVenta(tipoVenta) {
        let id_TipoPedido = 0;
        if (tipoVenta === 'mesa') {
            id_TipoPedido = 1;
            window.location.href = 'index.php';
        } else if (tipoVenta === 'delivery' || tipoVenta === 'llevar') {
            id_TipoPedido = (tipoVenta === 'delivery') ? 2 : 3;
            window.location.href = 'pedido.php?id_sala=1&mesa=5&id_TipoPedido=' + id_TipoPedido;
        }
    }
</script>

<?php include_once "includes/footer.php"; ?>

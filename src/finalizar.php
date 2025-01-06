<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3 || $_SESSION['rol'] == 2) {
    $fecha = date('Y-m-d');
    $id_sala = $_GET['id_sala'];
    $mesa = $_GET['mesa'];
    $id_cliente = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;

    include_once "includes/header.php";
    include "../conexion.php";
?>

<div class="container mt-5">
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-edit"></i> Detalle del Pedido</h3>
        </div>
        <div class="card-body">
            <input type="hidden" id="id_sala" value="<?php echo $id_sala; ?>">
            <input type="hidden" id="mesa" value="<?php echo $mesa; ?>">
            <input type="hidden" id="id_cliente" value="<?php echo $id_cliente; ?>">

            <?php
            $query = mysqli_query($conexion, "SELECT * FROM pedidos WHERE id_sala = $id_sala AND num_mesa = $mesa AND estado = 'PENDIENTE'");
            if ($result = mysqli_fetch_assoc($query)) {
                $id_pedido = $result['id'];
            ?>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Fecha:</strong> <?php echo $result['fecha']; ?></p>
                    <p><strong>Mesa:</strong> <?php echo $mesa; ?></p>
                </div>
                <div class="col-md-6 text-right">
                <h4>Total: <span class="text-success">S/. <?php echo number_format($result['total'], 2); ?></span></h4>

                </div>
            </div>
            <hr>
            <h5 class="text-center">Productos</h5>
            <div class="row">
                <?php
                $detalle_query = mysqli_query($conexion, "SELECT dp.id,p.nombre,dp.precio,dp.cantidad,dp.id_pedido FROM detalle_pedidos dp
INNER JOIN productos p ON p.id=dp.id_producto
WHERE id_pedido = $id_pedido");
                while ($detalle = mysqli_fetch_assoc($detalle_query)) {
                ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-secondary">
                        <div class="card-header bg-secondary text-white text-center">
                            <strong><?php echo $detalle['nombre']; ?></strong>
                        </div>
                        <div class="card-body text-center">
                            <p><strong>Precio:</strong> S/ <?php echo number_format($detalle['precio'], 2); ?></p>
                            <p><strong>Cantidad:</strong> <?php echo $detalle['cantidad']; ?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <hr>
            <div>
                <h5>Cliente Asociado:</h5>
                <p id="nombreCliente">
                    <?php
                    if ($id_cliente) {
                        $cliente_query = mysqli_query($conexion, "SELECT nombres, apellidos FROM cliente WHERE id = $id_cliente");
                        if ($cliente = mysqli_fetch_assoc($cliente_query)) {
                            echo $cliente['nombres'] . ' ' . $cliente['apellidos'];
                        } else {
                            echo '<span class="text-danger">Cliente no encontrado</span>';
                        }
                    } else {
                        echo '<span class="text-warning">No se ha asociado ningún cliente</span>';
                    }
                    ?>
                </p>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-secondary" data-toggle="modal" data-target="#asociarClienteModal">
                    <i class="fas fa-user-plus"></i> Asociar Cliente
                </button>
                <?php if ($id_cliente) { ?>
                <a href="#" class="btn btn-primary finalizarPedido">
                    <i class="fas fa-check-circle"></i> Finalizar Pedido
                </a>
                <?php } ?>
            </div>

            <?php } else { ?>
            <div class="alert alert-warning text-center">
                No se encontró un pedido pendiente para esta mesa.
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Modal para asociar cliente -->
<div class="modal fade" id="asociarClienteModal" tabindex="-1" aria-labelledby="asociarClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asociarClienteModalLabel">Asociar Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-md-4">
                        <select id="criterioBusqueda" class="form-control">
                            <option value="dni">DNI</option>
                            <option value="nombres">Nombre</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" id="buscarCliente" placeholder="Ingrese el criterio seleccionado">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-block" id="btnBuscarCliente">Buscar</button>
                    </div>
                </div>
                <div id="listaClientes">
                    <!-- Lista de clientes cargada dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>


<script>
// Función para seleccionar un cliente y redirigir
function seleccionarCliente(idCliente, nombres, apellidos) {
    // Obtener la URL actual
    var url = window.location.href;

    // Comprobar si ya existe un parámetro id_cliente en la URL
    var newUrl = new URL(url);
    newUrl.searchParams.set('id_cliente', idCliente);  // Establece el id_cliente en la URL

    // Redirigir a la nueva URL con el id_cliente en los parámetros
    window.location.href = newUrl.href;

    // Actualizar el nombre completo del cliente en la interfaz
    document.getElementById("nombreCliente").textContent = nombres + ' ' + apellidos;
}

// Evento de búsqueda de clientes con el criterio seleccionado
document.getElementById("btnBuscarCliente").addEventListener("click", function() {
    let criterio = document.getElementById("criterioBusqueda").value;
    let busqueda = document.getElementById("buscarCliente").value;

    // Limpia el contenedor de resultados antes de realizar una nueva búsqueda
    document.getElementById("listaClientes").innerHTML = '';

    fetch('buscar_cliente.php?criterio=' + criterio + '&query=' + encodeURIComponent(busqueda))
        .then(response => response.text())
        .then(data => {
            document.getElementById("listaClientes").innerHTML = data;
            document.getElementById("noClienteEncontrado").style.display = data.trim() === "" ? "block" : "none";
        })
        .catch(error => console.error('Error en la búsqueda:', error));
});
</script>

<?php include_once "includes/footer.php"; ?>
<?php } else { header('Location: permisos.php'); } ?>

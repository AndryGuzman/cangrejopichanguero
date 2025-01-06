<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3|| $_SESSION['rol'] == 2) {
    include_once "includes/header.php";

    $id_TipoPedido = isset($_GET['id_TipoPedido']) ? $_GET['id_TipoPedido'] : 1;
    $id_pedido = isset($_GET['id_pedido']) ? $_GET['id_pedido'] : null;
    $categoria_filtrada = isset($_GET['categoria']) ? $_GET['categoria'] : ''; // Obtener la categoría seleccionada
?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="fas fa-utensils"></i> Gestión de Pedido
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
           <!-- Sección de Platos Disponibles -->
<div class="col-md-8">
    <input type="hidden" id="id_sala" value="<?php echo $_GET['id_sala'] ?>">
    <input type="hidden" id="mesa" value="<?php echo $_GET['mesa'] ?>">
    <input type="hidden" id="id_TipoPedido" value="<?php echo $id_TipoPedido; ?>">
    <input type="hidden" id="id_pedido" value="<?php echo $id_pedido; ?>">

    <h5 class="mb-4 text-secondary">
        <i class="fas fa-concierge-bell"></i> Platos Disponibles
    </h5>

    <!-- Buscador dinámico -->
    <div class="form-group">
        <label for="buscador_platos" class="text-secondary font-weight-bold">Buscar Plato</label>
        <input type="text" id="buscador_platos" class="form-control" placeholder="Escribe para buscar...">
    </div>

    <!-- Filtro por categoría -->
    <div class="form-group">
        <label for="filtro_categoria" class="text-secondary font-weight-bold">Filtrar por Categoría</label>
        <select id="filtro_categoria" class="form-control">
            <option value="">Todas las Categorías</option>
            <?php
            include "../conexion.php";
            $query_categoria = mysqli_query($conexion, "SELECT id, nombre FROM categoria WHERE estado = 1");
            while ($categoria = mysqli_fetch_assoc($query_categoria)) { ?>
                <option value="<?php echo $categoria['id']; ?>"
                    <?php echo ($categoria['id'] == $categoria_filtrada) ? 'selected' : ''; ?>>
                    <?php echo $categoria['nombre']; ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <!-- Contenedor de Platos Disponibles -->
    <div id="platos_disponibles" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" style="max-height: 400px; overflow-y: auto;">
    <style>
    .card-img-top {
            height: 150px; /* Altura fija */
            object-fit: contain; /* Muestra la imagen completa */
            width: 100%; /* Asegura que ocupe todo el ancho del contenedor */
            background-color: #f8f9fa;
    }
    </style>
    <!-- Platos disponibles cargados dinámicamente -->
    <?php
    $query_platos = "SELECT * FROM productos WHERE estado = 1";
    if ($categoria_filtrada) {
        $query_platos .= " AND id_categoria = $categoria_filtrada";
    }
    $result_platos = mysqli_query($conexion, $query_platos);

    if (mysqli_num_rows($result_platos) > 0) {
        while ($data = mysqli_fetch_assoc($result_platos)) { ?>
            <div class="col plato-item" data-nombre="<?php echo strtolower($data['nombre']); ?>">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo ($data['imagen'] == null) ? '../assets/img/default.png' : $data['imagen']; ?>" class="card-img-top" alt="Plato">
                    <div class="card-body text-center d-flex flex-column" style="min-height: 250px;">
                        <h6 class="card-title font-weight-bold"><?php echo $data['nombre']; ?></h6>
                        <p class="text-success font-weight-bold mb-3">S/.<?php echo $data['precio']; ?></p>
                        <button class="btn btn-outline-primary btn-sm addDetalle" 
                                data-id="<?php echo $data['id']; ?>" 
                                data-nombre="<?php echo $data['nombre']; ?>" 
                                data-precio="<?php echo $data['precio']; ?>">
                            <i class="fas fa-cart-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            </div>
        <?php }
    } else { ?>
        <p class="text-center text-muted">No hay platos disponibles en esta categoría.</p>
    <?php }
    ?>
</div>


</div>


                <!-- Sección de Pedido -->
                <div class="col-md-4">
    <h5 class="mb-4 text-secondary"><i class="fas fa-list"></i> Detalle del Pedido</h5>
    <div class="card shadow-sm border-0">
        <div class="card-body" id="detalle_pedido" style="max-height: 300px; overflow-y: auto;">
            <ul class="list-group" id="contenedor_platos">
                <li class="list-group-item text-muted text-center">No hay platos seleccionados aún.</li>
            </ul>
        </div>
        <hr class="my-2">
        <div class="form-group d-flex justify-content-between align-items-center">
            <label for="total" class="mb-0 fw-bold text-primary">Total:</label>
            <span id="total_monto" class="fw-bold">S/.0.00</span>
        </div>
        <hr class="my-2">
        <div class="form-group">
            <label for="observacion">Observaciones</label>
            <textarea id="observacion" class="form-control rounded-3" rows="3" placeholder="Observaciones"></textarea>
        </div>
        <?php if ($id_pedido) { ?>
            <button class="btn btn-warning btn-block rounded-3" id="modificar_pedido">
                <i class="fas fa-edit"></i> Modificar pedido
            </button>
        <?php } else { ?>
            <button class="btn btn-primary btn-block rounded-3" id="realizar_pedido">
                <i class="fas fa-check"></i> Realizar pedido
            </button>
        <?php } ?>
    </div>
</div>
            </div>
        </div>
    </div>
</div>
<?php
include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
}
?>

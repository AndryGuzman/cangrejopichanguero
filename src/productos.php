<?php
session_start();
if ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 2) {
    header('Location: permisos.php');
    exit;
}

include "productos_service.php";

$alert = "";

// Obtener categorías
$categorias = obtenerCategorias($conexion);

// Procesar formulario
if (isset($_POST['guardar'])) {
    $datos = [
        'id' => intval($_POST['id'] ?? 0),
        'plato' => trim($_POST['plato'] ?? ''),
        'precio' => floatval($_POST['precio'] ?? 0),
        'categoria' => trim($_POST['categoria'] ?? ''),
        'foto_actual' => $_POST['foto_actual'] ?? '',
        'foto' => $_FILES['foto'] ?? null
    ];

    $resultado = guardarProducto($conexion, $datos);
    $alert = isset($resultado['error']) 
        ? "<div class='alert alert-danger'>{$resultado['error']}</div>" 
        : "<div class='alert alert-success'>{$resultado['success']}</div>";
}

// Obtener productos
$filtros = [
    'categoria' => $_POST['categoria'] ?? '',
    'plato' => $_POST['plato'] ?? '',
    'precio' => $_POST['precio'] ?? ''
];
$productos = obtenerProductos($conexion, $filtros);

include_once "includes/header.php";
?>
<div class="card">
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data" id="formulario">
            <?php echo $alert; ?>
            <input type="hidden" id="id" name="id">
            <input type="hidden" id="foto_actual" name="foto_actual">
            <div class="row">
                <div class="col-md-3">
                    <label for="plato">Plato</label>
                    <input type="text" class="form-control" id="plato" name="plato" placeholder="Ingrese nombre del plato" required>
                </div>
                <div class="col-md-3">
                    <label for="precio">Precio</label>
                    <input type="number" class="form-control" id="precio" name="precio" step="0.01" placeholder="Ingrese precio" required>
                </div>
                <div class="col-md-3">
                    <label for="categoria">Categoría</label>
                    <select name="categoria" id="categoria" class="form-control" required>
                        <option value="">Seleccione una categoría</option>
                        <?php
                        $categorias = mysqli_query($conexion, "SELECT nombre FROM categoria");
                        while ($categoria = mysqli_fetch_assoc($categorias)) {
                            echo "<option value='{$categoria['nombre']}'>{$categoria['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="foto">Foto</label>
                    <input type="file" class="form-control" id="foto" name="foto">
                </div>
            </div>
            <div class="mt-3">
            <button type="submit" name="guardar" class="btn btn-primary" id="btnGuardar">Guardar</button>
                <a type="button" href="productos.php" class="btn btn-success">Limpiar</a/>
                <a type="button" href="carta.php" class="btn btn-danger">Carta de Platos<i class="fas fa-external-link-alt"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Categoría</th>
                <th>Plato</th>
                <th>Precio</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT p.*, c.nombre AS categoria FROM productos p 
                                              INNER JOIN categoria c ON p.id_categoria = c.id 
                                              WHERE p.estado = 1");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    ?>
                    <tr>
                        <td><?php echo $data['id']; ?></td>
                        <td><?php echo $data['categoria']; ?></td>
                        <td><?php echo $data['nombre']; ?></td>
                        <td><?php echo $data['precio']; ?></td>
                        <td><img src="<?php echo $data['imagen'] ?: '../assets/img/default.png'; ?>" width="100"></td>
                        <td>
                            <a href="#" onclick="editarPlato(<?php echo $data['id']; ?>, '<?php echo $data['nombre']; ?>', 
                                                               <?php echo $data['precio']; ?>, '<?php echo $data['imagen']; ?>', 
                                                               '<?php echo $data['categoria']; ?>')" 
                               class="btn btn-warning"><i class="fas fa-pencil-alt"></i></a>
                            <form action="eliminar.php?id=<?php echo $data['id']; ?>&accion=productos" method="post" class="confirmar d-inline">
                                <button class="btn btn-danger" type="submit"><i class='fas fa-trash-alt'></i> </button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>

<script src="../assets/js/productos.js"></script>

<?php
include_once "includes/footer.php";
?>

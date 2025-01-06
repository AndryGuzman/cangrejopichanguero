<?php
session_start();
if ($_SESSION['rol'] != 1) {
    header('Location: permisos.php');
    exit;
}
include "../conexion.php";

// Verificar si se ha enviado el formulario
if (!empty($_POST)) {
    $alert = "";
    // Validar que los campos no estén vacíos
    if (empty($_POST['nombre']) || empty($_POST['mesas'])) {
        $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Todos los campos son obligatorios
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    } else {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $mesas = $_POST['mesas'];
        $result = 0;

        // Si el ID está vacío, es un nuevo registro
        if (empty($id)) {
            $query = mysqli_query($conexion, "SELECT * FROM salas WHERE nombre = '$nombre' AND estado = 1");
            $result = mysqli_fetch_array($query);
            if ($result > 0) {
                $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        La sala ya existe
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            } else {
                $query_insert = mysqli_query($conexion, "INSERT INTO salas (nombre, mesas) VALUES ('$nombre', '$mesas')");
                if ($query_insert) {
                    $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Sala registrada
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                } else {
                    $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error al registrar
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
                }
            }
        } else {
            // Si hay un ID, es una actualización
            $sql_update = mysqli_query($conexion, "UPDATE salas SET nombre = '$nombre', mesas = '$mesas' WHERE id = $id");
            if ($sql_update) {
                $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Sala modificada
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            } else {
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error al modificar
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            }
        }
    }
}

// Cargar los datos de la sala si se pasa un ID para editar
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = mysqli_query($conexion, "SELECT * FROM salas WHERE id = '$id' AND estado = 1");
    $data = mysqli_fetch_assoc($query);
    $nombre = $data['nombre'];
    $mesas = $data['mesas'];
} else {
    $nombre = '';
    $mesas = '';
}

include_once "includes/header.php";
?>

<div class="card">
    <div class="card-body">
        <div class="card">
            <div class="card-body">
                <?php echo (isset($alert)) ? $alert : ''; ?>
                <form action="" method="post" autocomplete="off" id="formulario">
                    <input type="hidden" name="id" id="id" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">    
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="nombre" class="text-dark font-weight-bold">Nombre</label>
                                <input type="text" placeholder="Ingrese Nombre" name="nombre" id="nombre" class="form-control" value="<?php echo $nombre; ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="mesas" class="text-dark font-weight-bold">Mesas</label>
                                <input type="number" placeholder="Mesas" name="mesas" id="mesas" class="form-control" value="<?php echo $mesas; ?>">
                            </div>
                        </div>
                        <div class="col-md-5 text-center">
                            <label for="">Acciones</label> <br>
                            <input type="submit" value="<?php echo isset($data['id']) ? 'Guardar Cambios' : 'Registrar'; ?>" class="btn btn-primary" id="btnAccion">
                            <a type="button" class="btn btn-success" href="salas.php" >Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tbl">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Mesas</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include "../conexion.php";

                            $query = mysqli_query($conexion, "SELECT * FROM salas WHERE estado = 1");
                            $result = mysqli_num_rows($query);
                            if ($result > 0) {
                                while ($data = mysqli_fetch_assoc($query)) { ?>
                                    <tr>
                                        <td><?php echo $data['id']; ?></td>
                                        <td><?php echo $data['nombre']; ?></td>
                                        <td><?php echo $data['mesas']; ?></td>
                                        <td>
                                            <a href="salas.php?id=<?php echo $data['id']; ?>" class="btn btn-primary"><i class='fas fa-edit'></i></a>
                                            <form action="eliminar.php?id=<?php echo $data['id']; ?>&accion=salas" method="post" class="confirmar d-inline">
                                                <button class="btn btn-danger" type="submit"><i class='fas fa-trash-alt'></i> </button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php }
                            } ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/salas.js"></script>

<?php
// Cerramos la conexión al final del script
mysqli_close($conexion);
?>

<?php include_once "includes/footer.php"; ?>

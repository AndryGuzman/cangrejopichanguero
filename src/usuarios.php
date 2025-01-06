<?php
session_start();
if ($_SESSION['rol'] != 1) {
    header('Location: permisos.php');
    exit;
}
include "../conexion.php";
$alert = "";
$id = 0; // Variable para almacenar el ID del usuario

// Verificamos si se ha enviado un ID de usuario para editar
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id = $id AND estado = 1");
    $data = mysqli_fetch_assoc($query);
    if (!$data) {
        header("Location: usuarios.php");
        exit;
    }
}

// Si se envían los datos del formulario
if (!empty($_POST)) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];
    $pass = $_POST['pass'];
    
    if (empty($nombre) || empty($correo) || empty($rol)) {
        $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    Todos los campos son obligatorios
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
    } else {
        if (empty($id)) {
            if (empty($pass)) {
                $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    La contraseña es requerida
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
            } else {
                $pass = md5($_POST['pass']);
                $query = mysqli_query($conexion, "SELECT * FROM usuarios where correo = '$correo' AND estado = 1");
                $result = mysqli_fetch_array($query);
                if ($result > 0) {
                    $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    El correo ya existe
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
                } else {
                    $query_insert = mysqli_query($conexion, "INSERT INTO usuarios (nombre, correo, rol, pass) VALUES ('$nombre', '$correo', '$rol', '$pass')");
                    if ($query_insert) {
                        $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Usuario Registrado
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
            }
        } else {
            // Si la contraseña no está vacía, la actualizamos, de lo contrario, mantenemos la contraseña actual
            if (!empty($pass)) {
                $pass = md5($pass);
                $sql_update = mysqli_query($conexion, "UPDATE usuarios SET nombre = '$nombre', correo = '$correo', rol = '$rol', pass = '$pass' WHERE id = $id");
            } else {
                $sql_update = mysqli_query($conexion, "UPDATE usuarios SET nombre = '$nombre', correo = '$correo', rol = '$rol' WHERE id = $id");
            }

            if ($sql_update) {
                $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Usuario Modificado
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
include "includes/header.php";
?>
<div class="card">
    <div class="card-body">
        <form action="" method="post" autocomplete="off" id="formulario">
            <?php echo isset($alert) ? $alert : ''; ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" placeholder="Ingrese Nombre" name="nombre" id="nombre" value="<?php echo isset($data['nombre']) ? $data['nombre'] : ''; ?>">
                        <input type="hidden" id="id" name="id" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="email" class="form-control" placeholder="Ingrese correo Electrónico" name="correo" id="correo" value="<?php echo isset($data['correo']) ? $data['correo'] : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" class="form-control" name="rol">
                            <option>Seleccionar</option>
                            <option value="1" <?php echo isset($data['rol']) && $data['rol'] == 1 ? 'selected' : ''; ?>>Administrador</option>
                            <option value="2" <?php echo isset($data['rol']) && $data['rol'] == 2 ? 'selected' : ''; ?>>Cajero</option>
                            <option value="3" <?php echo isset($data['rol']) && $data['rol'] == 3 ? 'selected' : ''; ?>>Mozo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="pass">Contraseña</label>
                        <input type="password" class="form-control" placeholder="Ingrese Contraseña (dejar vacío si no se cambia)" name="pass" id="pass">
                    </div>
                </div>
            </div>
            <input type="submit" value="<?php echo $id > 0 ? 'Actualizar' : 'Registrar'; ?>" class="btn btn-primary" id="btnAccion">
            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM usuarios WHERE estado = 1");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    if ($data['rol'] == 1) {
                        $rol = '<span class="badge badge-success">Administrador</span>';
                    } else if ($data['rol'] == 2) {
                        $rol = '<span class="badge badge-info">Cajero</span>';
                    } else {
                        $rol = '<span class="badge badge-warning">Mozo</span>';
                    }
                    ?>
                    <tr>
                        <td><?php echo $data['id']; ?></td>
                        <td><?php echo $data['nombre']; ?></td>
                        <td><?php echo $data['correo']; ?></td>
                        <td><?php echo $rol; ?></td>
                        <td>
                            <a href="usuarios.php?id=<?php echo $data['id']; ?>" class="btn btn-success">
                                <i class='fas fa-edit'></i>
                            </a>
                            <form action="eliminar.php?id=<?php echo $data['id']; ?>&accion=usuarios" method="post" class="confirmar d-inline">
                                <button class="btn btn-danger" type="submit"><i class='fas fa-trash-alt'></i> </button>
                            </form>
                        </td>
                    </tr>
                <?php }
            } ?>
        </tbody>
    </table>
</div>
<?php include "includes/footer.php"; ?>


<script>
    // Función para limpiar el formulario
    function limpiar() {
        $('#id').val(''); // Limpiar el campo id
        $('#nombre').val('');
        $('#correo').val('');
        $('#rol').val('');
        $('#btnAccion').val('Registrar');
    }
</script>

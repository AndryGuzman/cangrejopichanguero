<?php include 'cliente_service.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestionar Clientes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-light">
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="text-center mb-4">
        <h1 class="fw-bold text-primary">Gestionar Clientes</h1>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-info mt-3"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <button class="btn btn-primary" data-toggle="modal" data-target="#agregarClienteModal"><i class="fas fa-user-plus"></i> Agregar Cliente </i></button>
    <a class="btn btn-info" href="reporte_clientes.php"><i class="fas fa-file-pdf" > Reporte Clientes </i><a/>
                <br><br>
                <!-- Modal para Agregar Cliente -->
                <div class="modal fade" id="agregarClienteModal" tabindex="-1" role="dialog" aria-labelledby="agregarClienteModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="agregarClienteModalLabel">Agregar Cliente</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div id="formRegistroManual">
                                    <form method="POST" action="">
                                        <div class="form-group mt-3">
                                            <label for="dni">DNI del Cliente</label>
                                            <div class="input-group">
                                                <input type="text" name="documento" id="documento" class="form-control" required>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info" id="validarDni">Validar DNI</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="nombres">Nombres</label>
                                            <input type="text" name="nombres" id="nombres" class="form-control" disabled required>
                                        </div>
                                        <div class="form-group">
                                            <label for="apellidos">Apellidos</label>
                                            <input type="text" name="apellidos" id="apellidos" class="form-control" disabled required>
                                        </div>
                                        <div class="form-group">
                                            <label for="direccion">Dirección</label>
                                            <input type="text" name="direccion" id="direccion" class="form-control" disabled>
                                        </div>
                                        <div class="form-group">
                                            <label for="celular">Celular</label>
                                            <input type="text" name="celular" id="celular" class="form-control" disabled>
                                        </div>
                                        <div class="form-group">
                                            <label for="sexo">Sexo</label>
                                            <select name="sexo" id="sexo" class="form-control" disabled required>
                                                <option value="">Seleccione</option>
                                                <option value="M">Masculino</option>
                                                <option value="F">Femenino</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" disabled>
                                        </div>
                                        <button type="submit" name="guardar_cliente" class="btn btn-primary">Guardar Cliente</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
    <!-- Tabla de Clientes -->
    <div class="card shadow">
        <div class="card-body">
            <table id="tablaClientes" class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>DNI</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Dirección</th>
                        <th>Celular</th>
                        <th>Sexo</th>
                        <th>Fecha de Nacimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?php echo $cliente['documento']; ?></td>
                            <td><?php echo $cliente['nombres']; ?></td>
                            <td><?php echo $cliente['apellidos']; ?></td>
                            <td><?php echo $cliente['direccion']; ?></td>
                            <td><?php echo $cliente['celular']; ?></td>
                            <td><?php echo $cliente['sexo']; ?></td>
                            <td><?php echo $cliente['fecha_nacimiento']; ?></td>
                            <td>
                                <!-- Botón editar -->
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarClienteModal<?php echo $cliente['id']; ?>">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <!-- Modal para editar cliente -->
                                <div class="modal fade" id="editarClienteModal<?php echo $cliente['id']; ?>" tabindex="-1" aria-labelledby="editarClienteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Cliente</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
                                                    <!-- Campos para editar cliente -->
                                                    <?php include 'partials/form_cliente.php'; ?>
                                                    <button type="submit" name="editar_cliente" class="btn btn-primary">Actualizar Cliente</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Botón eliminar -->
                                <form action="eliminar.php?id=<?php echo $cliente['id']; ?>&accion=cliente" method="post" class="d-inline confirmar">
                                    <button class="btn btn-danger btn-sm" type="submit"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="../assets/js/cliente.js"></script>
</body>
</html>

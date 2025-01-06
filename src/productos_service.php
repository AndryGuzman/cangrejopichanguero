<?php
include "../conexion.php";

function obtenerCategorias($conexion) {
    $categorias = mysqli_query($conexion, "SELECT nombre FROM categoria");
    return mysqli_fetch_all($categorias, MYSQLI_ASSOC);
}

function obtenerProductos($conexion, $filtros = []) {
    $query = "SELECT p.*, c.nombre AS categoria 
              FROM productos p 
              INNER JOIN categoria c ON p.id_categoria = c.id 
              WHERE p.estado = 1";

    if (!empty($filtros['categoria'])) {
        $query .= " AND c.nombre LIKE '%" . mysqli_real_escape_string($conexion, $filtros['categoria']) . "%'";
    }
    if (!empty($filtros['plato'])) {
        $query .= " AND p.nombre LIKE '%" . mysqli_real_escape_string($conexion, $filtros['plato']) . "%'";
    }
    if (!empty($filtros['precio'])) {
        $query .= " AND p.precio LIKE '%" . mysqli_real_escape_string($conexion, $filtros['precio']) . "%'";
    }

    return mysqli_query($conexion, $query);
}

function guardarProducto($conexion, $datos) {
    // Validación del precio
    if (floatval($datos['precio']) <= 0) {
        return ['error' => 'El precio debe ser mayor que 0.'];
    }

    $fecha = date('YmdHis');
    $nombre_foto = !empty($datos['foto']['name']) ? "../assets/img/platos/" . $fecha . ".jpg" : $datos['foto_actual'];

    if ($datos['id'] == 0) {
        $query_exist = mysqli_query($conexion, "SELECT * FROM productos WHERE nombre = '{$datos['plato']}' AND estado = 1");
        if (mysqli_num_rows($query_exist) > 0) {
            return ['error' => 'El plato ya existe.'];
        }

        $query = "INSERT INTO productos (nombre, precio, imagen, id_categoria) 
                  VALUES ('{$datos['plato']}', '{$datos['precio']}', '$nombre_foto', 
                          (SELECT id FROM categoria WHERE nombre='{$datos['categoria']}'))";
    } else {
        $query = "UPDATE productos 
                  SET nombre = '{$datos['plato']}', precio = '{$datos['precio']}', 
                      imagen = '$nombre_foto', 
                      id_categoria = (SELECT id FROM categoria WHERE nombre='{$datos['categoria']}') 
                  WHERE id = {$datos['id']}";
    }

    $resultado = mysqli_query($conexion, $query);

    if ($resultado && !empty($datos['foto']['name'])) {
        move_uploaded_file($datos['foto']['tmp_name'], $nombre_foto);
    }

    return $resultado ? ['success' => 'Operación realizada con éxito.'] : ['error' => 'Error al procesar la operación.'];
}

?>

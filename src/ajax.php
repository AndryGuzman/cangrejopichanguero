<?php
require_once "../conexion.php";
session_start();


if (isset($_GET['detalle'])) {
    $datos = array();

// Verificar si el carrito existe en la sesión
if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $data['id_producto'] = $item['id_producto']; // Aquí se incluye el ID del producto
        $data['nombre'] = $item['nombre'];
        $data['cantidad'] = $item['cantidad'];
        $data['precio'] = $item['precio'];
        $data['imagen'] = isset($item['imagen']) ? $item['imagen'] : '../assets/img/default.png';
        $data['total'] = $item['precio'] * $item['cantidad'];
        array_push($datos, $data); // Se agrega el array al resultado final
    }
} else {
    echo json_encode([]); // Si no hay productos, retornar un array vacío
    die();
}


    echo json_encode($datos);
    die();
}



// Eliminar un detalle de pedido temporal
else if (isset($_GET['delete_detalle'])) {
    $id_detalle = $_GET['id'];

    // Verificar si el carrito existe en la sesión
    if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
        // Buscar el índice del producto en el carrito
        $index = array_search($id_detalle, array_column($_SESSION['carrito'], 'id_producto'));

        if ($index !== false) {
            // Eliminar el producto del carrito
            unset($_SESSION['carrito'][$index]);

            // Reindexar el array para que los índices se mantengan consecutivos
            $_SESSION['carrito'] = array_values($_SESSION['carrito']);

            echo json_encode([
                'status' => 'ok',
                'mensaje' => 'Producto eliminado correctamente del carrito'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'Producto no encontrado en el carrito'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'El carrito está vacío'
        ]);
    }
    die();
}


// Modificar la cantidad de un producto en el detalle del pedido
else if (isset($_GET['detalle_cantidad'])) {
    $id_producto = $_GET['id'];  // ID del producto a modificar
    $cantidad = $_GET['cantidad'];  // Nueva cantidad

    // Verificar si el carrito (array de productos) existe en la sesión
    if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
        // Buscar el índice del producto en el carrito
        $index = array_search($id_producto, array_column($_SESSION['carrito'], 'id_producto'));

        if ($index !== false) {
            // Actualizar la cantidad del producto en el carrito
            $_SESSION['carrito'][$index]['cantidad'] = $cantidad;

            // Recalcular el total del carrito
            $total = 0;
            foreach ($_SESSION['carrito'] as $producto) {
                $total += $producto['cantidad'] * $producto['precio'];
            }

            // Actualizar el total en la sesión (si es necesario)
            $_SESSION['total'] = $total;

            echo "ok";
        } else {
            echo "Producto no encontrado en el carrito";
        }
    } else {
        echo "El carrito está vacío";
    }

    die();
}


// Procesar un nuevo pedido
else if (isset($_GET['procesarPedido'])) {
    $id_sala = $_GET['id_sala'];
    $id_user = $_SESSION['idUser'];
    $mesa = $_GET['mesa'];
    $tipoVenta = $_GET['id_TipoPedido'];
    $observacion = $_GET['observacion'];

    // Verificar si el carrito (array de productos) existe en la sesión
    if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
        // Calcular el total a partir de los productos en el carrito
        $total = 0;
        foreach ($_SESSION['carrito'] as $producto) {
            $total += $producto['cantidad'] * $producto['precio']; // Calcula el total
        }

        // Insertar el pedido en la tabla pedidos
        $insertar = mysqli_query($conexion, "INSERT INTO pedidos (id_sala, num_mesa, total, observacion, id_usuario, id_tipoPedido) VALUES ($id_sala, $mesa, '$total', '$observacion', $id_user, '$tipoVenta')");
        $id_pedido = mysqli_insert_id($conexion);

        if ($insertar) {
            // Insertar los productos del carrito en la tabla detalle_pedidos
            foreach ($_SESSION['carrito'] as $producto) {
                $id_producto = $producto['id_producto'];
                $cantidad = $producto['cantidad'];
                $precio = $producto['precio'];
                mysqli_query($conexion, "INSERT INTO detalle_pedidos (id_producto, precio, cantidad, id_pedido) VALUES ('$id_producto', '$precio', $cantidad, $id_pedido)");
            }

            // Limpiar el carrito después de procesar el pedido
            unset($_SESSION['carrito']);

            // Obtener información de la sala
            $sala = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id_sala");
            $resultSala = mysqli_fetch_assoc($sala);
            $msg = array('mensaje' => $resultSala['mesas']);
        } else {
            $msg = array('mensaje' => 'error');
        }
    } else {
        $msg = array('mensaje' => 'El carrito está vacío');
    }

    echo json_encode($msg);
    die();
}

// Modificar un pedido existente
else if (isset($_GET['modificarPedido'])) {
    $id_sala = $_GET['id_sala'];
    $id_user = $_SESSION['idUser'];
    $mesa = $_GET['mesa'];
    $tipoVenta = $_GET['id_TipoPedido'];
    $id_pedido = $_GET['id_pedido'];
    $observacion = $_GET['observacion'];

    // Verificar si el carrito (array de productos) existe en la sesión
    if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
        // Calcular el nuevo total desde el carrito (productos en la sesión)
        $nuevo_total = 0;
        foreach ($_SESSION['carrito'] as $producto) {
            $nuevo_total += $producto['cantidad'] * $producto['precio']; // Calcular el total de los productos en el carrito
        }

        // Obtener el total actual del pedido existente
        $consulta_pedido = mysqli_query($conexion, "SELECT total FROM pedidos WHERE id = $id_pedido");
        $pedido = mysqli_fetch_assoc($consulta_pedido);
        $total_actual = $pedido['total'];

        // Calcular el total final sumando el nuevo total al total actual del pedido
        $total_final = $total_actual + $nuevo_total;

        // Actualizar el pedido con el nuevo total y la observación
        $actualizar = mysqli_query($conexion, "UPDATE pedidos SET total = '$total_final', observacion = '$observacion' WHERE id = $id_pedido");

        if ($actualizar) {
            // Agregar los nuevos productos del carrito al detalle del pedido
            foreach ($_SESSION['carrito'] as $producto) {
                $id_producto = $producto['id_producto'];
                $cantidad = $producto['cantidad'];
                $precio = $producto['precio'];
                mysqli_query($conexion, "INSERT INTO detalle_pedidos (id_producto, precio, cantidad, id_pedido) VALUES ('$id_producto', '$precio', $cantidad, $id_pedido)");
            }

            // Limpiar el carrito después de modificar el pedido
            unset($_SESSION['carrito']);

            // Obtener información de la sala
            $sala = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id_sala");
            $resultSala = mysqli_fetch_assoc($sala);
            $msg = array('mensaje' => $resultSala['mesas']);
        } else {
            $msg = array('mensaje' => 'error al actualizar el pedido');
        }
    } else {
        $msg = array('mensaje' => 'El carrito está vacío');
    }

    echo json_encode($msg);
    die();
}



// Finalizar un pedido
else if (isset($_GET['finalizarPedido'])) {
    $id_sala = $_GET['id_sala'];
    $id_user = $_SESSION['idUser'];
    $mesa = $_GET['mesa'];
    $id_cliente = $_GET['id_cliente'];  // Obtener el id_cliente
    $insertar = mysqli_query($conexion, "UPDATE pedidos SET id_cliente = '$id_cliente', estado = 'FINALIZADO' WHERE id_sala = $id_sala AND num_mesa = $mesa AND estado = 'PENDIENTE' AND id_usuario = $id_user");

    if ($insertar) {
        $sala = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id_sala");
        $resultSala = mysqli_fetch_assoc($sala);
        $msg = array('mensaje' => $resultSala['mesas']);
    } else {
        $msg = array('mensaje' => 'error');
    }

    echo json_encode($msg);
    die();
}

if (isset($_POST['regDetalle'])) {
    $id_producto = $_POST['id'];

    // Verificar si existe el carrito en la sesión
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = []; // Crear el carrito como array vacío
    }

    // Buscar el producto en el carrito
    $producto_encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id_producto'] == $id_producto) {
            $item['cantidad'] += 1; // Incrementar la cantidad
            $producto_encontrado = true;
            break;
        }
    }

    if (!$producto_encontrado) {
        // Obtener detalles del producto desde la base de datos
        $producto = mysqli_query($conexion, "SELECT * FROM productos WHERE id = $id_producto");
        $result = mysqli_fetch_assoc($producto);

        if ($result) {
            // Agregar nuevo producto al carrito
            $_SESSION['carrito'][] = [
                'id_producto' => $id_producto,
                'nombre' => $result['nombre'], // Si deseas mostrar el nombre del producto
                'precio' => $result['precio'],
                'cantidad' => 1,
            ];
        } else {
            echo json_encode("Error: Producto no encontrado");
            die();
        }
    }

    echo json_encode("registrado");
    die();
}


?>

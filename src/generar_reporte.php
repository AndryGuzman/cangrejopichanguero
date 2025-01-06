<?php
// Archivo: generar_reporte.php
require_once('../conexion.php');  // Incluir el archivo de conexión

// Función para generar reportes
function generarReporte($modulo, $reporte, $fecha_inicio, $fecha_fin, $formato) {
    global $conexion;  // Usar la conexión global definida en conexion.php

    // Reportes por módulo
    switch ($modulo) {
        case 1: // Administración
            switch ($reporte) {
                case 'ventas_totales':
                    $sql = "SELECT 
                                DATE(fecha) as fecha, 
                                SUM(total) as total_ventas, 
                                COUNT(id) as num_pedidos
                            FROM pedidos 
                            WHERE fecha BETWEEN ? AND ?
                            GROUP BY DATE(fecha)
                            ORDER BY fecha";
                    $titulo = "Reporte de Ventas Totales";
                    break;

                case 'ingresos_categoria':
                    $sql = "SELECT 
                                c.nombre as categoria, 
                                SUM(dp.precio * dp.cantidad) as ingresos_categoria
                            FROM detalle_pedidos dp
                            JOIN productos p ON dp.id_producto = p.id
                            JOIN categoria c ON p.id_categoria = c.id
                            JOIN pedidos ped ON dp.id_pedido = ped.id
                            WHERE ped.fecha BETWEEN ? AND ?
                            GROUP BY c.nombre
                            ORDER BY ingresos_categoria DESC";
                    $titulo = "Ingresos por Categoría de Producto";
                    break;

                case 'rendimiento_empleados':
                    $sql = "SELECT 
                                u.nombre as empleado, 
                                COUNT(p.id) as pedidos_atendidos,
                                SUM(p.total) as total_ventas
                            FROM pedidos p
                            JOIN usuarios u ON p.id_usuario = u.id
                            WHERE p.fecha BETWEEN ? AND ?
                            GROUP BY u.id, u.nombre
                            ORDER BY total_ventas DESC";
                    $titulo = "Rendimiento de Empleados";
                    break;

                case 'reporte_caja':
                    $sql = "SELECT 
                                cs.id as sesion,
                                c.nombre as caja,
                                cs.turno,
                                cs.fecha_apertura,
                                cs.fecha_cierre,
                                cs.monto_apertura,
                                cs.monto_cierre,
                                SUM(p.total) as total_ventas
                            FROM caja_sesion cs
                            JOIN caja c ON cs.id_caja = c.id
                            LEFT JOIN pedidos p ON p.fecha BETWEEN cs.fecha_apertura AND cs.fecha_cierre
                            WHERE cs.fecha_apertura BETWEEN ? AND ?
                            GROUP BY cs.id
                            ORDER BY cs.fecha_apertura";
                    $titulo = "Reporte Detallado de Caja";
                    break;

            }
            break;

        case 2: // Ventas
            switch ($reporte) {
                case 'ventas_tipo_pedido':
                    $sql = "SELECT 
                                tp.tipoPedido, 
                                COUNT(p.id) as num_pedidos,
                                SUM(p.total) as total_ventas
                            FROM pedidos p
                            JOIN tipopedido tp ON p.id_tipoPedido = tp.idTipoPedido
                            WHERE p.fecha BETWEEN ? AND ?
                            GROUP BY tp.tipoPedido
                            ORDER BY total_ventas DESC";
                    $titulo = "Ventas por Tipo de Pedido";
                    break;

                case 'ordenes_atendidas':
                    $sql = "SELECT 
                                u.nombre as empleado, 
                                COUNT(p.id) as ordenes_atendidas
                            FROM pedidos p
                            JOIN usuarios u ON p.id_usuario = u.id
                            WHERE p.fecha BETWEEN ? AND ?
                            GROUP BY u.id, u.nombre
                            ORDER BY ordenes_atendidas DESC";
                    $titulo = "Órdenes Atendidas por Empleado";
                    break;

                case 'productos_mas_vendidos':
                    $sql = "SELECT 
                                pr.nombre as producto, 
                                SUM(dp.cantidad) as cantidad_vendida,
                                SUM(dp.precio * dp.cantidad) as total_ventas
                            FROM detalle_pedidos dp
                            JOIN productos pr ON dp.id_producto = pr.id
                            JOIN pedidos p ON dp.id_pedido = p.id
                            WHERE p.fecha BETWEEN ? AND ?
                            GROUP BY pr.id, pr.nombre
                            ORDER BY cantidad_vendida DESC
                            LIMIT 10";
                    $titulo = "Top 10 Productos Más Vendidos";
                    break;

                case 'ocupacion_mesas':
                    $sql = "SELECT 
                                s.nombre as sala, 
                                num_mesa, 
                                COUNT(p.id) as veces_ocupada
                            FROM pedidos p
                            JOIN salas s ON p.id_sala = s.id
                            WHERE p.fecha BETWEEN ? AND ?
                            GROUP BY s.id, s.nombre, num_mesa
                            ORDER BY veces_ocupada DESC";
                    $titulo = "Análisis de Ocupación de Mesas";
                    break;
            }
            break;

        case 3: // Caja
            switch ($reporte) {
                case 'medios_pago':
                    $sql = "SELECT 
                                p.medio_pago, 
                                COUNT(p.medio_pago) as num_transacciones,
                                SUM(pe.total) as total_pagado
                            FROM pago p
                            INNER JOIN pedidos pe ON p.id_pedido=pe.id
                            WHERE p.fecha BETWEEN ? AND ?
                            GROUP BY medio_pago
                            ORDER BY total_pagado DESC";
                    $titulo = "Resumen de Medios de Pago";
                    break;

                case 'transacciones_caja':
                    $sql = "SELECT 
                                p.fecha,
                                p.medio_pago,
                                p.num_operacion,
                                p.comprobante,
                                pe.total
                            FROM pago p
                            INNER JOIN pedidos pe ON p.id_pedido=pe.id
                            WHERE p.fecha BETWEEN ? AND ?
                            ORDER BY p.fecha";
                    $titulo = "Detalle de Transacciones";
                    break;

                case 'arqueo_diario':
                    $sql = "SELECT 
                                cs.id as sesion,
                                cs.fecha_apertura,
                                cs.fecha_cierre,
                                cs.monto_apertura,
                                cs.monto_cierre,
                                cs.saldo_final,
                                SUM(pe.Total) as total_ingresos
                            FROM caja_sesion cs
                            LEFT JOIN pago p ON p.id_sesion = cs.id
                            LEFT JOIN pedidos pe ON pe.id = p.id_pedido
                            WHERE cs.fecha_apertura BETWEEN ? AND ?
                            GROUP BY cs.id
                            ORDER BY cs.fecha_apertura";
                    $titulo = "Arqueo de Caja Diario";
                    break;

                case 'ajustes_caja':
                    $sql = "SELECT 
                                ac.fecha_registro,
                                ac.tipo,
                                ac.monto,
                                ac.descripcion
                            FROM ajustes_caja ac
                            JOIN caja_sesion cs ON ac.id_sesion = cs.id
                            WHERE ac.fecha_registro BETWEEN ? AND ?
                            ORDER BY ac.fecha_registro";
                    $titulo = "Ajustes de Caja";
                    break;
            }
            break;
    }

    // Preparar y ejecutar consulta
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $fecha_inicio, $fecha_fin);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    // Generar reporte según formato
    if ($formato == 'pdf') {
        generarPDF($resultado, $titulo);
    } elseif ($formato == 'excel') {
        generarExcel($resultado, $titulo);
    }

    mysqli_stmt_close($stmt);
    // mysqli_close($conexion); No cerrar la conexión, ya que está en conexion.php
}

// Incluir la librería TCPDF
require_once('../library/tcpdf.php');

// Clase personalizada para generar el PDF
class MiPDF extends TCPDF {
    // Sobrescribir el método Header() para personalizar el encabezado
    public function Header() {
        // Logo - Reducido de tamaño y ajustado
        $image_file = '../assets/img/logo.jpg';
        $this->Image($image_file, 15, 15, 25, 25, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        // Título y dirección de la cevichería
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor(39, 55, 70);  // Azul oscuro elegante
        $this->SetXY(50, 18);  // Ajustamos la posición para que no se solape con la imagen
        $this->Cell(0, 10, 'Cangrejo Pichanguero', 0, 1, 'L');
        
        $this->SetFont('helvetica', '', 12);
        $this->SetTextColor(90, 90, 90);
        $this->SetXY(50, 28);  // Ajustamos la posición para la dirección
        $this->Cell(0, 6, 'Morona 1150 - Sabor Auténtico', 0, 1, 'L');
        
        // Línea decorativa moderna
        $this->SetDrawColor(52, 152, 219);  // Azul vibrante
        $this->SetLineWidth(0.8);
        $this->Line(15, 40, 195, 40);  // Línea de separación entre encabezado y contenido
    }
}

// Función para generar PDF
function generarPDF($resultado, $titulo) {
    // Crear una nueva instancia de la clase personalizada
    $pdf = new MiPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Configuración del PDF
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($titulo);
    $pdf->AddPage();

    // Espacio antes de la tabla (20 unidades para no solaparse)
    $pdf->Ln(40);  // Añadimos espacio para separar la parte superior del contenido de la tabla

    // Estilos CSS para la tabla
    $css = '
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #BDC3C7;
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }
        th {
            background-color: #3498DB;
            color: white;
        }
        td {
            background-color: #ECF0F1;
        }
        h1 {
            font-size: 24px;
            color: #2C3E50;
            text-align: center;
            margin-top: 20px;
        }
        p {
            font-size: 14px;
            color: #7F8C8D;
            text-align: center;
        }
    ';

    // Comprobar si hay resultados
    if (mysqli_num_rows($resultado) > 0) {
        // Generar el contenido HTML para el PDF
        $html = "<h1>$titulo</h1>";
        $html .= "<style>$css</style>";
        $html .= "<table>";
        $html .= "<thead>";
        $html .= "<tr>";

        // Generar encabezados de tabla
        $fields = mysqli_fetch_fields($resultado);
        foreach ($fields as $campo) {
            $html .= "<th>" . ucfirst($campo->name) . "</th>";
        }
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";

        // Generar filas de la tabla
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $html .= "<tr>";
            foreach ($fila as $valor) {
                $html .= "<td>" . htmlspecialchars($valor) . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody>";
        $html .= "</table>";

        // Escribir el contenido HTML en el PDF
        $pdf->writeHTML($html, true, false, true, false, '');
    } else {
        // Manejar conjunto de resultados vacío
        $html = "<h1>$titulo</h1>";
        $html .= "<p>No se encontraron registros.</p>";
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    // Generar el PDF
    $pdf->Output($titulo . '.pdf', 'I');
}


// Incluir el autoload de Composer
require '../library/excel/vendor/autoload.php';  // Verificar que esta ruta sea correcta

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

function generarExcel($resultado, $titulo) {
    // Crear una nueva instancia de Spreadsheet
    $spreadsheet = new Spreadsheet();
    $hoja = $spreadsheet->getActiveSheet();
    
    // Asegurarse de que el título no sea mayor a 31 caracteres
    $hoja->setTitle(substr($titulo, 0, 31));

    // Generar encabezados de columnas
    $columna = 1;  // Empezamos desde la columna 1 (A)
    $fields = mysqli_fetch_fields($resultado);
    foreach ($fields as $campo) {
        // Convertir la columna a letra con stringFromColumnIndex
        $celda = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna) . '1';  // Fila 1 para los encabezados
        $hoja->setCellValue($celda, ucfirst($campo->name));
        
        // Establecer negrita para los encabezados
        $hoja->getStyle($celda)->getFont()->setBold(true);

        // Establecer borde alrededor del encabezado
        $hoja->getStyle($celda)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Ajustar el ancho de la columna al contenido
        $hoja->getColumnDimension(PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna))->setAutoSize(true);

        $columna++;
    }

    // Generar los datos de las filas
    $fila = 2;  // Empezamos desde la fila 2
    while ($datos = mysqli_fetch_assoc($resultado)) {
        $columna = 1;  // Empezamos desde la columna 1 (A)
        foreach ($datos as $valor) {
            // Convertir la columna a letra con stringFromColumnIndex y asignar el valor
            $celda = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna) . $fila;
            $hoja->setCellValue($celda, $valor);

            // Establecer borde alrededor de las celdas de datos
            $hoja->getStyle($celda)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Ajustar el ancho de la columna al contenido
            $hoja->getColumnDimension(PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columna))->setAutoSize(true);

            $columna++;
        }
        $fila++;
    }

    // Configurar las cabeceras para descargar el archivo Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $titulo . '.xlsx"');
    header('Cache-Control: max-age=0');

    // Crear el escritor y enviar el archivo al navegador
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}


// Ejemplo de uso
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $modulo = $_POST['modulo'];
    $reporte = $_POST['reporte'];
    $formato = $_POST['formato'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    generarReporte($modulo, $reporte, $fecha_inicio, $fecha_fin, $formato);
}
?>
<?php
require_once('../library/tcpdf.php');
require_once "../conexion.php";

if (isset($_GET['id_sesion'])) {
    $id_sesion = intval($_GET['id_sesion']);

    // Consulta para obtener el total vendido en la sesión
    $query_total_vendido = "
        SELECT 
            COALESCE(SUM(p.total), 0) AS total_vendido
        FROM caja_sesion cs
        INNER JOIN pago pa ON pa.id_sesion = cs.id
        INNER JOIN pedidos p ON pa.id_pedido = p.id
        INNER JOIN caja c ON c.id = cs.id_caja
        WHERE cs.id = ?
    ";
    $stmt_total_vendido = $conexion->prepare($query_total_vendido);
    $stmt_total_vendido->bind_param("i", $id_sesion);
    $stmt_total_vendido->execute();
    $result_total_vendido = $stmt_total_vendido->get_result();
    $total_vendido_row = $result_total_vendido->fetch_assoc();
    $total_vendido = $total_vendido_row['total_vendido'] ?? 0;

    // Consulta para obtener los detalles de la sesión
    $query_detalle_sesion = "
        SELECT 
            c.nombre AS caja,
            pa.id_pedido,
            pa.medio_pago,
            pa.num_operacion,
            COALESCE(p.total, 0) AS total,
            cs.turno,
            cs.fecha_apertura,
            cs.monto_apertura
        FROM caja_sesion cs
        INNER JOIN caja c ON cs.id_caja = c.id
        LEFT JOIN pago pa ON pa.id_sesion = cs.id
        LEFT JOIN pedidos p ON pa.id_pedido = p.id
        WHERE cs.id = ?
    ";

    // Consulta para obtener el total ajustado
    $query_total_ajustado = "
        SELECT 
            COALESCE(SUM(ac.monto), 0) AS total_ajustado
        FROM caja_sesion cs
        INNER JOIN ajustes_caja ac ON ac.id_sesion = cs.id
        WHERE cs.id = ?
    ";

    // Consulta para obtener los detalles de los ajustes
    $query_detalle_ajustes = "
        SELECT 
            ac.tipo,
            ac.monto,
            ac.descripcion
        FROM caja_sesion cs
        INNER JOIN ajustes_caja ac ON ac.id_sesion = cs.id
        WHERE cs.id = ?
    ";

    // Ejecutar las consultas
    $stmt_detalle_sesion = $conexion->prepare($query_detalle_sesion);
    $stmt_detalle_sesion->bind_param("i", $id_sesion);
    $stmt_detalle_sesion->execute();
    $result_detalle_sesion = $stmt_detalle_sesion->get_result();

    $stmt_total_ajustado = $conexion->prepare($query_total_ajustado);
    $stmt_total_ajustado->bind_param("i", $id_sesion);
    $stmt_total_ajustado->execute();
    $result_total_ajustado = $stmt_total_ajustado->get_result();
    $total_ajustado_row = $result_total_ajustado->fetch_assoc();
    $total_ajustado = $total_ajustado_row['total_ajustado'] ?? 0;

    $stmt_detalle_ajustes = $conexion->prepare($query_detalle_ajustes);
    $stmt_detalle_ajustes->bind_param("i", $id_sesion);
    $stmt_detalle_ajustes->execute();
    $result_detalle_ajustes = $stmt_detalle_ajustes->get_result();

    if ($result_detalle_sesion->num_rows > 0) {
        $data = $result_detalle_sesion->fetch_all(MYSQLI_ASSOC);

        // Calcular Total Cierre
        $total_cierre = $data[0]['monto_apertura'] + $total_vendido - $total_ajustado;

        // Crear el PDF
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sistema de Caja');
        $pdf->SetTitle('Reporte de Sesión de Caja');
        $pdf->SetSubject('Reporte Detallado');

        // Configuración de la página
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        // Título del reporte
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, "Reporte de Sesion de Caja: {$data[0]['caja']}", 0, 1, 'C');
        $pdf->Ln(5);

        // Información general de la sesión
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(50, 10, "Turno: {$data[0]['turno']}", 0, 1);
        $pdf->Cell(50, 10, "Fecha Apertura: {$data[0]['fecha_apertura']}", 0, 1);
        $pdf->Cell(50, 10, "Monto Apertura: S/ " . number_format($data[0]['monto_apertura'], 2), 0, 1);
        $pdf->Ln(5);

        // Monto total de ventas de la sesión
        $pdf->Cell(50, 10, "Total de Ventas: S/ " . number_format($total_vendido, 2), 0, 1);
        $pdf->Ln(5);

        // Tabla de detalle de operaciones
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(30, 10, 'Pedido', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Medio de Pago', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Nro. Operación', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Total (S/)', 1, 0, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('helvetica', '', 10);
        foreach ($data as $row) {
            $pdf->Cell(30, 10, $row['id_pedido'], 1, 0, 'C');
            $pdf->Cell(40, 10, $row['medio_pago'], 1, 0, 'C');
            $pdf->Cell(50, 10, $row['num_operacion'], 1, 0, 'C');
            $pdf->Cell(30, 10, number_format($row['total'], 2), 1, 1, 'C');
        }

        // Mostrar Totales
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Ln(5);
        $pdf->Cell(50, 10, "Total Ajustado: S/ " . number_format($total_ajustado, 2), 0, 1);


        // Mostrar detalles de ajustes si existen
        if ($total_ajustado != 0) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Ln(5);

            // Encabezado de la tabla de ajustes
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(40, 10, 'Tipo', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Monto (S/)', 1, 0, 'C');
            $pdf->Cell(0, 10, 'Descripción', 1, 1, 'C');

            // Detalles de los ajustes
            $pdf->SetFont('helvetica', '', 10);
            foreach ($result_detalle_ajustes as $ajuste) {
                $monto_ajuste = $ajuste['monto'] < 0 ? "S/ " . number_format($ajuste['monto'], 2) : "S/ -" . number_format($ajuste['monto'], 2);
                $pdf->Cell(40, 10, $ajuste['tipo'], 1, 0, 'C');
                $pdf->Cell(40, 10, $monto_ajuste, 1, 0, 'C');
                $pdf->Cell(0, 10, $ajuste['descripcion'], 1, 1);
            }
        }

        // Mostrar Totales
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 10, "Total Cierre: S/ " . number_format($total_cierre, 2), 0, 1);

        // Salida del PDF
        $pdf->Output('reporte_sesion.pdf', 'I');
    } else {
        echo "No se encontraron datos para esta sesión.";
    }
} else {
    echo "ID de sesión no válido.";
}
?>

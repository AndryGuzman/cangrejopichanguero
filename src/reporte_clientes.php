<?php
// Incluir conexión y TCPDF
require_once '../conexion.php';
require_once '../library/tcpdf.php';
require_once 'cliente_service.php';

// Crear una instancia de TCPDF
$pdf = new TCPDF();

// Configuración inicial del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tu Empresa');
$pdf->SetTitle('Reporte de Clientes');
$pdf->SetSubject('Reporte de Clientes');
$pdf->SetKeywords('TCPDF, PDF, Reporte, Clientes');

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Añadir una página
$pdf->AddPage();

// Título del reporte
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Reporte de Clientes', 0, 1, 'C');
$pdf->Ln(5);

// Configurar tabla
$pdf->SetFont('helvetica', '', 10);
$html = '
<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>#</th>
            <th>DNI</th>
            <th>Nombres</th>
            <th>Apellidos</th>
            <th>Dirección</th>
            <th>Celular</th>
            <th>Sexo</th>
            <th>Fecha de Nacimiento</th>
        </tr>
    </thead>
    <tbody>';

// Obtener clientes
$clientes = obtenerClientes($conexion);
$contador = 1; // Inicializar el contador

foreach ($clientes as $cliente) {
    $html .= '<tr>
        <td>' . $contador++ . '</td> <!-- Número de lista -->
        <td>' . htmlspecialchars($cliente['documento']) . '</td>
        <td>' . htmlspecialchars($cliente['nombres']) . '</td>
        <td>' . htmlspecialchars($cliente['apellidos']) . '</td>
        <td>' . htmlspecialchars($cliente['direccion']) . '</td>
        <td>' . htmlspecialchars($cliente['celular']) . '</td>
        <td>' . htmlspecialchars($cliente['sexo']) . '</td>
        <td>' . htmlspecialchars($cliente['fecha_nacimiento']) . '</td>
    </tr>';
}

$html .= '
    </tbody>
</table>';

// Escribir contenido HTML al PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Salida del PDF al navegador
$pdf->Output('reporte_clientes.pdf', 'I');

// Cerrar conexión
mysqli_close($conexion);
?>

<?php
include "../conexion.php";
require_once('../library/tcpdf.php');
include "productos_service.php";

class MYPDF extends TCPDF {
    public function Header() {
        // Logo - Reducido de tamaño y ajustado
        $image_file = '../assets/img/logo.jpg';
        $this->Image($image_file, 15, 15, 25, 25, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        // Título y dirección de la cevichería
        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor(39, 55, 70);  // Azul oscuro elegante
        $this->SetXY(50, 20);
        $this->Cell(0, 10, 'Cangrejo Pichanguero', 0, 1, 'L');
        
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(90, 90, 90);
        $this->SetX(50);
        $this->Cell(0, 5, 'Morona 1150 - Sabor Auténtico', 0, 1, 'L');
        
        // Línea decorativa moderna
        $this->SetDrawColor(52, 152, 219);  // Azul vibrante
        $this->SetLineWidth(0.5);
        $this->Line(15, 40, 195, 40);
        
        // Mover cursor después del encabezado
        $this->SetY(45);
    }

    // Pie de página con estilo
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Cangrejo Pichanguero - Reservados todos los derechos', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Crear el documento PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración de márgenes
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Configuración de salto de página automático
$pdf->SetAutoPageBreak(TRUE, 20);

// Añadir primera página
$pdf->AddPage();

// Título de la carta con estilo
$pdf->SetFont('helvetica', 'B', 22);
$pdf->SetTextColor(52, 152, 219);  // Azul vibrante
$pdf->Cell(0, 10, 'Carta de Platos', 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);

// Salto de línea
$pdf->Ln(10);

// Obtener las categorías
$categorias = obtenerCategorias($conexion);

foreach ($categorias as $categoria) {
    // Obtener los productos de la categoría actual
    $productos = obtenerProductos($conexion, ['categoria' => $categoria['nombre']]);

    // Verificar espacio disponible para la categoría
    $availableSpace = $pdf->getPageHeight() - $pdf->GetY() - $pdf->getFooterMargin();
    if ($availableSpace < 50) {  // Espacio mínimo para categoría
        $pdf->AddPage();
    }

    // Categoría con diseño mejorado
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetFillColor(241, 243, 245);  // Gris claro
    $pdf->SetTextColor(39, 55, 70);  // Azul oscuro
    $pdf->Cell(0, 10, $categoria['nombre'], 0, 1, 'L', 1);
    $pdf->SetFont('helvetica', '', 12);

    // Línea de separación después del título de la categoría
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line($pdf->GetX(), $pdf->GetY() + 5, $pdf->GetX() + 180, $pdf->GetY() + 5);
    $pdf->Ln(7);

    while ($producto = mysqli_fetch_assoc($productos)) {
        // Calcular espacio necesario para el producto
        $requiredSpace = 50;  // Espacio estimado para imagen y texto
        $availableSpace = $pdf->getPageHeight() - $pdf->GetY() - $pdf->getFooterMargin();

        // Si no hay suficiente espacio, agregar nueva página
        if ($availableSpace < $requiredSpace) {
            $pdf->AddPage();
        }

        // Guardar la posición inicial Y
        $initialY = $pdf->GetY();
        
        // Imagen del producto
        $imagen = !empty($producto['imagen']) ? $producto['imagen'] : '../assets/img/default_producto.jpg';
        
        // Imagen a la izquierda con borde suave
        $pdf->Image($imagen, 15, $initialY, 40, 40, '', '', 'T', false, 300);
        
        // Posicionar texto más a la derecha de la imagen
        $pdf->SetXY(65, $initialY);
        
        // Nombre del producto
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(52, 152, 219);  // Azul vibrante
        $pdf->Cell(120, 7, $producto['nombre'], 0, 1, 'L');
        
        // Precio del producto
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(231, 76, 60);  // Rojo suave
        $pdf->SetX(65);
        $pdf->Cell(120, 7, 'S/. ' . number_format($producto['precio'], 2), 0, 1, 'L');
        
        // Descripción
        if (!empty($producto['descripcion'])) {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetX(65);
            
            $pdf->MultiCell(120, 7, $producto['descripcion'], 0, 'L');
        }
        
        // Mover el cursor Y al final del contenido
        $finalY = $pdf->GetY();
        $pdf->SetY(max($finalY, $initialY + 40) + 5);
        
        // Línea de separación más sutil
        $pdf->SetDrawColor(230, 230, 230);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        
        $pdf->Ln(5);
    }

    // Salto adicional entre categorías
    $pdf->Ln(5);
}

// Generar el PDF
$pdf->Output('carta_platos.pdf', 'I');
?>
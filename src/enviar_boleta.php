<?php
// Configuración de la URL de autenticación y credenciales
$loginUrl = "https://facturacion.apisperu.com/api/v1/auth/login";
$username = "Andry";
$password = "Andryc102003dic";

// Paso 1: Autenticación
$loginData = json_encode([
    "username" => $username,
    "password" => $password
]);

// Inicializa cURL para autenticación
$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);

// Ejecuta la solicitud de autenticación
$loginResponse = curl_exec($ch);

if (curl_errno($ch)) {
    die('Error de autenticación: ' . curl_error($ch));
}

$loginResponseData = json_decode($loginResponse, true);
curl_close($ch);

// Verifica si se obtuvo el token
if (isset($loginResponseData['token'])) {
    $token = $loginResponseData['token'];
    echo "Token obtenido: $token <br>";
} else {
    die("Error en la autenticación: No se pudo obtener el token.");
}

// Paso 2: Enviar la factura
$invoiceUrl = "https://facturacion.apisperu.com/api/v1/invoice/send";
$invoiceData = [
    "ublVersion" => "2.1",
    "tipoOperacion" => "0101",
    "tipoDoc" => "03",
    "serie" => "B001",
    "correlativo" => "1",
    "fechaEmision" => "2021-01-27T00:00:00-05:00",
    "formaPago" => [
        "moneda" => "PEN",
        "tipo" => "Contado"
    ],
    "tipoMoneda" => "PEN",
    "client" => [
        "tipoDoc" => "6",
        "numDoc" => 20000000002,
        "rznSocial" => "Cliente",
        "address" => [
            "direccion" => "Direccion cliente",
            "provincia" => "LIMA",
            "departamento" => "LIMA",
            "distrito" => "LIMA",
            "ubigueo" => "150101"
        ]
    ],
    "company" => [
        "ruc" => 21111111118,
        "razonSocial" => "Mi empresa",
        "nombreComercial" => "Mi empresa",
        "address" => [
            "direccion" => "Direccion empresa",
            "provincia" => "LIMA",
            "departamento" => "LIMA",
            "distrito" => "LIMA",
            "ubigueo" => "150101"
        ]
    ],
    "mtoOperGravadas" => 100,
    "mtoIGV" => 18,
    "valorVenta" => 100,
    "totalImpuestos" => 18,
    "subTotal" => 118,
    "mtoImpVenta" => 118,
    "details" => [
        [
            "codProducto" => "P001",
            "unidad" => "NIU",
            "descripcion" => "PRODUCTO 1",
            "cantidad" => 2,
            "mtoValorUnitario" => 50,
            "mtoValorVenta" => 100,
            "mtoBaseIgv" => 100,
            "porcentajeIgv" => 18,
            "igv" => 18,
            "tipAfeIgv" => 10,
            "totalImpuestos" => 18,
            "mtoPrecioUnitario" => 59
        ]
    ],
    "legends" => [
        [
            "code" => "1000",
            "value" => "SON CIENTO DIECIOCHO CON 00/100 SOLES"
        ]
    ]
];

// Convierte los datos de la factura a JSON
$jsonInvoiceData = json_encode($invoiceData);

// Inicializa cURL para enviar la factura
$ch = curl_init($invoiceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonInvoiceData);

// Ejecuta la solicitud para enviar la factura
$invoiceResponse = curl_exec($ch);

if (curl_errno($ch)) {
    die('Error al enviar la factura: ' . curl_error($ch));
}

$invoiceResponseData = json_decode($invoiceResponse, true);
curl_close($ch);

// Muestra el resultado de la solicitud de facturación
if (isset($invoiceResponseData['success']) && $invoiceResponseData['success']) {
    echo "Factura enviada correctamente. ID: " . $invoiceResponseData['data']['id'] . "<br>";

    // Paso 3: Obtener el PDF de la factura
    $pdfUrl = "https://facturacion.apisperu.com/api/v1/invoice/pdf";
    $pdfData = [
        "ublVersion" => "2.1",
        "tipoOperacion" => "0101",
        "tipoDoc" => "03",
        "serie" => "B001",
        "correlativo" => "1",
        "fechaEmision" => "2021-01-27T00:00:00-05:00",
        "formaPago" => [
            "moneda" => "PEN",
            "tipo" => "Contado"
        ],
        "tipoMoneda" => "PEN",
        "client" => [
            "tipoDoc" => "6",
            "numDoc" => 20000000002,
            "rznSocial" => "Cliente",
            "address" => [
                "direccion" => "Direccion cliente",
                "provincia" => "LIMA",
                "departamento" => "LIMA",
                "distrito" => "LIMA",
                "ubigueo" => "150101"
            ]
        ],
        "company" => [
            "ruc" => 21111111118,
            "razonSocial" => "Mi empresa",
            "nombreComercial" => "Mi empresa",
            "address" => [
                "direccion" => "Direccion empresa",
                "provincia" => "LIMA",
                "departamento" => "LIMA",
                "distrito" => "LIMA",
                "ubigueo" => "150101"
            ]
        ],
        "mtoOperGravadas" => 100,
        "mtoIGV" => 18,
        "valorVenta" => 100,
        "totalImpuestos" => 18,
        "subTotal" => 118,
        "mtoImpVenta" => 118,
        "details" => [
            [
                "codProducto" => "P001",
                "unidad" => "NIU",
                "descripcion" => "PRODUCTO 1",
                "cantidad" => 2,
                "mtoValorUnitario" => 50,
                "mtoValorVenta" => 100,
                "mtoBaseIgv" => 100,
                "porcentajeIgv" => 18,
                "igv" => 18,
                "tipAfeIgv" => 10,
                "totalImpuestos" => 18,
                "mtoPrecioUnitario" => 59
            ]
        ],
        "legends" => [
            [
                "code" => "1000",
                "value" => "SON CIENTO DIECIOCHO CON 00/100 SOLES"
            ]
        ]
    ];

    // Convierte los datos del PDF a JSON
    $jsonPdfData = json_encode($pdfData);

    // Inicializa cURL para obtener el PDF
    $ch = curl_init($pdfUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPdfData);

    // Ejecuta la solicitud para obtener el PDF
    $pdfResponse = curl_exec($ch);

    if (curl_errno($ch)) {
        die('Error al obtener el PDF: ' . curl_error($ch));
    }

    // Verifica si la respuesta es válida
    if ($pdfResponse !== false) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="factura.pdf"');
        echo $pdfResponse;
    } else {
        echo "Error al obtener el PDF.";
    }

    curl_close($ch);

} else {
    echo "Error al enviar la factura: " . $invoiceResponseData['message'];
}
?>

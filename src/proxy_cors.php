<?php
// Permitir solicitudes desde cualquier origen
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Obtener el contenido del archivo JSON desde el CDN
$url = 'https://cdn.datatables.net/plug-ins/1.10.11/i18n/Spanish.json';

// Obtener el contenido del archivo remoto
$json_data = file_get_contents($url);

// Enviar el contenido al navegador
echo $json_data;
?>

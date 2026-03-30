<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI - Migrado a GLPI 11.x por Dawing S.A.S.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Session::checkRight('config', UPDATE);

if (!isset($_GET['file'])) {
   http_response_code(400);
   exit;
}

$requestedFile = $_GET['file'];

// Validación de seguridad: solo permitir acceso a barcode/logo.png
if ($requestedFile !== 'barcode/logo.png') {
   Toolbox::logWarning(
      "[Plugin barcode][security][document.send] Intento de acceso no permitido: " . $requestedFile
   );
   http_response_code(403);
   exit;
}

$filePath = GLPI_PLUGIN_DOC_DIR . '/' . $requestedFile;

if (!file_exists($filePath)) {
   http_response_code(404);
   exit;
}

// Detectar tipo MIME real
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Solo servir imágenes
if (!in_array($mimeType, ['image/png', 'image/jpeg', 'image/gif'])) {
   http_response_code(403);
   exit;
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: max-age=3600, public');
readfile($filePath);

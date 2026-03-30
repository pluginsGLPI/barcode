<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI - Migrado a GLPI 11.x por Dawing S.A.S.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Session::checkRight('plugin_barcode_barcode', CREATE);

if (!isset($_GET['file'])) {
   Html::displayErrorAndDie(__('No file specified.', 'barcode'), true);
}

$docDir  = GLPI_PLUGIN_DOC_DIR . '/barcode';
$filename = $_GET['file'];

// Validación de seguridad: evitar path traversal
if (strpos($filename, '../') !== false || strpos($filename, '..\\') !== false) {
   Toolbox::logWarning(
      "[Plugin barcode][security][sendfile] " .
      ($_SESSION['glpiname'] ?? 'unknown') .
      " intentó acceder a un archivo fuera del directorio permitido: " . $filename
   );
   Html::displayErrorAndDie(__('Security error.', 'barcode'), true);
}

// Solo permitir archivos PDF (los generados por el plugin)
if (!preg_match('/^[a-zA-Z0-9_\-]+\.(pdf)$/', $filename)) {
   Html::displayErrorAndDie(__('Invalid file name.', 'barcode'), true);
}

$file = $docDir . '/' . $filename;

if (!file_exists($file)) {
   Html::displayErrorAndDie(sprintf(__('File %s does not exist.', 'barcode'), $filename), true);
}

// Enviar el archivo PDF al navegador
header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
header('Pragma: private');
header('Cache-control: private, must-revalidate');
header('Content-disposition: attachment; filename="' . $filename . '"');
header('Content-type: application/pdf');
header('Content-Length: ' . filesize($file));

$f = fopen($file, 'r');
if (!$f) {
   Html::displayErrorAndDie(__('Error opening file.', 'barcode'), true);
}

$fsize = filesize($file);
if ($fsize > 0) {
   echo fread($f, $fsize);
}
fclose($f);

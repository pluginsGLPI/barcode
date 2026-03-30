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

// Valores por defecto para los parámetros opcionales del formulario
$_POST['length']  = $_POST['length']  ?? '';
$_POST['prefixe'] = $_POST['prefixe'] ?? '';
$_POST['size']    = $_POST['size']    ?? '';
$_POST['format']  = $_POST['format']  ?? '';

if (isset($_POST['generate'])) {
   $barcode  = new PluginBarcodeBarcode();
   $file     = $barcode->printPDF($_POST);
   $filePath = explode('/', $file);
   $filename = end($filePath);

   $msg = "<a href='/plugins/barcode/front/send.php?file=" . urlencode($filename) . "'>"
        . __('Generated file', 'barcode') . "</a>";

   Session::addMessageAfterRedirect($msg);
}

Html::back();

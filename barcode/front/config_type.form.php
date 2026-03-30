<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI - Migrado a GLPI 11.x por Dawing S.A.S.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Session::checkRight('plugin_barcode_config', UPDATE);

if (isset($_POST['type'])) {

   $pbConfig = new PluginBarcodeConfig();
   $pbcconf  = new PluginBarcodeConfig_Type();

   // Buscar el registro existente del tipo
   $results = $pbcconf->find(['type' => $_POST['type']]);

   $data = [
      'type'             => $_POST['type'],
      'size'             => $_POST['size']             ?? 'A4',
      'orientation'      => $_POST['orientation']      ?? 'Portrait',
      'marginTop'        => (int)($_POST['marginTop']        ?? 30),
      'marginBottom'     => (int)($_POST['marginBottom']     ?? 30),
      'marginLeft'       => (int)($_POST['marginLeft']       ?? 30),
      'marginRight'      => (int)($_POST['marginRight']      ?? 30),
      'marginHorizontal' => (int)($_POST['marginHorizontal'] ?? 25),
      'marginVertical'   => (int)($_POST['marginVertical']   ?? 30),
      'maxCodeWidth'     => (int)($_POST['maxCodeWidth']     ?? 110),
      'maxCodeHeight'    => (int)($_POST['maxCodeHeight']    ?? 70),
      'txtSize'          => (int)($_POST['txtSize']          ?? 8),
      'txtSpacing'       => (int)($_POST['txtSpacing']       ?? 3),
   ];

   if (!empty($results)) {
      $id          = array_key_first($results);
      $data['id']  = $id;
      $pbcconf->update($data);
   } else {
      $pbcconf->add($data);
   }

   Session::addMessageAfterRedirect(__('Configuration saved.', 'barcode'));
}

Html::back();

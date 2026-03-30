<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI - Migrado a GLPI 11.x por Dawing S.A.S.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

// Verificar que el usuario tiene sesión válida
Session::checkLoginUser();

if (!isset($_GET['inventoryNumber']) || !isset($_GET['itemtype'])) {
   Html::displayErrorAndDie(__('Missing parameters.', 'barcode'), true);
}

$inventoryNumber = $_GET['inventoryNumber'];
$itemtype        = $_GET['itemtype'];

// Validar que el itemtype es una clase GLPI válida
if (!class_exists($itemtype) || !is_subclass_of($itemtype, CommonDBTM::class)) {
   Html::displayErrorAndDie(__('Invalid item type.', 'barcode'), true);
}

$item = new $itemtype();

if ($item->getFromDBByCrit(['otherserial' => $inventoryNumber])) {
   Html::redirect($item->getFormURLWithID($item->getID()));
} else {
   Html::displayErrorAndDie(
      sprintf(__('No item found with inventory number: %s', 'barcode'), htmlspecialchars($inventoryNumber)),
      true
   );
}

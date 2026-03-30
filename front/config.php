<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI - Migrado a GLPI 11.x por Dawing S.A.S.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

// Bootstrap de GLPI 11 - la raíz web es public/, pero include sigue siendo desde la raíz del proyecto
include('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

// Cargar el plugin aunque no esté activado (para acceso desde Setup > Plugins)
Plugin::load('barcode');

Html::header(__('Barcode', 'barcode'), $_SERVER['PHP_SELF'], "config", "plugins");

$pbConfig = new PluginBarcodeConfig();
$pbConfig->showForm('');

Html::footer();

<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI
   Migrado a GLPI 11.x por Dawing S.A.S.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Clase de configuración por tipo de código de barras
 */
class PluginBarcodeConfig_Type extends CommonDBTM {

   static $rightname = 'plugin_barcode_config';

   /**
    * Nombre del tipo de item
    */
   static function getTypeName($nb = 0) {
      return __('Barcode configuration type', 'barcode');
   }
}

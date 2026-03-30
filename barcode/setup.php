<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI
   Migrado a GLPI 11.x por Dawing S.A.S.
   Original: Copyright (C) 2009-2016 by the Barcode plugin Development Team.
   ------------------------------------------------------------------------

   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

define("PLUGIN_BARCODE_VERSION", "3.0.0");

// Versión mínima de GLPI requerida (inclusive)
define('PLUGIN_BARCODE_MIN_GLPI', '11.0.0');
// Versión máxima de GLPI soportada (exclusiva)
define('PLUGIN_BARCODE_MAX_GLPI', '11.0.99');

/**
 * Inicialización de los hooks del plugin - Requerido
 */
function plugin_init_barcode() {
   global $PLUGIN_HOOKS;

   // Cargar el autoloader de Composer
   if (file_exists(__DIR__ . '/vendor/autoload.php')) {
      require_once(__DIR__ . '/vendor/autoload.php');
   }

   // NOTA: csrf_compliant fue deprecado en GLPI 11 - eliminado intencionalmente

   Plugin::registerClass('PluginBarcodeDropdown');
   Plugin::registerClass('PluginBarcodeProfile', ['addtabon' => ['Profile']]);
   Plugin::registerClass('PluginBarcodeBarcode');

   if (Session::haveRight('plugin_barcode_barcode', CREATE)
           || Session::haveRight('plugin_barcode_config', UPDATE)) {

      $PLUGIN_HOOKS['pre_item_purge']['barcode']
         = ['Profile' => ['PluginBarcodeProfile', 'cleanProfiles']];

      // Acción masiva habilitada
      $PLUGIN_HOOKS['use_massive_action']['barcode'] = 1;

      // Ruta base del plugin (sin Plugin::getWebDir - deprecado en GLPI 11)
      $web_dir = '/plugins/barcode';

      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['title']  = __('Search');
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['page']   = $web_dir . '/front/barcode.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['search'] = $web_dir . '/front/barcode.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['add']    = $web_dir . '/front/barcode.form.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['config'] = $web_dir . '/index.php';

      $PLUGIN_HOOKS['helpdesk_menu_entry']['barcode'] = true;
   }

   // Página de configuración
   if (Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['config_page']['barcode'] = 'front/config.php';
   }

   // Redirección por ID: /index.php?redirect=plugin_barcode_2
   $PLUGIN_HOOKS['redirect_page']['barcode'] = 'barcode.form.php';
}

/**
 * Versión e información del plugin - Requerido
 */
function plugin_version_barcode() {
   return [
      'name'           => 'Barcode',
      'shortname'      => 'barcode',
      'version'        => PLUGIN_BARCODE_VERSION,
      'license'        => 'AGPLv3+',
      'author'         => '<a href="mailto:d.durieux@siprossii.com">David DURIEUX</a> &amp;
                           Jean Marc GRISARD &amp; Vincent MAZZONI
                           (Migrado a GLPI 11 por Dawing S.A.S.)',
      'homepage'       => 'https://github.com/pluginsGLPI/barcode',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_BARCODE_MIN_GLPI,
            'max' => PLUGIN_BARCODE_MAX_GLPI,
         ],
         'php'  => [
            'min' => '8.1',
         ],
      ]
   ];
}

/**
 * Verificación de prerequisitos antes de instalar
 */
function plugin_barcode_check_prerequisites() {
   if (version_compare(GLPI_VERSION, PLUGIN_BARCODE_MIN_GLPI, 'lt')
       || version_compare(GLPI_VERSION, PLUGIN_BARCODE_MAX_GLPI, 'gt')) {
      if (method_exists('Plugin', 'messageIncompatible')) {
         Plugin::messageIncompatible('core', PLUGIN_BARCODE_MIN_GLPI, PLUGIN_BARCODE_MAX_GLPI);
      }
      return false;
   }
   return true;
}

/**
 * Verificación de configuración
 */
function plugin_barcode_check_config($verbose = false) {
   return true;
}

<?php

/*
   ------------------------------------------------------------------------
   Barcode
   Copyright (C) 2009-2026 by the Barcode plugin Development Team.

   https://github.com/ISPF/info-glpi-barcode-ispf
   ------------------------------------------------------------------------

   LICENSE

   This file is part of barcode plugin project.

   Plugin Barcode is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Barcode is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Plugin Barcode. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Barcode
   @author    David Durieux
   @co-author
   @copyright Copyright (c) 2009-2026 Barcode plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/ISPF/info-glpi-barcode-ispf
   @since     2009

   ------------------------------------------------------------------------
 */

define("PLUGIN_BARCODE_VERSION", "3.1.0");

// Minimal GLPI version, inclusive
define('PLUGIN_BARCODE_MIN_GLPI', '11.0.0');
// Maximum GLPI version, exclusive
define('PLUGIN_BARCODE_MAX_GLPI', '12.99');

// Init the hooks of the plugins -Needed
function plugin_init_barcode() {
   global $PLUGIN_HOOKS;

   require_once(__DIR__ . '/vendor/autoload.php');

   $PLUGIN_HOOKS['csrf_compliant']['barcode'] = true;

   Plugin::registerClass('PluginBarcodeProfile', ['addtabon' => ['Profile']]);
   Plugin::registerClass('PluginBarcodeBarcode');
   Plugin::registerClass('PluginBarcodeQRcode');

   // Generation is also available in the simplified interface (helpdesk menu entry)
   if (!in_array('plugin_barcode_barcode', Profile::$helpdesk_rights, true)) {
      Profile::$helpdesk_rights[] = 'plugin_barcode_barcode';
   }

   // Generation: massive actions and menu entries
   if (Session::haveRight('plugin_barcode_barcode', CREATE)) {
      // Massive Action definition
      $PLUGIN_HOOKS['use_massive_action']['barcode'] = 1;

      // Menu entry
      $PLUGIN_HOOKS['menu_toadd']['barcode'] = ['tools' => 'PluginBarcodeBarcode'];

      $PLUGIN_HOOKS['helpdesk_menu_entry']['barcode']      = '/front/barcode.form.php';
      $PLUGIN_HOOKS['helpdesk_menu_entry_icon']['barcode'] = 'ti ti-barcode';
   }

   // Config page
   if (Session::haveRight('plugin_barcode_config', UPDATE)) {
      $PLUGIN_HOOKS['config_page']['barcode'] = 'front/config.php';
   }
}

function plugin_version_barcode() {

   return [
      'name'           => 'Barcode',
      'shortname'      => 'barcode',
      'version'        => PLUGIN_BARCODE_VERSION,
      'license'        => 'AGPLv3+',
      'author'         => 'David DURIEUX, Jean Marc GRISARD, Vincent MAZZONI (GLPI 12 Update by Tamatoa)',
      'homepage'       => 'https://github.com/ISPF/info-glpi-barcode-ispf',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_BARCODE_MIN_GLPI,
            'max' => PLUGIN_BARCODE_MAX_GLPI,
         ]
      ]
   ];
}

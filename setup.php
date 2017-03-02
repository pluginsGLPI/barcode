<?php

/*
   ------------------------------------------------------------------------
   Barcode
   Copyright (C) 2009-2016 by the Barcode plugin Development Team.

   https://forge.indepnet.net/projects/barscode
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
   @copyright Copyright (c) 2009-2016 Barcode plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/barscode
   @since     2009

   ------------------------------------------------------------------------
 */

define ("PLUGIN_BARCODE_VERSION", "0.90+1.0");

// Init the hooks of the plugins -Needed
function plugin_init_barcode() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['barcode'] = true;

   // Params : plugin name - string type - ID - Array of attributes
   Plugin::registerClass('PluginBarcodeDropdown');
   Plugin::registerClass('PluginBarcodeProfile',
              array('addtabon' => array('Profile')));
   Plugin::registerClass('PluginBarcode');

   // Display a menu entry ?

   if (Session::haveRight('plugin_barcode_barcode', CREATE)
           || Session::haveRight('plugin_barcode_config', UPDATE)) {
      $PLUGIN_HOOKS['pre_item_purge']['barcode'] = array('Profile' => array('PluginBarcodeProfile','cleanProfiles'));

      // Massive Action definition
      $PLUGIN_HOOKS['use_massive_action']['barcode'] = 1;

      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['title'] = "Search";
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['page'] = '/plugins/barcode/front/barcode.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['search'] = '/plugins/barcode/front/barcode.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['add'] = '/plugins/barcode/front/barcode.form.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['config'] = '/plugins/barcode/index.php';

      $PLUGIN_HOOKS["helpdesk_menu_entry"]['barcode'] = true;
   }

   // Config page
   if (Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['config_page']['barcode'] = 'front/config.php';
   }

   //redirect appel http://localhost/glpi/index.php?redirect=plugin_example_2 (ID 2 du form)
   $PLUGIN_HOOKS['redirect_page']['barcode'] = 'barcode.form.php';
}


// Get the name and the version of the plugin - Needed
function plugin_version_barcode() {
   return array('name'           => 'Barcode',
                'shortname'      => 'barcode',
                'version'        => PLUGIN_BARCODE_VERSION,
                'license'        => 'AGPLv3+',
                'author'         => '<a href="mailto:d.durieux@siprossii.com">David DURIEUX</a> &
                   Jean Marc GRISARD & Vincent MAZZONI',
                'homepage'       => 'https://forge.indepnet.net/projects/barscode',
                'minGlpiVersion' => '0.85');// For compatibility / no install in version < minGlpiVersion
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_barcode_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '0.85', 'lt') || version_compare(GLPI_VERSION, '0.91', 'ge')) {
      echo __('GLPI version not compatible need 0.85.x or 0.90.x', 'barcode');
      return false;
   }
   return true;
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_barcode_check_config($verbose=false) {
   if (true) { // Your configuration check
      return true;
   }
   return false;
}



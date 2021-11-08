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

define ("PLUGIN_BARCODE_VERSION", "2.6.1");

// Minimal GLPI version, inclusive
define('PLUGIN_BARCODE_MIN_GLPI', '9.5');
// Maximum GLPI version, exclusive
define('PLUGIN_BARCODE_MAX_GLPI', '9.6');

// Init the hooks of the plugins -Needed
function plugin_init_barcode() {
   global $PLUGIN_HOOKS;

   require_once(__DIR__ . '/vendor/autoload.php');

   $PLUGIN_HOOKS['csrf_compliant']['barcode'] = true;

   Plugin::registerClass('PluginBarcodeDropdown');
   Plugin::registerClass('PluginBarcodeProfile', ['addtabon' => ['Profile']]);
   Plugin::registerClass('PluginBarcode');

   // Display a menu entry ?

   if (Session::haveRight('plugin_barcode_barcode', CREATE)
           || Session::haveRight('plugin_barcode_config', UPDATE)) {
      $PLUGIN_HOOKS['pre_item_purge']['barcode']
         = ['Profile' => ['PluginBarcodeProfile','cleanProfiles']];

      // Massive Action definition
      $PLUGIN_HOOKS['use_massive_action']['barcode'] = 1;

      $web_dir = '/' . Plugin::getWebDir('barcode', false);
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['title'] = "Search";
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['page'] = $web_dir . '/front/barcode.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['search'] = $web_dir. '/front/barcode.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['add'] = $web_dir. '/front/barcode.form.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['config'] = $web_dir. '/index.php';

      $PLUGIN_HOOKS["helpdesk_menu_entry"]['barcode'] = true;
   }

   // Config page
   if (Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['config_page']['barcode'] = 'front/config.php';
   }

   //Redirect code
   //http://localhost/glpi/index.php?redirect=plugin_barcode_2 to form ID 2
   $PLUGIN_HOOKS['redirect_page']['barcode'] = 'barcode.form.php';
}

function plugin_version_barcode() {

   return [
      'name'           => 'Barcode',
      'shortname'      => 'barcode',
      'version'        => PLUGIN_BARCODE_VERSION,
      'license'        => 'AGPLv3+',
      'author'         => '<a href="mailto:d.durieux@siprossii.com">David DURIEUX</a> &
                           Jean Marc GRISARD & Vincent MAZZONI',
      'homepage'       => 'https://github.com/pluginsGLPI/barcode',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_BARCODE_MIN_GLPI,
            'max' => PLUGIN_BARCODE_MAX_GLPI,
         ]
      ]
   ];
}

<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

// Init the hooks of the plugins -Needed
function plugin_init_barcode() {
   global $PLUGIN_HOOKS,$LANG,$CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['barcode'] = true;
   
   // Params : plugin name - string type - ID - Array of attributes
   Plugin::registerClass('PluginBarcodeDropdown');

   Plugin::registerClass('PluginBarcodeProfile');

   Plugin::registerClass('PluginBarcode');

   $PLUGIN_HOOKS['change_profile']['barcode'] = array('PluginBarcodeProfile','changeprofile');

   // Display a menu entry ?
   if ((isset($_SESSION["glpi_plugin_barcode_profile"])
           && $_SESSION["glpi_plugin_barcode_profile"]["generate"])
        OR (isset($_SESSION["glpi_plugin_barcode_profile"])
           && $_SESSION["glpi_plugin_barcode_profile"]["config"])) {  // Right set in change_profile hook

      $PLUGIN_HOOKS['pre_item_purge']['barcode'] = array('Profile' => array('PluginBarcodeProfile','cleanProfiles'));

      // Onglets management
      $PLUGIN_HOOKS['headings']['barcode']        = 'plugin_get_headings_barcode';
      $PLUGIN_HOOKS['headings_action']['barcode'] = 'plugin_headings_actions_barcode';

      // Massive Action definition
      $PLUGIN_HOOKS['use_massive_action']['barcode'] = 1;

      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['title'] = "Search";
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['page'] = '/plugins/barcode/front/barcode.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['search'] = '/plugins/barcode/front/barcode.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['add'] = '/plugins/barcode/front/barcode.form.php';
      $PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']['config'] = '/plugins/barcode/index.php';
      //$PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links']["<img  src='".$CFG_GLPI["root_doc"]."/pics/menu_showall.png' title='".$LANG['plugin_barcode']["test"]."' alt='".$LANG['plugin_barcode']["test"]."'>"] = '/plugins/barcode/index.php';
      //$PLUGIN_HOOKS['submenu_entry']['barcode']['options']['optionname']['links'][$LANG['plugin_barcode']["test"]] = '/plugins/barcode/index.php';

      $PLUGIN_HOOKS["helpdesk_menu_entry"]['barcode'] = true;
   }

   // Config page
   if (Session::haveRight('config','w')) {
      $PLUGIN_HOOKS['config_page']['barcode'] = 'front/config.php';
   }

   //redirect appel http://localhost/glpi/index.php?redirect=plugin_example_2 (ID 2 du form)
   $PLUGIN_HOOKS['redirect_page']['barcode'] = 'barcode.form.php';

}


// Get the name and the version of the plugin - Needed
function plugin_version_barcode() {

   return array('name'           => 'Plugin Barcode',
                'shortname'      => 'barcode',
                'version'        => '0.83+1.0',
                'license'        => 'AGPLv3+',
                'author'         => '<a href="<a href="mailto:d.durieux@siprossii.com">David DURIEUX</a> &
                   Jean Marc GRISARD & Vincent MAZZONI',
                'homepage'       => 'https://forge.indepnet.net/projects/show/barscode',
                'minGlpiVersion' => '0.83.3');// For compatibility / no install in version < minGlpiVersion
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_barcode_check_prerequisites() {
   

   if (version_compare(GLPI_VERSION,'0.83.3','lt') || version_compare(GLPI_VERSION,'0.84','ge')) {
      echo "GLPI version not compatible need 0.83.x";
      return false;
   }
   return true;
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_barcode_check_config($verbose=false) {
   global $LANG;

   if (true) { // Your configuration check
      return true;
   }
   if ($verbose) {
      echo $LANG['plugins'][2];
   }
   return false;
}


?>
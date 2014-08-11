<?php

/*
   ------------------------------------------------------------------------
   Barcode
   Copyright (C) 2009-2014 by the Barcode plugin Development Team.

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
   @copyright Copyright (c) 2009-2014 Barcode plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/barscode
   @since     2009

   ------------------------------------------------------------------------
 */

// Define actions :
function plugin_barcode_MassiveActions($type) {

   switch ($type) {
      // New action for core and other plugin types : name = plugin_PLUGINNAME_actionname
      case 'Computer' :
      case 'Monitor' :
		case 'Networking' :
		case 'Printer' :
		case 'Peripheral' :
		case 'Phone' :
         return array("PluginBarcodeBarcode".MassiveAction::CLASS_ACTION_SEPARATOR.'Generate' => __('Barcode', 'barcode')." - ".__('Print barcodes', 'barcode'),
                      "PluginBarcodeQRcode".MassiveAction::CLASS_ACTION_SEPARATOR.'Generate'  => __('Barcode', 'barcode')." - ".__('Print QRcodes', 'barcode'));
         
		case 'Ticket' :
         return array("PluginBarcodeBarcode".MassiveAction::CLASS_ACTION_SEPARATOR.'Generate' => __('Barcode', 'barcode')." - ".__('Print barcodes', 'barcode'));

//      case 'Profile' :
//         return array("plugin_barcode_allow" => __('Barcode', 'barcode'));
   }
   return array();
}



// Install process for plugin : need to return true if succeeded
function plugin_barcode_install() {
   global $DB;

   $migration = new Migration(PLUGIN_BARCODE_VERSION);
   
   if (!file_exists(GLPI_PLUGIN_DOC_DIR."/barcode")) {
      mkdir(GLPI_PLUGIN_DOC_DIR."/barcode");
   }
   $migration->renameTable("glpi_plugin_barcode_config", "glpi_plugin_barcode_configs");
   if (!TableExists("glpi_plugin_barcode_configs")) {
      $query = "CREATE TABLE `glpi_plugin_barcode_configs` (
                  `id` int(11) NOT NULL auto_increment,
                  `type` varchar(20) collate utf8_unicode_ci default NULL,
                  PRIMARY KEY  (`ID`)
               ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->query($query) or die("error creating glpi_plugin_barcode_configs ". $DB->error());

      $query = "INSERT INTO `glpi_plugin_barcode_configs` 
                     (`id`, `type`)
                VALUES
                     ('1', 'code128')";
      $DB->query($query) or die("error populate glpi_plugin_barcode_configs ". $DB->error());
   }

   $migration->renameTable("glpi_plugin_barcode_config_type", "glpi_plugin_barcode_configs_types");
   if (!TableExists("glpi_plugin_barcode_configs_types")) {
      $query = "CREATE TABLE `glpi_plugin_barcode_configs_types` (
                  `id` int(11) NOT NULL auto_increment,
                  `type` varchar(20) collate utf8_unicode_ci default NULL,
                  `size` varchar(20) collate utf8_unicode_ci default NULL,
                  `orientation` varchar(9) collate utf8_unicode_ci default NULL,
                  `marginTop` int(11) NULL,
                  `marginBottom` int(11) NULL,
                  `marginLeft` int(11) NULL,
                  `marginRight` int(11) NULL,
                  `marginHorizontal` int(11) NULL,
                  `marginVertical` int(11) NULL,
                  `maxCodeWidth` int(11) NULL,
                  `maxCodeHeight` int(11) NULL,
                  PRIMARY KEY  (`ID`),
                  UNIQUE  (`type`)
               ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->query($query) or die("error creating glpi_plugin_barcode_configs_types ". $DB->error());

      $query = "INSERT INTO `glpi_plugin_barcode_configs_types`
                     (`type`, `size`, `orientation`,
                     `marginTop`, `marginBottom`, `marginLeft`, `marginRight`,
                     `marginHorizontal`, `marginVertical`, `maxCodeWidth`, `maxCodeHeight`)
                VALUES
                     ('Code39', 'A4', 'Portrait',
                     '30', '30', '30', '30',
                     '25', '30', '128', '50'),
                     ('code128', 'A4', 'Portrait',
                     '30', '30', '30', '30',
                     '25', '30', '110', '70'),
                     ('ean13', 'A4', 'Portrait',
                     '30', '30', '30', '30',
                     '25', '30', '110', '70'),
                     ('int25', 'A4', 'Portrait',
                     '30', '30', '30', '30',
                     '25', '30', '110', '70'),
                     ('postnet', 'A4', 'Portrait',
                     '30', '30', '30', '30',
                     '25', '30', '110', '70'),
                     ('upca', 'A4', 'Portrait',
                     '30', '30', '30', '30',
                     '25', '30', '110', '70'),
                     ('QRcode', 'A4', 'Portrait',
                     '30', '30', '30', '30',
                     '25', '30', '110', '100')";
      $DB->query($query) or die("error populate glpi_plugin_barcode_configs_types ". $DB->error());
   }
   
   if (countElementsInTable("glpi_plugin_barcode_configs_types",
                            "`type`='QRcode'") == 0) {
      $query = "INSERT INTO `glpi_plugin_barcode_configs_types`
                     (`type`, `size`, `orientation`,
                     `marginTop`, `marginBottom`, `marginLeft`, `marginRight`,
                     `marginHorizontal`, `marginVertical`, `maxCodeWidth`, `maxCodeHeight`)
                VALUES
                     ('QRcode', 'A4', 'Portrait',
                     '30', '30', '30', '30',
                     '25', '30', '110', '100')";
      $DB->query($query) or die("error populate glpi_plugin_barcode_configs_types ". $DB->error());
   }

   include_once GLPI_ROOT.'/plugins/barcode/inc/profile.class.php';
   include_once GLPI_ROOT.'/plugins/barcode/inc/config.class.php';
   PluginBarcodeProfile::initProfile();
   if (TableExists("glpi_plugin_barcode_profiles")) {
      $query = "DROP TABLE `glpi_plugin_barcode_profiles`";
      $DB->query($query) or die("error deleting glpi_plugin_barcode_profiles");
   }
   return true;
}



// Uninstall process for plugin : need to return true if succeeded
function plugin_barcode_uninstall() {
   global $DB;

   if (TableExists("glpi_plugin_barcode_configs")) {
      $query = "DROP TABLE `glpi_plugin_barcode_configs`";
      $DB->query($query) or die("error deleting glpi_plugin_barcode_configs");
   }
   if (TableExists("glpi_plugin_barcode_configs_types")) {
      $query = "DROP TABLE `glpi_plugin_barcode_configs_types`";
      $DB->query($query) or die("error deleting glpi_plugin_barcode_configs_types");
   }
   if (TableExists("glpi_plugin_barcode_profiles")) {
      $query = "DROP TABLE `glpi_plugin_barcode_profiles`";
      $DB->query($query) or die("error deleting glpi_plugin_barcode_profiles");
   }
   
   include_once GLPI_ROOT.'/plugins/barcode/inc/profile.class.php';
   PluginBarcodeProfile::removeRights();

   return true;
}
?>
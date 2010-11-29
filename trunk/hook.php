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
// Original Author of file: Vincent Mazzoni
// Purpose of file:
// ----------------------------------------------------------------------

//////////////////////////////
////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

// Define actions :
function plugin_barcode_MassiveActions($type) {
   global $LANG;

   switch ($type) {
      // New action for core and other plugin types : name = plugin_PLUGINNAME_actionname
      case 'Computer' :
      case 'Monitor' :
		case 'Networking' :
		case 'Printer' :
		case 'Peripheral' :
         return array("plugin_barcode_barcode" => $LANG['plugin_barcode']["title"][4]);
   }
   return array();
}


// How to display specific actions ?
// options contain at least itemtype and and action
function plugin_barcode_MassiveActionsDisplay($options=array()) {
   global $LANG;

   switch ($options['itemtype']) {
      case 'Computer' :
      case 'Monitor' :
		case 'Networking' :
		case 'Printer' :
		case 'Peripheral' :
         switch ($options['action']) {
            case "plugin_barcode_barcode" :
               $barcode = new PluginBarcodeBarcode();
               $barcode->showFormMassiveAction();
            break;
         }
         break;
   }
   return "";
}


// How to process specific actions ?
function plugin_barcode_MassiveActionsProcess($data) {
   global $LANG;

   switch ($data['action']) {
      case 'plugin_barcode_barcode' :
         $ci = new $data['itemtype']();
         $codes = array();
         foreach ($data['item'] as $key => $val) {
            if ($val == 1) {
               $ci->getFromDB($key);
               if ($ci->isField('otherserial')) {
                  $codes[] = $ci->getField('otherserial');
               }
            }
         }
         if (sizeof($codes)) {
            $params['codes'] = $codes;
            $params['type'] = $data['type'];
            $params['size'] = $data['size'];
            $params['orientation'] = $data['orientation'];
            $barcode = new PluginBarcodeBarcode();
            $file = $barcode->printPDF($params);
            $filePath = explode('/', $file);
            $filename = $filePath[count($filePath)-1];
            // TODO : recup GLPI_ROOT de la page d'origine via SESSION ?
            $pathOrigin = '..';
            $msg = "<a href='".$pathOrigin.'/plugins/barcode/front/send.php?file='.urlencode($filename)."'>".$LANG['plugin_barcode']["message"][0]."</a>";
            addMessageAfterRedirect($msg);
         }
         break;
   }
}


// How to display specific update fields ?
// options must contain at least itemtype and options array
function plugin_barcode_MassiveActionsFieldsDisplay($options=array()) {
   $table = $options['options']['table'];
   $field = $options['options']['field'];
   $linkfield = $options['options']['linkfield'];
   if ($table == getTableForItemType($options['itemtype'])) {

      // Table fields
      switch ($table.".".$field) {
         case 'glpi_plugin_example.serial' :
            echo "Not really specific - Just for example&nbsp;";
            autocompletionTextField($linkfield,$table,$field);
            // Need to return true if specific display
            return true;
      }

   } else {
      // Linked Fields
      switch ($table.".".$field) {
         case "glpi_plugin_example_dropdowns.name" :
            echo "Not really specific - Just for example&nbsp;";
            dropdown($table,$linkfield,1,$_SESSION["glpiactive_entity"]);
            // Need to return true if specific display
            return true;
      }
   }
   // Need to return false on non display item
   return false;
}

// Define headings added by the plugin
function plugin_get_headings_barcode($item, $withtemplate) {
   global $LANG;

   switch (get_class($item)) {
      case 'Computer' :
      case 'Monitor' :
		case 'Networking' :
		case 'Printer' :
		case 'Peripheral' :
         // new object / template case
         if ($withtemplate) {
            return array();
            // Non template case / editing an existing object
         } else {
            return array(1 => $LANG['plugin_barcode']["name"]);
         }
   }
   return false;
}


// Define headings actions added by the plugin
function plugin_headings_actions_barcode($item) {

   switch (get_class($item)) {
      case 'Computer' :
      case 'Monitor' :
		case 'Networking' :
		case 'Printer' :
		case 'Peripheral' :
         return array(1 => "plugin_headings_barcode");
   }
   return false;
}


// Example of an action heading
function plugin_headings_barcode($item, $withtemplate=0) {
   global $LANG;

   if (!$withtemplate) {
      echo "<div class='center'>";
      switch (get_class($item)) {
         default :
            $barcode = new PluginBarcodeBarcode();
            $barcode->showForm(get_class($item), $item->getField('id'));
            break;
      }
      echo "</div>";
   }
}

// Install process for plugin : need to return true if succeeded
function plugin_barcode_install() {
   global $DB;

   if (!file_exists(GLPI_ROOT."/files/_plugins/barcode")) {
      mkdir(GLPI_ROOT."/files/_plugins/barcode",0750);
   }
   if (!TableExists("glpi_plugin_barcode_config")) {
      $query = "CREATE TABLE `glpi_plugin_barcode_config` (
                  `id` int(11) NOT NULL auto_increment,
                  `type` varchar(20) collate utf8_unicode_ci default NULL,
                  PRIMARY KEY  (`ID`)
               ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->query($query) or die("error creating glpi_plugin_barcode_config ". $DB->error());

      $query = "INSERT INTO `glpi_plugin_barcode_config` 
                     (`id`, `type`)
                VALUES
                     ('1', 'code128')";
      $DB->query($query) or die("error populate glpi_plugin_barcode_config ". $DB->error());

      $query = "CREATE TABLE `glpi_plugin_barcode_config_type` (
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
      $DB->query($query) or die("error creating glpi_plugin_barcode_config_type ". $DB->error());

      $query = "INSERT INTO `glpi_plugin_barcode_config_type`
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
                     '25', '30', '110', '70')";
      $DB->query($query) or die("error populate glpi_plugin_barcode_config_type ". $DB->error());
   }
   return true;
}


// Uninstall process for plugin : need to return true if succeeded
function plugin_barcode_uninstall() {
   global $DB;

   if (TableExists("glpi_plugin_barcode_config")) {
      $query = "DROP TABLE `glpi_plugin_barcode_config`";
      $DB->query($query) or die("error deleting glpi_plugin_barcode_config");
   }
   if (TableExists("glpi_plugin_barcode_config_type")) {
      $query = "DROP TABLE `glpi_plugin_barcode_config_type`";
      $DB->query($query) or die("error deleting glpi_plugin_barcode_config_type");
   }
   return true;
}
?>
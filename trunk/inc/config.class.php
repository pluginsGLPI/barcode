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

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginBarcodeConfig extends CommonDBTM {

   static $rightname = 'plugin_barcode_config';
   
   function __construct() {
		$this->table = "glpi_plugin_barcode_config";
	}
   
   

   function showForm($p_type=NULL) {
      global $CFG_GLPI;

      $pbBarcode = new PluginBarcodeBarcode();

      $defaultType = $this->getConfig();
      echo "<form name='form' method='post'
                  action='".$CFG_GLPI['root_doc']."/plugins/barcode/front/config.form.php'
                   enctype='multipart/form-data'>";

		echo "<div align='center'>";
		echo "<table class='tab_cadre' width='550'>";
		echo "<tr><th colspan='4'>".__('Barcode plugin configuration', 'barcode')."</th></tr>";
		echo "</table><br>";

		echo "<table class='tab_cadre' width='550'>";
		echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>".__('General configuration', 'barcode')."</th>";
      echo "</tr>";
      
		echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Type', 'barcode')."</td>";
      echo "<td>";
      $this->showTypeSelect($defaultType);
      echo "</td>";
      echo "<td colspan='2'><input type='submit' value='".__('Save')."' class='submit'></td>";
		echo "</tr>";
 
		echo "<tr class='tab_bg_1'>";
      echo "<td class='tab_bg_1' colspan='4' align='center'><input type='submit' value='".__('Empty the cache', 'barcode')."' class='submit' name='dropCache'></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>".__('Company logo', 'barcode')."</th>";
      echo "</tr>";

      if (file_exists(GLPI_PLUGIN_DOC_DIR.'/barcode/logo.png')) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='4' align='center'>";
         echo "<img src='".$CFG_GLPI['root_doc']."/plugins/barcode/front/document.send.php?file=barcode/logo.png'
               width='300'/>";
         echo "</td>";
         echo "</tr>";
      }
      
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'><input type='file' name='logo' value='' /></td>";
      echo "<td colspan='2'><input type='submit' value='".__('Save')."' class='submit'></td>";
      echo "</tr>";
      
		echo "</table>";
		echo "</div>";
		Html::closeForm();
      $types = $pbBarcode->getCodeTypes();
      foreach($types as $type) {
         echo '<br>';
         $this->showFormConfigType($type);
      }
   }

   
   
   function getConfig() {
      $pbconf = new PluginBarcodeConfig();
      if ($pbconf->getFromDB(1)) {
         $type = $pbconf->fields['type'];
      } else {
         $type = 'code128';
      }
      return $type;
   }

   
   
   function showFormConfigType($p_type=NULL) {
      global $CFG_GLPI;
      
      $pbBarcode = new PluginBarcodeBarcode();

      if (is_null($p_type)) {
         $type = $this->getConfig();
      } else {
         $type = $p_type;
      }
		$config = $this->getConfigType($type);
      echo "<form name='form' method='post'
                  action='".$CFG_GLPI['root_doc']."/plugins/barcode/front/config_type.form.php'>";
      echo "<input type='hidden' name='type' value='".$type."'>";
		echo "<div align='center'>";
		echo "<table class='tab_cadre' >";

		echo "<tr><th colspan='4'>".$type."</th></tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".__('Page size', 'barcode')."</td><td>";
            $pbBarcode->showSizeSelect($config['size']);
            echo "</td>";
            echo "<td>".__('Orientation', 'barcode')."</td><td>";
            $pbBarcode->showOrientationSelect($config['orientation']);
            echo "</td>";
         echo "</tr>";
         echo "<tr><th colspan='4'>".__('Margins', 'barcode')."</th></tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".__('Top', 'barcode')."</td><td>";
            echo "<input type='text' size='20' name='marginTop' value='".$config['marginTop']."'>";
            echo "</td>";
            echo "<td>".__('Bottom', 'barcode')."</td><td>";
            echo "<input type='text' size='20' name='marginBottom' value='".$config['marginBottom']."'>";
            echo "</td>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".__('Left', 'barcode')."</td><td>";
            echo "<input type='text' size='20' name='marginLeft' value='".$config['marginLeft']."'>";
            echo "</td>";
            echo "<td>".__('Right', 'barcode')."</td><td>";
            echo "<input type='text' size='20' name='marginRight' value='".$config['marginRight']."'>";
            echo "</td>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".__('Inner horizontal', 'barcode')."</td><td>";
            echo "<input type='text' size='20' name='marginHorizontal' value='".$config['marginHorizontal']."'>";
            echo "</td>";
            echo "<td>".__('Inner vertical', 'barcode')."</td><td>";
            echo "<input type='text' size='20' name='marginVertical' value='".$config['marginVertical']."'>";
            echo "</td>";
         echo "</tr>";
         echo "<tr><th colspan='4'>".__('Barcodes sizes', 'barcode')."</th></tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".__('Maximum width', 'barcode')."</td><td>";
            echo "<input type='text' size='20' name='maxCodeWidth' value='".$config['maxCodeWidth']."'>";
            echo "</td>";
            echo "<td>".__('Maximum height', 'barcode')."</td><td>";
            echo "<input type='text' size='20' name='maxCodeHeight' value='".$config['maxCodeHeight']."'>";
            echo "</td>";
         echo "</tr>";

		echo "<tr><td class='tab_bg_1' colspan='4' align='center'><input type='submit' value='".__('Save')."' class='submit'></td></tr>";
		echo "</table>";
		echo "</div>";
		Html::closeForm();
   }

   
   
   function getConfigType($p_type=NULL) {
      if (is_null($p_type)) {
         $p_type=$this->getConfig();
      }
      $pbcconf = new PluginBarcodeConfig_Type();
      if ($res=array_keys($pbcconf->find("`type`='$p_type'"))) {
         $id = $res[0];
         $pbcconf->getFromDB($id);
         $config['type']               = $pbcconf->fields['type'];
         $config['size']               = $pbcconf->fields['size'];
         $config['orientation']        = $pbcconf->fields['orientation'];
         $config['marginTop']          = $pbcconf->fields['marginTop'];
         $config['marginBottom']       = $pbcconf->fields['marginBottom'];
         $config['marginLeft']         = $pbcconf->fields['marginLeft'];
         $config['marginRight']        = $pbcconf->fields['marginRight'];
         $config['marginHorizontal']   = $pbcconf->fields['marginHorizontal'];
         $config['marginVertical']     = $pbcconf->fields['marginVertical'];
         $config['maxCodeWidth']       = $pbcconf->fields['maxCodeWidth'];
         $config['maxCodeHeight']      = $pbcconf->fields['maxCodeHeight'];
      } else {
         $config['type']               = 'code128';
         $config['size']               = 'A4';
         $config['orientation']        = 'Portrait';
         $config['marginTop']          = 30;
         $config['marginBottom']       = 30;
         $config['marginLeft']         = 30;
         $config['marginRight']        = 30;
         $config['marginHorizontal']   = 25;
         $config['marginVertical']     = 30;
         $config['maxCodeWidth']       = 110;
         $config['maxCodeHeight']      = 70;
      }
      return $config;
   }

   
   
   function showTypeSelect($p_type=NULL, $used=array()) {

      
      $options = array(
          'width' => '100',
          'used'  => $used
      );
      if (!is_null($p_type)) {
         $options['value'] = $p_type;
      }
      Dropdown::showFromArray("type",
                              array('Code39'    => __('code39', 'barcode'),
                                    'code128'   => __('code128', 'barcode'),
                                    'ean13'     => __('ean13', 'barcode'),
                                    'int25'     => __('int25', 'barcode'),
                                    'postnet'   => __('postnet', 'barcode'),
                                    'upca'      => __('upca', 'barcode'),
                                    'QRcode'    => __('QRcode', 'barcode')),
                              $options
                              );
   }
   
   
   
   /**
    * Configure QRcode to display: 
    *  ¤ GLPI URL of device
    *  ¤ Serial number
    *  ¤ inventory number
    */
   function showQRcodeConfig() {
      
      
      
   }
}

?>
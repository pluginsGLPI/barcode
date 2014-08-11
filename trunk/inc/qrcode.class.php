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
   @since     2013

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginBarcodeQRcode {

   function __construct() {
      require_once(GLPI_ROOT.'/plugins/barcode/lib/phpqrcode/qrlib.php'); 
   }
   
   
   
   function generateQRcode($itemtype, $items_id, $rand, $number, $data) {
      global $CFG_GLPI;
      
      $item = new $itemtype();
      $item->getFromDB($items_id);
      $a_content = array();
      $have_content = FALSE;
      if ($data['serialnumber']) {
         if ($item->fields['serial'] != '') {
            $have_content = TRUE;
         }
         $a_content[] = 'Serial Number = '.$item->fields['serial'];
      }
      if ($data['inventorynumber']) {
         if ($item->fields['otherserial'] != '') {
            $have_content = TRUE;
         }
         $a_content[] = 'Inventory Number = '.$item->fields['otherserial'];
      }
      if ($data['id']) {
         if ($item->fields['id'] != '') {
            $have_content = TRUE;
         }
         $a_content[] = 'ID = '.$item->fields['id'];
      }
      if ($data['uuid']) {
         if ($item->fields['uuid'] != '') {
            $have_content = TRUE;
         }
         $a_content[] = 'UUID = '.$item->fields['uuid'];
      }      
      if ($data['name']) {
         if ($item->fields['name'] != '') {
            $have_content = TRUE;
         }
         $a_content[] = 'Name = '.$item->fields['name'];
      }
      if ($data['url']) {
         $a_content[] = 'URL = '.$CFG_GLPI["url_base"].Toolbox::getItemTypeFormURL($itemtype, false)."?id=".$items_id;
      }      
      if ($data['qrcodedate']) {
         $a_content[] = 'QRcode date = '.date('Y-m-d');
      }      
      
      if (count($a_content) > 0) {
         $codeContents = implode("\n", $a_content);
         QRcode::png($codeContents, 
                     GLPI_PLUGIN_DOC_DIR.'/barcode/_tmp_'.$rand.'-'.$number.'.png',
                     QR_ECLEVEL_L, 
                     4);
         return GLPI_PLUGIN_DOC_DIR.'/barcode/_tmp_'.$rand.'-'.$number.'.png';
      }
      return false;
   }
   
   
   
   function cleanQRcodefiles($rand, $number) {      
      for ($i = 0; $i < $number; $i++) {
         unlink(GLPI_PLUGIN_DOC_DIR.'/barcode/_tmp_'.$rand.'-'.$i.'.png');
      }      
   }
   
   
   
   function showFormMassiveAction() {
      
      echo '<input type="hidden" name="type" value="QRcode" />';
      echo '<center>';
      echo '<table>';
      echo '<tr>';
      echo '<td>';
      echo __('Serial number')." : </td><td>";
      Dropdown::showYesNo("serialnumber", 1, -1, array('width' => '100'));
      echo '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>';
      echo __('Inventory number')." : </td><td>";
      Dropdown::showYesNo("inventorynumber", 1, -1, array('width' => '100')); 
      echo '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>';
      echo __('ID')." : </td><td>";
      Dropdown::showYesNo("id", 1, -1, array('width' => '100')); 
      echo '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>';
      echo __('UUID')." : </td><td>";
      Dropdown::showYesNo("uuid", 1, -1, array('width' => '100')); 
      echo '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>';
      echo __('Name')." : </td><td>";
      Dropdown::showYesNo("name", 1, -1, array('width' => '100'));
      echo '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>';     
      echo __('Web page of the device')." : </td><td>";
      Dropdown::showYesNo("url", 1, -1, array('width' => '100'));
      echo '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>';
      echo __('Date QRcode')." (".date('Y-m-d').") : </td><td>";
      Dropdown::showYesNo("qrcodedate", 1, -1, array('width' => '100'));
      echo '</td>';
      echo '</tr>';
      echo '</table>';
      echo '<br/>';
      
      PluginBarcodeBarcode::commonShowMassiveAction();
   }

   
   
   function getSpecificMassiveActions($checkitem=NULL) {
      $actions = parent::getSpecificMassiveActions($checkitem);
      return $actions;
   }
   
   
   
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      global $CFG_GLPI;

      switch ($ma->getAction()) {
         case 'Generate':
            $pbQRcode = new self();
            $pbQRcode->showFormMassiveAction();
            return true;
    }
      return parent::showMassiveActionsSubForm($ma);
   }

   
   
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      global $CFG_GLPI;

      switch ($ma->getAction()) {
         case 'Generate' :
            $pbQRcode = new PluginBarcodeQRcode();
            $rand = mt_rand();
            $number = 0;     
            $codes = array();
            if ($ma->POST['eliminate'] > 0) {
               for ($nb=0; $nb < $ma->POST['eliminate']; $nb++) {
                  $codes[] = '';
               }
            }
            if ($ma->POST['type'] == 'QRcode') {
               foreach ($ids as $key) {
                  $filename = $pbQRcode->generateQRcode($item->getType(), $key, $rand, $number, $ma->POST);
                  if ($filename) {
                     $codes[] = $filename;
                     $number++;
                  }
               }
            } else {    
               foreach ($ids as $key) {
                  $item->getFromDB($key);
                  if ($item->isField('otherserial')) {
                     $codes[] = $item->getField('otherserial');
                  }
               }
            }
            if (count($codes) > 0) {
               $params['codes']  = $codes;
               $params['type']   = $ma->POST['type'];
               $params['size']   = $ma->POST['size'];
               $params['border'] = $ma->POST['border'];
               $params['orientation'] = $ma->POST['orientation'];
               $barcode = new PluginBarcodeBarcode();
               $file = $barcode->printPDF($params);
               $filePath = explode('/', $file);
               $filename = $filePath[count($filePath)-1];

               $msg = "<a href='".$CFG_GLPI['root_doc'].'/plugins/barcode/front/send.php?file='.urlencode($filename)."'>".__('Generated file', 'barcode')."</a>";
               Session::addMessageAfterRedirect($msg);
               $pbQRcode->cleanQRcodefiles($rand, $number);
            }
            $ma->itemDone($item->getType(), 0, MassiveAction::ACTION_OK);
            return;
         
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   
}

?>
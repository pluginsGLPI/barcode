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
   @since     2013

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginBarcodeQRcode
{

   function generateQRcode($itemtype, $items_id, $rand, $number, $data)
   {
      global $CFG_GLPI;

      /** @var CommonDBTM $item */

      $item = new $itemtype();
      $item->getFromDB($items_id);
      $itemByInvNumber = $item->fields['otherserial'];
      $URLById= 'URL = ' . $CFG_GLPI['url_base'] . $itemtype::getFormURLWithID($items_id);
      $URLByInvNumber = 'URL = ' . $CFG_GLPI['url_base'] . '/plugins/barcode/front/checkItemByInv.php?inventoryNumber='. $itemByInvNumber . '&itemtype=' . $itemtype;

      $a_content = [];
      if ($data['serialnumber'] && $item->fields['serial'] != '') {
         $a_content[] = 'Serial Number = ' . $item->fields['serial'];
      }
      if ($data['inventorynumber'] && $item->fields['otherserial'] != '') {
         $a_content[] = 'Inventory Number = ' . $item->fields['otherserial'];
      }
      if ($data['id'] && $item->fields['id'] != '') {
         $a_content[] = 'ID = ' . $item->fields['id'];
      }
      if ($data['uuid'] && $item->fields['uuid'] != '') {
         $a_content[] = 'UUID = ' . $item->fields['uuid'];
      }
      if ($data['name'] && $item->fields['name'] != '') {
         $a_content[] = 'Name = ' . $item->fields['name'];
      }
      if ($data['url'] && !$item->no_form_page) {
         if($data['inventorynumberURL']) {
            $a_content[] = $URLByInvNumber;
         } else {
         $a_content[] = $URLById;
         }
      }

      if ($data['qrcodedate']) {
         $a_content[] = 'QRcode date = ' . date('Y-m-d');
      }

      if (count($a_content) > 0) {
         $codeContents = implode("\n", $a_content);
         QRcode::png(
            $codeContents,
            GLPI_PLUGIN_DOC_DIR . '/barcode/_tmp_' . $rand . '-' . $number . '.png',
            QR_ECLEVEL_L,
            4
         );
         return GLPI_PLUGIN_DOC_DIR . '/barcode/_tmp_' . $rand . '-' . $number . '.png';
      }
      return false;
   }



   function cleanQRcodefiles($rand, $number)
   {
      for ($i = 0; $i < $number; $i++) {
         unlink(GLPI_PLUGIN_DOC_DIR . '/barcode/_tmp_' . $rand . '-' . $i . '.png');
      }
   }


   function showFormMassiveAction(MassiveAction $ma)
   {

      $fields       = [];
      $no_form_page = true;

      $itemtype = $ma->getItemtype(false);
      if (is_a($itemtype, CommonDBTM::class, true)) {
         /** @var CommonDBTM $item */
         $item = new $itemtype();
         $item->getEmpty();
         $fields = array_keys($item->fields);
         $no_form_page = $item->no_form_page;
      }

      echo '<input type="hidden" name="type" value="QRcode" />';
      echo '<center>';
      echo '<table>';

      if (in_array('serial', $fields)) {
         echo '<tr>';
         echo '<td>';
         echo __('Serial number') . " : </td><td>";
         Dropdown::showYesNo("serialnumber", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('serialnumber', ['value' => 0]);
      }

      if (in_array('otherserial', $fields)) {
         echo '<tr>';
         echo '<td>';
         echo __('Inventory number') . " : </td><td>";
         Dropdown::showYesNo("inventorynumber", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('inventorynumber', ['value' => 0]);
      }

      echo '<tr>';
      echo '<td>';
      echo __('ID') . " : </td><td>";
      Dropdown::showYesNo("id", 1, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';

      if (in_array('uuid', $fields)) {
         echo '<tr>';
         echo '<td>';
         echo __('UUID') . " : </td><td>";
         Dropdown::showYesNo("uuid", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('uuid', ['value' => 0]);
      }

      if (in_array('name', $fields)) {
         echo '<tr>';
         echo '<td>';
         echo __('Name') . " : </td><td>";
         Dropdown::showYesNo("name", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('name', ['value' => 0]);
      }

      echo '<tr>';
      echo '<td>';
      echo __('URL by inventory number') . " : </td><td>";
      Dropdown::showYesNo("inventorynumberURL", 1, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';

      if (!$no_form_page) {
         echo '<tr>';
         echo '<td>';
         echo __('Web page of the item') . " : </td><td>";
         Dropdown::showYesNo("url", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('url', ['value' => 0]);
      }

      echo '<tr>';
      echo '<td>';
      echo __('Date QRcode') . " (" . date('Y-m-d') . ") : </td><td>";
      Dropdown::showYesNo("qrcodedate", 1, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';

      echo '</table>';
      echo '<br/>';

      PluginBarcodeBarcode::commonShowMassiveAction();
   }



   function getSpecificMassiveActions($checkitem = null)
   {
      $actions = parent::getSpecificMassiveActions($checkitem);
      return $actions;
   }



   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    **/
   static function showMassiveActionsSubForm(MassiveAction $ma)
   {

      switch ($ma->getAction()) {

         case 'Generate':
            $pbQRcode = new self();
            $pbQRcode->showFormMassiveAction($ma);
            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }



   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
   {
      global $CFG_GLPI;

      switch ($ma->getAction()) {

         case 'Generate':
            $pbQRcode = new PluginBarcodeQRcode();
            $rand     = mt_rand();
            $number   = 0;
            $codes    = [];
            $URLtype = '';
            if ($ma->POST['eliminate'] > 0) {
               for ($nb = 0; $nb < $ma->POST['eliminate']; $nb++) {
                  $codes[] = '';
               }
            }
            if ($ma->POST['type'] == 'QRcode') {
               if($item->isField('inventotynumberURL')) {
                  $URLtype = 'inventoryURL';
               } else {
                  $URLtype = 'idURL';
               }
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
               $params['codes']       = $codes;
               $params['type']        = $ma->POST['type'];
               $params['size']        = $ma->POST['size'];
               $params['border']      = $ma->POST['border'];
               $params['orientation'] = $ma->POST['orientation'];
               $barcode               = new PluginBarcodeBarcode();
               $file                  = $barcode->printPDF($params);
               $filePath              = explode('/', $file);
               $filename              = $filePath[count($filePath) - 1];
               $msg = "<a href='" . $CFG_GLPI['root_doc'] . '/plugins/barcode/front/send.php?file=' . urlencode($filename) . "'>" . __('Generated file', 'barcode') . "</a>";
               Session::addMessageAfterRedirect($msg);
               $pbQRcode->cleanQRcodefiles($rand, $number);
            }
            $ma->itemDone($item->getType(), 0, MassiveAction::ACTION_OK);
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }
}

<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

require_once __DIR__ . '/../vendor/deltalab/phpqrcode/phpqrcode.php';

class PluginBarcodeQRcode {

   function generateQRcode($itemtype, $items_id, $rand, $number, $data) {
      global $CFG_GLPI;

      $item = new $itemtype();
      $item->getFromDB($items_id);

      $itemByInvNumber = $item->fields['otherserial'] ?? '';

      $URLById = 'URL = ' . $CFG_GLPI['url_base'] . $itemtype::getFormURLWithID($items_id, false);
      $URLByInvNumber = 'URL = ' . $CFG_GLPI['url_base']
         . '/plugins/barcode/front/checkItemByInv.php?inventoryNumber='
         . urlencode($itemByInvNumber)
         . '&itemtype=' . urlencode($itemtype);

      $a_content = [];
      $b_content = [];

      if (!empty($data['serialnumber']) && isset($item->fields['serial'])) {
         $a_content[] = 'Serial number = ' . $item->fields['serial'];
         if (!empty($data['displayserialnumber'])) {
            $label = !empty($data['displaylabels']) ? 'Serial: ' : '';
            $b_content[] = $label . $item->fields['serial'];
         }
      }

      if (!empty($data['inventorynumber']) && isset($item->fields['otherserial'])) {
         $a_content[] = 'Inventory number = ' . $item->fields['otherserial'];
         if (!empty($data['displayinventorynumber'])) {
            $label = !empty($data['displaylabels']) ? 'Inventario: ' : '';
            $b_content[] = $label . $item->fields['otherserial'];
         }
      }

      if (!empty($data['id'])) {
         $a_content[] = 'ID = ' . $item->fields['id'];
         if (!empty($data['displayid'])) {
            $label = !empty($data['displaylabels']) ? 'ID: ' : '';
            $b_content[] = $label . $item->fields['id'];
         }
      }

      if (!empty($data['uuid']) && isset($item->fields['uuid'])) {
         $a_content[] = 'UUID = ' . $item->fields['uuid'];
         if (!empty($data['displayuuid'])) {
            $label = !empty($data['displaylabels']) ? 'UUID: ' : '';
            $b_content[] = $label . $item->fields['uuid'];
         }
      }

      if (!empty($data['name']) && isset($item->fields['name'])) {
         $a_content[] = 'Name = ' . $item->fields['name'];
         if (!empty($data['displayname'])) {
            $label = !empty($data['displaylabels']) ? 'Nombre: ' : '';
            $b_content[] = $label . $item->fields['name'];
         }
      }

      if (!empty($data['url']) && !$item->no_form_page) {
         $urlValue = !empty($data['inventorynumberURL']) ? $URLByInvNumber : $URLById;
         $a_content[] = $urlValue;
         if (!empty($data['displayurl'])) {
            $label = !empty($data['displaylabels']) ? 'URL: ' : '';
            $b_content[] = $label . $urlValue;
         }
      }

      if (!empty($data['qrcodedate'])) {
         $dateStr = date('Y-m-d');
         $a_content[] = 'QRcode date = ' . $dateStr;
         if (!empty($data['displayqrcodedate'])) {
            $label = !empty($data['displaylabels']) ? 'Fecha: ' : '';
            $b_content[] = $label . $dateStr;
         }
      }

      if (count($a_content) === 0) {
         return false;
      }

      $codeContents = implode("\n", $a_content);
      $outputPath   = GLPI_PLUGIN_DOC_DIR . '/barcode/_tmp_' . $rand . '-' . $number . '.png';

      QRcode::png($codeContents, $outputPath, QR_ECLEVEL_L, 4);

      return [$outputPath, $b_content];
   }

   function cleanQRcodefiles($rand, $number) {
      for ($i = 0; $i < $number; $i++) {
         $tmpFile = GLPI_PLUGIN_DOC_DIR . '/barcode/_tmp_' . $rand . '-' . $i . '.png';
         if (file_exists($tmpFile)) {
            @unlink($tmpFile);
         }
      }
   }

   function showFormMassiveAction(MassiveAction $ma) {

      $fields       = [];
      $no_form_page = true;

      $itemtype = $ma->getItemtype(false);
      if (is_a($itemtype, CommonDBTM::class, true)) {
         $item = new $itemtype();
         $item->getEmpty();
         $fields       = array_keys($item->fields);
         $no_form_page = $item->no_form_page;
      }

      echo '<input type="hidden" name="type" value="QRcode" />';
      echo '<center>';
      echo '<table>';

      if (in_array('serial', $fields, true)) {
         echo '<tr>';
         echo '<td>Numero de serial : </td><td>';
         Dropdown::showYesNo("serialnumber", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '<td>Mostrar en etiqueta : </td><td>';
         Dropdown::showYesNo("displayserialnumber", 0, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('serialnumber', ['value' => 0]);
      }

      if (in_array('otherserial', $fields, true)) {
         echo '<tr>';
         echo '<td>Numero de inventario : </td><td>';
         Dropdown::showYesNo("inventorynumber", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '<td>Mostrar en etiqueta : </td><td>';
         Dropdown::showYesNo("displayinventorynumber", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('inventorynumber', ['value' => 0]);
      }

      echo '<tr>';
      echo '<td>ID : </td><td>';
      Dropdown::showYesNo("id", 1, -1, ['width' => '100']);
      echo '</td>';
      echo '<td>Mostrar en etiqueta : </td><td>';
      Dropdown::showYesNo("displayid", 0, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';

      if (in_array('uuid', $fields, true)) {
         echo '<tr>';
         echo '<td>UUID : </td><td>';
         Dropdown::showYesNo("uuid", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '<td>Mostrar en etiqueta : </td><td>';
         Dropdown::showYesNo("displayuuid", 0, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('uuid', ['value' => 0]);
      }

      if (in_array('name', $fields, true)) {
         echo '<tr>';
         echo '<td>Nombre : </td><td>';
         Dropdown::showYesNo("name", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '<td>Mostrar en etiqueta : </td><td>';
         Dropdown::showYesNo("displayname", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('name', ['value' => 0]);
      }

      echo '<tr>';
      echo '<td>URL por numero de inventario : </td><td>';
      Dropdown::showYesNo("inventorynumberURL", 1, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';

      if (!$no_form_page) {
         echo '<tr>';
         echo '<td>Pagina web del dispositivo : </td><td>';
         Dropdown::showYesNo("url", 1, -1, ['width' => '100']);
         echo '</td>';
         echo '<td>Mostrar en etiqueta : </td><td>';
         Dropdown::showYesNo("displayurl", 0, -1, ['width' => '100']);
         echo '</td>';
         echo '</tr>';
      } else {
         echo Html::hidden('url', ['value' => 0]);
      }

      echo '<tr>';
      echo '<td>Fecha QRcode (' . date('Y-m-d') . ') : </td><td>';
      Dropdown::showYesNo("qrcodedate", 1, -1, ['width' => '100']);
      echo '</td>';
      echo '<td>Mostrar en etiqueta : </td><td>';
      Dropdown::showYesNo("displayqrcodedate", 0, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td colspan="4"><em>';
      echo '<strong>Columna izquierda:</strong> incluir dato en el QR code. ';
      echo '<strong>Mostrar en etiqueta:</strong> mostrar texto visible bajo el codigo.';
      echo '</em></td>';
      echo '</tr>';

      echo '</table>';
      echo '<br/>';

      PluginBarcodeBarcode::commonShowMassiveAction();
   }

   function getSpecificMassiveActions($checkitem = null) {
      return [];
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {
      switch ($ma->getAction()) {
         case 'Generate':
            $pbQRcode = new self();
            $pbQRcode->showFormMassiveAction($ma);
            return true;
      }
      return false;
   }

   static function processMassiveActionsForOneItemtype(
      MassiveAction $ma,
      CommonDBTM $item,
      array $ids
   ) {
      switch ($ma->getAction()) {
         case 'Generate':
            $pbQRcode = new PluginBarcodeQRcode();
            $rand     = mt_rand();
            $number   = 0;
            $codes    = [];
            $displayDataCollection = [];

            $eliminate = (int)($ma->POST['eliminate'] ?? 0);
            for ($nb = 0; $nb < $eliminate; $nb++) {
               $codes[]               = '';
               $displayDataCollection[] = [];
            }

            if ($ma->POST['type'] === 'QRcode') {
               foreach ($ids as $key) {
                  $values = $pbQRcode->generateQRcode(
                     $item->getType(), $key, $rand, $number, $ma->POST
                  );
                  if ($values !== false) {
                     [$filename, $displayData] = $values;
                     $codes[]                 = $filename;
                     $displayDataCollection[] = $displayData;
                     $number++;
                  }
               }
            } else {
               foreach ($ids as $key) {
                  $item->getFromDB($key);
                  if ($item->isField('otherserial')) {
                     $codes[]               = $item->getField('otherserial');
                     $displayDataCollection[] = [];
                  }
               }
            }

            if (count($codes) > 0) {
               $params = [
                  'codes'         => $codes,
                  'displayData'   => $displayDataCollection,
                  'type'          => $ma->POST['type'],
                  'size'          => $ma->POST['size'],
                  'border'        => $ma->POST['border'],
                  'orientation'   => $ma->POST['orientation'],
                  'displaylabels' => $ma->POST['displaylabels'],
               ];

               $barcode  = new PluginBarcodeBarcode();
               $file     = $barcode->printPDF($params);
               $filePath = explode('/', $file);
               $filename = end($filePath);

               $msg = "<a href='/plugins/barcode/front/send.php?file=" . urlencode($filename) . "'>"
                    . __('Generated file', 'barcode') . "</a>";

               Session::addMessageAfterRedirect($msg);
               $pbQRcode->cleanQRcodefiles($rand, $number);
            }

            $ma->itemDone($item->getType(), 0, MassiveAction::ACTION_OK);
            return;
      }
   }
}

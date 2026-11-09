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
   @since     2013

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * QR code generation as a ZIP archive of BMP files
 * (24-bit BMP, white margins cropped: MS Access / Brother P-touch labels)
 */
class PluginBarcodeQRcode {

   /**
    * Sanitize a string for safe use as a filename
    * Replaces spaces with underscores and removes invalid Windows filename characters
    *
    * @param string $name The filename to sanitize
    * @return string The sanitized filename
    */
   private static function sanitizeFilename($name) {
      $name = str_replace(' ', '_', (string) $name);
      // Remove invalid Windows filename characters and control chars
      $name = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F]/', '', $name);
      $name = rtrim($name, ' .');
      return mb_substr($name, 0, 100);
   }



   /**
    * Text encoded in the QR code of one item.
    *
    * The format ("Label = value" lines) is consumed by external tools:
    * keep it stable.
    *
    * @param CommonDBTM $item  loaded item
    * @param array      $data  options posted by the massive action form
    *
    * @return string[] lines
    */
   static function getContent(CommonDBTM $item, array $data): array {
      global $CFG_GLPI;

      $fields   = $item->fields;
      $itemtype = $item->getType();
      $content  = [];
      if (!empty($data['serialnumber'])) {
         $content[] = __('Serial number') . ' = ' . ($fields['serial'] ?? '');
      }
      if (!empty($data['inventorynumber'])) {
         $content[] = __('Inventory number') . ' = ' . ($fields['otherserial'] ?? '');
      }
      if (!empty($data['id'])) {
         $content[] = __('ID') . ' = ' . ($fields['id'] ?? '');
      }
      if (!empty($data['uuid'])) {
         $content[] = __('UUID') . ' = ' . ($fields['uuid'] ?? '');
      }
      if (!empty($data['name'])) {
         $content[] = __('Item Name') . ' = ' . ($fields['name'] ?? '');
      }
      if (!empty($data['url']) && !$item->no_form_page) {
         if (!empty($data['inventorynumberURL'])) {
            $content[] = 'URL = ' . $CFG_GLPI['url_base'] . '/plugins/barcode/front/checkItemByInv.php?'
               . http_build_query([
                  'inventoryNumber' => $fields['otherserial'] ?? '',
                  'itemtype'        => $itemtype,
               ], '', '&', PHP_QUERY_RFC3986);
         } else {
            $content[] = 'URL = ' . $CFG_GLPI['url_base'] . $itemtype::getFormURLWithID($item->getID(), false);
         }
      }
      if (!empty($data['qrcodedate'])) {
         $content[] = 'QRcode date = ' . date('Y-m-d');
      }
      return $content;
   }



   /**
    * Generate the QR code PNG of one item.
    *
    * @param string $itemtype
    * @param int    $items_id
    * @param int    $rand     unique id of the current generation
    * @param int    $number   index of the file in the current generation
    * @param array  $data     options posted by the massive action form
    *
    * @return string|false path of the PNG file
    */
   function generateQRcode($itemtype, $items_id, $rand, $number, $data) {
      $item = getItemForItemtype($itemtype);
      if (!$item || !$item->getFromDB($items_id)) {
         return false;
      }

      $content = self::getContent($item, $data);
      if (count($content) === 0) {
         return false;
      }

      $file = GLPI_PLUGIN_DOC_DIR . '/barcode/_tmp_' . $rand . '-' . $number . '.png';
      QRcode::png(implode("\n", $content), $file, QR_ECLEVEL_L, 4);
      return is_file($file) ? $file : false;
   }



   /**
    * Show the massive actions sub form for QR code generation
    *
    * @param MassiveAction $ma The massive action object
    * @return void
    */
   function showFormMassiveAction(MassiveAction $ma) {

      $itemtype     = $ma->getItemtype(false);
      $fields       = [];
      $no_form_page = true;
      if (is_a($itemtype, CommonDBTM::class, true)) {
         /** @var CommonDBTM $item */
         $item = new $itemtype();
         $item->getEmpty();
         $fields       = array_keys($item->fields);
         $no_form_page = $item->no_form_page;
      }

      // name => [label, default value, displayed?]
      $options = [
         'serialnumber'       => [__('Serial number'), 1, in_array('serial', $fields, true)],
         'inventorynumber'    => [__('Inventory number'), 0, in_array('otherserial', $fields, true)],
         'id'                 => [__('ID'), 0, true],
         'uuid'               => [__('UUID'), 0, in_array('uuid', $fields, true)],
         'name'               => [__('Name'), 1, in_array('name', $fields, true)],
         'url'                => [__('Web page of the item', 'barcode'), 1, !$no_form_page],
         'inventorynumberURL' => [__('URL by inventory number', 'barcode'), 0,
                                  !$no_form_page && in_array('otherserial', $fields, true)],
         'qrcodedate'         => [sprintf(__('Date QRcode (%s)', 'barcode'), date('Y-m-d')), 1, true],
      ];

      echo Html::hidden('type', ['value' => 'QRcode']);
      echo '<div class="d-flex flex-column gap-3">';
      foreach ($options as $name => [$label, $default, $displayed]) {
         if (!$displayed) {
            echo Html::hidden($name, ['value' => 0]);
            continue;
         }
         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . htmlescape($label) . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo($name, $default, -1, ['width' => '100%']);
         echo '</div></div>';
      }
      echo '<div class="alert alert-info">';
      echo __s('QR codes are exported as a ZIP archive of BMP images (MS Access / Brother printer).', 'barcode');
      echo '</div>';
      echo '</div>';
   }



   /**
    * Show the massive actions sub form
    *
    * @param MassiveAction $ma The massive action object
    * @return bool True if the form was displayed
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      if ($ma->getAction() !== 'Generate') {
         return false;
      }

      $qrcode = new self();
      $qrcode->showFormMassiveAction($ma);

      echo '<div class="mt-3 text-center">';
      echo Html::submit(_x('button', 'Post'), [
         'name'  => 'massiveaction',
         'icon'  => 'ti ti-device-floppy',
         'class' => 'btn btn-primary',
      ]);
      echo '</div>';

      return true;
   }



   /**
    * Generate one BMP per item and send them as a ZIP archive
    *
    * @param MassiveAction $ma The massive action object
    * @param CommonDBTM $item The item object
    * @param array $ids Array of item IDs to process
    * @return void
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      if ($ma->getAction() !== 'Generate') {
         return;
      }

      if (!Session::haveRight('plugin_barcode_barcode', CREATE)) {
         $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
         return;
      }

      set_time_limit(0);

      $qrcode     = new self();
      $rand       = mt_rand();
      $barcodeDir = GLPI_PLUGIN_DOC_DIR . '/barcode';
      $zipName    = Session::getLoginUserID() . '_barcode_qrcodes.zip';
      $zipPath    = $barcodeDir . '/' . $zipName;
      $tmpFiles   = [];
      $entries    = []; // archive name => bmp file
      $processed  = [];
      $handled    = []; // ids already reported as no right / nothing to do

      try {
         if (!is_dir($barcodeDir) || !is_writable($barcodeDir)) {
            throw new RuntimeException(__('Barcode temporary directory is not writable.', 'barcode'));
         }

         foreach ($ids as $id) {
            if (!$item->getFromDB($id) || !$item->can($id, READ)) {
               $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
               $handled[] = $id;
               continue;
            }

            $pngFile = $qrcode->generateQRcode($item->getType(), $id, $rand, count($tmpFiles), $ma->POST);
            if ($pngFile === false) {
               $ma->itemDone($item->getType(), $id, MassiveAction::NO_ACTION);
               $handled[] = $id;
               continue;
            }
            $tmpFiles[] = $pngFile;

            $bmpFile    = preg_replace('/\.png$/', '.bmp', $pngFile);
            $tmpFiles[] = $bmpFile;
            if (!self::pngToBmp($pngFile, $bmpFile)) {
               throw new RuntimeException(__('Unable to convert generated QR code to BMP.', 'barcode'));
            }

            // Unique archive name based on the item name
            $baseName = self::sanitizeFilename($item->fields['name'] ?? '');
            if ($baseName === '') {
               $baseName = $item->getType() . '_' . (int) $id;
            }
            $archiveName = $baseName;
            for ($suffix = 2; isset($entries[$archiveName . '.bmp']); $suffix++) {
               $archiveName = $baseName . '_' . $suffix;
            }
            $entries[$archiveName . '.bmp'] = $bmpFile;
            $processed[] = $id;
         }

         if (count($entries) === 0) {
            Session::addMessageAfterRedirect(__('No QR codes were generated.', 'barcode'), true, WARNING);
            return;
         }

         $zip = new ZipArchive();
         if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException(__('Unable to create ZIP archive.', 'barcode'));
         }
         foreach ($entries as $archiveName => $bmpFile) {
            $zip->addFile($bmpFile, $archiveName);
         }
         // Files are read when the archive is closed: keep them until then
         if (!$zip->close()) {
            throw new RuntimeException(__('Unable to finalize ZIP archive.', 'barcode'));
         }

         Session::addMessageAfterRedirect(
            PluginBarcodeBarcode::getDownloadLink($zipName, sprintf(__('Download %s', 'barcode'), 'barcode_qrcodes.zip'))
         );
         $ma->itemDone($item->getType(), $processed, MassiveAction::ACTION_OK);

      } catch (Throwable $e) {
         Session::addMessageAfterRedirect(
            htmlescape(__('Error creating ZIP archive: ', 'barcode') . $e->getMessage()),
            true,
            ERROR
         );
         if (is_file($zipPath)) {
            @unlink($zipPath);
         }
         $ma->itemDone($item->getType(), array_values(array_diff($ids, $handled)), MassiveAction::ACTION_KO);

      } finally {
         foreach ($tmpFiles as $file) {
            if (is_file($file)) {
               @unlink($file);
            }
         }
      }
   }



   /**
    * Convert a QR code PNG to a 24-bit BMP with white margins cropped
    *
    * @param string $pngFile Path to the source PNG file
    * @param string $bmpFile Path to the destination BMP file
    * @return bool True on success, false on failure
    */
   private static function pngToBmp($pngFile, $bmpFile) {
      $image = @imagecreatefrompng($pngFile);
      if ($image === false) {
         return false;
      }
      $cropped = imagecropauto($image, IMG_CROP_WHITE);
      if ($cropped !== false) {
         $image = $cropped;
      }
      // Palette images would be written as 8-bit BMP
      imagepalettetotruecolor($image);

      ob_start();
      $ok  = imagebmp($image, null, false);
      $bmp = ob_get_clean();
      if (!$ok || strlen($bmp) < 54) {
         return false;
      }
      // Normalize the BITMAPINFOHEADER as the labelling software expects it:
      // 72 dpi (2835 px/m) and no palette entries declared for 24-bit data
      // (GD writes 0 dpi and the source palette size).
      $bmp = substr_replace($bmp, pack('VVVV', 2835, 2835, 0, 0), 38, 16);
      return file_put_contents($bmpFile, $bmp) !== false;
   }
}

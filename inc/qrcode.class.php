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

if (!function_exists('imagebmp')) {
    /**
     * BMP output function for GD
     * @param resource $image
     * @param string|resource $filename
     * @return bool
     */
    function imagebmp($image, $filename = null) {
        $width = imagesx($image);
        $height = imagesy($image);
        $bytesPerPixel = 3;
        $fileSize = (int)(($width * $bytesPerPixel + 4) * $height + 54);
        $rowSize = (int)($width * $bytesPerPixel + 3) & ~3;

        // Header
        $header = pack('v', 0x4D42) // 'BM'
                . pack('V', $fileSize) // File size
                . pack('v', 0) // Reserved
                . pack('v', 0) // Reserved
                . pack('V', 54); // Pixel data offset

        // DIB Header
        $header .= pack('V', 40) // DIB header size
                . pack('V', $width) // Width
                . pack('V', $height) // Height
                . pack('v', 1) // Color planes
                . pack('v', 24) // Bits per pixel
                . pack('V', 0) // Compression (BI_RGB)
                . pack('V', $rowSize * $height) // Image size
                . pack('V', 2835) // Horizontal resolution
                . pack('V', 2835) // Vertical resolution
                . pack('V', 0) // Colors used
                . pack('V', 0); // Important colors

        // Write file
        if ($filename !== null) {
            $fp = fopen($filename, 'wb');
            if (!$fp) {
                return false;
            }
            fwrite($fp, $header);
            for ($y = $height - 1; $y >= 0; $y--) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $b = ($rgb >> 0) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $r = ($rgb >> 16) & 0xFF;
                    fwrite($fp, pack('V', ($b << 16) | ($g << 8) | $r));
                }
                // Padding
                $padding = $rowSize - $width * $bytesPerPixel;
                if ($padding > 0) {
                    fwrite($fp, str_repeat("\x00", $padding));
                }
            }
            fclose($fp);
        } else {
            // Output directly to browser
            header('Content-Type: image/bmp');
            echo $header;
            for ($y = $height - 1; $y >= 0; $y--) {
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $b = ($rgb >> 0) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $r = ($rgb >> 16) & 0xFF;
                    echo pack('V', ($b << 16) | ($g << 8) | $r);
                }
                $padding = $rowSize - $width * $bytesPerPixel;
                if ($padding > 0) {
                    echo str_repeat("\x00", $padding);
                }
            }
        }
        return true;
    }
}

class PluginBarcodeQRcode {

   /**
    * Sanitize a string for safe use as a filename
    * Replaces spaces with underscores and removes invalid Windows filename characters
    *
    * @param string $name The filename to sanitize
    * @return string The sanitized filename
    */
   private static function sanitizeFilename($name) {
      // Replace spaces with underscores
      $name = str_replace(' ', '_', $name);
      // Remove invalid Windows filename characters: < > : " / \ | ? *
      $name = preg_replace('/[<>:"\/\\\\|?*]/', '', $name);
      // Remove trailing dots and spaces
      $name = rtrim($name, ' .');
      // Limit length (Windows max is 255 chars for full path)
      $name = substr($name, 0, 100);
      return $name;
   }

   function generateQRcode($itemtype, $items_id, $rand, $number, $data) {
      global $CFG_GLPI;

      /** @var CommonDBTM $item */
      $item = new $itemtype();
      $item->getFromDB($items_id);
      $itemByInvNumber = $item->fields['otherserial'] ?? '';
      $URLById = 'URL = ' . $CFG_GLPI['url_base'] . $itemtype::getFormURLWithID($items_id, false);
      $URLByInvNumber = 'URL = ' . $CFG_GLPI['url_base'] . '/plugins/barcode/front/checkItemByInv.php?inventoryNumber='. $itemByInvNumber . '&itemtype=' . $itemtype;
      $a_content = [];

      $b_content = [];
      $have_content = false;
      if ($data['serialnumber']) {
         if ($item->fields['serial'] ?? '' !== '') {
            $have_content = true;
         }
         $a_content[] = __("Serial number").' = '.($item->fields['serial'] ?? '');
         if ($data['displayserialnumber']) {
            $label = '';
            if ($data['displaylabels'] ?? false) {
               $label = __("Serial number").': ';
            }
            $b_content[] = $label.($item->fields['serial'] ?? '');
         }
      }
      if ($data['inventorynumber']) {
         if ($item->fields['otherserial'] ?? '' !== '') {
            $have_content = true;
         }
         $a_content[] = __('Inventory number').' = '.($item->fields['otherserial'] ?? '');
         if ($data['displayinventorynumber']) {
            $label = '';
            if ($data['displaylabels'] ?? false) {
               $label = __('Inventory number').': ';
            }
            $b_content[] = $label.($item->fields['otherserial'] ?? '');
         }
      }
      if ($data['id']) {
         if ($item->fields['id'] ?? '' !== '') {
            $have_content = true;
         }
         $a_content[] = __('ID').' = '.($item->fields['id'] ?? '');
         if ($data['displayid']) {
            $label = '';
            if ($data['displaylabels'] ?? false) {
               $label = __('ID').': ';
            }
            $b_content[] = $label.($item->fields['id'] ?? '');
         }
      }
      if (isset($data['uuid']) && $data['uuid']) {
         if (isset($item->fields['uuid']) && $item->fields['uuid'] !== '') {
            $have_content = true;
         }
         $a_content[] = __('UUID').' = '.($item->fields['uuid'] ?? '');
         if ($data['displayuuid']) {
            $label = '';
            if ($data['displaylabels'] ?? false) {
               $label = __('UUID').': ';
            }
            $b_content[] = $label.($item->fields['uuid'] ?? '');
         }
      }
      if ($data['name']) {
         if ($item->fields['name'] ?? '' !== '') {
            $have_content = true;
         }
         $a_content[] = __('Item Name').' = '.($item->fields['name'] ?? '');
         if ($data['displayname']) {
            $label = '';
            if ($data['displaylabels'] ?? false) {
               $label = __('Item Name').': ';
            }
            $b_content[] = $label.($item->fields['name'] ?? '');
         }
      }
      if ($data['url'] && !$item->no_form_page) {
         if ($data['inventorynumberURL']) {
            $a_content[] = $URLByInvNumber;
         } else {
            $a_content[] = $URLById;
         }
         if ($data['displayurl']) {
            $label = '';
            if ($data['displaylabels'] ?? false) {
               $label = __('Item URL').': ';
            }
            if ($data['inventorynumberURL']) {
               $b_content[] = $label.$URLByInvNumber;
            } else {
               $b_content[] = $label.$URLById;
            }
         }
      }
      if ($data['qrcodedate']) {
         $a_content[] = 'QRcode date = '.date('Y-m-d');
         if ($data['displayqrcodedate']) {
            $label = '';
            if ($data['displaylabels'] ?? false) {
               $label = __('Date').': ';
            }
            $b_content[] = $label.date('Y-m-d');
         }
      }

      if (count($a_content) > 0) {
         $codeContents = implode("\n", $a_content);
         QRcode::png($codeContents,
                     GLPI_PLUGIN_DOC_DIR.'/barcode/_tmp_'.$rand.'-'.$number.'.png',
                     QR_ECLEVEL_L,
                     4);
         return [GLPI_PLUGIN_DOC_DIR.'/barcode/_tmp_'.$rand.'-'.$number.'.png', $b_content];
      }
      return false;
   }


   function cleanQRcodefiles($rand, $number) {
      for ($i = 0; $i < $number; $i++) {
         $file = GLPI_PLUGIN_DOC_DIR.'/barcode/_tmp_'.$rand.'-'.$i.'.png';
         if (file_exists($file)) {
            unlink($file);
         }
      }
   }


   /**
    * Show the massive actions sub form for QR code generation with ZIP output
    *
    * @param MassiveAction $ma The massive action object
    * @return void
    */
   function showFormMassiveAction(MassiveAction $ma) {
      global $CFG_GLPI;

      $itemtype = $ma->getItemtype(false);
      $item = null;
      $fields = [];
      $no_form_page = true;

      if (is_a($itemtype, CommonDBTM::class, true)) {
         /** @var CommonDBTM $item */
         $item = new $itemtype();
         $item->getEmpty();
         $fields = array_keys($item->fields);
         $no_form_page = $item->no_form_page ?? true;
      }

      echo '<input type="hidden" name="type" value="QRcode" />';

      // Output form content
      echo '<div class="d-flex flex-column gap-3">';

      // Serial number option
      if (in_array('serial', $fields, true)) {
         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __("Serial number") . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("serialnumber", 1, -1, ['width' => '100%']);
         echo '</div></div>';
      } else {
         echo Html::hidden('serialnumber', ['value' => 0]);
      }

      // Display serial number
      if (in_array('serial', $fields, true)) {
         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __('Display serial number') . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("displayserialnumber", 0, -1, ['width' => '100%']);
         echo '</div></div>';
      }

      // Inventory number
      if (in_array('otherserial', $fields, true)) {
         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __('Inventory number') . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("inventorynumber", 0, -1, ['width' => '100%']);
         echo '</div></div>';
      } else {
         echo Html::hidden('inventorynumber', ['value' => 0]);
      }

      // Display inventory number
      if (in_array('otherserial', $fields, true)) {
         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __('Display inventory number') . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("displayinventorynumber", 0, -1, ['width' => '100%']);
         echo '</div></div>';
      }

      // ID
      echo '<div class="form-group row">';
      echo '<label class="col-sm-4 col-form-label">' . __('ID') . '</label>';
      echo '<div class="col-sm-8">';
      Dropdown::showYesNo("id", 0, -1, ['width' => '100%']);
      echo '</div></div>';

      // Display ID
      echo '<div class="form-group row">';
      echo '<label class="col-sm-4 col-form-label">' . __('Display ID') . '</label>';
      echo '<div class="col-sm-8">';
      Dropdown::showYesNo("displayid", 0, -1, ['width' => '100%']);
      echo '</div></div>';

      // UUID
      if (in_array('uuid', $fields, true)) {
         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __('UUID') . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("uuid", 0, -1, ['width' => '100%']);
         echo '</div></div>';

         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __('Display UUID') . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("displayuuid", 0, -1, ['width' => '100%']);
         echo '</div></div>';
      } else {
         echo Html::hidden('uuid', ['value' => 0]);
      }

      // Name
      if (in_array('name', $fields, true)) {
         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __('Name') . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("name", 1, -1, ['width' => '100%']);
         echo '</div></div>';

         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __('Display name') . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("displayname", 0, -1, ['width' => '100%']);
         echo '</div></div>';
      } else {
         echo Html::hidden('name', ['value' => 0]);
      }

      // URL by inventory number
      echo '<div class="form-group row">';
      echo '<label class="col-sm-4 col-form-label">' . __('URL by inventory number') . '</label>';
      echo '<div class="col-sm-8">';
      Dropdown::showYesNo("inventorynumberURL", 0, -1, ['width' => '100%']);
      echo '</div></div>';

      // URL
      if (!$no_form_page) {
         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __('Web page of the item') . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("url", 1, -1, ['width' => '100%']);
         echo '</div></div>';

         echo '<div class="form-group row">';
         echo '<label class="col-sm-4 col-form-label">' . __('Display web page of the item') . '</label>';
         echo '<div class="col-sm-8">';
         Dropdown::showYesNo("displayurl", 0, -1, ['width' => '100%']);
         echo '</div></div>';
      } else {
         echo Html::hidden('url', ['value' => 0]);
      }

      // Date QRcode
      echo '<div class="form-group row">';
      echo '<label class="col-sm-4 col-form-label">' . __('Date QRcode') . ' (' . date('Y-m-d') . ')</label>';
      echo '<div class="col-sm-8">';
      Dropdown::showYesNo("qrcodedate", 1, -1, ['width' => '100%']);
      echo '</div></div>';

      echo '<div class="form-group row">';
      echo '<label class="col-sm-4 col-form-label">' . __('Display date QRcode') . ' (' . date('Y-m-d') . ')</label>';
      echo '<div class="col-sm-8">';
      Dropdown::showYesNo("displayqrcodedate", 0, -1, ['width' => '100%']);
      echo '</div></div>';

      // QR code format option (for ZIP generation)
      echo '<div class="form-group row">';
      echo '<label class="col-sm-4 col-form-label">' . __('QR Code format') . '</label>';
      echo '<div class="col-sm-8">';
      Dropdown::showFromArray('qrcode_format', [
         'bmp' => __('BMP (for MS Access / Brother printer)'),
         'png' => __('PNG'),
         'jpg' => __('JPEG'),
      ], ['value' => 'bmp', 'width' => '100%']);
      echo '</div></div>';

      echo '<div class="alert alert-info">';
      echo __s('Note: When generating a ZIP archive, QR codes will be in BMP format for MS Access and Brother printer compatibility.');
      echo '</div>';

      echo '</div>'; // d-flex flex-column gap-3
   }


   /**
    * Get specific massive actions available for this object
    *
    * @param CommonDBTM|null $checkitem Check item
    * @return array
    */
   function getSpecificMassiveActions($checkitem = null) {
      $actions = [];

      if (is_null($checkitem)) {
         // Available from list view
         $actions[__CLASS__ . '::Generate'] = __('Generate QR codes (ZIP archive)', 'barcode');
      }

      return $actions;
   }


   /**
    * Show the massive actions sub form
    *
    * @since version 0.85
    * @see CommonDBTM::showMassiveActionsSubForm()
    * @param MassiveAction $ma The massive action object
    * @return bool True if the form was displayed
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      $action = $ma->getAction();

      if ($action === 'Generate') {
         $qrcode = new self();
         $qrcode->showFormMassiveAction($ma);

         // Add the submit button - CRITICAL for GLPI 11 massive action modal
         echo '<div class="mt-3 text-center">';
         echo Html::submit(_x('button', 'Post'), [
            'name'  => 'massiveaction',
            'icon'  => 'ti ti-device-floppy',
            'class' => 'btn btn-primary',
         ]);
         echo '</div>';

         return true;
      }

      return false;
   }


   /**
    * Process massive actions for one itemtype
    *
    * @param MassiveAction $ma The massive action object
    * @param CommonDBTM $item The item object
    * @param array $ids Array of item IDs to process
    * @return void
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      $action = $ma->getAction();

      if ($action !== 'Generate') {
         return;
      }

      set_time_limit(0);

      $qrcode = new self();
      $rand = mt_rand();
      $number = 0;
      $itemNames = [];
      $tmpPngFiles = [];
      $tmpBmpFiles = [];
      $zip = null;
      $zipOpen = false;
      $zipPath = null;
      $actionResult = MassiveAction::ACTION_OK;

      try {
         $barcodeDir = GLPI_PLUGIN_DOC_DIR . '/barcode';
         if (!is_dir($barcodeDir) || !is_writable($barcodeDir)) {
            throw new RuntimeException(__('Barcode temporary directory is not writable.', 'barcode'));
         }

         foreach ($ids as $key) {
            if (!$item->getFromDB($key)) {
               continue;
            }

            $itemName = $item->fields['name'] ?? '';
            $sanitizedName = self::sanitizeFilename($itemName);
            if ($sanitizedName === '') {
               $sanitizedName = $item->getType() . '_' . (int) $key;
            }

            $archiveName = $sanitizedName;
            $suffix = 2;
            while (in_array($archiveName, $itemNames, true)) {
               $archiveName = $sanitizedName . '_' . $suffix;
               $suffix++;
            }

            $values = $qrcode->generateQRcode($item->getType(), $key, $rand, $number, $ma->POST);
            if (!$values || empty($values[0])) {
               continue;
            }

            $pngFile = $values[0];
            if (!is_file($pngFile) || !is_readable($pngFile)) {
               continue;
            }

            $itemNames[] = $archiveName;
            $tmpPngFiles[] = $pngFile;
            $number++;
         }

         if ($number <= 0) {
            Session::addMessageAfterRedirect(__('No QR codes were generated.', 'barcode'), true, 'warning');
         } else {
            for ($i = 0; $i < $number; $i++) {
               $pngFile = $tmpPngFiles[$i];
               $bmpFile = $barcodeDir . '/_tmp_' . $rand . '-' . $i . '.bmp';

               if (!self::pngToBmp($pngFile, $bmpFile)) {
                  throw new RuntimeException(__('Unable to convert generated QR code to BMP.', 'barcode'));
               }

               $tmpBmpFiles[] = $bmpFile;
            }

            $zipFileName = 'barcode_qrcodes_user_' . Session::getLoginUserID() . '.zip';
            $zipPath = $barcodeDir . '/' . $zipFileName;

            if (is_file($zipPath)) {
               $lastModified = @filemtime($zipPath);
               if ($lastModified !== false && (time() - $lastModified) > 300) {
                  @unlink($zipPath);
               }
            }

            $zip = new ZipArchive();
            $zipStatus = $zip->open($zipPath, ZipArchive::CREATE);
            if ($zipStatus !== true) {
               throw new RuntimeException(__('Unable to create ZIP archive.', 'barcode'));
            }
            $zipOpen = true;

            foreach ($tmpBmpFiles as $index => $bmpFile) {
               if (!is_file($bmpFile) || !is_readable($bmpFile)) {
                  throw new RuntimeException(__('A generated BMP file is missing or unreadable.', 'barcode'));
               }

               $bmpFileName = ($itemNames[$index] ?? ('qrcode_' . ($index + 1))) . '.bmp';
               if (!$zip->addFile($bmpFile, $bmpFileName)) {
                  throw new RuntimeException(__('Unable to add a BMP file to the ZIP archive.', 'barcode'));
               }
            }

            if (!$zip->close()) {
               $zipOpen = false;
               throw new RuntimeException(__('Unable to finalize ZIP archive.', 'barcode'));
            }
            $zipOpen = false;

            $downloadUrl = '/plugins/barcode/front/send.php?file=' . urlencode(basename($zipPath));
            $msg = "<a href='" . htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8') . "' class='alert-link'>"
                 . sprintf(__('Download %s', 'barcode'), htmlspecialchars($zipFileName, ENT_QUOTES, 'UTF-8'))
                 . '</a>';
            Session::addMessageAfterRedirect($msg, true, 'information');
         }

      } catch (Throwable $e) {
         $actionResult = MassiveAction::ACTION_KO;
         Session::addMessageAfterRedirect(__('Error creating ZIP archive: ', 'barcode') . $e->getMessage(), true, 'error');

         if ($zipPath !== null && is_file($zipPath)) {
            @unlink($zipPath);
         }

      } finally {
         if ($zipOpen && $zip instanceof ZipArchive) {
            $zip->close();
         }

         foreach ($tmpBmpFiles as $bmpFile) {
            if (is_file($bmpFile)) {
               @unlink($bmpFile);
            }
         }

         foreach ($tmpPngFiles as $pngFile) {
            if (is_file($pngFile)) {
               @unlink($pngFile);
            }
         }

         if ($number > 0) {
            $qrcode->cleanQRcodefiles($rand, $number);
         }

         unset($tmpBmpFiles, $tmpPngFiles, $itemNames, $zip);
         if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
         }
      }

      $ma->itemDone($item->getType(), count($ids), $actionResult);
      return;
   }


   /**
    * Convert PNG image to BMP format
    *
    * @param string $pngFile Path to the source PNG file
    * @param string $bmpFile Path to the destination BMP file
    * @return bool True on success, false on failure
    */
   private static function pngToBmp($pngFile, $bmpFile) {
      if (!is_file($pngFile) || !is_readable($pngFile)) {
         return false;
      }

      $imageSize = @getimagesize($pngFile);
      if ($imageSize === false || count($imageSize) < 3) {
         return false;
      }

      list($width, $height, $type) = $imageSize;
      $width = (int) $width;
      $height = (int) $height;

      if ($type !== IMAGETYPE_PNG || $width <= 0 || $height <= 0) {
         return false;
      }

      // Refuse images that are unlikely to fit in memory before GD attempts to
      // decode them. This avoids fatal memory exhaustion on crafted/huge PNGs.
      $memoryLimit = trim((string) ini_get('memory_limit'));
      if ($memoryLimit !== '' && $memoryLimit !== '-1') {
         $unit = strtolower(substr($memoryLimit, -1));
         $memoryLimitBytes = (int) $memoryLimit;
         switch ($unit) {
            case 'g':
               $memoryLimitBytes *= 1024;
               // no break
            case 'm':
               $memoryLimitBytes *= 1024;
               // no break
            case 'k':
               $memoryLimitBytes *= 1024;
               break;
         }

         if ($memoryLimitBytes > 0) {
            $estimatedBytes = $width * $height * 8;
            if ($estimatedBytes > ($memoryLimitBytes - memory_get_usage(true))) {
               return false;
            }
         }
      }

      $srcImage = @imagecreatefrompng($pngFile);
      if (!$srcImage) {
         return false;
      }

      $isTrueColor = imageistruecolor($srcImage);
      $getPixel = static function ($image, $x, $y) use ($isTrueColor) {
         $pixel = imagecolorat($image, $x, $y);

         if ($isTrueColor) {
            $alpha = ($pixel & 0x7F000000) >> 24;
            $red = ($pixel >> 16) & 0xFF;
            $green = ($pixel >> 8) & 0xFF;
            $blue = $pixel & 0xFF;
         } else {
            $colors = imagecolorsforindex($image, $pixel);
            $alpha = $colors['alpha'];
            $red = $colors['red'];
            $green = $colors['green'];
            $blue = $colors['blue'];
         }

         if ($alpha > 0) {
            $opacity = 127 - $alpha;
            $red = (int) round((($red * $opacity) + (255 * $alpha)) / 127);
            $green = (int) round((($green * $opacity) + (255 * $alpha)) / 127);
            $blue = (int) round((($blue * $opacity) + (255 * $alpha)) / 127);
         }

         return [$red, $green, $blue];
      };

      // Find exact non-white bounds to match the previous white-margin crop
      // behavior without allocating another full-size image.
      $left = $width;
      $top = $height;
      $right = -1;
      $bottom = -1;
      for ($y = 0; $y < $height; $y++) {
         for ($x = 0; $x < $width; $x++) {
            list($red, $green, $blue) = $getPixel($srcImage, $x, $y);
            if ($red !== 255 || $green !== 255 || $blue !== 255) {
               if ($x < $left) {
                  $left = $x;
               }
               if ($x > $right) {
                  $right = $x;
               }
               if ($y < $top) {
                  $top = $y;
               }
               if ($y > $bottom) {
                  $bottom = $y;
               }
            }
         }
      }

      if ($right < $left || $bottom < $top) {
         $left = 0;
         $top = 0;
         $right = $width - 1;
         $bottom = $height - 1;
      }

      $bmpWidth = $right - $left + 1;
      $bmpHeight = $bottom - $top + 1;
      $rowSize = (($bmpWidth * 3) + 3) & ~3;
      $pixelDataSize = $rowSize * $bmpHeight;
      $fileSize = 54 + $pixelDataSize;

      if ($fileSize > 0xFFFFFFFF || $pixelDataSize > 0xFFFFFFFF) {
         imagedestroy($srcImage);
         return false;
      }

      $fp = @fopen($bmpFile, 'wb');
      if (!$fp) {
         imagedestroy($srcImage);
         return false;
      }

      $success = true;
      $header = 'BM'
         . pack('V', $fileSize)
         . pack('v', 0)
         . pack('v', 0)
         . pack('V', 54)
         . pack('V', 40)
         . pack('V', $bmpWidth)
         . pack('V', $bmpHeight)
         . pack('v', 1)
         . pack('v', 24)
         . pack('V', 0)
         . pack('V', $pixelDataSize)
         . pack('V', 2835)
         . pack('V', 2835)
         . pack('V', 0)
         . pack('V', 0);

      if (fwrite($fp, $header) !== strlen($header)) {
         $success = false;
      }

      $padding = str_repeat("\x00", $rowSize - ($bmpWidth * 3));
      for ($y = $bottom; $success && $y >= $top; $y--) {
         $row = '';
         for ($x = $left; $x <= $right; $x++) {
            list($red, $green, $blue) = $getPixel($srcImage, $x, $y);
            $row .= pack('C3', $blue, $green, $red);
         }
         $row .= $padding;

         if (fwrite($fp, $row) !== strlen($row)) {
            $success = false;
         }
      }

      if (!fclose($fp)) {
         $success = false;
      }

      imagedestroy($srcImage);

      if (!$success && is_file($bmpFile)) {
         @unlink($bmpFile);
      }

      return $success;
   }

}

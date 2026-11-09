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
   @since     2009

   ------------------------------------------------------------------------
 */

Session::checkRight('plugin_barcode_config', UPDATE);

$dir  = GLPI_PLUGIN_DOC_DIR . '/barcode';
$logo = $dir . '/logo.png';

if (isset($_POST['dropCache'])) {
   // Remove every generated file except the logo
   foreach (glob($dir . '/*') ?: [] as $file) {
      if (is_file($file) && basename($file) !== 'logo.png') {
         unlink($file);
      }
   }
   Session::addMessageAfterRedirect(__('The cache has been emptied.', 'barcode'));
} else if (isset($_POST['dropLogo'])) {
   if (is_file($logo)) {
      unlink($logo);
   }
   Session::addMessageAfterRedirect(__('The logo has been removed.', 'barcode'));
} else if (!empty($_FILES['logo']['name'])) {
   $upload  = $_FILES['logo'];
   $imgInfo = ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && is_uploaded_file($upload['tmp_name'])
      ? @getimagesize($upload['tmp_name'])
      : false;
   if ($imgInfo === false || $imgInfo[2] !== IMAGETYPE_PNG) {
      Session::addMessageAfterRedirect(__('The logo must be a PNG image.', 'barcode'), true, ERROR);
   } else if (!move_uploaded_file($upload['tmp_name'], $logo)) {
      Session::addMessageAfterRedirect(__('Unable to save the logo.', 'barcode'), true, ERROR);
   }
} else if (isset($_POST['type'])) {
   if (in_array($_POST['type'], PluginBarcodeBarcode::getBarcodeTypes(), true)) {
      $pbconf = new PluginBarcodeConfig();
      $pbconf->update(['id' => 1, 'type' => $_POST['type']]);
   }
}
Html::back();

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

// ----------------------------------------------------------------------
// Original Author of file: Vincent Mazzoni
// Purpose of file: save the page layout of one barcode type
// ----------------------------------------------------------------------

Session::checkRight('plugin_barcode_config', UPDATE);

if (isset($_POST['type'])) {
   $pbcconf = new PluginBarcodeConfig_Type();
   if ($res = array_keys($pbcconf->find(['type' => (string) $_POST['type']]))) {
      $input = ['id' => $res[0]];
      foreach (PluginBarcodeConfig_Type::INTEGER_FIELDS as $field) {
         if (isset($_POST[$field]) && is_numeric($_POST[$field])) {
            $input[$field] = max(0, (int) $_POST[$field]);
         }
      }
      if (array_key_exists($_POST['size'] ?? '', PluginBarcodeBarcode::getPageSizes())) {
         $input['size'] = $_POST['size'];
      }
      if (in_array($_POST['orientation'] ?? '', ['Portrait', 'Landscape'], true)) {
         $input['orientation'] = $_POST['orientation'];
      }
      $pbcconf->update($input);
   }
}
Html::back();

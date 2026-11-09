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

Session::checkRight('plugin_barcode_barcode', CREATE);

$barcode = new PluginBarcodeBarcode();

if (isset($_POST['generate'])) {
   $file = $barcode->printPDF([
      'code'        => $_POST['code'] ?? '',
      'nb'          => $_POST['nb'] ?? 1,
      'type'        => $_POST['type'] ?? '',
      'size'        => $_POST['size'] ?? null,
      'orientation' => $_POST['orientation'] ?? null,
      'border'      => $_POST['border'] ?? 0,
   ]);
   if ($file !== false) {
      Session::addMessageAfterRedirect(PluginBarcodeBarcode::getDownloadLink($file));
   }
   Html::back();
}

Html::header(PluginBarcodeBarcode::getMenuName(), '', 'tools', 'PluginBarcodeBarcode');
$barcode->showForm((string) ($_GET['code'] ?? ''));
Html::footer();

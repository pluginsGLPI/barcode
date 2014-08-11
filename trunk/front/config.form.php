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

include ('../../../inc/includes.php');

if (isset($_POST['dropCache'])) {
   $dir = GLPI_PLUGIN_DOC_DIR.'/barcode';
   if (is_dir($dir)) {
      if ($dh = opendir($dir)) {
         while (($file = readdir($dh)) !== false) {
            if ($file != "." && $file != ".." && $file != "logo.png") {
               unlink($dir.'/'.$file);
            }
         }
         closedir($dh);
      }
   }
   Session::addMessageAfterRedirect(__('The cache has been emptied.', 'barcode'));
} else if (!empty($_FILES['logo']['name'])) {
   if (is_file(GLPI_PLUGIN_DOC_DIR.'/barcode/logo.png')) {
      @unlink(GLPI_PLUGIN_DOC_DIR.'/barcode/logo.png');
   }
   // Move
   rename($_FILES['logo']['tmp_name'],GLPI_PLUGIN_DOC_DIR.'/barcode/logo.png');

} else if (isset($_POST['type'])) {
   $pbconf = new PluginBarcodeConfig();
   $_POST['id']=1;
   $pbconf->update($_POST);
}
Html::back();
?>

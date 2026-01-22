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
   @since     2009
   ------------------------------------------------------------------------
 */
// Non menu entry case
//header("Location:../../central.php");
include ('../../../inc/includes.php');
// To be available when plugin is not activated
Plugin::load('barcode');
if (!isset($_GET['itemtype']) || !isset($_GET['inventoryNumber'])) {
   Html::displayErrorAndDie(__('Invalid request'), true);
}
$itemtype = $_GET['itemtype'];
$item = new $itemtype();
$itemInventoryNumber[] = $_GET['inventoryNumber'];
foreach ($itemInventoryNumber as $key => $value) {
   if ($item->getFromDBByCrit(['otherserial' => $value])) {
      Html::header(__('Item Information', 'barcode'), $_SERVER['PHP_SELF'], "config", "plugins");
      echo "<h1>" . __('Item found', 'barcode') . "</h1>";
      echo "<p><strong>" . __('Name') . ":</strong> " . $item->getName() . "</p>";
      echo "<p><strong>" . __('Inventory number') . ":</strong> " . $item->getField('otherserial') . "</p>";
      echo "<p><strong>" . __('Type') . ":</strong> " . $itemtype . "</p>";
      // Agrega marca, modelo, RAM, almacenamiento, CPU
      if ($itemtype == 'Computer') {
         $manufacturer = Dropdown::getDropdownName('glpi_manufacturers', $item->getField('manufacturers_id'));
         $model = Dropdown::getDropdownName('glpi_computermodels', $item->getField('computermodels_id'));
         echo "<p><strong>" . __('Manufacturer') . ":</strong> " . $manufacturer . "</p>";
         echo "<p><strong>" . __('Model') . ":</strong> " . $model . "</p>";
         
         // RAM
         $ram = $DB->request([
            'SELECT' => ['SUM' => 'size AS total_ram'],
            'FROM' => 'glpi_items_devicememories',
            'WHERE' => ['items_id' => $item->getID(), 'itemtype' => 'Computer']
         ]);
         $total_ram = 0;
         if ($ram->count() > 0) {
            $total_ram = $ram->current()['total_ram'];
         }
         echo "<p><strong>" . __('RAM') . ":</strong> " . $total_ram . " MB</p>";
         
      }
      // Add more basic fields if needed, like location, status, etc.
      echo "<p><a href='" . $item->getFormURLWithID($item->getID()) . "' class='btn btn-primary'>" . __('View full details', 'barcode') . "</a></p>";
      Html::footer();
   } else {
      Html::displayErrorAndDie(__('No item found'), true);
   }
};






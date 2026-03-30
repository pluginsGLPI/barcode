<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI - Migrado a GLPI 11.x por Dawing S.A.S.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Session::checkRight('plugin_barcode_barcode', CREATE);

Html::header(
   __('Barcode', 'barcode'),
   $_SERVER['PHP_SELF'],
   'tools',
   'PluginBarcodeBarcode'
);

$pbBarcode = new PluginBarcodeBarcode();
$pbConfig  = new PluginBarcodeConfig();

echo "<div class='center'>";
echo "<h2>" . __('Barcode generation', 'barcode') . "</h2>";

echo "<form name='form' method='post' action='/plugins/barcode/front/barcode.form.php'>";

echo "<table class='tab_cadre_fixe'>";
echo "<tr class='tab_bg_1'>";
echo "<th colspan='4'>" . __('Manual generation', 'barcode') . "</th>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Code', 'barcode') . "</td><td>";
echo "<input type='text' size='30' name='code' value=''>";
echo "</td>";
echo "<td>" . __('Type', 'barcode') . "</td><td>";
$config = $pbConfig->getConfigType();
$pbConfig->showTypeSelect($config['type']);
echo "</td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Page size', 'barcode') . "</td><td>";
$pbBarcode->showSizeSelect($config['size']);
echo "</td>";
echo "<td>" . __('Orientation', 'barcode') . "</td><td>";
$pbBarcode->showOrientationSelect($config['orientation']);
echo "</td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Number of copies', 'barcode') . "</td>";
echo "<td><input type='text' size='5' name='nb' value='1'></td>";
echo "<td>" . __('Not use first xx barcodes', 'barcode') . "</td><td>";
Dropdown::showNumber("eliminate", ['width' => '100']);
echo "</td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Display border', 'barcode') . "</td><td>";
Dropdown::showYesNo("border", 1, -1, ['width' => '100']);
echo "</td>";
echo "<td>" . __('Display labels', 'barcode') . "</td><td>";
Dropdown::showYesNo("displaylabels", 0, -1, ['width' => '100']);
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td colspan='4' align='center'>";
echo Html::submit(__('Create', 'barcode'), ['name' => 'generate']);
echo "</td>";
echo "</tr>";

echo "</table>";
Html::closeForm();
echo "</div>";

Html::footer();

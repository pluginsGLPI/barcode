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

// Define actions :
function plugin_barcode_MassiveActions($itemtype) {

   $generate_barcode_action = 'PluginBarcodeBarcode' . MassiveAction::CLASS_ACTION_SEPARATOR . 'Generate';
   $generate_barcode_label  = '<i class="ti ti-barcode"></i> ' . __('Barcode', 'barcode')." - ".__('Print barcodes', 'barcode');

   $generate_qrcode_action  = 'PluginBarcodeQRcode' . MassiveAction::CLASS_ACTION_SEPARATOR . 'Generate';
   $generate_qrcode_label   = '<i class="ti ti-qrcode"></i> ' . __('Barcode', 'barcode')." - ".__('Print QRcodes', 'barcode');

   if (!is_a($itemtype, CommonDBTM::class, true)
       || !Session::haveRight('plugin_barcode_barcode', CREATE)) {
      return [];
   }

   $actions = [
      // QR code is always available as it contains ID field value
      $generate_qrcode_action => $generate_qrcode_label,
   ];

   if (is_a($itemtype, CommonITILObject::class, true)) {
      // CommonITILObject specific case, barcode is generated based on ticket ID
      $actions[$generate_barcode_action] = $generate_barcode_label;
   }

   /** @var CommonDBTM $item */
   $item = new $itemtype();
   if ($item->isField('otherserial')) {
      // Barcode is based on otherserial field value
      $actions[$generate_barcode_action] = $generate_barcode_label;
   }

   return $actions;
}



// Install / update process for plugin : need to return true if succeeded
function plugin_barcode_install() {
   global $DB;

   $migration = new Migration(PLUGIN_BARCODE_VERSION);

   $default_charset   = DBConnection::getDefaultCharset();
   $default_collation = DBConnection::getDefaultCollation();
   $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();

   if (!is_dir(GLPI_PLUGIN_DOC_DIR . '/barcode')) {
      mkdir(GLPI_PLUGIN_DOC_DIR . '/barcode');
   }

   // Default barcode type
   $migration->renameTable('glpi_plugin_barcode_config', 'glpi_plugin_barcode_configs');
   if (!$DB->tableExists('glpi_plugin_barcode_configs')) {
      $DB->doQuery("CREATE TABLE `glpi_plugin_barcode_configs` (
                     `id` int {$default_key_sign} NOT NULL auto_increment,
                     `type` varchar(20) default NULL,
                     PRIMARY KEY  (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;");
      $DB->insert('glpi_plugin_barcode_configs', ['id' => 1, 'type' => 'code128']);
   } else {
      $migration->changeField('glpi_plugin_barcode_configs', 'id', 'id', "int {$default_key_sign} NOT NULL AUTO_INCREMENT");
      // QR codes are exported as ZIP and cannot be the default barcode type
      $migration->addPostQuery($DB->buildUpdate(
         'glpi_plugin_barcode_configs',
         ['type' => 'code128'],
         ['type' => 'QRcode']
      ));
   }

   // Page layout per barcode type
   $migration->renameTable('glpi_plugin_barcode_config_type', 'glpi_plugin_barcode_configs_types');
   if (!$DB->tableExists('glpi_plugin_barcode_configs_types')) {
      $DB->doQuery("CREATE TABLE `glpi_plugin_barcode_configs_types` (
                     `id` int {$default_key_sign} NOT NULL auto_increment,
                     `type` varchar(20) default NULL,
                     `size` varchar(20) default NULL,
                     `orientation` varchar(9) default NULL,
                     `marginTop` int NULL,
                     `marginBottom` int NULL,
                     `marginLeft` int NULL,
                     `marginRight` int NULL,
                     `marginHorizontal` int NULL,
                     `marginVertical` int NULL,
                     `maxCodeWidth` int NULL,
                     `maxCodeHeight` int NULL,
                     `txtSize` int NULL,
                     `txtSpacing` int NULL,
                     PRIMARY KEY  (`id`),
                     UNIQUE  (`type`)
                  ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;");
   } else {
      $migration->changeField('glpi_plugin_barcode_configs_types', 'id', 'id', "int {$default_key_sign} NOT NULL AUTO_INCREMENT");
      $migration->addField('glpi_plugin_barcode_configs_types', 'txtSize', 'integer');
      $migration->addField('glpi_plugin_barcode_configs_types', 'txtSpacing', 'integer');
   }
   $migration->migrationOneTable('glpi_plugin_barcode_configs_types');

   $defaults = [
      'size' => 'A4', 'orientation' => 'Portrait',
      'marginTop' => 30, 'marginBottom' => 30, 'marginLeft' => 30, 'marginRight' => 30,
      'marginHorizontal' => 25, 'marginVertical' => 30,
      'maxCodeWidth' => 110, 'maxCodeHeight' => 70, 'txtSize' => 8, 'txtSpacing' => 3,
   ];
   $specific = ['Code39' => ['maxCodeWidth' => 128, 'maxCodeHeight' => 50]];
   include_once Plugin::getPhpDir('barcode') . '/inc/barcode.class.php';
   foreach (PluginBarcodeBarcode::getBarcodeTypes() as $type) {
      if (!countElementsInTable('glpi_plugin_barcode_configs_types', ['type' => $type])) {
         $DB->insert(
            'glpi_plugin_barcode_configs_types',
            ['type' => $type] + ($specific[$type] ?? []) + $defaults
         );
      }
   }

   // Rights stored in a plugin table before GLPI 0.85
   $migration->dropTable('glpi_plugin_barcode_profiles');

   $migration->executeMigration();

   include_once Plugin::getPhpDir('barcode') . '/inc/profile.class.php';
   PluginBarcodeProfile::initProfile();

   return true;
}



// Uninstall process for plugin : need to return true if succeeded
function plugin_barcode_uninstall() {
   global $DB;

   foreach (['glpi_plugin_barcode_configs', 'glpi_plugin_barcode_configs_types', 'glpi_plugin_barcode_profiles'] as $table) {
      $DB->dropTable($table, true);
   }

   include_once Plugin::getPhpDir('barcode') . '/inc/profile.class.php';
   PluginBarcodeProfile::removeRights();

   return true;
}

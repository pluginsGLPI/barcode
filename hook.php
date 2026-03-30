<?php

/*
   ------------------------------------------------------------------------
   Barcode - Plugin para GLPI
   Migrado a GLPI 11.x por Dawing S.A.S.
   Original: Copyright (C) 2009-2016 by the Barcode plugin Development Team.
   ------------------------------------------------------------------------
   LICENSE: AGPL License 3.0 or (at your option) any later version
   ------------------------------------------------------------------------
 */

/**
 * Define las acciones masivas disponibles por tipo de objeto
 */
function plugin_barcode_MassiveActions($itemtype) {

   $generate_barcode_action = 'PluginBarcodeBarcode' . MassiveAction::CLASS_ACTION_SEPARATOR . 'Generate';
   $generate_barcode_label  = '<i class="fas fa-barcode"></i> ' . __('Barcode', 'barcode') . " - " . __('Print barcodes', 'barcode');

   $generate_qrcode_action  = 'PluginBarcodeQRcode' . MassiveAction::CLASS_ACTION_SEPARATOR . 'Generate';
   $generate_qrcode_label   = '<i class="fas fa-qrcode"></i> ' . __('Barcode', 'barcode') . " - " . __('Print QRcodes', 'barcode');

   if (!is_a($itemtype, CommonDBTM::class, true)) {
      return [];
   }

   // QR code siempre disponible (contiene campo ID)
   $actions = [
      $generate_qrcode_action => $generate_qrcode_label,
   ];

   if (is_a($itemtype, CommonITILObject::class, true)) {
      // Para objetos ITIL, el barcode se genera con el ID del ticket
      $actions[$generate_barcode_action] = $generate_barcode_label;
   }

   /** @var CommonDBTM $item */
   $item = new $itemtype();
   $item->getEmpty();

   if (array_key_exists('otherserial', $item->fields)) {
      // Barcode basado en número de inventario (otherserial)
      $actions[$generate_barcode_action] = $generate_barcode_label;
   }

   return $actions;
}


/**
 * Instalación del plugin - debe retornar true si fue exitoso
 */
function plugin_barcode_install() {
   global $DB;

   $migration = new Migration(PLUGIN_BARCODE_VERSION);

   // Obtener charset y collation por defecto (API GLPI 11)
   $default_charset   = DBConnection::getDefaultCharset();
   $default_collation = DBConnection::getDefaultCollation();

   // Crear directorio de documentos del plugin si no existe
   if (!file_exists(GLPI_PLUGIN_DOC_DIR . "/barcode")) {
      @mkdir(GLPI_PLUGIN_DOC_DIR . "/barcode", 0750, true);
   }

   // Renombrar tabla legacy si existe
   $migration->renameTable("glpi_plugin_barcode_config", "glpi_plugin_barcode_configs");

   // Crear tabla de configuración general si no existe
   if (!$DB->tableExists("glpi_plugin_barcode_configs")) {
      $query = "CREATE TABLE `glpi_plugin_barcode_configs` (
                  `id`   int          NOT NULL AUTO_INCREMENT,
                  `type` varchar(20)  DEFAULT NULL,
                  PRIMARY KEY (`id`)
               ) ENGINE=InnoDB
                 DEFAULT CHARSET={$default_charset}
                 COLLATE={$default_collation}
                 ROW_FORMAT=DYNAMIC;";
      $DB->doQueryOrDie($query, "Error al crear glpi_plugin_barcode_configs");

      $DB->doQueryOrDie(
         "INSERT INTO `glpi_plugin_barcode_configs` (`id`, `type`) VALUES (1, 'code128')",
         "Error al poblar glpi_plugin_barcode_configs"
      );
   }

   // Renombrar tabla de tipos legacy si existe
   $migration->renameTable("glpi_plugin_barcode_config_type", "glpi_plugin_barcode_configs_types");

   // Crear tabla de configuración por tipo si no existe
   if (!$DB->tableExists("glpi_plugin_barcode_configs_types")) {
      $query = "CREATE TABLE `glpi_plugin_barcode_configs_types` (
                  `id`                int          NOT NULL AUTO_INCREMENT,
                  `type`              varchar(20)  DEFAULT NULL,
                  `size`              varchar(20)  DEFAULT NULL,
                  `orientation`       varchar(9)   DEFAULT NULL,
                  `marginTop`         int          DEFAULT NULL,
                  `marginBottom`      int          DEFAULT NULL,
                  `marginLeft`        int          DEFAULT NULL,
                  `marginRight`       int          DEFAULT NULL,
                  `marginHorizontal`  int          DEFAULT NULL,
                  `marginVertical`    int          DEFAULT NULL,
                  `maxCodeWidth`      int          DEFAULT NULL,
                  `maxCodeHeight`     int          DEFAULT NULL,
                  `txtSize`           int          DEFAULT NULL,
                  `txtSpacing`        int          DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `type` (`type`)
               ) ENGINE=InnoDB
                 DEFAULT CHARSET={$default_charset}
                 COLLATE={$default_collation}
                 ROW_FORMAT=DYNAMIC;";
      $DB->doQueryOrDie($query, "Error al crear glpi_plugin_barcode_configs_types");

      $insert_query = "INSERT INTO `glpi_plugin_barcode_configs_types`
                         (`type`, `size`, `orientation`,
                          `marginTop`, `marginBottom`, `marginLeft`, `marginRight`,
                          `marginHorizontal`, `marginVertical`,
                          `maxCodeWidth`, `maxCodeHeight`,
                          `txtSize`, `txtSpacing`)
                       VALUES
                         ('Code39',  'A4', 'Portrait', 30, 30, 30, 30, 25, 30, 128, 50,  8, 3),
                         ('code128', 'A4', 'Portrait', 30, 30, 30, 30, 25, 30, 110, 70,  8, 3),
                         ('ean13',   'A4', 'Portrait', 30, 30, 30, 30, 25, 30, 110, 70,  8, 3),
                         ('int25',   'A4', 'Portrait', 30, 30, 30, 30, 25, 30, 110, 70,  8, 3),
                         ('postnet', 'A4', 'Portrait', 30, 30, 30, 30, 25, 30, 110, 70,  8, 3),
                         ('upca',    'A4', 'Portrait', 30, 30, 30, 30, 25, 30, 110, 70,  8, 3),
                         ('QRcode',  'A4', 'Portrait', 30, 30, 30, 30, 25, 30, 110, 100, 8, 3)";
      $DB->doQueryOrDie($insert_query, "Error al poblar glpi_plugin_barcode_configs_types");
   }

   // Agregar campos faltantes si la tabla ya existía sin ellos (migración de versión anterior)
   if ($DB->tableExists("glpi_plugin_barcode_configs_types")
      && !$DB->fieldExists("glpi_plugin_barcode_configs_types", "txtSize")
      && !$DB->fieldExists("glpi_plugin_barcode_configs_types", "txtSpacing")) {
      $migration->addField("glpi_plugin_barcode_configs_types", "txtSize",    "integer");
      $migration->addField("glpi_plugin_barcode_configs_types", "txtSpacing", "integer");
      $migration->executeMigration();
   }

   // Insertar configuración de QRcode si no existe
   if (!countElementsInTable("glpi_plugin_barcode_configs_types", ['type' => 'QRcode'])) {
      $DB->doQueryOrDie(
         "INSERT INTO `glpi_plugin_barcode_configs_types`
            (`type`, `size`, `orientation`,
             `marginTop`, `marginBottom`, `marginLeft`, `marginRight`,
             `marginHorizontal`, `marginVertical`,
             `maxCodeWidth`, `maxCodeHeight`, `txtSize`, `txtSpacing`)
          VALUES
            ('QRcode', 'A4', 'Portrait', 30, 30, 30, 30, 25, 30, 110, 100, 8, 3)",
         "Error al insertar configuración QRcode"
      );
   }

   // Inicializar perfiles de derechos
   include_once Plugin::getPhpDir('barcode') . '/inc/profile.class.php';
   include_once Plugin::getPhpDir('barcode') . '/inc/config.class.php';
   PluginBarcodeProfile::initProfile();

   // Eliminar tabla de perfiles legacy si existe
   if ($DB->tableExists("glpi_plugin_barcode_profiles")) {
      $DB->doQueryOrDie(
         "DROP TABLE `glpi_plugin_barcode_profiles`",
         "Error al eliminar glpi_plugin_barcode_profiles"
      );
   }

   return true;
}


/**
 * Desinstalación del plugin - debe retornar true si fue exitoso
 */
function plugin_barcode_uninstall() {
   global $DB;

   $tables = [
      'glpi_plugin_barcode_configs',
      'glpi_plugin_barcode_configs_types',
      'glpi_plugin_barcode_profiles',
   ];

   foreach ($tables as $table) {
      if ($DB->tableExists($table)) {
         $DB->doQueryOrDie(
            "DROP TABLE `{$table}`",
            "Error al eliminar {$table}"
         );
      }
   }

   include_once Plugin::getPhpDir('barcode') . '/inc/profile.class.php';
   PluginBarcodeProfile::removeRights();

   return true;
}

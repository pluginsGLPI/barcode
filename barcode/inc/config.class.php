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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginBarcodeConfig extends CommonDBTM {

   static $rightname = 'plugin_barcode_config';

   /**
    * Nombre del tipo
    */
   static function getTypeName($nb = 0) {
      return __('Barcode configuration', 'barcode');
   }

   /**
    * Formulario de configuración general + configuración por tipo
    *
    * @param  int   $ID      ID del registro (no usado actualmente)
    * @param  array $options Opciones adicionales
    */
   function showForm($ID, array $options = []) {

      $pbBarcode   = new PluginBarcodeBarcode();
      $defaultType = $this->getConfig();

      // Ruta base del plugin compatible con GLPI 11
      $action_url = '/plugins/barcode/front/config.form.php';

      echo "<form name='form' method='post'
                  action='" . htmlspecialchars($action_url) . "'
                   enctype='multipart/form-data'>";

      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>" . __('Barcode plugin configuration', 'barcode') . "</th></tr>";
      echo "</table><br>";

      // --- Configuración general ---
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>" . __('General configuration', 'barcode') . "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Type', 'barcode') . "</td>";
      echo "<td>";
      $this->showTypeSelect($defaultType);
      echo "</td>";
      echo "<td colspan='2'>";
      echo Html::submit(__('Save'), ['name' => 'update_type']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='tab_bg_1' colspan='4' align='center'>";
      echo Html::submit(__('Empty the cache', 'barcode'), ['name' => 'dropCache']);
      echo "</td>";
      echo "</tr>";

      // --- Logo de empresa ---
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>" . __('Company logo', 'barcode') . "</th>";
      echo "</tr>";

      if (file_exists(GLPI_PLUGIN_DOC_DIR . '/barcode/logo.png')) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='4' align='center'>";
         // Ruta compatible GLPI 11 - sin Plugin::getWebDir()
         echo "<img src='/plugins/barcode/front/document.send.php?file=barcode/logo.png'
               width='300'/>";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td class='tab_bg_1' colspan='4' align='center'>";
         echo Html::submit(__('Delete the logo', 'barcode'), ['name' => 'dropLogo']);
         echo "</td>";
         echo "</tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' align='center'><input type='file' name='logo' value='' /></td>";
      echo "<td colspan='2'>";
      echo Html::submit(__('Save'), ['name' => 'upload_logo']);
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      echo "</div>";
      Html::closeForm();

      // --- Formulario por cada tipo de código ---
      foreach ($pbBarcode->getCodeTypes() as $type) {
         echo '<br>';
         $this->showFormConfigType($type);
      }
   }

   /**
    * Obtiene el tipo de código predeterminado desde la BD
    *
    * @return string  Tipo de código (p.ej. 'code128')
    */
   function getConfig() {
      $pbconf = new PluginBarcodeConfig();
      if ($pbconf->getFromDB(1)) {
         return $pbconf->fields['type'];
      }
      return 'code128';
   }

   /**
    * Formulario de configuración para un tipo específico de código
    *
    * @param  string|null $p_type  Tipo de código (Code39, code128, QRcode, etc.)
    */
   function showFormConfigType($p_type = null) {

      $pbBarcode = new PluginBarcodeBarcode();

      if (is_null($p_type)) {
         $type = $this->getConfig();
      } else {
         $type = $p_type;
      }

      $config = $this->getConfigType($type);

      $action_url = '/plugins/barcode/front/config_type.form.php';

      echo "<form name='form' method='post'
            action='" . htmlspecialchars($action_url) . "'>";
      echo "<input type='hidden' name='type' value='" . htmlspecialchars($type) . "'>";
      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='4'>" . htmlspecialchars($type) . "</th></tr>";

      // Tamaño y orientación de página
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Page size', 'barcode') . "</td><td>";
      $pbBarcode->showSizeSelect($config['size']);
      echo "</td>";
      echo "<td>" . __('Orientation', 'barcode') . "</td><td>";
      $pbBarcode->showOrientationSelect($config['orientation']);
      echo "</td>";
      echo "</tr>";

      // Márgenes externos
      echo "<tr><th colspan='4'>" . __('Margins', 'barcode') . "</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Top', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='marginTop'    value='" . (int)$config['marginTop']    . "'>";
      echo "</td>";
      echo "<td>" . __('Bottom', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='marginBottom' value='" . (int)$config['marginBottom'] . "'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Left', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='marginLeft'  value='" . (int)$config['marginLeft']  . "'>";
      echo "</td>";
      echo "<td>" . __('Right', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='marginRight' value='" . (int)$config['marginRight'] . "'>";
      echo "</td>";
      echo "</tr>";

      // Márgenes internos
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Inner horizontal', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='marginHorizontal' value='" . (int)$config['marginHorizontal'] . "'>";
      echo "</td>";
      echo "<td>" . __('Inner vertical', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='marginVertical'   value='" . (int)$config['marginVertical']   . "'>";
      echo "</td>";
      echo "</tr>";

      // Dimensiones del código
      echo "<tr><th colspan='4'>" . __('Barcodes sizes', 'barcode') . "</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Maximum width', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='maxCodeWidth'  value='" . (int)$config['maxCodeWidth']  . "'>";
      echo "</td>";
      echo "<td>" . __('Maximum height', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='maxCodeHeight' value='" . (int)$config['maxCodeHeight'] . "'>";
      echo "</td>";
      echo "</tr>";

      // Opciones de texto bajo el código
      echo "<tr><th colspan='4'>" . __('Text display options', 'barcode') . "</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Text size', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='txtSize'    value='" . (int)$config['txtSize']    . "'>";
      echo "</td>";
      echo "<td>" . __('Text spacing between lines', 'barcode') . "</td><td>";
      echo "<input type='text' size='10' name='txtSpacing' value='" . (int)$config['txtSpacing'] . "'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td class='tab_bg_1' colspan='4' align='center'>";
      echo Html::submit(__('Save'), ['name' => 'update']);
      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }

   /**
    * Obtiene la configuración de un tipo de código específico
    *
    * @param  string|null $p_type  Tipo de código
    * @return array                Array con todos los parámetros de configuración
    */
   function getConfigType($p_type = null) {
      if (is_null($p_type)) {
         $p_type = $this->getConfig();
      }

      $pbcconf = new PluginBarcodeConfig_Type();

      // API GLPI 11: usar find() con array estructurado
      $results = $pbcconf->find(['type' => $p_type]);

      if (!empty($results)) {
         $id = array_key_first($results);
         $pbcconf->getFromDB($id);
         return [
            'type'              => $pbcconf->fields['type'],
            'size'              => $pbcconf->fields['size'],
            'orientation'       => $pbcconf->fields['orientation'],
            'marginTop'         => (int)$pbcconf->fields['marginTop'],
            'marginBottom'      => (int)$pbcconf->fields['marginBottom'],
            'marginLeft'        => (int)$pbcconf->fields['marginLeft'],
            'marginRight'       => (int)$pbcconf->fields['marginRight'],
            'marginHorizontal'  => (int)$pbcconf->fields['marginHorizontal'],
            'marginVertical'    => (int)$pbcconf->fields['marginVertical'],
            'maxCodeWidth'      => (int)$pbcconf->fields['maxCodeWidth'],
            'maxCodeHeight'     => (int)$pbcconf->fields['maxCodeHeight'],
            'txtSize'           => (int)$pbcconf->fields['txtSize'],
            'txtSpacing'        => (int)$pbcconf->fields['txtSpacing'],
         ];
      }

      // Valores por defecto si no hay configuración en BD
      return [
         'type'              => 'code128',
         'size'              => 'A4',
         'orientation'       => 'Portrait',
         'marginTop'         => 30,
         'marginBottom'      => 30,
         'marginLeft'        => 30,
         'marginRight'       => 30,
         'marginHorizontal'  => 25,
         'marginVertical'    => 30,
         'maxCodeWidth'      => 110,
         'maxCodeHeight'     => 70,
         'txtSize'           => 8,
         'txtSpacing'        => 3,
      ];
   }

   /**
    * Muestra un selector de tipo de código
    *
    * @param  string|null $p_type  Valor seleccionado actualmente
    * @param  array       $used    Tipos a excluir del dropdown
    */
   function showTypeSelect($p_type = null, $used = []) {
      $options = [
         'width' => '100',
         'used'  => $used,
      ];
      if (!is_null($p_type)) {
         $options['value'] = $p_type;
      }
      Dropdown::showFromArray(
         "type",
         [
            'Code39'    => __('code39',  'barcode'),
            'code128'   => __('code128', 'barcode'),
            'ean13'     => __('ean13',   'barcode'),
            'int25'     => __('int25',   'barcode'),
            'postnet'   => __('postnet', 'barcode'),
            'upca'      => __('upca',    'barcode'),
            'QRcode'    => __('QRcode',  'barcode'),
         ],
         $options
      );
   }
}

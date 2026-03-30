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

/**
 * Clase principal para generar códigos de barras (tipos lineales)
 * y exportar PDFs usando rospdf/Cezpdf
 */
class PluginBarcodeBarcode {

   /** @var string Ruta al directorio de documentos del plugin */
   private $docsPath;

   static $rightname = 'plugin_barcode_barcode';

   /**
    * Constructor
    */
   function __construct() {
      $this->docsPath = GLPI_PLUGIN_DOC_DIR . '/barcode/';
   }

   /**
    * Retorna la lista de tipos de código disponibles
    *
    * @return array
    */
   function getCodeTypes() {
      return ['Code39', 'code128', 'ean13', 'int25', 'postnet', 'upca', 'QRcode'];
   }

   /**
    * Muestra un selector de tamaño de página
    *
    * @param  string|null $p_size  Valor por defecto
    */
   function showSizeSelect($p_size = null) {
      $sizes = [
         '4A0' => '4A0', '2A0' => '2A0',
         'A0'  => 'A0',  'A1'  => 'A1',  'A2'  => 'A2',
         'A3'  => 'A3',  'A4'  => 'A4',  'A5'  => 'A5',
         'A6'  => 'A6',  'A7'  => 'A7',  'A8'  => 'A8',
         'A9'  => 'A9',  'A10' => 'A10',
         'B0'  => 'B0',  'B1'  => 'B1',  'B2'  => 'B2',
         'B3'  => 'B3',  'B4'  => 'B4',  'B5'  => 'B5',
         'B6'  => 'B6',  'B7'  => 'B7',  'B8'  => 'B8',
         'B9'  => 'B9',  'B10' => 'B10',
         'C0'  => 'C0',  'C1'  => 'C1',  'C2'  => 'C2',
         'C3'  => 'C3',  'C4'  => 'C4',  'C5'  => 'C5',
         'C6'  => 'C6',  'C7'  => 'C7',  'C8'  => 'C8',
         'C9'  => 'C9',  'C10' => 'C10',
         'RA0' => 'RA0', 'RA1' => 'RA1', 'RA2' => 'RA2',
         'RA3' => 'RA3', 'RA4' => 'RA4',
         'SRA0' => 'SRA0', 'SRA1' => 'SRA1', 'SRA2' => 'SRA2',
         'SRA3' => 'SRA3', 'SRA4' => 'SRA4',
         'LETTER'    => 'LETTER',
         'LEGAL'     => 'LEGAL',
         'EXECUTIVE' => 'EXECUTIVE',
         'FOLIO'     => 'FOLIO',
      ];

      $opts = ['width' => '100'];
      if (!is_null($p_size)) {
         $opts['value'] = $p_size;
      }
      Dropdown::showFromArray("size", $sizes, $opts);
   }

   /**
    * Muestra un selector de orientación de página
    *
    * @param  string|null $p_orientation  Valor por defecto
    */
   function showOrientationSelect($p_orientation = null) {
      $opts = ['width' => '100'];
      if (!is_null($p_orientation)) {
         $opts['value'] = $p_orientation;
      }
      Dropdown::showFromArray(
         "orientation",
         [
            'Portrait'  => __('Portrait',  'barcode'),
            'Landscape' => __('Landscape', 'barcode'),
         ],
         $opts
      );
   }

   /**
    * Muestra el formulario de generación de un código de barras individual
    *
    * @param  string $p_type  Tipo de objeto GLPI (ej: 'Computer')
    * @param  int    $p_ID    ID del objeto
    */
   function showForm($p_type, $p_ID) {

      $pbConfig = new PluginBarcodeConfig();
      $config   = $pbConfig->getConfigType();
      $ci       = new $p_type();
      $ci->getFromDB($p_ID);

      // Intentar obtener el número de inventario (otherserial)
      $code = ($ci->isField('otherserial')) ? $ci->getField('otherserial') : '';

      // Ruta compatible con GLPI 11
      $action_url = '/plugins/barcode/front/barcode.form.php';

      echo "<form name='form' method='post' action='" . htmlspecialchars($action_url) . "'>";
      echo "<div align='center'>";
      echo "<table class='tab_cadre'>";
      echo "<tr><th colspan='4'>" . __('Generation', 'barcode') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Code', 'barcode') . "</td><td>";
      echo "<input type='text' size='20' name='code' value='" . htmlspecialchars($code) . "'>";
      echo "</td>";
      echo "<td>" . __('Type', 'barcode') . "</td><td>";
      $pbConfig->showTypeSelect($config['type']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Page size', 'barcode') . "</td><td>";
      $this->showSizeSelect($config['size']);
      echo "</td>";
      echo "<td>" . __('Orientation', 'barcode') . "</td><td>";
      $this->showOrientationSelect($config['orientation']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Number of copies', 'barcode') . "</td>";
      echo "<td><input type='text' size='20' name='nb' value='1'></td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      echo "<tr><td class='tab_bg_1' colspan='4' align='center'>";
      echo Html::submit(__('Create', 'barcode'), ['name' => 'generate']);
      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }

   /**
    * Muestra el formulario de acción masiva para barcodes lineales
    *
    * @param  MassiveAction $ma  Objeto de acción masiva
    */
   function showFormMassiveAction(MassiveAction $ma) {

      $pbConfig = new PluginBarcodeConfig();

      echo '<center>';
      echo '<strong>';
      echo __('It will generate only elements have defined field:', 'barcode') . ' ';
      if (key($ma->items) == 'Ticket') {
         echo __('Ticket number', 'barcode');
      } else {
         echo __('Inventory number');
      }
      echo '</strong>';
      echo '<table>';
      echo '<tr>';
      echo '<td>';
      $config = $pbConfig->getConfigType();
      echo __('Type', 'barcode') . " : </td><td>";
      $pbConfig->showTypeSelect($config['type'], ['QRcode' => 'QRcode']);
      echo '</td>';
      echo '</tr>';
      echo '</table>';
      echo '<br/>';

      PluginBarcodeBarcode::commonShowMassiveAction();
   }

   /**
    * Muestra las opciones comunes de acción masiva (tamaño, orientación, opciones de display)
    * Reutilizado tanto por Barcode como por QRcode
    */
   static function commonShowMassiveAction() {

      $pbBarcode = new PluginBarcodeBarcode();
      $pbConfig  = new PluginBarcodeConfig();
      $config    = $pbConfig->getConfigType();

      echo '<table>';
      echo '<tr>';
      echo '<td>';
      echo "<br/>" . __('Page size', 'barcode') . " : </td><td>";
      $pbBarcode->showSizeSelect($config['size']);
      echo '</td>';
      echo '<td>';
      echo __('Not use first xx barcodes', 'barcode') . " : </td><td>";
      Dropdown::showNumber("eliminate", ['width' => '100']);
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>';
      echo "<br/>" . __('Orientation', 'barcode') . " : </td><td>";
      $pbBarcode->showOrientationSelect($config['orientation']);
      echo '</td>';
      echo '<td>';
      echo __('Display border', 'barcode') . " : </td><td>";
      Dropdown::showYesNo("border", 1, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';

      echo '<tr>';
      echo '<td>&nbsp;</td>';
      echo '<td>&nbsp;</td>';
      echo '<td>';
      echo __('Display labels', 'barcode') . " : </td><td>";
      Dropdown::showYesNo("displaylabels", 0, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';
      echo '</table>';
      echo '</center>';

      echo "<br/>";
      echo Html::submit(__('Create', 'barcode'), ['name' => 'massiveaction']);
   }

   /**
    * Genera el PDF con los códigos de barras y retorna la ruta relativa del archivo
    *
    * @param  array $p_params  Parámetros: type, size, orientation, codes, nb, border, displaylabels, displayData
    * @return string|int       Ruta relativa del PDF generado, o 0 si falla
    */
   function printPDF($p_params) {

      $pbConfig  = new PluginBarcodeConfig();
      $ext       = 'png';
      $type      = $p_params['type'];
      $size      = $p_params['size'];
      $orientation = $p_params['orientation'];
      $codes     = [];

      $displayDataCollection = $p_params['displayData'] ?? [];

      if ($type == 'QRcode') {
         $codes = $p_params['codes'];
      } else {
         if (isset($p_params['code'])) {
            $nb = isset($p_params['nb']) ? (int)$p_params['nb'] : 1;
            if ($nb > 1) {
               $this->create($p_params['code'], $type, $ext);
               for ($i = 1; $i <= $nb; $i++) {
                  $codes[] = $p_params['code'];
               }
            } else {
               if (!$this->create($p_params['code'], $type, $ext)) {
                  Session::addMessageAfterRedirect(
                     __('The generation of some barcodes produced errors.', 'barcode')
                  );
               }
               $codes[] = $p_params['code'];
            }
         } elseif (isset($p_params['codes'])) {
            $codes = $p_params['codes'];
            foreach ($codes as $code) {
               if ($code !== '') {
                  $this->create($code, $type, $ext);
               }
            }
         } else {
            return 0;
         }
      }

      // Obtener configuración dimensional del tipo
      $config    = $pbConfig->getConfigType($type);
      $width     = $config['maxCodeWidth'];
      $height    = $config['maxCodeHeight'];
      $marginH   = $config['marginHorizontal'];
      $marginV   = $config['marginVertical'];
      $txtSize   = $config['txtSize'];
      $txtSpacing = $config['txtSpacing'];

      // Crear el PDF con Cezpdf (rospdf/pdf-php)
      $pdf = new Cezpdf($size, $orientation);
      $pdf->tempPath = GLPI_TMP_DIR;
      $pdf->selectFont(Plugin::getPhpDir('barcode') . "/lib/ezpdf/fonts/Helvetica.afm");
      $pdf->ezSetMargins(
         $config['marginTop'],
         $config['marginBottom'],
         $config['marginLeft'],
         $config['marginRight']
      );
      $pdf->ezStartPageNumbers(
         $pdf->ez['pageWidth'] - 30,
         10, 10, 'left',
         '{PAGENUM} / {TOTALPAGENUM}'
      );

      // Detectar si hay logo y calcular alturas
      $heightimage = $height;
      $logoWidth   = 0;
      $logoHeight  = 0;
      $heightyposText = $height;

      if (file_exists(GLPI_PLUGIN_DOC_DIR . '/barcode/logo.png')) {
         $heightLogomax = 20;
         $imgSize       = getimagesize(GLPI_PLUGIN_DOC_DIR . '/barcode/logo.png');
         $logoWidth     = $imgSize[0];
         $logoHeight    = $imgSize[1];

         if ($logoHeight > $heightLogomax) {
            $ratio      = (100 * $heightLogomax) / $logoHeight;
            $logoHeight = $heightLogomax;
            $logoWidth  = $logoWidth * ($ratio / 100);
         }
         if ($logoWidth > $width) {
            $ratio      = (100 * $width) / $logoWidth;
            $logoWidth  = $width;
            $logoHeight = $logoHeight * ($ratio / 100);
         }
         $heightyposText = $height - $logoHeight;
         $heightimage    = $heightyposText;
      }

      // Posicionamiento y renderizado de cada código
      $first = true;
      $count = count($codes);
      for ($ia = 0; $ia < $count; $ia++) {
         $code        = $codes[$ia];
         $displayData = $displayDataCollection[$ia] ?? [];

         if ($first) {
            $x     = $pdf->ez['leftMargin'];
            $y     = $pdf->ez['pageHeight'] - $pdf->ez['topMargin'] - $height;
            $first = false;
         } else {
            if ($x + $width + $marginH > $pdf->ez['pageWidth']) {
               // Nueva fila
               $x = $pdf->ez['leftMargin'];
               if ($y - $height - $marginV < $pdf->ez['bottomMargin']) {
                  // Nueva página
                  $pdf->ezNewPage();
                  $y = $pdf->ez['pageHeight'] - $pdf->ez['topMargin'] - $height;
               } else {
                  $y -= $height + $marginV;
               }
            }
         }

         if ($code !== '') {
            $imgFile = ($type == 'QRcode')
               ? $code
               : $this->docsPath . $code . '_' . $type . '.' . $ext;

            if (file_exists($imgFile)) {
               $imgSize   = getimagesize($imgFile);
               $imgWidth  = $imgSize[0];
               $imgHeight = $imgSize[1];

               // Escalar manteniendo proporciones
               if ($imgWidth > $width) {
                  $ratio     = (100 * $width) / $imgWidth;
                  $imgWidth  = $width;
                  $imgHeight = $imgHeight * ($ratio / 100);
               }
               if ($imgHeight > $heightimage) {
                  $ratio     = (100 * $heightimage) / $imgHeight;
                  $imgHeight = $heightimage;
                  $imgWidth  = $imgWidth * ($ratio / 100);
               }

               $image = imagecreatefrompng($imgFile);

               $xOffset = ($imgWidth < $width) ? $x + (($width - $imgWidth) / 2) : $x;
               $pdf->addImage($image, $xOffset, $y, $imgWidth, $imgHeight);

               // Agregar logo si existe
               if (file_exists(GLPI_PLUGIN_DOC_DIR . '/barcode/logo.png')) {
                  $logoimg = imagecreatefrompng(GLPI_PLUGIN_DOC_DIR . '/barcode/logo.png');
                  $pdf->addImage(
                     $logoimg,
                     $x + (($width - $logoWidth) / 2),
                     $y + $heightyposText,
                     $logoWidth,
                     $logoHeight
                  );
               }

               // Agregar texto bajo el código
               $txtHeight = 0;
               $displayCount = is_array($displayData) ? count($displayData) : 0;
               for ($i = 0; $i < $displayCount; $i++) {
                  $pdf->addTextWrap(
                     $x,
                     $y - ($txtSpacing + $txtHeight),
                     $txtSize,
                     $displayData[$i],
                     $width,
                     'center'
                  );
                  $txtHeight += $txtSpacing / 2 + $txtSize;
               }

               // Dibujar borde si se solicitó
               if (!empty($p_params['border'])) {
                  $pdf->Rectangle(
                     $x,
                     $y - ($txtHeight + $txtSpacing * 2),
                     $width,
                     $height + ($txtHeight + $txtSpacing * 2)
                  );
               }
            }
         }

         $x += $width + $marginH;
      }

      // Guardar el PDF en disco
      $file    = $pdf->ezOutput();
      $pdfFile = $_SESSION['glpiID'] . '_' . $type . '.pdf';
      file_put_contents($this->docsPath . $pdfFile, $file);

      return '/files/_plugins/barcode/' . $pdfFile;
   }

   /**
    * Genera la imagen PNG de un código de barras lineal
    *
    * @param  string $p_code  Valor a codificar
    * @param  string $p_type  Tipo de código (Code39, code128, etc.)
    * @param  string $p_ext   Extensión de la imagen (png)
    * @return bool            true si se generó correctamente
    */
   function create($p_code, $p_type, $p_ext) {
      $filePath = $this->docsPath . $p_code . '_' . $p_type . '.' . $p_ext;

      // Si ya existe en caché, no regenerar
      if (file_exists($filePath)) {
         return true;
      }

      ob_start();
      $barcode = new Image_Barcode();
      $resImg  = @imagepng(
         @$barcode->draw($p_code, $p_type, $p_ext, false)
      );
      $img = ob_get_clean();

      file_put_contents($filePath, $img);

      return $resImg !== false;
   }

   /**
    * Retorna un array vacío (sin acciones masivas específicas en el item)
    */
   function getSpecificMassiveActions($checkitem = null) {
      return [];
   }

   /**
    * Muestra el subformulario de la acción masiva
    *
    * @param  MassiveAction $ma
    * @return bool
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      switch ($ma->getAction()) {
         case 'Generate':
            $barcode = new self();
            $barcode->showFormMassiveAction($ma);
            return true;
      }
      return false;
   }

   /**
    * Procesa la acción masiva de generación de códigos de barras para cada item
    *
    * @param  MassiveAction $ma
    * @param  CommonDBTM    $item
    * @param  array         $ids   IDs de los items seleccionados
    */
   static function processMassiveActionsForOneItemtype(
      MassiveAction $ma,
      CommonDBTM $item,
      array $ids
   ) {
      switch ($ma->getAction()) {

         case 'Generate':
            $pbQRcode = new PluginBarcodeQRcode();
            $rand     = mt_rand();
            $number   = 0;
            $codes    = [];

            // Agregar celdas vacías para desplazamiento si se solicitó
            $eliminate = (int)($ma->POST['eliminate'] ?? 0);
            for ($nb = 0; $nb < $eliminate; $nb++) {
               $codes[] = '';
            }

            foreach ($ids as $key) {
               $item->getFromDB($key);
               if (key($ma->items) === 'CommonITILObject' || is_a($item, CommonITILObject::class)) {
                  $codes[] = (string)$item->getField('id');
               } elseif ($item->isField('otherserial')) {
                  $codes[] = $item->getField('otherserial');
               }
            }

            if (count($codes) > 0) {
               $params = [
                  'codes'         => $codes,
                  'type'          => $ma->POST['type'],
                  'size'          => $ma->POST['size'],
                  'border'        => $ma->POST['border'],
                  'orientation'   => $ma->POST['orientation'],
                  'displaylabels' => $ma->POST['displaylabels'],
               ];

               $barcode  = new PluginBarcodeBarcode();
               $file     = $barcode->printPDF($params);
               $filePath = explode('/', $file);
               $filename = end($filePath);

               $msg = "<a href='/plugins/barcode/front/send.php?file=" . urlencode($filename) . "'>"
                    . __('Generated file', 'barcode') . "</a>";

               Session::addMessageAfterRedirect($msg);
               $pbQRcode->cleanQRcodefiles($rand, $number);
            }

            $ma->itemDone($item->getType(), 0, MassiveAction::ACTION_OK);
            return;
      }
   }
}

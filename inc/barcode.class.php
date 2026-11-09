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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class to generate barcodes using PEAR Image_Barcode
 **/
class PluginBarcodeBarcode {
   private $docsPath;

   static string $rightname = 'plugin_barcode_barcode';


   /**
     * Constructor
    **/
   function __construct() {
      $this->docsPath = GLPI_PLUGIN_DOC_DIR.'/barcode/';
   }


   /**
    * GLPI 10/11/12 menu integration
    */
   static function getMenuName() {
      return __('Barcode', 'barcode');
   }

   static function getMenuContent() {
      $menu = [];
      if (Session::haveRight('plugin_barcode_barcode', CREATE)) {
         $menu['title'] = self::getMenuName();
         $menu['page']  = '/plugins/barcode/front/barcode.form.php';
         $menu['icon']  = 'ti ti-barcode';
         if (Session::haveRight('plugin_barcode_config', UPDATE)) {
            $menu['links']['config'] = '/plugins/barcode/front/config.php';
         }
      }
      return $menu;
   }



   /**
    * Page sizes supported by Cezpdf
    *
    * @return array size => label
    */
   static function getPageSizes(): array {
      $sizes = [
         '4A0',
         '2A0',
         'A0',
         'A1',
         'A2',
         'A3',
         'A4',
         'A5',
         'A6',
         'A7',
         'A8',
         'A9',
         'A10',
         'B0',
         'B1',
         'B2',
         'B3',
         'B4',
         'B5',
         'B6',
         'B7',
         'B8',
         'B9',
         'B10',
         'C0',
         'C1',
         'C2',
         'C3',
         'C4',
         'C5',
         'C6',
         'C7',
         'C8',
         'C9',
         'C10',
         'RA0',
         'RA1',
         'RA2',
         'RA3',
         'RA4',
         'SRA0',
         'SRA1',
         'SRA2',
         'SRA3',
         'SRA4',
         'LETTER',
         'LEGAL',
         'EXECUTIVE',
         'FOLIO',
      ];
      return array_combine($sizes, $sizes);
   }



   function showSizeSelect($p_size = null) {
      Dropdown::showFromArray(
         "size",
         self::getPageSizes(),
         (is_null($p_size) ? ['width' => '100'] : ['value' => $p_size, 'width' => '100'])
      );
   }



   function showOrientationSelect($p_orientation = null) {
      //TODO : utiliser fonction du coeur

      Dropdown::showFromArray("orientation",
                              ['Portrait'  => __('Portrait', 'barcode'),
                                    'Landscape' => __('Landscape', 'barcode')],
                              (is_null($p_orientation)?['width' => '100']:['value' => $p_orientation, 'width' => '100']));
   }



   /**
    * Form to print one code on a sheet (Tools > Barcode)
    *
    * @param string $code default value of the code field
    */
   function showForm(string $code = '') {
      global $CFG_GLPI;

      $pbConfig = new PluginBarcodeConfig();
      $config   = $pbConfig->getConfigType();

      echo "<form name='form' method='post' action='"
         . htmlescape($CFG_GLPI['root_doc'] . '/plugins/barcode/front/barcode.form.php') . "'>";
      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>" . __s('Generation', 'barcode') . "</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __s('Code', 'barcode') . "</td><td>";
      echo "<input type='text' class='form-control' size='20' name='code' required value='" . htmlescape($code) . "'>";
      echo "</td>";
      echo "<td>" . __s('Type', 'barcode') . "</td><td>";
      $pbConfig->showTypeSelect($config['type'], ['QRcode' => 'QRcode']);
      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __s('Page size', 'barcode') . "</td><td>";
      $this->showSizeSelect($config['size']);
      echo "</td>";
      echo "<td>" . __s('Orientation', 'barcode') . "</td><td>";
      $this->showOrientationSelect($config['orientation']);
      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __s('Number of copies', 'barcode') . "</td><td>";
      Dropdown::showNumber('nb', ['value' => 1, 'min' => 1, 'max' => 200, 'width' => '100']);
      echo "</td>";
      echo "<td>" . __s('Display border', 'barcode') . "</td><td>";
      Dropdown::showYesNo('border', 1, -1, ['width' => '100']);
      echo "</td>";
      echo "</tr>";
      echo "<tr><td class='tab_bg_1 center' colspan='4'>";
      echo Html::submit(__('Create', 'barcode'), ['name' => 'generate']);
      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }



   function showFormMassiveAction(MassiveAction $ma) {

      $pbConfig = new PluginBarcodeConfig();

      echo '<center>';
      echo '<strong>';
      echo __('It will generate only elements have defined field:', 'barcode').' ';
      if (is_a($ma->getItemtype(false), CommonITILObject::class, true)) {
         echo __('Ticket number', 'barcode');
      } else {
         echo __('Inventory number');
      }
      echo '</strong>';
      echo '<table>';
      echo '<tr>';
      echo '<td>';
      $config = $pbConfig->getConfigType();
        echo __('Type', 'barcode')." : </td><td>";
        $pbConfig->showTypeSelect($config['type'], ['QRcode' => 'QRcode']);
      echo '</td>';
      echo '</tr>';
      echo '</table>';
      echo '<br/>';

      PluginBarcodeBarcode::commonShowMassiveAction();
   }



   static function commonShowMassiveAction() {

      $pbBarcode = new PluginBarcodeBarcode();
      $pbConfig  = new PluginBarcodeConfig();
      $config    = $pbConfig->getConfigType();

      echo '<table>';
      echo '<tr>';
      echo '<td>';
      echo "<br/>".__('Page size', 'barcode')." : </td><td>";
         $pbBarcode->showSizeSelect($config['size']);
      echo '</td>';
      echo '<td>';
      echo __('Not use first xx barcodes', 'barcode')." : </td><td>";
      Dropdown::showNumber("eliminate", ['width'=>'100']);
      echo '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>';
      echo "<br/>".__('Orientation', 'barcode')." : </td><td>";
         $pbBarcode->showOrientationSelect($config['orientation']);
      echo '</td>';
      echo '<td>';
      echo __('Display border', 'barcode')." : </td><td>";
      Dropdown::showYesNo("border", 1, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>&nbsp;</td>';
      echo '<td>&nbsp;</td>';
      echo '<td>';
      echo __('Display labels', 'barcode')." : </td><td>";
      Dropdown::showYesNo("displaylabels", 0, -1, ['width' => '100']);
      echo '</td>';
      echo '</tr>';
      echo '</table>';
      echo '</center>';

      //echo "<br/><input type='submit' value='".__('Create', 'barcode')."' class='submit'>";
      echo "<br/>";
      echo Html::submit(__('Create', 'barcode'), ['value' => 'create']);
   }



   /**
    * Barcode types rendered by PEAR Image_Barcode.
    * QR codes are handled by PluginBarcodeQRcode (ZIP export).
    *
    * @return string[]
    */
   static function getBarcodeTypes(): array {
      return ['Code39', 'code128', 'ean13', 'int25', 'postnet', 'upca'];
   }



   /**
    * Check that a value can be encoded with the given barcode type.
    *
    * @param string $code
    * @param string $type
    *
    * @return bool
    */
   static function isValidCode(string $code, string $type): bool {
      if ($code === '' || strlen($code) > 128) {
         return false;
      }
      switch ($type) {
         case 'Code39':
            return preg_match('/^[0-9A-Z\-+$%\/. ]+$/', $code) === 1;
         case 'code128':
            return preg_match('/^[\x20-\x7E]+$/', $code) === 1;
         case 'ean13':
            return preg_match('/^[0-9]{13}$/', $code) === 1;
         case 'upca':
            return preg_match('/^[0-9]{12}$/', $code) === 1;
         case 'int25':
         case 'postnet':
            return preg_match('/^[0-9]+$/', $code) === 1;
      }
      return false;
   }



   /**
    * Generate a PDF sheet of barcodes.
    *
    * @param array $p_params type, size, orientation, border, and either
    *                        'code' + 'nb' (one code printed nb times) or
    *                        'codes' (list, '' = empty cell) + optional
    *                        'displayData' (list of text lines per code)
    *
    * @return string|false name of the generated file (served by front/send.php)
    */
   function printPDF(array $p_params) {

      $pbConfig = new PluginBarcodeConfig();

      $type = $p_params['type'] ?? '';
      if (!in_array($type, self::getBarcodeTypes(), true)) {
         Session::addMessageAfterRedirect(__('Invalid barcode type.', 'barcode'), true, ERROR);
         return false;
      }

      if (isset($p_params['code'])) {
         $nb    = max(1, min(1000, (int) ($p_params['nb'] ?? 1)));
         $codes = array_fill(0, $nb, trim((string) $p_params['code']));
      } else {
         $codes = array_map(static fn ($c) => trim((string) $c), (array) ($p_params['codes'] ?? []));
      }
      $displayDataCollection = $p_params['displayData'] ?? [];

      // Generate (or reuse from cache) one image per distinct code
      $invalid = [];
      foreach (array_unique($codes) as $code) {
         if ($code !== '' && !$this->create($code, $type)) {
            $invalid[] = $code;
         }
      }
      if (count($invalid) > 0) {
         Session::addMessageAfterRedirect(
            sprintf(
               __('These values cannot be encoded as %1$s and were skipped: %2$s', 'barcode'),
               $type,
               htmlescape(implode(', ', $invalid))
            ),
            true,
            WARNING
         );
      }
      if (count(array_diff(array_unique($codes), $invalid, [''])) === 0) {
         Session::addMessageAfterRedirect(__('No barcode was generated.', 'barcode'), true, ERROR);
         return false;
      }

      $config      = $pbConfig->getConfigType($type);
      $size        = $p_params['size'] ?? $config['size'];
      $orientation = $p_params['orientation'] ?? $config['orientation'];
      if (!array_key_exists($size, self::getPageSizes())) {
         $size = 'A4';
      }
      if (!in_array($orientation, ['Portrait', 'Landscape'], true)) {
         $orientation = 'Portrait';
      }

      // create pdf
      // x is horizontal axis and y is vertical
      // x=0 and y=0 in bottom left hand corner
      $pdf = new Cezpdf($size, $orientation);
      $pdf->tempPath = GLPI_TMP_DIR;
      $pdf->selectFont(Plugin::getPhpDir('barcode') . "/vendor/rospdf/pdf-php/src/fonts/Helvetica.afm");
      $pdf->ezSetMargins($config['marginTop'], $config['marginBottom'], $config['marginLeft'], $config['marginRight']);
      $pdf->ezStartPageNumbers($pdf->ez['pageWidth'] - 30, 10, 10, 'left', '{PAGENUM} / {TOTALPAGENUM}');
      $width      = $config['maxCodeWidth'];
      $height     = $config['maxCodeHeight'];
      $marginH    = $config['marginHorizontal'];
      $marginV    = $config['marginVertical'];
      $txtSize    = $config['txtSize'];
      $txtSpacing = $config['txtSpacing'];
      $border     = !empty($p_params['border']);

      $heightimage = $height;

      $logoFile = GLPI_PLUGIN_DOC_DIR . '/barcode/logo.png';
      $logoimg  = false;
      if (is_file($logoFile) && ($imgSize = @getimagesize($logoFile)) !== false) {
         // Add logo to barcode
         $logoimg       = @imagecreatefrompng($logoFile);
         $heightLogomax = 20;
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

      $first = true;
      $x     = 0;
      $y     = 0;
      foreach (array_values($codes) as $ia => $code) {
         $displayData = $displayDataCollection[$ia] ?? [];
         if ($first) {
            $x     = $pdf->ez['leftMargin'];
            $y     = $pdf->ez['pageHeight'] - $pdf->ez['topMargin'] - $height;
            $first = false;
         } else if ($x + $width + $marginH > $pdf->ez['pageWidth']) { // new line
            $x = $pdf->ez['leftMargin'];
            if ($y - $height - $marginV < $pdf->ez['bottomMargin']) { // new page
               $pdf->ezNewPage();
               $y = $pdf->ez['pageHeight'] - $pdf->ez['topMargin'] - $height;
            } else {
               $y -= $height + $marginV;
            }
         }
         if ($code !== '' && !in_array($code, $invalid, true)) {
            $imgFile = $this->getCacheFile($code, $type);
            $image   = @imagecreatefrompng($imgFile);
            if ($image !== false) {
               $imgWidth  = imagesx($image);
               $imgHeight = imagesy($image);
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

               $pdf->addImage($image, $x + (($width - $imgWidth) / 2), $y, $imgWidth, $imgHeight);

               if ($logoimg !== false) {
                  $pdf->addImage($logoimg,
                                 $x + (($width - $logoWidth) / 2),
                                 $y + $heightyposText,
                                 $logoWidth,
                                 $logoHeight);
               }
               $txtHeight = 0;
               foreach ($displayData as $line) {
                  $pdf->addTextWrap(
                     $x,
                     $y - ($txtSpacing + $txtHeight),
                     $txtSize,
                     (string) $line,
                     $width,
                     'center'
                  );
                  $txtHeight += $txtSpacing / 2 + $txtSize;
               }
               if ($border) {
                  $pdf->Rectangle($x, $y - ($txtHeight + $txtSpacing * 2),
                       $width, $height + ($txtHeight + $txtSpacing * 2));
               }
            }
         }
         $x += $width + $marginH;
      }

      $pdfFile = Session::getLoginUserID() . '_barcode_' . $type . '.pdf';
      if (file_put_contents($this->docsPath . $pdfFile, $pdf->ezOutput()) === false) {
         Session::addMessageAfterRedirect(__('Unable to write the PDF file.', 'barcode'), true, ERROR);
         return false;
      }
      return $pdfFile;
   }



   /**
    * Cache file of a barcode image. The code is hashed so that user input
    * never ends up in a file path.
    */
   function getCacheFile(string $p_code, string $p_type): string {
      return $this->docsPath . 'cache_' . md5($p_code) . '_' . $p_type . '.png';
   }



   /**
    * Render a barcode image into the cache.
    *
    * @return bool false if the code cannot be encoded with this type
    */
   function create($p_code, $p_type) {
      $p_code = (string) $p_code;
      if (!in_array($p_type, self::getBarcodeTypes(), true) || !self::isValidCode($p_code, $p_type)) {
         return false;
      }
      $file = $this->getCacheFile($p_code, $p_type);
      if (is_file($file) && filesize($file) > 0) {
         return true;
      }

      $barcode = new Image_Barcode();
      $img     = @$barcode->draw($p_code, $p_type, 'png', false);
      if (!($img instanceof GdImage)) {
         return false;
      }
      return imagepng($img, $file);
   }



   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      switch ($ma->getAction()) {
         case 'Generate':
            $barcode = new self();
            $barcode->showFormMassiveAction($ma);
            return true;

      }
      return false;
   }



   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {

      if ($ma->getAction() !== 'Generate') {
         return;
      }

      if (!Session::haveRight('plugin_barcode_barcode', CREATE)) {
         $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
         return;
      }

      $displayLabels = !empty($ma->POST['displaylabels']);
      $codes         = array_fill(0, max(0, (int) ($ma->POST['eliminate'] ?? 0)), '');
      $displayData   = array_fill(0, count($codes), []);
      $processed     = [];

      foreach ($ids as $id) {
         if (!$item->getFromDB($id) || !$item->can($id, READ)) {
            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
            continue;
         }
         if ($item instanceof CommonITILObject) {
            $code = (string) $item->getID();
         } else {
            $code = trim((string) ($item->fields['otherserial'] ?? ''));
         }
         if ($code === '') {
            // Nothing to encode for this item
            $ma->itemDone($item->getType(), $id, MassiveAction::NO_ACTION);
            continue;
         }
         $codes[]       = $code;
         $displayData[] = $displayLabels ? [$item->fields['name'] ?? ''] : [];
         $processed[]   = $id;
      }

      if (count($processed) === 0) {
         return;
      }

      $barcode = new self();
      $file    = $barcode->printPDF([
         'codes'       => $codes,
         'displayData' => $displayData,
         'type'        => $ma->POST['type'] ?? '',
         'size'        => $ma->POST['size'] ?? null,
         'orientation' => $ma->POST['orientation'] ?? null,
         'border'      => $ma->POST['border'] ?? 0,
      ]);

      if ($file === false) {
         $ma->itemDone($item->getType(), $processed, MassiveAction::ACTION_KO);
         return;
      }

      Session::addMessageAfterRedirect(self::getDownloadLink($file));
      $ma->itemDone($item->getType(), $processed, MassiveAction::ACTION_OK);
   }



   /**
    * HTML link to download a generated file
    *
    * @param string $filename file name in the plugin doc dir
    *
    * @return string
    */
   static function getDownloadLink(string $filename, ?string $label = null): string {
      global $CFG_GLPI;

      $url = $CFG_GLPI['root_doc'] . '/plugins/barcode/front/send.php?file=' . rawurlencode($filename);
      return '<a href="' . htmlescape($url) . '">'
         . htmlescape($label ?? __('Generated file', 'barcode')) . '</a>';
   }
}

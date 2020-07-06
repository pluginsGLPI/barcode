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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class to generate barcodes using PEAR Image_Barcode
 **/
class PluginBarcodeBarcode {
   private $docsPath;

   static $rightname = 'plugin_barcode_barcode';


   /**
     * Constructor
    **/
   function __construct() {
      $this->docsPath = GLPI_PLUGIN_DOC_DIR.'/barcode/';
   }



   function getCodeTypes() {
      $types = ['Code39', 'code128', 'ean13', 'int25', 'postnet', 'upca',
                'QRcode'
               ];
      return $types;
   }



   function showSizeSelect($p_size = null) {
      //TODO : utiliser fonction du coeur

      Dropdown::showFromArray("size",
                              ['4A0'       => __('4A0', 'barcode'),
                               '2A0'       => __('2A0', 'barcode'),
                               'A0'        => __('A0', 'barcode'),
                               'A1'        => __('A1', 'barcode'),
                               'A2'        => __('A2', 'barcode'),
                               'A3'        => __('A3', 'barcode'),
                               'A4'        => __('A4', 'barcode'),
                               'A5'        => __('A5', 'barcode'),
                               'A6'        => __('A6', 'barcode'),
                               'A7'        => __('A7', 'barcode'),
                               'A8'        => __('A8', 'barcode'),
                               'A9'        => __('A9', 'barcode'),
                               'A10'       => __('A10', 'barcode'),
                               'B0'        => __('B0', 'barcode'),
                               'B1'        => __('B1', 'barcode'),
                               'B2'        => __('B2', 'barcode'),
                               'B3'        => __('B3', 'barcode'),
                               'B4'        => __('B4', 'barcode'),
                               'B5'        => __('B5', 'barcode'),
                               'B6'        => __('B6', 'barcode'),
                               'B7'        => __('B7', 'barcode'),
                               'B8'        => __('B8', 'barcode'),
                               'B9'        => __('B9', 'barcode'),
                               'B10'       => __('B10', 'barcode'),
                               'C0'        => __('C0', 'barcode'),
                               'C1'        => __('C1', 'barcode'),
                               'C2'        => __('C2', 'barcode'),
                               'C3'        => __('C3', 'barcode'),
                               'C4'        => __('C4', 'barcode'),
                               'C5'        => __('C5', 'barcode'),
                               'C6'        => __('C6', 'barcode'),
                               'C7'        => __('C7', 'barcode'),
                               'C8'        => __('C8', 'barcode'),
                               'C9'        => __('C9', 'barcode'),
                               'C10'       => __('C10', 'barcode'),
                               'RA0'       => __('RA0', 'barcode'),
                               'RA1'       => __('RA1', 'barcode'),
                               'RA2'       => __('RA2', 'barcode'),
                               'RA3'       => __('RA3', 'barcode'),
                               'RA4'       => __('RA4', 'barcode'),
                               'SRA0'      => __('SRA0', 'barcode'),
                               'SRA1'      => __('SRA1', 'barcode'),
                               'SRA2'      => __('SRA2', 'barcode'),
                               'SRA3'      => __('SRA3', 'barcode'),
                               'SRA4'      => __('SRA4', 'barcode'),
                               'LETTER'    => __('LETTER', 'barcode'),
                               'LEGAL'     => __('LEGAL', 'barcode'),
                               'EXECUTIVE' => __('EXECUTIVE', 'barcode'),
                               'FOLIO'     => __('FOLIO', 'barcode')],
                              (is_null($p_size)?['width' => '100']:['value' => $p_size, 'width' => '100']));
   }



   function showOrientationSelect($p_orientation = null) {
      //TODO : utiliser fonction du coeur

      Dropdown::showFromArray("orientation",
                              ['Portrait'  => __('Portrait', 'barcode'),
                                    'Landscape' => __('Landscape', 'barcode')],
                              (is_null($p_orientation)?['width' => '100']:['value' => $p_orientation, 'width' => '100']));
   }



   function showForm($p_type, $p_ID) {
      global $CFG_GLPI;

      $config = $this->getConfigType();
      $ci = new $p_type();
      $ci->getFromDB($p_ID);
      if ($ci->isField('otherserial')) {
         $code = $ci->getField('otherserial');
      } else {
         $code = '';
      }
      echo "<form name='form' method='post'
                  action='".Plugin::getWebDir('barcode')."/front/barcode.form.php'>";
        echo "<div align='center'>";
        echo "<table class='tab_cadre'>";
         echo "<tr><th colspan='4'>".__('Generation', 'barcode')."</th></tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".__('Code', 'barcode')."</td><td>";
            echo "<input type='text' size='20' name='code' value='$code'>";
            echo "</td>";
            echo "<td>".__('Type', 'barcode')."</td><td>";
            $this->showTypeSelect($config['type']);
            echo "</td>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".__('Page size', 'barcode')."</td><td>";
            $this->showSizeSelect($config['size']);
            echo "</td>";
            echo "<td>".__('Orientation', 'barcode')."</td><td>";
            $this->showOrientationSelect($config['orientation']);
            echo "</td>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".__('Number of copies', 'barcode')."</td>";
            echo "<td><input type='text' size='20' name='nb' value='1'></td>";
            echo "<td colspan='2'></td>";
         echo "</tr>";
         echo "<tr><td class='tab_bg_1' colspan='4' align='center'>
                   <input type='submit' value='".__('Create', 'barcode')."'
                          class='submit'></td></tr>";
        echo "</table>";
        echo "</div>";
        Html::closeForm();
   }



   function showFormMassiveAction(MassiveAction $ma) {

      $pbConfig = new PluginBarcodeConfig();

      echo '<center>';
      echo '<strong>';
      echo __('It will generate only elements have defined field:', 'barcode').' ';
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



   function printPDF($p_params) {

      $pbConfig = new PluginBarcodeConfig();

      // create barcodes
      $ext         = 'png';
      $type        = $p_params['type'];
      $size        = $p_params['size'];
      $orientation = $p_params['orientation'];
      $codes       = [];

      $displayDataCollection = $p_params['displayData'] ?? [];

      if ($type == 'QRcode') {
         $codes = $p_params['codes'];
      } else {
         if (isset($p_params['code'])) {
            if (isset($p_params['nb']) AND $p_params['nb']>1) {
               $this->create($p_params['code'], $type, $ext);
               for ($i=1; $i<=$p_params['nb']; $i++) {
                  $codes[] = $p_params['code'];
               }
            } else {
               if (!$this->create($p_params['code'], $type, $ext)) {
                  Session::addMessageAfterRedirect(__('The generation of some barcodes produced errors.', 'barcode'));
               }
               $codes[] = $p_params['code'];
            }
         } else if (isset($p_params['codes'])) {
            $codes = $p_params['codes'];
            foreach ($codes as $code) {
               if ($code != '') {
                  $this->create($code, $type, $ext);
               }
            }
         } else {
            // TODO : erreur ?
            //         print_r($p_params);
            return 0;
         }
      }

      // create pdf
      // x is horizontal axis and y is vertical
      // x=0 and y=0 in bottom left hand corner
      $config = $pbConfig->getConfigType($type);

      $pdf= new Cezpdf($size, $orientation);
      $pdf->tempPath = GLPI_TMP_DIR;
      $pdf->selectFont(Plugin::getPhpDir('barcode')."/lib/ezpdf/fonts/Helvetica.afm");
      $pdf->ezStartPageNumbers($pdf->ez['pageWidth']-30, 10, 10, 'left', '{PAGENUM} / {TOTALPAGENUM}').
      $width   = $config['maxCodeWidth'];
      $height  = $config['maxCodeHeight'];
      $marginH = $config['marginHorizontal'];
      $marginV = $config['marginVertical'];
      $txtSize    = $config['txtSize'];
      $txtSpacing = $config['txtSpacing'];

      $heightimage = $height;

      if (file_exists(GLPI_PLUGIN_DOC_DIR.'/barcode/logo.png')) {
         // Add logo to barcode
         $heightLogomax = 20;
         $imgSize       = getimagesize(GLPI_PLUGIN_DOC_DIR.'/barcode/logo.png');
         $logoWidth     = $imgSize[0];
         $logoHeight    = $imgSize[1];
         if ($logoHeight > $heightLogomax) {
            $ratio      = (100 * $heightLogomax ) / $logoHeight;
            $logoHeight = $heightLogomax;
            $logoWidth  = $logoWidth * ($ratio / 100);
         }
         if ($logoWidth > $width) {
            $ratio      = (100 * $width ) / $logoWidth;
            $logoWidth  = $width;
            $logoHeight = $logoHeight * ($ratio / 100);
         }
         $heightyposText = $height - $logoHeight;
         $heightimage    = $heightyposText;
      }

      $first=true;
      for ($ia = 0; $ia < count($codes); $ia++) {
         $code = $codes[$ia];
         $displayData = $displayDataCollection[$ia] ?? [];
         if ($first) {
            $x = $pdf->ez['leftMargin'];
            $y = $pdf->ez['pageHeight'] - $pdf->ez['topMargin'] - $height;
            $first = false;
         } else {
            if ($x + $width + $marginH > $pdf->ez['pageWidth']) { // new line
               $x = $pdf->ez['leftMargin'];
               if ($y - $height - $marginV < $pdf->ez['bottomMargin']) { // new page
                  $pdf->ezNewPage();
                  $y = $pdf->ez['pageHeight'] - $pdf->ez['topMargin'] - $height;
               } else {
                  $y -= $height + $marginV;
               }
            }
         }
         if ($code != '') {
            if ($type == 'QRcode') {
               $imgFile = $code;
            } else {
               $imgFile = $this->docsPath.$code.'_'.$type.'.'.$ext;
            }
            if (file_exists($imgFile)) {
               $imgSize   = getimagesize($imgFile);
               $imgWidth  = $imgSize[0];
               $imgHeight = $imgSize[1];
               if ($imgWidth > $width) {
                  $ratio     = (100 * $width ) / $imgWidth;
                  $imgWidth  = $width;
                  $imgHeight = $imgHeight * ($ratio / 100);
               }
               if ($imgHeight > $heightimage) {
                  $ratio     = (100 * $heightimage ) / $imgHeight;
                  $imgHeight = $heightimage;
                  $imgWidth  = $imgWidth * ($ratio / 100);
               }

               $image = imagecreatefrompng($imgFile);
               if ($imgWidth < $width) {
                  $pdf->addImage($image,
                                 $x + (($width - $imgWidth) / 2),
                                 $y,
                                 $imgWidth,
                                 $imgHeight);
               } else {
                  $pdf->addImage($image,
                                 $x,
                                 $y,
                                 $imgWidth,
                                 $imgHeight);
               }

               if (file_exists(GLPI_PLUGIN_DOC_DIR.'/barcode/logo.png')) {
                  $logoimg = imagecreatefrompng(GLPI_PLUGIN_DOC_DIR.'/barcode/logo.png');
                  $pdf->addImage($logoimg,
                                 $x + (($width - $logoWidth) / 2),
                                 $y + $heightyposText,
                                 $logoWidth,
                                 $logoHeight);
               }
               $txtHeight = 0;
               for ($i = 0; $i < count($displayData); $i++) {
                   $pdf->addTextWrap(
                       $x,
                       $y - ($txtSpacing + $txtHeight),
                       $txtSize,
                       $displayData[$i],
                       $width,
                       'center');
                   $txtHeight += $txtSpacing/2 + $txtSize;
               }
               if ($p_params['border']) {
                  $pdf->Rectangle($x, $y - ($txtHeight + $txtSpacing*2),
                       $width, $height + ($txtHeight + $txtSpacing*2));
               }
            }
         }
         $x += $width + $marginH;
         $y -= 0;
      }
      $file    = $pdf->ezOutput();
      $pdfFile = $_SESSION['glpiID'].'_'.$type.'.pdf';
      file_put_contents($this->docsPath.$pdfFile, $file);
      return '/files/_plugins/barcode/'.$pdfFile;
   }



   function create($p_code, $p_type, $p_ext) {
      //TODO : filtre sur le type
      if (!file_exists($this->docsPath.$p_code.'_'.$p_type.'.'.$p_ext)) {
         plugin_barcode_disableDebug();
         ob_start();
         $barcode = new Image_Barcode();
         $resImg  = imagepng($barcode->draw($p_code, $p_type, $p_ext, false));
         $img     = ob_get_contents();
         ob_end_clean();
         plugin_barcode_reenableusemode();
         file_put_contents($this->docsPath.$p_code.'_'.$p_type.'.'.$p_ext, $img);
         if (!$resImg) {
            return false;
         }
      }
      return true;
   }



   function getSpecificMassiveActions($checkitem = null) {
      return [];
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
      global $CFG_GLPI;

      switch ($ma->getAction()) {

         case 'Generate' :
            $pbQRcode = new PluginBarcodeQRcode();
            $rand     = mt_rand();
            $number   = 0;
            $codes    = [];
            if ($ma->POST['eliminate'] > 0) {
               for ($nb=0; $nb < $ma->POST['eliminate']; $nb++) {
                  $codes[] = '';
               }
            }
            foreach ($ids as $key) {
               $item->getFromDB($key);
               if (key($ma->items) == 'CommonITILObject') {
                  $codes[] = $item->getField('id');
               } else if ($item->isField('otherserial')) {
                  $codes[] = $item->getField('otherserial');
               }
            }
            if (count($codes) > 0) {
               $params['codes']       = $codes;
               $params['type']        = $ma->POST['type'];
               $params['size']        = $ma->POST['size'];
               $params['border']      = $ma->POST['border'];
               $params['orientation'] = $ma->POST['orientation'];
               $params['displaylabels'] = $ma->POST['displaylabels'];

               $barcode  = new PluginBarcodeBarcode();
               $file     = $barcode->printPDF($params);
               $filePath = explode('/', $file);
               $filename = $filePath[count($filePath)-1];

               $msg = "<a href='".Plugin::getWebDir('barcode').'/front/send.php?file='.urlencode($filename)
                  ."'>".__('Generated file', 'barcode')."</a>";

               Session::addMessageAfterRedirect($msg);
               $pbQRcode->cleanQRcodefiles($rand, $number);
            }
            $ma->itemDone($item->getType(), 0, MassiveAction::ACTION_OK);
            return;

      }
      return;
   }

}

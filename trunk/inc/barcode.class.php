<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Vincent Mazzoni
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

/**
 * Class to generate barcodes using PEAR Image_Barcode
 **/
class PluginBarcodeBarcode {
   private $docsPath;

   /**
	 * Constructor
	**/
   function __construct() {
      $this->docsPath = GLPI_PLUGIN_DOC_DIR.'/barcode/';
   }

   function showTypeSelect($p_type=NULL) {
      global $LANG;

      Dropdown::showFromArray("type",
                              array('Code39' => $LANG['plugin_barcode']["code"][0],
                                    'code128' => $LANG['plugin_barcode']["code"][1],
                                    'ean13' => $LANG['plugin_barcode']["code"][2],
                                    'int25' => $LANG['plugin_barcode']["code"][3],
                                    'postnet' => $LANG['plugin_barcode']["code"][4],
                                    'upca' => $LANG['plugin_barcode']["code"][5]),
                              (is_null($p_type)?array():array('value' => $p_type)));
   }

   function showSizeSelect($p_size=NULL) { //TODO : utiliser fonction du coeur
      global $LANG;

      Dropdown::showFromArray("size",
                              array('4A0' => $LANG['plugin_barcode']['size'][0],
                                    '2A0' => $LANG['plugin_barcode']['size'][1],
                                    'A0' => $LANG['plugin_barcode']['size'][2],
                                    'A1' => $LANG['plugin_barcode']['size'][3],
                                    'A2' => $LANG['plugin_barcode']['size'][4],
                                    'A3' => $LANG['plugin_barcode']['size'][5],
                                    'A4' => $LANG['plugin_barcode']['size'][6],
                                    'A5' => $LANG['plugin_barcode']['size'][7],
                                    'A6' => $LANG['plugin_barcode']['size'][8],
                                    'A7' => $LANG['plugin_barcode']['size'][9],
                                    'A8' => $LANG['plugin_barcode']['size'][10],
                                    'A9' => $LANG['plugin_barcode']['size'][11],
                                    'A10' => $LANG['plugin_barcode']['size'][12],
                                    'B0' => $LANG['plugin_barcode']['size'][13],
                                    'B1' => $LANG['plugin_barcode']['size'][14],
                                    'B2' => $LANG['plugin_barcode']['size'][15],
                                    'B3' => $LANG['plugin_barcode']['size'][16],
                                    'B4' => $LANG['plugin_barcode']['size'][17],
                                    'B5' => $LANG['plugin_barcode']['size'][18],
                                    'B6' => $LANG['plugin_barcode']['size'][19],
                                    'B7' => $LANG['plugin_barcode']['size'][20],
                                    'B8' => $LANG['plugin_barcode']['size'][21],
                                    'B9' => $LANG['plugin_barcode']['size'][22],
                                    'B10' => $LANG['plugin_barcode']['size'][23],
                                    'C0' => $LANG['plugin_barcode']['size'][24],
                                    'C1' => $LANG['plugin_barcode']['size'][25],
                                    'C2' => $LANG['plugin_barcode']['size'][26],
                                    'C3' => $LANG['plugin_barcode']['size'][27],
                                    'C4' => $LANG['plugin_barcode']['size'][28],
                                    'C5' => $LANG['plugin_barcode']['size'][29],
                                    'C6' => $LANG['plugin_barcode']['size'][30],
                                    'C7' => $LANG['plugin_barcode']['size'][31],
                                    'C8' => $LANG['plugin_barcode']['size'][32],
                                    'C9' => $LANG['plugin_barcode']['size'][33],
                                    'C10' => $LANG['plugin_barcode']['size'][34],
                                    'RA0' => $LANG['plugin_barcode']['size'][35],
                                    'RA1' => $LANG['plugin_barcode']['size'][36],
                                    'RA2' => $LANG['plugin_barcode']['size'][37],
                                    'RA3' => $LANG['plugin_barcode']['size'][38],
                                    'RA4' => $LANG['plugin_barcode']['size'][39],
                                    'SRA0' => $LANG['plugin_barcode']['size'][40],
                                    'SRA1' => $LANG['plugin_barcode']['size'][41],
                                    'SRA2' => $LANG['plugin_barcode']['size'][42],
                                    'SRA3' => $LANG['plugin_barcode']['size'][43],
                                    'SRA4' => $LANG['plugin_barcode']['size'][44],
                                    'LETTER' => $LANG['plugin_barcode']['size'][45],
                                    'LEGAL' => $LANG['plugin_barcode']['size'][46],
                                    'EXECUTIVE' => $LANG['plugin_barcode']['size'][47],
                                    'FOLIO' => $LANG['plugin_barcode']['size'][48]),
                              (is_null($p_size)?array():array('value' => $p_size)));
   }

   function showOrientationSelect($p_orientation=NULL) { //TODO : utiliser fonction du coeur
      global $LANG;

      Dropdown::showFromArray("orientation",
                              array('Portrait' => $LANG['plugin_barcode']["print"][9],
                                    'Landscape' => $LANG['plugin_barcode']["print"][10]),
                              (is_null($p_orientation)?array():array('value' => $p_orientation)));
   }

   function showForm($p_type, $p_ID) {
      global $LANG;

      $config = $this->getConfigType();
      $ci = new $p_type();
      $ci->getFromDB($p_ID);
      if ($ci->isField('otherserial')) {
         $code = $ci->getField('otherserial');
      } else {
         $code = '';
      }
      echo "<form name='form' method='post'
                  action='".GLPI_ROOT."/plugins/barcode/front/barcode.form.php'>";
		echo "<div align='center'>";
		echo "<table class='tab_cadre'>";
         echo "<tr><th colspan='4'>".$LANG['plugin_barcode']["title"][3]."</th></tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".$LANG['plugin_barcode']["print"][12]."</td><td>";
            echo "<input type='text' size='20' name='code' value='$code'>";
            echo "</td>";
            echo "<td>".$LANG['plugin_barcode']["print"][13]."</td><td>";
            $this->showTypeSelect($config['type']);
            echo "</td>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".$LANG['plugin_barcode']["print"][4]."</td><td>";
            $this->showSizeSelect($config['size']);
            echo "</td>";
            echo "<td>".$LANG['plugin_barcode']["print"][8]."</td><td>";
            $this->showOrientationSelect($config['orientation']);
            echo "</td>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".'Nombre d\'exemplaire'."</td>";
            echo "<td><input type='text' size='20' name='nb' value='1'></td>";
            echo "<td colspan='2'></td>";
         echo "</tr>";
         echo "<tr><td class='tab_bg_1' colspan='4' align='center'>
                   <input type='submit' value='".$LANG['plugin_barcode']["print"][11]."'
                          class='submit'></td></tr>";
		echo "</table>";
		echo "</div>";
		echo "</form>";
   }

   function showFormMassiveAction() {
      global $LANG;

      $config = $this->getConfigType();
      echo "<form name='form' method='post'
                  action='".GLPI_ROOT."/plugins/barcode/front/barcode.form.php'>";
		echo $LANG['plugin_barcode']["print"][13]." : ";
		$this->showTypeSelect($config['type']);
		echo " ".$LANG['plugin_barcode']["print"][4]." : ";
		$this->showSizeSelect($config['size']);
		echo " ".$LANG['plugin_barcode']["print"][8]." : ";
		$this->showOrientationSelect($config['orientation']);
		echo "<input type='submit' value='".$LANG['plugin_barcode']["print"][11]."' class='submit'>";
		echo "</form>";
   }

   function showFormConfig($p_type=NULL) {
      global $LANG;

      $defaultType = $this->getConfig();
      echo "<form name='form' method='post'
                  action='".GLPI_ROOT."/plugins/barcode/front/config.form.php'>";

		echo "<div align='center'>";
		echo "<table class='tab_cadre'>";
		echo "<tr><th colspan='4'>".$LANG['plugin_barcode']["title"][1]."</th></tr>";
		echo "</table><br>";

		echo "<table class='tab_cadre'>";
		echo "<tr><th colspan='4'>".$LANG['plugin_barcode']["title"][5]."</th></tr>";
		echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['plugin_barcode']["print"][13]."</td><td>";
            $this->showTypeSelect($defaultType);
      echo "</td>";
      echo "<td colspan='2'></td>";
		echo "</tr>";
      echo "<tr><td class='tab_bg_1' colspan='4' align='center'><input type='submit' value='".$LANG['buttons'][2]."' class='submit'></td></tr>";
		echo "<tr><td class='tab_bg_1' colspan='4' align='center'><input type='submit' value='".$LANG['plugin_barcode']["message"][2]."' class='submit' name='dropCache'></td></tr>";
		echo "</table>";
		echo "</div>";
		echo "</form>";
      $types = $this->getCodeTypes();
      foreach($types as $type) {
         echo '<br>';
         $this->showFormConfigType($type);
      }
   }

   function showFormConfigType($p_type=NULL) {
      global $LANG;

      if (is_null($p_type)) {
         $type = $this->getConfig();
      } else {
         $type = $p_type;
      }
		$config = $this->getConfigType($type);
      echo "<form name='form' method='post'
                  action='".GLPI_ROOT."/plugins/barcode/front/config_type.form.php'>";
      echo "<input type='hidden' name='type' value='".$type."'>";
		echo "<div align='center'>";
		echo "<table class='tab_cadre' >";

		echo "<tr><th colspan='4'>".$type."</th></tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".$LANG['plugin_barcode']["print"][4]."</td><td>";
            $this->showSizeSelect($config['size']);
            echo "</td>";
            echo "<td>".$LANG['plugin_barcode']["print"][8]."</td><td>";
            $this->showOrientationSelect($config['orientation']);
            echo "</td>";
         echo "</tr>";
         echo "<tr><th colspan='4'>".$LANG['plugin_barcode']["print"][14]."</th></tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".$LANG['plugin_barcode']["print"][15]."</td><td>";
            echo "<input type='text' size='20' name='marginTop' value='".$config['marginTop']."'>";
            echo "</td>";
            echo "<td>".$LANG['plugin_barcode']["print"][16]."</td><td>";
            echo "<input type='text' size='20' name='marginBottom' value='".$config['marginBottom']."'>";
            echo "</td>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".$LANG['plugin_barcode']["print"][17]."</td><td>";
            echo "<input type='text' size='20' name='marginLeft' value='".$config['marginLeft']."'>";
            echo "</td>";
            echo "<td>".$LANG['plugin_barcode']["print"][18]."</td><td>";
            echo "<input type='text' size='20' name='marginRight' value='".$config['marginRight']."'>";
            echo "</td>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".$LANG['plugin_barcode']["print"][19]."</td><td>";
            echo "<input type='text' size='20' name='marginHorizontal' value='".$config['marginHorizontal']."'>";
            echo "</td>";
            echo "<td>".$LANG['plugin_barcode']["print"][20]."</td><td>";
            echo "<input type='text' size='20' name='marginVertical' value='".$config['marginVertical']."'>";
            echo "</td>";
         echo "</tr>";
         echo "<tr><th colspan='4'>".$LANG['plugin_barcode']["print"][21]."</th></tr>";
         echo "<tr class='tab_bg_1'>";
            echo "<td>".$LANG['plugin_barcode']["print"][22]."</td><td>";
            echo "<input type='text' size='20' name='maxCodeWidth' value='".$config['maxCodeWidth']."'>";
            echo "</td>";
            echo "<td>".$LANG['plugin_barcode']["print"][23]."</td><td>";
            echo "<input type='text' size='20' name='maxCodeHeight' value='".$config['maxCodeHeight']."'>";
            echo "</td>";
         echo "</tr>";

		echo "<tr><td class='tab_bg_1' colspan='4' align='center'><input type='submit' value='".$LANG['buttons'][2]."' class='submit'></td></tr>";
		echo "</table>";
		echo "</div>";
		echo "</form>";
   }

   function getConfigType($p_type=NULL) {
      if (is_null($p_type)) $p_type=$this->getConfig();
      $pbcconf = new PluginBarcodeConfig_Type();
      if ($res=array_keys($pbcconf->find("`type`='$p_type'"))) {
         $id = $res[0];
         $pbcconf->getFromDB($id);
         $config['type'] = $pbcconf->fields['type'];
         $config['size'] = $pbcconf->fields['size'];
         $config['orientation'] = $pbcconf->fields['orientation'];
         $config['marginTop'] = $pbcconf->fields['marginTop'];
         $config['marginBottom'] = $pbcconf->fields['marginBottom'];
         $config['marginLeft'] = $pbcconf->fields['marginLeft'];
         $config['marginRight'] = $pbcconf->fields['marginRight'];
         $config['marginHorizontal'] = $pbcconf->fields['marginHorizontal'];
         $config['marginVertical'] = $pbcconf->fields['marginVertical'];
         $config['maxCodeWidth'] = $pbcconf->fields['maxCodeWidth'];
         $config['maxCodeHeight'] = $pbcconf->fields['maxCodeHeight'];
      } else {
         $config['type'] = 'code128';
         $config['size'] = 'A4';
         $config['orientation'] = 'Portrait';
         $config['marginTop'] = 30;
         $config['marginBottom'] = 30;
         $config['marginLeft'] = 30;
         $config['marginRight'] = 30;
         $config['marginHorizontal'] = 25;
         $config['marginVertical'] = 30;
         $config['maxCodeWidth'] = 110;
         $config['maxCodeHeight'] = 70;
      }
      return $config;
   }

   function getConfig() {
      $pbconf = new PluginBarcodeConfig();
      if ($pbconf->getFromDB(1)) {
         $type = $pbconf->fields['type'];
      } else {
         $type = 'code128';
      }
      return $type;
   }

   function getCodeTypes() {
      $types = array('Code39', 'code128', 'ean13', 'int25', 'postnet', 'upca');
      return $types;
   }

   function printPDF($p_params) {
      global $LANG;
      
      // create barcodes
      $ext = 'png';
      $type = $p_params['type'];
      $size = $p_params['size'];
      $orientation = $p_params['orientation'];
      $codes = array();
      if (isset($p_params['code'])) {
         if (isset($p_params['nb']) AND $p_params['nb']>1) {
            $this->create($p_params['code'], $type, $ext);
            for ($i=1 ; $i<=$p_params['nb'] ; $i++) {
               $codes[] = $p_params['code'];
            }
         } else {
            if (!$this->create($p_params['code'], $type, $ext)) {
               addMessageAfterRedirect($LANG['plugin_barcode']["message"][1]);
            }
            $codes[] = $p_params['code'];
         }
      } elseif (isset($p_params['codes'])) {
         $codes = $p_params['codes'];
         foreach ($codes as $code) {
            $this->create($code, $type, $ext);
         }
      } else {
         // TODO : erreur ?
//         print_r($p_params);
         return 0;
      }

      // create pdf
      // x is horizontal axis and y is vertical
      // x=0 and y=0 in bottom left hand corner
      $config = $this->getConfigType($type);
      require_once(GLPI_ROOT."/lib/ezpdf/class.ezpdf.php");
      $pdf= new Cezpdf($size, $orientation);
      $pdf->selectFont(GLPI_ROOT."/lib/ezpdf/fonts/Helvetica.afm");
      $pdf->ezStartPageNumbers($pdf->ez['pageWidth']-30, 10, 10, 'left', '{PAGENUM} / {TOTALPAGENUM}').
      $width = $config['maxCodeWidth'];
      $height = $config['maxCodeHeight'];
      $marginH = $config['marginHorizontal'];
      $marginV = $config['marginVertical'];

      $first=true;
      foreach ($codes as $code) {
         $imgFile = $this->docsPath.$code.'_'.$type.'.'.$ext;
         if (file_exists($imgFile)) {
            $imgSize = getimagesize($imgFile);
            $imgWidth = $imgSize[0];
            if ($imgWidth > $width) {
               addMessageAfterRedirect($LANG['plugin_barcode']["message"][1]);
            }
            $image = imagecreatefrompng($imgFile);
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
            $pdf->addImage($image, $x, $y, 0, $height);
            $x += $width + $marginH;
            $y -= 0;
         }
      }
      $file = $pdf->ezOutput();
      $pdfFile = $codes[0].'_'.$type.'.pdf';
      file_put_contents($this->docsPath.$pdfFile, $file);
      return '/files/_plugins/barcode/'.$pdfFile;
   }

   function create($p_code, $p_type, $p_ext) {
      require_once(GLPI_ROOT.'/plugins/barcode/tools/Image_Barcode/Barcode.php');
      require_once(GLPI_ROOT.'/plugins/barcode/inc/functions_debug.php');
      //TODO : filtre sur le type
      if (!file_exists($this->docsPath.$p_code.'_'.$p_type.'.'.$p_ext)) {
         plugin_barcode_disableDebug();
         ob_start();
         $barcode = new Image_Barcode();
         $resImg = imagepng($barcode->draw($p_code, $p_type, $p_ext, false));
         $img = ob_get_contents();
         ob_end_clean();
         plugin_barcode_reenableusemode();
         file_put_contents($this->docsPath.$p_code.'_'.$p_type.'.'.$p_ext, $img);
         if (!$resImg) return false;
      }
      return true;
   }
}

?>

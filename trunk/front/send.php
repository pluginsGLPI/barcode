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

if (!defined("GLPI_PLUGIN_DOC_DIR")){
	define("GLPI_PLUGIN_DOC_DIR",GLPI_ROOT . "/files/_plugins");
}
$docDir = GLPI_PLUGIN_DOC_DIR.'/barcode';

if (isset($_GET['file'])) {
   $filename = $_GET['file'];

   // Security test : document in $docDir
   if (strstr($filename,"../") || strstr($filename,"..\\")){
      echo "Security attack !!!";
      Toolbox::logDebug("[Plugin barcode][security][sendfile] ".
               $_SESSION["glpiname"]." try to get a non standard file : ".$filename);
   }

   $file = $docDir.'/'.$filename;
   if (!file_exists($file)){
      echo "Error file $filename does not exist"; //TODO : traduire
   } else {
      // Now send the file with header() magic
      header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
      header('Pragma: private'); /// IE BUG + SSL
      //header('Pragma: no-cache');
      header('Cache-control: private, must-revalidate'); /// IE BUG + SSL
      header("Content-disposition: filename=\"$filename\"");
      header("Content-type: application/pdf");

      $f=fopen($file,"r");

      if (!$f){
         echo "Error opening file $filename";
      } else {
         // Pour que les \x00 ne devienne pas \0
         $mc = Toolbox::get_magic_quotes_runtime();
         if ($mc) @set_magic_quotes_runtime(0);
         $fsize=filesize($file);

         if ($fsize){
            echo fread($f, filesize($file));
         } else {
            echo 'error';
         }

         if ($mc) @set_magic_quotes_runtime($mc);
      }
   }
}

?>
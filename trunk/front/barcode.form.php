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

if(!isset($_POST["length"])) $_POST["length"] = "";
if(!isset($_POST["prefixe"])) $_POST["prefixe"] = "";
if(!isset($_POST["size"])) $_POST["size"] = "";
if(!isset($_POST["format"])) $_POST["format"] = "";

$barcode = new PluginBarcodeBarcode();

$file = $barcode->printPDF($_POST);

$filePath = explode('/', $file);
$filename = $filePath[count($filePath)-1];
// TODO : recup GLPI_ROOT de la page d'origine via SESSION ?
$msg = "<a href='".$CFG_GLPI['root_doc'].'/plugins/barcode/front/send.php?file='.urlencode($filename)."'>".__('Generated file', 'barcode')."</a>";
Session::addMessageAfterRedirect($msg);

Html::back();
?>

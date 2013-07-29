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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if(!isset($_POST["length"])) $_POST["length"] = "";
if(!isset($_POST["prefixe"])) $_POST["prefixe"] = "";
if(!isset($_POST["size"])) $_POST["size"] = "";
if(!isset($_POST["format"])) $_POST["format"] = "";

$barcode = new PluginBarcodeBarcode();

$file = $barcode->printPDF($_POST);

$filePath = explode('/', $file);
$filename = $filePath[count($filePath)-1];
// TODO : recup GLPI_ROOT de la page d'origine via SESSION ?
$pathOrigin = '..';
$msg = "<a href='".$pathOrigin.'/plugins/barcode/front/send.php?file='.urlencode($filename)."'>".$LANG['plugin_barcode']["message"][0]."</a>";
Session::addMessageAfterRedirect($msg);

Html::back();
?>

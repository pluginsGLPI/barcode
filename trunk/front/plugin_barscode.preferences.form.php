<?php


/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

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
   ------------------------------------------------------------------------
 */

// Original Author of file: Balpe Dévi / Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------
if(!defined('GLPI_ROOT')){
	define('GLPI_ROOT', '../../..'); 
}
include_once (GLPI_ROOT . "/inc/includes.php");

//Save user preferences
if (isset($_POST["page_format"]) && isset($_POST["pdf_type"]) )
{
	$sql = "INSERT INTO glpi_plugin_barscode_preference SET user=".$_SESSION["glpiID"]." AND page_format='".$_POST["page_format"]."' AND pdf_type='".$_POST["pdf_type"]."'";
	$result = $DB->query($sql);
	
	glpi_header($_SERVER['HTTP_REFERER']);
}


?>
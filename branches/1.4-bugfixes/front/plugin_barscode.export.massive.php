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

// Original Author of file: BALPE Dévi
// Purpose of file:
// ----------------------------------------------------------------------



$NEEDED_ITEMS=array("computer","device","networking","monitor","printer","tracking","software","peripheral","reservation","infocom","contract","document","user","link","phone","registry");

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT . "/inc/includes.php");
define('FPDF_FONTPATH','../fpdf/font/');

//global $DB;
	
$values = unserialize($_SESSION["plugin_barscode"]["values"]);

unset($_SESSION["plugin_barscode"]["values"]);

/*
$user_id = $_SESSION['glpiID'];
$query = "select table_num from glpi_plugin_pdf_preference WHERE user_id =".$user_id." and cat=".$type;
$result = $DB->query($query);

$i=1;
		
while($data = $DB->fetch_array($result))
	{
	$tab[$i]=$data["table_num"];
	$i++;
	}
	*/

plugin_barscode_printMassiveAction($values);	
//plugin_pdf_general($type,$tab_id,$tab);
	
?>
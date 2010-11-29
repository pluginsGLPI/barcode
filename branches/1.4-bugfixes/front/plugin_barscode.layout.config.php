<?php
/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2005 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi.indepnet.org
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

// Original Author of file: GRISARD Jean Marc
// Purpose of file:
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array("setup");
if(!defined('GLPI_ROOT')){
	define('GLPI_ROOT', '../../..'); 
}
include (GLPI_ROOT . "/inc/includes.php");

if(!isset($_SESSION["glpi_plugin_barscode_installed"]) || $_SESSION["glpi_plugin_barscode_installed"]!=1) {
	
	commonHeader($LANG["common"][12],$_SERVER['PHP_SELF'],"config","plugins");
	
	if ($_SESSION["glpiactive_entity"]==0){
	
		if(!TableExists("glpi_plugin_barscode_config")) {
		
			echo "<div align='center'>";
			echo "<table class='tab_cadre' cellpadding='5'>";
			echo "<tr><th>".$LANGBARSCODE["setup"][1];
			echo "</th></tr>";
			echo "<tr class='tab_bg_1'><td>";
			echo "<a href='plugin_barscode.install.php'>".$LANGBARSCODE["setup"][2]."</a></td></tr>";
		
			echo "</table></div>";
		}
	}else{ 
		echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>"; 
		echo "<b>".$LANGBARSCODE["setup"][9]."</b></div>"; 
	}
}
else
{
	$config = new plugin_barscode;
	if(!empty($_POST["update_conf_bc"])) {
		$config->update($_POST);
		glpi_header($_SERVER["HTTP_REFERER"]);	
	} else {
		commonHeader($LANGBARSCODE["title"][1],$_SERVER["PHP_SELF"],"plugins","barscode");
		$config->showForm($_SERVER["PHP_SELF"]);
		commonFooter();
	}
}

?>

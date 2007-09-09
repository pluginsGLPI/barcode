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
	
	commonHeader($LANG["title"][2],$_SERVER['PHP_SELF'],"config","plugins");
	if(!TableExists("glpi_plugin_barscode_config")) {
		if ($_SESSION["glpiactive_entity"]==0){
			echo "<div align='center'>";
			echo "<table class='tab_cadre' cellpadding='5'>";
			echo "<tr><th>".$LANGBARSCODE["setup"][1];
			echo "</th></tr>";
			echo "<tr class='tab_bg_1'><td>";
			echo "<a href='plugin_barscode.install.php'>".$LANGBARSCODE["setup"][2]." v1.3</a></td></tr>";
		
			echo "</table></div>";
		}else{ 
				echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>"; 
				echo "<b>".$LANG["login"][5]."</b></div>"; 
			}
	
	}
}
elseif(!empty($_POST["update_conf_bc"])) {

	plugin_barscode_UpdateConfig($_POST,1);

} else {
	
	commonHeader($LANGBARSCODE["title"][1],$_SERVER["PHP_SELF"],"plugins","barscode");
	
	$plugin_barscode=new plugin_barscode();
	$plugin_barscode->getFromDB(1);
	
	echo "<form name='formconfig' action=\"./plugin_barscode.config.php\" method=\"post\">";
	echo "<div align='center'><table class='tab_cadre'>";
	echo "<tr><th colspan='2'>".$LANGBARSCODE["title"][2]."</th></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][0]." </td><td> <input type=\"text\" name=\"margeL\" value=\"".$plugin_barscode->fields["margeL"]."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][1]." </td><td> <input type=\"text\" name=\"margeT\" value=\"".$plugin_barscode->fields["margeT"]."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][2]." </td><td> <input type=\"text\" name=\"margeH\" value=\"".$plugin_barscode->fields["margeH"]."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][3]." </td><td> <input type=\"text\" name=\"margeW\" value=\"".$plugin_barscode->fields["margeW"]."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][4]." </td><td> <input type=\"text\" name=\"etiquetteW\" value=\"".$plugin_barscode->fields["etiquetteW"]."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][5]." </td><td> <input type=\"text\" name=\"etiquetteH\" value=\"".$plugin_barscode->fields["etiquetteH"]."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][6]." </td><td> <input type=\"text\" name=\"etiquetteR\" value=\"".$plugin_barscode->fields["etiquetteR"]."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][7]." </td><td> <input type=\"text\" name=\"etiquetteC\" value=\"".$plugin_barscode->fields["etiquetteC"]."\"></td></tr>";

	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][13]." </td><td> <input type=\"text\" name=\"etiquetteRL\" value=\"".$plugin_barscode->fields["etiquetteRL"]."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][14]." </td><td> <input type=\"text\" name=\"etiquetteCL\" value=\"".$plugin_barscode->fields["etiquetteCL"]."\"></td></tr>";
	
	echo "<tr><th colspan='2'><input type=\"submit\" name=\"update_conf_bc\" class=\"submit\" value=\"".$LANGBARSCODE["buttons"][0]."\" ></th></tr>";
	echo "</table></div>";
	echo "</form>";
	echo "<div align='center'>";
	echo "<table class='tab_cadre' cellpadding='5'>";
	echo "<tr><th>".$LANGBARSCODE["setup"][1];
	echo "</th></tr>";
	if (haveRight("config","w") && haveRight("profile","w")){
		echo "<tr class='tab_bg_1'><td align='center'>";
		echo "<a href=\"./plugin_barscode.profile.php\">".$LANGBARSCODE["profile"][0]."</a>";
		echo "</td></tr>";
	}
	echo "<tr class='tab_bg_1'><td align='center'>";
			echo "<a href='http://glpi-project.org/wiki/doku.php?id=".substr($_SESSION["glpilanguage"],0,2).":plugins:barscode_use' target='_blank'>".$LANGBARSCODE["setup"][7]."&nbsp;</a>";
			echo "/&nbsp;<a href='http://glpi-project.org/wiki/doku.php?id=".substr($_SESSION["glpilanguage"],0,2).":plugins:barscode_faq' target='_blank'>".$LANGBARSCODE["setup"][8]." </a>";
			echo "</td></tr>";
	if ($_SESSION["glpiactive_entity"]==0){
	echo "<tr class='tab_bg_1'><td><a href='plugin_barscode.uninstall.php'>".$LANGBARSCODE["setup"][4]." v1.3</a>";
	echo " <img src='".$CFG_GLPI["root_doc"]."/pics/aide.png' alt='' onmouseout=\"setdisplay(getElementById('commentsup'),'none')\" onmouseover=\"setdisplay(getElementById('commentsup'),'block')\">";
	echo "<span class='over_link' id='commentsup'>".$LANGBARSCODE["setup"][6]."</span>";
	echo "</td></tr>";
	}
	echo "</table></div>";

}

commonFooter();
?>

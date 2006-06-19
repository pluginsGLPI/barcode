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
include ("_relpos.php");
$NEEDED_ITEMS=array("setup");
include ($phproot . "/inc/includes.php");
checkRight("config","w");


if(!TableExists("glpi_plugin_barscode_config")) {

commonHeader($langbc["title"][1],$_SERVER["PHP_SELF"]);
	echo "<div align='center'>";
	echo "<table class='tab_cadre' cellpadding='5'>";
	echo "<tr><th>".$langbc["setup"][1];
	echo "</th></tr>";
	echo "<tr class='tab_bg_1'><td>";
	echo "<a href='plugin_barscode.install.php'>".$langbc["setup"][2]."</a></td></tr>";
	
	echo "</table></div>";


}
elseif(!empty($_POST["update_conf_bc"])) {
	commonHeader($langbc["title"][1],$_SERVER["PHP_SELF"]);
	plugin_barscode_UpdateConfig($_POST,1);
	
} else {

	commonHeader($langbc["title"][1],$_SERVER["PHP_SELF"]);
	echo "<div align='center'><a class='icon_consol' href=\"".$HTMLRel."front/setup.plugins.php\">".$lang["buttons"][13]."</a><br><br>";
	plugin_barscode_FormConfig($_SERVER["PHP_SELF"],1);
		echo "<div align='center'>";
	echo "<table class='tab_cadre' cellpadding='5'>";
	echo "<tr><th>".$langbc["setup"][1];
	echo "</th></tr>";
	echo "<tr class='tab_bg_1'><td><a href='plugin_barscode.uninstall.php'>".$langbc["setup"][4]."</a>";
		echo " <img src='".$HTMLRel."pics/aide.png' onmouseout=\"setdisplay(getElementById('commentsup'),'none')\" onmouseover=\"setdisplay(getElementById('commentsup'),'block')\">";
	echo "<span class='over_link' id='commentsup'>".$langbc["setup"][6]."</span>";
	echo "</td></tr>";
	echo "</table></div>";
	
}

commonFooter();
?>
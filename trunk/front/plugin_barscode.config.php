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

commonHeader($LANGBARSCODE["title"][1],$_SERVER["PHP_SELF"],"plugins","barscode");

if(!TableExists("glpi_plugin_barscode_config")) {

	echo "<div align='center'>";
	echo "<table class='tab_cadre' cellpadding='5'>";
	echo "<tr><th>".$LANGBARSCODE["setup"][1];
	echo "</th></tr>";
	echo "<tr class='tab_bg_1'><td>";
	echo "<a href='plugin_barscode.install.php'>".$LANGBARSCODE["setup"][2]." v1.3</a></td></tr>";

	echo "</table></div>";


}
elseif(!empty($_POST["update_conf_bc"])) {

	plugin_barscode_UpdateConfig($_POST,1);

} else {

	plugin_barscode_FormConfig($_SERVER["PHP_SELF"],1);
	echo "<div align='center'>";
	echo "<table class='tab_cadre' cellpadding='5'>";
	echo "<tr><th>".$LANGBARSCODE["setup"][1];
	echo "</th></tr>";
	if (haveRight("config","w") && haveRight("profile","w")){
		echo "<tr class='tab_bg_1'><td align='center'>";
		echo "<a href=\"./plugin_barscode.profile.php\">".$LANGBARSCODE["profile"][0]."</a>";
		echo "</td></tr>";
	}
	echo "<tr class='tab_bg_1'><td><a href='plugin_barscode.uninstall.php'>".$LANGBARSCODE["setup"][4]." v1.3</a>";
	echo " <img src='".$CFG_GLPI["root_doc"]."/pics/aide.png' alt='' onmouseout=\"setdisplay(getElementById('commentsup'),'none')\" onmouseover=\"setdisplay(getElementById('commentsup'),'block')\">";
	echo "<span class='over_link' id='commentsup'>".$LANGBARSCODE["setup"][6]."</span>";
	echo "</td></tr>";
	echo "</table></div>";

}

commonFooter();
?>

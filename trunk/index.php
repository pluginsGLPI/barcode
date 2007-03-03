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

define('GLPI_ROOT', '../..'); 
include (GLPI_ROOT . "/inc/includes.php");

if(plugin_barscode_haveRight("barscode","r") || haveRight("config","w")){

	if(!TableExists("glpi_plugin_barscode_config")) {
		glpi_header("./front/plugin_barscode.config.php");
	} else {

		commonHeader($LANGBARSCODE["title"][1],$_SERVER["PHP_SELF"],"plugins","barscode");

		echo "<form name='form' method='post' action='front/plugin_barscode.form.php'>";

		echo "<div align='center'>";
		echo "<table class='tab_cadre' ><tr>";

		echo "<th colspan='8'>".$LANGBARSCODE["title"][1]."</th></tr>";
		echo "<tr class='tab_bg_1'>";

		echo "<td>".$LANGBARSCODE["bc"][0]."</td><td>";
		echo "<input type='text' size='15' name=\"nb\" value=\"\" >";
		echo "</td>";
		echo "<td>".$LANGBARSCODE["bc"][1]."</td><td>";
		echo "<input type='text' size='15' name=\"from\" value=\"\" >";
		echo "</td>";
		echo "<tr class='tab_bg_1'>";
		echo "<td>".$LANGBARSCODE["bc"][2]."</td><td>";
		echo "<input type='text' size='15' name=\"lenght\" value=\"\" >";
		echo "</td>";
		echo "<td>".$LANGBARSCODE["bc"][3]."</td><td>";
		echo "<input type='text' size='15' name=\"prefixe\" value=\"\" >";
		echo "</td>";

		echo "</tr>";
		echo "<tr class='tab_bg_1'>";
		echo "<td>".$LANGBARSCODE["bc"][4]."</td><td>";
		echo "<select name='size'>";
		echo "<option value='A3'>".$LANGBARSCODE["bc"][6]."</option>";
		echo "<option value='A4'>".$LANGBARSCODE["bc"][5]."</option>";
		echo "<option value='A5'>".$LANGBARSCODE["bc"][7]."</option>";
		echo "</select>";
		echo "</td>";
		echo "<td>".$LANGBARSCODE["config"][8]."</td><td>";
		echo "<select name='format'>";
		echo "<option value='P'>".$LANGBARSCODE["config"][9]."</option>";
		echo "<option value='L'>".$LANGBARSCODE["config"][10]."</option>";
		echo "</select>";
		echo "</td>";
		echo "</tr>";

		echo "<tr><td class='tab_bg_1' colspan='8' align='center'><input type='submit' value='".$LANGBARSCODE["buttons"][1]."' class='submit'></td></tr>";
		echo "</table>";
		echo "</div>";
		echo "</form>";
	}
}
commonFooter();


?>

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
include ($phproot . "/inc/includes.php");

if(plugin_barscode_haveRight("barscode","r") || haveRight("config","w")){

if(!TableExists("glpi_plugin_barscode_config")) {
	glpi_header("./front/plugin_barscode.config.php");
} else {
	
	commonHeader($langbc["title"][1],$_SERVER["PHP_SELF"]);
	
	$plugin_barscode= new plugin_barscode;
$plugin_barscode->title();
	
echo "<form name='form' method='post' action='front/plugin_barscode.form.php'>";

echo "<div align='center'>";
echo "<table class='tab_cadre' >";


echo "<th colspan='8'>".$langbc["title"][1]."</th>";
echo "<tr class='tab_bg_1'>";

echo "<td>".$langbc["bc"][0]."</td><td>";
echo "<input type='text' size='15' name=\"nb\" value=\"\" >";
echo "</td>";
echo "<td>".$langbc["bc"][1]."</td><td>";
echo "<input type='text' size='15' name=\"from\" value=\"\" >";
echo "</td>";
echo "<tr class='tab_bg_1'>";
echo "<td>".$langbc["bc"][2]."</td><td>";
echo "<input type='text' size='15' name=\"lenght\" value=\"\" >";
echo "</td>";
echo "<td>".$langbc["bc"][3]."</td><td>";
echo "<input type='text' size='15' name=\"prefixe\" value=\"\" >";
echo "</td>";

echo "</tr>";
echo "<tr class='tab_bg_1'>";
echo "<td>".$langbc["bc"][4]."</td><td>";
echo "<select name='size'>";
echo "<option value='A4'>".$langbc["bc"][5]."</option>";
echo "<option value='A3'>".$langbc["bc"][6]."</option>";
echo "<option value='A5'>".$langbc["bc"][7]."</option>";
echo "</select>";
echo "</td>";
echo "<td>".$langbc["config"][8]."</td><td>";
echo "<select name='format'>";
echo "<option value='P'>".$langbc["config"][9]."</option>";
echo "<option value='L'>".$langbc["config"][10]."</option>";
echo "</select>";
echo "</td>";
echo "</tr>";

echo "<tr><td class='tab_bg_1' colspan='8' align='center'><input type='submit' value='".$langbc["buttons"][1]."' class='submit'></td></tr>";
echo "</table>";
echo "</div>";
echo "</form>";
}
}
	commonFooter();
	
	
?>
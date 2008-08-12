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
if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

class plugin_barscode extends CommonDBTM {

	function plugin_barscode () {
		$this->table="glpi_plugin_barscode_config";
	}

	function showForm($target)
	{
		global $LANGBARSCODE,$CFG_GLPI;
		$this->getFromDB(1);
		
		echo "<form name='formconfig' action=\"$target\" method=\"post\">";
		echo "<div align='center'><table class='tab_cadre'>";
		echo "<tr><th colspan='2'>".$LANGBARSCODE["title"][2]."</th></tr>";
		echo "<input type='hidden' name='ID' value='1'>";
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][15]." </td><td>";
		plugin_barscode_dropdownPrintField("print_field",$this->fields["print_field"]);
		echo "</td></tr>";

		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][0]." </td><td> <input type=\"text\" name=\"margeL\" value=\"".$this->fields["margeL"]."\"></td></tr>";
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][1]." </td><td> <input type=\"text\" name=\"margeT\" value=\"".$this->fields["margeT"]."\"></td></tr>";
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][2]." </td><td> <input type=\"text\" name=\"margeH\" value=\"".$this->fields["margeH"]."\"></td></tr>";
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][3]." </td><td> <input type=\"text\" name=\"margeW\" value=\"".$this->fields["margeW"]."\"></td></tr>";
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][4]." </td><td> <input type=\"text\" name=\"etiquetteW\" value=\"".$this->fields["etiquetteW"]."\"></td></tr>";
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][5]." </td><td> <input type=\"text\" name=\"etiquetteH\" value=\"".$this->fields["etiquetteH"]."\"></td></tr>";
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][6]." </td><td> <input type=\"text\" name=\"etiquetteR\" value=\"".$this->fields["etiquetteR"]."\"></td></tr>";
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][7]." </td><td> <input type=\"text\" name=\"etiquetteC\" value=\"".$this->fields["etiquetteC"]."\"></td></tr>";
	
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][13]." </td><td> <input type=\"text\" name=\"etiquetteRL\" value=\"".$this->fields["etiquetteRL"]."\"></td></tr>";
		echo "<tr class='tab_bg_2'><td align='center'>".$LANGBARSCODE["config"][14]." </td><td> <input type=\"text\" name=\"etiquetteCL\" value=\"".$this->fields["etiquetteCL"]."\"></td></tr>";
		
		echo "<tr><th colspan='2'><input type=\"submit\" name=\"update_conf_bc\" class=\"submit\" value=\"".$LANGBARSCODE["buttons"][0]."\" ></th></tr>";

		echo "</table></div>";
		echo "</form>";
		echo "</table></div>";
		
	}
}

?>
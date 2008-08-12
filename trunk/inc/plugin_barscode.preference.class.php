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

// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

class plugin_barscode_UserPreferences extends CommonDBTM{
	function plugin_barscode_UserPreferences () {
		$this->table="glpi_plugin_barscode_preference";
	}

	function getEmpty()
	{
		$this->fields["page_format"]="A4";
		$this->fields["pdf_type"]="L";	
	}
	
	function showForm($target,$post) {
		global $LANGBARSCODE, $LANG, $DB, $CFG_GLPI;
		
		$sql = "DELETE FROM glpi_plugin_barscode_preference WHERE user=".$_SESSION["glpiID"];
		$result = $DB->query($sql);
		
		if (isset($post["page_format"]) && isset($post["pdf_type"]) )
		{
			$sql = "INSERT INTO glpi_plugin_barscode_preference SET user=".$_SESSION["glpiID"]." AND page_format=".$post["page_format"]." AND pdf_type=".$post["pdf_type"];
			$result = $DB->query($sql);
		}

		echo "<form name='software' action='".$_SERVER['PHP_SELF']."' method='post'>";
		echo "<div align='center' id='pdf_type'>";
		echo "<table class='tab_cadre_fixe'>";
		echo "<tr class='tab_bg_1' align='center'><th colspan='6'>".$LANGBARSCODE["preference"][0]."</th></tr>";		
		echo "<input type='hidden' name='user' value='".$_SESSION["glpiID"]."'>";
		echo "</td></tr>";

		echo "<tr class='tab_bg_1'>";
		echo "<td>".$LANGBARSCODE["bc"][4]."</td><td>";
		plugin_barscode_dropdownPageFormat("page_format",(isset($post["page_format"])?$post["page_format"]:"A4"));
		echo "</td>";
		echo "<td>".$LANGBARSCODE["config"][8]."</td><td>";
		plugin_barscode_dropdownPDFFormat("pdf_type",(isset($post["pdf_type"])?$post["pdf_type"]:"L"));
		echo "</td>";
		echo "</tr>";

		echo "<tr class='tab_bg_1'><td colspan='4' align='center'>";
		echo "<input type='submit' value='" . $LANG["buttons"][2] . "' name='plugin_barscode_user_preferences_save' class='submit'></td></tr>";
		echo "</td></tr>";
		echo "</table></form>";
		echo "</div>";
	}
}	

?>
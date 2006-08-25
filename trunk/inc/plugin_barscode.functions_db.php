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



function plugin_barscode_UpdateConfig($input, $id) {

	global $phproot, $langbc;
	$db = new DB;
	$query = "update glpi_plugin_barscode_config set margeL = '".$input["margeL"]."', margeT = '".$input["margeT"]."', margeH = '".$input["margeH"]."', margeW = '".$input["margeW"]."', etiquetteW = '".$input["etiquetteW"]."', etiquetteH= '".$input["etiquetteH"]."', etiquetteR = '".$input["etiquetteR"]."',etiquetteC = '".$input["etiquetteC"]."',etiquetteRL='".$input["etiquetteRL"]."',etiquetteCL='".$input["etiquetteCL"]."'  where ID = '".$id."'";
	if($db->query($query)) {
		glpi_header($_SERVER["HTTP_REFERER"]); 
	} else {
		glpi_header($_SERVER["HTTP_REFERER"]);	
	}
}

function plugin_barscode_createaccess($ID){

	$db = new DB;
	$query="SELECT * FROM glpi_profiles where ID='$ID';";
	$result=$db->query($query);
	$i = 0;
	$name = $db->result($result, $i, "glpi_profiles.name");

	$query1 ="INSERT INTO `glpi_plugin_barscode_profiles` ( `ID`, `name` , `interface`, `is_default`, `barscode`) VALUES ('$ID', '$name','barscode','0',NULL);";

	$db->query($query1);

}

?>

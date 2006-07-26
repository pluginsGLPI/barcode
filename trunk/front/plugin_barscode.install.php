<?php
/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2005 by the INDEPNET Development Team.
 
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
 
// ----------------------------------------------------------------------
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------
include ("_relpos.php");
include ($phproot . "/inc/includes.php");

	if (haveRight("config","w") && haveRight("profile","w")){

 
if(!TableExists("glpi_plugin_barscode_config"))
	plugin_barscode_installv11();
	glpi_header($_SERVER['HTTP_REFERER']);
	}else{
	commonHeader($lang["login"][5],$_SERVER["PHP_SELF"]);
		echo "<div align='center'><br><br><img src=\"".$HTMLRel."pics/warning.png\" alt=\"warning\"><br><br>";
		echo "<b>".$lang["login"][5]."</b></div>";
		commonFooter();
		}

?>
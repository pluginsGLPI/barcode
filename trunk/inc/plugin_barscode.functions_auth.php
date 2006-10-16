<?php
/*
 * @version $Id: auth.function.php 3576 2006-06-12 08:40:44Z moyo $
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

function plugin_barscode_initSession()
{
	$prof=new plugin_barscode_Profile();
	$prof->getFromDBForUser($_SESSION["glpiID"]);
	$_SESSION["glpi_plugin_barscode_profile"]=$prof->fields;

}

function plugin_barscode_haveRight($module,$right){
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_barscode_profile"][$module])&&in_array($_SESSION["glpi_plugin_barscode_profile"][$module],$matches[$right]))
		return true;
	else return false;
}


/**
 * Get The Type Name of the Object
 *
 * @return String: name of the object type in the current LANGuage
 */
function plugin_barscode_haveTypeRight ($type,$right){
	global $LANG;

	switch ($type){
		case GENERAL_TYPE :
			return true;;
			break;
		case COMPUTER_TYPE :
			return haveRight("computer",$right);
			break;

	}
	return false;
}


function plugin_barscode_checkRight($module,$right) {
	global $LANG,$CFG_GLPI,$HEADER_LOADED;

	if (!plugin_barscode_haveRight($module,$right)){
		if (!$HEADER_LOADED){
			if (!isset($_SESSION["glpi_plugin_barscode_profile"]["interface"]))
				nullHeader($LANG["login"][5],$_SERVER["PHP_SELF"]);
			else if ($_SESSION["glpi_plugin_barscode_profile"]["interface"]=="barscode")
				commonHeader($LANG["login"][5],$_SERVER["PHP_SELF"]);

		}
		echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
		echo "<b>".$LANG["login"][5]."</b></div>";
		nullFooter();
		exit();
	}
}


function plugin_barscode_checkLoginUser(){

	global $LANG,$CFG_GLPI,$HEADER_LOADED;

	if (!isset($_SESSION["glpiname"])){
		if (!$HEADER_LOADED){
			if (!isset($_SESSION["glpi_plugin_barscode_profile"]["interface"]))
				nullHeader($LANG["login"][5],$_SERVER["PHP_SELF"]);
			else if ($_SESSION["glpi_plugin_barscode_profile"]["interface"]=="barscode")
				commonHeader($LANG["login"][5],$_SERVER["PHP_SELF"]);

		}
		echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
		echo "<b>".$LANG["login"][5]."</b></div>";
		nullFooter();
		exit();
	}
}


?>

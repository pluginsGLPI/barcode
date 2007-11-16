<?php
/*
 * @version $Id$
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
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT . "/inc/includes.php");

$NEEDED_ITEMS=array("profile");

checkRight("profile","r");
$prof=new plugin_barscode_Profile();

commonHeader($LANG["Menu"][35],$_SERVER["PHP_SELF"],"plugins","barscode");

if(!isset($_POST["ID"])) $ID=0;
else $ID=$_POST["ID"];

if (isset($_POST["add"])){

	checkRight("profile","w");
	$ID=$prof->add($_POST);

}else  if (isset($_POST["delete"])){
	checkRight("profile","w");

	$prof->delete($_POST);
	$ID=0;
}elseif (isset($_POST["delete_profile"])){
	
	foreach ($_POST["item"] as $key => $val){
		if ($val==1) {

			$query="DELETE FROM glpi_plugin_barscode_profiles WHERE ID='".$key."'";
			$DB->query($query);
		}
	}
			
	glpi_header($_SERVER['HTTP_REFERER']);
		
}
else  if (isset($_POST["update"])){
	checkRight("profile","w");

	$prof->update($_POST);
}

echo "<div align='center'><form method='post' name='massiveaction_form' id='massiveaction_form'  action=\"./plugin_barscode.profile.php\">";

echo "<table class='tab_cadre' cellpadding='5'><tr><th colspan='4'>";
echo $LANGBARSCODE["profile"][6]." : </th></tr>";
echo "<tr><th></th><th></th><th>".$LANG["Menu"][35]."</th><th>".$LANGBARSCODE["profile"][1]."</th></tr>";

$query0="SELECT * FROM glpi_plugin_barscode_profiles ORDER BY name";
$result0=$DB->query($query0);

while ($data0=$DB->fetch_assoc($result0)){
	$ID0=$data0['ID'];
	echo "<tr class='tab_bg_1'>";
	echo "<td align='center'>";
	echo "<input type='hidden' name='ID' value='$ID0'>";
	echo "<input type='checkbox' name='item[$ID0]' value='1'>";
	echo "</td>";
	echo "<td>".$data0['ID']."</td><td>".$data0['name']."</td>";
	if ($data0['barscode']=='r')
		echo "<td>".$LANG["profiles"][10]."</td>";
	elseif ($data0['barscode']=='w')
		echo "<td>".$LANG["profiles"][11]."</td>";
	else
		echo "<td>".$LANG["profiles"][12]."</td>";
}

echo "<tr class='tab_bg_1'><td colspan='4'>";
echo "<div align='center'><a onclick= \"if ( markAllRows('massiveaction_form') ) return false;\" href='".$_SERVER['PHP_SELF']."?select=all'>".$LANG["buttons"][18]."</a>";
echo " - <a onclick= \"if ( unMarkAllRows('massiveaction_form') ) return false;\" href='".$_SERVER['PHP_SELF']."?select=none'>".$LANG["buttons"][19]."</a> ";
echo "<input type='submit' name='delete_profile' value=\"".$LANG["buttons"][6]."\" class='submit' ></div></td></tr>";
			
echo "</table></form></div>";

echo "<div align='center'><form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/barscode/front/plugin_barscode.profile.php\">";
echo "<table class='tab_cadre' cellpadding='5'><tr><th colspan='2'>";
echo $LANG["profiles"][1].": </th></tr><tr class='tab_bg_1'><td>";

$query="SELECT ID, name FROM glpi_profiles ORDER BY name";
$result=$DB->query($query);

echo "<select name='ID'>";
while ($data=$DB->fetch_assoc($result)){
	echo "<option value='".$data["ID"]."' ".($ID==$data["ID"]?"selected":"").">".$data['name']."</option>";
}
echo "</select>";
echo "<td><input type='submit' value=\"".$LANG["buttons"][2]."\" class='submit' ></td></tr>";
echo "</table></form></div>";

if ($ID>0){	
	if ($prof->GetfromDB($ID)){
		$prof->showprofileForm($_SERVER["PHP_SELF"],$ID);
	}
	else {
		plugin_barscode_createaccess($ID);
		$prof->showprofileForm($_SERVER["PHP_SELF"],$ID);
	}
}

commonFooter();

?>
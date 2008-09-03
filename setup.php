<?php
/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2006 by the INDEPNET Development Team.

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

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

include_once ("inc/plugin_barscode.functions_auth.php");
include_once ("inc/plugin_barscode.functions_display.php");
include_once ("inc/plugin_barscode.functions_db.php");
include_once ("inc/plugin_barscode.functions_setup.php");
include_once ("inc/plugin_barscode.barscode.class.php");
include_once ("inc/plugin_barscode.profile.class.php");
include_once ("inc/plugin_barscode.preference.class.php");
include_once ("inc/plugin_barscode.dropdown.function.php");
include_once ("inc/plugin_barscode.constants.php");

require('fpdf/code39.php');
require('fpdf/avery.php');


// Init the hooks of the plugins -Needed
function plugin_init_barscode() {
	global $PLUGIN_HOOKS,$LANGBARSCODE,$CFG_GLPI;

	$PLUGIN_HOOKS['init_session']['barscode'] = 'plugin_barscode_initSession';
	$PLUGIN_HOOKS['change_profile']['barscode'] = 'plugin_barscode_changeprofile';

	pluginNewType('barscode',"PLUGIN_BARSCODE_TYPE",1350,"plugin_barscode","glpi_plugin_barscode_config","front/plugin_barscode.config.php",$LANGBARSCODE["title"][1]);
	
	if (isset($_SESSION["glpiID"])){

		// Display a menu entry ?		
		if (plugin_barscode_haveRight("barscode","r") && (isPluginBarscodeInstalled() && isset($_SESSION["glpi_plugin_barscode_profile"]))){
	
			$PLUGIN_HOOKS['use_massive_action']['barscode']=1;
			$PLUGIN_HOOKS['menu_entry']['barscode'] = true;
            $PLUGIN_HOOKS['submenu_entry']['barscode']['config'] = 'front/plugin_barscode.config.php';
            $PLUGIN_HOOKS['pre_item_delete']['barscode'] = 'plugin_pre_item_delete_barscode';
            $PLUGIN_HOOKS['headings']['barscode'] = 'plugin_get_headings_barscode';
			$PLUGIN_HOOKS['headings_action']['barscode'] = 'plugin_headings_actions_barscode';

		}
		// Config page
		if (plugin_barscode_haveRight("barscode","r") || haveRight("config","w"))
			$PLUGIN_HOOKS['config_page']['barscode'] = 'front/plugin_barscode.config.php';	
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_barscode(){
	global $LANGBARSCODE;
	return array( 'name'    => $LANGBARSCODE["title"][1],
			'minGlpiVersion' => '0.71',
			'version' => '1.4');
}

// Hook done on delete item case

function plugin_pre_item_delete_barscode($input){
	if (isset($input["_item_type_"]))
		switch ($input["_item_type_"]){
			case PROFILE_TYPE :
				// Manipulate data if needed 
				$plugin_barscode_Profile=new plugin_barscode_Profile;
				$plugin_barscode_Profile->cleanProfiles($input["ID"]);
				break;
		}
	return $input;
}

// Define rights for the plugin types
function plugin_barscode_haveTypeRight($type,$right){
	switch ($type){
		case COMPUTER_TYPE :
		case MONITOR_TYPE :
		case NETWORKING_TYPE :
		case PRINTER_TYPE :
		case PHONE_TYPE:
		case SOFTWARE_TYPE:
			return haveRight($type,$right);
			break;
	}
}

// Define actions :
function plugin_barscode_MassiveActions($type){
	global $LANGBARSCODE;
	switch ($type){
		case COMPUTER_TYPE :
		case MONITOR_TYPE :
		case NETWORKING_TYPE :
		case PRINTER_TYPE :
		case PHONE_TYPE:
		case SOFTWARE_TYPE:
			return array(
				"plugin_barscode_generateBarcode"=>$LANGBARSCODE["massiveaction"][0],
			);
			break;
	}
	return array();
}

// How to display specific actions ?
function plugin_barscode_MassiveActionsDisplay($type,$action){
	global $LANG;

	switch ($type){
		case COMPUTER_TYPE :
		case MONITOR_TYPE :
		case NETWORKING_TYPE :
		case PRINTER_TYPE :
		case PHONE_TYPE:
		case SOFTWARE_TYPE:
			switch ($action){
				case "plugin_barscode_generateBarcode":
				echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG["buttons"][2]."\" >";
				break;
			}
			break;
	}
	return "";
}

// How to process specific actions ?
function plugin_barscode_MassiveActionsProcess($data){
	global $LANG;
	
	$values = array();
	
	switch ($data['action']){
		case 'plugin_barscode_generateBarcode':
				foreach ($data['item'] as $key => $val){
					if ($val==1)
					{
						$tmp = plugin_barscode_getValuesByTypeAndID($data["device_type"],$key);
						if ($tmp != '')
						$values[] = $tmp;
					}
						
				}

			$_SESSION["plugin_barscode"]["values"] = serialize($values);
			
			echo "<script type='text/javascript'>location.href='../plugins/barscode/front/plugin_barscode.export.massive.php'</script>)";
			break;
	}
}


function plugin_get_headings_barscode($type,$withtemplate){
	global $LANGBARSCODE;
	switch ($type){
		/*case COMPUTER_TYPE :
		case MONITOR_TYPE :
		case NETWORKING_TYPE :
		case PRINTER_TYPE :
		case PHONE_TYPE:
		case SOFTWARE_TYPE:*/
		case "prefs":
			// template case
			if ($withtemplate){
				return array();
			} else { // Non template case
				return array(1 => $LANGBARSCODE["massiveaction"][0]);
                        }
			break;
	}
	return false;
}

// Define headings actions added by the plugin	 
function plugin_headings_actions_barscode($type){

	switch ($type){
		/*case COMPUTER_TYPE :
			return array(1 => "plugin_barscode_showBarscodeForm");

			break;*/
		case "prefs":
			return array(1 => "plugin_headings_barscode");

			break;
	}
	return false;
}

// action heading
function plugin_headings_barscode($type,$ID,$withtemplate=0){
	global $CFG_GLPI;

		switch ($type){
			case "prefs":
				$pref = new plugin_barscode_UserPreferences;
				$pref->showForm($CFG_GLPI['root_doc']."/plugins/barscode/front/plugin_barscode.preferences.form.php");
			break;
			default :
			break;
		}

}

?>
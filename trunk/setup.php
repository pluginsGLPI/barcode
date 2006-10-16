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
include_once ("inc/plugin_barscode.classes.php");

require('fpdf/code39.php');
require('fpdf/avery.php');


// Init the hooks of the plugins -Needed
function plugin_init_barscode() {
	global $PLUGIN_HOOKS;
	$PLUGIN_HOOKS['init_session']['barscode'] = 'plugin_barscode_initSession'; 
	if (isset($_SESSION["glpiID"])){

		// Display a menu entry ?
		if (plugin_barscode_haveRight("barscode","r") || haveRight("config","w"))
			$PLUGIN_HOOKS['menu_entry']['barscode'] = true;
		// Setup/Update functions
		// Config function
		if(TableExists("glpi_plugin_barscode_config") && haveRight("config","w"))
			$PLUGIN_HOOKS['config']['barscode'] = 'plugin_config_barscode';
		// Config page
		if(TableExists("glpi_plugin_barscode_config") && haveRight("config","w"))
			$PLUGIN_HOOKS['config_page']['barscode'] = 'front/plugin_barscode.config.php';
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_barscode(){
	global $LANGBARSCODE;
	return array( 'name'    => $LANGBARSCODE["title"][1],
			'version' => '1.3');
}




?>

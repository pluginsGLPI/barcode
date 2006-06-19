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


function plugin_barscode_Installv11() {

	$db = new DB;
	$query1 = "CREATE TABLE `glpi_plugin_barscode_config` (
  `ID` int(11) NOT NULL auto_increment,
  `margeL` int(11) NOT NULL default '0',
  `margeT` varchar(255) NOT NULL default '',
  `margeH` varchar(255) NOT NULL default '',
  `margeW` varchar(255) NOT NULL default '',
  `etiquetteW` varchar(255) NOT NULL default '',
  `etiquetteH` varchar(255) NOT NULL default '',
  `etiquetteR` varchar(255) NOT NULL default '',
  `etiquetteC` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM";
	
	$db->query($query1) or die($db->error());
	$query3 = "INSERT INTO `glpi_plugin_barscode_config` ( `ID` , `margeL` , `margeT` , `margeH` , `margeW`, `etiquetteW`, `etiquetteH`, `etiquetteR`, `etiquetteC` )VALUES ('1', '2', '8.6', '4', '0', '66.5', '35.9', '3', '8')";
	$db->query($query3) or die($db->error());
	
}

function plugin_barscode_uninstallv11() {

	$db = new DB;
	$query = "DROP TABLE `glpi_plugin_barscode_config`;";
	$db->query($query) or die($db->error());

}


?>
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
if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

function plugin_barscode_dropdownPageFormat($name,$value)
{
	global $LANGBARSCODE;
	$values["A3"]=$LANGBARSCODE["bc"][6];
	$values["A4"]=$LANGBARSCODE["bc"][5];
	$values["A5"]=$LANGBARSCODE["bc"][7];
	dropdownArrayValues($name,$values,$value);
}

function plugin_barscode_dropdownPDFFormat($name,$value)
{
	global $LANG;
	$values["L"]=$LANG["buttons"][27]." ".$LANG["common"][68];
	$values["P"]=$LANG["buttons"][27]." ".$LANG["common"][69];
	dropdownArrayValues($name,$values,$value);
}

function plugin_barscode_dropdownPrintField($name,$value)
{
	global $BARSCODE_FIELD;
	$values = array();
	
	foreach ($BARSCODE_FIELD as $key => $tmp)
		$values[$key]=$tmp['name'];
	
	dropdownArrayValues($name,$values,$value);	
}
?>
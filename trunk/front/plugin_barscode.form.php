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

if(isset($_GET)) $tab = $_GET;
if(empty($tab) && isset($_POST)) $tab = $_POST;
if(!isset($tab["ID"])) $tab["ID"] = "";
if(!isset($_POST["nb"])) $_POST["nb"] = "";
if(!isset($_POST["from"])) $_POST["from"] = "";
if(!isset($_POST["lenght"])) $_POST["lenght"] = "";
if(!isset($_POST["prefixe"])) $_POST["prefixe"] = "";

if(!isset($_POST["size"])) $_POST["size"] = "";
if(!isset($_POST["format"])) $_POST["format"] = "";

	define('FPDF_FONTPATH','fpdf/font/');
	include ("_relpos.php");
	

include ($phproot . "/inc/includes.php");

	
plugin_barscode_print($_POST["nb"],$_POST["from"],$_POST["lenght"],$_POST["prefixe"],$_POST["size"],$_POST["format"]);

commonFooter();
?>
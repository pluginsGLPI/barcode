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


class plugin_barscode extends CommonDBTM {

	function plugin_barscode () {
		$this->table="glpi_plugin_barscode_config";
	}
	
function title(){

         GLOBAL  $langbc,$HTMLRel;
         
	     echo "<div align='center'><table border='0'><tr><td>";
                echo "<img src=\"./pics/barscode.png\" alt='".$langbc["title"][0]."' title='".$langbc["title"][0]."'></td><td align ='center'><b><span class='icon_nav'>".$langbc["title"][0]."</span>";
		 echo "</b><tr><td>&nbsp;</td></tr></tr></table>&nbsp;</div>";
}

}

?>
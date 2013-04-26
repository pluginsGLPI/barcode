<?php
/*
 * @version $Id: document.send.php 10411 2010-02-09 07:58:26Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------


define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if (!$CFG_GLPI["use_public_faq"]) {
   checkLoginUser();
}

$doc = new Document;

if (isset($_GET["file"])) { // for other file
   $splitter = explode("/",$_GET["file"]);
   if (count($splitter) == 2) {
      $send = false;

      if ($splitter[0] == "barcode") {
         $send = true;
      }

      if (file_exists(GLPI_PLUGIN_DOC_DIR."/".$_GET["file"])) {
         sendFile(GLPI_PLUGIN_DOC_DIR."/".$_GET["file"],$splitter[1]);
      } else {
         displayErrorAndDie($LANG['document'][45],true);
      }
   } else {
      displayErrorAndDie($LANG['document'][44],true);
   }
}

?>

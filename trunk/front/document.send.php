<?php

/*
   ------------------------------------------------------------------------
   Barcode
   Copyright (C) 2009-2014 by the Barcode plugin Development Team.

   https://forge.indepnet.net/projects/barscode
   ------------------------------------------------------------------------

   LICENSE

   This file is part of barcode plugin project.

   Plugin Barcode is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Barcode is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Plugin Barcode. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Barcode
   @author    David Durieux
   @co-author
   @copyright Copyright (c) 2009-2014 Barcode plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/barscode
   @since     2009

   ------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!$CFG_GLPI["use_public_faq"]) {
   Session::checkLoginUser();
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
         Toolbox::sendFile(GLPI_PLUGIN_DOC_DIR."/".$_GET["file"],$splitter[1]);
      } else {
         Html::displayErrorAndDie(__('Unauthorized access to this file'),true);
      }
   } else {
      Html::displayErrorAndDie(__('Invalid Filename'),true);
   }
}

?>

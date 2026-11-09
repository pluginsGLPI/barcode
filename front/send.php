<?php

/*
   ------------------------------------------------------------------------
   Barcode
   Copyright (C) 2009-2016 by the Barcode plugin Development Team.

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
   @copyright Copyright (c) 2009-2016 Barcode plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/barscode
   @since     2009

   ------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!defined("GLPI_PLUGIN_DOC_DIR")) {
    define("GLPI_PLUGIN_DOC_DIR", GLPI_ROOT . "/files/_plugins");
}
$docDir = GLPI_PLUGIN_DOC_DIR.'/barcode';

// Check session and access rights
Session::checkLoginUser();

if (isset($_GET['file'])) {
    $filename = basename($_GET['file']); // Sanitize input
    $allowed_extensions = ['zip', 'bmp', 'png', 'pdf'];

    // Check file extension
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        echo "Invalid file type.";
        exit;
    }

    $file = realpath($docDir.'/'.$filename);

    // Ensure the file is within the designated directory
    if (strpos($file, realpath($docDir)) !== 0 || !file_exists($file)) {
        echo "File does not exist or access denied.";
        exit;
    }

    // Send the file with headers
    header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
    header('Pragma: private');
    header('Cache-control: private, must-revalidate');
    header("Content-disposition: attachment; filename=\"$filename\"");

    switch ($extension) {
        case 'zip':
            header("Content-type: application/zip");
            break;
        case 'png':
            header("Content-type: image/png");
            break;
        case 'bmp':
            header("Content-type: image/bmp");
            break;
        case 'pdf':
            header("Content-type: application/pdf");
            break;
        default:
            header("Content-type: application/octet-stream");
            break;
    }

    readfile($file);
}

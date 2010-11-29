<?php
/*
 * @version $Id: HEADER 10411 2010-02-09 07:58:26Z moyo $
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------


$LANG['plugin_barcode']["name"] = "Code-barres";

$LANG['plugin_barcode']["title"][1] = "Configuration du plugin code-barres";
$LANG['plugin_barcode']["title"][2] = "Mise en page";
$LANG['plugin_barcode']["title"][3] = "Génération";
$LANG['plugin_barcode']["title"][4] = "Imprimer les code-barres";
$LANG['plugin_barcode']["title"][5] = "Configuration générale";

$LANG['plugin_barcode']["print"][0] = "Nombre d'étiquettes à imprimer";
$LANG['plugin_barcode']["print"][1] = "Numéro de début";
$LANG['plugin_barcode']["print"][2] = "Longueur des codes barres";
$LANG['plugin_barcode']["print"][3] = "Préfixe devant le numéro";
$LANG['plugin_barcode']["print"][4] = "Format de la page";
$LANG['plugin_barcode']["print"][8] = "Orientation";
$LANG['plugin_barcode']["print"][9] = "Portrait";
$LANG['plugin_barcode']["print"][10] = "Paysage";
$LANG['plugin_barcode']["print"][11] = "Créer";
$LANG['plugin_barcode']["print"][12] = "Code";
$LANG['plugin_barcode']["print"][13] = "Type";
$LANG['plugin_barcode']["print"][14] = "Marges";
$LANG['plugin_barcode']["print"][15] = "Haut";
$LANG['plugin_barcode']["print"][16] = "Bas";
$LANG['plugin_barcode']["print"][17] = "Gauche";
$LANG['plugin_barcode']["print"][18] = "Droite";
$LANG['plugin_barcode']["print"][19] = "Intérieure horizontale";
$LANG['plugin_barcode']["print"][20] = "Intérieure verticale";
$LANG['plugin_barcode']["print"][21] = "Tailles des code-barres";
$LANG['plugin_barcode']["print"][22] = "Largeur maximale";
$LANG['plugin_barcode']["print"][23] = "Hauteur maximale";

$LANG['plugin_barcode']["message"][0] = "Fichier généré";
$LANG['plugin_barcode']["message"][1] = "La génération de certains code-barres a renvoyé des erreurs.";
$LANG['plugin_barcode']["message"][2] = "Vider le cache";
$LANG['plugin_barcode']["message"][3] = "Le cache a été vidé.";

$LANG['plugin_barcode']["code"][0] = "code39";
$LANG['plugin_barcode']["code"][1] = "code128";
$LANG['plugin_barcode']["code"][2] = "ean13";
$LANG['plugin_barcode']["code"][3] = "int25";
$LANG['plugin_barcode']["code"][4] = "postnet";
$LANG['plugin_barcode']["code"][5] = "upca";

$LANG['plugin_barcode']['size'][0] = '4A0';
$LANG['plugin_barcode']['size'][1] = '2A0';
$LANG['plugin_barcode']['size'][2] = 'A0';
$LANG['plugin_barcode']['size'][3] = 'A1';
$LANG['plugin_barcode']['size'][4] = 'A2';
$LANG['plugin_barcode']['size'][5] = 'A3';
$LANG['plugin_barcode']['size'][6] = 'A4';
$LANG['plugin_barcode']['size'][7] = 'A5';
$LANG['plugin_barcode']['size'][8] = 'A6';
$LANG['plugin_barcode']['size'][9] = 'A7';
$LANG['plugin_barcode']['size'][10] = 'A8';
$LANG['plugin_barcode']['size'][11] = 'A9';
$LANG['plugin_barcode']['size'][12] = 'A10';
$LANG['plugin_barcode']['size'][13] = 'B0';
$LANG['plugin_barcode']['size'][14] = 'B1';
$LANG['plugin_barcode']['size'][15] = 'B2';
$LANG['plugin_barcode']['size'][16] = 'B3';
$LANG['plugin_barcode']['size'][17] = 'B4';
$LANG['plugin_barcode']['size'][18] = 'B5';
$LANG['plugin_barcode']['size'][19] = 'B6';
$LANG['plugin_barcode']['size'][20] = 'B7';
$LANG['plugin_barcode']['size'][21] = 'B8';
$LANG['plugin_barcode']['size'][22] = 'B9';
$LANG['plugin_barcode']['size'][23] = 'B10';
$LANG['plugin_barcode']['size'][24] = 'C0';
$LANG['plugin_barcode']['size'][25] = 'C1';
$LANG['plugin_barcode']['size'][26] = 'C2';
$LANG['plugin_barcode']['size'][27] = 'C3';
$LANG['plugin_barcode']['size'][28] = 'C4';
$LANG['plugin_barcode']['size'][29] = 'C5';
$LANG['plugin_barcode']['size'][30] = 'C6';
$LANG['plugin_barcode']['size'][31] = 'C7';
$LANG['plugin_barcode']['size'][32] = 'C8';
$LANG['plugin_barcode']['size'][33] = 'C9';
$LANG['plugin_barcode']['size'][34] = 'C10';
$LANG['plugin_barcode']['size'][35] = 'RA0';
$LANG['plugin_barcode']['size'][36] = 'RA1';
$LANG['plugin_barcode']['size'][37] = 'RA2';
$LANG['plugin_barcode']['size'][38] = 'RA3';
$LANG['plugin_barcode']['size'][39] = 'RA4';
$LANG['plugin_barcode']['size'][40] = 'SRA0';
$LANG['plugin_barcode']['size'][41] = 'SRA1';
$LANG['plugin_barcode']['size'][42] = 'SRA2';
$LANG['plugin_barcode']['size'][43] = 'SRA3';
$LANG['plugin_barcode']['size'][44] = 'SRA4';
$LANG['plugin_barcode']['size'][45] = 'LETTER';
$LANG['plugin_barcode']['size'][46] = 'LEGAL';
$LANG['plugin_barcode']['size'][47] = 'EXECUTIVE';
$LANG['plugin_barcode']['size'][48] = 'FOLIO';
?>

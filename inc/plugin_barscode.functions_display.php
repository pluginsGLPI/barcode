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
if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

function plugin_barscode_print($nb,$begin,$lenght,$prefixe,$size,$format){

	GLOBAL  $DB,$LANG, $LANGBARSCODE, $PDF;

	$Logo['F']   = '../pics/logo.png';	// Fichier du logo

	$plugin_barscode=new plugin_barscode();
	$plugin_barscode->getFromDB(1);

	$Marge['L'] = $plugin_barscode->fields["margeL"];			// Marge a gauche
	$Marge['T'] = $plugin_barscode->fields["margeT"];		// Marge en Haut
	$Marge['H'] = $plugin_barscode->fields["margeH"];			// Marge Horizontale entre les etiquettes (entre chaque colonne d'etiquettes)
	$Marge['W'] = $plugin_barscode->fields["margeW"];			// Marge Verticale entre les etiquettes (entre chaque ligne d'etiquettes)

	$Etiquette['W'] = $plugin_barscode->fields["etiquetteW"];		// Largeur des etiquette
	$Etiquette['H'] = $plugin_barscode->fields["etiquetteH"];		// Hauteur des etiquette
	$Etiquette['R'] = $plugin_barscode->fields["etiquetteR"];			// NB etiquettes par ligne (rows)
	$Etiquette['C'] = $plugin_barscode->fields["etiquetteC"];			// NB etiquettes par colonne (cols)
	$Etiquette['RL']= $plugin_barscode->fields["etiquetteRL"];		//NB etiquette par ligne en mode Paysage
	$Etiquette['CL']= $plugin_barscode->fields["etiquetteCL"];		//NB etiquette par colonne en mode Paysage

	$PDF=new PDF_Avery();
	$PDF->AliasNbPages(); 
	$PDF->PDF($format,'mm', $size);
	$PDF->SetTopMargin('0');
	$PDF->SetRightMargin('0');
	$PDF->SetLeftMargin('0');
	$PDF->SetMargins('0', '0', '0');

	$PDF->Open();
	$PDF->SetFont('Arial','',6);
	$PDF->SetTextColor(0,0,0);

	if($format=='L')
		$nbppage = $Etiquette['RL'] * $Etiquette['CL'];
	else
		$nbppage = $Etiquette['R'] * $Etiquette['C'];
	
	if ($nb<$begin) $nb=$nb+$begin;
	for ($page=1; $page<=ceil(($nb) / $nbppage); $page++)
	{

		$from = ($page * ($nbppage)) - ($nbppage) + $begin;
		$to = $from + ($nbppage) - 1;
		if ($begin==0){$to=$nb-1;}
		if ($to > $nb-1) { $to = $nb; }
		$PDF->AddPage();
		$PDF->Cell(0,3, $LANGBARSCODE["config"][12].$PDF->PageNo().'/{nb}',0,0,'C'); 
		plugin_barscode_GeneratePage($Etiquette, $Marge, $Logo, $from, $to, $lenght, $prefixe, $format);
	}

	$PDF->Output();

}

function plugin_barscode_GenerateEtiquette($X_From, $Y_From, $X_To, $Y_To, $Weight, $Height, $Logo, $num, $length, $prefixe)
{
	GLOBAL  $LANG, $LANGBARSCODE, $PDF;

	$PDF->Image($Logo['F'], $X_From+2, $Y_From+2);

	$PDF->SetFont('Arial','',8);
	$PDF->SetTextColor(192,192,192);
	$PDF->SetXY($X_From+20, $Y_From+2);
	$PDF->Cell(43, 5, utf8_decode($LANGBARSCODE["config"][11]).date("j M Y"), 0, 0, 'R');

	$PDF->SetTextColor(0,0,0);

	if (strlen($prefixe) > 0) { $length = $length - strlen($prefixe); }
	$num = str_pad($num, $length, '0', STR_PAD_LEFT);

	$PDF->Code39($X_From+5, $Y_From+12, $prefixe.$num, '8', 0.5,$Height);
}


function plugin_barscode_GeneratePage($Etiquette, $Marge, $Logo, $from, $to, $length, $prefixe, $format)
{

	if($format == 'L')
	{
		$nbligne = $Etiquette['CL'];
		$nbcolone = $Etiquette['RL'];
	}
	else
	{
		$nbligne = $Etiquette['C'];
		$nbcolone = $Etiquette['R'];
	}
	for ($row=1; ( ($row<=$nbligne) && ($from <= $to) ); $row++)
	{
		$Y_From = $Marge['T']+( (($row-1)*$Etiquette['H']) + (($row-1)*$Marge['W']) );;
		$Y_To   = $Y_From+$Etiquette['H'];
		for ($col=1; ( ($col<=$nbcolone) && ($from <= $to) ); $col++)
		{
			$X_From = $Marge['L']+( (($col-1)*$Etiquette['W']) + (($col-1)*$Marge['H']) );
			$X_To   = $X_From+$Etiquette['W'];
			plugin_barscode_GenerateEtiquette($X_From, $Y_From, $X_To, $Y_To, $Etiquette['W'], $Etiquette['H'], $Logo, $from, $length, $prefixe);
			$from++;
		}
	}
}

?>

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



function plugin_barscode_FormConfig($target, $id) {

	GLOBAL  $lang, $langbc;
	
	$db = new DB;
	$query = "select * from glpi_plugin_barscode_config where ID = '".$id."'";
	$result = $db->query($query);
	echo "<form name='formconfig' action=\"$target\" method=\"post\">";
	echo "<div align='center'><table class='tab_cadre'>";
	echo "<tr><th colspan='2'>".$langbc["title"][2]."</th></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$langbc["config"][0]." </td><td> <input type=\"text\" name=\"margeL\" value=\"".$db->result($result,0,"margeL")."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$langbc["config"][1]." </td><td> <input type=\"text\" name=\"margeT\" value=\"".$db->result($result,0,"margeT")."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$langbc["config"][2]." </td><td> <input type=\"text\" name=\"margeH\" value=\"".$db->result($result,0,"margeH")."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$langbc["config"][3]." </td><td> <input type=\"test\" name=\"margeW\" value=\"".$db->result($result,0,"margeW")."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$langbc["config"][4]." </td><td> <input type=\"text\" name=\"etiquetteW\" value=\"".$db->result($result,0,"etiquetteW")."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$langbc["config"][5]." </td><td> <input type=\"text\" name=\"etiquetteH\" value=\"".$db->result($result,0,"etiquetteH")."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$langbc["config"][6]." </td><td> <input type=\"text\" name=\"etiquetteR\" value=\"".$db->result($result,0,"etiquetteR")."\"></td></tr>";
	echo "<tr class='tab_bg_2'><td align='center'>".$langbc["config"][7]." </td><td> <input type=\"text\" name=\"etiquetteC\" value=\"".$db->result($result,0,"etiquetteC")."\"></td></tr>";
	echo "</table></div>";
	echo "<p class=\"submit\"><input type=\"submit\" name=\"update_conf_bc\" class=\"submit\" value=\"".$langbc["buttons"][0]."\" ></p>";
	echo "</form>";
	
}


function plugin_barscode_print($nb,$from,$lenght,$prefixe,$size,$format){

	GLOBAL  $lang, $langbc, $pdf;

	$Logo['F']   = '../pics/logo.png';	// Fichier du logo

	$db = new DB;
	$query = "SELECT * FROM glpi_plugin_barscode_config";
	
	$result = $db->query($query);
	$number = $db->numrows($result);
	$i = 0;
	
	$db->query($query) or die("Lien impossible : ".$db->error());

	$Marge['L'] = $db->result($result, $i, "margeL");			// Marge a gauche
	$Marge['T'] = $db->result($result, $i, "margeT");		// Marge en Haut
	$Marge['H'] = $db->result($result, $i, "margeH");			// Marge Horizontale entre les etiquettes (entre chaque colonne d'etiquettes)
	$Marge['W'] = $db->result($result, $i, "margeW");			// Marge Verticale entre les etiquettes (entre chaque ligne d'etiquettes)
	
	$Etiquette['W'] = $db->result($result, $i, "etiquetteW");		// Largeur des etiquette
	$Etiquette['H'] = $db->result($result, $i, "etiquetteH");		// Hauteur des etiquette
	$Etiquette['R'] = $db->result($result, $i, "etiquetteR");			// NB etiquettes par ligne (rows)
	$Etiquette['C'] = $db->result($result, $i, "etiquetteC");			// NB etiquettes par colonne (cols)

	$pdf=new PDF_Avery();
	$pdf->AliasNbPages(); 
	$pdf->PDF($format,'mm', $size);
	$pdf->SetTopMargin('0');
	$pdf->SetRightMargin('0');
	$pdf->SetLeftMargin('0');
	$pdf->SetMargins('0', '0', '0');
	
	$pdf->Open();
	$pdf->SetFont('Arial','',6);
	$pdf->SetTextColor(0,0,0);

for ($page=1; $page<=ceil($nb / ($Etiquette['R'] * $Etiquette['C'])); $page++)
	{
		$from = ($page * ($Etiquette['R'] * $Etiquette['C'])) - ($Etiquette['R'] * $Etiquette['C']) + $from;
		$to = $from + ($Etiquette['R'] * $Etiquette['C']) - 1;
		if ($to > $nb) { $to = $nb; }
		$pdf->AddPage();
		$pdf->Cell(0,3, $langbc["config"][12].$pdf->PageNo().'/{nb}',0,0,'C'); 
		plugin_barscode_GeneratePage($Etiquette, $Marge, $Logo, $from, $to, $lenght, $prefixe);
	}
	
$pdf->Output();

}

function plugin_barscode_GenerateEtiquette($X_From, $Y_From, $X_To, $Y_To, $Weight, $Height, $Logo, $num, $length, $prefixe)
{
	GLOBAL  $lang, $langbc, $pdf;
	
	$pdf->Image($Logo['F'], $X_From+2, $Y_From+2);
	
	$pdf->SetFont('Arial','',8);
	$pdf->SetTextColor(192,192,192);
	$pdf->SetXY($X_From+20, $Y_From+2);
	$pdf->Cell(43, 5, $langbc["config"][11].date("j M Y"), 0, 0, 'R');
	
	$pdf->SetTextColor(0,0,0);
	
	if (strlen($prefixe) > 0) { $length = $length - strlen($prefixe); }
	$num = str_pad($num, $length, '0', STR_PAD_LEFT);
	
	$pdf->Code39($X_From+5, $Y_From+12, $prefixe.$num, '8');
}


function plugin_barscode_GeneratePage($Etiquette, $Marge, $Logo, $from, $to, $length, $prefixe)
{
	for ($row=1; ( ($row<=$Etiquette['C']) && ($from <= $to) ); $row++)
		{
			$Y_From = $Marge['T']+( (($row-1)*$Etiquette['H']) + (($row-1)*$Marge['W']) );;
			$Y_To   = $Y_From+$Etiquette['H'];
			for ($col=1; ( ($col<=$Etiquette['R']) && ($from <= $to) ); $col++)
				{
					$X_From = $Marge['L']+( (($col-1)*$Etiquette['W']) + (($col-1)*$Marge['H']) );
					$X_To   = $X_From+$Etiquette['W'];
					plugin_barscode_GenerateEtiquette($X_From, $Y_From, $X_To, $Y_To, $Etiquette['W'], $Etiquette['H'], $Logo, $from, $length, $prefixe);
					$from++;
				}
		}
}

?>
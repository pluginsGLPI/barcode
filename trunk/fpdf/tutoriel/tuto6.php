<?php
require('../fpdf.php');

class PDF extends FPDF
{
var $B;
var $I;
var $U;
var $HREF;

function PDF($orientation='P',$unit='mm',$format='A4')
{
	//Appel au constructeur parent
	$this->FPDF($orientation,$unit,$format);
	//Initialisation
	$this->B=0;
	$this->I=0;
	$this->U=0;
	$this->HREF='';
}

function WriteHTML($html)
{
	//Parseur HTML
	$html=str_replace("\n",' ',$html);
	$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	foreach($a as $i=>$e)
	{
		if($i%2==0)
		{
			//Texte
			if($this->HREF)
				$this->PutLink($this->HREF,$e);
			else
				$this->Write(5,$e);
		}
		else
		{
			//Balise
			if($e{0}=='/')
				$this->CloseTag(strtoupper(substr($e,1)));
			else
			{
				//Extraction des attributs
				$a2=explode(' ',$e);
				$tag=strtoupper(array_shift($a2));
				$attr=array();
				foreach($a2 as $v)
					if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
						$attr[strtoupper($a3[1])]=$a3[2];
				$this->OpenTag($tag,$attr);
			}
		}
	}
}

function OpenTag($tag,$attr)
{
	//Balise ouvrante
	if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,true);
	if($tag=='A')
		$this->HREF=$attr['HREF'];
	if($tag=='BR')
		$this->Ln(5);
}

function CloseTag($tag)
{
	//Balise fermante
	if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,false);
	if($tag=='A')
		$this->HREF='';
}

function SetStyle($tag,$enable)
{
	//Modifie le style et s�lectionne la police correspondante
	$this->$tag+=($enable ? 1 : -1);
	$style='';
	foreach(array('B','I','U') as $s)
		if($this->$s>0)
			$style.=$s;
	$this->SetFont('',$style);
}

function PutLink($URL,$txt)
{
	//Place un hyperlien
	$this->SetTextColor(0,0,255);
	$this->SetStyle('U',true);
	$this->Write(5,$txt,$URL);
	$this->SetStyle('U',false);
	$this->SetTextColor(0);
}
}

$html='Vous pouvez maintenant imprimer facilement du texte m�langeant
diff�rents styles : <B>gras</B>, <I>italique</I>, <U>soulign�</U>, ou
<B><I><U>tous � la fois</U></I></B> !<BR>Vous pouvez aussi ins�rer des
liens sous forme textuelle, comme <A HREF="http://www.fpdf.org">
www.fpdf.org</A>, ou bien sous forme d\'image : cliquez sur le logo.';

$pdf=new PDF();
//Premi�re page
$pdf->AddPage();
$pdf->SetFont('Arial','',20);
$pdf->Write(5,'Pour d�couvrir les nouveaut�s de ce tutoriel, cliquez ');
$pdf->SetFont('','U');
$link=$pdf->AddLink();
$pdf->Write(5,'ici',$link);
$pdf->SetFont('');
//Seconde page
$pdf->AddPage();
$pdf->SetLink($link);
$pdf->Image('logo.png',10,10,30,0,'','http://www.fpdf.org');
$pdf->SetLeftMargin(45);
$pdf->SetFontSize(14);
$pdf->WriteHTML($html);
$pdf->Output();
?>

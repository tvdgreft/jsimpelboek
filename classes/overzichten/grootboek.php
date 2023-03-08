<?php
namespace SIMPELBOEK;
#
# Begroting opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Grootboek extends Overzichten
{
    function Start()
    {
		$overzicht = new Overzicht();
        $this->LoadData();
		$form = new Forms();
		$html='';
		$html .= '<h2>grootboek_' . $GLOBALS['boekhouding'] . '_' . $this->boekjaar . '</h2>';
        $grootboek = $this->Grootboek($this->boekjaar);
        #print_r($grootboek);
		$html .= '<table class="compacttable">';
		foreach($grootboek as $grootboekrekening)
		{
			#echo '<br>grootboekrekening<br>';
			#print_r($grootboekrekening[0]);
			$header=$grootboekrekening[0];
			#$html .= '<br>'.$header[0].$header[1];
			$posts = $grootboekrekening[1];
			/*
			foreach($posts as  $post)
			{
				$html .= '<br>'.$post[0].' | ' . $post[1];
			}
			*/
			$html .= '<tr class="compacttr">';
			$html .= '<th class="compactth">' . $header[0] . '</th>';
			$html .= '<th class="compactth">' . $header[1] . '</th>';
			$html .= '<th class="compactth">' . 'datum' . '</th>';
			$html .= '<th class="compactth">' . 'omschrijving' . '</th>';
			$html .= '<th class="compactthright">' . 'bedrag' . '</th>';
			$html .= '</tr class="compacttr">';
			// beginbalans tonen
			if($header[2] == 'B') 
			{
				$html .= '<tr class="compacttr">';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . 'beginbalans' . '</td>';
				$html .= '<td class="compacttdright">' . $this->Euro($header[3]) . '</td>';
				$html .= '</tr>';
			}
			foreach($posts as $p)
			{
				$html .= '<tr class="compacttr">';
				$html .= '<td class="compacttd">' . $p[0] . '</td>';
				$html .= '<td class="compacttd">' . $p[2] . '</td>';
				$html .= '<td class="compacttd">' . $p[1] . '</td>';
				$html .= '<td class="compacttd">' . $p[3] . '</td>';
				$html .= '<td class="compacttdright">' . $this->Euro($p[4]) . '</td>';
				$html .= '</tr>';
			}
			$html .= '<tr class="compacttr">';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . '' . '</td>';
				$html .= '<td class="compacttd">' . 'totaal' . '</td>';
				$html .= '<td class="compacttdright">' . $this->Euro($header[4]) . '</td>';
				$html .= '</tr>';
		}
		$html .= '</table>';
		
		$filename = 'grootboek_' . $GLOBALS['boekhouding'] . '_' . $this->boekjaar . '.csv';
		$html .= '<span style="display:none">'.$filename.'</span>';				#filename voor export script
		$html .= '<input id="grootboek" name="grootboek" type="hidden" />';
		$form->buttons = [
			['id'=>'exporttable','class'=>'exporttable' ,'value'=>'exporteren'],	#knop voor het exporteren van de table (exportcsv.js)
			['id'=>'cancel','value'=>'terug',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
		];
		$html .= $form->DisplayButtons();
		return($html);
	}
	function Grootboek($boekjaar) : array
	{
        $overzicht = new Overzicht();
        $dbio = new DBIO();
		foreach ($this->vorigebalans as $p) { $balans[$p->rekeningnummer] = $p->bedrag; }
        $grootboek = array();
		foreach ($this->rekeningen as $p)
		{
			$beginbedrag = 0;;
			$totaal=0;
			// Bij balansrekening de beginbalans bepalen
			if($p->soort == 'B')
            {
                if (isset($balans[$p->rekeningnummer])) { $beginbedrag = $totaal = $balans[$p->rekeningnummer]; }
			}
            $grootboekrekening = array();
            $posten=array();
            $grootboekrekening[] = $p->rekeningnummer;
            $grootboekrekening[] = $p->naam;
            $grootboekrekening[] = $p->soort;
			$grootboekrekening[] = $beginbedrag;

			foreach ($this->boekingen as $b)
			{
				$bedrag = $overzicht->PlusOrMin($b->type,$p->rekeningnummer,$b->rekening,$b->tegenrekening,$p->soort,$p->type,$b->bedrag);
                if($bedrag)
                {
                    $posten[] = [$b->id,$b->datum,$b->bankrekeninghouder,$b->omschrijving,$bedrag];
					$totaal += $bedrag;
                }
			}
            #echo '<br>posten<br>';
            #print_r($posten);
			$grootboekrekening[] = $totaal;
            $grootboek[] = [$grootboekrekening,$posten];
		}
		return($grootboek);
    }
}
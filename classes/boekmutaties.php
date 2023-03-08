<?php
namespace SIMPELBOEK;
/**
 * boekingen van mutatiebestanden banken verwerken
 * tegenrekeningen opgeven
 */
class BoekMutaties
{
    public $boekjaar;
    public $boekingen;
    public $table;
    public $primarykey;
	public function Start()        
	{
        #
        # boekjaar inlezen
        #
        $html = '';
        $dbio = new DBIO();
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
        $this->boekjaar = $boekhouding->boekjaar;
        #
        # boekingen waarvan tegenrekening nog niet is opgegeven
        #
        $this->table = Dbtables::boekingen['name']."_".$GLOBALS['boekhouding'];
        $this->boekingen = $dbio->ReadRecords(array("table"=>$this->table,"filters"=>array("tegenrekening"=>'NULL',"datum"=>$this->boekjaar)));
        $this->primarykey="id";	#the primary key of the records
        if(isset($_POST['writeboekingen']))    # 
        {
            return($this->WriteBoekingen());
        }
        else   # formulier om bestand te zoeken met bankmutaties
        {
            return($this->FormOpenBoekingen());
        }
    }
    #
    # Toon tabel met open boekingen
    # @todo: btw bedrag invullen.
    #
    function FormOpenBoekingen()
    {
        $dbio = new DBIO();
        $form = new forms();
        $html = '';
        $html .= '<h1>Bankmutaties verwerken (tegenrekeningen opgeven)</h1>';
        #
        # todo: popup voor toelichting
        #
        
        #
        # array van tegenrekeningen
        #
        $table_rekeningen = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"sort"=>"rekeningnummer ASC"));
        $options = '';
		$options .= '<option value="" selected>selecteer tegenrekening' . '</option>';
        foreach ($rekeningen as $r)
        {
            $naam = sprintf("%s %03d %s %s",$r->naam,$r->rekeningnummer,$r->soort,$r->type);
            $options .= '<option value=' . $r->rekeningnummer . '>' . $naam . '</option>';
        }
        $html .= '<table id="boekingen" class="prana">';
        $html .= '<tr>';
        $html .= '<th>datum</th><th>bedrag</th><th>type</th><th>rekeninghouder</th><th>omschrijving</th><th>tegenrekening</th>';
        $html .= '</tr>';
        $afbij = array("D"=>"AF","C"=>"BIJ");
        foreach($this->boekingen as $b)
        {
            $html .= '<tr>';
            $html .= '<td>'. $b->datum . '</td>';
            $bedrag = number_format(($b->bedrag /100), 2, ',', '');
            $html .= '<td>'. $bedrag . '</td>';
            
            $html .= '<td>'. $afbij[$b->type] . '</td>';
            $html .= '<td>'. $b->bankrekeninghouder . '</td>';
            $html .= '<td>'. $b->omschrijving . '</td>';
            $html .= '<td>';
            $html .= '<select name=' . $b->id . ' style="width:250px;">';
			$html .= $options;
			$html .= '</select>';
            $html .= '</td>';
            $html .= '</tr>';

        }
        $html .= '</table>';
        $html .= '<br>';
        $form->buttons = [
            ['id'=>'writeboekingen','value'=>'opslaan'],
            ['id'=>'cancel','value'=>'annuleren',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="boekmutaties" name="boekmutaties" type="hidden" />';
        return($html);
    }
    function WriteBoekingen()
    {
        $dbio = new dbio;
        $html = '';
        $openboekingen = 0;  #tel het aantal boekingen zonder tegenrekening
        foreach($this->boekingen as $b)
        {
            if($_POST[$b->id])
            {
                $fields=array("tegenrekening"=>$_POST[$b->id]);
                $result = $dbio->ModifyRecord(array("table"=>$this->table,"fields"=>$fields,"key"=>$this->primarykey,"value"=>$b->id));
                $bedrag = number_format(($b->bedrag /100), 2, ',', '');
                $html .= sprintf('<br>boeking %s bedrag %s tegenrekening %s', $_POST[$b->id],$b->id,$bedrag,$_POST[$b->id]);
                if($result == false)
                {
                    $html .= "niet verwerkt";
                    $openboekingen++;
                }
                else $html .= "verwerkt";
            }
            else
            {
                $openboekingen++;
            }
        }
        if($openboekingen)
        {
            $html .= '<br><br>' . sprintf("Nog openstaande boekingen: %d",$openboekingen);
        }
        return($html);
    }
}
?>
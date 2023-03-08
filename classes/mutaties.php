<?php
namespace SIMPELBOEK;
/**
 * mutatiebestand van banken importeren
 */
class Mutaties
{
    public $table;
    public $table_rekeningen;
	public function Start()        
	{
        if(isset($_POST['writemutaties']))    # 
        {
            return($this->WriteMutaties());
        }
        else   # formulier om bestand te zoeken met bankmutaties
        {
            return($this->FormMutaties());
        }
    }
    #
    # Welke bank en welk bestand?
    #
    function FormMutaties()
    {
        $dbio = new DBIO();
        $form = new forms();
        $html = '';
        $html .= sprintf('<h1>Verwerken Bankmutaties</h1>');
        #
        # todo: popup voor toelichting
        #
        $options = array(
            "triodos bank"=>"triodos",
            "ING bank"=>"ing"
        );
        $html .= $form->Dropdown(array("label"=>'Kies een bank', "id"=>"bank", "value"=>"", "options"=>$options, "width"=>"300px"));
        $html .= $form->File(array("label"=>'Mutatie bestand', "id"=>"bestand", "value"=>"", "width"=>"300px", "accept"=>".csv"));
        $html .= $form->Check(array("label"=>'Testen of bestand al is verwerkt', "id"=>"checkdouble","value"=>"checkdouble","checked"=>TRUE));
        $form->buttons = [
            ['id'=>'writemutaties','value'=>'mutaties inlezen'],
            ['id'=>'cancel','value'=>'annuleren',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="mutaties" name="mutaties" type="hidden" />';
        return($html);
    }
    function WriteMutaties()
    {
        #
        # wat is het huidige boekjaar?
        #
        $dbio = new DBIO();
        $table_boekingen = Dbtables::boekingen['name']."_".$GLOBALS['boekhouding'];
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
        $boekjaar = $boekhouding->boekjaar;

        $html = '';
        if($_POST['bank'] == "triodos") { $mutaties=$this->Triodos(); }
        if($_POST['bank'] == "ing") { $mutaties=$this->Ing(); }
        #
        # test of de mutaties kloppen
        #
        $line = 1;
        foreach ($mutaties as $m)
        {
            # Test de datum
            $d = preg_split("/-/",$m["datum"]);
			if(!checkdate($d[1],$d[2],$d[0])) { $html .= sprintf('<br>regel:%s foute datum: %s',$line,$m["datum"]); }
            if($d[0] != $boekjaar) { $html .= '<br>regel:' . $line . __(' valt buiten boekjaar: ','prana'). $m["datum"];}
            # test of banknummer bestaat
            $rekening = $dbio->ReadUniqueRecord(array("table"=>Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'],"key"=>"bankrekening",'value'=>$m['banknr']));
            if(!$rekening) { $html .= '<br>regel:' . $line . __(' onbekende bankrekening: ','prana'). $m["banknr"];}
            if(!is_numeric($m["bedrag"])) { $html .= '<br>regel:' . $line . __(' foutief bedrag: ','prana'). $m["bedrag"];}
            if(!in_array($m["type"],array("D","C"))) { $html .= '<br>regel:' . $line . __(' debet/credit klopt niet: ','prana'). $m["type"];}
            #$html .= '<br>' . $m["datum"] . ' rekening=' . $rekening->rekeningnummer . ' bedrag'.$m["bedrag"];
            $line++;
        }
        if($html)
        {
            $error = sprintf('Bestand niet verwerkt');
            $html .= '<div class="isa_error">' . $error . '</div>';
            return($html);
        }
        #
        # testen of records al zijn ingelezen
        if(isset($_POST['checkdouble']))
        {
            $line=1;
            foreach ($mutaties as $m)
            {
                $fields = $m;
                $rekening = $dbio->ReadUniqueRecord(array("table"=>Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'],"key"=>"bankrekening",'value'=>$m['banknr']));
                $fields += ["rekening"=>$rekening->rekeningnummer];
                unset ($fields["banknr"]); # zit niet in boekingrecord
                $r = $dbio->ReadRecords(array("table"=>$table_boekingen,"filters"=>$fields));
                if(count($r) > 0)
                {
                    $euro=number_format(($m["bedrag"] /100), 2, ',', '');
                    $html .= sprintf('<br>regel: %s is al eens ingelezen: %s ( bedrag: %s )',$line,$m["datum"],$m["bedrag"]);
                }
                $line++;
             }
             if($html)
            {
                $error = 'Bestand niet verwerkt';
                $html .= '<div class="isa_error">' . $error . '</div>';
                return($html);
            }
        }
        #
        # boekingen in database opslaan
        #
        foreach ($mutaties as $m)
        {
            $rekening = $dbio->ReadUniqueRecord(array("table"=>Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'],"key"=>"bankrekening",'value'=>$m['banknr']));
            $fields = $m;
            unset ($fields["banknr"]); # zit niet in boekingrecord
            $fields += ["rekening"=>$rekening->rekeningnummer];
            $id=$dbio->CreateRecord(array("table"=>$table_boekingen,"fields"=>$fields));
            $euro=number_format(($fields["bedrag"] /100), 2, ',', '');
            $html.= sprintf ('<br>Boeking %s bedrag %s is aangemaakt',$id,$euro);
        }
        $html.= sprintf('<br>%s boekingen zijn ingelezen. Ze moeten nog wel verwerkt worden. (boek mutaties)',$line-1);
        return($html);
    }
    /**
     * Lees de Triodos mutaties
     * en zet ze in een array met mutaties
     * een mutatie bestaat uit de volgende elementen:
     * datum (y-m-d)
     * 
     */
    function Triodos()
    {
        $mutaties = array();
        $fp = fopen($_FILES['bestand']["tmp_name"],"rb");
		while(($line=fgets($fp)) !== false)
		{
            #echo "<br>".$line;
            $m=str_getcsv($line);
            #print_r($m);
            $mutatie = array();
            $mutatie += ["banknr"=>$m[1]];
            $mutatie += ["datum"=>date_format(date_create($m[0]),"Y-m-d")];
			$bedrag = str_replace(',','', $m[2]);  // bedrag in centen
			$bedrag = str_replace('.','', $bedrag);  # . voor duizendtallen verwijderen
            $mutatie += ["bedrag"=>$bedrag];
            $type = "";
            if($m[3] == "Debet") { $type="D"; }
            if($m[3] == "Credit") { $type = "C"; }
            $mutatie += ["type"=>$type];
            # bankrekening kan soms niet ingevuld zijn.
            if($m[5])
            {
                $b = preg_split("/ /",$m[5]);
                $mutatie += ["bankrekening"=>$b[1]];
            }
            else
            {
                $mutatie += ["bankrekening"=>""];
            }
            $mutatie += ["bankrekeninghouder" => $m[4]];
            $omschrijving = str_replace("\"","",$m[7]); #verwijder de quotes 
            $mutatie += ["omschrijving"=>$omschrijving];
            $mutaties[] = $mutatie;
            #echo "<br>".$line.'<br>mutatie<br>';
            #print_r($mutatie);
        }
        return($mutaties);
    }
    /**
     * Lees de ING mutaties
     * en zet ze in een array met mutaties
     * een mutatie bestaat uit de volgende elementen:
     * datum (y-m-d)
     * 
     */
    function Ing()
    {
        $mutaties = array();
        $fp = fopen($_FILES['bestand']["tmp_name"],"rb");
		while(($line=fgets($fp)) !== false)
		{
            #echo "<br>".$line;
            $m=str_getcsv($line);
			#print_r($m);
            if($m[0] == "Datum") { continue; }
            $mutatie = array();
            $mutatie += ["banknr"=>$m[2]];
            $mutatie += ["datum"=>date_format(date_create($m[0]),"Y-m-d")];
			$bedrag = str_replace(',','', $m[6]);  // bedrag in centen
			$bedrag = str_replace('.','', $bedrag);  # . voor duizendtallen verwijderen
            $mutatie += ["bedrag"=>$bedrag];
            $type = "";
            if($m[5] == "Bij") { $type="C"; }
            if($m[5] == "Af") { $type = "D"; }
            $mutatie += ["type"=>$type];
            $mutatie += ["bankrekening"=>$m[3]];
            $mutatie += ["bankrekeninghouder" => $m[1]];
            $mutatie += ["omschrijving"=>$m[8]];
            $mutaties[] = $mutatie;
            #echo "<br>".$line.'<br>mutatie<br>';
            #print_r($mutatie);
        }
        return($mutaties);
    }
    
}
?>
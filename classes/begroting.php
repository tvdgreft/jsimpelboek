<?php
namespace SIMPELBOEK;
#
# Begroting opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Begroting
{
    public $table;
    public $table_rekeningen;
	public function Start()        
	{
        $this->table = Dbtables::begroting['name']."_".$GLOBALS['boekhouding'];
        if(isset($_POST['writebegroting']))    # de begroting is ingevuld, nu vewerken
        {
            return($this->WriteBegroting());
        }
        elseif(isset($_POST['formbegroting']))    # de begroting is ingevuld, nu vewerken
        {
            return($this->FormBegroting());         # de begroting aanmaken
        }
        else
        {
            return($this->FormBoekjaar());      # het boekjaar vragen
        }
    }
    #
    # Voor welk jaar moet een begroting worden aangemaakt?
    #
    function FormBoekjaar()
    {
        $dbio = new DBIO();
        $form = new forms();
        #
        # wat is het huidige boekjaar?
        #
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
        $boekjaar = $boekhouding->boekjaar;
        $this->table = Dbtables::begroting['name']."_".$GLOBALS['boekhouding'];
        $html = '';
        $html .= sprintf('<h2>Opstellen begroting %s huidige boekjaar is %s</h2>',$boekhouding->naam,$boekjaar);
        //
        // Als het huidige boekjaar nog geen begroting heeft, dan huidige boekjaar default
        $begroting = $dbio->ReadRecords(array("table"=>Dbtables::begroting['name']."_".$GLOBALS['boekhouding'],"filters"=>array("boekjaar"=>$boekjaar)));
        $boekjaar = count($begroting) ? $boekjaar+1 : $boekjaar;
        $html .= $form->Text(array("label"=>"voor welk boekjaar", "id"=>"begrotingjaar", "type"=>"number" , "value"=>$boekjaar, "width"=>"100px;"));
        $form->buttons = [
            ['id'=>'formbegroting','value'=>'begroting aanmaken'],
            ['id'=>'cancel','value'=>'annuleren','status'=>'formnovalidate','onclick'=>'buttonclicked="cancel"']
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="begroting" name="begroting" type="hidden" />';
        return($html);
    }
    function FormBegroting()
    {
        $dbio = new DBIO();
        $form = new forms();
        #
        # wat is het huidige boekjaar?
        #
        $begrotingjaar = $_POST["begrotingjaar"];
        $vorigbegrotingjaar = $begrotingjaar-1;
        $this->table = Dbtables::begroting['name']."_".$GLOBALS['boekhouding'];
        $html = '';
        $html .= sprintf('<h2>Opstellen begroting voor boekjaar %s</h2>', $begrotingjaar);
        #
        # inlezen begroting rekeningen
        #
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"filters"=>array("soort"=>"R"),"sort"=>"type"));
        $html .= '<table id="begroting" class="pranatable">';
        $html .= '<tr>';
        $html .= '<th>rekening</th><th>soort</th><th>tyoe</th><th>' . $vorigbegrotingjaar . '</th><th>' . $begrotingjaar . '</th>';
        $html .= '</tr>';
        foreach($rekeningen as $r)
        {
            $begroting = $dbio->ReadRecords(array("table"=>$this->table,"filters"=>array("boekjaar"=>$begrotingjaar,"rekeningnummer"=>$r->rekeningnummer)));
            $vorigebegroting = $dbio->ReadRecords(array("table"=>$this->table,"filters"=>array("boekjaar"=>$vorigbegrotingjaar,"rekeningnummer"=>$r->rekeningnummer)));
            $vorigbedrag = count($vorigebegroting) ? $vorigebegroting[0]->bedrag : "";
            // vul als default het bedrag wat er stond in. Bij een nieuwe begroting: neem bedrag vorig jaar
            $bedrag = count($begroting) ? $begroting[0]->bedrag : $vorigbedrag;
            $html .= '<tr>';
            $html .= '<td>'. $r->naam . '</td>';
            $html .= '<td>'. $r->soort . '</td>';
            $html .= '<td>'. $r->type . '</td>';
            $html .= '<td>'. $vorigbedrag . '</td>';
            $html .= '<td><input type="number" style="width:100px" id="' . $r->rekeningnummer . '" name="' . $r->rekeningnummer .'" value="' . $bedrag . '"></td>';
            $html .= '</tr>';

        }
        $html .= '</table>';
        #$html .= '<p id="totaalbalans" class="isa_error"></p>';
        $form->buttons = [
            ['id'=>'writebegroting','value'=>'opslaan', "onclick"=>"buttonclicked='maakbegroting'"],   #maakbalans zorgt voor valideren van input
            ['id'=>'cancel','value'=>'annuleren',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="begroting" name="begroting" type="hidden" />';
        $html .='<input id="begrotingjaar" name="begrotingjaar" value=' . $begrotingjaar . ' type="hidden" />';
        return($html);
    }
    #
    # Sla de begroting op in de databank
    #
    function WriteBegroting()
    {
        $dbio = new DBIO();
        $html = '';
        $begrotingjaar = $_POST["begrotingjaar"];
        $this->table = Dbtables::begroting['name']."_".$GLOBALS['boekhouding'];
        $html .= sprintf('<h1>begroting wordt aangemaakt voor het jaar: %s',$begrotingjaar);
        #
        # inlezen begroting rekeningen
        #
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"filters"=>array("soort"=>"B"),"sort"=>"type"));
        $dbio->DeleteRecord(array("table"=>$this->table,"key"=>"boekjaar","value"=>$begrotingjaar));    #delete all records and make new ones
        #
        # sla de begroting op in de databank.
        #
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"filters"=>array("soort"=>"R"),"sort"=>"type"));
        foreach($rekeningen as $r)
        {
            $fields = array();
            if(isset($_POST[$r->rekeningnummer]))   #bedrag ingevuld in vorige stap
            {
                $fields += ['rekeningnummer'=>$r->rekeningnummer];
                $fields += ['bedrag'=>$_POST[$r->rekeningnummer]];
                $fields += ['boekjaar'=>$begrotingjaar];
            }
            $id=$dbio->CreateRecord(array("table"=>$this->table,"fields"=>$fields));
        }
        return($html);
    }
}
?>
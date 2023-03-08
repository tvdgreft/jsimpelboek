<?php
namespace SIMPELBOEK;
#
# Beginbalans opstellen.
# todo: als er boekingen zijn verricht kan er geen beginbalans meer worden aangemakt
#
class Beginbalans
{
    public $table;
    public $table_rekeningen;
	public function Start()        
	{
        $this->table = Dbtables::balans['name']."_".$GLOBALS['boekhouding'];
        if(isset($_POST['writebalans']))    # de balans is ingevuld, nu vewerken
        {
            return($this->WriteBalans());
        }
        else
        {
            return($this->FormBalans());         # de balans aanmaken
        }
    }
    function FormBalans()
    {
        $dbio = new DBIO();
        $form = new forms();
        #
        # wat is het huidige boekjaar?
        #
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
        $boekjaar = $boekhouding->boekjaar-1;
        $this->table = Dbtables::balans['name']."_".$GLOBALS['boekhouding'];
        $html = '';
        $html .= sprintf('<h2>Opstellen beginbalans boekhouding %s</h2>',$boekhouding->naam);
        #
        # Inlezen balans om te kijken of die al eerder is opgesteld.
        #
        $balans = $dbio->ReadRecords(array("table"=>$this->table));
        #
        # inlezen balans rekeningen
        #
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"filters"=>array("soort"=>"B"),"sort"=>"type"));
        $html .= '<table id="beginbalans" class="compact-table">';
        foreach($rekeningen as $r)
        {
            $bedrag = "";
            #
            # was er al een bedrag ingevoerd?
            #
            foreach($balans as $b)
            {
                if($r->rekeningnummer == $b->rekeningnummer)
                {
                    $bedrag = $b->bedrag;
                }
            }
            $html .= '<tr>';
            $html .= '<td>'. $r->naam . '</td>';
            $html .= '<td>'. $r->soort . '</td>';
            $html .= '<td>'. $r->type . '</td>';
            # java checks if ElemenstbyName(Name) is a numeric value
            $html .= '<td><input type="number" style="width:100px" id="' . $r->rekeningnummer . '" name="' . $r->rekeningnummer .'" value="' . $bedrag . '"></td>';
            $html .= '</tr>';

        }
        $html .= '</table>';
        $html .= '<p id="totaalbalans" class="isa_error"></p>';
        $form->buttons = [
            ['id'=>'writebalans','value'=>'opslaan', "onclick"=>"buttonclicked='maakbalans'"],   #maakbalans zorgt voor valideren van input
            ['id'=>'cancel','value'=>'annuleren',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="beginbalans" name="beginbalans" type="hidden" />';
        return($html);
    }
    #
    # Sla de balans op in de databank
    #
    function WriteBalans()
    {
        $dbio = new DBIO();
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
        $boekjaar = $boekhouding->boekjaar-1;
        $html = '';
        $html .= sprintf('<h1>begin balans is aangemaakt</h1>');
        #
        # inlezen balans rekeningen
        #
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"filters"=>array("soort"=>"B"),"sort"=>"type"));
        $dbio->DeleteAllRecords(array("table"=>$this->table));    #delete all records and make new ones
        foreach($rekeningen as $r)
        {
            $fields = array();
            $fields += ['rekeningnummer'=>$r->rekeningnummer];
            $fields += ['boekjaar'=>$boekjaar];
            $bedrag = $_POST[$r->rekeningnummer] ? $_POST[$r->rekeningnummer] : 0;
            $fields += ['bedrag'=>$bedrag];
            $dbio->CreateRecord(array("table"=>$this->table,"fields"=>$fields));
        }
        return($html);
    }
}
?>
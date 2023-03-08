<?php
namespace SIMPELBOEK;
#
# Beheer van boekingen
# TODO: testen op geldig boekjaar
#
class boekingen extends Tableform
{
    protected $fields = array();
    public $table_rekeningen;
	public function Start()
	{
        $dbio = new dbio;
        $html='';
        /**
         * Het is alleen mogelijk boekingen te registreren als er een beginbalans is. (eindbalans vorig jaar)
         */
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
        $boekjaar = $boekhouding->boekjaar-1;
        $AantalBalansRecords=count($dbio->ReadRecords(array("table"=>Dbtables::balans['name']."_".$GLOBALS['boekhouding'],"filter"=>array("boekjaar"=>"boekjaar"))));
        if(! $AantalBalansRecords)
        {
            $error = '<h2>Er is nog geen balans</h2>';
            $html .= '<div class="isa_error" >' . $error . '</div>';
            return($html);
        }
        
        $this->single = "boeking";
        $this->plural = 'boekingen';
        $this->class = "boekingen";
		$this->table = Dbtables::boekingen['name']."_".$GLOBALS['boekhouding'];
        $this->columns= [
                                ["id","id","string"],         #table column name, columnname to be displayed, display orientation
                                ["datum","datum","date"],
                                ["omschrijving","omschrijving","string"],
                                ["bedrag","bedrag","euro"],
                                ["btw","btw","euro"],
                                ["type","type","stringright"],
                                ["rekening","rekening","stringright"],
                                ["tegenrekening","tegenrekening","stringright"]];
		$this->filtercolumns = array("datum"=>"datum","bedrag"=>"bedrag","rekening"=>"rekening","tegenrekening"=>"tegenrekening");
        $this->permissions = ["vw","cr","md","dl"];
        $this->num_rows=explode(',',$GLOBALS['numrows']);   #default rows per page
        $this->rows_per_page = $this->num_rows[1];
		$this->primarykey="id";	#the primary key of the records
        $html .= $this->MaintainTable(); # start or restart tableform
        return($html);
    }
    #
    # maak formulier voor het invoeren van de record data
    # $crmod = "create" of "modify"
    #
    public function FormTable($crmod)
	{
        $form = new forms;
        $dbio = new dbio;
        $this->table_rekeningen = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
        $html = '';
        if($crmod == "modify")
        {
            $html .= $form->Text(array("label"=>'id', "id"=>"primarykey", "value"=>$this->fields['id'], "width"=>"100px", "readonly"=>TRUE));
        }
        $html .= $form->Date(array("label"=>'boekdatum', "id"=>"datum", "value"=>$this->fields['datum'], "width"=>"300px"));
        $html .= $form->Text(array("label"=>'Bankrekening', "id"=>"bankrekening", "value"=>$this->fields['bankrekening'], "checkclass"=>"checkbankrekening" , "width"=>"300px","required"=>FALSE,"error"=>"bankrekening onjuist"));
		$html .= $form->Text(array("label"=>'Bankrekeninghouder', "id"=>"bankrekeninghouder", "value"=>$this->fields['bankrekeninghouder'], "width"=>"300px","required"=>FALSE));
        $html .= $form->Text(array("label"=>'Bedrag (in centen)', "id"=>"bedrag", "value"=>$this->fields['bedrag'], "type"=>"number" , "width"=>"100px","required"=>TRUE));
        $html .= $form->Text(array("label"=>'BTW (in centen)', "id"=>"btw", "value"=>$this->fields['btw'], "type"=>"number" , "width"=>"100px","required"=>FALSE));
        $options = array("credit"=>"C","debet"=>"D");
        $html .= $form->Radio(array("label"=>'type', "id"=>"type", "value"=>$this->fields['type'], "options"=>$options));
        #
        # maak keuze uit balansrekening in rekeningschema
        #
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"filters"=>array("soort"=>"B"),"sort"=>"rekeningnummer"));
        $options = array();
        foreach ($rekeningen as $r)
        {
            $options += [$r->naam=>$r->rekeningnummer];
        }
        $html .= $form->Dropdown(array("label"=>'rekening', "id"=>"rekening", "value"=>$this->fields['rekening'], "options"=>$options, "width"=>"300px"));
        #
        # tegenrekening in rekeningschema
        #
        $rekeningen = $dbio->ReadRecords(array("table"=>$this->table_rekeningen,"sort"=>"rekeningnummer"));
        $options = array();
        foreach ($rekeningen as $r)
        {
            $options += [$r->naam=>$r->rekeningnummer];
        }
        $html .= $form->Dropdown(array("label"=>'tegenrekening', "id"=>"tegenrekening", "value"=>$this->fields['tegenrekening'], "options"=>$options, "width"=>"300px"));
        $html .= $form->TextArea(array("label"=>'omschrijving', "id"=>"omschrijving", "value"=>$this->fields['omschrijving'], "width"=>"300px","heigth"=>"150px","required"=>FALSE));
        $form->buttons = [
                            ['id'=>'writerecord','value'=>'opslaan', "onclick"=>"buttonclicked='boeking'"],
                            ['id'=>'writerecordandnext','value'=>'opslaan en nieuwe boeking', "onclick"=>"buttonclicked='boeking'"],
                            ['id'=>'cancel','value'=>'annuleren',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
                        ];
		$html .= $form->DisplayButtons();
        $html .='<input id="crmod" name="crmod" value="' . $crmod . '" type="hidden" />';
        return($html);
    }
     /**
	*	Wat doen we nadat een record is gewijzigd?
	**/
    public function CheckModify() : bool
    {
        return(TRUE);
    }
    /**
	*	Wat doen we nadat een record is verwijderd?
	**/
	public function AfterDelete($id)
	{
        return(TRUE);
    }
}
?>	
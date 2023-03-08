<?php
namespace SIMPELBOEK;
#
# Beheer van de boekhoudingen
#
class boekhoudingen extends tableform
{
    protected $fields = array();
    
	public function Start()
	{
        $this->class = "boekhoudingen";
        $this->single = "boekhouding";
        $this->plural = "boekhoudingen";
        $dbio = new dbio();
        $form = new Forms();
        $html='';
        $html .= '<h2>'. prana_Ptext('','Overzicht van boekhoudingen') . '</h2>';
		$this->table = Dbtables::boekhoudingen['name'];
        $this->columns= [
                                ["id","id","left"],         #table column name, columnname to be displayed, display orientation
                                ["code","code","left"],
                                ["naam","naam","left"],
                                ["boekjaar","boekjaar","left"],
                                ["kapitaalrekening","kapitaalrekening","left"],
                                ["winstrekening","winstrekening","left"],
                                ["verliesrekening","verliesrekening","left"],
                            ];
		$this->filtercolumns = array();                     #er hoeft niet gefilterd te worden
        $this->permissions = ["vw","cr","md"];
        $this->num_rows=explode(',',$GLOBALS['numrows']);   #default rows per page
        $this->rows_per_page = $this->num_rows[1];
		$this->primarykey="id";	#the primary key of the records
        $html .= $this->MaintainTable(); # start or restart tableform
        #
        # Tabellen aanmaken als een nieuwe boekhouding is aangemaakt
        #
        if(isset($_POST['crmod']) && $_POST['crmod'] == "create")
        {
            $html .= "tabellen worden aangemaakt";
            $result = $dbio->CreateTable(Dbtables::rekeningen['name']."_".$_POST['code'],Dbtables::rekeningen['columns']);
            $result = $dbio->CreateTable(Dbtables::balans['name']."_".$_POST['code'],Dbtables::balans['columns']);
            $result = $dbio->CreateTable(Dbtables::begroting['name']."_".$_POST['code'],Dbtables::begroting['columns']);
            $result = $dbio->CreateTable(Dbtables::boekingen['name']."_".$_POST['code'],Dbtables::boekingen['columns']);
            if($result === FALSE) 
            { 
                $html .= '<div class="isa_error">' . __( 'Fout bij aanmaken boekhouding', 'prana' ) . '</div>';
                return($html);
            }
        }
        #$form->buttons = [["id"=>"break1","value"=>"break1"]];
		#$html .= $form->DisplayButtons();
        return($html);
    }
    #
    # maak formulier voor het invoeren van de record data
    # $crmod = "create" of "modify"
    #
    public function FormTable($crmod)
	{
        $html = '';
        $form = new Forms();
        $dbio = new Dbio();
        if($crmod == "create" && isset($GLOBALS['boekhouding'])) { unset($GLOBALS['boekhouding']); } // Bij create lopende boekhouding afslkuite
        #
        # balansrekeningen
        #
        $rekeningen = 0;
        if(isset($GLOBALS['boekhouding']))
        {
            $table_rekeningen = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
            $rekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"filters"=>array("soort"=>"B"),"sort"=>"rekeningnummer ASC"));
            $options = array();
            foreach ($rekeningen as $r)
            {
                $naam = sprintf("%03d %s %s",$r->rekeningnummer,$r->type,$r->naam);
                $options += [$naam=>$r->rekeningnummer];
            }
        }
        
        if($crmod == "modify")
        {
            $html .= $form->Text(array("label"=>'id', "id"=>"primarykey", "value"=>$this->fields['id'], "width"=>"100px;", "readonly"=>TRUE));
        }
        #$form->buttons = [["id"=>"break2","value"=>"break2"]];
		#$html .= $form->DisplayButtons();
       
        $html .= $form->Text(array("label"=>'Code', "id"=>"code", "value"=>$this->fields['code'], "width"=>"100px;"));
        $html .= $form->Text(array("label"=>'Naam', "id"=>"naam", "value"=>$this->fields['naam'], "width"=>"300px;"));
        $html .= $form->Text(array("label"=>'Huidige boekjaar', "id"=>"boekjaar", "type"=>"number","value"=>$this->fields['boekjaar'],"width"=>"100px;"));
        // Als rekeningschema is ingevuld kunnen eventueel de rekeningen voor het afboeken van verlies en winst worden aangemaakt
        // Bij de jaarafrekening wordt gekeken of dat is gebeurd.
        if($rekeningen)
        {
            $html .= $form->Dropdown(array("label"=>"Rekening kapitaal","id"=>"kapitaalrekening","options"=>$options, "value"=>$this->fields['kapitaalrekening'],"required"=>FALSE));
            $html .= $form->Dropdown(array("label"=>"Rekening winst vorig jaar","id"=>"winstrekening","options"=>$options, "value"=>$this->fields['winstrekening'],"required"=>FALSE));
            $html .= $form->Dropdown(array("label"=>"Rekening verlies vorig jaar","id"=>"verliesrekening","options"=>$options, "value"=>$this->fields['verliesrekening'],"required"=>FALSE));
        }
       
        $form->buttons =
        [
            ['id'=>'writerecord','value'=>'opslaan', "onclick"=>"buttonclicked='boekhouding'"],
            ['id'=>'cancel','value'=>'annuleren',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
		$html .= $form->DisplayButtons();
        $html .='<input id="crmod" name="crmod" value="' . $crmod . '" type="hidden" />';
        #$html .='<input id="boekhoudingen" name="boekhoudingen" type="hidden" />';
        #$html .='<input id="menu" name="menu" "value="boekhoudingen" type="hidden" />';
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
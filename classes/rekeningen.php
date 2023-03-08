<?php
namespace SIMPELBOEK;
#
# Beheer van rekeningen
#
class rekeningen extends tableform
{
    protected $fields = array();
	public function Start()
	{
        $html='';
        $this->class = "rekeningen";
        $this->single = "rekening";
        $this->plural = "rekeningen";
		$this->table = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
        $this->columns= [
                                ["id","id","string"],         #table column name, columnname to be displayed, display orientation
                                ["naam","naam","string"],
                                ["bankrekening","Bankrekening","string"],
                                ["rekeningnummer","rekeningnummer","string"],
                                ["soort","soort","string"],
                                ["type","type","string"]];
		$this->filtercolumns = array("soort"=>"soort","type"=>"type");
        $this->permissions = ["vw","cr","md","dl","dm"];
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
        $form = new Forms();
        $html = '';
        if($crmod == "modify")
        {
            $html .= $form->Text(array("label"=>'id', "id"=>"primarykey", "value"=>$this->fields['id'], "width"=>"100px;", "readonly"=>TRUE));
        }
        $html .= $form->Text(array("label"=>'Naam', "id"=>"naam", "value"=>$this->fields['naam'], "width"=>"300px;"));
        $html .= $form->Text(array("label"=>'Bankrekening', "id"=>"bankrekening", "value"=>$this->fields['bankrekening'], "width"=>"300px;","required"=>FALSE));
        $html .= $form->Text(array("label"=>'Rekeningnummer', "id"=>"rekeningnummer", "value"=>$this->fields['rekeningnummer'],"width"=>"100px;","required"=>TRUE));
        $options = array("Balans"=>"B","Resultaat"=>"R");
        $html .= $form->Radio(array("label"=>'Soort', "id"=>"soort", "inline"=>TRUE, "options"=>$options,"value"=>$this->fields['soort'],"required"=>TRUE));
        $options = array("Credit"=>"C","Debet"=>"D");
        $html .= $form->Radio(array("label"=>'Type', "id"=>"type", "inline"=>TRUE, "options"=>$options,"value"=>$this->fields['type'],"required"=>TRUE));
        $html .= $form->Text(array("label"=>'BTW percentage', "id"=>"btwpercentage", "value"=>$this->fields['btwpercentage'],"width"=>"100px;", "required"=>FALSE));
        #$html .= '<div>';
		$form->buttons = [
                            ['id'=>'writerecord','value'=>'opslaan', "onclick"=>"buttonclicked='rekening'"], #javascipt simpelboek.js validates input
                            ['id'=>'cancel','value'=>'annuleren',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
                        ];
		$html .= $form->DisplayButtons();
        $html .='<input id="crmod" name="crmod" value="' . $crmod . '" type="hidden" />';
        return($html);
    }
    #
    # lees demorecords in vanaf een csv bestand.
    # naam van csv bestand is gedefineerd in de opties van de plugin
    #
    public function LoadDemoRecords()
    {
        $dbio = new DBIO();
        get_option('rekeningschema');
        $csvfile=SBK_DATA_DIR . get_option('rekeningschema');
        $fileHandle = fopen($csvfile, "r");
        $rekeningschema = array();
        if(($header = fgetcsv($fileHandle, 0, ";")) !== FALSE)
        {
            //Loop through the CSV rows.
            while (($row = fgetcsv($fileHandle, 0, ";")) !== FALSE) 
            {
                $rekeningschema[] = array_combine($header, $row);
            }
        }
        foreach ($rekeningschema as $rekening)
        {
            $dbio->CreateRecord(array("table"=>Dbtables::rekeningen['name']."_".$_SESSION['code'],"fields"=>$rekening));
        }   
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
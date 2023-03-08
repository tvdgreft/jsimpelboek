<?php
namespace SIMPELBOEK;
#
# Beheer gegevens van openstaande boekhouding.
#
class boekhouding
{
    protected $fields = array();
    protected $table;
    protected $columns= [
        ["id","id","left"],         #table column name, columnname to be displayed, display orientation
        ["code","code","left"],
        ["naam","naam","left"],
        ["boekjaar","boekjaar","left"],
        ["kapitaalrekening","kapitaalrekening","left"],
        ["winstrekening","winstrekening","left"],
        ["verliesrekening","verliesrekening","left"],
    ];
    public function Start()
    {
        if(isset($_POST['modifyrecord']))    #wijzig record
        {
            return($this->ModifyRecord());
        }
        else
        {
            return($this->FormRecord());         # de balans aanmaken
        }
    }
	public function FormRecord()
	{
        $html = '';
        $form = new Forms();
        $dbio = new Dbio();
        $this->table = Dbtables::boekhoudingen['name'];
        $columns = $dbio->columns($this->table);
        $p = $dbio->ReadUniqueRecord(array("table"=>$this->table,"key"=>"code","value"=>$GLOBALS['boekhouding']));
        foreach ($columns as $c)
        {
            $this->fields[$c]=$p->$c;
        }
		$html .=  sprintf('<h2> %s wijzigen</h2>',$GLOBALS['boekhouding']);
        #
        # balansrekeningen
        #
        $rekeningen = 0;
        $table_rekeningen = Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding'];
        $rekeningen = $dbio->ReadRecords(array("table"=>$table_rekeningen,"filters"=>array("soort"=>"B"),"sort"=>"rekeningnummer ASC"));
        $options = array();
        foreach ($rekeningen as $r)
        {
            $naam = sprintf("%03d %s %s",$r->rekeningnummer,$r->type,$r->naam);
            $options += [$naam=>$r->rekeningnummer];
        }
        $html .= $form->Text(array("label"=>'Code', "id"=>"code", "value"=>$this->fields['code'], "width"=>"100px;", "readonly"=>TRUE));
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
            ['id'=>'modifyrecord','value'=>'opslaan', "onclick"=>"buttonclicked='boekhouding'"],
            ['id'=>'cancel','value'=>'annuleren',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
		$html .= $form->DisplayButtons();
        return($html);
    }
    /**
     * wijziging opslaan in databank
     */
    public function ModifyRecord()
	{
        $dbio = new Dbio();
        $html = '';
        $this->table = Dbtables::boekhoudingen['name'];
        $columns = $dbio->columns($this->table);
        foreach ($columns as $c)
        {
            if(isset($_POST[$c]))
            {
                $this->fields += [$c=>$_POST[$c]];
            }
        }
        $dbio->ModifyRecord(array("table"=>$this->table,"fields"=>$this->fields,"key"=>'code',"value"=>$GLOBALS['boekhouding']));
        $html .=  sprintf('<h2>boekhouding %s is gewijzigd</h2>',$GLOBALS['boekhouding']);
        unset ($GLOBALS['menu']);  #sluit menu af
        return($html);
    }	
}
?>
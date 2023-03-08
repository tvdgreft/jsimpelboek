<?php
#
# Verwijder een boekhouding
#
namespace SIMPELBOEK;

class Delete
{
    public function Start()
    {
        if(isset($_POST['delete']) && $_POST["delete"] == 'delete')    // nu definitief verwijderen
        {
            return($this->Delete());
        }
        else
        {
            return($this->FormDelete());         # lopende boekhouding verwijderen.
        }
    }
    public function FormDelete()
    {
        $html = '';
        $dbio = new Dbio();
        $form = new forms();
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
        $html .= sprintf('Door om onderstaande link te klikken wordt de boekhouding %s definitief verwijderd.', $boekhouding->naam);
        $html .= '<br>Maak eventueel eerst een backup van de databank van de website<br>';
        $message=sprintf('boekhouding %s definitief wijderen , zeker weten?',$boekhouding->naam);
        $form->buttons = [
            ['id'=>'deleteboekhouding','value'=>'boekhouding verwijderen','onclick'=>"return confirm('".$message. "');"],
            ['id'=>'cancel','value'=>'annuleren',"status"=>"formnovalidate","onclick"=>"buttonclicked='cancel'"]
        ];
        $html .= $form->DisplayButtons();
        $html .='<input id="delete" name="delete" value="delete" type="hidden" />';
        return($html);
    }
    public function Delete()
    {
        $html = '';
        $dbio = new Dbio();
        $form = new forms();
        $boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
        $dbio->DeleteRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
        $result = $dbio->DeleteTable(Dbtables::rekeningen['name']."_".$GLOBALS['boekhouding']);
        $result = $dbio->DeleteTable(Dbtables::balans['name']."_".$GLOBALS['boekhouding']);
        $result = $dbio->DeleteTable(Dbtables::begroting['name']."_".$GLOBALS['boekhouding']);
        $result = $dbio->DeleteTable(Dbtables::boekingen['name']."_".$GLOBALS['boekhouding']);
        #echo sprintf('Boekhouding %s  is definitief verwijderd.', $boekhouding->naam);
        $GLOBALS['exit'] = "exit";
        return('');
    }
}
?>
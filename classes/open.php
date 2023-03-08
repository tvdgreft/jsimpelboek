<?php
/**
 * open een bestaande boekhouding
 */
namespace SIMPELBOEK;

class Open
{
    public function Start()
    {
        $html = '';
        $dbio = new Dbio();
        $html .= sprintf('<h2>Open een bestaande boekhouding</h2>');
        $result = $dbio->ReadRecords(array("table"=>Dbtables::boekhoudingen['name']));
		if($result === FALSE) 
        { 
            $error = sprintf('Er zijn geen boekhoudingen');
            $html .= '<div class="isa_error">' . $error . '</div>';
            return($html);
        }
        $options = array();
        foreach ($result as $r)
        {
            $options += [$r->naam=>$r->code];
        }
        $form = new Forms();
        $html .= $form->Dropdown(array("label"=>"kies boekhouding","id"=>"lastboekhouding","options"=>$options,"row"=>TRUE,"value"=>""));
        $form->buttons = [["id"=>"open","value"=>"openen"]];
		$html .= $form->DisplayButtons();
        unset ($GLOBALS['boekhouding']); #lopende boekhouding afsluiten
        unset ($GLOBALS['menu']);  #sluit menu open af
        return($html);
    }
}
?>
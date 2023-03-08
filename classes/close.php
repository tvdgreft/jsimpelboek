<?php
/**
 * afsluiten boekhouding
 */
namespace SIMPELBOEK;

class Close
{
    public function Start()
    {
        $html = sprintf("<h2>boekhouding %s is afgesloten</h2>",$GLOBALS['boekhouding']);
        if(isset($GLOBALS['boekhouding'])) { unset($GLOBALS['boekhouding']); }
        unset ($GLOBALS['menu']);
        return($html);
    }
}
?>
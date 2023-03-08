<?php

namespace SIMPELBOEK;
class boekmenu extends pranamenu
{   
    /**
    * define the main menu
    */

    public $mainmenu = array(
        array("boekhoudingen","boekhoudingen","dropdown"),
        array("begroting","begroting maken","link"),
        array("overzichten","overzichten","dropdown"),
        array("beheer","beheer","dropdown"),
        array("bankimport","bankimport","dropdown"),
        array("jaarafsluiting","jaarafsluiting","link"),
        array("help","help","link"),
    );
    /**
       * define the submenu
    */
    public $submenu = array(
        array("boekhoudingen","boekhoudingen","overzicht"),
        array("open","boekhoudingen","openen"),
        array("close","boekhoudingen","afsluiten"),
        #array("rekeningen","boekhoudingen","rekeningenschema"),
        array("beginbalans","boekhoudingen","begin balans"),
        array("delete","boekhoudingen","verwijderen"),
        array("balans","overzichten","balans"),
        array("resultaat","overzichten","resultatenrekening"),
        array("grootboek","overzichten","grootboek"),
        array("omzetbelasting","overzichten","BTW overzichten"),
        array("boekhouding","beheer","boekhouding"),
        array("rekeningschema","beheer","rekeningschema"),
        array("boekingen","beheer","beheer boekingen"),
        array("mutaties","bankimport","importeren bankmutaties"),
        array("boekmutaties","bankimport","bankmutaties verwerken"),
      );
    public function Start() : string
	{
      /**
       * opgestart voor een specifiekke boekhouding. disable close,delete en open
       */
      if(isset($GLOBALS['single']))
      {
          array_push($this->disabled,"boekhoudingen","close","delete","open");
      }
      /**
       * er is nog geen boekhouding gekozen, dus enable alleen het openen van een boekhouding en help
       */
      if(!isset($GLOBALS['boekhouding'])) 
      {
          array_push($this->disabled,"close","delete","beginbalans","begroting","overzichten","boekhouding","balans","resultaat","grootboek","omzetbelasting","beheer","rekeningschema","bankimport","mutaties","boekmutaties","jaarafsluiting","boekingen");
      }
      $html = $this->Menu();    # toon het menu
      return($html);
    }
}
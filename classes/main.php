<?php
##################################################################################
# class: 		main
##################################################################################
namespace SIMPELBOEK;

class Main
{
	public $single = '';				# boekhouding die als enige geopend kan worden
	public $open;				# geopende boekhouding
	public $action;
	
	function init($args)
	{
		$dbio = new Dbio();
		$form = new Forms();
		$menu = new boekmenu();
		print_r($_POST);
		$this->action = \JURI::current();

		$html = '';
		/**
		 * Is het gestart voor een bepaalde boekhouding? (simpelboek single=....)
		 */
		if(isset($args['single']))
		{
			$boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$args['single']));
			if(!$boekhouding)
			{
				$error = sprintf('<h2>boekhouding %s bestaat niet</h2>',$this->single);
				$html .= '<div class="isa_error" >' . $error . '</div>';
				return($html);
			}
			$GLOBALS['single'] = $GLOBALS['boekhouding'] = $args['single'];
		}
		elseif(isset($_POST['lastboekhouding']))
		{
			$GLOBALS['boekhouding'] = $_POST['lastboekhouding'];
		}
		/**
		 * Als er nog geen database tabellen zijn, maak dan in ieder geval de tabel boekhoudingen
		 */

		$dbio->CreateTable(Dbtables::boekhoudingen['name'],Dbtables::boekhoudingen['columns']);
		/**
		 * Titel tonen
		 */
		$html .= '<div class="prana-display">';
		$html .= sprintf('<center><h1>' . prana_Ptext('' , 'SIMPELBOEK BOEKHOUDING') . '</h1></center>');
		/**
		 * Is er eem menu gekozen nu of de vorige keer?
		 */
		if(isset($_POST['menu'])) 
		{ 	$GLOBALS['menu'] = $_POST['menu'];	# bewaar het laatst aangeklikte menuitem
			#$html .= '<br>menu=' . $GLOBALS['menu'];
		}
		elseif(isset($_POST['lastmenu']))
		{
			$GLOBALS['menu'] = $_POST['lastmenu'];
		}
		/**
		 * Welke helpfile moet in modal geladen worden
		 */
		if(isset($GLOBALS['menu']))
		{ 
			$mainmenu = $menu->MainMenu($GLOBALS['menu']);
			#$html .= '<br>menu=' . $GLOBALS['menu'] . 'mainmenu=' . $mainmenu;
		}
		/**
		 * welke helpfile moet getoond worden?
		 */
		$helpfile = SIMPELBOEK_DOC_DIR . 'manual.html';    #default
		if(isset($GLOBALS['menu'])) 
		{ 
			if(file_exists(SIMPELBOEK_DOC_DIR . 'manual_'.$GLOBALS['menu'].'.html')) { $helpfile = SIMPELBOEK_DOC_DIR . 'manual_'.$GLOBALS['menu']. '.html'; }
			elseif(file_exists(SIMPELBOEK_DOC_DIR . 'manual_'.$mainmenu . '.html')) { $helpfile = SIMPELBOEK_DOC_DIR . 'manual_' . $mainmenu . '.html'; }
		}
		$html .= HelpModal($helpfile);
		
		/**
		 * start form
		 */
		$html .='<form action=' . $this->action . ' method="post" enctype="multipart/form-data" onSubmit="return ValFormSimpelboek()">';
		/**
		 * Als er een boekhouding is geopend, toon welke boekhouding geopend is
		 * en bepaal vast wat vatiabelen
		 */
		if(isset($GLOBALS['boekhouding'])) 
		{ 
			echo $GLOBALS['boekhouding'];
			$boekhouding = $dbio->ReadUniqueRecord(array("table"=>Dbtables::boekhoudingen['name'],"key"=>"code","value"=>$GLOBALS['boekhouding']));
			$html .= sprintf('<h1>boekhouding %s boekjaar %s</h1>',$boekhouding->naam,$boekhouding->boekjaar);
			$boekjaar = $boekhouding->boekjaar;
        	$table_boekingen = Dbtables::boekingen['name']."_".$GLOBALS['boekhouding'];
       		$AantalBoekingen = count ($dbio->ReadRecords(array("table"=>$table_boekingen,"filters"=>array("datum"=>$boekjaar))));
        	$table_balans = Dbtables::balans['name'] ."_".$GLOBALS['boekhouding'];
        	$AantalBalansRecords=count($dbio->ReadRecords(array("table"=>$table_balans,"filters"=>array("boekjaar"=>$boekjaar-1))));
       		if($AantalBoekingen > 0) { array_push($menu->disabled,"beginbalans"); }    // Zodra boekingen hebben plaats gevonden kan beginbalans niet meer worden gewijzigd.
      	}
		$html .= $menu->Start();	#Menu plaatsen.
		/**
		 * Er is een menu item gekozen. Startr bijbehorende class
		 */
		if(isset($GLOBALS['menu']))
		{
			switch($GLOBALS['menu'])
			{
				case 'boekhoudingen':	#beheer boehoudingen;
				$class = new boekhoudingen;
				#$html .='<input id="break" name="break" type="hidden" value="break"/>';  #set break for testing
				break;

				case 'open';	#open een boekhouding
				$class= new open;
				break;

				case 'close';	#afsluitenboekhouding
				$class= new close;
				break;

				case 'delete';	#boekhouding verwijderen
				$class= new delete;
				break;

				case 'boekhouding';	#wijzig basisgegevens boekhouding
				$class = new boekhouding;
				break;

				case 'rekeningschema';	#beheer rekeningschema
				$class = new rekeningen;
				break;

				case 'beginbalans';	#voor nieuwe boekhoudingen: maak beginbalans
				$class = new beginbalans;
				break;

				case 'begroting';	#boekingen registreren
				$class = new begroting;
				break;

				case 'boekingen';	#boekingen registreren
				$class = new boekingen;
				break;

				case 'balans';	#balans overzicht
				$class = new balans;
				break;

				case 'resultaat';	#resultaatrekening
				$class = new resultaat;
				break;

				case 'grootboek';	#grootboek
				$class = new grootboek;
				break;

				case 'mutaties';	#inlezen bankimport mutaties vanuit csv file
				$class = new mutaties;
				break;

				case 'boekmutaties';	#verwerk bankimport mutaties
				$class = new boekmutaties;
				break;

				case 'omzetbelasting';	#verwerk bankimport mutaties
				$class = new omzetbelasting;
				break;

				case 'help';	#verwerk bankimport mutaties
				$class = new help;
				break;
			}
		}
		if(isset($class)) 
		{ 
			#echo "start boekhoudingen";
			#if(!isset($_POST['break'])) { $html .= $class->Start(); }
			$html .= $class->Start();
		}
		if(isset($GLOBALS['exit']))
		{
			echo("<script>location.href = '".$this->action."'</script>");
		}
		$html .= $this->SetPosts();
		$html .= '</form>';
		$html .= '<hr>';
		$html .= '</div>';
		return($html);
		/**
		 * zitten we al ergens in het menu?
		 */
		if(isset($_POST['menu'] )) { $part = $_POST['menu']; }
		elseif(isset($_GET['menu'] )) { $part=$_GET['menu']; }
		if($main = $menu->MainMenu($part)) { $part = $main; }
		if(isset($_GET['menu']))
		{
			if($classfile=Bootstrap::ClassFile($_GET['menu'])) 
			{ 
				$class = Bootstrap::NameSpace() . '\\' . $_GET['menu'];
				$run = new $class;
				$html .= $run->Start();
			}
		}
		/*
			Ga terug naar functie zolang er geen back is gegeven
		*/
		if(!isset($_POST['back']))
		{
			foreach ($_POST as $key => $value)
			{
				$class = Bootstrap::NameSpace() . '\\' . $key;
				if($classfile=Bootstrap::ClassFile($key)) 
				{ 
					$run = new $class;
					$html .= $run->Start(); 
					break;					# zorgt er voor dat een eerder gestarte class opnieuw wordt gestart
				}
			}
		}
		$html .= $this->SetPosts();
		$html .= '</form>';
		$html .= '<hr>';
		$html .= '</div>';
		return($html);
	}
	public function SetPosts()
	{
		#
		# set post values
		#
		$html = '';
		if(isset($GLOBALS['menu'])) { $html .='<input id="lastmenu" name="lastmenu" type="hidden" value=' . $GLOBALS['menu'] .  ' />'; }
		if(isset($GLOBALS['boekhouding'])) { $html .='<input id="lastboekhouding" name="lastboekhouding" type="hidden" value=' . $GLOBALS['boekhouding'] .  ' />'; }
		return($html);
	}
	
}
?>	
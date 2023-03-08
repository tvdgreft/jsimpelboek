<?php
/**
 * Bootstrap - start de plugin taken
 * Doorloop de pagina en vevang {pluginname param1="param1" param2="param2" ......}
 * door uitvoer van plugin
 */
namespace SIMPELBOEK;
class Bootstrap
{
	const PLUGINNAME = 'simpelboek';
	const NAMESPACE = 'SIMPELBOEK';
	public function init()
	{
		$this->DefineConstants();
		#$this->autoloader();	#start autoloader for loading classes automatically
		$this->loadclasses();	# dan maar even alles
		$this->loadfunctions();	# require all functions
	}
	public static function NameSpace()
	{ 
		return(self::NAMESPACE);
	}
	public static function PluginName()
	{ 
		return(self::PLUGINNAME);
	}
	/** Definieer constantes */
	protected function DefineConstants()
	{
		define ( 'SIMPELBOEK_ENCRYPTION_KEY', 'M6tKM6IJGyUyOmeW');
		define ( 'SIMPELBOEK_PLUGIN_URL', \JURI::base(). "plugins/system/".self::PLUGINNAME."/");
		define ( 'SIMPELBOEK_DATA_URL', SIMPELBOEK_PLUGIN_URL . 'data/' );
		define ( 'SIMPELBOEK_DOC_URL', SIMPELBOEK_PLUGIN_URL . 'doc/' );
		define ( 'SIMPELBOEK_FUNCTIONS_URL', SIMPELBOEK_PLUGIN_URL . 'functions/' );
		define ( 'SIMPELBOEK_PLUGIN_DIR', JPATH_SITE . "/plugins/system/".self::PLUGINNAME."/");
		define ( 'SIMPELBOEK_DATA_DIR', SIMPELBOEK_PLUGIN_DIR . 'data/' );
		define ( 'SIMPELBOEK_DOC_DIR',SIMPELBOEK_PLUGIN_DIR . 'doc/' );
		define ( 'SIMPELBOEK_FUNCTIONS_DIR', SIMPELBOEK_PLUGIN_DIR . 'functions/' );
		define ( 'SIMPELBOEK_VENDOR_DIR', SIMPELBOEK_PLUGIN_DIR . 'vendor/' );
	}
	/**
	 * This function will be started by SIMPELBOEKduin.php
	 */
	public function OnShortCode($text)
	{
		#$this->autoloader();	#start autoloader for loading classes automatically
		$this->loadclasses();	# dan maar even alles
		$this->loadfunctions();	# require all functions
		$main = new main;
		$html = $this->LoadScripts();	#load scripts and css files
		//
		// pluginlink zonder argumenten
		//
		$args=array();
		if(strpos($text,'{' . self::PLUGINNAME . '}',))
		{
			$text = str_replace('{'.self::PLUGINNAME.'}', $html . $main->init($args), $text);
		}
		//
		// pluginlink met argumenten
		//
		if (preg_match_all('/\{' . self::PLUGINNAME . '\ ([^\}]+)\}/', $text, $matches))
		{
			foreach ($matches[1] as $matchIndex => $match)
			{
				$tag = $matches[0][$matchIndex];
				$tagArgs = $this->convertTagArgs($match);
				$args = array();
				foreach ($tagArgs as $k => $v) {
					$args[$k] = $v;
				}
				$text = str_replace($tag, $html . $main->init($args), $text);
			}
		}
		return $text;
	}
	
	protected function autoloader()
	{
		spl_autoload_register(function ($class_name)
		{
			$self = new self();
			#echo "class=" . $class_name;
			$parts = explode( '\\', $class_name );
			if($parts[0] == $self::NAMESPACE)
			{
				$classfile=$this->ClassFile($parts[1]);
				require_once( dirname( __FILE__ ) . $classfile );
			}
		});
	}
	#
	# check if class file exists
	#
	public static function ClassFile($class)
	{
		$dirs=array("classes");
		$class=strtolower($class);
		#echo "zoek class";
		foreach($dirs as $d)
		{
			$classfile = '/' . $d . '/' . $class . '.php';
			#echo "<br>file=".dirname( __FILE__ ) .$classfile;
			if(file_exists(dirname( __FILE__ ) . $classfile)) return($classfile);
		}
		return("");
	}
	protected function loadclasses() 
	{
		/**
		 * Eerst moeten de classes worden geladen die extends hebben, anders foutmelding bij classes die een extend hebben hiernaartoe
		 */
		/* extend classes */
		$files = array(
			JPATH_SITE .'/plugins/system/simpelboek/classes/overzichten/overzichten.php',
			JPATH_SITE .'/plugins/system/simpelboek/classes/pranamenu.php',
			JPATH_SITE .'/plugins/system/simpelboek/classes/tableform.php'
		);
		foreach ( $files as $file ) {
			require_once ($file);
		}
		$files = glob( dirname(__FILE__) . '/classes/*.php' );
		foreach ( $files as $file ) {
			require_once ($file);
		}
		$files = glob( dirname(__FILE__) . '/classes/overzichten/*.php' );
		foreach ( $files as $file ) {
			require_once ($file);
		}
	}
	/**
	 * Method to convert a string into an argument array
	 *
	 * @param   string  $tagArgs  String to convert into an array
	 *
	 * @return array
	 */
	/** Laadt functiebestanden */
	protected function loadfunctions() {
		$files = glob( dirname(__FILE__) . '/functions/*.php' );
		foreach ( $files as $file ) {
			require_once $file;
		}
	}
	protected function convertTagArgs($tagArgs)
	{
		$args = array();
		preg_match_all('/([\w]+=\"[a-zA-Z0-9 _#\/.,-]+\")/',$tagArgs, $namevalues);
		foreach ($namevalues[1] as $nameIndex => $name)
		{
			$namevalue = $namevalues[0][$nameIndex];
			//echo "namevalue=". $namevalue;
			$namevalue = explode('=', $namevalue);
			$name = $namevalue[0];
			$value = $namevalue[1];
			$value = preg_replace('/(["]+)/', '', $value);
			//echo "name =". $name . "value =". $value;
			$args[$name] = $value;
		}
		return $args;
	}
	protected function LoadScripts() 
	{
		$html = '';
		#$self = new self();
		$cssurl=\JURI::base(). "plugins/system/". self::PLUGINNAME ."/css/";
		$jsurl=\JURI::base(). "plugins/system/". self::PLUGINNAME ."/javascript/";
		$html .= '<meta charset="utf-8">';
  		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
		$html .= '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">';
		$html .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>';
  		$html .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
  		$html .= '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>';
		$html .= '<script src="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css"></script>';
		$html .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
		#$html .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>';
		$html .= '<title>jQuery UI Datepicker - Default functionality</title>';
		$html .= '<link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">';
		$html .= '<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">';
		$html .= '<link rel="stylesheet" href="' . $cssurl . 'prana.css' . '">';
		$html .= '<link rel="stylesheet" href="' . $cssurl . 'forms.css' . '">';
		$html .= '<link rel="stylesheet" href="' . $cssurl . 'simpelboek.css' . '">';
		$html .= '<link rel="stylesheet" href="' . $cssurl . '/pranamenu.css' . '">';	// script clicky menu
		$html .= '<script src="https://code.jquery.com/jquery-1.12.4.js"></script>';
		$html .= '<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>';
		$html .= '<script src="' . $jsurl . 'mdt_tables.js' . '"></script>';
		$html .= '<script src="' . $jsurl . 'forms.js' . '"></script>';
		$html .= '<script src="' . $jsurl . 'exportcsv.js' . '"></script>';
		$html .= '<script src="' . $jsurl . 'simpelboek.js' . '"></script>';	#javascript to check forms input
		$html .= '<script src="' . $jsurl . 'pranamenu.js' . '"></script>';	#clicky menu javascripy
		return($html);
    }
}
?>

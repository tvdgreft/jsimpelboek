<?php
#######################################################################
#
# SCRIPT INFORMATION                                          TVDGREFT
# Name          :SIMPELBOEK
# Type			:system plugin
#
#
#######################################################################
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

/**
 * Class plgSystemevent
 *
 */
class PlgSystemSIMPELBOEK extends JPlugin
{
	protected $autoloadLanguage = true;
	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	/**
	 * This event is triggered after the framework has rendered the application.
	 * When this event is triggered the output of the application is available in the response buffer. 
	 *
	 * Method to replace tags in a text
	 * {bookevent event=eventcode}
	 * @param   string  $text  Text to replace tags in
	 *
	 * @return mixed
	 */
	public function onAfterRender()
	{
		$this->Parameters();
		require_once dirname( __FILE__ ) . '/bootstrap.php';
		$bootstrap = new SIMPELBOEK\Bootstrap();
		$bootstrap->init();
		$application = \JFactory::getApplication();
		if ($application->isClient('site') == false)    #Returns true if executed in the Joomla! website frontend. 
		{
			return;
		}
		$body = $bootstrap->OnShortCode($application->getBody());
		$application->setBody($body);
	}
	public function Parameters()
	{

		$GLOBALS['organisation'] = $this->params->get('organisation');
		$GLOBALS['introductie'] = $this->params->get('introductie');			#introductietekst organisatie
		$GLOBALS['logo'] = $this->params->get('logo');			#logo
		$GLOBALS['numrows'] = $this->params->get('numrows');			#Number of records per page in table
	}
}
?>
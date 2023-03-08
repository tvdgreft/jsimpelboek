<?php
namespace SIMPELBOEK;
/**
 * Functions just for joomla website
 */
/**
 * PluginExists test if plugin is available.
 */
function PluginExists($name)
{
    $plugindir = JPATH_SITE . "/plugins/system/".$name;
    if (!file_exists($plugindir))  return FALSE; 
    return TRUE;
}
function parse_args($nargs,$default)
{
	$args=$default;
	foreach ($nargs as $arg=>$value)
	{
		$args[$arg] = $value;
	}
	return($args);
}

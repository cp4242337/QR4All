<?php
/**
 * @version		$Id: factory.php 19176 2010-10-21 03:06:39Z ian $
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Joomla Framework Factory class
 *
 * @static
 * @package		Joomla.Framework
 * @since	1.5
 */
class JFactory
{
	/**
	 * Get a application object
	 *
	 * Returns a reference to the global {@link JApplication} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @param	mixed	$id 		A client identifier or name.
	 * @param	array	$config 	An optional associative array of configuration settings.
	 * @return object JApplication
	 */
	function &getApplication($id = null, $config = array(), $prefix='J')
	{
		static $instance;

		if (!is_object($instance))
		{
			jimport( 'joomla.application.application' );

			if (!$id) {
				JError::raiseError(500, 'Application Instantiation Error');
			}

			$instance = JApplication::getInstance($id, $config, $prefix);
		}

		return $instance;
	}

	

	/**
	 * Get a session object
	 *
	 * Returns a reference to the global {@link JSession} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @param array An array containing session options
	 * @return object JSession
	 */
	function &getSession($options = array())
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = JFactory::_createSession($options);
		}

		return $instance;
	}

	function &getDBO()
	{
		static $instance;

		if (!is_object($instance))
		{
			//get the debug configuration setting
			$debug = 0;

			$instance = JFactory::_createDBO();
			$instance->debug($debug);
		}

		return $instance;
	}

	

	/**
	 * Return a reference to the {@link JURI} object
	 *
	 * @access public
	 * @return object JURI
	 * @since 1.5
	 */
	function &getURI($uri = 'SERVER')
	{
		jimport('joomla.environment.uri');

		$instance =& JURI::getInstance($uri);
		return $instance;
	}

	/**
	 * Return a reference to the {@link JDate} object
	 *
	 * @access public
	 * @param mixed $time The initial time for the JDate object
	 * @param int $tzOffset The timezone offset.
	 * @return object JDate
	 * @since 1.5
	 */
	function &getDate($time = 'now', $tzOffset = 0)
	{
		jimport('joomla.utilities.date');
		static $instances;
		static $classname;
		static $mainLocale;

		if(!isset($instances)) {
			$instances = array();
		}

		$language =& JFactory::getLanguage();
		$locale = $language->getTag();

		if(!isset($classname) || $locale != $mainLocale) {
			//Store the locale for future reference
			$mainLocale = $locale;
			$localePath = JPATH_ROOT . DS . 'language' . DS . $mainLocale . DS . $mainLocale . '.date.php';
			if($mainLocale !== false && file_exists($localePath)) {
				$classname = 'JDate'.str_replace('-', '_', $mainLocale);
				JLoader::register( $classname,  $localePath);
				if(!class_exists($classname)) {
					//Something went wrong.  The file exists, but the class does not, default to JDate
					$classname = 'JDate';
				}
			} else {
				//No file, so default to JDate
				$classname = 'JDate';
			}
		}
		$key = $time . '-' . $tzOffset;

		if(!isset($instances[$classname][$key])) {
			$tmp = new $classname($time, $tzOffset);
			//We need to serialize to break the reference
			$instances[$classname][$key] = serialize($tmp);
			unset($tmp);
		}

		$date = unserialize($instances[$classname][$key]);
		return $date;
	}



	/**
	 * Create a session object
	 *
	 * @access private
	 * @param array $options An array containing session options
	 * @return object JSession
	 * @since 1.5
	 */
	function &_createSession( $options = array())
	{
		$handler = "none";
		// config time is in minutes
		$options['expire'] = ($options['expire'] * 60);

		$session = JSession::getInstance($handler, $options);
		if ($session->getState() == 'expired') {
			$session->restart();
		}

		return $session;
	}

	/**
	 * Create an database object
	 *
	 * @access private
	 * @return object JDatabase
	 * @since 1.5
	 */
	function &_createDBO()
	{
		global $dbc;

		$db =& JDatabase::getInstance( $dbc );

		

		return $db;
	}


}

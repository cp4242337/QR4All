<?php
/**
* @version		$Id: session.php 14401 2010-01-26 14:10:00Z louis $
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework


/**
 * Session table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableSession extends JTable
{
	/**
	 *
	 * @var int Primary key
	 */
	var $session_id			= null;

	/**
	 *
	 * @var string
	 */
	var $time				= null;

	/**
	 *
	 * @var string
	 */
	var $data				= null;

	/**
	*
	* @var string 
	*/
	var $form = null;

	/**
	 * Constructor
	 * @param database A database connector object
	 */
	function __construct( &$db )
	{
		parent::__construct( 'qr4_formdata_sessions', 'session_id', $db );

	}

	function insert($sessionId)
	{
		$this->session_id	= $sessionId;

		$this->time = time();
		$ret = $this->_db->insertObject( $this->_tbl, $this, 'session_id' );

		if( !$ret ) {
			$this->setError(strtolower(get_class( $this ))."::". JText::_( 'store failed' ) ."<br />" . $this->_db->stderr());
			return false;
		} else {
			return true;
		}
	}

	function update( $updateNulls = false )
	{
		$this->time = time();
		$ret = $this->_db->updateObject( $this->_tbl, $this, 'session_id', $updateNulls );

		if( !$ret ) {
			$this->setError(strtolower(get_class( $this ))."::". JText::_( 'store failed' ) ." <br />" . $this->_db->stderr());
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Destroys the pesisting session
	 */
	function destroy($userId)
	{
		

		//i do nothing

		return true;
	}

	/**
	* Purge old sessions
	*
	* @param int 	Session age in seconds
	* @return mixed Resource on success, null on fail
	*/
	function purge( $maxLifetime = 1440 )
	{
		$past = time() - $maxLifetime;
		$query = 'DELETE FROM '. $this->_tbl .' WHERE ( time < \''. (int) $past .'\' )'; // Index on 'VARCHAR'
		$this->_db->setQuery($query);

		return $this->_db->query();
	}

	/**
	 * Overloaded delete method
	 *
	 * We must override it because of the non-integer primary key
	 *
	 * @access public
	 * @return true if successful otherwise returns and error message
	 */
	function delete( $oid=null )
	{
		//if (!$this->canDelete( $msg ))
		//{
		//	return $msg;
		//}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = $oid;
		}

		$query = 'DELETE FROM '.$this->_db->nameQuote( $this->_tbl ).
				' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery( $query );

		if ($this->_db->query())
		{
			return true;
		}
		else
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	}
}

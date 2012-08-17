<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class rsseoControllerredirects extends rsseoController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('apply' ,  'save');
		$this->registerTask( 'unpublish' , 'publish');
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('redirects');
		$post=JRequest::get('post',JREQUEST_ALLOWRAW);
		
		$row= & JTable::getInstance('rsseo_redirects','Table');
		
		if(!$row->bind($post)) {
			return JError::raiseWarning(500, $row->getError());
		}
		
		
		if ($row->store()) {
			$msg = JText::_('RSSEO_REDIRECT_SAVE' );
		} else {
			JError::raiseWarning(500, $row->getError());

			$msg = JText::_('RSSEO_REDIRECT_SAVE_ERROR' );
		}
		switch(JRequest::getCmd('task'))
		{
			case 'apply' :
					$link = 'index.php?option=com_rsseo&task=editredirect&cid='.$row->IdRedirect;

			break;
			
			case 'save' :
					$link = 'index.php?option=com_rsseo&task=listredirects';

			break;
		}
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$db = & JFactory::getDBO();
		$model = $this->getModel('redirects');
		$row =& JTable::getInstance('rsseo_redirects','Table');
		$cids=JRequest::getVar('cid',array(0),'post','array');
		
		$db->setQuery("SELECT COUNT(*) AS cnt FROM #__rsseo_redirects WHERE IdRedirect IN ('".implode("','",$cids)."')");
		$redirectsFound = $db->loadResult();
		
		if(!empty($redirectsFound)) 
		{
			$db->setQuery("DELETE FROM #__rsseo_redirects WHERE IdRedirect IN ('".implode("','",$cids)."')");
			$db->query();
			$msg= JText::sprintf('RSSEO_REDIRECT_DELETE',count($cids));
			$this->setRedirect( 'index.php?option=com_rsseo&task=listredirects', $msg );
		}
		else 
		
		$msg = JText::_('RSSEO_REDIRECT_DELETE_ERROR' );
		$this->setRedirect( 'index.php?option=com_rsseo&task=listredirects', $msg , 'error'); 
	}

	function publish()
	{
		global $mainframe;

		$db 	=& JFactory::getDBO();

		$cid		= JRequest::getVar( 'cid', array(), '', 'array' );
		$publish	= ( $this->getTask() == 'publish' ? 1 : 0 );

		JArrayHelper::toInteger($cid);
		if (count( $cid ) < 1)
		{
			$action = $publish ? 'publish' : 'unpublish';
			JError::raiseError(500, JText::_('RSSEO_PAGE_PUBLISHED_ERROR' .$action, true ) );
		}

		$cids = implode( ',', $cid );
		
		$query = 'UPDATE #__rsseo_redirects'
		. ' SET published = ' . (int) $publish
		. ' WHERE IdRedirect IN ( '. $cids .' )'
		;
		$db->setQuery( $query );
		if (!$db->query())
		{
			JError::raiseError(500, $db->getErrorMsg() );
		}

		$mainframe->redirect( 'index.php?option=com_rsseo&task=listredirects' );
	}
	
	
	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_rsseo&task=listredirects');
	}

}
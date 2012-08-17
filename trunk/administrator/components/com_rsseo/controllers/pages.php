<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class rsseoControllerpages extends rsseoController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'editCategories'  , 	'edit' );
		$this->registerTask('apply' ,  'save');
		$this->registerTask( 'unpublish' , 'publish');
		$this->registerTask( 'notinsitemap' , 'insitemap');
		$this->registerTask( 'orderup' , 'move');
		$this->registerTask( 'orderdown' , 'move');
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('pages');
		$post=JRequest::get('post',JREQUEST_ALLOWRAW);
		
		$row= & JTable::getInstance('rsseo_pages','Table');
		
		if(!$row->bind($post)) {
			return JError::raiseWarning(500, $row->getError());
		}
		
		$row->PageURL = str_replace(array('&amp;','&apos;','&quot;','&gt;','&lt;'),array("&","'",'"',">","<"),$row->PageURL);
		$row->PageURL = str_replace(array("&","'",'"',">","<"),array('&amp;','&apos;','&quot;','&gt;','&lt;'),$row->PageURL);

		$row->PageModified = 1;
		
		if(isset($post['restoreoriginal']) && $post['restoreoriginal'] == 1)
		{
			$row->PageModified = 0 ;
			$row->PageCrawled = 0 ;
		}
		
		if ($row->store()) {
			$msg = JText::_('RSSEO_PAGE_SAVE' );
		} else {
			JError::raiseWarning(500, $row->getError());

			$msg = JText::_('RSSEO_PAGE_SAVE_ERROR' );
		}
		rsseoControllerpages::checkpage();
		switch(JRequest::getCmd('task'))
		{
			case 'apply' :
					$link = 'index.php?option=com_rsseo&task=editpage&cid='.$row->IdPage;

			break;
			
			case 'save' :
					$link = 'index.php?option=com_rsseo&task=listpages';

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
		$model = $this->getModel('pages');
		$row =& JTable::getInstance('rsseo_pages','Table');
		$cids=JRequest::getVar('cid',array(0),'post','array');
		foreach($cids as $i=>$cid)
			if($cid==1) unset($cids[$i]);
		$db->setQuery("SELECT COUNT(*) AS cnt FROM #__rsseo_pages WHERE IdPage IN ('".implode("','",$cids)."')");
		$pagesFound = $db->loadResult();
		
		if(!empty($pagesFound)) 
		{
			$db->setQuery("DELETE FROM #__rsseo_pages WHERE IdPage IN ('".implode("','",$cids)."')");
			$db->query();
			$msg= JText::sprintf('RSSEO_PAGES_DELETE',count($cids));
			$this->setRedirect( 'index.php?option=com_rsseo&task=listpages', $msg );
		}
		else 
		
		$msg = JText::_('RSSEO_PAGES_DELETE_ERROR' );
		$this->setRedirect( 'index.php?option=com_rsseo&task=listpages', $msg , 'error'); 
	}
	
	function removeall()
	{
		$db = JFactory::getDBO();
		
		$db->setQuery("TRUNCATE TABLE #__rsseo_pages");
		$db->query();
		
		$db->setQuery("INSERT INTO `#__rsseo_pages` (`IdPage`, `PageURL`, `PageTitle`, `PageKeywords`, `PageDescription`, `PageSitemap`, `PageInSitemap` , `PageCrawled`, `DatePageCrawled`, `PageLevel`, `PageGrade`, `params`, `published`) VALUES(1, '', '', '', '', 0, 1, 0, 0, 0, 0.00, '', 1);");
		$db->query();
		
		$msg = JText::_('RSSEO_ALL_PAGES_DELETED' );
		$this->setRedirect( 'index.php?option=com_rsseo&task=listpages', $msg , 'error'); 
	}
	
	function publish()
	{
		
		$db 	=& JFactory::getDBO();
		$cid		= JRequest::getVar( 'cid', array(), '', 'array' );
		$publish	= ( $this->getTask() == 'publish' ? 1 : 0 );

		JArrayHelper::toInteger($cid);
		//echo $cid; die();
		if (count( $cid ) < 1)
		{
			$action = $publish ? 'publish' : 'unpublish';
			JError::raiseError(500, JText::_('RSSEO_PAGE_PUBLISHED_ERROR' .$action, true ) );
		}

		$cids = implode( ',', $cid );
		
		$query = 'UPDATE #__rsseo_pages'
		. ' SET published = ' . (int) $publish
		. ' WHERE IdPage IN ( '. $cids .' )'
		;
		$db->setQuery( $query );
		if (!$db->query())
		{
			JError::raiseError(500, $db->getErrorMsg() );
		}
		rsseoControllerpages::checkpage();
		$this->setRedirect( 'index.php?option=com_rsseo&task=listpages' );
	}
	
	function insitemap()
	{
		$db		 	=& JFactory::getDBO();
		$cid		= JRequest::getVar( 'cid', array(), '', 'array' );
		$publish	= ($this->getTask() == 'insitemap' ? 1 : 0 );

		JArrayHelper::toInteger($cid);
		if (count( $cid ) < 1)
		{
			$action = $publish ? 'insitemap' : 'notinsitemap';
			JError::raiseError(500, JText::_('RSSEO_PAGE_PUBLISHED_ERROR' .$action, true ) );
		}

		$cids = implode( ',', $cid );
		
		$query = 'UPDATE #__rsseo_pages'
		. ' SET PageInSitemap = ' . (int) $publish
		. ' WHERE IdPage IN ( '. $cids .' )'
		;
		$db->setQuery( $query );
		if (!$db->query())
		{
			JError::raiseError(500, $db->getErrorMsg() );
		}
		rsseoControllerpages::checkpage();
		$this->setRedirect( 'index.php?option=com_rsseo&task=listpages' );
	}
	
	
	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$this->setRedirect( 'index.php?option=com_rsseo&task=listpages');
	}
	
	function restore()
	{
		$cid= intval(JRequest::getVar('cid',0,'request'));
		$model = $this->getModel('pages');
		$row= & JTable::getInstance('rsseo_pages','Table');
		$row->load($cid);
		$row->PageModified = 0 ;
		$row->PageCrawled = 0 ;
		$row->store();
		
		$newPage = rsseoHelper::checkPage($cid);
		$newPage->DatePageCrawled = time();
		$newPage->store();
		
	}

	function checkpage()
	{
		$cid=JRequest::getVar('cid',array(0),'request','array');
		if(is_array($cid)) $cid=intval($cid[0]);
		
		$newPage = rsseoHelper::checkPage($cid,$cid);
		$newPage->PageTitle = str_replace('&amp;','&',$newPage->PageTitle);
		$newPage->PageKeywords = str_replace('&amp;','&',$newPage->PageKeywords);
		$newPage->PageDescription = str_replace('&amp;','&',$newPage->PageDescription);
		$newPage->DatePageCrawled = time();
		$newPage->store();
	
		$this->setRedirect( 'index.php?option=com_rsseo&task=editpage&cid='.$cid);
	}
	
	function checktimesize()
	{
		require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'helpers'.DS.'rsseo.php');
		include(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_rsseo'.DS.'helpers'.DS.'class.webpagesize.php');
		$cid = intval(JRequest::getVar('cid',0,'request'));
		
		$model = $this->getModel('pages');
		$row= & JTable::getInstance('rsseo_pages','Table');
		$row->load($cid);
		
		set_time_limit(100);
		$size = new WebpageSize;
		$size->setURL(JURI::root().$row->PageURL);
		$page_size = $size->sizeofpage();
		$time_total = $size->getTime();
		$page_load = number_format($time_total,3);
		
		echo JText::sprintf('RSSEO_PAGE_SIZE_DESCR',$page_size,$cid)."\n".JText::sprintf('RSSEO_PAGE_TIME_DESCR',$page_load);
		exit();
	}
	
}
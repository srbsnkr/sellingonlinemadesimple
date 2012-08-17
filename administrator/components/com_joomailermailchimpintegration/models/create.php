<?php
/**
 * Copyright (C) 2011  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted Access' );

jimport( 'joomla.application.component.model' );


class joomailermailchimpintegrationsModelCreate extends JModel
{

    /**
     * joomailermailchimpintegrations data array
     *
     * @var array
     */
    var $_data;
    var $_total = null;
    var $_K2total = null;
    var $_pagination = null;
    var $_paginationK2 = null;

    function __construct()
    {
	parent::__construct();

	$mainframe =& JFactory::getApplication();
	$option = JRequest::getCmd('option');

	// Joomla core pagination
	// Get pagination request variables
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
	$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

	// In case limit has been changed, adjust it
	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

	$this->setState('limit', $limit);
	$this->setState('limitstart', $limitstart);
    }

    /**
     * Returns the query
     * @return string The query to be used to retrieve the data
     */
    function _buildQuery()
    {
	$mainframe =& JFactory::getApplication();
	$db =& JFactory::getDBO();
//	$filter_cat     = $mainframe->getUserStateFromRequest( "catid",	'catid', 	-1, 'string' );
	$filter_cat = JRequest::getVar( "cat_filter", -1, "", "int" );
//	$filter_sec	= $mainframe->getUserStateFromRequest( "filter_sectionid",	'filter_sectionid', -1, 'string' );
	$filter_sec = JRequest::getVar( "sec_filter", -1, "", "int" );
	$filter_status  = $mainframe->getUserStateFromRequest( "filter_status", 'filter_status', 0, 'string' );
	$search		= $mainframe->getUserStateFromRequest( "search",	    'search', 		'', 'string' );
	$search		= JString::strtolower( $search );

	$limit	    = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart = $mainframe->getUserStateFromRequest( 'limitstart', 'limitstart', 0, 'int' );

	$w = array();
	if (isset( $search ) && $search!= '')
	{
	    $searchEscaped = $db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
	    $where[] = ' a.title LIKE '.$searchEscaped;
	}
	if ($filter_sec > -1) {
	    $where[] = ' a.sectionid = '.(int) $filter_sec;
	}
	if ($filter_cat > -1) {
	    $where[] = ' a.catid = '.(int) $filter_cat;
	}
/*
	if($filter_cat >=0 && $filter_sec <0) {
	    $secquery = 'SELECT section FROM #__categories WHERE id = "'.$filter_cat.'"';
	    $db->setQuery($secquery);
	    $filter_sec = $db->loadResult();
	}
*/

	$where[] = " a.state = 1 ";

	$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );

	if( version_compare(JVERSION,'1.6.0','ge') ){
	    $query = ' SELECT a.*, b.name, d.title as category'.
		 ' FROM #__content as a'.
		 ' LEFT JOIN #__users as b ON b.id = a.created_by'.
		 ' LEFT JOIN #__categories as d ON d.id = a.catid'.
		 $where .
		 ' ORDER BY a.catid, a.ordering ';
	} else {
	    $query = ' SELECT a.*, b.name, c.title as section, d.title as category'.
		 ' FROM #__content as a'.
		 ' LEFT JOIN #__users as b ON b.id = a.created_by'.
		 ' LEFT JOIN #__sections as c ON c.id = a.sectionid'.
		 ' LEFT JOIN #__categories as d ON d.id = a.catid'.
		 $where .
		 ' ORDER BY a.sectionid, a.ordering ';
	}
	
	return $query;
	}

	/**
	 * Retrieves the data
	 * @return an Array of objects containing the data
	 */
	function getData()
	{
	    // Lets load the data if it doesn't already exist
	    if (empty( $this->_data ))
	    {
		$query = $this->_buildQuery();
//	        $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

		$db =& JFactory::getDBO();
		$db->setQuery( $query, $this->getState('limitstart'), $this->getState('limit') );
		$this->_data = $db->loadObjectList();
	    }

	    return $this->_data;
	}

    function getCore(){
	$db =& JFactory::getDBO();

	$query = "SELECT COUNT(id) FROM #__content WHERE state = 1";
	$db->setQuery( $query );
	$count = $db->loadResult();

	if($count>0){
	    return true;
	} else {
	    return false;
	}
    }

    function _buildQueryK2(){
	$mainframe =& JFactory::getApplication();
	$db =& JFactory::getDBO();

	$filter_k2cat	= $mainframe->getUserStateFromRequest('k2cat_filter', 'k2cat_filter',	0,	'int');

	if(!$filter_k2cat) {
	    $filter_k2cat = JRequest::getVar('k2cat_filter', 0, '', 'int');
	}

	// include previously selected articles
	$k2article = JRequest::getVar('k2article', '');
	if( isset($k2article[0])){
	    $k2article = implode("','", $k2article);
	    $previous = " OR a.id IN ('".$k2article."') ";
	} else {
	    $previous = '';
	}
	$filter = ($filter_k2cat > 0) ? ' AND ( a.catid = '.$db->Quote($filter_k2cat).' '.$previous.') ' : '';

	$search	= JRequest::getVar('searchK2', '', 'post' );
	$search	= JString::strtolower( $search );

	if (isset( $search ) && $search!= '')
	{
	    $searchEscaped = $db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
	    $search = ' AND a.title LIKE '.$searchEscaped;
	}

	

	$query = " SELECT a.*, b.name, c.name as category ".
		 " FROM #__k2_items as a".
		 " LEFT JOIN #__users as b ON b.id = a.created_by".
		 " LEFT JOIN #__k2_categories as c ON c.id = a.catid".
		 " WHERE a.published =1".
	$filter.
	$search;

	return $query;
    }

    function getK2installed(){
	$db =& JFactory::getDBO();
	$query = "SELECT id FROM #__components WHERE `option` = 'com_k2'";
	$db->setQuery( $query );
	$k2_exists = $db->loadResult();

	return ($k2_exists?true:false);
    }

    function getK2( $k2Limitstart = 0, $limit = 0 ) {

	if( ! version_compare(JVERSION,'1.6.0','ge') ){
	    $db =& JFactory::getDBO();
	    $query = $this->_buildQueryK2();
	    $db->setQuery( $query, $k2Limitstart, $limit );
	    $k2items = $db->loadObjectList();
	} else {
	    $k2items = array();
	}

	return $k2items;
    }

    function getK2Total()
    {
	// Load the content if it doesn't already exist
	if (empty($this->_K2total)) {
	    $query = $this->_buildQueryK2();
	    $this->_K2total = $this->_getListCount($query);
	}
	return $this->_K2total;
    }

    function getSec() {

	if( version_compare(JVERSION,'1.6.0','ge') ){
	    return array();
	} else {
	    $db = & JFactory::getDBO();
	    $query = 'SELECT id, title FROM #__sections WHERE scope="content"';
	    $db->setQuery($query);
	    $result[] = array('id'=>-1,'title'=>'-- '.JText::_('JM_SELECT_A_SECTION').' --');
	    $result = array_merge($result,$db->loadAssocList());
	    return $result;
	}
    }

    function getCat($secid = 'NULL') {

	$db = & JFactory::getDBO();
	if( version_compare(JVERSION,'1.6.0','ge') ){
	    $query = "SELECT c.id, c.title FROM #__categories c WHERE c.extension='com_content'";
	} else {
	    $and = ($secid) ? ' AND s.id ="'.$secid.'"' : '';
	    $query = 'SELECT c.id, c.title FROM #__categories c JOIN #__sections s ON c.section = s.id  WHERE s.scope="content" '.$and.' ORDER BY c.title ASC';
	}
	$db->setQuery($query);
	$result[] = array('id'=>-1,'title'=>'-- '.JText::_('JM_SELECT_A_CATEGORY').' --');
	$result = array_merge($result,$db->loadAssocList());

	return $result;
    }

    /*function getK2Cat() {

    $db = & JFactory::getDBO();
    $and = ($secid) ? ' AND s.id ="'.$secid.'"' : '';
    $query = 'SELECT c.id, c.name AS title FROM #__k2_categories c WHERE c.published="1"';
    $db->setQuery($query);
    $result[] = array('id'=>0,'title'=>'-- '.JText::_('SELECT A CATEGORY').' --');
    $result = array_merge($result,$db->loadAssocList());
    return $result;

    }*/

    function getSeccat(){

	$db =& JFactory::getDBO();
	$query = "SELECT s.id, s.title, c.id as cid, c.title as ctitle FROM #__sections as s JOIN #__categories as c ON s.id=c.section";
	$db->setQuery( $query );
	$seccat = $db->loadObjectList();
	$result = array();
	$result[0] = new stdClass;
	$result[0]->cid = 0;
	$result[0]->title = JText::_('uncategorized');
	$result[0]->ctitle = JText::_('uncategorized');
	for($i=0;$i<count($seccat);$i++){
	    $result[$i+1] = $seccat[$i];
	}

	return $result;
    }

    function getK2cat($catid = NULL){

	$db =& JFactory::getDBO();
	$where = ($catid > 0) ? ' WHERE id="'.$catid.'"' : '';
	$query = "SELECT id, name FROM #__k2_categories".$where;
	$db->setQuery( $query );
	$seccat = $db->loadObjectList();

	return $seccat;
    }

    function getVMproducts(){
	jimport('joomla.filesystem.file');
	if ( JFile::exists('../administrator/components/com_virtuemart/admin.virtuemart.php') ) {
	    $db =& JFactory::getDBO();

	    $query = 'SELECT a.product_id,a.product_name,a.product_thumb_image,
		      b.product_price,b.product_currency,
		      c.category_id,
		      d.category_name
		      FROM #__vm_product as a
		      INNER JOIN #__vm_product_price as b
		      ON a.product_id = b.product_id
		      INNER JOIN #__vm_product_category_xref as c
		      ON a.product_id = c.product_id
		      INNER JOIN #__vm_category as d
		      ON c.category_id = d.category_id
		      WHERE a.product_publish = "Y"
		      ORDER BY d.category_name,a.product_name,b.product_price ASC
		      ';
	    $db->setQuery( $query );
	    $products = $db->loadObjectList();

	    return $products;
	} else {
	    return false;
	}
    }

    function getTotal()
    {
	// Load the content if it doesn't already exist
	if (empty($this->_total)) {
	    $query = $this->_buildQuery();
	    $this->_total = $this->_getListCount($query);
	}
	return $this->_total;
    }
    /*
     function getTotalK2()
     {
     // Load the content if it doesn't already exist
     if (empty($this->_total)) {
     $query = $this->_buildQueryK2();
     $this->_total = $this->_getListCount($query);
     }
     return $this->_total;
     }
     */

    function getPagination()
    {
	// Load the content if it doesn't already exist
	if (empty($this->_pagination)) {
	    jimport('joomla.html.pagination');
	    $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
	}
	return $this->_pagination;
    }
    /*
     function getPaginationK2()
     {
	if($this->getK2()){
	if (empty($this->_paginationK2)) {
	jimport('joomla.html.pagination');
	$this->_paginationK2 = new JPagination($this->getTotalK2(), $this->getState('limitstartK2'), $this->getState('limitK2') );
	}
	return $this->_paginationK2;
	} else {
	return '';
	}
    }
    */


    function MC_object()
    {
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$MCapi  = $params->get( $paramsPrefix.'MCapi' );
	$MC = new joomlamailerMCAPI($MCapi);
	return $MC;
    }

    function getMClists(){
	$MC      = $this->MC_object();
	$lists   = $MC->lists();
	return $lists;
    }

    function getMergeTags(){
	$MC = $this->MC_object();
	$result = array();
	foreach($this->getMClists() as $list){
	    $merge = $MC->listMergeVars($list['id']);

	    for($i=0;$i<count($merge);$i++){
		if( $merge[$i]['tag'] == 'FNAME' ||
		$merge[$i]['tag'] == 'LNAME' ||
		$merge[$i]['tag'] == 'EMAIL' ){
		    $unset[] = $i;
		}
	    }
	    if( isset($unset) ){
		foreach($unset as $u){
		    unset($merge[$u]);
		}
	    }
	    if($merge){
		$result[$list['name']] = $merge;
	    }
	}

	return $result;
    }



    function getLists()
    {
	$mainframe =& JFactory::getApplication();

	// Initialize variables
	$db =& JFactory::getDBO();

	// Get some variables from the request
	$sectionid	    = JRequest::getVar( 'sectionid', -1, '', 'int' );
	$redirect	    = $sectionid;
	$option		    = JRequest::getCmd( 'option' );
	$filter_order	    = $mainframe->getUserStateFromRequest('articleelement.filter_order',		'filter_order',		'',	'cmd');
	$filter_order_Dir   = $mainframe->getUserStateFromRequest('articleelement.filter_order_Dir',	'filter_order_Dir',	'',	'word');
	$filter_state	    = $mainframe->getUserStateFromRequest('articleelement.filter_state',		'filter_state',		'',	'word');
	$catid		    = $mainframe->getUserStateFromRequest('articleelement.catid',				'catid',			0,	'int');
	$filter_authorid    = $mainframe->getUserStateFromRequest('articleelement.filter_authorid',		'filter_authorid',	0,	'int');
	$filter_sectionid   = $mainframe->getUserStateFromRequest('articleelement.filter_sectionid',	'filter_sectionid',	-1,	'int');
	$limit		    = $mainframe->getUserStateFromRequest('global.list.limit',					'limit', $mainframe->getCfg('list_limit'), 'int');
	$limitstart	    = $mainframe->getUserStateFromRequest('articleelement.limitstart',			'limitstart',		0,	'int');
	$search		    = $mainframe->getUserStateFromRequest('articleelement.search',				'search',			'',	'string');
	if (strpos($search, '"') !== false) {
	    $search = str_replace(array('=', '<'), '', $search);
	}
	$search = JString::strtolower($search);
/*
	// get list of categories for dropdown filter
	$filter = ($filter_sectionid >= 0) ? ' WHERE cc.section = '.$db->Quote($filter_sectionid).' AND' : ' WHERE ';

	// get list of categories for dropdown filter
	if( version_compare(JVERSION,'1.6.0','ge') ){
	    $query = 'SELECT cc.id AS value, cc.title AS text' .
		 ' FROM #__categories AS cc' .
		 $filter .
		 " cc.extension = 'com_content' ";
	} else {
	    $query = 'SELECT cc.id AS value, cc.title AS text, section' .
		 ' FROM #__categories AS cc' .
		 ' INNER JOIN #__sections AS s ON s.id = cc.section' .
		 $filter .
		 ' ORDER BY s.ordering, cc.ordering';
	}
	

	$lists['catid'] = $this->filterCategory($query, $catid);

	// get list of sections for dropdown filter
	if( ! version_compare(JVERSION,'1.6.0','ge') ){
	$javascript = 'onchange="joomailermailchimpintegration_ajax_loader();document.adminForm.submit();"';
	$lists['sectionid'] = JHTML::_('list.section', 'filter_sectionid', $filter_sectionid, $javascript);
	}

	// k2 category filter
	if($this->getK2() && ! version_compare(JVERSION,'1.6.0','ge')){
	    $selected = $mainframe->getUserStateFromRequest('filter_k2cat',	'filter_k2cat',	-1,	'int');
	    $query = 'SELECT id, name' .
		     ' FROM #__k2_categories ' .
		     ' WHERE published = 1 ' .
		     ' ORDER BY ordering';
	    $db->setQuery($query);
	    $k2Cats = $db->loadObjectList();

	    $options[] = JHTML::_('select.option', '-1', '- '. JText::_( 'Select a Category' ) .' -');
	    foreach($k2Cats as $value) {
		$options[] = JHTML::_('select.option', $value->id, $value->name );
	    }

	    //function genericlist( $arr, $name, $attribs = null, $key = 'value', $text = 'text', $selected = NULL, $idtag = false, $translate = false )

	    $lists['k2Cat'] = JHTML::_('select.genericlist', $options, 'filter_k2cat', $javascript, 'value', 'text', $selected, 'filter_k2cat' );
	} else {
	    $lists['k2Cat'] = '';
	}
*/
	// table ordering
	$lists['order_Dir'] = $filter_order_Dir;
	$lists['order']	    = $filter_order;

	// search filter
	$lists['search'] = $search;

	return $lists;
    }

    function filterCategory($query, $active = NULL)
    {
	// Initialize variables
	$db =& JFactory::getDBO();

	$categories[] = JHTML::_('select.option', '0', '- '.JText::_('Select Category').' -');
	$db->setQuery($query);
	$dbCategories = $db->loadObjectList();
	$categories = ( is_array( $dbCategories ) )? array_merge( $categories, $dbCategories ) : $categories;

	$category = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $active);

	return $category;
    }

    function getImagesDropdown(){

	$dropdown = '<select name="fbImage" id="fbImage">';
	jimport('joomla.filesystem.file');

	$dropdown .= '<optgroup label="images">';
	$images = Jfolder::files( '../images' , '.', false, false, array('index.html'));
	foreach($images as $image){
	    $dropdown .= '<option value="images/'.$image.'">'.$image.'</option>';
	}
	$dropdown .= '</optgroup>';

	$imageFolders = Jfolder::listFolderTree( '../images/' , '', 1);
	foreach($imageFolders as $folder){
	    $images = Jfolder::files( $folder["fullname"] , '.', true, false, array('index.html'));
	    if($images){
		$dropdown .= '<optgroup label="'.str_replace('../','', $folder["fullname"]).'">';
		foreach($images as $image){
		    $dropdown .= '<option value="'.str_replace('../','', $folder["fullname"]).$image.'">'.$image.'</option>';
		}
		$dropdown .= '</optgroup>';
	    }
	}
	$dropdown .= '</select>';

	return $dropdown;
    }

    function getFolders()
    {
	$MC = $this->MC_object();
	$folders = $MC->campaignFolders();
	return $folders;
    }

    function createFolder($folder_name)
    {
	$MC = $this->MC_object();

	// check if a folder with the given name exists
	$folders = $this->getFolders();
	foreach($folders as $folder){
	    if( $folder['name'] == $folder_name ){
		return $folder['folder_id'];
	    }
	}
	// create new folder
	$folder_id = $MC->createFolder($folder_name);
	return $folder_id;
    }
	
    function getJomsocial( $jsLimitstart, $limit ){

	jimport('joomla.filesystem.file');
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_community'.DS.'admin.community.php')){
	    $db	= & JFactory::getDBO();
	    $query = "SELECT * FROM #__users WHERE block = 0 ORDER BY id DESC";
	    $db->setQuery($query, $jsLimitstart, $limit);
	    $users = $db->loadObjectList();
	    return $users;
	} else {
	    return false;
	}
    }

    function getJomsocialTotal(){
	jimport('joomla.filesystem.file');
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_community'.DS.'admin.community.php')){
	    $db	= & JFactory::getDBO();
	    $query = "SELECT count(*) FROM #__users WHERE block = 0 ORDER BY id DESC";
	    $db->setQuery($query);
	    $total = $db->loadResult();
	    return $total;
	} else {
	    return 0;
	}
    }
	
    function getJomsocialFields(){
	jimport('joomla.filesystem.file');
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_community'.DS.'admin.community.php')){
	    $db	=& JFactory::getDBO();

	    $query = "SELECT * FROM #__community_fields WHERE published = 1 AND type != 'group'";
	    $db->setQuery($query, 0, 20);
	    $fields = $db->loadObjectList();

	    return $fields;
	} else {
	    return false;
	}
    }
	
    function getFieldValues( $uid, $fields ){

	$fields = implode("','",$fields);
	$db	= & JFactory::getDBO();
	$query = "SELECT f.name, f.type, v.value FROM #__community_fields_values as v ".
		 "JOIN #__community_fields as f ON f.id = v.field_id ".
		 "WHERE v.user_id = '".$uid."' AND v.field_id IN ('".$fields."')";
	$db->setQuery($query);
	$values = $db->loadObjectList();

	return $values;
    }
    function getJomsocialDiscussions( $ids = false ){

	jimport('joomla.filesystem.file');
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_community'.DS.'admin.community.php')){

	    if($ids){
		$where = " WHERE d.id IN ('".implode("','",$ids)."') ";
	    } else {
		$where = '';
	    }
	    $db =& JFactory::getDBO();
	    $query = "SELECT d.*, g.name FROM #__community_groups_discuss as d ".
		     "JOIN #__community_groups as g ON d.groupid = g.id ".
		     $where.
		     "ORDER BY d.created DESC";
	    $db->setQuery($query);
	    $data = $db->loadObjectList();
	    if(isset($data[0])){
		return $data;
	    } else {
		return false;
	    }
	} else {
	    return false;
	}
    }
	
    function getAECconfig(){
	jimport('joomla.filesystem.file');
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_acctexp'.DS.'admin.acctexp.php')){

	    $db	= & JFactory::getDBO();
	    $query = "SELECT settings FROM #__acctexp_config";
	    $db->setQuery($query);
	    $config = $db->loadResult();

	    return unserialize( base64_decode( $config ));
	} else {
	    return false;
	}
    }
		
    function getAECplans( $ids = false ){

	jimport('joomla.filesystem.file');
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_acctexp'.DS.'admin.acctexp.php')){

	    if($ids){
		$where = "AND id IN ('".implode("','",$ids)."')";
	    } else {
		$where = '';
	    }

	    $db =& JFactory::getDBO();
	    $query = "SELECT * FROM #__acctexp_plans WHERE active = 1 ".$where." ORDER BY ordering ASC";
	    $db->setQuery($query);
	    $plans = $db->loadObjectList();

	    return $plans;
	} else {
	    return false;
	}
    }
	
    function getAmbra( $ids = false ){

	jimport('joomla.filesystem.file');
	if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ambrasubs'.DS.'ambrasubs.php')){

	    if($ids){
		$where = "AND id IN ('".implode("','",$ids)."')";
	    } else {
		$where = '';
	    }

	    $db =& JFactory::getDBO();
	    $query = "SELECT * FROM #__ambrasubs_types WHERE published = 1 ".$where." ORDER BY ordering ASC";
	    $db->setQuery($query);
	    $plans = $db->loadObjectList();

	    return $plans;
	} else {
	    return false;
	}

    }

}

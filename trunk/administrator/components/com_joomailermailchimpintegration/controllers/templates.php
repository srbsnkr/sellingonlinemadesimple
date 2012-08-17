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

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

require_once(JPATH_ADMINISTRATOR.'/components/com_joomailermailchimpintegration/libraries/fileuploader.php');

$task =	JRequest::getVar('task', '', 'post', 'string', JREQUEST_ALLOWRAW );

class joomailermailchimpintegrationsControllerTemplates extends joomailermailchimpintegrationsController
{

    function __construct()
    {
	parent::__construct();

	// Register Extra tasks
	$this->registerTask( 'add' , 'upload' );
	$this->registerTask( 'start_upload' , 'start_upload' );
    }

    function edit()
    {
	JRequest::setVar( 'view', 'templates' );
	JRequest::setVar( 'layout', 'edit'  );
	JRequest::setVar( 'hidemainmenu', 1);

	parent::display();
    }
	
    function upload()
    {
	JRequest::setVar( 'view', 'templates' );
	JRequest::setVar( 'layout', 'upload'  );
	JRequest::setVar( 'hidemainmenu', 1);

	parent::display();
    }

    function start_upload()
    {
	$mainframe	=& JFactory::getApplication();
	$file 		= JRequest::getVar( 'Filedata', '', 'files', 'array' );
	$folder		= JPATH_ADMINISTRATOR.DS.'components/com_joomailermailchimpintegration/templates/';
	$format		= JRequest::getVar( 'format', 'html', '', 'cmd');
	$return		= JRequest::getVar( 'return-url', null, 'post', 'base64' );
	$err		= null;

	// Set FTP credentials, if given
	jimport('joomla.client.helper');
	JClientHelper::setCredentialsFromRequest('ftp');

	// Make the filename safe
	jimport('joomla.filesystem.file');
	$file['name']	= JFile::makeSafe($file['name']);

	if (isset($file['name'])) {
	    $filepath = JPath::clean($folder.DS.strtolower($file['name']));

	    if (!$this->canUpload( $file, $err )) {
		if ($format == 'json') {
		    jimport('joomla.error.log');
		    $log = &JLog::getInstance('upload.error.php');
		    $log->addEntry(array('comment' => 'Invalid: '.$filepath.': '.$err));
		    header('HTTP/1.0 415 Unsupported Media Type');
		    jexit('Error. Unsupported Media Type!');
		} else {
		    JError::raiseNotice(100, JText::_($err));
		    if ($return) {
			$mainframe->redirect(base64_decode($return));
		    }
		    return;
		}
	    }

	    if (JFile::exists($filepath)) {
		if ($format == 'json') {
		    jimport('joomla.error.log');
		    $log = &JLog::getInstance('upload.error.php');
		    $log->addEntry(array('comment' => 'File already exists: '.$filepath));
		    header('HTTP/1.0 409 Conflict');
		    jexit('Error. File already exists');
		} else {
		    JError::raiseNotice(100, JText::_('Error. File already exists'));
		    if ($return) {
			$mainframe->redirect(base64_decode($return));
		    }
		    return;
		}
	    }

	    if (!JFile::upload($file['tmp_name'], $filepath)) {
		if ($format == 'json') {
		    jimport('joomla.error.log');
		    $log = &JLog::getInstance('upload.error.php');
		    $log->addEntry(array('comment' => 'Cannot upload: '.$filepath));
		    header('HTTP/1.0 400 Bad Request');
		    jexit('Error. Unable to upload file');
		} else {
		    JError::raiseWarning(100, JText::_('Error. Unable to upload file'));
		    if ($return) {
			$mainframe->redirect(base64_decode($return));
		    }
		    return;
		}
	    } else {
		if ($format == 'json') {
		    jimport('joomla.error.log');
		    $log = &JLog::getInstance();
		    $log->addEntry(array('comment' => $folder));
		    jexit(JText::_('JM_UPLOAD_COMPLETE'));
		} else {
		    if ( $this->unzip( $folder, strtolower($file['name']) )) {
			$msg = JText::_('JM_UPLOAD_COMPLETE');
		    } else {
			$msg = JText::_('Error. Unable to upload file');
		    }
		}
	    }
	} else {
	    $mainframe->redirect('index.php', 'Invalid Request', 'error');
	}

        $link = 'index.php?option=com_joomailermailchimpintegration&view=templates';
        $this->setRedirect($link, $msg);

    }

    function canUpload( $file, &$err )
    {
	$params = &JComponentHelper::getParams( 'com_media' );

	if(empty($file['name'])) {
	    $err = 'JM_PLEASE_SELECT_A_FILE_TO_UPLOAD';
	    return false;
	}

	jimport('joomla.filesystem.file');
	if ($file['name'] !== JFile::makesafe($file['name'])) {
	    $err = 'JM_WARNFILENAME';
	    return false;
	}

	$format = strtolower(JFile::getExt($file['name']));

	$allowable = array( 'zip', 'gzip', 'gz', 'tar', 'tgz' );
	$ignored = explode(',', $params->get( 'ignore_extensions' ));
	if (!in_array($format, $allowable) && !in_array($format,$ignored))
	{
	    $err = 'JM_WARNFILETYPE';
	    return false;
	}

	$maxSize = (int) $params->get( 'upload_maxsize', 0 );
	if ($maxSize > 0 && (int) $file['size'] > $maxSize)
	{
	    $err = 'JM_WARNFILETOOLARGE';
	    return false;
	}

	$user = JFactory::getUser();
	$imginfo = null;
	if($params->get('restrict_uploads',1) ) {
	    $images = explode( ',', $params->get( 'image_extensions' ));
	    if(in_array($format, $images)) { // if its an image run it through getimagesize
		if(($imginfo = getimagesize($file['tmp_name'])) === FALSE) {
		    $err = 'WARNINVALIDIMG';
		    return false;
		}
	    } else if(!in_array($format, $ignored)) {
		// if its not an image...and we're not ignoring it
		$allowed_mime = explode(',', $params->get('upload_mime'));
		$illegal_mime = explode(',', $params->get('upload_mime_illegal'));
		if(function_exists('finfo_open') && $params->get('check_mime',1)) {
		    // We have fileinfo
		    $finfo = finfo_open(FILEINFO_MIME);
		    $type = finfo_file($finfo, $file['tmp_name']);
		    if(strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime)) {
			$err = 'WARNINVALIDMIME';
			return false;
		    }
		    finfo_close($finfo);
		} else if(function_exists('mime_content_type') && $params->get('check_mime',1)) {
		    // we have mime magic
		    $type = mime_content_type($file['tmp_name']);
		    if(strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime)) {
			$err = 'WARNINVALIDMIME';
			return false;
		    }
		} else if(!$user->authorize( 'login', 'administrator' )) {
		    $err = 'WARNNOTADMIN';
		    return false;
		}
	    }
	}

	$xss_check =  JFile::read($file['tmp_name'],false,256);
	$html_tags = array('abbr','acronym','address','applet','area','audioscope','base','basefont','bdo','bgsound','big','blackface','blink','blockquote','body','bq','br','button','caption','center','cite','code','col','colgroup','comment','custom','dd','del','dfn','dir','div','dl','dt','em','embed','fieldset','fn','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','hr','html','iframe','ilayer','img','input','ins','isindex','keygen','kbd','label','layer','legend','li','limittext','link','listing','map','marquee','menu','meta','multicol','nobr','noembed','noframes','noscript','nosmartquotes','object','ol','optgroup','option','param','plaintext','pre','rt','ruby','s','samp','script','select','server','shadow','sidebar','small','spacer','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var','wbr','xml','xmp','!DOCTYPE', '!--');
	foreach($html_tags as $tag) {
	    // A tag is '<tagname ', so we need to add < and a space or '<tagname>'
	    if(stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>')) {
		$err = 'WARNIEXSS';
		return false;
	    }
	}
	return true;
    }


    function unzip( $folder, $path ) {
	$mainframe =& JFactory::getApplication();

	// Set FTP credentials, if given
	jimport('joomla.client.helper');
	JClientHelper::setCredentialsFromRequest('ftp');

	if ($path !== JFilterInput::clean($path, 'path')) {
	    JError::raiseWarning(100, JText::_('JM_UNABLE_TO_EXTRACT').htmlspecialchars($path, ENT_COMPAT,'UTF-8').' '.JText::_('WARNDIRNAME'));
	}

	$fullPath = JPath::clean($folder.DS.$path);

	if (is_file($fullPath)) {
	    $ext = JFile::getExt(strtolower($fullPath));
		$pathdir = $fullPath;
		if($ext != 'gz') {
		    $pathdir = str_replace( ".".$ext, "",$pathdir);
		}
	    else {
		$pathdir = str_replace( ".".$ext, "",$pathdir);
		$pathdir = str_replace( ".tar", "",$pathdir);
	    }

	    jimport('joomla.filesystem.*');
	    jimport('joomla.filesystem.archive');
	    JFolder::create($pathdir);
	    JFile::write($pathdir.DS."index.html", "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>");
	    if ( JArchive::extract($fullPath, $pathdir) ) {     // extract archive and remove it if successfull
		JFile::delete($folder.DS.$path);
	    }
	} else if (is_dir($fullPath)) {
	    JError::raiseWarning(100, JText::_('JM_UNABLE_TO_EXTRACT').$fullPath.' '.JText::_('WARNFILETYPE'));
	    JFile::delete($folder.DS.$path);
	}

	return true;
    }


    function remove()
    {
	$mainframe =& JFactory::getApplication();

	// Set FTP credentials, if given
	jimport('joomla.client.helper');
	JClientHelper::setCredentialsFromRequest('ftp');

	// Get some data from the request
        $path    = JPATH_ADMINISTRATOR.DS.'components/com_joomailermailchimpintegration/templates/';
	$folders = JRequest::getVar( 'template', array(), '', 'array' );

	// Initialize variables
	$ret = true;

        foreach ( $folders as $folder ) {
				
	    // delete zip file
	    @chmod($path.'/'.$folder.'.zip', 0777);
	    @unlink($path.'/'.$folder.'.zip');

	    // delete template folder with all contents
	    $fullPath = JPath::clean($path.DS.$folder);

	    $files = JFolder::files($fullPath, '.', true);
	    foreach ($files as $file) {
		JFile::delete($fullPath.'/'.$file);
	    }

	    JFolder::delete($fullPath);
	    $msg = JText::_('JM_TEMPLATES_DELETED');
        }

        $link = 'index.php?option=com_joomailermailchimpintegration&view=templates';
        $this->setRedirect($link, $msg);
    }
	
    function ajax_download()
    {
	jimport( 'joomla.filesystem.archive' );
	jimport( 'joomla.filesystem.archive.zip' );
	$msg = false;
	$path    = JPATH_ADMINISTRATOR.DS.'components/com_joomailermailchimpintegration/templates/';
	$folder = JRequest::getVar( 'template', '', '', 'string' );
	$fullPath = JPath::clean($path.DS.$folder);
	$files = JFolder::files($fullPath, '.', false, false);

	$filesData = array();
	for($i=0;$i<count($files);$i++){
	    $filesData[$i]['data'] = $fullPath.'/'.$files[$i];
	    $filesData[$i]['name'] = $files[$i];
	}

	// delete file if already exists
	@chmod($path.'/'.$folders[0].'.zip', 0777);
	@unlink($path.'/'.$folders[0].'.zip');

	$JArchiveZip =& JArchive::getAdapter('zip');

	if ($JArchiveZip->create($path.$folder.'.zip', $filesData)) {
	    $error = false;
	}

	$file = JURI::root().'administrator/components/com_joomailermailchimpintegration/templates/'.$folder.'.zip';

	$response['error'] = $error;
	$response['url'] = $file;
	echo json_encode( $response );
    }
	
    function cancel()
    {
	$msg = JText::_( 'JM_OPERATION_CANCELLED' );
	$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=templates', $msg );
    }
	
	
	
    function reloadPalettes()
    {
	$elements = JRequest::getVar( 'elements', '', 'request', 'string' );
	$elements = json_decode($elements);
	$hex = (isset($elements->hex)) ? $elements->hex : false;
	$keyword = (isset($elements->keyword)) ? $elements->keyword : false;
	$showName = (isset($elements->showName)) ? $elements->showName : true;
	$float    = (isset($elements->float)) ? $elements->float : false;

	$model	  =& $this->getModel('templates');
	$newPalettes = $model->getPalettes($hex, $keyword);

	$response = array();
	$response['html'] = '';
	$response['js'] = '';
	$i=0;
	foreach ($newPalettes as $color) {
	    foreach ($color as $c) {
		$response['js'] .= 'colorsets['.$i.'] = [];';
		$response['html'] .= '<div class="color_list" style="margin-bottom: 3px;">';

		$response['html'] .= '<div class="color_samples" style="display:inline-block;width:125px;">';
		$response['html'] .=  '<a href="javascript:applyPalette('.$i.');" id="apply'.$i.'" title="'.JText::_('select').'">';
		$x=0;
		foreach($c->colors as $cc) {
		    $response['html'] .= '<div style="background:#'.$cc.' none repeat scroll 0 0 !important; width: 25px; height: 10px; float: left;"></div>';
		    $response['js'] .= 'colorsets['.$i.']['.$x.'] = "#'.$cc.'";';
		    $x++;
		}
		$response['html'] .= '</a>';
		$response['html'] .= '</div>';

		$response['html'] .= '<a href="'.$c->url.'" target="_blank" class="ColorSetInfo" style="margin-left:10px;position:relative;top:-2px;text-decoration:underline;">'.JText::_('details').'</a>';
		if($showName){
		$response['html'] .= '<br />'.$c->title.'<br />';
		}
		$response['html'] .= '<div class="clr"></div></div>';
		if(!$float){
		$response['html'] .= '<div class="clr"></div>';
		}
	    }
	    $i++;
	}

	echo json_encode( $response );
    }
	
    function uploadLogo()
    {
	$template = JRequest::getVar( 'name', false, '', 'string' );
	// list of valid extensions, ex. array("jpeg", "xml", "bmp")
	$allowedExtensions = array('jpg','jpeg','png','gif','bmp');
	// max file size in bytes
	$sizeLimit = 10 * 1024 * 1024;

	$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
	$result = $uploader->handleUpload(JPATH_SITE.'/tmp/'.$template.'/', true);
	// to pass data through iframe you will need to encode all html tags
	echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
    }
	
	
    function save()
    {
	$template = JRequest::getVar( 'template', false, '', 'string' );
	$template = preg_replace('/[^a-z0-9]/', '', $template);
	$templateOld = JRequest::getVar( 'templateOld', false, '', 'string' );
	$columns = JRequest::getVar( 'columns', false, '', 'string' );
	if($template != $templateOld){
	    $tmpName = rand(100000, 999999);
	} else {
	    $tmpName = $template;
	}
	$src = JPATH_SITE.DS.'tmp'.DS.$templateOld.DS;
	$dest = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'templates'.DS.$tmpName;
	$templatesPath = JURI::root().'administrator/components/com_joomailermailchimpintegration/templates';

	$content = JRequest::getVar( 'templateContent', false, '', 'string', JREQUEST_ALLOWRAW );
	$content = str_replace( '%7E', '~', $content );
	$content = '<html>'.$content.'</html>';

	$metaData = "<meta http-Equiv=\"Cache-Control\" Content=\"no-cache\">\n<meta http-Equiv=\"Pragma\" Content=\"no-cache\">\n<meta http-Equiv=\"Expires\" Content=\"0\">";
	$content = str_ireplace($metaData, '', $content);
	$content = str_ireplace(' title="'.JText::_('click to edit').'"', '', $content);

	$content = str_ireplace( JURI::root().'tmp/'.$templateOld.'/', '', $content);
	$content = str_replace( array("&lt;","&gt;","%3C","%3E", "%7C"), array('<','>','<','>', '|'), $content);
	$content = preg_replace( '!<head>(.*)</head>!i', '', $content);

	if($template){
	    jimport('joomla.filesystem.*');

	    if(JFolder::exists($dest)){
		JFolder::delete($dest);
	    }
	    if(JFolder::copy( $src, $dest, '', true)){

		JFile::write( $dest.DS.'template.html', $content );

		if(JFile::exists($dest.DS.'l.txt')){
		    JFile::delete( $dest.DS.'l.txt' );
		}
		if(JFile::exists($dest.DS.'r.txt')){
		    JFile::delete( $dest.DS.'r.txt' );
		}

		if($columns){
		    JFile::write( $dest.DS.$columns.'.txt' );
		}

		if($template != $templateOld){
		    $oldName = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'templates'.DS.$tmpName;
		    $newName = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'templates'.DS.$template;
		    rename( $oldName, $newName );
		}
		JFolder::delete( $src );

		$msg = JText::_( 'JM_TEMPLATE_SAVED' );
	    } else {
		$msg = JText::_( 'JM_ERROR' );
	    }
	} else {
	    $msg = JText::_( 'JM_INVALID_TEMPLATE_NAME_SUPPLIED' );
	}
			
	$this->setRedirect( 'index.php?option=com_joomailermailchimpintegration&view=templates', $msg );
    }
	
}

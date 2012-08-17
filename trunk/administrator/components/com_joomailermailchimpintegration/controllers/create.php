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

$task =	JRequest::getVar('task', '', 'post', 'string', JREQUEST_ALLOWRAW );

if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_community'.DS.'admin.community.php')){
    require_once (JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
}
/**
 * joomailermailchimpintegration Controller
 *
 * @package    joomailermailchimpintegration
 * @subpackage Controllers
 */
class joomailermailchimpintegrationsControllerCreate extends joomailermailchimpintegrationsController
{
    function __construct(){
	parent::__construct();
	$this->registerTask( 'add' , 'send' );

    }// function

    function save(){

	$error  = false;
	$db     =& JFactory::getDBO();

	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$archiveDir = $params->get( $paramsPrefix.'archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive' );

	$model	     =& $this->getModel( 'create' );
	$core_exists = $model->getCore();
	$k2_exists   = $model->getK2();
	$vm_exists   = $model->getVMproducts();

	// define abs path for regex
	$abs_path = '$1="'.JURI::root().'$2$3';
	// get POST data
	$creation_date       = JRequest::getVar('cid',  0, 'post', 'string');
	$action		     = JRequest::getVar('action',    'save', 'post', 'string');
	$campaign_name       = JRequest::getVar('campaign_name',  0, 'post', 'string');
	$campaign_name_ent   = $this->cleanString($campaign_name);
	$subject             = JRequest::getVar('subject',        0, 'post', 'string');
	$from_name           = str_ireplace(array('"','@'),array(' ','(at)'), JRequest::getVar('from_name',      0, 'post', 'string'));
	$from_email          = JRequest::getVar('from_email',     0, 'post', 'string');
	$reply_email         = JRequest::getVar('reply_email',    0, 'post', 'string');
	$confirmation_email  = JRequest::getVar('confirmation_email', 0, 'post', 'string');
	$template_folder     = JRequest::getVar('template',       0, 'post', 'string');
	$twitter_name        = JRequest::getVar('twitter',        0, 'post', 'string');
	$facebook_url        = JRequest::getVar('facebook',       0, 'post', 'string');
	$myspace_name        = JRequest::getVar('myspace',        0, 'post', 'string');
	$facebookShareIt     = JRequest::getVar('facebookShare',  0, 'post', 'string');
	$facebookShareDesc   = JRequest::getVar('facebookShareDesc',  '', 'post', 'string');
	$fbImage             = JRequest::getVar('fbImage',  '', 'post', 'string');

	$gaEnabled           = JRequest::getVar('gaEnabled',   0, 'post', 'int');
	$gaExcluded          = JRequest::getVar('gaExcluded', '', 'post', 'string');
	$gaSource            = $this->cleanString( JRequest::getVar('gaSource',    'newsletter', 'post', 'string') );
	$gaMedium            = $this->cleanString( JRequest::getVar('gaMedium',         'email', 'post', 'string') );
	$gaName              = $this->cleanString( JRequest::getVar('gaName',$campaign_name_ent, 'post', 'string') );

	$time                = JRequest::getVar('time',        0, 'post', 'int');
	$time                = strtotime(date('Y-m-d H:i:s'));

	$intro_text          = JRequest::getVar('intro',   '', 'post', 'string', JREQUEST_ALLOWRAW);
	$sidebar             = JRequest::getVar('sidebar',   '', 'post', 'string', JREQUEST_ALLOWRAW);
	$articles            = JRequest::getVar('article', 0, 'post', 'array');
	$articles_k2         = JRequest::getVar('k2article', 0, 'post', 'array');

	$jomsocialProfiles = JRequest::getVar('jsProfiles', false, 'post');
	$jsFields = JRequest::getVar('jsProfileFields', false, 'post');
	$jsdisc = JRequest::getVar('jsdisc', false, 'post');

	$aec = JRequest::getVar('aec', false, 'post');
	$ambra = JRequest::getVar('ambra', false, 'post');

	$subject	= stripslashes($subject);
	$from_name 	= stripslashes($from_name);
	$twitter_name	= stripslashes($twitter_name);
	$facebook_url	= stripslashes($facebook_url);
	$myspace_name	= stripslashes($myspace_name);

	// display table of contents?
	$toc_checkbox = JRequest::getVar('tableofcontents', 0, 'post', 'int');
	$toc_type     = JRequest::getVar('tableofcontents_type', 0, 'post', 'int');
	// display popular articles?
	$popular_checkbox = JRequest::getVar('populararticles', 0, 'post', 'int');
	$popular_ex = JRequest::getVar('popExclude', false, 'post');
	$popular_in = JRequest::getVar('popInclude', false, 'post');
	// include K2 in populars?
	$populark2_checkbox = JRequest::getVar('populark2', 0, 'post', 'int');
	$populark2_ex = JRequest::getVar('popk2Exclude', false, 'post');
	$populark2_in = JRequest::getVar('popk2Include', false, 'post');
	// only K2 articles in populars?
	$populark2_only = JRequest::getVar('populark2_only', 0, 'post', 'int');

	// display VM products in sidebar?
	$vm_sb = JRequest::getVar('vm_sidebar', 0, 'post', 'int');
	if($vm_sb && $vm_exists) {
	    if(JRequest::getVar('vm_sb_products', 0, 'post', 'array')){
		$vm_sb_products = JRequest::getVar('vm_sb_products', 0, 'post', 'array');
	    } else {
		$vm_sb_products = false;
	    }
	    if($vm_sb_products){
		$vm_sb_productids = array();
		$vm_sb_productprices = array();
		for($i=0;$i<count($vm_sb_products);$i++){
		    $vm_sb_products[$i] = explode(';',$vm_sb_products[$i]);
		    $vm_sb_productids[$i] = $vm_sb_products[$i][0];
		    $vm_sb_productprices[$i] = $vm_sb_products[$i][1];
		    $vm_sb_productcats[$i] = $vm_sb_products[$i][2];
		}
	    }
	    $vm_sb_order = JRequest::getVar('vm_sidebar_order',       0, 'post', 'string');
	    $vm_sb_price = JRequest::getVar('vm_sidebar_price',       0, 'post', 'int');
	    $vm_sb_cf    = JRequest::getVar('vm_sidebar_curr_first',  0, 'post', 'int');
	    $vm_sb_img   = JRequest::getVar('vm_sidebar_img',         0, 'post', 'int');
	    $vm_sb_link  = JRequest::getVar('vm_sidebar_link',        0, 'post', 'int');
	    $vm_short_desc  = JRequest::getVar('vm_short_desc',       0, 'post', 'int');
	    $vm_desc        = JRequest::getVar('vm_desc',             0, 'post', 'int');
	}

	// get folder id or name
	$folder_id = JRequest::getVar('folder_id', 0, 'post', 'int');
	$folder_name = JRequest::getVar('folder_name', 0, 'post', 'string');
	if(!$folder_id && $folder_name) {
	    $folder_id = $model->createFolder($folder_name);
	}


	// convert relative to absolute href paths
	$intro_text   = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $intro_text);
	$sidebar = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $sidebar);
	// open the template file
	$filename = JPATH_ADMINISTRATOR.DS."components/com_joomailermailchimpintegration/templates/".$template_folder."/template.html";
	$template = JFile::read( $filename, false, filesize($filename) );

	$regex = '!<#repeater#[^>]*>(.*)<#/repeater#>!is';
	preg_match($regex, $template, $repeater);

	$imagepath = '$1="'.JURI::base().'components/com_joomailermailchimpintegration/templates/'.$template_folder.'/$2$3';
	$repeater[0] = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);

	// JomSocial Profiles
	$regex = '!<#jomsocialprofiles#[^>]*>(.*)<#/jomsocialprofiles#>!is';
	preg_match($regex, $template, $jsp);
	if(isset($jsp[0])){ $jsp = $jsp[0]; } else { $jsp = ''; }
	$regex = '!<#jomsocialprofilesrepeater#[^>]*>(.*)<#/jomsocialprofilesrepeater#>!is';
	preg_match($regex, $template, $jspr);
	if(isset($jspr[0])) { $jspr = $jspr[0]; } else { $jspr = ''; }
	$regex = '!<#jsfieldsrepeater#[^>]*>(.*)<#/jsfieldsrepeater#>!is';
	preg_match($regex, $template, $jsfr);
	if(isset($jsfr[0])) { $jsfr = $jsfr[0]; } else { $jsfr = ''; }
	// JomSocial Discussions
	$regex = '!<#jomsocialdiscussions#[^>]*>(.*)<#/jomsocialdiscussions#>!is';
	preg_match($regex, $template, $jsd);
	if(isset($jsd[0])){ $jsd = $jsd[0]; } else { $jsd = ''; }
	$regex = '!<#jomsocialdiscussionsrepeater#[^>]*>(.*)<#/jomsocialdiscussionsrepeater#>!is';
	preg_match($regex, $template, $jsdr);
	if(isset($jsdr[0])) { $jsdr = $jsdr[0]; } else { $jsdr = ''; }

	// AEC plans
	$regex = '!<#aec#[^>]*>(.*)<#/aec#>!is';
	preg_match($regex, $template, $aecph);
	if(isset($aecph[0])){ $aecph = $aecph[0]; } else { $aecph = ''; }
	$regex = '!<#aecrepeater#[^>]*>(.*)<#/aecrepeater#>!is';
	preg_match($regex, $template, $aecr);
	if(isset($aecr[0])) { $aecr = $aecr[0]; } else { $aecr = ''; }

	// Ambra subscriptions
	$regex = '!<#ambra#[^>]*>(.*)<#/ambra#>!is';
	preg_match($regex, $template, $ambraph);
	if(isset($ambraph[0])){ $ambraph = $ambraph[0]; }
	else { $ambraph = ''; }
	$regex = '!<#ambrarepeater#[^>]*>(.*)<#/ambrarepeater#>!is';
	preg_match($regex, $template, $ambrar);
	if(isset($ambrar[0])) { $ambrar = $ambrar[0]; } else { $ambrar = ''; }

	// table of contents
	$regex = '!<#tableofcontents#[^>]*>(.*)<#/tableofcontents#>!is';
	preg_match($regex, $template, $tableofcontents);
	$tableofcontents = $tableofcontents[0];
	$regex = '!<#title_repeater#[^>]*>(.*)<#/title_repeater#>!is';
	preg_match($regex, $template, $title_repeater);
	$title_repeater = $title_repeater[0];

	// popular articles
	$regex = '!<#populararticles#[^>]*>(.*)<#/populararticles#>!is';
	preg_match($regex, $template, $populararticles);
	$populararticles = $populararticles[0];
	$regex = '!<#popular_repeater#[^>]*>(.*)<#/popular_repeater#>!is';
	preg_match($regex, $template, $popular_repeater);
	$popular_repeater = $popular_repeater[0];

	// VM products
	$regex = '!<#vm_products#[^>]*>(.*)<#/vm_products#>!is';
	preg_match($regex, $template, $vm_products);
	$vm_products = $vm_products[0];
	$regex = '!<#vm_repeater#[^>]*>(.*)<#/vm_repeater#>!is';
	preg_match($regex, $template, $vm_repeater);
	$vm_repeater = $vm_repeater[0];

	// Twitter
	$regex = '!<#twitter#[^>]*>(.*)<#/twitter#>!is';
	preg_match($regex, $template, $twitter);
	if(isset($twitter[0])) $twitter = $twitter[0];
	else $twitter = '';
	// Facebook
	$regex = '!<#facebook#[^>]*>(.*)<#/facebook#>!is';
	preg_match($regex, $template, $facebook);
	if(isset($facebook[0])) $facebook = $facebook[0];
	else $facebook = '';
	// MySpace
	$regex = '!<#myspace#[^>]*>(.*)<#/myspace#>!is';
	preg_match($regex, $template, $myspace);
	if(isset($myspace[0])) $myspace = $myspace[0];
	else $myspace = '';

	// Facebook share
	$regex = '!<#facebook_share#[^>]*>(.*)<#/facebook_share#>!is';
	preg_match($regex, $template, $facebookShare);
	if(isset($facebookShare[0])) $fbs = $facebookShare[0];
	else { $fbs = ''; }

	$content = '';
	$art_titles = array();
	// Joomla core articles
	if ($articles) {
	    foreach ($articles as $id ) {
		$full     = JRequest::getVar('article_full_'.$id, 0, 'post', 'int');
		$readmore = JRequest::getVar('readmore_'.$id, 0, 'post', 'int');

		$query = 'SELECT * FROM #__content WHERE id= '.$id;
		$db->setQuery($query);
		$article = $db->loadObjectList();

		foreach ( $article as $art ) {
		    $html_title = $html_content = '';

		    if($toc_type){
			$art_titles[] = '<a href="'.JURI::root().'index.php?option=com_content&view=article&id='.$id.'">'.$art->title.'</a>';
		    } else {
			$art_titles[] = '<a href="#'.$art->title.$id.'">'.$art->title.'</a>';
		    }

		    if ($readmore) {
			$html_title = '<a href="'.JURI::root().'index.php?option=com_content&view=article&id='.$id.'">'.$art->title.'</a><a name="'.$art->title.$id.'"></a>';
		    } else {
			$html_title = $art->title.'<a name="'.$art->title.$id.'"></a>';
		    }
		    $html_content  = $art->introtext;
		    if ( $full == 1 ) {
			$html_content .= ' '.$art->fulltext;
		    }
		    // Read more link
		    if ($readmore) {
			$html_content .= '<p><a href="'.JURI::root().'index.php?option=com_content&view=article&id='.$id.'">'.JText::_( 'JM_READ_MORE' ).'</a></p>';
		    }

		    $template = str_ireplace( '<#title#>',   $html_title,   $repeater[0] );
		    $template = str_ireplace( '<#content#>', $html_content, $template    );
		    $content .= $template;
		}
	    }
	}
	// K2 articles
	if ($articles_k2 && $k2_exists) {
	    foreach ($articles_k2 as $id ) {
		$full     = JRequest::getVar('k2article_full_'.$id, 0, 'post', 'int');
		$readmore = JRequest::getVar('k2readmore_'.$id, 0, 'post', 'int');

		$query = 'SELECT * FROM #__k2_items WHERE id= '.$id;
		$db->setQuery($query);
		$article = $db->loadObjectList();

		foreach ( $article as $art ) {
		    $html_title = $html_content = '';

		    if($toc_type){
			$art_titles[] = '<a href="'.JURI::root().'index.php?option=com_k2&view=item&id='.$id.'">'.$art->title.'</a>';
		    } else {
			$art_titles[] = '<a href="#'.$art->title.$id.'">'.$art->title.'</a>';
		    }

		    if ($readmore) {
			$html_title = '<a href="'.JURI::root().'index.php?option=com_k2&view=item&id='.$id.'">'.$art->title.'</a><a name="'.$art->title.$id.'"></a>';
		    } else {
			$html_title = $art->title.'<a name="'.$art->title.$id.'"></a>';
		    }
		    $html_content  = $art->introtext;
		    if ( $full == 1 ) {
			$html_content .= ' '.$art->fulltext;
		    }
		    // Read more link

		    if ($readmore) {
			$html_content .= '<p><a href="'.JURI::root().'index.php?option=com_k2&view=item&id='.$id.'">'.JText::_( 'JM_READ_MORE' ).'</a></p>';
		    }

		    $template = str_ireplace( '<#title#>',   $html_title,   $repeater[0] );
		    $template = str_ireplace( '<#content#>', $html_content, $template    );
		    $content .= $template;
		}
	    }

	}

	// JomSocial Profiles
	$jsProfiles = '';
	if($jomsocialProfiles){
	    foreach($jomsocialProfiles as $jsProfile){
		$jsUser = CFactory::getUser($jsProfile);

		$profileLink = JURI::root().CRoute::_('index.php?option=com_community&view=profile&userid='.$jsProfile );
		$thumb = '<a href="'.$profileLink.'"><img src="'.$jsUser->getThumbAvatar().'" alt="'.$jsUser->name.'" title="'.$jsUser->name.'" border="0" /></a>';
		$profiles = str_ireplace('<#jsAvatar#>', $thumb, $jspr);
		$profiles = str_ireplace('<#jsName#>', '<a href="'.$profileLink.'">'.$jsUser->name.'</a>', $profiles);

		$fieldValues = $model->getFieldValues($jsProfile, $jsFields);
		$fields = '';
		foreach($fieldValues as $f){
		    if($f->value){
			if($f->type == 'date'){ $f->value = substr($f->value, 0, -9); }
			$fieldsTmp  = str_ireplace('<#jsFieldTitle#>', $f->name, $jsfr);
			$fieldsTmp  = str_ireplace('<#jsFieldValue#>', $f->value, $fieldsTmp);
			$fields .= $fieldsTmp;
		    }
		}
		$profiles = preg_replace( '!<#jsfieldsrepeater#[^>]*>(.*)<#/jsfieldsrepeater#>!is', $fields, $profiles );
		$profiles = str_ireplace( array('<#jsfieldsrepeater#>','<#/jsfieldsrepeater#>'), '', $profiles);

		$jsProfiles .= $profiles;
	    }
	    $jsProfiles = str_ireplace( array('<#jomsocialprofilesrepeater#>','<#/jomsocialprofilesrepeater#>'), '', $jsProfiles);
	}
	// JomSocial Discussions
	$jsDiscussions = '';
	if($jsdisc){
	    $lang =& JFactory::getLanguage();
	    $lang->load('com_community', JPATH_SITE);
	    $langString = JText::_('CC ACTIVITIES NEW GROUP DISCUSSION');
	    $discussions = $model->getJomsocialDiscussions($jsdisc);
	    foreach($discussions as $d){
		if($d->creator){
		    $jsUser = CFactory::getUser($d->creator);
		    $profileLink = JURI::root().CRoute::_('index.php?option=com_community&view=profile&userid='.$d->creator );
		    $discLink = JURI::root().CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid='.$d->groupid.'&topicid='.$d->id );
		    $groupLink = JURI::root().CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$d->groupid );
		    $thumb = '<a href="'.$profileLink.'"><img src="'.$jsUser->getThumbAvatar().'" alt="'.$jsUser->name.'" title="'.$jsUser->name.'" border="0" /></a>';

		    $discTmp = str_ireplace('{actor}', $jsUser->name, $langString);
		    $discTmp = str_ireplace('{topic_url}', $discLink, $discTmp);
		    $discTmp = str_ireplace('{topic}', $d->title, $discTmp);
		    $discTmp = str_ireplace('%1$s', $groupLink, $discTmp);
		    $discTmp = str_ireplace('%2$s', $d->name, $discTmp);

		    $discTmp .= ':<br />'.$d->message;

		    $discTmp = '<table valign="top">
				    <tr>
					<td valign="top">'.$thumb.'</td>
					<td valign="top" style="padding: 0 0 0 10px;">'.$discTmp.'</td>
				    </tr>
				</table>';

		    $discTmp = str_ireplace('<#jsDiscussionContent#>', $discTmp, $jsdr);
		    $jsDiscussions .= $discTmp;
		}
	    }
	    $jsDiscussions = str_ireplace( array('<#jomsocialdiscussionsrepeater#>','<#/jomsocialdiscussionsrepeater#>'), '', $jsDiscussions);
	}
	// AEC plans
	$aecContent = '';
	if($aec){
	    $aecConfig = $model->getAECconfig();
	    $aecPlans = $model->getAECplans($aec);
	    foreach($aecPlans as $a){
		$aecParams = unserialize( base64_decode( $a->params ) );
		$link = JURI::root().JRoute::_('index.php?option=com_acctexp&view=subscribe');
		$aecTmp =  '<tr>
				<td valign="top"><a href="'.$link.'">'.$a->name.'</a></td>
				<td valign="top" style="padding: 0 0 0 10px;">'.$a->desc.'</td>
				<td valign="top" style="padding: 0 0 0 10px;">'.$aecParams['full_amount'].' '.$aecConfig['standard_currency'].'</td>
			    </tr>';
		$aecTmp = str_ireplace('<#aeccontent#>', $aecTmp, $aecr);
		$aecContent .= $aecTmp;
	    }
	    $aecContent = str_ireplace( array('<#aecrepeater#>','<#/aecrepeater#>'), '', $aecContent);
	}

	// Ambra subscriptions
	$ambraContent = '';
	if($ambra){
	    require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ambrasubs'.DS.'helpers'.DS.'_base.php');
	    require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ambrasubs'.DS.'helpers'.DS.'config.php');
	    $ambraConfig = AmbrasubsConfig::getInstance();
	    $ambraPre = str_ireplace('$','\$',$ambraConfig->get('currency_preval', '$'));
	    $ambraPost = $ambraConfig->get('currency_postval', '');
	    $ambraPlans = $model->getAmbra($ambra);

	    foreach($ambraPlans as $a){
		$link = JURI::root().JRoute::_('index.php?option=com_ambrasubs&controller=subscriptions&task=new&id='.$a->id);
		$img = '<img src="'.JURI::root().$a->img.'" alt="'.$a->title.'" title="'.$a->title.'" border="0" />';
		$ambraTmp = '<tr>
				<td valign="top"><a href="'.$link.'">'.$img.'</a></td>
				<td valign="top"><a href="'.$link.'">'.$a->title.'</a><br />'.$a->description.'</td>
				<td valign="top" style="padding: 0 0 0 10px;" nowrap="nowrap">'.$ambraPre.''.$a->value.''.$ambraPost.'</td>
			    </tr>';
		$ambraTmp = str_ireplace('<#ambracontent#>', $ambraTmp, $ambrar);
		$ambraContent .= $ambraTmp;
	    }
	    $ambraContent = str_ireplace( array('<#ambrarepeater#>','<#/ambrarepeater#>'), '', $ambraContent);
	}

	// remove tiny mce stuff like mce_src="..."
	$content = preg_replace('(mce_style=".*?")', '', $content);
	$content = preg_replace('(mce_src=".*?")',   '', $content);
	$content = preg_replace('(mce_href=".*?")',  '', $content);
	$content = preg_replace('(mce_bogus=".*?")', '', $content);
	// convert relative to absolute paths
	$abs_path     = '$1="'.JURI::root().'$2$3';
	$content = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#', $abs_path, $content);

	// create table of contents
	$toc = '';
	foreach ($art_titles as $art_title) {
	    $toc .= str_ireplace( '<#article_title#>', $art_title, $title_repeater );
	}
	$tableofcontents = preg_replace( '!<#title_repeater#[^>]*>(.*)<#/title_repeater#>!is', $toc, $tableofcontents );
	$to_replace = array('<#tableofcontents#>', '<#/tableofcontents#>', '<#title_repeater#>', '<#/title_repeater#>');
	$tableofcontents = str_ireplace( $to_replace, '', $tableofcontents);

	$where = '';
	$wEx = array();
	$wIn = array();
	$wCore = array();
	if($popular_checkbox){
	    if(isset($popular_ex[0])){
		foreach($popular_ex as $p){
		    $wEx[] = ' c.catid != '.$p;
		}
		$wCore[] = ( count( $wEx ) ? ' AND (' . implode( ' AND ', $wEx ) . ' )' : '' );
	    }
	    if(isset($popular_in[0])){
		foreach($popular_in as $p){
		    $wIn[] = ' c.catid = '.$p;
		}
		$wCore[] = ( count( $wIn ) ? ' AND (' . implode( ' OR ', $wIn ) . ' )' : '' );
	    }
	    $where = implode('', $wCore);
	}

	$whereK2 = '';
	$wEx = array();
	$wIn = array();
	$wK2 = array();
	if($populark2_checkbox){
	    if(isset($populark2_ex[0])){
		foreach($populark2_ex as $p){
		    $wEx[] = ' k.catid != '.$p;
		}
		$wK2[] = ( count( $wEx ) ? ' AND (' . implode( ' AND ', $wEx ) . ' )' : '' );
	    }
	    if(isset($populark2_in[0])){
		foreach($populark2_in as $p){
		    $wIn[] = ' k.catid = '.$p;
		}
		$wK2[] = ( count( $wIn ) ? ' AND (' . implode( ' OR ', $wIn ) . ' )' : '' );
	    }
	}
	$whereK2 = implode('', $wK2);

	// create list of popular articles
	if ( $popular_checkbox && !$populark2_checkbox){
	    $query = 'SELECT c.id, c.title, c.hits FROM #__content as c
		      WHERE ( c.state = 1 OR c.state = -2 )
		      AND c.hits != 0
		      '.$where.'
		      ORDER BY c.hits DESC
		      LIMIT 0 , 5';
	} else if ( $popular_checkbox && $populark2_checkbox && !$populark2_only ) {
	    $query = 'SELECT c.id, c.title, c.hits
		      FROM #__content as c
		      WHERE ( c.state = 1 OR c.state = -2 )
		      AND c.hits != 0
		      '.$where.'
		      UNION ALL SELECT k.id, k.title, k.hits
		      FROM #__k2_items as k
		      WHERE k.published = 1
		      AND k.hits != 0
		      '.$whereK2.'
		      ORDER BY hits DESC
		      LIMIT 0 , 5 ';
	} else if ( $popular_checkbox && $populark2_checkbox && $populark2_only )  {
	    $query = 'SELECT k.id, k.title, k.hits
		      FROM #__k2_items as k
		      WHERE k.published = 1
		      AND k.hits != 0
		      '.$whereK2.'
		      ORDER BY k.hits DESC
		      LIMIT 0 , 5 ';
	}

	$db->setQuery($query);
	$popular = $db->loadObjectList();

	$popularlist = '';
	$i=0;
	foreach ($popular as $pop){
	$i++;
	$core = false;
	if ($i>5) { break; }

	$query = 'SELECT title FROM #__content WHERE title = "'.$pop->title.'"';
	$db->setQuery($query);
	$core = $db->loadResult();
	if ($core) {
	    $url = '<a href="'.JURI::root().'index.php?option=com_content&view=article&id='.$pop->id.'">'.$pop->title.'</a>';
	} else {
	    $url = '<a href="'.JURI::root().'index.php?option=com_k2&view=item&id='.$pop->id.'">'.$pop->title.'</a>';
	}
	$popularlist .= str_ireplace( '<#popular_title#>', $url, $popular_repeater );
	}
	$popularlist = preg_replace( '!<#popular_repeater#[^>]*>(.*)<#/popular_repeater#>!is', $popularlist, $populararticles );
	$to_replace  = array('<#populararticles#>', '<#/populararticles#>', '<#popular_repeater#>', '<#/popular_repeater#>');
	$popularlist = str_ireplace( $to_replace, '', $popularlist);

	// insert vm products into sidebar
	 $vm = '';
	if ( $vm_sb && $vm_exists ) {
	    require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart.cfg.php');
	    $where = '';
	    for($i=0;$i<count($vm_sb_productids);$i++){
		$where .= ( !$where ) ? ' AND ' : ' OR ';
		$where .= ' ( a.product_id = '.$vm_sb_productids[$i].' '.
			  'AND b.product_price = '.$vm_sb_productprices[$i].' '.
			  'AND c.category_id = '.$vm_sb_productcats[$i].' ) ';
	    }

	    if($vm_sb_order=='price_desc'){
		$order = ' ORDER BY b.product_price DESC ';
	    } else if($vm_sb_order=='price_asc'){
		$order = ' ORDER BY b.product_price ASC ';
	    } else if($vm_sb_order=='name_desc'){
		$order = ' ORDER BY a.product_name DESC ';
	    } else if($vm_sb_order=='name_asc'){
		$order = ' ORDER BY a.product_name ASC ';
	    } else if($vm_sb_order=='cat_desc'){
		$order = ' ORDER BY d.category_name DESC ';
	    } else if($vm_sb_order=='cat_asc'){
		$order = ' ORDER BY d.category_name ASC ';
	    } else if($vm_sb_order=='random'){
		$order = ' ORDER BY RAND() ';
	    }

	    $query = 'SELECT a.product_id,a.product_name,a.product_thumb_image, a.product_s_desc, a.product_desc,
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
		      '.$where.'
		      AND a.product_thumb_image != ""
		      '.$order;

	    $db->setQuery($query);
	    $products = $db->loadObjectList();
	    foreach ($products as $prod){
		$product_content = '';

		if ($vm_sb_link){
		    $product_content .= '<a href="'.JURI::root().'index.php?page=shop.product_details&flypage='.FLYPAGE.'&product_id='.$prod->product_id.'&category_id='.$prod->category_id.'&option=com_virtuemart">';
		}
		$product_content .= $prod->product_name.'<br />';
		if ( $vm_sb_price ) {
		    if ($vm_sb_cf){
			$product_content .= $prod->product_currency.' '.number_format($prod->product_price,2).'<br />';
		    } else {
			$product_content .= number_format($prod->product_price,2).' '.$prod->product_currency.'<br />';
		    }
		}
		if ($vm_sb_img){
		    $product_content .= '<img src="'.JURI::root().'components/com_virtuemart/shop_image/product/'.$prod->product_thumb_image.'" border="0" />';
		}
		if ($vm_sb_link){
		    $product_content .= '</a>';
		}

		if ($vm_short_desc){
		    $product_content .= '<p>'.$prod->product_s_desc.'</p>';
		}
		if ($vm_desc){
		    $product_content .= '<p>'.$prod->product_desc.'</p>';
		}

		$vm .= str_ireplace( '<#vm_content#>', $product_content, $vm_repeater );
	    }
	}
	$vm = preg_replace( '!<#vm_repeater#[^>]*>(.*)<#/vm_repeater#>!is', $vm, $vm_products);
	$to_replace  = array('<#vm_products#>', '<#/vm_products#>', '<#vm_repeater#>', '<#/vm_repeater#>');
	$vm = str_ireplace( $to_replace, '', $vm);

	$imagepath = '$1="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/templates/'.$template_folder.'/$2$3';
	// twitter
	$tw = str_ireplace( '<#twitter-name#>', $twitter_name, $twitter);
	$to_replace  = array('<#twitter#>', '<#/twitter#>');
	$tw = str_ireplace( $to_replace, '', $tw);
	// convert relative to absolute paths
	$tw = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $tw);
	// facebook
	$fb = str_ireplace( '<#facebook-url#>', $facebook_url, $facebook);
	$to_replace  = array('<#facebook#>', '<#/facebook#>');
	$fb = str_ireplace( $to_replace, '', $fb);
	// convert relative to absolute paths
	$fb = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $fb);
	// myspace
	$ms = str_ireplace( '<#myspace-name#>', $myspace_name, $myspace);
	$to_replace  = array('<#myspace#>', '<#/myspace#>');
	$ms = str_ireplace( $to_replace, '', $ms);
	// convert relative to absolute paths
	$ms = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $ms);
	// facebook share
	$to_replace  = array('<#facebook_share#>', '<#/facebook_share#>');
	$fbs = str_ireplace( $to_replace, '', $fbs);
	// convert relative to absolute paths
	$fbs = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $fbs);

	$filename = JPATH_ADMINISTRATOR.DS."components/com_joomailermailchimpintegration/templates/".$template_folder."/template.html";
	$template = JFile::read( $filename, false, filesize($filename) );

	// create absolute image paths
	$imagepath = ' src="'.JURI::base().'components/com_joomailermailchimpintegration/templates/'.$template_folder.'/'.'$2$3';
	$template  = preg_replace('#(src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $template);
	$imagepath = " url('".JURI::base()."components/com_joomailermailchimpintegration/templates/".$template_folder."/";
	$template  = preg_replace('#(\s*)url?\([\'"]?[../]*[\'"]?#i', $imagepath, $template);

	// modify paths of intro-text
	// remove tiny mce stuff like mce_src="..."
	$intro_text = preg_replace('(mce_style=".*?")', '', $intro_text);
	$intro_text = preg_replace('(mce_src=".*?")',   '', $intro_text);
	$intro_text = preg_replace('(mce_href=".*?")',  '', $intro_text);
	$intro_text = preg_replace('(mce_bogus=".*?")', '', $intro_text);
	$sidebar = preg_replace('(mce_style=".*?")', '', $sidebar);
	$sidebar = preg_replace('(mce_src=".*?")',   '', $sidebar);
	$sidebar = preg_replace('(mce_href=".*?")',  '', $sidebar);
	$sidebar = preg_replace('(mce_bogus=".*?")', '', $sidebar);
	// convert relative to absolute paths
	$abs_path   = '$1="'.JURI::root().'$2$3';
	$intro_text = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $intro_text);
	$sidebar = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $sidebar);
	// end paths intro-text

	// insert page title and intro-text
	$template = str_ireplace( '<#subject#>', $subject, $template);
	$template = str_ireplace( '<#intro_content#>', $intro_text, $template);
	$template = str_ireplace( '<#sidebar#>', $sidebar, $template);
	// insert articles
	$content  = str_ireplace('$', '\$', $content);
	$template = preg_replace( '!<#repeater#[^>]*>(.*)<#/repeater#>!s', $content, $template );
	// remove placeholders
	$template = str_ireplace( '<#repeater#>',  '', $template);
	$template = str_ireplace( '<#/repeater#>', '', $template);

	// insert JomSocial Profiles
	if($jsProfiles){
	    $jsProfiles = str_ireplace('$', '\$', $jsProfiles);
	    $template = preg_replace( '!<#jomsocialprofilesrepeater#[^>]*>(.*)<#/jomsocialprofilesrepeater#>!is', $jsProfiles, $template );
	    $template = str_ireplace( array('<#jomsocialprofiles#>','<#/jomsocialprofiles#>'), '', $template);
	} else {
	    $template = preg_replace( '!<#jomsocialprofiles#[^>]*>(.*)<#/jomsocialprofiles#>!is', '', $template );
	}
	// insert JomSocial Discussions
	if($jsDiscussions){
	    $jsDiscussions = str_ireplace('$', '\$', $jsDiscussions);
	    $template = preg_replace( '!<#jomsocialdiscussionsrepeater#[^>]*>(.*)<#/jomsocialdiscussionsrepeater#>!is', $jsDiscussions, $template );
	    $template = str_ireplace( array('<#jomsocialdiscussions#>','<#/jomsocialdiscussions#>'), '', $template);
	} else {
	    $template = preg_replace( '!<#jomsocialdiscussions#[^>]*>(.*)<#/jomsocialdiscussions#>!is', '', $template );
	}

	// insert AEC plans
	if($aecContent){
	    $aecContent = str_ireplace('$', '\$', $aecContent);
	    $template = preg_replace( '!<#aecrepeater#[^>]*>(.*)<#/aecrepeater#>!is', $aecContent, $template );
	    $template = str_ireplace( array('<#aec#>','<#/aec#>'), '', $template);
	} else {
	    $template = preg_replace( '!<#aec#[^>]*>(.*)<#/aec#>!is', '', $template );
	}

	// insert Ambra subscriptions
	if($ambraContent){
	    $ambraContent = str_ireplace('$', '\$', $ambraContent);
	    $template = preg_replace( '!<#ambrarepeater#[^>]*>(.*)<#/ambrarepeater#>!is', $ambraContent, $template );
	    $template = str_ireplace( array('<#ambra#>','<#/ambra#>'), '', $template);
	} else {
	    $template = preg_replace( '!<#ambra#[^>]*>(.*)<#/ambra#>!is', '', $template );
	}

	// insert table of contents
	if ($toc_checkbox && ( $articles || $articles_k2) ){
	    $tableofcontents = str_ireplace('$', '\$', $tableofcontents);
	    $template = preg_replace( '!<#tableofcontents#[^>]*>(.*?)<#/tableofcontents#>!is', $tableofcontents, $template );
	} else {
	    $template = preg_replace( '!<#tableofcontents#[^>]*>(.*?)<#/tableofcontents#>!is', '', $template );
	}

	//insert popular articles
	if ($popular_checkbox){
	    $popularlist = str_ireplace('$', '\$', $popularlist);
	    $template = preg_replace( '!<#populararticles#[^>]*>(.*?)<#/populararticles#>!is', $popularlist, $template );
	} else {
	    $template = preg_replace( '!<#populararticles#[^>]*>(.*?)<#/populararticles#>!is', '', $template );
	}

	//insert vm products
	if ($vm_sb){
	    $vm = str_ireplace('$', '\$', $vm);
	    $template = preg_replace( '!<#vm_products#[^>]*>(.*?)<#/vm_products#>!is', $vm, $template );
	} else {
	    $template = preg_replace( '!<#vm_products#[^>]*>(.*?)<#/vm_products#>!is', '', $template );
	}

	//insert twitter link
	$tw = ($twitter_name) ? $tw : '';
	$template = preg_replace( '!<#twitter#[^>]*>(.*?)<#/twitter#>!is', $tw, $template );

	//insert facebook link
	$fb = ($facebook_url) ? $fb : '';
	$template = preg_replace( '!<#facebook#[^>]*>(.*?)<#/facebook#>!is', $fb, $template );

	//insert myspace link
	$ms = ($myspace_name) ? $ms : '';
	$template = preg_replace( '!<#myspace#[^>]*>(.*?)<#/myspace#>!is', $ms, $template );

	//insert facebook share link
	if ($facebookShareIt){
	    $template = preg_replace( '!<#facebook_share#[^>]*>(.*?)<#/facebook_share#>!is', $fbs, $template );
	    $metaData = "<meta name=\"title\" content=\"".$campaign_name."\" />\n".
			"<meta name=\"description\" content=\"".$facebookShareDesc."\" />\n".
			"<link rel=\"image_src\" href=\"".$fbImage."\" />\n";
	    $metaData = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $metaData);
	    $template = str_ireplace( '</head>', $metaData.'</head>', $template );
	} else {
	    $template = preg_replace( '!<#facebook_share#[^>]*>(.*?)<#/facebook_share#>!is', '', $template );
	}

	// create google analytics tracking links
	if($gaEnabled){
	    $ga = 'utm_source='.$gaSource.'&utm_medium='.$gaMedium.'&utm_campaign='.$gaName.'"';
	    $gaEx = explode("\n", $gaExcluded);
	    for($i=0;$i<count($gaEx);$i++){
		$gaEx[$i] = trim($gaEx[$i]);
	    }
	    $gaEx[] = '*|UNSUB|*';

	    $regex = '#<a(.*?)>(.*?)</a>#i';
	    preg_match_all($regex, $template, $templateLinks, PREG_PATTERN_ORDER);

		 if(isset($templateLinks[0])) {
			foreach($templateLinks[0] as $link){
				
				preg_match_all( '#((href)="(?!\.css)[^"]+)"#i' , $link, $oldLink, PREG_PATTERN_ORDER);
				if(isset($oldLink[0][0])){
					$glue = ( strstr($oldLink[0][0], '?') )? $glue = '&' : $glue = '?';
					$oldHref = substr($oldLink[0][0], 0, -1);
					$addGA = true;
					
					foreach($gaEx as $ex){
						if (stristr($oldHref,$ex)) { $addGA = false; }
					}
					if( $addGA ){
						$newLink  = preg_replace('#((href)="(?!\.css)[^"]+)"#i', $oldHref.$glue.$ga.'"', $link);
						$template = str_ireplace( $oldLink[0][0], $oldHref.$glue.$ga.'"', $template);
					}
				}
			}
		}
	}


	// prevent preview from being cached
	$metaData = "\n<meta http-Equiv=\"Cache-Control\" Content=\"no-cache\">\n".
		    "<meta http-Equiv=\"Pragma\" Content=\"no-cache\">\n".
		    "<meta http-Equiv=\"Expires\" Content=\"0\">\n";
	if( !stristr($template, "<head>") ){
	    $template = str_ireplace( '<html>', '<html><head>'.$metaData.'</head>', $template );
	} else {
	    $template = str_ireplace( '</head>', $metaData.'</head>', $template );
	}

	// create html version
	$filename = JPATH_SITE . $archiveDir .'/'. $campaign_name_ent.".html";
	$handle = @JFile::write( $filename, $template );
	if(!$handle){
	    $error = true;
	} else {
	    $html_file = JURI::base() . (substr($archiveDir,1)) . "/" . $campaign_name_ent.".html";
	}

	 // create txt version
	if (!$error){
	    $txt_content = $template;
	    $txt_content = preg_replace( "!<head[^>]*>(.*?)</head>!is", '', $txt_content );
	    $txt_content = preg_replace( "!<style[^>]*>(.*?)</style>!is", '', $txt_content );
	    $txt_content = preg_replace( "!<forwardtoafriend[^>]*>(.*?)</forwardtoafriend>!is", 'Forward to a friend: *|FORWARD|*', $txt_content );
	    $txt_content = preg_replace( "!<preferences[^>]*>(.*?)</preferences>!is", 'Preference center: *|UPDATE_PROFILE|*', $txt_content );
	    $txt_content = preg_replace( "!<unsubscribe[^>]*>(.*?)</unsubscribe>!is", '*|UNSUB|*', $txt_content );
	    $txt_content = preg_replace( "!<webversion[^>]*>(.*?)</webversion>!is", '*|ARCHIVE|*', $txt_content );
	    $txt_content = strip_tags($txt_content);
	    $txt_content = htmlspecialchars($txt_content);
	    $txt_content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n\n", $txt_content);
	    $txt_content = $campaign_name_ent."\n".$txt_content;

	    $filename = JPATH_SITE . $archiveDir .'/'. $campaign_name_ent.".txt";
	    $handle = @JFile::write( $filename, $txt_content );
	    $txt_file = JURI::base() . (substr($archiveDir,1)) . "/" . $campaign_name_ent.".txt";
	}

	// set the redirection link and message
	if ( $error ) {
	    $msg = JText::sprintf( 'JM_CAMPAIGN_CREATION_FAILED', $archiveDir );
	    $msgType = 'error';
	    $link = 'index.php?option=com_joomailermailchimpintegration&view=create';

	    if( $creation_date )    { JRequest::setVar('cid',   $creation_date); }
	    if ($campaign_name)	    { JRequest::setVar('cn',    $campaign_name); }
	    if ($subject)           { JRequest::setVar('sj',    $subject); }
	    if ($from_name)         { JRequest::setVar('fn',    $from_name); }
	    if ($from_email)        { JRequest::setVar('fe',    $from_email); }
	    if ($reply_email)       { JRequest::setVar('re',    $reply_email); }
	    if ($confirmation_email){ JRequest::setVar('ce',    $confirmation_email); }
	    if ($template_folder)   { JRequest::setVar('tpl',   $template_folder); }
	    if ($articles)          { JRequest::setVar('arts',  implode(';',$articles)); }
	    if ($articles_k2)       { JRequest::setVar('artsk2',implode(';',$articles_k2)); }
	    if ($toc_checkbox)      { JRequest::setVar('toc',   $toc_checkbox); }
	    if ($toc_type)          { JRequest::setVar('toct',  $toc_type); }
	    if ($popular_checkbox)  { JRequest::setVar('pop',   $popular_checkbox); }
	    if ($popular_in)        { JRequest::setVar('pin',   implode(';',$popular_in)); }
	    if ($popular_ex)        { JRequest::setVar('pex',   implode(';',$popular_ex)); }
	    if ($populark2_checkbox){ JRequest::setVar('pk2',   $populark2_checkbox); }
	    if ($populark2_in)      { JRequest::setVar('pk2in', implode(';',$populark2_in)); }
	    if ($populark2_ex)      { JRequest::setVar('pk2ex', implode(';',$populark2_ex)); }
	    if ($populark2_only)    { JRequest::setVar('pk2o',  $populark2_only); }
	    if( isset($jomsocialProfiles) ){
	    if ( is_array($jomsocialProfiles)) { JRequest::setVar('jsp',   implode(';',$jomsocialProfiles)); }
	    if ( is_array($jsFields)) { JRequest::setVar('jsf',   implode(';',$jsFields)); }
	    if ( is_array($jsdisc)) { JRequest::setVar('jsd',   implode(';',$jsdisc)); }
	    }
	    if ( is_array($aec))    { JRequest::setVar('aec',   implode(';',$aec)); }
	    if ( is_array($ambra))  { JRequest::setVar('amb',   implode(';',$ambra)); }

	    if( $vm_sb == 1 ){
	    JRequest::setVar('vmsb',   $vm_sb);
	    JRequest::setVar('vmid',   implode(';',$vm_sb_productids));
	    JRequest::setVar('vmpr',   implode(';',$vm_sb_productprices));
	    JRequest::setVar('vmct',   implode(';',$vm_sb_productcats));
	    JRequest::setVar('vmor',   $vm_sb_order);
	    JRequest::setVar('vmsp',   $vm_sb_price);
	    JRequest::setVar('vmcf',   $vm_sb_cf);
	    JRequest::setVar('vmimg',  $vm_sb_img);
	    JRequest::setVar('vmlnk',  $vm_sb_link);
	    JRequest::setVar('vmsdesc',$vm_short_desc);
	    JRequest::setVar('vmdesc', $vm_desc);
	    }
	    if ($twitter_name)	{ JRequest::setVar('tw',     $twitter_name); }
	    if ($facebook_url)  { JRequest::setVar('fb',     urlencode(htmlentities(urlencode( $facebook_url )))); }
	    if ($myspace_name)  { JRequest::setVar('ms',     $myspace_name); }
	    if ($intro_text)    { JRequest::setVar('intro',  urlencode(htmlentities(urlencode( $intro_text )))); }
	    if ($sidebar)       { JRequest::setVar('sidebar',urlencode(htmlentities(urlencode( $sidebar )))); }
	    if ($gaSource)      { JRequest::setVar('gaS',    urlencode(htmlentities(urlencode( $gaSource )))); }
	    if ($gaMedium)      { JRequest::setVar('gaM',    urlencode(htmlentities(urlencode( $gaMedium )))); }
	    if ($gaName)        { JRequest::setVar('gaN',    urlencode(htmlentities(urlencode( $gaName   )))); }
	    if ($gaExcluded)    { JRequest::setVar('gaE',    $gaExcluded ); }
	    JRequest::setVar('coreOrder',JRequest::getVar('coreOrder', 0, 'post', 'string'));
	    JRequest::setVar('k2Order',  JRequest::getVar('k2Order', '', 'post', 'string'));

	    JRequest::setVar('sec_filter',  JRequest::getVar('sec_filter', 0, 'post', 'string'));
	    JRequest::setVar('cat_filter',  JRequest::getVar('cat_filter', 0, 'post', 'string'));
	    JRequest::setVar('k2cat_filter',JRequest::getVar('k2cat_filter', 0, 'post', 'string'));

	    JRequest::setVar( 'view',   'create' );
	    JRequest::setVar( 'layout', 'default'  );
	    JRequest::setVar( 'action', JRequest::getVar('action','')  );
	    JRequest::setVar( 'hidemainmenu', 0 );
	    JRequest::setVar( 'offset', 0 );

	    jimport( 'joomla.error.error' );
	    JError::raiseWarning( 100, $msg );

	    parent::display();

	} else {

	    $subject		= $db->getEscaped($subject);
	    $from_name		= $db->getEscaped($from_name);
	    $twitter_name	= $db->getEscaped($twitter_name);
	    $facebook_url	= $db->getEscaped($facebook_url);
	    $myspace_name	= $db->getEscaped($myspace_name);

	    $mainframe = & JFactory::getApplication();

	    $postdata = array();
	    $postdata['cn'] = $campaign_name;
	    $postdata['sj'] = stripslashes($subject);
	    $postdata['fn'] = stripslashes($from_name);
	    $postdata['fe'] = $from_email;
	    $postdata['re'] = $reply_email;
	    $postdata['ce'] = $confirmation_email;
	    $postdata['tpl'] = $template_folder;
	    $postdata['intro'] = urlencode( $intro_text );
	    $postdata['sidebar'] = urlencode( $sidebar );
	    $postdata['arts'] = implode(';',$articles);
	    $postdata['artsk2'] = implode(';',$articles_k2);
	    $postdata['sec_filter'] = $mainframe->getUserStateFromRequest('sec_filter',	'sec_filter',	0,	'int');
	    $postdata['cat_filter'] = $mainframe->getUserStateFromRequest('cat_filter',	'cat_filter',	0,	'int');
	    $postdata['k2cat_filter'] = $mainframe->getUserStateFromRequest('k2cat_filter',	'k2cat_filter',	0,	'int');
	    $postdata['coreOrder'] = JRequest::getVar('coreOrder', 0, 'post', 'string');
	    $postdata['k2Order'] = JRequest::getVar('k2Order', 0, 'post', 'string');

	    if( isset($jomsocialProfiles) ){
	    if ( is_array($jomsocialProfiles)) { JRequest::setVar('jsp',   implode(';',$jomsocialProfiles)); }
	    if ( is_array($jsFields)) { JRequest::setVar('jsf',   implode(';',$jsFields)); }
	    if ( is_array($jsdisc)) { JRequest::setVar('jsd',   implode(';',$jsdisc)); }
	    }

	    if( isset($jomsocialProfiles) ){
	    if ( is_array($jomsocialProfiles)) { $postdata['jsp'] = implode(';', $jomsocialProfiles); }
	    if ( is_array($jsFields)) { $postdata['jsf'] = implode(';',$jsFields); }
	    if ( is_array($jsdisc)) { $postdata['jsd'] = implode(';',$jsdisc); }
	    }
    //	$postdata['jsdiscOrder'] = JRequest::getVar('jsdiscOrder', false, 'post');
	    $aec = JRequest::getVar('aec', false, 'post');
	    if($aec){
	    $postdata['aec'] = implode(';', $aec);
	    }
    //	$postdata['aecOrder'] = JRequest::getVar('aecOrder', false, 'post');
	    $ambra = JRequest::getVar('ambra', false, 'post');
	    if($ambra){
	    $postdata['amb'] = implode(';', $ambra);
	    }
    //	$postdata['ambraOrder'] = JRequest::getVar('ambraOrder', false, 'post');

	    $postdata['toc'] = $toc_checkbox;
	    $postdata['toct'] = $toc_type;
	    $postdata['pop'] = $popular_checkbox;
	    if ($popular_in)
		$postdata['pin'] = implode(';',$popular_in);
	    if ($popular_ex)
		$postdata['pex'] = implode(';',$popular_ex);
	    $postdata['pk2'] = $populark2_checkbox;
	    if ($populark2_in)
		$postdata['pk2in'] = implode(';',$populark2_in);
	    if ($populark2_ex)
		$postdata['pk2ex'] = implode(';',$populark2_ex);
	    $postdata['pk2o'] = $populark2_only;
	    if( $vm_sb == 1 ){
	    $postdata['vmsb'] = $vm_sb;
	    $postdata['vmid'] = implode(';',$vm_sb_productids);
	    $postdata['vmpr'] = implode(';',$vm_sb_productprices);
	    $postdata['vmct'] = implode(';',$vm_sb_productcats);
	    $postdata['vmor'] = $vm_sb_order;
	    $postdata['vmsp'] = $vm_sb_price;
	    $postdata['vmcf'] = $vm_sb_cf;
	    $postdata['vmimg'] = $vm_sb_img;
	    $postdata['vmlnk'] = $vm_sb_link;
	    $postdata['vmsdesc'] = $vm_short_desc;
	    $postdata['vmdesc'] = $vm_desc;
	    }
	    $postdata['tw'] = stripslashes($twitter_name);
	    $postdata['fb'] = urlencode(htmlentities(urlencode( stripslashes($facebook_url) )));
	    $postdata['ms'] = stripslashes($myspace_name);
	    $postdata['gaS'] = urlencode(htmlentities(urlencode( $gaSource )));
	    $postdata['gaM'] = urlencode(htmlentities(urlencode( $gaMedium )));
	    $postdata['gaN'] = urlencode(htmlentities(urlencode( $gaName   )));
	    $postdata['gaE'] = urlencode(htmlentities(urlencode( $gaExcluded )));

	    $postdataJson = $db->getEscaped(json_encode( $postdata ));


	    // store campaign details locally
	    if( $creation_date && $action != 'copy' ){
		$query = "UPDATE #__joomailermailchimpintegration_campaigns "
			."SET `subject`='".$subject."', `from_name`='".$from_name."', `from_email`='".$from_email."', `reply`='".$reply_email."', `confirmation`='".$confirmation_email."', `creation_date`='".$time."', `cdata`='".$postdataJson."', `folder_id`='".$folder_id."'"
			."WHERE `creation_date` = '".$creation_date."'";
	    } else {
		$query = "INSERT INTO #__joomailermailchimpintegration_campaigns "
			."(name, subject, from_name, from_email, reply, confirmation, creation_date, cdata, folder_id) "
			."VALUES ('".$campaign_name."', '".$subject."', '".$from_name."', '".$from_email."', '".$reply_email."', '".$confirmation_email."', '".$time."', '".$postdataJson."', ".$folder_id." )";
	    }
	    $db->setQuery($query);
	    $db->query();
	    if($db->getErrorMsg()){
		$msg = $db->getErrorMsg();
		jimport( 'joomla.error.error' );
		JError::raiseWarning( 100, $msg );
	    } else {
		$msg  = sprintf ( JText::_( 'JM_DRAFT_SAVED' ), $campaign_name);
		 $mainframe->enqueueMessage( $msg );
	    }
	    JRequest::setVar( 'view',    'send' );
	    JRequest::setVar( 'layout',  'default' );
	    JRequest::setVar( 'campaign', $time );
	    JRequest::setVar( 'hidemainmenu', 0 );

	    parent::display();
	 //   $link = 'index.php?option=com_joomailermailchimpintegration&view=send&campaign='.$time;
	 //   $this->setRedirect($link, $msg, $msgType);
	}
    }// function

    function cleanString( $string ){
	$string = str_ireplace(' ', '_', $string);
	$string = htmlentities($string);
	return $string;
    }
	
	
    function preview(){

	jimport('joomla.filesystem.file');
	$db =& JFactory::getDBO();
	$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	$model = $this->getModel('create');
	$error = false;
	$elements = JRequest::getVar( 'elements', '', 'request', 'string' );
	$elements = json_decode($elements);
	$response = array();
	$response['msg'] = '';
	$abs_path = '$1="'.JURI::root().'$2$3';

	$campaign_name_ent = htmlentities( urldecode($elements->campaignName) );
	if ( get_magic_quotes_gpc() ) $campaign_name_ent = stripslashes($campaign_name_ent);
	$campaign_name_ent = str_ireplace(' ','_',$campaign_name_ent);
	$subject = urldecode($elements->subject);
	if ( get_magic_quotes_gpc() ) $subject = stripslashes($subject);
	$intro_text = urldecode($elements->intro);
	$sidebar = urldecode($elements->sidebar);
	if ( get_magic_quotes_gpc() ) $intro_text = stripslashes($intro_text);

	if (isset($elements->article)) { 
	    $articles = $elements->article;
	} else {
	    $articles = false;
	}

	$k2_installed = $elements->k2_installed;
	if ( $k2_installed && isset($elements->k2article) ) {
	    $articles_k2 = $elements->k2article;
	} else {
	    $articles_k2 = false;
	}

	$jomsocial_installed = $elements->jomsocial_installed;
	if ( $jomsocial_installed ){
	    if(isset($elements->jsProfiles) ) {
		$jomsocialProfiles = $elements->jsProfiles;
		$jsFields = $elements->jsProfileFields;
	    } else {
		$jomsocialProfiles = false;
	    }
	    if(isset($elements->jsdisc) ) {
		$jsdisc = $elements->jsdisc;
	    } else {
		$jsdisc = false;
	    }
	}

	$aec_installed = $elements->aec_installed;
	if ( $aec_installed ){
	    if(isset($elements->aec) ) {
		$aec = $elements->aec;
	    } else {
		$aec = false;
	    }
	}

	$ambra_installed = $elements->ambra_installed;
	if ( $ambra_installed ){
	    if(isset($elements->ambra) ) {
		$ambra = $elements->ambra;
	    } else {
		$ambra = false;
	    }
	}

	$template_folder = $elements->template;

	// display table of contents?
	$toc_checkbox = $elements->toc;
	$toc_type	  = $elements->toc_type;
	// display popular articles?
	$popular_checkbox = $elements->popular;
	$popular_ex = $elements->popEx;
	$popular_in = $elements->popIn;
	if ( $k2_installed ) {
	    // include K2 in populars?
	    $populark2_checkbox = $elements->populark2;
	    $populark2_ex = $elements->popk2Ex;
	    $populark2_in = $elements->popk2In;
	    // only K2 articles in populars?
	    $populark2_only = $elements->populark2_only;
	} else {
	    $populark2_checkbox = false;
	}

	// display VM products in sidebar?
	if (isset($elements->vm_sb)) { $vm_sb = $elements->vm_sb; } else { $vm_sb = false; }
	if(isset($elements->vm_sb_products)) {
	    $vm_sb_products = $elements->vm_sb_products;
	} else {
	    $vm_sb_products = false;
	}
	if($vm_sb_products){
	    $vm_sb_productids = array();
	    $vm_sb_productprices = array();
	    for($i=0;$i<count($vm_sb_products);$i++){
		$vm_sb_products[$i] = explode(';',$vm_sb_products[$i]);
		$vm_sb_productids[$i] = $vm_sb_products[$i][0];
		$vm_sb_productprices[$i] = $vm_sb_products[$i][1];
		$vm_sb_productcats[$i] = $vm_sb_products[$i][2];
	    }

	    $vm_sb_order = $elements->vm_sb_order;
	    $vm_sb_price = (isset($elements->vm_sb_pr))   ? $elements->vm_sb_pr   : '';
	    $vm_sb_cf    = (isset($elements->vm_sb_cf))   ? $elements->vm_sb_cf   : '';
	    $vm_sb_img   = (isset($elements->vm_sb_img))  ? $elements->vm_sb_img  : '';
	    $vm_sb_link  = (isset($elements->vm_sb_link)) ? $elements->vm_sb_link : '';
	    $vm_short_desc  = (isset($elements->vm_short_desc)) ? $elements->vm_short_desc : '';
	    $vm_desc        = (isset($elements->vm_desc)) ? $elements->vm_desc : '';
	}

	$twitter_name  = $elements->twitter;
	$facebook_url  = $elements->facebook;
	$myspace_name  = $elements->myspace;

	//	$facebookShareIt = $_POST['facebookShare'];
	$facebookShareIt = 0;

	// open the template file
	$filename = JPATH_ADMINISTRATOR.DS."components".DS."com_joomailermailchimpintegration".DS."templates".DS.$template_folder.DS."template.html";
	$template = JFile::read( $filename, false, filesize($filename) );
	if (!$template){
	    $response['html'] = '<div style="border: 2px solid #ff0000; margin:15px 0 5px;padding:10px 15px 12px;">'.
				'<img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/warning.png" align="left"/>'.
				'<div style="padding-left: 45px; line-height: 28px; font-size: 14px;">'.
				JText::_('JM_TEMPLATE_ERROR').
				'</div></div>';
	} else {

	    $regex = '!<#repeater#[^>]*>(.*)<#/repeater#>!is';
	    preg_match($regex, $template, $repeater);
	    $imagepath = '$1="'.JURI::root().'administrator'.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'templates'.DS.$template_folder.DS.'$2$3';
	    if(isset($repeater[0])) {
		$repeater[0] = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $repeater[0]);
	    } else {
		$response['html'] = '<div style="border: 2px solid #ff0000; margin:15px 0 5px;padding:10px 15px 12px;">'.
				    '<img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/warning.png" align="left"/>'.
				    '<div style="padding-left: 45px; line-height: 28px; font-size: 14px;">'.
				    JTEXT::_('JM_NO_CONTENT_CONTAINER').
				    '</div></div>';
		$repeater[0] = '';
		$error = true;
	    }

	    // JomSocial Profiles
	    $regex = '!<#jomsocialprofiles#[^>]*>(.*)<#/jomsocialprofiles#>!is';
	    preg_match($regex, $template, $jsp);
	    if(isset($jsp[0])){ $jsp = $jsp[0]; }
	    else { $jsp = ''; if($jomsocialProfiles) { $response['msg'] .= JTEXT::_('Error').': '.JTEXT::_('No JomSocial profile container').'<br />'; }}
	    $regex = '!<#jomsocialprofilesrepeater#[^>]*>(.*)<#/jomsocialprofilesrepeater#>!is';
	    preg_match($regex, $template, $jspr);
	    if(isset($jspr[0])) { $jspr = $jspr[0]; } else { $jspr = ''; }
	    $regex = '!<#jsfieldsrepeater#[^>]*>(.*)<#/jsfieldsrepeater#>!is';
	    preg_match($regex, $template, $jsfr);
	    if(isset($jsfr[0])) { $jsfr = $jsfr[0]; } else { $jsfr = ''; }
	    // JomSocial Discussions
	    $regex = '!<#jomsocialdiscussions#[^>]*>(.*)<#/jomsocialdiscussions#>!is';
	    preg_match($regex, $template, $jsd);
	    if(isset($jsd[0])){ $jsd = $jsd[0]; }
	    else { $jsd = ''; if($jsdisc) { $response['msg'] .= JTEXT::_('Error').': '.JTEXT::_('No JomSocial discussions container').'<br />'; }}
	    $regex = '!<#jomsocialdiscussionsrepeater#[^>]*>(.*)<#/jomsocialdiscussionsrepeater#>!is';
	    preg_match($regex, $template, $jsdr);
	    if(isset($jsdr[0])) { $jsdr = $jsdr[0]; } else { $jsdr = ''; }
	    // AEC plans
	    $regex = '!<#aec#[^>]*>(.*)<#/aec#>!is';
	    preg_match($regex, $template, $aecph);
	    if(isset($aecph[0])){ $aecph = $aecph[0]; }
	    else { $aecph = ''; if($aec) { $response['msg'] .= JTEXT::_('Error').': '.JTEXT::_('No AEC container').'<br />'; }}
	    $regex = '!<#aecrepeater#[^>]*>(.*)<#/aecrepeater#>!is';
	    preg_match($regex, $template, $aecr);
	    if(isset($aecr[0])) { $aecr = $aecr[0]; } else { $aecr = ''; }
	    // Ambra subscriptions
	    $regex = '!<#ambra#[^>]*>(.*)<#/ambra#>!is';
	    preg_match($regex, $template, $ambraph);
	    if(isset($ambraph[0])){ $ambraph = $ambraph[0]; }
	    else { $ambraph = ''; if($ambra) { $response['msg'] .= JTEXT::_('Error').': '.JTEXT::_('No Ambra container').'<br />'; }}
	    $regex = '!<#ambrarepeater#[^>]*>(.*)<#/ambrarepeater#>!is';
	    preg_match($regex, $template, $ambrar);
	    if(isset($ambrar[0])) { $ambrar = $ambrar[0]; } else { $ambrar = ''; }

	    // table of contents
	    $regex = '!<#tableofcontents#[^>]*>(.*)<#/tableofcontents#>!is';
	    preg_match($regex, $template, $tableofcontents);
	    if(isset($tableofcontents[0])) $tableofcontents = $tableofcontents[0];
	    else { $tableofcontents = ''; if($toc_checkbox) { $response['msg'] .= JTEXT::_('Error').': '.JTEXT::_('JM_NO_TOC_CONTAINER').'<br />'; }}
	    $regex = '!<#title_repeater#[^>]*>(.*)<#/title_repeater#>!is';
	    preg_match($regex, $template, $title_repeater);
	    if(isset($title_repeater[0])) $title_repeater = $title_repeater[0];
	    else $title_repeater = '';

	    // popular articles
	    $regex = '!<#populararticles#[^>]*>(.*)<#/populararticles#>!is';
	    preg_match($regex, $template, $populararticles);
	    if(isset($populararticles[0])) $populararticles = $populararticles[0];
	    else { $populararticles = ''; if($popular_checkbox) { $response['msg'] .= JTEXT::_('Error').': '.JTEXT::_('JM_NO_POPULAR_CONTAINER').'<br />'; }}
	    $regex = '!<#popular_repeater#[^>]*>(.*)<#/popular_repeater#>!is';
	    preg_match($regex, $template, $popular_repeater);
	    if(isset($popular_repeater[0])) $popular_repeater = $popular_repeater[0];
	    else $popular_repeater = '';

	    // VM products
	    $regex = '!<#vm_products#[^>]*>(.*)<#/vm_products#>!is';
	    preg_match($regex, $template, $vm_products);
	    if(isset($vm_products[0])) $vm_products = $vm_products[0];
	    else $vm_products = '';
	    $regex = '!<#vm_repeater#[^>]*>(.*)<#/vm_repeater#>!is';
	    preg_match($regex, $template, $vm_repeater);
	    if(isset($vm_repeater[0])) $vm_repeater = $vm_repeater[0];
	    else $vm_repeater = '';

	    // Twitter
	    $regex = '!<#twitter#[^>]*>(.*)<#/twitter#>!is';
	    preg_match($regex, $template, $twitter);
	    if(isset($twitter[0])) $twitter = $twitter[0];
	    else { $twitter = ''; if($twitter_name) { $response['msg'] .= JTEXT::_('Error').': '.JTEXT::_('JM_NO_TWITTER_CONTAINER').'<br />'; }}
	    // Facebook
	    $regex = '!<#facebook#[^>]*>(.*)<#/facebook#>!is';
	    preg_match($regex, $template, $facebook);
	    if(isset($facebook[0])) $facebook = $facebook[0];
	    else { $facebook = ''; if($facebook_url) { $response['msg'] .= JTEXT::_('Error').': '.JTEXT::_('JM_NO_FACEBOOK_CONTAINER').'<br />'; }}
	    // MySpace
	    $regex = '!<#myspace#[^>]*>(.*)<#/myspace#>!is';
	    preg_match($regex, $template, $myspace);
	    if(isset($myspace[0])) $myspace = $myspace[0];
	    else { $myspace = ''; if($myspace_name) { $response['msg'] .= JTEXT::_('Error').': '.JTEXT::_('JM_NO_MYSPACE_CONTAINER').'<br />'; }}
	    // Facebook share
	    $regex = '!<#facebook_share#[^>]*>(.*)<#/facebook_share#>!is';
	    preg_match($regex, $template, $facebookShare);
	    if(isset($facebookShare[0])) $fbs = $facebookShare[0];
	    //	 else { $fbs = ''; echo JTEXT::_('Error').': '; echo JTEXT::_('No facebook share container').'<br />'; }
	    $fbs = '';


	    $content = '';
	    $art_titles = array();
	    // Joomla core articles
	    if (@$articles) {
		foreach ($articles as $id) {
		    $full     = $elements->{'article_full_'.$id};
		    $readmore = $elements->{'readmore_'.$id};

		    $query = 'SELECT * FROM #__content WHERE id= '.$id;
		    $db->setQuery($query);
		    $article = $db->loadObjectList();

		    foreach ( $article as $art ) {
			$html_title = $html_content = '';

			if($toc_type){
			    $art_titles[] = '<a href="'.JURI::root().'index.php?option=com_content&view=article&id='.$id.'">'.$art->title.'</a>';
			} else {
			    $art_titles[] = '<a href="#'.$art->title.$id.'">'.$art->title.'</a>';
			}
			if ($readmore) {
			    $html_title = '<a href="'.JURI::root().'index.php?option=com_content&view=article&id='.$id.'">'.$art->title.'</a><a name="'.$art->title.$id.'"></a>';
			} else {
			    $html_title = $art->title;
			}
			$html_content  = $art->introtext;
			if ( $full == 1 ) {
			    $html_content .= ' '.$art->fulltext;
			}
			// Read more link
			if ($readmore) {
			    $html_content .= '<p><a href="'.JURI::root().'index.php?option=com_content&view=article&id='.$id.'">'.JTEXT::_('JM_READ_MORE').'</a></p>';
			}

			$template = str_ireplace( '<#title#>',   $html_title,   $repeater[0] );
			$template = str_ireplace( '<#content#>', $html_content, $template    );
			$content .= $template;
		    } // end foreach article
		}
	    }

	    // K2 articles
	    if ($k2_installed && $articles_k2) {
		foreach ($articles_k2 as $id ) {
		    $full     = $elements->{'k2article_full_'.$id};
		    $readmore = $elements->{'k2readmore_'.$id};

		    $query = 'SELECT * FROM #__k2_items WHERE id= '.$id;
		    $db->setQuery($query);
		    $article = $db->loadObjectList();

		    foreach ( $article as $art ) {
			$html_title = $html_content = '';
			if ($readmore) {
			    $html_title = '<a href="'.JURI::root().'index.php?option=com_k2&view=item&id='.$id.'">'.$art->title.'</a><a name="'.$art->title.$id.'"></a>';
			} else {
			    $html_title = $art->title.'<a name="'.$art->title.$id.'"></a>';
			}

			$art_titles[] = '<a href="#'.$art->title.$id.'">'.$art->title.'</a>';

			$html_content  = $art->introtext;
			if ( $full == 1 ) {
			    $html_content .= ' '.$art->fulltext;
			}
			// Read more link
			if ($readmore) {
			    $html_content .= '<p><a href="'.JURI::root().'index.php?option=com_k2&view=item&id='.$id.'">'.JTEXT::_('JM_READ_MORE').'</a></p>';
			}

			$template = str_ireplace( '<#title#>',   $html_title,   $repeater[0] );
			$template = str_ireplace( '<#content#>', $html_content, $template    );
			$content .= $template;
		    }
		}
	    }

	    // JomSocial Profiles
	    $jsProfiles = '';
	    if(@$jomsocialProfiles){
		foreach($jomsocialProfiles as $jsProfile){
		    $jsUser = CFactory::getUser($jsProfile);
		    $profileLink = JURI::root().CRoute::_('index.php?option=com_community&view=profile&userid='.$jsProfile );
		    $thumb = '<a href="'.$profileLink.'"><img src="'.$jsUser->getThumbAvatar().'" alt="'.$jsUser->name.'" title="'.$jsUser->name.'" border="0" /></a>';
		    $profiles = str_ireplace('<#jsAvatar#>', $thumb, $jspr);
		    $profiles = str_ireplace('<#jsName#>', '<a href="'.$profileLink.'">'.$jsUser->name.'</a>', $profiles);

		    $fieldValues = $model->getFieldValues($jsProfile, $jsFields);
		    $fields = '';
		    foreach($fieldValues as $f){
			if($f->value){
			    if($f->type == 'date'){ $f->value = substr($f->value, 0, -9); }
			    $fieldsTmp  = str_ireplace('<#jsFieldTitle#>', $f->name, $jsfr);
			    $fieldsTmp  = str_ireplace('<#jsFieldValue#>', $f->value, $fieldsTmp);
			    $fields .= $fieldsTmp;
			}
		    }
		    $profiles = preg_replace( '!<#jsfieldsrepeater#[^>]*>(.*)<#/jsfieldsrepeater#>!is', $fields, $profiles );
		    $profiles = str_ireplace( array('<#jsfieldsrepeater#>','<#/jsfieldsrepeater#>'), '', $profiles);

		    $jsProfiles .= $profiles;
		}
		$jsProfiles = str_ireplace( array('<#jomsocialprofilesrepeater#>','<#/jomsocialprofilesrepeater#>'), '', $jsProfiles);
	    }

	    $jsDiscussions = '';
	    if(@$jsdisc){
		$lang =& JFactory::getLanguage();
		$lang->load('com_community', JPATH_SITE);
		$langString = JText::_('CC ACTIVITIES NEW GROUP DISCUSSION');
		$discussions = $model->getJomsocialDiscussions($jsdisc);
		foreach($discussions as $d){
		    $jsUser = CFactory::getUser($d->creator);
		    $profileLink = JURI::root().CRoute::_('index.php?option=com_community&view=profile&userid='.$d->creator );
		    $discLink = JURI::root().CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid='.$d->groupid.'&topicid='.$d->id );
		    $groupLink = JURI::root().CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$d->groupid );
		    $thumb = '<a href="'.$profileLink.'"><img src="'.$jsUser->getThumbAvatar().'" alt="'.$jsUser->name.'" title="'.$jsUser->name.'" border="0" /></a>';

		    $discTmp = str_ireplace('{actor}', $jsUser->name, $langString);
		    $discTmp = str_ireplace('{topic_url}', $discLink, $discTmp);
		    $discTmp = str_ireplace('{topic}', $d->title, $discTmp);
		    $discTmp = str_ireplace('%1$s', $groupLink, $discTmp);
		    $discTmp = str_ireplace('%2$s', $d->name, $discTmp);

		    $discTmp .= ':<br />'.$d->message;

		    $discTmp = '<table valign="top">
				    <tr>
					<td valign="top">'.$thumb.'</td>
					<td valign="top" style="padding: 0 0 0 10px;">'.$discTmp.'</td>
				    </tr>
				</table>';

		    $discTmp = str_ireplace('<#jsDiscussionContent#>', $discTmp, $jsdr);
		    $jsDiscussions .= $discTmp;
		}
		$jsDiscussions = str_ireplace( array('<#jomsocialdiscussionsrepeater#>','<#/jomsocialdiscussionsrepeater#>'), '', $jsDiscussions);
	    }

	    $aecContent = '';
	    if(@$aec){
		$aecConfig = $model->getAECconfig();
		$aecPlans = $model->getAECplans($aec);
		foreach($aecPlans as $a){
		    $aecParams = unserialize( base64_decode( $a->params ) );
		    $link = JURI::root().JRoute::_('index.php?option=com_acctexp&view=subscribe');
		    $aecTmp =  '<tr>
				    <td valign="top"><a href="'.$link.'">'.$a->name.'</a></td>
				    <td valign="top" style="padding: 0 0 0 10px;">'.$a->desc.'</td>
				    <td valign="top" style="padding: 0 0 0 10px;">'.$aecParams['full_amount'].' '.$aecConfig['standard_currency'].'</td>
				</tr>';
		    $aecTmp = str_ireplace('<#aeccontent#>', $aecTmp, $aecr);
		    $aecContent .= $aecTmp;
		}
		$aecContent = str_ireplace( array('<#aecrepeater#>','<#/aecrepeater#>'), '', $aecContent);
	    }

	    $ambraContent = '';
	    if(@$ambra){
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ambrasubs'.DS.'helpers'.DS.'_base.php');
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ambrasubs'.DS.'helpers'.DS.'config.php');
		$ambraConfig = AmbrasubsConfig::getInstance();
		$ambraPre = str_ireplace('$','\$',$ambraConfig->get('currency_preval', '$'));
		$ambraPost = $ambraConfig->get('currency_postval', '');
		$ambraPlans = $model->getAmbra($ambra);

		foreach($ambraPlans as $a){
		    $link = JURI::root().JRoute::_('index.php?option=com_ambrasubs&controller=subscriptions&task=new&id='.$a->id);
		    $img = '<img src="'.JURI::root().$a->img.'" alt="'.$a->title.'" title="'.$a->title.'" border="0" />';
		    $ambraTmp = '<tr>
				    <td valign="top"><a href="'.$link.'">'.$img.'</a></td>
				    <td valign="top"><a href="'.$link.'">'.$a->title.'</a><br />'.$a->description.'</td>
				    <td valign="top" style="padding: 0 0 0 10px;" nowrap="nowrap">'.$ambraPre.''.$a->value.''.$ambraPost.'</td>
				</tr>';
		    $ambraTmp = str_ireplace('<#ambracontent#>', $ambraTmp, $ambrar);
		    $ambraContent .= $ambraTmp;
		}
		$ambraContent = str_ireplace( array('<#ambrarepeater#>','<#/ambrarepeater#>'), '', $ambraContent);
	    }

	    // remove tiny mce stuff like mce_src="..."
	    $content = preg_replace('(mce_style=".*?")', '', $content);
	    $content = preg_replace('(mce_src=".*?")',   '', $content);
	    $content = preg_replace('(mce_href=".*?")',  '', $content);
	    $content = preg_replace('(mce_bogus=".*?")', '', $content);
	    // convert relative to absolute paths
	    $content = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $content);

	    // create table of contents
	    $toc = '';
	    foreach ($art_titles as $art_title) {
		$toc .= str_ireplace( '<#article_title#>', $art_title, $title_repeater );
	    }
	    $tableofcontents = preg_replace( '!<#title_repeater#[^>]*>(.*)<#/title_repeater#>!is', $toc, $tableofcontents );
	    $to_replace = array('<#tableofcontents#>', '<#/tableofcontents#>', '<#title_repeater#>', '<#/title_repeater#>');
	    $tableofcontents = str_ireplace( $to_replace, '', $tableofcontents);

	    $where = '';
	    $wEx = array();
	    $wIn = array();
	    $wCore = array();
	    if($popular_checkbox){
		if(isset($popular_ex[0])){
		    foreach($popular_ex as $p){
			$wEx[] = ' c.catid != '.$p;
		    }
		    $wCore[] = ( count( $wEx ) ? ' AND (' . implode( ' AND ', $wEx ) . ' )' : '' );
		}
		if(isset($popular_in[0])){
		    foreach($popular_in as $p){
			$wIn[] = ' c.catid = '.$p;
		    }
		    $wCore[] = ( count( $wIn ) ? ' AND (' . implode( ' OR ', $wIn ) . ' )' : '' );
		}
		$where = implode('', $wCore);
	    }

	    $whereK2 = '';
	    $wEx = array();
	    $wIn = array();
	    $wK2 = array();
	    if($populark2_checkbox){
		if(isset($populark2_ex[0])){
		    foreach($populark2_ex as $p){
			$wEx[] = ' k.catid != '.$p;
		    }
		    $wK2[] = ( count( $wEx ) ? ' AND (' . implode( ' AND ', $wEx ) . ' )' : '' );
		}
		if(isset($populark2_in[0])){
		    foreach($populark2_in as $p){
			$wIn[] = ' k.catid = '.$p;
		    }
		    $wK2[] = ( count( $wIn ) ? ' AND (' . implode( ' OR ', $wIn ) . ' )' : '' );
		}
	    }
	    $whereK2 = implode('', $wK2);

	    // create list of popular articles
	    if ( $popular_checkbox && !$populark2_checkbox){
		$query = 'SELECT c.id, c.title, c.hits FROM #__content as c
			  WHERE ( c.state = 1 OR c.state = -2 )
			  AND c.hits != 0
			  '.$where.'
			  ORDER BY c.hits DESC
			  LIMIT 0 , 5';
	    } else if ( $popular_checkbox && $populark2_checkbox && !$populark2_only ) {
		$query = 'SELECT c.id, c.title, c.hits
			  FROM #__content as c
			  WHERE ( c.state = 1 OR c.state = -2 )
			  AND c.hits != 0
			  '.$where.'
			  UNION ALL SELECT k.id, k.title, k.hits
			  FROM #__k2_items as k
			  WHERE k.published = 1
			  AND k.hits != 0
			  '.$whereK2.'
			  ORDER BY hits DESC
			  LIMIT 0 , 5 ';
	    } else if ( $popular_checkbox && $populark2_checkbox && $populark2_only )  {
		$query = 'SELECT k.id, k.title, k.hits
			  FROM #__k2_items as k
			  WHERE k.published = 1
			  AND k.hits != 0
			  '.$whereK2.'
			  ORDER BY k.hits DESC
			  LIMIT 0 , 5 ';
	    }

	    $popularlist = '';
	    if ( $popular_checkbox || $populark2_checkbox ) {
		$db->setQuery($query);
		$popular = $db->loadObjectList();
		$i=0;
		if($popular){
		    foreach ($popular as $pop){
			$i++;
			$core = false;
			if ($i>5) { break; }

			$query = 'SELECT title FROM #__content WHERE title = "'.$pop->title.'"';
			$db->setQuery($query);
			$core = $db->loadResult();
			if ($core) {
			    $url = '<a href="'.JURI::root().'index.php?option=com_content&view=article&id='.$pop->id.'">'.$pop->title.'</a>';
			} else {
			    $url = '<a href="'.JURI::root().'index.php?option=com_k2&view=item&id='.$pop->id.'">'.$pop->title.'</a>';
			}
			$popularlist .= str_ireplace( '<#popular_title#>', $url, $popular_repeater );
		    }
		}
	    }
	    $popularlist = preg_replace( '!<#popular_repeater#[^>]*>(.*)<#/popular_repeater#>!is', $popularlist, $populararticles );
	    $to_replace  = array('<#populararticles#>', '<#/populararticles#>', '<#popular_repeater#>', '<#/popular_repeater#>');
	    $popularlist = str_ireplace( $to_replace, '', $popularlist);

	    // insert vm products into sidebar
	    $vm = '';
	    if ( $vm_sb ) {
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart.cfg.php');
		$where = '';
		for($i=0;$i<count($vm_sb_productids);$i++){
		    $where .= ( !$where ) ? ' AND ' : ' OR ';
		    $where .= ' ( a.product_id = '.$vm_sb_productids[$i].' '.
			      'AND b.product_price = '.$vm_sb_productprices[$i].' '.
			      'AND c.category_id = '.$vm_sb_productcats[$i].' ) ';
		}

		if($vm_sb_order=='price_desc'){
		    $order = ' ORDER BY b.product_price DESC ';
		} else if($vm_sb_order=='price_asc'){
		    $order = ' ORDER BY b.product_price ASC ';
		} else if($vm_sb_order=='name_desc'){
		    $order = ' ORDER BY a.product_name DESC ';
		} else if($vm_sb_order=='name_asc'){
		    $order = ' ORDER BY a.product_name ASC ';
		} else if($vm_sb_order=='cat_desc'){
		    $order = ' ORDER BY d.category_name DESC ';
		} else if($vm_sb_order=='cat_asc'){
		    $order = ' ORDER BY d.category_name ASC ';
		} else if($vm_sb_order=='random'){
		    $order = ' ORDER BY RAND() ';
		} else {
		    $order = '';
		}

		$query = 'SELECT a.product_id,a.product_name,a.product_thumb_image, a.product_s_desc, a.product_desc,
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
			    '.$where.'
			    AND a.product_thumb_image != ""
			    '.$order;

		$db->setQuery($query);
		$products = $db->loadObjectList();

		foreach ($products as $prod){
		    $product_content = '';

		    if ($vm_sb_link){
			$product_content .= '<a href="'.JURI::root().'index.php?page=shop.product_details&flypage='.FLYPAGE.'&product_id='.$prod->product_id.'&category_id='.$prod->category_id.'&option=com_virtuemart">';
		    }
		    $product_content .= $prod->product_name.'<br />';
		    if ( $vm_sb_price ) {
			if ($vm_sb_cf){
			    $product_content .= $prod->product_currency.' '.number_format($prod->product_price,2).'<br />';
			} else {
			    $product_content .= number_format($prod->product_price,2).' '.$prod->product_currency.'<br />';
			}
		    }
		    if ($vm_sb_img){
			$product_content .= '<img src="'.JURI::root().'components/com_virtuemart/shop_image/product/'.$prod->product_thumb_image.'" border="0" />';
		    }

		    if ($vm_short_desc){
			$product_content .= '<p>'.$prod->product_s_desc.'</p>';
		    }
		    if ($vm_desc){
			$product_content .= '<p>'.$prod->product_desc.'</p>';
		    }


		    if ($vm_sb_link){
			$product_content .= '</a>';
		    }

		    $vm .= str_ireplace( '<#vm_content#>', $product_content, $vm_repeater );
		}
	    }
	    $vm = preg_replace( '!<#vm_repeater#[^>]*>(.*)<#/vm_repeater#>!is', $vm, $vm_products);
	    $to_replace  = array('<#vm_products#>', '<#/vm_products#>', '<#vm_repeater#>', '<#/vm_repeater#>');
	    $vm = str_ireplace( $to_replace, '', $vm);

	    // twitter
	    $tw = str_ireplace( '<#twitter-name#>', $twitter_name, $twitter);
	    $to_replace  = array('<#twitter#>', '<#/twitter#>');
	    $tw = str_ireplace( $to_replace, '', $tw);
	    // convert relative to absolute paths
	    $tw = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $tw);
	    // facebook
	    $fb = str_ireplace( '<#facebook-url#>', $facebook_url, $facebook);
	    $to_replace  = array('<#facebook#>', '<#/facebook#>');
	    $fb = str_ireplace( $to_replace, '', $fb);
	    // convert relative to absolute paths
	    $fb = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $fb);
	    // myspace
	    $ms = str_ireplace( '<#myspace-name#>', $myspace_name, $myspace);
	    $to_replace  = array('<#myspace#>', '<#/myspace#>');
	    $ms = str_ireplace( $to_replace, '', $ms);
	    // convert relative to absolute paths
	    $ms = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $ms);
	    // facebook share
	    $to_replace  = array('<#facebook_share#>', '<#/facebook_share#>');
	    $fbs = str_ireplace( $to_replace, '', $fbs);
	    // convert relative to absolute paths
	    $fbs = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $fbs);

	    // modify paths of intro-text
	    // remove tiny mce stuff like mce_src="..."
	    $intro_text = preg_replace('(mce_style=".*?")', '', $intro_text);
	    $intro_text = preg_replace('(mce_src=".*?")',   '', $intro_text);
	    $intro_text = preg_replace('(mce_href=".*?")',  '', $intro_text);
	    $intro_text = preg_replace('(mce_bogus=".*?")', '', $intro_text);
	    // convert relative to absolute paths
	    $intro_text = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $intro_text);
	    // end paths intro-text


	    // re-open the template file
	    $filename = JPATH_ADMINISTRATOR.DS."components".DS."com_joomailermailchimpintegration".DS."templates".DS.$template_folder.DS."template.html";
	    $template = JFile::read( $filename, false, filesize($filename) );

	    // create absolute image paths
	    $imagepath = ' src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/templates/'.$template_folder.'/';
	    $template  = preg_replace('#(src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath.'$2$3', $template);
	    $imagepath = " url('".JURI::root()."administrator/components/com_joomailermailchimpintegration/templates/".$template_folder."/";
	    $template  = preg_replace('#(\s*)url?\([\'"]?[../]*[\'"]?#i', $imagepath, $template);


	    // insert sidebar editor content
	    $sidebar = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $sidebar);
	    $template = str_ireplace( '<#sidebar#>', $sidebar, $template    );


	    // insert page title and intro-text
	    $subject = str_ireplace( '$' , '\$', $subject);
	    $template = str_ireplace( '<#subject#>', $subject, $template);
	    $intro_text = str_ireplace( '$' , '\$', $intro_text);
	    $template = str_ireplace( '<#intro_content#>', $intro_text, $template);

	    // insert articles
	    $content = str_ireplace( '$' , '\$', $content);
	    $template = preg_replace( '!<#repeater#[^>]*>(.*)<#/repeater#>!is', $content, $template );

	    // remove placeholders
	    $template = str_ireplace( '<#repeater#>',  '', $template);
	    $template = str_ireplace( '<#/repeater#>', '', $template);

	    // insert JomSocial Profiles
	    if($jsProfiles){
		$template = preg_replace( '!<#jomsocialprofilesrepeater#[^>]*>(.*)<#/jomsocialprofilesrepeater#>!is', $jsProfiles, $template );
		$template = str_ireplace( array('<#jomsocialprofiles#>','<#/jomsocialprofiles#>'), '', $template);
	    } else {
		$template = preg_replace( '!<#jomsocialprofiles#[^>]*>(.*)<#/jomsocialprofiles#>!is', '', $template );
	    }
	    // insert JomSocial Discussions
	    if($jsDiscussions){
		$template = preg_replace( '!<#jomsocialdiscussionsrepeater#[^>]*>(.*)<#/jomsocialdiscussionsrepeater#>!is', $jsDiscussions, $template );
		$template = str_ireplace( array('<#jomsocialdiscussions#>','<#/jomsocialdiscussions#>'), '', $template);
	    } else {
		$template = preg_replace( '!<#jomsocialdiscussions#[^>]*>(.*)<#/jomsocialdiscussions#>!is', '', $template );
	    }

	    // insert AEC plans
	    if($aecContent){
		$template = preg_replace( '!<#aecrepeater#[^>]*>(.*)<#/aecrepeater#>!is', $aecContent, $template );
		$template = str_ireplace( array('<#aec#>','<#/aec#>'), '', $template);
	    } else {
		$template = preg_replace( '!<#aec#[^>]*>(.*)<#/aec#>!is', '', $template );
	    }

	    // insert Ambra subscriptions
	    if($ambraContent){
		$template = preg_replace( '!<#ambrarepeater#[^>]*>(.*)<#/ambrarepeater#>!is', $ambraContent, $template );
		$template = str_ireplace( array('<#ambra#>','<#/ambra#>'), '', $template);
	    } else {
		$template = preg_replace( '!<#ambra#[^>]*>(.*)<#/ambra#>!is', '', $template );
	    }

	    // insert table of contents
	    if ($toc_checkbox && ( $articles || $articles_k2) ){
		$tableofcontents = str_ireplace( '$' , '\$', $tableofcontents);
		$template = preg_replace( '!<#tableofcontents#[^>]*>(.*?)<#/tableofcontents#>!is', $tableofcontents, $template );
	    } else {
		$template = preg_replace( '!<#tableofcontents#[^>]*>(.*?)<#/tableofcontents#>!is', '', $template );
	    }

	    //insert popular articles
	    if ($popular_checkbox){
		$popularlist = str_ireplace( '$' , '\$', $popularlist);
		$template = preg_replace( '!<#populararticles#[^>]*>(.*?)<#/populararticles#>!is', $popularlist, $template );
	    } else {
		$template = preg_replace( '!<#populararticles#[^>]*>(.*?)<#/populararticles#>!is', '', $template );
	    }

	    //insert vm products
	    if ($vm_sb){
		$vm = str_ireplace( '$' , '\$', $vm);
		$template = preg_replace( '!<#vm_products#[^>]*>(.*?)<#/vm_products#>!is', $vm, $template );

	    } else {
		$template = preg_replace( '!<#vm_products#[^>]*>(.*?)<#/vm_products#>!is', '', $template );
	    }

	    //replace all the escaped dollars
	    $template = str_ireplace( '\$', '$', $template);

	    //insert twitter link
	    $tw = ($twitter_name) ? $tw : '';
	    $template = preg_replace( '!<#twitter#[^>]*>(.*?)<#/twitter#>!is', $tw, $template );

	    //insert facebook link
	    $fb = ($facebook_url) ? $fb : '';
	    $template = preg_replace( '!<#facebook#[^>]*>(.*?)<#/facebook#>!is', $fb, $template );

	    //insert myspace link
	    $ms = ($myspace_name) ? $ms : '';
	    $template = preg_replace( '!<#myspace#[^>]*>(.*?)<#/myspace#>!is', $ms, $template );

	    //insert facebook share link
	    if ($facebookShareIt){
		$template = preg_replace( '!<#facebook_share#[^>]*>(.*?)<#/facebook_share#>!is', $fbs, $template );
		$metaData = '<meta name="title" content="'.$_POST['campaign_name'].'" />
			    <meta name="description" content="'.$_POST['facebookShareDesc'].'" />
			    <link rel="image_src" href="A URL to a thumbnail image" / >';
		$metaData = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $metaData);
		$template = str_ireplace( '</head>', $metaData.'</head>', $template );
	    } else {
		$template = preg_replace( '!<#facebook_share#[^>]*>(.*?)<#/facebook_share#>!is', '', $template );
	    }

	    // create google analytics tracking links
	    if($elements->gaEnabled){
		$ga = 'utm_source='.$elements->gaSource.'&utm_medium='.$elements->gaMedium.'&utm_campaign='.$elements->gaName.'"';
		$excludedURLs = urldecode($elements->gaExcluded);
		$excludedURLs = explode("\n", $excludedURLs);
		for($i=0;$i<count($excludedURLs);$i++){
			$excludedURLs[$i] = trim($excludedURLs[$i]);
		}
		$excludedURLs[] = '*|UNSUB|*';

		$regex = '#<a(.*?)>(.*?)</a>#i';
		preg_match_all($regex, $template, $templateLinks, PREG_PATTERN_ORDER);

		 if(isset($templateLinks[0])) {
		    foreach($templateLinks[0] as $link){
			if( !strstr($link, 'javascript') ){
			    preg_match_all( '#((href)="(?!\.css)[^"]+)"#i' , $link, $oldLink, PREG_PATTERN_ORDER);
			    if(isset($oldLink[0][0])){
				$glue = ( strstr($oldLink[0][0], '?') )? $glue = '&' : $glue = '?';
				$oldHref = substr($oldLink[0][0], 0, -1);
				$addGA = true;
				foreach($excludedURLs as $ex){
				    if (stristr($oldHref,$ex)) { $addGA = false; }
				}
				if( $addGA ){
				    $newLink = preg_replace('#((href)="(?!\.css)[^"]+)"#i', $oldHref.$glue.$ga.'"', $link);
				    $template = str_ireplace( $oldLink[0][0], $oldHref.$glue.$ga.'"', $template);
				}
			    }
			}
		    }
		}
	    }

	    // prevent preview from being cached
	    $metaData = '<meta http-Equiv="Cache-Control" Content="no-cache">
			<meta http-Equiv="Pragma" Content="no-cache">
			<meta http-Equiv="Expires" Content="0">
			<script type="text/javascript" src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/js/jquery.min.js"></script>
			<script type="text/javascript">
			var tmplUrl = "'.JURI::root().'tmp/";
			jQuery(document).ready(function() {
			    jQuery("a").click( function(){
				link = jQuery(this).attr("href").replace(tmplUrl,"");
				alert( link );
				void(0);
				return false;
			    });
			});
			</script>';
	    if( !stristr($template, "<head>") ){
		    $template = str_ireplace( '<html>', '<html><head>'.$metaData.'</head>', $template );
	    } else {
		    $template = str_ireplace( '</head>', $metaData.'</head>', $template );
	    }


	    // create output
	    if(!$error){
		$filename = JPATH_SITE.DS."tmp/".$campaign_name_ent.".html";
		if ( JFile::exists( $filename ) ) {
		     JFile::delete( $filename );
		}
		$handle = JFile::write( $filename, $template );
		if(!$handle){
		    $response['html'] = '<div style="border: 2px solid #ff0000; margin:15px 0 5px;padding:10px 15px 12px;">'.
					'<img src="'.JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/warning.png" align="left"/>'.
					'<div style="padding-left: 45px; line-height: 28px; font-size: 14px;">'.
					JText::sprintf('PERMISSIONS ERROR GLOBAL', $params->get( $paramsPrefix.'archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive' ) ).
					'</div></div>';
		} else {

		    $html_file = JURI::root()."tmp/".$campaign_name_ent.".html";
		    $template = '<iframe src="'.$html_file.'" width="100%" height="800" name="previewIframe" id="previewIframe"></iframe>';
		    $response['html'] = $template;
		}
	    }
	}

	// return AJAX response
	echo json_encode( $response );
    }

}// class

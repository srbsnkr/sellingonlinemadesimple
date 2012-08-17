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

if($this->jomsocial){
    require_once (JPATH_SITE.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
}
if($this->ambra){
    require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ambrasubs'.DS.'helpers'.DS.'_base.php');
    require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ambrasubs'.DS.'helpers'.DS.'config.php');
    $ambraConfig = AmbrasubsConfig::getInstance();
    $ambraPre = $ambraConfig->get('currency_preval', '$');
    $ambraPost = $ambraConfig->get('currency_postval', '');
}

$isWritable = new checkPermissions();
echo $isWritable->check();

$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
$MCapi  = $params->get( $paramsPrefix.'MCapi' );
$MCauth = new MCauth();
// GA data
$gusername  = $params->get( $paramsPrefix.'gusername' );
$gpw        = $params->get( $paramsPrefix.'gpw' );
$gprofileid = $params->get( $paramsPrefix.'gprofileid' );


if ( !$MCapi ) {
	echo $MCauth->apiKeyMissing();
} else {
    if( !$MCauth->MCauth() ) {
	echo $MCauth->apiKeyMissing(1);
    } else {

    JHTML::_('behavior.modal');
    JHTML::_('behavior.tooltip');
    jimport('joomla.html.pane');
    jimport('joomla.filesystem.file');

    $mainframe =& JFactory::getApplication();
    $db = & JFactory::getDBO();
    if( version_compare(JVERSION,'1.6.0','ge') ){
	$query = "SELECT enabled FROM #__extensions WHERE type = 'plugin' AND element ='tinymce'";
    } else {
	$query = 'SELECT published FROM #__plugins WHERE element = "tinymce"';
    }
    $db->setQuery($query);
    $tinymce = $db->loadResult();

    if ( !$tinymce ){
        $editortype = 'none';
    } else {
        $editortype = 'tinymce';
    }
    $editor =& JFactory::getEditor($editortype);

    $fName = $params->get( $paramsPrefix.'from_name', $mainframe->getCfg( 'sitename' ) );
    $fMail = $params->get( $paramsPrefix.'from_email', $mainframe->getCfg( 'mailfrom' ) );
    $rMail = $params->get( $paramsPrefix.'reply_email', $mainframe->getCfg( 'mailfrom' ) );
    $cMail = $params->get( $paramsPrefix.'confirmation_email', $mainframe->getCfg( 'mailfrom' ) );

    $twitter_name = $params->get( $paramsPrefix.'twitter_name', '' );
    $fb_link      = $params->get( $paramsPrefix.'facebook_link','' );
    $myspace_name = $params->get( $paramsPrefix.'myspace_name', '' );

    if( version_compare(JVERSION,'1.6.0','ge') ){
	$tt_image = JURI::root() .'administrator/components/com_joomailermailchimpintegration/assets/images/info.png';
    } else {
	$tt_image = '../../../administrator/components/com_joomailermailchimpintegration/assets/images/info.png';
    }
    $tt_image_abs = JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/images/info.png';

    if ( JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'admin.virtuemart.php') ) {
	$vm_installed = true;
    } else {
	$vm_installed = false;
    }

    $campaign_name      = JRequest::getVar('cn',   '', '', 'string');
    $subject            = JRequest::getVar('sj',   '', '', 'string');
    $from_name          = JRequest::getVar('fn',   '', '', 'string');
    $from_email         = JRequest::getVar('fe',   '', '', 'string');
    $reply_email        = JRequest::getVar('re',   '', '', 'string');
    $confirmation_email = JRequest::getVar('ce',   '', '', 'string');
    $listid             = JRequest::getVar('li',false, '', 'string');
    $listid             = ($listid)? explode(';', $listid) : false;
    $articles_pre       = JRequest::getVar('arts', '', '', 'string');
    $articles_pre       = explode(';', $articles_pre);
    $articlesk2_pre     = JRequest::getVar('artsk2','', '', 'string');
    if($articlesk2_pre){
    $articlesk2_pre     = explode(';', $articlesk2_pre);
    }
    if(!$articlesk2_pre){
    $articlesk2_pre     = JRequest::getVar('k2article',array(),'','array');
    }
    $toc                = JRequest::getVar('toc',false, '', 'string');
    $toct               = JRequest::getVar('toct',false, '', 'string');
    $pop                = JRequest::getVar('pop',false, '', 'string');
    $pex                = JRequest::getVar('pex',false, '', 'string');
    if($pex){ $pex      = explode(';', $pex); }
    $pin                = JRequest::getVar('pin',false, 'get');
    if($pin){ $pin      = explode(';', $pin); }
    $pk2                = JRequest::getVar('pk2',false, '', 'string');
    $pk2ex              = JRequest::getVar('pk2ex',false, 'get');
    if($pk2ex){ $pk2ex  = explode(';', $pk2ex); }
    $pk2in              = JRequest::getVar('pk2in',false, 'get');
    if($pk2in){ $pk2in  = explode(';', $pk2in); }
    $pk2o               = JRequest::getVar('pk2o',false, '', 'string');
	
    $jomsocial_pre      = JRequest::getVar('jsp','', '', 'string');
    $jomsocial_pre      = explode(';', $jomsocial_pre);
    $jsf_pre            = JRequest::getVar('jsf','', '', 'string');
    $jsf_pre            = explode(';', $jsf_pre);
    $jsdisc_pre         = JRequest::getVar('jsd','', '', 'string');
    $jsdisc_pre         = explode(';', $jsdisc_pre);
    $aec_pre            = JRequest::getVar('aec','', '', 'string');
    $aec_pre            = explode(';', $aec_pre);
    $ambra_pre          = JRequest::getVar('amb','', '', 'string');
    $ambra_pre          = explode(';', $ambra_pre);
	
    $vm_sb              = JRequest::getVar('vmsb',false, '', 'string');
    $vmid               = JRequest::getVar('vmid', false, '', 'string');
    if($vmid) $vmid     = explode(';', $vmid);
    $vmpr               = JRequest::getVar('vmpr', false, '', 'string');
    if($vmpr) $vmpr     = explode(';', $vmpr);
    $vmct               = JRequest::getVar('vmct', false, '', 'string');
    if($vmct) $vmct     = explode(';', $vmct);
    $vmor               = JRequest::getVar('vmor',false, '', 'string');
    $vmsp               = JRequest::getVar('vmsp',false, '', 'string');
    $vmcf               = JRequest::getVar('vmcf',false, '', 'string');
    $vmimg              = JRequest::getVar('vmimg',false, '', 'string');
    $vmlnk              = JRequest::getVar('vmlnk',false, '', 'string');
    $vmsdesc            = JRequest::getVar('vmsdesc',false, '', 'string');
    $vmdesc             = JRequest::getVar('vmdesc',false, '', 'string');
	
    $template           = JRequest::getVar('tpl',  false, '', 'string');
    $twitter            = JRequest::getVar('tw',   false, '', 'string');
    $facebook           = urldecode(html_entity_decode(urldecode(JRequest::getVar('fb',   false, '', 'string'))));
    $myspace            = JRequest::getVar('ms',   false, '', 'string');
    $editorcontent      = urldecode(JRequest::getVar('intro',false, '', 'string'));
    $sidebarcontent	= urldecode(JRequest::getVar('sidebar',false,'','string'));
    $gaSource           = JRequest::getVar('gaS',   false, '', 'string');
    $gaMedium           = JRequest::getVar('gaM',   false, '', 'string');
    $gaName             = JRequest::getVar('gaN',   false, '', 'string');
    $gaExcluded         = urldecode(html_entity_decode(urldecode(JRequest::getVar('gaE',   false, '', 'string'))));



    if (!$campaign_name) {      $campaign_name      = JRequest::getVar('campaign_name',     '', 'POST', 'string'); }
    if (!$subject){             $subject            = JRequest::getVar('subject',           '', 'POST', 'string'); }
    if (!$from_name){           $from_name          = JRequest::getVar('from_name',         $fName, 'POST', 'string'); }
    if (!$from_email){          $from_email         = JRequest::getVar('from_email',        $fMail, 'POST', 'string'); }
    if (!$reply_email){         $reply_email        = JRequest::getVar('reply_email',       $rMail, 'POST', 'string'); }
    if (!$confirmation_email){  $confirmation_email = JRequest::getVar('confirmation_email',$cMail, 'POST', 'string'); }
    if (!$listid){              $listid             = JRequest::getVar('listid',       array(), 'POST', 'array'); }
    if (!$articlesk2_pre){      $articlesk2_pre     = JRequest::getVar('artsk2',       array(), 'POST', 'array'); }
    if (!$toc){                 $toc                = JRequest::getVar('tableofcontents',   '', 'POST', 'string'); }
    if (!$toct){                $toct               = JRequest::getVar('tableofcontents_type', '', 'POST', 'string'); }
    if (!$pop){                 $pop                = JRequest::getVar('populararticles',false, 'POST', 'string'); }
    if (!$pex){                 $pex                = JRequest::getVar('pex',            false, 'POST');
		    if ($pex){  $pex                = explode(';', $pex); }
    }
    if (!$pin){                 $pin                = JRequest::getVar('pin',            false, 'POST');
		    if ($pin){  $pin                = explode(';', $pin); }
    }
    if (!$pk2){                 $pk2                = JRequest::getVar('populark2',        false, 'POST', 'string'); }
    if (!$pk2ex){               $pk2ex              = JRequest::getVar('pk2ex',            false, 'POST');
		   if ($pk2ex){ $pk2ex              = explode(';', $pk2ex); }
    }
    if (!$pk2in){               $pk2in              = JRequest::getVar('pk2in',            false, 'POST');
		   if ($pk2in){ $pk2in              = explode(';', $pk2in); }
    }
    if (!$pk2ex){               $pk2ex              = explode(';', JRequest::getVar('pk2ex',  '', 'POST', 'string'));}
    if (!$pk2in){               $pk2in              = explode(';', JRequest::getVar('pk2in',  '', 'POST', 'string'));}
    if (!$pk2o){                $pk2o               = JRequest::getVar('populark2_only',    '', 'POST', 'string'); }
    if (!$vm_sb){               $vm_sb              = JRequest::getVar('vm_sidebar',        '', 'POST', 'string'); }
    if (!$vmid){
	$vmid = array();
	$vmpr = array();
	$vmct = array();
	$vm_sb_products = JRequest::getVar('vm_sb_products',array(), 'POST', 'array');
	foreach($vm_sb_products as $v){
		$w = explode(';', $v);
		$vmid[] = $w[0];
		$vmpr[] = $w[1];
		$vmct[] = $w[2];
	    }
    }
    if (!$vmor){                $vmor               = JRequest::getVar('vm_sidebar_order',  '', 'POST', 'string'); }
    if (!$vmsp){                $vmsp               = JRequest::getVar('vm_sidebar_price',  '', 'POST', 'string'); }
    if (!$vmcf){                $vmcf               = JRequest::getVar('vm_sidebar_curr_first','', 'POST', 'string'); }
    if (!$vmimg){               $vmimg              = JRequest::getVar('vm_sidebar_img',    '', 'POST', 'string'); }
    if (!$vmlnk){               $vmlnk              = JRequest::getVar('vm_sidebar_link',   '', 'POST', 'string'); }
    if (!$vmsdesc){             $vmsdesc            = JRequest::getVar('vm_short_desc',     '', 'POST', 'string'); }
    if (!$vmdesc){              $vmdesc             = JRequest::getVar('vm_desc',           '', 'POST', 'string'); }
    if (!$template){            $template           = JRequest::getVar('template',          '', 'POST', 'string'); }
    if (!$twitter){             $twitter            = JRequest::getVar('twitter',$twitter_name, 'POST', 'string'); }
    if (!$facebook){            $facebook           = JRequest::getVar('facebook',    $fb_link, 'POST', 'string'); }
    if (!$myspace){             $myspace            = JRequest::getVar('myspace',$myspace_name, 'POST', 'string'); }
    if (!$editorcontent){       $editorcontent      = JRequest::getVar('intro',             '', 'POST', 'string', JREQUEST_ALLOWRAW); }
    if (!$sidebarcontent){      $sidebarcontent     = JRequest::getVar('sidebar',           '', 'POST', 'string', JREQUEST_ALLOWRAW); }
    if (!$gaSource){            $gaSource           = JRequest::getVar('gaSource','newsletter', 'POST', 'string'); }
    if (!$gaMedium){            $gaMedium           = JRequest::getVar('gaMedium',     'email', 'POST', 'string'); }
    if (!$gaName){              $gaName             = JRequest::getVar('gaName',            '', 'POST', 'string'); }
    if (!$gaExcluded){          $gaExcluded         = JRequest::getVar('gaExcluded', "twitter.com\nfacebook.com\nmyspace.com", 'POST', 'string'); }

    // Include js files
    $document = & JFactory::getDocument();
    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/preview.js');
    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/tablednd.js');
    $document->addScript(JURI::base().'components/com_joomailermailchimpintegration/assets/js/sorttable.js');
?>
<script language="javascript" type="text/javascript">
var baseUrl = '<?php echo JURI::base();?>';
var tmplUrl = "<?php echo JURI::root().'tmp/';?>";

<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>
Joomla.submitbutton = function(pressbutton) {
<?php } else { ?>
function submitbutton(pressbutton) {
<?php } ?>
    var astPipe = new RegExp("\\*\\|");
    var pipeAst = new RegExp("\\|\\*");
    if (document.adminForm.campaign_name.value == ""){
	alert('<?php echo JText::_( 'JM_CAMPAIGN_NAME_REQUIRED' ); ?>');
    } else if ( !check_special() ){
	alert('<?php echo JText::_( 'JM_CAMPAIGN_NAME_CONTAINS_SPECIAL_CHARACTERS' ); ?>');
    } else if (document.adminForm.subject.value == ""){
	alert('<?php echo JText::_( 'JM_PLEASE_ENTER_A_SUBJECT' ); ?>');
    } else if (document.adminForm.subject.value.match(astPipe) || document.adminForm.subject.value.match(pipeAst)){
	alert('<?php echo JText::_( 'JM_NO_MERGE_TAGS_IN_SUBJECT' ); ?>');
    } else if (document.adminForm.from_name.value == ""){
	alert('<?php echo JText::_( 'JM_PLEASE_ENTER_A_FROM_NAME' ); ?>');
    } else if (document.adminForm.from_email.value == ""){
	alert('<?php echo JText::_( 'JM_PLEASE_ENTER_A_FROM_EMAIL' ); ?>');
    } else if (document.adminForm.reply_email.value == ""){
	alert('<?php echo JText::_( 'JM_PLEASE_ENTER_A_REPLY_EMAIL' ); ?>');
    } else if (document.adminForm.confirmation_email.value == ""){
	alert('<?php echo JText::_( 'JM_PLEASE_ENTER_A_CONFIRMATION_EMAIL' ); ?>');
    } else if (document.adminForm.template.value == ""){
	alert('<?php echo JText::_( 'JM_PLEASE_SELECT_A_TEMPLATE' ); ?>');
    } else {
	joomailermailchimpintegration_ajax_loader();
	<?php
	$editor =& JFactory::getEditor($editortype);
	echo $editor->save( 'intro' );
	$sidebareditor =& JFactory::getEditor($editortype);
	echo $sidebareditor->save( 'sidebar' );
	?>
	<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
    }
}

function check_special()  {
    var regex=/^[0-9A-Za-z\s]+$/; //^[a-zA-z]+$/;
    if (regex.test(document.adminForm.campaign_name.value)) {
	return true;
    } else {
	return false;
    }
}

function personalizeSubject() {
    var subject = 'subject';
    var newVal  = $(subject).value + $('personalize').value;
    $(subject).value=newVal;
    $(subject).focus();
}

function insertMergeTag( editor, tag ){
    jInsertEditorText(tag, editor);
}

function preview(){
    var obj = document.getElementById('adminForm');
    var astPipe = new RegExp("\\*\\|");
    var pipeAst = new RegExp("\\|\\*");
    if (document.adminForm.campaign_name.value == ""){
	alert('<?php echo JText::_( 'JM_CAMPAIGN_NAME_REQUIRED' ); ?>');
    } else if ( !check_special() ){
	alert('<?php echo JText::_( 'JM_CAMPAIGN_NAME_CONTAINS_SPECIAL_CHARACTERS' ); ?>');
    } else if (document.adminForm.subject.value == ""){
	alert('<?php echo JText::_( 'JM_PLEASE_ENTER_A_SUBJECT' ); ?>');
    } else if (document.adminForm.subject.value.match(astPipe) || document.adminForm.subject.value.match(pipeAst)){
	alert('<?php echo JText::_( 'JM_NO_MERGE_TAGS_IN_SUBJECT' ); ?>');
    } else if (document.adminForm.template.value == ""){
	alert('<?php echo JText::_( 'JM_PLEASE_SELECT_A_TEMPLATE' ); ?>');
    } else {

	document.getElementById('ajax-spin').style.background = "url(components/com_joomailermailchimpintegration/assets/images/loader_16.gif) no-repeat 0 2px";
	document.getElementById('preview').style.opacity = "0.3";
	window.location = '#preview';

	var intro = <?php echo $editor->getContent( 'intro' ); ?>
	var sidebar = <?php echo $sidebareditor->getContent( 'sidebar' ); ?>

	AJAXpreview(obj, intro, sidebar);
    }
}

window.addEvent('load', function() {
    <?php if ( $vm_installed ) { ?>
    var vmSlide    = new Fx.Slide('vm_productlist');
    <?php } ?>
    var popSlide   = new Fx.Slide('popSlide');
    <?php if($this->K2Installed){ ?>
    var popk2Slide = new Fx.Slide('popk2Slide');
    <?php } ?>
    <?php if($vm_installed && !$vm_sb){ ?>
    vmSlide.toggle();
    <?php } ?>
    <?php if(!$pop){ ?>
    popSlide.toggle();
    <?php } ?>
    <?php if(!$pk2 && $this->K2Installed){ ?>
    popk2Slide.toggle();
    <?php } ?>
    <?php
    $browser  = JBrowser::getInstance();
    $bName    = $browser->getBrowser();
    if($bName != 'msie'){
    ?>
    <?php if ( $vm_installed ) { ?>
    $('vm_sidebar_label').addEvent('click', function(e){
	if($('vm_sidebar').checked==true ){
	    vmSlide.slideIn();
	} else {
	    vmSlide.slideOut();
	}
    });
    <?php } ?>
    $('populararticles_label').addEvent('click', function(e){
	if($('populararticles').checked==true ){
	    popSlide.slideIn();
	} else {
	    popSlide.slideOut();
	}
    });
    <?php if($this->K2Installed){ ?>
    $('populark2_label').addEvent('click', function(e){
	if($('populark2').checked==true ){
	    popk2Slide.slideIn();
	} else {
	    popk2Slide.slideOut();
	}
    });
    <?php } ?>
    <?php
    } else {
    ?>
    <?php if ( $vm_installed ) { ?>
    $('vm_sidebar').addEvent('click', function(e){
	if($('vm_sidebar').checked==true ){
	    vmSlide.slideIn();
	} else {
	    vmSlide.slideOut();
	}
    });
    <?php } ?>
    $('populararticles').addEvent('click', function(e){
	if($('populararticles').checked==true ){
	    popSlide.slideIn();
	} else {
	    popSlide.slideOut();
	}
    });
    <?php if($this->K2Installed){ ?>
    $('populark2').addEvent('click', function(e){
	if($('populark2').checked==true ){
	    popk2Slide.slideIn();
	} else {
	    popk2Slide.slideOut();
	}
    });
    <?php } ?>
		
    <?php
    }
    ?>
    <?php if ( $vm_installed ) { ?>
    $('vm_toggle').addEvent('click', function(e){
	vmSlide.toggle();
    });
    <?php } ?>
});
window.addEvent('domready', function() {		
    $('campaign_name').addEvent('blur', function(e){
	if(document.getElementById('gaName')){
	    var cName = $('campaign_name').value;
	    cName = str_replace(' ','_', cName);
	    cName = str_replace('ä','ae', cName);
	    cName = str_replace('ö','oe', cName);
	    cName = str_replace('ü','ue', cName);
	    cName = str_replace('Ä','Ae', cName);
	    cName = str_replace('Ö','Oe', cName);
	    cName = str_replace('Ü','Ue', cName);
	    cName = str_replace('ß','ss', cName);
	    document.getElementById('gaName').value = cName;
	}
    });
    $('from_name').addEvent('keyup', function(e){
	this.value = str_replace('@','(at)', this.value);
	this.value = str_replace('"',' ', this.value);
	document.getElementById('from_name').value = this.value;
    });
});
	
function str_replace(search, replace, subject){
    var result = "";
    var  oldi = 0;
    for (i = subject.indexOf (search); i > -1; i = subject.indexOf (search, i)){
	result += subject.substring (oldi, i);
	result += replace;
	i += search.length;
	oldi = i;
    }
    return result + subject.substring (oldi, subject.length);
}


<?php /*	
window.addEvent('domready', function(){
	new Sortables($('articles'), {
		initialize: function(){
			var step = 0;
			this.elements.each(function(element, i){
				var color = [step, 82, 87].hsbToRgb();
				element.setStyle('background-color', color);
				element.setStyle('cursor', 'move');
				step = step + 35;
			});
		}
	});
});
*/ ?>

function calculate_price(ob) {
    var Recipients = 0;
    for (var i = 0; i < ob.options.length; i++) {
	if (ob.options[ i ].selected) {
	    Recipients += parseInt(document.getElementById(ob.options[ i ].value).value);
	}
    }

    document.getElementById('result').innerHTML = Recipients+ ' <?php echo JText::_('JM_SUBSCRIBERS');?> => '+Recipients+ '<?php echo ' '.JText::_('JM_CREDITS');?>';
    document.getElementById('result_title').style.display = '';
}
<?php /*
function setListNames(ob){
	var list_names = '';
	for (var i = 0; i < ob.options.length; i++) {
		if (ob.options[ i ].selected) {
			list_names += ob.options[ i ].text + ';';
		}
	}
	document.adminForm.list_names.value = list_names;
}
*/ ?>
function deselect( id ){
    var ob = document.getElementById(id);
    for (var i = 0; i < ob.options.length; i++) {
	ob.options[ i ].selected = false;
    }
}
</script>
<?php 
if ($this->core && ( $this->K2Installed || $this->jomsocial || $this->aec || $this->ambra)){
?>
<script language="javascript" type="text/javascript">
jQuery(document).ready(function() {
    <?php if($this->K2Installed){ ?>
    jQuery("#panel_2_content").slideUp();
    <?php } ?>
    <?php if($this->jomsocial){ ?>
    jQuery("#panel_3_content").slideUp();
    jQuery("#panel_4_content").slideUp();
    <?php } ?>
    <?php if($this->aec){ ?>
    jQuery("#panel_5_content").slideUp();
    <?php } ?>
    <?php if($this->ambra){ ?>
    jQuery("#panel_6_content").slideUp();
    <?php } ?>

    jQuery("#panel_1").click( function(){
	if(jQuery(this).hasClass("jpane-toggler-down")){
	    jQuery(this).removeClass("jpane-toggler-down");
	    jQuery("#panel_1_content").slideUp();
	    <?php if($this->K2Installed){ ?>
	    jQuery("#panel_2").addClass("jpane-toggler-down");
	    jQuery("#panel_2_content").slideDown();
	    jQuery("#panel_3_content").slideUp();
	    jQuery("#panel_4_content").slideUp();
	    jQuery("#panel_5_content").slideUp();
	    jQuery("#panel_6_content").slideUp();
	    <?php } else if($this->jomsocial){ ?>
	    jQuery("#panel_3").addClass("jpane-toggler-down");
	    jQuery("#panel_3_content").slideDown();
	    jQuery("#panel_4_content").slideUp();
	    jQuery("#panel_5_content").slideUp();
	    jQuery("#panel_6_content").slideUp();
	    <?php } else if($this->aec){ ?>
	    jQuery("#panel_5").addClass("jpane-toggler-down");
	    jQuery("#panel_5_content").slideDown();
	    jQuery("#panel_6_content").slideUp();
	    <?php } else if($this->ambra){ ?>
	    jQuery("#panel_6").addClass("jpane-toggler-down");
	    jQuery("#panel_6_content").slideDown();
	    <?php } ?>
	} else {
	    jQuery(this).addClass("jpane-toggler-down");
	    jQuery("#panel_1_content").slideDown();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    jQuery("#panel_4_content").slideUp();
	    jQuery("#panel_5_content").slideUp();
	    jQuery("#panel_6_content").slideUp();
	    jQuery("#panel_2").removeClass("jpane-toggler-down");
	    jQuery("#panel_3").removeClass("jpane-toggler-down");
	    jQuery("#panel_4").removeClass("jpane-toggler-down");
	    jQuery("#panel_5").removeClass("jpane-toggler-down");
	    jQuery("#panel_6").removeClass("jpane-toggler-down");
	}
    });
    jQuery("#panel_2").click( function(){
	if(jQuery(this).hasClass("jpane-toggler-down")){
	    jQuery(this).removeClass("jpane-toggler-down");
	    jQuery("#panel_2_content").slideUp();
	    <?php if($this->jomsocial){ ?>
	    jQuery("#panel_3").addClass("jpane-toggler-down");
	    jQuery("#panel_3_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    <?php } else if($this->aec){ ?>
	    jQuery("#panel_5").addClass("jpane-toggler-down");
	    jQuery("#panel_5_content").slideDown();
	    jQuery("#panel_6_content").slideUp();
	    <?php } else if($this->ambra){ ?>
	    jQuery("#panel_6").addClass("jpane-toggler-down");
	    jQuery("#panel_6_content").slideDown();
	    <?php } else { ?>
	    jQuery("#panel_1").addClass("jpane-toggler-down");
	    jQuery("#panel_1_content").slideDown();
	    <?php } ?>
	} else {
	    jQuery(this).addClass("jpane-toggler-down");
	    jQuery("#panel_2_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    jQuery("#panel_4_content").slideUp();
	    jQuery("#panel_5_content").slideUp();
	    jQuery("#panel_6_content").slideUp();
	    jQuery("#panel_1").removeClass("jpane-toggler-down");
	    jQuery("#panel_3").removeClass("jpane-toggler-down");
	    jQuery("#panel_4").removeClass("jpane-toggler-down");
	    jQuery("#panel_5").removeClass("jpane-toggler-down");
	    jQuery("#panel_6").removeClass("jpane-toggler-down");
	}
    });
    jQuery("#panel_3").click( function(){
	if(jQuery(this).hasClass("jpane-toggler-down")){
	    jQuery(this).removeClass("jpane-toggler-down");
	    jQuery("#panel_3_content").slideUp();
	    jQuery("#panel_4").addClass("jpane-toggler-down");
	    jQuery("#panel_4_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_5_content").slideUp();
	    jQuery("#panel_6_content").slideUp();
	} else {
	    jQuery(this).addClass("jpane-toggler-down");
	    jQuery("#panel_3_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_4_content").slideUp();
	    jQuery("#panel_5_content").slideUp();
	    jQuery("#panel_6_content").slideUp();
	    jQuery("#panel_1").removeClass("jpane-toggler-down");
	    jQuery("#panel_2").removeClass("jpane-toggler-down");
	    jQuery("#panel_4").removeClass("jpane-toggler-down");
	    jQuery("#panel_5").removeClass("jpane-toggler-down");
	    jQuery("#panel_6").removeClass("jpane-toggler-down");
	}
    });
    jQuery("#panel_4").click( function(){
	if(jQuery(this).hasClass("jpane-toggler-down")){
	    jQuery(this).removeClass("jpane-toggler-down");
	    jQuery("#panel_4_content").slideUp();
	    <?php if($this->aec){ ?>
	    jQuery("#panel_5").addClass("jpane-toggler-down");
	    jQuery("#panel_5_content").slideDown();
	    jQuery("#panel_3_content").slideUp();
	    <?php } else if($this->ambra){ ?>
	    jQuery("#panel_6").addClass("jpane-toggler-down");
	    jQuery("#panel_6_content").slideDown();
	    jQuery("#panel_3_content").slideUp();
	    <?php } else { ?>
	    jQuery("#panel_3").addClass("jpane-toggler-down");
	    jQuery("#panel_3_content").slideDown();
	    <?php } ?>
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	} else {
	    jQuery(this).addClass("jpane-toggler-down");
	    jQuery("#panel_4_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    jQuery("#panel_5_content").slideUp();
	    jQuery("#panel_6_content").slideUp();
	    jQuery("#panel_1").removeClass("jpane-toggler-down");
	    jQuery("#panel_2").removeClass("jpane-toggler-down");
	    jQuery("#panel_3").removeClass("jpane-toggler-down");
	    jQuery("#panel_5").removeClass("jpane-toggler-down");
	    jQuery("#panel_6").removeClass("jpane-toggler-down");
	}
    });
    jQuery("#panel_5").click( function(){
	if(jQuery(this).hasClass("jpane-toggler-down")){
	    jQuery(this).removeClass("jpane-toggler-down");
	    jQuery("#panel_5_content").slideUp();
	    <?php if($this->ambra){ ?>
	    jQuery("#panel_6").addClass("jpane-toggler-down");
	    jQuery("#panel_6_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    jQuery("#panel_4_content").slideUp();
	    <?php } else if($this->jomsocial) { ?>
	    jQuery("#panel_4").addClass("jpane-toggler-down");
	    jQuery("#panel_4_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    <?php } else if($this->K2Installed) { ?>
	    jQuery("#panel_2").addClass("jpane-toggler-down");
	    jQuery("#panel_2_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    <?php } else { ?>
	    jQuery("#panel_1").addClass("jpane-toggler-down");
	    jQuery("#panel_1_content").slideDown();
	    <?php } ?>
	} else {
	    jQuery(this).addClass("jpane-toggler-down");
	    jQuery("#panel_5_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    jQuery("#panel_4_content").slideUp();
	    jQuery("#panel_6_content").slideUp();
	    jQuery("#panel_1").removeClass("jpane-toggler-down");
	    jQuery("#panel_2").removeClass("jpane-toggler-down");
	    jQuery("#panel_3").removeClass("jpane-toggler-down");
	    jQuery("#panel_4").removeClass("jpane-toggler-down");
	    jQuery("#panel_6").removeClass("jpane-toggler-down");
	}
    });
    jQuery("#panel_6").click( function(){
	if(jQuery(this).hasClass("jpane-toggler-down")){
	    jQuery(this).removeClass("jpane-toggler-down");
	    jQuery("#panel_6_content").slideUp();
	    <?php if($this->aec) { ?>
	    jQuery("#panel_5").addClass("jpane-toggler-down");
	    jQuery("#panel_5_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    <?php } else if($this->jomsocial) { ?>
	    jQuery("#panel_4").addClass("jpane-toggler-down");
	    jQuery("#panel_4_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    <?php } else if($this->K2Installed) { ?>
	    jQuery("#panel_2").addClass("jpane-toggler-down");
	    jQuery("#panel_2_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    <?php } else { ?>
	    jQuery("#panel_1").addClass("jpane-toggler-down");
	    jQuery("#panel_1_content").slideDown();
	    <?php } ?>
	} else {
	    jQuery(this).addClass("jpane-toggler-down");
	    jQuery("#panel_6_content").slideDown();
	    jQuery("#panel_1_content").slideUp();
	    jQuery("#panel_2_content").slideUp();
	    jQuery("#panel_3_content").slideUp();
	    jQuery("#panel_4_content").slideUp();
	    jQuery("#panel_5_content").slideUp();
	    jQuery("#panel_1").removeClass("jpane-toggler-down");
	    jQuery("#panel_2").removeClass("jpane-toggler-down");
	    jQuery("#panel_3").removeClass("jpane-toggler-down");
	    jQuery("#panel_4").removeClass("jpane-toggler-down");
	    jQuery("#panel_5").removeClass("jpane-toggler-down");
	}
    });
		
});
</script>
<?php } ?>

<div id="create">
<form action="index.php?option=com_joomailermailchimpintegration&view=create" method="post" name="adminForm" id="adminForm">

<?php  // no lists created yet?
if ( !$this->lists ) {
echo JText::_( 'JM_CREATE_A_LIST' );
$i = $n = 1;
} else {
?>
<div id="buttons">
	<div id="previewButton">
		<span id="ajax-spin" style=""></span>
		<a class="JMbuttonOrange" href="javascript:preview();">
		    <span></span>
		    <?php echo JText::_( 'JM_PREVIEW' );?>
		</a>
	</div>
	<div id="saveButton">
	<a class="toolbar JMbuttonBlue" onclick="javascript: <?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitbutton('save')" href="#">
	    <span></span>
	    <?php echo JText::_('JM_SAVE_DRAFT'); ?>
	</a>
	</div>
	<div style="clear:both;"></div>
</div>
<?php
$search = $mainframe->getUserStateFromRequest( "search",'search','','string' );
$sec_filter = JRequest::getVar('sec_filter');
$cat_filter = JRequest::getVar('cat_filter');
$k2cat_filter = JRequest::getVar('k2cat_filter');
$k2Limit = JRequest::getVar('k2Limit');
$k2Limitstart = JRequest::getVar('k2Limitstart');
$jsLimit = JRequest::getVar('jsLimit');
$jsLimitstart = JRequest::getVar('jsLimitstart');
if($search||$sec_filter||$cat_filter||$k2cat_filter||$jsLimit) {
	$offset = 1;
} else {
	$offset = 0;
}
$offset = JRequest::getVar( 'offset', $offset);

$tabs =& JPane::getInstance( 'tabs', array('startOffset'=>$offset) );
echo $tabs->startPane( 'create_campaign' );
echo $tabs->startPanel( JText::_( 'JM_MAIN_SETTINGS' ), 'create_main', 'h4', 'text-transform:none;' );
?>
<div class="col100">
		<table class="admintable" width="100%">
		<tr>
			<td width="100" align="right" class="key">
				<label for="campaign_name">
					<?php echo JText::_( 'JM_CAMPAIGN_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="campaign_name" id="campaign_name" <?php echo (JRequest::getVar('action','')=='edit')?'readonly="readonly" onfocus="$(\'subject\').focus()"':'';?> size="48" maxlength="250" value="<?php echo $campaign_name; ?>" style="float:left;margin-right: 20px;<?php echo (JRequest::getVar('action','')=='edit')?'color:#AFAFAF;':'';?>" />
				<div class="inputInfo"><?php echo JText::_( 'JM_CAMPAIGN_NAME_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="subject">
					<?php echo JText::_( 'JM_SUBJECT' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="subject" id="subject" size="48" maxlength="250" value="<?php echo $subject; ?>" style="float:left;margin-right: 20px;" /> 
				<div class="inputInfo"><?php echo JText::_( 'JM_SUBJECT_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="from_name">
					<?php echo JText::_( 'JM_FROM_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="from_name" id="from_name" size="48" maxlength="250" value="<?php echo $from_name; ?>" style="float:left;margin-right: 20px;" />
				<div class="inputInfo"><?php echo JText::_( 'JM_FROM_NAME_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="from_email">
					<?php echo JText::_( 'JM_FROM_EMAIL' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="from_email" id="from_email" size="48" maxlength="250" value="<?php echo $from_email; ?>" style="float:left;margin-right: 20px;" />
				<div class="inputInfo"><?php echo JText::_( 'JM_FROM_EMAIL_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="reply_email">
					<?php echo JText::_( 'JM_REPLY_EMAIL' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="reply_email" id="reply_email" size="48" maxlength="250" value="<?php echo $reply_email; ?>" style="float:left;margin-right: 20px;" />
				<div class="inputInfo"><?php echo JText::_( 'JM_REPLY_EMAIL_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="confirmation_email">
					<?php echo JText::_( 'JM_CONFIRMATION_EMAIL' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="confirmation_email" id="confirmation_email" size="48" maxlength="250" value="<?php echo $confirmation_email; ?>" style="float:left;margin-right: 20px;" />
				<div class="inputInfo"><?php echo JText::_( 'JM_CONFIRMATION_EMAIL_INFO' ); ?></div>
			</td>
		</tr>
	</table>
</div>
<?php
echo $tabs->endPanel();
echo $tabs->startPanel( JText::_( 'JM_CONTENT' ), 'select_content', 'h4', 'text-transform:none;' );
?>
<div class="col100">
		<table class="admintable" width="100%">
		<tr>
			<td>
				<h3 style="margin:0;"><?php echo JText::_( 'JM_CHOOSE_TEMPLATE' ); ?></h3>
				<?php
				$template_folders = Jfolder::listFolderTree( '../administrator/components/com_joomailermailchimpintegration/templates/' , '', 1);
				?>
				<select name="template" id="template" style="width: 210px;font-size:14px;margin:5px 0 0 0;">
					<?php /*
					<option value=""><?php echo JText::_( 'JM_CHOOSE_TEMPLATE' ); ?></option>
					*/ ?>
					<?php
						foreach ( $template_folders as $tf ){
						if ( $tf['name'] == $template ) { $sel = ' selected="selected"'; } else { $sel = ''; }
						?>
						<option value="<?php echo $tf['name'];?>"<?php echo $sel;?>><?php echo $tf['name'];?></option>
						<?php
						}
						?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<?php
					echo '<h3>'.JText::_( 'JM_INTRO_TEXT' ).'</h3>';
					$editor =& JFactory::getEditor($editortype);
					$buttons2exclude = array( 'pagebreak', 'readmore' );
					echo '<div style="float:left;margin-right: 15px;">'.$editor->display('intro', $editorcontent, '550', '200', '60', '20', $buttons2exclude ).'</div>';
					echo '<div style="float:left;margin:5px;">'.JHTML::tooltip( JText::_( 'JM_TOOLTIP_INTRO' ), JText::_( 'JM_INTRO' ), $tt_image, '' );
					?>
					<br />
					<br />
					<?php echo JText::_('JM_MERGE_TAGS_AVAILABLE');?> <a href="http://www.mailchimp.com/resources/merge/" title="<?php echo JText::_( 'JM_MERGE_TAG_CHEATSHEET' ); ?>" class="modal" rel="{handler: 'iframe', size: {x: 980, y: 550} }" style="margin:5px;position:relative;top:3px;"><img src="<?php echo $tt_image_abs;?>" /></a>
					<br />
					<br />
					<select onchange="insertMergeTag( 'intro', this.value );this.options[0].selected=true;" style="float:none;">
						<option value=""><?php echo JText::_('JM_INSERT_MERGE_TAG');?></option>
						<option value="*|FNAME|*"><?php echo JText::_('JM_FIRST_NAME');?></option>
						<option value="*|LNAME|*"><?php echo JText::_('JM_LAST_NAME');?></option>
						<option value="*|DATE:d/m/y|*"><?php echo JText::_('JM_DATE');?></option>
						<option value="*|MC:SUBJECT|*"><?php echo JText::_('JM_SUBJECT');?></option>
						<option value="*|EMAIL|*"><?php echo JText::_('JM_RECIPIENTS_EMAIL');?></option>
					</select>
					<br />
					<br />
					<?php if($this->merge){ ?>
						<?php echo JText::_('JM_LIST_SPECIFIC_MERGE_TAGS');?> <?php echo JHTML::tooltip( JText::_( 'JM_LIST_SPECIFIC_MERGE_TAGS_INFO' ), JText::_( 'JM_LIST_SPECIFIC_MERGE_TAGS' ), $tt_image.'" style="margin:0 5px;position:relative;top:3px;"', '' );?>
						<br />
						<br />
						<select onchange="insertMergeTag( 'intro', this.value );this.options[0].selected=true;">
						<option value=""><?php echo JText::_('JM_INSERT_MERGE_TAG');?></option>
						<?php
							foreach($this->merge as $k => $v){
								echo '<optgroup label="'.JText::_('JM_LIST').': '.$k.'">';
								foreach($v as $tag){
									echo '<option value="*|'.$tag['tag'].'|*">'.$tag['name'].'</option>';
								}
								echo '</optgroup>';
							}
						?>
						</select>
					<?php } ?>
					</div>
					<?php
				?>
			</td>
		</tr>
<?php  // preview button
/* 
		<tr>
			<td width="100" align="right" class="key" valign="top"></td>
			<td>
			<div class="preview-button">
					<div style="width: 150px; margin: auto;">
						<input type="button" name="button" value="<?php echo JText::_( 'Preview' );?>" onclick="javascript:preview(document.adminForm); window.location = '#preview';" />
						<span id="ajax-spin1" style=""></span>
					</div>
				</div>
			</td>
		</tr>
*/ ?>
		<tr>
			<td>
<?php
// Joomla core article list
			if ($this->core && ($this->K2Installed || $this->jomsocial || $this->aec || $this->ambra)){
//			$pane =& JPane::getInstance('sliders', array('startOffset'=>0, 'startTransition' => true));    // 'tabs' or 'sliders'
//			echo $pane->startPane( 'pane' );
//			echo $pane->startPanel( JText::_( 'Joomla core' ), 'panel_1' );
			?>
			<div id="contentSlider" class="pane-sliders">
				<div class="panel">
				<h3 class="jpane-toggler jpane-toggler-down title" id="panel_1">
					<span><?php echo JText::_( 'JM_JOOMLA_CORE' );?></span></h3>
						<div class="jpane-slider content" id="panel_1_content">
			<?php
			}
			if ($this->core){
			?>
			<table>
				<tr>
					<td nowrap="nowrap">
						<?php echo JText::_( 'JM_FILTERS' ); ?>:
						<input type="text" name="search" id="search" value="<?php echo $mainframe->getUserStateFromRequest( "search", 'search', '', 'string' );?>" class="text_area" style="float:none;" onchange="document.adminForm.submit();" />
						<button onclick="joomailermailchimpintegration_ajax_loader();this.form.submit();"><?php echo JText::_( 'JM_GO' ); ?></button>
						<button onclick="joomailermailchimpintegration_ajax_loader();document.getElementById('search').value='';this.form.getElementById('filter_type').value='0';this.form.getElementById('filter_logged').value='0';this.form.submit();"><?php echo JText::_( 'JM_RESET' ); ?></button>
					</td>
					<td align="center" width="100%">
					</td>
					<td nowrap="nowrap">
						<?php echo (version_compare(JVERSION,'1.6.0','ge')) ? '' : $this->secDropDown;?>
						<?php echo $this->catDropDown;?>
					</td>
				</tr>
			</table>
			<script language="javascript" type="text/javascript">
				window.addEvent('load', function() { 
					var table1 = document.getElementById("core-articles");
					var tableDnD = new TableDnD();
					tableDnD.init(table1);
					tableDnD.onDrop = function(table, droppedRow) {
										var adminForm = document.getElementById('adminForm');
										var coreOrder = '';
										for (i=0; i<adminForm.article.length; i++){
											coreOrder += adminForm.article[i].value + ';';
										}
										document.getElementById('coreOrder').value = coreOrder;
										}
				});
			</script>
			<table class="adminlist sortable sorted" id="core-articles">
				<thead>
					<tr>
						<th width="20" nowrap="nowrap">ID</th>
						<th width="40" nowrap="nowrap" class="sorttable_nosort"><?php 
							echo JText::_( 'JM_INCLUDE' );
						?></th>
                        <th width="50" nowrap="nowrap" class="sorttable_nosort"><?php 
							echo JText::_( 'JM_TEXT' ).'  ';
							echo JHTML::tooltip( JText::_( 'JM_TOOLTIP_TEXT' ), JText::_( 'JM_TEXT' ), $tt_image, '' );
						?></th>
                        <th width="60" nowrap="nowrap" class="sorttable_nosort"><?php 
							echo JText::_( 'JM_READ_MORE' ).'  ';
							echo JHTML::tooltip( JText::_( 'JM_TOOLTIP_READMORE' ), JText::_( 'JM_READ_MORE' ), $tt_image, '' );
						?></th>
                        <th nowrap="nowrap"><?php 
							echo JText::_( 'JM_TITLE' ).'  ';
							echo JHTML::tooltip( JText::_( 'JM_TOOLTIP_TITLE' ), JText::_( 'JM_TITLE' ), $tt_image, '' );
						?></th>
					    <?php if ( ! version_compare(JVERSION,'1.6.0','ge')) { ?>
						<th width="80" nowrap="nowrap"><?php echo JText::_( 'JM_SECTION' ); ?></th>
					    <?php } ?>
						<th width="80" nowrap="nowrap"><?php echo JText::_( 'JM_CATEGORY' ); ?></th>
						<th width="120" nowrap="nowrap"><?php echo JText::_( 'JM_AUTHOR' ); ?></th>
						<th width="80" nowrap="nowrap"><?php echo JText::_( 'JM_DATE' ); ?></th>
					</tr>
				</thead>
				<tfoot>
						<tr>
							<td colspan="9">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
				</tfoot>
				<tbody id="articles">
				<?php
				$k=0;
				$coreOrder = '';
				foreach ( $this->items as $article ) {
					if( in_array($article->id, $articles_pre) ) { $checked = 'checked="checked"'; } else { $checked = ''; }
					echo '<tr class="row'.$k.'">';
					echo '<td align="center">'.$article->id.'</td>';
					echo '<td align="center"><input type="checkbox" name="article[]" id="article" value="'.$article->id.'" '.$checked.'/></td>';
					echo '<td>';
					echo '<table><tr><td align="center" style="padding:0;">';
					echo '<label for="article_full_'.$article->id.'">'.JText::_( 'JM_INTRO' ).'</label>';
					echo '</td><td>';
					echo '<input type="radio" name="article_full_'.$article->id.'" id="article_full_'.$article->id.'" value="0" checked="checked">';
					echo '</td></tr><tr><td style="padding:0;">';
					echo '<label for="article_full_'.$article->id.'">'.JText::_( 'JM_FULL' ).'</label>';
					echo '</td><td>';
					echo '<input type="radio" name="article_full_'.$article->id.'" id="article_full_'.$article->id.'" value="1">';
					echo '</td></tr></table>';
					echo '</td>';
					echo '<td align="center"><input type="checkbox" name="readmore_'.$article->id.'" id="readmore_'.$article->id.'" value="1" checked="checked"></td>';
					echo '<td>'.$article->title.'</td>';
					if ( ! version_compare(JVERSION,'1.6.0','ge')) {
					echo '<td align="center">'.$article->section.'</td>';
					}
					echo '<td align="center">'.JText::_( $article->category ).'</td>';
					echo '<td align="center">'.$article->name.'</td>';
					echo '<td align="center">'.substr($article->created, 0, -9 ).'</td>';
					echo '</tr>';
					$coreOrder .= $article->id.';';
					$k = ($k)? 0:1;
				}
				?>
					 </tbody>
			 </table>
		<?php
		}
		if ($this->core && ( $this->K2Installed || $this->jomsocial || $this->aec || $this->ambra)){
		//echo $pane->endPanel();
		echo '</div></div>';
		}

// K2 article list
		if ($this->K2Installed){
		    if ($this->core && ($this->K2Installed || $this->jomsocial || $this->aec || $this->ambra)){
			?>
			<div class="panel">
			    <h3 class="jpane-toggler title" id="panel_2">
				<span><?php echo JText::_( 'K2' );?></span></h3>
				<div class="jpane-slider content" id="panel_2_content">
			<?php
		    }
		?>
		<script language="javascript" type="text/javascript">
			window.addEvent('load', function() { 
				var table2 = document.getElementById("k2-articles");
				var tableDnD = new TableDnD();
				tableDnD.init(table2);
				tableDnD.onDrop = function(table, droppedRow) {
										var adminForm = document.getElementById('adminForm');
										var coreOrder = '';
										for (i=0; i<adminForm.k2article.length; i++){
											coreOrder += adminForm.k2article[i].value + ';';
										}
										document.getElementById('k2Order').value = coreOrder;
										}
			});
		</script>

		<table>
			<tr>
				<td nowrap="nowrap">
					<?php echo JText::_( 'JM_FILTERS' ); ?>:
					<input type="text" name="searchK2" id="searchK2" value="<?php echo JRequest::getVar('searchK2', '' );?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="joomailermailchimpintegration_ajax_loader();this.form.submit();"><?php echo JText::_( 'JM_GO' ); ?></button>
					<button onclick="joomailermailchimpintegration_ajax_loader();document.getElementById('searchK2').value='';document.getElementById('k2Order').value='';this.form.submit();"><?php echo JText::_( 'JM_RESET' ); ?></button>
				</td>
				<td align="center" width="100%">
				</td>
				<td nowrap="nowrap">
					<?php if($this->K2Installed) { echo $this->k2catDropDown; } ?>
				</td>
			</tr>
		</table>
		
		<table class="adminlist sortable" id="k2-articles">
		<thead>
			<tr>
				<th width="20" nowrap="nowrap">ID</th>
				<th width="40" nowrap="nowrap" class="sorttable_nosort"><?php echo JText::_( 'JM_INCLUDE' ); ?></th>
				<th width="50" nowrap="nowrap" class="sorttable_nosort"><?php echo JText::_( 'JM_TEXT' ); ?></th>
				<th width="60" nowrap="nowrap" class="sorttable_nosort"><?php echo JText::_( 'JM_READ_MORE' ); ?></th>
				<th nowrap="nowrap"><?php echo JText::_( 'JM_TITLE' ); ?></th>
				<th width="80" nowrap="nowrap"><?php echo JText::_( 'JM_CATEGORY' ); ?></th>
				<th width="120" nowrap="nowrap"><?php echo JText::_( 'JM_AUTHOR' ); ?></th>
				<th width="80" nowrap="nowrap"><?php echo JText::_( 'JM_DATE' ); ?></th>
			</tr>
		</thead>
		<tfoot>
		    <tr>
			<td colspan="9">
			    <div class="pagination">
			    <div class="limit">
			    <?php echo JText::_('Display Num');?>
			    <?php $k2Limit = JRequest::getVar('k2Limit', $mainframe->getCfg('list_limit'), '', 'int'); ?>
			    <select name="k2Limit" id="k2Limit" onchange="submitform();">
				<option value="5" <?php if($k2Limit==5){echo 'selected="selected"';}?>>5</option>
				<option value="10" <?php if($k2Limit==10){echo 'selected="selected"';}?>>10</option>
				<option value="15" <?php if($k2Limit==15){echo 'selected="selected"';}?>>15</option>
				<option value="20" <?php if($k2Limit==20){echo 'selected="selected"';}?>>20</option>
				<option value="25" <?php if($k2Limit==25){echo 'selected="selected"';}?>>25</option>
				<option value="30" <?php if($k2Limit==30){echo 'selected="selected"';}?>>30</option>
				<option value="50" <?php if($k2Limit==50){echo 'selected="selected"';}?>>50</option>
				<option value="100" <?php if($k2Limit==100){echo 'selected="selected"';}?>>100</option>
				<option value="-1" <?php if($k2Limit==-1){echo 'selected="selected"';}?>><?php echo JText::_('all');?></option>
			    </select>
			    </div>
			    <?php
			    if($k2Limit < $this->K2Total ){
				$previous = (($k2Limitstart - $k2Limit)<0) ? 0 : ($k2Limitstart - $k2Limit);
				$next     = (($k2Limitstart + $k2Limit)>$this->K2Total) ? $k2Limitstart : ($k2Limitstart + $k2Limit);

				echo '<div class="button2-right"><div class="prev">';
				echo '<a href="javascript:document.adminForm.k2Limitstart.value='.$previous.'; submitform();">'.JText::_('prev').'</a>';
				echo '</div></div>';
				echo '<div class="button2-left"><div class="next">';
				echo '<a href="javascript:document.adminForm.k2Limitstart.value='.$next.'; submitform();">'.JText::_('next').'</a> ';
				echo '</div></div>';
				echo '<div class="limit">';

				echo JText::sprintf('JPAGE_CURRENT_OF_TOTAL', ceil(($k2Limitstart+1) / $k2Limit), ceil( $this->K2Total / $k2Limit));
				echo '</div>';
			    }
			    ?>
			    <input type="hidden" name="k2Limitstart" value="0" />
			    </div>
			</td>
		    </tr>
		</tfoot>
 
		<?php
		$k=0;
		$k2Order = '';
		foreach ( $this->k2 as $article ) {
			if( in_array($article->id, $articlesk2_pre) ) { $checked = 'checked="checked"'; } else { $checked = ''; }
			echo '<tr class="row'.$k.'">';
			echo '<td align="center">'.$article->id.'</td>';
			echo '<td align="center"><input type="checkbox" name="k2article[]" id="k2article" value="'.$article->id.'" '.$checked.'/></td>';
			echo '<td>';
			echo '<table><tr><td align="center" style="padding:0;">';
			echo '<label for="k2article_full_'.$article->id.'">'.JText::_( 'JM_INTRO' ).'</label>';
			echo '</td><td>';
			echo '<input type="radio" name="k2article_full_'.$article->id.'" id="k2article_full_'.$article->id.'" value="0" checked="checked">';
			echo '</td></tr><tr><td style="padding:0;">';
			echo '<label for="k2article_full_'.$article->id.'">'.JText::_( 'JM_FULL' ).'</label>';
			echo '</td><td>';
			echo '<input type="radio" name="k2article_full_'.$article->id.'" id="k2article_full_'.$article->id.'" value="1">';
			echo '</td></tr></table>';
			echo '</td>';
			echo '<td align="center"><input type="checkbox" name="k2readmore_'.$article->id.'" id="k2readmore_'.$article->id.'" value="1" checked="checked"></td>';
			echo '<td>'.$article->title.'</td>';
			echo '<td align="center">'.$article->category.'</td>';
			echo '<td align="center">'.$article->name.'</td>';
			echo '<td align="center">'.substr($article->created, 0, -9 ).'</td>';
			echo '</tr>';
			$k2Order .= $article->id.';';
			$k = ($k)? 0:1;
		}?>
				</table>
		<?php
		echo '</div></div>';
		}
		
		// JomSocial
		if($this->jomsocial){
			if ($this->core && ( $this->K2Installed || $this->jomsocial || $this->aec || $this->ambra)){
				?>
				<div class="panel">
					<h3 class="jpane-toggler title" id="panel_3">
						<span><?php echo JText::_( 'JM_JOMSOCIAL_PROFILES' );?></span></h3>
						<div class="jpane-slider content" id="panel_3_content">
				<?php
			}
			?>
			<table>
				<tr>
					<td><?php echo JText::_('JM_SELECT_PROFILE_FIELDS');?>: </td>
					<td>
					<select name="jsProfileFields[]" id="jsProfileFields" multiple="multiple" size="5">
					<?php
						foreach($this->jsFields as $field){
							if( in_array($field->id, $jsf_pre) ) { $selected = 'selected="selected"'; } else { $selected = ''; }
							echo '<option value="'.$field->id.'" '.$selected.'>'.$field->name.'</option>';
						}
					?>
					</select>
					</td>
					<td>Please make sure to respect the user's privacy settings as you can include user data even if their privacy level isn't public!</td>
				</tr>
			</table>
			<table class="adminlist sortable" id="jomsocial-users">
			<thead>
				<tr>
					<th width="20" nowrap="nowrap">ID</th>
					<th width="40" nowrap="nowrap" class="sorttable_nosort"><?php echo JText::_( 'JM_INCLUDE' ); ?></th>
					<th width="40" nowrap="nowrap" class="sorttable_nosort"></th>
					<th nowrap="nowrap"><?php echo JText::_( 'JM_NAME' ); ?></th>
					<th width="60" nowrap="nowrap"><?php echo JText::_( 'JM_EMAIL_ADDRESS' ); ?></th>
					<th width="60" nowrap="nowrap"><?php echo JText::_( 'JM_REGISTRATION' ); ?></th>
					<th width="60" nowrap="nowrap"><?php echo JText::_( 'JM_LAST_VISIT' ); ?></th>
					<th width="60" nowrap="nowrap"><?php echo JText::_( 'JM_PROFILE_PRIVACY' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="9">
						<div class="pagination">
						<div class="limit">
						<?php echo JText::_('Display Num');?>
						<?php $jsLimit = JRequest::getVar('jsLimit', $mainframe->getCfg('list_limit'), '', 'int'); ?>
						<select name="jsLimit" id="jsLimit" onchange="submitform();">
							<option value="5" <?php if($jsLimit==5){echo 'selected="selected"';}?>>5</option>
							<option value="10" <?php if($jsLimit==10){echo 'selected="selected"';}?>>10</option>
							<option value="15" <?php if($jsLimit==15){echo 'selected="selected"';}?>>15</option>
							<option value="20" <?php if($jsLimit==20){echo 'selected="selected"';}?>>20</option>
							<option value="25" <?php if($jsLimit==25){echo 'selected="selected"';}?>>25</option>
							<option value="30" <?php if($jsLimit==30){echo 'selected="selected"';}?>>30</option>
							<option value="50" <?php if($jsLimit==50){echo 'selected="selected"';}?>>50</option>
							<option value="100" <?php if($jsLimit==100){echo 'selected="selected"';}?>>100</option>
							<option value="-1" <?php if($jsLimit==-1){echo 'selected="selected"';}?>><?php echo JText::_('all');?></option>
						</select>
						</div>
						<?php
						if($jsLimit < $this->jsTotal){
							$previous = (($jsLimitstart - $jsLimit)<0) ? 0 : ($jsLimitstart - $jsLimit);
							$next     = (($jsLimitstart + $jsLimit)>$this->jsTotal) ? $jsLimitstart : ($jsLimitstart + $jsLimit);
							
							echo '<div class="button2-right"><div class="prev">';
							echo '<a href="javascript:document.adminForm.jsLimitstart.value='.$previous.'; submitform();">'.JText::_('prev').'</a>';
							echo '</div></div>';
							echo '<div class="button2-left"><div class="next">';
							echo '<a href="javascript:document.adminForm.jsLimitstart.value='.$next.'; submitform();">'.JText::_('next').'</a> ';
							echo '</div></div>';
							echo '<div class="limit">';
							
							echo JText::sprintf('JPAGE_CURRENT_OF_TOTAL', ceil(($jsLimitstart+1) / $jsLimit), ceil($this->jsTotal / $jsLimit));
							echo '</div>';
						}
						?>
						<input type="hidden" name="jsLimitstart" value="0" />
						</div>
					</td>
				</tr>
			</tfoot>
			<?php
			$k=0;
			$jomsocialOrder = '';
			foreach ( $this->jomsocial as $u ) {
				$jsUser = CFactory::getUser($u->id);
				$params = $jsUser->getParams();
				switch($params->get('privacyProfileView', 0)){
					case 0:
					default:
						$privacy = 'public';
						break;
					case 20:
						$privacy = 'members only';
						break;
					case 30:
						$privacy = 'friends only';
						break;
					case 40:
						$privacy = 'self';
						break;
				}
				
				$thumb = '<img src="'.$jsUser->getThumbAvatar().'" alt="'.$u->name.'" title="'.$u->name.'" />';
				if( in_array($u->id, $jomsocial_pre) ) { $checked = 'checked="checked"'; } else { $checked = ''; }
				echo '<tr class="row'.$k.'">';
				echo '<td align="center">'.$u->id.'</td>';
				echo '<td align="center"><input type="checkbox" name="jsProfiles[]" id="jsProfiles" value="'.$u->id.'" '.$checked.'/></td>';
				echo '<td align="center">'.$thumb.'</td>';
				echo '<td>'.$u->name.'</td>';
				echo '<td align="center">'.$u->email.'</td>';
				echo '<td align="center">'.$u->registerDate.'</td>';
				echo '<td align="center">'.$u->lastvisitDate.'</td>';
				echo '<td align="center">'.$privacy.'</td>';
				echo '</tr>';
				$jomsocialOrder .= $u->id.';';
				$k = ($k)? 0:1;
			}?>
			</table>
				
			</div></div>
			
			<?php
			if ($this->core && ( $this->K2Installed || $this->jomsocial || $this->aec || $this->ambra)){
				?>
				<div class="panel">
					<h3 class="jpane-toggler title" id="panel_4">
						<span><?php echo JText::_( 'JM_JOMSOCIAL_DISCUSSIONS' );?></span></h3>
						<div class="jpane-slider content" id="panel_4_content">
				<?php
			}
			if($this->jsdiscussions){
			?>
			<table class="adminlist sortable" id="jomsocial-users">
			<thead>
				<tr>
					<th width="40" nowrap="nowrap" class="sorttable_nosort"><?php echo JText::_( 'JM_INCLUDE' ); ?></th>
					<th width="60" nowrap="nowrap"><?php echo JText::_( 'JM_GROUP' ); ?></th>
					<th width="200" nowrap="nowrap"><?php echo JText::_( 'JM_TITLE' ); ?></th>
					<th nowrap="nowrap"><?php echo JText::_( 'JM_FIRST_POST' ); ?></th>
					<th width="60" nowrap="nowrap"><?php echo JText::_( 'JM_STARTER' ); ?></th>
					<th width="60" nowrap="nowrap"><?php echo JText::_( 'JM_DATE' ); ?></th>
				</tr>
			</thead>
			<?php
			$k=0;
			$jsdiscOrder = '';		
			    foreach($this->jsdiscussions as $d){
				if( in_array($d->id, $jsdisc_pre) ) { $checked = 'checked="checked"'; } else { $checked = ''; }
				echo '<tr class="row'.$k.'">';
				echo '<td align="center"><input type="checkbox" name="jsdisc[]" id="jsdisc" value="'.$d->id.'" '.$checked.'/></td>';
				echo '<td>'.$d->name.'</td>';
				echo '<td>'.$d->title.'</td>';
				echo '<td>'.$d->message.'</td>';
				echo '<td nowrap="nowrap">'.CFactory::getUser($d->creator)->name.'</td>';
				echo '<td nowrap="nowrap">'.$d->created .'</td>';
				echo '</tr>';
				$jsdiscOrder .= $d->id.';';
				$k = ($k)? 0:1;
			    }
			?>
			</table>
			<?php
			    echo '<input type="hidden" name="jsdiscussions" id="jsdiscussions" value="1" />';
			} else {
			    $jsdiscOrder = '';
			    echo '<input type="hidden" name="jsdiscussions" id="jsdiscussions" value="0" />';
			}
			?>
			</div></div>
			<?php
		}
		
		// AEC
		if ($this->aec){
			if ($this->core && ( $this->K2Installed || $this->jomsocial || $this->aec || $this->ambra)){
					?>
					<div class="panel">
						<h3 class="jpane-toggler title" id="panel_5">
							<span><?php echo JText::_( 'JM_AEC_PLANS' );?></span></h3>
							<div class="jpane-slider content" id="panel_5_content">
							
							<table class="adminlist sortable" id="aec-plans">
							<thead>
								<tr>
									<th width="20" nowrap="nowrap" class="sorttable_nosort"><?php echo JText::_( 'JM_ID' ); ?></th>
									<th width="40" nowrap="nowrap" class="sorttable_nosort"><?php echo JText::_( 'JM_INCLUDE' ); ?></th>
									<th nowrap="nowrap"><?php echo JText::_( 'JM_NAME' ); ?></th>
									<th nowrap="nowrap"><?php echo JText::_( 'JM_DESCRIPTION' ); ?></th>
									<th width="60" nowrap="nowrap"><?php echo JText::_( 'JM_PRICE' ); ?></th>
								</tr>
							</thead>
							<?php
							$k=0;
							$aecOrder = '';
							foreach($this->aec as $aec){
								if( in_array($aec->id, $aec_pre) ) { $checked = 'checked="checked"'; } else { $checked = ''; }
								$aecParams = unserialize( base64_decode( $aec->params ) );
								echo '<tr class="row'.$k.'">';
								echo '<td align="center">'.$aec->id.'</td>';
								echo '<td align="center"><input type="checkbox" name="aec[]" id="aec" value="'.$aec->id.'" '.$checked.'/></td>';
								echo '<td>'.$aec->name.'</td>';
								echo '<td>'.substr(strip_tags($aec->desc),0, 140).'</td>';
								echo '<td>'.$aecParams['full_amount'].' '.$this->aecConfig['standard_currency'].'</td>';
								echo '</tr>';
								$aecOrder .= $aec->id.';';
								$k = ($k)? 0:1;
							}
							?>
							</table>
							</div></div>
			<?php
			}
		}
		
		// AMBRA
		if ($this->ambra){
			if ($this->core && ( $this->K2Installed || $this->jomsocial || $this->aec || $this->ambra)){
					?>
					<div class="panel">
						<h3 class="jpane-toggler title" id="panel_6">
							<span><?php echo JText::_( 'JM_AMBRA_SUBS' );?></span></h3>
							<div class="jpane-slider content" id="panel_6_content">
							
							<table class="adminlist sortable" id="ambra-plans">
							<thead>
								<tr>
									<th width="20" nowrap="nowrap" class="sorttable_nosort"><?php echo JText::_( 'JM_ID' ); ?></th>
									<th width="40" nowrap="nowrap" class="sorttable_nosort"><?php echo JText::_( 'JM_INCLUDE' ); ?></th>
									<th nowrap="nowrap"><?php echo JText::_( 'JM_NAME' ); ?></th>
									<th nowrap="nowrap"><?php echo JText::_( 'JM_DESCRIPTION' ); ?></th>
									<th width="60" nowrap="nowrap"><?php echo JText::_( 'JM_PRICE' ); ?></th>
								</tr>
							</thead>
							<?php
							$k=0;
							$ambraOrder = '';
							foreach($this->ambra as $ambra){
								if( in_array($ambra->id, $ambra_pre) ) { $checked = 'checked="checked"'; } else { $checked = ''; }
								echo '<tr class="row'.$k.'">';
								echo '<td align="center">'.$ambra->id.'</td>';
								echo '<td align="center"><input type="checkbox" name="ambra[]" id="ambra" value="'.$ambra->id.'" '.$checked.'/></td>';
								echo '<td>'.$ambra->title.'</td>';
								echo '<td>'.substr(strip_tags($ambra->description),0, 140).'</td>';
								echo '<td>'.$ambraPre.$ambra->value.' '.$ambraPost.'</td>';
								echo '</tr>';
								$ambraOrder .= $ambra->id.';';
								$k = ($k)? 0:1;
							}
							?>
							</table>
							</div></div>
			<?php
			}
		}
		
		
		
		if ($this->core && ( $this->K2Installed || $this->jomsocial || $this->aec || $this->ambra)){
//		echo $pane->endPanel();
//		echo $pane->endPane();
		echo '</div>';
		}
		?>
		
		</td>
		</tr>
</table>
</div>
<?php
echo $tabs->endPanel();
echo $tabs->startPanel( JText::_( 'JM_SIDEBAR' ), 'create_sidebar', 'h4', 'text-transform:none;' );
?>
<div class="col100">
<div id="sidebar_info"><?php echo JText::_('JM_SIDEBAR_INFO');?></div>
	<table class="admintable" width="100%">
        <tr>
            <td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_SIDEBAR_EDITOR' ); ?>:
			</td>
            <td>
                <?php
        		$buttons2exclude = array( 'pagebreak', 'readmore' );
                $sidebareditor =& JFactory::getEditor($editortype);
        		echo $sidebareditor->display('sidebar', $sidebarcontent, '550', '200', '60', '20', $buttons2exclude );
                ?>
            </td>
        </tr>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_TABLE_OF_CONTENTS' ); ?>:
			</td>
			<td>
				<input class="checkbox" type="checkbox" name="tableofcontents" id="tableofcontents" value="1" <?php echo ($toc)?'checked="checked"':'';?> />
				<div class="checkboxInfo">
					<?php echo JText::_( 'JM_TABLE_OF_CONTENTS_INFO' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_ANCHOR_HYPERLINK' ); ?>:
			</td>
			<td>
				<span  class="nodeImg"><img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<input class="checkbox" type="checkbox" name="tableofcontents_type" id="tableofcontents_type" value="1" <?php echo ($toct)?'checked="checked"':'';?>/>
				<div class="checkboxInfo"><?php echo JText::_( 'JM_TABLE_OF_CONTENTS_TYPE_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
		    <td width="100" align="right" class="key" valign="top">
			<?php echo JText::_( 'JM_POPULAR_ARTICLES' ); ?>:
		    </td>
		    <td>
			<input class="checkbox" type="checkbox" name="populararticles" id="populararticles" value="1" <?php echo ($pop)?'checked="checked"':'';?> />
			<div class="checkboxInfo"><?php echo JText::_( 'JM_POPULAR_ARTICLES_INFO' ); ?></div>
			<div style="clear:both;"></div>
			<div id="popSlide">
			<table>
			    <tr>
				<td valign="top">
				    <?php echo JText::_('JM_INCLUDE');?>:
				    <div style="padding: 4em 0 0 0;text-align:right;"><a href="javascript:void(0);deselect('popInclude');" title="<?php echo JText::_('JM_CLEAR_SELECTION');?>" style="color:#666666;text-decoration:none;outline:none;"><img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/deselect.png" alt="<?php echo JText::_('JM_CLEAR_SELECTION');?>" /></a></div>
				</td>
				<td valign="top">
				    <select multiple="multiple" name="popInclude[]" id="popInclude" size="5">
					<?php foreach($this->seccat as $sc){
					    if($pin){
						if(in_array($sc->cid, $pin)){
						    $selected = 'selected="selected"';
						} else {
						    $selected = '';
						}
					    } else {
						$selected = 'selected="selected"';
					    }
					    if($sc->cid == 0){
						echo '<option value="'.$sc->cid.'" '.$selected.'>'.$sc->title.'</option>';
					    } else {
						echo '<option value="'.$sc->cid.'" '.$selected.'>'.$sc->title.' - '.$sc->ctitle.'</option>';
					    }
					} ?>
				    </select>
				</td>
				<td valign="top">
				    <?php echo JText::_('JM_EXCLUDE');?>:
				    <div style="padding: 4em 0 0 0;text-align:right;"><a href="javascript:void(0);deselect('popExclude');" title="<?php echo JText::_('JM_CLEAR_SELECTION');?>" style="color:#666666;text-decoration:none;outline:none;"><img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/deselect.png" alt="<?php echo JText::_('JM_CLEAR_SELECTION');?>" /></a></div>
				</td>
				<td valign="top">
				    <select multiple="multiple" name="popExclude[]" id="popExclude" size="5">
					<?php foreach($this->seccat as $sc){
					    if($pex){
						if(in_array($sc->cid, $pex)){
						    $selected = 'selected="selected"';
						} else {
						    $selected = '';
						}
					    } else {
						$selected = '';
					    }
					    if($sc->cid == 0){
						echo '<option value="'.$sc->cid.'" '.$selected.'>'.$sc->title.'</option>';
					    } else {
						echo '<option value="'.$sc->cid.'" '.$selected.'>'.$sc->title.' - '.$sc->ctitle.'</option>';
					    }
					} ?>
				    </select>
				</td>
			    </tr>
			</table>
			</div>
		    </td>
		</tr>
		<?php if ($this->K2Installed) { ?>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_INCLUDE_K2_ARTICLES' ); ?>:
			</td>
			<td>
			    <span  class="nodeImg"><img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
			    <input class="checkbox" type="checkbox" name="populark2" id="populark2" value="1" <?php echo ($pk2)?'checked="checked"':'';?> />
			    <div class="checkboxInfo"><?php echo JText::_( 'JM_INCLUDE_K2_ARTICLES_INFO' ); ?></div>
			    <div style="clear:both;"></div>
			    <div id="popk2Slide">
			    <table>
				<tr>
				    <td valign="top">
					<?php echo JText::_('JM_INCLUDE');?>:
					<div style="padding: 4em 0 0 0;text-align:right;"><a href="javascript:void(0);deselect('popk2Include');" title="<?php echo JText::_('JM_CLEAR_SELECTION');?>" style="color:#666666;text-decoration:none;outline:none;"><img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/deselect.png" alt="<?php echo JText::_('JM_CLEAR_SELECTION');?>" /></a></div>
				    </td>
				    <td valign="top">
					<select multiple="multiple" name="popk2Include[]" id="popk2Include" size="5">
					    <?php foreach($this->allk2cat as $sc){
						    if($pk2in){
							if(in_array($sc->id, $pk2in)){
							    $selected = 'selected="selected"';
							} else {
							    $selected = '';
							}
						    } else {
							$selected = 'selected="selected"';
						    }
						    echo '<option value="'.$sc->id.'" '.$selected.'>'.$sc->name.'</option>';
					    } ?>
					</select>
				    </td>
				    <td valign="top">
					<?php echo JText::_('JM_EXCLUDE');?>:
					<div style="padding: 4em 0 0 0;text-align:right;"><a href="javascript:void(0);deselect('popk2Exclude');" title="<?php echo JText::_('JM_CLEAR_SELECTION');?>" style="color:#666666;text-decoration:none;outline:none;"><img src="<?php echo JURI::root();?>administrator/components/com_joomailermailchimpintegration/assets/images/deselect.png" alt="<?php echo JText::_('JM_CLEAR_SELECTION');?>" /></a></div>
				    </td>
				    <td valign="top">
					<select multiple="multiple" name="popk2Exclude[]" id="popk2Exclude" size="5">
					    <?php foreach($this->allk2cat as $sc){
						    if($pk2ex){
							if(in_array($sc->id, $pk2ex)){
							    $selected = 'selected="selected"';
							} else {
							    $selected = '';
							}
						    } else {
							$selected = '';
						    }
						    echo '<option value="'.$sc->id.'" '.$selected.'>'.$sc->name.'</option>';
					    } ?>
					</select>
				    </td>
				</tr>
			    </table>
			    </div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_ONLY_K2_ARTICLES' ); ?>:
			</td>
			<td>
				<span  class="nodeImg">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<input class="checkbox" type="checkbox" name="populark2_only" id="populark2_only" value="1" <?php echo ($pk2o)?'checked="checked"':'';?> />
				<div class="checkboxInfo"><?php echo JText::_( 'JM_ONLY_K2_ARTICLES_INFO' ); ?></div>
			</td>
		</tr>
		<?php } ?>
		
		
<?php if ( $vm_installed ) { ?>
		<tr>
			<td width="100" align="right" class="key" valign="top">
			</td>
			<td><hr style="border: 0;border-bottom: 1px dotted #666666;margin:0;padding:0;"/></td>
		</tr>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_VIRTUEMART_PRODUCTS' ); ?>:
			</td>
			<td>
				<input class="checkbox" type="checkbox" name="vm_sidebar" id="vm_sidebar" value="1" <?php echo ($vm_sb)?'checked="checked"':'';?> onchange="if(this.checked==false){vmSlide.slideOut();}else{vmSlide.slideIn();}"/>
				<div class="checkboxInfo"><?php echo JText::_( 'JM_VM_SIDEBAR_INFO' ); ?></div>
			</td>
		</tr>
		<?php /*
		<tr>
			<td width="100" align="right" class="key">
				<label for="vm_sidebar">
					<?php echo JText::_( 'Amount of Products' ); ?>:
				</label>
			</td>
			<td>
				<span  class="nodeImg"><img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<input class="textarea" type="text" name="vm_sidebar_amount" id="vm_sidebar_amount" value="5" size="2" />
				&nbsp;<?php echo JText::_( 'vm sidebar amount info' ); ?>
			</td>
		</tr>
		*/ ?>
		<tr>
			<td width="100" align="right" valign="top" class="key" valign="top">
				<label for="vm_productlist">
					<?php echo JText::_( 'JM_PRODUCTS' ); ?>:
				</label>
			</td>
			<td>
			<?php /*
				<script language="javascript" type="text/javascript">
					window.addEvent('load', function() { 
						var table3 = document.getElementById("vm_productlist");
						var tableDnD = new TableDnD();
						tableDnD.init(table3);
					});
				</script>
			*/ ?>
			<table class="adminlist sortable" id="vm_productlist">
				<thead>
				  <tr>
					  <th width="20" nowrap="nowrap">ID</th>
					  <th width="40" class="sorttable_nosort" nowrap="nowrap"><?php echo JText::_( 'JM_INCLUDE' ); ?></th>
					  <th nowrap="nowrap"><?php echo JText::_( 'JM_NAME' ); ?></th>
					  <th width="70" class="sorttable_nosort" nowrap="nowrap"><?php echo JText::_( 'JM_THUMBNAIL' ); ?></th>
					  <th width="80" nowrap="nowrap"><?php echo JText::_( 'JM_CATEGORY' ); ?></th>
					  <th width="80" nowrap="nowrap"><?php echo JText::_( 'JM_PRICE' ); ?></th>
				  </tr>
				</thead>
			<?php
			$k=1;
			$cat_old = '';
			foreach ( $this->VMproducts as $product ) {
				if( $product->category_id != $cat_old){
					if($k == 0) { $k=1; } else { $k=0; }
				}
				$checked = '';
				for($i=0;$i<count($vmid);$i++){
					if(		$vmid[$i] == $product->product_id 
						&&	$vmpr[$i] == $product->product_price
						&&	$vmct[$i] == $product->category_id
					){
						$checked = 'checked="checked"';
					}
				}
				echo '<tr class="row'.$k.'">';
				echo '<td align="center">'.$product->product_id.'</td>';
				echo '<td align="center"><input type="checkbox" name="vm_sb_products[]" id="vm_sb_products" '.$checked.' value="'.$product->product_id.';'.$product->product_price.';'.$product->category_id.'"></td>';
				echo '<td>'.$product->product_name.'</td>';
				echo '<td align="center"><a href="'.JURI::root().'components/com_virtuemart/shop_image/product/'.$product->product_thumb_image.'" class="modal">';
				echo '<img src="'.JURI::root().'components/com_virtuemart/shop_image/product/'.$product->product_thumb_image.'" height="30"/>';
				echo '</a></td>';
				echo '<td align="center">'.$product->category_name.'</td>';
				echo '<td align="center">'.number_format($product->product_price,2).' '.$product->product_currency.'</td>';
				echo '</tr>';
				$cat_old = $product->category_id;
			}
?>
				</table>
				<a id="vm_toggle" href="javascript:void(0);"><?php echo JText::_( 'JM_SHOW_HIDE_PRODUCTS' ); ?></a>
<?php /* echo JText::_( 'vm sidebar ids info' ); */ ?>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<label for="vm_sidebar_order">
					<?php echo JText::_( 'JM_ORDER_BY' ); ?>:
				</label>
			</td>
			<td>
				<span  class="nodeImg"><img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<select name="vm_sidebar_order" id="vm_sidebar_order">
					<option value="name_asc" <?php echo ($vmor=='name_asc')? 'selected="selected"':'';?>><?php   echo JText::_( 'JM_NAME_ASC' ); ?></option>
					<option value="name_desc" <?php echo ($vmor=='name_desc')? 'selected="selected"':'';?>><?php  echo JText::_( 'JM_NAME_DESC' ); ?></option>
					<option value="price_asc" <?php echo ($vmor=='price_asc')? 'selected="selected"':'';?>><?php  echo JText::_( 'JM_PRICE_ASC' ); ?></option>
					<option value="price_desc" <?php echo ($vmor=='price_desc')? 'selected="selected"':'';?>><?php echo JText::_( 'JM_PRICE_DESC' ); ?></option>
					<option value="cat_asc" <?php echo ($vmor=='cat_asc')? 'selected="selected"':'';?>><?php    echo JText::_( 'JM_CATEGORY_ASC' ); ?></option>
					<option value="cat_desc" <?php echo ($vmor=='cat_desc')? 'selected="selected"':'';?>><?php   echo JText::_( 'JM_CATEGORY_DESC' ); ?></option>
					<option value="random" <?php echo ($vmor=='random')? 'selected="selected"':'';?>><?php     echo JText::_( 'JM_RANDOM' ); ?></option>
				</select>
				<div class="inputInfo" style="display:inline;margin-left: 10px;"><?php echo JText::_( 'JM_VM_SIDEBAR_ORDER_INFO' ); ?></div>
			</td>
		</tr>
		
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_DISPLAY_PRICE' ); ?>:
			</td>
			<td>
				<span  class="nodeImg"><img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<input class="checkbox" type="checkbox" name="vm_sidebar_price" id="vm_sidebar_price" value="1" <?php echo ($vmsp)?'checked="checked"':'';?>/>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_CURRENCY_FIRST' ); ?>:
			</td>
			<td>
				<span  class="nodeImg">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<input class="checkbox" type="checkbox" name="vm_sidebar_curr_first" id="vm_sidebar_curr_first" value="1" <?php echo ($vmcf)?'checked="checked"':'';?> />
				<div class="checkboxInfo"><?php echo JText::_( 'JM_VM_CURRENCY_FIRST_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_DISPLAY_IMAGE' ); ?>:
			</td>
			<td>
				<span  class="nodeImg"><img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<input class="checkbox" type="checkbox" name="vm_sidebar_img" id="vm_sidebar_img" value="1" <?php echo ($vmimg)?'checked="checked"':'';?> />
				<div class="checkboxInfo"><?php echo JText::_( 'JM_VM_SIDEBAR_IMAGE_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_LINK_SIDEBAR_PRODUCT' ); ?>:
			</td>
			<td>
				<span  class="nodeImg"><img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<input class="checkbox" type="checkbox" name="vm_sidebar_link" id="vm_sidebar_link" value="1" <?php echo ($vmlnk)?'checked="checked"':'';?> />
				<div class="checkboxInfo"><?php echo JText::_( 'JM_VM_SIDEBAR_LINK_INFO' ); ?></div>
			</td>
		</tr>
		
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_SHOW_SHORT_DESCRIPTION' ); ?>:
			</td>
			<td>
				<span  class="nodeImg"><img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<input class="checkbox" type="checkbox" name="vm_short_desc" id="vm_short_desc" value="1" <?php echo ($vmsdesc)?'checked="checked"':'';?> />
				<div class="checkboxInfo"><?php echo JText::_( 'JM_VM_SIDEBAR_SHORT_DESCRIPTION_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_SHOW_PRODUCT_DESCRIPTION' ); ?>:
			</td>
			<td>
				<span  class="nodeImg"><img src="components/com_joomailermailchimpintegration/assets/images/node.gif" /></span>
				<input class="checkbox" type="checkbox" name="vm_desc" id="vm_desc" value="1" <?php echo ($vmdesc)?'checked="checked"':'';?> />
				<div class="checkboxInfo"><?php echo JText::_( 'JM_VM_SIDEBAR_PRODUCT_DESCRIPTION_INFO' ); ?></div>
			</td>
		</tr>
				
<?php } ?>
		<tr>
			<td width="100" align="right" class="key">
			</td>
			<td><hr style="border: 0;border-bottom: 1px dotted #666666;margin:0;padding:0;"/></td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="twitter">
					<?php echo JText::_( 'JM_TWITTER_ICON' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="twitter" id="twitter" size="25" maxlength="250" value="<?php echo $twitter; ?>" style="margin-right: 20px;" />
				<div class="inputInfo"><?php echo JText::_( 'JM_TWITTER_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="facebook">
					<?php echo JText::_( 'JM_FACEBOOK_LINK' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="facebook" id="facebook" size="25" maxlength="250" value="<?php echo $facebook; ?>" style="margin-right: 20px;" />
				<div class="inputInfo"><?php echo JText::_( 'JM_FACEBOOK_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="myspace">
					<?php echo JText::_( 'JM_MYSPACE_ICON' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="myspace" id="myspace" size="25" maxlength="250" value="<?php echo $myspace; ?>" style="margin-right: 20px;" />
				<div class="inputInfo"><?php echo JText::_( 'JM_MYSPACE_INFO' ); ?></div>
			</td>
		</tr>
	</table>

</div>
<?php
echo $tabs->endPanel(); 
echo $tabs->startPanel( JText::_( 'JM_ANALYTICS' ), 'gaSettings', 'h4', 'text-transform:none;' );
?>
<div class="col100">
		<table class="admintable" width="100%">
		<tr>
			<td width="155" style="width:155px;" align="right" class="key" valign="top">
				<?php echo JText::_( 'JM_ENABLE_GOOGLE_ANALYTICS' ); ?>:
			</td>
			<td>
				<input class="checkbox" type="checkbox" name="gaEnabled" id="gaEnabled" value="1" />
			</td>
		</tr>
		<tr>
			<td align="right" class="key">
				<label for="gaSource">
					<?php echo JText::_( 'JM_SOURCE' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="gaSource" id="gaSource" value="<?php echo $gaSource;?>" size="48" style="float:left;margin-right: 20px;" />
				<div class="inputInfo"><?php echo JText::_( 'JM_GASOURCE_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td align="right" class="key">
				<label for="gaMedium">
					<?php echo JText::_( 'JM_MEDIUM' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="gaMedium" id="gaMedium" value="<?php echo $gaMedium;?>" size="48" style="float:left;margin-right: 20px;"/>
				<div class="inputInfo"><?php echo JText::_( 'JM_GAMEDIUM_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td align="right" class="key">
				<label for="gaName">
					<?php echo JText::_( 'JM_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="gaName" id="gaName" value="<?php echo $gaName;?>" size="48" style="float:left;margin-right: 20px;"/>
				<div class="inputInfo"><?php echo JText::_( 'JM_GANAME_INFO' ); ?></div>
			</td>
		</tr>
		<tr>
			<td align="right" class="key" valign="top">
				<label for="listid">
					<?php echo JText::_( 'JM_EXCLUDE_URLS' ); ?>:
				</label>
			</td>
			<td>
				<textarea name="gaExcluded" id="gaExcluded" rows="10" style="width:302px;float:left;margin-right: 20px; padding: 5px;"><?php echo $gaExcluded;?></textarea>
				<div class="inputInfo" style="display:block;"><?php echo JText::_( 'JM_GAEXCLUDED_INFO' ); ?></div>
			</td>
		</tr>
		</table>
</div>

<?php
echo $tabs->endPanel();
echo $tabs->startPanel( JText::_( 'JM_FOLDERS' ), 'Folders', 'h4', 'text-transform:none;' );
?>
<div class="col100">
		<table class="admintable" width="100%">
		<tr>
			<td width="155" style="width:155px;" align="right" class="key">
				<?php echo JText::_( 'JM_CHOOSE_A_FOLDER' ); ?>:
			</td>
			<td>
				<?php echo $this->foldersDropDown;?>
			</td>
		</tr>
		<tr>
			<td width="155" style="width:155px;" align="right" class="key">
				<?php echo JText::_( 'JM_CREATE_A_NEW_FOLDER' ); ?>:
			</td>
			<td>
				<input class="text_area" type="text" name="folder_name" id="folder_name" value="" size="48" style="float:left;margin-right: 20px;" />
				<?php echo JHTML::tooltip( JText::_( 'JM_FOLDER_INFO' ), JText::_( 'JM_FOLDER_INFO_HEADING' ), $tt_image.'" style="margin:0 5px;position:relative;top:3px;"', '' );?>
			</td>
		</tr>
		</table>
</div>
<?php
echo $tabs->endPane(); 
?>
<a name="preview"></a>
<div class="clr"></div>
<span id="preview"></span>

<?php } // end - no list created ?>

<?php
echo '<input type="hidden" name="coreOrder" id="coreOrder" value="'.$coreOrder.'" />';
if ($this->K2Installed) {
	echo '<input type="hidden" name="k2_installed" id="k2_installed" value="1" />';
	echo '<input type="hidden" name="k2Order" id="k2Order" value="'.$k2Order.'" />';
} else {
	echo '<input type="hidden" name="k2_installed" id="k2_installed" value="0" />';
}
if ( $this->jomsocial ) {
	echo '<input type="hidden" name="jomsocial_installed" id="jomsocial_installed" value="1" />';
	echo '<input type="hidden" name="jsOrder" id="jsOrder" value="'.$jomsocialOrder.'" />';
	echo '<input type="hidden" name="jsdiscOrder" id="jsdiscOrder" value="'.$jsdiscOrder.'" />';
} else {
	echo '<input type="hidden" name="jomsocial_installed" id="jomsocial_installed" value="0" />';
}
if ( $this->aec ) {
	echo '<input type="hidden" name="aec_installed" id="aec_installed" value="1" />';
	echo '<input type="hidden" name="aecOrder" id="aecOrder" value="'.$aecOrder.'" />';
} else {
	echo '<input type="hidden" name="aec_installed" id="aec_installed" value="0" />';
}
if ( $this->ambra ) {
	echo '<input type="hidden" name="ambra_installed" id="ambra_installed" value="1" />';
	echo '<input type="hidden" name="ambraOrder" id="ambraOrder" value="'.$ambraOrder.'" />';
} else {
	echo '<input type="hidden" name="ambra_installed" id="ambra_installed" value="0" />';
}
?>
<input type="hidden" name="vm_installed" id="vm_installed" value="<?php echo $vm_installed;?>" />
<input type="hidden" name="time" value="<?php echo strtotime(date('Y-m-d H:i:s'));?>" />
<input type="hidden" name="list_names" id="list_names" value="" />

<input type="hidden" name="cid" value="<?php echo JRequest::getVar('cid', 0);?>" />

<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="action" value="<?php echo JRequest::getVar('action','save');?>" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="articlechecked" value="0" />
<input type="hidden" name="k2checked" value="0" />
<input type="hidden" name="controller" value="create" />
<input type="hidden" name="type" value="create" />
</form>
</div>
<?php
}
}

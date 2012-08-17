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

jimport( 'joomla.filesystem.file' );

$template_folder = JRequest::getVar('template',  array(), '', 'array');

$document = & JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components/com_joomailermailchimpintegration/assets/css/templateEditor.css');
$document->addScript(JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/js/jquery-ui-1.8.5.custom.min.js');
$document->addScript(JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/js/jquery.jeditable.js');
$document->addScript(JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/js/picker.js');
$document->addStyleSheet(JURI::base().'components/com_joomailermailchimpintegration/assets/css/picker.css');
$document->addScript(JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/js/fileuploader.js');
$document->addStyleSheet(JURI::base().'components/com_joomailermailchimpintegration/assets/css/fileuploader.css');

if(version_compare(JVERSION,'1.6.0','ge')){
    $submitFunction = 'Joomla.submitbutton = function(pressbutton) {';
} else {
    $submitFunction = 'function submitbutton(pressbutton) {';
}
$script = 'var baseUrl = "'.JURI::base().'";
var tmplUrl = "'.JURI::root().'tmp/'.$template_folder[0].'/";
window.addEvent("load", function() {
	jQuery(function() {
		jQuery( "#placeholders" ).sortable({
			revert: true,
			axis: "y"
		});
	});
	jQuery(".sideColumnTitle,.mainColumnTitle", window.frames[\'previewIframe\'].document).editable(
	function(value, settings){ return(value); } ,
		{submit : "OK", cancel : "Cancel",
		data: function(value, settings) {
											var retval = value.replace(/&lt;/, "<").replace(/&gt;/,">");
											retval = retval.replace(/&lt;/, "<").replace(/&gt;/,">");
											retval = retval.replace(/&lt;/, "<").replace(/&gt;/,">");
											retval = retval.replace(/&lt;/, "<").replace(/&gt;/,">");
											retval = retval.replace(/&lt;/, "<").replace(/&gt;/,">");
											return retval;
										},
		onblur: "submit",
		width: "99%"
		}
	).addClass("editable").attr("title","'.JText::_('JM_CLICK_TO_EDIT').'");

	iframeHeight = jQuery(".backgroundTable", window.frames[\'previewIframe\'].document).height();
	if(iframeHeight){
		jQuery("#previewIframe").attr( "height", iframeHeight+50 );
	}

	var currentColor = "#000000";
	jQuery.each( jQuery(".colorPreviewBox"), function(){
		currentId = jQuery(".colorPreview", this).attr("id").replace("Preview", "");
		currentColor = jQuery("#"+currentId).val();
		jQuery(".colorPreview", this).css("background-color", currentColor );
	});

	jQuery(".colorValue").keyup( function(){
		removeLayer("picker");
		currentId = jQuery(this).attr("id").replace("Value", "");
		jQuery("#"+currentId+"Preview").css("background-color", jQuery(this).val() );
	}).blur( function(){
		removeLayer("picker");
		currentId = jQuery(this).attr("id").replace("Value", "");
		jQuery("#"+currentId+"Preview").css("background-color", jQuery(this).val() );
	});

	createUploader( "uploadLogo" );
	logoWidth = jQuery(".headerBar", window.frames[\'previewIframe\'].document).width();
	if(logoWidth){
	var logoSizeInfo = "'.JText::_('JM_TEMPLATE_WIDTH_OF').' <span style=\"font-weight:bold;\">"+logoWidth+"px</span>";
	jQuery("#logoSizeInfo").html(logoSizeInfo);
	}

	createUploader( "twitterUpload" );
	createUploader( "facebookUpload" );
	createUploader( "myspaceUpload" );

	jQuery(".optionsHeader_r").toggle(
		function () {
			jQuery( "#"+jQuery(this).attr("rel") ).slideUp();
			jQuery(this).addClass("optionsHeader_rc");
		},
		function () {
			jQuery( "#"+jQuery(this).attr("rel") ).slideDown();
			jQuery(this).removeClass("optionsHeader_rc");
		}
	);

	jQuery("#phPosition").change( function(){
		if( jQuery(this).val() != ""){
			jQuery("#phOptions").slideDown();
		} else {
			jQuery("#phOptions").slideUp();
		}
	});

	jQuery("#toggleSelect").toggle(
		function(){
			jQuery.each( jQuery(".phCb"), function(){ this.checked = true; } );
			jQuery("#toggleSelect").addClass("selNone");
		},
		function(){
			jQuery.each( jQuery(".phCb"), function(){ this.checked = false; } );
			jQuery("#toggleSelect").removeClass("selNone");
		}
	);

	jQuery("#insertLogoUrl").click( function(){
		if( jQuery(".headerBar a", window.frames[\'previewIframe\'].document).length > 0 ){
		jQuery(".headerBar a", window.frames[\'previewIframe\'].document).attr("href", jQuery("#logoUrl").val());
		} else {
		jQuery(".headerBar img", window.frames[\'previewIframe\'].document).wrap("<a href=\""+jQuery("#logoUrl").val()+"\" style=\"border:none;\"/>");
		}
		jQuery(".headerBar img", window.frames[\'previewIframe\'].document).css("border", "none");
		jQuery(".headerBar a", window.frames[\'previewIframe\'].document).attr("title", jQuery("#logoAlt").val());
		jQuery(".headerBar img", window.frames[\'previewIframe\'].document).attr("alt", jQuery("#logoAlt").val());
		jQuery(".headerBar img", window.frames[\'previewIframe\'].document).attr("title", jQuery("#logoAlt").val());
	});

	jQuery("a", window.frames[\'previewIframe\'].document).click( function(){
		link = jQuery(this).attr("href").replace(tmplUrl,"");
		alert( link );
		return false;
	});
});


var placeholders= new Array();
placeholders["sidebarText"] = \'<#sidebar#><br />\';
placeholders["toc"] = \'<#tableofcontents#><br /><span class="sideColumnTitle">In this issue</span><ul><#title_repeater#><li><#article_title#></li><#/title_repeater#></ul><br /><#/tableofcontents#>\';
placeholders["pop"] = \'<#populararticles#><br /><span class="sideColumnTitle">Popular Articles</span><ul><#popular_repeater#><li><#popular_title#></li><#/popular_repeater#></ul><br /><#/populararticles#>\';
placeholders["vm"] = \'<#vm_products#><br /><span class="sideColumnTitle">Top Products</span><table><#vm_repeater#><tr><td align="center"><p><#vm_content#></p></td></tr><#/vm_repeater#></table><br /><#/vm_products#><br />\';
placeholders["twitter"] = \'<#twitter#><span class="sideColumnTitle">Follow us</span><br /><a href="http://twitter.com/<#twitter-name#>"><img src="twitter.png" id="twitterIcon" /></a><#/twitter#>\';
placeholders["facebook"] = \'<#facebook#><a href="<#facebook-url#>"><img src="facebook.png" id="facebookIcon" /></a><#/facebook#>\';
placeholders["myspace"] = \'<#myspace#><a href="http://www.myspace.com/<#myspace-name#>"><img src="myspace.png" id="myspaceIcon" /></a><#/myspace#>\';
placeholders["facebook_share"] = \'*|SHARE:Facebook|*<br />\';
placeholders["facebook_like"] = \'*|FACEBOOK:LIKE|*<br />\';
placeholders["facebook_comments"] = \'*|FACEBOOK:COMMENTS|*<br />\';
placeholders["intro_content"] = \'<#intro_content#><br />\';
placeholders["articleRepeater"] = \'<#repeater#><br /><h2 class="mainColumnTitle"><#title#></h2><#content#><br /><#/repeater#>\';

placeholders["jsProfiles"] = \'<#jomsocialprofiles#><h3 class="mainColumnTitle">Featured Members</h3><table width="100%"><#jomsocialprofilesrepeater#><tr><td width="50"><#jsAvatar#></td><td><#jsName#></td><td><#jsfieldsrepeater#><#jsFieldTitle#>: <#jsFieldValue#><br /><#/jsfieldsrepeater#></td></tr><#/jomsocialprofilesrepeater#></table><#/jomsocialprofiles#>\';
placeholders["jsDiscussions"] = \'<#jomsocialdiscussions#><h3 class="mainColumnTitle">Discussions</h3><table width="100%"><#jomsocialdiscussionsrepeater#><tr><td><#jsDiscussionContent#></td></tr><#/jomsocialdiscussionsrepeater#></table><#/jomsocialdiscussions#>\';
placeholders["aec"] = \'<#aec#><h3 class="mainColumnTitle">Subscription Plans</h3><table width="100%"><#aecrepeater#><tr><td><#aeccontent#></td></tr><#/aecrepeater#></table><#/aec#>\';
placeholders["ambra"] = \'<#ambra#><h3 class="mainColumnTitle">Subscription Plans</h3><table width="100%"><#ambrarepeater#><tr><td><#ambracontent#></td></tr><#/ambrarepeater#></table><#/ambra#>\';

function insertPh(){
	var pos = $("phPosition").value;
	if(pos != ""){
		jQuery.each( jQuery(".phCb"), function(){
			if(this.checked === true){
				jQuery(pos, window.frames[\'previewIframe\'].document).append( placeholders[this.value] );
			}
		} );


		if(pos == ".sideColumnText" || pos == ".defaultText"){
			jQuery(".sideColumnTitle,.mainColumnTitle", window.frames[\'previewIframe\'].document).editable( function(value, settings){ return(value); } ,
										{submit : "OK", cancel : "Cancel",
										data: function(value, settings) {
																			var retval = value.replace(/&lt;/, "<").replace(/&gt;/,">");
																			retval = retval.replace(/&lt;/, "<").replace(/&gt;/,">");
																			retval = retval.replace(/&lt;/, "<").replace(/&gt;/,">");
																			retval = retval.replace(/&lt;/, "<").replace(/&gt;/,">");
																			retval = retval.replace(/&lt;/, "<").replace(/&gt;/,">");
																			return retval;
																		},
										onblur: "submit",
										width: "99%"
										}
									  ).addClass("editable").attr("title","click to edit");
		}

		iframeHeight = jQuery(".backgroundTable", window.frames[\'previewIframe\'].document).height();
		if(iframeHeight){
			jQuery("#previewIframe").attr( "height", iframeHeight+50 );
		}
		jQuery(pos+" a", window.frames[\'previewIframe\'].document).click( function(){
			link = jQuery(this).attr("href").replace(tmplUrl,"");
			alert( link );
			return false;
		});
	}
}
function clearPosition(){
	var pos = $("phPosition").value;
	if(pos != ""){
		var index = $("phPosition").selectedIndex;
		var Text = $("phPosition").options[index].text;
		if( confirm("'.JText::_('JM_ARE_YOU_SURE_TO_DELETE_EVERYTHING_FROM_THE').' "+Text+"'.JText::_('JM_POSITION_DELETE').'") ){
			jQuery(pos, window.frames[\'previewIframe\'].document).text( "" );
		}

		iframeHeight = jQuery(".backgroundTable", window.frames[\'previewIframe\'].document).height();
		if(iframeHeight){
			jQuery("#previewIframe").attr( "height", iframeHeight+50 );
		}
	}
}

function addCSS(){
	var pos = $("cssPosition").value;
	if(pos != ""){
		var fontCustom = $("fontCustom").value;
		if(fontCustom != "'.JText::_('JM_CUSTOM_FONT_FAMILY').'"){
			var font = $("fontCustom").value;
		} else {
			var font = $("font").value;
		}
		jQuery(pos, window.frames[\'previewIframe\'].document).css("font-family", font);
		var fontSize = $("fontSize").value;
		jQuery(pos, window.frames[\'previewIframe\'].document).css("font-size", fontSize);

		if(jQuery("#bold").is(":checked")){
			jQuery(pos, window.frames[\'previewIframe\'].document).css("font-weight", "bold");
		} else {
			jQuery(pos, window.frames[\'previewIframe\'].document).css("font-weight", "normal");
		}
		if(jQuery("#italics").is(":checked")){
			jQuery(pos, window.frames[\'previewIframe\'].document).css("font-style", "italic");
		} else {
			jQuery(pos, window.frames[\'previewIframe\'].document).css("font-style", "normal");
		}
		if(jQuery("#underline").is(":checked")){
			jQuery(pos, window.frames[\'previewIframe\'].document).css("text-decoration", "underline");
		} else {
			jQuery(pos, window.frames[\'previewIframe\'].document).css("text-decoration", "none");
		}

		var color = $("color").value;
		jQuery(pos, window.frames[\'previewIframe\'].document).css("color", color);
	}
}
function addColor(){
	var page = $("page").value;
	jQuery("body", window.frames[\'previewIframe\'].document).attr("bgcolor", page);
	jQuery("body", window.frames[\'previewIframe\'].document).css("background", page);
	jQuery(".backgroundTable", window.frames[\'previewIframe\'].document).css("background", page);
	var header = $("header").value;
	jQuery(".headerTop", window.frames[\'previewIframe\'].document).attr("bgcolor", header);
	jQuery(".headerTop", window.frames[\'previewIframe\'].document).css("background", header);
	jQuery(".headerBar", window.frames[\'previewIframe\'].document).attr("bgcolor", header);
	jQuery(".headerBar", window.frames[\'previewIframe\'].document).css("background", header);
	jQuery(".headerBarText", window.frames[\'previewIframe\'].document).css("background", header);
	var content = $("content").value;
	jQuery(".defaultText", window.frames[\'previewIframe\'].document).attr("bgcolor", content);
	jQuery(".defaultText", window.frames[\'previewIframe\'].document).css("background", content);
	var sidebar = $("sidebar").value;
	jQuery(".sideColumn", window.frames[\'previewIframe\'].document).attr("bgcolor", sidebar);
	jQuery(".sideColumn", window.frames[\'previewIframe\'].document).css("background", sidebar);
	var footerRow = $("footerRow").value;
	jQuery(".footerRow", window.frames[\'previewIframe\'].document).attr("bgcolor", footerRow);
	jQuery(".footerRow", window.frames[\'previewIframe\'].document).css("background", footerRow);
	var footerText = $("footerText").value;
	jQuery(".footerRow", window.frames[\'previewIframe\'].document).css("color", footerText);
	jQuery(".footerText", window.frames[\'previewIframe\'].document).css("color", footerText);

	var style = "<style>";
	var bodyText = $("bodyText").value;
	style = style+"* { color: "+bodyText+";}";
	var headings = $("headings").value;
	style = style+"h1,h2,h3,h4,h5,h6,.sideColumnTitle,.mainColumnTitle,.title,.subTitle { color: "+headings+";}";
	var links = $("links").value;
	style = style+"a { color: "+links+";}";
	jQuery("a", window.frames[\'previewIframe\'].document).css("color", links);
	style = style+"</style>";
	jQuery("body", window.frames[\'previewIframe\'].document).append( style );

	jQuery(".sideColumnTitle", window.frames[\'previewIframe\'].document).css("color", "");
	jQuery(".mainColumnTitle", window.frames[\'previewIframe\'].document).css("color", "");
	jQuery(".title", window.frames[\'previewIframe\'].document).css("color", "");
	jQuery(".subTitle", window.frames[\'previewIframe\'].document).css("color", "");
}
function reloadPalettes(){
	var url = baseUrl + "index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=templates&format=raw&task=reloadPalettes";
	var data = new Object();
	data["hex"] = $("hex").value.replace("#","");
	data["keyword"] = $("keyword").value.replace(" ","+");

	doAjaxTask(url, data, function(postback){
					    jQuery("#palettes").html( postback.html );
					    if(postback.js){
						eval( postback.js );
					    }
					});
}
function applyPalette( x, shuffle ){

		if(!shuffle){
			$("page").value = colorsets[x][0];
			jQuery("#pagePreview").css("background", colorsets[x][0]);
			$("header").value = colorsets[x][2];
			jQuery("#headerPreview").css("background", colorsets[x][2]);
			$("content").value = colorsets[x][1];
			jQuery("#contentPreview").css("background", colorsets[x][1]);
			$("sidebar").value = colorsets[x][1];
			jQuery("#sidebarPreview").css("background", colorsets[x][1]);
			$("footerRow").value = colorsets[x][2];
			jQuery("#footerRowPreview").css("background", colorsets[x][2]);
			$("bodyText").value = colorsets[x][3];
			jQuery("#bodyTextPreview").css("background", colorsets[x][3]);
			$("headings").value = colorsets[x][3];
			jQuery("#headingsPreview").css("background", colorsets[x][3]);
			$("footerText").value = colorsets[x][4];
			jQuery("#footerTextPreview").css("background", colorsets[x][4]);
			$("links").value = colorsets[x][4];
			jQuery("#linksPreview").css("background", colorsets[x][4]);

			addColor();
			jQuery("#apply"+x).attr("href", "javascript:applyPalette("+x+",1)");
		} else {
			r = Math.floor(Math.random()*4);
			$("page").value = colorsets[x][r];
			jQuery("#pagePreview").css("background", colorsets[x][r]);
			r = Math.floor(Math.random()*4);
			$("header").value = colorsets[x][r];
			jQuery("#headerPreview").css("background", colorsets[x][r]);
			r = Math.floor(Math.random()*4);
			$("content").value = colorsets[x][[r]];
			jQuery("#contentPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("sidebar").value = colorsets[x][[r]];
			jQuery("#sidebarPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("footerRow").value = colorsets[x][[r]];
			jQuery("#footerRowPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("bodyText").value = colorsets[x][[r]];
			jQuery("#bodyTextPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("headings").value = colorsets[x][[r]];
			jQuery("#headingsPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("footerText").value = colorsets[x][[r]];
			jQuery("#footerTextPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("links").value = colorsets[x][[r]];
			jQuery("#linksPreview").css("background", colorsets[x][[r]]);

			addColor();
		}

}


function createUploader( buttonId ){
	var uploader = new qq.FileUploader({
		element: document.getElementById( buttonId ),
		action: baseUrl + "index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=templates&format=raw&task=uploadLogo&name="+ jQuery("#template").val(),
		allowedExtensions: ["jpg","jpeg","png","gif","bmp"],
		multiple: false,
		messages: {
			typeError: "{file}: '.JText::_('JM_INVALID_FILE_TYPE').'! '.JText::_('JM_ALLOWED_EXTENSIONS').': {extensions}"
		},
		onSubmit: function(id, fileName){ jQuery(buttonId+" .qq-upload-list").fadeIn(); },
		onComplete: function(id, fileName, responseJSON){ uploadComplete(fileName, buttonId); },
		debug: false
	});
}

function uploadComplete( fileName, buttonId ){
	if(buttonId == "uploadLogo"){
		jQuery(".headerBar img", window.frames[\'previewIframe\'].document).attr("src", "'.JURI::root().'tmp/'.$template_folder[0].'/"+fileName);

		iframeHeight = jQuery(".backgroundTable", window.frames[\'previewIframe\'].document).height();
		if(iframeHeight){
			jQuery("#previewIframe").attr( "height", iframeHeight+50 );
		}

		jQuery("#logoFilename").html( fileName );
		jQuery(".qq-upload-list").fadeOut();
		jQuery("#logoSizeInfo").fadeOut();
	} else {
		buttonId = buttonId.replace("Upload","");
		jQuery("#"+buttonId+"Icon" , window.frames[\'previewIframe\'].document).attr("src", "'.JURI::root().'tmp/'.$template_folder[0].'/"+fileName);
		jQuery("#"+buttonId+"Upload .qq-upload-list").fadeOut();
	}
}


'.$submitFunction.'
	firstColumn = jQuery(".bodyTable td:first", window.frames[\'previewIframe\'].document).attr("class");
	sideColumnExists = jQuery(".sideColumn", window.frames[\'previewIframe\'].document).length;
	$("templateContent").value = jQuery("#previewIframe").contents().find("html").html().replace(\'"\',\'\"\');
	if( firstColumn == "sideColumn" && sideColumnExists ){
		$("columns").value = "l";
	} else if( firstColumn == "defaultText" && sideColumnExists ){
		$("columns").value = "r";
	}
	submitform(pressbutton);
}

var uploadButtonText = "'.JText::_('JM_UPLOAD_HEADER_IMAGE').'";
';



$document->addScriptDeclaration( $script );

//$editor =& JFactory::getEditor();
//$buttons2exclude = array( 'pagebreak', 'readmore' );

$filename = JPATH_ADMINISTRATOR.DS."components/com_joomailermailchimpintegration/templates/".$template_folder[0]."/template.html";
$template = JFile::read( $filename );

$src = JPATH_ADMINISTRATOR.DS."components/com_joomailermailchimpintegration/templates/".$template_folder[0];
$dest = JPATH_SITE.DS.'tmp'.DS.$template_folder[0].DS;
JFolder::create( $dest, 0777);
JFolder::copy( $src, $dest, '', true);


$imagepath = '$1="'.JURI::base().'components/com_joomailermailchimpintegration/templates/'.$template_folder[0].'/$2$3';
$template = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $imagepath, $template);
// prevent preview from being cached
$metaDataArray = array( '<meta http-Equiv="Cache-Control" Content="no-cache">',
						'<meta http-Equiv="Pragma" Content="no-cache">',
						'<meta http-Equiv="Expires" Content="0">');
$template = str_ireplace($metaDataArray, '', $template);

$metaData = '<meta http-Equiv="Cache-Control" Content="no-cache"><meta http-Equiv="Pragma" Content="no-cache"><meta http-Equiv="Expires" Content="0">';
if( !stristr($template, "<head>") ){
	$template = str_ireplace( '<html>', '<head>'.$metaData.'</head>', $template );
} else {
	$template = str_ireplace( '</head>', $metaData.'</head>', $template );
}

$templatesPath = JURI::root().'administrator/components/com_joomailermailchimpintegration/templates';
$template = str_replace( $templatesPath, JURI::root().'tmp', $template);

$tmpFile = JPATH_SITE.DS."tmp/".$template_folder[0]."/template.html";

if(JFile::exists($tmpFile)){
	JFile::delete($tmpFile);
}
JFile::write($tmpFile, $template );
$tmpFileURL = "../tmp/".$template_folder[0]."/template.html";
?>
<form action="index.php?option=com_joomailermailchimpintegration&view=templates" method="post" name="adminForm" enctype="multipart/form-data" >
<div id="templateEditor">
    <div id="preview">
	<?php /*include($filename);*/ ?>
	<?php /* echo $template; */ ?>
	<iframe name="previewIframe" id="previewIframe" src="<?php echo $tmpFileURL;?>" width="100%" height="500" style="margin:0; border:0px solid #fff;">
	    <p>Your browser does not support iframes.</p>
	</iframe>
    </div>
    <h1 id="optionsTitle"><?php echo JText::_('JM_TEMPLATE_OPTIONS');?></h1>
    <table style="float:right;">
	<tr>
	    <td><a href="#" onclick="javascript:<?php if(version_compare(JVERSION,'1.6.0','ge')){ echo 'Joomla.'; } ?>submitbutton('save')" class="JMbuttonOrange" style="margin-right:3px;"><?php echo (version_compare(JVERSION,'1.6.0','ge')) ? JText::_('JAPPLY') : JText::_('SAVE');?></a></td>
	    <td><a href="#" onclick="javascript:<?php if(version_compare(JVERSION,'1.6.0','ge')){ echo 'Joomla.'; } ?>submitbutton('cancel')" class="JMbuttonOrange"><?php echo (version_compare(JVERSION,'1.6.0','ge')) ? JText::_('JCANCEL') : JText::_('CANCEL');?></a></td>
	</tr>
    </table>
    <div id="options">
	<div class="optionsHeader" rel="title">
	<?php echo JText::_('JM_TEMPLATE_NAME');?>
	<div class="optionsHeader_r" rel="title"></div>
	</div>
	<div class="optionsContent" id="title">
	<input type="text" size="30" name="template" id="template" value="<?php echo $template_folder[0];?>" />
	<div style="position:relative;top:6px;"><?php echo JText::_('JM_USE_ONLY_LETTERS_NUMBERS_AND_UNDERSCORES');?></div>
	</div>
	<div class="optionsHeader" rel="logo">
	<?php echo JText::_('JM_CUSTOM_HEADER');?>
	<div class="optionsHeader_r" rel="logo"></div>
	</div>
	<div class="optionsContent" id="logo">
	<div id="uploadLogo"></div>
	<div id="logoFilename"></div>
	<div id="logoSizeInfo"></div>
	<table>
	    <tr>
		<td><?php echo JText::_('JM_LINK_URL');?></td>
		<td><input type="text" id="logoUrl" name="logoUrl" value="" /></td>
		<td><?php echo JText::_('JM_LINK_URL_INFO');?></td>
	    </tr>
	    <tr>
		<td><?php echo JText::_('JM_ALTERNATE_TEXT');?></td>
		<td><input type="text" id="logoAlt" name="logoAlt" value="" /></td>
		<td><?php echo JText::_('JM_ALTERNATE_TEXT_INFO');?></td>
	    </tr>
	</table>
	<br />
	<a href="javascript:void(0)" id="insertLogoUrl" class="JMbuttonOrange" title="<?php echo JText::_('JM_INSERT_LINK');?>"><?php echo JText::_('JM_INSERT_LINK');?></a>
	</div>
	    <div class="optionsHeader" rel="placeholderOptions">
		<?php echo JText::_('JM_PLACEHOLDER_ELEMENTS');?>
		<div class="optionsHeader_r" rel="placeholderOptions"></div>
	    </div>
	    <div class="optionsContent" id="placeholderOptions">
		<select id="phPosition">
		    <option value=""><?php echo JText::_('JM_SELECT_POSITION');?></option>
		    <option value=".sideColumnText"><?php echo JText::_('JM_SIDEBAR');?></option>
		    <option value=".defaultText"><?php echo JText::_('JM_CONTENT');?></option>
		</select>
		<div id="phOptions">
		    <p><?php echo JText::_('JM_CLEAR_POSITION_INFO');?></p>
		    <a href="javascript:void(0);" id="toggleSelect" title="<?php echo Jtext::_('JM_SELECT_ALL_NONE');?>"><?php echo Jtext::_('JM_SELECT_ALL_NONE');?></a>
		    <ul id="placeholders">
		    <li class="draggable"><input type="checkbox" class="phCb" value="sidebarText" id="sidebarText"/><label for="sidebarText"><?php echo JText::_('JM_SIDEBAR_TEXT');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="toc" id="toc"/><label for="toc"><?php echo JText::_('JM_TABLE_OF_CONTENTS');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="pop" id="pop"/><label for="pop"><?php echo JText::_('JM_POPULAR_ARTICLES');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="vm" id="vm"/><label for="vm"><?php echo JText::_('JM_VIRTUEMART_PRODUCTS');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="twitter" id="twitter"/><label for="twitter"><?php echo JText::_('JM_TWITTER_ICON');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="facebook" id="facebook"/><label for="facebook"><?php echo JText::_('JM_FACEBOOK_ICON');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="myspace" id="myspace"/><label for="myspace"><?php echo JText::_('JM_MYSPACE_ICON');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="facebook_share" id="facebook_share"/><label for="facebook_share"><?php echo JText::_('JM_FACEBOOK_SHARE_BUTTON');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="facebook_like" id="facebook_like"/><label for="facebook_like"><?php echo JText::_('JM_FACEBOOK_LIKE_BUTTON');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="facebook_comments" id="facebook_comments"/><label for="facebook_comments"><?php echo JText::_('JM_FACEBOOK_COMMENTS_BUTTON');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="intro_content" id="intro_content"/><label for="intro_content"><?php echo JText::_('JM_INTRO_TEXT');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="articleRepeater" id="articleRepeater"/><label for="articleRepeater"><?php echo JText::_('JM_CONTENT_ARTICLES');?></label></li>

		    <li class="draggable"><input type="checkbox" class="phCb" value="jsProfiles" id="jsProfiles"/><label for="jsProfiles"><?php echo JText::_('JM_JOMSOCIAL_PROFILES');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="jsDiscussions" id="jsDiscussions"/><label for="jsDiscussions"><?php echo JText::_('JM_JOMSOCIAL_DISCUSSIONS');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="aec" id="aec"/><label for="aec"><?php echo JText::_('JM_AEC_PLANS');?></label></li>
		    <li class="draggable"><input type="checkbox" class="phCb" value="ambra" id="ambra"/><label for="ambra"><?php echo JText::_('JM_AMBRA_SUBS');?></label></li>

		    </ul>
		    <a href="javascript:insertPh();" class="JMbuttonOrange"><?php echo JText::_('JM_INSERT');?></a>
		    <a href="javascript:clearPosition();" class="JMbuttonOrange"><?php echo JText::_('JM_CLEAR_POSITION');?></a>
		    <div style="clear:both;"></div>

		    <div id="twitterUpload" title="<?php echo JText::_('JM_UPLOAD_TWITTER_ICON');?>"></div>
		    <div id="facebookUpload" title="<?php echo JText::_('JM_UPLOAD_FACEBOOK_ICON');?>"></div>
		    <div id="myspaceUpload" title="<?php echo JText::_('JM_UPLOAD_MYSPACE_ICON');?>"></div>
		    <div style="clear:both;"></div>
		</div>
	    </div>

	    <div class="optionsHeader" rel="typography">
		<?php echo JText::_('JM_TYPOGRAPHY');?>
		<div class="optionsHeader_r" rel="typography"></div>
	    </div>
	    <div class="optionsContent" id="typography">
		<select id="cssPosition">
			<option value=""><?php echo JText::_('JM_SELECT_ELEMENT_TO_STYLE');?></option>
			<option value=".sideColumnText"><?php echo JText::_('JM_SIDEBAR');?></option>
			<option value=".sideColumnTitle"><?php echo JText::_('JM_SIDEBAR_TITLES');?></option>
			<option value=".defaultText"><?php echo JText::_('JM_CONTENT');?></option>
			<option value=".mainColumnTitle, .title"><?php echo JText::_('JM_CONTENT_TITLES');?></option>
			<option value=".subTitle"><?php echo JText::_('JM_CONTENT_SUB_TITLES');?></option>
		</select>
		<br />
		<br />
		<?php echo JText::_('JM_FONT_FAMILY');?>
		<select id="font">
			<optgroup label="sans-serif">
				<option value="Arial, sans-serif">Arial, sans-serif</option>
				<option value="GillSans, Calibri, Trebuchet, sans-serif">GillSans, Calibri, Trebuchet, sans-serif</option>
				<option value="Tahoma, Verdana, Geneva">Tahoma, Verdana, Geneva</option>
				<option value="Trebuchet, Tahoma, Arial, sans-serif">Trebuchet, Tahoma, Arial, sans-serif</option>
				<option value="Impact, Haettenschweiler, ‘Arial Narrow Bold’, sans-serif">Impact, Haettenschweiler, ‘Arial Narrow Bold’, sans-serif</option>
				<option value="Futura, ‘Century Gothic’, AppleGothic, sans-serif">Futura, ‘Century Gothic’, AppleGothic, sans-serif</option>
			</optgroup>
			<optgroup label="serif">
				<option value="Baskerville, ‘Times New Roman’, Times, serif">Baskerville, ‘Times New Roman’, Times, serif</option>
				<option value="Garamond, ‘Hoefler Text’, ‘Times New Roman’, Times, serif">Garamond, ‘Hoefler Text’, ‘Times New Roman’, Times, serif</option>
				<option value="Georgia, Times, ‘Times New Roman’, serif">Georgia, Times, ‘Times New Roman’, serif</option>
				<option value="Palatino, ‘Palatino Linotype’, ‘Hoefler Text’, Times, ‘Times New Roman’, serif">Palatino,‘Palatino Linotype’,‘Hoefler Text’,Times,‘Times New Roman’,serif</option>
				<option value="Cambria, Georgia, Times, ‘Times New Roman’, serif">Cambria, Georgia, Times, ‘Times New Roman’, serif</option>
				<option value="‘Copperplate Light’, ‘Copperplate Gothic Light’, serif">‘Copperplate Light’, ‘Copperplate Gothic Light’, serif</option>
			</optgroup>
		</select>
		<input type="text" id="fontCustom" size="30" value="<?php echo JText::_('JM_CUSTOM_FONT_FAMILY');?>" onclick="if(this.value=='<?php echo JText::_('JM_CUSTOM_FONT_FAMILY');?>'){ this.value=''; }" onblur="if(this.value==''){ this.value='<?php echo JText::_('JM_CUSTOM_FONT_FAMILY');?>'; }"/>
		<br />
		<br />
		<?php echo JText::_('JM_FONT_SIZE');?>
		<select id="fontSize">
		<option value="6px">6px</option>
		<option value="7px">7px</option>
		<option value="8px">8px</option>
		<option value="9px">9px</option>
		<option value="10px">10px</option>
		<option value="11px">11px</option>
		<option value="12px" selected="selected">12px</option>
		<option value="13px">13px</option>
		<option value="14px">14px</option>
		<option value="15px">15px</option>
		<option value="16px">16px</option>
		<option value="17px">17px</option>
		<option value="18px">18px</option>
		<option value="19px">19px</option>
		<option value="20px">20px</option>
		<option value="21px">21px</option>
		<option value="22px">22px</option>
		<option value="23px">23px</option>
		<option value="24px">24px</option>
		<option value="25px">25px</option>
		<option value="26px">26px</option>
		<option value="27px">27px</option>
		<option value="28px">28px</option>
		<option value="29px">29px</option>
		</select>
		<br />
		<br />
		<input type="checkbox" id="bold" value="bold" /><label for="bold"><?php echo JText::_('JM_BOLD');?></label>
		<input type="checkbox" id="italics" value="italics" /><label for="italics"><?php echo JText::_('JM_ITALICS');?></label>
		<input type="checkbox" id="underline" value="underline" /><label for="underline"><?php echo JText::_('JM_UNDERLINE');?></label>
		<br />

		<table>
			<tr>
				<td><?php echo JText::_('JM_COLOR');?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
				<td><div class="colorPreviewBox"><div class="colorPreview" id="colorPreview" onclick="openPicker('color')"></div></div></td>
				<td><input type="text" class="colorValue" id="color" size="7" maxlength="7" value="#000000" onclick="openPicker('color')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
			</tr>
		</table>

		<br />
		<a href="javascript:addCSS();" class="JMbuttonOrange"><?php echo JText::_('JM_APPLY_CSS');?></a>
	    </div>

	    <div class="optionsHeader" rel="colors">
		<?php echo JText::_('JM_TEMPLATE_COLORS');?>
		<div class="optionsHeader_r" rel="colors"></div>
	    </div>
	    <div class="optionsContent" id="colors">
		<?php echo JText::_('JM_CHOOSE_YOUR_OWN');?>:<br />
		<table>
		    <tr>
			<td><?php echo JText::_('JM_PAGE_BACKGROUND');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="pagePreview" onclick="openPicker('page')"></div></div></td>
			<td><input type="text" class="colorValue" id="page" size="7" maxlength="7" value="#000000" onclick="openPicker('page')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    </tr>
		    <tr>
			<td><?php echo JText::_('JM_HEADER_BACKGROUND');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="headerPreview" onclick="openPicker('header')"></div></div></td>
			<td><input type="text" class="colorValue" id="header" size="7" maxlength="7" value="#000000" onclick="openPicker('header')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    </tr>
		    <tr>
			<td><?php echo JText::_('JM_CONTENT_BACKGROUND');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="contentPreview" onclick="openPicker('content')"></div></div></td>
			<td><input type="text" class="colorValue" id="content" size="7" maxlength="7" value="#000000" onclick="openPicker('content')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    </tr>
		    <tr>
			<td><?php echo JText::_('JM_SIDEBAR_BACKGROUND');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="sidebarPreview" onclick="openPicker('sidebar')"></div></div></td>
			<td><input type="text" class="colorValue" id="sidebar" size="7" maxlength="7" value="#000000" onclick="openPicker('sidebar')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    </tr>
		    <tr>
			<td><?php echo JText::_('JM_FOOTER_BACKGROUND');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="footerRowPreview" onclick="openPicker('footerRow')"></div></div></td>
			<td><input type="text" class="colorValue" id="footerRow" size="7" maxlength="7" value="#000000" onclick="openPicker('footerRow')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    </tr>
		    <tr>
			<td><?php echo JText::_('JM_BODY_TEXT');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="bodyTextPreview" onclick="openPicker('bodyText')"></div></div></td>
			<td><input type="text" class="colorValue" id="bodyText" size="7" maxlength="7" value="#000000" onclick="openPicker('bodyText')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    </tr>
		    <tr>
			<td><?php echo JText::_('JM_FOOTER_TEXT');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="footerTextPreview" onclick="openPicker('footerText')"></div></div></td>
			<td><input type="text" class="colorValue" id="footerText" size="7" maxlength="7" value="#000000" onclick="openPicker('footerText')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    </tr>
		    <tr>
			<td><?php echo JText::_('JM_HEADINGS');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="headingsPreview" onclick="openPicker('headings')"></div></div></td>
			<td><input type="text" class="colorValue" id="headings" size="7" maxlength="7" value="#000000" onclick="openPicker('headings')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    </tr>
		    <tr>
			<td><?php echo JText::_('JM_LINKS');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="linksPreview" onclick="openPicker('links')"></div></div></td>
			<td><input type="text" class="colorValue" id="links" size="7" maxlength="7" value="#000000" onclick="openPicker('links')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    </tr>
		</table>

		<br />
		<i><?php echo JText::_('JM_IMPORT_FROM_COLOURLOVERS');?>:</i>
		<br />
		<br />
		<div id="palettes">
		<?php
		$js = 'var colorsets = [];';
		$i=0;
		foreach ($this->palettes as $color) {
		    foreach ($color as $c) {
			$js .= 'colorsets['.$i.'] = [];';
			echo '<div class="color_list" style="margin-bottom: 3px;">';
			echo '<div class="color_samples" style="display:inline-block;width:125px;">';
		//	echo '<a href="'.$c->url.'" target="_blank" title="'.JText::_('details').'">';
			echo '<a href="javascript:applyPalette('.$i.');" id="apply'.$i.'" title="'.JText::_('JM_SELECT').'">';
			$x=0;
			foreach($c->colors as $cc) {
			    echo '<div style="background:#'.$cc.' none repeat scroll 0 0 !important; width: 25px; height: 10px; float: left;"></div>';
			    $js .= 'colorsets['.$i.']['.$x.'] = "#'.$cc.'";';
			    $x++;
			}
			echo '</a>';
			echo '</div>';
			echo '<a href="'.$c->url.'" target="_blank" class="ColorSetInfo">'.JText::_('JM_DETAILS').'</a><br />';
			echo $c->title.'<br /><div class="clr"></div></div><div class="clr"></div>';
		    }
		    $i++;
		}
		$document->addScriptDeclaration( $js );
		?>
		</div>
		<a href="http://www.colourlovers.com/palettes" target="_blank" style="text-decoration:underline;"><?php echo JText::_('JM_VIEW_MORE');?></a>
		<br />
		<br />
		<table>
		    <tr>
			<td><?php echo JText::_('JM_BASE_COLOR');?></td>
			<td><div class="colorPreviewBox"><div class="colorPreview" id="hexPreview" onclick="openPicker('hex')"></div></div></td>
			<td><input type="text" class="colorValue" id="hex" size="7" maxlength="7" onclick="openPicker('hex')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" /></td>
		    </tr>
		    <tr>
			<td><?php echo JText::_('JM_KEYWORD');?></td>
			<td colspan="2"><input type="text" id="keyword" /></td>
		    </tr>
		</table>
		<br />
		<a href="javascript:reloadPalettes();" class="JMbuttonOrange" title="<?php echo JText::_('JM_RELOAD_PALETTES');?>"><?php echo JText::_('JM_RELOAD_PALETTES');?></a>
		<a href="javascript:addColor();" class="JMbuttonOrange" title="<?php echo JText::_('JM_APPLY_COLORS');?>"><?php echo JText::_('JM_APPLY_COLORS');?></a>
		<div style="clear:both;"></div>

	    </div>
	</div>
</div>

<div style="clear:both;"></div>


<input type="hidden" name="columns" id="columns" value="0" />
<input type="hidden" name="templateOld" id="templateOld" value="<?php echo $template_folder[0];?>" />
<input type="hidden" name="templateContent" id="templateContent" value="" />

<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="templates" />
<input type="hidden" name="type" value="templates" />
<input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_joomailermailchimpintegration&view=templates'); ?>" />
</form>

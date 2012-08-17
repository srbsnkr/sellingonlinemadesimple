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

$document = & JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components/com_joomailermailchimpintegration/assets/css/shareReport.css');
$document->addScript(JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/js/picker.js');
$document->addStyleSheet(JURI::base().'components/com_joomailermailchimpintegration/assets/css/picker.css');
$document->addScript(JURI::root().'administrator/components/com_joomailermailchimpintegration/assets/js/fileuploader.js');
$document->addStyleSheet(JURI::base().'components/com_joomailermailchimpintegration/assets/css/fileuploader.css');

$script = 'var baseUrl = "'.JURI::base().'";
jQuery(document).ready(function() {
	var currentColor = "#000000";
	jQuery.each( jQuery(".colorPreviewBox"), function(){ 
		currentId = jQuery(".colorPreview", this).attr("id").replace("Preview", "");
		currentColor = jQuery("#"+currentId).val();
		jQuery(".colorPreview", this).css("background-color", currentColor ); 
	});
	
	createUploader( "uploadLogo" );
	
	jQuery(".colorValue").keyup( function(){ 
		removeLayer("picker"); 
		currentId = jQuery(this).attr("id").replace("Value", "");
		jQuery("#"+currentId+"Preview").css("background-color", jQuery(this).val() );   
	}).blur( function(){ 
		removeLayer("picker"); 
		currentId = jQuery(this).attr("id").replace("Value", "");
		jQuery("#"+currentId+"Preview").css("background-color", jQuery(this).val() );   
	});
});

function applyPalette( x, shuffle ){
	
		if(!shuffle){
			$("bg_color").value = colorsets[x][0];
			jQuery("#bg_colorPreview").css("background", colorsets[x][0]);
			$("header_color").value = colorsets[x][1];
			jQuery("#header_colorPreview").css("background", colorsets[x][1]);
			$("current_tab").value = colorsets[x][2];
			jQuery("#current_tabPreview").css("background", colorsets[x][2]);
			$("current_tab_text").value = colorsets[x][3];
			jQuery("#current_tab_textPreview").css("background", colorsets[x][3]);
			$("normal_tab").value = colorsets[x][4];
			jQuery("#normal_tabPreview").css("background", colorsets[x][4]);
			$("normal_tab_text").value = colorsets[x][3];
			jQuery("#normal_tab_textPreview").css("background", colorsets[x][3]);
			$("hover_tab").value = colorsets[x][2];
			jQuery("#hover_tabPreview").css("background", colorsets[x][2]);
			$("hover_tab_text").value = colorsets[x][3];
			jQuery("#hover_tab_textPreview").css("background", colorsets[x][3]);
			
			jQuery("#apply"+x).attr("href", "javascript:applyPalette("+x+",1)");
		} else {
			r = Math.floor(Math.random()*4);
			$("bg_color").value = colorsets[x][[r]];
			jQuery("#bg_colorPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("header_color").value = colorsets[x][[r]];
			jQuery("#header_colorPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("current_tab").value = colorsets[x][[r]];
			jQuery("#current_tabPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("normal_tab").value = colorsets[x][[r]];
			jQuery("#normal_tabPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("hover_tab").value = colorsets[x][[r]];
			jQuery("#hover_tabPreview").css("background", colorsets[x][[r]]);
			r = Math.floor(Math.random()*4);
			$("current_tab_text").value = colorsets[x][[r]];
			jQuery("#current_tab_textPreview").css("background", colorsets[x][[r]]);
			$("normal_tab_text").value = colorsets[x][[r]];
			jQuery("#normal_tab_textPreview").css("background", colorsets[x][[r]]);
			$("hover_tab_text").value = colorsets[x][[r]];
			jQuery("#hover_tab_textPreview").css("background", colorsets[x][[r]]);
		}
	
}

function reloadPalettes(){
	var url = baseUrl + "index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=templates&format=raw&task=reloadPalettes";
	var data = new Object();
	data["showName"] = 0;
	data["float"] = 1;
	
	doAjaxTask(url, data, function(postback){   jQuery("#palettes").html( postback.html );
						    if(postback.js){
							eval( postback.js );
						    }
	});
}

function createUploader( buttonId ){
	var uploader = new qq.FileUploader({
		element: document.getElementById( buttonId ),
		action: baseUrl + "index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=campaigns&format=raw&task=uploadLogo",
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
	jQuery("#logoPreview").css("display","block");
	jQuery("#logoPreview img").attr("src", "'.JURI::root().'tmp/"+fileName);
	jQuery("#logoUrl").val("'.JURI::root().'tmp/"+fileName);
	jQuery("#headerTypeImage").attr("checked", true);
}

function refreshPreview(){
	var url = baseUrl + "index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=campaigns&format=raw&task=refreshShareReport";
	var data = new Object();
	data["cid"] = jQuery( "#cid" ).val();
	data["type"] = jQuery("input:radio[name=headerType]:checked").val();
	data["logo"] = jQuery( "#logoUrl" ).val();
	data["css"] = jQuery( "#css" ).val();
	data["title"] = jQuery( "#title" ).val();
	data["email"] = jQuery( "#email" ).val();
	data["bg_color"] = jQuery( "#bg_color" ).val();
	data["header_color"] = jQuery( "#header_color" ).val();
	data["current_tab"] = jQuery( "#current_tab" ).val();
	data["current_tab_text"] = jQuery( "#current_tab_text" ).val();
	data["normal_tab"] = jQuery( "#normal_tab" ).val();
	data["normal_tab_text"] = jQuery( "#normal_tab_text" ).val();
	data["hover_tab"] = jQuery( "#hover_tab" ).val();
	data["hover_tab_text"] = jQuery( "#hover_tab_text" ).val();
	
	doAjaxTask(url, data, function(postback){
	    jQuery("#reportPreview").html( postback.iframe );
	    jQuery("#directLink a").html( postback.url ).attr("href", postback.url);
	});
}
';
$document->addScriptDeclaration( $script );

$model =& $this->getModel('campaigns');

$cData = $model->getCampaignData( JRequest::getVar( 'cid', '', 'get', 'string' ) );
if(isset($cData[0]->name)) {
    $name = $cData[0]->name;
} else {
    $name = $cData[0]['title'];
}
$data  = $model->getShareReport( JRequest::getVar( 'cid', '', 'get', 'string' ), JText::_('JM_CAMPAIGN_REPORT').': '.$name );
?>
<script type="text/javascript">
<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>
Joomla.submitbutton = function(pressbutton) {
<?php } else { ?>
function submitbutton(pressbutton) {
<?php } ?>
    if ( $('email').value == '' ){
	alert('<?php echo JText::_( 'JM_INVALID_EMAIL' ); ?>');
    } else if( $('secure').getProperty('checked')==true && $('password').value == ''){
	alert('<?php echo JText::_( 'JM_ENTER_PASSWORD' ); ?>');
    } else {
	joomailermailchimpintegration_ajax_loader();
	<?php if(version_compare(JVERSION,'1.6.0','ge')){ ?>Joomla.<?php } ?>submitform(pressbutton);
    }
}

function validateEmail(email){
    if ( !checkEmail( email ) ) {
	alert('<?php echo JText::_( 'JM_INVALID_EMAIL' ); ?>');
	$('email').value = '';
	$('email').focus();
    }
}
function checkEmail(email) {
    if(email==''){
	return true;
    } else {
	var pattern = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	return pattern.test(email);
    }
}
</script>
<form name="adminForm" action="index.php" method="post">
<h3 style="margin:0 0 1em 0;"><?php echo $name;?></h3>
<div id="selectCampaign" style="margin:0 0 1em 0;">
    <h3 style="margin:0;float:left;"><?php echo JText::_('JM_DIRECT_LINK');?>:</h3>
    <span id="directLink"><a href="<?php echo $data['url'];?>" target="_blank"><?php echo $data['url'];?></a></span>
</div>
<table class="noPadding" width="100%">
    <tr valign="top">
	    <td width="380">
<div id="shareReport">
	<div id="shareReportTitle">
	<h3><?php echo JText::_('JM_SEND_REPORT');?></h3>
	</div>
	<div id="shareReportTable">
	<table>
	    <tr>
		<td align="right" nowrap="nowrap"><label for="title"><?php echo JText::_('JM_PAGE_TITLE');?>:</label></td>
		<td><input type="text" name="title" id="title" value="<?php echo JText::_('JM_CAMPAIGN_REPORT').': '.$name;?>" size="30" onfocus="if(this.value=='<?php echo 'Campaign Report: '.$name;?>'){this.value='';}" onblur="if(this.value==''){this.value='<?php echo 'Campaign Report: '.$name;?>';}" /></td>
	    </tr>
	    <tr>
		<td align="right" nowrap="nowrap"><label for="email"><?php echo JText::_('JM_SEND_TO');?>:</label></td>
		<td><input type="text" name="email" id="email" value="" size="30" onchange="validateEmail(this.value)" /></td>
	    </tr>
	    <tr>
		<td align="right" nowrap="nowrap"><label for="secure"><?php echo JText::_('JM_REQUIRE_PASSWORD');?>:</label></td>
		<td><input type="checkbox" class="checkbox" name="secure" id="secure" value="1" checked="checked" /></td>
	    </tr>
	    <tr>
		<td align="right" nowrap="nowrap"><label for="password"><?php echo JText::_('JM_PASSWORD');?>:</label></td>
		<td><input type="text" name="password" id="password" value="" size="30" /></td>
	    </tr>
	    <tr>
		<td></td>
		<td><div class="sendOptionsButton" style="float: none;">
		    <a  id="sendNowButton" href="javascript: submitbutton('sendShareReport');" id="editDraft"><?php echo JText::_( 'JM_SEND' ); ?></a>
		</div>
		</td>
	    </tr>
	</table>
	</div>
</div>
<div style="clear: both;"></div>
 </td>
 <td>
<div id="shareReportOptions">
	<div id="shareReportOptionsTitle">
	<h3><?php echo JText::_('JM_DESIGN');?></h3>
	</div>
	<div id="shareReportOptionsTable">
	    <table>
		<tr>
		    <td colspan="3"><?php echo JText::_('JM_THEME');?></td>
		</tr>
		<tr>
		    <td><?php echo JText::_('JM_BACKGROUND');?></td>
		    <td><div class="colorPreviewBox"><div class="colorPreview" id="bg_colorPreview" onclick="openPicker('bg_color')"></div></div></td>
		    <td><input type="text" class="colorValue" id="bg_color" name="bg_color" size="7" maxlength="7" value="#000000" onclick="openPicker('bg_color')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    <td><?php echo JText::_('JM_HEADER');?></td>
		    <td><div class="colorPreviewBox"><div class="colorPreview" id="header_colorPreview" onclick="openPicker('header_color')"></div></div></td>
		    <td><input type="text" class="colorValue" id="header_color" name="header_color" size="7" maxlength="7" value="#000000" onclick="openPicker('header_color')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		</tr>
		<tr>
		    <td><?php echo JText::_('JM_CURRENT_TAB');?></td>
		    <td><div class="colorPreviewBox"><div class="colorPreview" id="current_tabPreview" onclick="openPicker('current_tab')"></div></div></td>
		    <td><input type="text" class="colorValue" id="current_tab" name="current_tab" size="7" maxlength="7" value="#000000" onclick="openPicker('current_tab')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    <td><?php echo JText::_('JM_CURRENT_TAB_TEXT');?></td>
		    <td><div class="colorPreviewBox"><div class="colorPreview" id="current_tab_textPreview" onclick="openPicker('current_tab_text')"></div></div></td>
		    <td><input type="text" class="colorValue" id="current_tab_text" name="current_tab_text" size="7" maxlength="7" value="#000000" onclick="openPicker('current_tab_text')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		</tr>
		<tr>
		    <td><?php echo JText::_('JM_NORMAL_TAB');?></td>
		    <td><div class="colorPreviewBox"><div class="colorPreview" id="normal_tabPreview" onclick="openPicker('normal_tab')"></div></div></td>
		    <td><input type="text" class="colorValue" id="normal_tab" name="normal_tab" size="7" maxlength="7" value="#000000" onclick="openPicker('normal_tab')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    <td><?php echo JText::_('JM_NORMAL_TAB_TEXT');?></td>
		    <td><div class="colorPreviewBox"><div class="colorPreview" id="normal_tab_textPreview" onclick="openPicker('normal_tab_text')"></div></div></td>
		    <td><input type="text" class="colorValue" id="normal_tab_text" name="normal_tab_text" size="7" maxlength="7" value="#000000" onclick="openPicker('normal_tab_text')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		</tr>
		<tr>
		    <td><?php echo JText::_('JM_HOVERED_TAB');?></td>
		    <td><div class="colorPreviewBox"><div class="colorPreview" id="hover_tabPreview" onclick="openPicker('hover_tab')"></div></div></td>
		    <td><input type="text" class="colorValue" id="hover_tab" name="hover_tab" size="7" maxlength="7" value="#000000" onclick="openPicker('hover_tab')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		    <td><?php echo JText::_('JM_HOVERED_TAB_TEXT');?></td>
		    <td><div class="colorPreviewBox"><div class="colorPreview" id="hover_tab_textPreview" onclick="openPicker('hover_tab_text')"></div></div></td>
		    <td><input type="text" class="colorValue" id="hover_tab_text" name="hover_tab_text" size="7" maxlength="7" value="#000000" onclick="openPicker('hover_tab_text')" onkeyup="if(this.value.substr(0, 1) != '#') this.value = '#' + this.value" onchange="if(this.value==''){this.value = '#000000'; }" /></td>
		</tr>
	    </table>
	    <div id="palettes">
		<?php
		$js = 'var colorsets = [];';
		$i=0;
		foreach ($this->palettes as $color) {
		    foreach ($color as $c) {
			$js .= 'colorsets['.$i.'] = [];';
			echo '<div class="color_list" style="margin-bottom: 3px;">';
			echo '<div class="color_samples" style="display:inline-block;width:125px;">';
		//	echo '<a href="'.$c->url.'" target="_blank" title="'.JText::_('JM_DETAILS').'">';
			echo '<a href="javascript:applyPalette('.$i.');" id="apply'.$i.'" title="'.JText::_('JM_SELECT').'">';
			$x=0;
			foreach($c->colors as $cc) {
			    echo '<div style="background:#'.$cc.' none repeat scroll 0 0 !important; width: 25px; height: 10px; float: left;"></div>';
			    $js .= 'colorsets['.$i.']['.$x.'] = "#'.$cc.'";';
			    $x++;
			}
			echo '</a>';
			echo '</div>';
			echo '<a href="'.$c->url.'" target="_blank" class="ColorSetInfo">'.JText::_('JM_DETAILS').'</a>';
			echo '<div class="clr"></div></div>';
		    }
		    $i++;
		}
		$document->addScriptDeclaration( $js );
		?>
	    </div>
	    <a href="javascript:reloadPalettes();" class="refresh" style="float:left;" title="<?php echo JText::_('JM_RELOAD_PALETTES');?>"><?php echo JText::_('JM_RELOAD_PALETTES');?></a>
	    <div class="clr"></div>
	    <br />
	    <?php echo JText::_('JM_HEADER_TYPE');?>
	    <div id="typeSelect">
	    <input type="radio" name="headerType" id="headerTypeText" value="text" checked="checked" /><label for="headerTypeText"><?php echo JText::_('JM_TEXT');?></label>
	    <input type="radio" name="headerType" id="headerTypeImage" value="image" /><label for="headerTypeImage"><?php echo JText::_('JM_IMAGE');?></label>
	    </div>
	    <br />
	    <div style="float:left;"><?php echo JText::_('JM_UPLOAD_IMAGE');?>: </div>
	    <div id="uploadLogo"></div>
	    <div style="float:left;"><?php echo JText::_('JM_IMAGE_MAX_SIZE');?></div>
	    <div style="float:left;margin-left: 30px;" id="logoPreview"><img src="" width="150" /></div>
	    <input type="hidden" name="logoUrl" id="logoUrl" value="" />
	    <div style="clear: both;"></div>
	    <br />
	    <label for="css"><?php echo JText::_('JM_ADDITIONAL_CSS');?>: </label><input type="text" name="css" id="css" value="" /> <?php echo JText::_('JM_CSS_URL_INFO');?>

	    <div style="clear: both;"></div>
	    <div style="width:100%;text-align:center;">
	    <a href="javascript:refreshPreview()" id="previewButton"><?php echo JText::_('JM_PREVIEW');?></a>
	    </div>
	</div>	
</div>


</td>
</tr>
</table>


<input type="hidden" name="cid" id="cid" value="<?php echo JRequest::getVar( 'cid', '', 'get', 'string' );?>" />
<input type="hidden" name="option" value="com_joomailermailchimpintegration" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="campaigns" />
</form>
<br />
<div id="reportPreview">
<iframe src="<?php echo $data['url'];?>" width="100%" height="800"></iframe>
</div>

<?php
/**
 * Copyright (C) 2011  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

$user =& JFactory::getUser();
$uri  =& JURI::getInstance();
$rand = rand(1000,9999);

$doc =& JFactory::getDocument();
$doc->addStyleSheet( JURI::root().'modules/mod_mailchimpsignup/assets/css/default.css' );
?>  
<script type="text/javascript">
/* <![CDATA[ */
window.addEvent('load', function() {
    $('mcsignupSubmit<?php echo $rand;?>').addEvent('click', function(){
	var isMember = <?php echo $user->id;?>;
	// Validate email address with regex
	if ( !checkEmail( document.mcsignupForm<?php echo $rand;?>.EMAIL.value ) )
	{
	    alert("<?php echo JText::_( 'JM_EMAIL_ERROR' );?>");
	    return;
	}
	rVal = true;
	var doneElements = '';
	$$('#mcsignup<?php echo $rand;?> .mcsignupRequired').each(function(el){
			
	    if(rVal){
		var defVal = el.getProperty('title');
		var thisName = el.getProperty('name');
		if( (el.getProperty('type') == 'checkbox' || el.getProperty('type') == 'radio') && el.getProperty('checked') == false ){
		    if( doneElements.indexOf( thisName )){
			doneElements = doneElements + thisName;
			rVal = false;
			return false;
		    } else {
			valid = -1;
			for (i = document.mcsignupForm<?php echo $rand;?>.elements[thisName].length-1; i > -1; i--) {
			    if ( document.mcsignupForm<?php echo $rand;?>.elements[thisName].checked ) {
				valid = i;
				i = -1;
			    }
			}
			if (valid == -1) {
			    alert( defVal.replace(' \*','')+' <?php echo JText::_('JM_IS_REQUIRED');?>');
			    rVal = false;
			    return false;
			}
		    }
		} else if(el.value == '' || ( el.value == defVal && ! isMember ) ){
		    alert( defVal.replace(' \*','')+' <?php echo JText::_('JM_IS_REQUIRED');?>');
		    rVal = false;
		    return false;
		}

		return rVal;
	    } else {
		return;
	    }
	});
		
	// Submit the form
	if(rVal){
	    subscribe<?php echo $rand;?>();
	}
    });
	
});

<?php
if (ini_get('register_globals')){
    $ip = @$REMOTE_ADDR;
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
?>
function subscribe<?php echo $rand;?>(){

//  var introSlider<?php echo $rand;?> = new Fx.Slide('intro<?php echo $rand;?>');
//  var mcsignupFormSlider<?php echo $rand;?> = new Fx.Slide('mcsignupForm<?php echo $rand;?>');
//  introSlider<?php echo $rand;?>.slideOut();
//  mcsignupFormSlider<?php echo $rand;?>.slideOut();
	
    document.getElementById('intro<?php echo $rand;?>').style.display = 'none';
    document.getElementById('mcsignupForm<?php echo $rand;?>').style.display = 'none';
    document.getElementById('ajaxLoader<?php echo $rand;?>').style.display = 'block';

    var url = baseUrl + 'modules/mod_mailchimpsignup/assets/scripts/ajax-subscribe.php';
    var data = new Object();
    data["listid"] = '<?php echo $params->get( 'listid' );?>';
    data["ip"] = '<?php echo $ip;?>';
    data["thankyouMsg"] = '<?php echo JText::_( $params->get( 'thankyou' ) );?>';
    data["updateMsg"] = '<?php echo JText::_( $params->get( 'updateMsg' ) );?>';
    data["userId"] = '<?php echo $user->id;?>';

    var merges = '';
    var mergeVal = [];
    $$('#mcsignup<?php echo $rand;?> .submitInt').each(function(el){
	if(mergeVal[el.getProperty('name')] == undefined){
	    mergeVal[el.getProperty('name')] = '';
	}
	if(el.getProperty('type') == 'radio' ){
	    if(el.checked == true){
		mergeVal[el.getProperty('name')] = el.value;
	    }
	} else if(el.getProperty('type') == 'checkbox' ){
	    if(el.checked == true){
		mergeVal[el.getProperty('name')] = mergeVal[el.getProperty('name')] + el.value + ',';
	    }
	} else {
	    if(el.value != el.getProperty('title') && el.value+' *' != el.getProperty('title') ){
		mergeVal[el.getProperty('name')] = el.value;
	    }
	}
	data[el.getProperty('name')] = mergeVal[el.getProperty('name')];
	merges = merges +'|'+ el.getProperty('name')
    });
    data["merges"] = merges;

    var groups = '';
    var groupVal = [];
    $$('#mcsignup<?php echo $rand;?> .submitMerge').each(function(el){
	if(groupVal[el.getProperty('name')] == undefined){
	    groupVal[el.getProperty('name')] = '';
	}
	if(el.getProperty('type') == 'radio' ){
	    if(el.checked == true){
		groupVal[el.getProperty('name')] = el.value;
	    }
	} else if(el.getProperty('type') == 'checkbox' ){
	    if(el.checked == true){
		groupVal[el.getProperty('name')] = groupVal[el.getProperty('name')] + el.value + ',';
	    }
	} else {
	    if(el.value != el.getProperty('title') && el.value+' *' != el.getProperty('title') ){
		groupVal[el.getProperty('name')] = el.value;
	    }
	}

	data[el.getProperty('name')] = groupVal[el.getProperty('name')];
	groups = groups +'|'+ el.getProperty('name')
    });
    data['groups'] = groups;

    doAjaxTask(url, data, function(postback){
	document.getElementById('ajaxLoader<?php echo $rand;?>').style.display = 'none';
	document.getElementById('mcsignupResult<?php echo $rand;?>').style.display = 'block';
	if( MooTools.version >= '1.3' ){
	    $('mcsignupResult<?php echo $rand;?>').set('html', postback.html);
	} else {
	    $('mcsignupResult<?php echo $rand;?>').setHTML(postback.html);
	}
	$('mcsignupResult<?php echo $rand;?>').style.display='block';
	if(postback.js){
	    eval( postback.js );
	}
	if(postback.error){
	    document.getElementById('tryAgain<?php echo $rand;?>').style.display = 'block';
	}
    });

}

var baseUrl = '<?php echo JURI::root();?>';

function tryAgain<?php echo $rand;?>(){
    document.getElementById('tryAgain<?php echo $rand;?>').style.display = 'none';
    document.getElementById('mcsignupResult<?php echo $rand;?>').style.display = 'none';
    document.getElementById('intro<?php echo $rand;?>').style.display = 'block';
    document.getElementById('mcsignupForm<?php echo $rand;?>').style.display = 'block';
}

function checkEmail(email){
    var pattern = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return pattern.test(email);
}

function checkNumber(str){
    var pattern = /^d.*/;
    return pattern.test(str);
}
<?php if( version_compare(JVERSION,'1.6.0','ge') ){ ?>
function doAjaxTask( requestUrl, data, callback ){
    var sendData = new Object();
    sendData['elements'] = JSON.encode(data);
    var a = new Request({   url: requestUrl,
			    method: 'post',
			    data: sendData,
			    onComplete: function(response){
				var resp=JSON.decode(response);
				callback(resp);
			    }
			}).send();
}
<?php } else { ?>
function doAjaxTask( url, data, callback ){
	var a=new Ajax(url,{
		method:"post",
		data:{"elements":Json.toString(data)},
		onComplete: function(response){
			var resp=Json.evaluate(response);
			callback(resp);
		}
	}).request();
}
<?php } ?>
/* ]]> */
</script>
<div id="mcsignup<?php echo $rand;?>" class="mcsignup <?php echo $params->get('moduleclass_sfx', ''); ?>">
<div id="intro<?php echo $rand;?>">
    <?php if( $params->get( 'intro-text' ) ) : ?>
	<p class="intro"><?php echo JText::_($params->get( 'intro-text' )); ?></p>
    <?php endif; ?>
</div>
<div id="mcsignupFormWrapper<?php echo $rand;?>">

<form action="<?php echo $uri->toString( array( 'scheme', 'host', 'port', 'path', 'query' ) ); ?>" method="post" id="mcsignupForm<?php echo $rand;?>" name="mcsignupForm<?php echo $rand;?>" onsubmit="return false;">
<?php

$js = '';
$fields = $params->get( 'fields' );
	//	var_dump($fields);die;
if( is_array($fields) || $fields != ''){
    if(!is_array($fields)){ $fld[] = $fields; } else { $fld = $fields; }
    foreach($fld as $f){
	$field = explode(';', $f);
	switch($field[1]){
	    case 'text':
	    case 'email':
	    case 'imageurl':
		$req = ($field[3])? 'class="mcsignupRequired submitInt inputbox"' : 'class="submitInt inputbox"';
		$title = $field[2];
		if($field[3]){ $title = $title.' *'; }
		$onclick = 'onfocus="if(this.value==\''.$field[2].'\'){this.value=\'\';}" onblur="if(this.value==\'\'){this.value=\''.$field[2].'\';}"';
		echo '<div><input name="'.$field[0].'" id="'.$field[0].'" '.$req.' type="text" value="'.$field[2].'" title="'.JText::_($title).'" '.$onclick.' /></div>';
		break;
	    case 'url':
		$req = ($field[3])? 'class="mcsignupRequired submitInt inputbox"' : 'class="submitInt inputbox"';
		$title = JText::_($field[2]);
		if($field[3]){ $title = JText::_($field[2]).' *'; }
		$onclick = 'onfocus="if(this.value==\''.$title.'\'){this.value=\'\';}" onblur="if(this.value==\'\'){this.value=\''.$title.'\';}"';
		echo '<div><input name="'.$field[0].'" id="'.$field[0].'" '.$req.' type="text" value="'.$title.'" title="'.$title.'" '.$onclick.' /></div>';
		$js .= "$('".$field[0]."').addEvent('keyup', function(){ if(this.value==''){this.value='".$title."';} else if(this.value.substr(0, 1) != 'h'&&this.value.substr(1, 1) != 't'&&this.value.substr(2, 1) != 't'&&this.value.substr(3, 1) != 'p'&&this.value!='".$title."'){ this.value = 'http://'+this.value; } });";
		break;
	    case 'dropdown':
		$choices = explode('##', $field[4]);
		$req = ($field[3])? 'class="mcsignupRequired submitInt inputbox"' : 'class="submitInt inputbox"';
		$title = JText::_($field[2]);
		if($field[3]){ $title = JText::_($field[2]).' *'; }
		echo '<div class="mcsignupTitle">'.$title.'</div>';
		echo '<select name="'.$field[0].'" id="'.$field[0].'" '.$req.'>';
		if(!$field[3]){
		    echo '<option value=""></option>';
		}
		foreach($choices as $ch){
		    echo '<option value="'.$ch.'">'.$ch.'</option>';
		}
		echo '</select><br />';
		break;
	    case 'radio':
		$choices = explode('##', $field[4]);
		$req = ($field[3])? 'class="mcsignupRequired submitInt inputbox"' : 'class="submitInt inputbox"';
		$title = JText::_($field[2]);
		if($field[3]){ $title = JText::_($field[2]).' *'; }
		echo '<div class="mcsignupTitle">'.$title.'</div>';
		foreach($choices as $ch){
		    echo '<input type="radio" name="'.$field[0].'" id="'.$field[0].'_'.str_replace(' ','_',$ch).'" '.$req.' value="'.$ch.'" title="'.JText::_($title).'" /><label for="'.$field[0].'_'.str_replace(' ','_',$ch).'">'.JText::_($ch).'</label><br />';
		}
		break;
	    case 'number':
	    case 'zip':
		$req = ($field[3])? 'class="mcsignupRequired submitInt number inputbox"' : 'class="submitInt number inputbox"';
		$title = JText::_($field[2]);
		if($field[3]){ $title = JText::_($field[2]).' *'; }
		$onclick = 'onfocus="if(this.value==\''.$title.'\'){this.value=\'\';}" onblur="if(this.value!=\'\' && this.value!=\''.$title.'\' && this.value != parseFloat(this.value)){ alert(\''.$title.' '.JText::_("JM_MUST_BE_A_NUMBER").'\'); this.value=\'\';this.focus(); } else if(this.value==\'\'){this.value=\''.$title.'\';}"';
		echo '<input name="'.$field[0].'" id="'.$field[0].'" '.$req.' type="text" value="'.$title.'" title="'.$title.'" '.$onclick.' />';
		$js .= "$('".$field[0]."').addEvent('keyup', function(){ this.value = this.value.replace(' ','').replace('-','').replace('+','').replace('.',''); });";
		break;
	    case 'date':
		JHTML::_('behavior.calendar');
		$title = JText::_($field[2]);
		if($field[3]){ $title = $field[2].' *'; }
		$attributes = array('maxlength'=>'10', 'style' => 'width:85%;', 'title' => $title );
		if($field[3]){ $attributes['class'] = 'mcsignupRequired submitInt inputbox'; } else { $attributes['class'] = 'submitInt inputbox'; }
		echo JHTML::calendar($title, $field[0], $field[0], $params->get('dateFormat', '%Y-%m-%d'), $attributes);
		break;

	    case 'birthday':
		$req = ($field[3])? 'class="mcsignupRequired submitInt inputbox"' : 'class="submitInt inputbox"';
		$title = JText::_($field[2]);
		if($field[3]){ $title = $field[2].' *'; }
		echo '<div><label for="'.$field[0].'_month">'.$title.': </label>';
		echo '<select name="'.$field[0].'#*#month" id="'.$field[0].'_month" title="'.JText::_($field[2]).'" '.$req.'>';
		    echo '<option value="">MM</option>';
		for( $i = 1; $i <= 12; $i++ ){
		    echo '<option value="'.str_pad($i,2,'0',str_pad_left).'">'.str_pad($i,2,'0',str_pad_left).'</option>';
		}
		echo '</select>';
		echo '<select name="'.$field[0].'#*#day" id="'.$field[0].'_day" title="'.JText::_($field[2]).'" '.$req.'>';
		    echo '<option value="">DD</option>';
		for( $i = 1; $i <= 31; $i++ ){
		    echo '<option value="'.str_pad($i,2,'0',str_pad_left).'">'.str_pad($i,2,'0',str_pad_left).'</option>';
		}
		echo '</select></div>';
		break;
	    case 'phone':
		$req = ($field[3])? 'class="mcsignupRequired submitInt inputbox"' : 'class="submitInt inputbox"';
		$title = JText::_($field[2]);
		if($field[3]){ $title = $field[2].' *'; }
		echo '<div><label for="'.$field[0].'">'.$title.': </label>';
		if( $params->get( 'phoneFormat', 'inter' ) == 'inter' ){
		    echo '<input name="'.$field[0].'" id="'.$field[0].'" '.$req.' type="text" value="" title="'.$title.'" />';
		} else {
		    echo '(<input name="'.$field[0].'*#*1" id="'.$field[0].'" '.$req.' type="text" value="" title="'.$title.'" size="2" maxlength="3" />)';
		    echo ' <input name="'.$field[0].'*#*2" id="'.$field[0].'" '.$req.' type="text" value="" title="'.$title.'" size="2" maxlength="3" />';
		    echo ' - <input name="'.$field[0].'*#*3" id="'.$field[0].'" '.$req.' type="text" value="" title="'.$title.'" size="3" maxlength="4" />';
		}
		echo '</div>';
		break;
	    case 'address':
		$req = ($field[3])? 'class="mcsignupRequired submitInt inputbox"' : 'class="submitInt inputbox"';
		$title = JText::_($field[2]);
		if($field[3]){ $title = $field[2].' *'; }
		echo '<div><label for="'.$field[0].'">'.$title.': </label><br />';
		$title = JText::_('JM_STREET_ADDRESS');
		$onclick = 'onfocus="if(this.value==\''.$title.'\'){this.value=\'\';}" onblur="if(this.value==\'\'){this.value=\''.$title.'\';}"';
		echo '<input name="'.$field[0].'***addr1" id="'.$field[0].'" '.$req.' '.$onclick.' type="text" value="'.$title.'" title="'.$title.'" /><br />';
		if( $params->get( 'address2', 0 ) ){
		$title = JText::_('JM_ADDRESS_2');
		$onclick = 'onfocus="if(this.value==\''.$title.'\'){this.value=\'\';}" onblur="if(this.value==\'\'){this.value=\''.$title.'\';}"';
		echo '<input name="'.$field[0].'***addr2" id="'.$field[0].'" '.$req.' '.$onclick.' type="text" value="'.$title.'" title="'.$title.'" /><br />';
		}
		$title = JText::_('JM_CITY');
		$onclick = 'onfocus="if(this.value==\''.$title.'\'){this.value=\'\';}" onblur="if(this.value==\'\'){this.value=\''.$title.'\';}"';
		echo '<input name="'.$field[0].'***city" id="'.$field[0].'" '.$req.' '.$onclick.' type="text" value="'.$title.'" title="'.$title.'" /><br />';
		$title = JText::_('JM_STATE');
		$onclick = 'onfocus="if(this.value==\''.$title.'\'){this.value=\'\';}" onblur="if(this.value==\'\'){this.value=\''.$title.'\';}"';
		echo '<input name="'.$field[0].'***state" id="'.$field[0].'" '.$req.' '.$onclick.' type="text" value="'.$title.'" title="'.$title.'" /><br />';
		$title = JText::_('JM_ZIP');
		$onclick = 'onfocus="if(this.value==\''.$title.'\'){this.value=\'\';}" onblur="if(this.value==\'\'){this.value=\''.$title.'\';}"';
		echo '<input name="'.$field[0].'***zip" id="'.$field[0].'" '.$req.' '.$onclick.' type="text" value="'.$title.'" title="'.$title.'" /><br />';
		$title = JText::_('JM_COUNTRY');

		echo getCountryDropdown( $field[0].'***country', $field[0], $title, $req ).'<br />';

		echo '</div>';
		break;
	}
    }
}
if($js){
    echo '<script type="text/javascript">window.addEvent("domready", function() {'.$js.'});</script>';
}

$interests = $params->get( 'interests' );
	//	var_dump($interests);die;
if( is_array($interests) || $interests != ''){
    if(!is_array($interests)){ $inter[] = $interests; } else { $inter = $interests; }
    foreach($inter as $i){
		
	$interest = explode(';', $i);
	$groups = explode('####', $interest[3]);
//	var_dump($groups);die;
	echo '<div class="mcsignupTitle">'.JText::_($interest[2]).'</div>';
	switch($interest[1]){
	    case 'checkboxes':
		foreach($groups as $g){
		    $o = explode('##', $g);
		    echo '<input type="checkbox" name="'.$interest[0].'" id="'.$interest[0].'_'.str_replace(' ','_',$o[0]).'" class="submitMerge inputbox" value="'.$o[0].'" /><label for="'.$interest[0].'_'.$o[0].'">'.JText::_($o[1]).'</label><br />';
		}
		break;
	    case 'radio':
		foreach($groups as $g){
		    $o = explode('##', $g);
		    echo '<input type="radio" name="'.$interest[0].'" id="'.$interest[0].'_'.str_replace(' ','_',$o[0]).'" class="submitMerge inputbox" value="'.$o[0].'" /><label for="'.$interest[0].'_'.$o[0].'">'.JText::_($o[1]).'</label><br />';
		}
		break;
	    case 'dropdown':
		echo '<select name="'.$interest[0].'" id="'.$interest[0].'" class="submitMerge inputbox">';
		echo '<option value=""></option>';
		foreach($groups as $g){
		    $o = explode('##', $g);
		    echo '<option value="'.$o[0].'">'.JText::_($o[1]).'</option>';
		}
		echo '</select><br />';
		break;
	}
    }
}

?>
<?php echo JHTML::_( 'form.token' ); ?>
<?php if( $params->get( 'outro-text-1' ) ) : ?>
<div id="outro1_<?php echo $rand;?>" class="outro1">
    <p class="outro"><?php echo JText::_($params->get( 'outro-text-1' )); ?></p>
</div>
<?php endif; ?>
<div>
    <input type="button" class="button" value="<?php echo JText::_( 'JM_SUBSCRIBE' ); ?>" id="mcsignupSubmit<?php echo $rand;?>" />
</div>
<?php if( $params->get( 'outro-text-2' ) ) : ?>
<div id="outro2_<?php echo $rand;?>" class="outro2">
    <p class="outro"><?php echo JText::_($params->get( 'outro-text-2' )); ?></p>
</div>
<?php endif; ?>
</form>
</div>
<div id="ajaxLoader<?php echo $rand;?>" style="text-align:center;display:none;"><img src="<?php echo JURI::root();?>modules/mod_mailchimpsignup/assets/images/ajax-loader.gif" alt="Please wait"/></div>
<div id="mcsignupResult<?php echo $rand;?>" class="mcsignupResult" style="display:none;"></div>
<div id="tryAgain<?php echo $rand;?>" class="tryAgain" style="display:none;"><a href="javascript:void(0);" onclick="javascript:tryAgain<?php echo $rand;?>();"><?php echo JText::_( 'JM_TRY_AGAIN' ); ?></a></div>
</div>
<?php

function getCountryDropdown( $name, $id, $title, $req ){


    $options = array(	'AF' => 'AFGHANISTAN',
			'AX' => 'ÅLAND ISLANDS',
			'AL' => 'ALBANIA',
			'DZ' => 'ALGERIA',
			'AS' => 'AMERICAN SAMOA',
			'AD' => 'ANDORRA',
			'AO' => 'ANGOLA',
			'AI' => 'ANGUILLA',
			'AQ' => 'ANTARCTICA',
			'AG' => 'ANTIGUA AND BARBUDA',
			'AR' => 'ARGENTINA',
			'AM' => 'ARMENIA',
			'AW' => 'ARUBA',
			'AU' => 'AUSTRALIA',
			'AT' => 'AUSTRIA',
			'AZ' => 'AZERBAIJAN',
			'BS' => 'BAHAMAS',
			'BH' => 'BAHRAIN',
			'BD' => 'BANGLADESH',
			'BB' => 'BARBADOS',
			'BY' => 'BELARUS',
			'BE' => 'BELGIUM',
			'BZ' => 'BELIZE',
			'BJ' => 'BENIN',
			'BM' => 'BERMUDA',
			'BT' => 'BHUTAN',
			'BO' => 'BOLIVIA, PLURINATIONAL STATE OF',
			'BA' => 'BOSNIA AND HERZEGOVINA',
			'BW' => 'BOTSWANA',
			'BV' => 'BOUVET ISLAND',
			'BR' => 'BRAZIL',
			'IO' => 'BRITISH INDIAN OCEAN TERRITORY',
			'BN' => 'BRUNEI DARUSSALAM',
			'BG' => 'BULGARIA',
			'BF' => 'BURKINA FASO',
			'BI' => 'BURUNDI',
			'KH' => 'CAMBODIA',
			'CM' => 'CAMEROON',
			'CA' => 'CANADA',
			'CV' => 'CAPE VERDE',
			'KY' => 'CAYMAN ISLANDS',
			'CF' => 'CENTRAL AFRICAN REPUBLIC',
			'TD' => 'CHAD',
			'CL' => 'CHILE',
			'CN' => 'CHINA',
			'CX' => 'CHRISTMAS ISLAND',
			'CC' => 'COCOS (KEELING) ISLANDS',
			'CO' => 'COLOMBIA',
			'KM' => 'COMOROS',
			'CG' => 'CONGO',
			'CD' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
			'CK' => 'COOK ISLANDS',
			'CR' => 'COSTA RICA',
			'CI' => 'CÔTE D\'IVOIRE',
			'HR' => 'CROATIA',
			'CU' => 'CUBA',
			'CY' => 'CYPRUS',
			'CZ' => 'CZECH REPUBLIC',
			'DK' => 'DENMARK',
			'DJ' => 'DJIBOUTI',
			'DM' => 'DOMINICA',
			'DO' => 'DOMINICAN REPUBLIC',
			'EC' => 'ECUADOR',
			'EG' => 'EGYPT',
			'SV' => 'EL SALVADOR',
			'GQ' => 'EQUATORIAL GUINEA',
			'ER' => 'ERITREA',
			'EE' => 'ESTONIA',
			'ET' => 'ETHIOPIA',
			'FK' => 'FALKLAND ISLANDS (MALVINAS)',
			'FO' => 'FAROE ISLANDS',
			'FJ' => 'FIJI',
			'FI' => 'FINLAND',
			'FR' => 'FRANCE',
			'GF' => 'FRENCH GUIANA',
			'PF' => 'FRENCH POLYNESIA',
			'TF' => 'FRENCH SOUTHERN TERRITORIES',
			'GA' => 'GABON',
			'GM' => 'GAMBIA',
			'GE' => 'GEORGIA',
			'DE' => 'GERMANY',
			'GH' => 'GHANA',
			'GI' => 'GIBRALTAR',
			'GR' => 'GREECE',
			'GL' => 'GREENLAND',
			'GD' => 'GRENADA',
			'GP' => 'GUADELOUPE',
			'GU' => 'GUAM',
			'GT' => 'GUATEMALA',
			'GG' => 'GUERNSEY',
			'GN' => 'GUINEA',
			'GW' => 'GUINEA-BISSAU',
			'GY' => 'GUYANA',
			'HT' => 'HAITI',
			'HM' => 'HEARD ISLAND AND MCDONALD ISLANDS',
			'VA' => 'HOLY SEE (VATICAN CITY STATE)',
			'HN' => 'HONDURAS',
			'HK' => 'HONG KONG',
			'HU' => 'HUNGARY',
			'IS' => 'ICELAND',
			'IN' => 'INDIA',
			'ID' => 'INDONESIA',
			'IR' => 'IRAN, ISLAMIC REPUBLIC OF',
			'IQ' => 'IRAQ',
			'IE' => 'IRELAND',
			'IM' => 'ISLE OF MAN',
			'IL' => 'ISRAEL',
			'IT' => 'ITALY',
			'JM' => 'JAMAICA',
			'JP' => 'JAPAN',
			'JE' => 'JERSEY',
			'JO' => 'JORDAN',
			'KZ' => 'KAZAKHSTAN',
			'KE' => 'KENYA',
			'KI' => 'KIRIBATI',
			'KP' => 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF',
			'KR' => 'KOREA, REPUBLIC OF',
			'KW' => 'KUWAIT',
			'KG' => 'KYRGYZSTAN',
			'LA' => 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC',
			'LV' => 'LATVIA',
			'LB' => 'LEBANON',
			'LS' => 'LESOTHO',
			'LR' => 'LIBERIA',
			'LY' => 'LIBYAN ARAB JAMAHIRIYA',
			'LI' => 'LIECHTENSTEIN',
			'LT' => 'LITHUANIA',
			'LU' => 'LUXEMBOURG',
			'MO' => 'MACAO',
			'MK' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
			'MG' => 'MADAGASCAR',
			'MW' => 'MALAWI',
			'MY' => 'MALAYSIA',
			'MV' => 'MALDIVES',
			'ML' => 'MALI',
			'MT' => 'MALTA',
			'MH' => 'MARSHALL ISLANDS',
			'MQ' => 'MARTINIQUE',
			'MR' => 'MAURITANIA',
			'MU' => 'MAURITIUS',
			'YT' => 'MAYOTTE',
			'MX' => 'MEXICO',
			'FM' => 'MICRONESIA, FEDERATED STATES OF',
			'MD' => 'MOLDOVA, REPUBLIC OF',
			'MC' => 'MONACO',
			'MN' => 'MONGOLIA',
			'ME' => 'MONTENEGRO',
			'MS' => 'MONTSERRAT',
			'MA' => 'MOROCCO',
			'MZ' => 'MOZAMBIQUE',
			'MM' => 'MYANMAR',
			'NA' => 'NAMIBIA',
			'NR' => 'NAURU',
			'NP' => 'NEPAL',
			'NL' => 'NETHERLANDS',
			'AN' => 'NETHERLANDS ANTILLES',
			'NC' => 'NEW CALEDONIA',
			'NZ' => 'NEW ZEALAND',
			'NI' => 'NICARAGUA',
			'NE' => 'NIGER',
			'NG' => 'NIGERIA',
			'NU' => 'NIUE',
			'NF' => 'NORFOLK ISLAND',
			'MP' => 'NORTHERN MARIANA ISLANDS',
			'NO' => 'NORWAY',
			'OM' => 'OMAN',
			'PK' => 'PAKISTAN',
			'PW' => 'PALAU',
			'PS' => 'PALESTINIAN TERRITORY, OCCUPIED',
			'PA' => 'PANAMA',
			'PG' => 'PAPUA NEW GUINEA',
			'PY' => 'PARAGUAY',
			'PE' => 'PERU',
			'PH' => 'PHILIPPINES',
			'PN' => 'PITCAIRN',
			'PL' => 'POLAND',
			'PT' => 'PORTUGAL',
			'PR' => 'PUERTO RICO',
			'QA' => 'QATAR',
			'RE' => 'RÉUNION',
			'RO' => 'ROMANIA',
			'RU' => 'RUSSIAN FEDERATION',
			'RW' => 'RWANDA',
			'BL' => 'SAINT BARTHÉLEMY',
			'SH' => 'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA',
			'KN' => 'SAINT KITTS AND NEVIS',
			'LC' => 'SAINT LUCIA',
			'MF' => 'SAINT MARTIN',
			'PM' => 'SAINT PIERRE AND MIQUELON',
			'VC' => 'SAINT VINCENT AND THE GRENADINES',
			'WS' => 'SAMOA',
			'SM' => 'SAN MARINO',
			'ST' => 'SAO TOME AND PRINCIPE',
			'SA' => 'SAUDI ARABIA',
			'SN' => 'SENEGAL',
			'RS' => 'SERBIA',
			'SC' => 'SEYCHELLES',
			'SL' => 'SIERRA LEONE',
			'SG' => 'SINGAPORE',
			'SK' => 'SLOVAKIA',
			'SI' => 'SLOVENIA',
			'SB' => 'SOLOMON ISLANDS',
			'SO' => 'SOMALIA',
			'ZA' => 'SOUTH AFRICA',
			'GS' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
			'ES' => 'SPAIN',
			'LK' => 'SRI LANKA',
			'SD' => 'SUDAN',
			'SR' => 'SURINAME',
			'SJ' => 'SVALBARD AND JAN MAYEN',
			'SZ' => 'SWAZILAND',
			'SE' => 'SWEDEN',
			'CH' => 'SWITZERLAND',
			'SY' => 'SYRIAN ARAB REPUBLIC',
			'TW' => 'TAIWAN, PROVINCE OF CHINA',
			'TJ' => 'TAJIKISTAN',
			'TZ' => 'TANZANIA, UNITED REPUBLIC OF',
			'TH' => 'THAILAND',
			'TL' => 'TIMOR-LESTE',
			'TG' => 'TOGO',
			'TK' => 'TOKELAU',
			'TO' => 'TONGA',
			'TT' => 'TRINIDAD AND TOBAGO',
			'TN' => 'TUNISIA',
			'TR' => 'TURKEY',
			'TM' => 'TURKMENISTAN',
			'TC' => 'TURKS AND CAICOS ISLANDS',
			'TV' => 'TUVALU',
			'UG' => 'UGANDA',
			'UA' => 'UKRAINE',
			'AE' => 'UNITED ARAB EMIRATES',
			'GB' => 'UNITED KINGDOM',
			'US' => 'UNITED STATES',
			'UM' => 'UNITED STATES MINOR OUTLYING ISLANDS',
			'UY' => 'URUGUAY',
			'UZ' => 'UZBEKISTAN',
			'VU' => 'VANUATU',
			'VA' => 'VATICAN CITY STATE',
			'VE' => 'VENEZUELA, BOLIVARIAN REPUBLIC OF',
			'VN' => 'VIET NAM',
			'VG' => 'VIRGIN ISLANDS, BRITISH',
			'VI' => 'VIRGIN ISLANDS, U.S.',
			'WF' => 'WALLIS AND FUTUNA',
			'EH' => 'WESTERN SAHARA',
			'YE' => 'YEMEN',
			'ZM' => 'ZAMBIA',
			'ZW' => 'ZIMBABWE'
		    ); //@todo: cross-reference this list with Mailchimp country list

    $result = '<select name="'.$name.'" id="'.$id.'" title="'.$title.'" '.$req.'>';
    $result .= '<option value=""></option>';
    foreach( $options as $k => $v ){
	$result .= '<option value="'.$k.'">'.ucwords(strtolower($v)).'</option>';
    }
    $result .= '</select>';

    return $result;
}
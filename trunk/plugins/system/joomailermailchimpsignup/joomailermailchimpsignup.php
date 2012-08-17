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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');

if(JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
	require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
}

class plgSystemJoomailerMailchimpSignup extends JPlugin
{
    function onAfterRender()
    {
	jimport('joomla.filesystem.file');
	if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
	    return;
	}
	$mainframe =& JFactory::getApplication();
	$option	= JRequest::getCmd('option');
	$view	= JRequest::getVar('view');
	$layout	= JRequest::getVar('layout', '');
	$task	= JRequest::getVar('task', 0, 'get', 'string');
	$user	=& JFactory::getUser();
	if(version_compare(JVERSION,'1.6.0','ge')) {
		$user	= JFactory::getUser( $user->id );
	}
	if ( $mainframe->isSite() )
	{
	    jimport('joomla.filesystem.file');
	    if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'admin.k2.php')){
			$k2params =& JComponentHelper::getParams('com_k2');
			$k2plugin = $k2params->get('K2UserProfile');
		} else {
			$k2plugin = false;
		}

	    if (( $option == 'com_user' && ( $view == 'register' || $view == 'user' || $task == 'edit' ) )
	    || (  $option == 'com_users' && ($view == 'registration' || ($view == 'profile' && $layout == 'edit' )) )
	    ||  ($option == 'com_community'   && ( $task == 'registerProfile' || $view == 'profile' || $view == 'register'))
	    ||  ($option == 'com_comprofiler' && ( $task == 'registers' || strtolower($task) == 'userdetails' ))
	    ||  ($option == 'com_virtuemart'  && JRequest::getVar('page') == 'checkout.index' )
	    )
	    {

		$body 	= JResponse::getBody();
		$mainframe = & JFactory::getApplication();
		$plugin = JPluginHelper::getPlugin( 'system', 'joomailermailchimpsignup');
		$listId = $this->params->get('listid');

		//Check that there is a newsletter list specified
		if ($listId) {

		    $lang = & JFactory::getLanguage();
		    $lang->load('plg_system_joomailermailchimpsignup', JPATH_ADMINISTRATOR);

		    $api = $this->api();

		    $mergefields = $api->listMergeVars( $listId );
		    $mergeids = $this->params->get('fields');
		    $key = 'tag';
		    $val = 'name';
		    $ihtml = '';

		    $interests = $api->listInterestGroupings( $listId );
		    $interestids = $this->params->get('interests');
		    $key = 'bit';
		    $val = 'name';

		    $checked = '';
		    $groupings = '';
		    //Check if the user is subscribed
		    if($user->email)
		    {
			$userlists = $api->listsForEmail($user->email);

			if($userlists && in_array($listId,$userlists)) {
			    $checked = 'checked="checked"';
			}
			$userinfo = $api->listMemberInfo($listId, $user->email);
			$usermerges = $userinfo['merges'];

			$groupings = (isset($userinfo['merges']['GROUPINGS'])) ? $userinfo['merges']['GROUPINGS'] : '';
		    } else {
			$usermerges = array();
		    }

		    //For the standard registration
		    if (   ( $option == 'com_user' && ($view == 'register' || $view == 'user' || $task == 'edit') )
			|| (     $option == 'com_users' && ($view == 'registration' || ($view == 'profile' && $layout == 'edit' )) )
		    ) {

			if($mergefields){
			    $ihtml = $this->buildMergeVarsHTML( $mergefields, $usermerges, $mergeids );
			}

			if($interests){
			    //Build the HTML for the interests
			    $ihtml .= $this->buildInterestsHTML( $interests, $groupings, $interestids );
			}

			JHTML::_( 'behavior.calendar' );

			if ($view == 'register' || $view == 'registration') {
			    $registerRequired = (version_compare(JVERSION,'1.6.0','ge')) ? 'COM_USERS_REGISTER_REQUIRED' : 'REGISTER_REQUIRED';
			    if(version_compare(JVERSION,'1.6.0','ge')){
					$pattern = '/<\/dl>(\n|\s)*<\/fieldset>(\n|\s)*<div>(\n|\s)*<button type="submit" class="validate">/';
					$replacement = "<dt><label for='newsletter'>";
					$replacement.= JText::_('JM_NEWSLETTERSIGNUP');
					$replacement.= '</label></dt>';
					$replacement.= '<dd>';
					$replacement.= "<input type='checkbox' name='newsletter' id='newsletter' value='1' />";
					$replacement.= "</dd>";
					$replacement.= $ihtml;
					$replacement.= '</dl></fieldset><div><button type="submit" class="validate">';
				} else {
					$pattern = '/<\/table>(\n|\s)*<div class="k2AccountPageNotice">'.preg_quote(JText::_( $registerRequired )).'<\/div>/';
					$replacement = "<tr><td height='40'><label for='newsletter'>";
					$replacement.= JText::_('JM_NEWSLETTERSIGNUP');
					$replacement.= '</label></td>';
					$replacement.= '<td height="40">';
					$replacement.= "<input type='checkbox' name='newsletter' id='newsletter' value='1' />";
					$replacement.= "</td></tr>";
					$replacement.= $ihtml;
					$replacement.= "<tr><td height='40' colspan='2'>";
					$replacement.= JText::_( $registerRequired );
					$replacement.= "</td></tr></table>";
				}

			    $body = preg_replace($pattern,$replacement,$body);
//			    $body = str_replace('<input type="hidden" name="task" value="register_save" />','<input type="hidden" name="option" value="com_joomailermailchimpsignup" /><input type="hidden" name="component" value="com_user" /><input type="hidden" name="task" value="register_save" /><input type="hidden" name="oldtask" value="register_save" />',$body);
			} else {
				if( $option == 'com_users' && $view == 'profile' && $layout == 'edit' ){

					$pattern = '/<input type="text" name="jform\[email2\]" class="validate-email required" id="jform_email2" value="'.$user->email.'" size="30"\/><\/dd>/is';
					$replacement = "\t<input type='text' name='jform[email2]' class='validate-email required' id='jform_email2' value='".$user->email."' size='30'/></dd>\n";
					$replacement.= "\t\t<dt>\n";
					$replacement.= "\t\t\t<label for='newsletter'>".JText::_('JM_NEWSLETTERSIGNUP')."</label>\n";
					$replacement.= "\t\t</dt>\n";
					$replacement.= "\t\t<dd>\n";
					$replacement.= "\t\t\t<input type='checkbox' name='newsletter' id='newsletter' value='1' ".$checked."/><input type='hidden' name='oldEmail' value='".$user->email."' />\n";
					$replacement.= "\t\t</dd>\n";
					$replacement.= $ihtml;
					$body = preg_replace($pattern,$replacement,$body);
				} else {
	//				$pattern = '/Kiribati.*<\/option>.*<\/select>.*<\/td>.*<\/tr>.*<\/table>/is';
	//				$replacement = 'Kiribati</option></select></td></tr></table>';
	//				$replacement.= '<br /><h3>'.JText::_('JM_NEWSLETTER').'</h3>';
	//				$replacement.= "<table><tr><td height='40'><label for='newsletter'>";
	//				$replacement.= JText::_('JM_NEWSLETTERSIGNUP');
	//				$replacement.= '</label></td>';
	//				$replacement.= '<td height="40">';
	//				$replacement.= "<input type='checkbox' name='newsletter' id='newsletter' value='1' ".$checked." />";
	//				$replacement.= "</td></tr>".$ihtml."</table>";

	//			    $body = preg_replace($pattern,$replacement,$body);
	//			    $body = str_replace('<input type="hidden" name="task" value="save" />','<input type="hidden" name="component" value="com_user" /><input type="hidden" name="task" value="register_save" /><input type="hidden" name="oldtask" value="save" />',$body);
	//			    $body = str_replace('<input type="hidden" name="option" value="com_user" />','<input type="hidden" name="option" value="com_joomailermailchimpsignup" />',$body);
				}
			}
		    } elseif ($option == 'com_community') {
			if ( $task == 'registerProfile' || $view == 'register' )
			{
				$parser	=& JFactory::getXMLParser('Simple');
				$xml	=  JPATH_ADMINISTRATOR .DS. 'components'.DS.'com_community'.DS.'community.xml';
				$parser->loadFile( $xml );
				$doc	=& $parser->document;
				$element	=& $doc->getElementByPath( 'version' );
				$JSversion	=  $element->data();
			    // JomSocial < 2.0
			    if (version_compare( $JSversion, '2.0','le')){
				$pattern = '/<tr>(\s|\n)*<td class="listkey" >&nbsp;<\/td>(\s|\n)*<td class="listvalue">(\s|\n)*'.preg_quote(JText::_( 'CC_REG_REQUIRED_FILEDS' )).'(\s|\n)*<\/td>(\s|\n)*<\/tr>/';

				$replacement = '<tr><td class="listkey" >&nbsp;</td><td class="listvalue">'.JText::_( 'CC_REG_REQUIRED_FILEDS' ).'</td></tr></table>';
				$replacement.= '<div class="ctitle"><h2>'.JText::_('JM_NEWSLETTER').'</h2></div>';
				$replacement.= '<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;"><tbody>';
				$replacement.= "<tr><td class='key'><label for='newsletter'>";
				$replacement.= JText::_('JM_NEWSLETTERSIGNUP');
				$replacement.= "</label></td><td class='listvalue'>";
				$replacement.= "<input type='checkbox' name='newsletter' id='newsletter' id='newsletter' value='1' />";
				$replacement.= "</td></tr>";
			    // JomSocial >= 2.0
			    } else {

				// JomSocial >= 2.2
				if (version_compare( $JSversion, '2.2','ge')){
				    $langString = 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS';
				    $listKey = "key";
				    $listValue = "value";
				} else {
				    $langString = 'CC_REG_REQUIRED_FILEDS';
				    $listKey = "key";
				    $listValue = "value";
				}
				$pattern = '/<tr>(\s|\n)*<td class="listkey" >&nbsp;<\/td>(\s|\n)*<td class="listvalue">(\s|\n)*'.preg_quote(JText::_( $langString )).'(\s|\n)*<\/td>(\s|\n)*<\/tr>/';

				$replacement = '<tr><td class="listkey" >&nbsp;</td><td class="listvalue">'.JText::_( $langString ).'</td></tr></table>';
				$replacement.= '<div class="ctitle"><h2>'.JText::_('JM_NEWSLETTER').'</h2></div>';
				$replacement.= '<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;"><tbody>';
				$replacement.= "<tr><td class='".$listKey."'><label for='newsletter'>";
				$replacement.= JText::_('JM_NEWSLETTERSIGNUP');
				$replacement.= "</label></td><td class='".$listValue."'>";
				$replacement.= "<input type='checkbox' name='newsletter' id='newsletter' id='newsletter' value='1' />";
				$replacement.= "</td></tr>";
			    }

			    $body = preg_replace($pattern,$replacement,$body);
	//		    $body = str_replace('<input type="hidden" name="task" value="registerUpdateProfile" />','<input type="hidden" name="option" value="com_joomailermailchimpsignup" /><input type="hidden" name="component" value="com_community" /><input type="hidden" name="task" value="register_save" /><input type="hidden" name="cntrllr" value="register" /><input type="hidden" name="oldtask" value="registerUpdateProfile" />',$body);
	//		    var_dump($_POST);die;
	//		    if( isset($_POST['newsletter'])){
	//				$body = str_replace('<input class="button validateSubmit" type="submit" id="btnSubmit"', '<input type="hidden" name="newsletter" id="newsletter" id="newsletter" value="1" /><input class="button validateSubmit" type="submit" id="btnSubmit"', $body);
	//			}

			} elseif ($view == 'profile' && $task == 'edit') {

			    $pattern = '/<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">(\s|\n)*<tbody>(\s|\n)*<tr>(\s|\n)*<td class="key"><\/td>(\s|\n)*<td class="value">(\s|\n)*<input type="hidden" name="action" value="save" \/>/';
			    $replacement = '<div class="ctitle"><h2>'.JText::_('JM_NEWSLETTER').'</h2></div>';
			    $replacement.= '<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;"><tbody>';
			    $replacement.= "<tr><td class='key'><label for='newsletter'>";
			    $replacement.= JText::_('JM_NEWSLETTERSIGNUP');
			    $replacement.= "</label></td><td>";
			    $replacement.= "<input type='checkbox' name='newsletter' id='newsletter' value='1' ".$checked." />";
			    $replacement.= "</td></tr>";
			    $replacement.= "<tr><td class='key'></td><td class='value'>";
			    $replacement.= '<input type="hidden" name="option" value="com_joomailermailchimpsignup" />';
			    $replacement.= '<input type="hidden" name="component" value="com_community" />';
			    $replacement.= '<input type="hidden" name="task" value="register_save" />';
			    $replacement.= '<input type="hidden" name="cntrllr" value="profile" />';
			    $replacement.= '<input type="hidden" name="oldtask" value="edit" />';
			    $replacement.= '<input type="hidden" name="action" value="save" />';
			    $body = preg_replace($pattern,$replacement,$body);
			    $body = str_replace('action="/joomla/index.php?option=com_community&amp;view=profile&amp;task=edit&amp;Itemid=53"','action=""',$body);

			}
		    } elseif ($option =='com_comprofiler') {
			if ($task == 'registers') {
			    $pattern = '/<tr>(\s|\n)*<td colspan="2">(\s|\n)*(.*)<input type="submit" value="'._UE_REGISTER.'" class="button" \/>(.*)(\s|\n)*<\/td>(\s|\n)*<\/tr>/';
			    $replacement = '<tr>';
			    $replacement.= '<td class="titleCell"><label for="newsletter">';
			    $replacement.= JText::_('JM_NEWSLETTERSIGNUP');
			    $replacement.= '</label></td>';
			    $replacement.= '<td>';
			    $replacement.= '<input type=\'checkbox\' name=\'newsletter\' id=\'newsletter\' value=\'1\' />';
			    $replacement.= '</td>';
			    $replacement.= '</tr>';
			    $replacement.= '<tr>';
			    $replacement.= '<td>&nbsp;</td>';
			    $replacement.= '</tr>';
			    $replacement.= '<tr>';
			    $replacement.= '<td colspan="2">';
			    $replacement.= '<input type="submit" class="button" value="'._UE_REGISTER.'">';
			    $replacement.= '</td>';
			    $replacement.= '</tr>';

			    $body = preg_replace($pattern,$replacement,$body);
//			    $body = str_replace('<input type="hidden" name="option" value="com_comprofiler" />','<input type="hidden" name="option" value="com_joomailermailchimpsignup" />',$body);
//			    $body = str_replace('<input type="hidden" name="task" value="saveregisters" />','<input type="hidden" name="task" value="register_save" /><input type="hidden" name="component" value="com_comprofiler" /><input type="hidden" name="oldtask" value="saveregisters"/>',$body);
			} elseif ( strtolower($task) == 'userdetails' ) {
			    $pattern = '/<\/table>(\s|\n)*<\/div>(\s|\n)*<\/div>(\s|\n)*<span class="cb_button_wrapper"><input/';
			    $replacement = '<tr>';
			    $replacement.= '<td class="titleCell"><label for="newsletter">';
			    $replacement.= JText::_('JM_NEWSLETTERSIGNUP');
			    $replacement.= '</label></td>';
			    $replacement.= '<td>';
			    $replacement.= '<input type=\'checkbox\' name=\'newsletter\' id=\'newsletter\' value=\'1\' '.$checked.'/>';
			    $replacement.= '</td>';
			    $replacement.= '</tr>';
			    $replacement.= '<tr>';
			    $replacement.= '<td>&nbsp;</td>';
			    $replacement.= '</tr>';
			    $replacement.= '</table></div></div>';
			    $replacement.= '<span class="cb_button_wrapper"><input';

			    $body = preg_replace($pattern,$replacement,$body);
		//	    $body = str_replace('<input type="hidden" name="option" value="com_comprofiler" />','<input type="hidden" name="option" value="com_joomailermailchimpsignup" />',$body);
		//	    $body = str_replace('<input type="hidden" name="task" value="saveUserEdit" />','<input type="hidden" name="task" value="register_save" /><input type="hidden" name="component" value="com_comprofiler" /><input type="hidden" name="option" value="com_joomailermailchimpsignup" /><input type="hidden" name="oldtask" value="saveUserEdit" /><input type="hidden" name="oldEmail" value="'.$user->email.'" />',$body);
			    $body = str_replace('<input type="hidden" name="task" value="saveUserEdit" />','<input type="hidden" name="task" value="saveUserEdit" /><input type="hidden" name="component" value="com_comprofiler" /><input type="hidden" name="oldEmail" value="'.$user->email.'" />',$body);
			}
		    } else if( $option == 'com_virtuemart' ){

			if($mergefields){
			    $ihtml = $this->buildMergeVarsHTML( $mergefields, $usermerges, $mergeids );
			}
			if($interests){
			    //Build the HTML for the interests
			    $ihtml .= $this->buildInterestsHTML( $interests, $groupings, $interestids );
			}

			$pattern = '/<\/div>(\s|\n)*<div align="center">(\s|\n)*<input type="hidden" name="remember" value="yes" \/>/is';
			$replacement = "\t<fieldset>\n";
			$replacement.= "\t\t<legend class='sectiontableheader'>".JText::_('JM_NEWSLETTER')."</legend>\n";
			$replacement.= "\t\t<div class='formLabel'>\n";
			$replacement.= "\t\t\t<label for='newsletter'>".JText::_('JM_NEWSLETTERSIGNUP')."</label>\n";
			$replacement.= "\t\t</div>\n";
			$replacement.= "\t\t<div class='formField'>\n";
			$replacement.= "\t\t\t<input type='checkbox' name='newsletter' id='newsletter' value='1' ".$checked."/>\n";
			$replacement.= "\t\t</div>\n";
			$replacement.= "\t</fieldset>\n";
			$replacement.= "</div>\n<div align='center'>\n<input type='hidden' value='yes' name='remember'>";
			$body = preg_replace($pattern,$replacement,$body);
//			$body = str_replace('<input type="hidden" name="option" value="com_virtuemart" />','<input type="hidden" name="option" value="com_joomailermailchimpsignup" /><input type="hidden" name="component" value="com_virtuemart" /><input type="hidden" name="task" value="register_save" /><input type="hidden" name="oldtask" value="shopperadd" />',$body);
		    }

		    JResponse::setBody($body);

		    return true;
		}

	    }
	} else {
	    //Admin part of the site
	    $layout = JRequest::getVar('layout');
	    $body 	= JResponse::getBody();
	    $mainframe = & JFactory::getApplication();

	    if( ($option == 'com_community' && $view == 'users' && $layout == 'edit')
	    ||  ($option == 'com_comprofiler' && stristr($body, '"newCBuser"') ) ) {
		$api = $this->api();
		if ($option == 'com_comprofiler') {
		    $cid = JRequest::getVar('cid');
		    $id = $cid[0];
		} else {
		    $id = JRequest::getVar('id');
		}
		$user = & JFactory::getUser($id);
		//Get the ID of the mailing list
		$plugin = & JPluginHelper::getPlugin('system', 'joomailermailchimpsignup');
		$pluginParams = new JParameter( $plugin->params );
		$listId = $pluginParams->get('listid');
		//Check if the user is already logged in and subscribed

		if($user->email){
		    $userlists = $api->listsForEmail($user->email);
		    if($userlists && in_array($listId,$userlists)) {
			$body = str_replace('"'.$option.'"', '"com_joomailermailchimpsignup"', $body);
			$body = str_replace('"save" />', '"mcsave" /><input type="hidden" name="ext" value="'.$option.'" />', $body);
			$body = str_replace("javascript: submitbutton('save')","javascript: submitbutton('mcsave')",$body);

			//@todo: fix the script below so that it redirects properly on cancel
			$script = "if ($(this).attr('href') === 'save') {"
				 ."\nvar taskVal = 'mcsave';"
				 ."\n} else {"
				 ."\nvar taskVal = $(this).attr('href');"
				 ."\ndocument.adminForm.option.value='com_comprofiler';"
				 ."\ndocument.adminForm.task.value='save';"
				 ."\n}";

			$body = str_replace('var taskVal = $(this).attr(\'href\').substring(1);',$script,$body);
			JResponse::setBody($body);
		    }
		}
	    }
	}
    }// function

    function onAfterRoute()
    {
	jimport('joomla.filesystem.file');
	if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
	    return;
	}
	$mainframe =& JFactory::getApplication();
	if ( $mainframe->isSite() ) {
	    $option = JRequest::getCmd('option');
	    $task   = JRequest::getVar('task');
	    $view   = JRequest::getVar('view');

	    if ( ($option == 'com_user' && $task == 'activate') ||
			 ($option == 'com_users' && $task == 'registration.activate') ||
			 ($option == 'com_comprofiler' && $task == 'confirm'))
	    {

		$api = $this->api();
		$db = & JFactory::getDBO();
		if($option == 'com_user' || $option == 'com_users') {
		    $activation = (version_compare(JVERSION,'1.6.0','ge')) ? JRequest::getVar('token') : JRequest::getVar('activation');
		    $query = 'SELECT id,email FROM #__users WHERE activation="'.$activation.'"';
		    $db->setQuery($query);
		    $result = $db->loadAssocList();
		    $email = $result[0]['email'];
		    $uid = $result[0]['id'];
		} elseif ($option == 'com_comprofiler') {
		    $activation = JRequest::getVar("confirmcode");
		    $query = 'SELECT user_id FROM #__comprofiler WHERE cbactivation="'.$activation.'"';
		    $db->setQuery($query);
		    $uid = $db->loadResult();
		    $query = 'SELECT email FROM #__users WHERE id='.$uid;
		    $db->setQuery($query);
		    $email = $db->loadResult();
		}
		$query = 'SELECT fname, lname, email, groupings, merges FROM #__joomailermailchimpsignup WHERE email="'.$email.'"';
		$db->setQuery($query);
		$result = $db->loadAssocList();

		if(isset($result[0])) {
		    $garray = '';
		    if($result[0]['groupings']) {
			$groups = explode('||',$result[0]['groupings']);
			foreach($groups as $g) {
			    if($g[0] == "\n") { $g = substr($g,1); }
			    $groupings = explode("\n",$g);
			    $name = substr(stristr($groupings[0],'='),1);
			    $id = substr(stristr($groupings[1],'='),1);
			    $vars = substr(stristr($groupings[2],'='),1);
			    $garray[$name] = array('name' => $name, 'id' => $id, 'groups' => $vars);
			}
		    }
		    $merges = array();
		    $merges_string = $result[0]['merges'];
		    if( $merges_string ){
			$first = explode("\n\n",$merges_string);
			foreach($first as $f) {
			    $second = explode("\n",$f);

			    $name = str_replace('name=','',$second[0]);
			    $second[1] = str_replace('value=','',$second[1]);
			    if(stristr($second[1],'||')) {
				$value = explode("||",substr($second[1],0,-2));
				if(count($value) == 3) {
				    $value['area'] = $value[0];
				    $value['detail1'] = $value[1];
				    $value['detail2'] = $value[2];
				} else {
				    $value['addr1'] = $value[0];
				    $value['addr2'] = $value[1];
				    $value['city'] = $value[2];
				    $value['state'] = $value[3];
				    $value['zip'] = $value[4];
				    $value['country'] = $value[5];
				    unset($value[3]);
				    unset($value[4]);
				}
				unset($value[0]);
				unset($value[1]);
				unset($value[2]);
			    } else {
				$value = $second[1];
			    }
			    $merges[$name] = $value;
			}
		    }

		    $fname = $result[0]['fname'];
		    $lname = $result[0]['lname'];

		    //Get the users ip address
		    if (ini_get('register_globals')){
			$ip=@$REMOTE_ADDR;
		    } else {
			$ip=$_SERVER['REMOTE_ADDR'];
		    }

		    $merge_vars = array('FNAME' => $fname,
								'LNAME' => $lname,
								'INTERESTS' => '',
								'GROUPINGS' => $garray,
								'OPTINIP' => $ip );
		    $merge_vars = array_merge($merge_vars,$merges);
		    $email_type = '';
		    $double_optin = false;
		    $update_existing = true;
		    $replace_interests = true;
		    $send_welcome = false;
		    $plugin = JPluginHelper::getPlugin( 'system', 'joomailermailchimpsignup' );
		    $listId = $this->params->get('listid');
		    //Subscribe the user
		    $retval = $api->listSubscribe( $listId, $email, $merge_vars, $email_type, $double_optin, $update_existing, $replace_interests, $send_welcome);

		    $query = 'DELETE FROM #__joomailermailchimpsignup WHERE email="'.$email.'"';
		    $db->setQuery($query);
		    $db->Query();

		    if ($api->errorCode && $api->errorCode != 215 &&$api->errorCode != 211){
			echo "Unable to load listSubscribe()!\n";
			echo "\tCode=".$api->errorCode."\n";
			echo "\tMsg=".$api->errorMessage."\n";
		    } else {
			$query = 'INSERT INTO #__joomailermailchimpintegration VALUES ("", '.$uid.', "'.$email.'", "'.$listId.'")';
			$db->setQuery($query);
			$db->Query();
		    }
		}

		// add user to CRM
		if( $this->params->get('sugar') ){
		    $this->addToSugar( $uid );
		}
		if( $this->params->get('highrise') ){
		    $this->addToHighrise( $uid );
		}
	    }
	}
    }

	function onAfterDispatch() {

                jimport('joomla.filesystem.file');
                if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
                    return;
                }
		$mainframe = &JFactory::getApplication();

		if($mainframe->isAdmin()) return;

		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$task = JRequest::getCmd('task');
		$layout = JRequest::getCmd('layout');
		$user = &JFactory::getUser();

		$plugin = JPluginHelper::getPlugin( 'system' , 'joomailermailchimpsignup' );

		$api = $this->api();

		$k2plugin = false;
		jimport('joomla.filesystem.file');
		if(JFile::exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'admin.k2.php')){
		    $k2params = JComponentHelper::getParams( 'com_k2' );
		    $k2plugin = $k2params->get('K2UserProfile');
		}

		if (!$k2plugin && ($option == 'com_user' && ($view == 'register' || $view == 'user')))
		{
			$template =& $mainframe->getTemplate();
			if($view == 'register') {
				$v = 'register';
				$l = ($template == 'morph') ? 'register_morph' : 'register';
			} else {
				$v = 'user';
				$l = 'profile';
			}
			require_once (JPATH_SITE.DS.'components'.DS.'com_user'.DS.'controller.php');
			$controller = new UserController;
			$view = $controller->getView($v, 'html');
			$view->_addPath('template', JPATH_SITE.DS.'components'.DS.'com_joomailermailchimpsignup'.DS.'templates');
			$view->_addPath('template', JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'com_joomailermailchimpsignup'.DS.'templates');
			$view->setLayout($l);

			$listId = $this->params->get('listid');

			$mergefields = $api->listMergeVars( $listId );
			$mergeids = $this->params->get('fields');

			$interests = $api->listInterestGroupings( $listId );
			$interestids = $this->params->get('interests');

			$ihtml = '';
			$checked = '';
			$groupings = '';
			$usermerges = array();

			//Check if the user is subscribed
			if($user->email){

				$userlists = $api->listsForEmail($user->email);

				if($userlists && in_array($listId,$userlists)) {
					$checked = 'checked="checked"';
				}
				$userinfo = $api->listMemberInfo($listId, $user->email);
				$usermerges = $userinfo['merges'];
				$groupings = $userinfo['merges']['GROUPINGS'];
			}

			$lang = & JFactory::getLanguage();
			$lang->load('plg_system_joomailermailchimpsignup', JPATH_ADMINISTRATOR);

			if($template == 'morph'){
			    $ihtml = '<li class="label"><label for="newsletter">';
			    $ihtml.= JText::_('JM_NEWSLETTERSIGNUP');
			    $ihtml.= '</label></li>';
			    $ihtml.= '<li>';
			    $ihtml.= "<input type='checkbox' name='newsletter' id='newsletter' value='1' ".$checked." />";
			    $ihtml.= '&nbsp;&nbsp;<label for="newsletter">';
			    $ihtml.= JText::_('JM_NEWSLETTERSIGNUP');
			    $ihtml.= '</label>';
			    $ihtml.= "</li>";
			} else {
			    $ihtml = "<tr><td height='40'><label for='newsletter'>";
			    $ihtml.= JText::_('JM_NEWSLETTERSIGNUP');
			    $ihtml.= '</label></td>';
			    $ihtml.= '<td height="40">';
			    $ihtml.= "<input type='checkbox' name='newsletter' id='newsletter' value='1' ".$checked." /><input type='hidden' name='oldEmail' value='".$user->email."' />";
			    $ihtml.= "</td></tr>";
			}

			//Merge Fields
			if($mergefields){
				$ihtml .= $this->buildMergeVarsHTML( $mergefields, $usermerges, $mergeids );
			}

			//Groups
			if($interests){
				//Build the HTML for the interests
				$ihtml .= $this->buildInterestsHTML( $interests, $groupings, $interestids );
			}
			$view->assignRef('ihtml', $ihtml);

			$pathway = &$mainframe->getPathway();
			$pathway->setPathway(NULL);

			ob_start();
			$view->display();
			$contents = ob_get_clean();
			$document = &JFactory::getDocument();
			$document->setBuffer($contents, 'component');
		}
	}

	function buildMergeVarsHTML($mergefields, $usermerges, $mergeids) {

		jimport('joomla.filesystem.file');
                if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
                    return;
                }
                $key = 'tag';
		$val = 'name';
		$ihtml = array();
		$values = '';
		$k = 0;
		foreach ($mergefields as $mf) {
			$tag = $mf['tag'];

			$attribs = '';
			$control_name = '';
			$options = '';
			$selected = '';
			$req = ($mf['req'])?'*':'';
			if(isset($usermerges[$tag])) { $usertag = $usermerges[$tag]; } else { $usertag = ''; }

			if ((is_array($mergeids) && in_array($mf['tag'],$mergeids) || $mergeids == $mf['tag'])) {

				if ($mf['field_type'] == 'dropdown') {

					$selected = 0;

					foreach ($mf['choices'] as $m){

						if($usertag==$m) {
							$selected = $m;
						}

						$options[] = array($key => $m , $val => $m);

					}

					$ihtml[] = array( '<label for="mf_'.$tag.'">'.$mf['name'].'</label>',
										JHTML::_('select.genericlist',$options, 'mf_'.$tag, $attribs, $key, $val, $selected, $control_name.'mf_'.$tag).$req);


				} elseif ($mf['field_type'] == 'radio') {

					$selected = 0;

					for($n=0; $n<count($mf['choices']); $n++) {
						//foreach ($mf['choices'] as $m){
						$m = $mf['choices'][$n];

						if($usertag==$m) {

							$selected = $m;

						}

						$options[] = JHTML::_( 'select.option', $m, $m );

					}

					$ihtml[] = array( '<label for="mf_'.$tag.'">'.$mf['name'].'</label>',
										JHTML::_('select.radiolist', $options, 'mf_'.$tag, 'class="inputbox"', 'value', 'text', $selected ).$req);

				} elseif ($mf['field_type'] == 'number' || $mf['field_type'] == 'text' || $mf['field_type'] == 'url') {

					$ihtml[] = array( '<label for="mf_'.$tag.'">'.$mf['name'].'</label>',
										'<input type="text" name="mf_'.$tag.'" id="mf_'.$mf['name'].'" class="inputbox '.$mf['field_type'].'" size="25" value="'.$usertag.'"/>'.$req);

				} elseif ($mf['field_type'] == 'date') {

					$ihtml[] = array( '<label for="mf_'.$tag.'">'.$mf['name'].'</label>',
										JHTML::_('calendar', $usertag, 'mf_'.$tag, 'mf_'.$tag, '%Y-%m-%d', '').$req);

					JHTML::_('behavior.calendar');

				} elseif ($mf['field_type'] == 'address') {

					$ihtml[] = array( '<label for="mf_'.$tag.'">'.$mf['name'].$req.'</label>');

					$addr1  = (isset($usertag['addr1'])) ? $usertag['addr1'] : '';
					$ihtml[ (count($ihtml)-1) ][] = '<input type="text" name="mf_'.$tag.'[addr1]" class="inputbox '.$mf['field_type'].'" value="'.$addr1.'"/>'
													.'<label for ="mf_'.$tag.'[addr1]" style="font-style:italic">'.JText::_('JM_STREET_ADDRESS').'</label>';

					$addr2  = (isset($usertag['addr2'])) ? $usertag['addr2'] : '';
					$ihtml[] = array( '',
									'<input type="text" name="mf_'.$tag.'[addr2]" class="inputbox '.$mf['field_type'].'" value="'.$addr2.'"/>'
									.'<label for ="mf_'.$tag.'[addr1]" style="font-style:italic">'.JText::_('JM_ADDRESS_LINE_2').'</label>');

					$city   = (isset($usertag['city'])) ? $usertag['city'] : '';
					$ihtml[] = array( '',
									'<input type="text" name="mf_'.$tag.'[city]" class="inputbox '.$mf['field_type'].'" value="'.$city.'"/>'
									.'<label for ="mf_'.$tag.'[addr1]" style="font-style:italic">'.JText::_('JM_CITY').'</label>');

					$state  = (isset($usertag['state'])) ? $usertag['state'] : '';
					$ihtml[] = array( '',
									'<input type="text" name="mf_'.$tag.'[state]" class="inputbox '.$mf['field_type'].'" value="'.$state.'"/>'
									.'<label for ="mf_'.$tag.'[addr1]" style="font-style:italic">'.JText::_('JM_STATE_PROVINCE_REGION').'</label>');

					$zip    = (isset($usertag['zip'])) ? $usertag['zip'] : '';
					$ihtml[] = array( '',
									'<input type="text" name="mf_'.$tag.'[zip]" class="inputbox '.$mf['field_type'].'" value="'.$zip.'"/>'
									.'<label for ="mf_'.$tag.'[addr1]" style="font-style:italic">'.JText::_('JM_ZIP_POSTAL').'</label>');

					$options = array(array($key => "164", $val => "USA"),array($key => "286", $val => "Aaland Islands"),array($key => "274", $val => "Afghanistan"),array($key => "2", $val => "Albania"),array($key => "3", $val => "Algeria"),array($key => "178", $val => "American Samoa"),array($key => "4", $val => "Andorra"),array($key => "5", $val => "Angola"),array($key => "176", $val => "Anguilla"),array($key => "6", $val => "Argentina"),array($key => "7", $val => "Armenia"),array($key => "8", $val => "Australia"),array($key => "9", $val => "Austria"),array($key => "10", $val => "Azerbaijan"),array($key => "11", $val => "Bahamas"),array($key => "12", $val => "Bahrain"),array($key => "13", $val => "Bangladesh"),array($key => "14", $val => "Barbados"),array($key => "15", $val => "Belarus"),array($key => "16", $val => "Belgium"),array($key => "17", $val => "Belize"),array($key => "18", $val => "Benin"),array($key => "19", $val => "Bermuda"),array($key => "20", $val => "Bhutan"),array($key => "21", $val => "Bolivia"),array($key => "22", $val => "Bosnia and Herzegovina"),array($key => "23", $val => "Botswana"),array($key => "24", $val => "Brazil"),array($key => "180", $val => "Brunei Darussalam"),array($key => "25", $val => "Bulgaria"),array($key => "26", $val => "Burkina Faso"),array($key => "27", $val => "Burundi"),array($key => "28", $val => "Cambodia"),array($key => "29", $val => "Cameroon"),array($key => "30", $val => "Canada"),array($key => "31", $val => "Cape Verde"),array($key => "32", $val => "Cayman Islands"),array($key => "33", $val => "Central African Republic"),array($key => "34", $val => "Chad"),array($key => "35", $val => "Chile"),array($key => "36", $val => "China"),array($key => "37", $val => "Colombia"),array($key => "38", $val => "Congo"),array($key => "183", $val => "Cook Islands"),array($key => "268", $val => "Costa Rica"),array($key => "39", $val => "Costa Rica"),array($key => "40", $val => "Croatia"),array($key => "276", $val => "Cuba"),array($key => "41", $val => "Cyprus"),array($key => "42", $val => "Czech Republic"),array($key => "43", $val => "Denmark"),array($key => "44", $val => "Djibouti"),array($key => "289", $val => "Dominica"),array($key => "187", $val => "Dominican Republic"),array($key => "45", $val => "Ecuador"),array($key => "46", $val => "Egypt"),array($key => "47", $val => "El Salvador"),array($key => "48", $val => "Equatorial Guinea"),array($key => "49", $val => "Eritrea"),array($key => "50", $val => "Estonia"),array($key => "51", $val => "Ethiopia"),array($key => "191", $val => "Faroe Islands"),array($key => "52", $val => "Fiji"),array($key => "53", $val => "Finland"),array($key => "54", $val => "France"),array($key => "277", $val => "French Polynesia"),array($key => "59", $val => "Germany"),array($key => "60", $val => "Ghana"),array($key => "194", $val => "Gibraltar"),array($key => "61", $val => "Greece"),array($key => "195", $val => "Greenland"),array($key => "192", $val => "Grenada"),array($key => "62", $val => "Guam"),array($key => "198", $val => "Guatemala"),array($key => "270", $val => "Guernsey"),array($key => "200", $val => "Haiti"),array($key => "66", $val => "Honduras"),array($key => "67", $val => "Hong Kong"),array($key => "68", $val => "Hungary"),array($key => "69", $val => "Iceland"),array($key => "70", $val => "India"),array($key => "71", $val => "Indonesia"),array($key => "278", $val => "Iran"),array($key => "279", $val => "Iraq"),array($key => "74", $val => "Ireland"),array($key => "75", $val => "Israel"),array($key => "76", $val => "Italy"),array($key => "202", $val => "Jamaica"),array($key => "78", $val => "Japan"),array($key => "288", $val => "Jersey  (Channel Islands)"),array($key => "79", $val => "Jordan"),array($key => "80", $val => "Kazakhstan"),array($key => "81", $val => "Kenya"),array($key => "82", $val => "Kuwait"),array($key => "85", $val => "Latvia"),array($key => "86", $val => "Lebanon"),array($key => "90", $val => "Liechtenstein"),array($key => "91", $val => "Lithuania"),array($key => "92", $val => "Luxembourg"),array($key => "208", $val => "Macau"),array($key => "93", $val => "Macedonia"),array($key => "94", $val => "Madagascar"),array($key => "95", $val => "Malawi"),array($key => "96", $val => "Malaysia"),array($key => "97", $val => "Maldives"),array($key => "98", $val => "Mali"),array($key => "99", $val => "Malta"),array($key => "212", $val => "Mauritius"),array($key => "101", $val => "Mexico"),array($key => "102", $val => "Moldova, Republic of"),array($key => "103", $val => "Monaco"),array($key => "290", $val => "Montenegro"),array($key => "105", $val => "Morocco"),array($key => "106", $val => "Mozambique"),array($key => "242", $val => "Myanmar"),array($key => "108", $val => "Nepal"),array($key => "109", $val => "Netherlands"),array($key => "110", $val => "Netherlands Antilles"),array($key => "213", $val => "New Caledonia"),array($key => "111", $val => "New Zealand"),array($key => "112", $val => "Nicaragua"),array($key => "113", $val => "Niger"),array($key => "114", $val => "Nigeria"),array($key => "272", $val => "North Korea"),array($key => "116", $val => "Norway"),array($key => "117", $val => "Oman"),array($key => "118", $val => "Pakistan"),array($key => "222", $val => "Palau"),array($key => "119", $val => "Panama"),array($key => "219", $val => "Papua New Guinea"),array($key => "120", $val => "Paraguay"),array($key => "121", $val => "Peru"),array($key => "122", $val => "Philippines"),array($key => "123", $val => "Poland"),array($key => "124", $val => "Portugal"),array($key => "126", $val => "Qatar"),array($key => "58", $val => "Republic of Georgia"),array($key => "128", $val => "Romania"),array($key => "129", $val => "Russia"),array($key => "130", $val => "Rwanda"),array($key => "205", $val => "Saint Kitts and Nevis"),array($key => "206", $val => "Saint Lucia"),array($key => "132", $val => "Samoa (Independent)"),array($key => "227", $val => "San Marino"),array($key => "133", $val => "Saudi Arabia"),array($key => "134", $val => "Senegal"),array($key => "266", $val => "Serbia"),array($key => "137", $val => "Singapore"),array($key => "138", $val => "Slovakia"),array($key => "139", $val => "Slovenia"),array($key => "223", $val => "Solomon Islands"),array($key => "141", $val => "South Africa"),array($key => "142", $val => "South Korea"),array($key => "143", $val => "Spain"),array($key => "144", $val => "Sri Lanka"),array($key => "146", $val => "Suriname"),array($key => "147", $val => "Swaziland"),array($key => "148", $val => "Sweden"),array($key => "149", $val => "Switzerland"),array($key => "152", $val => "Taiwan"),array($key => "153", $val => "Tanzania"),array($key => "154", $val => "Thailand"),array($key => "232", $val => "Tonga"),array($key => "234", $val => "Trinidad and Tobago"),array($key => "156", $val => "Tunisia"),array($key => "157", $val => "Turkey"),array($key => "287", $val => "Turks &amp; Caicos Islands"),array($key => "159", $val => "Uganda"),array($key => "161", $val => "Ukraine"),array($key => "162", $val => "United Arab Emirates"),array($key => "262", $val => "United Kingdom"),array($key => "163", $val => "Uruguay"),array($key => "239", $val => "Vanuatu"),array($key => "166", $val => "Vatican City State (Holy See)"),array($key => "167", $val => "Venezuela"),array($key => "168", $val => "Vietnam"),array($key => "169", $val => "Virgin Islands (British)"),array($key => "238", $val => "Virgin Islands (U.S.)"),array($key => "173", $val => "Zambia"),array($key => "174", $val => "Zimbabwe"));
					$options = array(
					array($key=>'AF',$val=>'AFGHANISTAN'),
					array($key=>'AX',$val=>'ÅLAND ISLANDS'),
					array($key=>'AL',$val=>'ALBANIA'),
					array($key=>'DZ',$val=>'ALGERIA'),
					array($key=>'AS',$val=>'AMERICAN SAMOA'),
					array($key=>'AD',$val=>'ANDORRA'),
					array($key=>'AO',$val=>'ANGOLA'),
					array($key=>'AI',$val=>'ANGUILLA'),
					array($key=>'AQ',$val=>'ANTARCTICA'),
					array($key=>'AG',$val=>'ANTIGUA AND BARBUDA'),
					array($key=>'AR',$val=>'ARGENTINA'),
					array($key=>'AM',$val=>'ARMENIA'),
					array($key=>'AW',$val=>'ARUBA'),
					array($key=>'AU',$val=>'AUSTRALIA'),
					array($key=>'AT',$val=>'AUSTRIA'),
					array($key=>'AZ',$val=>'AZERBAIJAN'),
					array($key=>'BS',$val=>'BAHAMAS'),
					array($key=>'BH',$val=>'BAHRAIN'),
					array($key=>'BD',$val=>'BANGLADESH'),
					array($key=>'BB',$val=>'BARBADOS'),
					array($key=>'BY',$val=>'BELARUS'),
					array($key=>'BE',$val=>'BELGIUM'),
					array($key=>'BZ',$val=>'BELIZE'),
					array($key=>'BJ',$val=>'BENIN'),
					array($key=>'BM',$val=>'BERMUDA'),
					array($key=>'BT',$val=>'BHUTAN'),
					array($key=>'BO',$val=>'BOLIVIA, PLURINATIONAL STATE OF'),
					array($key=>'BA',$val=>'BOSNIA AND HERZEGOVINA'),
					array($key=>'BW',$val=>'BOTSWANA'),
					array($key=>'BV',$val=>'BOUVET ISLAND'),
					array($key=>'BR',$val=>'BRAZIL'),
					array($key=>'IO',$val=>'BRITISH INDIAN OCEAN TERRITORY'),
					array($key=>'BN',$val=>'BRUNEI DARUSSALAM'),
					array($key=>'BG',$val=>'BULGARIA'),
					array($key=>'BF',$val=>'BURKINA FASO'),
					array($key=>'BI',$val=>'BURUNDI'),
					array($key=>'KH',$val=>'CAMBODIA'),
					array($key=>'CM',$val=>'CAMEROON'),
					array($key=>'CA',$val=>'CANADA'),
					array($key=>'CV',$val=>'CAPE VERDE'),
					array($key=>'KY',$val=>'CAYMAN ISLANDS'),
					array($key=>'CF',$val=>'CENTRAL AFRICAN REPUBLIC'),
					array($key=>'TD',$val=>'CHAD'),
					array($key=>'CL',$val=>'CHILE'),
					array($key=>'CN',$val=>'CHINA'),
					array($key=>'CX',$val=>'CHRISTMAS ISLAND'),
					array($key=>'CC',$val=>'COCOS (KEELING) ISLANDS'),
					array($key=>'CO',$val=>'COLOMBIA'),
					array($key=>'KM',$val=>'COMOROS'),
					array($key=>'CG',$val=>'CONGO'),
					array($key=>'CD',$val=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE'),
					array($key=>'CK',$val=>'COOK ISLANDS'),
					array($key=>'CR',$val=>'COSTA RICA'),
					array($key=>'CI',$val=>'CÔTE D\'IVOIRE'),
					array($key=>'HR',$val=>'CROATIA'),
					array($key=>'CU',$val=>'CUBA'),
					array($key=>'CY',$val=>'CYPRUS'),
					array($key=>'CZ',$val=>'CZECH REPUBLIC'),
					array($key=>'DK',$val=>'DENMARK'),
					array($key=>'DJ',$val=>'DJIBOUTI'),
					array($key=>'DM',$val=>'DOMINICA'),
					array($key=>'DO',$val=>'DOMINICAN REPUBLIC'),
					array($key=>'EC',$val=>'ECUADOR'),
					array($key=>'EG',$val=>'EGYPT'),
					array($key=>'SV',$val=>'EL SALVADOR'),
					array($key=>'GQ',$val=>'EQUATORIAL GUINEA'),
					array($key=>'ER',$val=>'ERITREA'),
					array($key=>'EE',$val=>'ESTONIA'),
					array($key=>'ET',$val=>'ETHIOPIA'),
					array($key=>'FK',$val=>'FALKLAND ISLANDS (MALVINAS)'),
					array($key=>'FO',$val=>'FAROE ISLANDS'),
					array($key=>'FJ',$val=>'FIJI'),
					array($key=>'FI',$val=>'FINLAND'),
					array($key=>'FR',$val=>'FRANCE'),
					array($key=>'GF',$val=>'FRENCH GUIANA'),
					array($key=>'PF',$val=>'FRENCH POLYNESIA'),
					array($key=>'TF',$val=>'FRENCH SOUTHERN TERRITORIES'),
					array($key=>'GA',$val=>'GABON'),
					array($key=>'GM',$val=>'GAMBIA'),
					array($key=>'GE',$val=>'GEORGIA'),
					array($key=>'DE',$val=>'GERMANY'),
					array($key=>'GH',$val=>'GHANA'),
					array($key=>'GI',$val=>'GIBRALTAR'),
					array($key=>'GR',$val=>'GREECE'),
					array($key=>'GL',$val=>'GREENLAND'),
					array($key=>'GD',$val=>'GRENADA'),
					array($key=>'GP',$val=>'GUADELOUPE'),
					array($key=>'GU',$val=>'GUAM'),
					array($key=>'GT',$val=>'GUATEMALA'),
					array($key=>'GG',$val=>'GUERNSEY'),
					array($key=>'GN',$val=>'GUINEA'),
					array($key=>'GW',$val=>'GUINEA-BISSAU'),
					array($key=>'GY',$val=>'GUYANA'),
					array($key=>'HT',$val=>'HAITI'),
					array($key=>'HM',$val=>'HEARD ISLAND AND MCDONALD ISLANDS'),
					array($key=>'VA',$val=>'HOLY SEE (VATICAN CITY STATE)'),
					array($key=>'HN',$val=>'HONDURAS'),
					array($key=>'HK',$val=>'HONG KONG'),
					array($key=>'HU',$val=>'HUNGARY'),
					array($key=>'IS',$val=>'ICELAND'),
					array($key=>'IN',$val=>'INDIA'),
					array($key=>'ID',$val=>'INDONESIA'),
					array($key=>'IR',$val=>'IRAN, ISLAMIC REPUBLIC OF'),
					array($key=>'IQ',$val=>'IRAQ'),
					array($key=>'IE',$val=>'IRELAND'),
					array($key=>'IM',$val=>'ISLE OF MAN'),
					array($key=>'IL',$val=>'ISRAEL'),
					array($key=>'IT',$val=>'ITALY'),
					array($key=>'JM',$val=>'JAMAICA'),
					array($key=>'JP',$val=>'JAPAN'),
					array($key=>'JE',$val=>'JERSEY'),
					array($key=>'JO',$val=>'JORDAN'),
					array($key=>'KZ',$val=>'KAZAKHSTAN'),
					array($key=>'KE',$val=>'KENYA'),
					array($key=>'KI',$val=>'KIRIBATI'),
					array($key=>'KP',$val=>'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF'),
					array($key=>'KR',$val=>'KOREA, REPUBLIC OF'),
					array($key=>'KW',$val=>'KUWAIT'),
					array($key=>'KG',$val=>'KYRGYZSTAN'),
					array($key=>'LA',$val=>'LAO PEOPLE\'S DEMOCRATIC REPUBLIC'),
					array($key=>'LV',$val=>'LATVIA'),
					array($key=>'LB',$val=>'LEBANON'),
					array($key=>'LS',$val=>'LESOTHO'),
					array($key=>'LR',$val=>'LIBERIA'),
					array($key=>'LY',$val=>'LIBYAN ARAB JAMAHIRIYA'),
					array($key=>'LI',$val=>'LIECHTENSTEIN'),
					array($key=>'LT',$val=>'LITHUANIA'),
					array($key=>'LU',$val=>'LUXEMBOURG'),
					array($key=>'MO',$val=>'MACAO'),
					array($key=>'MK',$val=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF'),
					array($key=>'MG',$val=>'MADAGASCAR'),
					array($key=>'MW',$val=>'MALAWI'),
					array($key=>'MY',$val=>'MALAYSIA'),
					array($key=>'MV',$val=>'MALDIVES'),
					array($key=>'ML',$val=>'MALI'),
					array($key=>'MT',$val=>'MALTA'),
					array($key=>'MH',$val=>'MARSHALL ISLANDS'),
					array($key=>'MQ',$val=>'MARTINIQUE'),
					array($key=>'MR',$val=>'MAURITANIA'),
					array($key=>'MU',$val=>'MAURITIUS'),
					array($key=>'YT',$val=>'MAYOTTE'),
					array($key=>'MX',$val=>'MEXICO'),
					array($key=>'FM',$val=>'MICRONESIA, FEDERATED STATES OF'),
					array($key=>'MD',$val=>'MOLDOVA, REPUBLIC OF'),
					array($key=>'MC',$val=>'MONACO'),
					array($key=>'MN',$val=>'MONGOLIA'),
					array($key=>'ME',$val=>'MONTENEGRO'),
					array($key=>'MS',$val=>'MONTSERRAT'),
					array($key=>'MA',$val=>'MOROCCO'),
					array($key=>'MZ',$val=>'MOZAMBIQUE'),
					array($key=>'MM',$val=>'MYANMAR'),
					array($key=>'NA',$val=>'NAMIBIA'),
					array($key=>'NR',$val=>'NAURU'),
					array($key=>'NP',$val=>'NEPAL'),
					array($key=>'NL',$val=>'NETHERLANDS'),
					array($key=>'AN',$val=>'NETHERLANDS ANTILLES'),
					array($key=>'NC',$val=>'NEW CALEDONIA'),
					array($key=>'NZ',$val=>'NEW ZEALAND'),
					array($key=>'NI',$val=>'NICARAGUA'),
					array($key=>'NE',$val=>'NIGER'),
					array($key=>'NG',$val=>'NIGERIA'),
					array($key=>'NU',$val=>'NIUE'),
					array($key=>'NF',$val=>'NORFOLK ISLAND'),
					array($key=>'MP',$val=>'NORTHERN MARIANA ISLANDS'),
					array($key=>'NO',$val=>'NORWAY'),
					array($key=>'OM',$val=>'OMAN'),
					array($key=>'PK',$val=>'PAKISTAN'),
					array($key=>'PW',$val=>'PALAU'),
					array($key=>'PS',$val=>'PALESTINIAN TERRITORY, OCCUPIED'),
					array($key=>'PA',$val=>'PANAMA'),
					array($key=>'PG',$val=>'PAPUA NEW GUINEA'),
					array($key=>'PY',$val=>'PARAGUAY'),
					array($key=>'PE',$val=>'PERU'),
					array($key=>'PH',$val=>'PHILIPPINES'),
					array($key=>'PN',$val=>'PITCAIRN'),
					array($key=>'PL',$val=>'POLAND'),
					array($key=>'PT',$val=>'PORTUGAL'),
					array($key=>'PR',$val=>'PUERTO RICO'),
					array($key=>'QA',$val=>'QATAR'),
					array($key=>'RE',$val=>'RÉUNION'),
					array($key=>'RO',$val=>'ROMANIA'),
					array($key=>'RU',$val=>'RUSSIAN FEDERATION'),
					array($key=>'RW',$val=>'RWANDA'),
					array($key=>'BL',$val=>'SAINT BARTHÉLEMY'),
					array($key=>'SH',$val=>'SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA'),
					array($key=>'KN',$val=>'SAINT KITTS AND NEVIS'),
					array($key=>'LC',$val=>'SAINT LUCIA'),
					array($key=>'MF',$val=>'SAINT MARTIN'),
					array($key=>'PM',$val=>'SAINT PIERRE AND MIQUELON'),
					array($key=>'VC',$val=>'SAINT VINCENT AND THE GRENADINES'),
					array($key=>'WS',$val=>'SAMOA'),
					array($key=>'SM',$val=>'SAN MARINO'),
					array($key=>'ST',$val=>'SAO TOME AND PRINCIPE'),
					array($key=>'SA',$val=>'SAUDI ARABIA'),
					array($key=>'SN',$val=>'SENEGAL'),
					array($key=>'RS',$val=>'SERBIA'),
					array($key=>'SC',$val=>'SEYCHELLES'),
					array($key=>'SL',$val=>'SIERRA LEONE'),
					array($key=>'SG',$val=>'SINGAPORE'),
					array($key=>'SK',$val=>'SLOVAKIA'),
					array($key=>'SI',$val=>'SLOVENIA'),
					array($key=>'SB',$val=>'SOLOMON ISLANDS'),
					array($key=>'SO',$val=>'SOMALIA'),
					array($key=>'ZA',$val=>'SOUTH AFRICA'),
					array($key=>'GS',$val=>'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS'),
					array($key=>'ES',$val=>'SPAIN'),
					array($key=>'LK',$val=>'SRI LANKA'),
					array($key=>'SD',$val=>'SUDAN'),
					array($key=>'SR',$val=>'SURINAME'),
					array($key=>'SJ',$val=>'SVALBARD AND JAN MAYEN'),
					array($key=>'SZ',$val=>'SWAZILAND'),
					array($key=>'SE',$val=>'SWEDEN'),
					array($key=>'CH',$val=>'SWITZERLAND'),
					array($key=>'SY',$val=>'SYRIAN ARAB REPUBLIC'),
					array($key=>'TW',$val=>'TAIWAN, PROVINCE OF CHINA'),
					array($key=>'TJ',$val=>'TAJIKISTAN'),
					array($key=>'TZ',$val=>'TANZANIA, UNITED REPUBLIC OF'),
					array($key=>'TH',$val=>'THAILAND'),
					array($key=>'TL',$val=>'TIMOR-LESTE'),
					array($key=>'TG',$val=>'TOGO'),
					array($key=>'TK',$val=>'TOKELAU'),
					array($key=>'TO',$val=>'TONGA'),
					array($key=>'TT',$val=>'TRINIDAD AND TOBAGO'),
					array($key=>'TN',$val=>'TUNISIA'),
					array($key=>'TR',$val=>'TURKEY'),
					array($key=>'TM',$val=>'TURKMENISTAN'),
					array($key=>'TC',$val=>'TURKS AND CAICOS ISLANDS'),
					array($key=>'TV',$val=>'TUVALU'),
					array($key=>'UG',$val=>'UGANDA'),
					array($key=>'UA',$val=>'UKRAINE'),
					array($key=>'AE',$val=>'UNITED ARAB EMIRATES'),
					array($key=>'GB',$val=>'UNITED KINGDOM'),
					array($key=>'US',$val=>'UNITED STATES'),
					array($key=>'UM',$val=>'UNITED STATES MINOR OUTLYING ISLANDS'),
					array($key=>'UY',$val=>'URUGUAY'),
					array($key=>'UZ',$val=>'UZBEKISTAN'),
					array($key=>'VU',$val=>'VANUATU'),
					array($key=>'see HOLY SEE',$val=>'VATICAN CITY STATE'),
					array($key=>'VE',$val=>'VENEZUELA, BOLIVARIAN REPUBLIC OF'),
					array($key=>'VN',$val=>'VIET NAM'),
					array($key=>'VG',$val=>'VIRGIN ISLANDS, BRITISH'),
					array($key=>'VI',$val=>'VIRGIN ISLANDS, U.S.'),
					array($key=>'WF',$val=>'WALLIS AND FUTUNA'),
					array($key=>'EH',$val=>'WESTERN SAHARA'),
					array($key=>'YE',$val=>'YEMEN'),
					array($key=>'ZM',$val=>'ZAMBIA'),
					array($key=>'ZW',$val=>'ZIMBABWE')

					); //@todo: cross-reference this list with Mailchimp country list


					$selected = (isset($usertag['country'])) ? $usertag['country'] : '' ;
					$ihtml[] = array( '',
									JHTML::_('select.genericlist',$options, 'mf_'.$tag.'[country]', $attribs, $key, $val, $selected, $control_name.'mf_'.$tag.'[country]').$req
									.'<label for ="mf_'.$tag.'[country]" style="font-style:italic">'.JText::_('JM_COUNTRY').'</label>');

				} elseif ($mf['field_type'] == 'phone') {

					$ihtml[] = array( '<label for="mf_'.$tag.'">'.JText::_('JM_PHONE').'</label>',

								'(<input type="text" size="3" maxlength="3" name="mf_'.$tag.'[area]" id="mf_'.$mf['name'].'-area" value="'.substr($usertag,0,3).'"/>)'
								.' <input type="text" size="3" maxlength="3" name="mf_'.$tag.'[detail1]" id="mf_'.$mf['name'].'-detail1" value="'.substr($usertag,4,3).'"/>'
								.' - <input type="text" size="4" maxlength="4" name="mf_'.$tag.'[detail2]" id="mf_'.$mf['name'].'-detail2" value="'.substr($usertag,8,4).'"/>'
								.'<label for="mf_'.$tag.'[detail2]">(####) ### - ####</label>'.$req);

				} elseif ($mf['field_type'] == 'imageurl') {
					//@todo: create HTML for image URL
				}
			}
		}

		$returnValue = '';
		if( count( $ihtml ) ){

			foreach( $ihtml as $field ){
				if(version_compare(JVERSION,'1.6.0','ge')){
					$returnValue .= '<dt>'.$field[0].'</dt><dd>'.$field[1].'</dd>';
				} else {
					$returnValue .= '<tr><td height="40">'.$field[0].'</td><td>'.$field[1].'</td></tr>';
				}
			}

		}

		return $returnValue;

	} //function

	function buildInterestsHTML($interests, $groupings, $interestids) {

		jimport('joomla.filesystem.file');
		if(!JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
			return;
		}
		$key = 'bit';
		$val = 'name';
		$ihtml = array();

		foreach ($interests as $i) {

			//Check user values for the interests
			if(isset($groupings) && $groupings !== '') {
				foreach($groupings as $group) {
					if($group['id'] == $i['id']) {
						if(stristr($group['groups'],',')) {
							$values[$i['id']] = explode(', ',$group['groups']);
						} else {
							$values[$i['id']] = array($group['groups']);
						}
					}
				}
			}
			$attribs = '';
			$control_name = '';
			$options = '';
			$selected = '';

			if ((is_array($interestids) && in_array($i['id'],$interestids) || $interestids == $i['id'])) {

				if ($i['form_field'] == 'checkboxes') {

					$ihtml[] = array( '<label for="interest_'.$i['id'].'">'.$i['name'].'</label>');
					foreach ($i['groups'] as $g){
						$selected = '';

						if(isset($values[$i['id']])){
							if(in_array($g[$val],$values[$i['id']])) {
								$selected = 'checked';
							}
						}
						if( ! isset($ihtml[ (count($ihtml)-1) ][1]) ) { $ihtml[ (count($ihtml)-1) ][1] = ''; }
						$ihtml[ (count($ihtml)-1) ][1] .= '<input type="checkbox" name="interest_'.$i['id'].'[]" id="'.$g[$key].'" value="'.$g[$key].'" '.$selected.'/>'
														.'<label for="'.$g[$key].'">'.$g[$val].'</label>&nbsp;';
					}

				} elseif ($i['form_field'] == 'dropdown') {

					foreach ($i['groups'] as $g){
						if(isset($values[$i['id']])){
							if(in_array($g[$val],$values[$i['id']])) {
								$selected = $g[$key];
							}
						}
						$options[] = array($key => $g[$key] , $val => $g[$val]);
					}


					$ihtml[] = array( '<label for="interest_'.$i['id'].'">'.$i['name'].'</label>',
										JHTML::_('select.genericlist',$options, 'interest_'.$i['id'], $attribs, $key, $val, $selected, $control_name.'interest_'.$i['id']));

				} elseif ($i['form_field'] == 'radio') {

					foreach ($i['groups'] as $g){
						if(isset($values[$i['id']])){
							if(in_array($g[$val],$values[$i['id']])) {
								$selected = $g[$key];
							}
						}
						$options[] = JHTML::_( 'select.option', $g[$key], $g[$val] );
					}
					$ihtml[] = array( '<label for="interest_'.$i['id'].'">'.$i['name'].'</label>',
										JHTML::_('select.radiolist', $options, 'interest_'.$i['id'], 'class="inputbox"', 'value', 'text', $selected ));
				}
			}
		}

		$returnValue = '';
		if( count( $ihtml ) ){

			foreach( $ihtml as $field ){
				if(version_compare(JVERSION,'1.6.0','ge')){
					$returnValue .= '<dt>'.$field[0].'</dt><dd>'.$field[1].'</dd>';
				} else {
					$returnValue .= '<tr><td height="40">'.$field[0].'</td><td>'.$field[1].'</td></tr>';
				}
			}

		}

		return $returnValue;

	} //function

	function api()
	{
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $MCapi  = $params->get( $paramsPrefix.'MCapi' );
	    $MC = new joomlamailerMCAPI($MCapi);
	    return $MC;
	}





	function addToSugar( $uid ){

	    $user =& JFactory::getUser( $uid );
	    $db	=& JFactory::getDBO();
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $sugar_name = $params->get( $paramsPrefix.'sugar_name' );
	    $sugar_pwd  = $params->get( $paramsPrefix.'sugar_pwd' );
	    $sugar_url  = $params->get( $paramsPrefix.'sugar_url' );

	    $config = $this->getCrmConfig( 'sugar' );
	    if( $config == NULL ){
		jimport('joomla.filesystem.file');
		if ( JFile::exists( JPATH_ADMINISTRATOR.'/components/com_comprofiler/admin.comprofiler.php') ) {
		    jimport( 'joomla.application.component.helper' );
		    $cHelper = JComponentHelper::getComponent( 'com_comprofiler', true );
		} else {
		     $cHelper->enabled = false;
		}

		$config = new stdClass();
		$config->first_name = ($cHelper->enabled) ? 'CB' : 'core';
	    }

	    require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'sugar.php');
	    $sugar = new SugarCRMWebServices;
	    $sugar->SugarCRM( $sugar_name, $sugar_pwd, $sugar_url );
	    $sugar->login();

	    $queryJS = false;
	    $queryCB = false;
	    $JSand = array();
	    foreach( $config as $k => $v ){
		if( $k != 'firstname' && $k != 'lastname' ){
		    $vEx = explode(';', $v);
		    if($vEx[0] == 'js' ) {
			$queryJS = true;
			$JSand[] = $vEx[1];
		    } else {
			$queryCB = true;
		    }
		}
	    }
	    $JSand = implode("','", array_unique($JSand) );

	    $userCB = false;

	    if( $config->first_name == 'core' ){
		$names = explode(' ', $user->name);
		$first_name = $names[0];
		$last_name = '';
		if(isset($names[1])){
		    for($i=1;$i<count($names);$i++){
			$last_name .= $names[$i].' ';
		    }
		}
		$last_name = trim($last_name);
	    } else {
		$query = "SELECT * FROM #__comprofiler WHERE user_id = '$user->id'";
		$db->setQuery($query);
		$userCB = $db->loadObjectList();

		$first_name = $userCB[0]->firstname;
		$last_name  = $userCB[0]->lastname;
		if( $userCB[0]->middlename != '' ){
		    $last_name = $userCB[0]->middlename.' '.$last_name;
		}
	    }
	    if( $queryJS ){
		$query = "SELECT field_id, value FROM #__community_fields_values ".
			 "WHERE user_id = '$user->id' ".
			 "AND field_id IN ('$JSand')";
		$db->setQuery($query);
		$JSfields = $db->loadObjectList();
		$JSfieldsArray = array();
		foreach($JSfields as $jsf){
		    $JSfieldsArray[$jsf->field_id] = $jsf->value;
		}
	    }

	    if( $queryCB ){
		if( !$userCB ){
		    $query = "SELECT * FROM #__comprofiler WHERE user_id = '$user->id'";
		    $db->setQuery($query);
		    $userCB = $db->loadObjectList();
		}
	    }
	    $data = array('first_name'  => $first_name,
			  'last_name'   => $last_name,
			  'email1'	=> $user->email
			 );
	    foreach( $config as $k => $v ){
		if( $k != 'first_name' && $k != 'last_name' ){
		    if( $v ){
			$vEx = explode(';', $v);
			if($vEx[0] == 'js' ) {
			    $data[$k] = ( isset($JSfieldsArray[$vEx[1]]) ) ? $JSfieldsArray[$vEx[1]] : '';
			} else {
			    $data[$k] = ( isset( $userCB[0]->{$vEx[1]} ) ) ? str_replace('|*|',', ',$userCB[0]->{$vEx[1]}) : '';
			}
		    }

		}
	    }

	    $existing_user = $sugar->findUserByEmail( $user->email );

	    if( isset( $existing_users[ $data['email1'] ] ) ){
		$data['id'] = $existing_user[ $d['email1'] ];
	    }

	    $sendData = array( $data );

	    $result = $sugar->setContactMulti( $sendData );


	    return;
	}

	function addToHighrise( $uid ){

	    $user =& JFactory::getUser( $uid );
	    $db	=& JFactory::getDBO();
	    $params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
	    $paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
	    $highrise_url = $params->get( $paramsPrefix.'highrise_url' );
	    $highrise_api_token = $params->get( $paramsPrefix.'highrise_api_token' );

	    $config = $this->getCrmConfig( 'highrise' );
	    if( $config == NULL ){
		jimport('joomla.filesystem.file');
		if ( JFile::exists( JPATH_ADMINISTRATOR.'/components/com_comprofiler/admin.comprofiler.php') ) {
		    jimport( 'joomla.application.component.helper' );
		    $cHelper = JComponentHelper::getComponent( 'com_comprofiler', true );
		} else {
		     $cHelper->enabled = false;
		}

		$config = new stdClass();
		$config->{'first-name'} = ($cHelper->enabled) ? 'CB' : 'core';
		$config->email_work = 'default';
	    }

	    require_once( JPATH_ADMINISTRATOR.'/components/com_joomailermailchimpintegration/helpers'.DS.'common.php' );

	    $queryJS = false;
	    $queryCB = false;
	    $JSand = array();
	    foreach( $config as $k => $v ){
		if( $k != 'first-name' && $k != 'last-name' ){
		    $vEx = explode(';', $v);
		    if($vEx[0] == 'js' ) {
			$queryJS = true;
			$JSand[] = $vEx[1];
		    } else {
			$queryCB = true;
		    }
		}
	    }
	    $JSand = implode("','", array_unique($JSand) );

	    require_once(JPATH_ADMINISTRATOR.'/components/com_joomailermailchimpintegration/libraries/push2Highrise.php');
	    $highrise = new Push_Highrise($highrise_url, $highrise_api_token);

	    $userCB = false;

	    if( $config->{'first-name'} == 'core' ){
		$names = explode(' ', $user->name);
		$firstname = $names[0];
		$lastname = '';
		if(isset($names[1])){
		    for($i=1;$i<count($names);$i++){
			$lastname .= $names[$i].' ';
		    }
		}
		$lastname = trim($lastname);
	    } else {
		$query = "SELECT * FROM #__comprofiler WHERE user_id = '$user->id'";
		$db->setQuery($query);
		$userCB = $db->loadObjectList();

		$firstname = $userCB[0]->firstname;
		$lastname  = $userCB[0]->lastname;
		if( $userCB[0]->middlename != '' ){
		    $lastname = $userCB[0]->middlename.' '.$lastname;
		}
	    }

	    $highriseUser = $highrise->person_in_highrise( array( 'first-name' => $firstname, 'last-name' => $lastname) );
	    $request['id'] = $highriseUser->id;

	    if( $queryJS ){
		$query = "SELECT field_id, value FROM #__community_fields_values ".
			 "WHERE user_id = '$user->id' ".
			 "AND field_id IN ('$JSand')";
		$db->setQuery($query);
		$JSfields = $db->loadObjectList();
		$JSfieldsArray = array();
		foreach($JSfields as $jsf){
		    $JSfieldsArray[$jsf->field_id] = $jsf->value;
		}
	    }

	    if( $queryCB ){
		if( !$userCB ){
		    $query = "SELECT * FROM #__comprofiler WHERE user_id = '$user->id'";
		    $db->setQuery($query);
		    $userCB = $db->loadObjectList();
		}
	    }

	    $xml =  "<person>\n";

	    if( (int)$highriseUser->id > 0){
		$xml .= '<id>'.$highriseUser->id."</id>\n";
	    }

	    $xml .=  "<first-name>".htmlspecialchars($firstname)."</first-name>\n"
		    ."<last-name>".htmlspecialchars($lastname)."</last-name>";



	    if( isset($config->title) && $config->title != '' ){
		$conf = explode(';', $config->title);
		$value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		$xml .= "\n<title>".htmlspecialchars($value)."</title>";
	    }
	    if( isset($config->background) && $config->background != '' ){
		$conf = explode(';', $config->background);
		$value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		$xml .= "\n<background>".htmlspecialchars($value)."</background>";
	    }
	    if( isset($config->company) && $config->company != '' ){
		$conf = explode(';', $config->company);
		$value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		$xml .= "\n<company-name>".htmlspecialchars($value).'</company-name>';
	    }


	    $xml .= "\n<contact-data>";
	    $xml .= "\n<email-addresses>";

	    $emailTypes = array( 'work', 'home', 'other' );
	    foreach ($emailTypes as $et){

		if( isset($config->{'email_'.$et}) && $config->{'email_'.$et} != '' ){
		if($config->{'email_'.$et} == 'default'){
		    $value = $user->email;
		} else {
		    $conf = explode(';', $config->{'email_'.$et});
		    $value = ( $conf[0] == 'js' ) ?  $JSfieldsArray[$conf[1]] : $userCB[0]->{$conf[1]};
		}

		$fieldId = '';
		if( isset($highriseUser->{'contact-data'}->{'email-addresses'}->{'email-address'}) ){
		foreach( $highriseUser->{'contact-data'}->{'email-addresses'} as $hu){
		    foreach( $hu->{'email-address'} as $ea){
			if( $ea->location == ucfirst($et) ){
			    $fieldId = '<id type="integer">'.$ea->id[0]."</id>\n";
			    break;
			}
		    }
		}
		}
		$xml .= "\n<email-address>\n"
			    .$fieldId
			    ."<address>".htmlspecialchars($value)."</address>\n"
			    ."<location>".ucfirst($et)."</location>\n"
			."</email-address>";
		}


	    }

	    $xml .= "\n</email-addresses>\n";

	    $xml .= "\n<phone-numbers>\n";
	    $phoneTypes = array('work','mobile','fax','pager','home','skype','other');
	    foreach($phoneTypes as $pt){
		if( isset($config->{'phone_'.$pt}) && $config->{'phone_'.$pt} != '' ){
		    $conf = explode(';', $config->{'phone_'.$pt});
		    $value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		}

		$fieldId = '';
		if( isset($highriseUser->{'contact-data'}->{'phone-numbers'}->{'phone-number'}) ){
		foreach( $highriseUser->{'contact-data'}->{'phone-numbers'} as $hu){
		    foreach( $hu->{'phone-number'} as $pn){
			if( $pn->location == ucfirst($pt) ){
			    $fieldId = '<id type="integer">'.$pn->id[0]."</id>\n";
			    break;
			}
		    }
		}
		}
		$xml .= "<phone-number>\n"
			    .$fieldId
			    ."<number>".htmlspecialchars($value)."</number>\n"
			    ."<location>".ucfirst($pt)."</location>\n"
			."</phone-number>";
	    }
	    $xml .= "\n</phone-numbers>\n";

	    $xml .= "\n<instant-messengers>\n";
	    $imTypes = array('AIM','MSN','ICQ','Jabber','Yahoo','Skype','QQ','Sametime','Gadu-Gadu','Google Talk','Other');
	    foreach($imTypes as $im){
		if( isset($config->{$im}) && $config->{$im} != '' ){
		    $value = false;
		    if( $config->{$im} == 'default' ){
			$value = $user->email;
		    } else if( $config->{$im} != '' ){
			$conf = explode(';', $config->{$im});
			$value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		    }
		    if( $value ){
			$fieldId = '';
			if( isset($highriseUser->{'contact-data'}->{'instant-messengers'}->{'instant-messenger'}) ){
			foreach( $highriseUser->{'contact-data'}->{'instant-messengers'} as $imx){
			    foreach( $imx->{'instant-messenger'} as $ia){
				if( $ia->protocol == $im ){
				    $fieldId = '<id type="integer">'.$ia->id[0]."</id>\n";
				    break;
				}
			    }
			}
			}
			$xml .= "<instant-messenger>\n"
				    .$fieldId
				    ."<address>".htmlspecialchars($value)."</address>\n"
				    ."<location>Work</location>\n"
				    ."<protocol>".$im."</protocol>\n"
				."</instant-messenger>";
		    }
		}
	    }
	    $xml .= "\n</instant-messengers>\n";

	    if( isset($config->website) && $config->website != '' ){
	    $xml .= "\n<web-addresses>\n";
	    $conf = explode(';', $config->website);
	    $value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');

	    $fieldId = '';
	    if( isset($highriseUser->{'contact-data'}->{'web-addresses'}->{'web-address'}) ){
	    foreach( $highriseUser->{'contact-data'}->{'web-addresses'} as $ws){
		foreach( $ws->{'web-address'} as $wa){
		    if( $wa->location == 'Work' ){
			$fieldId = '<id type="integer">'.$wa->id[0]."</id>\n";
			break;
		    }
		}
	    }
	    }
	    $xml .= "<web-address>\n"
			.$fieldId
			."<url>".htmlspecialchars($value)."</url>\n"
			."<location>Work</location>\n"
		    ."</web-address>";
	    $xml .= "\n</web-addresses>\n";
	    }

	    if( isset($config->twitter) && $config->twitter != '' ){
	    $xml .= "\n<twitter-accounts>\n";
	    $conf = explode(';', $config->twitter);
	    $value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
	    $value = removeSpecialCharacters( $value );
	    $fieldId = '';
	    if( isset($highriseUser->{'contact-data'}->{'twitter-accounts'}->{'twitter-account'}) ){
	    foreach( $highriseUser->{'contact-data'}->{'twitter-accounts'} as $tac){
		foreach( $tac->{'twitter-account'} as $ta){
		    if( $ta->location == 'Personal' ){
			$fieldId = '<id type="integer">'.$ta->id[0]."</id>\n";
			break;
		    }
		}
	    }
	    }
	    $xml .= "<twitter-account>\n"
			.$fieldId
			."<username>".htmlspecialchars( str_replace(' ','',$value) )."</username>\n"
			."<location>Personal</location>\n"
		    ."</twitter-account>";
	    $xml .= "\n</twitter-accounts>\n";
	    }

	    if(    ( isset($config->street) && $config->street != '' )
		|| ( isset($config->city)   && $config->city != ''   )
		|| ( isset($config->zip)    && $config->zip != ''    )
		|| ( isset($config->state)  && $config->state != ''  )
		|| ( isset($config->country)&& $config->country != '')
	      ){
		$xml .= "\n<addresses>\n";
		$xml .= "<address>\n";

		$fieldId = '';
		if( isset($highriseUser->{'contact-data'}->addresses->address) ){
		foreach( $highriseUser->{'contact-data'}->addresses as $ads){
		    foreach( $ads->address as $ad){
			if( $ad->location == 'Work' ){
			    $fieldId = '<id type="integer">'.$ad->id[0]."</id>\n";
			    break;
			}
		    }
		}
		}
		$xml .= $fieldId;

		if( isset($config->street) && $config->street != '' ) {
		    $conf = explode(';', $config->street);
		    $value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		    $xml .= "<street>".htmlspecialchars($value)."</street>\n";
		}
		if( isset($config->city)   && $config->city != '' ) {
		    $conf = explode(';', $config->city);
		    $value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		    $xml .= "<city>".htmlspecialchars($value)."</city>\n";
		}
		if( isset($config->zip)    && $config->zip != '' ) {
		    $conf = explode(';', $config->zip);
		    $value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		    $xml .= "<zip>".htmlspecialchars($value)."</zip>\n";
		}
		if( isset($config->state)  && $config->state != '' ) {
		    $conf = explode(';', $config->state);
		    $value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		    $xml .= "<state>".htmlspecialchars($value)."</state>\n";
		}
		if( isset($config->country) && $config->country != '' ) {
		    $conf = explode(';', $config->country);
		    $value = ( $conf[0] == 'js' ) ?  ( (isset($JSfieldsArray[$conf[1]]))?$JSfieldsArray[$conf[1]]:'') : ((isset($userCB[0]->{$conf[1]}))?$userCB[0]->{$conf[1]}:'');
		    $xml .= "<country>".htmlspecialchars($value)."</country>\n";
		}

		$xml .= "<location>Work</location>\n";
		$xml .= "</address>\n";
		$xml .= "</addresses>\n";
	    }

	    $xml .= "\n</contact-data>";
	    $xml .= "\n</person>";

	    $request['xml'] = $xml;

	    $highrise->pushContact($request);

	    return;
	}

	function getCrmConfig( $crm ){

		$db    =& JFactory::getDBO();
		$query = "SELECT params FROM #__joomailermailchimpintegration_crm WHERE crm = '".$crm."'";
		$db->setQuery($query);
		$config = json_decode($db->loadResult());

		return $config;
	}

	function onAfterStoreUser( $user, $isnew, $success, $msg )
	{
	    $option = JRequest::getCmd('option');

	    if(		$option == 'com_user'
			||	$option == 'com_users'
			||	$option == 'com_virtuemart'
			||	$option == 'com_comprofiler'
			||(	$option == 'com_community' && !isset($_POST['jsemail']) )
	    ){
		JRequest::setVar( 'component', $option );
		$modelpath = JPATH_SITE.DS.'components/com_joomailermailchimpsignup/models/joomailermailchimpsignup.php';
		require_once($modelpath);

		$model = new JoomailerMailchimpSignupModelJoomailerMailchimpSignup;
		$model->register_save();
	    }
	}
	// J1.6
	function onUserAfterSave($user, $isnew, $success, $msg )
	{
		$this->onAfterStoreUser($user, $isnew, $success, $msg );
	}

	// unsubscribe the user when his account is deleted and if this option is set in the plugin config
	// J1.5
	function onAfterDeleteUser( $user, $success, $msg )
	{
	    $this->unsubscribeUser( $user, $success, $msg );
	}
	// J1.6
	function onUserAfterDelete( $user, $success, $msg )
	{
	    $this->unsubscribeUser( $user, $success, $msg );
	}

	function unsubscribeUser( $user, $success, $msg )
	{
		$plugin = JPluginHelper::getPlugin( 'system', 'joomailermailchimpsignup' );
		$unsubscribe = $this->params->get('unsubscribe', 0);
	    if( $unsubscribe && $success ){
			//Unsubscribe the user
			$listId = $this->params->get('listid');
			$api = $this->api();
			$result = $api->listUnsubscribe( $listId, $user['email'], false, false, false );

			$db    =& JFactory::getDBO();
			$query = 'DELETE FROM #__joomailermailchimpintegration "'.$email.'", "'.$listId.'")';
			$db->setQuery($query);
			$db->Query();
	    }
	}
}

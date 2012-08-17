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

if( !class_exists('MCAPI')) {
	require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php' );
}

jimport( 'joomla.application.component.model' );

/**
 * JoomailerMailchimpSignup Model
 *
 * @package    JoomailerMailchimpSignup
 * @subpackage Models
 */
class JoomailerMailchimpSignupModelJoomailerMailchimpSignup extends JModel
{
	/**
	 * Method to save user data from admin interface
	 */
	function mcsave($ext)
	{
		$api = $this->api();
		
		//Create the api object
		$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
		$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
		$MCapi  = $params->get( $paramsPrefix.'MCapi' );
		$api = new joomlamailerMCAPI($MCapi);

		$db = & JFactory::getDBO();
		$email = JRequest::getVar('email');
		$name = JRequest::getVar('name');
		$name = explode(' ',$name);
		$fname = $name[0];
		$lname = (isset($name[1])) ? $name[1] : '';

		//Get the ID of the mailing list
		$plugin = & JPluginHelper::getPlugin('system', 'joomailermailchimpsignup');
		$pluginParams = new JParameter( $plugin->params );
		$listId = $pluginParams->get('listid');
        $dbext = ($ext == 'com_community') ? 'JS' : 'CB';
		//Get the fields associated with the extension
		$query = 'SELECT * FROM #__joomailermailchimpintegration_custom_fields '
		.'WHERE framework = "'.$dbext.'" '
		.'AND listID = "'.$listId.'"';
		$db->setQuery($query);
		$fields = $db->loadObjectList();
		$merge_vars = $this->getData($fields, $dbext);
		$others = array('FNAME'=>$fname, 'LNAME'=>$lname,'INTERESTS'=>'');
		$merge_vars = array_merge($others,$merge_vars);
		$email_type = '';
		$replace_interests = true;
        //var_dump($merge_vars);die;var_dump($_POST);die;
		$retval = $api->listUpdateMember( $listId, $email, $merge_vars, $email_type, $replace_interests);

		if ($api->errorCode){
			echo "Unable to update member info!\n";
			echo "\tCode=".$api->errorCode."\n";
			echo "\tMsg=".$api->errorMessage."\n";

		} else {
		    if($ext == 'com_community') {
    			//echo "Returned: ".$retval."\n";
    			require_once (JPATH_ADMINISTRATOR.'/components/com_community/controllers/controller.php');
    			require_once (JPATH_ADMINISTRATOR.'/components/com_community/controllers/users.php');
    			require_once (JPATH_ADMINISTRATOR.'/components/com_community/models/users.php');
    			$lang = & JFactory::getLanguage();
    			$lang->load('com_community',JPATH_ADMINISTRATOR);
    			$userController = new CommunityControllerUsers();
    			$userController->execute( 'save' );
    			$userController->redirect();
            } elseif($ext == 'com_comprofiler') {
                $GLOBALS['_JREQUEST']['option'] = array('DEFAULTCMD0'=>'com_comprofiler');
                require_once (JPATH_ADMINISTRATOR.'/components/com_comprofiler/admin.comprofiler.controller.php');
                saveUser($ext);
            }
		}
	}//function
	
	/**
	 * Method to create Mailchimp api object
	 */
	function api() {
		
		$params =& JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
		$paramsPrefix = (version_compare(JVERSION,'1.6.0','ge')) ? 'params.' : '';
		$MCapi  = $params->get( $paramsPrefix.'MCapi' );
		$api = new joomlamailerMCAPI($MCapi);
		
		return $api;
		
	}//function
	
	/**
	 * Method to get merge fields and interests from form
	 */	
	function getData($fields,$ext) {
		$merges = array();
		$groupings = array();
        $db = & JFactory::getDBO();
        if($ext == 'JS') {
          $table = 'community_fields';
          $field = 'id';
          $suffix = 'field';
        } else {
          $table = 'comprofiler_fields';
          $field = 'name';
          $suffix = '';
        }
		if($fields) {
			foreach($fields as $f) {
				$groups = '';
				$val = JRequest::getVar($suffix.$f->dbfield);
				if($f->type == 'field') {
					$query = 'SELECT type FROM #__'.$table.' '
							.'WHERE '.$field.' = "'.$f->dbfield.'"';
					$db->setQuery($query);
					$type = $db->loadResult();
					if($type == 'date' && $ext == 'JS') {
						$merges[$f->grouping_id] = $val[2].'-'.$val[1].'-'.$val[0];
					} elseif($type == 'date' && $ext == 'CB') {
						$merges[$f->grouping_id] = substr($val,3,2).'-'.substr($val,0,2).'-'.substr($val,6,4);
					} else {
						$merges[$f->grouping_id] = $val;
					}
				} elseif($f->type='group') {
					if (is_array($val)) {
						foreach ($val as $v) {
							$groups .= $v.',';
						}
						$groups = substr($groups,0,-1);
					} else {
						$groups .= $val;
					}
					$groupings[$f->name] =  array('name' => $f->name, 'id' => $f->grouping_id, 'groups' => $groups);
				}
			}
		}
		$result = array_merge($merges, array('GROUPINGS' => $groupings));
		return $result;
	}
}

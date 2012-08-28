<?php

/**

* @package 		ezTestimonial Component

* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.

* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php

* @author		Saran Chamling (saaraan@gmail.com)

*/ 



// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' ); 



jimport('joomla.application.component.controller');

class testimonialController extends JController

{

	function display() {

		switch (JRequest::getVar('task')) 

		{

			case 'submitmonial':

				testimonialController::addMonial();

				break;

			default:

				break;			

		}

		switch (JRequest::getVar( 'view' ))

			{

			case "form":

				JRequest::setVar('view', 'form' );

				break;				

			default:

				JRequest::setVar('view', 'eztestimonials' );

			}

		parent::display();

	}

	

	function LoadAntiSpam()

	{

		$myparams 		= &JComponentHelper::getParams('com_eztestimonial');
		$spamfilter 	= $myparams->getValue('data.params.spamfilter');

		

		if($spamfilter==1) //reCaptcha 

		{

			$republickey 	= $myparams->getValue('data.params.republickey');

			$looknfeel 	= $myparams->getValue('data.params.restyle');

			 echo '<script type="text/javascript">

			 var RecaptchaOptions = {

				theme : \''.$looknfeel.'\'

			 };

			 </script>';

			require_once( JPATH_COMPONENT.DS.'assets'.DS.'3rdparty'.DS.'recaptchalib.php' );

			$publickey = $republickey; // you got this from the signup page

			echo recaptcha_get_html($publickey);

		}

	}

	

	function addMonial()

	{

		jimport('joomla.filesystem.file');

		jimport( 'joomla.utilities.utility' );

		JRequest::checkToken() or jexit( 'Invalid Token' );

		$app 				= JFactory::getApplication();

		$db 				=& JFactory::getDBO();

		$document 			=& JFactory::getDocument();

		require_once( JPATH_COMPONENT.DS.'assets'.DS.'3rdparty'.DS.'SimpleImage.php' );

		$myparams 			= &JComponentHelper::getParams('com_eztestimonial');

		$imageSubFolder 	= $myparams->getValue('data.params.imagefolder');

		$autoApprove 		= $myparams->getValue('data.params.autoapprove',0);

		$uploadSize 		= $myparams->getValue('data.params.imagesize',400);

		$spamfilter 		= $myparams->getValue('data.params.spamfilter');

		$sendemailtouser 	= $myparams->getValue('data.params.sendemailtouser',0);

		$sendemailtoadmin 	= $myparams->getValue('data.params.sendemailtoadmin',0);

		$summerytxtlength 	= $myparams->getValue('data.params.summerytxtlength',100);

		$ImgUrl 			= JRoute::_(JURI::base().'images/'.$imageSubFolder.'/');

		$returnUrl			= JRoute::_("index.php?option=com_eztestimonial&view=testimonials");

		$valid 				= true;

		$fullname 			= strip_tags(JRequest::getVar('iname'));

		$useremail 			= strip_tags(JRequest::getVar('iemail'));

		$location 			= strip_tags(JRequest::getVar('iaddress'));

		$website 			= strip_tags(JRequest::getVar('iwebsite'));

		$message 			= strip_tags(JRequest::getVar('imessage'));

		$aboutme 			= strip_tags(JRequest::getVar('iboutme'));

		$rating 			= JRequest::getVar('rating');

		$file 				= JRequest::getVar('iimage', null, 'files', 'array');

		$filename 			= JFile::makeSafe($file['name']);

		$src 				= $file['tmp_name'];

		$extension_of_image = testimonialController::get_extension(strtolower($filename)); //get the extension of image

		$FileSize=filesize($file['tmp_name']);	

		$AllowedSize =$uploadSize*1048576;



		if($spamfilter==1) //reCaptcha 

		{

			$privatekey 	= $myparams->getValue('data.params.reprivatekey');

			require_once( JPATH_COMPONENT.DS.'assets'.DS.'3rdparty'.DS.'recaptchalib.php' );

			$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);

			if (!$resp->is_valid) 

			{

				 $app->enqueueMessage(JText::_('COM_TESTIMONIALS_WRONGRECAPTCHA'), 'error');

				 $valid=false;

			}

		}

		elseif($spamfilter==2) //Akismet

		{

			$akismetKey 	= $myparams->getValue('data.params.akismetKey');

			require_once( JPATH_COMPONENT.DS.'assets'.DS.'3rdparty'.DS.'Akismet.class.php' );

			$MyURL =  JURI::base();

			$akismet = new Akismet($MyURL ,$akismetKey);

			$akismet->setCommentAuthor($fullname);

			$akismet->setCommentAuthorEmail($email);

			$akismet->setCommentAuthorURL($website);

			$akismet->setCommentContent($message);

			$akismet->setPermalink(JURI::current());

			if($akismet->isCommentSpam())

				{

					die("spam alert!");

					$valid=false;

				}

		}





		if ($FileSize > $AllowedSize)

		{

			 $exceededtxt = JText::sprintf(JText::_('COM_TESTIMONIALS_IMAGESIZETOOBIG'),testimonialController::format_bytes($AllowedSize),testimonialController::format_bytes($FileSize));

			 $app->enqueueMessage($exceededtxt, 'error');

			 $valid=false;

		}



		if (strlen($FileSize)<=1 && strlen($filename)>1)

		{

			 $app->enqueueMessage(JText::_('COM_TESTIMONIALS_ERRUPLOADING'), 'error');

			 $valid=false;

		}



		if($FileSize>1 && $valid==true)

		{

			// Import image

			switch($extension_of_image) {

				case 'jpg':

				case 'jpeg':

				case 'png':

				case 'gif':

				break;

				default:

					// Unsupported format

						$app->enqueueMessage(JText::_('COM_TESTIMONIALS_FILENOTSUPPORTED'), 'error');

						$valid=false;

				break;

			}



		}

		if($FileSize>1 && $valid==true)

		{

			  $random_str 			= testimonialController::random_str();

			  $photo_name 			= strtolower(str_replace(" ", "-",htmlspecialchars($fullname)))."-".$random_str."."; 	 // cleaned photo name with random charactor

			  $newPhotoname 		= $photo_name.$extension_of_image;

			  $newPhotoPath 		= JPATH_BASE . DS . "images" . DS . $imageSubFolder . DS;

			  $thumb_dest 			= $newPhotoPath .'thumb_'.$newPhotoname;

			  $thumb_dest50			= $newPhotoPath .'thumb50_'.$newPhotoname;

			  $dest 				= $newPhotoPath .$newPhotoname;

			  $image 				= new SimpleImage();

			  

			  $image->square_crop($file['tmp_name'], $thumb_dest, $thumb_size = 200, $jpg_quality = 90);

			  $image->square_crop($file['tmp_name'], $thumb_dest50, $thumb_size = 50, $jpg_quality = 90);

			  $image->load($file['tmp_name']);

			  //$image->resizeToWidth(600);

			  $image->save($dest);

		}else{

			$newPhotoname 		= '';

		}



		if(strlen($fullname)<2)

		{

			$app->enqueueMessage(JText::_('COM_TESTIMONIALS_EMPTYNAME'), 'error');

			$valid=false;

		}

		if(strlen($useremail)<2)

		{

			$app->enqueueMessage(JText::_('COM_TESTIMONIALS_EMPTYEMAIL'), 'error');

			$valid=false;

		}



		if(strlen($location)<2)

		{

			$app->enqueueMessage(JText::_('COM_TESTIMONIALS_EMPTYLOCATION'), 'error');

			$valid=false;

		}

		if(strlen($message)<2)

		{

			$app->enqueueMessage(JText::_('COM_TESTIMONIALS_EMPTYMSSG'), 'error');	

			$valid=false;

		}

			if($valid)

			{

					$approved = ($autoApprove==1)?1:0;

					$postdata = array('fullName'    		=> $fullname,

									   'email'  			=> $useremail,

									   'location'  			=> $location,

									   'aboutauthor'  		=> $aboutme,

									   'website' 			=> $website,

									   'message_summary'    => testimonialController::truncate($message, $summerytxtlength),

									   'message_long'		=> $message,

									   'image_name'			=> $newPhotoname,

									   'added_date'			=> date("Y-m-d H:i:s"),

									   'rating'				=> $rating,

									   'approved'			=> $approved);

									   

					$row =& $this->getTable();				   

					if (!$row->bind( $postdata)) {

						$app->enqueueMessage($row->getError(), 'error');

					}

					if (!$row->store()) {

						$app->enqueueMessage($row->getError(), 'error');

					}else{


						//send email to user
					$sitename 		= $app->getCfg('sitename');
					
						if($sendemailtouser==1)

						{

							$useremailfromnametxt 	= $myparams->getValue('data.params.useremailfromnametxt','From A Company');

							$useremailaddress 		= $myparams->getValue('data.params.useremailfromtxt','noreply@somesite.com');

							$useremailsubject 		= $myparams->getValue('data.params.useremailsubjecttxt','Email Subject');

							$useremailbody 			= $myparams->getValue('data.params.useremailtxt','Email Body');

							$useremailbody 			=  JText::sprintf($useremailbody,$fullname);
							
							$prasearray = array('{b}' => '<b>', '{/b}' => '</b>','{br}' => '<br />', '{sitename}' => $sitename, '{siteurl}' => JURI::base(), '{name}' => $fullname);
							$useremailbody 			= testimonialController::mail_body_phraser($useremailbody,$prasearray);


							$SendUserEmail 			= JUtility::sendMail($useremailaddress, $useremailfromnametxt, $useremail, $useremailsubject, $useremailbody,true);

							if(!$SendUserEmail)

							{

								$app->enqueueMessage(JText::_('COM_TESTIMONIALS_EMAILFAILDUSER'), 'error');

							}

						}
						$adminemails	= explode(",",$myparams->getValue('data.params.adminemails','test@gmail.com'));
						$adminmailtxt	= $myparams->getValue('data.params.adminmailtxt');
						
						$prasearray = array('{b}' => '<b>', '{/b}' => '</b>', '{br}' => '<br />', '{sitename}' => $sitename, '{siteurl}' => JURI::base());
						$adminmailtxt			= testimonialController::mail_body_phraser($adminmailtxt,$prasearray);

							// send mail to all administrators
						foreach ($adminemails as $adminemail)
						{
									
									$adminmailtxt 			=  JText::sprintf($adminmailtxt,$row->iname);
									$SendAdminEmail = JUtility::sendMail($mailfrom, $fromname,$adminemail, $adminmailsubjecttxt, $adminmailtxt,true);
									$app->enqueueMessage($adminemailstosend, 'error');
									if(!$SendAdminEmail)

									{

										$app->enqueueMessage(JText::_('COM_TESTIMONIALS_EMAILFAILDADMIN'), 'error');

									}

						}
							//display message accordingly

							if($autoApprove==0)

							{

								$app->enqueueMessage(JText::_('COM_TESTIMONIALS_WAITINGAPPROVAL'), 'message');	

								$app->redirect($returnUrl);

							}else{

								$app->enqueueMessage(JText::_('COM_TESTIMONIALS_PUBLISHEDMSG'), 'message');	

								$app->redirect($returnUrl);

							}



					}

			}

	}

	public function getTable($type = 'Testimonials', $prefix = 'Table', $config = array()) 

	{

		return JTable::getInstance($type, $prefix, $config);

	}

	function format_bytes($size) {

		$units = array(' B', ' KB', ' MB', ' GB', ' TB');

		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;

		return round($size, 2).$units[$i];

	}

	function truncate($str, $len) {

	  $tail = max(0, $len-10);

	  $trunk = substr($str, 0, $tail);

	  $trunk .= strrev(preg_replace('~^..+?[\s,:]\b|^...~', '...', strrev(substr($str, $tail, $len-$tail))));

	  return $trunk;

	}

	//Get extionsion of file--------------------

	function get_extension($filename)

	{

	  $myext = substr($filename, strrpos($filename, '.'));

	  return str_replace('.','',$myext);

	}

	// Generate a random character string

	function random_str($length = 10, $chars = 'abcdefghijklmnopqrstuvwxyz1234567890')

	{

			if($length > 0)

			{

				$chars_length = (strlen($chars) - 1);

				$string = $chars{rand(0, $chars_length)};

				for ($i = 1; $i < $length; $i = strlen($string))

				{

					$r = $chars{rand(0, $chars_length)};

					if ($r != $string{$i - 1}) $string .=  $r;

				}

			

				return $string;

			}

	}

	//just clean string from harmful inputs

	function just_clean($string)

	{

		// Replace other special chars

		$specialCharacters = array(

		'#' => '','$' => '','%' => '','&' => '','@' => '','.' => '','€' => '','+' => '','=' => '','§' => '','\\' => '','/' => '','\'' => '',);

		while (list($character, $replacement) = each($specialCharacters)) {

		$string = str_replace($character, '-' . $replacement . '-', $string);

		}

		$string = strtr($string,"ÀÁÂÃÄÅ�áâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn.");

		// Remove all remaining other unknown characters

		$string = preg_replace('/[^a-zA-Z0-9\-]/', '', $string);

		$string = preg_replace('/^[\-]+/', '', $string);

		$string = preg_replace('/[\-]+$/', '', $string);

		$string = preg_replace('/[\-]{2,}/', '', $string);

		return $string;

	}
	
	function mail_body_phraser($string,$ReplaceArray)
	{
		$result = str_replace(array_keys($ReplaceArray), array_values($ReplaceArray),$string);
		return $result;
	}

	function RemoveExtension($strName) 
	{ 
		 $ext = strrchr($strName, '.'); 
		 if($ext !== false) 
		 { 
			 $strName = substr($strName, 0, -strlen($ext)); 
		 } 
		 return $strName; 
	} 
}


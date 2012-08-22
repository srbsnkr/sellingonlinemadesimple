<?php
/**
* @package 		ezTestimonial Component
* @copyright	Copyright (C) Computer - http://www.saaraan.com All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling (saaraan@gmail.com)
*/ 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); 

jimport( 'joomla.application.application' );
$app 				=& JFactory::getApplication();
$document 			=& JFactory::getDocument();
$user 				=& JFactory::getUser();
$myparams 			= &JComponentHelper::getParams('com_eztestimonial');
$imageSubFolder 	= $myparams->getValue('data.params.imagefolder');
					
$document->addStyleSheet('components/com_eztestimonial/assets/cssbackend.css');

JHtml::_('behavior.framework', true);
JHTML::_('script','system/modal.js', true, true);	
JHTML::_('stylesheet','system/modal.css', array(), true);
$modalbox 	= 'window.addEvent(\'domready\', function() {SqueezeBox.initialize({});SqueezeBox.assign($$(\'a.modal\'), {parse: \'rel\'});});';
$document->addScriptDeclaration($modalbox);

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$formlink 		= JRoute::_('index.php?option=com_eztestimonial&view=form');
if(!$this->items)
{
	if(strlen($this->lists['search'])>1)
	{
	$app->enqueueMessage('No Results found containing "'.htmlspecialchars($this->lists['search']).'"!', 'error');
	}else{
	$app->enqueueMessage('No testimonials yet', 'message');
	}
}

?>

<form action="<?php echo JRoute::_('index.php?option=com_eztestimonial');?>" method="post" name="adminForm" id="adminForm">
<div id="editcell">
    <table class="adminlist">
    <thead>
        <tr>
            <th width="10">
                 <?php echo JHtml::_('grid.sort', JText::_('COM_TESTIMONIALS_BAK_ID'), 'id', $listDirn, $listOrder); ?> </th>
            <th width="20">
              <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" /> </th>
            <th>
                <?php echo JHtml::_('grid.sort', JText::_('COM_TESTIMONIALS_BAK_NAME'), 'fullName', $listDirn, $listOrder); ?> </th>
             <th>
				<?php echo JText::_('COM_TESTIMONIALS_BAK_SUMMARY'); ?> </th>
             <th>
                <?php echo JHtml::_('grid.sort', JText::_('COM_TESTIMONIALS_BAK_LOCATION'), 'location', $listDirn, $listOrder); ?> 
				</th>
             <th>
                <?php echo JHtml::_('grid.sort', JText::_('COM_TESTIMONIALS_BAK_RATING'), 'rating', $listDirn, $listOrder); ?> 
                </th>
              <th>
                 <?php echo JText::_('COM_TESTIMONIALS_BAK_IMAGE'); ?></th>
             <th>
                <?php echo JHtml::_('grid.sort', JText::_('COM_TESTIMONIALS_BAK_DATE'), 'added_date', $listDirn, $listOrder); ?>
                </th>
             <th>
                 <?php echo JHtml::_('grid.sort', JText::_('COM_TESTIMONIALS_BAK_STATUS'), 'approved', $listDirn, $listOrder); ?>            </th> 
        </tr>            
    </thead>
    <?php
    $k = 0;
    for ($i=0, $n=count( $this->items ); $i < $n; $i++)
    {
        $row =& $this->items[$i];
        $checked    = JHTML::_( 'grid.id', $i, $row->id ); 

		
		$ImgUrl = JRoute::_(JURI::root() .'images/'.$imageSubFolder.'/'); //user selected image folder
		$defaultImg = JRoute::_(JURI::root() .'components/com_eztestimonial/assets/images/default_user.jpg'); // default image url
		$ImageUrl=(strlen($row->image_name)>0)?$ImgUrl.$row->image_name:$defaultImg; //image path to large image
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td>
               <?php echo $row->id; ?></td>
            <td>
              <?php echo $checked; ?> </td>
            <td>
               <?php echo $row->fullName; ?> </td>
            <td>
                <a href ="<?php echo JRoute::_('index.php?option=com_eztestimonial&tmpl=component&view=edit&task=edit&cid='.$row->id); ?>" class="modal" rel="{handler: 'iframe', size: {x: 600, y: 400}, onClose: function() {window.location.reload()}}">
				<?php echo $row->message_summary ; ?> </a> </td>
            <td>
                <?php echo $row->location ; ?></td>
            <td>
                <?php echo $row->rating ; ?>/5</td>
            <td>
                <?php echo(strlen($row->image_name)>1)?'<a href="'.$ImageUrl.'" class="modal" >Yes</a>':'No'; ?> </td>
            <td>
                <?php echo $row->added_date ; ?></td>
            <td>
                <?php echo($row->approved ==1)?'<span style="color:green">Published</span>':'<span style="color:red">Unpublished</span>'; ?></td>
        </tr>
        
          
        
        <?php
        $k = 1 - $k;
    }
    ?><tfoot><tr><td colspan="9" align="center"><div class="pagination"><?php echo $this->pagination->getListFooter();?></div></td></tr></tfoot>
    </table>
</div>
 
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
 
</form>
<?php echo AdminTestimonController::GetCredit(); ?>
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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.file');

if( ! JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php')) {
    echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;Please install the joomlamailer component!<br /><br />';
} else {

    $cid = JRequest::getVar('cid', 0);
    $doc = & JFactory::getDocument();
    $file = JURI::base().'components/com_joomailermailchimpintegration/assets/css/campaigns.css';
    $doc->addStyleSheet($file);
    $lang =& JFactory::getLanguage();
    $lang->load('com_joomailermailchimpintegration', JPATH_ADMINISTRATOR);

    if( ! class_exists( 'joomlamailerMCAPI' )) {
	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'libraries'.DS.'MCAPI.class.php');
    }
    require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomailermailchimpintegration'.DS.'models'.DS.'campaigns.php');
    $model = new joomailermailchimpintegrationsModelCampaigns;
    
    $AIM = false;
    $clientDetails = $model->getClientDetails();
    if( is_array($clientDetails['modules']) ){
	foreach($clientDetails['modules'] as $mod){
	    if($mod['name'] == 'AIM Reports'){
		    $AIM = true;
		    break;
	    }
	}
    }

    $campaigns = $model->getCampaigns( array('status'=>'sent'), 0, 1000 );

    if(!isset($campaigns[0])){
	echo '<div style="margin: 10px;">'.JText::_('JM_NO_CAMPAIGN_SENT').'</div>';
    } else {
	$stats = $model->getCampaignStats($campaigns[$cid]['id']);

	$successful = $campaigns[$cid]['emails_sent'] - $stats['soft_bounces'] - $stats['hard_bounces'];
	// process opens and open percentage
	$opens  =  $stats['unique_opens'];
	$opens_percent = ($successful) ? $opens / ( $successful * 0.01 ) : 0;
	$opens_percent = round($opens_percent,2);
	// process bounces and bounce percentage
	$bounced  = $stats['hard_bounces'] + $stats['soft_bounces'];
	$bounced_percent =($campaigns[$cid]['emails_sent']) ? $bounced / ( $campaigns[$cid]['emails_sent'] * 0.01 ) : 0;
	$bounced_percent = round($bounced_percent,2);
	// process not opened and not opened percentage
	$not_opened         =  $campaigns[$cid]['emails_sent'] -  $opens -  $bounced;
	$not_opened_percent =  ($campaigns[$cid]['emails_sent']) ? $not_opened / ( $campaigns[$cid]['emails_sent'] * 0.01 ) : 0;
	$not_opened_percent = round($not_opened_percent,2);
	// process clicks and click percentage
	$clicks = $stats['users_who_clicked'];
	$unique_opens = $stats['unique_opens'];
	if($unique_opens != 0){
		$clicks_per_open = round($stats['clicks'] / $unique_opens, 2);
	} else {
		$clicks_per_open = 0;
	}
	if ( $clicks != 0 ) {
		$clicks_percent = $clicks / ( $unique_opens * 0.01 );
		$clicks_percent = round($clicks_percent,2);
	} else {
		$clicks_percent  = 0;
	}
	// process unsubscribes and unsubscribe percentage
	$unsubs = $stats['unsubscribes'];
	if ( $unsubs != 0 ) {
		$unsubs_percent = $unsubs / ( $campaigns[$cid]['emails_sent'] * 0.01 );
		$unsubs_percent = round($unsubs_percent,2);
	} else {
		$unsubs_percent = 0;
	}

    //    echo '<div style="height: 360px; float:left;">';
    //    echo '<img src="http://chart.apis.google.com/chart?cht=p&chd=t:'.$opens_percent.','.$bounced_percent.','.$not_opened_percent.'&chs=260x360&chdl='.JText::_('Opened').' ('.$opens_percent.'%)|'.JText::_('Bounced').' ('.$bounced_percent.'%)|'.JText::_('Not opened').' ('.$not_opened_percent.'%)&chco=93ccea,5c8ea9,275886" />';
    //    echo '</div>';

    ?>
    <style>
    #mcStatsContent h3 {
    padding: 5px 0;
    margin:0;
    background: transparent;
    }
    #mcStatsSelectContainer {
    padding: 8px 10px;
    border-bottom: 1px solid #CCCCCC;
    }
    #mcStatsSelect {
 
    float:left;
    padding: 5px 0 0 0;
    }
    #mcStatsSelectContainer a.JMbutton {
    display: inline-block;
    height: 22px;
    background-color: rgb(207,104,0);
    background-image: -webkit-gradient( linear,
					left bottom,
					left top,
					color-stop(0.47, rgb(207,104,0)),
					color-stop(0.87, rgb(251,151,0))
				    );
    background-image: -moz-linear-gradient( center bottom,
					    rgb(207,104,0) 47%,
					    rgb(251,151,0) 87%
					);
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    margin: 0 0 0 5px;
    padding: 6px 10px 0;
    outline:none;
    float:right;
    font-size: 13px;
    color: #ffffff;
    text-shadow: 0 1px 2px #616161;
    }
    #mcStatsSelectContainer a.JMbutton:hover {
	color: #F4F4F4;
	text-decoration: none;
	background-color: rgb(207,104,0);
	background-image: -webkit-gradient( linear,
					    left bottom,
					    left top,
					    color-stop(0.47, rgb(186,93,0)),
					    color-stop(0.87, rgb(251,151,0))
					);
	background-image: -moz-linear-gradient( center bottom,
						rgb(186,93,0) 47%,
						rgb(251,151,0) 87%
					    );

    }
    #mcStatsDetails {
    background:white;
    }
    #detail-stats {
    width: 250px;
    float:left;
    height:auto;
    }
    #complaints {
    border: 1px solid #ebebeb;
    border-width: 0 1px 0 0;
    }
    .stats-list {
    margin-top: 4px;
    }
    .stats-list li {
    padding: 4px 0;
    }
    .stats-list .name, .stats-list .value {
    top: 1px;
    }

    #ap-main #mcStatsSelect {
    padding: 3px 0 0 0;
    }
    #ap-main .stats-list li {
    padding: 1px 0;
    }

    </style>
    <div id="mcStatsContent">
	<div id="mcStatsSelectContainer">
	    <form action="index.php" method="post" name="mcStatsSelect" id="mcStatsSelect">
	    <div>
		<select name="cid" onchange="document.mcStatsSelect.submit();">
		    <?php $x = 0; foreach($campaigns as $c){ ?>
		    <option value="<?php echo $x;?>" <?php if($cid==$x) echo 'selected="selected"';?>><?php echo $c['title'];?></option>
		    <?php $x++; } ?>
		</select>
	    </div>
	    </form>
	    <a class="JMbutton" href="index.php?option=com_joomailermailchimpintegration&view=create"><?php echo JText::_( 'JM_CREATE_CAMPAIGN' ); ?></a>
	    <a class="JMbutton" href="index.php?option=com_joomailermailchimpintegration&view=campaigns"><?php echo JText::_( 'JM_REPORTS_' ); ?></a>
	    <div style="clear:both;"></div>
	</div>

    <div id="mcStatsDetails">
    <h3 style="text-align:center;"><?php echo $campaigns[$cid]['title'].' ('.$campaigns[$cid]['subject'].')';?></h3>
    <?php
	echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
	echo '<script type="text/javascript">';

	echo "google.load('visualization', '1', {'packages':['corechart']});";
	echo "google.setOnLoadCallback(drawChart);";
	echo "function drawChart() {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Task');
		data.addColumn('number', 'Hours per Day');
		data.addRows(5);
		data.setValue(0, 0, 'opens');
		data.setValue(0, 1, ".$opens.");
		data.setValue(1, 0, 'bounced');
		data.setValue(1, 1, ".$bounced.");
		data.setValue(2, 0, 'not opened');
		data.setValue(2, 1, ".$not_opened.");

		var chart = new google.visualization.PieChart(document.getElementById('pieChart'));
		chart.draw(data, {width: 250,
				    height: 300,
				    is3D: false,
				    title: 'stats',
				    colors:['#93ccea','#5c8ea9','#275886'],
				    titleTextStyle: {color: '#c0c0c0'},
				    backgroundColor: {stroke:null, fill:null, strokeSize:0},
				    chartArea:{left:20,top:10,width:'90%',height:'75%'},
				    legend: 'bottom'
		});
	    }";

	echo '</script>';
	echo '<div id="pieChart" style="display:block;width:255px;height:300px;float:left;"></div>';

	echo  '<div id="detail-stats">'
	    . '<div id="complaints">'
	    . '<span id="complaint-count">'
	    .$stats['abuse_reports'].' '.JText::_( 'JM_COMPLAINTS' )
	    . '</span> '
	    . '<br /><a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=abuse&cid='.$campaigns[$cid]['id'].'">'
	    . JText::_('JM_VIEW_COMPLAINTS').'</a>'
	    . '</div>'
	    . '<ul class="stats-list">'
	    . '<li>'
	    . '<span class="value">'.$campaigns[$cid]['emails_sent'].'</span>';
	    if($AIM){
		echo '<span class="name"><a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=recipients&cid='.$campaigns[$cid]['id'].'">'.JText::_( 'JM_TOTAL_RECIPIENTS' ).'</a></span>';
	    } else {
		echo '<span class="name">'.JText::_( 'JM_TOTAL_RECIPIENTS' ).'</span>';
	    }

	echo  '</li>'
	    . '<li>'
	    . '<span class="value">'.$successful.'</span>'
	    . '<span class="name">'.JText::_( 'JM_SUCCESSFUL_DELIVERIES' ).'</span>'
	    . '</li>'
	    . '<li>'
	    . '<span class="value">'.$stats['forwards'].'</span>'
	    . '<span class="name">'.JText::_( 'JM_TIMES_FORWARDED' ).'</span>'
	    . '</li>'
	    . '<li>'
	    . '<span class="value">'.$stats['forwards_opens'].'</span>'
	    . '<span class="name">'.JText::_( 'JM_FORWARDED_OPENS' ).'</span>'
	    . '</li>'
	    . '<li>'
	    . '<span class="value"> <span class="percent">('.$opens_percent.'%)</span> '.$opens.' </span>'
	    . '<span class="name"><a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=opened&cid='.$campaigns[$cid]['id'].'">'.JText::_( 'JM_RECIPIENTS_WHO_OPENED' ).'</a></span>'
	    . '</li>'
	    . '<li>'
	    . '<span class="value">'.$stats['opens'].'</span>'
	    . '<span class="name">'.JText::_( 'JM_TOTAL_TIMES_OPENED' ).'</span>'
	    . '</li>'
	    . '<li>'
	    . '<span class="value">'.substr($stats['last_open'], 0, -3).'</span>'
	    . '<span class="name">'.JText::_( 'JM_LAST_OPEN_DATE' ).'</span>'
	    . '</li>'
	    . '<li>'
	    . '<span class="value"> <span class="percent">('.$clicks_percent.'%)</span> '.$stats['users_who_clicked'].'</span>';
	    if($AIM){
		echo '<span class="name"<a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=clicked&cid='.$campaigns[$cid]['id'].'">'.JText::_( 'JM_RECIPIENTS_WHO_CLICKED' ).'</a></span>';
	    } else {
		echo '<span class="name">'.JText::_( 'JM_RECIPIENTS_WHO_CLICKED' ).'</span>';
	    }
	echo  '</li>'
	    . '<li>'
	    . '<span class="value"> <span class="percent">'.$clicks_per_open.'</span> </span>'
	    . '<span class="name">'.JText::_( 'JM_CLICKS_UNIQUE_OPEN' ).'</span>'
	    . '</li>'
	    . '<li>'
	    . '<span class="value">'.$stats['clicks'].'</span>'
	    . '<span class="name"><a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=clickedlinks&cid='.$campaigns[$cid]['id'].'">'.JText::_( 'JM_TOTAL_CLICKS' ).'</a></span>'
	    . '</li>'
	    . '<li>'
	    . '<span class="value">'.$unsubs.'</span>'
	    . '<span class="name"><a href="index.php?option=com_joomailermailchimpintegration&view=campaigns&layout=unsubscribes&cid='.$campaigns[$cid]['id'].'">'.JText::_( 'JM_TOTAL_UNSUBSCRIBES' ).'</a></span>'
	    . '</li>'
	    . '</ul>'
	    . '</div>';

	echo '<div style="clear:both;"></div>';
    echo '</div>';
    echo '</div>';



    }
}
?>
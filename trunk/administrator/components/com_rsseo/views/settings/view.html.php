<?php
/**
* @version 1.0.0
* @package RSSeo! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsseoViewsettings extends JView
{
	function display($tpl = null)
	{
		global $mainframe;
		
		JSubMenuHelper::addEntry(JText::_('RSSEO_OVERVIEW'), 'index.php?option=com_rsseo');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_COMPETITORS'), 'index.php?option=com_rsseo&task=listcompetitors'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_PAGES'), 'index.php?option=com_rsseo&task=listpages'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_REDIRECTS'), 'index.php?option=com_rsseo&task=listredirects'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SITEMAP'), 'index.php?option=com_rsseo&task=sitemap'); 
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_CRAWLER'), 'index.php?option=com_rsseo&task=crawler');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_KEYWORDS'), 'index.php?option=com_rsseo&task=listkeywords');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_BACKUPRESTORE'), 'index.php?option=com_rsseo&task=backuprestore');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_ANALYTICS'), 'index.php?option=com_rsseo&task=analytics');
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_SETTINGS'), 'index.php?option=com_rsseo&task=editsettings',true);
		JSubMenuHelper::addEntry(JText::_('RSSEO_MENU_UPDATE'), 'index.php?option=com_rsseo&task=update'); 
		
		JHTML::_('behavior.tooltip');
		$task=JRequest::getVar('task','','request');
		$document 	= & JFactory::getDocument();
		switch($task)
		{
			case 'editsettings':
			{
				JToolBarHelper::title(JText::_('RSSEO_SETTINGS'),'rsseo');
				JToolBarHelper::apply();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				JToolBarHelper::custom('rsseo','preview.png','preview_f2.png',JText::_('RSSEO_PRODUCT'),false);
				
				$ConfigList=$this->get('data');
				
				
				$crawlerLevel[] = JHTML::_('select.option', '-1', JText::_( 'RSSEO_SETTINGS_CRAWLER_UNLIMITED' ) );
				$crawlerLevel[] = JHTML::_('select.option', '1', 1 );
				$crawlerLevel[] = JHTML::_('select.option', '2', 2 );
				$crawlerLevel[] = JHTML::_('select.option', '3', 3 );
				$crawlerLevel[] = JHTML::_('select.option', '4', 4 );
				$crawlerLevel[] = JHTML::_('select.option', '5', 5 );
				$crawlerLevel[] = JHTML::_('select.option', '6', 6 );
				$crawlerLevel[] = JHTML::_('select.option', '7', 7 );
				$crawlerLevel[] = JHTML::_('select.option', '8', 8 );
				$crawlerLevel[] = JHTML::_('select.option', '9', 9 );
				$crawlerLevel[] = JHTML::_('select.option', '10', 10 );
				$lists['crawler.level'] = JHTML::_('select.genericlist', $crawlerLevel, 'rsseoConfig[crawler.level]', 'size="1" class="inputbox"', 'value', 'text', $ConfigList['crawler.level'] );
				
				$headings[] = JHTML::_('select.option', '0', 'Off' );
				$headings[] = JHTML::_('select.option', 'h1', 'h1' );
				$headings[] = JHTML::_('select.option', 'h2', 'h2' );
				$headings[] = JHTML::_('select.option', 'h3', 'h3' );
				$headings[] = JHTML::_('select.option', 'h4', 'h4' );
				$headings[] = JHTML::_('select.option', 'h5', 'h5' );
				$headings[] = JHTML::_('select.option', 'h6', 'h6' );
				
				$lists['heading1'] = JHTML::_('select.genericlist', $headings, 'rsseoConfig[component.heading]', 'size="1" class="inputbox"', 'value', 'text', $ConfigList['component.heading'] );
				$lists['heading2'] = JHTML::_('select.genericlist', $headings, 'rsseoConfig[content.heading]', 'size="1" class="inputbox"', 'value', 'text', $ConfigList['content.heading'] );
				
				$googleDomain[] = JHTML::_('select.option', 'google.com', 'International (google.com)' );
				$googleDomain[] = JHTML::_('select.option', 'google.cat', 'Catalan Linguistic and Cultural Community (google.cat)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.af', 'Afghanistan (google.com.af)' );
				$googleDomain[] = JHTML::_('select.option', 'google.dz', 'Algeria (google.dz)' );
				$googleDomain[] = JHTML::_('select.option', 'google.as', 'American Samoa (google.as)' );
				$googleDomain[] = JHTML::_('select.option', 'google.it.ao', 'Angola (google.it.ao)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.ag', 'Antigua and Barbuda (google.com.ag)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.ar', 'Argentina (google.com.ar)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.am', 'Armenia (google.am)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.au', 'Australia (google.com.au)' );
				$googleDomain[] = JHTML::_('select.option', 'google.at', 'Austria (google.at)' );
				$googleDomain[] = JHTML::_('select.option', 'google.az', 'Azerbaijan (google.az)' );
				$googleDomain[] = JHTML::_('select.option', 'google.bs', 'Bahamas (google.bs)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.bh', 'Bahrain (google.com.bh)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.bd', 'Bangladesh (google.com.bd)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.by', 'Belarus (google.com.by)' );
				$googleDomain[] = JHTML::_('select.option', 'google.be', 'Belgium (google.be)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.bz', 'Belize (google.com.bz)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.bo', 'Bolivia (google.com.bo)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ba', 'Bosnia and Herzegovina (google.ba)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.bw', 'Botswana (google.co.bw)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.br', 'Brazil (google.com.br)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.bn', 'Brunei (google.com.bn)' );
				$googleDomain[] = JHTML::_('select.option', 'google.bg', 'Bulgaria (google.bg)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.kh', 'Cambodia (google.com.kh)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ca', 'Canada (google.ca)' );
				$googleDomain[] = JHTML::_('select.option', 'google.cl', 'Chile (google.cl)' );
				$googleDomain[] = JHTML::_('select.option', 'google.cn', 'China (google.cn)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.co', 'Colombia (google.com.co)' );
				$googleDomain[] = JHTML::_('select.option', 'google.cd', 'Congo, Democratic Republic of the (google.cd)' );
				$googleDomain[] = JHTML::_('select.option', 'google.cg', 'Congo (google.cg)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.cr', 'Costa Rica (google.co.cr)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ci', 'Côte d\'Ivoire (google.ci)' );
				$googleDomain[] = JHTML::_('select.option', 'google.hr', 'Croatia (google.hr)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.cu', 'Cuba (google.com.cu)' );
				$googleDomain[] = JHTML::_('select.option', 'google.cz', 'Czech Republic (google.cz)' );
				$googleDomain[] = JHTML::_('select.option', 'google.dk', 'Denmark (google.dk)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.do', 'Dominican Republic (google.com.do)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.ec', 'Ecuador (google.com.ec)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.eg', 'Egypt (google.com.eg)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.sv', 'El Salvador (google.com.sv)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ee', 'Estonia (google.ee)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.et', 'Ethiopia (google.com.et)' );
				$googleDomain[] = JHTML::_('select.option', 'google.fj', 'Fiji (google.com.fj)' );
				$googleDomain[] = JHTML::_('select.option', 'google.fi', 'Finland (google.fi)' );
				$googleDomain[] = JHTML::_('select.option', 'google.fr', 'France (google.fr)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ge', 'Georgia (google.ge)' );
				$googleDomain[] = JHTML::_('select.option', 'google.de', 'Germany (google.de)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.gh', 'Ghana (google.com.gh)' );
				$googleDomain[] = JHTML::_('select.option', 'google.gr', 'Greece (google.gr)' );
				$googleDomain[] = JHTML::_('select.option', 'google.gp', 'Guadeloupe (google.gp)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.gt', 'Guatemala (google.com.gt)' );
				$googleDomain[] = JHTML::_('select.option', 'google.gy', 'Guyana (google.gy)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ht', 'Haiti (google.ht)' );
				$googleDomain[] = JHTML::_('select.option', 'google.hn', 'Honduras (google.hn)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.hk', 'Hong Kong (google.com.hk)' );
				$googleDomain[] = JHTML::_('select.option', 'google.hu', 'Hungary (google.hu)' );
				$googleDomain[] = JHTML::_('select.option', 'google.is', 'Iceland (google.is)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.in', 'India (google.co.in)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.id', 'Indonesia (google.co.id)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ie', 'Ireland (google.ie)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.il', 'Israel (google.co.il)' );
				$googleDomain[] = JHTML::_('select.option', 'google.it', 'Italy (google.it)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.jm', 'Jamaica (google.com.jm)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.jp', 'Japan (google.co.jp)' );
				$googleDomain[] = JHTML::_('select.option', 'google.jo', 'Jordan (google.jo)' );
				$googleDomain[] = JHTML::_('select.option', 'google.kz', 'Kazakhstan (google.kz)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.ke', 'Kenya (google.co.ke)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.kw', 'Kuwait (google.com.kw)' );
				$googleDomain[] = JHTML::_('select.option', 'google.la', 'Laos (google.la)' );
				$googleDomain[] = JHTML::_('select.option', 'google.lv', 'Latvia (google.lv)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.lb', 'Lebanon (google.com.lb)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.ls', 'Lesotho (google.co.ls)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.ly', 'Libya (google.com.ly)' );
				$googleDomain[] = JHTML::_('select.option', 'google.lt', 'Lithuania (google.lt)' );
				$googleDomain[] = JHTML::_('select.option', 'google.lu', 'Luxembourg (google.lu)' );
				$googleDomain[] = JHTML::_('select.option', 'google.mg', 'Madagascar (google.mg)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.my', 'Malaysia (google.com.my)' );
				$googleDomain[] = JHTML::_('select.option', 'google.mv', 'Maldives (google.mv)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.mt', 'Malta (google.com.mt)' );
				$googleDomain[] = JHTML::_('select.option', 'google.mu', 'Mauritius (google.mu)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.mx', 'Mexico (google.com.mx)' );
				$googleDomain[] = JHTML::_('select.option', 'google.md', 'Moldova (google.md)' );
				$googleDomain[] = JHTML::_('select.option', 'google.mn', 'Mongolia (google.mn)' );
				$googleDomain[] = JHTML::_('select.option', 'google.me', 'Montenegro (google.me)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.ma', 'Morocco (google.co.ma)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.mz', 'Mozambique (google.co.mz)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.na', 'Namibia (google.com.na)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.np', 'Nepal (google.com.np)' );
				$googleDomain[] = JHTML::_('select.option', 'google.nl', 'Netherlands (google.nl)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.nz', 'New Zealand (google.co.nz)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.ni', 'Nicaragua (google.com.ni)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.ng', 'Nigeria (google.com.ng)' );
				$googleDomain[] = JHTML::_('select.option', 'google.no', 'Norway (google.no)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.om', 'Oman (google.com.om)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.pk', 'Pakistan (google.com.pk)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.pa', 'Panama (google.com.pa)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.py', 'Paraguay (google.com.py)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.pe', 'Peru (google.com.pe)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.ph', 'Philippines (google.com.ph)' );
				$googleDomain[] = JHTML::_('select.option', 'google.pl', 'Poland (google.pl)' );
				$googleDomain[] = JHTML::_('select.option', 'google.pt', 'Portugal (google.pt)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.pr', 'Puerto Rico (google.com.pr)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.qa', 'Qatar (google.com.qa)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ro', 'Romania (google.ro)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ru', 'Russia (google.ru)' );
				$googleDomain[] = JHTML::_('select.option', 'google.rw', 'Rwanda (google.rw)' );
				$googleDomain[] = JHTML::_('select.option', 'google.sm', 'San Marino (google.sm)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.sa', 'Saudi Arabia (google.com.sa)' );
				$googleDomain[] = JHTML::_('select.option', 'google.sn', 'Senegal (google.sn)' );
				$googleDomain[] = JHTML::_('select.option', 'google.rs', 'Serbia (google.rs)' );
				$googleDomain[] = JHTML::_('select.option', 'google.sc', 'Seychelles (google.sc)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.sg', 'Singapore (google.com.sg)' );
				$googleDomain[] = JHTML::_('select.option', 'google.sk', 'Slovakia (google.sk)' );
				$googleDomain[] = JHTML::_('select.option', 'google.si', 'Slovenia (google.si)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.za', 'South Africa (google.co.za)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.kr', 'South Korea (google.co.kr)' );
				$googleDomain[] = JHTML::_('select.option', 'google.es', 'Spain (google.es)' );
				$googleDomain[] = JHTML::_('select.option', 'google.lk', 'Sri Lanka (google.lk)' );
				$googleDomain[] = JHTML::_('select.option', 'google.se', 'Sweden (google.se)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ch', 'Switzerland (google.ch)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.tw', 'Taiwan (google.com.tw)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.tz', 'Tanzania (google.co.tz)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.th', 'Thailand (google.co.th)' );
				$googleDomain[] = JHTML::_('select.option', 'google.tt', 'Trinidad and Tobago (google.tt)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.tr', 'Turkey (google.com.tr)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.ug', 'Uganda (google.co.ug)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.ua', 'Ukraine (google.com.ua)' );
				$googleDomain[] = JHTML::_('select.option', 'google.ae', 'United Arab Emirates (google.ae)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.uk', 'United Kingdom (google.co.uk)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.uy', 'Uruguay (google.com.uy)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.ve', 'Venezuela (google.co.ve)' );
				$googleDomain[] = JHTML::_('select.option', 'google.com.vn', 'Vietnam (google.com.vn)' );
				$googleDomain[] = JHTML::_('select.option', 'google.co.vi', 'Virgin Islands, U.S. (google.co.vi)' );
				
				$lists['googleDomain'] = JHTML::_('select.genericlist', $googleDomain, 'rsseoConfig[google.domain]', 'size="1" class="inputbox"', 'value', 'text', $ConfigList['google.domain'] );
				
				
				$this->assignRef('lists',$lists);
				$this->assignRef('data',$ConfigList);

			} break;
			
		}
		
		
		parent::display($tpl);
	}
}
<?php
/**
* @version 1.0.0
* @copyright (C) 2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class RSPackage extends JObject
{
	var $_options = array();
	var $_db = null;
	var $_folder = null;
	var $_extractfolder = null;
	
	function __construct($options=array())
	{
		$this->_db =& JFactory::getDBO();
		$config = JFactory::getConfig();		
		
		$this->_options = $options;
		
		$this->setFile();
		
		$tmp_path = $config->getValue('config.tmp_path');
		$tmp_folder = 'rsbackup_'.$this->getMD5();
		$extract_tmp_folder = 'rsbackup_'.$this->getMD5File();
		$this->_folder = JPath::clean($tmp_path.DS.$tmp_folder);
		$this->_extractfolder = JPath::clean($tmp_path.DS.$extract_tmp_folder);
	}
	
	
	function setFile()
	{
		$file = JRequest::get('files');
		if (!empty($file['rspackage']) && $file['rspackage']['error'] == 0)
			$this->_options['file'] = $file['rspackage'];
	}
	
	function getMD5()
	{
		$string = '';
		$queries = $this->getQueries();
		foreach ($queries as $query)
			$string .= $query['query'].';';
		
		return md5($string);
	}
	
	function getMD5File()
	{
		if (isset($this->_options['file']))
		return md5($this->_options['file']['name']);
	}
	
	function getQueries()
	{
		if (isset($this->_options['queries']))
			return $this->_options['queries'];
		
		return array();
	}
	
	function getLimit()
	{
		$default = 300;
		
		if (isset($this->_options['limit']))
			return (int) $this->_options['limit'] <= 0 ? $default : $this->_options['limit'];
			
		return $default;
	}
	
	function getFolder()
	{
		return $this->_folder;
	}
	
	function getExtractFolder()
	{
		return $this->_extractfolder;
	}
	
	function backup()
	{
		if ($this->_isRequest())
		{
			$this->_parseRequest();
			return;
		}
		if ($this->_isDownload())
		{
			$this->_startDownload();
			return;
		}
		
		$folder = $this->getFolder();
		if (JFolder::exists($folder))
		{
			JFile::delete(JFolder::files($folder, '.xml$', 1, true));
			JFile::delete(JFolder::files($folder, '.tar.gz$', 1, true));
		}
		else
			JFolder::create($folder);
		
		$document = JFactory::getDocument();
		$script = 'var rspackage_queries = new Array();'."\n";
		
		$uri = JFactory::getURI();
		$url = $uri->toString();
		
		$limit = $this->getLimit();
		$queries = $this->getQueries();
		foreach ($queries as $query)
		{
			$this->_db->setQuery($query['query']);
			$results = $this->_db->getNumRows($this->_db->query());
			$pages = ceil($results / $limit);
			
			for ($i=0; $i<$pages; $i++)
			{
				$options = array();
				
				$page = $i*$limit;
				$query['limit_query'] = $query['query']." LIMIT {$page}, {$limit}";
				
				$script .= 'rspackage_queries.push("'.$this->encode($query).'");'."\n";
			}
		}
		
		$script .= '
		var rspackage_requests = new Array();
		function rspackage_backup()
		{
			for (var i=0; i<rspackage_queries.length; i++)
			{
				var rspackage_query = rspackage_queries[i];';
		$script .= rsseoHelper::is16() ? 'var rspackage_request = new Request({url:"'.$url.'",method: "post", data: {query: rspackage_query, ajax: 1, type: "backup"}, onComplete: rspackage_next});' : 'var rspackage_request = new Ajax("'.$url.'", {method: "post", data: {query: rspackage_query, ajax: 1, type: "backup"}, onComplete: rspackage_next});';
		
		$script .= 'rspackage_requests.push(rspackage_request);
			}
		}
		
		function rspackage_next(response)
		{
			var rspackage_progress_bar = document.getElementById("rspackage_progress_bar");
			
			if (rspackage_requests.length < 1)
			{
				if (rspackage_progress_bar != undefined)
					rspackage_progress_bar.innerHTML = rspackage_progress_bar.style.width = "100%";
				
				rspackage_pack();
				
				return;
			}

			if (rspackage_progress_bar != undefined)
			{
				var rspackage_progress = parseInt(rspackage_progress_bar.innerHTML) + rspackage_progress_bar_unit;
				rspackage_progress_bar.innerHTML = rspackage_progress_bar.style.width = rspackage_progress + "%";
			}
			
			var rspackage_request = rspackage_requests[rspackage_requests.length - 1];
			rspackage_requests.pop();';
		$script .= rsseoHelper::is16() ? 'rspackage_request.send();' : 'rspackage_request.request();';
		$script .= '}
		
		function rspackage_pack()
		{';
		
		$script .= rsseoHelper::is16() ? 'var rspackage_request = new Request({url:"'.$url.'",method: "post", data: {ajax: 1, pack: 1}, onComplete: rspackage_download});' : 'var rspackage_request = new Ajax("'.$url.'", {method: "post", data: {ajax: 1, pack: 1}, onComplete: rspackage_download});';
		$script .= rsseoHelper::is16() ? 'rspackage_request.send();' : 'rspackage_request.request();';
		$script .= 
		'}
		
		function rspackage_download()
		{
			var form = document.createElement("form");
			form.setAttribute("action", "'.$url.'");
			form.setAttribute("method", "post");
			
			var input = document.createElement("input");
			input.setAttribute("type", "hidden");
			input.setAttribute("name", "download");
			input.setAttribute("value", "1");
			
			form.appendChild(input);
			
			var body = document.body.appendChild(form);
			form.submit();
		}
		
		rspackage_backup();
		var rspackage_progress_bar_unit = Math.floor(100 / rspackage_requests.length);
		window.addEvent("domready", rspackage_next);';
		
		$document->addScriptDeclaration($script);
	}
	
	function restore()
	{
		$app =& JFactory::getApplication();
		
		if ($this->_isRequest())
		{
			$this->_parseRequest();
			return;
		}
		
		if(!isset($this->_options['file']) || $this->_options['file']['error'] != 0) return;
		
		$db			= JFactory::getDBO();
		$document	= JFactory::getDocument();
		
		if(isset($this->_options['file']) && $this->_options['file']['error'] == 0)
		{
			$extract = $this->_extract();
			if($extract == false) $app->redirect('index.php?option=com_rsseo&task=restore',JText::_('RSSEO_RESTORE_ERROR'),'error');
		}
		
		$script = 'var rspackage_files = new Array();';
		
		$uri = JFactory::getURI();
		$url = $uri->toString();
		
		$files = $this->_getFiles();
		
		if(!empty($files))
		foreach ($files as $file)
			$script .= 'rspackage_files.push("'.urlencode($db->getEscaped($file)).'");';
		
		
		$script .= '
		var rspackage_requests = new Array();
		function rspackage_restore()
		{
			for (var i=0; i<rspackage_files.length; i++)
			{
				var rspackage_file = rspackage_files[i];';
		$script .= rsseoHelper::is16() ? 'var rspackage_request = new Request({url:"'.$url.'" ,method: "post", data: {file: rspackage_file, ajax: 1, type: "restore"}, onComplete: rspackage_next});' : 'var rspackage_request = new Ajax("'.$url.'", {method: "post", data: {file: rspackage_file, ajax: 1, type: "restore"}, onComplete: rspackage_next});';
		$script .= 'rspackage_requests.push(rspackage_request);
			}
		';	
		$script .= rsseoHelper::is16() ? 'var clear = new Request({url:"'.$url.'" ,method: "post", data: {ajax: 1, type: "clear"}, onComplete: rspackage_next});' : 'var clear = new Ajax("'.$url.'", {method: "post", data: {ajax: 1, type: "clear"}, onComplete: rspackage_next});';
		$script .= 'rspackage_requests.push(clear);
		}
		
		function rspackage_next(response)
		{
			var rspackage_progress_bar = document.getElementById("rspackage_progress_bar");
			
			if (rspackage_requests.length < 1)
			{
				if (rspackage_progress_bar != undefined)
					rspackage_progress_bar.innerHTML = rspackage_progress_bar.style.width = "100%";
				
				document.location = "'.$this->getRedirect().'";
					
				return;
			}

			if (rspackage_progress_bar != undefined)
			{
				var rspackage_progress = parseInt(rspackage_progress_bar.innerHTML) + rspackage_progress_bar_unit;
				rspackage_progress_bar.innerHTML = rspackage_progress_bar.style.width = rspackage_progress + "%";
			}
			
			var rspackage_request = rspackage_requests[rspackage_requests.length - 1];
			rspackage_requests.pop();';
		$script .= rsseoHelper::is16() ? 'rspackage_request.send();' : 'rspackage_request.request();';
		$script .= '}
		
		rspackage_restore();
		var rspackage_progress_bar_unit = Math.floor(100 / rspackage_requests.length);
		window.addEvent("domready", rspackage_next);';
		
		
		$document->addScriptDeclaration($script);
	}
	
	function getRedirect()
	{
		if (isset($this->_options['redirect']))
			return $this->_options['redirect'].'&delfolder='.base64_encode($this->getExtractFolder());
		
		$uri = JFactory::getURI();
		$url = $uri->toString();
		
		return $url;
	}
	
	function _extract()
	{
		$folder		= $this->getExtractFolder();		
		$file		= $folder.DS.$this->_options['file']['name'];
		
		//check to see if its a gzip file
		if (rsseoHelper::is16())
		{
			if (!preg_match('#zip#is',$this->_options['file']['type'])) return false;
		}
		else
		{
			if (!preg_match('#gzip#is', $this->_options['file']['type'])) return false;
		}
		
		//upload the file in the tmp folder
		if (! JFile::upload($this->_options['file']['tmp_name'],$file)) return false;
		
		//ectract the archive
		$extract = JArchive::extract($file,$folder);
		
		//delete the archive
		if($extract) JFile::delete($file);
		
		return $extract;
	}
	
	function _getFiles()
	{
		$xmls = array();
		
		if(isset($this->_options['file']) && $this->_options['file']['error'] == 0)
		{
			$folder = $this->getExtractFolder();
			$xmls = JFolder::files($folder, '.xml$', 1, true);
		}
		
		return $xmls;
	}
	
	
	function _isDownload()
	{
		$download = JRequest::getInt('download', 0, 'post');
		
		return $download;
	}
	
	function _startDownload()
	{
		if (rsseoHelper::is16())
			$file = $this->getFolder().DS.'package.zip';
		else
		$file = $this->getFolder().DS.'package.tar.gz';
		$fsize = filesize($file);
		header("Cache-Control: public, must-revalidate");
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		header("Pragma: no-cache");
		header("Expires: 0"); 
		header("Content-Description: File Transfer");
		header("Expires: Sat, 01 Jan 2000 01:00:00 GMT");
		header("Content-Type: application/octet-stream");
		header("Content-Length: ".(string) ($fsize));
		if (rsseoHelper::is16())
			header('Content-Disposition: attachment; filename="backup_package_'.date('Y_m_d').'.zip"');
		else
			header('Content-Disposition: attachment; filename="backup_package_'.date('Y_m_d').'.tar.gz"');
		header("Content-Transfer-Encoding: binary\n");
		@ob_end_flush();
		$this->readfile_chunked($file);
		exit();
	}
	
	function readfile_chunked($filename,$retbytes=true)
	{
		$chunksize = 1*(1024*1024); // how many bytes per chunk
		$buffer = '';
		$cnt =0;
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt; // return num. bytes delivered like readfile() does.
		}
		return $status;
	}
	
	function _isRequest()
	{
		$ajax = JRequest::getInt('ajax', 0, 'post');
		
		return $ajax;
	}
	
	function _parseRequest()
	{
		$folder = $this->getFolder();
		
		$type = JRequest::getVar('type', false, 'post');			
		if ($type)
		{
			$query = JRequest::getVar('query', false, 'post', 'BASE64');
			$start = JRequest::getInt('start',0);
			$num = count(JFolder::files($folder, '.xml$', 1, false));
			
			switch ($type)
			{
				case 'clear':
					$this->_db->setQuery("TRUNCATE TABLE `#__rsseo_pages`");
					$this->_db->query();
					$this->_db->setQuery("TRUNCATE TABLE `#__rsseo_redirects`");
					$this->_db->query();
				break;
				
				case 'backup':				
					$buffer  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
					$buffer .= '<query>'."\n";
					
					$query = $this->decode($query);
					if (preg_match('# (\#__.*?) #is', $query['query'], $matches))
						$table = trim($matches[1]);
					$buffer .= $this->addTag('table', $table);					
					
					$this->_db->setQuery($query['query']);
					$results = $this->_db->loadObjectList();
					
					$buffer .= '<rows>'."\n";
					foreach ($results as $result)
					{
						$buffer .= '<row>'."\n";
						foreach ($result as $key => $value)
						{
							if (isset($query['primary']) && $key == $query['primary'])
								continue;
								
							$buffer .= $this->addTag('column',$value,$key);
						}
						$buffer .= '</row>'."\n";
					}
					$buffer .= '</rows>';
					
					$buffer .= '</query>';
					JFile::write($folder.DS.'package'.$num.'.xml', $buffer);
				break;
				
				case 'restore':					
					jimport('joomla.utilities.simplexml');
					$file = urldecode(JRequest::getVar('file', false, 'post', ''));
					$xml = new JSimpleXML;
					$xml->loadFile($file);
					
					$root = $xml->document;
					$table = $root->getElementByPath('table')->data();
					$rows  = $root->getElementByPath('rows')->children();
					
					$table_fields = $name = $data = array();
					$fields = $this->_db->getTableFields($table);
					foreach($fields[$table] as $field=>$type)
						$table_fields[] = $this->_db->NameQuote($field);
					
					foreach ($rows as $row)
					{
						$sql = array();
						$columns = $row->children();
						
						foreach ($columns as $column)
						{
							$properties = $column->children();
							foreach($properties as $prop)
							{
								if ($prop->name() == 'name') $name[] = $this->_db->NameQuote($prop->data());
								if ($prop->name() == 'value') $data[] = $this->_db->Quote($prop->data());
							}							
						}

						foreach($name as $i=>$val)
						{
							if(!in_array($val,$table_fields))
							{
								unset($name[$i]);
								unset($data[$i]);
							}
						}
						if(!empty($name) && !empty($data))
						{
							$this->_db->setQuery("INSERT INTO `".$table."` (".implode(',',$name).") VALUES (".implode(',',$data)."); ");
							$this->_db->query();
							unset($name);unset($data);
						}
					}
					
				break;
			}
		}
		
		$pack = JRequest::getInt('pack', 0, 'post');
		if ($pack)
		{
			if (rsseoHelper::is16())
			{
				$adapter = JArchive::getAdapter('zip');
		
				$archivefiles = array();
				$xmlfiles = JFolder::files($folder, '.xml$', 1, true);
				foreach($xmlfiles as $xmlfile)
				{
					$data = JFile::read($xmlfile);
					$archivefiles[] = array('name' => JFile::getName($xmlfile), 'data' => $data);
				}
				$adapter->create($folder.DS.'package.zip', $archivefiles);
			}
			else JArchive::create($folder.DS.'package.tar', JFolder::files($folder, '.xml$', 1, true), 'gz', '', $folder, true, true);
		}
		
		die();
	}
	
	function encode($array)
	{
		$array['query'] = $array['limit_query'];
		unset($array['limit_query']);
		return base64_encode(serialize($array));
	}
	
	function decode($array)
	{
		return unserialize(base64_decode($array));
	}
	
	function displayProgressBar()
	{
		$document = JFactory::getDocument();
		
		$style  = '#rspackage_progress_wrapper { border: solid 1px #ccc; width: 100%; }'."\n";
		$style .= '#rspackage_progress_bar { background: green; color: #fff; padding: 3px; text-align: left; }';
		$document->addStyleDeclaration($style);
		
		$html = '<div id="rspackage_progress_wrapper"><div id="rspackage_progress_bar" style="width: 1%">0%</div></div>';
		return $html;
	}
	
	function addTag($tag, $value,$name=null)
	{
		if (is_null($name))
			return "\t".'<'.$tag.'>'.$this->xmlentities($value).'</'.$tag.'>'."\n";
		else return "\t".'<'.$tag.'>'."\n"."\t\t".'<name>'.$this->xmlentities($name).'</name>'."\n\t\t".'<value>'.$this->xmlentities($value).'</value>'."\n\t".'</'.$tag.'>'."\n";
	}
	
	function xmlentities($string, $quote_style=ENT_QUOTES)
	{
		return htmlspecialchars($string,$quote_style,'UTF-8');
	}
}
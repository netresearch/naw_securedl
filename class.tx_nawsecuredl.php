<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2007 Dietrich Heise (typo3-ext(at)naw.info)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * @author	Dietrich Heise <typo3-ext(at)naw.info>
 */
class tx_nawsecuredl {


	function parseFE(&$content,$pObj) {
		$content['pObj']->content = $this->parseContent($content['pObj']->content);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$i: ...
	 * @return	[type]		...
	 */
	function parseContent($i){
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['naw_securedl']);
		$rest = $i;

		//while (preg_match('/(<[aA]|<[iI][mM][gG])+?\s[^>]*([hH][rR][eE][fF]|[sS][rR][cC])=(\"??)([^\" >]*?)\\3[^>]*>/siU', $i,$match)) {  // suchendes secured Verzeichnis
	  	//while (preg_match('/(<[aA]|<[iI][mM][gG])+?.[^>]*([hH][rR][eE][fF]|[sS][rR][cC])=(\"??)([^\" >]*?)\\3[^>]*>/siU', $i,$match)) {  // suchendes secured Verzeichnis
		while (preg_match('/(?i)(<a|<img)+?.[^>]*(href|src)=(\"??)([^\" >]*?)\\3[^>]*>/siU', $i,$match)) {  // suchendes secured Verzeichnis

 				$cont = explode($match[0],$i,2);
	  			$vor = $cont[0];
	  	   		$tag = $match[0];
	  	   		if ($this->extConf['debug'] == '2' || $this->extConf['debug'] == '3') debug('tag:'.$tag);
	  	   		
	  	   		$rest = $cont[1];

				if ($this->extConf['debug'] == '1' || $this->extConf['debug'] == '3') debug(array('html-tag:'=>$tag));

		  		// investigate the HTML-Tag...
		  		//while (preg_match('/"((typo3temp|fileadmin|uploads).*?([pP][dD][fF]|[jJ][pP][eE]?[gG]|[gG][iI][fF]|[pP][nN][gG]))"/i', $tag,$match1)){
		  		if (preg_match('/"(?:'.$this->modifiyregex($this->extConf['domain']).')?(\/?(?:'.$this->modifiyregex($this->extConf['securedDirs']).')+?.*?(?:'.$this->modifyfiletypes($this->extConf['filetype']).'))"/i', $tag,$match1)){
		  			if ($this->extConf['debug'] == '2' || $this->extConf['debug'] == '3') debug('/"(?:'.$this->modifiyregex($this->extConf['domain']).')?(\/?(?:'.$this->modifiyregex($this->extConf['securedDirs']).')+?.*?(?:'.$this->modifyfiletypes($this->extConf['filetype']).'))"/i');
		  			if ($this->extConf['debug'] == '2' || $this->extConf['debug'] == '3') debug($match1);
		  			$replace = $this->makeSecure($match1[1]);
		  			$tagexp = explode ($match1[1], $tag , 2 );
		  			
		  			if ($this->extConf['debug'] == '2' || $this->extConf['debug'] == '3') debug($tagexp[0]);
		  			if ($this->extConf['debug'] == '2' || $this->extConf['debug'] == '3') debug($replace);
		  			if ($this->extConf['debug'] == '2' || $this->extConf['debug'] == '3') debug($tagexp[1]);
		  			
		  			$tag = $tagexp[0].$replace.$tagexp[1];
				}
				$result .= $vor.$tag;
		 		$i = $rest;
		}
		return $result.$rest;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$element: ...
	 * @return	[type]		...
	 */
	function makeSecure($element){
		//header("Content-type: text/css; charset=UTF-8");

		$key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
		if ($GLOBALS['TSFE']->fe_user->user['uid']){
			$this->feuser = $GLOBALS['TSFE']->fe_user->user['uid'];
		}else{
			$this->feuser = 0;
		}

		//$securefilename = 'secure.php';
		$securefilename = 'index.php?eID=tx_nawsecuredl';

		//$tmp = explode(PATH_site,t3lib_extMgm::extPath('naw_securedl'),2);
		//$pre_dir = dirname(t3lib_div::getIndpEnv('SCRIPT_NAME'));
		//$pre_dir = str_replace('\\','/',$pre_dir);
		//if ($pre_dir != '/') $pre_dir .= '/';
		//$path_and_file_to_secure = $pre_dir.$tmp[1].$securefilename;
		$path_and_file_to_secure = $securefilename;

		$cachetimeadd = $this->extConf['cachetimeadd'];

		if ($GLOBALS['TSFE']->page['cache_timeout'] == 0){
			$timeout = 86400 + time() + $cachetimeadd;
		}else{
			$timeout =  $GLOBALS['TSFE']->page['cache_timeout'] + time() + $cachetimeadd;
		}

		$data = $this->feuser.rawurldecode($element).$timeout.$key;
		$hash = md5($data);

		//return $path_and_file_to_secure.'?u='.$this->feuser.'&amp;file='.$element.'&amp;t='.$timeout.'&amp;hash='.$hash;
		return $path_and_file_to_secure.'&amp;u='.$this->feuser.'&amp;file='.$element.'&amp;t='.$timeout.'&amp;hash='.$hash;
	}

	function modifyfiletypes($string){
		$chars = preg_split('//',$string);
		foreach ($chars as $i){
			if (preg_match('/\w/',$i)){
				$out .= '['.strtoupper($i).strtolower($i).']';
			}else{
				$out .= $i;
			}
		}
		return $out;
	}

	function modifiyregex($string){
		$string = str_replace('\\','\\\\',$string);
		$string = str_replace(' ','\ ',$string);
		$string = str_replace('/','\/',$string);
		$string = str_replace('.','\.',$string);
		return $string;
	}

}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_securedl/class.tx_nawsecuredl.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_securedl/class.tx_nawsecuredl.php']);
}
?>
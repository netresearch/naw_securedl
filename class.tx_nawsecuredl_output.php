<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2007 Dietrich Heise (typo3-ext(at)naw.info)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
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


// *******************************
// Set error reporting
// *******************************
//error_reporting (E_ALL ^ E_NOTICE);


class tx_nawsecuredl_output {


	/**
	 * The init Function, to check the access rights
	 *
	 * @return void
	 */
	function init(){
		//require_once(PATH_t3lib.'class.t3lib_div.php');

		$this->u = intval(t3lib_div::_GP('u'));
		if (!$this->u){
			$this->u = 0;
		}

		$hash = t3lib_div::_GP('hash');
		$t = t3lib_div::_GP('t');
		$this->file = t3lib_div::_GP('file');
		$key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];

		$data = $this->u.$this->file.$t.$key;
		$checkhash = md5($data);

		if ($checkhash != $hash){
			exit ('Access denied!');
		}

		if (intval($t) < time()){
			exit ('Access denied!');
		}

		$this->feUserObj = tslib_eidtools::initFeUser();
		tslib_eidtools::connectDB();

		if ($this->u != '0') {
			$feuser = $this->feUserObj->user['uid'];
			if ($this->u != $feuser){
				exit ('Access denied!!');
			}
		}
	}

	/**
	 * Output the requested file
	 *
	 * @param data $file
	 */
	function fileOutput($file){

		$file = PATH_site.'/'.$this->file;

		if (file_exists($file)){

			// files bigger than 32MB are now 'application/octet-stream' by default (getimagesize memory_limit problem)
			if (filesize($file)<1024*1024*32){
				$bildinfos=getimagesize($file);
				$bildtypnr=$bildinfos[2];
			}

			$contenttype[1]='image/gif';
			$contenttype[2]='image/jpeg';
			$contenttype[3]='image/png';

			$contenttypedatei='';
			$contenttypedatei=$contenttype[$bildtypnr];

			if ($contenttypedatei=='') // d.h. wenn noch nicht gesetzt:
			/* try to get the filetype from the fileending */
			{
				$endigung=strtolower(strrchr($file,'.'));
				//alles ab dem letzten Punkt
				switch(strtolower($endigung)){

					case '.pps':
						$contenttypedatei='application/mspowerpoint';
						break;
						##### Microsoft Powerpoint Dateien
					case '.jpeg':
						$contenttypedatei='image/jpeg';
						break;
						##### JPEG-Dateien
					case '.jpg':
						$contenttypedatei='image/jpeg';
						break;
						##### JPEG-Dateien
					case '.jpe':
						$contenttypedatei='image/jpeg';
						break;
						##### JPEG-Dateien
					case '.mpeg':
						$contenttypedatei='video/mpeg';
						break;
						##### MPEG-Dateien
					case '.mpg':
						$contenttypedatei='video/mpeg';
						break;
						##### MPEG-Dateien
					case '.mpe':
						$contenttypedatei='video/mpeg';
						break;
						##### MPEG-Dateien
					case '.mov':
						$contenttypedatei='video/quicktime';
						break;
						##### Quicktime-Dateien
					case '.avi':
						$contenttypedatei='video/x-msvideo';
						break;
						##### Microsoft AVI-Dateien
					case '.pdf':
						$contenttypedatei='application/pdf';
						break;
					case '.svg':
						$contenttypedatei='image/svg+xml';
						break;
						### Flash Video Files
					case '.flv':
						$contenttypedatei='video/x-flv';
						break;
						### Shockwave / Flash
					case 'swf':
						$contenttypedatei='application/x-shockwave-flash';
						break;

					default:
						$contenttypedatei='application/octet-stream';
						break;
				}//end of switch Case structure
			}

			header('Content-Type: '.$contenttypedatei);
			header('Content-Disposition: inline; filename="'.basename($file).'"');
			readfile($file);
		}else{
			print "File does not exists!";
		}
	}

	/**
	 * Log the access of the file
	 *
	 * @return void
	 */
	function logDownload(){
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['naw_securedl']);
		if (!$extConf['log']){ // no logging
			return;
		}

		$insert_array = array (
			'tstamp' => time(),
			'filename' => $this->file,
			'userid' => intval($this->feUserObj->user['uid']),
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_nawsecuredl_counter',$insert_array);
	}

}

$securedl = new tx_nawsecuredl_output();
$securedl->init();
$securedl->logDownload();
$securedl->fileOutput(rawurldecode($securedl->file));

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_securedl/class.tx_nawsecuredl_output.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_securedl/class.tx_nawsecuredl_output.php']);
}
?>

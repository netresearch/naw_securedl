<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Dietrich Heise (heise at naw de)
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
 * @author	Dietrich Heise <heise at naw.de>
 */

// *******************************
// Set error reporting
// *******************************
error_reporting (E_ALL ^ E_NOTICE);

// ***********************
// Paths are setup
// ***********************
define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('TYPO3_MODE','FE');
define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME']))));
define('PATH_site', dirname(dirname(dirname(dirname(PATH_thisScript)))).'/');
define('PATH_t3lib', PATH_site.'t3lib/');

if (@is_dir(PATH_site.'typo3/sysext/cms/tslib/')) {
        define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
} elseif (@is_dir(PATH_site.'tslib/')) {
        define('PATH_tslib', PATH_site.'tslib/');
}
if (PATH_tslib=='') {
        die('Cannot find tslib/. Please set path by defining $configured_tslib_path in '.basename(PATH_thisScript).'.');
}

define('PATH_typo3conf', PATH_site.'typo3conf/');
define('TYPO3_mainDir', 'typo3/');		// This is the directory of the backend administration for the sites of this TYPO3 installation.


require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');
require_once(PATH_t3lib.'class.t3lib_db.php');
require_once(PATH_t3lib.'config_default.php');

$u = t3lib_div::_GP('u');
if (!$u){
	$u = 0;
}

$hash = t3lib_div::_GP('hash');
$t = t3lib_div::_GP('t');
$file = t3lib_div::_GP('file');
$key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];

$data = $u.$file.$t.$key;
$checkhash = md5($data);

if ($checkhash != $hash){
	//print_r($_GET);
	exit ('Access denied!');
}

if ($u != '0') {

	require_once(PATH_tslib.'class.tslib_fe.php');
	require_once(PATH_t3lib.'class.t3lib_cs.php');
	require_once(PATH_t3lib.'class.t3lib_userauth.php');
	require_once(PATH_tslib.'class.tslib_feuserauth.php');
	require_once(PATH_t3lib.'class.t3lib_befunc.php');
	
	$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
	$TYPO3_DB->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password);

	//Create and init $TSFE object (TSFE = TypoScript Front End)
	$tempClassName=t3lib_div::makeInstanceClassName('tslib_fe');
	$TSFE = new $tempClassName($TYPO3_CONF_VARS,t3lib_div::GPvar('id'),t3lib_div::GPvar('type'));
	$TSFE->connectToMySQL();
	$TSFE->initFEuser();
	$TSFE->initUserGroups();
	$this->feuser = $GLOBALS['TSFE']->fe_user->user['username'];

	if ($u != $this->feuser){
		exit ('Access denied!!');
	}

}

function fileOutput($file){

$file = PATH_site.'/'.$file;

if (file_exists($file)){

	$bildinfos=getimagesize($file);
	$bildtypnr=$bildinfos[2];

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
		default:
			$contenttypedatei='application/octet-stream';
			break;
			}//end of switch Case structure
		}

		header('Content-Type: '.$contenttypedatei);
		header('Content-Disposition: inline; filename='.basename($file));
		readfile($file);
	}else{
		print "File does not exists!";
	}
}
fileOutput($file);
?>
<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 'EXT:naw_securedl/class.tx_nawsecuredl.php:&tx_nawsecuredl->parseFE';
$TYPO3_CONF_VARS['FE']['XCLASS']['tslib/showpic.php'] = t3lib_extMgm::extPath($_EXTKEY)."class.ux_SC_tslib_showpic.php";
?>
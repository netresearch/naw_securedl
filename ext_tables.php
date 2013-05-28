<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

if (TYPO3_MODE == 'BE')	{
    $_EXTCONF = unserialize($_EXTCONF);
    if ($_EXTCONF['log'])	{
	    t3lib_extMgm::addModule(
	        'tools', 'txnawsecuredlM1', '',
	        t3lib_extMgm::extPath($_EXTKEY) . 'modLog/'
	    );
	    unset ($_EXTCONF);
    }
}

?>

<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


	// hook when "save&view"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_previewrecords'] = 'EXT:previewrecords/Classes/Preview.php:tx_previewrecords_preview';

$GLOBALS['TYPO3_CONF_VARS']['FE']['XCLASS']['tslib/class.tslib_fe.php'] = t3lib_extMgm::extPath('previewrecords', 'Classes/Tsfe.php');

	// show the preview icon in the list module as well
$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'][] = 'EXT:previewrecords/Classes/Dblist.php:tx_previewrecords_dblist';



?>
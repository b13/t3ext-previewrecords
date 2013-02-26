<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Benjamin Mack <benni@typo3.org>
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class tx_previewrecords_preview {

	public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $pObj) {
		if (isset($GLOBALS['_POST']['_savedokview_x'])) {
				// direct preview
			if (!is_numeric($id)) {
				$id = $pObj->substNEWwithIDs[$id];
			}
	
			if ($status == 'update') {
				$pid = $pObj->getPID($table, $id);
			} else {
				$pid = $fieldArray['pid'];
			}
	
	
				// NOT using t3lib_BEfunc::getTSCpid() because we need the real pid - not the ID of a page, if the input is a page...
			$tscPID = t3lib_BEfunc::getTSconfig_pidValue($table, $uid, $pid);
			$TSConfig = $pObj->getTCEMAIN_TSconfig($tscPID);
			$TSConfig = $pObj->getTableEntries($table, $TSConfig);

			if ($TSConfig['saveAndViewPageId']) {
				$GLOBALS['_POST']['popViewId'] = urlencode(htmlspecialchars($TSConfig['saveAndViewPageId']));
				$additionalParams = $TSConfig['saveAndViewAdditionalParams'];
					// todo, make this better
				if (strpos($additionalParams, '{') !== FALSE) {
					$recordInfo = t3lib_BEfunc::getRecord($table, $id);
					foreach ($recordInfo as $fieldName => $field) {
						$additionalParams = str_replace('{field:' . $fieldName . '}', $field, $additionalParams);
					}
				}
				
				if ($additionalParams) {
					$GLOBALS['_POST']['popViewId_addParams'] = $additionalParams;
				}
			}
		}
	}

}

?>
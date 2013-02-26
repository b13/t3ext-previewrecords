<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Benjamin Mack <benni@typo3.org>
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
/**
 * extends the local records list so the view icon is shown all the time as well
 */
require_once(PATH_typo3 . '/interfaces/interface.localrecordlist_actionsHook.php');

class tx_previewrecords_dblist implements localRecordList_actionsHook {

	// cached info
	static $pageTSconfig = array();

	/**
	 * check if we should modify the table
	 */
	protected function isValidTable($table, $row) {
		if ($table == 'tt_content' || $table == 'pages') {
			return FALSE;
		}

		$TSconfig = $this->getTSconfigForTable($table, $row);
		if ($TSconfig['saveAndViewPageId'] || $TSconfig['saveAndViewAdditionalParams']) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * fetch the TCEMAIN.TSconfig for a table
	 */
	protected function getTSconfigForTable($table, $row) {
		if (!isset($this->pageTSconfig[$table])) {
				// NOT using t3lib_BEfunc::getTSCpid() because we need the real pid - not the ID of a page, if the input is a page...
			$tscPID = t3lib_BEfunc::getTSconfig_pidValue($table, $row['uid'], $row['pid']);
			$TSConfig = $GLOBALS['BE_USER']->getTSConfig('TCEMAIN', t3lib_BEfunc::getPagesTSconfig($tscPID));
			$this->pageTSconfig[$table] = $this->getTSconfigTableEntries($table, $TSConfig);
		}
		return $this->pageTSconfig[$table];
	}


	/**
	 * Extract entries from TSconfig for a specific table. This will merge specific and default configuration together.
	 *
	 * @param	string		Table name
	 * @param	array		TSconfig for page
	 * @return	array		TSconfig merged
	 * @see getTCEMAIN_TSconfig()
	 */
	protected function getTSconfigTableEntries($table, $TSconfig) {
		$tA = is_array($TSconfig['properties'][$table . '.']) ? $TSconfig['properties'][$table . '.'] : array();
		$dA = is_array($TSconfig['properties']['default.']) ? $TSconfig['properties']['default.'] : array();
		return t3lib_div::array_merge_recursive_overrule($dA, $tA);
	}




	/**
	 * modifies Web>List control icons of a displayed row
	 *
	 * @param	string		the current database table
	 * @param	array		the current record row
	 * @param	array		the default control-icons to get modified
	 * @param	object		Instance of calling object
	 * @return	array		the modified control-icons
	 */
	public function makeControl($table, $row, $cells, &$parentObject) {
		if ($this->isValidTable($table, $row)) {
		
			$TSconfig = $this->pageTSconfig[$table];

			$row = t3lib_BEfunc::getRecordWSOL($table, $row['uid']);

			$pageId = $this->insertData($TSconfig['saveAndViewPageId.']['data'], $row);
			if (!$pageId) {
				$pageId = $this->insertData($TSconfig['saveAndViewPageId'], $row);
			}
			$additionalParams = $this->insertData($TSconfig['saveAndViewAdditionalParams'], $row);

			$cells['view'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::viewOnClick($pageId, $parentObject->backPath, '', '', '', $additionalParams)).'" title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showPage', TRUE).'">'.
				t3lib_iconWorks::getSpriteIcon('actions-document-view') .
			'</a>';
		}
		return $cells;
	}
	
	/**
	 * a small version of the known "insertData"
	 * function
	 */
	protected function insertData($input, $data) {
		if (strpos($input, '{') !== FALSE) {
			foreach ($data as $fieldName => $fieldValue) {
				$input = str_replace('{field:' . $fieldName . '}', $fieldValue, $input);
			}
		}
		return $input;
	}





	/**********************
	 * UNTOUCHED FUNCTIONS
	 *********************/



	/**
	 * modifies Web>List clip icons (copy, cut, paste, etc.) of a displayed row
	 *
	 * @param	string		the current database table
	 * @param	array		the current record row
	 * @param	array		the default clip-icons to get modified
	 * @param	object		Instance of calling object
	 * @return	array		the modified clip-icons
	 */
	public function makeClip($table, $row, $cells, &$parentObject) {
		return $cells;
	}

	/**
	 * modifies Web>List header row columns/cells
	 *
	 * @param	string		the current database table
	 * @param	array		Array of the currently displayed uids of the table
	 * @param	array		An array of rendered cells/columns
	 * @param	object		Instance of calling (parent) object
	 * @return	array		Array of modified cells/columns
	 */
	public function renderListHeader($table, $currentIdList, $headerColumns, &$parentObject) {
		return $headerColumns;
	}


	/**
	 * modifies Web>List header row clipboard/action icons
	 *
	 * @param	string		the current database table
	 * @param	array		Array of the currently displayed uids of the table
	 * @param	array		An array of the current clipboard/action icons
	 * @param	object		Instance of calling (parent) object
	 * @return	array		Array of modified clipboard/action icons
	 */
	public function renderListHeaderActions($table, $currentIdList, $cells, &$parentObject) {
		return $cells;
	}

}
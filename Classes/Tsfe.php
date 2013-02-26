<?php

class ux_tslib_fe extends tslib_fe {
	/**
	 * Looking for a ADMCMD_prev code, looks it up if found and returns configuration data.
	 * Background: From the backend a request to the frontend to show a page, possibly with workspace preview can be "recorded" and associated with a keyword. When the frontend is requested with this keyword the associated request parameters are restored from the database AND the backend user is loaded - only for that request.
	 * The main point is that a special URL valid for a limited time, eg. http://localhost/typo3site/index.php?ADMCMD_prev=035d9bf938bd23cb657735f68a8cedbf will open up for a preview that doesn't require login. Thus it's useful for sending in an email to someone without backend account.
	 * This can also be used to generate previews of hidden pages, start/endtimes, usergroups and those other settings from the Admin Panel - just not implemented yet.
	 *
	 * @return	array		Preview configuration array from sys_preview record.
	 * @see t3lib_BEfunc::compilePreviewKeyword()
	 */
	function ADMCMD_preview(){
		$inputCode = t3lib_div::_GP('ADMCMD_prev');

			// If no inputcode and a cookie is set, load input code from cookie:
		if (!$inputCode && $_COOKIE['ADMCMD_prev'])	{
			$setFromCookie = TRUE;
			$inputCode = $_COOKIE['ADMCMD_prev'];
		}

			// If inputcode now, look up the settings:
		if ($inputCode)	{

			if ($inputCode=='LOGOUT') {	// "log out":
				SetCookie('ADMCMD_prev', '', 0, t3lib_div::getIndpEnv('TYPO3_SITE_PATH'));
				if ($this->TYPO3_CONF_VARS['FE']['workspacePreviewLogoutTemplate'])	{
					if (@is_file(PATH_site.$this->TYPO3_CONF_VARS['FE']['workspacePreviewLogoutTemplate']))	{
						$message = t3lib_div::getUrl(PATH_site.$this->TYPO3_CONF_VARS['FE']['workspacePreviewLogoutTemplate']);
					} else {
						$message = '<strong>ERROR!</strong><br>Template File "'.$this->TYPO3_CONF_VARS['FE']['workspacePreviewLogoutTemplate'].'" configured with $TYPO3_CONF_VARS["FE"]["workspacePreviewLogoutTemplate"] not found. Please contact webmaster about this problem.';
					}
				} else {
					$message = 'You logged out from Workspace preview mode. Click this link to <a href="%1$s">go back to the website</a>';
				}

				$returnUrl = t3lib_div::sanitizeLocalUrl(t3lib_div::_GET('returnUrl'));
				die(sprintf($message,
					htmlspecialchars(preg_replace('/\&?ADMCMD_prev=[[:alnum:]]+/', '', $returnUrl))
					));
			}

				// Look for keyword configuration record:
			$previewData = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
				'*',
				'sys_preview',
				'keyword='.$GLOBALS['TYPO3_DB']->fullQuoteStr($inputCode, 'sys_preview').
					' AND endtime>' . $GLOBALS['EXEC_TIME']
			);

				// Get: Backend login status, Frontend login status
				// - Make sure to remove fe/be cookies (temporarily); BE already done in ADMCMD_preview_postInit()
			if (is_array($previewData))	{
				if ($setFromCookie || !count(t3lib_div::_POST())) {
						// Unserialize configuration:
					$previewConfig = unserialize($previewData['config']);

							// For full workspace preview we only ADD a get variable to set the preview 
							// of the workspace - so all other Get vars are accepted. Hope this is not 
							// a security problem. Still posting is not allowed and even if a backend 
							// user get initialized it shouldn't lead to situations where users can 
							// use those credentials.
					if ($previewConfig['fullWorkspace']) {

							// Set the workspace preview value:
						t3lib_div::_GETset($previewConfig['fullWorkspace'],'ADMCMD_previewWS');

							// If ADMCMD_prev is set the $inputCode value cannot come from a cookie
							// and we set that cookie here. Next time it will be found from the cookie
							// if ADMCMD_prev is not set again...
						if (t3lib_div::_GP('ADMCMD_prev'))	{
								// Lifetime is 1 hour, does it matter much? Requires the user to click the link from their email again if it expires.
							SetCookie('ADMCMD_prev', t3lib_div::_GP('ADMCMD_prev'), 0, t3lib_div::getIndpEnv('TYPO3_SITE_PATH'));
						}
						return $previewConfig;
					} elseif (t3lib_div::getIndpEnv('TYPO3_SITE_URL').'index.php?ADMCMD_prev='.$inputCode === t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'))	{

							// Set GET variables:
						$GET_VARS = '';
						parse_str($previewConfig['getVars'], $GET_VARS);
						t3lib_div::_GETset($GET_VARS);

							// Return preview keyword configuration:
						return $previewConfig;
					} else die(htmlspecialchars('Request URL did not match "'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').'index.php?ADMCMD_prev='.$inputCode.'"'));	// This check is to prevent people from setting additional GET vars via realurl or other URL path based ways of passing parameters.
				} else die('POST requests are incompatible with keyword preview.');
			} else die('ADMCMD command could not be executed! (No keyword configuration found)');
		}
	}
}
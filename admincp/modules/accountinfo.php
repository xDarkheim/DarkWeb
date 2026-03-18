<?php
use Darkheim\Application\Account\Account;
use Darkheim\Infrastructure\Email\Email;
use Darkheim\Application\Character\Character;
use Darkheim\Domain\Validator;
$accountInfoConfig['showGeneralInfo'] = true;
$accountInfoConfig['showStatusInfo'] = true;
$accountInfoConfig['showIpInfo'] = true;
$accountInfoConfig['showCharacters'] = true;

if(isset($_GET['u'])) {
	try {
		$Account = new Account();
		$userId = $Account->retrieveUserID($_GET['u']);
		if(check_value($userId)) {
			redirect(3, admincp_base('accountinfo&id='.$userId));
		}
	} catch(Exception $ex) {
		message('error', $ex->getMessage());
	}
}

if(isset($_GET['id'])) {
	try {
		if(isset($_POST['editaccount_submit'])) {
			try {
				if(!isset($_POST['action'])) {
					throw new RuntimeException("Invalid request.");
				}
				$sendEmail = ((isset($_POST['editaccount_sendmail'])
					&& $_POST['editaccount_sendmail'] == 1));
				$accountInfo = $common->accountInformation($_GET['id']);
				if(!$accountInfo) {
					throw new RuntimeException(
						"Could not retrieve account information (invalid account)."
					);
				}
				switch($_POST['action']) {
					case "changepassword":
						if(!isset($_POST['changepassword_newpw'])) {
							throw new RuntimeException(
								"Please enter the new password."
							);
						}
						if(!Validator::PasswordLength($_POST['changepassword_newpw'])) {
							throw new RuntimeException("Invalid password.");
						}
						if(!$common->changePassword($accountInfo[_CLMN_MEMBID_], $accountInfo[_CLMN_USERNM_], $_POST['changepassword_newpw'])) {
							throw new RuntimeException("Could not change password.");
						}
						message('success', 'Password updated!');
						
						// send new password
						if(isset($_POST['editaccount_sendmail'])) {
							$email = new Email();
							$email->setTemplate('ADMIN_CHANGE_PASSWORD');
							$email->addVariable('{USERNAME}', $accountInfo[_CLMN_USERNM_]);
							$email->addVariable('{NEW_PASSWORD}', $_POST['changepassword_newpw']);
							$email->addAddress($accountInfo[_CLMN_EMAIL_]);
							$email->send();
						}
						break;
					case "changeemail":
						if(!isset($_POST['changeemail_newemail'])) {
							throw new RuntimeException("Please enter the new email.");
						}
						if(!Validator::Email($_POST['changeemail_newemail'])) {
							throw new RuntimeException("Invalid email address.");
						}
						if($common->emailExists($_POST['changeemail_newemail'])) {
							throw new RuntimeException(
								"Another account with the same email already exists."
							);
						}
						if(!$common->updateEmail($accountInfo[_CLMN_MEMBID_], $_POST['changeemail_newemail'])) {
							throw new RuntimeException("Could not update email.");
						}
						message('success', 'Email address updated!');
						
						// send a new email to the current email
						if(isset($_POST['editaccount_sendmail'])) {
							$email = new Email();
							$email->setTemplate('ADMIN_CHANGE_EMAIL');
							$email->addVariable('{USERNAME}', $accountInfo[_CLMN_USERNM_]);
							$email->addVariable('{NEW_EMAIL}', $_POST['changeemail_newemail']);
							$email->addAddress($accountInfo[_CLMN_EMAIL_]);
							$email->send();
						}
						break;
					default:
						throw new RuntimeException("Invalid request.");
				}
			} catch(Exception $ex) {
				message('error', $ex->getMessage());
			}
		}
	
		$accountInfo = $common->accountInformation($_GET['id']);
		if(!$accountInfo) {
			throw new RuntimeException(
				"Could not retrieve account information (invalid account)."
			);
		}
		
		echo '<h1 class="page-header">Account Information: <small>'.$accountInfo[_CLMN_USERNM_].'</small></h1>';
		
		echo '<div class="row">';
			echo '<div class="col-md-6">';
			
				if($accountInfoConfig['showGeneralInfo']) {
					// GENERAL ACCOUNT INFORMATION
					echo '<div class="panel panel-primary">';
					echo '<div class="panel-heading">General Information</div>';
					echo '<div class="panel-body">';
					
						$isBanned = ($accountInfo[_CLMN_BLOCCODE_] == 0 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Banned</span>');
						echo '<table class="table table-no-border table-hover">';
							echo '<tr>';
								echo '<th>ID:</th>';
								echo '<td>'.$accountInfo[_CLMN_MEMBID_].'</td>';
							echo '</tr>';
							echo '<tr>';
								echo '<th>Username:</th>';
								echo '<td>'.$accountInfo[_CLMN_USERNM_].'</td>';
							echo '</tr>';
							echo '<tr>';
								echo '<th>Email:</th>';
								echo '<td>'.$accountInfo[_CLMN_EMAIL_].'</td>';
							echo '</tr>';
							
							echo '<tr>';
								echo '<th>Banned:</th>';
								echo '<td>'.$isBanned.'</td>';
							echo '</tr>';
						echo '</table>';
					echo '</div>';
					echo '</div>';
				}
				
				if($accountInfoConfig['showStatusInfo']) {
					// ACCOUNT STATUS
					$statusdb = $dB;
					$statusData = $statusdb->query_fetch_single("SELECT * FROM "._TBL_MS_." WHERE "._CLMN_MS_MEMBID_." = ?", array($accountInfo[_CLMN_USERNM_]));
					echo '<div class="panel panel-info">';
					echo '<div class="panel-heading">Status Information</div>';
					echo '<div class="panel-body">';
						if(is_array($statusData)) {
							$onlineStatus = ($statusData[_CLMN_CONNSTAT_] == 1 ? '<span class="label label-success">Online</span>' : '<span class="label label-danger">Offline</span>');
							echo '<table class="table table-no-border table-hover">';
								echo '<tr>';
									echo '<td>Status:</td>';
									echo '<td>'.$onlineStatus.'</td>';
								echo '</tr>';
								echo '<tr>';
									echo '<td>Server:</td>';
									echo '<td>'.$statusData[_CLMN_MS_GS_].'</td>';
								echo '</tr>';
							echo '</table>';
						} else {
							inline_message('info', 'No data found in '._TBL_MS_.' for this account.');
						}
					echo '</div>';
					echo '</div>';
				}
				
				if($accountInfoConfig['showCharacters']) {
					// ACCOUNT CHARACTERS
					$Character = new Character();
					$accountCharacters = $Character->AccountCharacter($accountInfo[_CLMN_USERNM_]);
					echo '<div class="panel panel-default">';
					echo '<div class="panel-heading">Characters</div>';
					echo '<div class="panel-body">';
						if(is_array($accountCharacters)) {
							echo '<table class="table table-no-border table-hover">';
								foreach($accountCharacters as $characterName) {
									echo '<tr>';
										echo '<td><a href="'.admincp_base("editcharacter&name=".$characterName).'">'.$characterName.'</a></td>';
									echo '</tr>';
								}
							echo '</table>';
						} else {
							inline_message('info', 'No characters found.');
						}
					echo '</div>';
					echo '</div>';
				}
				
				// CHANGE PASSWORD
				echo '<div class="panel panel-default">';
				echo '<div class="panel-heading">Change Account\'s Password</div>';
				echo '<div class="panel-body">';
					echo '<form role="form" method="post">';
					echo '<input type="hidden" name="action" value="changepassword"/>';
						echo '<div class="form-group">';
							echo '<label for="input_1">New Password:</label>';
							echo '<input type="text" class="form-control" id="input_1" name="changepassword_newpw" placeholder="New password">';
						echo '</div>';
						echo '<div class="checkbox">';
							echo '<label><input type="checkbox" name="editaccount_sendmail" value="1" checked> Email the user about this change.</label>';
						echo '</div>';
						echo '<button type="submit" name="editaccount_submit" class="btn btn-success" value="ok">Change Password</button>';
					echo '</form>';
				echo '</div>';
				echo '</div>';
				
				// CHANGE EMAIL
				echo '<div class="panel panel-default">';
				echo '<div class="panel-heading">Change Account\'s Email</div>';
				echo '<div class="panel-body">';
					echo '<form role="form" method="post">';
					echo '<input type="hidden" name="action" value="changeemail"/>';
						echo '<div class="form-group">';
							echo '<label for="input_2">New Email:</label>';
							echo '<input type="email" class="form-control" id="input_2" name="changeemail_newemail" placeholder="New email address">';
						echo '</div>';
						echo '<div class="checkbox">';
							echo '<label><input type="checkbox" name="editaccount_sendmail" value="1" checked> Email the user about this change.</label>';
						echo '</div>';
						echo '<button type="submit" name="editaccount_submit" class="btn btn-success" value="ok">Change Email</button>';
					echo '</form>';
				echo '</div>';
				echo '</div>';
				
			echo '</div>';
			echo '<div class="col-md-6">';
				
				if($accountInfoConfig['showIpInfo']) {
					$hasMuLogEx = defined('_TBL_LOGEX_') && defined('_CLMN_LOGEX_IP_');
					$hasConnectionHistory = defined('_TBL_CH_')
						&& defined('_CLMN_CH_IP_')
						&& defined('_CLMN_CH_ACCID_')
						&& defined('_CLMN_CH_STATE_')
						&& defined('_CLMN_CH_ID_')
						&& defined('_CLMN_CH_DATE_')
						&& defined('_CLMN_CH_SRVNM_')
						&& defined('_CLMN_CH_HWID_');
					
					if($hasMuLogEx) {
						$tblLogEx = constant('_TBL_LOGEX_');
						$clmnLogExIp = constant('_CLMN_LOGEX_IP_');
						// ACCOUNTS IP ADDRESS (MuEngine - MuLogEx tbl)
						$checkMuLogEx = $dB->query_fetch_single("SELECT * FROM sysobjects WHERE xtype = 'U' AND name = ?", array($tblLogEx));
						echo '<div class="panel panel-default">';
						echo '<div class="panel-heading">Account\'s IP Address (MuEngine)</div>';
						echo '<div class="panel-body">';
							if($checkMuLogEx) {
								$accountIpAddress = $common->retrieveAccountIPs($accountInfo[_CLMN_USERNM_]);
								if(is_array($accountIpAddress)) {
									echo '<table class="table table-no-border table-hover">';
										foreach($accountIpAddress as $accountIp) {
											echo '<tr>';
												echo '<td><a href="https://whatismyipaddress.com/ip/'
																	.urlencode($accountIp[$clmnLogExIp]).'" target="_blank">'.$accountIp[$clmnLogExIp].'</a></td>';
											echo '</tr>';
										}
									echo '</table>';
								} else {
									inline_message('info', 'No IP address found.');
								}
							} else {
								inline_message('warning', 'Could not find table '.$tblLogEx.' in the database.');
							}
						echo '</div>';
						echo '</div>';
					}
					
					if($hasConnectionHistory) {
						$tblCh = constant('_TBL_CH_');
						$clmnChIp = constant('_CLMN_CH_IP_');
						$clmnChAccid = constant('_CLMN_CH_ACCID_');
						$clmnChState = constant('_CLMN_CH_STATE_');
						$clmnChId = constant('_CLMN_CH_ID_');
						$clmnChDate = constant('_CLMN_CH_DATE_');
						$clmnChServer = constant('_CLMN_CH_SRVNM_');
						$clmnChHwid = constant('_CLMN_CH_HWID_');
						$accountDB = $dB;

						// ACCOUNT IP LIST
						echo '<div class="panel panel-default">';
						echo '<div class="panel-heading">Account\'s IP Address</div>';
						echo '<div class="panel-body">';
							
							$accountIpHistory = $accountDB->query_fetch("SELECT DISTINCT(".$clmnChIp.") FROM ".$tblCh." WHERE ".$clmnChAccid." = ?", array($accountInfo[_CLMN_USERNM_]));
							if(is_array($accountIpHistory)) {
								echo '<table class="table table-no-border table-hover">';
									foreach($accountIpHistory as $accountIp) {
										echo '<tr>';
											echo '<td><a href="https://whatismyipaddress.com/ip/'
													.urlencode($accountIp[$clmnChIp]).'" target="_blank">'.$accountIp[$clmnChIp].'</a></td>';
										echo '</tr>';
									}
								echo '</table>';
							} else {
								inline_message('info', 'No IP addresses found in the database.');
							}
							
						echo '</div>';
						echo '</div>';
						
						// ACCOUNT CONNECTION HISTORY
						echo '<div class="panel panel-default">';
						echo '<div class="panel-heading">Account Connection History (last 25)</div>';
						echo '<div class="panel-body">';
							
							$accountConHistory = $accountDB->query_fetch("SELECT TOP 25 * FROM ".$tblCh." WHERE ".$clmnChAccid." = ? AND ".$clmnChState." = ? ORDER BY ".$clmnChId." DESC", array($accountInfo[_CLMN_USERNM_], 'Connect'));
							if(is_array($accountConHistory)) {
								echo '<table class="table table-no-border table-hover">';
									echo '<tr>';
										echo '<th>Date</th>';
										echo '<th class="hidden-xs">Server</th>';
										echo '<th>IP</th>';
										echo '<th>HWID</th>';
									echo '</tr>';
									foreach($accountConHistory as $connection) {
										echo '<tr>';
											echo '<td>'.$connection[$clmnChDate].'</td>';
											echo '<td class="hidden-xs">'.$connection[$clmnChServer].'</td>';
											echo '<td>'.$connection[$clmnChIp].'</td>';
											echo '<td>'.$connection[$clmnChHwid].'</td>';
										echo '</tr>';
									}
								echo '</table>';
							} else {
								inline_message('info', 'No connection history found for account.');
							}
							
						echo '</div>';
						echo '</div>';
					}
					
				}
				
			echo '</div>';
		echo '</div>';
		
	} catch(Exception $ex) {
		echo '<h1 class="page-header">Account Information</h1>';
		message('error', $ex->getMessage());
	}
	
} else {
	echo '<h1 class="page-header">Account Information</h1>';
	message('error', 'Please provide a valid user id.');
}